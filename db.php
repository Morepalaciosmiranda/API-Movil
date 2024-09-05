<?php
include_once './includes/conexion.php';

// Modificar la tabla compras
$sql_compras = "
ALTER TABLE compras
DROP COLUMN id_usuario,
ADD COLUMN id_insumo INT AFTER id_proveedor,
ADD COLUMN marca VARCHAR(100) AFTER id_insumo,
DROP COLUMN subtotal,
DROP COLUMN valor_unitario;
";

// Modificar la tabla insumos
$sql_insumos = "
ALTER TABLE insumos
DROP COLUMN nombre_insumo,
DROP COLUMN marca,
DROP COLUMN precio,
ADD COLUMN id_compra INT AFTER id_proveedor;
";

// Ejecutar las consultas
if ($conn->multi_query($sql_compras . $sql_insumos)) {
    do {
        // Almacenar el resultado
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());

    echo "Las tablas se han modificado correctamente.";
} else {
    echo "Error al modificar las tablas: " . $conn->error;
}

$conn->close();
?>