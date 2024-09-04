<?php
$url = parse_url(getenv("MYSQL_URL"));

$host = $url["host"];
$username = $url["user"];
$password = $url["pass"];
$database = substr($url["path"], 1);
$port = $url["port"];

// Crear la conexión
$conn = mysqli_init();
if (!$conn) {
    die("mysqli_init failed");
}

if (!mysqli_real_connect($conn, $host, $username, $password, $database, $port)) {
    die("Connect Error: " . mysqli_connect_error());
}

$conn->set_charset("utf8");
$conn->query("SET time_zone = '+00:00'");
?>