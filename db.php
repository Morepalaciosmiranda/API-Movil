<?php
include './includes/conexion.php';

// Función para ejecutar una consulta y manejar errores
function ejecutarConsulta($conn, $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Operación exitosa: $sql<br>";
    } else {
        echo "Error en la operación: " . $conn->error . "<br>";
    }
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // 1. Crear una tabla temporal con la nueva estructura
    $sql_crear_temp = "CREATE TABLE productos_temp (
        id_producto INT AUTO_INCREMENT PRIMARY KEY,
        nombre_producto VARCHAR(255) NOT NULL,
        foto MEDIUMBLOB,
        foto_tipo VARCHAR(255),
        descripcion_producto TEXT,
        valor_unitario DECIMAL(10, 2) NOT NULL
    )";
    ejecutarConsulta($conn, $sql_crear_temp);

    // 2. Copiar los datos existentes a la tabla temporal
    $sql_copiar_datos = "INSERT INTO productos_temp (id_producto, nombre_producto, descripcion_producto, valor_unitario)
                         SELECT id_producto, nombre_producto, descripcion_producto, valor_unitario FROM productos";
    ejecutarConsulta($conn, $sql_copiar_datos);

    // 3. Renombrar la tabla original
    $sql_renombrar_original = "RENAME TABLE productos TO productos_old";
    ejecutarConsulta($conn, $sql_renombrar_original);

    // 4. Renombrar la tabla temporal a productos
    $sql_renombrar_temp = "RENAME TABLE productos_temp TO productos";
    ejecutarConsulta($conn, $sql_renombrar_temp);

    // 5. Eliminar la tabla antigua
    $sql_eliminar_antigua = "DROP TABLE productos_old";
    ejecutarConsulta($conn, $sql_eliminar_antigua);

    // Confirmar los cambios
    $conn->commit();
    echo "La tabla productos ha sido actualizada exitosamente.";

} catch (Exception $e) {
    // Si algo sale mal, revertir los cambios
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

// Cerrar la conexión
$conn->close();
?>