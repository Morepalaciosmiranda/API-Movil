<?php
require_once './includes/conexion.php';

// Actualizar estructura de la base de datos
$sql = "ALTER TABLE rolesxpermiso
        DROP FOREIGN KEY rolesxpermiso_ibfk_1,
        ADD CONSTRAINT rolesxpermiso_ibfk_1 FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario);";

if ($conn->query($sql) === TRUE) {
    echo "Estructura de la base de datos actualizada correctamente.";
} else {
    echo "Error al actualizar la estructura de la base de datos: ". $conn->error;
}

// Cerrar conexión
$conn->close();

?>