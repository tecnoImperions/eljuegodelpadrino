<?php
session_start();

if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['admin', 'trabajador'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config.php';

// Control de acciones POST (bloquear, activar, expulsar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'], $_POST['id_usuario'])) {
        $id = (int)$_POST['id_usuario'];
        $es_sesion_actual = isset($_SESSION['usuario']) && $_SESSION['usuario']['id'] == $id;

        if ($_POST['accion'] === 'bloquear') {
            $conn->query("UPDATE usuarios SET estado = 'bloqueado' WHERE id = $id");
            if ($es_sesion_actual) {
                session_destroy();
                header("Location: ../login.php");
                exit;
            }
        } elseif ($_POST['accion'] === 'activar') {
            $conn->query("UPDATE usuarios SET estado = 'activo' WHERE id = $id");
        } elseif ($_POST['accion'] === 'expulsar') {
            $conn->query("DELETE FROM usuarios WHERE id = $id");
            if ($es_sesion_actual) {
                session_destroy();
                header("Location: ../login.php");
                exit;
            }
        }

        header("Location: usuarios.php");
        exit;
    }
}

// Filtros GET
$buscar = $_GET['buscar'] ?? '';
$plan = $_GET['plan'] ?? '';
$estado = $_GET['estado'] ?? '';

$query = "SELECT * FROM usuarios WHERE 1=1";
$params = [];
$types = '';

if (!empty($buscar)) {
    $query .= " AND (nombre LIKE ? OR correo LIKE ?)";
    $buscar_param = '%' . $buscar . '%';
    $params[] = $buscar_param;
    $params[] = $buscar_param;
    $types .= 'ss';
}
if (!empty($plan)) {
    $query .= " AND plan = ?";
    $params[] = $plan;
    $types .= 's';
}
if (!empty($estado)) {
    $query .= " AND estado = ?";
    $params[] = $estado;
    $types .= 's';
}

