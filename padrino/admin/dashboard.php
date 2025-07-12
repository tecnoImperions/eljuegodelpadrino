<?php 
session_start();
require_once '../config.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$sql = "
    SELECT 
        s.*,
        (
            SELECT COALESCE(SUM(p.cantidad_boletos), 0)
            FROM participaciones p 
            WHERE p.id_sorteo = s.id
        ) AS total_boletos_vendidos
    FROM sorteos s
    ORDER BY 
        CASE s.plan 
            WHEN 'gratuito' THEN 1
            WHEN 'plus' THEN 2
            WHEN 'pro' THEN 3
            ELSE 4
        END, s.id ASC
";


$result = $conn->query($sql);
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

$mensaje = '';
if (isset($_GET['msg']) && $_GET['msg'] === 'reiniciado' && isset($_GET['id'])) {
    $sorteo_id = intval($_GET['id']);
    $mensaje = "✅ Sorteo #$sorteo_id reiniciado correctamente.";
}

// Datos para tarjetas resumen
$sqlResumen = "SELECT 
    COUNT(*) AS total_sorteos, 
    SUM(max_participantes) AS max_participantes_totales,
    SUM((SELECT COUNT(*) FROM participaciones p WHERE p.id_sorteo = s.id)) AS total_participantes
FROM sorteos s";
$resumen = $conn->query($sqlResumen)->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Panel Admin - El Padrino</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="css/d.css" />
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm border-bottom border-warning">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-warning" href="#"><i class="bi bi-shield-lock"></i> Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavIcons" aria-controls="navbarNavIcons" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavIcons">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link active text-warning" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="usuarios.php"><i class="bi bi-people"></i> Usuarios</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="trabajadores.php"><i class="bi bi-clipboard-data"></i> Trabajadores</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="configuracion.php"><i class="bi bi-gear"></i> Configuración</a></li>
            </ul>
            <a href="../logout.php" class="btn btn-outline-warning"><i class="bi bi-box-arrow-right"></i> Salir</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="admin-header">
        <h2>Panel de Administración</h2>
        <div id="reloj" aria-label="Reloj digital"></div>
        
    </div>

    <?php if ($mensaje): ?>
    <div class="alert alert-success" role="alert" aria-live="polite">
        <?= $mensaje ?>
    </div>
    <?php endif; ?>

    <!-- Tarjetas resumen -->
    <div class="resumen-cards" role="region" aria-label="Resumen rápido">
        <div class="card-resumen" tabindex="0" aria-describedby="desc-total-sorteos">
            <h3><?= $resumen['total_sorteos'] ?: 0 ?></h3>
            <p id="desc-total-sorteos"><i class="bi bi-card-list"></i> Sorteos Totales</p>
        </div>
        <div class="card-resumen" tabindex="0" aria-describedby="desc-max-participantes">
            <h3><?= $resumen['max_participantes_totales'] ?: 0 ?></h3>
            <p id="desc-max-participantes"><i class="bi bi-people-fill"></i> Máx. Participantes</p>
        </div>
        <div class="card-resumen" tabindex="0" aria-describedby="desc-total-participantes">
            <h3><?= $resumen['total_participantes'] ?: 0 ?></h3>
            <p id="desc-total-participantes"><i class="bi bi-person-check-fill"></i> Participantes Actuales</p>
        </div>
    </div>

    <!-- Tabla sorteos -->
    <div class="table-responsive" role="region" aria-label="Lista de sorteos">
    <table class="table table-dark table-bordered" id="sorteosTable" aria-describedby="tabla-sorteos-descripcion">
        <caption id="tabla-sorteos-descripcion" class="visually-hidden">Tabla con información de sorteos disponibles</caption>
        <thead class="filter-row">
            <tr>
                <td colspan="8" style="padding: 1rem;">
                    <button class="btn btn-sm filter-btn active" data-filter="todos" aria-pressed="true">Todos</button>
                    <button class="btn btn-sm filter-btn" data-filter="gratuito" aria-pressed="false">Gratuito</button>
                    <button class="btn btn-sm filter-btn" data-filter="plus" aria-pressed="false">Plus</button>
                    <button class="btn btn-sm filter-btn" data-filter="pro" aria-pressed="false">Pro</button>
                </td>
            </tr>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Plan</th>
                <th>Máx. Boletos</th>
                <th>Boletos Vendidos</th>
                <th>Boletos Restantes</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($sorteo = $result->fetch_assoc()):
                $max = $sorteo['max_participantes'];
                $vendidos = $sorteo['total_boletos_vendidos'] ?? 0;
                $restantes = max(0, $max - $vendidos);
                $disabled = ($restantes <= 0) || ($sorteo['estado'] === 'inactivo');
            ?>

            <tr data-plan="<?= htmlspecialchars(strtolower($sorteo['plan'])) ?>" class="<?= $disabled ? 'disabled' : '' ?>">
                <td><?= $sorteo['id'] ?></td>
                <td><?= htmlspecialchars($sorteo['titulo']) ?></td>
                <td><?= ucfirst(htmlspecialchars($sorteo['plan'])) ?></td>
                <td><?= $max ?></td>
                <td><?= $vendidos ?></td>
                <td><span class="badge bg-<?= $restantes > 0 ? 'success' : 'danger' ?>"><?= $restantes ?></span></td>
                <td><?= ucfirst(htmlspecialchars($sorteo['estado'])) ?></td>
                <td>
                    <a href="ver_comprobantes.php?id=<?= $sorteo['id'] ?>" class="btn btn-sm btn-warning" title="Ver Comprobantes" aria-label="Ver comprobantes sorteo #<?= $sorteo['id'] ?>"><i class="bi bi-file-earmark-text"></i></a>

                    <a href="resultados.php?id=<?= $sorteo['id'] ?>" class="btn btn-sm btn-info" title="Ver Resultados" aria-label="Ver resultados sorteo #<?= $sorteo['id'] ?>">
                        <i class="bi bi-trophy"></i>
                    </a>

                    <a href="reiniciar_sorteo.php?id=<?= $sorteo['id'] ?>" class="btn btn-sm btn-danger" title="Reiniciar Sorteo" aria-label="Reiniciar sorteo #<?= $sorteo['id'] ?>" onclick="return confirm('¿Estás seguro de reiniciar el sorteo #<?= $sorteo['id'] ?>? Esta acción eliminará todos los datos asociados.')">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Modal resultados -->
