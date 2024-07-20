<?php
header('Content-Type: application/json');
include '../includes/conexion.php';

$sql_usuarios = "SELECT id_usuario, nombre_usuario FROM usuarios";
$result_usuarios = $conn->query($sql_usuarios);

$usuarios = [];
if ($result_usuarios->num_rows > 0) {
    while ($row = $result_usuarios->fetch_assoc()) {
        $usuarios[] = $row;
    }
}

echo json_encode($usuarios);
$conn->close();
?>
