<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit;
}

require_once '../config.php';

$user = $_SESSION['usuario'];
$usuario_id = $user['id'];
$id_sorteo = $_POST['id_sorteo'] ?? $_POST['id_sorteo_confirmado'] ?? null;

$ya_participo = false;
$estado_participacion = null;
$mensaje = "";
$mensaje_error = "";

if ($id_sorteo) {
    $sql_check = "SELECT estado FROM participaciones WHERE id_sorteo = ? AND id_usuario = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $id_sorteo, $usuario_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check && $result_check->num_rows > 0) {
        $ya_participo = true;
        $participacion = $result_check->fetch_assoc();
        $estado_participacion = $participacion['estado'];
    }

    $sql_sorteo = "SELECT estado, precio_entrada, max_participantes FROM sorteos WHERE id = ?";
    $stmt_sorteo = $conn->prepare($sql_sorteo);
    $stmt_sorteo->bind_param("i", $id_sorteo);
    $stmt_sorteo->execute();
    $resultado_sorteo = $stmt_sorteo->get_result();

    if ($resultado_sorteo && $resultado_sorteo->num_rows > 0) {
        $sorteo = $resultado_sorteo->fetch_assoc();
    } else {
        $mensaje_error = "❌ Sorteo no encontrado o no válido.";
    }

    if (isset($sorteo) && $sorteo['estado'] === 'cerrado' && $ya_participo && $estado_participacion === 'comprobado') {
        header("Location: concurso.php?id=" . $id_sorteo);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['comprobante']) && empty($mensaje_error)) {
        $comprobante = $_FILES['comprobante'];
        $ext = strtolower(pathinfo($comprobante['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowed)) {
            $mensaje_error = "⚠️ Solo se permiten archivos JPG, JPEG y PNG.";
        } elseif ($comprobante['size'] > 5 * 1024 * 1024) {
            $mensaje_error = "⚠️ El archivo es demasiado grande. Máximo 5MB.";
        } elseif ($ya_participo && $estado_participacion !== 'rechazado') {
            $mensaje_error = "⚠️ Ya has enviado un comprobante para este sorteo.";
        } else {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $file_path = $upload_dir . uniqid('comprobante_', true) . '.' . $ext;

            if (move_uploaded_file($comprobante['tmp_name'], $file_path)) {
                $cantidad_tickets = max(0, min(intval($_POST['cantidad_tickets'] ?? 1), 25));

                if ($cantidad_tickets === 0) {
                    $mensaje_error = "❌ No puedes enviar un comprobante con 0 tickets.";
                    unlink($file_path);
                } else {
                    // Calcular boletos vendidos
                    $sql_boletos_vendidos = "SELECT COALESCE(SUM(cantidad_boletos), 0) AS boletos_vendidos FROM participaciones WHERE id_sorteo = ?";
                    $stmt_boletos = $conn->prepare($sql_boletos_vendidos);
                    $stmt_boletos->bind_param("i", $id_sorteo);
                    $stmt_boletos->execute();
                    $resultado_boletos = $stmt_boletos->get_result();
                    $fila_boletos = $resultado_boletos->fetch_assoc();

                    $boletos_vendidos = (int)($fila_boletos['boletos_vendidos'] ?? 0);
                    $boletos_restantes = max(0, $sorteo['max_participantes'] - $boletos_vendidos);

                    if ($cantidad_tickets > $boletos_restantes) {
                        $mensaje_error = "❌ No hay suficientes boletos disponibles. Solo quedan {$boletos_restantes}.";
                        unlink($file_path);
                    } else {
                        $conn->begin_transaction();
                        try {
                            $sql_insert = "INSERT INTO participaciones (id_usuario, id_sorteo, comprobante_imagen, estado, cantidad_boletos, fecha_participacion)
                                           VALUES (?, ?, ?, 'pendiente', ?, NOW())";
                            $stmt_insert = $conn->prepare($sql_insert);
                            $stmt_insert->bind_param("iisi", $usuario_id, $id_sorteo, $file_path, $cantidad_tickets);
                            $stmt_insert->execute();

                            $conn->commit();
                            $mensaje = "✅ ¡Comprobante enviado exitosamente! Espera la validación del administrador.";
                            $ya_participo = true;
                            $estado_participacion = 'pendiente';
                        } catch (Exception $e) {
                            $conn->rollback();
                            $mensaje_error = "❌ Ocurrió un error inesperado.";
                            unlink($file_path);
                        }
                    }
                }
            } else {
                $mensaje_error = "❌ Error al subir el archivo. Intenta de nuevo.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>🎟️ Participar en Sorteo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gold: #FFD700;
            --dark-bg: #0a0a0a;
            --card-bg: #1a1a1a;
            --border-gold: #B8860B;
            --text-light: #f8f9fa;
            --success-green: #28a745;
            --danger-red: #dc3545;
            --warning-orange: #fd7e14;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, var(--dark-bg) 0%, #1a1a1a 100%);
            color: var(--text-light);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            padding: 20px 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .welcome-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(45deg, var(--primary-gold), var(--border-gold));
            border-radius: 15px;
            color: var(--dark-bg);
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
        }

        .welcome-header h1 {
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 1.8rem;
        }

        .welcome-header p {
            margin: 0;
            opacity: 0.8;
            font-weight: 500;
        }

        .main-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 215, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .main-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-gold), var(--border-gold));
        }

        .sorteo-title {
            text-align: center;
            color: var(--primary-gold);
            font-weight: 600;
            margin-bottom: 25px;
            font-size: 1.5rem;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 10px;
        }

        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(255, 215, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .step.active {
            background: var(--primary-gold);
            color: var(--dark-bg);
        }

        .qr-section {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(255, 215, 0, 0.05);
            border-radius: 15px;
            border: 1px dashed var(--primary-gold);
        }

        .qr-img {
            max-width: 200px;
            height: auto;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .qr-img:hover {
            transform: scale(1.05);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            color: var(--primary-gold);
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 215, 0, 0.2);
            border-radius: 10px;
            color: var(--text-light);
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
            color: var(--text-light);
        }

        .input-group {
            position: relative;
        }

        .input-group-text {
            background: var(--primary-gold);
            color: var(--dark-bg);
            border: none;
            font-weight: 600;
        }

        .ticket-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-ticket {
            background: var(--primary-gold);
            color: var(--dark-bg);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-ticket:hover {
            background: var(--border-gold);
            transform: scale(1.1);
        }

        .ticket-display {
            background: rgba(255, 215, 0, 0.1);
            border: 2px solid var(--primary-gold);
            border-radius: 10px;
            padding: 10px 20px;
            text-align: center;
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--primary-gold);
            min-width: 80px;
        }

        .total-section {
            background: rgba(40, 167, 69, 0.1);
            border: 2px solid var(--success-green);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            margin-bottom: 20px;
        }

        .total-amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--success-green);
        }

        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload-input {
            position: absolute;
            left: -9999px;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: rgba(255, 215, 0, 0.1);
            border: 2px dashed var(--primary-gold);
            border-radius: 10px;
            padding: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .file-upload-label:hover {
            background: rgba(255, 215, 0, 0.2);
            border-color: var(--border-gold);
        }

        .file-upload-label.has-file {
            background: rgba(40, 167, 69, 0.1);
            border-color: var(--success-green);
            color: var(--success-green);
        }

        .btn-submit {
            background: linear-gradient(45deg, var(--primary-gold), var(--border-gold));
            color: var(--dark-bg);
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.4);
        }

        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .btn-back {
            background: transparent;
            color: var(--primary-gold);
            border: 2px solid var(--primary-gold);
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            margin-top: 15px;
        }

        .btn-back:hover {
            background: var(--primary-gold);
            color: var(--dark-bg);
            text-decoration: none;
        }

        .alert-custom {
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-green);
            border-left: 4px solid var(--success-green);
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-red);
            border-left: 4px solid var(--danger-red);
        }

        .alert-warning {
            background: rgba(253, 126, 20, 0.1);
            color: var(--warning-orange);
            border-left: 4px solid var(--warning-orange);
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
        }

        .modal-close {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .modal-close:hover {
            transform: scale(1.2);
        }

        .loading-spinner {
            display: none;
            margin-left: 10px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
            
            .main-card {
                margin: 0 10px;
                padding: 20px;
            }
            
            .welcome-header h1 {
                font-size: 1.5rem;
            }
        }

        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .main-card {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .qr-img {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header de Bienvenida -->
        <div class="welcome-header">
            <h1><i class="fas fa-user-circle"></i> ¡Hola, <?= htmlspecialchars($user['nombre']) ?>!</h1>
            <p>Estás a un paso de participar en el sorteo</p>
        </div>

        <?php if (!$id_sorteo): ?>
            <div class="main-card">
                <div class="alert-custom alert-warning text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                    <h4>⚠️ Oops! No hay sorteo seleccionado</h4>
                    <p>Por favor, selecciona un sorteo desde el panel principal.</p>
                </div>
                <div class="text-center">
                    <a href="dashboard.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Volver al Panel Principal
                    </a>
                </div>
            </div>

        <?php elseif ($mensaje_error): ?>
            <div class="main-card">
                <div class="alert-custom alert-danger text-center">
                    <i class="fas fa-times-circle fa-2x mb-3"></i>
                    <h4><?= htmlspecialchars($mensaje_error) ?></h4>
                </div>
                <div class="text-center">
                    <a href="dashboard.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Volver al Panel
                    </a>
                </div>
            </div>

        <?php elseif ($mensaje): ?>
            <div class="main-card">
                <div class="alert-custom alert-success text-center">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <h4><?= htmlspecialchars($mensaje) ?></h4>
                    <p class="mt-3">Te notificaremos cuando tu comprobante sea validado.</p>
                </div>
                <div class="text-center">
                    <a href="dashboard.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Volver al Panel
                    </a>
                </div>
            </div>

        <?php else: ?>
            <div class="main-card">
                <h2 class="sorteo-title">
                    <i class="fas fa-ticket-alt"></i> Sorteo #<?= htmlspecialchars($id_sorteo) ?>
                </h2>

                <?php if (!$ya_participo): ?>
                    <!-- Indicador de pasos -->
                    <div class="step-indicator">
                        <div class="step active">1</div>
                        <div class="step">2</div>
                        <div class="step">3</div>
                    </div>

                    <!-- Sección QR -->
                    <div class="qr-section">
                        <h5 class="mb-3"><i class="fas fa-qrcode"></i> Paso 1: Escanea y Paga</h5>
                        <p class="mb-3">Escanea el código QR con tu app de pagos favorita</p>
                        <img src="../assets/img/qr.jpg" alt="QR de pago" class="qr-img" onclick="openModal()">
                        <p class="mt-2"><small><i class="fas fa-search-plus"></i> Clic para ampliar</small></p>
                    </div>

                    <!-- Formulario -->
                    <form action="" method="POST" enctype="multipart/form-data" novalidate id="participationForm">
                        <input type="hidden" name="id_sorteo_confirmado" value="<?= htmlspecialchars($id_sorteo); ?>">

                        <!-- Paso 2: Selección de tickets -->
                        <div class="form-group">
                            <h5 class="mb-3"><i class="fas fa-tickets-alt"></i> Paso 2: Selecciona tus Tickets</h5>
                            <label class="form-label">Cantidad de Tickets</label>
                            <div class="ticket-controls">
                                <button type="button" class="btn-ticket" onclick="decreaseTickets()">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <div class="ticket-display" id="ticketDisplay">1</div>
                                <button type="button" class="btn-ticket" onclick="increaseTickets()">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <input type="hidden" name="cantidad_tickets" id="cantidad_tickets" value="1">
                            </div>
                        </div>

                        <!-- Total a pagar -->
                        <div class="total-section">
                            <div class="mb-2">
                                <i class="fas fa-calculator"></i> Total a Pagar
                            </div>
                            <div class="total-amount" id="total_pagar"><?= ($sorteo['precio_entrada'] ?? 5) ?> Bs</div>
                            <small>Precio por ticket: <?= ($sorteo['precio_entrada'] ?? 5) ?> Bs</small>
                        </div>

                        <!-- Paso 3: Subir comprobante -->
                        <div class="form-group">
                            <h5 class="mb-3"><i class="fas fa-cloud-upload-alt"></i> Paso 3: Sube tu Comprobante</h5>
                            <div class="file-upload">
                                <input type="file" class="file-upload-input" name="comprobante" id="comprobante" accept=".jpg,.jpeg,.png" required>
                                <label for="comprobante" class="file-upload-label" id="fileLabel">
                                    <i class="fas fa-upload fa-2x"></i>
                                    <div>
                                        <div><strong>Arrastra tu archivo aquí</strong></div>
                                        <div>o haz clic para seleccionar</div>
                                        <small>JPG, JPEG, PNG (máx. 5MB)</small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Botón de envío -->
                        <button type="submit" class="btn-submit" id="submitBtn" disabled>
                            <i class="fas fa-paper-plane"></i> Enviar Comprobante
                            <div class="loading-spinner">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </button>
                    </form>

                <?php else: ?>
                    <div class="alert-custom alert-warning text-center">
                        <i class="fas fa-clock fa-2x mb-3"></i>
                        <h4>Comprobante Enviado</h4>
                        <p>Ya has enviado un comprobante para este sorteo.<br>
                        <strong>Estado:</strong> <?= ucfirst($estado_participacion) ?></p>
                        <p>Espera la validación del administrador. Te notificaremos cuando esté listo.</p>
                    </div>
                <?php endif; ?>

                <!-- Botón de retorno -->
                <div class="text-center">
                    <a href="dashboard.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Volver al Panel Principal
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para QR ampliado -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeModal()">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <img src="../assets/img/qr.jpg" alt="QR ampliado" style="max-width: 100%; border-radius: 10px;">
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const precio = <?= $sorteo['precio_entrada'] ?? 5 ?>;
        let ticketCount = 1;
        let imageUploaded = false;

        // Elementos del DOM
        const ticketDisplay = document.getElementById("ticketDisplay");
        const ticketInput = document.getElementById("cantidad_tickets");
        const totalPagar = document.getElementById("total_pagar");
        const submitBtn = document.getElementById("submitBtn");
        const comprobanteInput = document.getElementById("comprobante");
        const fileLabel = document.getElementById("fileLabel");
        const form = document.getElementById("participationForm");
        const stepIndicators = document.querySelectorAll(".step");

        // Función para detectar si es dispositivo móvil
        function isMobile() {
            return window.innerWidth <= 768;
        }

        // Función para detectar si es tablet
        function isTablet() {
            return window.innerWidth > 768 && window.innerWidth <= 1024;
        }

        // Funciones para controlar tickets
        function increaseTickets() {
            if (ticketCount < 25 && !imageUploaded) {
                ticketCount++;
                updateTicketDisplay();
                updateStepIndicator(2);
            } else if (imageUploaded) {
                showAlert("⚠️ No puedes cambiar la cantidad de tickets después de subir el comprobante. Si necesitas cambiar la cantidad, selecciona otro archivo.", "warning");
            }
        }

        function decreaseTickets() {
            if (ticketCount > 1 && !imageUploaded) {
                ticketCount--;
                updateTicketDisplay();
                updateStepIndicator(2);
            } else if (imageUploaded) {
                showAlert("⚠️ No puedes cambiar la cantidad de tickets después de subir el comprobante. Si necesitas cambiar la cantidad, selecciona otro archivo.", "warning");
            }
        }

        function updateTicketDisplay() {
            ticketDisplay.textContent = ticketCount;
            ticketInput.value = ticketCount;
            totalPagar.textContent = (ticketCount * precio) + " Bs";
        }

        function updateStepIndicator(step) {
            stepIndicators.forEach((indicator, index) => {
                if (index < step) {
                    indicator.classList.add("active");
                } else {
                    indicator.classList.remove("active");
                }
            });
        }

        // Función para mostrar alertas responsivas
        function showAlert(message, type = "warning") {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert-custom alert-${type}`;
            
            // Estilos responsivos para la alerta
            const alertStyles = {
                position: 'fixed',
                zIndex: '10000',
                animation: 'fadeInUp 0.3s ease-out',
                borderRadius: '10px',
                padding: '15px',
                display: 'flex',
                alignItems: 'center',
                boxShadow: '0 5px 15px rgba(0, 0, 0, 0.2)'
            };

            if (isMobile()) {
                // Estilos para móvil
                Object.assign(alertStyles, {
                    top: '10px',
                    left: '10px',
                    right: '10px',
                    maxWidth: 'none',
                    fontSize: '14px'
                });
            } else {
                // Estilos para desktop
                Object.assign(alertStyles, {
                    top: '20px',
                    right: '20px',
                    maxWidth: '350px'
                });
            }

            Object.assign(alertDiv.style, alertStyles);
            
            // Solo mostrar el mensaje sin icono adicional (ya que los mensajes incluyen emojis)
            alertDiv.innerHTML = `
                <div style="flex: 1;">${message}</div>
            `;

            document.body.appendChild(alertDiv);

            // Remover después de 4 segundos (6 segundos en móvil para que sea más fácil de leer)
            const timeout = isMobile() ? 6000 : 4000;
            setTimeout(() => {
                alertDiv.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => {
                    if (document.body.contains(alertDiv)) {
                        document.body.removeChild(alertDiv);
                    }
                }, 300);
            }, timeout);
        }

        function updateTicketButtonsState() {
            const decreaseBtn = document.querySelector('.btn-ticket:first-of-type');
            const increaseBtn = document.querySelector('.btn-ticket:last-of-type');
            
            if (imageUploaded) {
                decreaseBtn.style.opacity = '0.5';
                increaseBtn.style.opacity = '0.5';
                decreaseBtn.style.cursor = 'not-allowed';
                increaseBtn.style.cursor = 'not-allowed';
                decreaseBtn.title = 'No puedes cambiar la cantidad después de subir el comprobante';
                increaseBtn.title = 'No puedes cambiar la cantidad después de subir el comprobante';
            } else {
                decreaseBtn.style.opacity = '1';
                increaseBtn.style.opacity = '1';
                decreaseBtn.style.cursor = 'pointer';
                increaseBtn.style.cursor = 'pointer';
                decreaseBtn.title = 'Disminuir cantidad de tickets';
                increaseBtn.title = 'Aumentar cantidad de tickets';
            }
        }

        // Función para actualizar el contenido del file label según el dispositivo
        function updateFileLabelContent(hasFile = false, file = null) {
            if (hasFile && file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                const iconSize = isMobile() ? 'fa-lg' : 'fa-2x';
                
                fileLabel.innerHTML = `
                    <i class="fas fa-check-circle ${iconSize}" style="color: #28a745;"></i>
                    <div style="margin-top: ${isMobile() ? '8px' : '10px'};">
                        <div><strong>Archivo seleccionado:</strong></div>
                        <div style="word-break: break-all; ${isMobile() ? 'font-size: 13px;' : ''}">${file.name}</div>
                        <small>${fileSize} MB</small>
                        <br><small style="color: #ffd700;"><i class="fas fa-lock"></i> Cantidad de tickets bloqueada</small>
                    </div>
                `;
            } else {
                const iconSize = isMobile() ? 'fa-lg' : 'fa-2x';
                const text = isMobile() ? 
                    'Toca para seleccionar archivo' : 
                    'Arrastra tu archivo aquí o haz clic para seleccionar';
                
                fileLabel.innerHTML = `
                    <i class="fas fa-upload ${iconSize}"></i>
                    <div style="margin-top: ${isMobile() ? '8px' : '10px'};">
                        <div><strong>${text}</strong></div>
                        <small>JPG, JPEG, PNG (máx. 5MB)</small>
                    </div>
                `;
            }
        }

        // Manejo del archivo con validaciones mejoradas
        comprobanteInput.addEventListener("change", function() {
            const file = this.files[0];
            if (file) {
                // Validar tamaño
                if (file.size > 5 * 1024 * 1024) {
                    showAlert("⚠️ El archivo es demasiado grande. Máximo 5MB.", "danger");
                    this.value = "";
                    return;
                }

                // Validar extensión
                const allowedExtensions = ['jpg', 'jpeg', 'png'];
                const fileExtension = file.name.split('.').pop().toLowerCase();
                if (!allowedExtensions.includes(fileExtension)) {
                    showAlert("⚠️ Solo se permiten archivos JPG, JPEG y PNG.", "danger");
                    this.value = "";
                    return;
                }

                imageUploaded = true;
                updateTicketButtonsState();
                
                fileLabel.classList.add("has-file");
                updateFileLabelContent(true, file);
                
                submitBtn.disabled = false;
                updateStepIndicator(3);
                
                showAlert("✅ Archivo cargado correctamente. La cantidad de tickets ha sido fijada en " + ticketCount + ".", "success");
                
            } else {
                imageUploaded = false;
                updateTicketButtonsState();
                
                fileLabel.classList.remove("has-file");
                updateFileLabelContent(false);
                
                submitBtn.disabled = true;
            }
        });

        // Drag and drop mejorado (solo para desktop)
        if (!isMobile()) {
            fileLabel.addEventListener("dragover", function(e) {
                e.preventDefault();
                this.style.background = "rgba(255, 215, 0, 0.3)";
            });

            fileLabel.addEventListener("dragleave", function(e) {
                e.preventDefault();
                this.style.background = "rgba(255, 215, 0, 0.1)";
            });

            fileLabel.addEventListener("drop", function(e) {
                e.preventDefault();
                this.style.background = "rgba(255, 215, 0, 0.1)";
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    comprobanteInput.files = files;
                    comprobanteInput.dispatchEvent(new Event('change'));
                }
            });
        }

        // Manejo del envío del formulario
        form.addEventListener("submit", function(e) {
            e.preventDefault();
            
            const file = comprobanteInput.files[0];
            if (!file) {
                showAlert("⚠️ Por favor selecciona un comprobante de pago.", "warning");
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                showAlert("⚠️ El archivo es demasiado grande. Máximo 5MB.", "danger");
                return;
            }

            const allowedExtensions = ['jpg', 'jpeg', 'png'];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            if (!allowedExtensions.includes(fileExtension)) {
                showAlert("⚠️ Solo se permiten archivos JPG, JPEG y PNG.", "danger");
                return;
            }

            submitBtn.disabled = true;
            const originalHTML = submitBtn.innerHTML;
            
            submitBtn.innerHTML = `
                <i class="fas fa-spinner fa-spin"></i> Enviando...
                ${!isMobile() ? '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i></div>' : ''}
            `;

            setTimeout(() => {
                this.submit();
            }, 500);
        });

        // Funciones para el modal responsivo
        function openModal() {
            const modal = document.getElementById("modalOverlay");
            modal.style.display = "flex";
            document.body.style.overflow = "hidden";
            
            // Ajustar padding del modal en móvil
            if (isMobile()) {
                modal.style.padding = "10px";
            }
        }

        function closeModal() {
            document.getElementById("modalOverlay").style.display = "none";
            document.body.style.overflow = "auto";
        }

        // Cerrar modal con ESC
        document.addEventListener("keydown", function(e) {
            if (e.key === "Escape") {
                closeModal();
            }
        });

        // Manejar cambios de orientación en móvil
        window.addEventListener("orientationchange", function() {
            setTimeout(() => {
                updateFileLabelContent(imageUploaded, comprobanteInput.files[0]);
            }, 100);
        });

        // Manejar redimensionado de ventana
        let resizeTimeout;
        window.addEventListener("resize", function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                updateFileLabelContent(imageUploaded, comprobanteInput.files[0]);
                updateTicketButtonsState();
            }, 150);
        });

        // Animación de entrada responsiva
        document.addEventListener("DOMContentLoaded", function() {
            const card = document.querySelector(".main-card");
            if (card) {
                card.style.opacity = "0";
                card.style.transform = isMobile() ? "translateY(20px)" : "translateY(30px)";
                
                setTimeout(() => {
                    card.style.transition = "all 0.6s ease-out";
                    card.style.opacity = "1";
                    card.style.transform = "translateY(0)";
                }, 100);
            }

            updateTicketButtonsState();
            updateFileLabelContent(false);
        });

        // Prevenir envío accidental
        window.addEventListener("beforeunload", function(e) {
            if (comprobanteInput.files.length > 0 && !form.submitted) {
                e.preventDefault();
                e.returnValue = "¿Estás seguro de que quieres salir? Los cambios no guardados se perderán.";
            }
        });

        form.addEventListener("submit", function() {
            form.submitted = true;
        });

        // Estilos CSS responsivos
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            @keyframes fadeOut {
                from {
                    opacity: 1;
                    transform: translateY(0);
                }
                to {
                    opacity: 0;
                    transform: translateY(-20px);
                }
            }
            
            .alert-success {
                background: rgba(40, 167, 69, 0.1) !important;
                color: #28a745 !important;
                border-left: 4px solid #28a745 !important;
            }
            
            .alert-warning {
                background: rgba(255, 193, 7, 0.1) !important;
                color: #ffc107 !important;
                border-left: 4px solid #ffc107 !important;
            }
            
            .alert-danger {
                background: rgba(220, 53, 69, 0.1) !important;
                color: #dc3545 !important;
                border-left: 4px solid #dc3545 !important;
            }
            
            /* Estilos responsivos para móvil */
            @media (max-width: 768px) {
                .alert-custom {
                    font-size: 14px !important;
                    padding: 12px !important;
                }
                
                .alert-custom i {
                    font-size: 16px !important;
                }
                
                /* Mejorar legibilidad en móvil */
                .main-card {
                    margin: 10px !important;
                    padding: 15px !important;
                }
                
                .btn-ticket {
                    padding: 8px 12px !important;
                    font-size: 16px !important;
                }
                
                #fileLabel {
                    padding: 15px !important;
                    text-align: center !important;
                }
                
                #submitBtn {
                    padding: 12px 20px !important;
                    font-size: 16px !important;
                }
            }
            
            /* Estilos para tablet */
            @media (min-width: 769px) and (max-width: 1024px) {
                .alert-custom {
                    max-width: 300px !important;
                }
                
                .main-card {
                    margin: 15px !important;
                }
            }
            
            /* Mejorar accesibilidad táctil */
            @media (hover: none) and (pointer: coarse) {
                .btn-ticket {
                    min-height: 44px !important;
                    min-width: 44px !important;
                }
                
                #fileLabel {
                    min-height: 120px !important;
                    cursor: pointer !important;
                }
                
                #submitBtn {
                    min-height: 48px !important;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>