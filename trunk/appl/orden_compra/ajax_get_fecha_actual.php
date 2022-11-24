<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

function str2date($fecha_str, $hora_str='00:00:00') {
	if ($fecha_str=='')
		return 'null';
	// Entra la fecha en formato dd/mm/yyyy		
	$res = explode('/', $fecha_str);
	if (strlen($res[2])==2)
		$res[2] = '20'.$res[2];
	return sprintf("{ts '$res[2]-$res[1]-$res[0] $hora_str.000'}");
}

$fecha_solicita_fact = $_REQUEST['fecha_solicita_fact'];

if($fecha_solicita_fact <> ''){
	$fecha_solicita_fact = str2date($fecha_solicita_fact);
	
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql = "SELECT CONVERT(VARCHAR, GETDATE(), 103) FECHA_ACTUAL
				  ,CASE
				  	WHEN $fecha_solicita_fact >= dbo.f_makedate(day(getdate()), month(getdate()), year(getdate())) THEN 'OK'
				  	ELSE 'MAYOR'
				  END VALIDACION";			
	$result = $db->build_results($sql);
}else{
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql = "SELECT CONVERT(VARCHAR, GETDATE(), 103) FECHA_ACTUAL
				  ,NULL VALIDACION";			
	$result = $db->build_results($sql);
}

print $result[0]['FECHA_ACTUAL'].'|'.$result[0]['VALIDACION'];
?>