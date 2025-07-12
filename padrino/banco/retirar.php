<?php
session_start();

// Seguridad: solo usuarios logueados
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    header("Location: ../login.php");
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];
$nombre_usuario = $_SESSION['usuario']['nombre'] ?? 'Usuario';

// Conexión a la base de datos
$conn = new mysqli("localhost:3307", "root", "", "padrino");
if ($conn->connect_error) die("Error DB: " . $conn->connect_error);

// Crear la tabla de cuentas si no existe
$conn->query("CREATE TABLE IF NOT EXISTS cuentas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL UNIQUE,
    saldo DECIMAL(10,2) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Asegurar cuenta del usuario
$conn->query("INSERT IGNORE INTO cuentas (id_usuario) VALUES ($usuario_id)");

// Obtener saldo
$saldo = 0;
$res = $conn->query("SELECT saldo FROM cuentas WHERE id_usuario = $usuario_id LIMIT 1");
if ($row = $res->fetch_assoc()) $saldo = $row['saldo'];

$mensaje = "";
$tipo_mensaje = "danger";

// Protección CSRF simple
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['csrf_token'] !== $_SESSION['token']) {
        $mensaje = "❌ Solicitud inválida.";
    } else {
        $monto = floatval($_POST['monto'] ?? 0);

        if (!is_numeric($_POST['monto']) || $monto <= 0) {
            $mensaje = "⚠️ Monto inválido.";
        } elseif ($monto > $saldo) {
            $mensaje = "❌ No tienes suficiente saldo, Don.";
        } elseif ($monto > 10000) {
            $mensaje = "⚠️ Máximo permitido por retiro es Bs 10,000.";
        } else {
            $conn->query("UPDATE cuentas SET saldo = saldo - $monto WHERE id_usuario = $usuario_id");

            $stmt = $conn->prepare("INSERT INTO transacciones (id_usuario, tipo, monto) VALUES (?, 'retirar', ?)");
            $stmt->bind_param("id", $usuario_id, $monto);
            $stmt->execute();

            $mensaje = "✅ Has retirado Bs " . number_format($monto, 2) . ". Un sicario ya lo lleva en camino.";
            $tipo_mensaje = "success";
            $saldo -= $monto;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Retirar | Banco del Padrino</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
      border: 2px solid crimson;
      box-shadow: 0 0 15px rgba(220, 20, 60, 0.2);
    }
    .btn-red {
      background: crimson;
      color: white;
      font-weight: bold;
      border: none;
    }
    .btn-red:hover {
      background: #a60000;
    }
    .nav-bottom {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: rgba(0, 0, 0, 0.9);
      border-top: 1px solid crimson;
      display: flex;
      justify-content: space-around;
      padding: 0.5rem 0;
      z-index: 1030;
    }
    .nav-bottom .nav-item {
      color: crimson;
      text-align: center;
      flex-grow: 1;
      font-size: 0.85rem;
      text-decoration: none;
    }
    .nav-bottom .nav-item:hover {
      background: crimson;
      color: white;
      transform: translateY(-3px);
    }
    .nav-bottom .nav-item i {
      font-size: 1.4rem;
    }
  </style>
</head>
<body>

<div class="container py-4">
  <h2 class="text-center mb-3">💼 Retirar Fondos</h2>
  <p class="text-center">Don <strong><?= htmlspecialchars($nombre_usuario) ?></strong>, tienes Bs <strong><?= number_format($saldo, 2) ?></strong> disponibles.</p>

  <?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipo_mensaje ?> text-center"><?= $mensaje ?></div>
  <?php endif; ?>

  <form method="POST" class="text-center">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['token'] ?>">
    <div class="mb-3">
      <label for="monto" class="form-label">💵 ¿Cuánto deseas retirar?</label>
      <input type="number" step="0.01" min="0.01" max="<?= $saldo ?>" name="monto" id="monto" class="form-control text-center mx-auto" style="max-width: 300px;" required>
    </div>
    <button type="submit" class="btn btn-red px-4 py-2">🚗 Enviar el dinero</button>
    <br>
    <a href="banco.php" class="btn btn-outline-light mt-3">⬅️ Volver</a>
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
