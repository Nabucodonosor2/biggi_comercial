<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if(w_output::f_viene_del_menu('inf_ventas_por_mes_fa')) {
	$wo = new wo_inf_ventas_por_mes_fa();
	$wo->retrieve();
}else{
	$wo = session::get('wo_inf_ventas_por_mes_fa');
	$wo->procesa_event();
}
?>