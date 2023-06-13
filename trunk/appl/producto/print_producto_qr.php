<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once("../../appl.ini");
require_once("phpqrcode/qrlib.php");

//para hacer pruebas
//http://localhost/biggi_comercial/trunk/appl/producto/print_producto_qr.php?cod_producto=CG-4H|BAL|MTE-140|ECE-90
//http://www.biggi.cl/sysbiggi_new/biggi_comercial/trunk/appl/producto/print_producto_qr.php?cod_producto=CG-4H|BAL|MTE-140|ECE-90

$cod_producto = $_REQUEST['cod_producto'];
$producto_list = explode("|", $cod_producto);

$pdf = new FPDF('P','pt','letter');
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

for ($i=0; $i < 4; $i++){
    $x = 0;
    $y = 0;

    if($i == 1)
        $x = 300;
    else if($i == 2)
        $y = 400;
    else if($i == 3){
        $x = 300;
        $y = 400;
    }

    $cod_producto_sin_slash = str_replace("/", "_", $producto_list[$i]);
    $filename = dirname(__FILE__)."/img_temp/".$cod_producto_sin_slash.".png"; 
    $tamanio = 10;
    $level = 'H';
    $frameSize = 1;
    $contenido = $producto_list[$i].'$INVSALAVENTA';
    QRcode::png($contenido, $filename, $level, $tamanio, $frameSize);
    $pdf->Image($filename, 115 + $x, $y + 120, 80, 80);
    unlink($filename);

    $pdf->SetFont('Arial','B', 14);
    $pdf->SetXY(95+$x, 200+$y);
    $pdf->Cell(120, 17, $producto_list[$i], 0, '', 'C');
    $y_linea = 217 + $y;
    $pdf->Line(115 + $x, $y_linea, 195 + $x, $y_linea);

    $pdf->SetFont('Arial','', 6);
    $pdf->SetXY(95 + $x, 218 + $y);
    $pdf->Cell(120, 10, 'INVENTARIO SALA VENTA', 0, '', 'C');
}

/////////prueba lineas
$x = 306;
$pdf->Line($x, 0, $x, 790);
$pdf->Line(0, 395, 612, 395);
/////////////////////////

$pdf->Output('titulo', 'I');
?>