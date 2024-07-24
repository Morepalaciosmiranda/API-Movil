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

// ID del usuario al que quieres asignar permisos de administrador
$admin_user_id = 54; // Cambia esto al ID del usuario que quieres hacer administrador

// Primero, asegúrate de que existe un rol de administrador
$check_admin_role = "SELECT id_rol FROM roles WHERE nombre_rol = 'Administrador'";
$result = $conn->query($check_admin_role);

if ($result->num_rows == 0) {
    // Si no existe, créalo
    $create_admin_role = "INSERT INTO roles (nombre_rol) VALUES ('Administrador')";
    $conn->query($create_admin_role);
    $admin_role_id = $conn->insert_id;
} else {
    $row = $result->fetch_assoc();
    $admin_role_id = $row['id_rol'];
}

// Asigna el rol de administrador al usuario
$assign_role = "UPDATE usuarios SET id_rol = ? WHERE id_usuario = ?";
$stmt = $conn->prepare($assign_role);
$stmt->bind_param("ii", $admin_role_id, $admin_user_id);
$stmt->execute();

// Obtén todos los permisos
$get_permissions = "SELECT id_permiso FROM permisos";
$result = $conn->query($get_permissions);

// Asigna todos los permisos al rol de administrador
while ($row = $result->fetch_assoc()) {
    $permission_id = $row['id_permiso'];
    $assign_permission = "INSERT INTO rolesxpermiso (id_usuario, id_permiso) VALUES (?, ?) ON DUPLICATE KEY UPDATE id_permiso = ?";
    $stmt = $conn->prepare($assign_permission);
    $stmt->bind_param("iii", $admin_user_id, $permission_id, $permission_id);
    $stmt->execute();
}

echo "Se han asignado todos los permisos de administrador al usuario con ID: " . $admin_user_id;

$conn->close();
?>