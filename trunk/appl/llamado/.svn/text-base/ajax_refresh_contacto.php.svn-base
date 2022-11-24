<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_contacto = $_REQUEST["cod_contacto"];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql="SELECT NOM_CONTACTO
			,DIRECCION
			,COD_CIUDAD
			,COD_COMUNA
		FROM CONTACTO
		WHERE COD_CONTACTO = $cod_contacto";

$result = $db->build_results($sql);
$cod_comuna = $result[0]['COD_COMUNA'];
$cod_ciudad = $result[0]['COD_CIUDAD'];

$result[0]['DIRECCION'] = urlencode($result[0]['DIRECCION']);
$result[0]['NOM_CONTACTO'] = urlencode($result[0]['NOM_CONTACTO']);

if($cod_comuna <> NULL){
	$sql="SELECT NOM_COMUNA 
		  FROM COMUNA
		  WHERE COD_COMUNA = $cod_comuna";
	
	$result2 = $db->build_results($sql);
	$result[0]['COD_COMUNA'] = urlencode($result2[0]['NOM_COMUNA']);
}

if($cod_ciudad <> NULL){
	$sql="SELECT NOM_CIUDAD 
		  FROM CIUDAD
		  WHERE COD_CIUDAD = $cod_ciudad";
	
	$result3 = $db->build_results($sql);
	$result[0]['COD_CIUDAD'] = urlencode($result3[0]['NOM_CIUDAD']);
}

print urlencode(json_encode($result));
?>