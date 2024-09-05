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

// Función para verificar si existe una columna
function existeColumna($conn, $tabla, $columna) {
    $sql = "SHOW COLUMNS FROM $tabla LIKE '$columna'";
    $result = $conn->query($sql);
    return $result->num_rows > 0;
}

// Modificar la tabla compras
$sql_compras = "";

if (existeColumna($conn, 'compras', 'id_usuario')) {
    $sql_compras .= "ALTER TABLE compras DROP COLUMN id_usuario;";
}
if (!existeColumna($conn, 'compras', 'id_insumo')) {
    $sql_compras .= "ALTER TABLE compras ADD COLUMN id_insumo INT AFTER id_proveedor;";
}
if (!existeColumna($conn, 'compras', 'marca')) {
    $sql_compras .= "ALTER TABLE compras ADD COLUMN marca VARCHAR(100) AFTER id_insumo;";
}
if (existeColumna($conn, 'compras', 'subtotal')) {
    $sql_compras .= "ALTER TABLE compras DROP COLUMN subtotal;";
}
if (existeColumna($conn, 'compras', 'valor_unitario')) {
    $sql_compras .= "ALTER TABLE compras DROP COLUMN valor_unitario;";
}

if (!empty($sql_compras)) {
    ejecutarConsulta($conn, $sql_compras);
}

// Modificar la tabla insumos
$sql_insumos = "";

if (existeColumna($conn, 'insumos', 'nombre_insumo')) {
    $sql_insumos .= "ALTER TABLE insumos DROP COLUMN nombre_insumo;";
}
if (existeColumna($conn, 'insumos', 'marca')) {
    $sql_insumos .= "ALTER TABLE insumos DROP COLUMN marca;";
}
if (existeColumna($conn, 'insumos', 'precio')) {
    $sql_insumos .= "ALTER TABLE insumos DROP COLUMN precio;";
}
if (!existeColumna($conn, 'insumos', 'id_compra')) {
    $sql_insumos .= "ALTER TABLE insumos ADD COLUMN id_compra INT AFTER id_proveedor;";
}

if (!empty($sql_insumos)) {
    ejecutarConsulta($conn, $sql_insumos);
}

// Agregar nueva clave foránea si es necesario
$sql_add_fk = "
ALTER TABLE insumos
ADD CONSTRAINT fk_insumos_compras
FOREIGN KEY (id_compra) REFERENCES compras(id_compra);
";
ejecutarConsulta($conn, $sql_add_fk);

echo "Las tablas se han modificado correctamente.";

$conn->close();
?>