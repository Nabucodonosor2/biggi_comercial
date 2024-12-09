<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('informe_comision')) {
	$wo_informe_comision = new wo_informe_comision();
	$wo_informe_comision->retrieve();
}
else {
	$wo = session::get('wo_informe_comision');
	$wo->procesa_event();
}
?>