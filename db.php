<?php
// Incluir el archivo de conexión
require_once './includes/conexion.php';

// Función para ejecutar una consulta y manejar errores
function executeQuery($conn, $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Operación exitosa: " . $sql . "<br>";
    } else {
        echo "Error al ejecutar la consulta: " . $conn->error . "<br>";
    }
}

// Verificar si la columna ya permite NULL
$checkNullable = "SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'rolesxpermiso' 
                  AND COLUMN_NAME = 'id_usuario'";

$result = $conn->query($checkNullable);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['IS_NULLABLE'] === 'NO') {
        // La columna no permite NULL, procedemos a modificarla
        $alterTable = "ALTER TABLE rolesxpermiso MODIFY COLUMN id_usuario INT NULL";
        executeQuery($conn, $alterTable);
    } else {
        echo "La columna id_usuario ya permite valores NULL.<br>";
    }
} else {
    echo "No se pudo verificar el estado de la columna id_usuario.<br>";
}

// Cerrar la conexión
$conn->close();

echo "Proceso completado.";
?>