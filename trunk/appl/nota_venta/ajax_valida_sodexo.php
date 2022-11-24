<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$vl_rut = urldecode($_REQUEST["ve_rut"]); 
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT COUNT(*) COUNT
		FROM EMPRESA_SODEXO
		WHERE RUT_SODEXO = $vl_rut";
$result = $db->build_results($sql);

if($result[0]['COUNT'] > 0)
	print 'ES_SODEXO';
else
	print 'NO_ES_SODEXO';
?>