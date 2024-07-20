<?php

include '../includes/conexion.php';


if (isset($_POST['permission_name'])) {

    $permission_name = $_POST['permission_name'];

   
    $insert_sql = "INSERT INTO permisos (nombre_permiso) VALUES (?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("s", $permission_name);


    if ($insert_stmt->execute()) {
      
        echo "Permiso creado correctamente.";
    } else {

        echo "Error al crear el permiso: " . $conn->error;
    }
}

?>