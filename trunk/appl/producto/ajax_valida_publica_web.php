<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_producto = $_REQUEST["cod_producto"];
$valida = 0;
$nom_zona = '';
$nom_familia = '';

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT	PUBLICA_WEB
				,NOM_ZONA
				,NOM_FAMILIA
		FROM	FAMILIA_PRODUCTO FP
				,FAMILIA F
				,ZONA_FAMILIA ZF
				,ZONA Z
		WHERE COD_PRODUCTO = '$cod_producto'
		AND FP.COD_FAMILIA = F.COD_FAMILIA
		AND ZF.COD_FAMILIA = F.COD_FAMILIA
		AND Z.COD_ZONA = ZF.COD_ZONA";

$result = $db->build_results($sql);	

for($i= 0 ; $i < count($result) ; $i++){
	if($result[$i]['PUBLICA_WEB'] == 'S'){
		$valida			= 1;
		$nom_zona		= $result[$i]['NOM_ZONA'];
		$nom_familia	= $result[$i]['NOM_FAMILIA'];
		break;
	}	
}

if($valida == 1)
	print urlencode($nom_zona).'|'.urlencode($nom_familia);
else	
	print 'NO_TIENE';

?>