<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if(w_output::f_viene_del_menu('inf_backcharge')){
	$wo = new wo_inf_backcharge();
	$wo->retrieve();
}else{
	$wo = session::get('wo_inf_backcharge');
	$wo->procesa_event();
}
?>