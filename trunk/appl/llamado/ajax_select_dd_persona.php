<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_contacto_persona = $_REQUEST["cod_contacto_persona"];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$respuesta = "";
$sql = "SELECT COD_CONTACTO_PERSONA, CARGO, NOM_PERSONA
		FROM CONTACTO_PERSONA
		WHERE COD_CONTACTO_PERSONA =".$cod_contacto_persona;
$result = $db->build_results($sql);
$COD_CONTACTO_PERSONA = $result[0]["COD_CONTACTO_PERSONA"];
$CARGO = urlencode($result[0]["CARGO"]);
$NOM_PERSONA = urlencode($result[0]["NOM_PERSONA"]);
$respuesta = $COD_CONTACTO_PERSONA.'|'.$CARGO.'|'.$NOM_PERSONA;
print $respuesta;
?>