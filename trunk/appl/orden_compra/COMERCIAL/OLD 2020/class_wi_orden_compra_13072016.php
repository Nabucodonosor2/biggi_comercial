<?php
/////////////////////////////////////////
///COMERCIAL
/////////////////////////////////////////

class wi_orden_compra extends wi_orden_compra_base {
	function wi_orden_compra($cod_item_menu) {
		parent::wi_orden_compra_base($cod_item_menu);
		$this->dws['dw_orden_compra']->add_control(new edit_text('COD_CUENTA_CORRIENTE',10,10, 'hidden'));
        $this->dws['dw_orden_compra']->add_control(new edit_text('NRO_CUENTA_CORRIENTE',10,10, 'hidden'));
	}
	
	function load_record(){
		parent::load_record();
		
		$AUTORIZA_FACTURACION = $this->dws['dw_orden_compra']->get_item(0, 'AUTORIZA_FACTURACION');
		if($AUTORIZA_FACTURACION <> 'S'){
			$COD_EMPRESA = $this->dws['dw_orden_compra']->get_item(0, 'COD_EMPRESA');
			if($COD_EMPRESA == 1302){ //Todoinox
				$this->dws['dw_orden_compra']->set_entrable('AUTORIZA_FACTURACION', true);
				$this->dws['dw_orden_compra']->set_entrable('FECHA_SOLICITA_FACTURACION', true);
			}else{
				$this->dws['dw_orden_compra']->set_entrable('AUTORIZA_FACTURACION', false);
				$this->dws['dw_orden_compra']->set_entrable('FECHA_SOLICITA_FACTURACION', false);
			}
		}else{
			$this->dws['dw_orden_compra']->set_entrable('AUTORIZA_FACTURACION', false);
			$this->dws['dw_orden_compra']->set_entrable('FECHA_SOLICITA_FACTURACION', false);
		}
		$priv = $this->get_privilegio_opcion_usuario('991540', $this->cod_usuario);
		
		if($priv <> 'E'){
			$this->dws['dw_item_orden_compra']->b_add_line_visible = false;
			$this->dws['dw_item_orden_compra']->b_del_line_visible = false;
			
			$this->dws['dw_item_orden_compra']->set_entrable('ORDEN', false);
			$this->dws['dw_item_orden_compra']->set_entrable('ITEM', false);
			$this->dws['dw_item_orden_compra']->add_control(new static_text('COD_PRODUCTO'));
			$this->dws['dw_item_orden_compra']->set_entrable('NOM_PRODUCTO', false);
			
			$this->dws['dw_item_orden_compra']->set_protect('CANTIDAD', "[COD_PRODUCTO] != 'F' &&
																		 [COD_PRODUCTO] != 'E' &&
																		 [COD_PRODUCTO] != 'I' &&
																		 [COD_PRODUCTO] != 'VT'");
			
			$this->dws['dw_item_orden_compra']->set_protect('PRECIO', "[COD_PRODUCTO] != 'F' &&
																	   [COD_PRODUCTO] != 'E' &&
																	   [COD_PRODUCTO] != 'I' &&
																	   [COD_PRODUCTO] != 'VT'");
			
		}else{
			$this->dws['dw_item_orden_compra']->b_add_line_visible = true;
			$this->dws['dw_item_orden_compra']->b_del_line_visible = true;
			
			$this->dws['dw_item_orden_compra']->set_entrable('ORDEN', true);
			$this->dws['dw_item_orden_compra']->set_entrable('ITEM', true);
			$this->dws['dw_item_orden_compra']->add_control($control = new edit_text('COD_PRODUCTO', 25, 30));
			$control->set_onChange("change_item_orden_compra(this, 'COD_PRODUCTO');");
			$this->dws['dw_item_orden_compra']->set_entrable('NOM_PRODUCTO', true);
		}
	}
	
	function new_record(){
		parent::new_record();
		
		$this->dws['dw_orden_compra']->set_item(0, 'AUTORIZA_FACTURACION', 'N');
		$this->dws['dw_orden_compra']->set_item(0, 'FECHA_SOLICITA_FACTURACION', '');
		
		$this->dws['dw_orden_compra']->set_entrable('AUTORIZA_FACTURACION', false);
		$this->dws['dw_orden_compra']->set_entrable('FECHA_SOLICITA_FACTURACION', false);
	}
	
	function print_record() {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$cod_orden_compra = $this->get_key();
		$sql= "SELECT OC.COD_ORDEN_COMPRA,
					OC.COD_NOTA_VENTA,
					OC.SUBTOTAL,
					OC.PORC_DSCTO1,
					OC.MONTO_DSCTO1,
					OC.PORC_DSCTO2,
					OC.MONTO_DSCTO2,
					OC.TOTAL_NETO,
					OC.PORC_IVA,
					OC.MONTO_IVA,
					OC.TOTAL_CON_IVA,
					OC.REFERENCIA,																
					OC.OBS,
					E.NOM_EMPRESA,
					E.RUT,
					E.DIG_VERIF,
					dbo.f_get_direccion('SUCURSAL', OC.COD_SUCURSAL, '[DIRECCION] [NOM_COMUNA] [NOM_CIUDAD]') DIRECCION,
					dbo.f_format_date(OC.FECHA_ORDEN_COMPRA, 3) FECHA_ORDEN_COMPRA,	
					S.TELEFONO,
					S.FAX,
					P.NOM_PERSONA,
					U.NOM_USUARIO,
					U.MAIL,
					IOC.NOM_PRODUCTO,
					case IOC.COD_PRODUCTO
						when 'T' then ''
						else IOC.COD_PRODUCTO
					end COD_PRODUCTO,
					case IOC.COD_PRODUCTO
						when 'T' then ''
						else IOC.ITEM
					end ITEM,
					case IOC.COD_PRODUCTO
						when 'T' then ''
						else IOC.CANTIDAD
					end CANTIDAD,
					case IOC.COD_PRODUCTO
						when 'T' then ''
						else IOC.PRECIO
					end PRECIO,
					case IOC.COD_PRODUCTO		
						when 'T' then ''
						else IOC.CANTIDAD * IOC.PRECIO
					end TOTAL_IOC,			
					M.SIMBOLO,
					dbo.f_get_parametro(".self::K_PARAM_NOM_EMPRESA.") NOM_EMPRESA_EMISOR,
					dbo.f_get_parametro(".self::K_PARAM_RUT_EMPRESA.") RUT_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_DIR_EMPRESA.") DIR_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_GIRO_EMPRESA.") GIRO_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_TEL_EMPRESA.") TEL_EMPRESA,	
					dbo.f_get_parametro(".self::K_PARAM_FAX_EMPRESA.") FAX_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_MAIL_EMPRESA.") MAIL_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_CIUDAD_EMPRESA.") CIUDAD_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_PAIS_EMPRESA.") PAIS_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_SITIO_WEB_EMPRESA.") SITIO_WEB_EMPRESA,
					dbo.f_emp_get_cc(NV.COD_EMPRESA) CC_EMPRESA
			FROM    ORDEN_COMPRA OC LEFT OUTER JOIN PERSONA P ON  OC.COD_PERSONA = P.COD_PERSONA
									LEFT OUTER JOIN NOTA_VENTA NV ON NV.COD_NOTA_VENTA = OC.COD_NOTA_VENTA,
					ITEM_ORDEN_COMPRA IOC, EMPRESA E, SUCURSAL S, USUARIO U, MONEDA M
			WHERE   OC.COD_ORDEN_COMPRA = $cod_orden_compra
			AND		E.COD_EMPRESA = OC.COD_EMPRESA 
			AND		S.COD_SUCURSAL = OC.COD_SUCURSAL 
			AND		U.COD_USUARIO = OC.COD_USUARIO_SOLICITA 
			AND		IOC.COD_ORDEN_COMPRA = OC.COD_ORDEN_COMPRA 
			AND		M.COD_MONEDA = OC.COD_MONEDA";
		$result_sql = $db->build_results($sql);
		//reporte
		$labels = array();
		$labels['strCOD_ORDEN_COMPRA'] = $cod_orden_compra;
		$labels['strFECHA_ORDEN_COMPRA'] = $result_sql[0]['FECHA_ORDEN_COMPRA'];
		$rpt = new print_reporte($sql, $this->root_dir.'appl/orden_compra/orden_compra.xml', $labels, "Orden de Compra ".$cod_orden_compra.".pdf", 1);
		$this->_load_record();
		//$this->redraw();
	}
}
?>