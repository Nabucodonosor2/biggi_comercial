<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_guia_despacho = $_REQUEST['cod_guia_despacho'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT CREADA_EN_SV
		FROM NOTA_VENTA 
		WHERE COD_NOTA_VENTA IN (SELECT COD_DOC
								FROM GUIA_DESPACHO
								WHERE COD_GUIA_DESPACHO = $cod_guia_despacho)";
								
$result = $db->build_results($sql);
$creada_sv = $result[0]['CREADA_EN_SV'];

if($creada_sv == 'S'){
	print urlencode('S');
}else{
	print urlencode('N');
}

?>