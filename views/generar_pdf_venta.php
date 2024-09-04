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
        $this->SetFont('helvetica', 'B', 20);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 15, 'Factura de Venta', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
        // Agregar logo
        $this->Image('../img/LogoExterminio.png', 10, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// ... (configuración del PDF)

$pdf->AddPage();

$pdf->SetFont('helvetica', '', 12);

$html = '
<style>
    h2, h3 {color: #FF6600; text-align: center; margin-bottom: 20px;}
    h2 {font-size: 18pt;}
    h3 {font-size: 14pt;}
    table {border-collapse: collapse; width: 100%; margin-bottom: 20px;}
    th {background-color: #FF6600; color: white; font-weight: bold;}
    td {border-bottom: 1px solid #ddd;}
    tr:nth-child(even) {background-color: #f2f2f2;}
    .total {background-color: #FF6600; color: white; font-weight: bold;}
</style>
<h2>

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

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Output('factura_venta_' . $venta['id_venta'] . '.pdf', 'I');