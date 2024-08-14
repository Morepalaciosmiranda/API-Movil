<?php
// Incluye el archivo de conexión
include './includes/conexion.php';

// Verifica si la columna ya existe
$checkColumn = "SHOW COLUMNS FROM pedidos LIKE 'timestamp_pedido'";
$result = $conn->query($checkColumn);

if ($result->num_rows == 0) {
    // La columna no existe, así que la agregamos
    $addColumn = "ALTER TABLE pedidos ADD COLUMN timestamp_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    
    if ($conn->query($addColumn) === TRUE) {
        echo "La columna 'timestamp_pedido' se ha agregado correctamente a la tabla 'pedidos'.";
        
        // Actualiza los registros existentes
        $updateExisting = "UPDATE pedidos SET timestamp_pedido = fecha_pedido WHERE timestamp_pedido IS NULL";
        if ($conn->query($updateExisting) === TRUE) {
            echo "<br>Los registros existentes se han actualizado con éxito.";
        } else {
            echo "<br>Error al actualizar los registros existentes: " . $conn->error;
        }
    } else {
        echo "Error al agregar la columna: " . $conn->error;
    }
} else {
    echo "La columna 'timestamp_pedido' ya existe en la tabla 'pedidos'.";
}

// Cierra la conexión
$conn->close();