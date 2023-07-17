<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../producto_web/FPDF/fpdf.php");

$pdf = new FPDF('L','pt','letter');

$pdf->AddFont('FuturaBook','','futurabook.php');
$pdf->AddFont('FuturaMedium','','futuramedium.php');
$pdf->AddPage();
$pdf->SetAutoPageBreak(true,0);
$titulo = "Reporte por vendedores";
$pdf->SetTitle($titulo);

$pdf->Image(dirname(__FILE__).'/../../images_appl/logo_reporte_horizontal.jpg', 18, 13,575,760);
$pdf->SetY(140);
$rect = 50;
$constX1 = 21;
$constX2 = 50;

$pdf->SetFont('Arial','B', 8);
$pdf->setFillColor(145, 145, 145);
$pdf->setTextColor(255, 255, 255);

$pdf->SetX($constX1+($constX2*0));
$pdf->Cell($rect,17, 'Vendedor', 1, '', 'C', true);
$pdf->SetX($constX1+($constX2*1));
$pdf->Cell($rect,17, 'Enero', 1, '', 'C', true);
$pdf->SetX($constX1+($constX2*2));
$pdf->Cell($rect,17, 'Febrero', 1, '', 'C', true);
$pdf->SetX($constX1+($constX2*3));
$pdf->Cell($rect,17, 'Marzo', 1, '', 'C', true);
$pdf->SetX($constX1+($constX2*4));
$pdf->Cell($rect,17, 'Abril', 1, '', 'C', true);
$pdf->SetX($constX1+($constX2*5));
$pdf->Cell($rect,17, 'Mayo', 1, '', 'C', true);
$pdf->SetX($constX1+($constX2*6));
$pdf->Cell($rect,17, 'Junio', 1, '', 'C', true);
$pdf->SetX($constX1+($constX2*7));
$pdf->Cell($rect,17, 'Julio', 1, '', 'C', true);
$pdf->SetX($constX1+($constX2*8));
$pdf->Cell($rect,17, 'Agosto', 1, '', 'C', true);
$pdf->SetX($constX1+($constX2*9));
$pdf->Cell($rect,17, 'Septiembre', 1, '', 'C', true);
$pdf->SetX($constX1+($constX2*10));
$pdf->Cell($rect,17, 'Octubre', 1, '', 'C', true);
$pdf->SetX($constX1+($constX2*11));
$pdf->Cell($rect,17, 'Noviembre', 1, '', 'C', true);
$pdf->SetX($constX1+($constX2*12));
$pdf->Cell($rect,17, 'Diciembre', 1, '', 'C', true);
$pdf->SetX($constX1+($constX2*13));
$pdf->Cell($rect,17, 'Promedio', 1, '', 'C', true);
$pdf->SetX($constX1+($constX2*14));
$pdf->Cell($rect,17, 'Venta Total', 1, '', 'C', true);

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$ano            = session::get('temp.inf_reporte_vendedores.ano');
$cod_usuario    = session::get("COD_USUARIO");
$sql            = "spi_ventas_x_vendedor_alt $ano, $cod_usuario";
$result         = $db->build_results($sql);

$actualY = $pdf->getY()+17;

$pdf->SetXY($constX1, $actualY+(17*0));
$pdf->Cell($rect,17, 'PV', 1, '', 'L', true);
$pdf->SetXY($constX1, $actualY+(17*1));
$pdf->Cell($rect,17, 'HE', 1, '', 'L', true);
$pdf->SetXY($constX1, $actualY+(17*2));
$pdf->Cell($rect,17, 'RB', 1, '', 'L', true);
$pdf->SetXY($constX1, $actualY+(17*3));
$pdf->Cell($rect,17, 'RB CDR', 1, '', 'L', true);
$pdf->SetXY($constX1, $actualY+(17*4));
$pdf->Cell($rect,17, 'LL', 1, '', 'L', true);
$pdf->SetXY($constX1, $actualY+(17*6));
$pdf->Cell($rect,17, 'CU', 1, '', 'L', true);
$pdf->SetXY($constX1, $actualY+(17*7));
$pdf->Cell($rect,17, 'CU CDR', 1, '', 'L', true);
$pdf->SetXY($constX1, $actualY+(17*8));
$pdf->Cell($rect,17, 'EO', 1, '', 'L', true);
$pdf->SetXY($constX1, $actualY+(17*9));
$pdf->Cell($rect,17, 'AR', 1, '', 'L', true);
$pdf->SetXY($constX1, $actualY+(17*10));
$pdf->Cell($rect,17, 'AM', 1, '', 'L', true);
$pdf->SetXY($constX1, $actualY+(17*12));
$pdf->Cell($rect,17, 'CA', 1, '', 'L', true);
$pdf->SetXY($constX1, $actualY+(17*13));
$pdf->Cell($rect,17, 'CA SODEXO', 1, '', 'L', true);
$pdf->SetXY($constX1, $actualY+(17*14));
$pdf->Cell($rect,17, 'CA COMPASS', 1, '', 'L', true);
$pdf->SetXY($constX1, $actualY+(17*15));
$pdf->Cell($rect,17, 'OTROS', 1, '', 'L', true);
$pdf->SetXY($constX1, $actualY+(17*17));
$pdf->Cell($rect,17, 'TOTALES', 1, '', 'L', true);