$query .= " LIMIT 100";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$usuarios = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>👥 Usuarios - El Padrino</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 50%, #111111 100%);
            min-height: 100vh;
        }
        
        .glass-effect {
            background: rgba(17, 17, 17, 0.85);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 204, 0, 0.15);
        }
        
        .header-glow {
            background: linear-gradient(135deg, #333333 0%, #1a1a1a 100%);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }
        
        .title-glow {
            text-shadow: 0 0 30px rgba(255, 204, 0, 0.6);
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
        
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .status-active {
            background: linear-gradient(45deg, #27ae60, #2ecc71);
        }
        
        .status-blocked {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
        }
        
        .plan-badge {
            background: rgba(255, 204, 0, 0.1);
            border: 1px solid rgba(255, 204, 0, 0.3);
        }
        
        .plan-pro {
            background: linear-gradient(45deg, #9b59b6, #8e44ad);
        }
        
        .plan-plus {
            background: linear-gradient(45deg, #f39c12, #e67e22);
        }
        
        .plan-gratuito {
            background: linear-gradient(45deg, #7f8c8d, #95a5a6);
        }
        
        .action-btn {
            transition: all 0.3s ease;
        }
        .action-btn:hover {
            transform: scale(1.05);
        }
        
        @media (max-width: 768px) {
            .user-card {
                display: block;
                background: rgba(26, 26, 26, 0.9);
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 16px;
                border: 1px solid rgba(255, 204, 0, 0.2);
                backdrop-filter: blur(10px);
            }
        }
    </style>
</head>
<body class="text-white font-sans">

    <!-- Header -->
    <header class="header-glow p-6 mb-8">
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-yellow-400 title-glow">
                <i class="fas fa-users mr-3"></i>El Padrino - Control de Usuarios
            </h1>
            <p class="text-gray-400 mt-2 text-sm sm:text-base">Gestión suprema de la familia</p>
        </div>
    </header>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">

        <!-- Filtros -->
        <div class="glass-effect rounded-xl p-4 sm:p-6 mb-6 lg:mb-8 shadow-2xl card-hover animate-fade-in">
            <form method="GET" class="space-y-4 lg:space-y-0 lg:flex lg:items-center lg:gap-4" autocomplete="off">
                
                <!-- Buscador -->
                <div class="flex-1">
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-yellow-500"></i>
                        <input type="text" 
                               name="buscar" 
                               placeholder="🔍 Buscar por nombre, correo o ID..." 
                               value="<?= htmlspecialchars($buscar) ?>"
                               class="w-full pl-12 pr-4 py-3 rounded-lg bg-gray-900/80 border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-300">
                    </div>
                </div>

                <!-- Filtros -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-2">
                    <select name="plan" class="px-4 py-3 bg-gray-900/80 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 transition-all duration-300">
                        <option value="">📋 Todos los planes</option>
                        <option value="gratuito" <?= $plan == 'gratuito' ? 'selected' : '' ?>>🆓 Gratuito</option>
                        <option value="plus" <?= $plan == 'plus' ? 'selected' : '' ?>>⭐ Plus</option>
                        <option value="pro" <?= $plan == 'pro' ? 'selected' : '' ?>>💎 Pro</option>
                    </select>

                    <select name="estado" class="px-4 py-3 bg-gray-900/80 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 transition-all duration-300">
                        <option value="">🔄 Todos los estados</option>
                        <option value="activo" <?= $estado == 'activo' ? 'selected' : '' ?>>✅ Activo</option>
                        <option value="bloqueado" <?= $estado == 'bloqueado' ? 'selected' : '' ?>>🚫 Bloqueado</option>
                    </select>

                    <button type="submit" class="col-span-1 sm:col-span-2 lg:col-span-1 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-black font-bold px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-filter mr-2"></i>Aplicar Filtros
                    </button>
                </div>
                
            </form>
        </div>

        <?php if (count($usuarios) > 0): ?>
            
            <!-- Vista Desktop - Tabla -->
            <div class="hidden lg:block glass-effect rounded-xl shadow-2xl overflow-hidden card-hover animate-fade-in">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-white">
                        <thead class="bg-gradient-to-r from-gray-800 to-gray-700 text-yellow-300">
                            <tr>
                                <th class="px-6 py-4 text-left font-bold"> ID</th>
                                <th class="px-6 py-4 text-left font-bold"> Nombre</th>
                                <th class="px-6 py-4 text-left font-bold"> Correo</th>
                                <th class="px-6 py-4 text-left font-bold"> Plan</th>
                                <th class="px-6 py-4 text-left font-bold"> Estado</th>
                                <th class="px-6 py-4 text-left font-bold"> Participaciones</th>
                                <th class="px-6 py-4 text-left font-bold"> Ver</th>
                                <th class="px-6 py-4 text-left font-bold"> Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr class="hover:bg-gray-700/50 transition-all duration-300">
                                    <td class="px-6 py-4 font-mono text-yellow-400"><?= $usuario['id'] ?></td>
                                    <td class="px-6 py-4 font-medium"><?= htmlspecialchars($usuario['nombre']) ?></td>
                                    <td class="px-6 py-4 text-gray-300"><?= htmlspecialchars($usuario['correo']) ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                                            <?= $usuario['plan'] === 'pro' ? 'plan-pro text-white' : 
                                                ($usuario['plan'] === 'plus' ? 'plan-plus text-white' : 'plan-gratuito text-white') ?>">
                                            <?= $usuario['plan'] === 'pro' ? '💎 Pro' : 
                                                ($usuario['plan'] === 'plus' ? '⭐ Plus' : ' Gratuito') ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold text-white
                                            <?= $usuario['estado'] === 'activo' ? 'status-active' : 'status-blocked' ?>">
                                            <?= $usuario['estado'] === 'activo' ? ' Activo' : '🚫 Bloqueado' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php
                                        $q = $conn->prepare("SELECT COUNT(*) FROM participaciones WHERE id_usuario = ?");
                                        $q->bind_param("i", $usuario['id']);
                                        $q->execute();
                                        $q->bind_result($total);
                                        $q->fetch();
                                        $q->close();
                                        ?>
                                        <span class="bg-gray-700 px-3 py-1 rounded-full font-bold text-yellow-400">
                                            <?= $total ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="usuario_detalle.php?id=<?= $usuario['id'] ?>" 
                                           class="inline-flex items-center justify-center w-10 h-10 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all duration-300 transform hover:scale-110"
                                           title="Ver detalles del usuario">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2">
                                            <!-- Bloquear / Activar -->
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="id_usuario" value="<?= $usuario['id'] ?>">
                                                <input type="hidden" name="accion" value="<?= $usuario['estado'] === 'activo' ? 'bloquear' : 'activar' ?>">
                                                <button type="submit" 
                                                        class="action-btn px-3 py-2 rounded-lg text-xs font-bold text-white transition-all duration-300 
                                                               <?= $usuario['estado'] === 'activo' ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700' ?>"
                                                        title="<?= $usuario['estado'] === 'activo' ? 'Bloquear usuario' : 'Desbloquear usuario' ?>">
                                                    <?= $usuario['estado'] === 'activo' ? '🚫 Bloquear' : '✅ Activar' ?>
                                                </button>
                                            </form>
                                            
                                            <!-- Expulsar -->
                                            <form method="POST" class="inline" onsubmit="return confirm('⚠️ ¿Seguro que deseas expulsar a este usuario? Esta acción es IRREVERSIBLE.');">
                                                <input type="hidden" name="id_usuario" value="<?= $usuario['id'] ?>">
                                                <input type="hidden" name="accion" value="expulsar">
                                                <button type="submit" 
                                                        class="action-btn px-3 py-2 rounded-lg text-xs font-bold bg-red-600 hover:bg-red-700 text-white transition-all duration-300"
                                                        title="Expulsar usuario permanentemente">
                                                    💀 Expulsar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Vista Mobile - Cards -->
            <div class="lg:hidden space-y-4 animate-fade-in">
                <?php foreach ($usuarios as $usuario): ?>
                    <div class="user-card card-hover">
                        <!-- Header del usuario -->
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-yellow-400 mb-1">
                                    <?= htmlspecialchars($usuario['nombre']) ?>
                                </h3>
                                <p class="text-gray-400 text-sm">ID: <?= $usuario['id'] ?></p>
                                <p class="text-gray-300 text-sm mt-1"><?= htmlspecialchars($usuario['correo']) ?></p>
                            </div>
                            
                            <!-- Estados y Plan -->
                            <div class="flex flex-col gap-2 items-end">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold text-white
                                    <?= $usuario['plan'] === 'pro' ? 'plan-pro' : 
                                        ($usuario['plan'] === 'plus' ? 'plan-plus' : 'plan-gratuito') ?>">
                                    <?= $usuario['plan'] === 'pro' ? '💎 Pro' : 
                                        ($usuario['plan'] === 'plus' ? '⭐ Plus' : '🆓 Gratuito') ?>
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold text-white
                                    <?= $usuario['estado'] === 'activo' ? 'status-active' : 'status-blocked' ?>">
                                    <?= $usuario['estado'] === 'activo' ? '✅ Activo' : '🚫 Bloqueado' ?>
                                </span>
                            </div>
                        </div>

                        <!-- Información adicional -->
                        <div class="grid grid-cols-2 gap-4 mb-4 p-4 bg-black/30 rounded-lg">
                            <div class="text-center">
                                <p class="text-yellow-400 font-bold text-sm">🎯 Participaciones</p>
                                <p class="text-2xl font-bold text-white">
                                    <?php
                                    $q = $conn->prepare("SELECT COUNT(*) FROM participaciones WHERE id_usuario = ?");
                                    $q->bind_param("i", $usuario['id']);
                                    $q->execute();
                                    $q->bind_result($total);
                                    $q->fetch();
                                    $q->close();
                                    echo $total;
                                    ?>
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-yellow-400 font-bold text-sm">👁️ Ver Detalles</p>
                                <a href="usuario_detalle.php?id=<?= $usuario['id'] ?>" 
                                   class="inline-flex items-center justify-center w-12 h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all duration-300 transform hover:scale-110 mt-1">
                                    <i class="fas fa-eye text-lg"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="flex gap-2">
                            <!-- Bloquear / Activar -->
                            <form method="POST" class="flex-1">
                                <input type="hidden" name="id_usuario" value="<?= $usuario['id'] ?>">
                                <input type="hidden" name="accion" value="<?= $usuario['estado'] === 'activo' ? 'bloquear' : 'activar' ?>">
                                <button type="submit" 
                                        class="w-full action-btn py-3 rounded-lg font-bold text-white transition-all duration-300 
                                               <?= $usuario['estado'] === 'activo' ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700' ?>">
                                    <?= $usuario['estado'] === 'activo' ? '🚫 Bloquear' : '✅ Activar' ?>
                                </button>
                            </form>
                            
                            <!-- Expulsar -->
                            <form method="POST" class="flex-1" onsubmit="return confirm('⚠️ ¿Seguro que deseas expulsar a este usuario? Esta acción es IRREVERSIBLE.');">
                                <input type="hidden" name="id_usuario" value="<?= $usuario['id'] ?>">
                                <input type="hidden" name="accion" value="expulsar">
                                <button type="submit" 
                                        class="w-full action-btn py-3 rounded-lg font-bold bg-red-600 hover:bg-red-700 text-white transition-all duration-300">
                                    💀 Expulsar
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="glass-effect rounded-xl p-12 text-center card-hover animate-fade-in">
                <div class="text-6xl mb-4">🔍</div>
                <h3 class="text-2xl font-bold text-yellow-400 mb-2">No se encontraron usuarios</h3>
                <p class="text-gray-400">Intenta ajustar los filtros de búsqueda</p>
            </div>
        <?php endif; ?>

        <!-- Botón Volver -->
        <div class="mt-8 lg:mt-12">
            <a href="javascript:history.back()" class="btn-volver">
                <i class="fas fa-arrow-left mr-2"></i>Volver al Panel Principal
            </a>
        </div>

        <!-- Footer -->
        <footer class="text-center text-gray-500 mt-12 pb-8">
            <div class="glass-effect rounded-lg p-4 inline-block">
                <p class="text-sm lg:text-base italic">
                    👑 El Padrino controla su familia &copy; <?= date('Y') ?>
                </p>
                <p class="text-xs text-gray-600 mt-1">
                    "La lealtad se gana, el respeto se impone"
                </p>
            </div>
        </footer>

    </div>

</body>
</html>