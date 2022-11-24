<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('bitacora_cotizacion')) {
	$wo = new wo_bitacora_cotizacion();
	$wo->retrieve();
}
else {
	$wo = session::get('wo_bitacora_cotizacion');
	$wo->procesa_event();
}
?>