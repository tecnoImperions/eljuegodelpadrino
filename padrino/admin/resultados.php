<?php 
session_start();
require_once '../config.php';

if (!isset($_SESSION['usuario'])) {
    echo "⚠️ Debes iniciar sesión para acceder a los resultados.";
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "❌ ID de sorteo no válido.";
    exit;
}

$id_sorteo = intval($_GET['id']);

$stmt = $conn->prepare("SELECT titulo FROM sorteos WHERE id = ?");
$stmt->bind_param("i", $id_sorteo);
$stmt->execute();
$sorteo = $stmt->get_result()->fetch_assoc();

if (!$sorteo) {
    echo "❌ El sorteo no existe.";
    exit;
}

$sql = "
    SELECT p.id AS id_participacion, u.nombre, p.lugar, g.qr_pago_premio, g.estado_pago
    FROM participaciones p
    JOIN usuarios u ON u.id = p.id_usuario
    JOIN ganadores g ON g.id_participacion = p.id
    WHERE p.id_sorteo = ? AND p.estado = 'ganador'
    ORDER BY FIELD(p.lugar, 'primer', 'segundo', 'tercer', 'unico', 'ambos')
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_sorteo);
$stmt->execute();
$ganadores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

function obtenerMonto($lugar) {
    return match ($lugar) {
        'primer' => 50,
        'segundo' => 30,
        'tercer' => 20,
        'unico' => 100,
        'ambos' => 50,
        default => 0,
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados - <?= htmlspecialchars($sorteo['titulo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #f8f8f8;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding-top: 80px;
        }
        .navbar {
            background-color: #1a1a1a;
            box-shadow: 0 2px 10px rgba(0,0,0,0.6);
        }
        .navbar-brand {
            font-weight: bold;
            color: #ffd700 !important;
            font-size: 1.2rem;
        }
        .nav-link {
            color: #fff !important;
        }
        .contenedor {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .ganador {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(8px);
            border: -1px solid rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 20px 20px 40px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            width: 350px;
            min-height: 480px;
        }

        .ganador h3 {
            color: #f1c40f;
            margin-bottom: 10px;
        }
        .ganador img {
            max-width: 100%;
            border-radius: 10px;
            margin-top: 10px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .ganador img:hover {
            transform: scale(1.05);
        }
        .estado, .pago {
            margin-top: 12px;
        }
        .estado span, .pago span {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 5px;
            font-weight: bold;
        }
        .pendiente {
            background-color: #e67e22;
            color: #fff;
        }
        .pagado {
            background-color: #2ecc71;
            color: #fff;
        }
        .pago span {
            background-color: #2980b9;
            color: #fff;
        }
        .btn-pagar {
            margin-top: 10px;
            padding: 10px 16px;
            font-size: 0.9rem;
            border: none;
            border-radius: 6px;
            background-color: #27ae60;
            color: white;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn-pagar:hover {
            background-color: #1e8449;
        }
        #modalImagen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        #modalImagen img {
            max-width: 90%;
            max-height: 85%;
            border-radius: 12px;
            box-shadow: 0 0 30px rgba(255,255,255,0.3);
            margin-top: 50px; /* baja la imagen */
        }

        #modalImagen .cerrar {
            position: absolute;
            top: 70px;
            right: 30px;
            font-size: 2rem;
            color: #fff;
            cursor: pointer;
        }
        .titulo-sorteo {
            text-align: center;
            margin: 40px auto 20px;
            color: #ffdd57;
            font-size: 2rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top shadow" style="background: linear-gradient(90deg, #0f2027, #203a43);">
    <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
        <a class="navbar-brand d-flex align-items-center text-warning fw-bold" href="#">
            <i class="bi bi-award-fill me-2 fs-4"></i> Sorteos Admin
        </a>
        <a href="dashboard.php" class="btn btn-outline-warning rounded-pill px-3">
            <i class="bi bi-arrow-left-circle me-1"></i> Volver
        </a>
    </div>
</nav>


<h1 class="titulo-sorteo">🏆 Resultados del sorteo: <?= htmlspecialchars($sorteo['titulo']) ?></h1>

<?php if (count($ganadores) === 0): ?>
    <p class="text-center mt-5">⏳ Aún no hay ganadores registrados para este sorteo.</p>
<?php else: ?>
    <div class="contenedor">
        <?php foreach ($ganadores as $g): ?>
            <div class="ganador">
                <h3><?= strtoupper($g['lugar']) ?> lugar - <?= htmlspecialchars($g['nombre']) ?></h3>
                <?php if (!empty($g['qr_pago_premio'])): ?>
                    <img src="../<?= htmlspecialchars($g['qr_pago_premio']) ?>" alt="QR del ganador">
                <?php else: ?>
                    <p>📭 QR aún no enviado</p>
                <?php endif; ?>
                <div class="estado">
                    Estado de pago:
                    <span class="<?= $g['estado_pago'] === 'pagado' ? 'pagado' : 'pendiente' ?>">
                        <?= $g['estado_pago'] === 'pagado' ? 'Pagado' : 'Pendiente' ?>
                    </span>
                </div>
                <div class="pago">
                    Debe recibir: <span><?= obtenerMonto($g['lugar']) ?> Bs</span>
                </div>
                <?php if ($g['estado_pago'] !== 'pagado'): ?>
                    <form action="marcar_pagado.php?id=<?= $id_sorteo ?>" method="post" onsubmit="return confirm('¿Estás seguro de marcar como pagado?');">
                        <input type="hidden" name="id_participacion" value="<?= $g['id_participacion'] ?>">
                        <button type="submit" class="btn-pagar">✅ Marcar como Pagado</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal Imagen -->
<div id="modalImagen">
    <span class="cerrar" onclick="cerrarModal()">✖</span>
    <img id="imagenAmpliada" src="" alt="Imagen ampliada">
</div>

<script>
    document.querySelectorAll('.ganador img').forEach(img => {
        img.addEventListener('click', function() {
            document.getElementById('imagenAmpliada').src = this.src;
            document.getElementById('modalImagen').style.display = 'flex';
        });
    });
    function cerrarModal() {
        document.getElementById('modalImagen').style.display = 'none';
    }
    document.getElementById('modalImagen').addEventListener('click', function(e) {
        if (e.target === this) cerrarModal();
    });
</script>

</body>
</html>
