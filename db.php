<?php
// alter_productos.php
require_once './includes/conexion.php';

// SQL para alterar la tabla
$sql = "ALTER TABLE productos ADD COLUMN activo TINYINT(1) DEFAULT 1";

// Ejecutar la consulta
if ($conn->query($sql) === TRUE) {
    echo "La columna 'activo' ha sido añadida a la tabla 'productos' exitosamente.";
} else {
    echo "Error al alterar la tabla: " . $conn->error;
}

// Cerrar la conexión
$conn->close();
?>