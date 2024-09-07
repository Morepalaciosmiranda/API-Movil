<?php
include '../includes/conexion.php';

// Asegúrate de que estás usando la paginación correctamente
$items_por_pagina = 10; // o el número que prefieras
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $items_por_pagina;

$sql = "SELECT c.id_compra, p.nombre_proveedor, c.nombre_insumos, c.fecha_compra, c.total_compra, c.cantidad
        FROM compras c
        JOIN proveedores p ON c.id_proveedor = p.id_proveedor
        ORDER BY c.fecha_compra DESC
        LIMIT $items_por_pagina OFFSET $offset";
$resultado = $conn->query($sql);

if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        echo "<tr id='compra-" . $row['id_compra'] . "'>";
        echo "<td>" . htmlspecialchars($row['nombre_proveedor']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nombre_insumos']) . "</td>";
        echo "<td>" . htmlspecialchars($row['fecha_compra']) . "</td>";
        echo "<td>" . htmlspecialchars($row['total_compra']) . "</td>";
        echo "<td>" . htmlspecialchars($row['cantidad']) . "</td>";
        echo '<td class="actions">';
        echo '<button class="edit-btn" onclick="abrirModalEditar(' . $row['id_compra'] . ', \'' . htmlspecialchars($row['nombre_proveedor'], ENT_QUOTES) . '\', \'' . htmlspecialchars($row['nombre_insumos'], ENT_QUOTES) . '\', \'' . $row['fecha_compra'] . '\', ' . $row['total_compra'] . ', ' . $row['cantidad'] . ')"><i class="fa fa-edit"></i></button>';
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