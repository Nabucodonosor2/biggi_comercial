<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$operacion  = $_REQUEST["op"];
$param1     = $_REQUEST["param1"];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

if($operacion == 'validaFechaDoc'){
    $fecha_doc = str2date($param1);

    $sql = "SELECT  CASE 
                        WHEN $fecha_doc BETWEEN DATEADD(DAY, -4, GETDATE()) AND GETDATE() THEN 'SI'
                        ELSE 'NO'
                    END RESPUESTA";

    $variable = $db->build_results($sql);
    print $variable[0]['RESPUESTA'];
}

function str2date($fecha_str, $hora_str='00:00:00') {
	if ($fecha_str=='')
		return 'null';
	// Entra la fecha en formato dd/mm/yyyy		
	if (K_TIPO_BD=='mssql') {
		$res = explode('/', $fecha_str);
		if (strlen($res[2])==2)
			$res[2] = '20'.$res[2];
		return sprintf("{ts '$res[2]-$res[1]-$res[0] $hora_str.000'}");
	}
	else if (K_TIPO_BD=='oci')
		return "to_date('$fecha_str $hora_str', 'dd/mm/yyyy hh24:mi:ss')";
	else
		base::error("base.str2date, no soportado para ".K_TIPO_BD);
}
?>