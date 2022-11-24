<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (isset($_POST['b_ok'])) {
	$fecha_ini = $_POST['FECHA_INICIO_0'];
	$fecha_fin = $_POST['FECHA_TERMINO_0'];
	
	session::set("inf_backcharge.FECHA_INICIO", $fecha_ini);
	session::set("inf_backcharge.FECHA_TERMINO", $fecha_fin);

	$url = "../../../../commonlib/trunk/php/mantenedor.php?modulo=inf_backcharge&cod_item_menu=4075";
	header ('Location:'.$url);
	
}
else if (isset($_POST['b_cancel'])) {
	base::presentacion();
}
else {
	$temp = new Template_appl('inf_backcharge.htm');	
	
	// make_menu
	$menu = session::get('menu_appl');
	$menu->draw($temp);
	
	$sql = "SELECT convert(varchar, dbo.f_makedate(1, 1, year(getdate())), 103) FECHA_INICIO
				   ,convert(varchar, getdate(), 103) FECHA_TERMINO";
	$dw_param = new datawindow($sql);
	$dw_param->add_control(new edit_date('FECHA_INICIO'));
	$dw_param->add_control(new edit_date('FECHA_TERMINO'));

	// draw
	$dw_param->retrieve();
	$dw_param->habilitar($temp, true);
	
	print $temp->toString();
}
?>