<?php
$servername = getenv('MYSQLHOST');
$username = getenv('MYSQLUSER');
$password = getenv('MYSQLPASSWORD');
$dbname = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT') ?: '3306'; // Usa 3306 como valor predeterminado si MYSQLPORT no está configurado

// Agrega estas líneas para depuración
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
