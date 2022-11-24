<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (isset($_REQUEST['cod_item_menu'])) {
	$w = new wi_consulta_stock_comercial();
} else {
	$w = session::get('wi_consulta_stock_comercial');
	$w->procesa_event();
}
?>