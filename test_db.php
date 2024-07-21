<?php
include 'includes/conexion.php';

echo "<pre>";
print_r(parse_url(getenv("MYSQL_URL")));
echo "</pre>";

echo "Intentando conectar a la base de datos...<br>";

if ($conn->ping()) {
    echo "¡Conexión exitosa a la base de datos!<br>";
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        echo "Tablas en la base de datos:<br>";
        while ($row = $result->fetch_array()) {
            echo $row[0] . "<br>";
        }
    } else {
        echo "Error al obtener las tablas: " . $conn->error;
    }
} else {
    echo "Error de conexión: " . $conn->error;
}
?>