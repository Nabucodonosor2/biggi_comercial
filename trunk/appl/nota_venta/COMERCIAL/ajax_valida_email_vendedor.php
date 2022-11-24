<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$cod_vendedor_1 = $_REQUEST["cod_vendedor_1"]; 
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "select mail 
		from usuario
		where COD_USUARIO=$cod_vendedor_1";

$result = $db->build_results($sql);
	
$SALIDA = $result[0]['mail'];

if($SALIDA=='')
	print '1';
else 
	print '0';
	
?>