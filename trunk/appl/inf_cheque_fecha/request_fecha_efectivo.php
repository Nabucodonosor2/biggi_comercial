<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$prompt = $_REQUEST['prompt'];
$valor =  $_REQUEST['valor'];
$count =  $_REQUEST['count'];
$temp = new Template_appl('request_fecha_efectivo.htm');	
$temp->setVar("PROMPT", $prompt);
$temp->setVar("VALOR", $valor);
$temp->setVar("COUNT", $count);

$sql="SELECT CONVERT(VARCHAR, GETDATE(), 103) FECHA_DOC
			 ,CONVERT(VARCHAR, GETDATE(), 103) FECHA_DOC_H";

$dw = new datawindow($sql);

$dw->add_control($control = new edit_date('FECHA_DOC'));
$control->set_onChange("valida_fecha();");
$dw->add_control(new edit_text_hidden('FECHA_DOC_H',10,10));
$dw->retrieve();
	
$dw->habilitar($temp, true);
print $temp->toString();
?>