<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_usuario = $_REQUEST["cod_usuario"];

$resultado = "";
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT COD_USUARIO
			  ,NOM_USUARIO
		FROM USUARIO 
		 WHERE COD_USUARIO =".$cod_usuario;

$result = $db->build_results($sql);
$row_count = $db->count_rows();	

	$resultado = $result[0]['COD_USUARIO']."|";
	$resultado .= $result[0]['NOM_USUARIO'];	

print $resultado;

?>