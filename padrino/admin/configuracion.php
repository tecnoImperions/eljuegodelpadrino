<?php
require_once __DIR__ . '/../config.php';

// ⚙️ Procesar cambios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario_id'])) {
    $id = intval($_POST['usuario_id']);
    $rol = $_POST['rol'] ?? '';
    $plan = $_POST['plan'] ?? '';

    $stmt = $conn->prepare("UPDATE usuarios SET rol = ?, plan = ? WHERE id = ?");
    $stmt->bind_param("ssi", $rol, $plan, $id);
    $stmt->execute();
    header("Location: configuracion.php");
    exit;
}

// 🔍 Filtros
$busqueda = $_GET['busqueda'] ?? '';
$filtro_rol = $_GET['rol'] ?? '';
$filtro_plan = $_GET['plan'] ?? '';

$sql = "SELECT * FROM usuarios WHERE 1=1";

if ($busqueda !== '') {
    $b = '%' . $conn->real_escape_string($busqueda) . '%';
    $sql .= " AND (nombre LIKE '$b' OR correo LIKE '$b' OR id LIKE '$b')";
}
if ($filtro_rol !== '') $sql .= " AND rol = '" . $conn->real_escape_string($filtro_rol) . "'";
if ($filtro_plan !== '') $sql .= " AND plan = '" . $conn->real_escape_string($filtro_plan) . "'";

