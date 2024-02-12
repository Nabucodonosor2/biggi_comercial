<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");

class dw_pago_docs extends datawindow{
	function dw_pago_docs(){
		$sql ="SELECT F.COD_FAPROV
					  ,CONVERT(VARCHAR, F.FECHA_REGISTRO, 103)				FECHA_REGISTRO
					  ,F.NRO_FAPROV
					  ,CONVERT(VARCHAR, FECHA_FAPROV, 103)					FECHA_FAPROV
					  ,F.TOTAL_NETO											DO_TOTAL_NETO				
					  ,F.MONTO_IVA											DO_MONTO_IVA
					  ,F.TOTAL_CON_IVA										DO_TOTAL_CON_IVA
					  ,ISNULL((SELECT SUM(MONTO_ASIGNADO) 
					  	FROM PAGO_FAPROV_FAPROV
					  	WHERE COD_FAPROV = F.COD_FAPROV),0)					PASADO
				FROM ORDEN_COMPRA O
					,FAPROV F
					,ITEM_FAPROV I 
				WHERE O.COD_ORDEN_COMPRA = {KEY1} 
				AND F.ORIGEN_FAPROV = 'ORDEN_COMPRA'
				AND F.COD_ESTADO_FAPROV = 2						--CONFIRMADA
				AND I.COD_FAPROV = F.COD_FAPROV
				AND I.COD_DOC = O.COD_ORDEN_COMPRA";
		
		parent::datawindow($sql, 'PAGO_DOCS');
		
		$this->add_control(new static_num('DO_TOTAL_NETO'));
		$this->add_control(new static_num('DO_MONTO_IVA'));
		$this->add_control(new static_num('DO_TOTAL_CON_IVA'));
		$this->add_control(new static_num('PASADO'));
	}
}


class dw_pago_docs_link extends datawindow{
	function dw_pago_docs_link(){
		$sql ="SELECT F.NRO_FAPROV
					  ,dbo.f_pago_link(O.COD_ORDEN_COMPRA, f.COD_FAPROV) LINKS
				FROM ORDEN_COMPRA O
					,FAPROV F
					,ITEM_FAPROV I 
				WHERE O.COD_ORDEN_COMPRA = {KEY1} 
				AND F.ORIGEN_FAPROV = 'ORDEN_COMPRA'
				AND F.COD_ESTADO_FAPROV = 2						--CONFIRMADA
				AND I.COD_FAPROV = F.COD_FAPROV
				AND I.COD_DOC = O.COD_ORDEN_COMPRA";
		
		parent::datawindow($sql, 'PAGO_DOCS_LINK');
		$this->add_control(new static_text('COD_FAPROV'));
	}
	
	function fill_record(&$temp, $record){	
		parent::fill_record($temp, $record);
			
		$string_link = $this->get_item($record, 'LINKS');
		$array_pagos = explode("|", $string_link);
		
		for($i=0 ; $i < count($array_pagos) ; $i++)
			$links .= "<a href=../../../../commonlib/trunk/php/link_wi.php?modulo_origen=gasto_fijo&modulo_destino=pago_faprov&cod_modulo_destino=".$array_pagos[$i]."&cod_item_menu=2530&current_tab_page=3>".$array_pagos[$i]."</a> ";
		
		$temp->setVar('PAGO_DOCS_LINK.LINK_S', $links);
		
	}
	
}

class dw_item_orden_compra_GF extends datawindow {
	const K_TIPO_PRODUCTO_GF  = 5;
	function dw_item_orden_compra_GF() {		
		$sql = "SELECT		COD_ITEM_ORDEN_COMPRA,
							COD_ORDEN_COMPRA,
							COD_PRODUCTO,
							CANTIDAD,							
							PRECIO,
							round(PRECIO * CANTIDAD, 0) TOTAL
				FROM		ITEM_ORDEN_COMPRA
				WHERE		COD_ORDEN_COMPRA = {KEY1}";
								
		parent::datawindow($sql, 'ITEM_ORDEN_COMPRA', false, false);	
		
		$this->add_control(new edit_text_upper('COD_ITEM_ORDEN_COMPRA',10, 10, 'hidden'));
		$this->add_control(new edit_cantidad('CANTIDAD',12,10));
		
		//cuando se ingresa un producto se obtiene el precio del primer proveedor
		$sql = "select COD_PRODUCTO
						,cast(COD_PRODUCTO as char(30))+'-'+NOM_PRODUCTO NOM_PRODUCTO
						,dbo.f_prod_get_precio_costo (COD_PRODUCTO, dbo.f_nv_get_first_proveedor (COD_PRODUCTO) , getdate()) PRECIO
						,NOM_PRODUCTO NOM_PRODUCTO_O
				from PRODUCTO
				where COD_TIPO_PRODUCTO = ".self::K_TIPO_PRODUCTO_GF."
				order by NOM_PRODUCTO_O asc";
		
		$this->add_control($control = new drop_down_dw('COD_PRODUCTO', $sql, 500));
		$control->set_onChange("change_producto(this);calcula_totales(this);");
		
		$this->add_control($control = new edit_precio('PRECIO'));
		$control->set_onChange("calcula_totales(this);");
					
		$this->add_control($control = new edit_num('CANTIDAD', 16, 16, 1));
		$control->set_onChange("calcula_totales(this);");
		
		$this->add_control(new static_num('TOTAL'));

		// asigna los mandatorys
		$this->set_mandatory('COD_PRODUCTO', 'Producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');
		$this->set_mandatory('PRECIO', 'Precio');
	}
	
