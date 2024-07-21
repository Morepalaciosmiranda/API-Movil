<?php
include 'includes/conexion.php';

echo "<pre>";
print_r($url);
echo "</pre>";

echo "Intentando conectar a la base de datos...<br>";

if ($conn->ping()) {
    echo "¡Conexión exitosa a la base de datos!";
} else {
    echo "Error de conexión: " . $conn->error;
}
?>