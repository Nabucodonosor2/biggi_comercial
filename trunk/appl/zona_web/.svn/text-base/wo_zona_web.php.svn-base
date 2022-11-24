<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");


if (w_output::f_viene_del_menu('zona_web')) {
	$wo_zona_web = new wo_zona_web();
	$wo_zona_web->retrieve();
}
else {
	$wo = session::get('wo_zona_web');
	$wo->procesa_event();
	
}
?>