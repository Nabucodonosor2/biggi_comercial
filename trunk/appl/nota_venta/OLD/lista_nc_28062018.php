<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_nota_venta = $_REQUEST['cod_nota_venta'];
$temp = new Template_appl('lista_nc.htm');	
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT SELECCION
			  ,NRO_NOTA_CREDITO
			  ,TOTAL_NETO
			  ,TOTAL_CONSIDERADO
			  ,COD_NOTA_VENTA_NC
		FROM NOTA_VENTA_NC
		WHERE COD_NOTA_VENTA = $cod_nota_venta";

$dw = new datawindow($sql, 'NOTA_CREDITO_IT');
$dw->add_control(new edit_text_hidden('COD_NOTA_VENTA_NC'));
$dw->add_control($control = new edit_check_box('SELECCION','S','N',''));
$control->set_onChange("check_value(this);");
$dw->add_control(new static_text('NRO_NOTA_CREDITO'));
$dw->add_control(new static_num('TOTAL_NETO'));
$dw->add_control($control = new edit_num('TOTAL_CONSIDERADO', 10, 10));
$control->set_onChange("valida_considerado(this);");
$dw->retrieve();
$dw->habilitar($temp, true);
print $temp->toString();
?>