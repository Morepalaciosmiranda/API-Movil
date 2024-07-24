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

// Primero, verificamos si la columna id_usuario existe
$checkColumn = "SHOW COLUMNS FROM rolesxpermiso LIKE 'id_usuario'";
$result = $conn->query($checkColumn);

if ($result->num_rows > 0) {
    // Si existe, la cambiamos a id_rol
    $sql = "ALTER TABLE rolesxpermiso CHANGE id_usuario id_rol INT;";
} else {
    // Si no existe, agregamos la columna id_rol
    $sql = "ALTER TABLE rolesxpermiso ADD COLUMN id_rol INT;";
}

// Agregamos la clave primaria compuesta
$sql .= "ALTER TABLE rolesxpermiso ADD PRIMARY KEY (id_rol, id_permiso);";

if ($conn->multi_query($sql) === TRUE) {
    echo "Table modified successfully";
} else {
    echo "Error modifying table: " . $conn->error;
}

// Limpiar resultados pendientes
while ($conn->more_results() && $conn->next_result()) {
    if ($result = $conn->store_result()) {
        $result->free();
    }
}

$conn->close();
?>