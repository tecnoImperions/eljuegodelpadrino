<?php
session_start();
if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['admin', 'trabajador'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo "ID de usuario inválido.";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$usuario = $res->fetch_assoc();
$stmt->close();

if (!$usuario) {
    echo "Usuario no encontrado.";
    exit;
}

$stmt = $conn->prepare("
    SELECT s.titulo, p.estado, p.cantidad_boletos, p.fecha_participacion
    FROM participaciones p
    JOIN sorteos s ON p.id_sorteo = s.id
    WHERE p.id_usuario = ?
    ORDER BY p.fecha_participacion DESC
");
$stmt->bind_param("i", $id);
$stmt->execute();
$participaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function formatoFecha($fecha) {
    return date('d/m/Y H:i', strtotime($fecha));
}
function mostrarEstado($estado) {
    $colores = [
        'activo' => 'green',
        'bloqueado' => 'red',
        'pendiente' => 'orange',
        'comprobado' => 'green',
        'rechazado' => 'red',
        'ganador' => 'gold',
        'perdedor' => 'gray'
    ];
    $color = $colores[$estado] ?? 'white';
    return "<span style='color: $color; font-weight: bold; text-transform: capitalize;'>$estado</span>";
}
function mostrarRol($rol) {
    return ucfirst($rol);
}
function mostrarPlan($plan) {
    $mapa = [
        'gratuito' => 'Gratuito',
        'plus' => 'Plus',
        'pro' => 'Pro'
    ];
    return $mapa[$plan] ?? ucfirst($plan);
}

// --- 📸 Rutas de la foto de perfil ---
$fotoNombre = $usuario['foto_perfil'] ?? '';
$rutaServidorFoto = __DIR__ . '/../uploads/perfiles/' . $fotoNombre;

// Detectar base URL dinámicamente
$baseUrl = dirname($_SERVER['SCRIPT_NAME'], 2);
$rutaWebFoto = $baseUrl . '/uploads/perfiles/' . $fotoNombre;

// Verificar si el archivo existe
$fotoPerfil = (!empty($fotoNombre) && file_exists($rutaServidorFoto))
    ? $rutaWebFoto
    : $baseUrl . '/assets/default_profile.png';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Ficha del Usuario - <?= htmlspecialchars($usuario['nombre']) ?></title>
  <style>
    body {
      background: #111;
      color: #f0e6d2;
      font-family: 'Georgia', serif;
      padding: 20px;
      margin: 0;
    }
    h1, h2 {
      text-align: center;
      color: #ffcc00;
      margin-bottom: 0.5em;
    }
    .container {
      max-width: 900px;
      margin: 0 auto;
      background-color: #1a1a1a;
      border-radius: 12px;
      padding: 25px 40px 40px 40px;
      box-shadow: 0 0 20px #000;
    }
    .info {
      display: flex;
      justify-content: flex-start;
      align-items: flex-start;
      gap: 30px;
      margin-bottom: 30px;
      position: relative;
    }
    .foto-perfil {
      position: absolute;
      top: 0;
      right: 0;
      width: 100px;
      height: 100px;
      border: 4px solid #ffcc00;
      border-radius: 8px;
      overflow: hidden;
      background: #333;
      box-shadow: 0 0 15px #000, 0 0 8px #ffcc00 inset;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 8px;
      cursor: pointer;
    }
    .foto-perfil img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      border-radius: 4px;
      pointer-events: none;
    }
    .datos {
      flex: 1 1 60%;
      min-width: 300px;
    }
    .datos p {
      margin: 10px 0;
      font-size: 1.1em;
    }
    .datos p strong {
      color: #ffcc00;
    }
    .info-adicional {
      background: #222;
      border-radius: 10px;
      padding: 15px;
      margin-top: 15px;
      font-style: italic;
      color: #ddd;
      white-space: pre-wrap;
      border-left: 4px solid #ffcc00;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
      font-size: 1em;
    }
    th, td {
      padding: 12px 15px;
      border-bottom: 1px solid #333;
      text-align: left;
    }
    th {
      background-color: #222;
      color: #ffcc00;
    }
    tbody tr:hover {
      background-color: #2a2a2a;
    }
    @media (max-width: 768px) {
      .info {
        flex-direction: column-reverse;
        align-items: center;
      }
      .datos {
        min-width: auto;
        width: 100%;
        margin-top: 15px;
      }
      .foto-perfil {
        position: relative;
        margin-bottom: 1rem;
        top: auto;
        right: auto;
        width: 80px;
        height: 80px;
      }
    }
    .btn-volver {
      display: inline-block;
      margin: 10px 10px 20px 0;
      background-color: #444;
      color: #ffcc00;
      padding: 10px 18px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      transition: background 0.3s;
    }
    .btn-volver:hover {
      background-color: #666;
    }

    /* Overlay para zoom */
    #zoom-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background-color: rgba(0,0,0,0.85);
      backdrop-filter: blur(3px);
      z-index: 9999;
      justify-content: center;
      align-items: center;
      cursor: zoom-out;
      transition: opacity 0.3s ease;
      opacity: 0;
    }
    #zoom-overlay.active {
      display: flex;
      opacity: 1;
    }
    #zoom-overlay img {
      max-width: 90vw;
      max-height: 90vh;
      border-radius: 12px;
      box-shadow: 0 0 25px #ffcc00;
      transition: transform 0.3s ease;
      cursor: zoom-out;
      user-select: none;
    }
    /* Botón cerrar */
    #zoom-overlay button {
      position: absolute;
      top: 20px;
      right: 30px;
      background: #ffcc00;
      border: none;
      padding: 8px 14px;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      color: #111;
      font-size: 16px;
      box-shadow: 0 0 10px #000;
      transition: background-color 0.2s ease;
    }
    #zoom-overlay button:hover {
      background-color: #e6b800;
    }
  </style>
