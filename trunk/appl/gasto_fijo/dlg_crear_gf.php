<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl('dlg_crear_gf.htm');
$sql = "select null REFERENCIA
			  ,null NOTAS";
$dw = new datawindow($sql);
$dw->add_control(new edit_text_upper('REFERENCIA', 120, 150));
$dw->add_control(new edit_text_multiline('NOTAS', 54, 4));
$dw->retrieve();
$dw->habilitar($temp, true);
print $temp->toString();	
?>