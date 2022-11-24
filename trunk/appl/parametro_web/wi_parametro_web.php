<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");


if (isset($_REQUEST['cod_item_menu'])) {
	$w = new wi_parametro_web();
} else {
	$w = session::get('wi_parametro_web');
	$w->procesa_event();
}
?>