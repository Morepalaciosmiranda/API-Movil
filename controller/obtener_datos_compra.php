<?php
include_once('../includes/conexion.php');

function getValidConnection() {
    global $conn, $servername, $username, $password, $dbname;
    
    if (!$conn || $conn->ping() === false) {
        $conn->close(); // Cerrar la conexión existente si está en un estado inválido
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Reconnection failed: " . $conn->connect_error);
        }
    }
    return $conn;
}

$conn = getValidConnection();

if (isset($_GET['nombre_insumo'])) {
    $nombre_insumo = $_GET['nombre_insumo'];
    
    $consulta = "SELECT marca, cantidad FROM compras WHERE nombre_insumos = ? ORDER BY fecha_compra DESC LIMIT 1";
    $stmt = $conn->prepare($consulta);
    $stmt->bind_param("s", $nombre_insumo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($row = $resultado->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "No se encontraron datos para este insumo"]);
    }
} else {
    echo json_encode(["error" => "Nombre de insumo no proporcionado"]);
}