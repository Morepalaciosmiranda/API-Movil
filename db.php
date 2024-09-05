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

// Eliminar la restricción de clave foránea
$sql_drop_fk = "ALTER TABLE compras DROP FOREIGN KEY compras_ibfk_1;";
ejecutarConsulta($conn, $sql_drop_fk);

// Modificar la tabla compras
$sql_compras = "
ALTER TABLE compras
DROP COLUMN id_usuario,
ADD COLUMN id_insumo INT AFTER id_proveedor,
ADD COLUMN marca VARCHAR(100) AFTER id_insumo,
DROP COLUMN subtotal,
DROP COLUMN valor_unitario;
";
ejecutarConsulta($conn, $sql_compras);

// Modificar la tabla insumos
$sql_insumos = "
ALTER TABLE insumos
DROP COLUMN nombre_insumo,
DROP COLUMN marca,
DROP COLUMN precio,
ADD COLUMN id_compra INT AFTER id_proveedor;
";
ejecutarConsulta($conn, $sql_insumos);

// Agregar nuevas claves foráneas si es necesario
$sql_add_fk = "
ALTER TABLE insumos
ADD CONSTRAINT fk_insumos_compras
FOREIGN KEY (id_compra) REFERENCES compras(id_compra);
";
ejecutarConsulta($conn, $sql_add_fk);

echo "Las tablas se han modificado correctamente.";

$conn->close();
?>