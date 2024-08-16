<?php
header('Content-Type: application/json');

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

function jsonResponse($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

try {
    $url = parse_url(getenv("MYSQL_URL"));

    if (!$url) {
        throw new Exception("Invalid MYSQL_URL environment variable");
    }

    $host = $url["host"] ?? null;
    $username = $url["user"] ?? null;
    $password = $url["pass"] ?? null;
    $database = isset($url["path"]) ? substr($url["path"], 1) : null;
    $port = $url["port"] ?? null;

    if (!$host || !$username || !$password || !$database || !$port) {
        throw new Exception("Missing required database connection parameters");
    }

    $conn = mysqli_init();
    if (!$conn) {
        throw new Exception("mysqli_init failed");
    }

    if (!mysqli_real_connect($conn, $host, $username, $password, $database, $port)) {
        throw new Exception("Connect Error: " . mysqli_connect_error());
    }

    if (!$conn->set_charset("utf8")) {
        throw new Exception("Error setting charset: " . $conn->error);
    }

    if (!$conn->query("SET time_zone = '+00:00'")) {
        throw new Exception("Error setting timezone: " . $conn->error);
    }

    // Si llegamos aquí, la conexión fue exitosa
    // No enviamos respuesta JSON aquí, ya que este archivo será incluido en otros scripts

} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    jsonResponse('error', 'Error de conexión a la base de datos. Por favor, inténtelo de nuevo más tarde.');
}
?>