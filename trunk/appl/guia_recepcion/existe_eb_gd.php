<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_guia = $_REQUEST['cod_guia'];


$sql = "SELECT COUNT(*)CANT
        FROM ENTRADA_BODEGA 
        WHERE TIPO_DOC = 'GUIA_RECEPCION' AND COD_DOC = $cod_guia";

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$result = $db->build_results($sql);

print $result[0]['CANT'];
?>