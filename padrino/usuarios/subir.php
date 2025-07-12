<?php 
session_start();
require_once '../config.php';

if (!isset($_SESSION['usuario'])) {
    // Si prefieres redirigir al login
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario']['id'];
$id_sorteo = intval($_POST['id_sorteo'] ?? 0);
$lugar = $_POST['lugar'] ?? '';

if ($id_sorteo <= 0 || !in_array($lugar, ['primer', 'segundo', 'tercer'])) {
    // Podrías redirigir o mostrar error
    echo "❌ Datos inválidos.";
    exit;
}

if (!isset($_FILES['qr']) || $_FILES['qr']['error'] !== UPLOAD_ERR_OK) {
    echo "❌ Error al subir el archivo.";
    exit;
}

$extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
$ext = strtolower(pathinfo($_FILES['qr']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $extensiones_permitidas)) {
    echo "❌ Formato no permitido. Usa JPG, PNG o GIF.";
    exit;
}

// Verificar si el usuario ganó
$stmt = $conn->prepare("
    SELECT g.id 
    FROM ganadores g
    JOIN participaciones p ON p.id = g.id_participacion
    WHERE p.id_usuario = ? AND p.id_sorteo = ? AND p.lugar = ? AND p.estado = 'ganador'
    LIMIT 1
");
$stmt->bind_param("iis", $id_usuario, $id_sorteo, $lugar);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo "❌ No eres un ganador válido.";
    exit;
}

$id_ganador = $row['id'];
$dir_destino = '../qr_pago_premio/';
if (!is_dir($dir_destino)) mkdir($dir_destino, 0755, true);

$nombre_archivo = uniqid("qr_") . "." . $ext;
$ruta_final = $dir_destino . $nombre_archivo;

if (!move_uploaded_file($_FILES['qr']['tmp_name'], $ruta_final)) {
    echo "❌ Error al guardar el archivo.";
    exit;
}

$ruta_bd = 'qr_pago_premio/' . $nombre_archivo;

$stmt_update = $conn->prepare("UPDATE ganadores SET qr_pago_premio = ? WHERE id = ?");
$stmt_update->bind_param("si", $ruta_bd, $id_ganador);

if ($stmt_update->execute()) {
    // Redirige al dashboard tras éxito
    header("Location: dashboard.php?msg=qr_subido");
    exit;
} else {
    echo "❌ Error al actualizar el QR.";
    exit;
}
