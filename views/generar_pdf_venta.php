<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Current directory: " . __DIR__ . "<br>";
echo "TCPDF path: " . __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php' . "<br>";
echo "File exists: " . (file_exists(__DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php') ? 'Yes' : 'No') . "<br>";
die();
include_once "../includes/conexion.php";

if (isset($_GET['id_venta'])) {
    $id_venta = $_GET['id_venta'];
    
    // Obtener detalles de la venta
    $sql = "SELECT detalle_venta.*, ventas.id_usuario, usuarios.nombre_usuario, usuarios.correo_electronico, productos.nombre_producto
            FROM detalle_venta
            JOIN ventas ON detalle_venta.id_venta = ventas.id_venta
            JOIN usuarios ON ventas.id_usuario = usuarios.id_usuario
            JOIN productos ON detalle_venta.id_producto = productos.id_producto
            WHERE detalle_venta.id_venta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_venta);
    $stmt->execute();
    $result = $stmt->get_result();

    $detalles = $result->fetch_all(MYSQLI_ASSOC);

    // Crear PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle('Detalles de Venta');
    $pdf->SetHeaderData('', 0, 'Detalles de Venta', '');
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->AddPage();

    $html = '<h1>Detalles de Venta</h1>';
    $html .= '<h2>Datos del Cliente</h2>';
    $html .= '<p><strong>Nombre Usuario:</strong> ' . $detalles[0]['nombre_usuario'] . '</p>';
    $html .= '<p><strong>Correo Electrónico:</strong> ' . $detalles[0]['correo_electronico'] . '</p>';

    $html .= '<h2>Productos</h2>';
    $html .= '<table border="1"><tr><th>Producto</th><th>Cantidad</th><th>Valor Unitario</th><th>Total</th></tr>';
    foreach ($detalles as $detalle) {
        $html .= '<tr>';
        $html .= '<td>' . $detalle['nombre_producto'] . '</td>';
        $html .= '<td>' . $detalle['cantidad'] . '</td>';
        $html .= '<td>' . $detalle['valor_unitario'] . '</td>';
        $html .= '<td>' . $detalle['total_venta'] . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('detalles_venta.pdf', 'I');
}