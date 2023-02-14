<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_usuario = $_REQUEST['cod_usuario'];
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT  NOM_USUARIO
                ,CONVERT(VARCHAR, GETDATE(), 103) +' '+ CONVERT(VARCHAR, GETDATE(), 108) FECHA_RP_CLIENTE
        FROM USUARIO
        WHERE COD_USUARIO = $cod_usuario";  
$result = $db->build_results($sql);

print $result[0]['NOM_USUARIO'].'|'.$result[0]['FECHA_RP_CLIENTE'];
?>