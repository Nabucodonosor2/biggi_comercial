<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if(w_output::f_viene_del_menu('inf_por_cobrar_tbk')){
	$wo = new wo_inf_por_cobrar_tbk();
	$wo->retrieve();
}else{
	$wo = session::get('wo_inf_por_cobrar_tbk');
	$wo->procesa_event();
}
?>