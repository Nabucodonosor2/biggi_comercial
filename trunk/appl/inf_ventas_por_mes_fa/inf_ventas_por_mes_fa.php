<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (isset($_POST['b_ok'])) {
	$tipo = $_POST['TIPO'];
	$ano = $_POST['ANO_0'];
	$mes_desde = $_POST['MES_DESDE_0'];
	$mes_hasta = $_POST['MES_HASTA_0'];
	
	session::set("inf_ventas_por_mes_fa.ANO", $ano);
	session::set("inf_ventas_por_mes_fa.MES_DESDE", $mes_desde);
	session::set("inf_ventas_por_mes_fa.MES_HASTA", $mes_hasta);
	
	if ($tipo=='R') {
		header ('Location: inf_ventas_por_mes_resumen_fa.php');
	}else{
		$url = "../../../../commonlib/trunk/php/mantenedor.php?modulo=inf_ventas_por_mes_fa&cod_item_menu=4010";
		header ('Location:'.$url);
	}
}
else if (isset($_POST['b_cancel'])) {
	base::presentacion();
}
else {
	$temp = new Template_appl('inf_ventas_por_mes_fa.htm');	
	
	// make_menu
	$menu = session::get('menu_appl');
	$menu->draw($temp);
	
	$sql = "select year(getdate()) ANO
				, month(getdate()) MES_DESDE
				, month(getdate()) MES_HASTA
				, 'N' RESUMEN
				, 'D' DETALLE";
	$dw_param = new datawindow($sql);
	
	$sql = "select ANO,NOM_ANO 
            from dbo.f_anos_ventas_x_mes() 
            order by ano DESC";
	$dw_param->add_control(new drop_down_dw('ANO',$sql));
	
	
	$dw_param->add_control(new edit_mes('MES_DESDE'));
	$dw_param->add_control(new edit_mes('MES_HASTA'));
	$dw_param->add_control(new edit_radio_button('RESUMEN', 'R', 'N', 'Resumen', 'TIPO'));
	$dw_param->add_control(new edit_radio_button('DETALLE', 'D', 'N', 'Detalle', 'TIPO'));
	
	// draw
	$dw_param->retrieve();
	
	$cod_usuario = $dw_param->cod_usuario;
	if($cod_usuario == 1 || $cod_usuario == 2){
	    $dw_param->set_item(0, 'MES_DESDE', 1);
	}
	$dw_param->habilitar($temp, true);
	
	print $temp->toString();
}
?>