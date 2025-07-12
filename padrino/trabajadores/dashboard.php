<?php 
session_start();
require_once '../config.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'trabajador') {
    header("Location: ../login.php");
    exit;
}

$trabajador_id = $_SESSION['usuario']['id'];

// Función para responder JSON y terminar ejecución
function respondJSON($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// --- LÓGICA AJAX ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    // Validar sesión y rol antes de cualquier acción ajax
    if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'trabajador') {
        respondJSON(['success' => false, 'error' => 'No autorizado']);
    }

    $accion = $_POST['accion'];

    // Función común para validar participación y que pertenece al trabajador
    function participacionValida(mysqli $conn, int $participacion_id, int $usuario_id): bool {
        $sqlCheck = "
            SELECT p.id 
            FROM participaciones p
            INNER JOIN sorteos s ON s.id = p.id_sorteo
            INNER JOIN trabajadores t ON t.id_sorteo = s.id
            WHERE p.id = ? AND t.id_usuario = ? AND t.estado = 'activo'
            LIMIT 1
        ";
        $stmtCheck = $conn->prepare($sqlCheck);
        if (!$stmtCheck) return false;
        $stmtCheck->bind_param('ii', $participacion_id, $usuario_id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        return $resultCheck->num_rows > 0;
    }

    if ($accion === 'actualizar_estado') {
        $participacion_id = filter_input(INPUT_POST, 'participacion_id', FILTER_VALIDATE_INT);
        $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);
        $estados_validos = ['pendiente', 'comprobado', 'rechazado'];

        if (!$participacion_id || !$estado || !in_array($estado, $estados_validos, true)) {
            respondJSON(['success' => false, 'error' => 'Datos inválidos']);
        }
        if (!participacionValida($conn, $participacion_id, $_SESSION['usuario']['id'])) {
            respondJSON(['success' => false, 'error' => 'Participación inválida o no autorizada']);
        }

        $sqlUpdate = "UPDATE participaciones SET estado = ? WHERE id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        if (!$stmtUpdate) {
            respondJSON(['success' => false, 'error' => 'Error en la consulta']);
        }
        $stmtUpdate->bind_param('si', $estado, $participacion_id);
        if ($stmtUpdate->execute()) {
            respondJSON(['success' => true, 'nuevo_estado' => $estado]);
        } else {
            respondJSON(['success' => false, 'error' => 'Error al actualizar estado']);
        }
    }
    elseif ($accion === 'quitar_participacion') {
        $participacion_id = filter_input(INPUT_POST, 'participacion_id', FILTER_VALIDATE_INT);

        if (!$participacion_id) {
            respondJSON(['success' => false, 'error' => 'ID inválido']);
        }
        if (!participacionValida($conn, $participacion_id, $_SESSION['usuario']['id'])) {
            respondJSON(['success' => false, 'error' => 'Participación inválida o no autorizada']);
        }

        $sqlDelete = "DELETE FROM participaciones WHERE id = ?";
        $stmtDelete = $conn->prepare($sqlDelete);
        if (!$stmtDelete) {
            respondJSON(['success' => false, 'error' => 'Error en la consulta']);
        }
        $stmtDelete->bind_param('i', $participacion_id);
        if ($stmtDelete->execute()) {
            respondJSON(['success' => true]);
        } else {
            respondJSON(['success' => false, 'error' => 'Error al eliminar participación']);
        }
    }
    else {
        respondJSON(['success' => false, 'error' => 'Acción no válida']);
    }
}

// --- FIN LÓGICA AJAX ---

// Carga datos para mostrar en la página (igual que antes)
$stmt = $conn->prepare("
    SELECT s.* FROM trabajadores t
    JOIN sorteos s ON s.id = t.id_sorteo
    WHERE t.id_usuario = ? AND t.estado = 'activo'
    LIMIT 1
");
$stmt->bind_param("i", $trabajador_id);
$stmt->execute();
$sorteo = $stmt->get_result()->fetch_assoc();

if (!$sorteo) {
    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Sin Sorteo Asignado</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background-color: #111;
                color: #fff;
                font-family: "Segoe UI", sans-serif;
                text-align: center;
                padding-top: 100px;
            }
            .mensaje {
                background-color: #222;
                padding: 40px;
                border-radius: 12px;
                max-width: 500px;
                margin: auto;
                box-shadow: 0 0 15px rgba(255, 204, 0, 0.3);
            }
            .btn-volver {
                margin-top: 30px;
            }
        </style>
    </head>
    <body>

    <div class="mensaje">
        <h2>❌ No tienes un sorteo asignado actualmente, capisce?</h2>
        <p>Por favor, contacta al administrador si crees que esto es un error.</p>
        <a href="../" class="btn btn-warning btn-lg btn-volver">🔙 Volver al Inicio</a>
        <a href="../logout.php" class="btn btn-outline-light btn-lg btn-volver ms-2">🚪 Cerrar Sesión</a>
    </div>

    </body>
    </html>';
    exit();

}

