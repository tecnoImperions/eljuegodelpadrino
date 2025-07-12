<?php
require_once 'config.php';
session_start();

$error = '';

// Paso 1: Verificar usuario por correo y celular
if (isset($_POST['verificar'])) {
    $correo = trim($_POST['correo']);
    $celular = trim($_POST['celular']);

    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ? AND celular = ?");
    $stmt->bind_param("ss", $correo, $celular);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $_SESSION['recuperar_usuario'] = $correo; // Guardamos para el paso 2
        header('Location: recuperar.php?step=2');
        exit;
    } else {
        $error = "Correo y celular no coinciden con ningún usuario.";
    }
}

// Paso 2: Cambiar contraseña
if (isset($_POST['cambiar'])) {
    $correo = $_SESSION['recuperar_usuario'] ?? '';
    $contrasena1 = trim($_POST['contrasena1']);
    $contrasena2 = trim($_POST['contrasena2']);

    if (!$correo) {
        $error = "Error de sesión. Por favor inicia el proceso de nuevo.";
    } elseif ($contrasena1 !== $contrasena2) {
        $error = "Las contraseñas no coinciden.";
    } else {
        $hash = password_hash($contrasena1, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET contrasena = ? WHERE correo = ?");
        $stmt->bind_param("ss", $hash, $correo);
        if ($stmt->execute()) {
            unset($_SESSION['recuperar_usuario']);
            // Redirigir automáticamente al login
            header('Location: login.php?msg=contraseña_cambiada');
            exit;
        } else {
            $error = "Error al actualizar la contraseña.";
        }
    }
}

$step = $_GET['step'] ?? 1;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Recuperar contraseña - El Padrino</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
    <style>
        body { background-color: #0f0f0f; color: #f1c40f; font-family: 'Georgia', serif; }
        .container { max-width: 450px; margin-top: 50px; padding: 20px; background: #1c1c1c; border-radius: 10px; }
        .btn-custom { background-color: #f1c40f; color: #0f0f0f; font-weight: bold; width: 100%; }
        .btn-custom:hover { background-color: #e67e22; }
        .form-control { background-color: #0f0f0f; border: 1px solid #f1c40f; color: #f1c40f; }
        .form-control::placeholder { color: #f1c40f; }
        .error-msg { color: #e74c3c; margin-bottom: 10px; }
        .password-toggle {
            position: relative;
        }
        .password-toggle {
            position: relative;
        }

        .password-toggle input[type="password"],
        .password-toggle input[type="text"] {
            padding-right: 40px; /* espacio para el botón */
        }

        .password-toggle .toggle-btn {
            position: absolute;
            right: 10px;
            top: 70%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #f1c40f;
            cursor: pointer;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        .password-toggle .toggle-btn svg {
            width: 22px;
            height: 22px;
        }

    </style>
</head>
<body>
<div class="container">
    <h2 class="mb-4 text-center">Recuperar contraseña</h2>

    <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($step == 1): ?>
    <form method="post" novalidate>
        <div class="mb-3">
            <label for="correo" class="form-label">Correo electrónico</label>
            <input type="email" name="correo" id="correo" class="form-control" required placeholder="correo@ejemplo.com" />
        </div>
        <div class="mb-3">
            <label for="celular" class="form-label">Número de celular</label>
            <input type="text" name="celular" id="celular" class="form-control" required placeholder="Tu número de celular" />
        </div>
        <button type="submit" name="verificar" class="btn btn-custom">Verificar</button>
        <a href="login.php" class="btn btn-outline-light mt-3 w-100">Volver al login</a>
    </form>
    <?php elseif ($step == 2): ?>
    <form method="post" novalidate>
        <div class="mb-3 password-toggle">
            <label for="contrasena1" class="form-label">Nueva contraseña</label>
            <input type="password" name="contrasena1" id="contrasena1" class="form-control" required placeholder="Nueva contraseña" />
            <button type="button" class="toggle-btn" onclick="togglePassword('contrasena1', this)" aria-label="Mostrar u ocultar contraseña">
                <!-- Ojo abierto SVG -->
                <svg xmlns="http://www.w3.org/2000/svg" class="icon-eye" fill="none" viewBox="0 0 24 24" stroke="currentColor" >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <!-- Ojo cerrado SVG, oculto por defecto -->
                <svg xmlns="http://www.w3.org/2000/svg" class="icon-eye-off" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.969 9.969 0 012.694-4.393m3.799-2.558A9.965 9.965 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.097 10.097 0 01-1.318 2.507M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                </svg>
            </button>
        </div>
        <div class="mb-3 password-toggle">
            <label for="contrasena2" class="form-label">Confirmar contraseña</label>
            <input type="password" name="contrasena2" id="contrasena2" class="form-control" required placeholder="Repite la nueva contraseña" />
            <button type="button" class="toggle-btn" onclick="togglePassword('contrasena2', this)" aria-label="Mostrar u ocultar contraseña">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon-eye" fill="none" viewBox="0 0 24 24" stroke="currentColor" >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" class="icon-eye-off" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.969 9.969 0 012.694-4.393m3.799-2.558A9.965 9.965 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.097 10.097 0 01-1.318 2.507M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                </svg>
            </button>
        </div>
        <button type="submit" name="cambiar" class="btn btn-custom">Cambiar contraseña</button>
        <a href="login.php" class="btn btn-outline-light mt-3 w-100">Volver al login</a>
    </form>

    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const iconEye = btn.querySelector('.icon-eye');
            const iconEyeOff = btn.querySelector('.icon-eye-off');

            if (input.type === "password") {
                input.type = "text";
                iconEye.style.display = "none";
                iconEyeOff.style.display = "block";
            } else {
                input.type = "password";
                iconEye.style.display = "block";
                iconEyeOff.style.display = "none";
            }
        }
    </script>
    <?php endif; ?>
</div>
</body>
</html>
