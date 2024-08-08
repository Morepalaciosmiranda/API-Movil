<?php
// Incluir el archivo de conexión
require_once 'conexion.php';

// Función para ejecutar una consulta y manejar errores
function executeQuery($conn, $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Operación exitosa: " . $sql . "\n";
    } else {
        echo "Error al ejecutar la consulta: " . $conn->error . "\n";
    }
}

// Verificar si la columna 'origen' ya existe
$checkColumnSQL = "SHOW COLUMNS FROM pedidos LIKE 'origen'";
$result = $conn->query($checkColumnSQL);

if ($result->num_rows == 0) {
    // La columna no existe, así que la añadimos
    $addColumnSQL = "ALTER TABLE pedidos ADD COLUMN origen ENUM('web', 'admin') DEFAULT 'web'";
    executeQuery($conn, $addColumnSQL);
} else {
    echo "La columna 'origen' ya existe en la tabla 'pedidos'.\n";
}

// Actualizar los pedidos existentes
// Asumimos que los pedidos creados por usuarios con rol 'Administrador' son de origen 'admin'
$updatePedidosSQL = "
UPDATE pedidos p
JOIN usuarios u ON p.id_usuario = u.id_usuario
SET p.origen = 'admin'
WHERE u.rol = 'Administrador'
";
executeQuery($conn, $updatePedidosSQL);

// Cerrar la conexión
$conn->close();

echo "Proceso completado.\n";
?>