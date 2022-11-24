<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('contacto')) {
	$wo_contacto = new wo_contacto();
	$wo_contacto->retrieve();
}
else {
	$wo = session::get('wo_contacto');
	$wo->procesa_event();
}
?>