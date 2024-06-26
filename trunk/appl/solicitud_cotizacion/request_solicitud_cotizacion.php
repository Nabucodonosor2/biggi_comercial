<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$prompt = $_REQUEST['prompt'];
$valor =  $_REQUEST['valor'];
$temp = new Template_appl('request_solicitud_cotizacion.htm');

$sql = "SELECT NULL MOTIVO_ANULA";
$dw = new datawindow($sql);

$dw->add_control(new edit_text_multiline('MOTIVO_ANULA',54,4));
$dw->insert_row();
$dw->habilitar($temp, true);

$temp->setVar("PROMPT", $prompt);
$temp->setVar("VALOR", $valor);

print $temp->toString();
?>