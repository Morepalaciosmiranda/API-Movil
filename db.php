<?php
// Incluir el archivo de conexión para usar la configuración existente
include './includes/conexion.php';

// SQL para crear la tabla productos_insumos
$sql = "
CREATE TABLE IF NOT EXISTS productos_insumos (
  id_producto int(11) NOT NULL,
  id_insumo int(11) NOT NULL,
  cantidad_insumo double DEFAULT NULL,
  PRIMARY KEY (id_producto, id_insumo),
  FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
  FOREIGN KEY (id_insumo) REFERENCES insumos(id_insumo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

// Ejecutar la consulta para crear la tabla
if ($conn->query($sql) === TRUE) {
    echo "Tabla productos_insumos creada exitosamente.";
} else {
    echo "Error al crear la tabla: " . $conn->error;
}

// Cerrar la conexión
$conn->close();
?>
