<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$sql = $_REQUEST['sql'];

$sql = str_replace("\\'", "'", $sql);		// Las comillas simples ', vuelven como \'
$K_MAX_LISTA = 100;
$temp = new Template_appl('help_lista_persona.htm');	

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$result = $db->build_results($sql, $K_MAX_LISTA);

if ($db->count_rows() > $K_MAX_LISTA) {
	$temp->setVar("TIENE_MAS_REGISTROS", 'Se cargaron los primeros '.$K_MAX_LISTA.' registos de un total de '.$db->count_rows().' registros.<br>Sea má especifico en los datos de búsqueda.');			
	$count = $K_MAX_LISTA;
}
else {
	$temp->setVar("TIENE_MAS_REGISTROS", '');			
	$count = $db->count_rows();
}

$fields = $db->get_fields();

for ($i=0 ; $i <$count; $i++) {
	$returnValue = '1|';
	$returnValue .= $result[$i]['COD_CONTACTO_PERSONA']."|";
	$returnValue .= $result[$i]['NOM_PERSONA']."|";
	$returnValue .= $result[$i]['CARGO']."|";
	$returnValue .= $result[$i]['COD_CONTACTO']."|";
	$returnValue .= $result[$i]['NOM_CONTACTO']."|";
	$returnValue .= $result[$i]['RUT']."|";
	$returnValue .= $result[$i]['DIG_VERIF']."|";
	$returnValue .= $result[$i]['DIRECCION']."|";
	$returnValue .= $result[$i]['NOM_CIUDAD']."|";
	$returnValue .= $result[$i]['NOM_COMUNA'];
	
	$temp->gotoNext("EMPRESA");		

	if ($i % 2 == 0)
		$temp->setVar("EMPRESA.DW_TR_CSS", datawindow::css_claro);
	else
		$temp->setVar("EMPRESA.DW_TR_CSS", datawindow::css_oscuro);

	for ($j=0; $j<count($fields); $j++) {
		if ($j==1)
			$temp->setVar("EMPRESA.".$fields[$j]->name, '<a href="#" onClick="returnValue=\''.$returnValue.'\'; setWindowReturnValue(returnValue); window.close();">'.$result[$i][$fields[$j]->name].'</a>');
		else
			$temp->setVar("EMPRESA.".$fields[$j]->name, $result[$i][$fields[$j]->name]);			
	}
}
print $temp->toString();		
?>