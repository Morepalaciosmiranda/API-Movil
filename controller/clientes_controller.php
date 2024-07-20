<?php
include '../includes/conexion.php';

function obtenerClientes($offset, $items_por_pagina) {
    global $conn;
    $sql = "SELECT * FROM clientes LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $offset, $items_por_pagina);
    $stmt->execute();
    $result = $stmt->get_result();
    $clientes = array();
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }
    return $clientes;
}

function obtenerTotalClientes() {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM clientes";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}
?>
