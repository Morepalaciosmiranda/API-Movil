<?php
// Incluir el archivo de conexión
require_once './includes/conexion.php';

// Verificar la conexión
if ($conn->connect_error) {
    die("La conexión falló: " . $conn->connect_error);
}

// Consulta SQL para actualizar la tabla productos
$sql = "UPDATE productos SET foto = CONCAT('uploads/', foto) WHERE foto NOT LIKE 'uploads/%'";

// Ejecutar la consulta
if ($conn->query($sql) === TRUE) {
    echo "Registros actualizados correctamente";
} else {
    echo "Error al actualizar registros: " . $conn->error;
}

// Cerrar la conexión
$conn->close();
?>