<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_producto = urldecode($_REQUEST["cod_producto"]); 
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "select P.COD_PRODUCTO COD_PRODUCTO_HIJO 
			   ,P.NOM_PRODUCTO 
			   ,PL.ES_COMPUESTO
			   ,'S' GENERA_COMPRA 
		from PRODUCTO P, PRODUCTO_LOCAL PL
		where P.COD_PRODUCTO = '$cod_producto'
		AND P.COD_PRODUCTO = PL.COD_PRODUCTO";
		
$result = $db->build_results($sql);

if ($result[0]['ES_COMPUESTO']== 'S'){
	$sql_compuesto = "select PC.COD_PRODUCTO_HIJO 
				,P.NOM_PRODUCTO
				,PC.CANTIDAD
				,'S' ES_COMPUESTO
				,GENERA_COMPRA
		from PRODUCTO_COMPUESTO PC, PRODUCTO P
		where PC.COD_PRODUCTO = '$cod_producto' and
			P.COD_PRODUCTO = PC.COD_PRODUCTO_HIJO
		order by PC.ORDEN";		
		$result = $db->build_results($sql_compuesto);	
}
print urlencode(json_encode($result));
?>