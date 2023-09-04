<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
$cod_nota_venta = $_REQUEST['cod_nota_venta'];

$temp = new Template_appl('dlg_lista_nc_compra.htm');	
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT 'N' SELECCION
				,OC.COD_ORDEN_COMPRA
				,OC.TOTAL_NETO TOTAL_NETO_OC
				,'TODOINOX' PROVEEDOR
				,1302 COD_EMPRESA_PROVEEDOR
				,FA.NRO_FACTURA
				,FA.TOTAL_NETO TOTAL_NETO_FA
				,NC.NRO_NOTA_CREDITO
				,NC.TOTAL_NETO TOTAL_NETO_NC
		FROM TODOINOX.dbo.NOTA_CREDITO NC
			,TODOINOX.dbo.FACTURA FA
			,ORDEN_COMPRA OC
		WHERE NC.COD_DOC = FA.COD_FACTURA
		AND FA.NRO_ORDEN_COMPRA = CONVERT(VARCHAR, OC.COD_ORDEN_COMPRA)
		AND OC.COD_NOTA_VENTA = $cod_nota_venta
		AND OC.COD_EMPRESA = 1302	--Empresa TODOINOX en COMERCIAL
		AND OC.COD_ESTADO_ORDEN_COMPRA IN (2, 3) --CERRADA / AUTORIZADA
		AND FA.COD_ESTADO_DOC_SII = 3 --ENVIADA A SII de Factura
		AND FA.COD_EMPRESA = 1	-- Empresa COMERCIAL en TODOINOX
		AND NC.COD_ESTADO_DOC_SII = 3 --ENVIADA A SII de Nota de Credito
		AND NC.NRO_NOTA_CREDITO NOT IN (SELECT NRO_NOTA_CREDITO
										FROM REBAJA_COMPRA_NETA_NV R
										WHERE COD_EMPRESA = 1302)
		UNION
		SELECT 'N' SELECCION
				,OC.COD_ORDEN_COMPRA
				,OC.TOTAL_NETO TOTAL_NETO_OC
				,'BODEGA' PROVEEDOR
				,1138 COD_EMPRESA_PROVEEDOR
				,FA.NRO_FACTURA
				,FA.TOTAL_NETO TOTAL_NETO_FA
				,NC.NRO_NOTA_CREDITO
				,NC.TOTAL_NETO TOTAL_NETO_NC
		FROM BODEGA_BIGGI.dbo.NOTA_CREDITO NC
				,BODEGA_BIGGI.dbo.FACTURA FA
				,ORDEN_COMPRA OC
		WHERE NC.COD_DOC = FA.COD_FACTURA
		AND FA.NRO_ORDEN_COMPRA = CONVERT(VARCHAR, OC.COD_ORDEN_COMPRA)
		AND OC.COD_NOTA_VENTA = $cod_nota_venta
		AND OC.COD_EMPRESA = 1138 --Empresa BODEGA en COMERCIAL
		AND OC.COD_ESTADO_ORDEN_COMPRA IN (2, 3) --CERRADA / AUTORIZADA
		AND FA.COD_ESTADO_DOC_SII = 3 --ENVIADA A SII de Factura
		AND FA.COD_EMPRESA = 1 --Empresa COMERCIAL en BODEGA
		AND NC.COD_ESTADO_DOC_SII = 3 --ENVIADA A SII de Nota de Credito
		AND NC.NRO_NOTA_CREDITO NOT IN (SELECT NRO_NOTA_CREDITO
										FROM REBAJA_COMPRA_NETA_NV R
										WHERE COD_EMPRESA = 1138)";

$dw = new datawindow($sql, 'NOTA_CREDITO');
$dw->add_control($control = new edit_check_box('SELECCION','S','N',''));
$dw->add_control(new static_num('TOTAL_NETO_OC'));
$dw->add_control(new static_num('TOTAL_NETO_FA'));
$dw->add_control(new static_num('TOTAL_NETO_NC'));

$dw->add_control(new static_text('COD_ORDEN_COMPRA'));
$dw->add_control(new static_text('NRO_FACTURA'));
$dw->add_control(new static_text('NRO_NOTA_CREDITO'));
$dw->add_control(new edit_text_hidden('COD_EMPRESA_PROVEEDOR'));

$dw->retrieve();
$dw->habilitar($temp, true);


$sql = "SELECT 'S' SELECCION_U
				,COD_ORDEN_COMPRA COD_ORDEN_COMPRA_U
				,MONTO_ORDEN_COMPRA MONTO_ORDEN_COMPRA_U
				,CASE
					WHEN COD_EMPRESA = 1302 THEN 'TODOINOX'
					ELSE 'BODEGA'
				END PROVEEDOR_U
				,NRO_FACTURA NRO_FACTURA_U
				,MONTO_FACTURA MONTO_FACTURA_U
				,NRO_NOTA_CREDITO NRO_NOTA_CREDITO_U
				,MONTO_NOTA_CREDITO MONTO_NOTA_CREDITO_U
		FROM REBAJA_COMPRA_NETA_NV
		WHERE COD_NOTA_VENTA = $cod_nota_venta";

$dw = new datawindow($sql, 'NOTA_CREDITO_USADO');
$dw->add_control($control = new edit_check_box('SELECCION_U','S','N',''));
$dw->add_control(new static_num('MONTO_ORDEN_COMPRA_U'));
$dw->add_control(new static_num('MONTO_FACTURA_U'));
$dw->add_control(new static_num('MONTO_NOTA_CREDITO_U'));
$dw->set_entrable('SELECCION_U', false);

$dw->retrieve();
$dw->habilitar($temp, true);

print $temp->toString();
?>