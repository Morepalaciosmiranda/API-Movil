<?php
$database_url = getenv('MYSQL_URL');

if ($database_url) {
    // Si tenemos la URL de Railway, la usamos
    $url_parts = parse_url($database_url);
    
    $servername = $url_parts['host'];
    $username = $url_parts['user'];
    $password = $url_parts['pass'];
    $dbname = ltrim($url_parts['path'], '/');
} else {
    // Si no, usamos las variables individuales (por compatibilidad)
    $servername = getenv('DB_HOST');
    $username = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');
    $dbname = getenv('DB_NAME');
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
} else {
    $conn->set_charset("utf8");
}
?>