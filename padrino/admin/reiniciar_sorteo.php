<?php
session_start();
require_once '../config.php';

// Asegurar que solo admin puede reiniciar sorteos
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$sorteo_id = intval($_GET['id'] ?? 0);
if ($sorteo_id <= 0) {
    header("Location: dashboard.php?msg=error_id");
    exit;
}

// Verificar existencia del sorteo
$stmt = $conn->prepare("SELECT id FROM sorteos WHERE id = ?");
$stmt->bind_param("i", $sorteo_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: dashboard.php?msg=no_existe&id=$sorteo_id");
    exit;
}
$stmt->close();

$conn->begin_transaction();
try {
    // Eliminar ganadores
    $sql1 = "
        DELETE g FROM ganadores g
        INNER JOIN participaciones p ON g.id_participacion = p.id
        WHERE p.id_sorteo = ?
    ";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $sorteo_id);
    $stmt1->execute();
    $stmt1->close();

    // Eliminar historial
    $stmt2 = $conn->prepare("DELETE FROM historial_sorteos WHERE id_sorteo = ?");
    $stmt2->bind_param("i", $sorteo_id);
    $stmt2->execute();
    $stmt2->close();

    // Eliminar participaciones
    $stmt3 = $conn->prepare("DELETE FROM participaciones WHERE id_sorteo = ?");
    $stmt3->bind_param("i", $sorteo_id);
    $stmt3->execute();
    $stmt3->close();

    // Reiniciar sorteo
    $stmt4 = $conn->prepare("UPDATE sorteos SET estado = 'activo' WHERE id = ?");
    $stmt4->bind_param("i", $sorteo_id);
    $stmt4->execute();
    $stmt4->close();

    $conn->commit();

    header("Location: dashboard.php?msg=reiniciado&id=$sorteo_id");
    exit;
} catch (Exception $e) {
    $conn->rollback();
    header("Location: dashboard.php?msg=error_transaccion&id=$sorteo_id");
    exit;
}
