<?php
function getValidConnection() {
    global $conn, $servername, $username, $password, $dbname;
    
    if (!isset($conn) || !$conn || $conn->ping() === false) {
        if (isset($conn)) {
            $conn->close(); // Cerrar la conexión existente si está en un estado inválido
        }
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Reconnection failed: " . $conn->connect_error);
        }
    }
    return $conn;
}
?>