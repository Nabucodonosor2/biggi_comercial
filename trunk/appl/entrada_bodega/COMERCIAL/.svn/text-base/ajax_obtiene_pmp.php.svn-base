<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$cod_producto = $_REQUEST['cod_producto'];
$K_SALA_VENTA = 4;	// bodega sala de venta
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "select dbo.f_bodega_pmp('$cod_producto', $K_SALA_VENTA, getdate()) PRECIO_PMP";
$result = $db->build_results($sql);
print urlencode($result[0]['PRECIO_PMP']);
?>