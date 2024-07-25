<?php
require_once './includes/conexion.php';

// Verificar si la restricción de clave externa existe
$sql = "SELECT COUNT(*) as count
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_NAME = 'rolesxpermiso'
        AND CONSTRAINT_NAME = 'rolesxpermiso_ibfk_1'
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'";

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $sql = "ALTER TABLE rolesxpermiso DROP FOREIGN KEY rolesxpermiso_ibfk_1";
    $conn->query($sql);
}

// Crear nueva restricción de clave externa
$sql = "ALTER TABLE rolesxpermiso
        ADD CONSTRAINT fk_rolesxpermiso_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)";
$conn->query($sql);

// Cerrar conexión
$conn->close();

?>