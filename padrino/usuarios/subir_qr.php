<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    echo "🚫 No autorizado.";
    exit;
}

$id_usuario = $_SESSION['usuario']['id'];

// Buscar ganador sin QR y con ronda cerrada
$sql = "
    SELECT g.id, r.estado AS estado_ronda
    FROM ganadores g
    JOIN participaciones p ON p.id = g.id_participacion
    JOIN rondas_sorteo r ON r.id = p.id_ronda_sorteo
    WHERE p.id_usuario = ? 
      AND g.qr_pago_premio IS NULL
      AND r.estado = 'cerrada'
    LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    http_response_code(400);
    echo "🚫 No tienes permiso para esta acción o ya enviaste tu QR, o la ronda no está cerrada.";
    exit;
}

$ganador = $res->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['qr'])) {
    $allowed_ext = ['jpg', 'jpeg', 'png'];
    $max_size = 5 * 1024 * 1024; // 5MB

    $file = $_FILES['qr'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext)) {
        http_response_code(415);
        echo "⚠️ Solo se permiten archivos JPG, JPEG y PNG.";
        exit;
    }

    if ($file['size'] > $max_size) {
        http_response_code(413);
        echo "⚠️ El archivo es demasiado grande. Máximo 5MB.";
        exit;
    }

    $ruta = '../uploads/qr/';
    if (!file_exists($ruta)) mkdir($ruta, 0777, true);

    $nombre = uniqid('qr_') . '.' . $ext;
    $destino = $ruta . $nombre;

    if (!move_uploaded_file($file['tmp_name'], $destino)) {
        http_response_code(500);
        echo "❌ Error al subir el archivo. Intenta de nuevo.";
        exit;
    }

    $qr_path = 'uploads/qr/' . $nombre;

    $sql_u = "UPDATE ganadores SET qr_pago_premio = ? WHERE id = ?";
    $stmt_u = $conn->prepare($sql_u);
    $stmt_u->bind_param("si", $qr_path, $ganador['id']);

    if ($stmt_u->execute()) {
        echo "✅ Tu QR fue enviado correctamente. El padrino te pagará pronto.";
    } else {
        if (file_exists($destino)) unlink($destino);
        http_response_code(500);
        echo "❌ Error al registrar el QR. Intenta de nuevo.";
    }
    exit;
}

// Si no es POST o no se envió archivo:
http_response_code(405);
echo "Método no permitido.";
exit;
