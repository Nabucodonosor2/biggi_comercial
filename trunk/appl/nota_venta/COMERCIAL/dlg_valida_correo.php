<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$cod_nota_venta = $_REQUEST["cod_nota_venta"];

$temp = new Template_appl('dlg_valida_correo.htm');
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql = "SELECT P.NOM_PERSONA
					,P.EMAIL 
					,NV.TOTAL_CON_IVA
					,NV.TOTAL_CON_IVA - isnull((SELECT SUM(MONTO_ASIGNADO) FROM INGRESO_PAGO_FACTURA WHERE COD_DOC =$cod_nota_venta),0) CALCULO_TOTAL_CON_IVA
					,NV.TOTAL_CON_IVA - isnull((SELECT SUM(MONTO_ASIGNADO) FROM INGRESO_PAGO_FACTURA WHERE COD_DOC =$cod_nota_venta),0) CALCULO_TOTAL
					FROM NOTA_VENTA NV
					,PERSONA P 
					WHERE NV.COD_PERSONA=P.COD_PERSONA
					AND COD_NOTA_VENTA =$cod_nota_venta";
	/*$result = $db->build_results($sql);
	if($result[0]['TOTAL_CON_IVA'] == null || $result[0]['TOTAL_CON_IVA'] < 0){
		$result[0]['TOTAL_CON_IVA'] =0;
	}*/
	$dw_valida_correo = new datawindow($sql);
	$dw_valida_correo->add_control(new edit_text('NOM_PERSONA',47,50));
	$dw_valida_correo->add_control(new edit_text('EMAIL',47,50));
	$dw_valida_correo->add_control(new edit_text('TOTAL_CON_IVA',47,50,'hidden'));
	$dw_valida_correo->add_control(new edit_text('CALCULO_TOTAL',47,50,'hidden'));
	$dw_valida_correo->add_control($control = new edit_num('CALCULO_TOTAL_CON_IVA',12,10,0));
	$control->set_onChange('valida_rangos();');
	$dw_valida_correo->retrieve();
	//$dw_valida_correo->set_item(0,'TOTAL_CON_IVA',$result[0]['TOTAL_CON_IVA']);
	$dw_valida_correo->habilitar($temp, true);
	print $temp->toString();
?>