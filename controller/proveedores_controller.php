<?php
include '../includes/conexion.php';


if ($_SERVER["REQUEST_METHOD"] == "GET" && !isset($_GET['eliminar'])) {
    $consulta_proveedores = "SELECT id_proveedor, nombre_proveedor, correo_electronico, celular, estado_proveedor, contacto FROM proveedores";
    $resultado_proveedores = $conn->query($consulta_proveedores);

    if ($resultado_proveedores->num_rows > 0) {
        $proveedores = array();
        while ($row = $resultado_proveedores->fetch_assoc()) {
            $proveedores[] = $row;
        }
        echo json_encode($proveedores);
    } else {
        echo json_encode([]);
    }
    exit();
}



if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];


    $sql_verificar_compras = "SELECT COUNT(*) AS total_compras FROM comprass WHERE id_proveedor = ?";
    $stmt = $conn->prepare($sql_verificar_compras);

    if ($stmt) {
        $stmt->bind_param("i", $id_eliminar);
        $stmt->execute();
        $stmt->bind_result($total_compras);
        $stmt->fetch();
        $stmt->close();

        if ($total_compras > 0) {
       
            echo json_encode(array("error" => true, "message" => "El proveedor está asociado a compras y no puede ser eliminado."));
            exit();
        } else {
        
            $sql_delete = "DELETE FROM proveedores WHERE id_proveedor = ?";
            $stmt = $conn->prepare($sql_delete);

            if ($stmt) {
                $stmt->bind_param("i", $id_eliminar);
                $stmt->execute();
                $stmt->close();
             
                echo json_encode(array("success" => true, "message" => "Proveedor eliminado correctamente."));
                exit();
            } else {
                echo json_encode(array("error" => true, "message" => "Error al preparar la consulta de eliminación."));
                exit();
            }
        }
    } else {
        echo json_encode(array("error" => true, "message" => "Error al preparar la consulta de verificación de compras."));
        exit();
    }
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['id_editar'])) {
        $id = $_POST['id_editar'];
        $nombre = $_POST['nombre_edit'];
        $correo = $_POST['correo_edit'];
        $celular = $_POST['celular_edit'];
        $contacto = $_POST['contacto_edit'];
        $estado = $_POST['estado_edit'];

        $sql_update = "UPDATE proveedores SET nombre_proveedor=?, correo_electronico=?, celular=?, estado_proveedor=?, contacto=? WHERE id_proveedor=?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("sssssi", $nombre, $correo, $celular, $estado, $contacto, $id);
        $stmt->execute();
        $stmt->close();
    } else {
        $nombre = $_POST['nombre'];
        $correo = $_POST['correo'];
        $celular = $_POST['celular'];
        $contacto = $_POST['contacto'];
        $estado = $_POST['estado'];

        $sql_insert = "INSERT INTO proveedores (nombre_proveedor, correo_electronico, celular, estado_proveedor, contacto) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param("sssss", $nombre, $correo, $celular, $estado, $contacto);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: ../views/proveedores.php");
    exit();
}
?>