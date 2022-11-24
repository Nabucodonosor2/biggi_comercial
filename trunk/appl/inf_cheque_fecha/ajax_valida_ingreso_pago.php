<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
$cod_ingreso_pago	= $_REQUEST['cod_ingreso_pago'];
$nro_doc			= $_REQUEST['nro_doc'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
if($nro_doc == ''){
	$sql = "SELECT COD_INGRESO_PAGO
			FROM INGRESO_PAGO
			WHERE COD_INGRESO_PAGO = $cod_ingreso_pago";
			
	$result = $db->build_results($sql);
	
	if(count($result) == 0)
		print 'NO_VALIDO|1';
}else{
	$sql = "SELECT COD_INGRESO_PAGO
			FROM DOC_INGRESO_PAGO
			WHERE COD_INGRESO_PAGO = $cod_ingreso_pago
			AND NRO_DOC = $nro_doc";
			
	$result = $db->build_results($sql);
	
	if(count($result) == 0)
		print 'NO_VALIDO|2';
}	
?>