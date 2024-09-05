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
if (existeColumna($conn, 'compras', 'id_usuario')) {
    ejecutarConsulta($conn, "ALTER TABLE compras DROP COLUMN id_usuario");
}
if (!existeColumna($conn, 'compras', 'id_insumo')) {
    ejecutarConsulta($conn, "ALTER TABLE compras ADD COLUMN id_insumo INT AFTER id_proveedor");
}
if (!existeColumna($conn, 'compras', 'marca')) {
    ejecutarConsulta($conn, "ALTER TABLE compras ADD COLUMN marca VARCHAR(100) AFTER id_insumo");
}
if (existeColumna($conn, 'compras', 'subtotal')) {
    ejecutarConsulta($conn, "ALTER TABLE compras DROP COLUMN subtotal");
}
if (existeColumna($conn, 'compras', 'valor_unitario')) {
    ejecutarConsulta($conn, "ALTER TABLE compras DROP COLUMN valor_unitario");
}

// Modificar la tabla insumos
if (existeColumna($conn, 'insumos', 'nombre_insumo')) {
    ejecutarConsulta($conn, "ALTER TABLE insumos DROP COLUMN nombre_insumo");
}
if (existeColumna($conn, 'insumos', 'marca')) {
    ejecutarConsulta($conn, "ALTER TABLE insumos DROP COLUMN marca");
}
if (existeColumna($conn, 'insumos', 'precio')) {
    ejecutarConsulta($conn, "ALTER TABLE insumos DROP COLUMN precio");
}
if (!existeColumna($conn, 'insumos', 'id_compra')) {
    ejecutarConsulta($conn, "ALTER TABLE insumos ADD COLUMN id_compra INT AFTER id_proveedor");
}

// Verificar si la clave foránea ya existe
$check_fk_sql = "SELECT COUNT(*) as count FROM information_schema.TABLE_CONSTRAINTS 
                 WHERE TABLE_SCHEMA = DATABASE() 
                 AND TABLE_NAME = 'insumos' 
                 AND CONSTRAINT_NAME = 'fk_insumos_compras'";
$result = $conn->query($check_fk_sql);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Agregar nueva clave foránea si no existe
    $sql_add_fk = "ALTER TABLE insumos
                   ADD CONSTRAINT fk_insumos_compras
                   FOREIGN KEY (id_compra) REFERENCES compras(id_compra)";
    ejecutarConsulta($conn, $sql_add_fk);
} else {
    echo "La clave foránea fk_insumos_compras ya existe.<br>";
}

echo "Las tablas se han modificado correctamente.";

$conn->close();
?>