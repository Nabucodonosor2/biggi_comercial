<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_factura = $_REQUEST['cod_factura'];
$cod_usuario = session::get("COD_USUARIO");
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT CREADA_EN_SV
		  FROM NOTA_VENTA
         WHERE COD_NOTA_VENTA in (SELECT COD_DOC
						 			FROM FACTURA 
						 		   WHERE COD_FACTURA = $cod_factura)";  
$result = $db->build_results($sql);
$creada_sv = $result[0]['CREADA_EN_SV'];

if($creada_sv == 'S' && $cod_usuario != 5){
	print urlencode('S');
}else{
	print urlencode('N');
}

?>