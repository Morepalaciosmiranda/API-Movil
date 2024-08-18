<?php
require_once __DIR__ . '/../libraries/tcpdf/tcpdf.php';
include_once "../includes/conexion.php";

// Verificar que se ha proporcionado un ID de venta
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Se requiere un ID de venta válido');
}

$id_venta = $_GET['id'];

// Obtener los datos de la venta
$sql = "SELECT v.*, u.nombre_usuario, p.nombre_producto, dv.cantidad, dv.precio_unitario
        FROM ventas v
        JOIN usuarios u ON v.id_usuario = u.id_usuario
        JOIN detalle_venta dv ON v.id_venta = dv.id_venta
        JOIN productos p ON dv.id_producto = p.id_producto
        WHERE v.id_venta = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_venta);
$stmt->execute();
$result = $stmt->get_result();

// Crear PDF
class MYPDF extends TCPDF {
    public function Header() {
        $image_file = __DIR__ . '/../img/LogoExterminio.png';
        $this->Image($image_file, 10, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 15, 'Factura de Venta', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Tu Empresa');
$pdf->SetTitle('Factura de Venta');
$pdf->SetSubject('Factura');
$pdf->SetKeywords('Factura, Venta, PDF');

$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

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

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    $html = '<h2>Detalles de la Venta</h2>';
    $html .= '<table border="1" cellpadding="5">
        <tr>
            <th><b>ID Venta:</b></th>
            <td>' . $row['id_venta'] . '</td>
            <th><b>Fecha:</b></th>
            <td>' . $row['fecha_venta'] . '</td>
        </tr>
        <tr>
            <th><b>Cliente:</b></th>
            <td colspan="3">' . $row['nombre_usuario'] . '</td>
        </tr>
    </table><br><br>';
    
    $html .= '<h3>Productos</h3>';
    $html .= '<table border="1" cellpadding="5">
        <tr>
            <th><b>Producto</b></th>
            <th><b>Cantidad</b></th>
            <th><b>Precio Unitario</b></th>
            <th><b>Subtotal</b></th>
        </tr>';
    
    $total = 0;
    do {
        $subtotal = $row['cantidad'] * $row['precio_unitario'];
        $total += $subtotal;
        $html .= '<tr>
            <td>' . $row['nombre_producto'] . '</td>
            <td>' . $row['cantidad'] . '</td>
            <td>$' . number_format($row['precio_unitario'], 2) . '</td>
            <td>$' . number_format($subtotal, 2) . '</td>
        </tr>';
    } while ($row = $result->fetch_assoc());
    
    $html .= '<tr>
        <th colspan="3" align="right"><b>Total:</b></th>
        <td><b>$' . number_format($total, 2) . '</b></td>
    </tr>';
    $html .= '</table>';
    
    $pdf->writeHTML($html, true, false, true, false, '');
    
    $pdf->Output('factura_venta_' . $id_venta . '.pdf', 'I');
} else {
    die('No se encontró la venta especificada');
}