	function update($db)	{
		$sp = 'spu_item_orden_compra';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
			
			$COD_ITEM_ORDEN_COMPRA 	= $this->get_item($i, 'COD_ITEM_ORDEN_COMPRA');
			$COD_ORDEN_COMPRA 		= $this->get_item($i, 'COD_ORDEN_COMPRA');			
			$ORDEN 					= 10; 
			$ITEM 					= 1;
			$COD_PRODUCTO 			= $this->get_item($i, 'COD_PRODUCTO');
			
			//obtiene el nombre del producto, ya que el campo NOM_PRODUCTO no es un control 
			$sql = "select NOM_PRODUCTO from PRODUCTO where COD_PRODUCTO = '$COD_PRODUCTO'";
			$result = $db->build_results($sql);		
			$NOM_PRODUCTO 			= $result[0]['NOM_PRODUCTO'];
			
			$PRECIO					= $this->get_item($i, 'PRECIO');
			$CANTIDAD 				= $this->get_item($i, 'CANTIDAD');
			$COD_TIPO_TE			= "null";
			$MOTIVO_TE		 		= "null";			
 			
			if ($PRECIO=='') $PRECIO = 0;
			$COD_ITEM_ORDEN_COMPRA = ($COD_ITEM_ORDEN_COMPRA=='') ? "null" : $COD_ITEM_ORDEN_COMPRA;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
		
			$param = "'$operacion',$COD_ITEM_ORDEN_COMPRA, $COD_ORDEN_COMPRA, $ORDEN, '$ITEM', '$COD_PRODUCTO', '$NOM_PRODUCTO', $CANTIDAD, $PRECIO, $COD_TIPO_TE, $MOTIVO_TE"; 

			if (!$db->EXECUTE_SP($sp, $param))
				return false;
			else {
				if ($statuts == K_ROW_NEW_MODIFIED) {
					$COD_ITEM_ORDEN_COMPRA = $db->GET_IDENTITY();
					$this->set_item($i, 'COD_ITEM_ORDEN_COMPRA', $COD_ITEM_ORDEN_COMPRA);		
				}
			}
		}
		
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_ITEM_ORDEN_COMPRA = $this->get_item($i, 'COD_ITEM_ORDEN_COMPRA', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_ITEM_ORDEN_COMPRA")){
			return false;				
			}			
		}
		return true;
	}
}

class dw_orden_compra extends dw_help_empresa {
	const K_ESTADO_ANULADA  = 2;
	
