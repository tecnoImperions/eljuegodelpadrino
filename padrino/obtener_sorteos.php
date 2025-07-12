<?php
require_once '../config.php';

// Obtener sorteos activos
$sql = "SELECT * FROM sorteos WHERE estado = 'activo'";
$result = $conn->query($sql);

// Crear la salida HTML
$output = "";
while ($row = $result->fetch_assoc()) {
    $id_sorteo = $row['id'];
    $sql_part = "SELECT COUNT(*) AS total FROM participaciones WHERE id_sorteo = $id_sorteo";
    $res_part = $conn->query($sql_part)->fetch_assoc();
    $restantes = max(0, $row['max_participantes'] - $res_part['total']);
    
    $output .= "<tr>
                    <td>" . $row['titulo'] . "</td>
                    <td>" . ucfirst($row['plan']) . "</td>
                    <td><span class='badge bg-warning text-dark fs-5'>" . $restantes . "</span></td>
                    <td>";
                    
    if ($restantes > 0) {
        $output .= "<form action='participar.php' method='post'>
                        <input type='hidden' name='id_sorteo' value='" . $id_sorteo . "'>
                        <button type='submit' class='btn btn-sm btn-warning'>Participar</button>
                    </form>";
    } else {
        $output .= "<button class='btn btn-sm btn-secondary' disabled>Completo</button>";
    }
    
    $output .= "</td></tr>";
}

echo $output;
?>
