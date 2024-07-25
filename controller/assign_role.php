<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');

session_start();
include '../includes/conexion.php';

$response = array('status' => '', 'message' => '');

if (isset($_POST['user_id']) && isset($_POST['new_role'])) {
    $user_id = $_POST['user_id'];
    $new_role_or_permissions = $_POST['new_role'];
    
    if (is_numeric($new_role_or_permissions)) {
        $check_role_sql = "SELECT COUNT(*) as count FROM roles WHERE id_rol = ?";
        $check_role_stmt = $conn->prepare($check_role_sql);
        $check_role_stmt->bind_param("i", $new_role_or_permissions);
        $check_role_stmt->execute();
        $role_result = $check_role_stmt->get_result();
        $role_count = $role_result->fetch_assoc()['count'];
        
        if ($role_count > 0) {
            $update_sql = "UPDATE usuarios SET id_rol = ? WHERE id_usuario = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $new_role_or_permissions, $user_id);
            if ($update_stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Rol asignado correctamente';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Error al asignar el nuevo rol: ' . $conn->error;
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'El ID del rol no existe en la tabla roles.';
        }
    } else {
        if (isset($_POST['permissions']) && is_array($_POST['permissions'])) {
            $permissions = $_POST['permissions'];
            $delete_sql = "DELETE FROM rolesxpermiso WHERE id_usuario = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $user_id);
            $delete_stmt->execute();
            
            $insert_sql = "INSERT INTO rolesxpermiso (id_usuario, id_permiso) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            foreach ($permissions as $permission) {
                $insert_stmt->bind_param("ii", $user_id, $permission);
                $insert_stmt->execute();
            }
            $response['status'] = 'success';
            $response['message'] = 'Permisos asignados correctamente.';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Debe seleccionar al menos un permiso.';
        }
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Faltan parámetros obligatorios.';
}

error_log("Response sent: " . json_encode($response));

header('Content-Type: application/json');
echo json_encode($response);
?>