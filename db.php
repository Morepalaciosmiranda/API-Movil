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

// Función para verificar si existe una clave foránea
function existeClaveForanea($conn, $tabla, $nombreClave) {
    $sql = "SELECT COUNT(*) as count FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = '$tabla' 
            AND CONSTRAINT_NAME = '$nombreClave' 
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['count'] > 0;
}

// Verificar y eliminar la restricción de clave foránea si existe
if (existeClaveForanea($conn, 'compras', 'compras_ibfk_1')) {
    $sql_drop_fk = "ALTER TABLE compras DROP FOREIGN KEY compras_ibfk_1;";
    ejecutarConsulta($conn, $sql_drop_fk);
} else {
    echo "La clave foránea compras_ibfk_1 no existe, continuando con las modificaciones.<br>";
}

// Modificar la tabla compras
$sql_compras = "
ALTER TABLE compras
DROP COLUMN IF EXISTS id_usuario,
ADD COLUMN IF NOT EXISTS id_insumo INT AFTER id_proveedor,
ADD COLUMN IF NOT EXISTS marca VARCHAR(100) AFTER id_insumo,
DROP COLUMN IF EXISTS subtotal,
DROP COLUMN IF EXISTS valor_unitario;
";
ejecutarConsulta($conn, $sql_compras);

// Modificar la tabla insumos
$sql_insumos = "
ALTER TABLE insumos
DROP COLUMN IF EXISTS nombre_insumo,
DROP COLUMN IF EXISTS marca,
DROP COLUMN IF EXISTS precio,
ADD COLUMN IF NOT EXISTS id_compra INT AFTER id_proveedor;
";
ejecutarConsulta($conn, $sql_insumos);

// Agregar nuevas claves foráneas si es necesario
$sql_add_fk = "
ALTER TABLE insumos
ADD CONSTRAINT IF NOT EXISTS fk_insumos_compras
FOREIGN KEY (id_compra) REFERENCES compras(id_compra);
";
ejecutarConsulta($conn, $sql_add_fk);

echo "Las tablas se han modificado correctamente.";

$conn->close();
?>