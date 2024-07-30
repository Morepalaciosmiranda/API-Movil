<?php
include './includes/conexion.php';

function ejecutarConsulta($conn, $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Operación exitosa: $sql<br>";
    } else {
        echo "Error : " . $conn->error . "<br>";
    }
}

$conn->begin_transaction();

try {
    // 0. Verificar si productos_temp existe y eliminarla si es necesario
    $sql_drop_temp_if_exists = "DROP TABLE IF EXISTS productos_temp";
    ejecutarConsulta($conn, $sql_drop_temp_if_exists);

    // 1. Crear una tabla temporal con la estructura original
    $sql_crear_temp = "CREATE TABLE productos_temp (
        id_producto int(11) NOT NULL,
        nombre_producto varchar(200) DEFAULT NULL,
        foto varchar(250) DEFAULT NULL,
        descripcion_producto varchar(200) DEFAULT NULL,
        valor_unitario double DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    ejecutarConsulta($conn, $sql_crear_temp);

    // 2. Copiar los datos existentes a la tabla temporal
    $sql_copiar_datos = "INSERT INTO productos_temp (id_producto, nombre_producto, descripcion_producto, valor_unitario)
                         SELECT id_producto, nombre_producto, descripcion_producto, valor_unitario FROM productos";
    ejecutarConsulta($conn, $sql_copiar_datos);

    // 3. Eliminar la clave foránea en detalle_venta
    $sql_eliminar_fk = "ALTER TABLE detalle_venta DROP FOREIGN KEY detalle_venta_ibfk_2";
    ejecutarConsulta($conn, $sql_eliminar_fk);

    // 4. Eliminar la tabla actual de productos
    $sql_eliminar_actual = "DROP TABLE productos";
    ejecutarConsulta($conn, $sql_eliminar_actual);

    // 5. Renombrar la tabla temporal a productos
    $sql_renombrar_temp = "RENAME TABLE productos_temp TO productos";
    ejecutarConsulta($conn, $sql_renombrar_temp);

    // 6. Establecer la clave primaria
    $sql_set_primary_key = "ALTER TABLE productos ADD PRIMARY KEY (id_producto)";
    ejecutarConsulta($conn, $sql_set_primary_key);

    // 7. Configurar el auto_increment si es necesario
    $sql_set_auto_increment = "ALTER TABLE productos MODIFY id_producto int(11) NOT NULL AUTO_INCREMENT";
    ejecutarConsulta($conn, $sql_set_auto_increment);

    // 8. Recrear la clave foránea en detalle_venta
    $sql_recrear_fk = "ALTER TABLE detalle_venta ADD CONSTRAINT detalle_venta_ibfk_2 
                       FOREIGN KEY (id_producto) REFERENCES productos(id_producto)";
    ejecutarConsulta($conn, $sql_recrear_fk);

    $conn->commit();
    echo "La tabla productos ha sido restaurada a su estructura original.";
} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>