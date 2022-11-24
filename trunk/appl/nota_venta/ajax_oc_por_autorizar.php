<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_nota_venta = urldecode($_REQUEST["cod_nota_venta"]); 
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT COD_ORDEN_COMPRA 
		FROM ORDEN_COMPRA
		WHERE COD_NOTA_VENTA = $cod_nota_venta
		AND AUTORIZADA_20_PROC = 'N'";

$result = $db->build_results($sql);
$row_count = $db->count_rows();
$cod_orden_compra_20_porc = '';
if ($row_count > 0){
	for($i=0; $i <count($result); $i++){
		$cod_orden_compra_20_porc .= $result[$i]['COD_ORDEN_COMPRA'].' , ';
	}
}

$sql = "SELECT COD_ORDEN_COMPRA 
		FROM ORDEN_COMPRA
		WHERE COD_NOTA_VENTA = $cod_nota_venta
		AND AUTORIZADA = 'N'";

$result = $db->build_results($sql);
$row_count = $db->count_rows();
$cod_orden_compra = '';
if ($row_count > 0){
	for($i=0; $i <count($result); $i++){
		$cod_orden_compra .= $result[$i]['COD_ORDEN_COMPRA'].' , ';
	}
}

print urlencode($cod_orden_compra_20_porc.'|'.$cod_orden_compra);
?>