$pdf->SetFont('Arial','I', 8);
$pdf->setFillColor(215, 228, 189);
$pdf->setTextColor(0, 0, 0);

$pdf->SetXY($constX1, $actualY+(17*5));
$pdf->Cell($rect,17, 'SUBTOTAL', 1, '', 'R', true);
$pdf->SetXY($constX1, $actualY+(17*11));
$pdf->Cell($rect,17, 'SUBTOTAL', 1, '', 'R', true);
$pdf->SetXY($constX1, $actualY+(17*16));
$pdf->Cell($rect,17, 'SUBTOTAL', 1, '', 'R', true);

$pdf->SetFont('FuturaBook','', 8);
$pdf->SetTextColor(0, 0, 0);

for ($i=0; $i < count($result); $i++){
    $ene = $result[$i]['SUM_ENERO']/1000;
    $feb = $result[$i]['SUM_FEBRERO']/1000;
    $mar = $result[$i]['SUM_MARZO']/1000;
    $abr = $result[$i]['SUM_ABRIL']/1000;
    $may = $result[$i]['SUM_MAYO']/1000;
    $jun = $result[$i]['SUM_JUNIO']/1000;
    $jul = $result[$i]['SUM_JULIO']/1000;
    $ago = $result[$i]['SUM_AGOSTO']/1000;
    $sep = $result[$i]['SUM_SEPTIEMBRE']/1000;
    $oct = $result[$i]['SUM_OCTUBRE']/1000;
    $nov = $result[$i]['SUM_NOVIMEBRE']/1000;
    $dic = $result[$i]['SUM_DICIEMBRE']/1000;

    $total_venta = $ene + $feb + $mar + $abr + $may + $jun + $jul + $ago + $sep + $oct + $nov + $dic;
	$promedio = $total_venta/12;

    $pdf->SetY($actualY+(17*$i));

    if($i == 5 || $i == 11 || $i == 16)
        $pdf->setFillColor(215, 228, 189);     
    else
        $pdf->setFillColor(234, 234, 234); 
    
    $pdf->SetX($constX1+($constX2*1));
    $pdf->Cell($rect,17, number_format($ene, 0, ',', '.'), 1, '', 'R', true);
    $pdf->SetX($constX1+($constX2*2));
    $pdf->Cell($rect,17, number_format($feb, 0, ',', '.'), 1, '', 'R', true);
    $pdf->SetX($constX1+($constX2*3));
    $pdf->Cell($rect,17, number_format($mar, 0, ',', '.'), 1, '', 'R', true);
    $pdf->SetX($constX1+($constX2*4));
    $pdf->Cell($rect,17, number_format($abr, 0, ',', '.'), 1, '', 'R', true);
    $pdf->SetX($constX1+($constX2*5));
    $pdf->Cell($rect,17, number_format($may, 0, ',', '.'), 1, '', 'R', true);
    $pdf->SetX($constX1+($constX2*6));
    $pdf->Cell($rect,17, number_format($jun, 0, ',', '.'), 1, '', 'R', true);
    $pdf->SetX($constX1+($constX2*7));
    $pdf->Cell($rect,17, number_format($jul, 0, ',', '.'), 1, '', 'R', true);
    $pdf->SetX($constX1+($constX2*8));
    $pdf->Cell($rect,17, number_format($ago, 0, ',', '.'), 1, '', 'R', true);
    $pdf->SetX($constX1+($constX2*9));
    $pdf->Cell($rect,17, number_format($sep, 0, ',', '.'), 1, '', 'R', true);
    $pdf->SetX($constX1+($constX2*10));
    $pdf->Cell($rect,17, number_format($oct, 0, ',', '.'), 1, '', 'R', true);
    $pdf->SetX($constX1+($constX2*11));
    $pdf->Cell($rect,17, number_format($nov, 0, ',', '.'), 1, '', 'R', true);
    $pdf->SetX($constX1+($constX2*12));
    $pdf->Cell($rect,17, number_format($dic, 0, ',', '.'), 1, '', 'R', true);
    $pdf->SetX($constX1+($constX2*13));
    $pdf->Cell($rect,17, number_format($promedio, 0, ',', '.'), 1, '', 'R', true);

    $pdf->setFillColor(215, 228, 189);
    $pdf->SetX($constX1+($constX2*14));
    $pdf->Cell($rect,17, number_format($total_venta, 0, ',', '.'), 1, '', 'R', true);

}

$pdf->SetFont('FuturaBook','', 7);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(384, 570);
$pdf->Cell(206,17,'Comercial Biggi (Chile) S.A. - Av. Portugal 1726 Santiago, Chile', 0, '', 'C');
$pdf->SetXY(384, 582);
$pdf->Cell(206,17,'Tel. (56-2) 2412 6200 - info@biggi.cl', 0, '', 'C');

$pdf->Output("Reporte_por_vendedores_$ano.pdf", 'I');
?>