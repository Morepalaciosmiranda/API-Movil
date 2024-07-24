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

$sql = "ALTER TABLE rolesxpermiso DROP COLUMN id_usuario;
        ALTER TABLE rolesxpermiso ADD PRIMARY KEY (id_rol, id_permiso);";

if ($conn->multi_query($sql) === TRUE) {
    echo "Table modified successfully";
} else {
    echo "Error modifying table: " . $conn->error;
}

$conn->close();
?>