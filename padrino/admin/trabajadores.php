<?php 
session_start();
require_once '../config.php';

// Seguridad: solo admin puede entrar
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$mensaje = '';

// Función para mostrar mensajes con estilos y iconos
function mostrarMensaje($tipo, $texto) {
    $iconos = [
        'success' => 'bi-check-circle-fill',
        'danger' => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-diamond-fill',
    ];
    $icono = $iconos[$tipo] ?? 'bi-info-circle-fill';

    return "<div class='alert alert-$tipo d-flex align-items-center' role='alert'>
                <i class='bi $icono me-2'></i> $texto
            </div>";
}

// Procesar asignación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['sorteo_id'], $_POST['trabajador_id'])) {
        $sorteo_id = intval($_POST['sorteo_id']);
        $trabajador_id = intval($_POST['trabajador_id']);

        $sqlCheck = "SELECT 1 FROM trabajadores WHERE id_usuario = ? AND id_sorteo = ?";
        $stmt = $conn->prepare($sqlCheck);
        $stmt->bind_param("ii", $trabajador_id, $sorteo_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $sqlInsert = "INSERT INTO trabajadores (id_usuario, id_sorteo, estado) VALUES (?, ?, 'activo')";
            $stmt = $conn->prepare($sqlInsert);
            $stmt->bind_param("ii", $trabajador_id, $sorteo_id);
            $mensaje = $stmt->execute()
                ? mostrarMensaje('success', '✅ El trabajador fue asignado al sorteo con éxito.')
                : mostrarMensaje('danger', '❌ Error al hacer la asignación.');
        } else {
            $mensaje = mostrarMensaje('warning', '⚠️ Este trabajador ya está asignado a ese sorteo.');
        }
    } elseif (isset($_POST['eliminar_asignacion'])) {
        $trabajador_id = intval($_POST['id_usuario']);
        $sorteo_id = intval($_POST['id_sorteo']);

        $sqlDelete = "DELETE FROM trabajadores WHERE id_usuario = ? AND id_sorteo = ?";
        $stmt = $conn->prepare($sqlDelete);
        $stmt->bind_param("ii", $trabajador_id, $sorteo_id);
        $mensaje = $stmt->execute()
            ? mostrarMensaje('success', '🗑️ Asignación eliminada correctamente.')
            : mostrarMensaje('danger', '❌ No se pudo eliminar la asignación.');
    }
}

// Obtener sorteos y trabajadores
$sorteos = $conn->query("SELECT id, titulo FROM sorteos WHERE estado = 'activo' ORDER BY titulo ASC");
$trabajadores = $conn->query("SELECT id, nombre FROM usuarios WHERE rol = 'trabajador' ORDER BY nombre ASC");

$asignaciones_sql = "
    SELECT t.id_sorteo, s.titulo as sorteo, t.id_usuario, u.nombre as trabajador, t.estado
    FROM trabajadores t
    JOIN usuarios u ON t.id_usuario = u.id
    JOIN sorteos s ON t.id_sorteo = s.id
    ORDER BY s.titulo ASC, u.nombre ASC
