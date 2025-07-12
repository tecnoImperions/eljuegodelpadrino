<?php  
session_start();
include '../config.php';

if (!isset($_SESSION['usuario'])) {
    echo '<div class="alert alert-danger text-center">No autenticado.</div>';
    exit();
}

$usuario_id = $_SESSION['usuario']['id'];

// Obtener sorteos donde el usuario participó
$sql = "
    SELECT 
        s.id, s.titulo, s.precio_entrada, s.max_participantes, s.estado,
        SUM(p.cantidad_boletos) AS boletos_comprados
    FROM sorteos s
    INNER JOIN participaciones p ON s.id = p.id_sorteo
    WHERE p.id_usuario = ?
    GROUP BY s.id
    ORDER BY s.fecha_cierre ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

// ✅ Nueva prioridad con "ganador"
function estadoPrioridad(array $estados) {
    $prioridad = [
        'rechazado'  => 0,
        'pendiente'  => 1,
        'comprobado' => 2,
        'ganador'    => 3
    ];
    $maxEstado = 'rechazado';
    foreach ($estados as $estado) {
        $estado = strtolower($estado);
        if (isset($prioridad[$estado]) && $prioridad[$estado] > $prioridad[$maxEstado]) {
            $maxEstado = $estado;
        }
    }
    return $maxEstado;
}
?>

<div class="container mt-4">
    <?php if ($resultado->num_rows === 0): ?>
        <div class="alert alert-warning text-center fw-bold">
            ❗ No estás participando en ningún sorteo aún.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php while ($row = $resultado->fetch_assoc()):
                // Traer los estados de participación del usuario en este sorteo
                $sql_estados = "SELECT estado FROM participaciones WHERE id_usuario = ? AND id_sorteo = ?";
                $stmt_est = $conn->prepare($sql_estados);
                $stmt_est->bind_param("ii", $usuario_id, $row['id']);
                $stmt_est->execute();
                $res_est = $stmt_est->get_result();
                
                $estados_participacion = [];
                while ($e = $res_est->fetch_assoc()) {
                    $estados_participacion[] = $e['estado'];
                }
                $estado = estadoPrioridad($estados_participacion);

                // Colores y texto según estado
                $color = 'secondary';
                $texto_estado = 'Desconocido';
                switch ($estado) {
                    case 'pendiente':
                        $color = 'warning'; $texto_estado = 'Pendiente ⏳'; break;
                    case 'comprobado':
                        $color = 'success'; $texto_estado = 'Comprobado ✅'; break;
                    case 'rechazado':
                        $color = 'danger'; $texto_estado = 'Rechazado ❌'; break;
                    case 'ganador':
                        $color = 'info'; $texto_estado = 'Ganador 🏆'; break;
                }

                $card_border = $row['estado'] === 'activo' ? 'border-warning' : 'border-dark';
                $bg_gradient = 'bg-dark';
                $boletos = (int)($row['boletos_comprados'] ?? 0);
                $total_pagado = $row['precio_entrada'] * $boletos;
            ?>
            <div class="col-12">
                <div class="card text-white <?= $bg_gradient ?> shadow-lg <?= $card_border ?> border-3 rounded-4">
                    <div class="card-body">
                        <h5 class="card-title text-warning fw-bold mb-2">
                            🎯 <?= htmlspecialchars($row['titulo']) ?>
                        </h5>

                        <p class="mb-1">
                            <strong>💰 Total Pagado:</strong> 
                            <?= number_format($total_pagado, 2) ?> Bs 
                            <span class="text-muted small">(<?= $boletos ?> boletos a <?= number_format($row['precio_entrada'], 2) ?> Bs)</span>
                        </p>
                        <p class="mb-3">
                            <strong>📋 Estado:</strong> 
                            <span class="badge bg-<?= $color ?> fs-6"><?= $texto_estado ?></span>
                        </p>

                        <div>
                            <?php if ($estado === 'comprobado' || $estado === 'ganador'): ?>
                                <form action="concurso.php" method="GET" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-warning fw-bold">
                                        📊 Ver Resultados
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-outline-light" disabled title="Pago no confirmado o no ganador">
                                    ⛔ Ver Resultados
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>
