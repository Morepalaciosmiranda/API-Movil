<?php
header('Content-Type: application/json');
include '../includes/conexion.php';

$sql = "DESCRIBE compras";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "Campo: " . $row['Field'] . ", Tipo: " . $row['Type'] . "<br>";
    }
} else {
    echo "Error al obtener la estructura de la tabla: " . $conn->error;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getProveedores') {
    try {
        $sql = "SELECT id_proveedor, nombre_proveedor FROM proveedores";
        $result = $conn->query($sql);

        if ($result === false) {
            throw new Exception("Error al ejecutar la consulta: " . $conn->error);
        }

        $proveedores = [];
        while ($row = $result->fetch_assoc()) {
            $proveedores[] = $row;
        }

        echo json_encode(['success' => true, 'data' => $proveedores]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_proveedor'], $_POST['nombre_insumo'], $_POST['marca'], $_POST['cantidad'], $_POST['fecha_compra'], $_POST['total_compra'])) {
    try {
        $id_proveedor = $_POST['id_proveedor'];
        $nombre_insumo = $_POST['nombre_insumo'];
        $marca = $_POST['marca'];
        $cantidad = $_POST['cantidad'];
        $fecha_compra = $_POST['fecha_compra'];
        $total_compra = $_POST['total_compra'];

        // Aquí debería estar la consulta de inserción
        $insert_sql = "INSERT INTO compras (id_proveedor, nombre_insumo, marca, cantidad, fecha_compra, total_compra) VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        if (!$insert_stmt) {
            throw new Exception('Error al preparar la consulta de inserción: ' . $conn->error);
        }

        if (!$insert_stmt->bind_param("issids", $id_proveedor, $nombre_insumo, $marca, $cantidad, $fecha_compra, $total_compra)) {
            throw new Exception('Error al enlazar parámetros: ' . $insert_stmt->error);
        }

        if (!$insert_stmt->execute()) {
            throw new Exception('Error al ejecutar la consulta de inserción: ' . $insert_stmt->error);
        }

        echo json_encode(['success' => true, 'message' => 'Compra agregada exitosamente']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Si ninguna de las condiciones anteriores se cumple, enviar un error
echo json_encode(['success' => false, 'message' => 'Solicitud no válida o acción no reconocida']);

// Agregar un nuevo endpoint para obtener los insumos
if (isset($_GET['action']) && $_GET['action'] == 'getInsumos') {
    $sql = "SELECT DISTINCT id_insumo, nombre_insumo, cantidad FROM compras";
    $result = $conn->query($sql);
    $insumos = [];
    while ($row = $result->fetch_assoc()) {
        $insumos[] = $row;
    }
    echo json_encode($insumos);
    exit;
}

if (isset($_GET['eliminar'])) {
    $id_compra = $_GET['eliminar'];

    $conn->begin_transaction();

    try {
        // Primero, obtenemos la información de la compra
        $select_compra_sql = "SELECT id_insumo, cantidad FROM compras WHERE id_compra = ?";
        $select_compra_stmt = $conn->prepare($select_compra_sql);
        $select_compra_stmt->bind_param("i", $id_compra);
        $select_compra_stmt->execute();
        $result = $select_compra_stmt->get_result();
        $compra = $result->fetch_assoc();

        // Actualizamos la cantidad en la tabla insumos
        $update_insumo_sql = "UPDATE insumos SET cantidad = cantidad - ? WHERE id_insumo = ?";
        $update_insumo_stmt = $conn->prepare($update_insumo_sql);
        $update_insumo_stmt->bind_param("ii", $compra['cantidad'], $compra['id_insumo']);
        $update_insumo_stmt->execute();

        // Eliminamos la compra
        $eliminar_sql = "DELETE FROM compras WHERE id_compra = ?";
        $eliminar_stmt = $conn->prepare($eliminar_sql);
        $eliminar_stmt->bind_param("i", $id_compra);
        $eliminar_stmt->execute();

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Compra eliminada exitosamente.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => "Error al eliminar la compra: " . $e->getMessage()]);
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id_compra'])) {
    $id_compra = $_POST['edit_id_compra'];
    $id_proveedor = $_POST['edit_id_proveedor'];
    $nombre_insumo = $_POST['edit_nombre_insumo'];
    $marca = $_POST['edit_marca'];
    $cantidad = $_POST['edit_cantidad'];
    $fecha_compra = $_POST['edit_fecha_compra'];
    $total_compra = $_POST['edit_total_compra'];

    $conn->begin_transaction();

    try {
        // Primero, actualizamos el insumo
        $update_insumo_sql = "UPDATE insumos SET nombre_insumo = ? WHERE id_insumo = (SELECT id_insumo FROM compras WHERE id_compra = ?)";
        $update_insumo_stmt = $conn->prepare($update_insumo_sql);
        $update_insumo_stmt->bind_param("si", $nombre_insumo, $id_compra);
        $update_insumo_stmt->execute();

        // Obtenemos la información de la compra original
        $select_compra_sql = "SELECT id_insumo, cantidad FROM compras WHERE id_compra = ?";
        $select_compra_stmt = $conn->prepare($select_compra_sql);
        $select_compra_stmt->bind_param("i", $id_compra);
        $select_compra_stmt->execute();
        $result = $select_compra_stmt->get_result();
        $compra_original = $result->fetch_assoc();

        // Actualizamos la cantidad en la tabla insumos (restamos la cantidad original y sumamos la nueva)
        $update_insumo_cantidad_sql = "UPDATE insumos SET cantidad = cantidad - ? + ? WHERE id_insumo = ?";
        $update_insumo_cantidad_stmt = $conn->prepare($update_insumo_cantidad_sql);
        $update_insumo_cantidad_stmt->bind_param("iii", $compra_original['cantidad'], $cantidad, $compra_original['id_insumo']);
        $update_insumo_cantidad_stmt->execute();

        // Actualizamos la compra
        $update_compra_sql = "UPDATE compras SET id_proveedor = ?, marca = ?, cantidad = ?, fecha_compra = ?, total_compra = ? WHERE id_compra = ?";
        $update_compra_stmt = $conn->prepare($update_compra_sql);
        $update_compra_stmt->bind_param("isidsi", $id_proveedor, $marca, $cantidad, $fecha_compra, $total_compra, $id_compra);
        $update_compra_stmt->execute();

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Compra actualizada exitosamente.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => "Error al actualizar la compra: " . $e->getMessage()]);
    }
    exit();
}

$consulta_compras = "SELECT c.id_compra, p.nombre_proveedor, i.nombre_insumo, c.marca, c.cantidad, c.fecha_compra, c.total_compra 
FROM compras c
JOIN proveedores p ON c.id_proveedor = p.id_proveedor
JOIN insumos i ON c.id_insumo = i.id_insumo
ORDER BY c.fecha_compra DESC";
$resultado_compras = $conn->query($consulta_compras);

if ($resultado_compras->num_rows > 0) {
    $compras = array();
    while ($row = $resultado_compras->fetch_assoc()) {
        $compras[] = $row;
    }
    echo json_encode(['success' => true, 'compras' => $compras]);
} else {
    echo json_encode(['success' => false, 'message' => 'No hay compras disponibles.']);
}

if ($conn->error) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $conn->error]);
    exit;
}

$conn->close();
