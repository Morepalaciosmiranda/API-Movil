<?php
require_once __DIR__ . '/../libraries/tcpdf/tcpdf.php';
include_once "../includes/conexion.php";

// Obtener todas las ventas
$sql = "SELECT ventas.*, usuarios.nombre_usuario, productos.nombre_producto
        FROM ventas
        JOIN usuarios ON ventas.id_usuario = usuarios.id_usuario
        JOIN detalle_venta ON ventas.id_venta = detalle_venta.id_venta
        JOIN productos ON detalle_venta.id_producto = productos.id_producto
        ORDER BY ventas.fecha_venta DESC";
$result = $conn->query($sql);

// Crear PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Reporte General de Ventas');
$pdf->SetHeaderData('', 0, 'Reporte General de Ventas', '');
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->AddPage();

$html = '<h1>Reporte General de Ventas</h1>';
$html .= '<table border="1"><tr><th>ID Venta</th><th>Usuario</th><th>Producto</th><th>Fecha Venta</th></tr>';
while ($row = $result->fetch_assoc()) {
    $html .= '<tr>';
    $html .= '<td>' . $row['id_venta'] . '</td>';
    $html .= '<td>' . $row['nombre_usuario'] . '</td>';
    $html .= '<td>' . $row['nombre_producto'] . '</td>';
    $html .= '<td>' . $row['fecha_venta'] . '</td>';
    $html .= '</tr>';
}
$html .= '</table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('reporte_general_ventas.pdf', 'I');