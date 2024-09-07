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
    $sql = "SELECT * FROM information_schema.TABLE_CONSTRAINTS 
            WHERE CONSTRAINT_SCHEMA = DATABASE() 
            AND CONSTRAINT_NAME = '$nombreClave' 
            AND CONSTRAINT_TYPE = 'FOREIGN KEY' 
            AND TABLE_NAME = '$tabla'";
    $result = $conn->query($sql);
    return $result->num_rows > 0;
}

// Desactivar verificación de claves foráneas
ejecutarConsulta($conn, "SET FOREIGN_KEY_CHECKS = 0");

// Eliminar la clave foránea en la tabla 'insumos' si existe
if (existeClaveForanea($conn, 'insumos', 'fk_insumos_compras')) {
    ejecutarConsulta($conn, "ALTER TABLE insumos DROP FOREIGN KEY fk_insumos_compras");
}

// Eliminar la clave foránea en la tabla 'detalle_compras' si existe
if (existeClaveForanea($conn, 'detalle_compras', 'detalle_compras_ibfk_1')) {
    ejecutarConsulta($conn, "ALTER TABLE detalle_compras DROP FOREIGN KEY detalle_compras_ibfk_1");
}

// Guardar datos existentes de la tabla compras
$result = $conn->query("SELECT * FROM compras");
$compras_data = $result->fetch_all(MYSQLI_ASSOC);

// SQL para eliminar la tabla si existe
$sql_drop = "DROP TABLE IF EXISTS compras";
ejecutarConsulta($conn, $sql_drop);

// SQL para crear la tabla compras
$sql_create = "CREATE TABLE compras (
    id_compra INT NOT NULL AUTO_INCREMENT,
    id_proveedor INT,
    id_insumo INT,
    nombre_insumo VARCHAR(100),
    marca VARCHAR(100),
    cantidad INT,
    fecha_compra DATE,
    total_compra DOUBLE,
    PRIMARY KEY (id_compra)
)";
ejecutarConsulta($conn, $sql_create);

// Reinsertar los datos guardados
foreach ($compras_data as $row) {
    $sql_insert = "INSERT INTO compras (id_compra, id_proveedor, id_insumo, nombre_insumo, marca, cantidad, fecha_compra, total_compra) 
                   VALUES ({$row['id_compra']}, {$row['id_proveedor']}, {$row['id_insumo']}, '{$row['nombre_insumo']}', '{$row['marca']}', {$row['cantidad']}, '{$row['fecha_compra']}', {$row['total_compra']})";
    ejecutarConsulta($conn, $sql_insert);
}

// Volver a añadir la clave foránea en la tabla 'detalle_compras'
$sql_add_fk_detalle = "ALTER TABLE detalle_compras 
                       ADD CONSTRAINT detalle_compras_ibfk_1 
                       FOREIGN KEY (id_compra) REFERENCES compras(id_compra)";
ejecutarConsulta($conn, $sql_add_fk_detalle);

// Volver a añadir la clave foránea en la tabla 'insumos'
$sql_add_fk_insumos = "ALTER TABLE insumos 
                       ADD CONSTRAINT fk_insumos_compras 
                       FOREIGN KEY (id_compra) REFERENCES compras(id_compra)";
ejecutarConsulta($conn, $sql_add_fk_insumos);

// Reactivar verificación de claves foráneas
ejecutarConsulta($conn, "SET FOREIGN_KEY_CHECKS = 1");

echo "La tabla 'compras' ha sido recreada correctamente, los datos han sido reinsertados y las claves foráneas han sido restauradas.";

$conn->close();
?>