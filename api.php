<?php
header("Content-Type: application/json");
require_once('./includes/conexion.php'); // Archivo de configuración de la base de datos

// Manejar la solicitud POST para iniciar sesión
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    // Obtener datos del formulario
    $correo_electronico = mysqli_real_escape_string($conn, $data->correo_electronico);
    $contrasena = mysqli_real_escape_string($conn, $data->contrasena);

    // Consulta SQL para verificar credenciales
    $query = "SELECT * FROM usuarios WHERE correo_electronico='$correo_electronico'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($contrasena, $row['contrasena'])) {
            // Credenciales válidas
            $response = array(
                "status" => "success",
                "role" => $row['rol'], // Devuelve el rol del usuario
                "message" => "Inicio de sesión exitoso"
            );
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

    echo json_encode($response);
}

mysqli_close($conn); // Cerrar conexión
?>
