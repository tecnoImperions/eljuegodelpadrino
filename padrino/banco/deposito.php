<?php 
session_start();

// Seguridad: Evitar acceso sin sesión
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
  header("Location: ../login.php");
  exit;
}

$usuario_id = $_SESSION['usuario']['id'];
$nombre_usuario = $_SESSION['usuario']['nombre'] ?? 'Usuario';

// Conexión a la base de datos
$conn = new mysqli("localhost:3307", "root", "", "padrino");
if ($conn->connect_error) die("Error DB: " . $conn->connect_error);

// Asegurar que exista la tabla de cuentas
$conn->query("CREATE TABLE IF NOT EXISTS cuentas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL UNIQUE,
  saldo DECIMAL(10,2) DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Asegurar cuenta del usuario
$conn->query("INSERT IGNORE INTO cuentas (id_usuario) VALUES ($usuario_id)");

$mensaje = "";
$tipo_mensaje = "warning";

// Protección simple CSRF
if (empty($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Validar token
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['token']) {
    $mensaje = "❌ Solicitud inválida.";
    $tipo_mensaje = "danger";
  } else {
    $monto = floatval($_POST['monto'] ?? 0);

    // ⚠️ Validaciones contra trampas
    if (!is_numeric($_POST['monto']) || $monto <= 0) {
      $mensaje = "⚠️ Monto inválido.";
    } elseif ($monto > 10000) {
      $mensaje = "⚠️ No puedes depositar más de $10,000 por vez.";
    } else {
      // Evitar doble depósito por recarga rápida (simple protección)
      if (!isset($_SESSION['ultimo_deposito']) || time() - $_SESSION['ultimo_deposito'] > 5) {
        // Actualizar saldo
        $conn->query("UPDATE cuentas SET saldo = saldo + $monto WHERE id_usuario = $usuario_id");

        // Guardar transacción
        $stmt = $conn->prepare("INSERT INTO transacciones (id_usuario, tipo, monto) VALUES (?, 'depositar', ?)");
        $stmt->bind_param("id", $usuario_id, $monto);
        $stmt->execute();

        $_SESSION['ultimo_deposito'] = time();
        $mensaje = "✅ Depósito de $" . number_format($monto, 2) . " realizado correctamente.";
        $tipo_mensaje = "success";
      } else {
        $mensaje = "⚠️ Espera unos segundos antes de intentar otro depósito.";
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Depositar | Banco del Padrino</title>
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
      box-shadow: 0 -2px 10px rgba(255, 215, 0, 0.1);
    }

    .nav-bottom .nav-item {
      color: gold;
      text-align: center;
      flex-grow: 1;
      font-size: 0.85rem;
      text-decoration: none;
      transition: all 0.3s ease;
      border-radius: 10px;
      margin: 0 5px;
    }

    .nav-bottom .nav-item i {
      font-size: 1.3rem;
      display: block;
    }

    .nav-bottom .nav-item:hover {
      background: gold;
      color: black;
      transform: translateY(-3px);
    }
  </style>
</head>
<body>
<div class="container py-4">
  <h2 class="text-center mb-3">💸 Depósito Seguro</h2>
  <p class="text-center">Hola <strong><?= htmlspecialchars($nombre_usuario) ?></strong>, escanea el código QR y confirma el monto enviado.</p>

  <div class="text-center mb-4">
    <img src="../assets/img/qr.jpg" alt="Código QR de depósito" class="img-qr">
  </div>

  <?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipo_mensaje ?> text-center"><?= $mensaje ?></div>
  <?php endif; ?>

  <form method="POST" class="text-center">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['token'] ?>">
    <div class="mb-3">
      <label for="monto" class="form-label">💵 Monto en Bs</label>
      <input type="number" step="0.01" min="0.01" max="10000" name="monto" id="monto" class="form-control text-center mx-auto" style="max-width: 300px;" required>
    </div>
    <button type="submit" class="btn btn-gold px-4 py-2">✅ Confirmar Depósito</button>
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
