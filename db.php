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
    // 1. Eliminar la clave foránea existente
    $sql_drop_fk = "ALTER TABLE detalle_venta DROP FOREIGN KEY detalle_venta_ibfk_2";
    ejecutarConsulta($conn, $sql_drop_fk);

    // 2. Añadir la nueva clave foránea que apunte a la nueva tabla productos
    $sql_add_fk = "ALTER TABLE detalle_venta ADD CONSTRAINT detalle_venta_ibfk_2 
                   FOREIGN KEY (id_producto) REFERENCES productos(id_producto)";
    ejecutarConsulta($conn, $sql_add_fk);

    // 3. Ahora podemos eliminar la tabla antigua
    $sql_drop_old = "DROP TABLE productos_old";
    ejecutarConsulta($conn, $sql_drop_old);

    // Confirmar los cambios
    $conn->commit();
    echo "La tabla productos_old ha sido eliminada y las referencias han sido actualizadas exitosamente.";

} catch (Exception $e) {
    // Si algo sale mal, revertir los cambios
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

// Cerrar la conexión
$conn->close();
?>