";
$asignaciones = $conn->query($asignaciones_sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Asignar Trabajador a Sorteo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
               body {
            background-color: #0a0a0a;
            color: #ffd54f;
            font-family: 'Georgia', serif;
            min-height: 100vh;
            padding: 2rem 1rem;
        }
        .container {
            max-width: 600px;
            margin: 0 auto 3rem;
            background-color: #181818;
            border-radius: 15px;
            padding: 2.5rem 2rem;
            box-shadow: 0 0 30px #ffca28cc;
            border: 2px solid #ffb300;
        }
        h2 {
            font-weight: 900;
            text-align: center;
            margin-bottom: 2rem;
            color: #ffd600;
            text-shadow: 2px 2px 6px #b28704;
        }
        label {
            font-weight: 700;
            color: #ffca28;
        }
        select.form-select {
            background-color: #222222;
            color: #ffd54f;
            border: 2px solid #ffb300;
            transition: all 0.3s ease;
            box-shadow: inset 0 0 6px #ffca28;
        }
        select.form-select:focus {
            outline: none;
            border-color: #fff176;
            box-shadow: 0 0 12px #fff176;
            background-color: #333333;
            color: #fff9c4;
        }
        .btn-primary {
            background-color: #ffb300;
            border-color: #fbc02d;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            box-shadow: 0 0 15px #ffca28;
            transition: background-color 0.3s ease, color 0.3s ease;
            color: #0a0a0a;
        }
        .btn-primary:hover {
            background-color: #ffd54f;
            border-color: #f9a825;
            color: #1a1a1a;
        }
        .alert {
            font-weight: 600;
            font-size: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            box-shadow: 0 0 15px #ffca28cc;
            background-color: #332b00;
            color: #fff8e1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background: #181818;
            color: #ffd54f;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 0 20px #ffca28cc;
            border: 1.5px solid #ffb300;
        }
        thead {
            background-color: #ffb300;
        }
        thead th {
            padding: 12px 15px;
            font-weight: 900;
            text-transform: uppercase;
            border-bottom: 3px solid #ffa000;
            color: #1a1a1a;
        }
        tbody tr:nth-child(even) {
            background-color: #2a2a2a;
        }
        tbody td {
            padding: 10px 15px;
            border-bottom: 1px solid #ffb30055;
        }
        tbody tr:hover {
            background-color: #fff17622;
        }
        .estado-activo {
            color: #66bb6a;
            font-weight: 700;
            text-shadow: 0 0 4px #388e3c;
        }
        .estado-inactivo {
            color: #e53935;
            font-weight: 700;
            text-shadow: 0 0 4px #b71c1c;
        }
    </style>
</head>
<body>
<div class="container">
    <h2><i class="bi bi-person-badge-fill me-2"></i>Asignar Trabajador a Sorteo</h2>

    <?= $mensaje ?>

    <form method="POST">
        <div class="mb-3">
            <label for="sorteo_id">Seleccione Sorteo</label>
            <select id="sorteo_id" name="sorteo_id" class="form-select" required>
                <option value="" disabled selected>Elige un sorteo...</option>
                <?php while ($s = $sorteos->fetch_assoc()): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['titulo']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-4">
            <label for="trabajador_id">Seleccione Trabajador</label>
            <select id="trabajador_id" name="trabajador_id" class="form-select" required>
                <option value="" disabled selected>Elige un trabajador...</option>
                <?php while ($t = $trabajadores->fetch_assoc()): ?>
                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-person-plus-fill me-2"></i>Asignar Trabajador
        </button>
    </form>

    <?php if ($asignaciones && $asignaciones->num_rows > 0): ?>
        <h3 class="mt-5">Trabajadores Asignados</h3>
        <table class="table table-bordered table-dark table-hover mt-3">
            <thead>
                <tr>
                    <th>Sorteo</th>
                    <th>Trabajador</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($a = $asignaciones->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['sorteo']) ?></td>
                        <td><?= htmlspecialchars($a['trabajador']) ?></td>
                        <td class="<?= $a['estado'] === 'activo' ? 'estado-activo' : 'estado-inactivo' ?>">
                            <?= ucfirst($a['estado']) ?>
                        </td>
                        <td>
                            <form method="POST" class="eliminar-asignacion" onsubmit="return confirm('¿Seguro que deseas eliminar esta asignación?');">
                                <input type="hidden" name="eliminar_asignacion" value="1" />
                                <input type="hidden" name="id_sorteo" value="<?= $a['id_sorteo'] ?>" />
                                <input type="hidden" name="id_usuario" value="<?= $a['id_usuario'] ?>" />
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash-fill"></i> Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="dashboard.php" class="btn btn-outline-warning">
            <i class="bi bi-arrow-left-circle me-2"></i>Volver al Dashboard
        </a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
