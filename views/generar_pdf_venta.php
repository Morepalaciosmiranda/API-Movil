<?php
require_once __DIR__ . '/../libraries/tcpdf/tcpdf.php';
include_once "../includes/conexion.php";

$sql = "SELECT v.*, u.nombre_usuario, p.nombre_producto, dv.cantidad, dv.valor_unitario
        FROM ventas v
        JOIN usuarios u ON v.id_usuario = u.id_usuario
        JOIN detalle_venta dv ON v.id_venta = dv.id_venta
        JOIN productos p ON dv.id_producto = p.id_producto
        ORDER BY v.fecha_venta DESC
        LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die('No se encontraron ventas');
}

$venta = $result->fetch_assoc();

class MYPDF extends TCPDF {
    public function Header() {
        // Fondo negro para toda la página
        $this->Rect(0, 0, $this->getPageWidth(), $this->getPageHeight(), 'F', array(), array(0, 0, 0));
        
        $this->SetFont('helvetica', 'B', 24);
        $this->SetTextColor(255, 255, 255); // Texto blanco
        $this->Cell(0, 20, 'Factura de Venta', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
      
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
$pdf->SetTitle('Factura de Venta');
$pdf->SetSubject('Factura');
$pdf->SetKeywords('Factura, Venta, PDF');

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
    body {color: #FFFFFF;} /* Texto blanco para todo el contenido */h2, h3 {color: #FF6600; text-align: center; margin-bottom: 20px;}
    h2 {font-size: 18pt;}
    h3 {font-size: 14pt;}
    table {border-collapse: collapse; width: 100%; margin-bottom: 20px;}
    th {background-color: #FF6600; color: white; font-weight: bold;}
    td {border-bottom: 1px solid #444; color: #FFFFFF;} /* Borde más oscuro y texto blanco */
    tr:nth-child(even) {background-color: #222;} /* Fondo ligeramente más claro para filas pares */
    .total {background-color: #FF6600; color: white; font-weight: bold;}
</style>
<h2>Detalles de la Venta</h2>
<table cellpadding="5">
    <tr>
        <th>ID Venta:</th>
        <td>' . $venta['id_venta'] . '</td>
        <th>Fecha:</th>
        <td>' . $venta['fecha_venta'] . '</td>
    </tr>
    <tr>
        <th>Cliente:</th>
        <td colspan="3">' . $venta['nombre_usuario'] . '</td>
    </tr>
</table>

<h3>Productos</h3>
<table cellpadding="5">
    <tr>
        <th>Producto</th>
        <th>Cantidad</th>
        <th>Precio Unitario</th>
        <th>Subtotal</th>
    </tr>';

$subtotal = $venta['cantidad'] * $venta['valor_unitario'];
$html .= '<tr>
    <td>' . $venta['nombre_producto'] . '</td>
    <td>' . $venta['cantidad'] . '</td>
    <td>$' . number_format($venta['valor_unitario'], 2) . '</td>
    <td>$' . number_format($subtotal, 2) . '</td>
</tr>';

$html .= '<tr class="total">
    <td colspan="3" align="right">Total:</td>
    <td>$' . number_format($subtotal, 2) . '</td>
</tr>';
$html .= '</table>';

$pdf->SetTextColor(255, 255, 255); // Asegura que el texto principal sea blanco
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Output('factura_venta_' . $venta['id_venta'] . '.pdf', 'I');