<?php
require_once __DIR__ . '/../libraries/tcpdf/tcpdf.php';
include_once "../includes/conexion.php";

$sql = "SELECT v.id_venta, v.fecha_venta, u.nombre_usuario, p.nombre_producto, dv.cantidad, dv.valor_unitario
        FROM ventas v
        JOIN usuarios u ON v.id_usuario = u.id_usuario
        JOIN detalle_venta dv ON v.id_venta = dv.id_venta
        JOIN productos p ON dv.id_producto = p.id_producto
        ORDER BY v.fecha_venta DESC";
$result = $conn->query($sql);

class MYPDF extends TCPDF {
    public function Header() {
        // Fondo negro para toda la página
        $this->Rect(0, 0, $this->getPageWidth(), $this->getPageHeight(), 'F', array(), array(0, 0, 0));
        
        $this->SetFont('helvetica', 'B', 24);
        $this->SetTextColor(255, 255, 255); // Texto blanco
        $this->Cell(0, 20, 'Reporte General de Ventas', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
        $this->Image('../img/LogoExterminio.png', 10, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(200, 200, 200); // Gris claro para el pie de página
        $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Tu Empresa');
$pdf->SetTitle('Reporte General de Ventas');
$pdf->SetSubject('Ventas');
$pdf->SetKeywords('Reporte, Ventas, PDF');

$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$pdf->AddPage();

$pdf->SetFont('helvetica', '', 12);

$html = '
<style>
    body {color: #FFFFFF;} /* Texto blanco para todo el contenido */
    h2 {color: #FF6600; font-size: 18pt; text-align: center; margin-bottom: 20px;}
    table {border-collapse: collapse; width: 100%; margin-bottom: 20px;}
    th {background-th {background-color: #FF6600; color: white; font-weight: bold;}
    td {border-bottom: 1px solid #444; color: #FFFFFF;} /* Borde más oscuro y texto blanco */
    tr:nth-child(even) {background-color: #222;} /* Fondo ligeramente más claro para filas pares */
    .total {background-color: #FF6600; color: white; font-weight: bold;}
</style>
<h2>Resumen de Ventas</h2>
<table cellpadding="5">
    <tr>
        <th>ID Venta</th>
        <th>Fecha</th>
        <th>Cliente</th>
        <th>Producto</th>
        <th>Cantidad</th>
        <th>Precio Unitario</th>
        <th>Subtotal</th>
    </tr>';

$total_general = 0;

while ($row = $result->fetch_assoc()) {
    $subtotal = $row['cantidad'] * $row['valor_unitario'];
    $total_general += $subtotal;
    
    $html .= '<tr>
        <td>' . $row['id_venta'] . '</td>
        <td>' . $row['fecha_venta'] . '</td>
        <td>' . $row['nombre_usuario'] . '</td>
        <td>' . $row['nombre_producto'] . '</td>
        <td>' . $row['cantidad'] . '</td>
        <td>$' . number_format($row['valor_unitario'], 2) . '</td>
        <td>$' . number_format($subtotal, 2) . '</td>
    </tr>';
}

$html .= '<tr class="total">
    <td colspan="6" align="right">Total General:</td>
    <td>$' . number_format($total_general, 2) . '</td>
</tr>';
$html .= '</table>';

$pdf->SetTextColor(255, 255, 255); // Asegura que el texto principal sea blanco
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Output('reporte_general_ventas.pdf', 'I');