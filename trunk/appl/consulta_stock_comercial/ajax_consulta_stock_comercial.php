<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../ws_client_biggi/class_client_biggi.php");
$cod_producto = $_REQUEST['cod_producto'];
	
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql = "select dbo.f_nv_proveedor_prod('$cod_producto') COD_EMPRESA";
	$result = $db->build_results($sql);
	$cod_empresa = $result[0]['COD_EMPRESA'];

	if($cod_empresa == 1302) {
		$sistema = 'TODOINOX';
		$bd = 'TODOINOX';
		$cod_bodega = 1;
	}
	else if($cod_empresa == 1138) {
		$sistema = 'BODEGA';
		$bd = 'BODEGA_BIGGI';
		$cod_bodega = 2;
	}

	if ($sistema != ''){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT $bd.dbo.f_bodega_stock(COD_PRODUCTO, $cod_bodega, GETDATE()) STOCK
					  ,MANEJA_INVENTARIO
				FROM PRODUCTO
				WHERE COD_PRODUCTO = '$cod_producto'";
		$result = $db->build_results($sql);
		$result = $sistema.'|'.$result[0]['STOCK'];
		
	}
print $result;
?>