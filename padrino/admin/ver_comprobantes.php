<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    http_response_code(403);
    echo "Acceso no autorizado.";
    exit;
}

if (!isset($_GET['id'])) {
    echo "<div class='vc-warning'>ID de sorteo no especificado.</div>";
    exit;
}

$id_sorteo = intval($_GET['id']);

$sorteo_stmt = $conn->prepare("SELECT * FROM sorteos WHERE id = ?");
$sorteo_stmt->bind_param('i', $id_sorteo);
$sorteo_stmt->execute();
$sorteo_result = $sorteo_stmt->get_result();
$sorteo = $sorteo_result->fetch_assoc();

if (!$sorteo) {
    echo "<div class='vc-warning'>Sorteo no encontrado.</div>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_participacion'])) {
    $id_participacion = intval($_POST['id_participacion']);
    $update_stmt = $conn->prepare("UPDATE participaciones SET estado = 'comprobado' WHERE id = ?");
    $update_stmt->bind_param('i', $id_participacion);
    $update_stmt->execute();
    header("Location: ver_comprobantes.php?id=$id_sorteo");
    exit;
}

$comprobantes_stmt = $conn->prepare("
    SELECT p.id AS id_participacion, p.comprobante_imagen, u.nombre
    FROM participaciones p
    JOIN usuarios u ON p.id_usuario = u.id
    WHERE p.id_sorteo = ? AND p.estado = 'pendiente' AND p.comprobante_imagen IS NOT NULL
");
$comprobantes_stmt->bind_param('i', $id_sorteo);
$comprobantes_stmt->execute();
$comprobantes = $comprobantes_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobantes - <?= htmlspecialchars($sorteo['titulo']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        
        body {
            background-color: #0f0f0f;
            color: #fdfdfd;
            font-family: 'Playfair Display', serif;
            margin: 0;
            padding: 0;
        }
        header {
            background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
            padding: 20px;
            text-align: center;
            border-bottom: 4px solid #00ffe7;
        }
        header h1 {
            font-size: 36px;
            margin: 0;
            color: #fff;
            text-shadow: 0 0 8px #00ffe7;
        }
        .container {
            padding: 30px;
            max-width: 1200px;
            margin: auto;
        }
        .vc-warning {
            background: #330000;
            padding: 15px;
            color: #ff6b6b;
            border: 2px solid #ff6b6b;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
        }
        .view-toggle {
            text-align: right;
            margin-bottom: 20px;
        }
        .view-toggle button {
            background: linear-gradient(45deg, #00ffe7, #00ffa3);
            color: #000;
            border: none;
            padding: 10px 16px;
            margin-left: 10px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .view-toggle button:hover {
            transform: scale(1.05);
            box-shadow: 0 0 10px #00ffc3;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        .card {
            background: #1a1a1a;
            border: 2px solid #6a11cb;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 0 15px rgba(106, 17, 203, 0.4);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: scale(1.03);
        }
        .card img {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 12px;
            cursor: zoom-in;
        }
        .usuario {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 12px;
            color: #00ffe7;
        }
        .confirmar-btn {
            background: linear-gradient(45deg, #00ffa3, #00ff6a);
            color: #000;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            width: 100%;
        }
        .confirmar-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 10px #00ff6a;
        }
        .list-table {
            width: 100%;
            border-collapse: collapse;
            color: #fff;
        }
        .list-table th, .list-table td {
            border: 1px solid #2575fc;
            padding: 10px;
            text-align: center;
        }
        .list-table th {
            background-color: #121212;
            color: #00bfff;
        }
        .list-table td img {
            max-width: 150px;
            border-radius: 8px;
            cursor: zoom-in;
        }
        @media(max-width: 768px) {
            .container {
                padding: 15px;
            }
            header h1 {
                font-size: 28px;
            }
            .list-table td img {
                max-width: 100%;
            }
        }
        .zoom-modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.95);
        }
        .zoom-content {
            margin: 5% auto;
            display: block;
            max-width: 90%;
            max-height: 80vh;
            border: 4px solid #6a11cb;
            border-radius: 10px;
        }
        .close-zoom {
            position: absolute;
            top: 30px;
            right: 50px;
            color: #00ffe7;
            font-size: 32px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
    <script>
        function toggleView(view) {
            const grid = document.getElementById("grid-view");
            const list = document.getElementById("list-view");
            if (view === 'grid') {
                grid.style.display = "grid";
                list.style.display = "none";
            } else {
                grid.style.display = "none";
                list.style.display = "block";
            }
        }

        function openZoom(src) {
            const modal = document.getElementById("zoom-modal");
            const zoomImg = document.getElementById("zoom-img");
            zoomImg.src = src;
            modal.style.display = "block";
        }

        function closeZoom() {
            document.getElementById("zoom-modal").style.display = "none";
        }

        window.onclick = function(event) {
            const modal = document.getElementById("zoom-modal");
            if (event.target === modal) {
                modal.style.display = "none";
            }
        }
    </script>
</head>
<body>

<header style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
    <h1 style="margin: 0;">🕵️‍♂️ Comprobantes - <?= htmlspecialchars($sorteo['titulo']) ?></h1>
    <a href="dashboard.php" style="
        display: inline-block;
        background: linear-gradient(45deg, #2575fc, #6a11cb);
        color: #fff;
        padding: 10px 18px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        box-shadow: 0 0 10px #6a11cb;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        margin-top: 10px;
    " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
        ⬅️ Volver al Dashboard
    </a>
</header>


<div class="container">

    <div class="view-toggle">
        
        Ver como:
        <button onclick="toggleView('grid')">🔲 Recuadros</button>
        <button onclick="toggleView('list')">📋 Lista</button>
        
    </div>
    
    <?php if ($comprobantes->num_rows > 0): ?>

        <div id="grid-view" class="grid">
            <?php
            $comprobantes->data_seek(0);
            while ($row = $comprobantes->fetch_assoc()):
            ?>
                <div class="card">
                    <div class="usuario">👤 <?= htmlspecialchars($row['nombre']) ?></div>
                    <img src="<?= htmlspecialchars($row['comprobante_imagen']) ?>" alt="Comprobante" onclick="openZoom(this.src)">
                    <form method="POST" onsubmit="return confirm('¿Confirmar esta participación?');">
                        <input type="hidden" name="id_participacion" value="<?= $row['id_participacion'] ?>">
                        <button type="submit" name="confirmar_participacion" class="confirmar-btn">✅ Confirmar</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>

        <div id="list-view" style="display: none;">
            <table class="list-table">
                <thead>
                    <tr>
                        <th>👤 Usuario</th>
                        <th>🧾 Comprobante</th>
                        <th>✅ Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $comprobantes->data_seek(0);
                    while ($row = $comprobantes->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td><img src="<?= htmlspecialchars($row['comprobante_imagen']) ?>" alt="Comprobante" onclick="openZoom(this.src)"></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('¿Confirmar esta participación?');">
                                <input type="hidden" name="id_participacion" value="<?= $row['id_participacion'] ?>">
                                <button type="submit" name="confirmar_participacion" class="confirmar-btn">Confirmar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <div class="vc-warning">🚫 No hay comprobantes pendientes para este sorteo.</div>
    <?php endif; ?>
</div>

<!-- Modal para zoom -->
<div id="zoom-modal" class="zoom-modal">
    <span class="close-zoom" onclick="closeZoom()">&times;</span>
    <img class="zoom-content" id="zoom-img">
</div>

</body>
</html>
