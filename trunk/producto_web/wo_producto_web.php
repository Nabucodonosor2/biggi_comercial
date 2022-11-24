<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('producto_web')) {
	$wo = new wo_producto_web();
	$wo->retrieve();
}
else {
	$wo = session::get('wo_producto_web');
	$wo->procesa_event();
}
?>