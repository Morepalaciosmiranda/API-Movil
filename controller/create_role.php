<?php
session_start();
include '../includes/conexion.php';

header('Content-Type: application/json');
$url = getenv("MYSQL_URL");
$conn = new mysqli($url);

$response = array('status' => '', 'message' => '');

if (isset($_POST['role_name']) && isset($_POST['permissions'])) {
    $role_name = $_POST['role_name'];
    $permissions = json_decode($_POST['permissions']);

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // Insertar el nuevo rol
        $insert_role_sql = "INSERT INTO roles (nombre_rol) VALUES (?)";
        $insert_role_stmt = $conn->prepare($insert_role_sql);
        $insert_role_stmt->bind_param("s", $role_name);

        if ($insert_role_stmt->execute()) {
            $new_role_id = $conn->insert_id;

            // Asignar permisos al nuevo rol
            $insert_permission_sql = "INSERT INTO rolesxpermiso (id_rol, id_permiso) VALUES (?, ?)";
            $insert_permission_stmt = $conn->prepare($insert_permission_sql);

            foreach ($permissions as $permission_id) {
                $insert_permission_stmt->bind_param("ii", $new_role_id, $permission_id);
                $insert_permission_stmt->execute();
            }

            // Confirmar transacción
            $conn->commit();

            $response['status'] = 'success';
            $response['message'] = 'Rol creado y permisos asignados correctamente.';
        } else {
            throw new Exception("Error al crear el rol: " . $conn->error);
        }
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        $response['status'] = 'error';
        $response['message'] = $e->getMessage();
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Faltan parámetros obligatorios.';
}

echo json_encode($response);
?>