<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$cod_destinatario = $_REQUEST["cod_destinatario"];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT COD_USUARIO
		FROM DESTINATARIO 
		 WHERE COD_DESTINATARIO =".$cod_destinatario;

$result = $db->build_results($sql);
print $result[0]['COD_USUARIO'];

?>