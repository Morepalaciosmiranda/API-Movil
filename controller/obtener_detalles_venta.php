<?php
include_once "../includes/conexion.php";

if (isset($_GET['id_venta'])) {
    $id_venta = $_GET['id_venta'];
    
    $sql = "SELECT detalle_venta.*, ventas.id_usuario, usuarios.nombre_usuario, usuarios.correo_electronico, productos.nombre_producto
            FROM detalle_venta
            JOIN ventas ON detalle_venta.id_venta = ventas.id_venta
            JOIN usuarios ON ventas.id_usuario = usuarios.id_usuario
            JOIN productos ON detalle_venta.id_producto = productos.id_producto
            WHERE detalle_venta.id_venta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_venta);
    $stmt->execute();
    $result = $stmt->get_result();

    $detalles = array();
    while ($row = $result->fetch_assoc()) {
        $detalles[] = $row;
    }

    if (!empty($detalles)) {
        $usuario = array(
            "nombre_usuario" => $detalles[0]['nombre_usuario'],
            "correo_electronico" => $detalles[0]['correo_electronico']
        );
    } else {
        $usuario = null;
    }

    echo json_encode(array("success" => true, "detalles" => $detalles, "usuario" => $usuario));
} else {
    echo json_encode(array("success" => false, "message" => "ID de venta no proporcionado"));
}
?>
