<?php
require_once '../config.php';

if (!isset($_GET['id_sorteo']) || !is_numeric($_GET['id_sorteo'])) {
    echo "<div class='text-warning text-center p-4'>ID inválido.</div>";
    exit;
}

$id_sorteo = intval($_GET['id_sorteo']);

$stmt = $conn->prepare("SELECT titulo FROM sorteos WHERE id = ?");
$stmt->bind_param("i", $id_sorteo);
$stmt->execute();
$sorteo = $stmt->get_result()->fetch_assoc();

if (!$sorteo) {
    echo "<div class='text-warning text-center p-4'>Sorteo no encontrado.</div>";
    exit;
}

$sql = "
    SELECT u.nombre, p.lugar, g.qr_pago_premio
    FROM ganadores g
    JOIN participaciones p ON p.id = g.id_participacion
    JOIN usuarios u ON u.id = p.id_usuario
    WHERE p.id_sorteo = ?
    ORDER BY FIELD(p.lugar, 'primer', 'segundo', 'tercer')
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_sorteo);
$stmt->execute();
$ganadores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Mostrar montos solo si hay premios establecidos
$montos = ['primer' => '50 Bs', 'segundo' => '30 Bs', 'tercer' => '20 Bs'];
?>

<style>
    .qr-img {
        max-width: 100px;
        border-radius: 6px;
        transition: transform 0.3s ease;
        cursor: pointer;
    }
    .qr-img:hover {
        transform: scale(1.1);
        box-shadow: 0 0 10px #f1c40f;
    }
    .ganador-card {
        background-color: #111;
        border: 1px solid #f1c40f22;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
        text-align: center;
    }
    .ganador-lugar {
        font-size: 1.1rem;
        font-weight: bold;
    }
    .ganador-nombre {
        font-size: 1.25rem;
        color: #f1c40f;
    }
</style>

<h5 class="text-warning text-center mb-4">🎯 Resultados del Sorteo: <?= htmlspecialchars($sorteo['titulo']) ?></h5>

<?php if (count($ganadores) === 0): ?>
    <div class="alert alert-warning text-center">No hay ganadores registrados aún.</div>
<?php else: ?>
    <div class="row">
        <?php foreach ($ganadores as $g): ?>
            <div class="col-md-4">
                <div class="ganador-card">
                    <div class="ganador-lugar">
                        <?= $g['lugar'] === 'primer' ? '🥇 Primer Lugar' : ($g['lugar'] === 'segundo' ? '🥈 Segundo Lugar' : ($g['lugar'] === 'tercer' ? '🥉 Tercer Lugar' : '🏅')) ?>
                    </div>
                    <div class="ganador-nombre"><?= htmlspecialchars($g['nombre']) ?></div>
                    <div class="text-success mt-2 mb-1"><?= $montos[$g['lugar']] ?? '-' ?></div>
                    <?php if ($g['qr_pago_premio']): ?>
                        <a href="../<?= htmlspecialchars($g['qr_pago_premio']) ?>" target="_blank" rel="noopener noreferrer">
                            <img src="../<?= htmlspecialchars($g['qr_pago_premio']) ?>" class="qr-img mt-2" alt="QR de pago">
                        </a>
                    <?php else: ?>
                        <div class="text-danger fw-bold mt-2">QR pendiente</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
