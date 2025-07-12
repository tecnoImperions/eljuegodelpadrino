<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    header("Location: login.php");
    exit;
}

// Incluir la librería de generación de QR
require_once 'vendor/autoload.php';

use Emizorip\Payment\Qr\BCP\QrBCP;

// Obtener el ID del usuario y su saldo
$usuario_id = $_SESSION['usuario']['id'];
$saldo = obtenerSaldoUsuario($usuario_id); // Implementa esta función según tu base de datos

// Configurar los parámetros para la generación del QR
$transaction_id = uniqid('txn_');
$currency = 'BOB';
$amount = 100.00; // Monto a cobrar
$gloss = 'Pago por servicios';
$expiration = '2025-06-30T23:59:59';

// Crear una instancia de la clase QrBCP
$qr = new QrBCP([
    'username' => 'tu_username',
    'password' => 'tu_password',
    'appUserId' => 'tu_appUserId',
    'certificate' => 'ruta/a/tu/certificado.pfx',
    'certificate_password' => 'tu_certificado_password',
]);

// Generar el código QR
$response = $qr->generate([
    'transaction_id' => $transaction_id,
    'currency' => $currency,
    'amount' => $amount,
    'gloss' => $gloss,
    'expiration' => $expiration,
]);

if ($response['status'] == '00') {
    $qr_code = $response['qr_code'];
    $qr_url = $response['qr_url'];
} else {
    $error_message = 'Error al generar el código QR: ' . $response['message'];
}

// Función para obtener el saldo del usuario desde la base de datos
function obtenerSaldoUsuario($id) {
    global $conn;
    $result = $conn->query("SELECT saldo FROM cuentas WHERE id_usuario = $id");
    $row = $result->fetch_assoc();
    return $row['saldo'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cobro con QR - Banco del Padrino</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
    </style>
</head>
<body>
    <div class="container py-4">
        <h2 class="text-center mb-3">💰 Cobro con Código QR</h2>
        <p class="text-center">Hola <strong><?= htmlspecialchars($nombre_usuario) ?></strong>, escanea el código QR para realizar el pago de Bs <?= number_format($amount, 2) ?>.</p>

        <div class="text-center mb-4">
            <img src="<?= $qr_url ?>" alt="Código QR de pago" class="img-qr">
        </div>

        <form method="POST" class="text-center">
            <div class="mb-3">
                <label for="monto" class="form-label">💵 Monto a cobrar</label>
                <input type="number" step="0.01" min="0.01" max="10000" name="monto" id="monto" class="form-control text-center mx-auto" style="max-width: 300px;" required>
            </div>
            <button type="submit" class="btn btn-gold px-4 py-2">✅ Confirmar Cobro</button>
            <br>
            <a href="index.php" class="btn btn-outline-light mt-3">⬅️ Volver</a>
        </form>
    </div>
</body>
</html>
