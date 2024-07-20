<?php
include '../includes/conexion.php';

session_start();

$response = array('status' => '', 'permissions' => array());

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT id_permiso FROM rolesxpermiso WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response['permissions'][] = $row['id_permiso'];
    }

    $response['status'] = 'success';
} else {
    $response['status'] = 'error';
    $response['message'] = 'No se pudo obtener los permisos del usuario.';
}

echo json_encode($response);
?>
