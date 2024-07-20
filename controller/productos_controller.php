<?php
include '../includes/conexion.php';

function mostrarAlerta($mensaje, $tipo = 'success')
{
    echo '<script>';
    echo 'Swal.fire({';
    echo 'title: "Notificación",';
    echo 'text: "' . $mensaje . '",';
    echo 'icon: "' . $tipo . '",';
    echo 'confirmButtonText: "OK"';
    echo '});';
    echo '</script>';
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nombre'], $_POST['precio'], $_POST['descripcion'], $_FILES['imagen'], $_POST['insumo_id'], $_POST['cantidad_insumo'])) {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $descripcion = $_POST['descripcion'];
    $insumo_ids = $_POST['insumo_id'];
    $cantidades_insumo = $_POST['cantidad_insumo'];


    $errores = false;
foreach ($insumo_ids as $index => $insumo_id) {
    $cantidad_insumo = $cantidades_insumo[$index];

   
    $consulta_cantidad_sql = "SELECT cantidad FROM insumos WHERE id_insumo = ?";
    $consulta_cantidad_stmt = $conn->prepare($consulta_cantidad_sql);
    if (!$consulta_cantidad_stmt) {
        die("Error al preparar la consulta de cantidad: " . $conn->error);
    }
    $consulta_cantidad_stmt->bind_param("i", $insumo_id);
    $consulta_cantidad_stmt->execute();
    $consulta_cantidad_stmt->bind_result($cantidad_disponible);
    $consulta_cantidad_stmt->fetch();
    $consulta_cantidad_stmt->close();

 
    if ($cantidad_insumo > $cantidad_disponible) {
        mostrarAlerta("No hay suficientes insumos disponibles de ese tipo.", "error");
        $errores = true;
        break;
    }
}

    if (!$errores) {
        $imagen = $_FILES['imagen'];
        $imagen_tmp_name = $imagen['tmp_name'];
        $imagen_error = $imagen['error'];

        if ($imagen_error !== UPLOAD_ERR_OK) {
            die("Error al cargar la imagen: " . $imagen_error);
        }

        $upload_dir = '../uploads/';
        $imagen_nombre = uniqid('producto_') . '_' . basename($imagen['name']);
        $imagen_destino = $upload_dir . $imagen_nombre;

        if (!move_uploaded_file($imagen_tmp_name, $imagen_destino)) {
            die("Error al mover la imagen al directorio de destino.");
        }

    
        $conn->begin_transaction();

        try {
      
            $insert_sql = "INSERT INTO productos (nombre_producto, foto, descripcion_producto, valor_unitario) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            if (!$insert_stmt) {
                throw new Exception("Error al preparar la consulta de inserción: " . $conn->error);
            }

            if (!$insert_stmt->bind_param("sssd", $nombre, $imagen_nombre, $descripcion, $precio)) {
                throw new Exception("Error al enlazar parámetros: " . $insert_stmt->error);
            }

            if (!$insert_stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta de inserción: " . $insert_stmt->error);
            }

            $producto_id = $conn->insert_id;

            foreach ($insumo_ids as $index => $insumo_id) {
                $cantidad_insumo = $cantidades_insumo[$index];

                $update_sql = "UPDATE insumos SET cantidad = cantidad - ? WHERE id_insumo = ?";
                $update_stmt = $conn->prepare($update_sql);
                if (!$update_stmt) {
                    throw new Exception("Error al preparar la consulta de actualización: " . $conn->error);
                }

                if (!$update_stmt->bind_param("ii", $cantidad_insumo, $insumo_id)) {
                    throw new Exception("Error al enlazar parámetros: " . $update_stmt->error);
                }

                if (!$update_stmt->execute()) {
                    throw new Exception("Error al ejecutar la consulta de actualización: " . $update_stmt->error);
                }
            }

        
            $conn->commit();

            mostrarAlerta("Producto agregado correctamente");
        } catch (Exception $e) {
            $conn->rollback();
            die("Error: " . $e->getMessage());
        }
    }

    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_editar'], $_POST['nombre_edit'], $_POST['precio_edit'], $_POST['descripcion_edit'])) {
    $id_editar = $_POST['id_editar'];
    $nombre_editar = $_POST['nombre_edit'];
    $precio_editar = $_POST['precio_edit'];
    $descripcion_editar = $_POST['descripcion_edit'];

    if (isset($_FILES['imagen_edit']) && $_FILES['imagen_edit']['error'] == UPLOAD_ERR_OK) {
        $imagen = $_FILES['imagen_edit'];
        $imagen_tmp_name = $imagen['tmp_name'];
        $imagen_nombre = uniqid('producto_') . '_' . basename($imagen['name']);
        $upload_dir = '../uploads/';
        $imagen_destino = $upload_dir . $imagen_nombre;

        if (!move_uploaded_file($imagen_tmp_name, $imagen_destino)) {
            die("Error al mover la imagen al directorio de destino.");
        }

        $actualizar_sql = "UPDATE productos SET nombre_producto = ?, descripcion_producto = ?, valor_unitario = ?, foto = ? WHERE id_producto = ?";
        $actualizar_stmt = $conn->prepare($actualizar_sql);
        if (!$actualizar_stmt) {
            die("Error al preparar la consulta de actualización: " . $conn->error);
        }

        if (!$actualizar_stmt->bind_param("ssdsi", $nombre_editar, $descripcion_editar, $precio_editar, $imagen_nombre, $id_editar)) {
            die("Error al enlazar parámetros: " . $actualizar_stmt->error);
        }
    } else {
        $actualizar_sql = "UPDATE productos SET nombre_producto = ?, descripcion_producto = ?, valor_unitario = ? WHERE id_producto = ?";
        $actualizar_stmt = $conn->prepare($actualizar_sql);
        if (!$actualizar_stmt) {
            die("Error al preparar la consulta de actualización: " . $conn->error);
        }

        if (!$actualizar_stmt->bind_param("ssdi", $nombre_editar, $descripcion_editar, $precio_editar, $id_editar)) {
            die("Error al enlazar parámetros: " . $actualizar_stmt->error);
        }
    }

    if (!$actualizar_stmt->execute()) {
        die("Error al ejecutar la consulta de actualización: " . $actualizar_stmt->error);
    }

    mostrarAlerta("Producto editado correctamente");

    exit();
}

if (isset($_GET['eliminar'])) {
    $id_producto = $_GET['eliminar'];

    $eliminar_sql = "DELETE FROM productos WHERE id_producto = ?";
    $eliminar_stmt = $conn->prepare($eliminar_sql);
    if (!$eliminar_stmt) {
        die("Error al preparar la consulta de eliminación: " . $conn->error);
    }

    if (!$eliminar_stmt->bind_param("i", $id_producto)) {
        die("Error al enlazar parámetros: " . $eliminar_stmt->error);
    }

    if (!$eliminar_stmt->execute()) {
        die("Error al ejecutar la consulta de eliminación: " . $eliminar_stmt->error);
    }

    mostrarAlerta("Producto eliminado correctamente");

    exit();
}

$consulta_productos = "SELECT * FROM productos";
$resultado_productos = $conn->query($consulta_productos);

if ($resultado_productos->num_rows > 0) {
    $productos = array();
    while ($row = $resultado_productos->fetch_assoc()) {
        $productos[] = $row;
    }
} else {
    $productos = array();
}
?>
