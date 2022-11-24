<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_parametro = $_REQUEST["cod_parametro"];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT VALOR FROM PARAMETRO WHERE COD_PARAMETRO = $cod_parametro";

$result = $db->build_results($sql);
$VALOR = $result[0]['VALOR'];
print $VALOR;
?>