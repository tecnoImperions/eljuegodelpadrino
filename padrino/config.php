<?php
// config.php
$host = "localhost:3307";
$usuario = "root";
$contrasena = "";
$bd = "padrino";

$conn = new mysqli($host, $usuario, $contrasena, $bd);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