	function dw_orden_compra() {	
		$cod_usuario = session::get('COD_USUARIO');
		
		$sql = "SELECT	 O.COD_ORDEN_COMPRA
						,substring(convert(varchar(20), O.FECHA_ORDEN_COMPRA, 103) + ' ' + convert(varchar(20), O.FECHA_ORDEN_COMPRA, 108), 1, 16) FECHA_ORDEN_COMPRA
						,O.COD_USUARIO
						,O.COD_USUARIO COD_USUARIO_H
						,U.NOM_USUARIO										
						,O.COD_ESTADO_ORDEN_COMPRA			
						,O.COD_CUENTA_CORRIENTE
						,O.COD_CUENTA_CORRIENTE AS NRO_CUENTA_CORRIENTE
						,O.REFERENCIA
						,O.COD_EMPRESA
						,E.ALIAS
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,E.GIRO													
						,COD_SUCURSAL AS COD_SUCURSAL_FACTURA	 				
						,dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA						
						,O.COD_PERSONA
						,O.SUBTOTAL	SUM_TOTAL					
						,O.PORC_DSCTO1
						,O.MONTO_DSCTO1  
						,O.PORC_DSCTO2
						,O.MONTO_DSCTO2  
						,O.TOTAL_NETO
						,O.PORC_IVA
						,O.MONTO_IVA
						,O.TOTAL_CON_IVA
						,O.OBS
						,substring(convert(varchar(20), O.FECHA_ANULA, 103) + ' ' + convert(varchar(20), O.FECHA_ANULA, 108), 1, 16) FECHA_ANULA						
						,O.MOTIVO_ANULA
						,O.COD_USUARIO_ANULA	
						,O.INGRESO_USUARIO_DSCTO1  
						,O.INGRESO_USUARIO_DSCTO2	
						,case O.COD_ESTADO_ORDEN_COMPRA
							when ".self::K_ESTADO_ANULADA." then '' 
							else 'none'
						end TR_DISPLAY
						,case O.COD_ESTADO_ORDEN_COMPRA
							when ".self::K_ESTADO_ANULADA." then 'ANULADA'
							else ''
						end TITULO_ESTADO_NOTA_VENTA
						,dbo.f_emp_get_mail_cargo_persona(O.COD_PERSONA, '[EMAIL]') MAIL_CARGO_PERSONA
						,ESTADO_OC_PLANO
						,NRO_COTIZACION
						,NRO_NOTA_VENTA
						,OBSERVACIONES
						,(SELECT NOM_USUARIO FROM USUARIO WHERE COD_USUARIO = C.COD_USUARIO_VENDEDOR1) NOM_VENDEDOR_C
						,C.REFERENCIA REF_COTIZACION
						,(SELECT NOM_USUARIO FROM USUARIO WHERE COD_USUARIO = NV.COD_USUARIO_VENDEDOR1) NOM_VENDEDOR_NV
						,NV.REFERENCIA REF_NOTA_VENTA
				FROM 	ORDEN_COMPRA O LEFT OUTER JOIN COTIZACION C ON O.NRO_COTIZACION = C.COD_COTIZACION
									   LEFT OUTER JOIN NOTA_VENTA NV ON O.NRO_NOTA_VENTA = NV.COD_NOTA_VENTA
						,USUARIO U, EMPRESA E, ESTADO_ORDEN_COMPRA EOC
				WHERE	O.COD_ORDEN_COMPRA = {KEY1} and
						U.COD_USUARIO = O.COD_USUARIO AND
						E.COD_EMPRESA = O.COD_EMPRESA AND
						EOC.COD_ESTADO_ORDEN_COMPRA = O.COD_ESTADO_ORDEN_COMPRA";		

		parent::dw_help_empresa($sql, '', false, false, 'P');	// El último parametro indica que solo acepta proveedores
		
		$this->add_control(new edit_nro_doc('COD_ORDEN_COMPRA','ORDEN_COMPRA'));
											
		$sql_estado_oc 			= "	select 			COD_ESTADO_ORDEN_COMPRA
													,NOM_ESTADO_ORDEN_COMPRA
										from		ESTADO_ORDEN_COMPRA
						  			order by  		COD_ESTADO_ORDEN_COMPRA";
		$this->add_control(new drop_down_dw('COD_ESTADO_ORDEN_COMPRA',$sql_estado_oc,150));
		
		$this->add_control(new edit_text_upper('REFERENCIA',120,150));			
		
		$sql_cta_cte 			= " select 			COD_CUENTA_CORRIENTE
													,NOM_CUENTA_CORRIENTE
													,ORDEN
									from			CUENTA_CORRIENTE
									order by		ORDEN";											
		$this->add_control(new drop_down_dw('COD_CUENTA_CORRIENTE',$sql_cta_cte,145));
		
		$sql_cta_cte_nom		= " select 			COD_CUENTA_CORRIENTE
													,NRO_CUENTA_CORRIENTE
													,ORDEN
									from			CUENTA_CORRIENTE
									order by		ORDEN";											
		$this->add_control(new drop_down_dw('NRO_CUENTA_CORRIENTE',$sql_cta_cte_nom,150));
		
		
		// usuario anulación
		$sql = "select 	COD_USUARIO
						,NOM_USUARIO
				from USUARIO";
								
		$this->add_control(new drop_down_dw('COD_USUARIO_ANULA',$sql,150));	
		$this->set_entrable('COD_USUARIO_ANULA', false);

		$this->add_control(new static_num('SUM_TOTAL'));
		
		$this->add_control($control = new edit_num('PORC_DSCTO1', 4,4,1));
		$control->set_onChange("calcula_totales(this);");
		
		$this->add_control($control = new edit_num('PORC_DSCTO2', 4,4,1));
		$control->set_onChange("calcula_totales(this);");
		
		$this->add_control($control = new edit_num('MONTO_DSCTO1'));
		$control->set_onChange("calcula_totales(this);");
		
		$this->add_control($control = new edit_num('MONTO_DSCTO2'));
		$control->set_onChange("calcula_totales(this);");
		
		$this->add_control(new edit_text('INGRESO_USUARIO_DSCTO1',10, 10, 'hidden'));
		$this->add_control(new edit_text('INGRESO_USUARIO_DSCTO2',10, 10, 'hidden'));
		
		$this->add_control(new static_num('TOTAL_NETO'));
		$this->add_control(new static_num('MONTO_IVA'));
		$this->add_control(new static_num('TOTAL_CON_IVA'));
		$this->add_control($control = new drop_down_iva());
		$control->set_onChange("calcula_totales(this);");
		
		$this->add_control(new edit_num('VALIDEZ_OFERTA',2,2));
		$this->add_control(new edit_text_upper('GARANTIA',109,140));
		$this->add_control(new edit_text_multiline('OBS',54,4));

		/////////////////////////////////////////////////


		$sql = "SELECT 'I' ESTADO_OC_PLANO, 'INGRESADO' NOM_ESTADO_OC_PLANO, 1 ORDEN 
				UNION 
				SELECT 'E' ESTADO_OC_PLANO , 'EN PROCESO' NOM_ESTADO_OC_PLANO, 2 ORDEN
				UNION 
				SELECT 'T' ESTADO_OC_PLANO , 'TERMINADO' NOM_ESTADO_OC_PLANO, 3 ORDEN
				ORDER BY ORDEN";

		$this->add_control(new drop_down_dw('ESTADO_OC_PLANO',$sql,150));

		$this->add_control($control = new edit_num('NRO_COTIZACION', 16, 16, 0, true, false, false));
		$control->set_onChange("ajax_cotizacion(this);");

		$this->add_control($control = new edit_num('NRO_NOTA_VENTA', 16, 16, 0, true, false, false));
		$control->set_onChange("ajax_nota_venta(this);");

		$this->add_control(new edit_text_multiline('OBSERVACIONES',54,4));
		$this->add_control(new static_text('NOM_VENDEDOR_C'));
		$this->add_control(new static_text('REF_COTIZACION'));
		$this->add_control(new static_text('NOM_VENDEDOR_NV'));
		$this->add_control(new static_text('REF_NOTA_VENTA'));
		$this->add_control(new edit_text_hidden('COD_USUARIO_H'));

		///////////////////////////////////////////////////

		// asigna los mandatorys/
		$this->set_mandatory('COD_ESTADO_ORDEN_COMPRA', 'un Estado');
		//$this->set_mandatory('COD_CUENTA_CORRIENTE', 'Cuenta Corriente');
		//$this->set_mandatory('NRO_CUENTA_CORRIENTE', 'Nro. Cuenta Corriente');		
		$this->set_mandatory('REFERENCIA', 'Referencia');
		$this->set_mandatory('VALIDEZ_OFERTA', 'Validez Oferta');
		$this->set_mandatory('GARANTIA', 'Garantía');
	}
}

