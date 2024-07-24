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

// Verificamos si la columna id_usuario existe
$checkUserColumn = "SHOW COLUMNS FROM rolesxpermiso LIKE 'id_usuario'";
$resultUser = $conn->query($checkUserColumn);

// Verificamos si la columna id_rol existe
$checkRolColumn = "SHOW COLUMNS FROM rolesxpermiso LIKE 'id_rol'";
$resultRol = $conn->query($checkRolColumn);

if ($resultUser->num_rows > 0 && $resultRol->num_rows == 0) {
    // Si existe id_usuario pero no id_rol, la cambiamos
    $sql = "ALTER TABLE rolesxpermiso CHANGE id_usuario id_rol INT;";
} elseif ($resultUser->num_rows == 0 && $resultRol->num_rows == 0) {
    // Si no existe ni id_usuario ni id_rol, agregamos id_rol
    $sql = "ALTER TABLE rolesxpermiso ADD COLUMN id_rol INT;";
} else {
    // Si ya existe id_rol, no hacemos cambios en las columnas
    $sql = "";
}

// Verificamos si ya existe una clave primaria
$checkPrimaryKey = "SHOW KEYS FROM rolesxpermiso WHERE Key_name = 'PRIMARY'";
$resultPrimaryKey = $conn->query($checkPrimaryKey);

if ($resultPrimaryKey->num_rows == 0) {
    // Si no existe clave primaria, la agregamos
    $sql .= "ALTER TABLE rolesxpermiso ADD PRIMARY KEY (id_rol, id_permiso);";
}

if (!empty($sql)) {
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
} else {
    echo "No changes were necessary";
}

$conn->close();
?>