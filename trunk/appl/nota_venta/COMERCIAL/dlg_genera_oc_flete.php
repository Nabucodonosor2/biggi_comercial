<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$datos = $_REQUEST["datos"];
$array_datos = explode("|", $datos);
$cod_nota_venta = $array_datos[0];
$cod_user = $array_datos[1];
$nro_cuenta_corriente = $array_datos[2];
$temp = new Template_appl('dlg_genera_oc_flete.htm');
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql = "SELECT CONVERT(VARCHAR,RUT)+'-'+DIG_VERIF RUT
				,ALIAS
				,PP.COD_EMPRESA
				,NOM_EMPRESA
				,'N' PROVEDOR
			FROM PRODUCTO_PROVEEDOR PP
			,PRODUCTO P
			,EMPRESA E
			WHERE PP.COD_PRODUCTO = 'F'
			AND P.COD_PRODUCTO = PP.COD_PRODUCTO
			AND E.COD_EMPRESA = PP.COD_EMPRESA
			ORDER BY RUT";
	
	$dw_provedor = new datawindow($sql,'PROVEDORES');
	
	$dw_provedor->add_control(new static_text('NRO_CUENTA_CORRIENTE'));
	$dw_provedor->add_control(new static_text('ALIAS'));
	$dw_provedor->add_control(new static_text('NOM_EMPRESA'));
	$dw_provedor->add_control(new static_text('RUT'));
	$dw_provedor->add_control(new static_text('COD_EMPRESA'));
	$dw_provedor->add_control($control = new edit_radio_button('PROVEDOR', 'S', 'N','','R_PROV'));
	$control->set_onClick("provedor();");
	$dw_provedor->retrieve();
	$dw_provedor->habilitar($temp, true);
	
	$sql = "select 
			$cod_nota_venta COD_NOTA_VENTA
			,$cod_user COD_USUARIO
			,$nro_cuenta_corriente NRO_CUENTA_CORRIENTE
			, null MONTO_NETO
			,'' PROVEDOR_H ";
			
	$dw = new datawindow($sql);		
	$dw->add_control(new edit_num('MONTO_NETO'));
	$dw->add_control(new edit_text_hidden('COD_NOTA_VENTA'));
	$dw->add_control(new edit_text_hidden('COD_USUARIO'));
	$dw->add_control(new edit_text_hidden('NRO_CUENTA_CORRIENTE'));
	$dw->add_control(new edit_text_hidden('PROVEDOR_H'));
	$dw->retrieve();
	$dw->habilitar($temp, true);
	print $temp->toString();
?>