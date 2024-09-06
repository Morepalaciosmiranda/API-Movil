<?php
include '../includes/conexion.php';

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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_proveedor'], $_POST['id_insumo'], $_POST['marca'], $_POST['fecha_compra'], $_POST['total_compra'], $_POST['cantidad'])) {
    $id_proveedor = $_POST['id_proveedor'];
    $id_insumo = $_POST['id_insumo'];
    $marca = $_POST['marca'];
    $fecha_compra = $_POST['fecha_compra'];
    $total_compra = $_POST['total_compra'];
    $cantidad = $_POST['cantidad'];

    $insert_sql = "INSERT INTO compras (id_proveedor, id_insumo, marca, fecha_compra, total_compra, cantidad) VALUES (?, ?, ?, ?, ?, ?)";
    if (!$insert_stmt->bind_param("iissdi", $id_proveedor, $id_insumo, $marca, $fecha_compra, $total_compra, $cantidad)) {
        die("Error al enlazar parámetros: " . $insert_stmt->error);
    }

    if (!$insert_stmt->execute()) {
        die("Error al ejecutar la consulta de inserción: " . $insert_stmt->error);
    }

    // Actualizar la cantidad en la tabla insumos
    $update_insumo_sql = "UPDATE insumos SET cantidad = cantidad + ? WHERE id_insumo = ?";
    $update_insumo_stmt = $conn->prepare($update_insumo_sql);
    if (!$update_insumo_stmt) {
        die("Error al preparar la consulta de actualización de insumo: " . $conn->error);
    }

    if (!$update_insumo_stmt->bind_param("ii", $cantidad, $id_insumo)) {
        die("Error al enlazar parámetros para actualización de insumo: " . $update_insumo_stmt->error);
    }

    if (!$update_insumo_stmt->execute()) {
        die("Error al ejecutar la consulta de actualización de insumo: " . $update_insumo_stmt->error);
    }

    header('Location: ../views/compras.php');
    exit();
}

// Agregar un nuevo endpoint para obtener los insumos
if (isset($_GET['action']) && $_GET['action'] == 'getInsumos') {
    $sql = "SELECT DISTINCT id_insumo, nombre_insumo FROM compras";
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
        $eliminar_detalles_sql = "DELETE FROM detalle_compras WHERE id_compra = ?";
        $eliminar_detalles_stmt = $conn->prepare($eliminar_detalles_sql);
        if (!$eliminar_detalles_stmt) {
            throw new Exception("Error al preparar la consulta de eliminación de detalles: " . $conn->error);
        }
        if (!$eliminar_detalles_stmt->bind_param("i", $id_compra)) {
            throw new Exception("Error al enlazar parámetros: " . $eliminar_detalles_stmt->error);
        }
        if (!$eliminar_detalles_stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta de eliminación de detalles: " . $eliminar_detalles_stmt->error);
        }
        $eliminar_detalles_stmt->close();

        $eliminar_sql = "DELETE FROM compras WHERE id_compra = ?";
        $eliminar_stmt = $conn->prepare($eliminar_sql);
        if (!$eliminar_stmt) {
            throw new Exception("Error al preparar la consulta de eliminación: " . $conn->error);
        }
        if (!$eliminar_stmt->bind_param("i", $id_compra)) {
            throw new Exception("Error al enlazar parámetros: " . $eliminar_stmt->error);
        }
        if (!$eliminar_stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta de eliminación: " . $eliminar_stmt->error);
        }
        $eliminar_stmt->close();

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Compra eliminada exitosamente.']);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => "Error al eliminar la compra: " . $e->getMessage()]);
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id_compra'], $_POST['edit_id_proveedor'], $_POST['edit_id_insumo'], $_POST['edit_marca'], $_POST['edit_fecha_compra'], $_POST['edit_total_compra'], $_POST['edit_cantidad'])) {
    $id_compra = $_POST['edit_id_compra'];
    $id_proveedor = $_POST['edit_id_proveedor'];
    $id_insumo = $_POST['edit_id_insumo'];
    $marca = $_POST['edit_marca'];
    $fecha_compra = $_POST['edit_fecha_compra'];
    $total_compra = $_POST['edit_total_compra'];
    $cantidad = $_POST['edit_cantidad'];

    $conn->begin_transaction();

    try {
        $update_compra_sql = "UPDATE compras SET id_proveedor = ?, id_insumo = ?, marca = ?, fecha_compra = ?, total_compra = ?, cantidad = ? WHERE id_compra = ?";
        $stmt_compra = $conn->prepare($update_compra_sql);
        if (!$stmt_compra) {
            throw new Exception("Error al preparar la consulta de actualización en compras: " . $conn->error);
        }

        if (!$stmt_compra->bind_param("iissdii", $id_proveedor, $id_insumo, $marca, $fecha_compra, $total_compra, $cantidad, $id_compra)) {
            throw new Exception("Error al enlazar parámetros: " . $stmt_compra->error);
        }

        if (!$stmt_compra->execute()) {
            throw new Exception("Error al ejecutar la consulta de actualización en compras: " . $stmt_compra->error);
        }

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Compra actualizada exitosamente.']);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => "Error al actualizar la compra: " . $e->getMessage()]);
        exit();
    }
}

$consulta_compras = "SELECT * FROM compras";
$resultado_compras = $conn->query($consulta_compras);

if ($resultado_compras->num_rows > 0) {
    $compras = array();
    while ($row = $resultado_compras->fetch_assoc()) {
        $compras[] = $row;
    }
} else {
    $compras = array();
}

$conn->close();
?>

conexion.php
<?php
$url = parse_url(getenv("MYSQL_URL"));

$host = $url["host"];
$username = $url["user"];
$password = $url["pass"];
$database = substr($url["path"], 1);
$port = $url["port"];

// Crear la conexión
$conn = mysqli_init();
if (!$conn) {
    die("mysqli_init failed");
}

if (!mysqli_real_connect($conn, $host, $username, $password, $database, $port)) {
    die("Connect Error: " . mysqli_connect_error());
}

$conn->set_charset("utf8");
$conn->query("SET time_zone = '+00:00'");
?>