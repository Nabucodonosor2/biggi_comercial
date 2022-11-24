<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");


if (w_output::f_viene_del_menu('familia_web')) {
	$wo_familia_web = new wo_familia_web();
	$wo_familia_web->retrieve();
}
else {
	$wo = session::get('wo_familia_web');
	$wo->procesa_event();
	
}
?>