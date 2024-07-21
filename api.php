<?php
header("Content-Type: application/json");
require_once('./includes/conexion.php'); // Archivo de configuración de la base de datos
// Manejar la solicitud POST para iniciar sesión
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    // Obtener datos del formulario
    $correo_electronico = $data->correo_electronico;
    $contrasena = $data->contrasena;
    // Consulta SQL para verificar credenciales
    $query = "SELECT usuarios.*, roles.nombre_rol FROM usuarios
              INNER JOIN roles ON usuarios.id_rol = roles.id_rol
              WHERE correo_electronico=?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        $response = array(
            "status" => "error",
            "message" => "Error en la preparación de la consulta: " . $conn->error
        );
    } else {
        $stmt->bind_param("s", $correo_electronico);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($contrasena, $row['contrasena'])) {
                // Credenciales válidas
                if ($row['estado_usuario'] == 'Inactivo') {
                    $response = array(
                        "status" => "warning",
                        "message" => "Su cuenta está inactiva. " . $row['mensaje_estado']
                    );
                } else {
                    $response = array(
                        "status" => "success",
                        "role" => $row['nombre_rol'],
                        "message" => "Inicio de sesión exitoso"
                    );
                }
            } else {
                // Contraseña incorrecta
                $response = array(
                    "status" => "error",
                    "message" => "Contraseña incorrecta"
                );
            }
        } else {
            // Usuario no encontrado
            $response = array(
                "status" => "error",
                "message" => "Usuario no encontrado"
            );
        }
        $stmt->close();
    }
    echo json_encode($response);
}
mysqli_close($conn); // Cerrar conexión

// Nueva ruta para manejar la creación de pedidos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'crear_pedido') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Aquí va la lógica para crear el pedido en la base de datos
    $nombre_cliente = $data['nombre_cliente'];
    $direccion = $data['direccion'];
    $barrio = $data['barrio'];
    $telefono = $data['telefono'];
    $metodo_pago = $data['metodo_pago'] ?? 'Efectivo';
    $productos = $data['productos'];

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // Insertar el pedido
        $sql_pedido = "INSERT INTO pedidos (fecha_pedido, estado_pedido, id_usuario) VALUES (NOW(), 'Pendiente', ?)";
        $stmt_pedido = $conn->prepare($sql_pedido);
        $stmt_pedido->bind_param("i", $data['id_usuario']);
        $stmt_pedido->execute();
        $pedido_id = $conn->insert_id;

        // Insertar detalles del pedido
        $sql_detalle = "INSERT INTO detalle_pedido (id_pedido, id_producto, nombre, direccion, barrio, telefono, cantidad, valor_unitario, subtotal) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_detalle = $conn->prepare($sql_detalle);

        foreach ($productos as $producto) {
            $subtotal = $producto['cantidad'] * $producto['precio'];
            $stmt_detalle->bind_param("iissssidd", $pedido_id, $producto['id'], $nombre_cliente, $direccion, $barrio, $telefono, $producto['cantidad'], $producto['precio'], $subtotal);
            $stmt_detalle->execute();
        }

        // Confirmar transacción
        $conn->commit();

        // Preparar datos para la app Flutter
        $pedidoFlutter = [
            'id' => $pedido_id,
            'fechapedido' => date('Y-m-d H:i:s'),
            'fechaentrega' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'metodopago' => $metodo_pago,
            'estadopedido' => 'Pendiente'
        ];

        send_json_response(true, "Pedido creado exitosamente", $pedidoFlutter);
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        send_json_response(false, "Error al crear el pedido: " . $e->getMessage());
    }
}

// Ruta para obtener pedidos (para la app Flutter)
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'obtener_pedidos') {
    $sql = "SELECT id_pedido, fecha_pedido, fecha_entrega, metodo_pago, estado_pedido FROM pedidos ORDER BY fecha_pedido DESC";
    $result = $conn->query($sql);

    if ($result) {
        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $pedidos[] = [
                'id' => $row['id_pedido'],
                'fechapedido' => $row['fecha_pedido'],
                'fechaentrega' => $row['fecha_entrega'],
                'metodopago' => $row['metodo_pago'],
                'estadopedido' => $row['estado_pedido']
            ];
        }
        send_json_response(true, "Pedidos obtenidos con éxito", $pedidos);
    } else {
        send_json_response(false, "Error al obtener los pedidos: " . $conn->error);
    }
}

mysqli_close($conn); // Cerrar conexión
?>