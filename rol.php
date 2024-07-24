<?php
$url = getenv("MYSQL_URL");
if (!$url) {
    die("MYSQL_URL environment variable is not set.");
}

$parsed = parse_url($url);

$host = $parsed["host"];
$username = $parsed["user"];
$password = $parsed["pass"];
$database = ltrim($parsed["path"], '/');
$port = $parsed["port"] ?? 3306;

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verificar si existe la columna id_rol
$checkColumn = "SHOW COLUMNS FROM rolesxpermiso LIKE 'id_rol'";
$result = $conn->query($checkColumn);

if ($result->num_rows > 0) {
    // Si existe id_rol, lo cambiamos a id_usuario
    $sql = "ALTER TABLE rolesxpermiso CHANGE id_rol id_usuario INT;";
    
    if ($conn->query($sql) === TRUE) {
        echo "La columna id_rol ha sido cambiada a id_usuario exitosamente.\n";
    } else {
        echo "Error al cambiar la columna: " . $conn->error . "\n";
    }
} else {
    echo "La columna id_rol no existe. No se necesitan cambios.\n";
}

// Verificar la estructura final de la tabla
$checkTable = "DESCRIBE rolesxpermiso";
$result = $conn->query($checkTable);

echo "Estructura actual de la tabla rolesxpermiso:\n";
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . "\n";
}

$conn->close();
?>