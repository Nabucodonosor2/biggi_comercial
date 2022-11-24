<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_nv = $_REQUEST['cod_nv'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT COD_NOTA_VENTA
		FROM NOTA_VENTA 
		WHERE COD_NOTA_VENTA = $cod_nv";

								
$result = $db->build_results($sql);


if(count($result)== 0){
	echo 'N';
}else{
	echo 'S';
}

?>