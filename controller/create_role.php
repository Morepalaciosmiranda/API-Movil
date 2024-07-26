<?php
session_start();
include '../includes/conexion.php';

header('Content-Type: application/json');

function sendJsonResponse($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    sendJsonResponse('error', 'Método no permitido');
}

if (!isset($_POST['roleName']) || !isset($_POST['permissions'])) {
    sendJsonResponse('error', 'Datos incompletos');
}

$roleName = $_POST['roleName'];
$permissions = json_decode($_POST['permissions']);

if (!is_array($permissions)) {
    sendJsonResponse('error', 'Formato de permisos inválido');
}

try {
    $conn->begin_transaction();

    // Insertar el nuevo rol
    $insertRoleSql = "INSERT INTO roles (nombre_rol) VALUES (?)";
    $stmt = $conn->prepare($insertRoleSql);
    $stmt->bind_param("s", $roleName);

    if (!$stmt->execute()) {
        throw new Exception("Error al crear el rol: " . $stmt->error);
    }

    $newRoleId = $conn->insert_id;

    // Insertar los permisos para el nuevo rol
    $insertPermissionSql = "INSERT INTO rolesxpermiso (id_rol, id_permiso) VALUES (?, ?)";
    $stmt = $conn->prepare($insertPermissionSql);

    foreach ($permissions as $permissionId) {
        $stmt->bind_param("ii", $newRoleId, $permissionId);
        if (!$stmt->execute()) {
            throw new Exception("Error al asignar permiso: " . $stmt->error);
        }
    }

    $conn->commit();
    sendJsonResponse('success', 'Rol creado exitosamente');

} catch (Exception $e) {
    $conn->rollback();
    sendJsonResponse('error', $e->getMessage());
}