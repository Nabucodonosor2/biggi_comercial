<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../ws_client_biggi/class_client_biggi.php");

$cod_producto = $_REQUEST["cod_producto"]; 
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$resultado 		= '';
$cod_empresa 	= 0;
$cod_producto_n = '';

$array_cod_prod_cant = explode("|", $cod_producto);
for($i=0 ; $i < count($array_cod_prod_cant) ; $i++){
	$array_cod_producto = explode("*", $array_cod_prod_cant[$i]);
	$sistema		= '';
	
	$sql = "select dbo.f_nv_proveedor_prod('$array_cod_producto[0]') COD_EMPRESA";
	$result = $db->build_results($sql);
	$cod_empresa = $result[0]['COD_EMPRESA'];
		 
	if($cod_empresa == 1302)
		$sistema = 'TODOINOX';
	else if($cod_empresa == 1138)
		$sistema = 'BODEGA';

	if ($sistema != ''){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select SISTEMA, URL_WS, USER_WS,PASSWROD_WS  from PARAMETRO_WS
			where SISTEMA = '$sistema'";
		$result = $db->build_results($sql);
	
		$user_ws		= $result[0]['USER_WS'];
		$passwrod_ws	= $result[0]['PASSWROD_WS'];
		$url_ws			= $result[0]['URL_WS'];
	
		$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
		$result = $biggi->consulta_stock($array_cod_producto[0], $sistema);
	
		$result2 = $result2.$array_cod_producto[0].'*'.$result[0]['STOCK'] .'*'.$sistema.'*'.$array_cod_producto[1].'|';
	}
}
print base64_encode($result2)
?>