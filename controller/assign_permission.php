<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');

include '../includes/conexion.php';

$response = array('status' => '', 'message' => '');

if (isset($_POST['permissions'], $_POST['user_id'])) {
    $permissions = json_decode($_POST['permissions']);
    $user_id = $_POST['user_id'];
    
    error_log("User ID: " . $user_id . ", Permissions: " . json_encode($permissions));
    
    $delete_sql = "DELETE FROM rolesxpermiso WHERE id_usuario = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $user_id);
    $delete_stmt->execute();
    
    if ($delete_stmt->affected_rows >= 0) {
        $response['status'] = 'success';
        $response['message'] .= "Permisos anteriores eliminados correctamente.<br>";
    } else {
        $response['status'] = 'warning';
        $response['message'] .= "No se eliminaron permisos anteriores.<br>";
    }
    
    $insert_sql = "INSERT INTO rolesxpermiso (id_usuario, id_permiso) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    foreach ($permissions as $permission) {
        $insert_stmt->bind_param("ii", $user_id, $permission);
        $insert_stmt->execute();
    }
    
    if ($insert_stmt->affected_rows > 0) {
        $response['status'] = 'success';
        $response['message'] .= "Permisos asignados correctamente.";
    } else {
        $response['status'] = 'error';
        $response['message'] .= "Error al asignar permisos.";
    }
} else {
    $response['status'] = 'error';
    $response['message'] = "Error: Datos insuficientes para asignar permisos.";
}

error_log("Response sent: " . json_encode($response));

header('Content-Type: application/json');
echo json_encode($response);
?>