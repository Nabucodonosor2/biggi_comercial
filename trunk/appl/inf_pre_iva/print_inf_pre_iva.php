<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../producto_web/FPDF/fpdf.php");

$arrayDatos = session::get('arrayDatos');

$pdf = new FPDF('L','pt','letter');

$pdf->AddPage();
$pdf->SetAutoPageBreak(true,0);
$titulo = "Informe PRE-IVA";
$pdf->SetTitle($titulo);

///////////////////////////////////TITULO////////////////////////////////////////
$pdf->SetFont('Arial','B', 18);
$pdf->setFillColor(255, 255, 255);

$pdf->SetXY(220, 20);
$pdf->Cell(350, 22, 'Informe PRE-IVA '.$arrayDatos[0]['MES'].' / '.$arrayDatos[0]['ANO'] , 0, '', 'C', true);

$pdf->SetFont('Arial','', 8);
$pdf->SetXY(225, 42);
$pdf->Cell(340, 15, '(Valores unificados Comercial Biggi y Rental)', 0, '', 'C', true);
/////////////////////////////////////////////////////////////////////////////////

//////////////////////TABLE HEADER///////////////////////////////////////////////
$pdf->SetFont('Arial','B', 10);
$pdf->setFillColor(145, 145, 145);
$pdf->setTextColor(255, 255, 255);
$pdf->SetXY(41, 80);
$pdf->Cell(180, 20, 'TIPO DOCUMENTO', 0, '', 'C', true);
$pdf->SetXY(221, 80);
$pdf->Cell(50, 20, 'CT', 0, '', 'C', true);
$pdf->SetXY(271, 80);
$pdf->Cell(120, 20, 'EXENTO', 0, '', 'C', true);
$pdf->SetXY(391, 80);
$pdf->Cell(120, 20, 'NETO', 0, '', 'C', true);
$pdf->SetXY(511, 80);
$pdf->Cell(120, 20, 'IVA', 0, '', 'C', true);
$pdf->SetXY(631, 80);
$pdf->Cell(120, 20, 'TOTAL', 0, '', 'C', true);
/////////////////////////////////////////////////////////////////////////////////

//////////////////////TABLE CONTENT//////////////////////////////////////////////
$pdf->SetFont('Arial','', 9);
$pdf->setFillColor(234, 234, 234);
$pdf->setTextColor(0, 0, 0);