class drop_down_iva_oc_negativo extends drop_down_iva  {
	const K_PARAM_IVA = 1;
	const K_PARAM_BH = 2;
	
	function drop_down_iva_oc_negativo() {
		// No se llama al ancestro directo drop_down_iva
		$porc_iva = $this->get_parametro(self::K_PARAM_IVA);
		$porc_iva = number_format($porc_iva, 1, ',', '.');

		$porc_bh = $this->get_parametro(self::K_PARAM_BH);
		$porc_bh = number_format( - $porc_bh, 2, ',', '.');
		
		$porc_bh2 = 16;
		$porc_bh2 = number_format( - $porc_bh2, 2, ',', '.');
   
		parent::drop_down_list('PORC_IVA',array($porc_iva, $porc_bh2, $porc_bh, 0),array($porc_iva, $porc_bh2, $porc_bh, '0'),52);
	}
}

class drop_down_iva_oc extends drop_down_iva  {
	const K_PARAM_IVA = 1;
	const K_PARAM_BH = 2;
	
	function drop_down_iva_oc() {
		// No se llama al ancestro directo drop_down_iva
		$porc_iva = $this->get_parametro(self::K_PARAM_IVA);
		$porc_iva = number_format($porc_iva, 1, ',', '.');

		parent::drop_down_list('PORC_IVA',array($porc_iva,0),array($porc_iva,'0'),52);
	}
}

