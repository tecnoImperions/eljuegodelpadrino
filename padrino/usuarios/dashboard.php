<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION['usuario'];
$usuario_id = $user['id'];
$plan_usuario = $user['plan'];

// Determinar la clase CSS según el plan
$clase_plan = 'plan-gratuito'; // Por defecto
switch($plan_usuario) {
    case 'plus':
        $clase_plan = 'plan-plus';
        break;
    case 'pro':
        $clase_plan = 'plan-pro';
        break;
    default:
        $clase_plan = 'plan-gratuito';
        break;
}

$mensaje_confirmado = null;
$mensaje_key = "mensaje_confirmado_user_{$usuario_id}";
if (isset($_SESSION[$mensaje_key])) {
    $mensaje_confirmado = $_SESSION[$mensaje_key];
    unset($_SESSION[$mensaje_key]);
}

$planes_permitidos = ['gratuito'];
if ($plan_usuario === 'plus') {
    $planes_permitidos = ['gratuito', 'plus'];
} elseif ($plan_usuario === 'pro') {
    $planes_permitidos = ['gratuito', 'plus', 'pro'];
}

$placeholders = implode(',', array_fill(0, count($planes_permitidos), '?'));

$sql = "
    SELECT s.*, 
        (SELECT COALESCE(SUM(p.cantidad_boletos), 0) FROM participaciones p WHERE p.id_sorteo = s.id) AS total_boletos_vendidos
    FROM sorteos s
    WHERE s.estado = 'activo' AND s.plan IN ($placeholders)
    ORDER BY s.fecha_creacion DESC
";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error en la preparación de la consulta: " . $conn->error);
}

$stmt->bind_param(str_repeat('s', count($planes_permitidos)), ...$planes_permitidos);
$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="es" class="<?= $clase_plan ?>">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Usuario <?= ucfirst($plan_usuario) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#000000">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- CSS dinámico por plan -->
    <link rel="stylesheet" href="css/dash.css">

</head>
<body class="<?= $clase_plan ?>">
<div class="container-fluid">
    <div class="mafia-header d-flex justify-content-between align-items-center flex-wrap py-2 px-3">
        <div class="bienvenida d-flex align-items-center flex-wrap gap-2">
            <button class="btn btn-link text-warning fs-3 p-0 me-2 align-baseline" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu" aria-controls="offcanvasMenu"
                    style="text-decoration: none; color: var(--primary-color) !important;">
                ☰
            </button>
            <span style="color: var(--text-color);">
                Bienvenido, <strong><?= htmlspecialchars($user['nombre']); ?></strong>
                <span class="plan-badge" style="
                    background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
                    color: var(--text-contrast);
                    padding: 4px 12px;
                    border-radius: 20px;
                    font-size: 0.9rem;
                    font-weight: bold;
                    margin-left: 8px;
                    box-shadow: 0 2px 10px var(--glow-color);
                ">
                    <?= ucfirst(htmlspecialchars($plan_usuario ?? 'gratuito')); ?>
                </span>
            </span>
            
        </div>
    </div>

    <!-- Menú lateral -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasMenuLabel"
         style="width: 75vw; max-width: 300px;">
        <div class="offcanvas-header border-bottom" style="border-color: var(--primary-color) !important;">
            <h5 class="offcanvas-title" id="offcanvasMenuLabel" style="color: var(--primary-color);">☰ Menú</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body ps-3 pe-2">
            <ul class="nav flex-column">
                <li class="nav-item mb-3">
                    <a class="nav-link fw-bold ps-0" href="#" onclick="filtrarPanel()" 
                       style="color: var(--primary-color) !important;"> Panel de Participación</a>
                </li>
                <li class="nav-item mb-3">
                    <a class="nav-link fw-bold ps-0" href="#" onclick="abrirModalPlanes()"
                       style="color: var(--primary-color) !important;"> Ver Planes</a>
                </li>
                <li class="nav-item mb-3">
                    <a class="nav-link fw-bold ps-0" href="#" onclick="abrirModalContactos()"
                       style="color: var(--primary-color) !important;"> Contactos</a>
                </li>
                <li class="nav-item mb-3">
                    <a class="nav-link fw-bold ps-0" href="perfil.php"
                       style="color: var(--primary-color) !important;"> Mi Perfil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger fw-bold ps-0" href="../logout.php">🔒 Cerrar Sesión</a>
                </li>
            </ul>
        </div>
    </div>

    <?php if ($mensaje_confirmado): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($mensaje_confirmado); ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 style="color: var(--text-color);">🗂️ Sorteos Activos</h4>
        <button class="btn btn-custom" onclick="filtrarPanel()">Panel de Participación</button>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover w-100">
            <thead style="background: linear-gradient(45deg, var(--primary-color), var(--accent-color)); color: var(--text-contrast);">
            <tr>
                <th>Sorteo</th>
                <th>Plan</th>
                <th>Boletos Restantes</th>
                <th>Acción</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()):
                $restantes = max(0, $row['max_participantes'] - $row['total_boletos_vendidos']);
            ?>

                <tr>
                    <td style="color: var(--text-color);"><?= htmlspecialchars($row['titulo']); ?></td>
                    <td style="color: var(--accent-color); font-weight: bold;"><?= ucfirst(htmlspecialchars($row['plan'])); ?></td>
                    <td><span class="badge badge-custom"><?= $restantes; ?></span></td>
                    <td>
                        <?php if ($restantes > 0): ?>
                            <form action="participar.php" method="POST" class="mb-0">
                                <input type="hidden" name="id_sorteo" value="<?= (int)$row['id'] ?>">
                                <button type="submit" class="btn btn-participar w-100" style="color: var(--text-contrast) !important;">Participar</button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-secondary w-100" disabled>
                                Completo
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($result->num_rows === 0): ?>
                <tr><td colspan="4" style="color: var(--text-color);">No hay sorteos disponibles para tu plan actualmente.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="text-center mt-2">
        <a href="../logout.php" class="btn btn-custom">🔒 Cerrar Sesión</a>
    </div>
