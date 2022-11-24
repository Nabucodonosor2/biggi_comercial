<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_nota_venta = $_REQUEST["cod_nota_venta"]; 
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT COD_ORDEN_COMPRA
		FROM ORDEN_COMPRA
		WHERE COD_NOTA_VENTA = $cod_nota_venta
		AND CREADA_DESDE = 'BTN_GENERA_OC'";
$result = $db->build_results($sql);

for($i=0 ; $i < count($result) ; $i++)
	$list_cod_nota_venta .= $result[$i]['COD_ORDEN_COMPRA'].'/';

if(strlen($list_cod_nota_venta) == 0)
	print "NO_EXISTE";
else
	print substr($list_cod_nota_venta, 0, strlen($list_cod_nota_venta) - 1);
?>