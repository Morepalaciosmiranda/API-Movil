<?php
include '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(obtenerInsumos());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_compra'], $_POST['fecha_vencimiento'], $_POST['estado_insumo'])) {
    $id_compra = $_POST['id_compra'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];
    $estado_insumo = $_POST['estado_insumo'];

    $conn->begin_transaction();

    try {
        // Obtener información de la compra
        $query_compra = "SELECT id_proveedor, cantidad FROM compras WHERE id_compra = ?";
        $stmt_compra = $conn->prepare($query_compra);
        $stmt_compra->bind_param("i", $id_compra);
        $stmt_compra->execute();
        $result_compra = $stmt_compra->get_result();
        $compra = $result_compra->fetch_assoc();

        if (!$compra) {
            throw new Exception("No se encontró la compra especificada.");
        }

        $id_proveedor = $compra['id_proveedor'];
        $cantidad = $compra['cantidad'];

        // Insertar el insumo
        $insert_sql = "INSERT INTO insumos (cantidad, fecha_vencimiento, estado_insumo, id_proveedor, id_compra) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        if (!$insert_stmt) {
            throw new Exception("Error al preparar la consulta de inserción: " . $conn->error);
        }

        if (!$insert_stmt->bind_param("issis", $cantidad, $fecha_vencimiento, $estado_insumo, $id_proveedor, $id_compra)) {
            throw new Exception("Error al enlazar parámetros: " . $insert_stmt->error);
        }

        if (!$insert_stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta de inserción: " . $insert_stmt->error);
        }

        $conn->commit();

        header('Location: ../views/insumos.php?success=1');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        header('Location: ../views/insumos.php?error=' . urlencode($e->getMessage()));
        exit();
    }
}


if (isset($_POST['id_editar'], $_POST['fecha_vencimiento_editar'], $_POST['estado_insumo_editar'])) {
    $id_editar = $_POST['id_editar'];
    $fecha_vencimiento_editar = $_POST['fecha_vencimiento_editar'];
    $estado_insumo_editar = $_POST['estado_insumo_editar'];

    $conn->begin_transaction();

    try {
        $actualizar_sql = "UPDATE insumos SET fecha_vencimiento = ?, estado_insumo = ? WHERE id_insumo = ?";
        $actualizar_stmt = $conn->prepare($actualizar_sql);
        if (!$actualizar_stmt) {
            throw new Exception("Error al preparar la consulta de actualización: " . $conn->error);
        }

        if (!$actualizar_stmt->bind_param("ssi", $fecha_vencimiento_editar, $estado_insumo_editar, $id_editar)) {
            throw new Exception("Error al enlazar parámetros: " . $actualizar_stmt->error);
        }

        if (!$actualizar_stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta de actualización: " . $actualizar_stmt->error);
        }

        $conn->commit();

        header('Location: ../views/insumos.php?success=2');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        header('Location: ../views/insumos.php?error=' . urlencode($e->getMessage()));
        exit();
    }
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

    $consulta = "SELECT i.id_insumo, i.cantidad, i.fecha_vencimiento, i.estado_insumo,
                        p.nombre_proveedor, c.fecha_compra, c.marca, c.total_compra
                 FROM insumos i
                 JOIN compras c ON i.id_compra = c.id_compra
                 JOIN proveedores p ON i.id_proveedor = p.id_proveedor
                 ORDER BY c.fecha_compra DESC";
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