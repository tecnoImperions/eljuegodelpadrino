<?php 
session_start();
require_once '../config.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION['usuario'];
$id_usuario = $user['id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$id_sorteo = intval($_GET['id']);

// Verificar participación
$stmt = $conn->prepare("SELECT id, estado FROM participaciones WHERE id_usuario = ? AND id_sorteo = ?");
$stmt->bind_param("ii", $id_usuario, $id_sorteo);
$stmt->execute();
$mi_participacion = $stmt->get_result()->fetch_assoc();

if (!$mi_participacion) {
    echo "<div style='color:red;text-align:center;margin-top:40px;'>🚫 No has participado en este sorteo.</div>";
    exit;
}

$estado_participacion = $mi_participacion['estado'];

// IMPORTANTE: Permitir acceso a usuarios con estado 'comprobado' O 'ganador'
if ($estado_participacion != 'comprobado' && $estado_participacion != 'ganador') {
    echo "<div style='color:orange;text-align:center;margin-top:40px;'>⏳ Tu participación aún no ha sido confirmada.</div>";
    exit;
}

// Obtener sorteo
$stmt = $conn->prepare("SELECT * FROM sorteos WHERE id = ?");
$stmt->bind_param("i", $id_sorteo);
$stmt->execute();
$sorteo = $stmt->get_result()->fetch_assoc();

if (!$sorteo) {
    header("Location: ../dashboard.php");
    exit;
}

// Participaciones confirmadas (comprobadas)
$stmt = $conn->prepare("SELECT id, id_usuario, cantidad_boletos FROM participaciones WHERE id_sorteo = ? AND estado IN ('comprobado', 'ganador')");
$stmt->bind_param("i", $id_sorteo);
$stmt->execute();
$participaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total_boletos = array_sum(array_column($participaciones, 'cantidad_boletos'));
$max_boletos = $sorteo['max_participantes']; // Cambiar a 25 en tu BD si quieres
$sorteo_completo = ($total_boletos >= $max_boletos);

// Verificar si ya se sorteó
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM ganadores g JOIN participaciones p ON g.id_participacion = p.id WHERE p.id_sorteo = ?");
$stmt->bind_param("i", $id_sorteo);
$stmt->execute();
$ya_sorteado = $stmt->get_result()->fetch_assoc()['total'] > 0;

// NUEVA LÓGICA: Ejecutar sorteo SOLO si está completo Y no se ha sorteado
if ($sorteo_completo && !$ya_sorteado) {
    // Bloquear para evitar ejecución simultánea
    $conn->begin_transaction();
    try {
        // Verificar una vez más dentro de la transacción
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM ganadores g JOIN participaciones p ON g.id_participacion = p.id WHERE p.id_sorteo = ? FOR UPDATE");
        $stmt->bind_param("i", $id_sorteo);
        $stmt->execute();
        $doble_verificacion = $stmt->get_result()->fetch_assoc()['total'] > 0;
        
        if (!$doble_verificacion) {
            // Crear pool solo de participaciones 'comprobado' (no ganadores aún)
            $stmt_pool = $conn->prepare("SELECT id, id_usuario, cantidad_boletos FROM participaciones WHERE id_sorteo = ? AND estado = 'comprobado'");
            $stmt_pool->bind_param("i", $id_sorteo);
            $stmt_pool->execute();
            $participaciones_sorteo = $stmt_pool->get_result()->fetch_all(MYSQLI_ASSOC);
            
            $pool = [];
            foreach ($participaciones_sorteo as $p) {
                $peso = ($p['cantidad_boletos'] > 5) ? $p['cantidad_boletos'] * 2 : $p['cantidad_boletos'];
                for ($j = 0; $j < $peso; $j++) {
                    $pool[] = $p['id'];
                }
            }
            
            if (count($pool) > 0) {
                shuffle($pool);
                $ganadores_unicos = array_unique($pool);
                $ganadores_escogidos = array_values($ganadores_unicos);
                
                $puestos = ['primer', 'segundo', 'tercer'];
                
                for ($i = 0; $i < min(3, count($ganadores_escogidos)); $i++) {
                    $pid = $ganadores_escogidos[$i];
                    $puesto = $puestos[$i];
                    
                    // Actualizar estado a 'ganador'
                    $stmt_update = $conn->prepare("UPDATE participaciones SET estado = 'ganador', lugar = ? WHERE id = ?");
                    $stmt_update->bind_param("si", $puesto, $pid);
                    $stmt_update->execute();
                    
                    // Insertar en tabla ganadores
                    $stmt_ganador = $conn->prepare("INSERT INTO ganadores (id_participacion) VALUES (?)");
                    $stmt_ganador->bind_param("i", $pid);
                    $stmt_ganador->execute();
                }
                
                error_log("Sorteo ID $id_sorteo ejecutado correctamente");
            }
        }
        
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error en sorteo ID $id_sorteo: " . $e->getMessage());
    }
}

// IMPORTANTE: Obtener ganadores DESPUÉS del sorteo (para todos los usuarios)
$stmt = $conn->prepare("
    SELECT u.nombre, u.id AS id_usuario, p.lugar, g.qr_pago_premio
    FROM participaciones p
    JOIN usuarios u ON u.id = p.id_usuario
    JOIN ganadores g ON g.id_participacion = p.id
    WHERE p.id_sorteo = ? AND p.estado = 'ganador'
    ORDER BY FIELD(p.lugar, 'primer','segundo','tercer')
");
$stmt->bind_param("i", $id_sorteo);
$stmt->execute();
$ganadores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Verificar si el usuario actual ganó
$usuario_gano = false;
$mi_lugar = null;
foreach ($ganadores as $ganador) {
    if ($ganador['id_usuario'] == $id_usuario) {
        $usuario_gano = true;
        $mi_lugar = $ganador['lugar'];
        break;
    }
}

$texto_lugar = ['primer' => '1er lugar', 'segundo' => '2do lugar', 'tercer' => '3er lugar'];

// Determinar tipo de ganador para mostrar mensaje apropiado
$total_ganadores = count($ganadores);
$tipo_ganador = '';
if ($total_ganadores == 1) {
    $tipo_ganador = 'unico';
} elseif ($total_ganadores == 2) {
    $tipo_ganador = 'ambos';
} else {
    $tipo_ganador = 'multiples';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sorteo: <?= htmlspecialchars($sorteo['titulo']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg,rgb(41, 54, 112) 0%,rgb(13, 7, 19) 100%);
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .stars {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .star {
            position: absolute;
            background: #fff;
            border-radius: 50%;
            animation: twinkle 2s infinite;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        .container {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 1s ease;
        }

        .header h1 {
            font-size: 3rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            background: linear-gradient(45deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 20px;
        }

        .progress-bar {
            background: rgba(255,255,255,0.2);
            border-radius: 25px;
            padding: 3px;
            margin: 20px auto;
            max-width: 400px;
        }

        .progress-fill {
            background: linear-gradient(90deg, #00d2ff, #3a7bd5);
            height: 20px;
            border-radius: 22px;
            transition: width 0.8s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .waiting-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            margin: 40px auto;
            max-width: 600px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .waiting-card .icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: spin 3s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .results-container {
            margin-top: 40px;
        }

        .winner-announcement {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            border-radius: 20px;
            animation: bounceIn 1s ease;
        }

        .winner-announcement.ganador-unico {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: 3px solid #ffd700;
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.5);
        }

        .winner-announcement.ganador-ambos {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            border: 3px solid #ff6b6b;
            box-shadow: 0 0 30px rgba(255, 107, 107, 0.5);
        }

        .winner-announcement.ganador-multiples {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            border: 3px solid #00d2ff;
            box-shadow: 0 0 30px rgba(0, 210, 255, 0.5);
        }

        .winner-announcement h2 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .mi-resultado {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            border: 3px solid #00ff88;
            box-shadow: 0 0 40px rgba(0, 255, 136, 0.6);
            animation: celebration 2s ease;
        }

        @keyframes celebration {
            0%, 100% { transform: scale(1); }
            25% { transform: scale(1.05) rotate(1deg); }
            75% { transform: scale(1.05) rotate(-1deg); }
        }

        .no-gane {
            background: linear-gradient(135deg, #fc466b, #3f5efb);
            border: 3px solid #ff6b9d;
            opacity: 0;
            animation: fadeIn 1s 0.5s forwards;
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        .ganadores-grid {
            display: grid;
            gap: 20px;
            margin-top: 30px;
        }

        .ganador-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 15px;
            padding: 25px;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
            position: relative;
            overflow: hidden;
        }

        .ganador-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s ease;
        }

        .ganador-card:hover::before {
            left: 100%;
        }

        .ganador-card.show {
            opacity: 1;
            transform: translateY(0);
        }

        .ganador-card.primer {
            border: 2px solid #ffd700;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.3);
        }

        .ganador-card.segundo {
            border: 2px solid #c0c0c0;
            box-shadow: 0 0 20px rgba(192, 192, 192, 0.3);
        }

        .ganador-card.tercer {
            border: 2px solid #cd7f32;
            box-shadow: 0 0 20px rgba(205, 127, 50, 0.3);
        }

        .medal {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .winner-name {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .winner-place {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 20px;
        }

        .qr-section {
            margin-top: 20px;
            padding: 20px;
            background: rgba(0,0,0,0.2);
            border-radius: 10px;
        }

        .qr-preview {
            max-width: 200px;
            border: 3px solid #ffd700;
            border-radius: 10px;
            margin: 15px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .upload-form {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 10px;
            margin-top: 15px;
        }

        .file-input {
            margin: 15px 0;
            padding: 10px;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            color: white;
            width: 100%;
        }

        .upload-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }

        .back-btn {
            display: inline-block;
            margin-top: 40px;
            padding: 15px 30px;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            z-index: 10;
            pointer-events: none;
        }

        @keyframes confetti-fall {
            0% {
                transform: translateY(-100vh) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(720deg);
                opacity: 0;
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3);
            }
            50% {
                opacity: 1;
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .container {
                padding: 15px;
            }
            
            .ganador-card {
                padding: 20px;
            }
            
            .medal {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="stars"></div>
    
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-trophy"></i> <?= htmlspecialchars($sorteo['titulo']) ?></h1>
            <p class="subtitle">Sorteo en vivo - ¡Buena suerte a todos!</p>
            
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= min(100, ($total_boletos / $max_boletos) * 100) ?>%">
                    <?= $total_boletos ?>/<?= $max_boletos ?> boletos
                </div>
            </div>
        </div>

        <?php if (!$sorteo_completo): ?>
            <div class="waiting-card">
                <div class="icon">⏳</div>
                <h2>Esperando más participantes...</h2>
                <p style="font-size: 1.2rem; margin: 20px 0;">
                    Se necesitan al menos <strong><?= $max_boletos ?></strong> boletos para iniciar el sorteo
                </p>
                <p style="opacity: 0.8;">La página se actualiza automáticamente cada 5 segundos</p>
                <div style="margin-top: 20px;">
                    <i class="fas fa-users" style="font-size: 1.5rem; margin-right: 10px;"></i>
                    <span style="font-size: 1.1rem;">Participantes actuales: <strong><?= count($participaciones) ?></strong></span>
                </div>
            </div>
            <script>setTimeout(() => location.reload(), 5000);</script>

        <?php elseif (count($ganadores) === 0): ?>
            <div class="waiting-card">
                <div class="icon">🎲</div>
                <h2>¡Sorteo iniciando!</h2>
                <p style="font-size: 1.2rem; margin: 20px 0;">
                    Todos los boletos han sido vendidos, ejecutando sorteo...
                </p>
                <p style="opacity: 0.8;">Espera un momento mientras seleccionamos a los ganadores</p>
            </div>
            <script>setTimeout(() => location.reload(), 3000);</script>

        <?php else: ?>
            <div class="results-container">
                <?php if ($usuario_gano): ?>
                    <div class="winner-announcement mi-resultado ganador-<?= $tipo_ganador ?>">
                        <h2>🎉 ¡FELICITATIONS! 🎉</h2>
                        <p style="font-size: 1.5rem; margin: 15px 0;">
                            <strong>¡Ganaste el <?= $texto_lugar[$mi_lugar] ?>!</strong>
                        </p>
                        <?php if ($tipo_ganador == 'unico'): ?>
                            <p style="font-size: 1.2rem; opacity: 0.9;">¡Eres el ganador único de este sorteo!</p>
                        <?php elseif ($tipo_ganador == 'ambos'): ?>
                            <p style="font-size: 1.2rem; opacity: 0.9;">¡Eres uno de los dos afortunados ganadores!</p>
                        <?php else: ?>
                            <p style="font-size: 1.2rem; opacity: 0.9;">¡Eres uno de los afortunados ganadores!</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="winner-announcement no-gane">
                        <h2>😔 Esta vez no fue tu turno</h2>
                        <p style="font-size: 1.3rem; margin: 15px 0;">
                            No ganaste en esta ocasión, pero no te desanimes
                        </p>
                        <p style="font-size: 1.1rem; opacity: 0.9;">¡La próxima vez será tu oportunidad!</p>
                    </div>
                <?php endif; ?>
                
                <div style="text-align: center; margin: 40px 0;">
                    <h3 style="font-size: 2rem; margin-bottom: 30px;">
                        🏆 
                        <?php if ($tipo_ganador == 'unico'): ?>
                            Ganador Único
                        <?php elseif ($tipo_ganador == 'ambos'): ?>
                            Ganadores del Sorteo
                        <?php else: ?>
                            Lista de Ganadores
                        <?php endif; ?>
                    </h3>
                </div>

                <div class="ganadores-grid">
                    <?php foreach ($ganadores as $index => $g): ?>
                        <div class="ganador-card <?= $g['lugar'] ?> <?= ($g['id_usuario'] == $id_usuario) ? 'mi-resultado' : '' ?>" id="g<?= $index ?>">
                            <div style="text-align: center;">
                                <span class="medal">
                                    <?= ['primer'=>'🥇','segundo'=>'🥈','tercer'=>'🥉'][$g['lugar']] ?>
                                </span>
                                <div class="winner-name"><?= htmlspecialchars($g['nombre']) ?></div>
                                <div class="winner-place"><?= $texto_lugar[$g['lugar']] ?></div>
                                
                                <?php if ($g['id_usuario'] == $id_usuario): ?>
                                    <div class="qr-section">
                                        <?php if (!empty($g['qr_pago_premio'])): ?>
                                            <p style="color: #00ff88; font-weight: bold; margin-bottom: 15px;">
                                                <i class="fas fa-check-circle"></i> QR de cobro ya enviado
                                            </p>
                                            <img src="../<?= htmlspecialchars($g['qr_pago_premio']) ?>" class="qr-preview" alt="QR enviado">
                                        <?php else: ?>
                                            <div class="upload-form">
                                                <h4 style="margin-bottom: 15px;">📱 Sube tu QR para recibir el premio</h4>
                                                <form action="subir.php" method="POST" enctype="multipart/form-data">
                                                    <input type="hidden" name="id_sorteo" value="<?= $id_sorteo ?>">
                                                    <input type="hidden" name="lugar" value="<?= $g['lugar'] ?>">
                                                    <input type="file" name="qr" accept="image/png,image/jpeg,image/jpg,image/gif" required class="file-input">
                                                    <button type="submit" class="upload-btn">
                                                        <i class="fas fa-upload"></i> Subir QR de Cobro
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <script>
                // Animación de aparición de ganadores
                setTimeout(() => {
                    document.querySelectorAll('.ganador-card').forEach((el, i) => {
                        setTimeout(() => el.classList.add('show'), 600 * i);
                    });
                }, 500);

                // Crear confetti si el usuario ganó
                <?php if ($usuario_gano): ?>
                function createConfetti() {
                    const colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#ffd93d', '#6c5ce7'];
                    for (let i = 0; i < 50; i++) {
                        const confetti = document.createElement('div');
                        confetti.classList.add('confetti');
                        confetti.style.left = Math.random() * 100 + '%';
                        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                        confetti.style.animation = `confetti-fall ${Math.random() * 3 + 2}s linear infinite`;
                        confetti.style.animationDelay = Math.random() * 2 + 's';
                        document.body.appendChild(confetti);
                        
                        setTimeout(() => confetti.remove(), 5000);
                    }
                }
                createConfetti();
                setInterval(createConfetti, 3000);
                <?php endif; ?>

                // Crear estrellas de fondo
                function createStars() {
                    const starsContainer = document.querySelector('.stars');
                    for (let i = 0; i < 100; i++) {
                        const star = document.createElement('div');
                        star.classList.add('star');
                        star.style.left = Math.random() * 100 + '%';
                        star.style.top = Math.random() * 100 + '%';
                        star.style.width = Math.random() * 3 + 1 + 'px';
                        star.style.height = star.style.width;
                        star.style.animationDelay = Math.random() * 2 + 's';
                        starsContainer.appendChild(star);
                    }
                }
                createStars();
            </script>
        <?php endif; ?>

        <div style="text-align: center;">
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
    </div>
</body>
</html>