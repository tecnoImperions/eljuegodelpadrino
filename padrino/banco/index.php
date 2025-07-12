<?php 
session_start();

if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
  header("Location: ../login.php");
  exit;
}

$usuario_id = $_SESSION['usuario']['id'];
$nombre_usuario = $_SESSION['usuario']['nombre'] ?? 'Usuario';

$conn = new mysqli("localhost:3307", "root", "", "padrino");
if ($conn->connect_error) die("Error DB: " . $conn->connect_error);

// Crear tabla cuentas si no existe
$conn->query("CREATE TABLE IF NOT EXISTS cuentas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL UNIQUE,
  saldo DECIMAL(10,2) DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Asegurar cuenta del usuario
$conn->query("INSERT IGNORE INTO cuentas (id_usuario) VALUES ($usuario_id)");

$saldo = 0;
$result = $conn->query("SELECT saldo FROM cuentas WHERE id_usuario = $usuario_id LIMIT 1");
if ($row = $result->fetch_assoc()) $saldo = $row['saldo'];

$historial = $conn->query("SELECT * FROM transacciones WHERE id_usuario = $usuario_id ORDER BY fecha DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Banco del Padrino</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      background: #121212;
      color: #f1f1f1;
      font-family: 'Oswald', sans-serif;
      overflow-x: hidden;
    }
    .card-saldo {
      background: linear-gradient(135deg, #000000, #222);
      border: 2px solid gold;
      box-shadow: 0 0 20px rgba(255, 215, 0, 0.2);
      color: gold;
      animation: fadeIn 1s ease-in-out;
    }
    .btn-square {
      width: 120px;
      height: 80px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      border-radius: 20px;
      font-size: 1rem;
      font-weight: bold;
      background: linear-gradient(135deg, #f1c40f, #e67e22);
      color: #000;
      box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3);
      transition: all 0.3s ease;
      border: none;
    }
    .btn-square:hover {
      background: linear-gradient(135deg, #f39c12, #d35400);
      color: #fff;
      transform: translateY(-4px) scale(1.05);
      box-shadow: 0 8px 20px rgba(243, 156, 18, 0.6);
    }
    .btn-square i {
      font-size: 1.6rem;
      margin-bottom: 6px;
    }
    .table-dark th, .table-dark td {
      color: #ffc107;
    }
    .footer-text {
      text-align: center;
      margin-top: 40px;
      color: #aaa;
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
<nav class="nav-bottom d-md-none">
  <a href="../usuarios/dashboard.php" class="nav-item"><i class="bi bi-speedometer2"></i><span>Inicio</span></a>
  <a href="banco.php" class="nav-item"><i class="bi bi-currency-dollar"></i><span>BCO</span></a>
  <a href="../usuarios/perfil.php" class="nav-item"><i class="bi bi-person-circle"></i><span>Perfil</span></a>
</nav>


<div class="container py-4">
  <h2 class="text-center mb-4">👋 Bienvenido, <?= htmlspecialchars($nombre_usuario) ?>!</h2>

  <div class="card card-saldo p-4 text-center mb-4">
    <h4 class="mb-2">💰 Saldo Disponible</h4>
    <h1>$<?= number_format($saldo, 2) ?></h1>
  </div>

  <div class="row g-3 justify-content-center mb-4">
    <!-- Botones que envían formulario -->
    <div class="col-4 d-flex justify-content-center">
      <a href="deposito.php" class="btn btn-square"><i class="bi bi-piggy-bank"></i>Depositar</a>
    </div>
    <div class="col-4 d-flex justify-content-center">
      <a href="retirar.php" class="btn btn-square"><i class="bi bi-bank"></i>Retirar</a>
    </div>
    <div class="col-4 d-flex justify-content-center">
      <a href="informacion.php" class="btn btn-square"><i class="bi bi-info-circle"></i>Información</a>
    </div>
    <div class="col-4 d-flex justify-content-center">
      <a href="pagar.php" class="btn btn-square"><i class="bi bi-credit-card"></i>Pagar</a>
    </div>
    <div class="col-4 d-flex justify-content-center">
      <a href="cobrar.php" class="btn btn-square"><i class="bi bi-cash-coin"></i>Cobrar</a>
    </div>
    <div class="col-4 d-flex justify-content-center">
      <a href="actualizar.php" class="btn btn-square"><i class="bi bi-arrow-repeat"></i>Actualizar</a>
    </div>


  <h5 class="text-center mb-3">📜 Historial Reciente</h5>
  <div class="table-responsive animate__animated animate__fadeInUp">
    <table class="table table-dark text-center table-bordered">
      <thead>
        <tr>
          <th>Tipo</th>
          <th>Monto</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($tx = $historial->fetch_assoc()): ?>
        <tr>
          <td><?= ucfirst($tx['tipo']) ?></td>
          <td>$<?= number_format($tx['monto'], 2) ?></td>
          <td><?= date("d/m/Y H:i", strtotime($tx['fecha'])) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <p class="footer-text">🕵️‍♂️ Todas las operaciones están bajo vigilancia del Don.</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
