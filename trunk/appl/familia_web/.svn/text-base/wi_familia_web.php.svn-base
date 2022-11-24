<?php
//cambio
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");


if (w_input::f_viene_del_output('familia_web')) {
	$wi = new wi_familia_web($_REQUEST['cod_item_menu']);
	$rec_no = $_REQUEST['rec_no'];
	$wi->goto_record($rec_no);
}
else {
	$wi = session::get('wi_familia_web');
	$wi->procesa_event();
}
?>