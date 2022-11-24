<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$cod_nota_venta = $_REQUEST["cod_nota_venta"]; 
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$resultado 	= '';

$sql = "select count(I.COD_PRODUCTO)CANT 
		from ITEM_NOTA_VENTA I 
		where I.COD_NOTA_VENTA = $cod_nota_venta AND
		I.COD_PRODUCTO = 'E'";
$result = $db->build_results($sql);
$cant_item_nv = $result[0]['CANT'];
		 
$sql = "select COUNT(IOC.COD_ITEM_ORDEN_COMPRA)CANT
		from ITEM_ORDEN_COMPRA IOC,ORDEN_COMPRA OC,NOTA_VENTA NV
		WHERE NV.COD_NOTA_VENTA = $cod_nota_venta
		AND OC.COD_NOTA_VENTA = NV.COD_NOTA_VENTA
		AND IOC.COD_ORDEN_COMPRA  = OC.COD_ORDEN_COMPRA
		AND IOC.COD_PRODUCTO = 'E'";
$result = $db->build_results($sql);
$cant_item_oc = $result[0]['CANT'];

$resultado = "$cant_item_nv|$cant_item_oc";

print $resultado;
?>