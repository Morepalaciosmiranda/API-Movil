<?php
$url = parse_url(getenv("MYSQL_URL"));

$host = $url["host"];
$username = $url["user"];
$password = $url["pass"];
$database = "railway";  // Cambiado a "railway"
$port = $url["port"];

// Crear la conexión
$conn = new mysqli($host, $username, $password, $database, $port);

// Comprobar si la conexión fue exitosa
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
} else {
    $conn->set_charset("utf8");
    echo "Conexión exitosa a la base de datos " . $database;
}
?>