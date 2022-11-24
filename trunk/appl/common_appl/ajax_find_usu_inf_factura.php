<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$vigente = $_REQUEST["vigente"];

$resultado = "";
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
/*
 * opcion ORIGINAL deja el selec como el original, cuando se cargo por primera vez la ventana
 */

if ($vigente == 'NO_VIGENTE'){ //devuelve vigente y no vigente
	$sql = "select	distinct I.COD_USUARIO_VENDEDOR1 COD_USUARIO ,U.NOM_USUARIO 
			FROM INF_FACTURAS_POR_COBRAR I left outer join USUARIO U on U.COD_USUARIO = I.COD_USUARIO_VENDEDOR1 
			WHERE (U.VENDEDOR_VISIBLE_FILTRO =1
			or U.VENDEDOR_VISIBLE_FILTRO=2)
			order by U.NOM_USUARIO";
	
}else if ($vigente == 'ORIGINAL'){//se mantiene el select del inicio, cuando se carga por primera vez la ventana
	$sql = "select	distinct I.COD_USUARIO_VENDEDOR1 COD_USUARIO ,U.NOM_USUARIO 
			FROM INF_FACTURAS_POR_COBRAR I left outer join USUARIO U on U.COD_USUARIO = I.COD_USUARIO_VENDEDOR1 
			order by U.NOM_USUARIO";
}

$result = $db->build_results($sql);

for ($i=0; $i<count($result); $i++) {
$result[$i]['COD_USUARIO'] = urlencode($result[$i]['COD_USUARIO']);
$result[$i]['NOM_USUARIO'] = urlencode($result[$i]['NOM_USUARIO']);
}
print urlencode(json_encode($result));
?>