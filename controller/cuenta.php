<?php
include '../includes/conexion.php'; 


$nombre_usuario = 'admin';
$correo_electronico = 'admin@gmail.com';
$contraseña = 'melos';


$hashed_password = password_hash($contraseña, PASSWORD_DEFAULT);


$sql = "INSERT INTO usuarios (nombre_usuario, correo_electronico, contrasena, id_rol, estado_usuario) VALUES ('$nombre_usuario', '$correo_electronico', '$hashed_password', 1, 'activo')";


if ($conn->query($sql) === TRUE) {
    echo "Cuenta de administrador creada exitosamente.";
} else {
    echo "Error al crear la cuenta de administrador: " . $conn->error;
}


$conn->close();
?>
