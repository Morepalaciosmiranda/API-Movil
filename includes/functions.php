<?php
// includes/functions.php

/**
 * Verifica si un usuario tiene un permiso específico.
 * 
 * @param int $idUsuario ID del usuario
 * @param string $nombrePermiso Nombre del permiso a verificar
 * @return bool True si el usuario tiene el permiso, false en caso contrario
 */
function tienePermiso($idUsuario, $nombrePermiso) {
    global $conn;
    $sql = "SELECT 1 FROM usuarios u
            JOIN roles r ON u.id_rol = r.id_rol
            JOIN rolesxpermiso rxp ON r.id_rol = rxp.id_rol
            JOIN permisos p ON rxp.id_permiso = p.id_permiso
            WHERE u.id_usuario = ? AND p.nombre_permiso = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        // Manejar error en la preparación de la consulta
        error_log("Error preparando la consulta: " . $conn->error);
        return false;
    }

    $stmt->bind_param("is", $idUsuario, $nombrePermiso);
    if (!$stmt->execute()) {
        // Manejar error en la ejecución de la consulta
        error_log("Error ejecutando la consulta: " . $stmt->error);
        $stmt->close();
        return false;
    }

    $result = $stmt->get_result();
    $hasPermission = $result->num_rows > 0;
    $stmt->close();

    return $hasPermission;
}

/**
 * Obtiene todos los permisos de un usuario.
 * 
 * @param int $idUsuario ID del usuario
 * @return array Array con los nombres de los permisos del usuario
 */
function obtenerPermisosUsuario($idUsuario) {
    global $conn;
    $sql = "SELECT DISTINCT p.nombre_permiso 
            FROM usuarios u
            JOIN roles r ON u.id_rol = r.id_rol
            JOIN rolesxpermiso rxp ON r.id_rol = rxp.id_rol
            JOIN permisos p ON rxp.id_permiso = p.id_permiso
            WHERE u.id_usuario = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Error preparando la consulta: " . $conn->error);
        return [];
    }

    $stmt->bind_param("i", $idUsuario);
    if (!$stmt->execute()) {
        error_log("Error ejecutando la consulta: " . $stmt->error);
        $stmt->close();
        return [];
    }

    $result = $stmt->get_result();
    $permisos = [];
    while ($row = $result->fetch_assoc()) {
        $permisos[] = $row['nombre_permiso'];
    }
    $stmt->close();

    return $permisos;
}

/**
 * Registra un intento de acceso no autorizado.
 * 
 * @param int $idUsuario ID del usuario
 * @param string $accion Acción que se intentó realizar
 */
function registrarAccesoNoAutorizado($idUsuario, $accion) {
    global $conn;
    $sql = "INSERT INTO log_accesos_no_autorizados (id_usuario, accion, fecha) VALUES (?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Error preparando la consulta de registro: " . $conn->error);
        return;
    }

    $stmt->bind_param("is", $idUsuario, $accion);
    if (!$stmt->execute()) {
        error_log("Error ejecutando la consulta de registro: " . $stmt->error);
    }
    $stmt->close();
}

// Puedes agregar más funciones útiles aquí según sea necesario