$sql .= " ORDER BY fecha_registro ASC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>👑 Panel del Don</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: linear-gradient(135deg, #1f1f1f 0%, #111111 50%, #0f0f0f 100%);
      min-height: 100vh;
    }
    
    .glass-effect {
      background: rgba(31, 31, 31, 0.8);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 204, 0, 0.1);
    }
    
    .btn-volver {
      display: inline-block;
      margin: 20px 0;
      background: linear-gradient(45deg, #444, #666);
      color: #ffcc00;
      padding: 12px 24px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    }
    .btn-volver:hover {
      background: linear-gradient(45deg, #666, #888);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    }
    
    .card-hover {
      transition: all 0.3s ease;
    }
    .card-hover:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }
    
    .title-glow {
      text-shadow: 0 0 20px rgba(255, 204, 0, 0.5);
    }
    
    @media (max-width: 768px) {
      .table-card {
        display: block;
        background: rgba(31, 31, 31, 0.9);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
        border: 1px solid rgba(255, 204, 0, 0.2);
      }
      
      .table-row {
        display: block !important;
        margin-bottom: 8px;
      }
      
      .table-cell {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      }
      
      .table-cell:last-child {
        border-bottom: none;
      }
      
      .table-cell::before {
        content: attr(data-label);
        font-weight: bold;
        color: #ffcc00;
        flex-shrink: 0;
        width: 80px;
      }
    }
    
    .animate-fade-in {
      animation: fadeIn 0.6s ease-out;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body class="text-white font-sans">

  <div class="min-h-screen">
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
      
      <!-- Header -->
      <div class="text-center mb-8 lg:mb-12 animate-fade-in">
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-yellow-400 title-glow mb-4">
          💼 El Don Controla Todo
        </h1>
        <p class="text-gray-400 text-sm sm:text-base">Panel de administración supremo</p>
      </div>

      <!-- 🔍 Buscador y Filtros -->
      <div class="glass-effect rounded-xl p-4 sm:p-6 mb-6 lg:mb-8 shadow-2xl card-hover animate-fade-in">
        <form method="GET" class="space-y-4 lg:space-y-0 lg:flex lg:items-center lg:gap-4">
          
          <!-- Buscador -->
          <div class="flex-1">
            <input name="busqueda" 
                   value="<?= htmlspecialchars($busqueda) ?>" 
                   placeholder="🔎 Buscar por nombre, correo o ID"
                   class="w-full px-4 py-3 rounded-lg bg-gray-900/80 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-300 text-white placeholder-gray-400" />
          </div>

          <!-- Filtros -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-2">
            <select name="rol" class="px-4 py-3 bg-gray-900/80 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 transition-all duration-300">
              <option value="">🎭 Todos los roles</option>
              <option value="usuario" <?= $filtro_rol === 'usuario' ? 'selected' : '' ?>>👤 Usuario</option>
              <option value="admin" <?= $filtro_rol === 'admin' ? 'selected' : '' ?>>👑 Admin</option>
              <option value="trabajador" <?= $filtro_rol === 'trabajador' ? 'selected' : '' ?>>🔧 Trabajador</option>
            </select>

            <select name="plan" class="px-4 py-3 bg-gray-900/80 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 transition-all duration-300">
              <option value="">📋 Todos los planes</option>
              <option value="gratuito" <?= $filtro_plan === 'gratuito' ? 'selected' : '' ?>>🆓 Gratuito</option>
              <option value="plus" <?= $filtro_plan === 'plus' ? 'selected' : '' ?>>⭐ Plus</option>
              <option value="pro" <?= $filtro_plan === 'pro' ? 'selected' : '' ?>>💎 Pro</option>
            </select>

            <button type="submit" class="col-span-1 sm:col-span-2 lg:col-span-1 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-black font-bold px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform hover:scale-105">
              🔍 Aplicar Filtros
            </button>
          </div>
          
        </form>
      </div>

      <!-- 📋 Tabla de usuarios - Desktop -->
      <div class="hidden lg:block glass-effect rounded-xl shadow-2xl overflow-hidden card-hover animate-fade-in">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-white">
            <thead class="bg-gradient-to-r from-gray-800 to-gray-700 text-yellow-300">
              <tr>
                <th class="px-6 py-4 text-left font-bold">🆔 ID</th>
                <th class="px-6 py-4 text-left font-bold">👤 Nombre</th>
                <th class="px-6 py-4 text-left font-bold">📧 Correo</th>
                <th class="px-6 py-4 text-left font-bold">🎭 Rol</th>
                <th class="px-6 py-4 text-left font-bold">📋 Plan</th>
                <th class="px-6 py-4 text-left font-bold">⚙️ Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
              <?php while ($usuario = $resultado->fetch_assoc()): ?>
                <tr class="hover:bg-gray-700/50 transition-all duration-300">
                  <td class="px-6 py-4 font-mono text-yellow-400"><?= $usuario['id'] ?></td>
                  <td class="px-6 py-4 font-medium"><?= htmlspecialchars($usuario['nombre']) ?></td>
                  <td class="px-6 py-4 text-gray-300"><?= htmlspecialchars($usuario['correo']) ?></td>
                  <td class="px-6 py-4">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                      <?= $usuario['rol'] === 'admin' ? 'bg-red-600 text-white' : 
                          ($usuario['rol'] === 'trabajador' ? 'bg-blue-600 text-white' : 'bg-gray-600 text-white') ?>">
                      <?= ucfirst($usuario['rol']) ?>
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                      <?= $usuario['plan'] === 'pro' ? 'bg-purple-600 text-white' : 
                          ($usuario['plan'] === 'plus' ? 'bg-yellow-600 text-black' : 'bg-gray-600 text-white') ?>">
                      <?= ucfirst($usuario['plan']) ?>
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <form method="POST" class="flex gap-2 items-center">
                      <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                      <select name="rol" class="bg-gray-900/80 border border-gray-600 px-3 py-2 rounded-lg text-white text-xs focus:outline-none focus:ring-1 focus:ring-yellow-500">
                        <option value="usuario" <?= $usuario['rol'] === 'usuario' ? 'selected' : '' ?>>Usuario</option>
                        <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="trabajador" <?= $usuario['rol'] === 'trabajador' ? 'selected' : '' ?>>Trabajador</option>
                      </select>
                      <select name="plan" class="bg-gray-900/80 border border-gray-600 px-3 py-2 rounded-lg text-white text-xs focus:outline-none focus:ring-1 focus:ring-yellow-500">
                        <option value="gratuito" <?= $usuario['plan'] === 'gratuito' ? 'selected' : '' ?>>Gratuito</option>
                        <option value="plus" <?= $usuario['plan'] === 'plus' ? 'selected' : '' ?>>Plus</option>
                        <option value="pro" <?= $usuario['plan'] === 'pro' ? 'selected' : '' ?>>Pro</option>
                      </select>
                      <button type="submit" class="bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-black font-bold px-4 py-2 rounded-lg shadow transition-all duration-300 transform hover:scale-105 text-xs">
                        💾 Guardar
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 📋 Cards de usuarios - Mobile -->
      <div class="lg:hidden space-y-4 animate-fade-in">
        <?php 
        // Reset the result pointer for mobile view
        $resultado->data_seek(0);
        while ($usuario = $resultado->fetch_assoc()): 
        ?>
          <div class="table-card card-hover">
            <div class="flex justify-between items-start mb-4">
              <div>
                <h3 class="text-lg font-bold text-yellow-400"><?= htmlspecialchars($usuario['nombre']) ?></h3>
                <p class="text-gray-400 text-sm">ID: <?= $usuario['id'] ?></p>
              </div>
              <div class="flex flex-col gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-semibold
                  <?= $usuario['rol'] === 'admin' ? 'bg-red-600 text-white' : 
                      ($usuario['rol'] === 'trabajador' ? 'bg-blue-600 text-white' : 'bg-gray-600 text-white') ?>">
                  <?= ucfirst($usuario['rol']) ?>
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold
                  <?= $usuario['plan'] === 'pro' ? 'bg-purple-600 text-white' : 
                      ($usuario['plan'] === 'plus' ? 'bg-yellow-600 text-black' : 'bg-gray-600 text-white') ?>">
                  <?= ucfirst($usuario['plan']) ?>
                </span>
              </div>
            </div>
            
            <div class="table-cell" data-label="📧 Correo:">
              <span class="text-gray-300"><?= htmlspecialchars($usuario['correo']) ?></span>
            </div>
            
            <div class="mt-4 pt-4 border-t border-gray-700">
              <form method="POST" class="space-y-3">
                <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-yellow-400 text-sm font-bold mb-1">🎭 Rol</label>
                    <select name="rol" class="w-full bg-gray-900/80 border border-gray-600 px-3 py-2 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500">
                      <option value="usuario" <?= $usuario['rol'] === 'usuario' ? 'selected' : '' ?>>Usuario</option>
                      <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                      <option value="trabajador" <?= $usuario['rol'] === 'trabajador' ? 'selected' : '' ?>>Trabajador</option>
                    </select>
                  </div>
                  
                  <div>
                    <label class="block text-yellow-400 text-sm font-bold mb-1">📋 Plan</label>
                    <select name="plan" class="w-full bg-gray-900/80 border border-gray-600 px-3 py-2 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500">
                      <option value="gratuito" <?= $usuario['plan'] === 'gratuito' ? 'selected' : '' ?>>Gratuito</option>
                      <option value="plus" <?= $usuario['plan'] === 'plus' ? 'selected' : '' ?>>Plus</option>
                      <option value="pro" <?= $usuario['plan'] === 'pro' ? 'selected' : '' ?>>Pro</option>
                    </select>
                  </div>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-black font-bold px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform hover:scale-105">
                  💾 Guardar Cambios
                </button>
              </form>
            </div>
          </div>
        <?php endwhile; ?>
      </div>

      <!-- Botón Volver -->
      <div class="mt-8 lg:mt-12">
        <a href="javascript:history.back()" class="btn-volver">
          🔙 Volver 
        </a>
      </div>

      <!-- Footer -->
      <footer class="text-center text-gray-500 mt-12 pb-8">
        <div class="glass-effect rounded-lg p-4 inline-block">
          <p class="text-sm lg:text-base italic">
            ⚖️ Poder absoluto del Don &copy; <?= date('Y') ?>
          </p>
          <p class="text-xs text-gray-600 mt-1">
            "El control total está en tus manos"
          </p>
        </div>
      </footer>
      
    </div>
  </div>

</body>
</html>