<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$vigente = $_REQUEST["vigente"];

$resultado = "";
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
/*
 * opcion ORIGINAL deja el selec como el original, cuando se cargo por primera vez la ventana
 */
if ($vigente == 'NO_VIGENTE'){ //devuelve vigente y no vigente
	$sql = "SELECT DISTINCT u.NOM_USUARIO 
			FROM inf_oc_por_facturar_bodega inf_oc,USUARIO u
			where  (U.VENDEDOR_VISIBLE_FILTRO =1
			or U.VENDEDOR_VISIBLE_FILTRO=2 )
			order by NOM_USUARIO";
	
}else if ($vigente == 'ORIGINAL'){//se mantiene el select del inicio, cuando se carga por primera vez la ventana
	$sql = "SELECT DISTINCT COD_USUARIO_VENDEDOR,NOM_USUARIO 
				FROM inf_oc_por_facturar_bodega 
				order by COD_USUARIO_VENDEDOR";
}

$result = $db->build_results($sql);

for ($i=0; $i<count($result); $i++) {
$result[$i]['COD_USUARIO'] = urlencode($result[$i]['COD_USUARIO']);
$result[$i]['NOM_USUARIO'] = urlencode($result[$i]['NOM_USUARIO']);
}
print urlencode(json_encode($result));
?>