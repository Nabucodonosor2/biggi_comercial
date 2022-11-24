<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");


if (w_output::f_viene_del_menu('llamado_accion')) {
	$wo_llamado_accion = new wo_llamado_accion();
	$wo_llamado_accion->retrieve();
}
else {
	$wo = session::get('wo_llamado_accion');
	$wo->procesa_event();
	
}
?>