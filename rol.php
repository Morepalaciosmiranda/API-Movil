<?php
$url = getenv("MYSQL_URL");
$conn = new mysqli($url);

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