</head>
<body>

<div class="container">

  <h1>🕵️ Detalles del Usuario</h1>

  <section class="info">
    <div class="datos">
      <h2>👤 Información Personal</h2>
      <p><strong>ID:</strong> <?= $usuario['id'] ?></p>
      <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']) ?></p>
      <p><strong>Correo:</strong> <?= htmlspecialchars($usuario['correo']) ?></p>
      <p><strong>Celular:</strong> <?= htmlspecialchars($usuario['celular'] ?: 'No registrado') ?></p>
      <p><strong>Rol:</strong> <?= mostrarRol($usuario['rol']) ?></p>
      <p><strong>Plan:</strong> <?= mostrarPlan($usuario['plan']) ?></p>
      <p><strong>Estado:</strong> <?= mostrarEstado($usuario['estado']) ?></p>
      <p><strong>Fecha de Registro:</strong> <?= formatoFecha($usuario['fecha_registro']) ?></p>
      <div class="info-adicional">
        <?= nl2br(htmlspecialchars($usuario['info_adicional'] ?: 'Sin información adicional.')) ?>
      </div>
    </div>

    <div class="foto-perfil" title="Carnet de <?= htmlspecialchars($usuario['nombre']) ?>" id="fotoPerfil">
      <img src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto de perfil" />
    </div>
  </section>

  <section class="participaciones">
    <h2>🎟 Participaciones</h2>
    <?php if (count($participaciones) > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Sorteo</th>
          <th>Boletos</th>
          <th>Estado</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($participaciones as $p): ?>
        <tr>
          <td data-label="Sorteo"><?= htmlspecialchars($p['titulo']) ?></td>
          <td data-label="Boletos"><?= $p['cantidad_boletos'] ?></td>
          <td data-label="Estado"><?= mostrarEstado($p['estado']) ?></td>
          <td data-label="Fecha"><?= formatoFecha($p['fecha_participacion']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <p>Este usuario no ha participado en sorteos aún.</p>
    <?php endif; ?>
  </section>

  <a href="javascript:history.back()" class="btn-volver">🔙 Volver</a>

</div>

<!-- Overlay zoom -->
<div id="zoom-overlay" role="dialog" aria-modal="true" aria-label="Imagen ampliada">
  <button id="btnCerrarZoom" aria-label="Cerrar imagen ampliada">Cerrar ✕</button>
  <img src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto de perfil ampliada" />
</div>

<script>
  const fotoPerfil = document.getElementById('fotoPerfil');
  const zoomOverlay = document.getElementById('zoom-overlay');
  const zoomImg = zoomOverlay.querySelector('img');
  const btnCerrar = document.getElementById('btnCerrarZoom');

  fotoPerfil.addEventListener('click', () => {
    zoomOverlay.classList.add('active');
    document.body.style.overflow = 'hidden'; // Evita scroll al hacer zoom
  });

  btnCerrar.addEventListener('click', cerrarZoom);
  zoomOverlay.addEventListener('click', (e) => {
    // Si el clic es fuera de la imagen o en overlay, cerrar zoom
    if (e.target === zoomOverlay) {
      cerrarZoom();
    }
  });

  function cerrarZoom() {
    zoomOverlay.classList.remove('active');
    document.body.style.overflow = ''; // Restaurar scroll
  }
</script>

</body>
</html>
