<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_usuario = session::get("COD_USUARIO");	// viene del login

$temp = new Template_appl('select_printer_dte.htm');
$sql = "select 0 COD_IMPRESORA_DTE";
$dw = new datawindow($sql);
$sql = "select COD_IMPRESORA_DTE
				,NOM_IMPRESORA
		from IMPRESORA_DTE";
$dw->add_control(new drop_down_dw('COD_IMPRESORA_DTE', $sql, 0, '', false));
$dw->insert_row();
$dw->habilitar($temp, true);
print $temp->toString();	
?>