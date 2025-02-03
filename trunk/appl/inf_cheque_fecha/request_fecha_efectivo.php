<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$prompt = $_REQUEST['prompt'];
$valor =  $_REQUEST['valor'];
$temp = new Template_appl('request_fecha_ingreso.htm');	
$temp->setVar("PROMPT", $prompt);
$temp->setVar("VALOR", $valor);

$sql="SELECT NULL FECHA_DOC
			 ,'S' CREA_MASIVO
			 ,'N' CREA_ESPECIFICO
			 ,NULL COD_INGRESO_PAGO
			 ,NULL NRO_DOC
			 ,NULL NEW_FECHA_DOC";

$dw = new datawindow($sql);

$dw->add_control(new edit_date('FECHA_DOC'));
$dw->add_control($control = new edit_num('COD_INGRESO_PAGO'));
$control->set_onChange("valida_cod_ingreso();");
$dw->add_control($control = new edit_num('NRO_DOC'));
$control->set_onChange("valida_cod_ingreso();");
$dw->add_control(new edit_date('NEW_FECHA_DOC'));
$dw->add_control($control = new edit_radio_button('CREA_MASIVO', 'S', 'N', 'Ingrese la nueva fecha para depósito', 'CHEQUE_FECHA'));
$control->set_onChange("display_div();");
$dw->add_control($control = new edit_radio_button('CREA_ESPECIFICO', 'S', 'N', 'Cambiar fecha desposito a otro cheque', 'CHEQUE_FECHA'));
$control->set_onChange("display_div();");
$dw->retrieve();
	
$dw->habilitar($temp, true);
print $temp->toString();
?>