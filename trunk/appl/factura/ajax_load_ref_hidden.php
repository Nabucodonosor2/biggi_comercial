<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$vl_cod_empresa = $_REQUEST['vl_cod_empresa'];
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT REFERENCIA_HEM
			  ,REFERENCIA_HES
		FROM EMPRESA
		WHERE COD_EMPRESA = $vl_cod_empresa";
  
$result = $db->build_results($sql);
$REFERENCIA_HEM = $result[0]['REFERENCIA_HEM'];
$REFERENCIA_HES = $result[0]['REFERENCIA_HES'];

print $REFERENCIA_HEM."|".$REFERENCIA_HES;
?>