<div class="modal fade" id="modalResultados" tabindex="-1" aria-labelledby="modalResultadosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-warning">
                <h5 class="modal-title" id="modalResultadosLabel"><i class="bi bi-target"></i> Resultados del Sorteo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="resultadosContent">
                <div class="text-center">Cargando resultados...</div>
            </div>
            <div class="modal-footer border-warning">
                <button type="button" class="btn btn-outline-warning" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- Scripts -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

    // Actualiza el reloj cada segundo
    function actualizarReloj() {
        const reloj = document.getElementById('reloj');
        if (!reloj) return;
        const ahora = new Date();
        const horas = ahora.getHours().toString().padStart(2, '0');
        const minutos = ahora.getMinutes().toString().padStart(2, '0');
        const segundos = ahora.getSeconds().toString().padStart(2, '0');
        reloj.textContent = `${horas}:${minutos}:${segundos}`;
    }
    setInterval(actualizarReloj, 1000);
    actualizarReloj();

    // Filtros tabla
    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const filtro = btn.getAttribute('data-filter');

            // Cambiar estado aria-pressed para accesibilidad
            filterBtns.forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-pressed', 'false');
            });
            btn.classList.add('active');
            btn.setAttribute('aria-pressed', 'true');

            document.querySelectorAll('#sorteosTable tbody tr').forEach(row => {
                const plan = row.getAttribute('data-plan');
                row.style.display = (filtro === 'todos' || filtro === plan) ? '' : 'none';
            });
        });
    });

    // Modal resultados
    document.querySelectorAll('.ver-resultados').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            const id = btn.dataset.id;
            const modalContent = document.getElementById('resultadosContent');
            modalContent.innerHTML = '<div class="text-center">Cargando resultados...</div>';

            fetch('resultados_modal.php?id_sorteo=' + id)
                .then(res => res.text())
                .then(data => {
                    modalContent.innerHTML = data;
                    const modal = new bootstrap.Modal(document.getElementById('modalResultados'));
                    modal.show();
                })
                .catch(() => {
                    modalContent.innerHTML = '<div class="alert alert-danger">❌ Error al cargar los resultados.</div>';
                });
        });
    });

    
    function recargarSuavemente(segundos = 10) {
    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.top = 0;
    overlay.style.left = 0;
    overlay.style.width = '100vw';
    overlay.style.height = '100vh';
    overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.05)';
    overlay.style.opacity = '0';
    overlay.style.transition = 'opacity 0.2s ease';
    overlay.style.zIndex = 9999;
    document.body.appendChild(overlay);

    setTimeout(() => {
      overlay.style.opacity = '1';
      setTimeout(() => {
        location.reload();
      }, 200);
    }, segundos * 1000);
  }
</script>

</body>
</html>
