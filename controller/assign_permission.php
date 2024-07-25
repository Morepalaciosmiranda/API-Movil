<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

include './includes/conexion.php';

$response = array('status' => '', 'message' => '');

if (isset($_POST['permissions'], $_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    
    error_log("Procesando usuario ID: " . $user_id);
    error_log("Permisos recibidos: " . $_POST['permissions']);
    
    $permissions = json_decode($_POST['permissions'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['status'] = 'error';
        $response['message'] = 'Error al decodificar JSON: ' . json_last_error_msg();
        echo json_encode($response);
        exit;
    }
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Eliminar permisos anteriores
        $delete_sql = "DELETE FROM rolesxpermiso WHERE id_usuario = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        if (!$delete_stmt) {
            throw new Exception("Error en la preparación de la consulta DELETE: " . $conn->error);
        }
        $delete_stmt->bind_param("i", $user_id);
        if (!$delete_stmt->execute()) {
            throw new Exception("Error al eliminar permisos anteriores: " . $delete_stmt->error);
        }
        
        $response['message'] .= "Permisos anteriores eliminados correctamente. ";
        
        // Insertar nuevos permisos
        $insert_sql = "INSERT INTO rolesxpermiso (id_usuario, id_permiso) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        if (!$insert_stmt) {
            throw new Exception("Error en la preparación de la consulta INSERT: " . $conn->error);
        }
        
        foreach ($permissions as $permission) {
            $insert_stmt->bind_param("ii", $user_id, $permission);
            if (!$insert_stmt->execute()) {
                throw new Exception("Error al insertar permiso: " . $insert_stmt->error);
            }
        }
        
        // Si llegamos aquí, todo ha ido bien
        $conn->commit();
        $response['status'] = 'success';
        $response['message'] .= "Nuevos permisos asignados correctamente.";
        
    } catch (Exception $e) {
        // Si hay un error, revertimos la transacción
        $conn->rollback();
        $response['status'] = 'error';
        $response['message'] = "Error: " . $e->getMessage();
    }
    
} else {
    $response['status'] = 'error';
    $response['message'] = "Error: Datos insuficientes para asignar permisos.";
}

error_log("Respuesta enviada: " . json_encode($response));

header('Content-Type: application/json');
echo json_encode($response);
?>