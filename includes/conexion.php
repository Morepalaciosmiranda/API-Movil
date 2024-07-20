<?php
// Extraer los datos de la URL de conexión pública
$url = parse_url(getenv('MYSQL_PUBLIC_URL'));

$servername = $url['host'];
$username = $url['user'];
$password = $url['pass'];
$dbname = substr($url['path'], 1); // Elimina la barra inicial del nombre de la base de datos
$port = $url['port'];

// Depuración para asegurarse de que las variables de entorno están configuradas correctamente
error_log("Servername: $servername");
error_log("Username: $username");
error_log("DB Name: $dbname");
error_log("Port: $port");

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
} else {
    $conn->set_charset("utf8");
}
?>