</div>

<!-- Modal Panel Participación -->
<div class="modal fade" id="modalPanel" tabindex="-1" aria-labelledby="modalPanelLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="border-color: var(--primary-color) !important;">
                <h5 class="modal-title" id="modalPanelLabel" style="color: var(--primary-color);">🎛 Panel de Participación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="contenidoPanel">
                <p class="text-center" style="color: var(--text-color);">⏳ Cargando contenido...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Planes -->
<div class="modal fade" id="modalPlanes" tabindex="-1" aria-labelledby="modalPlanesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="border-color: var(--primary-color) !important;">
                <h5 class="modal-title" id="modalPlanesLabel" style="color: var(--primary-color);">Tus Planes Disponibles</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="contenidoPlanes">
                <p class="text-center" style="color: var(--text-color);">🔄 Cargando contenido...</p>
            </div>
            <div class="modal-footer" style="border-color: var(--primary-color) !important;">
                <button type="button" class="btn btn-custom" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>




<nav class="nav-bottom d-md-none">
    <a href="#" onclick="filtrarPanel()" class="nav-item">
        <i class="bi bi-ui-checks"></i><span>Panel</span>
    </a>
    <a href="#" onclick="abrirModalPlanes()" class="nav-item">
        <i class="bi bi-trophy"></i><span>Planes</span>
    </a>
    <a href="#" onclick="abrirModalContactos()" class="nav-item">
        <i class="bi bi-telephone"></i><span>Contactos</span>
    </a>
    <a href="perfil.php" class="nav-item">
        <i class="bi bi-person"></i><span>Perfil</span>
    </a>


</nav>


<!-- Scripts -->
<script src="../recarga-suave.js"></script>
  <!-- Script de pantalla completa -->
<script src="../pantallaCompleta.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>



function filtrarPanel() {
    const modal = new bootstrap.Modal(document.getElementById('modalPanel'));
    modal.show();
    const contenedor = document.getElementById('contenidoPanel');
    contenedor.innerHTML = '<p class="text-center">🔄 Cargando datos...</p>';
    fetch('panel_modal.php')
        .then(res => res.text())
        .then(html => contenedor.innerHTML = html)
        .catch(err => contenedor.innerHTML = '<div class="alert alert-danger">❌ Error al cargar el panel.</div>');
}

function abrirModalPlanes() {
    const modal = new bootstrap.Modal(document.getElementById('modalPlanes'));
    modal.show();
    const contenedor = document.getElementById('contenidoPlanes');
    contenedor.innerHTML = '<p class="text-center">🔄 Cargando datos...</p>';
    fetch('plan.php')
        .then(response => response.text())
        .then(html => contenedor.innerHTML = html)
        .catch(() => contenedor.innerHTML = '<div class="alert alert-danger">❌ No se pudo cargar la información del plan.</div>');
}

// Agrega esta función a tu script existente, justo después de la función abrirModalPlanes()

function abrirModalContactos() {
    // Crear modal dinámicamente si no existe
    let modalContactos = document.getElementById('modalContactos');
    if (!modalContactos) {
        const modalHTML = `
        <div class="modal fade" id="modalContactos" tabindex="-1" aria-labelledby="modalContactosLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header" style="border-color: var(--primary-color) !important;">
                        <h5 class="modal-title" id="modalContactosLabel" style="color: var(--primary-color);">📞 Contactos</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body" id="contenidoContactos">
                        <p class="text-center" style="color: var(--text-color);">🔄 Cargando contenido...</p>
                    </div>
                    <div class="modal-footer" style="border-color: var(--primary-color) !important;">
                        <button type="button" class="btn btn-custom" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        modalContactos = document.getElementById('modalContactos');
    }
    
    const modal = new bootstrap.Modal(modalContactos);
    modal.show();
    const contenedor = document.getElementById('contenidoContactos');
    contenedor.innerHTML = '<p class="text-center">🔄 Cargando datos...</p>';
    fetch('modal_contactos.php')
        .then(response => response.text())
        .then(html => contenedor.innerHTML = html)
        .catch(() => contenedor.innerHTML = '<div class="alert alert-danger">❌ No se pudo cargar la información de contactos.</div>');
}


function actualizarReloj() {
    const ahora = new Date();
    const horaBolivia = new Date(ahora.getTime() - (4 * 60 * 60 * 1000) + (ahora.getTimezoneOffset() * 60000));

    const horas = horaBolivia.getHours().toString().padStart(2, '0');
    const minutos = horaBolivia.getMinutes().toString().padStart(2, '0');

    document.getElementById('reloj-bolivia').textContent = ` ${horas}:${minutos}`;
}

// Actualiza cada minuto (60000 ms)
setInterval(actualizarReloj, 60000);
actualizarReloj();

recargarSuavemente(10); // 8 segundos antes de recargar
</script>

</body>
</html>