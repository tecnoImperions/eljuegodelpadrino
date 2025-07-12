<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['usuario']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $celular = trim($_POST['celular'] ?? '');
    $info = trim($_POST['info_adicional'] ?? '');
    $foto_nombre = $_SESSION['usuario']['foto_perfil'] ?? null;

    if (!empty($_FILES['foto']['name'])) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_nombre = "perfil_$user_id." . strtolower($ext);

        // Ruta física al directorio de subida
        $directorio = __DIR__ . '/../uploads/perfiles';

        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $ruta = $directorio . '/' . $foto_nombre;
        move_uploaded_file($_FILES['foto']['tmp_name'], $ruta);
    }

    $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, correo = ?, celular = ?, info_adicional = ?, foto_perfil = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("sssssi", $nombre, $correo, $celular, $info, $foto_nombre, $user_id);

    if ($stmt->execute()) {
        $_SESSION['usuario']['nombre'] = $nombre;
        $_SESSION['usuario']['correo'] = $correo;
        $_SESSION['usuario']['celular'] = $celular;
        $_SESSION['usuario']['info_adicional'] = $info;
        $_SESSION['usuario']['foto_perfil'] = $foto_nombre;
        echo json_encode(['success' => true, 'mensaje' => 'Perfil actualizado con éxito']);
    } else {
        echo json_encode(['success' => false, 'mensaje' => 'Ocurrió un error. Intenta de nuevo.']);
    }
    exit;
}

