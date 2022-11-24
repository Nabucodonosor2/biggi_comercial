<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$nom_header = $_REQUEST['nom_header'];
$valor_filtro = $_REQUEST['valor_filtro'];

$temp = new Template_appl('dlg_estado.htm');	

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "select COD_ESTADO_ORDEN_COMPRA, NOM_ESTADO_ORDEN_COMPRA 
        from ESTADO_ORDEN_COMPRA order by COD_ESTADO_ORDEN_COMPRA";
$result = $db->build_results($sql);/*
if ($valor_filtro=='') {	//todos
	for ($i=0 ; $i < count($result); $i++) {
		$valor_filtro = $valor_filtro.$result[$i]['COD_ESTADO_ORDEN_COMPRA'].',';
	}
	$valor_filtro = substr($valor_filtro, 0, strlen($valor_filtro)-1);	//borra ultima coma
}*/

$check_box = new edit_check_box('SELECCION', 'S', 'N');
$edit_text = new edit_text_hidden('COD_ESTADO_ORDEN_COMPRA');
$a_values = explode(",", $valor_filtro);
for ($i=0 ; $i < count($result); $i++) {
	$temp->gotoNext("ESTADO_ORDEN_COMPRA");		

	if ($i % 2 == 0)
		$temp->setVar("ESTADO_ORDEN_COMPRA.DW_TR_CSS", datawindow::css_claro);
	else
		$temp->setVar("ESTADO_ORDEN_COMPRA.DW_TR_CSS", datawindow::css_oscuro);

	$cod_tipo_producto = $result[$i]['COD_ESTADO_ORDEN_COMPRA'];
	if ($a_values[$i] == $cod_tipo_producto)
		$html = $check_box->draw_entrable('S', $i);
	else			
		$html = $check_box->draw_entrable('N', $i);
		
	
	$temp->setVar("ESTADO_ORDEN_COMPRA.SELECCION", $html);
		
	$html = $edit_text->draw_entrable($cod_tipo_producto, $i);
	$temp->setVar("ESTADO_ORDEN_COMPRA.COD_ESTADO_ORDEN_COMPRA", $html);			

	$nom_tipo_producto = $result[$i]['NOM_ESTADO_ORDEN_COMPRA'];
	$temp->setVar("ESTADO_ORDEN_COMPRA.NOM_ESTADO_ORDEN_COMPRA", $nom_tipo_producto);			
}
print $temp->toString();

?>