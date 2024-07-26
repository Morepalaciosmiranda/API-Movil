<?php
session_start();
include '../includes/conexion.php';

$response = array('status' => '', 'message' => '');

if (isset($_POST['role_id'])) {
    $role_id = $_POST['role_id'];
    
    // En lugar de eliminar el rol, lo marcamos como inactivo
    $update_sql = "UPDATE roles SET estado_rol = 'Inactivo' WHERE id_rol = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $role_id);
    
    if ($update_stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Rol desactivado correctamente';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error al desactivar el rol: ' . $conn->error;
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Falta el ID del rol';
}

echo json_encode($response);
?>