<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $correo = filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL);
    $celular = htmlspecialchars(trim($_POST['celular']));
    $contrasena_plana = trim($_POST['contrasena']);

    // Validar que no exista el correo
    $check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $check->bind_param("s", $correo);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "El correo ya está registrado.";
    } else {
        $contrasena = password_hash($contrasena_plana, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, contrasena, celular) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $correo, $contrasena, $celular);

        if ($stmt->execute()) {
            header("Location: login.php?registro=exito");
            exit();
        } else {
            $error = "Error al registrar: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta name="theme-color" content="#000000">
    <title>Registro - El Padrino</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #1c1c1c; color: #f1c40f; font-family: 'Georgia', serif; }
        .container { max-width: 500px; margin-top: 50px; padding: 20px; }
        .btn-custom { background-color: #f1c40f; color: #0f0f0f; font-weight: bold; font-size: 1.2rem; padding: 15px; width: 100%; }
        .btn-custom:hover { background-color: #e67e22; }
        .form-control { background-color: #333; border: 1px solid #f1c40f; color: #f1c40f; font-size: 1.1rem; padding: 15px; }
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
    <h2 class="text-center">Registro de Usuario</h2>
    <form method="post">
        <input name="nombre" class="form-control mb-3" placeholder="Nombre completo" required>
        <input type="email" name="correo" class="form-control mb-3" placeholder="Correo" required>
        <input name="celular" class="form-control mb-3" placeholder="Celular" required>
        <input type="password" name="contrasena" class="form-control mb-3" placeholder="Contraseña" required>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="mostrarContrasena" onclick="togglePassword()">
            <label class="form-check-label" for="mostrarContrasena">Mostrar contraseña</label>
        </div>

        <button type="submit" class="btn btn-custom mb-3">Registrarse</button>
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
