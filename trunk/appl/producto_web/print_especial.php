<?php
//require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once("class_print_folleto_producto.php");
require_once(dirname(__FILE__)."/FPDF/fpdf.php");

$cod_producto	= $_REQUEST['COD_PRODUCTO'];
$TIPO_PRINT		= $_REQUEST['TIPO_PRINT'];

$pdf = new FPDF('P','pt','letter');
$print = new print_folleto_producto();

if($TIPO_PRINT == '1'){
	$print->folleto($cod_producto, $pdf);
	$pdf->Output("Folleto.pdf", 'I');
}else{
	$print->ficha_tecnica($cod_producto, $pdf);
	$pdf->Output("Ficha_tecnica.pdf", 'I');
}
?>