class wi_gasto_fijo_base extends w_input {
	const K_ESTADO_EMITIDA 	= 1;
	const K_ESTADO_ANULADA  = 2;
	const K_ESTADO_CERRADA	= 3;
	const K_ESTADO_AUTORIZADA = 4;
	const K_MONEDA			= 1;
	const K_PARAM_PORC_IVA  = 1;
	
	const K_PARAM_NOM_EMPRESA        =6;
	const K_PARAM_RUT_EMPRESA        =20;
	const K_PARAM_DIR_EMPRESA        =10;
	const K_PARAM_TEL_EMPRESA        =11;
	const K_PARAM_FAX_EMPRESA        =12;
	const K_PARAM_MAIL_EMPRESA       =13;
	const K_PARAM_CIUDAD_EMPRESA     =14;
	const K_PARAM_PAIS_EMPRESA       =15;
	const K_PARAM_SITIO_WEB_EMPRESA  =25;
	const K_AUTORIZA_PORC_NEGATIVO	 = '994005';	
	const K_AUTORIZA_IMPRESION		= '994010';
	
	function wi_gasto_fijo_base($cod_item_menu) {
		parent::w_input('gasto_fijo', $cod_item_menu);
		
		$this->dws['dw_orden_compra'] = new dw_orden_compra();
		$this->dws['dw_item_orden_compra'] = new dw_item_orden_compra_GF();
		
		$this->dws['dw_pago_docs'] = new dw_pago_docs();
		$this->dws['dw_pago_docs_link'] = new dw_pago_docs_link();
		
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_PORC_NEGATIVO, $this->cod_usuario);
		
		if ($priv=='E') {
			$this->dws['dw_orden_compra']->add_control(new drop_down_iva_oc_negativo());
      	}
      	else {
			$this->dws['dw_orden_compra']->add_control(new drop_down_iva_oc());
      	}
		$this->dws['dw_orden_compra']->set_computed('MONTO_IVA', '[TOTAL_NETO] * [PORC_IVA] / 100');
		$this->dws['dw_orden_compra']->set_computed('TOTAL_CON_IVA', '[TOTAL_NETO] + [MONTO_IVA]');		
		
		$this->add_auditoria_relacionada('ITEM_ORDEN_COMPRA', 'PRECIO');
		$this->add_auditoria('COD_EMPRESA');
		$this->add_auditoria('COD_ESTADO_ORDEN_COMPRA');
		$this->add_auditoria('COD_SUCURSAL');
		$this->add_auditoria('COD_PERSONA');
		$this->add_auditoria('COD_CUENTA_CORRIENTE');
	
