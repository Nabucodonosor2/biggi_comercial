<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl('dlg_tipo_impresion_gr.htm');
$sql = "SELECT NULL PRINT_NORMAL
			  ,NULL PRINT_SECUNDARIO";
$dw = new datawindow($sql);

$dw->add_control(new edit_radio_button('PRINT_NORMAL', 'S', 'N', 'Print Normal', 'TIPO'));
$dw->add_control(new edit_radio_button('PRINT_SECUNDARIO', 'N', 'N', 'Print Nuevo', 'TIPO'));
$dw->insert_row();
$dw->set_item(0, 'PRINT_NORMAL', 'S');
$dw->habilitar($temp, true);

print $temp->toString();	
?>