<?php
function getValidConnection() {
    global $conn;
    
    if (!isset($conn) || !($conn instanceof mysqli) || $conn->ping() === false) {
        // Si la conexión no existe o no es válida, creamos una nueva
        require_once(__DIR__ . '/conexion.php');
    }
    
    return $conn;
}

function closeConnection(&$conn) {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
        $conn = null;
    }
}
?>