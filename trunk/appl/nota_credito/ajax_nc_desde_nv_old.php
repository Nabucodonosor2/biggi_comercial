<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_nota_credito = $_REQUEST['cod_nota_credito'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

	$sql_f="SELECT COD_FACTURA
			  FROM FACTURA
			 WHERE COD_FACTURA IN(SELECT COD_DOC
								 FROM NOTA_CREDITO
								WHERE COD_NOTA_CREDITO = $cod_nota_credito)";

		$result_f = $db->build_results($sql_f);
		$cod_factura = $result_f[0]['COD_FACTURA'];
		
		$sql = "SELECT CREADA_EN_SV
				  FROM NOTA_VENTA
		         WHERE COD_NOTA_VENTA in (SELECT COD_DOC
								 			FROM FACTURA 
								 		   WHERE COD_FACTURA = $cod_factura)";  
$result = $db->build_results($sql);
$creada_sv = $result[0]['CREADA_EN_SV'];

if($creada_sv == 'S'){
	print urlencode('S');
}else{
	print urlencode('N');
}

?>