<?php
session_start();

// Seguridad: Verificar sesión
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    header("Location: ../login.php");
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];
$nombre_usuario = $_SESSION['usuario']['nombre'] ?? 'Usuario';

// Conexión a la base de datos
$conn = new mysqli("localhost:3307", "root", "", "padrino");
if ($conn->connect_error) die("Error DB: " . $conn->connect_error);

// Obtener saldo del usuario
$result = $conn->query("SELECT saldo FROM cuentas WHERE id_usuario = $usuario_id");
$saldo = $result->fetch_assoc()['saldo'] ?? 0;

// Generar código QR para el pago
$qr_data = "pagar.php?usuario_id=$usuario_id&saldo=$saldo";
$qr_image = generate_qr_code($qr_data);

function generate_qr_code($data) {
    // Implementar la generación del código QR aquí
    // Puedes usar una librería como PHP QR Code o similar
    return 'ruta/a/tu/qr.png';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pago | Banco del Padrino</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #121212;
            color: #f1f1f1;
            font-family: 'Arial', sans-serif;
            padding-bottom: 80px;
        }
        .card-custom {
            background-color: #1e1e1e;
            border: 2px solid gold;
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.2);
        }
        .btn-gold {
            background: gold;
            color: black;
            font-weight: bold;
            border: none;
        }
        .btn-gold:hover {
            background: #f1c40f;
        }
        .img-qr {
            max-width: 100%;
            height: auto;
            border: 4px solid gold;
            padding: 10px;
            border-radius: 10px;
        }
        .nav-bottom {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.9);
            border-top: 1px solid gold;
            display: flex;
            justify-content: space-around;
            padding: 0.5rem 0;
            z-index: 1030;
        }
        .nav-bottom .nav-item {
            color: gold;
            text-align: center;
            flex-grow: 1;
            font-size: 0.85rem;
            text-decoration: none;
        }
        .nav-bottom .nav-item:hover {
            background: gold;
            color: black;
            transform: translateY(-3px);
        }
        .nav-bottom .nav-item i {
            display: block;
            font-size: 1.4rem;
        }
    </style>
</head>
<body>
<div class="container py-4">
    <h2 class="text-center mb-3">💸 Pago Seguro</h2>
    <p class="text-center">Hola <strong><?= htmlspecialchars($nombre_usuario) ?></strong>, escanea el código QR y confirma el monto a pagar.</p>

    <div class="text-center mb-4">
        <img src="<?= $qr_image ?>" alt="Código QR de pago" class="img-qr">
    </div>

    <form method="POST" class="text-center">
        <div class="mb-3">
            <label for="monto" class="form-label">💵 Monto a Pagar (Bs)</label>
            <input type="number" step="0.01" min="0.01" max="<?= $saldo ?>" name="monto" id="monto" class="form-control text-center mx-auto" style="max-width: 300px;" required>
        </div>
        <button type="submit" class="btn btn-gold px-4 py-2">✅ Confirmar Pago</button>
        <br>
        <a href="index.php" class="btn btn-outline-light mt-3">⬅️ Volver</a>
    </form>
</div>

<nav class="nav-bottom d-md-none">
    <a href="../usuarios/dashboard.php" class="nav-item"><i class="bi bi-speedometer2"></i><span>Inicio</span></a>
    <a href="banco.php" class="nav-item"><i class="bi bi-currency-dollar"></i><span>BCO</span></a>
    <a href="../usuarios/perfil.php" class="nav-item"><i class="bi bi-person-circle"></i><span>Perfil</span></a>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
