<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_contacto = $_REQUEST["cod_contacto"];

$resultado = "";
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT COD_CONTACTO_PERSONA, NOM_PERSONA FROM CONTACTO_PERSONA WHERE COD_CONTACTO = ".$cod_contacto;

$result = $db->build_results($sql);
$row_count = $db->count_rows();	
for($i=0;$i<$row_count;$i++){
	$resultado .= $result[$i]['COD_CONTACTO_PERSONA']."|";
	$resultado .= $result[$i]['NOM_PERSONA']."*";	
}
print urlencode($resultado);

?>