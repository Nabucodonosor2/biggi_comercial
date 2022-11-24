<?php
//cambio
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");


if (w_input::f_viene_del_output('llamado_accion')) {
	$wi = new wi_llamado_accion($_REQUEST['cod_item_menu']);
	$rec_no = $_REQUEST['rec_no'];
	$wi->goto_record($rec_no);
}
else {
	$wi = session::get('wi_llamado_accion');
	$wi->procesa_event();
}
?>