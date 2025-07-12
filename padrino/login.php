<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = trim($_POST['correo']);
    $contrasena = trim($_POST['contrasena']);

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($contrasena, $user['contrasena'])) {
        $_SESSION['usuario'] = $user;
        if ($user['rol'] == 'admin') header("Location: admin/dashboard.php");
        elseif ($user['rol'] == 'trabajador') header("Location: trabajadores/dashboard.php");
        else header("Location: usuarios/dashboard.php");
        exit();
    } else {
        $error = "Correo o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#000000">

    <title>Login - El Padrino</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #0f0f0f; color: #f1c40f; font-family: 'Georgia', serif; }
        .container { max-width: 500px; margin-top: 50px; padding: 20px; }
        .btn-custom { background-color: #f1c40f; color: #0f0f0f; font-weight: bold; font-size: 1.2rem; padding: 15px; width: 100%; }
        .btn-custom:hover { background-color: #e67e22; }
        .form-control { background-color: #1c1c1c; border: 1px solid #f1c40f; color: #f1c40f; font-size: 1.1rem; padding: 15px; }
        .form-control::placeholder { color: #f1c40f; }
        .form-check-label { font-size: 1rem; color: #f1c40f; }
        .form-check-input:checked { background-color: #f1c40f; border-color: #f1c40f; }
        .error-msg { color: #e74c3c; text-align: center; margin-top: 10px; }
        .mb-3, .btn-custom { margin-bottom: 1.5rem; }
        @media (max-width: 768px) { .container { margin-top: 30px; } }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center">Iniciar Sesión</h2>
    <form method="post">
        <input type="email" name="correo" class="form-control mb-3" placeholder="Correo" required>
        <input type="password" name="contrasena" class="form-control mb-3" placeholder="Contraseña" required>

        <div class="form-check mb-2" style="font-size: 0.80rem;">
            <input class="form-check-input" type="checkbox" id="mostrarContrasena" onclick="togglePassword()" style="transform: scale(0.85); margin-right: 6px;">
            <label class="form-check-label" for="mostrarContrasena">Mostrar contraseña</label> 
            <a href="recuperar.php" class="text-decoration-none" style="font-size: 0.80rem; color: #3498db; margin-left: 12px;">
                ¿Olvidaste tu contraseña?
            </a>     
        </div>

        
        <button type="submit" class="btn btn-custom mb-3">Entrar</button>
        <a href="index.php" class="btn btn-outline-light w-100">Volver</a>
    </form>
    <?php if (!empty($error)) echo "<p class='error-msg'>" . htmlspecialchars($error) . "</p>"; ?>
</div>

<script>
function togglePassword() {
    const passwordField = document.querySelector('input[name="contrasena"]');
    passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
}
</script>

</body>
</html>