$stmt = $conn->prepare("SELECT nombre, correo, celular, info_adicional, foto_perfil FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#000000">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-gold: #f1c40f;
            --primary-gold-dark: #d4ac0d;
            --bg-dark: #0a0a0a;
            --bg-card: #1a1a1a;
            --bg-card-hover: #222;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --border-subtle: rgba(241, 196, 15, 0.2);
            --shadow-gold: rgba(241, 196, 15, 0.4);
            --gradient-bg: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            --gradient-card: linear-gradient(145deg, #1a1a1a, #252525);
            --gradient-gold: linear-gradient(135deg, #f1c40f, #f39c12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #0f0f0f; /* Fondo oscuro sólido */
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(241, 196, 15, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(241, 196, 15, 0.08) 0%, transparent 50%);
            z-index: -1;
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
        }

        .container {
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .perfil-card {
            background: var(--gradient-card);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.8),
                0 0 0 1px var(--border-subtle),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            position: relative;
            transform: translateY(20px);
            opacity: 0;
            animation: slideUp 0.8s ease-out forwards;
        }

        @keyframes slideUp {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .perfil-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--gradient-gold);
            border-radius: 24px 24px 0 0;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
            position: relative;
        }

        .subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
            font-weight: 400;
        }

        .foto-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 40px;
            position: relative;
        }

        .foto-wrapper {
            position: relative;
            margin-bottom: 16px;
        }

        .foto-preview {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 4px solid transparent;
            background: var(--gradient-gold);
            padding: 4px;
            object-fit: cover;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 15px 35px rgba(241, 196, 15, 0.2);
        }

        .foto-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 40px rgba(241, 196, 15, 0.4);
        }

        .foto-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            cursor: pointer;
        }

        .foto-wrapper:hover .foto-overlay {
            opacity: 1;
        }

        .foto-overlay i {
            font-size: 1.5rem;
            color: var(--primary-gold);
        }

        .change-photo-btn {
            background: var(--gradient-gold);
            color: #000;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(241, 196, 15, 0.3);
        }

        .change-photo-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(241, 196, 15, 0.4);
        }

        input[type="file"] {
            display: none;
        }

        .form-grid {
            display: grid;
            gap: 24px;
        }

        .field-group {
            position: relative;
            animation: fadeInUp 0.6s ease-out both;
        }

        .field-group:nth-child(2) { animation-delay: 0.1s; }
        .field-group:nth-child(3) { animation-delay: 0.2s; }
        .field-group:nth-child(4) { animation-delay: 0.3s; }
        .field-group:nth-child(5) { animation-delay: 0.4s; }

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

        .field-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary-gold);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .field-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .field-input,
        .field-textarea {
            width: 100%;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid transparent;
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 400;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
        }

        .field-input:focus,
        .field-textarea:focus {
            outline: none;
            border-color: var(--primary-gold);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 4px rgba(241, 196, 15, 0.1);
        }

        .field-input:read-only,
        .field-textarea:read-only {
            cursor: default;
            background: rgba(255, 255, 255, 0.03);
        }

        .field-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .edit-btn {
            position: absolute;
            right: 16px;
            background: none;
            border: none;
            color: var(--primary-gold);
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            padding: 4px;
            border-radius: 6px;
        }

        .edit-btn:hover {
            background: rgba(241, 196, 15, 0.1);
            transform: scale(1.1);
        }

        .message {
            margin-top: 24px;
            padding: 16px 20px;
            border-radius: 12px;
            font-weight: 500;
            text-align: center;
            display: none;
            animation: slideInDown 0.5s ease-out;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.success {
            background: rgba(46, 204, 113, 0.1);
            border: 1px solid rgba(46, 204, 113, 0.3);
            color: #2ecc71;
        }

        .message.error {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: #e74c3c;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 32px;
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 12px 20px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-subtle);
        }

        .back-link:hover {
            color: var(--primary-gold);
            background: rgba(241, 196, 15, 0.1);
            transform: translateX(-4px);
        }

        .confirm-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .confirm-box {
            background: var(--gradient-card);
            border: 2px solid var(--primary-gold);
            padding: 32px;
            text-align: center;
            border-radius: 20px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.8);
            animation: modalSlideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes modalSlideUp {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .confirm-box h3 {
            color: var(--primary-gold);
            margin-bottom: 16px;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
        }

        .confirm-box p {
            margin-bottom: 24px;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .confirm-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--gradient-gold);
            color: #000;
            box-shadow: 0 4px 15px rgba(241, 196, 15, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(241, 196, 15, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
            border: 1px solid var(--border-subtle);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 16px;
                align-items: flex-start;
                padding-top: 40px;
            }

            .perfil-card {
                padding: 24px;
                border-radius: 20px;
            }

            .title {
                font-size: 2rem;
            }

            .foto-preview {
                width: 120px;
                height: 120px;
            }

            .form-grid {
                gap: 20px;
            }

            .field-input,
            .field-textarea {
                padding: 14px 16px;
                font-size: 16px; /* Prevents zoom on iOS */
            }

            .confirm-box {
                padding: 24px;
                margin: 20px;
            }

            .confirm-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .perfil-card {
                padding: 20px;
            }

            .title {
                font-size: 1.75rem;
            }

            .foto-preview {
                width: 100px;
                height: 100px;
            }
        }

        /* Dark scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-dark);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-gold);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-gold-dark);
        }

        /* Loading animation */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 24px;
            height: 24px;
            margin: -12px 0 0 -12px;
            border: 2px solid var(--primary-gold);
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="perfil-card">
            <div class="header">
                <h1 class="title">Mi Perfil</h1>
                <p class="subtitle">Gestiona tu información personal</p>
            </div>

            <div class="foto-section">
                <div class="foto-wrapper">
                    <img src="<?= !empty($usuario['foto_perfil']) && file_exists("../uploads/perfiles/{$usuario['foto_perfil']}") 
                        ? "../uploads/perfiles/" . htmlspecialchars($usuario['foto_perfil']) 
                        : 'https://static.vecteezy.com/system/resources/previews/026/619/142/non_2x/default-avatar-profile-icon-of-social-media-user-photo-image-vector.jpg' ?>" 
                         alt="Foto de perfil" class="foto-preview" id="fotoPreview">
                    <div class="foto-overlay">
                        <i class="bi bi-camera"></i>
                    </div>
                </div>
                <label for="foto" class="change-photo-btn">
                    <i class="bi bi-camera"></i> Cambiar foto
                </label>
                <input type="file" name="foto" id="foto" accept="image/*">
            </div>

            <form id="perfilForm" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="field-group">
                        <label class="field-label" for="nombre">Nombre completo</label>
                        <div class="field-input-wrapper">
                            <input type="text" 
                                   name="nombre" 
                                   id="nombre" 
                                   class="field-input"
                                   value="<?= htmlspecialchars($usuario['nombre']) ?>" 
                                   readonly 
                                   placeholder="Ingresa tu nombre completo">
                            <button type="button" class="edit-btn" onclick="confirmarCambio('nombre')">
                                <i class="bi bi-gear-fill"></i>
                            </button>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="correo">Correo electrónico</label>
                        <div class="field-input-wrapper">
                            <input type="email" 
                                   name="correo" 
                                   id="correo" 
                                   class="field-input"
                                   value="<?= htmlspecialchars($usuario['correo']) ?>" 
                                   readonly 
                                   placeholder="tu@email.com">
                            <button type="button" class="edit-btn" onclick="confirmarCambio('correo')">
                                <i class="bi bi-gear-fill"></i>
                            </button>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="celular">Número de celular</label>
                        <div class="field-input-wrapper">
                            <input type="text" 
                                   name="celular" 
                                   id="celular" 
                                   class="field-input"
                                   value="<?= htmlspecialchars($usuario['celular']) ?>" 
                                   readonly 
                                   placeholder="+591 123 456 789">
                            <button type="button" class="edit-btn" onclick="confirmarCambio('celular')">
                                <i class="bi bi-gear-fill"></i>
                            </button>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="info_adicional">Información adicional</label>
                        <div class="field-input-wrapper">
                            <textarea name="info_adicional" 
                                      id="info_adicional" 
                                      class="field-textarea"
                                      readonly 
                                      placeholder="Cuéntanos más sobre ti..."><?= htmlspecialchars($usuario['info_adicional']) ?></textarea>
                            <button type="button" class="edit-btn" onclick="confirmarCambio('info_adicional')">
                                <i class="bi bi-gear-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div id="msg" class="message"></div>

            <a href="dashboard.php" class="back-link">
                <i class="bi bi-arrow-left-circle"></i>
                Volver al Panel
            </a>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div class="confirm-modal" id="confirmModal">
        <div class="confirm-box">
            <h3><i class="bi bi-exclamation-triangle"></i> Confirmar cambio</h3>
            <p>¿Estás seguro que quieres editar este campo?<br><strong>Los cambios se guardarán automáticamente.</strong></p>
            <div class="confirm-buttons">
                <button class="btn btn-primary" onclick="aceptarCambio()">
                    <i class="bi bi-check-lg"></i> Sí, editar
                </button>
                <button class="btn btn-secondary" onclick="cancelarCambio()">
                    <i class="bi bi-x-lg"></i> Cancelar
                </button>
            </div>
        </div>
    </div>

    <script>
        let campoActual = null;

        function confirmarCambio(id) {
            campoActual = document.getElementById(id);
            document.getElementById('confirmModal').style.display = 'flex';
        }

        function aceptarCambio() {
            if (campoActual) {
                campoActual.removeAttribute('readonly');
                campoActual.focus();
                
                // Añadir animación visual
                campoActual.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    campoActual.style.transform = 'scale(1)';
                }, 200);
            }
            document.getElementById('confirmModal').style.display = 'none';
        }

        function cancelarCambio() {
            campoActual = null;
            document.getElementById('confirmModal').style.display = 'none';
        }

        function mostrarMensaje(texto, tipo = 'success') {
            const msg = document.getElementById('msg');
            msg.textContent = texto;
            msg.className = `message ${tipo}`;
            msg.style.display = 'block';
            
            setTimeout(() => {
                msg.style.display = 'none';
            }, 4000);
        }

        function addLoadingState(element) {
            element.classList.add('loading');
        }

        function removeLoadingState(element) {
            element.classList.remove('loading');
        }

        // Event listeners para los campos del formulario
        const form = document.getElementById('perfilForm');
        ['nombre', 'correo', 'celular', 'info_adicional'].forEach(id => {
            const el = document.getElementById(id);
            el.addEventListener('change', () => {
                addLoadingState(el.closest('.field-group'));
                
                const formData = new FormData(form);
                formData.append('ajax', '1');

                fetch('perfil.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    removeLoadingState(el.closest('.field-group'));
                    if (data.success) {
                        mostrarMensaje(data.mensaje, 'success');
                        el.setAttribute('readonly', true);
                    } else {
                        mostrarMensaje(data.mensaje, 'error');
                    }
                })
                .catch(() => {
                    removeLoadingState(el.closest('.field-group'));
                    mostrarMensaje('Error de conexión. Intenta nuevamente.', 'error');
                });
            });

            // Auto-guardar al perder el foco
            el.addEventListener('blur', () => {
                if (!el.hasAttribute('readonly')) {
                    el.dispatchEvent(new Event('change'));
                }
            });
        });

        // Event listener para cambio de foto
        document.getElementById('foto').addEventListener('change', () => {
            const input = document.getElementById('foto');
            const preview = document.getElementById('fotoPreview');
            const fotoSection = document.querySelector('.foto-section');
            
            if (input.files.length > 0) {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                    // Animación de cambio de foto
                    preview.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        preview.style.transform = 'scale(1)';
                    }, 150);
                };
                reader.readAsDataURL(input.files[0]);

                addLoadingState(fotoSection);

                const formData = new FormData(form);
                formData.append('ajax', '1');
                formData.append('foto', input.files[0]);

                fetch('perfil.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    removeLoadingState(fotoSection);
                    mostrarMensaje(data.mensaje, data.success ? 'success' : 'error');
                })
                .catch(() => {
                    removeLoadingState(fotoSection);
                    mostrarMensaje('Error al subir la imagen', 'error');
                });
            }
        });

        // Cerrar modal con Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && document.getElementById('confirmModal').style.display === 'flex') {
                cancelarCambio();
            }
        });

        // Cerrar modal al hacer clic fuera
        document.getElementById('confirmModal').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                cancelarCambio();
            }
        });

        // Animación de entrada mejorada
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                document.body.style.overflow = 'auto';
            }, 800);
        });
    </script>
</body>
</html>