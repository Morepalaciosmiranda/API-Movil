<?php
include '../includes/conexion.php';


$reset_autoincrement_sql = "ALTER TABLE rolesxpermiso AUTO_INCREMENT = 1";


if ($conn->query($reset_autoincrement_sql) === TRUE) {
    echo "El contador de autoincremento se reinició correctamente.";
} else {
    echo "Error al reiniciar el contador de autoincremento: " . $conn->error;
}


$insert_sql = "INSERT INTO rolesxpermiso (id_rol, id_permiso) VALUES (?, ?)";
$insert_stmt = $conn->prepare($insert_sql);


$insert_stmt->bind_param("ii", $id_rol, $id_permiso);
$insert_stmt->execute();


if ($insert_stmt->affected_rows > 0) {
    echo "Inserción realizada correctamente.";
} else {
    echo "Error al insertar datos: " . $insert_stmt->error;
}


$conn->close();
?>
