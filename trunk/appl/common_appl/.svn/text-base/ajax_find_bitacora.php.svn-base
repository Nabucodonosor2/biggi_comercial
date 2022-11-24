<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$vigente = $_REQUEST["vigente"];

$resultado = "";
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
/*
 * opcion ORIGINAL deja el selec como el original, cuando se cargo por primera vez la ventana
 */
if ($vigente == 'NO_VIGENTE'){ //devuelve vigente y no vigente
	$sql = "SELECT DISTINCT U.COD_USUARIO, U.NOM_USUARIO 
			FROM BITACORA_FACTURA B, USUARIO U 
			WHERE B.COD_USUARIO = U.COD_USUARIO 
			AND (U.VENDEDOR_VISIBLE_FILTRO =1
			 OR U.VENDEDOR_VISIBLE_FILTRO=2)
			ORDER BY NOM_USUARIO";
	
}else if ($vigente == 'ORIGINAL'){//se mantiene el select del inicio, cuando se carga por primera vez la ventana
	$sql = "SELECT DISTINCT U.COD_USUARIO, U.NOM_USUARIO 
			FROM BITACORA_FACTURA B, USUARIO U 
			WHERE B.COD_USUARIO = U.COD_USUARIO 
			ORDER BY NOM_USUARIO";
}

$result = $db->build_results($sql);

for ($i=0; $i<count($result); $i++) {
$result[$i]['COD_USUARIO'] = urlencode($result[$i]['COD_USUARIO']);
$result[$i]['NOM_USUARIO'] = urlencode($result[$i]['NOM_USUARIO']);
}
print urlencode(json_encode($result));
?>