		//al momento de crear un nuevo registro se iniciara en rut de empresa proveedor
		$this->set_first_focus('RUT');
	}
	
	function new_record() {
		$this->dws['dw_orden_compra']->insert_row();
		$this->dws['dw_orden_compra']->set_item(0, 'TR_DISPLAY', 'none');
		$this->dws['dw_orden_compra']->set_item(0, 'FECHA_ORDEN_COMPRA', substr($this->current_date_time(), 0, 16));
		$this->dws['dw_orden_compra']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_orden_compra']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$autoriza_gf = '994010';
		$sql = "select dbo.f_get_autoriza_menu($this->cod_usuario, $autoriza_gf) AUTORIZA";
		$result = $db->build_results($sql);
		$autorizar = $result[0]['AUTORIZA'];
		if($autorizar == 'E')
		$this->dws['dw_orden_compra']->set_item(0, 'COD_ESTADO_ORDEN_COMPRA', self::K_ESTADO_AUTORIZADA);
		else
		$this->dws['dw_orden_compra']->set_item(0, 'COD_ESTADO_ORDEN_COMPRA', self::K_ESTADO_EMITIDA);
		$this->dws['dw_orden_compra']->set_entrable('COD_ESTADO_ORDEN_COMPRA', false);
		$this->dws['dw_item_orden_compra']->insert_row();
		$this->dws['dw_orden_compra']->set_item(0, 'ESTADO_OC_PLANO', 'I');
	}
	
	function load_record() {
		$cod_orden_compra = $this->get_item_wo($this->current_record, 'COD_ORDEN_COMPRA');
		$this->dws['dw_orden_compra']->retrieve($cod_orden_compra);	
		$this->dws['dw_pago_docs']->retrieve($cod_orden_compra);
		$this->dws['dw_pago_docs_link']->retrieve($cod_orden_compra);	
		$cod_empresa = $this->dws['dw_orden_compra']->get_item(0, 'COD_EMPRESA');
		$this->dws['dw_orden_compra']->controls['COD_SUCURSAL_FACTURA']->retrieve($cod_empresa);
		$this->dws['dw_orden_compra']->controls['COD_PERSONA']->retrieve($cod_empresa);
		$COD_ESTADO_ORDEN_COMPRA = $this->dws['dw_orden_compra']->get_item(0, 'COD_ESTADO_ORDEN_COMPRA');
		
		$this->b_print_visible 	 = true;
		$this->b_no_save_visible = true;
		$this->b_save_visible 	 = true;
		$this->b_modify_visible	 = true;
		$this->b_delete_visible  = true;
		
		 if ($COD_ESTADO_ORDEN_COMPRA == self::K_ESTADO_EMITIDA) {
			$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_IMPRESION, $this->cod_usuario);
	
			$sql = "select 	COD_ESTADO_ORDEN_COMPRA
							,NOM_ESTADO_ORDEN_COMPRA
							,ORDEN
					from ESTADO_ORDEN_COMPRA
					where COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_EMITIDA." or
							COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_ANULADA;
			if ($priv =='E')
				$sql .= " or COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_AUTORIZADA;
				
			$sql .= "order by COD_ESTADO_ORDEN_COMPRA";
					
			unset($this->dws['dw_orden_compra']->controls['COD_ESTADO_ORDEN_COMPRA']);
			$this->dws['dw_orden_compra']->add_control($control = new drop_down_dw('COD_ESTADO_ORDEN_COMPRA',$sql,150));	
			$control->set_onChange("mostrarOcultar_Anula(this);");
			$this->dws['dw_orden_compra']->add_control(new edit_text_upper('MOTIVO_ANULA',110, 100));
			
			$this->b_delete_visible  = false;
			$this->b_print_visible 	 = false;
		}
		else if ($COD_ESTADO_ORDEN_COMPRA== self::K_ESTADO_ANULADA) {
			$this->b_print_visible 	 = false;
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			$this->b_delete_visible  = false;	
			
		}
		else if ($COD_ESTADO_ORDEN_COMPRA== self::K_ESTADO_CERRADA) {
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			$this->b_delete_visible  = false;	
		}
		else if ($COD_ESTADO_ORDEN_COMPRA == self::K_ESTADO_AUTORIZADA) {	// solo para GF
	
			$sql = "select 	COD_ESTADO_ORDEN_COMPRA
							,NOM_ESTADO_ORDEN_COMPRA
							,ORDEN
					from ESTADO_ORDEN_COMPRA
					where COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_AUTORIZADA." or
							COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_ANULADA."
					order by COD_ESTADO_ORDEN_COMPRA";
					
			unset($this->dws['dw_orden_compra']->controls['COD_ESTADO_ORDEN_COMPRA']);
			$this->dws['dw_orden_compra']->add_control($control = new drop_down_dw('COD_ESTADO_ORDEN_COMPRA',$sql,150));	
			$control->set_onChange("mostrarOcultar_Anula(this);");
			$this->dws['dw_orden_compra']->add_control(new edit_text_upper('MOTIVO_ANULA',110, 100));
			
			$this->b_delete_visible  = false;
			$this->b_print_visible 	 = true;
		
		}
		
		$this->dws['dw_item_orden_compra']->retrieve($cod_orden_compra);
	}
	
	function get_key() {
		return $this->dws['dw_orden_compra']->get_item(0, 'COD_ORDEN_COMPRA');
	}
	
	function make_sql_auditoria() {
		$nom_tabla_original = $this->nom_tabla;
		$this->nom_tabla = 'orden_compra';
		$sql = parent::make_sql_auditoria();
		$this->nom_tabla = $nom_tabla_original;
		return $sql;
	}
	function make_sql_auditoria_relacionada($tabla) {
		$nom_tabla_original = $this->nom_tabla;
		$this->nom_tabla = 'orden_compra';
		$sql = parent::make_sql_auditoria_relacionada($tabla);
		$this->nom_tabla = $nom_tabla_original;
		return $sql;
	}
	function save_record($db) {	
		$cod_orden_compra 	= $this->get_key();		
		$cod_usuario 		= $this->dws['dw_orden_compra']->get_item(0, 'COD_USUARIO');
		$cod_usuario_sol 	= $this->dws['dw_orden_compra']->get_item(0, 'COD_USUARIO'); //el solicitante será el mismo que el emisor		
		$cod_moneda			= self::K_MONEDA;	//la moneda será $						
		$cod_est_oc			= $this->dws['dw_orden_compra']->get_item(0, 'COD_ESTADO_ORDEN_COMPRA');
		$cod_nota_venta		= "null";
		
		$cod_cta_cte		= $this->dws['dw_orden_compra']->get_item(0, 'COD_CUENTA_CORRIENTE');	 	
		$referencia			= $this->dws['dw_orden_compra']->get_item(0, 'REFERENCIA');
		$referencia 		= str_replace("'", "''", $referencia);
		$cod_empresa		= $this->dws['dw_orden_compra']->get_item(0, 'COD_EMPRESA');
		$cod_suc_factura	= $this->dws['dw_orden_compra']->get_item(0, 'COD_SUCURSAL_FACTURA');
		$cod_persona		= $this->dws['dw_orden_compra']->get_item(0, 'COD_PERSONA');
		$cod_persona		= ($cod_persona =='') ? "null" : "$cod_persona";			
		$sub_total			= $this->dws['dw_orden_compra']->get_item(0, 'SUM_TOTAL');
		$sub_total      	= ($sub_total =='') ? 0 : "$sub_total";
		
		$porc_descto1		= $this->dws['dw_orden_compra']->get_item(0, 'PORC_DSCTO1');
		$porc_descto1		= ($porc_descto1 =='') ? "null" : "$porc_descto1";
				
		$monto_dscto1		= $this->dws['dw_orden_compra']->get_item(0, 'MONTO_DSCTO1');
		$monto_dscto1		= ($monto_dscto1 =='') ? 0 : "$monto_dscto1";
		
		$porc_descto2		= $this->dws['dw_orden_compra']->get_item(0, 'PORC_DSCTO2');
		$porc_descto2		= ($porc_descto2 =='') ? "null" : "$porc_descto2";
				
		$monto_dscto2		= $this->dws['dw_orden_compra']->get_item(0, 'MONTO_DSCTO2');
		$monto_dscto2		= ($monto_dscto2 =='') ? 0 : "$monto_dscto2";
		
		$total_neto			= $this->dws['dw_orden_compra']->get_item(0, 'TOTAL_NETO');
		$total_neto			= ($total_neto =='') ? 0 : "$total_neto";
		
		$porc_iva			= $this->dws['dw_orden_compra']->get_item(0, 'PORC_IVA');		
		
		$monto_iva			= $this->dws['dw_orden_compra']->get_item(0, 'MONTO_IVA');
		$monto_iva			= ($monto_iva =='') ? 0 : "$monto_iva";
		
		$total_con_iva		= $this->dws['dw_orden_compra']->get_item(0, 'TOTAL_CON_IVA');
		$total_con_iva		= ($total_con_iva =='') ? 0 : "$total_con_iva";
				
		$obs				= $this->dws['dw_orden_compra']->get_item(0, 'OBS');
		$obs		 		= str_replace("'", "''", $obs);
		$obs				= ($obs =='') ? "null" : "'$obs'";
							// NOTA: para el manejo de fecha se debe pasar un string dd/mm/yyyy y en el sp llamar a to_date ber eje en spi_orden_trabajo
		$motivo_anula		= $this->dws['dw_orden_compra']->get_item(0, 'MOTIVO_ANULA');
		$motivo_anula		= str_replace("'", "''", $motivo_anula);
		$motivo_anula		= ($motivo_anula =='') ? "null" : "'$motivo_anula'";
		
		$cod_user_anula		= $this->dws['dw_orden_compra']->get_item(0, 'COD_USUARIO_ANULA');
		
		if (($motivo_anula!= '') && ($cod_user_anula == '')) // se anula 
			$cod_user_anula			= $this->cod_usuario;
		else
			$cod_usuario_anula			= "null";
		
		$ingreso_usuario_dscto1 = $this->dws['dw_orden_compra']->get_item(0, 'INGRESO_USUARIO_DSCTO1');;
		$ingreso_usuario_dscto1 = ($ingreso_usuario_dscto1 =='') ? "null" : "'$ingreso_usuario_dscto1'";
			
		$ingreso_usuario_dscto2 = $this->dws['dw_orden_compra']->get_item(0, 'INGRESO_USUARIO_DSCTO2');;
		$ingreso_usuario_dscto2 = ($ingreso_usuario_dscto2 =='') ? "null" : "'$ingreso_usuario_dscto2'";

		$cod_orden_compra = ($cod_orden_compra=='') ? "null" : $cod_orden_compra;

		$estado_oc_plano	= $this->dws['dw_orden_compra']->get_item(0, 'ESTADO_OC_PLANO');
		$nro_cotizacion		= $this->dws['dw_orden_compra']->get_item(0, 'NRO_COTIZACION');
		$nro_nota_venta		= $this->dws['dw_orden_compra']->get_item(0, 'NRO_NOTA_VENTA');
		$observaciones		= $this->dws['dw_orden_compra']->get_item(0, 'OBSERVACIONES');

		$estado_oc_plano	= ($estado_oc_plano =='') ? "null" : "'$estado_oc_plano'";
		$nro_cotizacion		= ($nro_cotizacion =='') ? "null" : "$nro_cotizacion";
		$nro_nota_venta		= ($nro_nota_venta =='') ? "null" : "$nro_nota_venta";

		$observaciones		= str_replace("'", "''", $observaciones);
		$observaciones		= ($observaciones =='') ? "null" : "'$observaciones'";
    
		$sp = 'spu_orden_compra';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
			$param	= "'$operacion'
						,$cod_orden_compra				
						,$cod_usuario 		
						,$cod_usuario_sol 									
						,$cod_moneda		
						,$cod_est_oc
						,$cod_nota_venta			
						,$cod_cta_cte
						,'$referencia'																						
						,$cod_empresa		
						,$cod_suc_factura	
						,$cod_persona			
						,$sub_total		
						,$porc_descto1		
						,$monto_dscto1		
						,$porc_descto2		
						,$monto_dscto2		
						,$total_neto		
						,$porc_iva		
						,$monto_iva		
						,$total_con_iva				
						,$obs
						,$motivo_anula
						,$cod_user_anula
						,$ingreso_usuario_dscto1
						,$ingreso_usuario_dscto2
						,'GASTO_FIJO'
						, null
						, 'S'
						, 'S'
						,NULL
						,NULL
						,NULL
						,NULL
						,NULL
						,NULL
						,NULL
						,'N'
						,$estado_oc_plano
						,$nro_cotizacion
						,$nro_nota_venta
						,$observaciones";

		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_orden_compra = $db->GET_IDENTITY();
				$this->dws['dw_orden_compra']->set_item(0, 'COD_ORDEN_COMPRA', $cod_orden_compra);
			}
		
			 for ($i=0; $i<$this->dws['dw_item_orden_compra']->row_count(); $i++)
				$this->dws['dw_item_orden_compra']->set_item($i, 'COD_ORDEN_COMPRA', $this->dws['dw_orden_compra']->get_item(0, 'COD_ORDEN_COMPRA'), 'primary', false);				
			 if (!$this->dws['dw_item_orden_compra']->update($db))			
			 	return false;			
			 
			$parametros_sp = "'RECALCULA',$cod_orden_compra";	
			if (!$db->EXECUTE_SP('spu_orden_compra', $parametros_sp))
				return false;
			
			return true;
		}	
		return false;				
	}
	
	function print_record() {
	$cod_orden_compra = $this->get_key();
	$sql= "SELECT OC.COD_ORDEN_COMPRA,
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
				dbo.f_get_parametro(".self::K_PARAM_TEL_EMPRESA.") TEL_EMPRESA,	
				dbo.f_get_parametro(".self::K_PARAM_FAX_EMPRESA.") FAX_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_MAIL_EMPRESA.") MAIL_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_CIUDAD_EMPRESA.") CIUDAD_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_PAIS_EMPRESA.") PAIS_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_SITIO_WEB_EMPRESA.") SITIO_WEB_EMPRESA
		FROM    ORDEN_COMPRA OC LEFT OUTER JOIN PERSONA P ON  OC.COD_PERSONA = P.COD_PERSONA,
				ITEM_ORDEN_COMPRA IOC, EMPRESA E, SUCURSAL S, USUARIO U, MONEDA M
		WHERE   OC.COD_ORDEN_COMPRA = $cod_orden_compra AND
				E.COD_EMPRESA = OC.COD_EMPRESA AND
				S.COD_SUCURSAL = OC.COD_SUCURSAL AND
				U.COD_USUARIO = OC.COD_USUARIO and
				IOC.COD_ORDEN_COMPRA = OC.COD_ORDEN_COMPRA AND
				M.COD_MONEDA = OC.COD_MONEDA";
	// reporte
	$labels = array();
	$labels['strCOD_ORDEN_COMPRA'] = $cod_orden_compra;
	$rpt = new reporte($sql, $this->root_dir.'appl/gasto_fijo/gasto_fijo.xml', $labels, "Orden de Compra ".$cod_orden_compra.".pdf", 1);
	$this->redraw();
	}
}
/////////////////////////////////////////////////////////////
// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wi_gasto_fijo.php";
if (file_exists($file_name)) 
	require_once($file_name);
else {
	class wi_gasto_fijo extends wi_gasto_fijo_base {
		function wi_gasto_fijo($cod_item_menu) {
			parent::wi_gasto_fijo_base($cod_item_menu); 
		}
	}
}
?>