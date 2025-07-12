<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_participacion'])) {
    $id = intval($_POST['id_participacion']);

    $stmt = $conn->prepare("UPDATE ganadores SET estado_pago = 'pagado' WHERE id_participacion = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        // Redirigir de vuelta al sorteo correspondiente
        $id_sorteo = $_GET['id'] ?? 0;
        header("Location: resultados.php?id=" . $id_sorteo);
        exit;
    } else {
        echo "❌ Error al actualizar estado de pago.";
    }
} else {
    echo "🚫 Solicitud inválida.";
}
