<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once("../../appl.ini");

$COD_CHEQUE_RENTA = $_REQUEST['token'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);	
$sql = "SELECT TOP 1 COD_CHEQUE_RENTA 
				,RUT_PROVEEDOR
				,DIG_VERIF
				,LISTA_FACTURA 
				,BOLETA_FACTURA 
		FROM CHEQUE_RENTA
		WHERE COD_CHEQUE_RENTA = $COD_CHEQUE_RENTA";
$result = $db->build_results($sql);
$RUT_PROVEEDOR	= $result[0]['RUT_PROVEEDOR'];
$DIG_VERIF 		= $result[0]['DIG_VERIF'];
$LISTA_FACTURA	= $result[0]['LISTA_FACTURA'];
$BOLETA_FACTURA 	= $result[0]['BOLETA_FACTURA'];

$boleta_factura_print = '';
if($BOLETA_FACTURA == 'BOLETA')
$boleta_factura_print = 'Boletas:';
else 
$boleta_factura_print = 'Facturas:'; 


$pdf = new FPDF('P','pt','letter');
$pdf->AddPage();

$pdf->Rotate(-90, 0, 0);
$pdf->SetAutoPageBreak(false);

$pdf->SetFont('Arial','',8);
$pdf->Text(385, -415, "RUT Proveedor: $RUT_PROVEEDOR-$DIG_VERIF");
$pdf->Text(385, -405, $boleta_factura_print);
$pdf->SetXY(385, -1195);
$pdf->MultiCell(160, 12, $LISTA_FACTURA, '', '','L');
$pdf->Output('titulo', 'I');
?>