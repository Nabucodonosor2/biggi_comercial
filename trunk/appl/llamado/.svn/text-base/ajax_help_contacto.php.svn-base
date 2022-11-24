<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$nom_contacto = $_REQUEST['nom_contacto'];
$rut_contacto = $_REQUEST['rut_contacto'];
$nom_persona = $_REQUEST['nom_persona'];
$cod_contacto = $_REQUEST['cod_contacto'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

if ($nom_contacto!='' || $rut_contacto!='' || $cod_contacto!=''){
	$sql = "SELECT C.COD_CONTACTO, NOM_CONTACTO, RUT, DIG_VERIF, DIRECCION, ci.NOM_CIUDAD, co.NOM_COMUNA
			FROM CONTACTO C LEFT OUTER JOIN CIUDAD CI ON CI.COD_CIUDAD = C.COD_CIUDAD
						LEFT OUTER JOIN COMUNA CO ON CO.COD_COMUNA = C.COD_COMUNA";

	if($nom_contacto!=''){
		$sql .= " WHERE NOM_CONTACTO LIKE '%".$nom_contacto."%'";
	}
	elseif($rut_contacto!=''){
		$sql .= " WHERE RUT=".$rut_contacto;
	}
	elseif($cod_contacto!=''){
		$sql .= " WHERE C.COD_CONTACTO =".$cod_contacto;
	}
			
	$result = $db->build_results($sql);
	$count_rows = $db->count_rows();

	if ($count_rows==0)
		$resp = "0|";
	elseif ($count_rows==1) {
		$resp = "1||||"; //LOS DATOS DE PERSONA SE ENVIAN VACIO
		$resp .= $result[0]['COD_CONTACTO']."|";
		$resp .= $result[0]['NOM_CONTACTO']."|";
		$resp .= $result[0]['RUT']."|";
		$resp .= $result[0]['DIG_VERIF']."|";
		$resp .= $result[0]['DIRECCION']."|";
		$resp .= $result[0]['NOM_CIUDAD']."|";
		$resp .= $result[0]['NOM_COMUNA'];
	}
	else
		$resp = $count_rows."|".$sql;
	
}
elseif ($nom_persona!=''){	
	
	$sql = "SELECT CP.COD_CONTACTO_PERSONA, 
				NOM_PERSONA, 
				CARGO, 
				CP.COD_CONTACTO, 
				NOM_CONTACTO, 
				RUT, 
				DIG_VERIF, 
				DIRECCION,
				CI.NOM_CIUDAD,
				CO.NOM_COMUNA
	FROM CONTACTO_PERSONA CP
		,CONTACTO C LEFT OUTER JOIN CIUDAD CI ON CI.COD_CIUDAD = C.COD_CIUDAD
			LEFT OUTER JOIN COMUNA CO ON CO.COD_COMUNA = C.COD_COMUNA
	WHERE CP.COD_CONTACTO = C.COD_CONTACTO
		AND NOM_PERSONA LIKE '%".$nom_persona."%'";
	
	
	$result = $db->build_results($sql);
	$count_rows = $db->count_rows();

	if ($count_rows==0)
		$resp = "0|";
	elseif ($count_rows==1) {
		$resp = "1|";
		$resp .= $result[0]['COD_CONTACTO_PERSONA']."|";
		$resp .= $result[0]['NOM_PERSONA']."|";
		$resp .= $result[0]['CARGO']."|";
		$resp .= $result[0]['COD_CONTACTO']."|";
		$resp .= $result[0]['NOM_CONTACTO']."|";
		$resp .= $result[0]['RUT']."|";
		$resp .= $result[0]['DIG_VERIF']."|";
		$resp .= $result[0]['DIRECCION']."|";
		$resp .= $result[0]['NOM_CIUDAD']."|";
		$resp .= $result[0]['NOM_COMUNA'];
	}
	else
		$resp = $count_rows."|".$sql;
}

print urlencode($resp);

?>