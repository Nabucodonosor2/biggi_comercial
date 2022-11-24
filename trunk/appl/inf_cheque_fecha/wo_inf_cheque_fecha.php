<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('inf_cheque_fecha')) {
	$wo = new wo_inf_cheque_fecha();
	$wo->retrieve();
}
else {
	$wo = session::get('wo_inf_cheque_fecha');
	$wo->procesa_event();
}
?>