<?php
// Incluir el archivo de conexión
require_once './includes/conexion.php';

// SQL para crear la tabla productos_insumos
$sql = "CREATE TABLE IF NOT EXISTS productos_insumos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    id_insumo INT NOT NULL,
    cantidad INT NOT NULL,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    FOREIGN KEY (id_insumo) REFERENCES insumos(id_insumo)
)";

// Ejecutar la consulta
if (mysqli_query($conn, $sql)) {
    echo "La tabla productos_insumos ha sido creada exitosamente.";
} else {
    echo "Error al crear la tabla: " . mysqli_error($conn);
}

// Cerrar la conexión
mysqli_close($conn);
?>