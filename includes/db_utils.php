<?php
function getValidConnection() {
    global $conn, $servername, $username, $password, $dbname;
    
    if (!isset($conn) || !($conn instanceof mysqli) || !@$conn->ping()) {
        if (isset($conn) && $conn instanceof mysqli) {
            @$conn->close(); // Intentar cerrar la conexión existente si está en un estado inválido
        }
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    }
    return $conn;
}

function closeConnection(&$conn) {
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_errno) {
        $conn->close();
        $conn = null; // Asegurarse de que la variable de conexión se establezca a null después de cerrarla
    }
}
?>