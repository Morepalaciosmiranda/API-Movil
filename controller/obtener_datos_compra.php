<?php
include_once('../includes/db_utils.php');

$conn = getValidConnection();

if (isset($_GET['nombre_insumo'])) {
    $nombre_insumo = $_GET['nombre_insumo'];
    
    $query = "SELECT marca, cantidad FROM compras WHERE nombre_insumos = ? ORDER BY fecha_compra DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nombre_insumo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(["marca" => "", "cantidad" => ""]);
    }
} else {
    echo json_encode(["error" => "Nombre de insumo no proporcionado"]);
}

$conn->close();
?>