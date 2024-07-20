<?php
$servername = getenv('MYSQLHOST');
$username = getenv('MYSQLUSER');
$password = getenv('MYSQLPASSWORD');
$dbname = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

// Comprobar si las variables están definidas
if (!$servername || !$username || !$password || !$dbname || !$port) {
    die('Error: Las variables de entorno para la base de datos no están configuradas correctamente.');
}

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Comprobar si la conexión fue exitosa
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
} else {
    $conn->set_charset("utf8");
}
?>
