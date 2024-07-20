<?php
session_start();
include '../includes/conexion.php';

function send_json_response($success, $message = '') {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nombre_cliente']) && isset($_POST['calle'])  && isset($_POST['interior']) && isset($_POST['barrio_cliente']) && isset($_POST['telefono_cliente']) && isset($_POST['productos'])) {
        
        $nombre = $_POST['nombre_cliente'];
        $calle = $_POST['calle'];
        $interior = $_POST['interior'];
        $barrio = $_POST['barrio_cliente'];
        $telefono = $_POST['telefono_cliente'];

        if (empty($nombre) || empty($calle) || empty($interior) || empty($barrio) || empty($telefono)) {
            send_json_response(false, 'No se recibieron todos los datos esperados desde el formulario.');
        }

        $direccion = "$calle $numero, $interior";

        $productos = json_decode($_POST['productos'], true);

        if (!$productos) {
            send_json_response(false, 'No se recibieron productos en la solicitud.');
        }

        if (!isset($_SESSION['id_usuario'])) {
            send_json_response(false, 'No se encontró el id_usuario en la sesión.');
        }
        $id_usuario = $_SESSION['id_usuario'];

        $sql_pedido = "INSERT INTO pedidos (fecha_pedido, precio_domicilio, estado_pedido, id_usuario) VALUES (NOW(), 5000, 'en proceso', '$id_usuario')";
        if (mysqli_query($conn, $sql_pedido)) {
            $pedido_id = mysqli_insert_id($conn);

            foreach ($productos as $producto) {
                $id_producto = $producto['id'];
                $nombre_producto = $producto['name'];
                $precio_producto = $producto['price'];

                $sql_detalle = "INSERT INTO detalle_pedido (id_pedido, id_producto, nombre, direccion, barrio, telefono, cantidad, valor_unitario, subtotal) 
                                VALUES ('$pedido_id', '$id_producto', '$nombre_producto', '$direccion', '$barrio', '$telefono', 1, '$precio_producto', '$precio_producto')";
                if (!mysqli_query($conn, $sql_detalle)) {
                    send_json_response(false, 'Error al insertar detalle del pedido para el producto con ID ' . $id_producto . ': ' . mysqli_error($conn));
                }
            }

            send_json_response(true, 'Pedido realizado con éxito.');
        } else {
            send_json_response(false, 'Error al realizar el pedido: ' . mysqli_error($conn));
        }
    } elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pedido_id']) && isset($_POST['nuevo_estado'])) {
        $pedido_id = $_POST['pedido_id'];
        $nuevo_estado = $_POST['nuevo_estado'];

        $sql_actualizar_estado = "UPDATE pedidos SET estado_pedido = ? WHERE id_pedido = ?";
        $stmt = $conn->prepare($sql_actualizar_estado);
        $stmt->bind_param('si', $nuevo_estado, $pedido_id);

        if ($stmt->execute()) {
            if ($nuevo_estado === 'entregado') {
                $sql_usuario = "SELECT id_usuario FROM pedidos WHERE id_pedido = ?";
                $stmt_usuario = $conn->prepare($sql_usuario);
                $stmt_usuario->bind_param('i', $pedido_id);
                $stmt_usuario->execute();
                $resultado_usuario = $stmt_usuario->get_result();

                if ($resultado_usuario && $resultado_usuario->num_rows > 0) {
                    $id_usuario = $resultado_usuario->fetch_assoc()['id_usuario'];

                    $sql_verificar_cliente = "SELECT * FROM clientes WHERE id_usuario = ?";
                    $stmt_verificar_cliente = $conn->prepare($sql_verificar_cliente);
                    $stmt_verificar_cliente->bind_param('i', $id_usuario);
                    $stmt_verificar_cliente->execute();
                    $resultado_cliente = $stmt_verificar_cliente->get_result();

                    if ($resultado_cliente->num_rows == 0) {
                        $sql_datos_usuario = "SELECT nombre_usuario, correo_electronico FROM usuarios WHERE id_usuario = ?";
                        $stmt_datos_usuario = $conn->prepare($sql_datos_usuario);
                        $stmt_datos_usuario->bind_param('i', $id_usuario);
                        $stmt_datos_usuario->execute();
                        $resultado_datos_usuario = $stmt_datos_usuario->get_result();

                        if ($resultado_datos_usuario && $resultado_datos_usuario->num_rows > 0) {
                            $datos_usuario = $resultado_datos_usuario->fetch_assoc();
                            $nombre_cliente = $datos_usuario['nombre_usuario'];
                            $correo_electronico = $datos_usuario['correo_electronico'];

                            $sql_insertar_cliente = "INSERT INTO clientes (id_usuario, nombre_cliente, correo_electronico, estado_cliente) VALUES (?, ?, ?, 'Activo')";
                            $stmt_insertar_cliente = $conn->prepare($sql_insertar_cliente);
                            $stmt_insertar_cliente->bind_param('iss', $id_usuario, $nombre_cliente, $correo_electronico);
                            $stmt_insertar_cliente->execute();
                        }
                    }

                    $sql_insertar_venta = "INSERT INTO ventas (id_usuario, id_pedido, fecha_venta) VALUES (?, ?, NOW())";
                    $stmt_insertar_venta = $conn->prepare($sql_insertar_venta);
                    $stmt_insertar_venta->bind_param('ii', $id_usuario, $pedido_id);
                    $stmt_insertar_venta->execute();

                    $venta_id = $conn->insert_id;

                    $sql_obtener_detalles_pedido = "SELECT id_producto, cantidad, valor_unitario FROM detalle_pedido WHERE id_pedido = ?";
                    $stmt_obtener_detalles_pedido = $conn->prepare($sql_obtener_detalles_pedido);
                    $stmt_obtener_detalles_pedido->bind_param('i', $pedido_id);
                    $stmt_obtener_detalles_pedido->execute();
                    $result_detalles_pedido = $stmt_obtener_detalles_pedido->get_result();

                    while ($detalle_pedido = $result_detalles_pedido->fetch_assoc()) {
                        $id_producto = $detalle_pedido['id_producto'];
                        $cantidad = $detalle_pedido['cantidad'];
                        $valor_unitario = $detalle_pedido['valor_unitario'];
                        $total_venta = $cantidad * $valor_unitario;

                        $sql_detalle_venta = "INSERT INTO detalle_venta (id_venta, id_producto, cantidad, valor_unitario, total_venta) 
                                              VALUES (?, ?, ?, ?, ?)";
                        $stmt_detalle_venta = $conn->prepare($sql_detalle_venta);
                        $stmt_detalle_venta->bind_param('iiidd', $venta_id, $id_producto, $cantidad, $valor_unitario, $total_venta);
                        $stmt_detalle_venta->execute();
                    }
                }
            }
            send_json_response(true, 'Estado actualizado correctamente');
        } else {
            send_json_response(false, 'Error al actualizar el estado del pedido: ' . $stmt->error);
        }
        $stmt->close();
    } elseif (isset($_GET['eliminar'])) {
        $pedido_id = $_GET['eliminar'];

        $sql_detalle = "DELETE FROM detalle_pedido WHERE id_pedido = ?";
        $stmt_detalle = $conn->prepare($sql_detalle);
        $stmt_detalle->bind_param('i', $pedido_id);

        if ($stmt_detalle->execute()) {
            $sql_pedido = "DELETE FROM pedidos WHERE id_pedido = ?";
            $stmt_pedido = $conn->prepare($sql_pedido);
            $stmt_pedido->bind_param('i', $pedido_id);

            if ($stmt_pedido->execute()) {
                send_json_response(true, 'Pedido eliminado con éxito.');
            } else {
                send_json_response(false, 'Error al eliminar el pedido: ' . $stmt_pedido->error);
            }
            $stmt_pedido->close();
        } else {
            send_json_response(false, 'Error al eliminar los detalles del pedido: ' . $stmt_detalle->error);
        }
        $stmt_detalle->close();
    }
    $conn->close();
} catch (Exception $e) {
    send_json_response(false, 'Error inesperado: ' . $e->getMessage());
}
?>
