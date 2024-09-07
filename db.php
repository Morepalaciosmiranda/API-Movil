<?php
include './includes/conexion.php';

// Función para ejecutar consultas y manejar errores
function ejecutarConsulta($conn, $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Consulta ejecutada con éxito: $sql<br>";
    } else {
        echo "Error al ejecutar la consulta: " . $conn->error . "<br>";
    }
}

// SQL para eliminar la tabla si existe
$sql_drop = "DROP TABLE IF EXISTS compras";
ejecutarConsulta($conn, $sql_drop);

// SQL para crear la tabla compras
$sql_create = "CREATE TABLE compras (
    id_compra int NOT NULL AUTO_INCREMENT,
    id_proveedor int,
    id_insumo int,
    nombre_insumo varchar(100),
    marca varchar(100),
    cantidad int,
    fecha_compra date,
    total_compra double,
    PRIMARY KEY (id_compra)
)";
ejecutarConsulta($conn, $sql_create);

echo "La tabla 'compras' ha sido recreada correctamente.";

$conn->close();
?>