<?php
include '../includes/conexion.php';

$sql = "SELECT c.id_compra, p.nombre_proveedor, c.nombre_insumos, c.fecha_compra, c.total_compra, c.cantidad
        FROM compras c
        JOIN proveedores p ON c.id_proveedor = p.id_proveedor
        ORDER BY c.fecha_compra DESC";
$resultado = $conn->query($sql);

if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        echo "<tr id='compra-" . $row['id_compra'] . "'>";
        echo "<td>" . $row['nombre_proveedor'] . "</td>";
        echo "<td>" . $row['nombre_insumos'] . "</td>";
        echo "<td>" . $row['fecha_compra'] . "</td>";
        echo "<td>" . $row['total_compra'] . "</td>";
        echo "<td>" . $row['cantidad'] . "</td>";
        echo '<td class="actions">';
        echo '<button class="edit-btn" onclick="abrirModalEditar(' . $row['id_compra'] . ', \'' . $row['nombre_proveedor'] . '\', \'' . $row['nombre_insumos'] . '\', \'' . $row['fecha_compra'] . '\', ' . $row['total_compra'] . ', ' . $row['cantidad'] . ')"><i class="fa fa-edit"></i></button>';
        echo '<button class="delete-btn" onclick="eliminarCompra(' . $row['id_compra'] . ')"><i class="fa fa-trash"></i></button>';
        echo '<button class="details-btn" onclick="abrirModalDetalle(' . $row['id_compra'] . ')"><i class="fa fa-eye"></i></button>';
        echo '</td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No hay compras disponibles.</td></tr>";
}

$conn->close();
?>