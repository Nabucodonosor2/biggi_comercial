<?php
require_once(dirname(__FILE__)."/../producto_web/class_print_folleto_producto.php");
require_once(dirname(__FILE__)."/../producto_web/FPDF/fpdf.php");

$result		= base64_decode($_REQUEST['RESULT_PRODUCTO']);
$TIPO_PRINT	= $_REQUEST['TIPO_PRINT'];
$COD = $_REQUEST['COD'];

$pdf = new FPDF('P','pt','letter');
$print = new print_folleto_producto();

if($TIPO_PRINT == '1'){
	$print->lista_folleto($pdf, $result);
	$pdf->SetTitle("FOLLETO COTIZACION $COD");
	$pdf->Output("FOLLETO COTIZACION $COD.pdf", 'I');
}else{
	$print->lista_ficha_tecnica($pdf, $result);
	$pdf->SetTitle("FICHA TECNICA COTIZACION $COD");
	$pdf->Output("FICHA TECNICA COTIZACION $COD.pdf", 'I');
}
?>