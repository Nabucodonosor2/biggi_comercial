<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$cod_persona = $_REQUEST['cod_persona'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql="SELECT TELEFONO,
			 EMAIL
	  FROM PERSONA 
	  WHERE COD_PERSONA = $cod_persona";

$result = $db->build_results($sql);
	  
$result['EMAIL'][0] = urlencode($result['EMAIL'][0]);
print urlencode(json_encode($result));

?>