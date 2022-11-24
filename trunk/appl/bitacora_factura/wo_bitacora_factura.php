<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('bitacora_factura')) {
	$wo = new wo_bitacora_factura();
	$wo->retrieve();
}
else {
	$wo = session::get('wo_bitacora_factura');
	$wo->procesa_event();
}
?>