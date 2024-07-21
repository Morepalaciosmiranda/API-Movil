<?php
$url = parse_url(getenv("MYSQL_URL"));

echo "Host: " . $url["host"] . "<br>";
echo "Username: " . $url["user"] . "<br>";
echo "Database: " . substr($url["path"], 1) . "<br>";
echo "Port: " . $url["port"] . "<br>";

$host = $url["host"];
$username = $url["user"];
$password = $url["pass"];
$database = substr($url["path"], 1);
$port = $url["port"];

// Crear la conexión
$conn = new mysqli($host, $username, $password, $database, $port);

// Comprobar si la conexión fue exitosa
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
} else {
    $conn->set_charset("utf8");
}
?>