$y = 102;
$pdf->SetXY(41, $y);
$pdf->Cell(180, 20, 'FACTURA', 0, '', 'L', true);
$pdf->SetXY(221, $y);
$pdf->Cell(50, 20, $arrayDatos[0]['SUM_CT_FA_VENTA_AF_CB_RE'], 0, '', 'R', true);
$pdf->SetXY(271, $y);
$pdf->Cell(120, 20, '0', 0, '', 'R', true); //Siempre es cero
$pdf->SetXY(391, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_NETO_FA_VENTA_AF_CB_RE'], 0, '', 'R', true);
$pdf->SetXY(511, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_IVA_FA_VENTA_AF_CB_RE'], 0, '', 'R', true);
$pdf->SetXY(631, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_TOTAL_CON_IVA_FA_VENTA_AF_CB_RE'], 0, '', 'R', true);

$y = 124;
$pdf->SetXY(41, $y);
$pdf->Cell(180, 20, 'FACTURA EXENTA', 0, '', 'L', true);
$pdf->SetXY(221, $y);
$pdf->Cell(50, 20, $arrayDatos[0]['SUM_CT_FA_VENTA_EX_CB_RE'], 0, '', 'R', true);
$pdf->SetXY(271, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_NETO_FA_VENTA_EX_CB_RE'], 0, '', 'R', true);
$pdf->SetXY(391, $y);
$pdf->Cell(120, 20, '0', 0, '', 'R', true); //Siempre es cero
$pdf->SetXY(511, $y);
$pdf->Cell(120, 20, '0', 0, '', 'R', true); //Siempre es cero
$pdf->SetXY(631, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_TOTAL_CON_IVA_FA_VENTA_EX_CB_RE'], 0, '', 'R', true);

$y = 146;
$pdf->SetXY(41, $y);
$pdf->Cell(180, 20, 'FACTURA EXPORTACION', 0, '', 'L', true);
$pdf->SetXY(221, $y);
$pdf->Cell(50, 20, $arrayDatos[0]['CT_FA_VENTA_EXP_CB'], 0, '', 'R', true);
$pdf->SetXY(271, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['NETO_FA_VENTA_EXP_CB'], 0, '', 'R', true);
$pdf->SetXY(391, $y);
$pdf->Cell(120, 20, '0', 0, '', 'R', true); //Siempre es cero
$pdf->SetXY(511, $y);
$pdf->Cell(120, 20, '0', 0, '', 'R', true); //Siempre es cero
$pdf->SetXY(631, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_TOTAL_CON_IVA_FA_VENTA_EXP_CB'], 0, '', 'R', true);

$y = 168;
$pdf->SetXY(41, $y);
$pdf->Cell(180, 20, 'NOTA DE CREDITO', 0, '', 'L', true);
$pdf->SetXY(221, $y);
$pdf->Cell(50, 20, $arrayDatos[0]['CT_TOTAL_NC_VENTA'], 0, '', 'R', true);
$pdf->SetXY(271, $y);
$pdf->Cell(120, 20, '- '.$arrayDatos[0]['SUM_NETO_NC_VENTA_EX_CB_RE'], 0, '', 'R', true);
$pdf->SetXY(391, $y);
$pdf->Cell(120, 20, '- '.$arrayDatos[0]['SUM_NETO_NC_VENTA_CB_RE'], 0, '', 'R', true);
$pdf->SetXY(511, $y);
$pdf->Cell(120, 20, '- '.$arrayDatos[0]['SUM_IVA_NC_VENTA_CB_RE'], 0, '', 'R', true);
$pdf->SetXY(631, $y);
$pdf->Cell(120, 20, '- '.$arrayDatos[0]['AUX_SUM_TOT_CON_IVA_NC_VENTA_EX_CB_RE'], 0, '', 'R', true);

$y = 193;
$pdf->setFillColor(215, 222, 232);
$pdf->SetXY(271, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_GNRL_EXENTO'], 0, '', 'R', true);
$pdf->SetXY(391, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_GNRL_NETO'], 0, '', 'R', true);
$pdf->SetFont('Arial','B', 9);
$pdf->SetXY(511, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_GNRL_IVA'], 0, '', 'R', true);
$pdf->SetFont('Arial','', 9);
$pdf->SetXY(631, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_GNRL_TOTAL'], 0, '', 'R', true);

$y = 235;
$pdf->setFillColor(234, 234, 234);
$pdf->SetXY(41, $y);
$pdf->Cell(180, 20, 'FACTURA PROVEEDOR ('.$arrayDatos[0]['SUM_CT_FA_COMPRA_TDNX_CB_RE'].' - '.$arrayDatos[0]['SUM_CT_FA_COMPRA_BODEGA_CB_RE'].')', 0, '', 'L', true);
$pdf->SetXY(221, $y);
$pdf->Cell(50, 20, $arrayDatos[0]['SUM_CT_FA_COMRPRA_AF_CB_RE'], 0, '', 'R', true);
$pdf->SetXY(271, $y);
$pdf->Cell(120, 20, '0', 0, '', 'R', true); //Siempre es cero
$pdf->SetXY(391, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_NETO_FA_COMPRA_AF_CB_RE'], 0, '', 'R', true);
$pdf->SetXY(511, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_IVA_FA_COMPRA_AF_CB_RE'], 0, '', 'R', true);
$pdf->SetXY(631, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_TOT_CON_IVA_FA_COMPRA_AF_CB_RE'], 0, '', 'R', true);

$y = 257;
$pdf->SetXY(41, $y);
$pdf->Cell(180, 20, 'FACTURA EXENTA PROVEEDOR', 0, '', 'L', true);
$pdf->SetXY(221, $y);
$pdf->Cell(50, 20, $arrayDatos[0]['SUM_CT_FA_COMPRA_EX_CB_RE'], 0, '', 'R', true);
$pdf->SetXY(271, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_NETO_FA_COMPRA_EX_CB_RE'], 0, '', 'R', true);
$pdf->SetXY(391, $y);
$pdf->Cell(120, 20, '0', 0, '', 'R', true);
$pdf->SetXY(511, $y);
$pdf->Cell(120, 20, '0', 0, '', 'R', true);
$pdf->SetXY(631, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_TOT_CON_IVA_FA_COMPRA_EX_CB_RE'], 0, '', 'R', true);

$y = 279;
$pdf->SetXY(41, $y);
$pdf->Cell(180, 20, 'NOTA DE CREDITO PROVEEDOR ('.$arrayDatos[0]['SUM_CT_NC_COMPRA_TDNX_CB_RE'].' - '.$arrayDatos[0]['SUM_CT_NC_COMPRA_BODEGA_CB_RE'].')', 0, '', 'L', true);
$pdf->SetXY(221, $y);
$pdf->Cell(50, 20, $arrayDatos[0]['SUM_CT_NOTA_CREDITO_COMPRA_CB_RE'], 0, '', 'R', true);
$pdf->SetXY(271, $y);
$pdf->Cell(120, 20, '- '.$arrayDatos[0]['SUM_NETO_NC_COMPRA_EX_CB_RE'], 0, '', 'R', true);
$pdf->SetXY(391, $y);
$pdf->Cell(120, 20, '- '.$arrayDatos[0]['SUM_NETO_NC_COMPRA_CB_RE'], 0, '', 'R', true);
$pdf->SetXY(511, $y);
$pdf->Cell(120, 20, '- '.$arrayDatos[0]['SUM_IVA_NC_COMPRA_CB_RE'], 0, '', 'R', true);
$pdf->SetXY(631, $y);
$pdf->Cell(120, 20, '- '.$arrayDatos[0]['SUM_TOT_CON_IVA_NC_COMPRA_CB_RE'], 0, '', 'R', true);

$y = 305;
$pdf->setFillColor(215, 222, 232);
$pdf->SetXY(271, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_GNRL_EXENTO_COMPRA'], 0, '', 'R', true);
$pdf->SetXY(391, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_GNRL_NETO_COMPRA'], 0, '', 'R', true);
$pdf->SetFont('Arial','B', 9);
$pdf->SetXY(511, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_GNRL_IVA_COMPRA'], 0, '', 'R', true);
$pdf->SetFont('Arial','', 9);
$pdf->SetXY(631, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['SUM_GNRL_TOT_CON_IVA_COMPRA'], 0, '', 'R', true);

$pdf->Line(41, 340, 750, 340);

$y = 350;
$pdf->SetFont('Arial','B', 9);
$pdf->setFillColor(236, 225, 225);
$pdf->SetXY(271, $y);
$pdf->Cell(240, 20, 'TOTAL IVA', 0, '', 'R', true);
$pdf->SetXY(511, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['TOTAL_IVA_A_PAGAR'], 0, '', 'R', true);

$y = 373;
$pdf->SetXY(271, $y);
$pdf->Cell(240, 20, 'PPM A PAGAR', 0, '', 'R', true);
$pdf->SetXY(511, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['PPM_MES'], 0, '', 'R', true);

$pdf->Line(271, 402, 631, 402);

$y = 410;
$pdf->SetFont('Arial','B', 12);
$pdf->SetXY(271, $y);
$pdf->Cell(240, 20, 'TOTAL PRE-IVA A PAGAR', 0, '', 'R', true);
$pdf->SetXY(511, $y);
$pdf->Cell(120, 20, $arrayDatos[0]['TOTAL_PRE_IVA_PAGAR'], 0, '', 'R', true);
/////////////////////////////////////////////////////////////////////////////////



$pdf->Output("informe pre-iva.pdf", 'I');
?>