$stmt = $conn->prepare("
    SELECT p.*, u.nombre, u.correo, u.foto_perfil 
    FROM participaciones p
    JOIN usuarios u ON u.id = p.id_usuario
    WHERE p.id_sorteo = ?
    ORDER BY p.fecha_participacion DESC
");
$stmt->bind_param("i", $sorteo['id']);
$stmt->execute();
$participaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>👑 El Padrino - Panel del Trabajador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="trab.css" rel="stylesheet" />
</head>
<body>
<nav id="menu-lateral" role="navigation" aria-label="Menú lateral principal" class="bg-dark text-white p-3 vh-100">
    <h2>El Padrino</h2>
    <button class="btn btn-outline-light w-100 mb-2" onclick="location.href='../'" aria-label="Volver al inicio">
        <i class="bi bi-house-door-fill"></i> Inicio
    </button>
    <button class="btn btn-outline-light w-100" onclick="location.href='../logout.php'" aria-label="Cerrar sesión">
        <i class="bi bi-box-arrow-right"></i> Cerrar sesión
    </button>
</nav>

<div class="container my-4" role="main" aria-label="Panel del trabajador">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Panel del Trabajador</h1>
        <div id="reloj" aria-live="polite" aria-atomic="true" aria-label="Reloj digital" class="fs-4 text-monospace">--:--:--</div>
    </div>

    <section class="sorteo-info mb-4" aria-labelledby="titulo-sorteo">
        <h2 id="titulo-sorteo">📋 Sorteo Asignado</h2>
        <p><strong>Título:</strong> <?= htmlspecialchars($sorteo['titulo']) ?></p>
        <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($sorteo['descripcion'])) ?></p>
        <p><strong>Plan:</strong> <?= ucfirst($sorteo['plan']) ?> | <strong>Entrada:</strong> $<?= number_format($sorteo['precio_entrada'], 2) ?></p>
        <p><strong>Fechas:</strong> <?= htmlspecialchars($sorteo['fecha_inicio']) ?> → <?= htmlspecialchars($sorteo['fecha_cierre']) ?></p>
        <p><strong>Estado:</strong> <?= ucfirst(htmlspecialchars($sorteo['estado'])) ?></p>
    </section>

    <section class="participaciones" aria-labelledby="titulo-participaciones">
        <h2 id="titulo-participaciones">👥 Participaciones</h2>

        <?php if (count($participaciones) > 0): ?>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle" aria-describedby="desc-participaciones">
                    <caption id="desc-participaciones">Lista de usuarios participando en el sorteo asignado</caption>
                    <thead>
                        <tr>
                            <th scope="col">Usuario</th>
                            <th scope="col">Correo</th>
                            <th scope="col">Boletos</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Comprobante</th>
                            <th scope="col">Lugar</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-participaciones">
                    <?php foreach ($participaciones as $p): ?>
                        <tr id="fila-<?= (int)$p['id'] ?>">
                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                            <td><a href="mailto:<?= htmlspecialchars($p['correo']) ?>" class="link-warning"><?= htmlspecialchars($p['correo']) ?></a></td>
                            <td><?= (int)$p['cantidad_boletos'] ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo ($p['estado'] === 'comprobado') ? 'success' : (($p['estado'] === 'rechazado') ? 'danger' : 'secondary');
                                ?>" id="estado-badge-<?= (int)$p['id'] ?>">
                                    <?= ucfirst(htmlspecialchars($p['estado'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($p['comprobante_imagen']): ?>
                                    <a href="../uploads/<?= htmlspecialchars($p['comprobante_imagen']) ?>" target="_blank" rel="noopener" aria-label="Ver comprobante de <?= htmlspecialchars($p['nombre']) ?>">
                                        <img src="../uploads/<?= htmlspecialchars($p['comprobante_imagen']) ?>" alt="Comprobante de <?= htmlspecialchars($p['nombre']) ?>" style="max-width: 80px; max-height: 80px; object-fit: cover; border-radius: 4px;">
                                    </a>
                                <?php else: ?>
                                    <span>—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= ucfirst(htmlspecialchars($p['lugar'])) ?></td>
                            <td>
                                <form class="form-actualizar-estado d-inline" data-id="<?= (int)$p['id'] ?>" aria-label="Formulario para actualizar estado de <?= htmlspecialchars($p['nombre']) ?>">
                                    <select name="estado" class="form-select form-select-sm d-inline-block w-auto" aria-label="Seleccionar nuevo estado para <?= htmlspecialchars($p['nombre']) ?>">
                                        <option value="pendiente" <?= $p['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="comprobado" <?= $p['estado'] === 'comprobado' ? 'selected' : '' ?>>Comprobado</option>
                                        <option value="rechazado" <?= $p['estado'] === 'rechazado' ? 'selected' : '' ?>>Rechazado</option>
                                    </select>
                                    <button type="submit" class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Actualizar estado">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </button>
                                </form>

                                <form class="form-quitar-participacion d-inline ms-2" data-id="<?= (int)$p['id'] ?>" aria-label="Formulario para quitar participación de <?= htmlspecialchars($p['nombre']) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Quitar participación">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-warning text-center fs-5">🕵️ No hay participaciones registradas... todavía.</p>
        <?php endif; ?>
    </section>

    <footer class="footer mt-5 text-center text-muted">
        &copy; <?= date("Y") ?> El Padrino Sorteos. Todos los derechos reservados.
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar tooltips Bootstrap
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

    // Reloj digital
    function actualizarReloj() {
        const reloj = document.getElementById('reloj');
        const ahora = new Date();
        const hora = String(ahora.getHours()).padStart(2, '0');
        const minutos = String(ahora.getMinutes()).padStart(2, '0');
        const segundos = String(ahora.getSeconds()).padStart(2, '0');
        reloj.textContent = `${hora}:${minutos}:${segundos}`;
    }
    setInterval(actualizarReloj, 1000);
    actualizarReloj();

    // Manejar actualización estado
    document.querySelectorAll('.form-actualizar-estado').forEach(form => {
        form.addEventListener('submit', async e => {
            e.preventDefault();
            const id = form.dataset.id;
            const select = form.querySelector('select[name="estado"]');
            const nuevoEstado = select.value;

            if (!confirm(`¿Confirmas actualizar el estado de esta participación a "${nuevoEstado}"?`)) return;

            try {
                const formData = new FormData();
                formData.append('accion', 'actualizar_estado');
                formData.append('participacion_id', id);
                formData.append('estado', nuevoEstado);

                const res = await fetch('', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                });
                const data = await res.json();

                if (data.success) {
                    const badge = document.getElementById(`estado-badge-${id}`);
                    badge.textContent = nuevoEstado.charAt(0).toUpperCase() + nuevoEstado.slice(1);
                    badge.className = 'badge bg-' + (nuevoEstado === 'comprobado' ? 'success' : (nuevoEstado === 'rechazado' ? 'danger' : 'secondary'));
                    // Opcional: mostrar tooltip o alert pequeño
                    alert('Estado actualizado correctamente.');
                } else {
                    alert('Error: ' + (data.error || 'Error desconocido'));
                }
            } catch (error) {
                alert('Error al procesar la solicitud');
                console.error(error);
            }
        });
    });

    // Manejar quitar participación
    document.querySelectorAll('.form-quitar-participacion').forEach(form => {
        form.addEventListener('submit', async e => {
            e.preventDefault();
            const id = form.dataset.id;

            if (!confirm('¿Estás seguro de quitar esta participación? Esta acción es irreversible.')) return;

            try {
                const formData = new FormData();
                formData.append('accion', 'quitar_participacion');
                formData.append('participacion_id', id);

                const res = await fetch('', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                });
                const data = await res.json();

                if (data.success) {
                    // Quitar fila de la tabla
                    const fila = document.getElementById(`fila-${id}`);
                    if (fila) fila.remove();
                    alert('Participación eliminada correctamente.');
                } else {
                    alert('Error: ' + (data.error || 'Error desconocido'));
                }
            } catch (error) {
                alert('Error al procesar la solicitud');
                console.error(error);
            }
        });
    });
});
</script>

</body>
</html>
