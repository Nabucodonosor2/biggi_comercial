<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT CONVERT(VARCHAR, GETDATE(), 103) FECHA_ACTUAL";			
$result = $db->build_results($sql);
	
print $result[0]['FECHA_ACTUAL'];
?>