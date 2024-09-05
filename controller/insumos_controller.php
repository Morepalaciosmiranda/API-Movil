<?php
include '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_proveedor'], $_POST['id_compra'], $_POST['fecha_vencimiento'], $_POST['estado_insumo'])) {
    $id_proveedor = $_POST['id_proveedor'];
    $id_compra = $_POST['id_compra'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];
    $estado_insumo = $_POST['estado_insumo'];

    // Obtener la cantidad de la compra seleccionada
    $sql_cantidad = "SELECT cantidad FROM compras WHERE id_compra = ?";
    $stmt_cantidad = $conn->prepare($sql_cantidad);
    $stmt_cantidad->bind_param("i", $id_compra);
    $stmt_cantidad->execute();
    $result_cantidad = $stmt_cantidad->get_result();
    $cantidad = $result_cantidad->fetch_assoc()['cantidad'];

    $insert_sql = "INSERT INTO insumos (id_proveedor, id_compra, fecha_vencimiento, cantidad, estado_insumo) VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    if (!$insert_stmt) {
        die("Error al preparar la consulta de inserción: " . $conn->error);
    }

    if (!$insert_stmt->bind_param("iisis", $id_proveedor, $id_compra, $fecha_vencimiento, $cantidad, $estado_insumo)) {
        die("Error al enlazar parámetros: " . $insert_stmt->error);
    }

    if (!$insert_stmt->execute()) {
        die("Error al ejecutar la consulta de inserción: " . $insert_stmt->error);
    }

    header('Location: ../views/insumos.php');
    exit();
}


if (isset($_POST['id_editar'], $_POST['nombre_editar'], $_POST['id_proveedor_editar'], $_POST['precio_editar'], $_POST['fecha_vencimiento_editar'], $_POST['marca_editar'], $_POST['cantidad_editar'], $_POST['estado_insumo_editar'])) {
    $id_editar = $_POST['id_editar'];
    $nombre_editar = $_POST['nombre_editar'];
    $id_proveedor_editar = $_POST['id_proveedor_editar'];
    $precio_editar = $_POST['precio_editar'];
    $fecha_vencimiento_editar = $_POST['fecha_vencimiento_editar'];
    $marca_editar = $_POST['marca_editar'];
    $cantidad_editar = $_POST['cantidad_editar'];
    $estado_insumo_editar = $_POST['estado_insumo_editar'];

    if (strtotime($fecha_vencimiento_editar) < strtotime(date('Y-m-d'))) {
        die("Error: La fecha de vencimiento no puede ser una fecha pasada.");
    }

    $actualizar_sql = "UPDATE insumos SET nombre_insumo = ?, id_proveedor = ?, precio = ?, fecha_vencimiento = ?, marca = ?, cantidad = ?, estado_insumo = ? WHERE id_insumo = ?";
    $actualizar_stmt = $conn->prepare($actualizar_sql);
    if (!$actualizar_stmt) {
        die("Error al preparar la consulta de actualización: " . $conn->error);
    }

    if (!$actualizar_stmt->bind_param("sisssisi", $nombre_editar, $id_proveedor_editar, $precio_editar, $fecha_vencimiento_editar, $marca_editar, $cantidad_editar, $estado_insumo_editar, $id_editar)) {
        die("Error al enlazar parámetros: " . $actualizar_stmt->error);
    }

    if (!$actualizar_stmt->execute()) {
        die("Error al ejecutar la consulta de actualización: " . $actualizar_stmt->error);
    }

    header('Location: ../views/insumos.php');
    exit();
}


if (isset($_GET['eliminar'])) {
    $id_insumo = $_GET['eliminar'];

    try {
        $eliminar_sql = "DELETE FROM insumos WHERE id_insumo = ?";
        $eliminar_stmt = $conn->prepare($eliminar_sql);
        if (!$eliminar_stmt) {
            throw new Exception("Error al preparar la consulta de eliminación: " . $conn->error);
        }

        if (!$eliminar_stmt->bind_param("i", $id_insumo)) {
            throw new Exception("Error al enlazar parámetros: " . $eliminar_stmt->error);
        }

        if (!$eliminar_stmt->execute()) {
            // Si hay una excepción, la capturamos
            throw new mysqli_sql_exception($eliminar_stmt->error, $eliminar_stmt->errno);
        }

        // Redirigir a la vista de insumos si todo salió bien
        header('Location: ../views/insumos.php?eliminar=exito');
        exit();
    } catch (mysqli_sql_exception $e) {
        // Verificamos si el error es debido a la restricción de clave foránea
        if ($e->getCode() == 1451) {
            header('Location: ../views/insumos.php?error=relacion');
        } else {
            die("Error inesperado al intentar eliminar el insumo: " . $e->getMessage());
        }
    }
}


$consulta_insumos = "SELECT * FROM insumos";
$resultado_insumos = $conn->query($consulta_insumos);

if ($resultado_insumos->num_rows > 0) {
    $insumos = array();
    while ($row = $resultado_insumos->fetch_assoc()) {
        $insumos[] = $row;
    }
} else {
    $insumos = array();
}

function obtenerInsumos() {
    global $conn;
    $insumos = array();

    $consulta = "SELECT i.*, p.nombre_proveedor, c.fecha_compra, c.marca 
                 FROM insumos i 
                 JOIN proveedores p ON i.id_proveedor = p.id_proveedor
                 JOIN compras c ON i.id_compra = c.id_compra";
    $resultado = $conn->query($consulta);

    if ($resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            $insumos[] = $row;
        }
    }

    return $insumos;
}

function buscarInsumosPorNombre($nombre) {
    global $conn;
    $insumos = array();

    $consulta = "SELECT i.*, p.nombre_proveedor 
                 FROM insumos i 
                 JOIN proveedores p ON i.id_proveedor = p.id_proveedor 
                 WHERE i.nombre_insumo LIKE '%$nombre%'";
    $resultado = $conn->query($consulta);

    if ($resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            $insumos[] = $row;
        }
    }

    return $insumos;
}