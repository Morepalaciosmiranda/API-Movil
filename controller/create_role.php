<?php
session_start();
include '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $roleName = $_POST['roleName'];
    $permissions = json_decode($_POST['permissions']);

    // Insertar el nuevo rol
    $insertRoleSql = "INSERT INTO roles (nombre_rol) VALUES (?)";
    $stmt = $conn->prepare($insertRoleSql);
    $stmt->bind_param("s", $roleName);

    if ($stmt->execute()) {
        $newRoleId = $conn->insert_id;

        // Insertar los permisos para el nuevo rol
        $insertPermissionSql = "INSERT INTO rolesxpermisos (id_rol, id_permiso) VALUES (?, ?)";
        $stmt = $conn->prepare($insertPermissionSql);

        foreach ($permissions as $permissionId) {
            $stmt->bind_param("ii", $newRoleId, $permissionId);
            $stmt->execute();
        }

        echo json_encode(['status' => 'success', 'message' => 'Rol creado exitosamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al crear el rol']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}