<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");


if (w_output::f_viene_del_menu('cartola_participacion')) {
    $wo_cartola_participacion = new wo_cartola_participacion();
    $wo_cartola_participacion->retrieve();
}
else {
	$wo = session::get('wo_cartola_participacion');
	$wo->procesa_event();
}
?>
