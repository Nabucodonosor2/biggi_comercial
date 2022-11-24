<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$vigente = $_REQUEST["vigente"];

$resultado = "";
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
/*
 * opcion ORIGINAL deja el selec como el original, cuando se cargo por primera vez la ventana
 */
if ($vigente == 'NO_VIGENTE'){ //devuelve vigente y no vigente
	$sql = "select distinct U.COD_USUARIO, U.NOM_USUARIO 
			from FACTURA F, USUARIO U 
			where F.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO 
			AND(U.VENDEDOR_VISIBLE_FILTRO =1
			 OR U.VENDEDOR_VISIBLE_FILTRO=2 )
			order by NOM_USUARIO ASC";
	
}else if ($vigente == 'ORIGINAL'){//se mantiene el select del inicio, cuando se carga por primera vez la ventana
	$sql = "select distinct U.COD_USUARIO, U.NOM_USUARIO 
			from FACTURA F, USUARIO U 
			where F.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO 
			order by NOM_USUARIO";
}

$result = $db->build_results($sql);

for ($i=0; $i<count($result); $i++) {
$result[$i]['COD_USUARIO'] = urlencode($result[$i]['COD_USUARIO']);
$result[$i]['NOM_USUARIO'] = urlencode($result[$i]['NOM_USUARIO']);
}
print urlencode(json_encode($result));
?>