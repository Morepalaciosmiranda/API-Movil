<?php

include '../includes/conexion.php';


if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];


    $sql = "SELECT id_permiso FROM rolesxpermiso WHERE id_rol = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
    
        $permissions = array();
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row['id_permiso'];
        }
   
        echo json_encode($permissions);
    } else {
        echo "Error al obtener los permisos: " . $conn->error;
    }
} else {
    echo "No se recibió el ID de usuario.";
}
?>
