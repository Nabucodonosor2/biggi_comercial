<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_doc	= $_REQUEST['cod_doc'];
$temp = new Template_appl('request_gd_parcial.htm');
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT dbo.f_get_parametro(29) MAX_ITEM";
$result = $db->build_results($sql);

$title_doc = "Item Nota de Venta";
$sql = "SELECT 'N' SELECCION
			  ,ITEM
			  ,COD_ITEM_NOTA_VENTA COD_DOC
			  ,COD_PRODUCTO
			  ,NOM_PRODUCTO
			  ,CANTIDAD CANTIDAD_NV
			  ,dbo.f_nv_cant_por_despachar(COD_ITEM_NOTA_VENTA, 'TODO_ESTADO') CANTIDAD
			  ,0 CANTIDAD_X_FACTURAR
		FROM ITEM_NOTA_VENTA
		WHERE COD_NOTA_VENTA = $cod_doc
		AND dbo.f_nv_cant_por_despachar(COD_ITEM_NOTA_VENTA, 'TODO_ESTADO') > 0
		AND PRECIO > 0";

$temp->setVar("TITLE_DOC", $title_doc);
$temp->setVar("MAX_ITEM", '<input class="input_text" name="MAX_ITEM_0" id="MAX_ITEM_0" value="'.$result[0]['MAX_ITEM'].'" size="100" maxlength="100" type="hidden">');
$temp->setVar("COD_NOTA_VENTA", '<input class="input_text" name="COD_NOTA_VENTA_0" id="COD_NOTA_VENTA_0" value="'.$cod_doc.'" size="100" maxlength="100" type="hidden">');

$dw = new datawindow($sql, 'ITEM_FACTURA');
$dw->add_control(new edit_text_hidden('COD_DOC'));
$dw->add_control($control = new edit_check_box('SELECCION','S','N',''));
$control->set_onChange("value_check(this); sel_count();");
$dw->add_control(new static_text('COD_PRODUCTO'));
$dw->add_control(new static_text('NOM_PRODUCTO'));
$dw->add_control(new static_num('CANTIDAD_NV',1));
$dw->add_control(new static_num('CANTIDAD',1));
$dw->add_control($control = new edit_num('CANTIDAD_X_FACTURAR', 5, 10, 2));
$control->set_onChange("value_check(this); sel_count();");
$dw->retrieve();

$dw->habilitar($temp, true);
print $temp->toString();
?>