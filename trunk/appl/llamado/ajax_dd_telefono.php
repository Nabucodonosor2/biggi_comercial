<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_contacto = $_REQUEST["cod_contacto"];
$cod_persona = $_REQUEST["cod_persona"];

$resultado = "";
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

if ($cod_contacto != ''){
	//6017
	$sql = "SELECT TELEFONO
			FROM CONTACTO C, CONTACTO_TELEFONO CT
			WHERE C.COD_CONTACTO = CT.COD_CONTACTO
				AND C.COD_CONTACTO = ".$cod_contacto;
}
else if($cod_persona != ''){
	//8619
	$sql = "SELECT TELEFONO
			FROM CONTACTO_PERSONA CP, CONTACTO_PERSONA_TELEFONO CPT
			WHERE CP.COD_CONTACTO_PERSONA = ".$cod_persona."
				AND CP.COD_CONTACTO_PERSONA = CPT.COD_CONTACTO_PERSONA";
	
	
}

$result = $db->build_results($sql);
print urlencode(json_encode($result));
?>