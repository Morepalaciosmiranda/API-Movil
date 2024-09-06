<?php
include '../includes/conexion.php';

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
if (!existeColumna($conn, 'compras', 'nombre_insumo')) {
    ejecutarConsulta($conn, "ALTER TABLE compras ADD COLUMN nombre_insumo VARCHAR(100) AFTER id_insumo");
    echo "Se ha añadido el campo 'nombre_insumo' a la tabla 'compras'.<br>";
} else {
    echo "El campo 'nombre_insumo' ya existe en la tabla 'compras'.<br>";
}

echo "La tabla 'compras' se ha actualizado correctamente.";

$conn->close();
?>