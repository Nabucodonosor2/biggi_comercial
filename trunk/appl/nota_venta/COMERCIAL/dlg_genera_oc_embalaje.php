<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$datos = $_REQUEST["datos"];
$array_datos = explode("|", $datos);
$cod_nota_venta = $array_datos[0];
$cod_user = $array_datos[1];
$temp = new Template_appl('dlg_genera_oc_embalaje.htm');
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql = "SELECT null MONTO_NETO,
				 $cod_nota_venta COD_NOTA_VENTA,
				 $cod_user COD_USUARIO";
	
	$dw_genera_oc_embalaje = new datawindow($sql);
	$dw_genera_oc_embalaje->add_control(new edit_num('MONTO_NETO',20,20));
	$dw_genera_oc_embalaje->add_control(new edit_text_hidden('COD_NOTA_VENTA'));
	$dw_genera_oc_embalaje->add_control(new edit_text_hidden('COD_USUARIO'));
	$dw_genera_oc_embalaje->retrieve();
	$dw_genera_oc_embalaje->habilitar($temp, true);
	print $temp->toString();
?>