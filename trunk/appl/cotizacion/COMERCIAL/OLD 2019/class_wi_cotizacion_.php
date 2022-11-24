<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../common_appl/class_w_cot_nv.php");
require_once(dirname(__FILE__)."/../../empresa/class_dw_help_empresa.php");

class edit_protected extends edit_control {
	var $edit_text;
	var $static_text;
	
	function edit_protected($field, $edit_text, $static_text) {
		parent::edit_control($field);
		$this->edit_text = $edit_text;
		$this->edit_text->forzar_js = true;
		$this->edit_text->set_onChange("change_protected(this);");
		$this->static_text = $static_text;
	}
	function draw_entrable($dato, $record) {
		// input text visible
		$this->edit_text->type = 'text';
		$html = $this->edit_text->draw_entrable($dato, $record);
		
		// static text hidden
		$this->static_text->type = 'hidden';
		$html .= $this->static_text->draw_entrable($dato, $record);
		return $html; 
	}
	function draw_no_entrable($dato, $record) {
		// input text visible
		$this->edit_text->type = 'hidden';
		$html = $this->edit_text->draw_entrable($dato, $record);		// Es correcto que diga draw_entrable, porque se dese aque cree el input text 
		
		// static text hidden
		$this->static_text->type = '';
		$html .= $this->static_text->draw_no_entrable($dato, $record);
		return $html; 
	}
	function get_values_from_POST($record) {
		return $this->edit_text->get_values_from_POST($record);
	}
}

class dw_item_resumen_cotizacion extends datawindow{
	function dw_item_resumen_cotizacion(){
		$sql = "SELECT		COD_PRODUCTO R_COD_PRODUCTO,
							NOM_PRODUCTO R_NOM_PRODUCTO,
							SUM(CANTIDAD) R_CANTIDAD,
							PRECIO R_PRECIO,
							SUM(CANTIDAD * PRECIO) R_TOTAL
				FROM		ITEM_COTIZACION
				WHERE		COD_COTIZACION = {KEY1}
				AND			COD_PRODUCTO not in ('T', 'TE', 'F', 'I', 'E')
				GROUP BY COD_PRODUCTO, NOM_PRODUCTO, PRECIO
				UNION
				SELECT		COD_PRODUCTO R_COD_PRODUCTO,
							NOM_PRODUCTO R_NOM_PRODUCTO,
							SUM(CANTIDAD) R_CANTIDAD,
							PRECIO R_PRECIO,
							SUM(CANTIDAD * PRECIO) R_TOTAL
				FROM		ITEM_COTIZACION
				WHERE		COD_COTIZACION = {KEY1}
				AND			COD_PRODUCTO in ('TE', 'F', 'I', 'E')
				GROUP BY COD_PRODUCTO, NOM_PRODUCTO, PRECIO";


		parent::datawindow($sql, 'ITEM_RESUMEN_COTIZACION');

		$this->add_control(new static_text('R_COD_PRODUCTO'));
		$this->add_control(new static_text('R_NOM_PRODUCTO'));
		$this->add_control(new static_num('R_CANTIDAD',1));
		$this->add_control(new static_num('R_PRECIO'));
		$this->add_control(new static_num('R_TOTAL'));
	}
}

/*Begin- Seguimiento Cotizacion*/
class dw_seguimiento_cotizacion extends datawindow {
	function dw_seguimiento_cotizacion() {
		$sql = "select BC.COD_BITACORA_COTIZACION  										SC_COD_BITACORA_COTIZACION
						,convert(varchar, BC.FECHA_BITACORA, 103) 						SC_FECHA_BITACORA
						,substring(convert(varchar, BC.FECHA_BITACORA, 108),1 , 5) 		SC_HORA_BITACORA
						,convert(varchar, BC.FECHA_COMPROMISO, 103)						SC_FECHA_COMPROMISO
						,substring(convert(varchar, BC.FECHA_COMPROMISO, 108),1 , 5)	SC_HORA_COMPROMISO
						,convert(varchar, BC.FECHA_REALIZADO, 103)						SC_FECHA_REALIZADO
						,substring(convert(varchar, BC.FECHA_REALIZADO, 108),1 , 5)		SC_HORA_REALIZADO
						,BC.COD_USUARIO  					SC_COD_USUARIO
						,U1.INI_USUARIO  					SC_INI_USUARIO
						,BC.COD_COTIZACION 					SC_COD_COTIZACION	
						,BC.COD_ACCION_COTIZACION			SC_COD_ACCION_COTIZACION
						,BC.CONTACTO						SC_CONTACTO
						,BC.TELEFONO						SC_TELEFONO
						,BC.MAIL							SC_MAIL
						,BC.TELEFONO						SC_TELEFONO_H
						,BC.MAIL							SC_MAIL_H
						,BC.GLOSA							SC_GLOSA
						,BC.TIENE_COMPROMISO				SC_TIENE_COMPROMISO		
						,BC.GLOSA_COMPROMISO				SC_GLOSA_COMPROMISO
						,BC.COMPROMISO_REALIZADO			SC_COMPROMISO_REALIZADO
						,BC.COD_USUARIO_REALIZADO			SC_COD_USUARIO_REALIZADO
						,U2.INI_USUARIO						SC_INI_USUARIO_REALIZADO
						,BC.COD_PERSONA						SC_COD_PERSONA
						,'N' IS_NEW
						,'S' DISABLED
				from BITACORA_COTIZACION BC left outer join USUARIO U2 on U2.COD_USUARIO = BC.COD_USUARIO_REALIZADO
					,USUARIO U1 
				where BC.COD_COTIZACION = {KEY1}
				 and U1.COD_USUARIO = BC.COD_USUARIO";
		parent::datawindow($sql, 'SEGUIMIENTO_COTIZACION', true, false);
		
		// controls
		$this->add_control(new static_text('SC_FECHA_BITACORA'));
		$this->add_control(new static_text('SC_HORA_BITACORA'));
		$this->add_control(new static_text('SC_INI_USUARIO'));
		
		$this->add_control(new static_text('SC_TELEFONO'));
		$this->add_control(new static_text('SC_MAIL'));
		$this->add_control(new edit_text('IS_NEW',3, 3, 'hidden'));
		$this->add_control(new edit_text('DISABLED',3, 3, 'hidden'));
		$this->add_control(new edit_text('SC_CONTACTO', 20, 100, 'hidden'));
		$this->add_control(new edit_text('SC_TELEFONO_H', 20, 100, 'hidden'));
		$this->add_control(new edit_text('SC_MAIL_H', 20, 100, 'hidden'));
		$this->add_control(new edit_text_multiline('SC_GLOSA', 30, 3));
		$this->add_control($control = new edit_check_box('SC_TIENE_COMPROMISO', 'S', 'N'));
		$control->set_onClick("tiene_compromiso(this);");
		$this->add_control(new edit_protected('SC_FECHA_COMPROMISO', new edit_date('FECHA_COMPROMISO_E'), new static_text('FECHA_COMPROMISO_S')));
		$this->add_control(new edit_protected('SC_HORA_COMPROMISO', new edit_time('HORA_COMPROMISO_E'), new static_text('HORA_COMPROMISO_S')));
		$this->add_control(new edit_protected('SC_GLOSA_COMPROMISO', new edit_text_upper('GLOSA_COMPROMISO_E', 51, 100), new static_text('GLOSA_COMPROMISO_S')));
		$this->add_control($control = new edit_check_box('SC_COMPROMISO_REALIZADO', 'S', 'N'));
		$control->set_onClick("compromiso_realizado(this);");
		$this->add_control(new static_text('SC_FECHA_REALIZADO'));

		$sql = "SELECT P.COD_PERSONA
					  ,P.NOM_PERSONA
				FROM PERSONA P
					,SUCURSAL S
					,EMPRESA E
				WHERE E.COD_EMPRESA = {KEY1}
				AND P.COD_SUCURSAL = S.COD_SUCURSAL
				AND S.COD_EMPRESA = E.COD_EMPRESA
				ORDER BY NOM_PERSONA ASC";
		$this->add_control($control = new drop_down_dw('SC_COD_PERSONA', $sql, 103));
		$control->set_onChange("set_mail_telefono(this);");
		// mandatory
		$this->set_mandatory('SC_GLOSA', 'Glosa');
		$this->set_mandatory('SC_COD_ACCION_COTIZACION', 'Acción');
		$this->set_mandatory('SC_COD_PERSONA', 'Contacto');
		
		// first focus
		$this->set_first_focus('SC_COD_ACCION_COTIZACION');

		// protected
		$this->set_protect('SC_FECHA_COMPROMISO', "[SC_TIENE_COMPROMISO]=='N'");
		$this->set_protect('SC_GLOSA_COMPROMISO', "[SC_TIENE_COMPROMISO]=='N'");
		$this->set_protect('SC_COMPROMISO_REALIZADO', "[SC_TIENE_COMPROMISO]=='N'");
		
		$this->set_protect('SC_COD_ACCION_COTIZACION', "[IS_NEW]=='N'");
		$this->set_protect('SC_COD_PERSONA', "[IS_NEW]=='N'");
		$this->set_protect('SC_GLOSA', "[IS_NEW]=='N'");
		$this->set_protect('FECHA_COMPROMISO_E', "[IS_NEW]=='N'");
		$this->set_protect('SC_FECHA_COMPROMISO', "[IS_NEW]=='N'");
		$this->set_protect('SC_HORA_COMPROMISO', "[IS_NEW]=='N'");
		$this->set_protect('SC_COMPROMISO_REALIZADO', "[DISABLED]=='S'");
		$this->set_protect('SC_GLOSA_COMPROMISO', "[IS_NEW]=='N'");
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'SC_TIENE_COMPROMISO', 'S');
		$this->set_item($row, 'SC_FECHA_BITACORA', $this->current_date());
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select INI_USUARIO
				from USUARIO
				where COD_USUARIO = ".$this->cod_usuario;
		$result = $db->build_results($sql);
		$this->set_item($row, 'SC_INI_USUARIO', $result[0]['INI_USUARIO']);
		$this->set_item($row, 'IS_NEW', 'S');
		$this->set_item($row, 'DISABLED', 'S');
		return $row;
	}
	function update($db)	{
		$sp = 'spu_bitacora_cotizacion';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
	
			$cod_bitacora_factura = $this->get_item($i, 'SC_COD_BITACORA_COTIZACION'); 
			$cod_factura = $this->get_item($i, 'SC_COD_COTIZACION');
			$cod_accion_cobranza = $this->get_item($i, 'SC_COD_ACCION_COTIZACION');
			$contacto = $this->get_item($i, 'SC_CONTACTO');
			$telefono = $this->get_item($i, 'SC_TELEFONO_H');
			$mail = $this->get_item($i, 'SC_MAIL_H');
			$glosa = $this->get_item($i, 'SC_GLOSA');
			$tiene_compromiso = $this->get_item($i, 'SC_TIENE_COMPROMISO');
			$fecha_compromiso = $this->get_item($i, 'SC_FECHA_COMPROMISO');
			$hora_compromiso = $this->get_item($i, 'SC_HORA_COMPROMISO');
			$glosa_compromiso = $this->get_item($i, 'SC_GLOSA_COMPROMISO');
			$compromiso_realizado = $this->get_item($i, 'SC_COMPROMISO_REALIZADO');
			$cod_persona = $this->get_item($i, 'SC_COD_PERSONA');
			
			$cod_bitacora_factura = ($cod_bitacora_factura =='') ? "null" : "$cod_bitacora_factura";
			$contacto = ($contacto =='') ? "null" : "'$contacto'";			
			$telefono = ($telefono =='') ? "null" : "'$telefono'";			
			$mail = ($mail =='') ? "null" : "'$mail'";			
			$glosa = ($glosa =='') ? "null" : "'$glosa'";			
			$fecha_compromiso = ($fecha_compromiso =='') ? "null" : $this->str2date($fecha_compromiso, $hora_compromiso.':00');			
			$glosa_compromiso = ($glosa_compromiso =='') ? "null" : "'$glosa_compromiso'";			
			$compromiso_realizado = ($compromiso_realizado =='') ? "N" : "'$compromiso_realizado'";			
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			else if ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';		
						
			$param = "'$operacion'
					,$cod_bitacora_factura
					,$this->cod_usuario
					,$cod_factura
					,$cod_accion_cobranza
					,$contacto
					,$telefono
					,$mail
					,$glosa
					,'$tiene_compromiso'
					,$fecha_compromiso
					,$glosa_compromiso
					,$compromiso_realizado
					,$cod_persona";
			
			if (!$db->EXECUTE_SP($sp, $param)) 
				return false;
			else {
				if ($statuts == K_ROW_NEW_MODIFIED) {
					$cod_bitacora_factura = $db->GET_IDENTITY();
					$this->set_item($i, 'SC_COD_BITACORA_COTIZACION', $cod_bitacora_factura);		
				}
			}
		}
		
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$cod_bitacora_factura = $this->get_item($i, 'SC_COD_BITACORA_COTIZACION ', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_bitacora_factura"))
				return false;
		}	
		return true;
	}
}
/*End- Seguimiento Cotizacion*/

class dw_bitacora_cotizacion extends datawindow {
	function dw_bitacora_cotizacion() {
		$sql = "select BC.COD_BITACORA_COTIZACION  									BC_COD_BITACORA_COTIZACION
						,convert(varchar, BC.FECHA_BITACORA, 103) 					BC_FECHA_BITACORA
						,substring(convert(varchar, BC.FECHA_BITACORA, 108),1 , 5) 	BC_HORA_BITACORA
						,BC.COD_USUARIO  											BC_COD_USUARIO
						,U1.INI_USUARIO  											BC_INI_USUARIO
						,BC.COD_COTIZACION  BC_COD_COTIZACION	
						,BC.COD_ACCION_COTIZACION  BC_COD_ACCION_COTIZACION
						,BC.CONTACTO   BC_CONTACTO
						,BC.TELEFONO	BC_TELEFONO
						,BC.MAIL		BC_MAIL
						,BC.GLOSA		BC_GLOSA
						,BC.TIENE_COMPROMISO 		BC_TIENE_COMPROMISO		
						,convert(varchar, BC.FECHA_COMPROMISO, 103)  BC_FECHA_COMPROMISO
						,substring(convert(varchar, BC.FECHA_COMPROMISO, 108),1 , 5) 		BC_HORA_COMPROMISO
						,BC.GLOSA_COMPROMISO		BC_GLOSA_COMPROMISO
						,BC.COMPROMISO_REALIZADO	BC_COMPROMISO_REALIZADO
						,convert(varchar, BC.FECHA_REALIZADO, 103) 	 BC_FECHA_REALIZADO
						,substring(convert(varchar, BC.FECHA_REALIZADO, 108),1 , 5) BC_HORA_REALIZADO
						,BC.COD_USUARIO_REALIZADO		BC_COD_USUARIO_REALIZADO
						,U2.INI_USUARIO BC_INI_USUARIO_REALIZADO
				from BITACORA_COTIZACION BC left outer join USUARIO U2 on U2.COD_USUARIO = BC.COD_USUARIO_REALIZADO, USUARIO U1 
				where BC.COD_COTIZACION = {KEY1}
				 and U1.COD_USUARIO = BC.COD_USUARIO";
		parent::datawindow($sql, 'BITACORA_COTIZACION', true, false);
		
		// controls
		$this->add_control(new static_text('BC_FECHA_BITACORA'));
		$this->add_control(new static_text('BC_HORA_BITACORA'));
		$this->add_control(new static_text('BC_INI_USUARIO'));
		$sql = "select COD_ACCION_COTIZACION
						,NOM_ACCION_COTIZACION
				from ACCION_COTIZACION
				order by NOM_ACCION_COTIZACION";
		$this->add_control(new drop_down_dw('BC_COD_ACCION_COTIZACION', $sql, 103));
		$this->add_control(new edit_text_upper('BC_CONTACTO', 20, 100));
		$this->add_control(new edit_text_upper('BC_TELEFONO', 20, 100));
		$this->add_control(new edit_mail('BC_MAIL', 20, 100));
		$this->add_control(new edit_text_multiline('BC_GLOSA', 30, 1));
		$this->add_control($control = new edit_check_box('BC_TIENE_COMPROMISO', 'S', 'N'));
		$control->set_onClick("tiene_compromiso(this);");
		$this->add_control(new edit_protected('BC_FECHA_COMPROMISO', new edit_date('FECHA_COMPROMISO_E'), new static_text('FECHA_COMPROMISO_S')));
		$this->add_control(new edit_protected('BC_HORA_COMPROMISO', new edit_time('HORA_COMPROMISO_E'), new static_text('HORA_COMPROMISO_S')));
		$this->add_control(new edit_protected('BC_GLOSA_COMPROMISO', new edit_text_upper('GLOSA_COMPROMISO_E', 51, 100), new static_text('GLOSA_COMPROMISO_S')));
		$this->add_control($control = new edit_check_box('BC_COMPROMISO_REALIZADO', 'S', 'N'));
		$control->set_onClick("compromiso_realizado(this);");
		$this->add_control(new static_text('BC_FECHA_REALIZADO'));

		
		// mandatory
		$this->set_mandatory('BC_COD_ACCION_COTIZACION', 'Acción');
		$this->set_mandatory('BC_CONTACTO', 'Contacto');
		
		// first focus
		$this->set_first_focus('BC_COD_ACCION_COTIZACION');

		// protected
		$this->set_protect('BC_FECHA_COMPROMISO', "[BC_TIENE_COMPROMISO]=='N'");
		$this->set_protect('BC_GLOSA_COMPROMISO', "[BC_TIENE_COMPROMISO]=='N'");
		$this->set_protect('BC_COMPROMISO_REALIZADO', "[BC_TIENE_COMPROMISO]=='N'");
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'BC_TIENE_COMPROMISO', 'S');
		$this->set_item($row, 'BC_FECHA_BITACORA', $this->current_date());
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select INI_USUARIO
				from USUARIO
				where COD_USUARIO = ".$this->cod_usuario;
		$result = $db->build_results($sql);
		$this->set_item($row, 'BC_INI_USUARIO', $result[0]['INI_USUARIO']);
		return $row;
	}
	function update($db)	{
		$sp = 'spu_bitacora_cotizacion';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
	
			$cod_bitacora_factura = $this->get_item($i, 'BC_COD_BITACORA_COTIZACION'); 
			$cod_factura = $this->get_item($i, 'BC_COD_COTIZACION');
			$cod_accion_cobranza = $this->get_item($i, 'BC_COD_ACCION_COTIZACION');
			$contacto = $this->get_item($i, 'BC_CONTACTO');
			$telefono = $this->get_item($i, 'BC_TELEFONO');
			$mail = $this->get_item($i, 'BC_MAIL');
			$glosa = $this->get_item($i, 'BC_GLOSA');
			$tiene_compromiso = $this->get_item($i, 'BC_TIENE_COMPROMISO');
			$fecha_compromiso = $this->get_item($i, 'BC_FECHA_COMPROMISO');
			$hora_compromiso = $this->get_item($i, 'BC_HORA_COMPROMISO');
			$glosa_compromiso = $this->get_item($i, 'BC_GLOSA_COMPROMISO');
			$compromiso_realizado = $this->get_item($i, 'BC_COMPROMISO_REALIZADO');
			
			$cod_bitacora_factura = ($cod_bitacora_factura =='') ? "null" : "$cod_bitacora_factura";			
			$telefono = ($telefono =='') ? "null" : "'$telefono'";			
			$mail = ($mail =='') ? "null" : "'$mail'";			
			$glosa = ($glosa =='') ? "null" : "'$glosa'";			
			$fecha_compromiso = ($fecha_compromiso =='') ? "null" : $this->str2date($fecha_compromiso, $hora_compromiso.':00');			
			$glosa_compromiso = ($glosa_compromiso =='') ? "null" : "'$glosa_compromiso'";			
			$compromiso_realizado = ($compromiso_realizado =='') ? "N" : "'$compromiso_realizado'";			
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			else if ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';		
						
			$param = "'$operacion'
					,$cod_bitacora_factura
					,$this->cod_usuario
					,$cod_factura
					,$cod_accion_cobranza
					,'$contacto'
					,$telefono
					,$mail
					,$glosa
					,'$tiene_compromiso'
					,$fecha_compromiso
					,$glosa_compromiso
					,$compromiso_realizado";
			
			if (!$db->EXECUTE_SP($sp, $param)) 
				return false;
			else {
				if ($statuts == K_ROW_NEW_MODIFIED) {
					$cod_bitacora_factura = $db->GET_IDENTITY();
					$this->set_item($i, 'BC_COD_BITACORA_COTIZACION', $cod_bitacora_factura);		
				}
			}
		}
		
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$cod_bitacora_factura = $this->get_item($i, 'BC_COD_BITACORA_COTIZACION ', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_bitacora_factura"))
				return false;
		}	
		return true;
	}
}

class dw_llamado extends datawindow {
	function dw_llamado() {
		
		$sql = "SELECT LL.COD_LLAMADO LL_COD_LLAMADO
						,CONVERT (VARCHAR(10), LL.FECHA_LLAMADO, 103) LL_FECHA_LLAMADO
						,LLA.NOM_LLAMADO_ACCION LL_NOM_LLAMADO_ACCION
						,C.NOM_CONTACTO LL_NOM_CONTACTO
						,dbo.f_llamado_telefono(LL.COD_CONTACTO, 'EMPRESA') LL_TELEFONO_CONTACTO
						,CP.NOM_PERSONA LL_NOM_PERSONA
						,dbo.f_llamado_telefono(LL.COD_CONTACTO_PERSONA, 'PERSONA') LL_TELEFONO_PERSONA
						,LL.MENSAJE LL_MENSAJE
					FROM LLAMADO LL
						,LLAMADO_ACCION LLA
						,CONTACTO C
						,CONTACTO_PERSONA CP
				   WHERE LL.TIPO_DOC_REALIZADO = 'COTIZACION'
				   	 AND LL.COD_DOC_REALIZADO = {KEY1}
					 AND LLA.COD_LLAMADO_ACCION = LL.COD_LLAMADO_ACCION
					 AND C.COD_CONTACTO = LL.COD_CONTACTO
					 AND CP.COD_CONTACTO_PERSONA = LL.COD_CONTACTO_PERSONA";

		parent::datawindow($sql);

		$this->add_control($control = new edit_num('LL_COD_LLAMADO'));
		$control->set_onChange('find_1_llamado(this);');
		
		$this->add_control(new static_text('LL_FECHA_LLAMADO'));
		$this->add_control(new static_text('LL_NOM_LLAMADO_ACCION'));
		$this->add_control(new static_text('LL_NOM_CONTACTO'));
		$this->add_control(new static_text('LL_TELEFONO_CONTACTO'));
		$this->add_control(new static_text('LL_NOM_PERSONA'));
		$this->add_control(new static_text('LL_TELEFONO_PERSONA'));
		$this->add_control(new edit_text_multiline('LL_MENSAJE', 80, 3));
		$this->set_entrable('LL_MENSAJE', false);
	}
}

class wi_cotizacion extends wi_cotizacion_base {

	
	const K_ESTADO_EMITIDA 			= 1;	
	const K_PARAM_VALIDEZ_OFERTA 	= 7;
	const K_ESTADO_ANULADA			= 7;
	const K_PARAM_ENTREGA			= 8;
	const K_PARAM_GARANTIA	 		= 9;
	
	const K_PARAM_NOM_EMPRESA        =6;
	const K_PARAM_RUT_EMPRESA        =20;
	const K_PARAM_DIR_EMPRESA        =10;
	const K_PARAM_TEL_EMPRESA        =11;
	const K_PARAM_FAX_EMPRESA        =12;
	const K_PARAM_MAIL_EMPRESA       =13;
	const K_PARAM_CIUDAD_EMPRESA     =14;
	const K_PARAM_PAIS_EMPRESA       =15;
	const K_PARAM_SMTP 				 =17;
	const K_PARAM_GIRO_EMPRESA		 =21;
	const K_PARAM_SITIO_WEB_EMPRESA  =25;
	const K_PARAM_BANCO				 =61;
	const K_PARAM_CTA_CTE  			 =62;
	const K_PARAM_PORC_DSCTO_MAX_ESP =69;

	const K_AUTORIZA_SOLO_BITACORA	= '990530';
	const K_AUTORIZA_MOD_COTIZACION = '990535';
	const K_AUTORIZA_VALIDA_OFERTA = '990545';
	
	
	var $desde_wo_bitacora_cotizacion = false;
	var $desde_wo_seguimiento_cotizacion = false;
		
	function wi_cotizacion($cod_item_menu) {	
		if (session::is_set('DESDE_wo_bitacora_cotizacion')) {
			session::un_set('DESDE_wo_bitacora_cotizacion');
			$this->desde_wo_bitacora_cotizacion = true;
		}
		if (session::is_set('DESDE_wo_seguimiento_cotizacion')) {
			session::un_set('DESDE_wo_seguimiento_cotizacion');
			$this->desde_wo_seguimiento_cotizacion = true;
		}

		//parent::wi_cotizacion_base($cod_item_menu);	
		parent::w_cot_nv('cotizacion', $cod_item_menu);
		$sql = "select	 C.COD_COTIZACION
						,convert(varchar(20), C.FECHA_COTIZACION, 103) FECHA_COTIZACION
						,C.COD_USUARIO
						,$this->cod_usuario COD_USUARIO_ACTUAL
						,U.NOM_USUARIO
						,COD_USUARIO_VENDEDOR1
						,PORC_VENDEDOR1
						,COD_USUARIO_VENDEDOR2
						,PORC_VENDEDOR2
						,IDIOMA
						,REFERENCIA
						,REFERENCIA REFERENCIA_L
						,COD_MONEDA
						,C.COD_ESTADO_COTIZACION
						,EC.NOM_ESTADO_COTIZACION
						,COD_ORIGEN_COTIZACION
						,COD_COTIZACION_DESDE
						,C.COD_EMPRESA
						,E.ALIAS
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,C.COD_EMPRESA	COD_EMPRESA_L
						,E.ALIAS		ALIAS_L
						,E.RUT			RUT_L
						,E.DIG_VERIF	DIG_VERIF_L
						,E.NOM_EMPRESA	NOM_EMPRESA_L
						,E.GIRO
						,'none' LL_LLAMADO
						,'' CONTACTO_WEB
						,'' NOM_CONTACTO
						,'' RUT_CONTACTO
						,'' EMPRESA_CONTACTO
						,'' CIUDAD_CONTACTO
						,'' TELEFONO_CONTACTO
						,'' CELULAR_CONTACTO
						,'' EMAIL_CONTACTO
						,'' COMENTARIO_CONTACTO
						,''DESDE_COTI
						,''DESDE_SOLI
						,case E.SUJETO_A_APROBACION
							when 'S' then 'SUJETO A APROBACION'
							else ''
						end SUJETO_A_APROBACION
						,C.COD_FORMA_PAGO
						,C.NOM_FORMA_PAGO_OTRO
						,COD_COTIZACION_DESDE
						,COD_SUCURSAL_FACTURA
						,SUMAR_ITEMS
						,SUBTOTAL SUM_TOTAL
						,SUBTOTAL SUM_TOTAL_L
						,PORC_DSCTO1
						,PORC_DSCTO1 PORC_DSCTO1_L
						,MONTO_DSCTO1
						,MONTO_DSCTO1 MONTO_DSCTO1_L
						,'none' VISIBLE
						,INGRESO_USUARIO_DSCTO1
						,PORC_DSCTO2
						,PORC_DSCTO2 PORC_DSCTO2_L
						,MONTO_DSCTO2
						,MONTO_DSCTO2 MONTO_DSCTO2_L
						,INGRESO_USUARIO_DSCTO2
						,TOTAL_NETO
						,TOTAL_NETO TOTAL_NETO_L
						,PORC_IVA
						,PORC_IVA PORC_IVA_L
						,MONTO_IVA
						,MONTO_IVA MONTO_IVA_L
						,TOTAL_CON_IVA
						,TOTAL_CON_IVA TOTAL_CON_IVA_L
						,dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL_FACTURA, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA						
						,COD_SUCURSAL_DESPACHO
						,dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL_DESPACHO, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_DESPACHO
						,C.COD_PERSONA
						,NOM_PERSONA
						,dbo.f_emp_get_mail_cargo_persona(C.COD_PERSONA,  '[EMAIL] - [NOM_CARGO]') MAIL_CARGO_PERSONA
						,VALIDEZ_OFERTA
						,ENTREGA
						,C.COD_EMBALAJE_COTIZACION
						,C.COD_FLETE_COTIZACION
						,C.COD_INSTALACION_COTIZACION
						,GARANTIA
						,OBS
						,POSIBILIDAD_CIERRE
						,FECHA_POSIBLE_CIERRE
						,dbo.f_get_parametro(".self::K_PARAM_PORC_DSCTO_MAX.") PORC_DSCTO_MAX
						,dbo.f_get_parametro(".self::K_PARAM_PORC_DSCTO_MAX_ESP.") PORC_DSCTO_MAX_ESP
						,dbo.f_get_parametro(".self::K_PARAM_PORC_DSCTO_MAX_ESP.") PORC_DSCTO_MAX_ESP_ST
						,NOM_FORMA_PAGO_OTRO
						,C.COD_SOLICITUD_COTIZACION
						,case C.COD_ESTADO_COTIZACION
							when ".self::K_ESTADO_ANULADA." then 'ANULADA'
							else ''
						end TITULO_ESTADO_SOLICITUD_COTIZACION
						,FECHA_REGISTRO_COTIZACION
						,case 
							when (INGRESO_USUARIO_DSCTO1='M' and MONTO_DSCTO1>0) then 'Descuento ingresado como monto'
							else ''
						end ETIQUETA_DESCT1
						,case 
							when (INGRESO_USUARIO_DSCTO2='M' and MONTO_DSCTO2>0)  then 'Descuento ingresado como monto'
							else ''
						end ETIQUETA_DESCT2
						,'' TIPO_DISPOSITIVO
						,COD_TIPO_RECHAZO
						,RECHAZADA
						,TEXTO_RECHAZO
						,'' DISPLAY_RECHAZO
						,'' DISPLAY_TR1
						,'' DISPLAY_TR2
						,DESCTO_1_AUTORIZADO
						,DESCTO_2_AUTORIZADO
						,dbo.f_last_usu_mod_dscto_mod(COD_COTIZACION, 1) LAST_USU_DSCTO1
						,dbo.f_last_usu_mod_dscto_mod(COD_COTIZACION, 2) LAST_USU_DSCTO2
						,AUT_DESCTO_ESP
						,AUT_DESCTO_ESP AUT_DESCTO_ESP_H
						,dbo.f_last_usu_mod_dscto_mod(COD_COTIZACION, 3) LAST_USU_DSCTO3
						,CASE $this->cod_usuario
							WHEN 1 THEN ''
							WHEN 2 THEN ''
							WHEN 4 THEN ''
							WHEN 8 THEN ''
							WHEN 69 THEN ''
							WHEN 71 THEN ''
							WHEN 40 THEN ''
							ELSE 'none'
						END TR_DISPLAY_TXT	
				from 	COTIZACION C, USUARIO U, EMPRESA E, ESTADO_COTIZACION EC, PERSONA P
				where	COD_COTIZACION = {KEY1} and
						U.COD_USUARIO = C.COD_USUARIO AND
						P.COD_PERSONA = C.COD_PERSONA AND
						E.COD_EMPRESA = C.COD_EMPRESA AND
						EC.COD_ESTADO_COTIZACION = C.COD_ESTADO_COTIZACION";


		////////////////////
		// tab Cotizacion
		// DATAWINDOWS COTIZACION
		$this->dws['dw_cotizacion'] = new dw_help_empresa($sql);
		$this->dws['dw_cotizacion']->add_control(new static_text('TIPO_DISPOSITIVO'));
		$this->dws['dw_cotizacion']->set_protect('AUT_DESCTO_ESP', "[AUT_DESCTO_ESP]=='S'");
		
		$this->dws['dw_cotizacion']->add_control(new static_text('NOM_CONTACTO_WEB'));
		$this->dws['dw_cotizacion']->add_control(new static_text('RUT_CONTACTO_WEB'));
		$this->dws['dw_cotizacion']->add_control(new static_text('EMPRESA_CONTACTO_WEB'));
		$this->dws['dw_cotizacion']->add_control(new static_text('CIUDAD_CONTACTO_WEB'));
		$this->dws['dw_cotizacion']->add_control(new static_text('TELEFONO_CONTACTO_WEB'));
		$this->dws['dw_cotizacion']->add_control(new static_text('CELULAR_CONTACTO_WEB'));
		$this->dws['dw_cotizacion']->add_control(new static_text('EMAIL_CONTACTO_WEB'));
		$this->dws['dw_cotizacion']->add_control(new static_text('COMENTARIO_CONTACTO_WEB'));
		$this->dws['dw_cotizacion']->add_control(new static_text('COD_SOLICITUD_COTIZACION'));
		
		$this->dws['dw_cotizacion']->add_control(new static_num('SUM_TOTAL_L'));
		$this->dws['dw_cotizacion']->add_control(new static_num('MONTO_DSCTO1_L'));
		$this->dws['dw_cotizacion']->add_control(new static_num('MONTO_DSCTO2_L'));
		$this->dws['dw_cotizacion']->add_control(new static_num('TOTAL_NETO_L'));
		$this->dws['dw_cotizacion']->add_control(new static_num('MONTO_IVA_L'));
		$this->dws['dw_cotizacion']->add_control(new static_num('TOTAL_CON_IVA_L'));
		$this->dws['dw_cotizacion']->add_control(new static_num('PORC_IVA_L', 1));
			
		// DATOS GENERALES
		$this->dws['dw_cotizacion']->add_control($control = new edit_porcentaje('DESCTO_1_AUTORIZADO'));
		$control->set_onChange("valida_descuento_aut(this);");
		$this->dws['dw_cotizacion']->add_control($control = new edit_porcentaje('DESCTO_2_AUTORIZADO'));
		$control->set_onChange("valida_descuento_aut(this);");
		
		$this->dws['dw_cotizacion']->add_control(new edit_nro_doc('COD_COTIZACION','COTIZACION'));
		$this->add_controls_cot_nv();
		$this->dws['dw_cotizacion']->add_control($control = new drop_down_list('IDIOMA',array('E','I'),array('ESPAÑOL','INGLES'),150));
		$this->dws['dw_cotizacion']->set_entrable('IDIOMA', false);
		
		$this->dws['dw_cotizacion']->add_control(new static_text('NOM_ESTADO_COTIZACION'));
		$sql_origen  			= "	select 			COD_ORIGEN_COTIZACION
													,NOM_ORIGEN_COTIZACION,
													ORDEN
									from 			ORIGEN_COTIZACION
									order by 		ORDEN";
		$this->dws['dw_cotizacion']->add_control(new drop_down_dw('COD_ORIGEN_COTIZACION',$sql_origen,150));
		$this->dws['dw_cotizacion']->add_control(new static_text('COD_COTIZACION_DESDE'));
		$this->dws['dw_cotizacion']->add_control(new static_text('NOM_ESTADO_COTIZACION'));
		
		$sql_origen  			= "	SELECT COD_TIPO_RECHAZO
										  ,NOM_TIPO_RECHAZO 
									FROM TIPO_RECHAZO
									ORDER BY ORDEN";
		$this->dws['dw_cotizacion']->add_control(new drop_down_dw('COD_TIPO_RECHAZO',$sql_origen,490));
		$this->dws['dw_cotizacion']->add_control($control = new edit_check_box('RECHAZADA', 'S', 'N'));
		$control->set_onChange('display_rechazo();');
		$this->dws['dw_cotizacion']->add_control(new edit_text_multiline('TEXTO_RECHAZO',99,4));
		
		
		// asigna los mandatorys
		$this->dws['dw_cotizacion']->set_mandatory('COD_ESTADO_COTIZACION', 'un Estado');
		$this->dws['dw_cotizacion']->set_mandatory('COD_ORIGEN_COTIZACION', 'un Origen');
		//$this->dws['dw_cotizacion']->add_control(new edit_porcentaje('PORC_VENDEDOR1',2,2,1));

		////////////////////
		// tab items
		// DATAWINDOWS ITEMS COTIZACION
		$this->dws['dw_item_cotizacion'] = new dw_item_cotizacion();
		$this->dws['dw_item_resumen_cotizacion'] = new dw_item_resumen_cotizacion();
		$this->dws['dw_llamado'] = new dw_llamado();
		

		// TOTALES
		$this->dws['dw_cotizacion']->add_control(new edit_check_box('SUMAR_ITEMS','S','N'));
		$this->dws['dw_cotizacion']->add_control(new edit_check_box('AUT_DESCTO_ESP','S','N'));

		////////////////////
		// tab Condiciones generales
		// CONDICIONES GENERALES
		$sql_forma_pago			= "	select 			COD_FORMA_PAGO
													,NOM_FORMA_PAGO
													,ORDEN
						   			from			FORMA_PAGO
						   			order by  		ORDEN";
		
		$this->dws['dw_cotizacion']->add_control($control = new drop_down_dw('COD_FORMA_PAGO', $sql_forma_pago, 180));
		$control->set_onChange('mostrarOcultar(this);');
		$this->dws['dw_cotizacion']->add_control(new edit_text_upper('NOM_FORMA_PAGO_OTRO',132, 100));
		
		$this->dws['dw_cotizacion']->add_control(new edit_num('VALIDEZ_OFERTA',3,3));
		$this->dws['dw_cotizacion']->add_control(new edit_text_upper('ENTREGA',180,100));
		$sql_embalaje_cot 		= "	select 			COD_EMBALAJE_COTIZACION
													,NOM_EMBALAJE_COTIZACION
						   			from			EMBALAJE_COTIZACION
						   			order by  		NOM_EMBALAJE_COTIZACION asc";
		$this->dws['dw_cotizacion']->add_control(new drop_down_dw('COD_EMBALAJE_COTIZACION',$sql_embalaje_cot,740));
		$sql_flete_cot 			= "	select 			COD_FLETE_COTIZACION
													,NOM_FLETE_COTIZACION
													,ORDEN
						  			 from			FLETE_COTIZACION
						  			order by  		ORDEN";
		$this->dws['dw_cotizacion']->add_control(new drop_down_dw('COD_FLETE_COTIZACION',$sql_flete_cot,740));
		$sql_ins_cot 			= "	select 			COD_INSTALACION_COTIZACION
													,NOM_INSTALACION_COTIZACION
													,ORDEN
						  			from			INSTALACION_COTIZACION
						   			order by  		ORDEN";
		$this->dws['dw_cotizacion']->add_control(new drop_down_dw('COD_INSTALACION_COTIZACION',$sql_ins_cot,740));
		$this->dws['dw_cotizacion']->add_control(new edit_text_upper('GARANTIA',180,100));
		$this->dws['dw_cotizacion']->add_control(new edit_text_multiline('OBS',54,4));

		//auditoria Solicitado por IS.
		$this->add_auditoria('COD_USUARIO_VENDEDOR1');
		$this->add_auditoria('PORC_VENDEDOR1');
		$this->add_auditoria('COD_USUARIO_VENDEDOR2');
		$this->add_auditoria('PORC_VENDEDOR2');
		$this->add_auditoria('COD_ESTADO_COTIZACION');
		$this->add_auditoria('COD_EMPRESA');
		$this->add_auditoria('COD_SUCURSAL_FACTURA');
		$this->add_auditoria('COD_SUCURSAL_DESPACHO');
		$this->add_auditoria('COD_PERSONA');
		$this->add_auditoria('RECHAZADA');
		$this->add_auditoria('TEXTO_RECHAZO');
		
		$this->add_auditoria('DESCTO_1_AUTORIZADO');
		$this->add_auditoria('DESCTO_2_AUTORIZADO');
		$this->add_auditoria('AUT_DESCTO_ESP');
		
		// asigna los mandatorys
		$this->dws['dw_cotizacion']->set_mandatory('COD_FORMA_PAGO', 'Forma de Pago');
		$this->dws['dw_cotizacion']->set_mandatory('VALIDEZ_OFERTA', 'Validez Oferta');
		$this->dws['dw_cotizacion']->set_mandatory('ENTREGA', 'Entrega');
		$this->dws['dw_cotizacion']->set_mandatory('COD_EMBALAJE_COTIZACION', 'Embalaje');
		$this->dws['dw_cotizacion']->set_mandatory('COD_FLETE_COTIZACION', 'Flete');
		$this->dws['dw_cotizacion']->set_mandatory('COD_INSTALACION_COTIZACION', 'Instalación');
		$this->dws['dw_cotizacion']->set_mandatory('GARANTIA', 'Garantía');


		////////////////////
		// tab STOCK
		//$this->dws['dw_item_stock'] = new dw_item_stock();

		////////////////////
		// TABS PRE-RESULTADO

		////////////////////
		// TABS SEGUIMIENTO COTIZACION
		$this->dws['dw_cotizacion']->add_control(new edit_text('COD_ESTADO_COTIZACION',10, 10, 'hidden'));
		
		$this->dws['dw_cotizacion']->add_control(new edit_text_hidden('COD_USUARIO_ACTUAL'));
		$this->dws['dw_cotizacion']->add_control(new edit_text_hidden('AUT_DESCTO_ESP_H'));
		
		$this->dws['dw_cotizacion']->set_mandatory('COD_ESTADO_COTIZACION', 'un Estado');

		$this->set_first_focus('REFERENCIA');
		
		$this->dws['dw_cotizacion']->add_control(new edit_text('PORC_DSCTO_MAX',10, 10, 'hidden'));
		$this->dws['dw_cotizacion']->add_control(new edit_text('PORC_DSCTO_MAX_ESP',10, 10, 'hidden'));
		
		///////////////////////
		// solo si tiene privilegios es ingtresable
		if ($this->tiene_privilegio_opcion('990520')<>'E') {
			$this->dws['dw_cotizacion']->controls['MONTO_DSCTO1']->readonly = true;
			$this->dws['dw_cotizacion']->controls['MONTO_DSCTO2']->readonly = true;
		}
		$this->dws['dw_cotizacion']->add_control(new static_text('ETIQUETA_DESCT1'));
		$this->dws['dw_cotizacion']->add_control(new static_text('ETIQUETA_DESCT2'));
		//////////////////////		 
		
		// tab Bitacora
		//$this->dws['dw_bitacora_cotizacion'] = new dw_bitacora_cotizacion();
		$this->dws['dw_seguimiento_cotizacion'] = new dw_seguimiento_cotizacion();
		
		$priv = $this->get_privilegio_opcion_usuario("990550", $this->cod_usuario);
		if($priv <> 'E'){
			$this->dws['dw_cotizacion']->set_entrable('DESCTO_1_AUTORIZADO_0', false);
			$this->dws['dw_cotizacion']->set_entrable('DESCTO_2_AUTORIZADO_0', false);
		}else{
			$this->dws['dw_cotizacion']->set_entrable('DESCTO_1_AUTORIZADO_0', true);
			$this->dws['dw_cotizacion']->set_entrable('DESCTO_2_AUTORIZADO_0', true);
		}
		
		$priv = $this->get_privilegio_opcion_usuario("990555", $this->cod_usuario);
		if($priv <> 'E'){
			$this->dws['dw_cotizacion']->set_entrable('AUT_DESCTO_ESP', false);
		}else{
			$this->dws['dw_cotizacion']->set_entrable('AUT_DESCTO_ESP', true);
		}
		
	}
	////////////////////
	// funciones auxiliares para cuando se accede a la FA desde_wo_bitacora_cotizacion
	function load_wo() {
		if ($this->desde_wo_bitacora_cotizacion)
			$this->wo = session::get("wo_bitacora_cotizacion");
		else if ($this->desde_wo_seguimiento_cotizacion)
			$this->wo = session::get("wo_seguimiento_cotizacion");
		else
			parent::load_wo();
	}
	function get_url_wo() {
		if ($this->desde_wo_bitacora_cotizacion) 
			return $this->root_url.'appl/bitacora_cotizacion/wo_bitacora_cotizacion.php';
		else if ($this->desde_wo_seguimiento_cotizacion) 
			return $this->root_url.'appl/seguimiento_cotizacion/wo_seguimiento_cotizacion.php';
		else
			return parent::get_url_wo();
	}
	////////////////////
	
	function add_controls_dscto($nro_dscto) {
		parent::add_controls_dscto($nro_dscto);
		$jsP = $this->dws[$this->dw_tabla]->controls['PORC_DSCTO'.$nro_dscto]->get_onChange();
		$jsM = $this->dws[$this->dw_tabla]->controls['MONTO_DSCTO'.$nro_dscto]->get_onChange();

		// maneja los static con los labes que indican el tipo dscto ingreso por el usuario		
		$java_script = " if (document.getElementById('INGRESO_USUARIO_DSCTO".$nro_dscto."_0').value == 'M') {
							if (document.getElementById('MONTO_DSCTO".$nro_dscto."_0').value == 0) 
								document.getElementById('ETIQUETA_DESCT".$nro_dscto."_0').innerHTML = '';
							else 
								document.getElementById('ETIQUETA_DESCT".$nro_dscto."_0').innerHTML = 'Descuento ingresado como monto';
						}
						else 
							document.getElementById('ETIQUETA_DESCT".$nro_dscto."_0').innerHTML = '';";
		
		$this->dws[$this->dw_tabla]->controls['PORC_DSCTO'.$nro_dscto]->set_onChange($jsP.$java_script);
		$this->dws[$this->dw_tabla]->controls['MONTO_DSCTO'.$nro_dscto]->set_onChange($jsM.$java_script);
	}
	function new_record() {
		$this->dws['dw_cotizacion']->insert_row();
		$this->dws['dw_cotizacion']->set_item(0, 'FECHA_COTIZACION', $this->current_date());
		$this->dws['dw_cotizacion']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_cotizacion']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->dws['dw_cotizacion']->set_item(0, 'DISPLAY_RECHAZO', 'none');
		
		//$nom_estado_cotizacion = $this->dws['dw_cotizacion']->controls['COD_ESTADO_COTIZACION']->get_label_from_value($this->get_orden_min('ESTADO_COTIZACION'));
		$this->dws['dw_cotizacion']->set_item(0, 'NOM_ESTADO_COTIZACION', 'EMITIDA');
		$this->dws['dw_cotizacion']->set_item(0, 'COD_ESTADO_COTIZACION', $this->get_orden_min('ESTADO_COTIZACION'));
		$this->dws['dw_cotizacion']->set_item(0, 'IDIOMA', 'E');
		$this->dws['dw_cotizacion']->set_item(0, 'COD_MONEDA', $this->get_orden_min('MONEDA'));
		$this->dws['dw_cotizacion']->set_item(0, 'COD_FORMA_PAGO', $this->get_orden_min('FORMA_PAGO'));
		$this->dws['dw_cotizacion']->controls['NOM_FORMA_PAGO_OTRO']->set_type('hidden');
		$this->dws['dw_cotizacion']->set_item(0, 'COD_EMBALAJE_COTIZACION', $this->get_orden_min('EMBALAJE_COTIZACION'));
		$this->dws['dw_cotizacion']->set_item(0, 'COD_FLETE_COTIZACION', $this->get_orden_min('FLETE_COTIZACION'));
		$this->dws['dw_cotizacion']->set_item(0, 'COD_INSTALACION_COTIZACION', $this->get_orden_min('INSTALACION_COTIZACION'));
		$this->dws['dw_cotizacion']->set_item(0, 'VALIDEZ_OFERTA',$this->get_parametro(self::K_PARAM_VALIDEZ_OFERTA));
		$this->dws['dw_cotizacion']->set_item(0, 'ENTREGA',$this->get_parametro(self::K_PARAM_ENTREGA));
		$this->dws['dw_cotizacion']->set_item(0, 'GARANTIA',$this->get_parametro(self::K_PARAM_GARANTIA));
		$this->dws['dw_cotizacion']->set_item(0, 'CONTACTO_WEB','none');
		$this->dws['dw_cotizacion']->set_item(0, 'DESDE_COTI','');
        $this->dws['dw_cotizacion']->set_item(0, 'DESDE_SOLI','none');
		
		$this->dws['dw_llamado']->insert_row();
		$this->dws['dw_seguimiento_cotizacion']->b_add_line_visible = false;
		//$this->valores_default_vend();
		if (session::is_set('CREADA_DESDE_SOLICITUD')) {
			$cod_solicitud = session::get('CREADA_DESDE_SOLICITUD');			
			session::un_set('CREADA_DESDE_SOLICITUD');
			$this->crear_desde_solicitud($cod_solicitud);

		}
		
		else if(session::is_set('CREADA_DESDE_COTIZACION')) {
			$cod_cotizacion = session::get('CREADA_DESDE_COTIZACION');
			$this->creada_desde($cod_cotizacion);
			session::un_set('CREADA_DESDE_COTIZACION');
			return;
		}
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_VALIDA_OFERTA, $this->cod_usuario);	// acceso bitacora
		if($priv <> 'E'){
			$this->dws['dw_cotizacion']->set_entrable('VALIDEZ_OFERTA', false);
		}
		
		$priv = $this->get_privilegio_opcion_usuario('990560', $this->cod_usuario);
		if($priv == 'E'){
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "SELECT PORC_DESCUENTO_PERMITIDO 
					FROM USUARIO
					WHERE COD_USUARIO = ".$this->cod_usuario;
			$result = $db->build_results($sql);
			$this->dws['dw_cotizacion']->set_item(0, 'PORC_DSCTO_MAX', $result[0]['PORC_DESCUENTO_PERMITIDO']);
			
		}else
			$this->dws['dw_cotizacion']->set_item(0, 'PORC_DSCTO_MAX', $this->get_parametro(self::K_PARAM_PORC_DSCTO_MAX));
	}
	function load_cotizacion($cod_cotizacion) {
		$this->dws['dw_cotizacion']->retrieve($cod_cotizacion);

		//*********VMC, deberia ser codigo generico ???
	 	$cod_empresa = $this->dws['dw_cotizacion']->get_item(0, 'COD_EMPRESA');
		$this->dws['dw_cotizacion']->controls['COD_SUCURSAL_FACTURA']->retrieve($cod_empresa);
		$this->dws['dw_cotizacion']->controls['COD_SUCURSAL_DESPACHO']->retrieve($cod_empresa);
		$this->dws['dw_cotizacion']->controls['COD_PERSONA']->retrieve($cod_empresa);
		
		$cod_forma_pago		= $this->dws['dw_cotizacion']->get_item(0, 'COD_FORMA_PAGO');
		if ($cod_forma_pago==1)
			$this->dws['dw_cotizacion']->controls['NOM_FORMA_PAGO_OTRO']->set_type('text');
		else
			$this->dws['dw_cotizacion']->controls['NOM_FORMA_PAGO_OTRO']->set_type('hidden');
		$this->dws['dw_item_cotizacion']->retrieve($cod_cotizacion);

		//$this->dws['dw_item_stock']->retrieve($cod_cotizacion);

		$this->dws['dw_llamado']->retrieve($cod_cotizacion);
		$this->dws['dw_item_resumen_cotizacion']->retrieve($cod_cotizacion);
		if ($this->dws['dw_llamado']->row_count()==0)
			$this->dws['dw_llamado']->insert_row();
		$this->dws['dw_cotizacion']->set_item(0, 'LL_LLAMADO','');
	}
	function load_record() {
		$cod_cotizacion = $this->get_item_wo($this->current_record, 'COD_COTIZACION');
		$this->load_cotizacion($cod_cotizacion);
		$this->dws['dw_cotizacion']->set_item(0, 'CONTACTO_WEB','none');
		$this->dws['dw_cotizacion']->set_item(0,'LL_LLAMADO','none');
		
		if($this->dws['dw_cotizacion']->get_item(0, 'RECHAZADA') == 'S'){
			$this->dws['dw_cotizacion']->set_item(0,'DISPLAY_TR1','');
			$this->dws['dw_cotizacion']->set_item(0,'DISPLAY_TR2','');
			$this->dws['dw_cotizacion']->set_entrable('TEXTO_RECHAZO', false);
			$this->dws['dw_cotizacion']->set_entrable('RECHAZADA', false);
			$this->dws['dw_cotizacion']->controls['COD_TIPO_RECHAZO']->enabled = false;
		}else{
			$this->dws['dw_cotizacion']->set_item(0,'DISPLAY_TR1','none');
			$this->dws['dw_cotizacion']->set_item(0,'DISPLAY_TR2','none');
			$this->dws['dw_cotizacion']->set_entrable('TEXTO_RECHAZO', true);
			$this->dws['dw_cotizacion']->set_entrable('RECHAZADA', true);
			$this->dws['dw_cotizacion']->controls['COD_TIPO_RECHAZO']->enabled = true;

		}
		
		$os = base::get_tipo_dispositivo();
		if($os == 'IPAD' ){
            $this->dws['dw_cotizacion']->set_item(0,'TIPO_DISPOSITIVO', 'IPAD');
		}
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

        $sql="SELECT COD_SOLICITUD_COTIZACION
				FROM COTIZACION
			   WHERE COD_COTIZACION = $cod_cotizacion";
		
        $result = $db->build_results($sql);
        
        
        $cod_solicitud_cotizacion=$result[0]['COD_SOLICITUD_COTIZACION'];
        if($cod_solicitud_cotizacion == ''){
	        $this->dws['dw_cotizacion']->set_item(0, 'DESDE_COTI','');
	        $this->dws['dw_cotizacion']->set_item(0, 'DESDE_SOLI','none');
	        $this->dws['dw_cotizacion']->set_item(0,'LL_LLAMADO','');
        }else {
        	$this->dws['dw_cotizacion']->set_item(0, 'DESDE_COTI','none');
	        $this->dws['dw_cotizacion']->set_item(0, 'DESDE_SOLI','');
	        $this->dws['dw_cotizacion']->set_item(0, 'CONTACTO_WEB','');
	        
	        $sql="SELECT SC.COD_SOLICITUD_COTIZACION
						, C.NOM_CONTACTO 
						, C.RUT 
						, C.DIG_VERIF
						, C.NOM_CIUDAD
						,dbo.f_contacto_telefono(CP.COD_CONTACTO_PERSONA,1) TELEFONO 
						,dbo.f_contacto_telefono(CP.COD_CONTACTO_PERSONA,2) CELULAR 
						,CP.NOM_PERSONA
						,CP.MAIL 
						,LL.MENSAJE
						,dbo.f_get_parametro(1) PORC_IVA
				FROM	  ITEM_SOLICITUD_COTIZACION ISC
						, SOLICITUD_COTIZACION SC
						, CONTACTO C
						, PRODUCTO P
						,CONTACTO_PERSONA CP
						,LLAMADO LL
				WHERE	  ISC.COD_SOLICITUD_COTIZACION = $cod_solicitud_cotizacion
				AND		  ISC.COD_SOLICITUD_COTIZACION = SC.COD_SOLICITUD_COTIZACION
				AND		  C.COD_CONTACTO = SC.COD_CONTACTO
				AND		  P.COD_PRODUCTO = ISC.COD_PRODUCTO
				AND       C.COD_CONTACTO = CP.COD_CONTACTO
				AND       SC.COD_LLAMADO = LL.COD_LLAMADO
				ORDER BY  COD_ITEM_SOLICITUD_COTIZACION";
				
			$result = $db->build_results($sql);	
			
		$rut = $result[0]['RUT'].'-'.$result[0]['DIG_VERIF'];	
		$this->dws['dw_cotizacion']->set_item(0,'NOM_CONTACTO', $result[0]['NOM_PERSONA']);
        $this->dws['dw_cotizacion']->set_item(0,'RUT_CONTACTO', $rut);
        $this->dws['dw_cotizacion']->set_item(0,'EMPRESA_CONTACTO', $result[0]['NOM_CONTACTO']);
        $this->dws['dw_cotizacion']->set_item(0,'CIUDAD_CONTACTO', $result[0]['NOM_CIUDAD']);
        $this->dws['dw_cotizacion']->set_item(0,'TELEFONO_CONTACTO', $result[0]['TELEFONO']);
        $this->dws['dw_cotizacion']->set_item(0,'CELULAR_CONTACTO', $result[0]['CELULAR']);
        $this->dws['dw_cotizacion']->set_item(0,'EMAIL_CONTACTO', $result[0]['MAIL']);
        $this->dws['dw_cotizacion']->set_item(0,'COMENTARIO_CONTACTO', $result[0]['MENSAJE']);
			
        }
        
        //////load modulo llamado
        $sql = "SELECT LL.COD_LLAMADO LL_COD_LLAMADO
						,CONVERT (VARCHAR(10), LL.FECHA_LLAMADO, 103) LL_FECHA_LLAMADO
						,LLA.NOM_LLAMADO_ACCION LL_NOM_LLAMADO_ACCION
						,C.NOM_CONTACTO LL_NOM_CONTACTO
						,dbo.f_llamado_telefono(LL.COD_CONTACTO, 'EMPRESA') LL_TELEFONO_CONTACTO
						,CP.NOM_PERSONA LL_NOM_PERSONA
						,dbo.f_llamado_telefono(LL.COD_CONTACTO_PERSONA, 'PERSONA') LL_TELEFONO_PERSONA
						,LL.MENSAJE LL_MENSAJE
					FROM LLAMADO LL
						,LLAMADO_ACCION LLA
						,CONTACTO C
						,CONTACTO_PERSONA CP
				   WHERE LL.TIPO_DOC_REALIZADO = 'COTIZACION'
				   	 AND LL.COD_DOC_REALIZADO = $cod_cotizacion
					 AND LLA.COD_LLAMADO_ACCION = LL.COD_LLAMADO_ACCION
					 AND C.COD_CONTACTO = LL.COD_CONTACTO
					 AND CP.COD_CONTACTO_PERSONA = LL.COD_CONTACTO_PERSONA";
					 
					 $result = $db->build_results($sql);
					 $cod_llamado = $result[0]['LL_COD_LLAMADO'];
					 
		if($cod_llamado <> ''){
			$this->dws['dw_cotizacion']->set_item(0,'LL_LLAMADO','');
	        $this->dws['dw_llamado']->set_item(0,'LL_COD_LLAMADO', $result[0]['LL_COD_LLAMADO']);
			$this->dws['dw_llamado']->set_item(0,'LL_FECHA_LLAMADO', $result[0]['LL_FECHA_LLAMADO']);
			$this->dws['dw_llamado']->set_item(0,'LL_NOM_LLAMADO_ACCION', $result[0]['LL_NOM_LLAMADO_ACCION']);
			$this->dws['dw_llamado']->set_item(0,'LL_NOM_CONTACTO', $result[0]['LL_NOM_CONTACTO']);
			$this->dws['dw_llamado']->set_item(0,'LL_TELEFONO_CONTACTO', $result[0]['LL_TELEFONO_CONTACTO']);
			$this->dws['dw_llamado']->set_item(0,'LL_NOM_PERSONA', $result[0]['LL_NOM_PERSONA']);
			$this->dws['dw_llamado']->set_item(0,'LL_TELEFONO_PERSONA', $result[0]['LL_TELEFONO_PERSONA']);
			$this->dws['dw_llamado']->set_item(0,'LL_MENSAJE', $result[0]['LL_MENSAJE']);
		}

		//$this->dws['dw_bitacora_cotizacion']->retrieve($cod_cotizacion);
		$this->dws['dw_seguimiento_cotizacion']->retrieve($cod_cotizacion);

		$cod_empresa = $this->dws['dw_cotizacion']->get_item(0, 'COD_EMPRESA');
		$this->dws['dw_seguimiento_cotizacion']->controls['SC_COD_PERSONA']->retrieve($cod_empresa);
		$this->dws['dw_seguimiento_cotizacion']->b_add_line_visible = true;
		
		//////////////////////////////////////////
		// Si tiene acceso solo bitacora se deshabilita lo demas
	   	$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_SOLO_BITACORA, $this->cod_usuario);	// acceso bitacora

	   	if ($priv=='E')	{	// tiene acceso solo a bitacora
			$this->dws['dw_cotizacion']->set_entrable_dw(false);
			$this->dws['dw_item_cotizacion']->set_entrable_dw(false);
			$this->dws['dw_llamado']->set_entrable_dw(false);
			//$this->dws['dw_item_stock']->set_entrable_dw(false);
			$this->b_delete_visible = false;
	   	}
	   	$cod_estado_cotizacion = $this->dws['dw_cotizacion']->get_item(0, 'COD_ESTADO_COTIZACION');
	   	
	   	if($cod_estado_cotizacion == 5 || $cod_estado_cotizacion == 6){
		   	unset($this->dws['dw_seguimiento_cotizacion']->controls['SC_COD_ACCION_COTIZACION']);
		   	$sql = "select COD_ACCION_COTIZACION
							,NOM_ACCION_COTIZACION
					from ACCION_COTIZACION
					order by NOM_ACCION_COTIZACION";
			$this->dws['dw_seguimiento_cotizacion']->add_control(new drop_down_dw('SC_COD_ACCION_COTIZACION', $sql, 103));
	   	}else{
	   		$sql = "select COD_ACCION_COTIZACION
							,NOM_ACCION_COTIZACION
					from ACCION_COTIZACION
					where COD_ACCION_COTIZACION not in (3,4)
					order by NOM_ACCION_COTIZACION";
			$this->dws['dw_seguimiento_cotizacion']->add_control(new drop_down_dw('SC_COD_ACCION_COTIZACION', $sql, 103));
	   	}
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_VALIDA_OFERTA, $this->cod_usuario);	
		if($priv <> 'E'){
			$this->dws['dw_cotizacion']->set_entrable('VALIDEZ_OFERTA', false);
		}
		
		$priv = $this->get_privilegio_opcion_usuario('990560', $this->cod_usuario);
		if($priv == 'E'){
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "SELECT PORC_DESCUENTO_PERMITIDO 
					FROM USUARIO
					WHERE COD_USUARIO = ".$this->cod_usuario;
			$result = $db->build_results($sql);
			$this->dws['dw_cotizacion']->set_item(0, 'PORC_DSCTO_MAX', $result[0]['PORC_DESCUENTO_PERMITIDO']);
			
		}else
			$this->dws['dw_cotizacion']->set_item(0, 'PORC_DSCTO_MAX', $this->get_parametro(self::K_PARAM_PORC_DSCTO_MAX));
	}
	function get_key() {
		return $this->dws['dw_cotizacion']->get_item(0, 'COD_COTIZACION');
	}	
	function habilita_boton_print(&$temp, $boton, $habilita) {
		if ($habilita){
			$ruta_over = "'../../../../commonlib/trunk/images/b_print_over.jpg'";
			$ruta_out = "'../../../../commonlib/trunk/images/b_print.jpg'";
			$ruta_click = "'../../../../commonlib/trunk/images/b_print_click.jpg'";
			$temp->setVar("WI_PRINT", '<input name="b_'.$boton.'" id="b_'.$boton.'" type="button" onmouseover="entrada(this, '.$ruta_over.')" onmouseout="salida(this, '.$ruta_out.')" onmousedown="down(this, '.$ruta_click.')"'.
												 'style="cursor:pointer;height:68px;width:66px;border: 0;background-image:url(../../../../commonlib/trunk/images/b_print.jpg);background-repeat:no-repeat;background-position:center;border-radius: 15px;"'.
												 'onClick="var vl_tab = document.getElementById(\'wi_current_tab_page\'); if (TabbedPanels1 && vl_tab) vl_tab.value =TabbedPanels1.getCurrentTabIndex();dlg_print();" />');
		}	
		
	}
	
	function habilita_boton(&$temp, $boton, $habilita) {
		parent::habilita_boton($temp, $boton, $habilita);
		
		if($boton == 'print_folleto'){
			if($habilita){
				$control = '<input name="b_'.$boton.'" id="b_'.$boton.'" src="../../images_appl/b_'.$boton.'.jpg" type="image" '.
							'onMouseDown="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../images_appl/b_'.$boton.'_click.jpg\',1)" '.
							'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
							'onMouseOver="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../images_appl/b_'.$boton.'_over.jpg\',1)" '.
							'/>';
			}else{
				$control = '<img src="../../images_appl/b_'.$boton.'_d.jpg">';
			}
			
			$temp->setVar("WI_".strtoupper($boton), $control);
		}
		if($boton == 'f_tecnica'){
			if($habilita){
				$control = '<input name="b_'.$boton.'" id="b_'.$boton.'" src="../../images_appl/b_'.$boton.'.jpg" type="image" '.
							'onMouseDown="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../images_appl/b_'.$boton.'_click.jpg\',1)" '.
							'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
							'onMouseOver="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../images_appl/b_'.$boton.'_over.jpg\',1)" '.
							'/>';
			}else{
				$control = '<img src="../../images_appl/b_'.$boton.'_d.jpg">';
			}
			
			$temp->setVar("WI_".strtoupper($boton), $control);
		}
	}
	
	function navegacion(&$temp){
		parent::navegacion($temp);
		
		$priv = $this->get_privilegio_opcion_usuario('999505', $this->cod_usuario);
		if($priv == 'E')
			$this->habilita_boton($temp, 'print_folleto', true);
		else
			$this->habilita_boton($temp, 'print_folleto', false);
		
		$priv = $this->get_privilegio_opcion_usuario('999510', $this->cod_usuario);
		if($priv == 'E')
			$this->habilita_boton($temp, 'f_tecnica', true);
		else
			$this->habilita_boton($temp, 'f_tecnica', false);
		
	}
	
	function habilitar(&$temp, $habilita) { 
		parent::habilitar(&$temp, $habilita);
		
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_MOD_COTIZACION, $this->cod_usuario);
		
		if($priv != 'E'){
			$cod_est_cot = $this->dws['dw_cotizacion']->get_item(0, 'COD_ESTADO_COTIZACION');
			if($cod_est_cot == 4 //Concretada
				|| $cod_est_cot == 5 //Rechazada
					|| $cod_est_cot == 6) //Re-abierta
				$this->habilita_boton($temp, 'modify', (false));
		}
		if($this->is_new_record())
			$this->habilita_boton_print($temp, 'print', false);
		else	
			$this->habilita_boton_print($temp, 'print', true);		
		
	}
	
	function envia_mail_acuse(){
		$cod_cotizacion 	= $this->get_key();
		$cod_empresa		= $this->dws['dw_cotizacion']->get_item(0, 'COD_EMPRESA');
		$cod_usuario_vend1 	= $this->dws['dw_cotizacion']->get_item(0, 'COD_USUARIO_VENDEDOR1');			
		$cod_usuario_vend2 	= $this->dws['dw_cotizacion']->get_item(0, 'COD_USUARIO_VENDEDOR2');
				
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT	E.COD_USUARIO,
						E.NOM_EMPRESA,
						U.NOM_USUARIO,
						U.MAIL
				FROM	EMPRESA E, USUARIO U 
				WHERE	E.COD_EMPRESA = $cod_empresa and
						E.COD_USUARIO = U.COD_USUARIO";
		$result = $db->build_results($sql);
				
		$cod_usuario_empresa = $result[0]['COD_USUARIO'];
		$nom_usuario_empresa = $result[0]['NOM_USUARIO'];
		$nom_empresa = $result[0]['NOM_EMPRESA'];
		$mail_usuario_empresa = $result[0]['MAIL'];
		
			
		if( $cod_usuario_empresa != $cod_usuario_vend1 && $cod_usuario_empresa != $cod_usuario_vend2){
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "SELECT	COD_USUARIO,
							NOM_USUARIO,
							MAIL 
					FROM	USUARIO 
					WHERE	COD_USUARIO = $cod_usuario_vend1";
			$result = $db->build_results($sql);
			
			$res_mail_emp = $db->build_results('SELECT VALOR FROM PARAMETRO WHERE COD_PARAMETRO ='.self::K_PARAM_MAIL_EMPRESA);
			$mail_emp = $res_mail_emp[0]['VALOR']; 
			
			$cod_vend = $result[0]['COD_USUARIO'];
			$nom_vend = $result[0]['NOM_USUARIO'];
			$mail_vend = $result[0]['MAIL'];
			
			$para = $mail_usuario_empresa;
			$asunto = 'Acuse de Cotización';		
			$mensaje = $nom_vend.' ha creado la Cotizacion Nº'.$cod_cotizacion.' al cliente '.$nom_empresa.'.';		
				
			$cabeceras  = 'MIME-Version: 1.0' . "\n";
			$cabeceras .= 'Content-type: text/html; charset=iso-8859-1'. "\n";
			$cabeceras .= 'From:'.$mail_emp."\n";
			$cabeceras .= 'CC:'.$mail_vend."\n";			  			              				 
			
			/******* consulta el smtp desde parametros *********/
			$sql = "SELECT 	VALOR
					FROM	PARAMETRO
					WHERE 	COD_PARAMETRO =".self::K_PARAM_SMTP;
			$result = $db->build_results($sql);
			
			$smtp = $result[0]['VALOR'];
			// se comenta el envio de mail por q ya no es necesario => Vmelo. 
			//ini_set('SMTP', $smtp);
			
			//mail($para, $asunto, $mensaje, $cabeceras);
		}	
	}
	
	function procesa_event(){
		if(isset($_POST['b_print_resumen_it'])){
			$this->print_resumen_item('S', 'S');
			$this->_load_record();
		}else if(isset($_POST['b_print_folleto_x'])){
			$this->print_folleto('1');
		}else if(isset($_POST['b_f_tecnica_x'])){
			$this->print_folleto('2');
		}else
			parent::procesa_event();
	}
	
	function print_folleto($tipo_print){
		$cod_cotizacion	= $this->get_key();
	
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT DISTINCT COD_PRODUCTO, MIN(ORDEN)
				FROM ITEM_COTIZACION
				WHERE COD_COTIZACION = $cod_cotizacion
				AND COD_PRODUCTO NOT IN ('T', 'TE', 'E', 'F', 'I')
				GROUP BY COD_PRODUCTO
				ORDER BY MIN(ORDEN)";
		$result = $db->build_results($sql);
		
		for($i=0 ; $i < count($result) ; $i++)
			$lista_productos .= $result[$i]['COD_PRODUCTO'].",";
		
		$lista_productos = base64_encode(trim($lista_productos, ','));
		print " <script>window.open('print_especial.php?RESULT_PRODUCTO=$lista_productos&TIPO_PRINT=$tipo_print')</script>";	
		$this->_load_record();
	}
	
	function save_record($db) {
		session::un_set('SOLICITUD_COTIZACION');
		$cod_cotizacion 	= $this->get_key();
		$fecha_cotizacion	= $this->dws['dw_cotizacion']->get_item(0, 'FECHA_COTIZACION');
		$cod_usuario 		= $this->dws['dw_cotizacion']->get_item(0, 'COD_USUARIO');
		$cod_usuario_vend1 	= $this->dws['dw_cotizacion']->get_item(0, 'COD_USUARIO_VENDEDOR1');
		$porc_vendedor1		= $this->dws['dw_cotizacion']->get_item(0, 'PORC_VENDEDOR1');
		$cod_usuario_vend2 	= $this->dws['dw_cotizacion']->get_item(0, 'COD_USUARIO_VENDEDOR2');
		if ($cod_usuario_vend2 =='') {
			$cod_usuario_vend2	= "null";
			$porc_vendedor2		= "null";
		}
		else
			$porc_vendedor2		= $this->dws['dw_cotizacion']->get_item(0, 'PORC_VENDEDOR2');	
		
		$cod_moneda			= $this->dws['dw_cotizacion']->get_item(0, 'COD_MONEDA');
		$idioma			 	= $this->dws['dw_cotizacion']->get_item(0, 'IDIOMA');
		$referencia			= $this->dws['dw_cotizacion']->get_item(0, 'REFERENCIA');
		$referencia 		= str_replace("'", "''", $referencia);
		$cod_est_cot		= $this->dws['dw_cotizacion']->get_item(0, 'COD_ESTADO_COTIZACION');
		$cod_ori_cot		= $this->dws['dw_cotizacion']->get_item(0, 'COD_ORIGEN_COTIZACION');
		$cod_cot_desde		= $this->dws['dw_cotizacion']->get_item(0, 'COD_COTIZACION_DESDE');
		$cod_cot_desde		= ($cod_cot_desde =='') ? "null" : "$cod_cot_desde";
		$cod_empresa		= $this->dws['dw_cotizacion']->get_item(0, 'COD_EMPRESA');
		$cod_suc_despacho	= $this->dws['dw_cotizacion']->get_item(0, 'COD_SUCURSAL_DESPACHO');
		$cod_suc_factura	= $this->dws['dw_cotizacion']->get_item(0, 'COD_SUCURSAL_FACTURA');
		$cod_persona		= $this->dws['dw_cotizacion']->get_item(0, 'COD_PERSONA');
		$cod_persona		= ($cod_persona =='') ? "null" : "$cod_persona";
		$sumar_items		= $this->dws['dw_cotizacion']->get_item(0, 'SUMAR_ITEMS');
		$aut_descto_esp		= $this->dws['dw_cotizacion']->get_item(0, 'AUT_DESCTO_ESP');
		

		$sub_total			= $this->dws['dw_cotizacion']->get_item(0, 'SUM_TOTAL');
		$sub_total      	= ($sub_total =='') ? 0 : "$sub_total";

		$porc_descto1		= $this->dws['dw_cotizacion']->get_item(0, 'PORC_DSCTO1');
		$porc_descto1		= ($porc_descto1 =='') ? "null" : "$porc_descto1";

		$monto_dscto1		= $this->dws['dw_cotizacion']->get_item(0, 'MONTO_DSCTO1');
		$monto_dscto1		= ($monto_dscto1 =='') ? 0 : "$monto_dscto1";

		$porc_descto2		= $this->dws['dw_cotizacion']->get_item(0, 'PORC_DSCTO2');
		$porc_descto2		= ($porc_descto2 =='') ? "null" : "$porc_descto2";

		$monto_dscto2		= $this->dws['dw_cotizacion']->get_item(0, 'MONTO_DSCTO2');
		$monto_dscto2		= ($monto_dscto2 =='') ? 0 : "$monto_dscto2";

		$total_neto			= $this->dws['dw_cotizacion']->get_item(0, 'TOTAL_NETO');
		$total_neto			= ($total_neto =='') ? 0 : "$total_neto";

		$porc_iva			= $this->dws['dw_cotizacion']->get_item(0, 'PORC_IVA');

		$monto_iva			= $this->dws['dw_cotizacion']->get_item(0, 'MONTO_IVA');
		$monto_iva			= ($monto_iva =='') ? 0 : "$monto_iva";

		$total_con_iva		= $this->dws['dw_cotizacion']->get_item(0, 'TOTAL_CON_IVA');
		$total_con_iva		= ($total_con_iva =='') ? 0 : "$total_con_iva";

		$cod_forma_pago		= $this->dws['dw_cotizacion']->get_item(0, 'COD_FORMA_PAGO');
		if ($cod_forma_pago==1){ // forma de pago = OTRO
			$nom_forma_pago_otro= $this->dws['dw_cotizacion']->get_item(0, 'NOM_FORMA_PAGO_OTRO');
		}else{
			$nom_forma_pago_otro= "";
		}
		$nom_forma_pago_otro= ($nom_forma_pago_otro =='') ? "null" : "'$nom_forma_pago_otro'";
		$validez_oferta		= $this->dws['dw_cotizacion']->get_item(0, 'VALIDEZ_OFERTA');
		$entrega			= $this->dws['dw_cotizacion']->get_item(0, 'ENTREGA');
		$entrega 			= str_replace("'", "''", $entrega);
		$cod_embalaje_cot	= $this->dws['dw_cotizacion']->get_item(0, 'COD_EMBALAJE_COTIZACION');
		$cod_flete_cot		= $this->dws['dw_cotizacion']->get_item(0, 'COD_FLETE_COTIZACION');
		$cod_inst_cot		= $this->dws['dw_cotizacion']->get_item(0, 'COD_INSTALACION_COTIZACION');
		$garantia			= $this->dws['dw_cotizacion']->get_item(0, 'GARANTIA');
		$garantia 			= str_replace("'", "''", $garantia);
		$obs				= $this->dws['dw_cotizacion']->get_item(0, 'OBS');
		$obs	 			= str_replace("'", "''", $obs);
		$obs				= ($obs =='') ? "null" : "'$obs'";
		$posib_cierre		= 1;//$this->dws['dw_cotizacion']->get_item(0, 'POSIBILIDAD_CIERRE');
		$fec_posib_cierre	= '01/12/2009';	// NOTA: para el manejo de fecha se debe pasar un string dd/mm/yyyy y en el sp llamar a to_date ber eje en spi_orden_trabajo
		$ing_usuario_dscto1	= $this->dws['dw_cotizacion']->get_item(0, 'INGRESO_USUARIO_DSCTO1');
		$ing_usuario_dscto1	= ($ing_usuario_dscto1 =='') ? "null" : "'$ing_usuario_dscto1'";
		$ing_usuario_dscto2	= $this->dws['dw_cotizacion']->get_item(0, 'INGRESO_USUARIO_DSCTO2');
		$ing_usuario_dscto2	= ($ing_usuario_dscto2 =='') ? "null" : "'$ing_usuario_dscto2'";
		//
		$cod_solicitud_cotizacion	= $this->dws['dw_cotizacion']->get_item(0, 'COD_SOLICITUD_COTIZACION');
		$cod_solicitud_cotizacion	= ($cod_solicitud_cotizacion =='') ? "null" : "$cod_solicitud_cotizacion";
		
		$cod_tipo_rechazo	= $this->dws['dw_cotizacion']->get_item(0, 'COD_TIPO_RECHAZO');
		$rechazo			= $this->dws['dw_cotizacion']->get_item(0, 'RECHAZADA');
		$texto_rechazo		= $this->dws['dw_cotizacion']->get_item(0, 'TEXTO_RECHAZO');
		
		$DESCTO_1_AUTORIZADO	= $this->dws['dw_cotizacion']->get_item(0, 'DESCTO_1_AUTORIZADO');
		$DESCTO_2_AUTORIZADO	= $this->dws['dw_cotizacion']->get_item(0, 'DESCTO_2_AUTORIZADO');

		$cod_tipo_rechazo	= ($cod_tipo_rechazo =='') ? "null" : "$cod_tipo_rechazo";
		$rechazo			= ($rechazo =='') ? "null" : "'$rechazo'";
		$texto_rechazo		= ($texto_rechazo =='') ? "null" : "'$texto_rechazo'";
		
		$DESCTO_1_AUTORIZADO		= ($DESCTO_2_AUTORIZADO =='') ? 0 : "$DESCTO_2_AUTORIZADO";
		$DESCTO_2_AUTORIZADO		= ($DESCTO_2_AUTORIZADO =='') ? 0 : "$DESCTO_2_AUTORIZADO";
		
		//
		
		$cod_cotizacion = ($cod_cotizacion=='') ? "null" : $cod_cotizacion;		
    
		$sp = 'spu_cotizacion';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';	 	
	    else
	    	$operacion = 'UPDATE';	
	    	
		$param	= "		'$operacion'
						,$cod_cotizacion
						,'$fecha_cotizacion'
						,$cod_usuario
						,$cod_usuario_vend1
						,$porc_vendedor1
						,$cod_usuario_vend2
						,$porc_vendedor2
						,$cod_moneda
						,'$idioma'
						,'$referencia'
						,$cod_est_cot
						,$cod_ori_cot
						,$cod_cot_desde
						,$cod_empresa
						,$cod_suc_despacho
						,$cod_suc_factura
						,$cod_persona
						,'$sumar_items'
						,$sub_total
						,$porc_descto1
						,$monto_dscto1
						,$porc_descto2
						,$monto_dscto2
						,$total_neto
						,$porc_iva
						,$monto_iva
						,$total_con_iva
						,$cod_forma_pago
						,$validez_oferta
						,'$entrega'
						,$cod_embalaje_cot
						,$cod_flete_cot
						,$cod_inst_cot
						,'$garantia'
						,$obs
						,$posib_cierre
						,'$fec_posib_cierre'
						,$ing_usuario_dscto1
						,$ing_usuario_dscto2
						,$nom_forma_pago_otro
						,$cod_solicitud_cotizacion
						,$cod_tipo_rechazo
						,$rechazo
						,$texto_rechazo
						,$DESCTO_1_AUTORIZADO
						,$DESCTO_2_AUTORIZADO
						,'$aut_descto_esp'";
										
		if ($db->EXECUTE_SP($sp, $param)){
		
			if ($this->is_new_record()) {
				$cod_cotizacion = $db->GET_IDENTITY();
				$this->dws['dw_cotizacion']->set_item(0, 'COD_COTIZACION', $cod_cotizacion);
				/*
				VMC, 7-01-2011
				se elimina el envio de mail cuando se cotiza a un cliente no asignado 

				$this->envia_mail_acuse();
				*/
				
				//Se crea Seguimiento de Cotizacion.			
				$sp = 'spu_bitacora_cotizacion';
				$param = "'INICIA_SEGUIMIENTO'
						,NULL
						,$this->cod_usuario
						,$cod_cotizacion
						,NULL 
						,$cod_persona";
						
				if (!$db->EXECUTE_SP($sp, $param)) 
					return false;
					
				if($cod_cot_desde != 'null' && $cod_est_cot== 5){ 
					$sp = 'spu_cotizacion';
					$param = "'RE-ABRIR', $cod_cot_desde";
							
					if (!$db->EXECUTE_SP($sp, $param)) 
						return false;
				}
					
			}
			for ($i=0; $i<$this->dws['dw_item_cotizacion']->row_count(); $i++)
				$this->dws['dw_item_cotizacion']->set_item($i, 'COD_COTIZACION', $cod_cotizacion);

			if (!$this->dws['dw_item_cotizacion']->update($db))
				return false;
				
			$parametros_sp = "'item_cotizacion','cotizacion',$cod_cotizacion";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp))
				return false;

			$cod_llamado	= $this->dws['dw_llamado']->get_item(0, 'LL_COD_LLAMADO');
			$parametros_sp="'REALIZADO_WEB'
							,$cod_llamado
							,null
							,null
							,null
							,null
							,null
							,null
							,'S'
							,null
							,'COTIZACION'
							,$cod_cotizacion
							,$this->cod_usuario";
							
			if($cod_llamado <> ''){				
				if (!$db->EXECUTE_SP('spu_llamado', $parametros_sp)){
					return false;
				}else{
						$param="'INSERT',
								NULL,
								$cod_llamado,
								32, -- MH
								'realizado con exito',
								'S',
								'N'";	
						
						if (!$db->EXECUTE_SP('spu_llamado_conversa', $param))
							return false;		
				}
					
			}


			// bitacora
			/*for ($i=0; $i<$this->dws['dw_bitacora_cotizacion']->row_count(); $i++) 
				$this->dws['dw_bitacora_cotizacion']->set_item($i, 'BC_COD_COTIZACION', $cod_cotizacion);
	
			if (!$this->dws['dw_bitacora_cotizacion']->update($db)) return false;
			*/
			for ($i=0; $i<$this->dws['dw_seguimiento_cotizacion']->row_count(); $i++) 
				$this->dws['dw_seguimiento_cotizacion']->set_item($i, 'SC_COD_COTIZACION', $cod_cotizacion);
	
			if (!$this->dws['dw_seguimiento_cotizacion']->update($db)) 
				return false;
			
			
			$parametros_sp = "'RECALCULA',$cod_cotizacion";	
			if (!$db->EXECUTE_SP('spu_cotizacion', $parametros_sp))
				return false;
				
			return true;			
		}
		return false;
	}
	
	function print_record() {
		$os = base::get_tipo_dispositivo();
		if($os == 'IPAD' ){
            $sel_print_cot = 'resumen||pdf|logo|';
		}else{
			$sel_print_cot = $_POST['wi_hidden'];
		}
		
		$print_cot = explode("|", $sel_print_cot);
		
		//VARIABLE DE LA OPCION DE DESCUENTO
		session::set('PRINT_DESCUENTO', $print_cot[5]);
		
		switch ($print_cot[0]) {
    	case "resumen":
			if($print_cot[2] == 'pdf'){
				$this->printcot_resumen_pdf($print_cot[3] == 'logo');
			}else
				$this->printcot_resumen_excel();
       	break;
       	case "resumen_item":
			$this->print_resumen_item($print_cot[3] == 'logo', 'N');
       	break;
    	case "ampliada":
			if($print_cot[2] == 'pdf')
				$this->printcot_ampliada_pdf($print_cot[3] == 'logo');
			else
				$this->printcot_ampliada_excel($print_cot[3] == 'logo');
       break;
    	case "pesomedida":
			if($print_cot[2] == 'pdf')
				$this->printcot_pesomedida_pdf($print_cot[3] == 'logo', $print_cot[4]);
			else
				$this->printcot_pesomedida_excel('' , $print_cot[4]);
      	break;
      	case "cad":
			$this->printcot_cad();
      	break;
    	case "tecnica":
    		$lista_tecnica = explode("¬", $print_cot[1]);
    		$tope = count($lista_tecnica);
    		for ($i = 0; $i < $tope; $i++){
    			switch ($lista_tecnica[$i]) {
    				case "electrico":
    					if($print_cot[2] == 'pdf')
								$this->printcot_tecnica_electrico_pdf($print_cot[3] == 'logo');
							else
								$this->printcot_tecnica_electrico_excel($print_cot[3] == 'logo');
      				break;
    				case "gas":
    					if($print_cot[2] == 'pdf')
								$this->printcot_tecnica_gas_pdf($print_cot[3] == 'logo');
							else
								$this->printcot_tecnica_gas_excel($print_cot[3] == 'logo');
    					break;
    				case "vapor":
    				 	if($print_cot[2] == 'pdf')
								$this->printcot_tecnica_vapor_pdf($print_cot[3] == 'logo');
							else
								$this->printcot_tecnica_vapor_excel($print_cot[3] == 'logo');
    					break;
    				case "agua":
    					if($print_cot[2] == 'pdf')
								$this->printcot_tecnica_agua_pdf($print_cot[3] == 'logo');
							else
								$this->printcot_tecnica_agua_excel($print_cot[3] == 'logo');
    					break;
    				case "ventilacion":
    					if($print_cot[2] == 'pdf')
								$this->printcot_tecnica_ventilacion_pdf($print_cot[3] == 'logo');
							else
								$this->printcot_tecnica_ventilacion_excel($print_cot[3] == 'logo');
    					break;
    				case "desague":
    					if($print_cot[2] == 'pdf')
								$this->printcot_tecnica_desague_pdf($print_cot[3] == 'logo');
							else
								$this->printcot_tecnica_desague_excel($print_cot[3] == 'logo');
    					break;
    			}
    		}
        break;
        
		}
		//$this->_load_record();
		$this->redraw();
	}
	/*
	FUNCIONES PARA IMPRIMIR COTIZACIONES RESUMEN AMPLIADA PESO Y MEDIDA
	*/
	function print_resumen_item($con_logo, $print_especial){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$cod_cotizacion = $this->get_key();
		$sql = "SELECT COD_PRODUCTO
					   ,NOM_PRODUCTO
					   ,SUM(CANTIDAD) CANTIDAD
					   ,PRECIO
					   ,PRECIO * SUM(CANTIDAD) TOTAL,
					    C.COD_COTIZACION,
						E.NOM_EMPRESA,
						E.RUT,
						E.DIG_VERIF,
						dbo.f_format_date(C.FECHA_COTIZACION, 3) FECHA_COTIZACION,				
						dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[DIRECCION]') DIRECCION,
						dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[NOM_COMUNA]') NOM_COMUNA,
						dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[NOM_CIUDAD]') NOM_CIUDAD,
						S.TELEFONO TELEFONO_F,
						S.FAX FAX_F,
						C.REFERENCIA,
						P.NOM_PERSONA,
						P.EMAIL,
						P.TELEFONO,
						IC.NOM_PRODUCTO,
						C.SUBTOTAL,
						C.PORC_DSCTO1,
						C.MONTO_DSCTO1,
						C.PORC_DSCTO2,
						C.MONTO_DSCTO2,
						C.MONTO_DSCTO1 + C.MONTO_DSCTO2 FINAL,
						C.TOTAL_NETO,
						C.PORC_IVA,
						C.MONTO_IVA,
						C.TOTAL_CON_IVA,
						C.NOM_FORMA_PAGO_OTRO,
						FP.NOM_FORMA_PAGO,
						C.VALIDEZ_OFERTA,
						C.ENTREGA,
						EC.NOM_EMBALAJE_COTIZACION,
						FL.NOM_FLETE_COTIZACION,
						I.NOM_INSTALACION_COTIZACION,
						C.GARANTIA,
						M.SIMBOLO,
						U.NOM_USUARIO,
						U.MAIL MAIL_U,
						U.TELEFONO FONO_U,
						U.CELULAR CEL_U,
						U.INI_USUARIO,
					    dbo.f_get_parametro(6) NOM_EMPRESA_EMISOR,
						dbo.f_get_parametro(20) RUT_EMPRESA,
						dbo.f_get_parametro(10) DIR_EMPRESA,
						dbo.f_get_parametro(21) GIRO_EMPRESA,
						dbo.f_get_parametro(11) TEL_EMPRESA,
						dbo.f_get_parametro(61) BANCO,
						dbo.f_get_parametro(62) CTA_CTE,		
						dbo.f_get_parametro(12) FAX_EMPRESA,
						dbo.f_get_parametro(13) MAIL_EMPRESA,
						dbo.f_get_parametro(14) CIUDAD_EMPRESA,
						dbo.f_get_parametro(15) PAIS_EMPRESA,
						dbo.f_get_parametro(25) SITIO_WEB_EMPRESA,
						CONVERT(VARCHAR, OBS) OBS
				FROM ITEM_COTIZACION IC, COTIZACION C, EMPRESA E, SUCURSAL S, PERSONA P
					,FORMA_PAGO FP, EMBALAJE_COTIZACION EC, FLETE_COTIZACION FL
					,INSTALACION_COTIZACION I, MONEDA M, USUARIO U
				WHERE C.COD_COTIZACION = $cod_cotizacion
				AND C.COD_COTIZACION = IC.COD_COTIZACION
				AND E.COD_EMPRESA = C.COD_EMPRESA
				AND S.COD_SUCURSAL = C.COD_SUCURSAL_FACTURA
				AND P.COD_PERSONA = C.COD_PERSONA
				AND FP.COD_FORMA_PAGO = C.COD_FORMA_PAGO
				AND EC.COD_EMBALAJE_COTIZACION = C.COD_EMBALAJE_COTIZACION
				AND FL.COD_FLETE_COTIZACION = C.COD_FLETE_COTIZACION
				AND I.COD_INSTALACION_COTIZACION = C.COD_INSTALACION_COTIZACION
				AND M.COD_MONEDA = C.COD_MONEDA
				AND U.COD_USUARIO = C.COD_USUARIO_VENDEDOR1
				GROUP BY COD_PRODUCTO, NOM_PRODUCTO, PRECIO, NOM_EMPRESA,
				RUT, DIG_VERIF, C.COD_COTIZACION, FECHA_COTIZACION,
				C.COD_SUCURSAL_FACTURA, S.TELEFONO, S.FAX, C.REFERENCIA,
				NOM_PERSONA, EMAIL, P.TELEFONO, SUBTOTAL, PORC_DSCTO1,
				MONTO_DSCTO1, PORC_DSCTO2, MONTO_DSCTO2, TOTAL_NETO,
				PORC_IVA, MONTO_IVA, TOTAL_CON_IVA, NOM_FORMA_PAGO_OTRO,
				NOM_FORMA_PAGO, VALIDEZ_OFERTA, ENTREGA, NOM_EMBALAJE_COTIZACION,
				NOM_FLETE_COTIZACION, NOM_INSTALACION_COTIZACION, GARANTIA,
				SIMBOLO, NOM_USUARIO, U.MAIL, U.TELEFONO, U.CELULAR, U.INI_USUARIO, CONVERT(VARCHAR, OBS)";
				
		$result = $db->build_results($sql);
		$cod_producto_old = "";		
		for($i=0 ; $i < count($result) ; $i++){
			if($cod_producto_old == $result[$i]['COD_PRODUCTO']){
				$this->alert('Atención:\n\nHay items con el mismo modelo de equipo, pero con distinto precio de venta unitario.\nSe sugiere revisar.');
				break;
			}else
				$cod_producto_old = $result[$i]['COD_PRODUCTO'];
		}

		$labels = array();
		$labels['strCOD_COTIZACION'] = $cod_cotizacion;
		
		if($print_especial == 'S')
			$xml = $this->root_dir.'appl/cotizacion/cot_resumen_item_esp.xml';
		else
			$xml = $this->root_dir.'appl/cotizacion/cot_resumen_item.xml';

		$rpt= new reporte($sql, $xml, $labels, "Cotización Resumen item ".$cod_cotizacion.".pdf", $con_logo);		
	}
	
	function printcot_resumen_pdf($con_logo) {
		$cod_cotizacion = $this->get_key();
						
		$sql = "SELECT	C.COD_COTIZACION,
				E.NOM_EMPRESA,
				E.RUT,
				E.DIG_VERIF,
				dbo.f_format_date(C.FECHA_COTIZACION, 3) FECHA_COTIZACION,				
				dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[DIRECCION]') DIRECCION,
				dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[NOM_COMUNA]') NOM_COMUNA,
				dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[NOM_CIUDAD]') NOM_CIUDAD,
				SF.TELEFONO TELEFONO_F,
				SF.FAX FAX_F,
				C.REFERENCIA,
				P.NOM_PERSONA,
				P.EMAIL,
				p.TELEFONO,
				IC.NOM_PRODUCTO,
					case IC.COD_PRODUCTO
						when 'T' then ''
					else IC.ITEM
					end ITEM,
					case IC.COD_PRODUCTO
						when 'T' then null
						else IC.COD_PRODUCTO
					end COD_PRODUCTO,
					case IC.COD_PRODUCTO
						when 'T' then null
						else IC.CANTIDAD
					end CANTIDAD,
					case IC.COD_PRODUCTO
						when 'T' then null
						else IC.PRECIO
					end PRECIO,
					case IC.COD_PRODUCTO
						when 'T' then null
						else IC.CANTIDAD * IC.PRECIO
					end TOTAL,
				C.SUBTOTAL,
				C.PORC_DSCTO1,
				C.MONTO_DSCTO1,
				C.PORC_DSCTO2,
				C.MONTO_DSCTO2,
				C.MONTO_DSCTO1 + C.MONTO_DSCTO2 FINAL,
				C.TOTAL_NETO,
				C.PORC_IVA,
				C.MONTO_IVA,
				C.TOTAL_CON_IVA,
				C.NOM_FORMA_PAGO_OTRO,
				FP.NOM_FORMA_PAGO,
				C.VALIDEZ_OFERTA,
				C.ENTREGA,
				C.OBS,
				EC.NOM_EMBALAJE_COTIZACION,
				FL.NOM_FLETE_COTIZACION,
				I.NOM_INSTALACION_COTIZACION,
				C.GARANTIA,
				M.SIMBOLO,
				U.NOM_USUARIO,
				U.MAIL MAIL_U,
				U.TELEFONO FONO_U,
				U.CELULAR CEL_U,
				U.INI_USUARIO,
				dbo.f_get_parametro(".self::K_PARAM_NOM_EMPRESA.") NOM_EMPRESA_EMISOR,
				dbo.f_get_parametro(".self::K_PARAM_RUT_EMPRESA.") RUT_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_GIRO_EMPRESA.") GIRO_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_DIR_EMPRESA.") DIR_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_TEL_EMPRESA.") TEL_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_BANCO.") BANCO,
				dbo.f_get_parametro(".self::K_PARAM_CTA_CTE.") CTA_CTE,				
				dbo.f_get_parametro(".self::K_PARAM_FAX_EMPRESA.") FAX_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_MAIL_EMPRESA.") MAIL_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_CIUDAD_EMPRESA.") CIUDAD_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_PAIS_EMPRESA.") PAIS_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_SITIO_WEB_EMPRESA.") SITIO_WEB_EMPRESA
				FROM COTIZACION C, EMPRESA E, PERSONA P, ITEM_COTIZACION IC,FORMA_PAGO FP,
				 INSTALACION_COTIZACION I, FLETE_COTIZACION FL, EMBALAJE_COTIZACION EC,
				 MONEDA M, USUARIO U, SUCURSAL SF
				WHERE C.COD_COTIZACION = $cod_cotizacion AND 
				E.COD_EMPRESA = C.COD_EMPRESA AND
				P.COD_PERSONA = C.COD_PERSONA AND
				IC.COD_COTIZACION = C.COD_COTIZACION AND
				FP.COD_FORMA_PAGO = C.COD_FORMA_PAGO AND
				I.COD_INSTALACION_COTIZACION =C.COD_INSTALACION_COTIZACION AND
				FL.COD_FLETE_COTIZACION = C.COD_FLETE_COTIZACION AND
				EC.COD_EMBALAJE_COTIZACION = C.COD_EMBALAJE_COTIZACION AND	
				M.COD_MONEDA = C.COD_MONEDA AND
				U.COD_USUARIO = C.COD_USUARIO_VENDEDOR1 AND
				SF.COD_SUCURSAL = C.COD_SUCURSAL_FACTURA
				order by IC.ORDEN asc";
				

		// reporte
		$labels = array();
		$labels['strCOD_COTIZACION'] = $cod_cotizacion;
		$rpt= new reporte($sql, $this->root_dir.'appl/cotizacion/cot_resumen.xml', $labels, "Cotización Resumen ".$cod_cotizacion.".pdf", $con_logo);
	}
	function printcot_ampliada_pdf($con_logo) {
	$cod_cotizacion = $this->get_key();
	
	$sql = "SELECT			C.COD_COTIZACION,
				E.NOM_EMPRESA,
				E.RUT,
				E.DIG_VERIF,
				dbo.f_format_date(C.FECHA_COTIZACION, 3) FECHA_COTIZACION,				
				dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[DIRECCION]') DIRECCION,
				dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[NOM_COMUNA]') NOM_COMUNA,
				dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[NOM_CIUDAD]') NOM_CIUDAD,
				SF.TELEFONO TELEFONO_F,
				SF.FAX FAX_F,
				C.REFERENCIA,
				P.NOM_PERSONA,
				P.EMAIL,
				p.TELEFONO,
				IC.NOM_PRODUCTO,
				case IC.COD_PRODUCTO
					when 'T' then ''
					else IC.ITEM
				end ITEM,
				IC.COD_PRODUCTO COD_PRODUCTO_ORIGINAL,
				case IC.COD_PRODUCTO
					when 'T' then null
					else IC.COD_PRODUCTO
				end COD_PRODUCTO,
				case IC.COD_PRODUCTO
					when 'T' then null
					else IC.CANTIDAD
				end CANTIDAD,
				case IC.COD_PRODUCTO
					when 'T' then null
					else IC.PRECIO
				end PRECIO,
				case IC.COD_PRODUCTO
					when 'T' then null
					else IC.CANTIDAD * IC.PRECIO
				end TOTAL,
				C.SUBTOTAL,
				C.PORC_DSCTO1,
				C.MONTO_DSCTO1,
				C.PORC_DSCTO2,
				C.MONTO_DSCTO2,
				C.MONTO_DSCTO1 + C.MONTO_DSCTO2 FINAL,
				C.TOTAL_NETO,
				C.PORC_IVA,
				C.MONTO_IVA,
				C.TOTAL_CON_IVA,
				FP.NOM_FORMA_PAGO,
				C.VALIDEZ_OFERTA,
				C.ENTREGA,
				C.OBS,
				EC.NOM_EMBALAJE_COTIZACION,
				FL.NOM_FLETE_COTIZACION,
				I.NOM_INSTALACION_COTIZACION,
				C.GARANTIA,
				M.SIMBOLO,
				U.NOM_USUARIO,
				U.MAIL MAIL_U,
				U.TELEFONO FONO_U,
				U.CELULAR CEL_U,
				case dbo.f_prod_get_atributo(IC.COD_PRODUCTO) 
					when '' then IC.MOTIVO_TE 
					else convert(text, dbo.f_prod_get_atributo(IC.COD_PRODUCTO)) 
				end ATRIBUTO_PRODUCTO,
				dbo.f_get_parametro(".self::K_PARAM_NOM_EMPRESA.") NOM_EMPRESA_EMISOR,
				dbo.f_get_parametro(".self::K_PARAM_RUT_EMPRESA.") RUT_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_DIR_EMPRESA.") DIR_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_GIRO_EMPRESA.") GIRO_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_TEL_EMPRESA.") TEL_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_BANCO.") BANCO,
				dbo.f_get_parametro(".self::K_PARAM_CTA_CTE.") CTA_CTE,
				dbo.f_get_parametro(".self::K_PARAM_FAX_EMPRESA.") FAX_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_MAIL_EMPRESA.") MAIL_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_CIUDAD_EMPRESA.") CIUDAD_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_PAIS_EMPRESA.") PAIS_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_SITIO_WEB_EMPRESA.") SITIO_WEB_EMPRESA
		FROM COTIZACION C, EMPRESA E, PERSONA P, ITEM_COTIZACION IC,FORMA_PAGO FP,
					 INSTALACION_COTIZACION I, FLETE_COTIZACION FL, EMBALAJE_COTIZACION EC,
					 MONEDA M, USUARIO U, SUCURSAL SF
		WHERE C.COD_COTIZACION = $cod_cotizacion AND 
						E.COD_EMPRESA = C.COD_EMPRESA AND
						P.COD_PERSONA = C.COD_PERSONA AND
						IC.COD_COTIZACION = C.COD_COTIZACION AND
						FP.COD_FORMA_PAGO = C.COD_FORMA_PAGO AND
						I.COD_INSTALACION_COTIZACION =C.COD_INSTALACION_COTIZACION AND
						FL.COD_FLETE_COTIZACION = C.COD_FLETE_COTIZACION AND
						EC.COD_EMBALAJE_COTIZACION = C.COD_EMBALAJE_COTIZACION AND	
						M.COD_MONEDA = C.COD_MONEDA AND
						U.COD_USUARIO = C.COD_USUARIO_VENDEDOR1 AND
						SF.COD_SUCURSAL = C.COD_SUCURSAL_FACTURA
						order by IC.ORDEN asc";
		//reporte
		$labels = array();
		$labels['strCOD_COTIZACION'] = $cod_cotizacion;
		$rpt= new reporte($sql, $this->root_dir.'appl/cotizacion/cot_ampliado.xml', $labels, "Cotización Ampliada ".$cod_cotizacion.".pdf", $con_logo);
	}
	
	function printcot_pesomedida_pdf($con_logo, $embalada) {
	$cod_cotizacion = $this->get_key();	
		
	$sql= "SELECT C.COD_COTIZACION,
				E.NOM_EMPRESA,
				E.RUT,
				E.DIG_VERIF,
				dbo.f_format_date(C.FECHA_COTIZACION, 3) FECHA_COTIZACION,				
				dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[DIRECCION]') DIRECCION,
				dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[NOM_COMUNA]') NOM_COMUNA,
				dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[NOM_CIUDAD]') NOM_CIUDAD,
				SF.TELEFONO TELEFONO_F,
				SF.FAX FAX_F,
				C.REFERENCIA,
				P.NOM_PERSONA,
				P.EMAIL,
				p.TELEFONO,
				IC.NOM_PRODUCTO,
				case IC.COD_PRODUCTO
					when 'T' then ''
					else IC.ITEM
				end ITEM,
				case IC.COD_PRODUCTO
					when 'T' then null
					else IC.COD_PRODUCTO
				end COD_PRODUCTO,
				case IC.COD_PRODUCTO
					when 'T' then null
					else IC.CANTIDAD
				end CANTIDAD, ";

		if($embalada == 'noembalada'){
			$sql.= "case PR.COD_PRODUCTO
						when 'T' then null
						else PR.LARGO
					end LARGO,
					case PR.COD_PRODUCTO
						when 'T' then null
						else PR.ANCHO
					end ANCHO,
					case PR.COD_PRODUCTO
						when 'T' then null
						else PR.ALTO
					end ALTO,
					case PR.COD_PRODUCTO
						when 'T' then null
						else PR.PESO
					end PESO,
					case PR.COD_PRODUCTO
						when 'T' then null
						else ((PR.LARGO)*(PR.ANCHO)*(PR.ALTO))/1000000 
					end VOLUMEN,
					case PR.COD_PRODUCTO
						when 'T' then null
						else IC.CANTIDAD * (((PR.LARGO)*(PR.ANCHO)*(PR.ALTO))/1000000)
					end VOLT,
					case PR.COD_PRODUCTO
						when 'T' then null
						else IC.CANTIDAD * PR.PESO
					end PESOT,
					'Especificaciones Equipo sin Embalaje' TITLE_ITEM, ";
		}else{
			$sql.= "case PR.COD_PRODUCTO
						when 'T' then null
						else PR.LARGO_EMBALADO
					end LARGO,
					case PR.COD_PRODUCTO
						when 'T' then null
						else PR.ANCHO_EMBALADO
					end ANCHO,
					case PR.COD_PRODUCTO
						when 'T' then null
						else PR.ALTO_EMBALADO
					end ALTO,
					case PR.COD_PRODUCTO
						when 'T' then null
						else PR.PESO_EMBALADO
					end PESO,
					case PR.COD_PRODUCTO
						when 'T' then null
						else ((PR.LARGO_EMBALADO)*(PR.ANCHO_EMBALADO)*(PR.ALTO_EMBALADO))/1000000 
					end VOLUMEN,
					case PR.COD_PRODUCTO
						when 'T' then null
						else IC.CANTIDAD * (((PR.LARGO_EMBALADO)*(PR.ANCHO_EMBALADO)*(PR.ALTO_EMBALADO))/1000000)
					end VOLT,
					case PR.COD_PRODUCTO
						when 'T' then null
						else IC.CANTIDAD * PR.PESO_EMBALADO
					end PESOT,
					'Especificaciones Equipo con Embalaje' TITLE_ITEM, ";
		}
					
		$sql.= "U. NOM_USUARIO,
				U.MAIL MAIL_U,
				U.TELEFONO FONO_U,
				U.CELULAR CEL_U,
				dbo.f_get_parametro(".self::K_PARAM_NOM_EMPRESA.") NOM_EMPRESA_EMISOR,
				dbo.f_get_parametro(".self::K_PARAM_RUT_EMPRESA.") RUT_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_DIR_EMPRESA.") DIR_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_GIRO_EMPRESA.") GIRO_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_TEL_EMPRESA.") TEL_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_BANCO.") BANCO,
				dbo.f_get_parametro(".self::K_PARAM_CTA_CTE.") CTA_CTE,
				dbo.f_get_parametro(".self::K_PARAM_FAX_EMPRESA.") FAX_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_MAIL_EMPRESA.") MAIL_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_CIUDAD_EMPRESA.") CIUDAD_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_PAIS_EMPRESA.") PAIS_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_SITIO_WEB_EMPRESA.") SITIO_WEB_EMPRESA
		FROM COTIZACION C, EMPRESA E, PERSONA P,
				ITEM_COTIZACION IC, USUARIO U, PRODUCTO PR,
				SUCURSAL SF, SUCURSAL SD
		WHERE C.COD_COTIZACION = $cod_cotizacion AND 
				E.COD_EMPRESA = C.COD_EMPRESA AND
				P.COD_PERSONA = C.COD_PERSONA AND
				IC.COD_COTIZACION = C.COD_COTIZACION AND
				U.COD_USUARIO = C.COD_USUARIO_VENDEDOR1 and
				SF.COD_SUCURSAL = C.COD_SUCURSAL_FACTURA AND						
				SD.COD_SUCURSAL = C.COD_SUCURSAL_DESPACHO AND
		    	PR.COD_PRODUCTO = IC.COD_PRODUCTO 
				order by IC.ORDEN asc";
				
		$labels = array();
		$labels['strCOD_COTIZACION'] = $cod_cotizacion;
		$rpt= new reporte($sql, $this->root_dir.'appl/cotizacion/pesos_medidas.xml', $labels, "Cotización Peso y Medida ".$cod_cotizacion.".pdf", $con_logo);				
	}
	
	function printcot_cad(){
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");
		
		$fname = tempnam("/tmp", "cotizacion_cad.xls");
		$workbook = &new writeexcel_workbook($fname);
		$cod_cotizacion = $this->get_key();
		$worksheet = &$workbook->addworksheet('COTIZACION_'.$cod_cotizacion);
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT COD_ITEM_COTIZACION
					  ,COD_COTIZACION
					  ,ORDEN
					  ,ITEM
					  ,COD_PRODUCTO
					  ,NOM_PRODUCTO
					  ,CANTIDAD
				FROM ITEM_COTIZACION
				WHERE COD_COTIZACION = $cod_cotizacion
				ORDER BY ORDEN";
		$res = $db->query($sql);
		
		$worksheet->set_column(0, 4, 15);
		$worksheet->set_column(5, 5, 30);
		$worksheet->set_column(6, 6, 15);
	
		$text =& $workbook->addformat();
		$text->set_font("Verdana");
		$text->set_valign('vcenter');
		
		$text_border_all =& $workbook->addformat();
		$text_border_all->copy($text);
		$text_border_all->set_border_color('black');
		$text_border_all->set_top(1);
		$text_border_all->set_bottom(1);
		$text_border_all->set_right(1);
		$text_border_all->set_left(1);
		
		$worksheet->write(3, 0, "COD_ITEM_COTIZACION", $text_border_all);
		$worksheet->write(3, 1, "COD_COTIZACION", $text_border_all);
		$worksheet->write(3, 2, "ORDEN", $text_border_all);
		$worksheet->write(3, 3, "ITEM", $text_border_all);
		$worksheet->write(3, 4, "COD_PRODUCTO", $text_border_all);
		$worksheet->write(3, 5, "NOM_PRODUCTO", $text_border_all);
		$worksheet->write(3, 6, "CANTIDAD", $text_border_all);
		
		$l = 0;
		while($my_row = $db->get_row()){
			$worksheet->write(4+$l, 0, $my_row['COD_ITEM_COTIZACION'], $text_border_all);
			$worksheet->write(4+$l, 1, $my_row['COD_COTIZACION'], $text_border_all);
			$worksheet->write(4+$l, 2, $my_row['ORDEN'], $text_border_all);
			$worksheet->write(4+$l, 3, $my_row['ITEM'], $text_border_all);
			$worksheet->write(4+$l, 4, $my_row['COD_PRODUCTO'], $text_border_all);
			$worksheet->write(4+$l, 5, $my_row['NOM_PRODUCTO'], $text_border_all);
			$worksheet->write(4+$l, 6, $my_row['CANTIDAD'], $text_border_all);
			$l++;
		}
		
		$workbook->close();
		
		header("Content-Type: application/x-msexcel; name=\"Cotizacion Resumen $COD_COTIZACION.xls\"");
		header("Content-Disposition: inline; filename=\"Cotizacion Resumen $COD_COTIZACION.xls\"");
		$fh=fopen($fname, "rb");
		fpassthru($fh);
		unlink($fname);
	}
	/*
	FUNCIONES PARA IMPRIMIR COTIZACIONES LISTA TECNICA
	*/
	function printcot_tecnica_electrico_pdf($con_logo) {
		$cod_cotizacion = $this->get_key();
		$sql = "exec spr_cot_tecnica $cod_cotizacion, 'ELECTRICIDAD'";
		//reporte
		$labels = array();
		$labels['strCOD_COTIZACION'] = $cod_cotizacion;
		$rpt = new reporte($sql, $this->root_dir.'appl/cotizacion/list_elect.xml', $labels, "Cotización Lista Eléctrica ".$cod_cotizacion.".pdf", $con_logo);
	}
	function printcot_tecnica_gas_pdf($con_logo) {
		$cod_cotizacion = $this->get_key();
		
		$sql = "exec spr_cot_tecnica $cod_cotizacion, 'GAS'";
		//reporte
		$labels = array();
		$labels['strCOD_COTIZACION'] = $cod_cotizacion;
		$rpt= new reporte($sql, $this->root_dir.'appl/cotizacion/list_gas.xml', $labels, "Cotización Lista Gas ".$cod_cotizacion.".pdf", $con_logo);						
		
	}
	function printcot_tecnica_vapor_pdf($con_logo) {
		$cod_cotizacion = $this->get_key();
		
		$sql = "exec spr_cot_tecnica $cod_cotizacion, 'VAPOR'";
		//reporte
		$labels = array();
		$labels['strCOD_COTIZACION'] = $cod_cotizacion;
		$rpt= new reporte($sql, $this->root_dir.'appl/cotizacion/list_vapor.xml', $labels, "Cotización Lista Vapor ".$cod_cotizacion.".pdf", $con_logo);						
		
	}
	function printcot_tecnica_agua_pdf($con_logo) {
		$cod_cotizacion = $this->get_key();
		
		$sql = "exec spr_cot_tecnica $cod_cotizacion, 'AGUA'";
		//reporte
		$labels = array();
		$labels['strCOD_COTIZACION'] = $cod_cotizacion;
		$rpt= new reporte($sql, $this->root_dir.'appl/cotizacion/list_agua.xml', $labels, "Cotización Lista Agua ".$cod_cotizacion.".pdf", $con_logo);						
	}
	function printcot_tecnica_ventilacion_pdf($con_logo) {
		$cod_cotizacion = $this->get_key();
		
		$sql = "exec spr_cot_tecnica $cod_cotizacion, 'VENTILACION'";
		//reporte
		$labels = array();
		$labels['strCOD_COTIZACION'] = $cod_cotizacion;
		$rpt= new reporte($sql, $this->root_dir.'appl/cotizacion/list_ventilacion.xml', $labels, "Cotización Lista Ventilación ".$cod_cotizacion.".pdf", $con_logo);
	}
	function printcot_tecnica_desague_pdf($con_logo) {
		$cod_cotizacion = $this->get_key();
		
		$sql = "exec spr_cot_tecnica $cod_cotizacion, 'DESAGUE'";
		//reporte
		$labels = array();
		$labels['strCOD_COTIZACION'] = $cod_cotizacion;
		$rpt= new reporte($sql, $this->root_dir.'appl/cotizacion/list_desague.xml', $labels, "Cotización Lista Desague ".$cod_cotizacion.".pdf", $con_logo);
	}	
	// EXCEL
	function printcot_resumen_excel() {
		
		$print_descto = session::get('PRINT_DESCUENTO');
		error_reporting(E_ALL & ~E_NOTICE);
		
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");
		
		$fname = tempnam("/tmp", "resumen.xls");
		$workbook = &new writeexcel_workbook($fname);
		$cod_cotizacion = $this->get_key();
		$worksheet = &$workbook->addworksheet('COTIZACION_'.$cod_cotizacion);
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT	C.COD_COTIZACION,
				E.NOM_EMPRESA,
				E.RUT,
				E.DIG_VERIF,
				dbo.f_format_date(getdate(), 3) FECHA_IMPRESO,
				dbo.f_format_date(C.FECHA_COTIZACION, 3) FECHA_COTIZACION,				
				dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[DIRECCION]') DIRECCION,
				dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[NOM_COMUNA]') NOM_COMUNA,
				dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[NOM_CIUDAD]') NOM_CIUDAD,
				SF.TELEFONO TELEFONO_F,
				SF.FAX FAX_F,
				C.REFERENCIA,
				P.NOM_PERSONA,
				P.EMAIL,
				p.TELEFONO,
				IC.NOM_PRODUCTO,
					case IC.COD_PRODUCTO
						when 'T' then ''
					else IC.ITEM
					end ITEM,
					case IC.COD_PRODUCTO
						when 'T' then null
						else IC.COD_PRODUCTO
					end COD_PRODUCTO,
					case IC.COD_PRODUCTO
						when 'T' then null
						else IC.CANTIDAD
					end CANTIDAD,
					case IC.COD_PRODUCTO
						when 'T' then null
						else IC.PRECIO
					end PRECIO,
					case IC.COD_PRODUCTO
						when 'T' then null
						else IC.CANTIDAD * IC.PRECIO
					end TOTAL,
				C.SUBTOTAL,
				C.PORC_DSCTO1,
				C.MONTO_DSCTO1,
				C.PORC_DSCTO2,
				C.MONTO_DSCTO2,
				C.MONTO_DSCTO1 + C.MONTO_DSCTO2 FINAL,
				C.TOTAL_NETO,
				C.PORC_IVA,
				C.MONTO_IVA,
				C.TOTAL_CON_IVA,
				FP.NOM_FORMA_PAGO,
				C.VALIDEZ_OFERTA,
				C.ENTREGA,
				C.OBS,
				EC.NOM_EMBALAJE_COTIZACION,
				FL.NOM_FLETE_COTIZACION,
				I.NOM_INSTALACION_COTIZACION,
				C.GARANTIA,
				M.SIMBOLO,
				U.NOM_USUARIO,
				U.MAIL MAIL_U,
				U.TELEFONO FONO_U,
				U.CELULAR CEL_U,
				dbo.f_get_parametro(".self::K_PARAM_NOM_EMPRESA.") NOM_EMPRESA_EMISOR,
				dbo.f_get_parametro(".self::K_PARAM_RUT_EMPRESA.") RUT_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_DIR_EMPRESA.") DIR_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_TEL_EMPRESA.") TEL_EMPRESA,	
				dbo.f_get_parametro(".self::K_PARAM_FAX_EMPRESA.") FAX_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_MAIL_EMPRESA.") MAIL_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_CIUDAD_EMPRESA.") CIUDAD_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_PAIS_EMPRESA.") PAIS_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_SITIO_WEB_EMPRESA.") SITIO_WEB_EMPRESA
			FROM COTIZACION C, EMPRESA E, PERSONA P, ITEM_COTIZACION IC,FORMA_PAGO FP,
				 INSTALACION_COTIZACION I, FLETE_COTIZACION FL, EMBALAJE_COTIZACION EC,
				 MONEDA M, USUARIO U, SUCURSAL SF
			WHERE C.COD_COTIZACION = $cod_cotizacion AND 
				E.COD_EMPRESA = C.COD_EMPRESA AND
				P.COD_PERSONA = C.COD_PERSONA AND
				IC.COD_COTIZACION = C.COD_COTIZACION AND
				FP.COD_FORMA_PAGO = C.COD_FORMA_PAGO AND
				I.COD_INSTALACION_COTIZACION =C.COD_INSTALACION_COTIZACION AND
				FL.COD_FLETE_COTIZACION = C.COD_FLETE_COTIZACION AND
				EC.COD_EMBALAJE_COTIZACION = C.COD_EMBALAJE_COTIZACION AND	
				M.COD_MONEDA = C.COD_MONEDA AND
				U.COD_USUARIO = C.COD_USUARIO_VENDEDOR1 AND
				SF.COD_SUCURSAL = C.COD_SUCURSAL_FACTURA
				order by IC.ORDEN asc";
				
		$result = $db->build_results($sql);
		
		$worksheet->set_row(0, 60);
		$worksheet->set_column(0, 0, 4);
		$worksheet->set_column(1, 2, 7);
		$worksheet->set_column(3, 9, 14);
		$worksheet->insert_bitmap('B1',$this->root_dir."images_appl/logo_reporte_excel.bmp");
		
	
		$text =& $workbook->addformat();
		$text->set_font("Verdana");
		$text->set_valign('vcenter');
    
		$text_bold =& $workbook->addformat();
		$text_bold->copy($text);
		$text_bold->set_bold(1);
	
		$text_blue_bold_left =& $workbook->addformat();
		$text_blue_bold_left->copy($text_bold);
		$text_blue_bold_left->set_align('left');
		$text_blue_bold_left->set_color('blue_0x20');

		$text_blue_bold_center =& $workbook->addformat();
		$text_blue_bold_center->copy($text_bold);
		$text_blue_bold_center->set_align('center');
		$text_blue_bold_center->set_color('blue_0x20');
		
		$text_blue_bold_right =& $workbook->addformat();
		$text_blue_bold_right->copy($text_bold);
		$text_blue_bold_right->set_align('right');
		$text_blue_bold_right->set_color('blue_0x20');

		$text_nro_docto =& $workbook->addformat();
		$text_nro_docto->copy($text_blue_bold_right);
		$text_nro_docto->set_size(13);
		
		$text_pie_de_pagina =& $workbook->addformat();
		$text_pie_de_pagina->copy($text_blue_bold_left);
		$text_pie_de_pagina->set_size(8);
		
		$text_normal_left =& $workbook->addformat();
		$text_normal_left->copy($text);
		$text_normal_left->set_align('left');
		
		$text_normal_justify =& $workbook->addformat();
		$text_normal_justify->copy($text);
		$text_normal_justify->set_align('justify');
		
		$text_normal_center =& $workbook->addformat();
		$text_normal_center->copy($text);
		$text_normal_center->set_align('center');
		
		$text_normal_right =& $workbook->addformat();
		$text_normal_right->copy($text);
		$text_normal_right->set_align('right');
				
		$text_normal_bold_left =& $workbook->addformat();
		$text_normal_bold_left->copy($text_bold);
		$text_normal_bold_left->set_align('left');
		
		
		$text_normal_bold_center =& $workbook->addformat();
		$text_normal_bold_center->copy($text_bold);
		$text_normal_bold_center->set_align('center');
	
		$text_normal_bold_right =& $workbook->addformat();
		$text_normal_bold_right->copy($text_bold);
		$text_normal_bold_right->set_align('right');
	
		
		$titulo_item_border_all =& $workbook->addformat();
		$titulo_item_border_all->copy($text_blue_bold_center);
		$titulo_item_border_all->set_border_color('black');
		$titulo_item_border_all->set_top(2);
		$titulo_item_border_all->set_bottom(2);
		$titulo_item_border_all->set_right(2);
		$titulo_item_border_all->set_left(2);
		
		$titulo_item_border_all_merge =& $workbook->addformat();
		$titulo_item_border_all_merge->copy($titulo_item_border_all);
		$titulo_item_border_all_merge->set_merge();
				
	
		$border_item_left = & $workbook->addformat();
		$border_item_left->copy($text_normal_left);
		$border_item_left->set_border_color('black');
		$border_item_left->set_left(2);
		
		$border_item_left_bold = & $workbook->addformat();
		$border_item_left_bold->copy($text_bold);
		$border_item_left_bold->set_border_color('black');
		$border_item_left_bold->set_left(2);
		
		$border_item_center = & $workbook->addformat();
		$border_item_center->copy($text_normal_center);
		$border_item_center->set_border_color('black');
		$border_item_center->set_left(2);
		$border_item_center->set_right(2);
		
		$border_item_right = & $workbook->addformat();
		$border_item_right->copy($text_normal_right);
		$border_item_right->set_border_color('black');
		$border_item_right->set_right(2);		
		
		$cant_normal =& $workbook->addformat();
		$cant_normal->copy($border_item_right);
		$cant_normal->set_num_format('0.0');
					
		$monto_normal =& $workbook->addformat();
		$monto_normal->copy($border_item_right);
		$monto_normal->set_num_format('#,##0');
		
		$border_item_top = & $workbook->addformat();
		$border_item_top->copy($text);
		$border_item_top->set_border_color('black');
		$border_item_top->set_top(2);
		
		$border_item_bottom = & $workbook->addformat();
		$border_item_bottom->copy($text);
		$border_item_bottom->set_border_color('black');
		$border_item_bottom->set_bottom(2);
		
		$border_item_especial_left = & $workbook->addformat();
		$border_item_especial_left->copy($text_normal_left);
		$border_item_especial_left->set_border_color('black');
		$border_item_especial_left->set_left(2);
		$border_item_especial_left->set_right(2);
	
		$COD_COTIZACION = $result[0]['COD_COTIZACION'];
		$FECHA_COTIZACION = $result[0]['FECHA_COTIZACION'];
		$FECHA_IMPRESO = $result[0]['FECHA_IMPRESO'];
		$NOM_EMPRESA = $result[0]['NOM_EMPRESA'];
		$RUT = $result[0]['RUT'];
		$DIG_VERIF = $result[0]['DIG_VERIF'];
		$DIRECCION = $result[0]['DIRECCION'];
		$NOM_COMUNA = $result[0]['NOM_COMUNA'];
		$NOM_CIUDAD = $result[0]['NOM_CIUDAD'];
		$TELEFONO_F = $result[0]['TELEFONO_F'];
		$FAX_F = $result[0]['FAX_F'];	
		$NOM_PERSONA = $result[0]['NOM_PERSONA'];
		$EMAIL = $result[0]['EMAIL'];
		$REFERENCIA = $result[0]['REFERENCIA'];
		$SIMBOLO = $result[0]['SIMBOLO'];
		$SUBTOTAL = $result[0]['SUBTOTAL'];
		$PORC_DSCTO1 = $result[0]['PORC_DSCTO1'];
		$MONTO_DSCTO1 = $result[0]['MONTO_DSCTO1'];
		$PORC_DSCTO2 = $result[0]['PORC_DSCTO2'];
		$MONTO_DSCTO2 = $result[0]['MONTO_DSCTO2'];
		$TOTAL_NETO = $result[0]['TOTAL_NETO'];
		$PORC_IVA = $result[0]['PORC_IVA'];
		$MONTO_IVA = $result[0]['MONTO_IVA'];
		$TOTAL_CON_IVA = $result[0]['TOTAL_CON_IVA'];
	
		$NOM_FORMA_PAGO = $result[0]['NOM_FORMA_PAGO'];
		$VALIDEZ_OFERTA = $result[0]['VALIDEZ_OFERTA'];
		$ENTREGA = $result[0]['ENTREGA'];
		$NOM_EMBALAJE_COTIZACION = $result[0]['NOM_EMBALAJE_COTIZACION'];
		$NOM_FLETE_COTIZACION = $result[0]['NOM_FLETE_COTIZACION'];
		$NOM_INSTALACION_COTIZACION = $result[0]['NOM_INSTALACION_COTIZACION'];
		$GARANTIA = $result[0]['GARANTIA'];
		$OBS = $result[0]['OBS'];		
		$NOM_USUARIO = $result[0]['NOM_USUARIO'];
		$MAIL_U = $result[0]['MAIL_U'];
		$FONO_U = $result[0]['FONO_U'];
		$CEL_U = $result[0]['CEL_U'];
		
	
		$NOM_EMPRESA_EMISOR = $result[0]['NOM_EMPRESA_EMISOR'];
		$RUT_EMPRESA = $result[0]['RUT_EMPRESA'];
		$DIR_EMPRESA = $result[0]['DIR_EMPRESA'];
		$CIUDAD_EMPRESA = $result[0]['CIUDAD_EMPRESA'];
		$PAIS_EMPRESA = $result[0]['PAIS_EMPRESA'];
		$TEL_EMPRESA = $result[0]['TEL_EMPRESA'];
		$FAX_EMPRESA = $result[0]['FAX_EMPRESA'];
		$MAIL_EMPRESA = $result[0]['MAIL_EMPRESA'];
		$SITIO_WEB_EMPRESA = $result[0]['SITIO_WEB_EMPRESA'];
		
		$FINAL = $result[0]['FINAL'];
		
		$worksheet->write(1, 9, "COTIZACION Nº".$COD_COTIZACION, $text_nro_docto);
		$worksheet->write(1, 1, "Santiago,".$FECHA_COTIZACION, $text_blue_bold_left);
		$worksheet->write(3, 1, "Razón Social", $text_blue_bold_left);
		
		$worksheet->write(3, 3, $NOM_EMPRESA, $text_normal_bold_left);
		$worksheet->write(3, 8, "Rut", $text_blue_bold_left);
		
		$rut=number_format($RUT, 0, ',', '.');
		$rut=$rut.'-'.$DIG_VERIF;
		
		$worksheet->write(3, 9, $rut, $text_normal_bold_left);
		
		$worksheet->write(4, 1, "Dirección", $text_blue_bold_left);
		$worksheet->write(4, 3, $DIRECCION, $text_normal_left);
		$worksheet->write(5, 1, "Comuna", $text_blue_bold_left);
		$worksheet->write(5, 3, $NOM_COMUNA, $text_normal_left);
		$worksheet->write(5, 4, "Ciudad", $text_blue_bold_left);
		$worksheet->write(5, 5, $NOM_CIUDAD, $text_normal_left);
		$worksheet->write(5, 6, "Fono", $text_blue_bold_left);
		$worksheet->write(5, 7, $TELEFONO_F, $text_normal_left);
		$worksheet->write(5, 8, "Fax",$text_blue_bold_left);
		$worksheet->write(5, 9, $FAX_F,$text_normal_left);
		$worksheet->write(6, 1, "Atención", $text_blue_bold_left);
		$worksheet->write(6, 3, $NOM_PERSONA." ".$EMAIL, $text_normal_left);
		$worksheet->write(7, 1, "Referencia",$text_blue_bold_left);
		$worksheet->write(7, 3, $REFERENCIA,$text_normal_left);
		
		$worksheet->write(9, 1, "Ítem", $titulo_item_border_all);
		$worksheet->write(9, 2, "", $titulo_item_border_all);
		$worksheet->write(9, 3, "                                Producto                                ", $titulo_item_border_all_merge);
		$worksheet->write(9, 4, "", $titulo_item_border_all);
		$worksheet->write(9, 5, "", $titulo_item_border_all);
		$worksheet->write(9, 6, "Modelo", $titulo_item_border_all);
		$worksheet->write(9, 7, "Cantidad", $titulo_item_border_all);
		$worksheet->write(9, 8, "Precio ".$SIMBOLO, $titulo_item_border_all);
		
		$margen = 0;
		//echo '$print_descto='.$print_descto;
		if($print_descto == 'item'){
			if($PORC_DSCTO1 <> 0 && $PORC_DSCTO2 == 0){
				//ENTRA ACA
				$margen += 1;
				$worksheet->set_column(10, 10, 14);
				$worksheet->write(9, 9, "Desc. %".$PORC_DSCTO1, $titulo_item_border_all);
			}else if($PORC_DSCTO1 == 0 && $PORC_DSCTO2 <> 0){
				$margen += 1;
				$worksheet->set_column(10, 10, 14);
				$worksheet->write(9, 9, "Desc. Adic. %".$PORC_DSCTO2, $titulo_item_border_all);
			}else if($PORC_DSCTO1 <> 0 && $PORC_DSCTO2 <> 0){
				$margen += 2;
				$worksheet->set_column(10, 10, 19);
				$worksheet->set_column(11, 11, 14);
				$worksheet->write(9, 9, "Desc. %".$PORC_DSCTO1, $titulo_item_border_all);
				$worksheet->write(9, 10, "Desc. Adic. %".$PORC_DSCTO2, $titulo_item_border_all);
			}
			$worksheet->write(9, $margen+9, "Total ".$SIMBOLO, $titulo_item_border_all);
		}else{
			$margen = 1;
			$worksheet->write(9, $margen+8, "Total ".$SIMBOLO, $titulo_item_border_all);
		}
 	
		for ($i=0 ; $i <count($result); $i++) {
			$ITEM = $result[$i]['ITEM'];
			$NOM_PRODUCTO = $result[$i]['NOM_PRODUCTO'];
			$COD_PRODUCTO = $result[$i]['COD_PRODUCTO'];
			$CANTIDAD = $result[$i]['CANTIDAD'];
			$PRECIO = $result[$i]['PRECIO'];
			if($print_descto == 'item'){
					if($PORC_DSCTO1 <> 0 && $PORC_DSCTO2 == 0){
						// ENTRA ACA TOTAL 133861 | $i=0
						$monto_descuento1 = $PRECIO * ($PORC_DSCTO1/100);
						$worksheet->write(10+$i, 9, $monto_descuento1, $monto_normal);
						$TOTAL = ($PRECIO - $monto_descuento1) * $CANTIDAD;
					}else if($PORC_DSCTO1 == 0 && $PORC_DSCTO2 <> 0){
						$monto_descuento2 = $PRECIO * ($PORC_DSCTO2/100);
						$worksheet->write(10+$i, 9, $monto_descuento2, $monto_normal);
						$TOTAL = ($PRECIO - $monto_descuento2) * $CANTIDAD;
					}else if($PORC_DSCTO1 <> 0 && $PORC_DSCTO2 <> 0){
						$monto_descuento1 = $PRECIO * ($PORC_DSCTO1/100);
						$worksheet->write(10+$i, 9, $monto_descuento1, $monto_normal);
						$monto_descuento2 = ($PRECIO - $monto_descuento1) * ($PORC_DSCTO2/100);
						$worksheet->write(10+$i, 10, $monto_descuento2, $monto_normal);
						$TOTAL = ($PRECIO - $monto_descuento1 - $monto_descuento2) * $CANTIDAD;
					}else	
						$TOTAL = $result[$i]['TOTAL'];
					
		
					$worksheet->write(10+$i, 1, $ITEM, $border_item_left);
					
					if($COD_PRODUCTO == '')
						$worksheet->write(10+$i, 2, $NOM_PRODUCTO, $border_item_left_bold);
					else{
						$worksheet->write(10+$i, 2, $NOM_PRODUCTO, $border_item_left);
					}
					$worksheet->write(10+$i, 6, $COD_PRODUCTO, $border_item_especial_left);
					$worksheet->write(10+$i, 7, $CANTIDAD, $cant_normal);
					$worksheet->write(10+$i, 8, $PRECIO, $monto_normal);
					$worksheet->write(10+$i, 9+$margen, $TOTAL, $monto_normal);
					
			}else{
					if($PORC_DSCTO1 <> 0 && $PORC_DSCTO2 == 0){
						// ENTRA ACA TOTAL 133861 | $i=0
						$monto_descuento1 = $PRECIO * ($PORC_DSCTO1/100);
						//$worksheet->write(10+$i, 9, $monto_descuento1, $monto_normal);
						$TOTAL = ($PRECIO - $monto_descuento1) * $CANTIDAD;
					}else if($PORC_DSCTO1 == 0 && $PORC_DSCTO2 <> 0){
						$monto_descuento2 = $PRECIO * ($PORC_DSCTO2/100);
						//$worksheet->write(10+$i, 9, $monto_descuento2, $monto_normal);
						$TOTAL = ($PRECIO - $monto_descuento2) * $CANTIDAD;
					}else if($PORC_DSCTO1 <> 0 && $PORC_DSCTO2 <> 0){
						$monto_descuento1 = $PRECIO * ($PORC_DSCTO1/100);
						//$worksheet->write(10+$i, 9, $monto_descuento1, $monto_normal);
						$monto_descuento2 = ($PRECIO - $monto_descuento1) * ($PORC_DSCTO2/100);
						//$worksheet->write(10+$i, 10, $monto_descuento2, $monto_normal);
						$TOTAL = ($PRECIO - $monto_descuento1 - $monto_descuento2) * $CANTIDAD;
					}else	
						$TOTAL = $result[$i]['TOTAL'];
					
		
					$worksheet->write(10+$i, 1, $ITEM, $border_item_left);
					
					if($COD_PRODUCTO == '')
						$worksheet->write(10+$i, 2, $NOM_PRODUCTO, $border_item_left_bold);
					else{
						$worksheet->write(10+$i, 2, $NOM_PRODUCTO, $border_item_left);
					}
					$worksheet->write(10+$i, 6, $COD_PRODUCTO, $border_item_especial_left);
					$worksheet->write(10+$i, 7, $CANTIDAD, $cant_normal);
					$worksheet->write(10+$i, 8, $PRECIO, $monto_normal);
					$worksheet->write(10+$i, 8+$margen, $TOTAL, $monto_normal);
			}
			
		}
		$worksheet->write(10+$i, 1, " ", $border_item_top);
		$worksheet->write(10+$i, 2, " ", $border_item_top);
		$worksheet->write(10+$i, 3, " ", $border_item_top);
		$worksheet->write(10+$i, 4, " ", $border_item_top);
		$worksheet->write(10+$i, 5, " ", $border_item_top);
		$worksheet->write(10+$i, 6, " ", $border_item_top);
		$worksheet->write(10+$i, 7, " ", $border_item_top);
		$worksheet->write(10+$i, 8, " ", $border_item_top);
		
		$margen_dscto = 0;
		
		if($PORC_DSCTO1 <> 0 && $PORC_DSCTO2 == 0){
			$margen_dscto = -1;
			$worksheet->write(10+$i, 9, " ", $border_item_top);
		}else if($PORC_DSCTO1 == 0 && $PORC_DSCTO2 <> 0){
			$margen_dscto = -1;
			$worksheet->write(10+$i, 9, " ", $border_item_top);	
		}else if($PORC_DSCTO1 <> 0 && $PORC_DSCTO2 <> 0){
			$margen_dscto = -1;
			$worksheet->write(10+$i, 9, " ", $border_item_top);	
			$worksheet->write(10+$i, 10, " ", $border_item_top);	
		}
		if($print_descto == 'item'){
			$worksheet->write(10+$i, 9+$margen, " ", $border_item_top);		
		}
						
		$row_position = $i+12;
		// $row_position=13 con un registro
		
		$worksheet->write($row_position-1, 6+$margen_dscto, " ", $border_item_bottom);
		$worksheet->write($row_position-1, 7+$margen_dscto, " ", $border_item_bottom);
		$worksheet->write($row_position-1, 8+$margen_dscto, " ", $border_item_bottom);
		$worksheet->write($row_position-1, 9+$margen_dscto, " ", $border_item_bottom);
		
		if($MONTO_DSCTO1 > 0 && $MONTO_DSCTO2 > 0){
			$worksheet->write($row_position, 6+$margen_dscto, "Subtotal ", $border_item_left);
			$worksheet->write($row_position, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
			$worksheet->write($row_position, 9+$margen_dscto, $SUBTOTAL, $monto_normal);
			
			if($print_descto == 'total'){
				$worksheet->write($row_position+1, 6+$margen_dscto, "Descuento ".$PORC_DSCTO1."% ", $border_item_left);
				$worksheet->write($row_position+1, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+1, 9+$margen_dscto, $MONTO_DSCTO1, $monto_normal);
				$worksheet->write($row_position+2, 6+$margen_dscto, "Descuento Adicional ".$PORC_DSCTO2."% ", $border_item_left);
				$worksheet->write($row_position+2, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+2, 9+$margen_dscto, $MONTO_DSCTO2, $monto_normal);
				
				$worksheet->write($row_position+3, 6+$margen_dscto, "Total Neto ", $border_item_left);
				$worksheet->write($row_position+3, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+3, 9+$margen_dscto, $TOTAL_NETO, $monto_normal);
				$worksheet->write($row_position+4, 6+$margen_dscto, "IVA ".$PORC_IVA."% ", $border_item_left);
				$worksheet->write($row_position+4, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+4, 9+$margen_dscto, $MONTO_IVA, $monto_normal);
				$worksheet->write($row_position+5, 6+$margen_dscto, "Total con IVA ", $border_item_left);
				$worksheet->write($row_position+5, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+5, 9+$margen_dscto, $TOTAL_CON_IVA, $monto_normal);
				$worksheet->write($row_position+6, 6+$margen_dscto, " ", $border_item_top);
				$worksheet->write($row_position+6, 7+$margen_dscto, " ", $border_item_top);
				$worksheet->write($row_position+6, 8+$margen_dscto, " ", $border_item_top);
				$worksheet->write($row_position+6, 9+$margen_dscto, " ", $border_item_top);
			}else{
				$worksheet->write($row_position+1, 6+$margen_dscto, "Total Neto ", $border_item_left);
				$worksheet->write($row_position+1, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+1, 9+$margen_dscto, $TOTAL_NETO, $monto_normal);
				$worksheet->write($row_position+2, 6+$margen_dscto, "IVA ".$PORC_IVA."% ", $border_item_left);
				$worksheet->write($row_position+2, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+2, 9+$margen_dscto, $MONTO_IVA, $monto_normal);
				$worksheet->write($row_position+3, 6+$margen_dscto, "Total con IVA ", $border_item_left);
				$worksheet->write($row_position+3, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+3, 9+$margen_dscto, $TOTAL_CON_IVA, $monto_normal);
				$worksheet->write($row_position+4, 6+$margen_dscto, " ", $border_item_top);
				$worksheet->write($row_position+4, 7+$margen_dscto, " ", $border_item_top);
				$worksheet->write($row_position+4, 8+$margen_dscto, " ", $border_item_top);
				$worksheet->write($row_position+4, 9+$margen_dscto, " ", $border_item_top);
			}

		
		}
		elseif($MONTO_DSCTO1 > 0 && $MONTO_DSCTO2 == 0){
			// ENTRA ACA TOTAL 133861
			$worksheet->write($row_position, 6+$margen_dscto, "Subtotal ", $border_item_left);
			$worksheet->write($row_position, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
			$worksheet->write($row_position, 9+$margen_dscto, $SUBTOTAL, $monto_normal);
			
			if($print_descto == 'total'){
				$worksheet->write($row_position+1, 6+$margen_dscto, "Descuento ".$PORC_DSCTO1."% ", $border_item_left);
				$worksheet->write($row_position+1, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+1, 9+$margen_dscto, $MONTO_DSCTO1, $monto_normal);
				
				$worksheet->write($row_position+2, 6+$margen_dscto, "Total Neto ", $border_item_left);
				$worksheet->write($row_position+2, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+2, 9+$margen_dscto, $TOTAL_NETO, $monto_normal);
				$worksheet->write($row_position+3, 6+$margen_dscto, "IVA ".$PORC_IVA."% ", $border_item_left);
				$worksheet->write($row_position+3, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+3, 9+$margen_dscto, $MONTO_IVA, $monto_normal);
				$worksheet->write($row_position+4, 6+$margen_dscto, "Total con IVA ", $border_item_left);
				$worksheet->write($row_position+4, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+4, 9+$margen_dscto, $TOTAL_CON_IVA, $monto_normal);
				$worksheet->write($row_position+5, 6+$margen_dscto, " ", $border_item_top);
				$worksheet->write($row_position+5, 7+$margen_dscto, " ", $border_item_top);
				$worksheet->write($row_position+5, 8+$margen_dscto, " ", $border_item_top);
				$worksheet->write($row_position+5, 9+$margen_dscto, " ", $border_item_top);
			}else{
				$worksheet->write($row_position+1, 6+$margen_dscto, "Total Neto ", $border_item_left);
				$worksheet->write($row_position+1, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+1, 9+$margen_dscto, $TOTAL_NETO, $monto_normal);
				$worksheet->write($row_position+2, 6+$margen_dscto, "IVA ".$PORC_IVA."% ", $border_item_left);
				$worksheet->write($row_position+2, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+2, 9+$margen_dscto, $MONTO_IVA, $monto_normal);
				$worksheet->write($row_position+3, 6+$margen_dscto, "Total con IVA ", $border_item_left);
				$worksheet->write($row_position+3, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+3, 9+$margen_dscto, $TOTAL_CON_IVA, $monto_normal);
				$worksheet->write($row_position+4, 6+$margen_dscto, " ", $border_item_top);
				$worksheet->write($row_position+4, 7+$margen_dscto, " ", $border_item_top);
				$worksheet->write($row_position+4, 8+$margen_dscto, " ", $border_item_top);
				$worksheet->write($row_position+4, 9+$margen_dscto, " ", $border_item_top);
			}

		}
		elseif($MONTO_DSCTO2 > 0 && $MONTO_DSCTO1 == 0){
			$worksheet->write($row_position, 6+$margen_dscto, "Subtotal ", $border_item_left);
			$worksheet->write($row_position, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
			$worksheet->write($row_position, 9+$margen_dscto, $SUBTOTAL, $monto_normal);	

			if($print_descto == 'total'){
				$worksheet->write($row_position+1, 6+$margen_dscto, "Descuento Adicional ".$PORC_DSCTO2."% ", $border_item_left);
				$worksheet->write($row_position+1, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+1, 9+$margen_dscto, $MONTO_DSCTO2, $monto_normal);
				
				$worksheet->write($row_position+2, 6+$margen_dscto, "Total Neto ", $border_item_left);
				$worksheet->write($row_position+2, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+2, 9+$margen_dscto, $TOTAL_NETO, $monto_normal);
				$worksheet->write($row_position+3, 6+$margen_dscto, "IVA ".$PORC_IVA."% ", $border_item_left);
				$worksheet->write($row_position+3, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+3, 9+$margen_dscto, $MONTO_IVA, $monto_normal);
				$worksheet->write($row_position+4, 6+$margen_dscto, "Total con IVA ", $border_item_left);
				$worksheet->write($row_position+4, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+4, 9+$margen_dscto, $TOTAL_CON_IVA, $monto_normal);
				$worksheet->write($row_position+5, 6+$margen_dscto, " ", $border_item_top);
				$worksheet->write($row_position+5, 7+$margen_dscto, " ", $border_item_top);
				$worksheet->write($row_position+5, 8+$margen_dscto, " ", $border_item_top);
				$worksheet->write($row_position+5, 9+$margen_dscto, " ", $border_item_top);
			}else{
				$worksheet->write($row_position+1, 6+$margen_dscto, "Total Neto ", $border_item_left);
				$worksheet->write($row_position+1, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+1, 9+$margen_dscto, $TOTAL_NETO, $monto_normal);
				$worksheet->write($row_position+2, 6+$margen_dscto, "IVA ".$PORC_IVA."% ", $border_item_left);
				$worksheet->write($row_position+2, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+2, 9+$margen_dscto, $MONTO_IVA, $monto_normal);
				$worksheet->write($row_position+3, 6+$margen_dscto, "Total con IVA ", $border_item_left);
				$worksheet->write($row_position+3, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+3, 9+$margen_dscto, $TOTAL_CON_IVA, $monto_normal);
				$worksheet->write($row_position+4, 6+$margen_dscto, " ", $border_item_top);
				$worksheet->write($row_position+4, 7+$margen_dscto, " ", $border_item_top);
				$worksheet->write($row_position+4, 8+$margen_dscto, " ", $border_item_top);
				$worksheet->write($row_position+4, 9+$margen_dscto, " ", $border_item_top);
			}
		}
		else
		{	
			$worksheet->write($row_position, 6+$margen_dscto, "Total Neto ", $border_item_left);
			$worksheet->write($row_position, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
			$worksheet->write($row_position, 9+$margen_dscto, $TOTAL_NETO, $monto_normal);
			$worksheet->write($row_position+1, 6+$margen_dscto, "IVA ".$PORC_IVA."% ", $border_item_left);
			$worksheet->write($row_position+1, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
			$worksheet->write($row_position+1, 9+$margen_dscto, $MONTO_IVA, $monto_normal);
			$worksheet->write($row_position+2, 6+$margen_dscto, "Total con IVA ", $border_item_left);
			$worksheet->write($row_position+2, 8+$margen_dscto, $SIMBOLO, $text_normal_right);
			$worksheet->write($row_position+2, 9+$margen_dscto, $TOTAL_CON_IVA, $monto_normal);
			$worksheet->write($row_position+3, 6+$margen_dscto, " ", $border_item_top);
			$worksheet->write($row_position+3, 7+$margen_dscto, " ", $border_item_top);
			$worksheet->write($row_position+3, 8+$margen_dscto, " ", $border_item_top);
			$worksheet->write($row_position+3, 9+$margen_dscto, " ", $border_item_top);	
		}

		$worksheet->write($row_position+7, 1, "Condiciones Generales:", $text_blue_bold_left);
		$worksheet->write($row_position+8, 1, "Foma de Pago", $text_blue_bold_left);
		$worksheet->write($row_position+8, 3, $NOM_FORMA_PAGO, $text_normal_left);
		$worksheet->write($row_position+9, 1, "Validez Oferta", $text_blue_bold_left);
		$worksheet->write($row_position+9, 3, $VALIDEZ_OFERTA." DÍAS", $text_normal_left);
		$worksheet->write($row_position+10, 1, "Entrega", $text_blue_bold_left);
		$worksheet->write($row_position+10, 3, $ENTREGA, $text_normal_left);
		$worksheet->write($row_position+11, 1, "Embalaje", $text_blue_bold_left);
		$worksheet->write($row_position+11, 3, $NOM_EMBALAJE_COTIZACION, $text_normal_left);
		$worksheet->write($row_position+12, 1, "Flete", $text_blue_bold_left);
		$worksheet->write($row_position+12, 3, $NOM_FLETE_COTIZACION, $text_normal_left);
		$worksheet->write($row_position+13, 1, "Instalación", $text_blue_bold_left);
		$worksheet->write($row_position+13, 3, $NOM_INSTALACION_COTIZACION, $text_normal_left);
		$worksheet->write($row_position+14, 1, "Garantía", $text_blue_bold_left);
		$worksheet->write($row_position+14, 3, $GARANTIA, $text_normal_left);
		$worksheet->write($row_position+15, 1, "Notas", $text_blue_bold_left);
		
		if($OBS<>'' && $OBS<>'NULL'){
			$worksheet->write($row_position+16, 1, $OBS, $text_normal_justify);
			$worksheet->merge_cells($row_position+16, 1, $row_position+23, 5);
			
			$worksheet->write($row_position+26, 8, $NOM_EMPRESA_EMISOR, $text_blue_bold_center);
			$worksheet->write($row_position+27, 8, $NOM_USUARIO, $text_blue_bold_center);
			$worksheet->write($row_position+28, 8, $MAIL_U, $text_blue_bold_center);
			$worksheet->write($row_position+29, 8, $FONO_U."-".$CEL_U, $text_blue_bold_center);
		
			$worksheet->write($row_position+32, 1, $NOM_EMPRESA_EMISOR." - RUT: ".$RUT_EMPRESA." - ".$DIR_EMPRESA." - ".$CIUDAD_EMPRESA." - ".$PAIS_EMPRESA." - ".$TEL_EMPRESA." - ".$FAX_EMPRESA, $text_pie_de_pagina);
			$worksheet->write($row_position+33, 5, $MAIL_EMPRESA." - ".$SITIO_WEB_EMPRESA, $text_pie_de_pagina);
		}else
		{
			$worksheet->write($row_position+16, 1, $OBS, $text_normal_left);
			
			$worksheet->write($row_position+19, 8, $NOM_EMPRESA_EMISOR, $text_blue_bold_center);
			$worksheet->write($row_position+20, 8, $NOM_USUARIO, $text_blue_bold_center);
			$worksheet->write($row_position+21, 8, $MAIL_U, $text_blue_bold_center);
			$worksheet->write($row_position+22, 8, $FONO_U."-".$CEL_U, $text_blue_bold_center);
	
			$worksheet->write($row_position+25, 1, $NOM_EMPRESA_EMISOR." - RUT: ".$RUT_EMPRESA." - ".$DIR_EMPRESA." - ".$CIUDAD_EMPRESA." - ".$PAIS_EMPRESA." - ".$TEL_EMPRESA." - ".$FAX_EMPRESA, $text_pie_de_pagina);
			$worksheet->write($row_position+26, 5, $MAIL_EMPRESA." - ".$SITIO_WEB_EMPRESA, $text_pie_de_pagina);
		}
		
		$workbook->close();
		
		header("Content-Type: application/x-msexcel; name=\"Cotizacion Resumen $COD_COTIZACION.xls\"");
		header("Content-Disposition: inline; filename=\"Cotizacion Resumen $COD_COTIZACION.xls\"");
		$fh=fopen($fname, "rb");
		fpassthru($fh);
		unlink($fname);
		
		error_reporting(E_ALL);
	}
	
function printcot_ampliada_excel($con_logo) {
		$print_descto = session::get('PRINT_DESCUENTO');
		error_reporting(E_ALL & ~E_NOTICE);
		
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");
		
		$fname = tempnam("/tmp", "ampliada.xls");
		$workbook = &new writeexcel_workbook($fname);
		$cod_cotizacion = $this->get_key();
		$worksheet = &$workbook->addworksheet('COTIZACION_'.$cod_cotizacion);
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT	C.COD_COTIZACION,
						IC.COD_ITEM_COTIZACION,
						E.NOM_EMPRESA,
						E.RUT,
						E.DIG_VERIF,
						dbo.f_format_date(getdate(), 3) FECHA_IMPRESO,
						dbo.f_format_date(C.FECHA_COTIZACION, 3) FECHA_COTIZACION,				
						dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[DIRECCION]') DIRECCION,
						dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[NOM_COMUNA]') NOM_COMUNA,
						dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[NOM_CIUDAD]') NOM_CIUDAD,
						SF.TELEFONO TELEFONO_F,
						SF.FAX FAX_F,
						C.REFERENCIA,
						P.NOM_PERSONA,
						P.EMAIL,
						p.TELEFONO,
						IC.NOM_PRODUCTO,
						case IC.COD_PRODUCTO
							when 'T' then ''
							else IC.ITEM
						end ITEM,
						IC.COD_PRODUCTO COD_PRODUCTO_ORIGINAL,
						case IC.COD_PRODUCTO
							when 'T' then null
							else IC.COD_PRODUCTO
						end COD_PRODUCTO,
						case IC.COD_PRODUCTO
							when 'T' then null
							else IC.CANTIDAD
						end CANTIDAD,
						case IC.COD_PRODUCTO
							when 'T' then null
							else IC.PRECIO
						end PRECIO,
						case IC.COD_PRODUCTO
							when 'T' then null
							else IC.CANTIDAD * IC.PRECIO
						end TOTAL,
						C.SUBTOTAL,
						C.PORC_DSCTO1,
						C.MONTO_DSCTO1,
						C.PORC_DSCTO2,
						C.MONTO_DSCTO2,
						C.MONTO_DSCTO1 + C.MONTO_DSCTO2 FINAL,
						C.TOTAL_NETO,
						C.PORC_IVA,
						C.MONTO_IVA,
						C.TOTAL_CON_IVA,
						FP.NOM_FORMA_PAGO,
						C.VALIDEZ_OFERTA,
						C.ENTREGA,
						C.OBS,
						EC.NOM_EMBALAJE_COTIZACION,
						FL.NOM_FLETE_COTIZACION,
						I.NOM_INSTALACION_COTIZACION,
						C.GARANTIA,
						M.SIMBOLO,
						U.NOM_USUARIO,
						U.MAIL MAIL_U,
						U.TELEFONO FONO_U,
						U.CELULAR CEL_U,
						IC.COD_PRODUCTO,
						dbo.f_get_parametro(".self::K_PARAM_NOM_EMPRESA.") NOM_EMPRESA_EMISOR,
						dbo.f_get_parametro(".self::K_PARAM_RUT_EMPRESA.") RUT_EMPRESA,
						dbo.f_get_parametro(".self::K_PARAM_DIR_EMPRESA.") DIR_EMPRESA,
						dbo.f_get_parametro(".self::K_PARAM_TEL_EMPRESA.") TEL_EMPRESA,	
						dbo.f_get_parametro(".self::K_PARAM_FAX_EMPRESA.") FAX_EMPRESA,
						dbo.f_get_parametro(".self::K_PARAM_MAIL_EMPRESA.") MAIL_EMPRESA,
						dbo.f_get_parametro(".self::K_PARAM_CIUDAD_EMPRESA.") CIUDAD_EMPRESA,
						dbo.f_get_parametro(".self::K_PARAM_PAIS_EMPRESA.") PAIS_EMPRESA,
						dbo.f_get_parametro(".self::K_PARAM_SITIO_WEB_EMPRESA.") SITIO_WEB_EMPRESA
		FROM COTIZACION C, EMPRESA E, PERSONA P, ITEM_COTIZACION IC,FORMA_PAGO FP,
							 INSTALACION_COTIZACION I, FLETE_COTIZACION FL, EMBALAJE_COTIZACION EC,
							 MONEDA M, USUARIO U, SUCURSAL SF
				WHERE C.COD_COTIZACION = $cod_cotizacion AND 
								E.COD_EMPRESA = C.COD_EMPRESA AND
								P.COD_PERSONA = C.COD_PERSONA AND
								IC.COD_COTIZACION = C.COD_COTIZACION AND
								FP.COD_FORMA_PAGO = C.COD_FORMA_PAGO AND
								I.COD_INSTALACION_COTIZACION =C.COD_INSTALACION_COTIZACION AND
								FL.COD_FLETE_COTIZACION = C.COD_FLETE_COTIZACION AND
								EC.COD_EMBALAJE_COTIZACION = C.COD_EMBALAJE_COTIZACION AND	
								M.COD_MONEDA = C.COD_MONEDA AND
								U.COD_USUARIO = C.COD_USUARIO_VENDEDOR1 AND
								SF.COD_SUCURSAL = C.COD_SUCURSAL_FACTURA
								order by IC.ORDEN asc";
				
		$result = $db->build_results($sql);
		
		$worksheet->set_row(0, 60);
		$worksheet->set_column(0, 0, 4);
		$worksheet->set_column(1, 2, 7);
		$worksheet->set_column(3, 9, 14);
		$worksheet->set_column(2, 10, 12);
		$worksheet->insert_bitmap('B1',$this->root_dir."images_appl/logo_reporte_excel.bmp");
		
	
		$text =& $workbook->addformat();
		$text->set_font("Verdana");
		$text->set_valign('vcenter');
    
		$text_bold =& $workbook->addformat();
		$text_bold->copy($text);
		$text_bold->set_bold(1);
	
		$text_blue_bold_left =& $workbook->addformat();
		$text_blue_bold_left->copy($text_bold);
		$text_blue_bold_left->set_align('left');
		$text_blue_bold_left->set_color('blue_0x20');

		$text_blue_bold_center =& $workbook->addformat();
		$text_blue_bold_center->copy($text_bold);
		$text_blue_bold_center->set_align('center');
		$text_blue_bold_center->set_color('blue_0x20');
		
		$text_blue_bold_right =& $workbook->addformat();
		$text_blue_bold_right->copy($text_bold);
		$text_blue_bold_right->set_align('right');
		$text_blue_bold_right->set_color('blue_0x20');

		$text_nro_docto =& $workbook->addformat();
		$text_nro_docto->copy($text_blue_bold_right);
		$text_nro_docto->set_size(13);
		
		$text_pie_de_pagina =& $workbook->addformat();
		$text_pie_de_pagina->copy($text_blue_bold_left);
		$text_pie_de_pagina->set_size(8);
		
		$text_normal_left =& $workbook->addformat();
		$text_normal_left->copy($text);
		$text_normal_left->set_align('left');
		
		$text_normal_center =& $workbook->addformat();
		$text_normal_center->copy($text);
		$text_normal_center->set_align('center');
		
		$text_normal_right =& $workbook->addformat();
		$text_normal_right->copy($text);
		$text_normal_right->set_align('right');
				
		$text_normal_bold_left =& $workbook->addformat();
		$text_normal_bold_left->copy($text_bold);
		$text_normal_bold_left->set_align('left');
		
		
		$text_normal_bold_center =& $workbook->addformat();
		$text_normal_bold_center->copy($text_bold);
		$text_normal_bold_center->set_align('center');
	
		$text_normal_bold_right =& $workbook->addformat();
		$text_normal_bold_right->copy($text_bold);
		$text_normal_bold_right->set_align('right');
	
		
		$titulo_item_border_all =& $workbook->addformat();
		$titulo_item_border_all->copy($text_blue_bold_center);
		$titulo_item_border_all->set_border_color('black');
		$titulo_item_border_all->set_top(2);
		$titulo_item_border_all->set_bottom(2);
		$titulo_item_border_all->set_right(2);
		$titulo_item_border_all->set_left(2);
		
		$titulo_item_border_all_merge =& $workbook->addformat();
		$titulo_item_border_all_merge->copy($titulo_item_border_all);
		$titulo_item_border_all_merge->set_merge();
				
	
		$border_item_left = & $workbook->addformat();
		$border_item_left->copy($text_normal_left);
		$border_item_left->set_border_color('black');
		$border_item_left->set_left(2);
		
		$border_item_right = & $workbook->addformat();
		$border_item_right->copy($text_normal_right);
		$border_item_right->set_border_color('black');
		$border_item_right->set_left(2);
		
		
		$border_item_left_bold = & $workbook->addformat();
		$border_item_left_bold->copy($text_bold);
		$border_item_left_bold->set_border_color('black');
		$border_item_left_bold->set_left(2);
		
		$border_item_right_bold = & $workbook->addformat();
		$border_item_right_bold->copy($text_bold);
		$border_item_right_bold->set_border_color('black');
		$border_item_right_bold->set_left(2);
		$border_item_right_bold->set_right(2);
		
		$border_item_right_bold_2 = & $workbook->addformat();
		$border_item_right_bold_2->copy($text_bold);
		$border_item_right_bold_2->set_border_color('black');
		$border_item_right_bold_2->set_left(2);
		$border_item_right_bold_2->set_right(2);
		
		$border_item_center = & $workbook->addformat();
		$border_item_center->copy($text_normal_center);
		$border_item_center->set_border_color('black');
		$border_item_center->set_left(2);
		$border_item_center->set_right(2);
		
		$border_item_right = & $workbook->addformat();
		$border_item_right->copy($text_normal_right);
		$border_item_right->set_border_color('black');
		$border_item_right->set_right(2);

		$border_item_right_total = & $workbook->addformat();
		$border_item_right_total->copy($text_normal_right);
		$border_item_right_total->set_border_color('black');
		$border_item_right_total->set_right(2);
		$border_item_right_total->set_left(2);
		
		$cant_normal =& $workbook->addformat();
		$cant_normal->copy($border_item_right);
		$cant_normal->set_num_format('0.0');
					
		$monto_normal =& $workbook->addformat();
		$monto_normal->copy($border_item_right);
		$monto_normal->set_num_format('#,##0');
		
		$border_item_top = & $workbook->addformat();
		$border_item_top->copy($text);
		$border_item_top->set_border_color('black');
		$border_item_top->set_top(2);
		
		$border_item_bottom = & $workbook->addformat();
		$border_item_bottom->copy($text);
		$border_item_bottom->set_border_color('black');
		$border_item_bottom->set_bottom(2);
		
		$border_item_total_2 = & $workbook->addformat();
		$border_item_total_2->copy($text);
		$border_item_total_2->set_border_color('black');
		$border_item_total_2->set_top(2);
		$border_item_total_2->set_bottom(2);
		$border_item_total_2->set_right(2);
		
		$border_item_total_3 = & $workbook->addformat();
		$border_item_total_3->copy($text);
		$border_item_total_3->set_border_color('black');
		$border_item_total_3->set_top(2);
		$border_item_total_3->set_bottom(2);
		
		$border_item_total = & $workbook->addformat();
		$border_item_total->copy($text);
		$border_item_total->set_border_color('black');
		$border_item_total->set_top(2);
		$border_item_total->set_bottom(2);
		$border_item_total->set_right(2);
		
		$border_item_especial_left = & $workbook->addformat();
		$border_item_especial_left->copy($text_normal_left);
		$border_item_especial_left->set_border_color('black');
		$border_item_especial_left->set_left(2);
		$border_item_especial_left->set_right(2);
	
		$border_item_especial_total = & $workbook->addformat();
		$border_item_especial_total->copy($border_item_total);
		$border_item_especial_total->set_border_color('black');
		$border_item_especial_total->set_bold(1);
		
		$border_item_especial_total_2 = & $workbook->addformat();
		$border_item_especial_total_2->copy($border_item_total_2);
		$border_item_especial_total_2->set_border_color('black');
		$border_item_especial_total_2->set_bold(1);
		
		$border_item_especial_total_3 = & $workbook->addformat();
		$border_item_especial_total_3->copy($border_item_total_3);
		$border_item_especial_total_3->set_border_color('black');
		$border_item_especial_total_3->set_bold(1);
		
		$COD_COTIZACION = $result[0]['COD_COTIZACION'];
		$FECHA_IMPRESO = $result[0]['FECHA_IMPRESO'];
		$FECHA_COTIZACION = $result[0]['FECHA_COTIZACION'];
		$NOM_EMPRESA = $result[0]['NOM_EMPRESA'];
		$RUT = $result[0]['RUT'];
		$DIG_VERIF = $result[0]['DIG_VERIF'];
		$DIRECCION = $result[0]['DIRECCION'];
		$NOM_COMUNA = $result[0]['NOM_COMUNA'];
		$NOM_CIUDAD = $result[0]['NOM_CIUDAD'];
		$TELEFONO_F = $result[0]['TELEFONO_F'];
		$FAX_F = $result[0]['FAX_F'];	
		$NOM_PERSONA = $result[0]['NOM_PERSONA'];
		$EMAIL = $result[0]['EMAIL'];
		$REFERENCIA = $result[0]['REFERENCIA'];
		$SIMBOLO = $result[0]['SIMBOLO'];
		
		$worksheet->write(1, 9, "COTIZACION Nº".$COD_COTIZACION, $text_nro_docto);
		$worksheet->write(1, 1, "Santiago,".$FECHA_COTIZACION, $text_blue_bold_left);
		$worksheet->write(3, 1, "Razón Social", $text_blue_bold_left);
		
		$worksheet->write(3, 3, $NOM_EMPRESA, $text_normal_bold_left);
		$worksheet->write(3, 8, "Rut", $text_blue_bold_left);
		
		$rut=number_format($RUT, 0, ',', '.');
		$rut=$rut.'-'.$DIG_VERIF;
		
		$worksheet->write(3, 9, $rut, $text_normal_bold_left);
		
		$worksheet->write(4, 1, "Dirección", $text_blue_bold_left);
		$worksheet->write(4, 3, $DIRECCION, $text_normal_left);
		$worksheet->write(5, 1, "Comuna", $text_blue_bold_left);
		$worksheet->write(5, 3, $NOM_COMUNA, $text_normal_left);
		$worksheet->write(5, 4, "Ciudad", $text_blue_bold_left);
		$worksheet->write(5, 5, $NOM_CIUDAD, $text_normal_left);
		$worksheet->write(5, 6, "Fono", $text_blue_bold_left);
		$worksheet->write(5, 7, $TELEFONO_F, $text_normal_left);
		$worksheet->write(5, 8, "Fax",$text_blue_bold_left);
		$worksheet->write(5, 9, $FAX_F,$text_normal_left);
		$worksheet->write(6, 1, "Atención", $text_blue_bold_left);
		$worksheet->write(6, 3, $NOM_PERSONA." ".$EMAIL, $text_normal_left);
		$worksheet->write(7, 1, "Referencia",$text_blue_bold_left);
		$worksheet->write(7, 3, $REFERENCIA,$text_normal_left);
		
		$worksheet->write(9, 1, "Ítem", $titulo_item_border_all);
		$worksheet->write(9, 2, "Modelo", $titulo_item_border_all);
		$worksheet->write(9, 3, "", $titulo_item_border_all);
		$worksheet->write(9, 4, "", $titulo_item_border_all);
		$worksheet->write(9, 5, "", $titulo_item_border_all);
		$worksheet->write(9, 3, "                                Producto                                ", $titulo_item_border_all_merge);
		$worksheet->merge_cells(9, 3, 9, 7);
		$worksheet->write(9, 6, "", $titulo_item_border_all);
		$worksheet->write(9, 7, "", $titulo_item_border_all);
		$worksheet->write(9, 8, "Cantidad", $titulo_item_border_all);
		$worksheet->write(9, 9, "Precio ".$SIMBOLO, $titulo_item_border_all);
		
		//cabecera de los items, para validar si selecciona items, total o ninguno
		
		if($print_descto=='item'){
			if(($result[0]['MONTO_DSCTO1']>0)&($result[0]['MONTO_DSCTO2']==0)){	
				$worksheet->set_column(11, 11, 20);
				$worksheet->write(9, 10, "Descto", $titulo_item_border_all);
				$worksheet->write(9, 11, "Total", $titulo_item_border_all);
			}else if(($result[0]['MONTO_DSCTO2']>0)&($result[0]['MONTO_DSCTO1']==0)){
				$worksheet->set_column(11, 11, 20);
				$worksheet->write(9, 10, "Descto", $titulo_item_border_all);
				$worksheet->write(9, 11, "Total", $titulo_item_border_all);
			}else if(($result[0]['MONTO_DSCTO1']>0)&($result[0]['MONTO_DSCTO2']>0)){
				$worksheet->set_column(12, 12, 20);
				$worksheet->write(9, 10, "Descto 1", $titulo_item_border_all);
				$worksheet->write(9, 11, "Descto 2", $titulo_item_border_all);
				$worksheet->write(9, 12, "Total".$SIMBOLO, $titulo_item_border_all);
			}else{
				$worksheet->write(9, 10, "Total".$SIMBOLO, $titulo_item_border_all);
			}
		}else {
			$worksheet->write(9, 10, "Total 4".$SIMBOLO, $titulo_item_border_all);
		}
		
		$p = 0;
		$max_len = 0; 
		
		for ($i=0 ; $i <count($result); $i++) {
			$COD_ITEM_COTIZACION = $result[$i]['COD_ITEM_COTIZACION'];
			$ITEM = $result[$i]['ITEM'];
			$NOM_PRODUCTO = $result[$i]['NOM_PRODUCTO'];
			$COD_PRODUCTO = $result[$i]['COD_PRODUCTO'];
			$CANTIDAD = $result[$i]['CANTIDAD'];
			$PRECIO = $result[$i]['PRECIO'];
			$TOTAL = $result[$i]['TOTAL'];
			$PORC_DSCTO1 = $result[$i]['PORC_DSCTO1'];
			$PORC_DSCTO2 = $result[$i]['PORC_DSCTO2'];
			
			$worksheet->write(10+$p, 1,$ITEM, $titulo_item_border_all);
			$worksheet->write(10+$p, 2,$COD_PRODUCTO,$titulo_item_border_all);
			if($COD_PRODUCTO == ''){
				$worksheet->write(10+$p, 3, $NOM_PRODUCTO, $border_item_especial_total);
				$worksheet->merge_cells(10+$p, 3, 10+$p, 7);
			}else{
				$worksheet->write(10+$p, 3, $NOM_PRODUCTO, $border_item_especial_total);
				$worksheet->merge_cells(10+$p, 3, 10+$p, 7);
			}
			$worksheet->write(10+$p, 4,"", $border_item_especial_total);
			$worksheet->write(10+$p, 5,"", $border_item_especial_total);
			$worksheet->write(10+$p, 6,"", $border_item_especial_total_3);	
			$worksheet->write(10+$p, 7,"", $border_item_especial_total_2);
			if($print_descto=='item'){
				if(($result[0]['MONTO_DSCTO1']>0)&($result[0]['MONTO_DSCTO2']==0)){
					$worksheet->write(10+$p, 8,$CANTIDAD, $border_item_especial_total);
					$worksheet->write(10+$p, 9,$PRECIO, $border_item_especial_total);
					$worksheet->write(10+$p, 10,$PRECIO * ($PORC_DSCTO1/100), $border_item_especial_total);
					$worksheet->write(10+$p, 11,($PRECIO - ($PRECIO * ($PORC_DSCTO1/100))) * $CANTIDAD, $border_item_especial_total);		
				}else if(($result[0]['MONTO_DSCTO2']>0)&($result[0]['MONTO_DSCTO1']==0)){
					$worksheet->write(10+$p, 8,$CANTIDAD, $border_item_especial_total);
					$worksheet->write(10+$p, 9,$PRECIO, $border_item_especial_total);
					$worksheet->write(10+$p, 10,$PRECIO * ($PORC_DSCTO2/100), $border_item_especial_total);
					$worksheet->write(10+$p, 11,($PRECIO - ($PRECIO * ($PORC_DSCTO2/100))) * $CANTIDAD, $border_item_especial_total);		
				}else if(($result[0]['MONTO_DSCTO1']>0)&($result[0]['MONTO_DSCTO2']>0)){
					$worksheet->write(10+$p, 8,$CANTIDAD, $border_item_especial_total);
					$worksheet->write(10+$p, 9,$PRECIO, $border_item_especial_total);
					$worksheet->write(10+$p, 10,$PRECIO * ($PORC_DSCTO1/100), $border_item_especial_total);
					$worksheet->write(10+$p, 11,$PRECIO * ($PORC_DSCTO2/100), $border_item_especial_total);
					$worksheet->write(10+$p, 12,($PRECIO - ($PRECIO * ($PORC_DSCTO1/100)) - ($PRECIO * ($PORC_DSCTO2/100))) * $CANTIDAD, $border_item_especial_total);		
				}
			}else{
				$worksheet->write(10+$p, 8,$CANTIDAD, $border_item_especial_total);
				$worksheet->write(10+$p, 9,$PRECIO, $border_item_especial_total);
				$worksheet->write(10+$p, 10,$TOTAL, $border_item_especial_total);
			}
			
			if ($COD_PRODUCTO == 'TE'){
				$sql_atr = "select MOTIVO_TE NOM_ATRIBUTO_PRODUCTO
							from ITEM_COTIZACION 
							where COD_PRODUCTO = 'TE'
							and COD_COTIZACION =$cod_cotizacion
							and COD_ITEM_COTIZACION = $COD_ITEM_COTIZACION";
			}else{
				$sql_atr = "SELECT NOM_ATRIBUTO_PRODUCTO
						FROM ATRIBUTO_PRODUCTO
						WHERE COD_PRODUCTO = '$COD_PRODUCTO'";
			}	
			
			$db_2 = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$result_2 = $db_2->build_results($sql_atr);

			for ($j=0 ; $j <count($result_2); $j++) {
				if($j==0){
					$worksheet->write(11+$p+$j, 1, '', $border_item_left);
					$worksheet->write(11+$p+$j, 2, '', $border_item_left);
					$worksheet->write(11+$p+$j, 8, '', $border_item_right_total);
					$worksheet->write(11+$p+$j, 9, '', $border_item_right);
					if($print_descto=='item'){
						if(($result[0]['MONTO_DSCTO1']>0)&($result[0]['MONTO_DSCTO2']==0)){
							$worksheet->write(11+$p+$j, 10, '', $border_item_right);
							$worksheet->write(11+$p+$j, 11,'', $border_item_right);
						}else if(($result[0]['MONTO_DSCTO2']>0)&($result[0]['MONTO_DSCTO1']==0)){
							$worksheet->write(11+$p+$j, 10, '', $border_item_right);
							$worksheet->write(11+$p+$j, 11,'', $border_item_right);
						}else if(($result[0]['MONTO_DSCTO1']>0)&($result[0]['MONTO_DSCTO2']>0)){
							$worksheet->write(11+$p+$j, 10, '', $border_item_right);
							$worksheet->write(11+$p+$j, 11,'', $border_item_right);
							$worksheet->write(11+$p+$j, 12,"", $border_item_right);
						}
					}else{
						$worksheet->write(11+$p+$j, 10, '', $border_item_right);
					}
				}
				else{
					$worksheet->write(11+$p+$j, 1, "", $border_item_left_bold);
					$worksheet->write(11+$p+$j, 2, "", $border_item_left_bold);
					$worksheet->write(11+$p+$j, 8, "", $border_item_left_bold);
					$worksheet->write(11+$p+$j, 9, "", $border_item_left_bold);
					if($print_descto=='item'){
						if(($result[0]['MONTO_DSCTO1']>0)&($result[0]['MONTO_DSCTO2']==0)){
							$worksheet->write(11+$p+$j, 10, "",$border_item_left_bold);
							$worksheet->write(11+$p+$j, 11,"", $border_item_right_bold);
						}else if(($result[0]['MONTO_DSCTO2']>0)&($result[0]['MONTO_DSCTO1']==0)){
						 	$worksheet->write(11+$p+$j, 10, "",$border_item_left_bold);
							$worksheet->write(11+$p+$j, 11,"", $border_item_right_bold);
						}else if(($result[0]['MONTO_DSCTO1']>0)&($result[0]['MONTO_DSCTO2']>0)){
							$worksheet->write(11+$p+$j, 10, "",$border_item_left_bold);
							$worksheet->write(11+$p+$j, 11,"", $border_item_left_bold);
							$worksheet->write(11+$p+$j, 12,"", $border_item_right_bold_2);
						}
					}else{
						$worksheet->write(11+$p+$j, 10, "",$border_item_right_bold);
					}
				}			
				$AT_PRODUCTO = $result_2[$j]['NOM_ATRIBUTO_PRODUCTO'];
			
				$worksheet->write(11+$p+$j, 3, ' '.$AT_PRODUCTO, $border_item_left);
				
				$LEN =  round(strlen ($result_2[$j]['NOM_ATRIBUTO_PRODUCTO'])/12);
				if($LEN > $max_len){
					$max_len = $LEN;
				 }
			}
			$p = $p + count($result_2)+1;
		}
		
		if ($max_len < 5){
			$max_len = 1;
		}else{
			$max_len = $max_len - 4;
		}
		
		$worksheet->set_column(7, 7, 13*$max_len);
		//final del itemizada .... linea negra que lo cierra
		$worksheet->write(10+$p, 1, " ", $border_item_top);
		$worksheet->write(10+$p, 2, " ", $border_item_top);
		$worksheet->write(10+$p, 3, " ", $border_item_top);
		$worksheet->write(10+$p, 4, " ", $border_item_top);
		$worksheet->write(10+$p, 5, " ", $border_item_top);
		$worksheet->write(10+$p, 6, " ", $border_item_top);
		$worksheet->write(10+$p, 7, " ", $border_item_top);
		$worksheet->write(10+$p, 8, " ", $border_item_top);
		$worksheet->write(10+$p, 9, " ", $border_item_top);
		$worksheet->write(10+$p, 10, " ", $border_item_top);
		
		if($print_descto=='item'){
			if(($result[0]['MONTO_DSCTO1']>0)&($result[0]['MONTO_DSCTO2']==0))
				$worksheet->write(10+$p, 11, " ", $border_item_top);
			else if(($result[0]['MONTO_DSCTO2']>0)&($result[0]['MONTO_DSCTO1']==0))	
				$worksheet->write(10+$p, 11, " ", $border_item_top);
			else if(($result[0]['MONTO_DSCTO1']>0)&($result[0]['MONTO_DSCTO2']>0)){
				$worksheet->write(10+$p, 11, " ", $border_item_top);
				$worksheet->write(10+$p, 12, " ", $border_item_top);
			}
		}
		
		
		$row_position = $p+15;
		/*
			*FALTA CAMBIAR EL TOTAL .... CUANDO ES AMPLIADA, EXCEL, COLOCAR LOS CAMPOS BIEN EN LA PARTE DE 
			* ABAJO 
		*/
		$SUBTOTAL = $result[0]['SUBTOTAL'];
		$PORC_DSCTO1 = $result[0]['PORC_DSCTO1'];
		$MONTO_DSCTO1 = $result[0]['MONTO_DSCTO1'];
		$PORC_DSCTO2 = $result[0]['PORC_DSCTO2'];
		$MONTO_DSCTO2 = $result[0]['MONTO_DSCTO2'];
		$TOTAL_NETO = $result[0]['TOTAL_NETO'];
		$PORC_IVA = $result[0]['PORC_IVA'];
		$MONTO_IVA = $result[0]['MONTO_IVA'];
		$TOTAL_CON_IVA = $result[0]['TOTAL_CON_IVA'];
	
		$NOM_FORMA_PAGO = $result[0]['NOM_FORMA_PAGO'];
		$VALIDEZ_OFERTA = $result[0]['VALIDEZ_OFERTA'];
		$ENTREGA = $result[0]['ENTREGA'];
		$NOM_EMBALAJE_COTIZACION = $result[0]['NOM_EMBALAJE_COTIZACION'];
		$NOM_FLETE_COTIZACION = $result[0]['NOM_FLETE_COTIZACION'];
		$NOM_INSTALACION_COTIZACION = $result[0]['NOM_INSTALACION_COTIZACION'];
		$GARANTIA = $result[0]['GARANTIA'];
		$OBS = $result[0]['OBS'];		
		$NOM_USUARIO = $result[0]['NOM_USUARIO'];
		$MAIL_U = $result[0]['MAIL_U'];
		$FONO_U = $result[0]['FONO_U'];
		$CEL_U = $result[0]['CEL_U'];
		
	
		$NOM_EMPRESA_EMISOR = $result[0]['NOM_EMPRESA_EMISOR'];
		$RUT_EMPRESA = $result[0]['RUT_EMPRESA'];
		$DIR_EMPRESA = $result[0]['DIR_EMPRESA'];
		$CIUDAD_EMPRESA = $result[0]['CIUDAD_EMPRESA'];
		$PAIS_EMPRESA = $result[0]['PAIS_EMPRESA'];
		$TEL_EMPRESA = $result[0]['TEL_EMPRESA'];
		$FAX_EMPRESA = $result[0]['FAX_EMPRESA'];
		$MAIL_EMPRESA = $result[0]['MAIL_EMPRESA'];
		$SITIO_WEB_EMPRESA = $result[0]['SITIO_WEB_EMPRESA'];
		
		$FINAL = $result[0]['FINAL'];

		$worksheet->write($row_position-1, 7, " ", $border_item_bottom);
		$worksheet->write($row_position-1, 8, " ", $border_item_bottom);
		$worksheet->write($row_position-1, 9, " ", $border_item_bottom);
		$worksheet->write($row_position-1, 10, " ", $border_item_bottom);
		if($print_descto=='item'){
			if((($MONTO_DSCTO1 > 0)&($MONTO_DSCTO2 > 0)) ||
				(($MONTO_DSCTO1 > 0)&($MONTO_DSCTO2 == 0)) ||
				(($MONTO_DSCTO2 > 0)&($MONTO_DSCTO1 == 0))){
				$worksheet->write($row_position, 7, "Subtotal ", $border_item_left);
				$worksheet->write($row_position, 9, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position, 10, $SUBTOTAL, $monto_normal);
				$worksheet->write($row_position+1, 7, "Total Neto ", $border_item_left);
				$worksheet->write($row_position+1, 9, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+1, 10, $TOTAL_NETO, $monto_normal);
				$worksheet->write($row_position+2, 7, "IVA ".$PORC_IVA."% ", $border_item_left);
				$worksheet->write($row_position+2, 9, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+2, 10, $MONTO_IVA, $monto_normal);
				$worksheet->write($row_position+3, 7, "Total con IVA ", $border_item_left);
				$worksheet->write($row_position+3, 9, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+3, 10, $TOTAL_CON_IVA, $monto_normal);
				$worksheet->write($row_position+4, 7, " ", $border_item_top);
				$worksheet->write($row_position+4, 8, " ", $border_item_top);
				$worksheet->write($row_position+4, 9, " ", $border_item_top);
				$worksheet->write($row_position+4, 10, " ", $border_item_top);
			}
		}else{
				if($MONTO_DSCTO1 > 0 && $MONTO_DSCTO2 > 0){
					$worksheet->write($row_position, 7, "Subtotal ", $border_item_left);
					$worksheet->write($row_position, 9, $SIMBOLO, $text_normal_right);
					$worksheet->write($row_position, 10, $SUBTOTAL, $monto_normal);
					$worksheet->write($row_position+1, 7, "Descuento ".$PORC_DSCTO1."% ", $border_item_left);
					$worksheet->write($row_position+1, 9, $SIMBOLO, $text_normal_right);
					$worksheet->write($row_position+1, 10, $MONTO_DSCTO1, $monto_normal);
					$worksheet->write($row_position+2, 7, "Descuento Adicional ".$PORC_DSCTO2."% ", $border_item_left);
					$worksheet->write($row_position+2, 9, $SIMBOLO, $text_normal_right);
					$worksheet->write($row_position+2, 10, $MONTO_DSCTO2, $monto_normal);
					$worksheet->write($row_position+3, 7, "Total Neto ", $border_item_left);
					$worksheet->write($row_position+3, 9, $SIMBOLO, $text_normal_right);
					$worksheet->write($row_position+3, 10, $TOTAL_NETO, $monto_normal);
					$worksheet->write($row_position+4, 7, "IVA ".$PORC_IVA."% ", $border_item_left);
					$worksheet->write($row_position+4, 9, $SIMBOLO, $text_normal_right);
					$worksheet->write($row_position+4, 10, $MONTO_IVA, $monto_normal);
					$worksheet->write($row_position+5, 7, "Total con IVA ", $border_item_left);
					$worksheet->write($row_position+5, 9, $SIMBOLO, $text_normal_right);
					$worksheet->write($row_position+5, 10, $TOTAL_CON_IVA, $monto_normal);
					$worksheet->write($row_position+6, 7, " ", $border_item_top);
					$worksheet->write($row_position+6, 8, " ", $border_item_top);
					$worksheet->write($row_position+6, 9, " ", $border_item_top);
					$worksheet->write($row_position+6, 10, " ", $border_item_top);
				}
			elseif($MONTO_DSCTO1 > 0 && $MONTO_DSCTO2 == 0){
				$worksheet->write($row_position, 7, "Subtotal ", $border_item_left);
				$worksheet->write($row_position, 9, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position, 10, $SUBTOTAL, $monto_normal);
				$worksheet->write($row_position+1, 7, "Descuento ".$PORC_DSCTO1."% ", $border_item_left);
				$worksheet->write($row_position+1, 9, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+1, 10, $MONTO_DSCTO1, $monto_normal);
	
				$worksheet->write($row_position+2, 7, "Total Neto ", $border_item_left);
				$worksheet->write($row_position+2, 9, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+2, 10, $TOTAL_NETO, $monto_normal);
				$worksheet->write($row_position+3, 7, "IVA ".$PORC_IVA."% ", $border_item_left);
				$worksheet->write($row_position+3, 9, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+3, 10, $MONTO_IVA, $monto_normal);
				$worksheet->write($row_position+4, 7, "Total con IVA ", $border_item_left);
				$worksheet->write($row_position+4, 9, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+4, 10, $TOTAL_CON_IVA, $monto_normal);
				$worksheet->write($row_position+5, 7, " ", $border_item_top);
				$worksheet->write($row_position+5, 8, " ", $border_item_top);
				$worksheet->write($row_position+5, 9, " ", $border_item_top);
				$worksheet->write($row_position+5, 10, " ", $border_item_top);
			}
			elseif($MONTO_DSCTO2 > 0 && $MONTO_DSCTO1 == 0){
				$worksheet->write($row_position, 7, "Subtotal ", $border_item_left);
				$worksheet->write($row_position, 9, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position, 10, $SUBTOTAL, $monto_normal);			
				$worksheet->write($row_position+1, 7, "Descuento Adicional ".$PORC_DSCTO2."% ", $border_item_left);
				$worksheet->write($row_position+1, 9, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+1, 10, $MONTO_DSCTO2, $monto_normal);
				
				$worksheet->write($row_position+2, 7, "Total Neto ", $border_item_left);
				$worksheet->write($row_position+2, 9, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+2, 10, $TOTAL_NETO, $monto_normal);
				$worksheet->write($row_position+3, 7, "IVA ".$PORC_IVA."% ", $border_item_left);
				$worksheet->write($row_position+3, 9, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+3, 10, $MONTO_IVA, $monto_normal);
				$worksheet->write($row_position+4, 7, "Total con IVA ", $border_item_left);
				$worksheet->write($row_position+4, 9, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+4, 10, $TOTAL_CON_IVA, $monto_normal);
				$worksheet->write($row_position+5, 7, " ", $border_item_top);
				$worksheet->write($row_position+5, 8, " ", $border_item_top);
				$worksheet->write($row_position+5, 9, " ", $border_item_top);
				$worksheet->write($row_position+5, 10, " ", $border_item_top);
			}
			else
			{	
				$worksheet->write($row_position, 7, "Total Neto ", $border_item_left);
				$worksheet->write($row_position, 9, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position, 10, $TOTAL_NETO, $monto_normal);
				$worksheet->write($row_position+1, 7, "IVA ".$PORC_IVA."% ", $border_item_left);
				$worksheet->write($row_position+1, 9, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+1, 10, $MONTO_IVA, $monto_normal);
				$worksheet->write($row_position+2, 7, "Total con IVA ", $border_item_left);
				$worksheet->write($row_position+2, 9, $SIMBOLO, $text_normal_right);
				$worksheet->write($row_position+2, 10, $TOTAL_CON_IVA, $monto_normal);
				$worksheet->write($row_position+3, 7, " ", $border_item_top);
				$worksheet->write($row_position+3, 8, " ", $border_item_top);
				$worksheet->write($row_position+3, 9, " ", $border_item_top);
				$worksheet->write($row_position+3, 10, " ", $border_item_top);	
			}
		}
		
		
		$worksheet->write($row_position+7, 1, "Condiciones Generales:", $text_blue_bold_left);
		$worksheet->write($row_position+8, 1, "Foma de Pago", $text_blue_bold_left);
		$worksheet->write($row_position+8, 3, $NOM_FORMA_PAGO, $text_normal_left);
		$worksheet->write($row_position+9, 1, "Validez Oferta", $text_blue_bold_left);
		$worksheet->write($row_position+9, 3, $VALIDEZ_OFERTA." DÍAS", $text_normal_left);
		$worksheet->write($row_position+10, 1, "Entrega", $text_blue_bold_left);
		$worksheet->write($row_position+10, 3, $ENTREGA, $text_normal_left);
		$worksheet->write($row_position+11, 1, "Embalaje", $text_blue_bold_left);
		$worksheet->write($row_position+11, 3, $NOM_EMBALAJE_COTIZACION, $text_normal_left);
		$worksheet->write($row_position+12, 1, "Flete", $text_blue_bold_left);
		$worksheet->write($row_position+12, 3, $NOM_FLETE_COTIZACION, $text_normal_left);
		$worksheet->write($row_position+13, 1, "Instalación", $text_blue_bold_left);
		$worksheet->write($row_position+13, 3, $NOM_INSTALACION_COTIZACION, $text_normal_left);
		$worksheet->write($row_position+14, 1, "Garantía", $text_blue_bold_left);
		$worksheet->write($row_position+14, 3, $GARANTIA, $text_normal_left);
		$worksheet->write($row_position+15, 1, "Notas", $text_blue_bold_left);
		$worksheet->write($row_position+16, 1, $OBS, $text_normal_left);
		
		$worksheet->write($row_position+19, 8, $NOM_EMPRESA_EMISOR, $text_blue_bold_center);
		$worksheet->write($row_position+20, 8, $NOM_USUARIO, $text_blue_bold_center);
		$worksheet->write($row_position+21, 8, $MAIL_U, $text_blue_bold_center);
		$worksheet->write($row_position+22, 8, $FONO_U."-".$CEL_U, $text_blue_bold_center);

		$worksheet->write($row_position+25, 1, $NOM_EMPRESA_EMISOR." - RUT: ".$RUT_EMPRESA." - ".$DIR_EMPRESA." - ".$CIUDAD_EMPRESA." - ".$PAIS_EMPRESA." - ".$TEL_EMPRESA." - ".$FAX_EMPRESA, $text_pie_de_pagina);
		$worksheet->write($row_position+26, 5, $MAIL_EMPRESA." - ".$SITIO_WEB_EMPRESA, $text_pie_de_pagina);
		
		
		$workbook->close();
		
		header("Content-Type: application/x-msexcel; name=\"Cotizacion Ampliada N° $COD_COTIZACION.xls\"");
		header("Content-Disposition: inline; filename=\"Cotizacion Ampliada N° $COD_COTIZACION.xls\"");
		$fh=fopen($fname, "rb");
		fpassthru($fh);
		unlink($fname);
		
		error_reporting(E_ALL);

	}
	
	function printcot_pesomedida_excel($con_logo, $embalada) {
		
		$print_descto = session::get('PRINT_DESCUENTO');
		error_reporting(E_ALL & ~E_NOTICE);

		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");
		
		$fname = tempnam("/tmp", "pesomedida.xls");
		$workbook = &new writeexcel_workbook($fname);
		$cod_cotizacion = $this->get_key();
		$worksheet = &$workbook->addworksheet('COTIZACION_'.$cod_cotizacion);
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT C.COD_COTIZACION,
					E.NOM_EMPRESA,
					E.RUT,
					E.DIG_VERIF,
					dbo.f_format_date(getdate(), 3) FECHA_IMPRESO,
					dbo.f_format_date(C.FECHA_COTIZACION, 3) FECHA_COTIZACION,				
					dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[DIRECCION]') DIRECCION,
					dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[NOM_COMUNA]') NOM_COMUNA,
					dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[NOM_CIUDAD]') NOM_CIUDAD,
					SF.TELEFONO TELEFONO_F,
					SF.FAX FAX_F,
					C.REFERENCIA,
					P.NOM_PERSONA,
					P.EMAIL,
					p.TELEFONO,
					IC.NOM_PRODUCTO,
					case IC.COD_PRODUCTO
						when 'T' then ''
						else IC.ITEM
					end ITEM,
					case IC.COD_PRODUCTO
						when 'T' then null
						else IC.COD_PRODUCTO
					end COD_PRODUCTO,
					case IC.COD_PRODUCTO
						when 'T' then null
						else IC.CANTIDAD
					end CANTIDAD,";
					
			if($embalada == 'noembalada'){
				$sql.= "case PR.COD_PRODUCTO
							when 'T' then null
							else PR.LARGO
						end LARGO,
						case PR.COD_PRODUCTO
							when 'T' then null
							else PR.ANCHO
						end ANCHO,
						case PR.COD_PRODUCTO
							when 'T' then null
							else PR.ALTO
						end ALTO,
						case PR.COD_PRODUCTO
							when 'T' then null
							else PR.PESO
						end PESO,
						case PR.COD_PRODUCTO
							when 'T' then null
							else ((PR.LARGO)*(PR.ANCHO)*(PR.ALTO))/1000000 
						end VOLUMEN,
						case PR.COD_PRODUCTO
							when 'T' then null
							else IC.CANTIDAD * (((PR.LARGO)*(PR.ANCHO)*(PR.ALTO))/1000000)
						end VOLT,
						case PR.COD_PRODUCTO
							when 'T' then null
							else IC.CANTIDAD * PR.PESO
						end PESOT,
						'Especificaciones Equipo sin Embalaje' TITLE_ITEM, ";
			}else{
				$sql.= "case PR.COD_PRODUCTO
							when 'T' then null
							else PR.LARGO_EMBALADO
						end LARGO,
						case PR.COD_PRODUCTO
							when 'T' then null
							else PR.ANCHO_EMBALADO
						end ANCHO,
						case PR.COD_PRODUCTO
							when 'T' then null
							else PR.ALTO_EMBALADO
						end ALTO,
						case PR.COD_PRODUCTO
							when 'T' then null
							else PR.PESO_EMBALADO
						end PESO,
						case PR.COD_PRODUCTO
							when 'T' then null
							else ((PR.LARGO_EMBALADO)*(PR.ANCHO_EMBALADO)*(PR.ALTO_EMBALADO))/1000000 
						end VOLUMEN,
						case PR.COD_PRODUCTO
							when 'T' then null
							else IC.CANTIDAD * (((PR.LARGO_EMBALADO)*(PR.ANCHO_EMBALADO)*(PR.ALTO_EMBALADO))/1000000)
						end VOLT,
						case PR.COD_PRODUCTO
							when 'T' then null
							else IC.CANTIDAD * PR.PESO_EMBALADO
						end PESOT,
						'Especificaciones Equipo con Embalaje' TITLE_ITEM, ";
			}
					
			$sql.= "U. NOM_USUARIO,
					U.MAIL MAIL_U,
					U.TELEFONO FONO_U,
					U.CELULAR CEL_U,
					dbo.f_get_parametro(".self::K_PARAM_NOM_EMPRESA.") NOM_EMPRESA_EMISOR,
					dbo.f_get_parametro(".self::K_PARAM_RUT_EMPRESA.") RUT_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_DIR_EMPRESA.") DIR_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_TEL_EMPRESA.") TEL_EMPRESA,	
					dbo.f_get_parametro(".self::K_PARAM_FAX_EMPRESA.") FAX_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_MAIL_EMPRESA.") MAIL_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_CIUDAD_EMPRESA.") CIUDAD_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_PAIS_EMPRESA.") PAIS_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_SITIO_WEB_EMPRESA.") SITIO_WEB_EMPRESA
			FROM COTIZACION C, EMPRESA E, PERSONA P,
					ITEM_COTIZACION IC, USUARIO U, PRODUCTO PR,
					SUCURSAL SF, SUCURSAL SD
			WHERE C.COD_COTIZACION = $cod_cotizacion AND 
					E.COD_EMPRESA = C.COD_EMPRESA AND
					P.COD_PERSONA = C.COD_PERSONA AND
					IC.COD_COTIZACION = C.COD_COTIZACION AND
					U.COD_USUARIO = C.COD_USUARIO_VENDEDOR1 and
					SF.COD_SUCURSAL = C.COD_SUCURSAL_FACTURA AND						
					SD.COD_SUCURSAL = C.COD_SUCURSAL_DESPACHO AND
			    	PR.COD_PRODUCTO = IC.COD_PRODUCTO 
					order by IC.ORDEN asc";
				
		$result = $db->build_results($sql);
		
		$worksheet->set_row(0, 60);
		$worksheet->set_column(0, 0, 4);
		$worksheet->set_column(1, 2, 7);
		$worksheet->set_column(3, 9, 14);
		$worksheet->set_column(2, 10, 12);
		
		$worksheet->set_column(6, 10, 10);
		$worksheet->insert_bitmap('B1',$this->root_dir."images_appl/logo_reporte_excel.bmp");
		
	
		$text =& $workbook->addformat();
		$text->set_font("Verdana");
		$text->set_valign('vcenter');
    
		$text_bold =& $workbook->addformat();
		$text_bold->copy($text);
		$text_bold->set_bold(1);
	
		$text_blue_bold_left =& $workbook->addformat();
		$text_blue_bold_left->copy($text_bold);
		$text_blue_bold_left->set_align('left');
		$text_blue_bold_left->set_color('blue_0x20');

		$text_blue_bold_center =& $workbook->addformat();
		$text_blue_bold_center->copy($text_bold);
		$text_blue_bold_center->set_align('center');
		$text_blue_bold_center->set_color('blue_0x20');
		
		$text_blue_bold_right =& $workbook->addformat();
		$text_blue_bold_right->copy($text_bold);
		$text_blue_bold_right->set_align('right');
		$text_blue_bold_right->set_color('blue_0x20');

		$text_nro_docto =& $workbook->addformat();
		$text_nro_docto->copy($text_blue_bold_right);
		$text_nro_docto->set_size(13);
		
		$text_pie_de_pagina =& $workbook->addformat();
		$text_pie_de_pagina->copy($text_blue_bold_left);
		$text_pie_de_pagina->set_size(8);
		
		$text_normal_left =& $workbook->addformat();
		$text_normal_left->copy($text);
		$text_normal_left->set_align('left');
		
		$text_normal_center =& $workbook->addformat();
		$text_normal_center->copy($text);
		$text_normal_center->set_align('center');
		
		$text_normal_right =& $workbook->addformat();
		$text_normal_right->copy($text);
		$text_normal_right->set_align('right');
				
		$text_normal_bold_left =& $workbook->addformat();
		$text_normal_bold_left->copy($text_bold);
		$text_normal_bold_left->set_align('left');
		
		
		$text_normal_bold_center =& $workbook->addformat();
		$text_normal_bold_center->copy($text_bold);
		$text_normal_bold_center->set_align('center');
	
		$text_normal_bold_right =& $workbook->addformat();
		$text_normal_bold_right->copy($text_bold);
		$text_normal_bold_right->set_align('right');
	
		
		$titulo_item_border_all =& $workbook->addformat();
		$titulo_item_border_all->copy($text_blue_bold_center);
		$titulo_item_border_all->set_border_color('black');
		$titulo_item_border_all->set_top(2);
		$titulo_item_border_all->set_bottom(2);
		$titulo_item_border_all->set_right(2);
		$titulo_item_border_all->set_left(2);
		
		$titulo_item_border_all_2 =& $workbook->addformat();
		$titulo_item_border_all_2->copy($text_blue_bold_center);
		$titulo_item_border_all_2->set_border_color('black');
		$titulo_item_border_all_2->set_bottom(2);
		$titulo_item_border_all_2->set_right(2);
		$titulo_item_border_all_2->set_left(2);
		
		$titulo_item_border_all_3 =& $workbook->addformat();
		$titulo_item_border_all_3->copy($text_blue_bold_center);
		$titulo_item_border_all_3->set_border_color('black');
		$titulo_item_border_all_3->set_bottom(2);
		
		$titulo_item_border_all_4 =& $workbook->addformat();
		$titulo_item_border_all_4->copy($text_blue_bold_center);
		$titulo_item_border_all_4->set_border_color('black');
		$titulo_item_border_all_4->set_top(2);
		$titulo_item_border_all_4->set_right(2);
		$titulo_item_border_all_4->set_left(2);
		
		$titulo_item_border_all_5 =& $workbook->addformat();
		$titulo_item_border_all_5->copy($text_blue_bold_center);
		$titulo_item_border_all_5->set_border_color('black');
		$titulo_item_border_all_5->set_top(2);
		$titulo_item_border_all_5->set_left(2);
		
		
		$titulo_item_border_all_merge =& $workbook->addformat();
		$titulo_item_border_all_merge->copy($titulo_item_border_all);
		$titulo_item_border_all_merge->set_merge();
				
	
		$border_item_left = & $workbook->addformat();
		$border_item_left->copy($text_normal_left);
		$border_item_left->set_border_color('black');
		$border_item_left->set_left(2);
		
		$border_item_left_bold = & $workbook->addformat();
		$border_item_left_bold->copy($text_bold);
		$border_item_left_bold->set_border_color('black');
		$border_item_left_bold->set_left(2);
		
		$border_item_left_bold_2 = & $workbook->addformat();
		$border_item_left_bold_2->copy($text_bold);
		$border_item_left_bold_2->set_border_color('black');
		$border_item_left_bold_2->set_bottom(2);
		$border_item_left_bold_2->set_left(2);
		
		$border_item_center = & $workbook->addformat();
		$border_item_center->copy($text_normal_center);
		$border_item_center->set_border_color('black');
		$border_item_center->set_left(2);
		$border_item_center->set_right(2);
		
		$border_item_right = & $workbook->addformat();
		$border_item_right->copy($text_normal_right);
		$border_item_right->set_border_color('black');
		$border_item_right->set_right(2);		
		
		$cant_normal =& $workbook->addformat();
		$cant_normal->copy($border_item_right);
		$cant_normal->set_num_format('0.0');
		
		$cant_normal_total =& $workbook->addformat();
		$cant_normal_total->copy($border_item_right);
		$cant_normal_total->set_num_format('0.0');
		$cant_normal_total->set_border_color('black');
		$cant_normal_total->set_bold(1);
		$cant_normal_total->set_top(2);
		$cant_normal_total->set_bottom(2);
		$cant_normal_total->set_right(2);
		$cant_normal_total->set_left(2);
					
		$monto_normal =& $workbook->addformat();
		$monto_normal->copy($border_item_right);
		$monto_normal->set_num_format('#,##0');
		
		$border_item_top = & $workbook->addformat();
		$border_item_top->copy($text);
		$border_item_top->set_border_color('black');
		$border_item_top->set_top(2);
		
		$border_item_top_2 = & $workbook->addformat();
		$border_item_top_2->copy($text);
		$border_item_top_2->set_color('blue_0x20');
		$border_item_top_2->set_border_color('black');
		$border_item_top_2->set_top(2);
		$border_item_top_2->set_bold(1);
		
		
		$border_item_bottom = & $workbook->addformat();
		$border_item_bottom->copy($text);
		$border_item_bottom->set_border_color('black');
		$border_item_bottom->set_bottom(2);
		
		$border_item_total = & $workbook->addformat();
		$border_item_total->copy($text);
		$border_item_total->set_border_color('black');
		$border_item_total->set_top(2);
		$border_item_total->set_bottom(2);
		$border_item_total->set_right(2);
		
		$border_item_especial_left = & $workbook->addformat();
		$border_item_especial_left->copy($text_normal_left);
		$border_item_especial_left->set_border_color('black');
		$border_item_especial_left->set_left(2);
		$border_item_especial_left->set_right(2);
		
		$border_item_especial_right = & $workbook->addformat();
		$border_item_especial_right->copy($text_normal_right);
		$border_item_especial_right->set_border_color('black');
		$border_item_especial_right->set_left(2);
		$border_item_especial_right->set_right(2);
		
		$border_item_especial_total = & $workbook->addformat();
		$border_item_especial_total->copy($border_item_total);
		$border_item_especial_total->set_border_color('black');
		$border_item_especial_total->set_bold(1);
		
		$border_item_total_2 = & $workbook->addformat();
		$border_item_total_2->copy($border_item_total);
		$border_item_total_2->set_left(2);
		$border_item_total_2->set_align('center');
		$border_item_total_2->set_color('blue_0x20');
		$border_item_total_2->set_bold(1);
		
		$COD_COTIZACION = $result[0]['COD_COTIZACION'];
		$FECHA_IMPRESO = $result[0]['FECHA_IMPRESO'];
		$FECHA_COTIZACION = $result[0]['FECHA_COTIZACION'];
		$NOM_EMPRESA = $result[0]['NOM_EMPRESA'];
		$RUT = $result[0]['RUT'];
		$DIG_VERIF = $result[0]['DIG_VERIF'];
		$DIRECCION = $result[0]['DIRECCION'];
		$NOM_COMUNA = $result[0]['NOM_COMUNA'];
		$NOM_CIUDAD = $result[0]['NOM_CIUDAD'];
		$TELEFONO_F = $result[0]['TELEFONO_F'];
		$FAX_F = $result[0]['FAX_F'];	
		$NOM_PERSONA = $result[0]['NOM_PERSONA'];
		$EMAIL = $result[0]['EMAIL'];
		$REFERENCIA = $result[0]['REFERENCIA'];
		$SIMBOLO = $result[0]['SIMBOLO'];
		$TITLE_ITEM = $result[0]['TITLE_ITEM'];
		
		$worksheet->write(1, 11, "COTIZACION Nº".$COD_COTIZACION, $text_nro_docto);
		$worksheet->write(1, 1, "Santiago,".$FECHA_COTIZACION, $text_blue_bold_left);
		$worksheet->write(3, 1, "Razón Social", $text_blue_bold_left);
		
		$worksheet->write(3, 3, $NOM_EMPRESA, $text_normal_bold_left);
		$worksheet->write(3, 8, "Rut", $text_blue_bold_left);
		
		$rut=number_format($RUT, 0, ',', '.');
		$rut=$rut.'-'.$DIG_VERIF;
		
		$worksheet->write(3, 9, $rut, $text_normal_bold_left);
		
		$worksheet->write(4, 1, "Dirección", $text_blue_bold_left);
		$worksheet->write(4, 3, $DIRECCION, $text_normal_left);
		$worksheet->write(5, 1, "Comuna", $text_blue_bold_left);
		$worksheet->write(5, 3, $NOM_COMUNA, $text_normal_left);
		$worksheet->write(5, 4, "Ciudad", $text_blue_bold_left);
		$worksheet->write(5, 5, $NOM_CIUDAD, $text_normal_left);
		$worksheet->write(5, 6, "Fono", $text_blue_bold_left);
		$worksheet->write(5, 7, $TELEFONO_F, $text_normal_left);
		$worksheet->write(5, 8, "Fax",$text_blue_bold_left);
		$worksheet->write(5, 9, $FAX_F,$text_normal_left);
		$worksheet->write(6, 1, "Atención", $text_blue_bold_left);
		$worksheet->write(6, 3, $NOM_PERSONA." ".$EMAIL, $text_normal_left);
		$worksheet->write(7, 1, "Referencia",$text_blue_bold_left);
		$worksheet->write(7, 3, $REFERENCIA,$text_normal_left);
		$worksheet->write(8, 7, $TITLE_ITEM,$border_item_total_2);
		$worksheet->write(8, 8, "",$border_item_total_2);
		$worksheet->write(8, 9, "",$border_item_total_2);
		$worksheet->write(8, 10, "",$border_item_total_2);
		$worksheet->write(8, 11, "",$border_item_total_2);
		$worksheet->write(8, 12, "",$border_item_total_2);
		$worksheet->write(8, 13, "",$border_item_total_2);
		$worksheet->merge_cells(8, 7, 8, 13);
		
		$worksheet->write(9, 1, "Ítem", $titulo_item_border_all_4);
		$worksheet->write(10, 1, "", $titulo_item_border_all_2);
		$worksheet->write(9, 2, "Modelo", $titulo_item_border_all_4);
		$worksheet->write(10, 2, "", $titulo_item_border_all_2);
		$worksheet->write(9, 3, "",$titulo_item_border_all_4);
		$worksheet->write(10, 3, "",$titulo_item_border_all_3);
		$worksheet->write(9, 4, "", $titulo_item_border_all_4);
		$worksheet->write(10, 4, "", $titulo_item_border_all_3);
		$worksheet->write(9, 5, "", $titulo_item_border_all_4);
		$worksheet->write(10, 5, "", $titulo_item_border_all_3);
		$worksheet->write(9, 3, "Producto", $titulo_item_border_all_4);
		$worksheet->merge_cells(9, 3, 9, 5);
		$worksheet->write(9, 6, "CT", $titulo_item_border_all_4);
		$worksheet->write(10, 6, "", $titulo_item_border_all_2);
		$worksheet->write(9, 7, "Largo", $titulo_item_border_all_4);
		$worksheet->write(10, 7, "[cm]", $titulo_item_border_all_2);
		$worksheet->write(9, 8, "Ancho", $titulo_item_border_all_4);
		$worksheet->write(10, 8, "[cm]", $titulo_item_border_all_2);
		$worksheet->write(9, 9, "Alto", $titulo_item_border_all_4);
		$worksheet->write(10, 9, "[cm]", $titulo_item_border_all_2);
		$worksheet->write(9, 10, "Vol", $titulo_item_border_all_4);
		$worksheet->write(10, 10, "[mt3]", $titulo_item_border_all_2);
		$worksheet->write(9, 11, "Peso", $titulo_item_border_all_4);
		$worksheet->write(10, 11, "[kg]", $titulo_item_border_all_2);
		$worksheet->write(9, 12, "Vol", $titulo_item_border_all_4);
		$worksheet->write(10, 12, "Total", $titulo_item_border_all_2);
		$worksheet->write(9, 13, "Peso", $titulo_item_border_all_4);
		$worksheet->write(10, 13, "Total", $titulo_item_border_all_2);
		
		$SUMP=0;
		$SUMV=0;
		for ($i=0 ; $i <count($result); $i++) {
			$ITEM = $result[$i]['ITEM'];
			$NOM_PRODUCTO = $result[$i]['NOM_PRODUCTO'];
			$COD_PRODUCTO = $result[$i]['COD_PRODUCTO'];
			$CANTIDAD = $result[$i]['CANTIDAD'];
			$LARGO = $result[$i]['LARGO'];
			$ANCHO = $result[$i]['ANCHO'];
			$ALTO = $result[$i]['ALTO'];
			$VOLUMEN = $result[$i]['VOLUMEN'];
			$PESO = $result[$i]['PESO'];
			$VOLT = $result[$i]['VOLT'];
			$PESOT = $result[$i]['PESOT'];
			
			$SUMP=$SUMP+$PESOT;
			$SUMV=$SUMV+$VOLT;
			$worksheet->write(11+$i, 1,$ITEM, $border_item_left);
			$worksheet->write(11+$i, 2,$COD_PRODUCTO,$border_item_left);
			
			if($COD_PRODUCTO == '')
				$worksheet->write(11+$i, 3, $NOM_PRODUCTO, $border_item_left_bold);
			else
				$worksheet->write(11+$i, 3, $NOM_PRODUCTO, $border_item_left);
		$worksheet->write(11+$i, 6,$CANTIDAD, $border_item_especial_right);
		$worksheet->write(11+$i, 7,$LARGO, $cant_normal);
		$worksheet->write(11+$i, 8,$ANCHO, $cant_normal);
		$worksheet->write(11+$i, 9,$ALTO, $cant_normal);
		$worksheet->write(11+$i, 10,number_format($VOLUMEN, 4, ',', '.'), $cant_normal);
		$worksheet->write(11+$i, 11,$PESO, $cant_normal);
		$worksheet->write(11+$i, 12,number_format($VOLT, 4, ',', '.'), $cant_normal);
		$worksheet->write(11+$i, 13,$PESOT, $cant_normal);
		
		}
		
		$worksheet->write(11+$i, 1, " ", $border_item_top);
		$worksheet->write(11+$i, 2, " ", $border_item_top);
		$worksheet->write(11+$i, 3, " ", $border_item_top);
		$worksheet->write(11+$i, 4, " ", $border_item_top);
		$worksheet->write(11+$i, 5, " ", $border_item_top);
		$worksheet->write(11+$i, 6, " ", $border_item_top);
		$worksheet->write(11+$i, 7, " ", $border_item_top);
		$worksheet->write(11+$i, 8, " ", $border_item_top);
		$worksheet->write(11+$i, 9, " ", $border_item_top);
		$worksheet->write(11+$i, 10, " ", $border_item_top);
		$worksheet->write(11+$i, 11, "TOTAL", $border_item_top_2);
		$worksheet->write(11+$i, 12, number_format($SUMV, 4, ',', '.'), $cant_normal_total);
		$worksheet->write(11+$i, 13, $SUMP, $cant_normal_total);

		$NOM_EMPRESA_EMISOR = $result[0]['NOM_EMPRESA_EMISOR'];
		$RUT_EMPRESA = $result[0]['RUT_EMPRESA'];
		$DIR_EMPRESA = $result[0]['DIR_EMPRESA'];
		$CIUDAD_EMPRESA = $result[0]['CIUDAD_EMPRESA'];
		$PAIS_EMPRESA = $result[0]['PAIS_EMPRESA'];
		$TEL_EMPRESA = $result[0]['TEL_EMPRESA'];
		$FAX_EMPRESA = $result[0]['FAX_EMPRESA'];
		$MAIL_EMPRESA = $result[0]['MAIL_EMPRESA'];
		$SITIO_WEB_EMPRESA = $result[0]['SITIO_WEB_EMPRESA'];
		$NOM_USUARIO= $result[0]['NOM_USUARIO'];
		$MAIL_U= $result[0]['MAIL_U'];
		$CEL_U= $result[0]['CEL_U'];
		$FONO_U= $result[0]['FONO_U'];

		$FINAL = $result[0]['FINAL'];

		$worksheet->write($row_position+19, 10, $NOM_EMPRESA_EMISOR, $text_blue_bold_center);
		$worksheet->write($row_position+20, 10, $NOM_USUARIO, $text_blue_bold_center);
		$worksheet->write($row_position+21, 10, $MAIL_U, $text_blue_bold_center);
		$worksheet->write($row_position+22, 10, $FONO_U."-".$CEL_U, $text_blue_bold_center);

		$worksheet->write($row_position+25, 1, $NOM_EMPRESA_EMISOR." - RUT: ".$RUT_EMPRESA." - ".$DIR_EMPRESA." - ".$CIUDAD_EMPRESA." - ".$PAIS_EMPRESA." - ".$TEL_EMPRESA." - ".$FAX_EMPRESA, $text_pie_de_pagina);
		$worksheet->write($row_position+26, 5, $MAIL_EMPRESA." - ".$SITIO_WEB_EMPRESA, $text_pie_de_pagina);
		
		
		$workbook->close();
		
		header("Content-Type: application/x-msexcel; name=\"cotizacion_peso_medida.xls\"");
		header("Content-Disposition: inline; filename=\"cotizacion_peso_medida.xls\"");
		$fh=fopen($fname, "rb");
		fpassthru($fh);
		unlink($fname);
		
		error_reporting(E_ALL);
		
	}
	function printcot_tecnica_electrico_excel($con_logo) {
		
		$print_descto = session::get('PRINT_DESCUENTO');
		error_reporting(E_ALL & ~E_NOTICE);
		
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");
		
		$fname = tempnam("/tmp", "producto_electrico.xls");
		$workbook = &new writeexcel_workbook($fname);
		$cod_cotizacion = $this->get_key();
		$worksheet = &$workbook->addworksheet('COTIZACION_'.$cod_cotizacion);
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$sql = "exec spr_cot_tecnica $cod_cotizacion, 'ELECTRICIDAD'";
				
		$result = $db->build_results($sql);
		
		$worksheet->set_row(0, 60);
		$worksheet->set_column(0, 0, 4);
		$worksheet->set_column(1, 2, 7);
		$worksheet->set_column(3, 9, 14);
		$worksheet->set_column(2, 10, 12);
		
		$worksheet->set_column(8, 10, 12);
		$worksheet->set_column(6, 6, 9);
		$worksheet->set_column(7, 7, 11);
		$worksheet->set_column(11, 12, 11);
		$worksheet->insert_bitmap('B1',$this->root_dir."images_appl/logo_reporte_excel.bmp");
		
	
		$text =& $workbook->addformat();
		$text->set_font("Verdana");
		$text->set_valign('vcenter');
    
		$text_bold =& $workbook->addformat();
		$text_bold->copy($text);
		$text_bold->set_bold(1);
	
		$text_blue_bold_left =& $workbook->addformat();
		$text_blue_bold_left->copy($text_bold);
		$text_blue_bold_left->set_align('left');
		$text_blue_bold_left->set_color('blue_0x20');

		$text_blue_bold_center =& $workbook->addformat();
		$text_blue_bold_center->copy($text_bold);
		$text_blue_bold_center->set_align('center');
		$text_blue_bold_center->set_color('blue_0x20');
		
		$text_blue_bold_right =& $workbook->addformat();
		$text_blue_bold_right->copy($text_bold);
		$text_blue_bold_right->set_align('right');
		$text_blue_bold_right->set_color('blue_0x20');

		$text_nro_docto =& $workbook->addformat();
		$text_nro_docto->copy($text_blue_bold_right);
		$text_nro_docto->set_size(13);
		
		$text_nro_docto_2 =& $workbook->addformat();
		$text_nro_docto_2->copy($text_blue_bold_left);
		$text_nro_docto_2->set_size(13);
		
		$text_pie_de_pagina =& $workbook->addformat();
		$text_pie_de_pagina->copy($text_blue_bold_left);
		$text_pie_de_pagina->set_size(8);
		
		$text_normal_left =& $workbook->addformat();
		$text_normal_left->copy($text);
		$text_normal_left->set_align('left');
		
		$text_normal_center =& $workbook->addformat();
		$text_normal_center->copy($text);
		$text_normal_center->set_align('center');
		
		$text_normal_right =& $workbook->addformat();
		$text_normal_right->copy($text);
		$text_normal_right->set_align('right');
				
		$text_normal_bold_left =& $workbook->addformat();
		$text_normal_bold_left->copy($text_bold);
		$text_normal_bold_left->set_align('left');
		
		
		$text_normal_bold_center =& $workbook->addformat();
		$text_normal_bold_center->copy($text_bold);
		$text_normal_bold_center->set_align('center');
	
		$text_normal_bold_right =& $workbook->addformat();
		$text_normal_bold_right->copy($text_bold);
		$text_normal_bold_right->set_align('right');
	
		
		$titulo_item_border_all =& $workbook->addformat();
		$titulo_item_border_all->copy($text_blue_bold_center);
		$titulo_item_border_all->set_border_color('black');
		$titulo_item_border_all->set_top(2);
		$titulo_item_border_all->set_bottom(2);
		$titulo_item_border_all->set_right(2);
		$titulo_item_border_all->set_left(2);
		
		$titulo_item_border_all_2 =& $workbook->addformat();
		$titulo_item_border_all_2->copy($text_blue_bold_center);
		$titulo_item_border_all_2->set_border_color('black');
		$titulo_item_border_all_2->set_bottom(2);
		$titulo_item_border_all_2->set_right(2);
		$titulo_item_border_all_2->set_left(2);
		
		$titulo_item_border_all_3 =& $workbook->addformat();
		$titulo_item_border_all_3->copy($text_blue_bold_center);
		$titulo_item_border_all_3->set_border_color('black');
		$titulo_item_border_all_3->set_bottom(2);
		
		$titulo_item_border_all_4 =& $workbook->addformat();
		$titulo_item_border_all_4->copy($text_blue_bold_center);
		$titulo_item_border_all_4->set_border_color('black');
		$titulo_item_border_all_4->set_top(2);
		$titulo_item_border_all_4->set_right(2);
		$titulo_item_border_all_4->set_left(2);
		
		$titulo_item_border_all_5 =& $workbook->addformat();
		$titulo_item_border_all_5->copy($text_blue_bold_center);
		$titulo_item_border_all_5->set_border_color('black');
		$titulo_item_border_all_5->set_top(2);
		$titulo_item_border_all_5->set_left(2);
		
		
		$titulo_item_border_all_merge =& $workbook->addformat();
		$titulo_item_border_all_merge->copy($titulo_item_border_all);
		$titulo_item_border_all_merge->set_merge();
				
	
		$border_item_left = & $workbook->addformat();
		$border_item_left->copy($text_normal_left);
		$border_item_left->set_border_color('black');
		$border_item_left->set_left(2);
		
		$border_item_left_bold = & $workbook->addformat();
		$border_item_left_bold->copy($text_bold);
		$border_item_left_bold->set_border_color('black');
		$border_item_left_bold->set_left(2);
		
		$border_item_left_bold_2 = & $workbook->addformat();
		$border_item_left_bold_2->copy($text_bold);
		$border_item_left_bold_2->set_border_color('black');
		$border_item_left_bold_2->set_bottom(2);
		$border_item_left_bold_2->set_left(2);
		
		$border_item_center = & $workbook->addformat();
		$border_item_center->copy($text_normal_center);
		$border_item_center->set_border_color('black');
		$border_item_center->set_left(2);
		$border_item_center->set_right(2);
		
		$border_item_right = & $workbook->addformat();
		$border_item_right->copy($text_normal_right);
		$border_item_right->set_border_color('black');
		$border_item_right->set_right(2);		
		
		$cant_normal =& $workbook->addformat();
		$cant_normal->copy($border_item_right);
		$cant_normal->set_num_format('0.0');
		
		$cant_normal_total =& $workbook->addformat();
		$cant_normal_total->copy($border_item_right);
		$cant_normal_total->set_num_format('0.0');
		$cant_normal_total->set_border_color('black');
		$cant_normal_total->set_bold(1);
		$cant_normal_total->set_top(2);
		$cant_normal_total->set_bottom(2);
		$cant_normal_total->set_right(2);
		$cant_normal_total->set_left(2);
					
		$monto_normal =& $workbook->addformat();
		$monto_normal->copy($border_item_right);
		$monto_normal->set_num_format('#,##0');
		
		$border_item_top = & $workbook->addformat();
		$border_item_top->copy($text);
		$border_item_top->set_border_color('black');
		$border_item_top->set_top(2);
		
		$border_item_top_2 = & $workbook->addformat();
		$border_item_top_2->copy($text);
		$border_item_top_2->set_color('blue_0x20');
		$border_item_top_2->set_border_color('black');
		$border_item_top_2->set_top(2);
		$border_item_top_2->set_bold(1);
		
		
		$border_item_bottom = & $workbook->addformat();
		$border_item_bottom->copy($text);
		$border_item_bottom->set_border_color('black');
		$border_item_bottom->set_bottom(2);
		
		$border_item_total = & $workbook->addformat();
		$border_item_total->copy($text);
		$border_item_total->set_border_color('black');
		$border_item_total->set_top(2);
		$border_item_total->set_bottom(2);
		$border_item_total->set_right(2);
		
		$border_item_especial_left = & $workbook->addformat();
		$border_item_especial_left->copy($text_normal_left);
		$border_item_especial_left->set_border_color('black');
		$border_item_especial_left->set_left(2);
		$border_item_especial_left->set_right(2);
		
		$border_item_especial_right = & $workbook->addformat();
		$border_item_especial_right->copy($text_normal_right);
		$border_item_especial_right->set_border_color('black');
		$border_item_especial_right->set_left(2);
		$border_item_especial_right->set_right(2);
		
		$border_item_especial_total = & $workbook->addformat();
		$border_item_especial_total->copy($border_item_total);
		$border_item_especial_total->set_border_color('black');
		$border_item_especial_total->set_bold(1);
		
		
		$COD_COTIZACION = $result[0]['COD_COTIZACION'];
		$FECHA_COTIZACION = $result[0]['FECHA_COTIZACION'];
		$FECHA_IMPRESO = $result[0]['FECHA_IMPRESO'];
		$NOM_EMPRESA = $result[0]['NOM_EMPRESA'];
		$RUT = $result[0]['RUT'];
		$DIG_VERIF = $result[0]['DIG_VERIF'];
		$DIRECCION = $result[0]['DIRECCION'];
		$COMUNA = $result[0]['COMUNA'];
		$CIUDAD = $result[0]['CIUDAD'];
		$TELEFONO = $result[0]['TELEFONO'];
		$FAX = $result[0]['FAX'];	
		$NOM_PERSONA = $result[0]['NOM_PERSONA'];
		$EMAIL = $result[0]['EMAIL'];
		$REFERENCIA = $result[0]['REFERENCIA'];
		$SIMBOLO = $result[0]['SIMBOLO'];
		
		$worksheet->write(1, 9, "COTIZACION Nº".$COD_COTIZACION, $text_nro_docto);
		$worksheet->write(2, 1, "PRODUCTOS ELECTRICOS", $text_nro_docto_2);
		$worksheet->write(4, 1, "Santiago,".$FECHA_COTIZACION, $text_blue_bold_left);
		$worksheet->write(6, 1, "Razón Social", $text_blue_bold_left);
		
		$worksheet->write(6, 3, $NOM_EMPRESA, $text_normal_bold_left);
		$worksheet->write(6, 8, "Rut", $text_blue_bold_left);
		
		$rut=number_format($RUT, 0, ',', '.');
		$rut=$rut.'-'.$DIG_VERIF;
		
		$worksheet->write(6, 9, $rut, $text_normal_bold_left);
		
		$worksheet->write(7, 1, "Dirección", $text_blue_bold_left);
		$worksheet->write(7, 3, $DIRECCION, $text_normal_left);
		$worksheet->write(8, 1, "Comuna", $text_blue_bold_left);
		$worksheet->write(8, 3, $COMUNA, $text_normal_left);
		$worksheet->write(8, 4, "Ciudad", $text_blue_bold_left);
		$worksheet->write(8, 5, $CIUDAD, $text_normal_left);
		$worksheet->write(8, 6, "Fono", $text_blue_bold_left);
		$worksheet->write(8, 7, $TELEFONO, $text_normal_left);
		$worksheet->write(8, 8, "Fax",$text_blue_bold_left);
		$worksheet->write(8, 9, $FAX,$text_normal_left);
		$worksheet->write(9, 8, "Fono",$text_blue_bold_left);
		$worksheet->write(9, 9, $TELEFONO,$text_normal_left);
		$worksheet->write(9, 1, "Atención", $text_blue_bold_left);
		$worksheet->write(9, 3, $NOM_PERSONA." ".$EMAIL, $text_normal_left);
		$worksheet->write(10, 1, "Referencia",$text_blue_bold_left);
		$worksheet->write(10, 3, $REFERENCIA,$text_normal_left);
		
		$worksheet->write(12, 1, "Ítem", $titulo_item_border_all);
		$worksheet->write(12, 2, "Modelo", $titulo_item_border_all);
		$worksheet->write(12, 3, "",$titulo_item_border_all);
		$worksheet->write(12, 4, "", $titulo_item_border_all);
		$worksheet->write(12, 5, "", $titulo_item_border_all);
		$worksheet->write(12, 3, "Producto", $titulo_item_border_all);
		$worksheet->merge_cells(12, 3, 12, 6);
		$worksheet->write(12, 6, "", $titulo_item_border_all);
		$worksheet->write(12, 7, "Cantidad", $titulo_item_border_all);
		$worksheet->write(12, 8, "Voltaje", $titulo_item_border_all);
		$worksheet->write(12, 9, "Fases", $titulo_item_border_all);
		$worksheet->write(12, 10, "Ciclos", $titulo_item_border_all);
		$worksheet->write(12, 11, "[Kw]", $titulo_item_border_all);
		$worksheet->write(12, 12, "Total [Kw]", $titulo_item_border_all);
	
		
		$SUMKW=0;
		for ($i=0 ; $i <count($result); $i++) {
			$ITEM = $result[$i]['ITEM'];
			$NOM_PRODUCTO = $result[$i]['NOM_PRODUCTO'];
			$COD_PRODUCTO = $result[$i]['COD_PRODUCTO'];
			$CANTIDAD = $result[$i]['CANTIDAD'];
			$VOLTAJE = $result[$i]['VOLTAJE'];
			$FASES = $result[$i]['FASES'];
			$CICLOS = $result[$i]['CICLOS'];
			$KW = $result[$i]['KW'];
			$TOTAL_KW = $result[$i]['TOTAL_KW'];
			
			$SUMKW=$SUMKW+$TOTAL_KW;
			$worksheet->write(13+$i, 1,$ITEM, $border_item_left);
			$worksheet->write(13+$i, 2,$COD_PRODUCTO,$border_item_left);
			
			if($COD_PRODUCTO == '')
				$worksheet->write(13+$i, 3, $NOM_PRODUCTO, $border_item_left_bold);
			else
				$worksheet->write(13+$i, 3, $NOM_PRODUCTO, $border_item_left);
				
			$worksheet->write(13+$i, 7,$CANTIDAD, $border_item_especial_right);
			$worksheet->write(13+$i, 8,$VOLTAJE, $cant_normal);
			$worksheet->write(13+$i, 9,$FASES, $cant_normal);
			$worksheet->write(13+$i, 10,$CICLOS, $cant_normal);
			$worksheet->write(13+$i, 11,$KW, $cant_normal);
			$worksheet->write(13+$i, 12,$TOTAL_KW, $cant_normal);

		}
		
		$worksheet->write(13+$i, 1, " ", $border_item_top);
		$worksheet->write(13+$i, 2, " ", $border_item_top);
		$worksheet->write(13+$i, 3, " ", $border_item_top);
		$worksheet->write(13+$i, 4, " ", $border_item_top);
		$worksheet->write(13+$i, 5, " ", $border_item_top);
		$worksheet->write(13+$i, 6, " ", $border_item_top);
		$worksheet->write(13+$i, 7, " ", $border_item_top);
		$worksheet->write(13+$i, 8, " ", $border_item_top);
		$worksheet->write(13+$i, 9, " ", $border_item_top);
		$worksheet->write(13+$i, 10, " ", $border_item_top);
		$worksheet->write(13+$i, 11,"TOTAL", $border_item_top_2);
		$worksheet->write(13+$i, 12,$SUMKW, $cant_normal_total);
	
	
		$NOM_EMPRESA_EMISOR = $result[0]['NOM_EMPRESA_EMISOR'];
		$RUT_EMPRESA = $result[0]['RUT_EMPRESA'];
		$DIR_EMPRESA = $result[0]['DIR_EMPRESA'];
		$CIUDAD_EMPRESA = $result[0]['CIUDAD_EMPRESA'];
		$PAIS_EMPRESA = $result[0]['PAIS_EMPRESA'];
		$TEL_EMPRESA = $result[0]['TEL_EMPRESA'];
		$FAX_EMPRESA = $result[0]['FAX_EMPRESA'];
		$MAIL_EMPRESA = $result[0]['MAIL_EMPRESA'];
		$SITIO_WEB_EMPRESA = $result[0]['SITIO_WEB_EMPRESA'];
		$NOM_USUARIO= $result[0]['NOM_USUARIO'];
		$MAIL_USUARIO= $result[0]['MAIL_USUARIO'];
		$CEL_USUARIO= $result[0]['CEL_USUARIO'];
		$FONO_USUARIO= $result[0]['FONO_USUARIO'];

		$FINAL = $result[0]['FINAL'];

		$worksheet->write($row_position+22, 10, $NOM_EMPRESA_EMISOR, $text_blue_bold_center);
		$worksheet->write($row_position+23, 10, $NOM_USUARIO, $text_blue_bold_center);
		$worksheet->write($row_position+24, 10, $MAIL_USUARIO, $text_blue_bold_center);
		$worksheet->write($row_position+25, 10, $FONO_USUARIO."-".$CEL_USUARIO, $text_blue_bold_center);

		$worksheet->write($row_position+28, 1, $NOM_EMPRESA_EMISOR." - RUT: ".$RUT_EMPRESA." - ".$DIR_EMPRESA." - ".$CIUDAD_EMPRESA." - ".$PAIS_EMPRESA." - ".$TEL_EMPRESA." - ".$FAX_EMPRESA, $text_pie_de_pagina);
		$worksheet->write($row_position+29, 5, $MAIL_EMPRESA." - ".$SITIO_WEB_EMPRESA, $text_pie_de_pagina);
		
		
		$workbook->close();
		
		header("Content-Type: application/x-msexcel; name=\"cotizacion_tecnico_electrico.xls\"");
		header("Content-Disposition: inline; filename=\"cotizacion_tecnico_electrico.xls\"");
		$fh=fopen($fname, "rb");
		fpassthru($fh);
		unlink($fname);
		
		error_reporting(E_ALL);
		
	}
	
		
	function printcot_tecnica_gas_excel($con_logo) {
		
		$print_descto = session::get('PRINT_DESCUENTO');
		error_reporting(E_ALL & ~E_NOTICE);
		
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");
		
		$fname = tempnam("/tmp", "producto_gas.xls");
		$workbook = &new writeexcel_workbook($fname);
		$cod_cotizacion = $this->get_key();
		$worksheet = &$workbook->addworksheet('COTIZACION_'.$cod_cotizacion);
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$sql = "exec spr_cot_tecnica $cod_cotizacion, 'GAS'";
				
		$result = $db->build_results($sql);
		
		$worksheet->set_row(0, 60);
		$worksheet->set_column(0, 0, 4);
		$worksheet->set_column(1, 2, 7);
		$worksheet->set_column(3, 9, 14);
		$worksheet->set_column(2, 10, 12);
		
		$worksheet->set_column(8, 10, 12);
		$worksheet->set_column(6, 6, 9);
		$worksheet->set_column(7, 7, 9);
		$worksheet->insert_bitmap('B1',$this->root_dir."images_appl/logo_reporte_excel.bmp");
		
	
		$text =& $workbook->addformat();
		$text->set_font("Verdana");
		$text->set_valign('vcenter');
    
		$text_bold =& $workbook->addformat();
		$text_bold->copy($text);
		$text_bold->set_bold(1);
	
		$text_blue_bold_left =& $workbook->addformat();
		$text_blue_bold_left->copy($text_bold);
		$text_blue_bold_left->set_align('left');
		$text_blue_bold_left->set_color('blue_0x20');

		$text_blue_bold_center =& $workbook->addformat();
		$text_blue_bold_center->copy($text_bold);
		$text_blue_bold_center->set_align('center');
		$text_blue_bold_center->set_color('blue_0x20');
		
		$text_blue_bold_right =& $workbook->addformat();
		$text_blue_bold_right->copy($text_bold);
		$text_blue_bold_right->set_align('right');
		$text_blue_bold_right->set_color('blue_0x20');

		$text_nro_docto =& $workbook->addformat();
		$text_nro_docto->copy($text_blue_bold_right);
		$text_nro_docto->set_size(13);
		
		$text_nro_docto_2 =& $workbook->addformat();
		$text_nro_docto_2->copy($text_blue_bold_left);
		$text_nro_docto_2->set_size(13);
		
		$text_pie_de_pagina =& $workbook->addformat();
		$text_pie_de_pagina->copy($text_blue_bold_left);
		$text_pie_de_pagina->set_size(8);
		
		$text_normal_left =& $workbook->addformat();
		$text_normal_left->copy($text);
		$text_normal_left->set_align('left');
		
		$text_normal_center =& $workbook->addformat();
		$text_normal_center->copy($text);
		$text_normal_center->set_align('center');
		
		$text_normal_right =& $workbook->addformat();
		$text_normal_right->copy($text);
		$text_normal_right->set_align('right');
				
		$text_normal_bold_left =& $workbook->addformat();
		$text_normal_bold_left->copy($text_bold);
		$text_normal_bold_left->set_align('left');
		
		
		$text_normal_bold_center =& $workbook->addformat();
		$text_normal_bold_center->copy($text_bold);
		$text_normal_bold_center->set_align('center');
	
		$text_normal_bold_right =& $workbook->addformat();
		$text_normal_bold_right->copy($text_bold);
		$text_normal_bold_right->set_align('right');
	
		
		$titulo_item_border_all =& $workbook->addformat();
		$titulo_item_border_all->copy($text_blue_bold_center);
		$titulo_item_border_all->set_border_color('black');
		$titulo_item_border_all->set_top(2);
		$titulo_item_border_all->set_bottom(2);
		$titulo_item_border_all->set_right(2);
		$titulo_item_border_all->set_left(2);
		
		$titulo_item_border_all_2 =& $workbook->addformat();
		$titulo_item_border_all_2->copy($text_blue_bold_center);
		$titulo_item_border_all_2->set_border_color('black');
		$titulo_item_border_all_2->set_bottom(2);
		$titulo_item_border_all_2->set_right(2);
		$titulo_item_border_all_2->set_left(2);
		
		$titulo_item_border_all_3 =& $workbook->addformat();
		$titulo_item_border_all_3->copy($text_blue_bold_center);
		$titulo_item_border_all_3->set_border_color('black');
		$titulo_item_border_all_3->set_bottom(2);
		
		$titulo_item_border_all_4 =& $workbook->addformat();
		$titulo_item_border_all_4->copy($text_blue_bold_center);
		$titulo_item_border_all_4->set_border_color('black');
		$titulo_item_border_all_4->set_top(2);
		$titulo_item_border_all_4->set_right(2);
		$titulo_item_border_all_4->set_left(2);
		
		$titulo_item_border_all_5 =& $workbook->addformat();
		$titulo_item_border_all_5->copy($text_blue_bold_center);
		$titulo_item_border_all_5->set_border_color('black');
		$titulo_item_border_all_5->set_top(2);
		$titulo_item_border_all_5->set_left(2);
		
		
		$titulo_item_border_all_merge =& $workbook->addformat();
		$titulo_item_border_all_merge->copy($titulo_item_border_all);
		$titulo_item_border_all_merge->set_merge();
				
	
		$border_item_left = & $workbook->addformat();
		$border_item_left->copy($text_normal_left);
		$border_item_left->set_border_color('black');
		$border_item_left->set_left(2);
		
		$border_item_left_bold = & $workbook->addformat();
		$border_item_left_bold->copy($text_bold);
		$border_item_left_bold->set_border_color('black');
		$border_item_left_bold->set_left(2);
		
		$border_item_left_bold_2 = & $workbook->addformat();
		$border_item_left_bold_2->copy($text_bold);
		$border_item_left_bold_2->set_border_color('black');
		$border_item_left_bold_2->set_bottom(2);
		$border_item_left_bold_2->set_left(2);
		
		$border_item_center = & $workbook->addformat();
		$border_item_center->copy($text_normal_center);
		$border_item_center->set_border_color('black');
		$border_item_center->set_left(2);
		$border_item_center->set_right(2);
		
		$border_item_right = & $workbook->addformat();
		$border_item_right->copy($text_normal_right);
		$border_item_right->set_border_color('black');
		$border_item_right->set_right(2);		
		
		$cant_normal =& $workbook->addformat();
		$cant_normal->copy($border_item_right);
		$cant_normal->set_num_format('0.0');
		
		$cant_normal_total =& $workbook->addformat();
		$cant_normal_total->copy($border_item_right);
		$cant_normal_total->set_num_format('0.0');
		$cant_normal_total->set_border_color('black');
		$cant_normal_total->set_bold(1);
		$cant_normal_total->set_top(2);
		$cant_normal_total->set_bottom(2);
		$cant_normal_total->set_right(2);
		$cant_normal_total->set_left(2);
					
		$monto_normal =& $workbook->addformat();
		$monto_normal->copy($border_item_right);
		$monto_normal->set_num_format('#,##0');
		
		$border_item_top = & $workbook->addformat();
		$border_item_top->copy($text);
		$border_item_top->set_border_color('black');
		$border_item_top->set_top(2);
		
		$border_item_top_2 = & $workbook->addformat();
		$border_item_top_2->copy($text);
		$border_item_top_2->set_color('blue_0x20');
		$border_item_top_2->set_border_color('black');
		$border_item_top_2->set_top(2);
		$border_item_top_2->set_bold(1);
		
		
		$border_item_bottom = & $workbook->addformat();
		$border_item_bottom->copy($text);
		$border_item_bottom->set_border_color('black');
		$border_item_bottom->set_bottom(2);
		
		$border_item_total = & $workbook->addformat();
		$border_item_total->copy($text);
		$border_item_total->set_border_color('black');
		$border_item_total->set_top(2);
		$border_item_total->set_bottom(2);
		$border_item_total->set_right(2);
		
		$border_item_especial_left = & $workbook->addformat();
		$border_item_especial_left->copy($text_normal_left);
		$border_item_especial_left->set_border_color('black');
		$border_item_especial_left->set_left(2);
		$border_item_especial_left->set_right(2);
		
		$border_item_especial_right = & $workbook->addformat();
		$border_item_especial_right->copy($text_normal_right);
		$border_item_especial_right->set_border_color('black');
		$border_item_especial_right->set_left(2);
		$border_item_especial_right->set_right(2);
		
		$border_item_especial_total = & $workbook->addformat();
		$border_item_especial_total->copy($border_item_total);
		$border_item_especial_total->set_border_color('black');
		$border_item_especial_total->set_bold(1);
		
		
		$COD_COTIZACION = $result[0]['COD_COTIZACION'];
		$FECHA_IMPRESO = $result[0]['FECHA_IMPRESO'];
		$FECHA_COTIZACION = $result[0]['FECHA_COTIZACION'];
		$NOM_EMPRESA = $result[0]['NOM_EMPRESA'];
		$RUT = $result[0]['RUT'];
		$DIG_VERIF = $result[0]['DIG_VERIF'];
		$DIRECCION = $result[0]['DIRECCION'];
		$COMUNA = $result[0]['COMUNA'];
		$CIUDAD = $result[0]['CIUDAD'];
		$TELEFONO = $result[0]['TELEFONO'];
		$FAX = $result[0]['FAX'];	
		$NOM_PERSONA = $result[0]['NOM_PERSONA'];
		$EMAIL = $result[0]['EMAIL'];
		$REFERENCIA = $result[0]['REFERENCIA'];
		$SIMBOLO = $result[0]['SIMBOLO'];
		
		$worksheet->write(1, 9, "COTIZACION Nº".$COD_COTIZACION, $text_nro_docto);
		$worksheet->write(2, 1, "PRODUCTO A GAS", $text_nro_docto_2);
		$worksheet->write(4, 1, "Santiago,".$FECHA_COTIZACION, $text_blue_bold_left);
		$worksheet->write(6, 1, "Razón Social", $text_blue_bold_left);
		
		$worksheet->write(6, 3, $NOM_EMPRESA, $text_normal_bold_left);
		$worksheet->write(6, 8, "Rut", $text_blue_bold_left);
		
		$rut=number_format($RUT, 0, ',', '.');
		$rut=$rut.'-'.$DIG_VERIF;
		
		$worksheet->write(6, 9, $rut, $text_normal_bold_left);
		
		$worksheet->write(7, 1, "Dirección", $text_blue_bold_left);
		$worksheet->write(7, 3, $DIRECCION, $text_normal_left);
		$worksheet->write(8, 1, "Comuna", $text_blue_bold_left);
		$worksheet->write(8, 3, $COMUNA, $text_normal_left);
		$worksheet->write(8, 4, "Ciudad", $text_blue_bold_left);
		$worksheet->write(8, 5, $CIUDAD, $text_normal_left);
		$worksheet->write(8, 6, "Fono", $text_blue_bold_left);
		$worksheet->write(8, 7, $TELEFONO, $text_normal_left);
		$worksheet->write(8, 8, "Fax",$text_blue_bold_left);
		$worksheet->write(8, 9, $FAX,$text_normal_left);
		$worksheet->write(9, 8, "Fono",$text_blue_bold_left);
		$worksheet->write(9, 9, $TELEFONO,$text_normal_left);
		$worksheet->write(9, 1, "Atención", $text_blue_bold_left);
		$worksheet->write(9, 3, $NOM_PERSONA." ".$EMAIL, $text_normal_left);
		$worksheet->write(10, 1, "Referencia",$text_blue_bold_left);
		$worksheet->write(10, 3, $REFERENCIA,$text_normal_left);
		
		$worksheet->write(12, 1, "Ítem", $titulo_item_border_all);
		$worksheet->write(12, 2, "Modelo", $titulo_item_border_all);
		$worksheet->write(12, 3, "",$titulo_item_border_all);
		$worksheet->write(12, 4, "", $titulo_item_border_all);
		$worksheet->write(12, 5, "", $titulo_item_border_all);
		$worksheet->write(12, 3, "Producto", $titulo_item_border_all);
		$worksheet->merge_cells(12, 3, 12, 7);
		$worksheet->write(12, 6, "", $titulo_item_border_all);
		$worksheet->write(12, 7, "", $titulo_item_border_all);
		$worksheet->write(12, 8, "Cantidad", $titulo_item_border_all);
		$worksheet->write(12, 9, "[MCal]", $titulo_item_border_all);
		$worksheet->write(12, 10, "Total [MCal]", $titulo_item_border_all);
	
		
		$SUMTG=0;
		for ($i=0 ; $i <count($result); $i++) {
			$ITEM = $result[$i]['ITEM'];
			$NOM_PRODUCTO = $result[$i]['NOM_PRODUCTO'];
			$COD_PRODUCTO = $result[$i]['COD_PRODUCTO'];
			$CANTIDAD = $result[$i]['CANTIDAD'];
			$POTENCIA = $result[$i]['POTENCIA'];
			$TOTAL_GAS = $result[$i]['TOTAL_GAS'];
			
			$SUMTG=$SUMTG+$TOTAL_GAS;
			$worksheet->write(13+$i, 1,$ITEM, $border_item_left);
			$worksheet->write(13+$i, 2,$COD_PRODUCTO,$border_item_left);
			
			if($COD_PRODUCTO == '')
				$worksheet->write(13+$i, 3, $NOM_PRODUCTO, $border_item_left_bold);
			else
				$worksheet->write(13+$i, 3, $NOM_PRODUCTO, $border_item_left);
		$worksheet->write(13+$i, 8,$CANTIDAD, $border_item_especial_right);
		$worksheet->write(13+$i, 9,$POTENCIA, $cant_normal);
		$worksheet->write(13+$i, 10,$TOTAL_GAS, $cant_normal);
		
		
		}
		
		$worksheet->write(13+$i, 1, " ", $border_item_top);
		$worksheet->write(13+$i, 2, " ", $border_item_top);
		$worksheet->write(13+$i, 3, " ", $border_item_top);
		$worksheet->write(13+$i, 4, " ", $border_item_top);
		$worksheet->write(13+$i, 5, " ", $border_item_top);
		$worksheet->write(13+$i, 6, " ", $border_item_top);
		$worksheet->write(13+$i, 7, " ", $border_item_top);
		$worksheet->write(13+$i, 8, " ", $border_item_top);
		$worksheet->write(13+$i, 9, "TOTAL", $border_item_top_2);
		$worksheet->write(13+$i, 10,$SUMTG, $cant_normal_total);
	

		$NOM_EMPRESA_EMISOR = $result[0]['NOM_EMPRESA_EMISOR'];
		$RUT_EMPRESA = $result[0]['RUT_EMPRESA'];
		$DIR_EMPRESA = $result[0]['DIR_EMPRESA'];
		$CIUDAD_EMPRESA = $result[0]['CIUDAD_EMPRESA'];
		$PAIS_EMPRESA = $result[0]['PAIS_EMPRESA'];
		$TEL_EMPRESA = $result[0]['TEL_EMPRESA'];
		$FAX_EMPRESA = $result[0]['FAX_EMPRESA'];
		$MAIL_EMPRESA = $result[0]['MAIL_EMPRESA'];
		$SITIO_WEB_EMPRESA = $result[0]['SITIO_WEB_EMPRESA'];
		$NOM_USUARIO= $result[0]['NOM_USUARIO'];
		$MAIL_U= $result[0]['MAIL_U'];
		$CEL_U= $result[0]['CEL_U'];
		$FONO_U= $result[0]['FONO_U'];

		$FINAL = $result[0]['FINAL'];

		$worksheet->write($row_position+22, 9, $NOM_EMPRESA_EMISOR, $text_blue_bold_center);
		$worksheet->write($row_position+23, 9, $NOM_USUARIO, $text_blue_bold_center);
		$worksheet->write($row_position+24, 9, $MAIL_U, $text_blue_bold_center);
		$worksheet->write($row_position+25, 9, $FONO_U."-".$CEL_U, $text_blue_bold_center);

		$worksheet->write($row_position+28, 1, $NOM_EMPRESA_EMISOR." - RUT: ".$RUT_EMPRESA." - ".$DIR_EMPRESA." - ".$CIUDAD_EMPRESA." - ".$PAIS_EMPRESA." - ".$TEL_EMPRESA." - ".$FAX_EMPRESA, $text_pie_de_pagina);
		$worksheet->write($row_position+29, 5, $MAIL_EMPRESA." - ".$SITIO_WEB_EMPRESA, $text_pie_de_pagina);
		
		
		$workbook->close();
		
		header("Content-Type: application/x-msexcel; name=\"cotizacion_tecnica_gas.xls\"");
		header("Content-Disposition: inline; filename=\"cotizacion_tecnica_gas.xls\"");
		$fh=fopen($fname, "rb");
		fpassthru($fh);
		unlink($fname);
		
		error_reporting(E_ALL);
	}
	function printcot_tecnica_vapor_excel($con_logo) {
		
		$print_descto = session::get('PRINT_DESCUENTO');
		error_reporting(E_ALL & ~E_NOTICE);
		
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");
		
		$fname = tempnam("/tmp", "producto_vapor.xls");
		$workbook = &new writeexcel_workbook($fname);
		$cod_cotizacion = $this->get_key();
		$worksheet = &$workbook->addworksheet('COTIZACION_'.$cod_cotizacion);
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$sql = "exec spr_cot_tecnica $cod_cotizacion, 'VAPOR'";
				
		$result = $db->build_results($sql);
		
		$worksheet->set_row(0, 60);
		$worksheet->set_column(0, 0, 4);
		$worksheet->set_column(1, 2, 7);
		$worksheet->set_column(3, 9, 14);
		$worksheet->set_column(2, 10, 12);
		
		$worksheet->set_column(8, 10, 12);
		$worksheet->set_column(6, 6, 9);
		$worksheet->set_column(7, 7, 9);
		$worksheet->insert_bitmap('B1',$this->root_dir."images_appl/logo_reporte_excel.bmp");
		
	
		$text =& $workbook->addformat();
		$text->set_font("Verdana");
		$text->set_valign('vcenter');
    
		$text_bold =& $workbook->addformat();
		$text_bold->copy($text);
		$text_bold->set_bold(1);
	
		$text_blue_bold_left =& $workbook->addformat();
		$text_blue_bold_left->copy($text_bold);
		$text_blue_bold_left->set_align('left');
		$text_blue_bold_left->set_color('blue_0x20');

		$text_blue_bold_center =& $workbook->addformat();
		$text_blue_bold_center->copy($text_bold);
		$text_blue_bold_center->set_align('center');
		$text_blue_bold_center->set_color('blue_0x20');
		
		$text_blue_bold_right =& $workbook->addformat();
		$text_blue_bold_right->copy($text_bold);
		$text_blue_bold_right->set_align('right');
		$text_blue_bold_right->set_color('blue_0x20');

		$text_nro_docto =& $workbook->addformat();
		$text_nro_docto->copy($text_blue_bold_right);
		$text_nro_docto->set_size(13);
		
		$text_nro_docto_2 =& $workbook->addformat();
		$text_nro_docto_2->copy($text_blue_bold_left);
		$text_nro_docto_2->set_size(13);
		
		$text_pie_de_pagina =& $workbook->addformat();
		$text_pie_de_pagina->copy($text_blue_bold_left);
		$text_pie_de_pagina->set_size(8);
		
		$text_normal_left =& $workbook->addformat();
		$text_normal_left->copy($text);
		$text_normal_left->set_align('left');
		
		$text_normal_center =& $workbook->addformat();
		$text_normal_center->copy($text);
		$text_normal_center->set_align('center');
		
		$text_normal_right =& $workbook->addformat();
		$text_normal_right->copy($text);
		$text_normal_right->set_align('right');
				
		$text_normal_bold_left =& $workbook->addformat();
		$text_normal_bold_left->copy($text_bold);
		$text_normal_bold_left->set_align('left');
		
		
		$text_normal_bold_center =& $workbook->addformat();
		$text_normal_bold_center->copy($text_bold);
		$text_normal_bold_center->set_align('center');
	
		$text_normal_bold_right =& $workbook->addformat();
		$text_normal_bold_right->copy($text_bold);
		$text_normal_bold_right->set_align('right');
	
		
		$titulo_item_border_all =& $workbook->addformat();
		$titulo_item_border_all->copy($text_blue_bold_center);
		$titulo_item_border_all->set_border_color('black');
		$titulo_item_border_all->set_top(2);
		$titulo_item_border_all->set_bottom(2);
		$titulo_item_border_all->set_right(2);
		$titulo_item_border_all->set_left(2);
		
		$titulo_item_border_all_2 =& $workbook->addformat();
		$titulo_item_border_all_2->copy($text_blue_bold_center);
		$titulo_item_border_all_2->set_border_color('black');
		$titulo_item_border_all_2->set_bottom(2);
		$titulo_item_border_all_2->set_right(2);
		$titulo_item_border_all_2->set_left(2);
		
		$titulo_item_border_all_3 =& $workbook->addformat();
		$titulo_item_border_all_3->copy($text_blue_bold_center);
		$titulo_item_border_all_3->set_border_color('black');
		$titulo_item_border_all_3->set_bottom(2);
		
		$titulo_item_border_all_4 =& $workbook->addformat();
		$titulo_item_border_all_4->copy($text_blue_bold_center);
		$titulo_item_border_all_4->set_border_color('black');
		$titulo_item_border_all_4->set_top(2);
		$titulo_item_border_all_4->set_right(2);
		$titulo_item_border_all_4->set_left(2);
		
		$titulo_item_border_all_5 =& $workbook->addformat();
		$titulo_item_border_all_5->copy($text_blue_bold_center);
		$titulo_item_border_all_5->set_border_color('black');
		$titulo_item_border_all_5->set_top(2);
		$titulo_item_border_all_5->set_left(2);
		
		
		$titulo_item_border_all_merge =& $workbook->addformat();
		$titulo_item_border_all_merge->copy($titulo_item_border_all);
		$titulo_item_border_all_merge->set_merge();
				
	
		$border_item_left = & $workbook->addformat();
		$border_item_left->copy($text_normal_left);
		$border_item_left->set_border_color('black');
		$border_item_left->set_left(2);
		
		$border_item_left_bold = & $workbook->addformat();
		$border_item_left_bold->copy($text_bold);
		$border_item_left_bold->set_border_color('black');
		$border_item_left_bold->set_left(2);
		
		$border_item_left_bold_2 = & $workbook->addformat();
		$border_item_left_bold_2->copy($text_bold);
		$border_item_left_bold_2->set_border_color('black');
		$border_item_left_bold_2->set_bottom(2);
		$border_item_left_bold_2->set_left(2);
		
		$border_item_center = & $workbook->addformat();
		$border_item_center->copy($text_normal_center);
		$border_item_center->set_border_color('black');
		$border_item_center->set_left(2);
		$border_item_center->set_right(2);
		
		$border_item_right = & $workbook->addformat();
		$border_item_right->copy($text_normal_right);
		$border_item_right->set_border_color('black');
		$border_item_right->set_right(2);		
		
		$cant_normal =& $workbook->addformat();
		$cant_normal->copy($border_item_right);
		$cant_normal->set_num_format('0.0');
		
		$cant_normal_total =& $workbook->addformat();
		$cant_normal_total->copy($border_item_right);
		$cant_normal_total->set_num_format('0.0');
		$cant_normal_total->set_border_color('black');
		$cant_normal_total->set_bold(1);
		$cant_normal_total->set_top(2);
		$cant_normal_total->set_bottom(2);
		$cant_normal_total->set_right(2);
		$cant_normal_total->set_left(2);
					
		$monto_normal =& $workbook->addformat();
		$monto_normal->copy($border_item_right);
		$monto_normal->set_num_format('#,##0');
		
		$border_item_top = & $workbook->addformat();
		$border_item_top->copy($text);
		$border_item_top->set_border_color('black');
		$border_item_top->set_top(2);
		
		$border_item_top_2 = & $workbook->addformat();
		$border_item_top_2->copy($text);
		$border_item_top_2->set_color('blue_0x20');
		$border_item_top_2->set_border_color('black');
		$border_item_top_2->set_top(2);
		$border_item_top_2->set_bold(1);
		
		
		$border_item_bottom = & $workbook->addformat();
		$border_item_bottom->copy($text);
		$border_item_bottom->set_border_color('black');
		$border_item_bottom->set_bottom(2);
		
		$border_item_total = & $workbook->addformat();
		$border_item_total->copy($text);
		$border_item_total->set_border_color('black');
		$border_item_total->set_top(2);
		$border_item_total->set_bottom(2);
		$border_item_total->set_right(2);
		
		$border_item_especial_left = & $workbook->addformat();
		$border_item_especial_left->copy($text_normal_left);
		$border_item_especial_left->set_border_color('black');
		$border_item_especial_left->set_left(2);
		$border_item_especial_left->set_right(2);
		
		$border_item_especial_right = & $workbook->addformat();
		$border_item_especial_right->copy($text_normal_right);
		$border_item_especial_right->set_border_color('black');
		$border_item_especial_right->set_left(2);
		$border_item_especial_right->set_right(2);
		
		$border_item_especial_total = & $workbook->addformat();
		$border_item_especial_total->copy($border_item_total);
		$border_item_especial_total->set_border_color('black');
		$border_item_especial_total->set_bold(1);
		
		
		$COD_COTIZACION = $result[0]['COD_COTIZACION'];
		$FECHA_COTIZACION = $result[0]['FECHA_COTIZACION'];
		$FECHA_IMPRESO = $result[0]['FECHA_IMPRESO'];
		$NOM_EMPRESA = $result[0]['NOM_EMPRESA'];
		$RUT = $result[0]['RUT'];
		$DIG_VERIF = $result[0]['DIG_VERIF'];
		$DIRECCION = $result[0]['DIRECCION'];
		$COMUNA = $result[0]['COMUNA'];
		$CIUDAD = $result[0]['CIUDAD'];
		$TELEFONO = $result[0]['TELEFONO'];
		$FAX = $result[0]['FAX'];	
		$NOM_PERSONA = $result[0]['NOM_PERSONA'];
		$EMAIL = $result[0]['EMAIL'];
		$REFERENCIA = $result[0]['REFERENCIA'];
		$SIMBOLO = $result[0]['SIMBOLO'];
		
		$worksheet->write(1, 9, "COTIZACION Nº".$COD_COTIZACION, $text_nro_docto);
		$worksheet->write(2, 1, "PRODUCTO A VAPOR", $text_nro_docto_2);
		$worksheet->write(4, 1, "Santiago,".$FECHA_COTIZACION, $text_blue_bold_left);
		$worksheet->write(6, 1, "Razón Social", $text_blue_bold_left);
		
		$worksheet->write(6, 3, $NOM_EMPRESA, $text_normal_bold_left);
		$worksheet->write(6, 8, "Rut", $text_blue_bold_left);
		
		$rut=number_format($RUT, 0, ',', '.');
		$rut=$rut.'-'.$DIG_VERIF;
		
		$worksheet->write(6, 9, $rut, $text_normal_bold_left);
		
		$worksheet->write(7, 1, "Dirección", $text_blue_bold_left);
		$worksheet->write(7, 3, $DIRECCION, $text_normal_left);
		$worksheet->write(8, 1, "Comuna", $text_blue_bold_left);
		$worksheet->write(8, 3, $COMUNA, $text_normal_left);
		$worksheet->write(8, 4, "Ciudad", $text_blue_bold_left);
		$worksheet->write(8, 5, $CIUDAD, $text_normal_left);
		$worksheet->write(8, 6, "Fono", $text_blue_bold_left);
		$worksheet->write(8, 7, $TELEFONO, $text_normal_left);
		$worksheet->write(8, 8, "Fax",$text_blue_bold_left);
		$worksheet->write(8, 9, $FAX,$text_normal_left);
		$worksheet->write(9, 8, "Fono",$text_blue_bold_left);
		$worksheet->write(9, 9, $TELEFONO,$text_normal_left);
		$worksheet->write(9, 1, "Atención", $text_blue_bold_left);
		$worksheet->write(9, 3, $NOM_PERSONA." ".$EMAIL, $text_normal_left);
		$worksheet->write(10, 1, "Referencia",$text_blue_bold_left);
		$worksheet->write(10, 3, $REFERENCIA,$text_normal_left);
		
		$worksheet->write(12, 1, "Ítem", $titulo_item_border_all);
		$worksheet->write(12, 2, "Modelo", $titulo_item_border_all);
		$worksheet->write(12, 3, "",$titulo_item_border_all);
		$worksheet->write(12, 4, "", $titulo_item_border_all);
		$worksheet->write(12, 5, "", $titulo_item_border_all);
		$worksheet->write(12, 3, "Producto", $titulo_item_border_all);
		$worksheet->merge_cells(12, 3, 12, 7);
		$worksheet->write(12, 6, "", $titulo_item_border_all);
		$worksheet->write(12, 7, "", $titulo_item_border_all);
		$worksheet->write(12, 8, "Cantidad", $titulo_item_border_all);
		$worksheet->write(12, 9, "[KVapor]", $titulo_item_border_all);
		$worksheet->write(12, 10, "Total [KV]", $titulo_item_border_all);
		$worksheet->write(12, 11, "[Psi]", $titulo_item_border_all);
	
		
		$SUMTV=0;
		for ($i=0 ; $i <count($result); $i++) {
			$ITEM = $result[$i]['ITEM'];
			$NOM_PRODUCTO = $result[$i]['NOM_PRODUCTO'];
			$COD_PRODUCTO = $result[$i]['COD_PRODUCTO'];
			$CANTIDAD = $result[$i]['CANTIDAD'];
			$CONSUMO_VAPOR = $result[$i]['CONSUMO_VAPOR'];
			$TOTAL_KV = $result[$i]['TOTAL_KV'];
			$PRESION_VAPOR = $result[$i]['PRESION_VAPOR'];
			
			$SUMTV=$SUMTV+$TOTAL_KV;
			$worksheet->write(13+$i, 1,$ITEM, $border_item_left);
			$worksheet->write(13+$i, 2,$COD_PRODUCTO,$border_item_left);
			
			if($COD_PRODUCTO == '')
				$worksheet->write(13+$i, 3, $NOM_PRODUCTO, $border_item_left_bold);
			else
				$worksheet->write(13+$i, 3, $NOM_PRODUCTO, $border_item_left);
		$worksheet->write(13+$i, 8,$CANTIDAD, $border_item_especial_right);
		$worksheet->write(13+$i, 9,$CONSUMO_VAPOR, $cant_normal);
		$worksheet->write(13+$i, 10,$TOTAL_KV, $cant_normal);
		$worksheet->write(13+$i, 11,$PRESION_VAPOR, $cant_normal);
		}
		
		$worksheet->write(13+$i, 1, " ", $border_item_top);
		$worksheet->write(13+$i, 2, " ", $border_item_top);
		$worksheet->write(13+$i, 3, " ", $border_item_top);
		$worksheet->write(13+$i, 4, " ", $border_item_top);
		$worksheet->write(13+$i, 5, " ", $border_item_top);
		$worksheet->write(13+$i, 6, " ", $border_item_top);
		$worksheet->write(13+$i, 7, " ", $border_item_top);
		$worksheet->write(13+$i, 8, " ", $border_item_top);
		$worksheet->write(13+$i, 9, "TOTAL", $border_item_top_2);
		$worksheet->write(13+$i, 10,$SUMTV, $cant_normal_total);
		$worksheet->write(13+$i, 11, " ", $border_item_top);
	

		$NOM_EMPRESA_EMISOR = $result[0]['NOM_EMPRESA_EMISOR'];
		$RUT_EMPRESA = $result[0]['RUT_EMPRESA'];
		$DIR_EMPRESA = $result[0]['DIR_EMPRESA'];
		$CIUDAD_EMPRESA = $result[0]['CIUDAD_EMPRESA'];
		$PAIS_EMPRESA = $result[0]['PAIS_EMPRESA'];
		$TEL_EMPRESA = $result[0]['TEL_EMPRESA'];
		$FAX_EMPRESA = $result[0]['FAX_EMPRESA'];
		$MAIL_EMPRESA = $result[0]['MAIL_EMPRESA'];
		$SITIO_WEB_EMPRESA = $result[0]['SITIO_WEB_EMPRESA'];
		$NOM_USUARIO= $result[0]['NOM_USUARIO'];
		$MAIL_USUARIO= $result[0]['MAIL_USUARIO'];
		$CEL_USUARIO= $result[0]['CEL_USUARIO'];
		$FONO_USUARIO= $result[0]['FONO_USUARIO'];

		$FINAL = $result[0]['FINAL'];

		$worksheet->write($row_position+22, 9, $NOM_EMPRESA_EMISOR, $text_blue_bold_center);
		$worksheet->write($row_position+23, 9, $NOM_USUARIO, $text_blue_bold_center);
		$worksheet->write($row_position+24, 9, $MAIL_USUARIO, $text_blue_bold_center);
		$worksheet->write($row_position+25, 9, $FONO_USUARIO."-".$CEL_USUARIO, $text_blue_bold_center);

		$worksheet->write($row_position+28, 1, $NOM_EMPRESA_EMISOR." - RUT: ".$RUT_EMPRESA." - ".$DIR_EMPRESA." - ".$CIUDAD_EMPRESA." - ".$PAIS_EMPRESA." - ".$TEL_EMPRESA." - ".$FAX_EMPRESA, $text_pie_de_pagina);
		$worksheet->write($row_position+29, 5, $MAIL_EMPRESA." - ".$SITIO_WEB_EMPRESA, $text_pie_de_pagina);
		
		
		$workbook->close();
		
		header("Content-Type: application/x-msexcel; name=\"cotizacion_tecnica_vapor.xls\"");
		header("Content-Disposition: inline; filename=\"cotizacion_tecnica_vapor.xls\"");
		$fh=fopen($fname, "rb");
		fpassthru($fh);
		unlink($fname);
		
		error_reporting(E_ALL);
	}
	
	function printcot_tecnica_agua_excel($con_logo) {
		
		$print_descto = session::get('PRINT_DESCUENTO');
		error_reporting(E_ALL & ~E_NOTICE);
		
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");
		
		$fname = tempnam("/tmp", "producto_agua.xls");
		$workbook = &new writeexcel_workbook($fname);
		$cod_cotizacion = $this->get_key();
		$worksheet = &$workbook->addworksheet('COTIZACION_'.$cod_cotizacion);
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$sql = "exec spr_cot_tecnica $cod_cotizacion, 'AGUA'";
				
		$result = $db->build_results($sql);
		
		$worksheet->set_row(0, 60);
		$worksheet->set_column(0, 0, 4);
		$worksheet->set_column(1, 2, 7);
		$worksheet->set_column(3, 9, 14);
		$worksheet->set_column(2, 10, 12);
		
		$worksheet->set_column(8, 10, 12);
		$worksheet->set_column(6, 6, 9);
		$worksheet->set_column(7, 7, 11);
		$worksheet->set_column(11, 12, 10);
		$worksheet->set_column(8, 8, 9);
		$worksheet->set_column(10, 10, 9);
		$worksheet->insert_bitmap('B1',$this->root_dir."images_appl/logo_reporte_excel.bmp");
		
	
		$text =& $workbook->addformat();
		$text->set_font("Verdana");
		$text->set_valign('vcenter');
    
		$text_bold =& $workbook->addformat();
		$text_bold->copy($text);
		$text_bold->set_bold(1);
	
		$text_blue_bold_left =& $workbook->addformat();
		$text_blue_bold_left->copy($text_bold);
		$text_blue_bold_left->set_align('left');
		$text_blue_bold_left->set_color('blue_0x20');

		$text_blue_bold_center =& $workbook->addformat();
		$text_blue_bold_center->copy($text_bold);
		$text_blue_bold_center->set_align('center');
		$text_blue_bold_center->set_color('blue_0x20');
		
		$text_blue_bold_right =& $workbook->addformat();
		$text_blue_bold_right->copy($text_bold);
		$text_blue_bold_right->set_align('right');
		$text_blue_bold_right->set_color('blue_0x20');

		$text_nro_docto =& $workbook->addformat();
		$text_nro_docto->copy($text_blue_bold_right);
		$text_nro_docto->set_size(13);
		
		$text_nro_docto_2 =& $workbook->addformat();
		$text_nro_docto_2->copy($text_blue_bold_left);
		$text_nro_docto_2->set_size(13);
		
		$text_pie_de_pagina =& $workbook->addformat();
		$text_pie_de_pagina->copy($text_blue_bold_left);
		$text_pie_de_pagina->set_size(8);
		
		$text_normal_left =& $workbook->addformat();
		$text_normal_left->copy($text);
		$text_normal_left->set_align('left');
		
		$text_normal_center =& $workbook->addformat();
		$text_normal_center->copy($text);
		$text_normal_center->set_align('center');
		
		$text_normal_right =& $workbook->addformat();
		$text_normal_right->copy($text);
		$text_normal_right->set_align('right');
				
		$text_normal_bold_left =& $workbook->addformat();
		$text_normal_bold_left->copy($text_bold);
		$text_normal_bold_left->set_align('left');
		
		
		$text_normal_bold_center =& $workbook->addformat();
		$text_normal_bold_center->copy($text_bold);
		$text_normal_bold_center->set_align('center');
	
		$text_normal_bold_right =& $workbook->addformat();
		$text_normal_bold_right->copy($text_bold);
		$text_normal_bold_right->set_align('right');
	
		
		$titulo_item_border_all =& $workbook->addformat();
		$titulo_item_border_all->copy($text_blue_bold_center);
		$titulo_item_border_all->set_border_color('black');
		$titulo_item_border_all->set_top(2);
		$titulo_item_border_all->set_bottom(2);
		$titulo_item_border_all->set_right(2);
		$titulo_item_border_all->set_left(2);
		
		$titulo_item_border_all_2 =& $workbook->addformat();
		$titulo_item_border_all_2->copy($text_blue_bold_center);
		$titulo_item_border_all_2->set_border_color('black');
		$titulo_item_border_all_2->set_bottom(2);
		$titulo_item_border_all_2->set_right(2);
		$titulo_item_border_all_2->set_left(2);
		
		$titulo_item_border_all_3 =& $workbook->addformat();
		$titulo_item_border_all_3->copy($text_blue_bold_center);
		$titulo_item_border_all_3->set_border_color('black');
		$titulo_item_border_all_3->set_bottom(2);
		
		$titulo_item_border_all_4 =& $workbook->addformat();
		$titulo_item_border_all_4->copy($text_blue_bold_center);
		$titulo_item_border_all_4->set_border_color('black');
		$titulo_item_border_all_4->set_top(2);
		$titulo_item_border_all_4->set_right(2);
		$titulo_item_border_all_4->set_left(2);
		
		$titulo_item_border_all_5 =& $workbook->addformat();
		$titulo_item_border_all_5->copy($text_blue_bold_center);
		$titulo_item_border_all_5->set_border_color('black');
		$titulo_item_border_all_5->set_top(2);
		$titulo_item_border_all_5->set_left(2);
		
		
		$titulo_item_border_all_merge =& $workbook->addformat();
		$titulo_item_border_all_merge->copy($titulo_item_border_all);
		$titulo_item_border_all_merge->set_merge();
				
	
		$border_item_left = & $workbook->addformat();
		$border_item_left->copy($text_normal_left);
		$border_item_left->set_border_color('black');
		$border_item_left->set_left(2);
		
		$border_item_left_bold = & $workbook->addformat();
		$border_item_left_bold->copy($text_bold);
		$border_item_left_bold->set_border_color('black');
		$border_item_left_bold->set_left(2);
		
		$border_item_left_bold_2 = & $workbook->addformat();
		$border_item_left_bold_2->copy($text_bold);
		$border_item_left_bold_2->set_border_color('black');
		$border_item_left_bold_2->set_bottom(2);
		$border_item_left_bold_2->set_left(2);
		
		$border_item_center = & $workbook->addformat();
		$border_item_center->copy($text_normal_center);
		$border_item_center->set_border_color('black');
		$border_item_center->set_left(2);
		$border_item_center->set_right(2);
		
		$border_item_right = & $workbook->addformat();
		$border_item_right->copy($text_normal_right);
		$border_item_right->set_border_color('black');
		$border_item_right->set_right(2);		
		
		$cant_normal =& $workbook->addformat();
		$cant_normal->copy($border_item_right);
		$cant_normal->set_num_format('0.0');
		
		$cant_normal_total =& $workbook->addformat();
		$cant_normal_total->copy($border_item_right);
		$cant_normal_total->set_num_format('0.0');
		$cant_normal_total->set_border_color('black');
		$cant_normal_total->set_bold(1);
		$cant_normal_total->set_top(2);
		$cant_normal_total->set_bottom(2);
		$cant_normal_total->set_right(2);
		$cant_normal_total->set_left(2);
					
		$monto_normal =& $workbook->addformat();
		$monto_normal->copy($border_item_right);
		$monto_normal->set_num_format('#,##0');
		
		$border_item_top = & $workbook->addformat();
		$border_item_top->copy($text);
		$border_item_top->set_border_color('black');
		$border_item_top->set_top(2);
		
		$border_item_top_2 = & $workbook->addformat();
		$border_item_top_2->copy($text);
		$border_item_top_2->set_color('blue_0x20');
		$border_item_top_2->set_border_color('black');
		$border_item_top_2->set_top(2);
		$border_item_top_2->set_bold(1);
		
		
		$border_item_bottom = & $workbook->addformat();
		$border_item_bottom->copy($text);
		$border_item_bottom->set_border_color('black');
		$border_item_bottom->set_bottom(2);
		
		$border_item_total = & $workbook->addformat();
		$border_item_total->copy($text);
		$border_item_total->set_border_color('black');
		$border_item_total->set_top(2);
		$border_item_total->set_bottom(2);
		$border_item_total->set_right(2);
		
		$border_item_especial_left = & $workbook->addformat();
		$border_item_especial_left->copy($text_normal_left);
		$border_item_especial_left->set_border_color('black');
		$border_item_especial_left->set_left(2);
		$border_item_especial_left->set_right(2);
		
		$border_item_especial_right = & $workbook->addformat();
		$border_item_especial_right->copy($text_normal_right);
		$border_item_especial_right->set_border_color('black');
		$border_item_especial_right->set_left(2);
		$border_item_especial_right->set_right(2);
		
		$border_item_especial_total = & $workbook->addformat();
		$border_item_especial_total->copy($border_item_total);
		$border_item_especial_total->set_border_color('black');
		$border_item_especial_total->set_bold(1);
		
		
		$COD_COTIZACION = $result[0]['COD_COTIZACION'];
		$FECHA_IMPRESO = $result[0]['FECHA_IMPRESO'];
		$FECHA_COTIZACION = $result[0]['FECHA_COTIZACION'];
		$NOM_EMPRESA = $result[0]['NOM_EMPRESA'];
		$RUT = $result[0]['RUT'];
		$DIG_VERIF = $result[0]['DIG_VERIF'];
		$DIRECCION = $result[0]['DIRECCION'];
		$COMUNA = $result[0]['COMUNA'];
		$CIUDAD = $result[0]['CIUDAD'];
		$TELEFONO = $result[0]['TELEFONO'];
		$FAX = $result[0]['FAX'];	
		$NOM_PERSONA = $result[0]['NOM_PERSONA'];
		$EMAIL = $result[0]['EMAIL'];
		$REFERENCIA = $result[0]['REFERENCIA'];
		$SIMBOLO = $result[0]['SIMBOLO'];
		
		$worksheet->write(1, 9, "COTIZACION Nº".$COD_COTIZACION, $text_nro_docto);
		$worksheet->write(2, 1, "PRODUCTO QUE REQUIEREN AGUA", $text_nro_docto_2);
		$worksheet->write(4, 1, "Santiago,".$FECHA_COTIZACION, $text_blue_bold_left);
		$worksheet->write(6, 1, "Razón Social", $text_blue_bold_left);
		
		$worksheet->write(6, 3, $NOM_EMPRESA, $text_normal_bold_left);
		$worksheet->write(6, 8, "Rut", $text_blue_bold_left);
		
		$rut=number_format($RUT, 0, ',', '.');
		$rut=$rut.'-'.$DIG_VERIF;
		
		$worksheet->write(6, 9, $rut, $text_normal_bold_left);
		
		$worksheet->write(7, 1, "Dirección", $text_blue_bold_left);
		$worksheet->write(7, 3, $DIRECCION, $text_normal_left);
		$worksheet->write(8, 1, "Comuna", $text_blue_bold_left);
		$worksheet->write(8, 3, $COMUNA, $text_normal_left);
		$worksheet->write(8, 4, "Ciudad", $text_blue_bold_left);
		$worksheet->write(8, 5, $CIUDAD, $text_normal_left);
		$worksheet->write(8, 6, "Fono", $text_blue_bold_left);
		$worksheet->write(8, 7, $TELEFONO, $text_normal_left);
		$worksheet->write(8, 8, "Fax",$text_blue_bold_left);
		$worksheet->write(8, 9, $FAX,$text_normal_left);
		$worksheet->write(9, 8, "Fono",$text_blue_bold_left);
		$worksheet->write(9, 9, $TELEFONO,$text_normal_left);
		$worksheet->write(9, 1, "Atención", $text_blue_bold_left);
		$worksheet->write(9, 3, $NOM_PERSONA." ".$EMAIL, $text_normal_left);
		$worksheet->write(10, 1, "Referencia",$text_blue_bold_left);
		$worksheet->write(10, 3, $REFERENCIA,$text_normal_left);
		
		$worksheet->write(12, 1, "Ítem", $titulo_item_border_all);
		$worksheet->write(12, 2, "Modelo", $titulo_item_border_all);
		$worksheet->write(12, 3, "",$titulo_item_border_all);
		$worksheet->write(12, 4, "", $titulo_item_border_all);
		$worksheet->write(12, 5, "", $titulo_item_border_all);
		$worksheet->write(12, 3, "Producto", $titulo_item_border_all);
		$worksheet->merge_cells(12, 3, 12, 6);
		$worksheet->write(12, 6, "", $titulo_item_border_all);
		$worksheet->write(12, 7, "CT", $titulo_item_border_all);
		$worksheet->write(12, 8, "Fría", $titulo_item_border_all);
		$worksheet->write(12, 9, "Caliente", $titulo_item_border_all);
		$worksheet->write(12, 10, "Caudal", $titulo_item_border_all);
		$worksheet->write(12, 11, "Presión", $titulo_item_border_all);
		$worksheet->write(12, 12, "Diámetro", $titulo_item_border_all);
	
		
		$SUMF=0;
		$SUMC=0;
		$C=0;
		for ($i=0 ; $i <count($result); $i++) {
			$ITEM = $result[$i]['ITEM'];
			$NOM_PRODUCTO = $result[$i]['NOM_PRODUCTO'];
			$COD_PRODUCTO = $result[$i]['COD_PRODUCTO'];
			$CANTIDAD = $result[$i]['CANTIDAD'];
			$FRIA = $result[$i]['FRIA'];
			$CALIENTE = $result[$i]['CALIENTE'];
			$CAUDAL = $result[$i]['CAUDAL'];
			$PRESION_AGUA = $result[$i]['PRESION_AGUA'];
			$DIAMETRO_CANERIA = $result[$i]['DIAMETRO_CANERIA'];
			$CONT_FRIA=$result[$i]['CONT_FRIA'];
			$CONT_CALIENTE=$result[$i]['CONT_CALIENTE'];
		
			//$SUMC=$SUMC+$CALIENTE;
			
			$worksheet->write(13+$i, 1,$ITEM, $border_item_left);
			$worksheet->write(13+$i, 2,$COD_PRODUCTO,$border_item_left);
			
			if($COD_PRODUCTO == '')
				$worksheet->write(13+$i, 3, $NOM_PRODUCTO, $border_item_left_bold);
			else
				$worksheet->write(13+$i, 3, $NOM_PRODUCTO, $border_item_left);
		$worksheet->write(13+$i, 7,$CANTIDAD, $border_item_especial_right);
		$worksheet->write(13+$i, 8,$FRIA, $cant_normal);
		$worksheet->write(13+$i, 9,$CALIENTE, $cant_normal);
		$worksheet->write(13+$i, 10,$CAUDAL, $cant_normal);
		$worksheet->write(13+$i, 11,$PRESION_AGUA, $cant_normal);
		$worksheet->write(13+$i, 12,$DIAMETRO_CANERIA, $cant_normal);
		
		
		}
		
		$worksheet->write(13+$i, 1, " ", $border_item_top);
		$worksheet->write(13+$i, 2, " ", $border_item_top);
		$worksheet->write(13+$i, 3, " ", $border_item_top);
		$worksheet->write(13+$i, 4, " ", $border_item_top);
		$worksheet->write(13+$i, 5, " ", $border_item_top);
		$worksheet->write(13+$i, 6, " ", $border_item_top);
		$worksheet->write(13+$i, 7, "TOTAL", $border_item_top_2);
		$worksheet->write(13+$i, 8,$CONT_FRIA, $cant_normal_total);
		$worksheet->write(13+$i, 9,$CONT_CALIENTE, $cant_normal_total);
		$worksheet->write(13+$i, 10," ", $border_item_top);
		$worksheet->write(13+$i, 11, " ", $border_item_top);
		$worksheet->write(13+$i, 12, " ", $border_item_top);
	

		$NOM_EMPRESA_EMISOR = $result[0]['NOM_EMPRESA_EMISOR'];
		$RUT_EMPRESA = $result[0]['RUT_EMPRESA'];
		$DIR_EMPRESA = $result[0]['DIR_EMPRESA'];
		$CIUDAD_EMPRESA = $result[0]['CIUDAD_EMPRESA'];
		$PAIS_EMPRESA = $result[0]['PAIS_EMPRESA'];
		$TEL_EMPRESA = $result[0]['TEL_EMPRESA'];
		$FAX_EMPRESA = $result[0]['FAX_EMPRESA'];
		$MAIL_EMPRESA = $result[0]['MAIL_EMPRESA'];
		$SITIO_WEB_EMPRESA = $result[0]['SITIO_WEB_EMPRESA'];
		$NOM_USUARIO= $result[0]['NOM_USUARIO'];
		$MAIL_USUARIO= $result[0]['MAIL_USUARIO'];
		$CEL_USUARIO= $result[0]['CEL_USUARIO'];
		$FONO_USUARIO= $result[0]['FONO_USUARIO'];

		$FINAL = $result[0]['FINAL'];

		$worksheet->write($row_position+22, 9, $NOM_EMPRESA_EMISOR, $text_blue_bold_center);
		$worksheet->write($row_position+23, 9, $NOM_USUARIO, $text_blue_bold_center);
		$worksheet->write($row_position+24, 9, $MAIL_USUARIO, $text_blue_bold_center);
		$worksheet->write($row_position+25, 9, $FONO_USUARIO."-".$CEL_USUARIO, $text_blue_bold_center);

		$worksheet->write($row_position+28, 1, $NOM_EMPRESA_EMISOR." - RUT: ".$RUT_EMPRESA." - ".$DIR_EMPRESA." - ".$CIUDAD_EMPRESA." - ".$PAIS_EMPRESA." - ".$TEL_EMPRESA." - ".$FAX_EMPRESA, $text_pie_de_pagina);
		$worksheet->write($row_position+29, 5, $MAIL_EMPRESA." - ".$SITIO_WEB_EMPRESA, $text_pie_de_pagina);
		
		
		$workbook->close();
		
		header("Content-Type: application/x-msexcel; name=\"cotizacion_tecnica_agua.xls\"");
		header("Content-Disposition: inline; filename=\"cotizacion_tecnica_agua.xls\"");
		$fh=fopen($fname, "rb");
		fpassthru($fh);
		unlink($fname);
		
		error_reporting(E_ALL);
	}
	
	function printcot_tecnica_ventilacion_excel($con_logo) {
		
		$print_descto = session::get('PRINT_DESCUENTO');
		error_reporting(E_ALL & ~E_NOTICE);
		
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");
		
		$fname = tempnam("/tmp", "producto_ventilación.xls");
		$workbook = &new writeexcel_workbook($fname);
		$cod_cotizacion = $this->get_key();
		$worksheet = &$workbook->addworksheet('COTIZACION_'.$cod_cotizacion);
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$sql = "exec spr_cot_tecnica $cod_cotizacion, 'VENTILACION'";
				
		$result = $db->build_results($sql);
		
		$worksheet->set_row(0, 60);
		$worksheet->set_column(0, 0, 4);
		$worksheet->set_column(1, 2, 7);
		$worksheet->set_column(3, 9, 14);
		$worksheet->set_column(2, 10, 12);
		
		$worksheet->set_column(8, 9, 12);
		$worksheet->set_column(6, 6, 9);
		$worksheet->set_column(7, 7, 11);
		$worksheet->set_column(10, 10, 15);
	
		$worksheet->insert_bitmap('B1',$this->root_dir."images_appl/logo_reporte_excel.bmp");
		
	
		$text =& $workbook->addformat();
		$text->set_font("Verdana");
		$text->set_valign('vcenter');
    
		$text_bold =& $workbook->addformat();
		$text_bold->copy($text);
		$text_bold->set_bold(1);
	
		$text_blue_bold_left =& $workbook->addformat();
		$text_blue_bold_left->copy($text_bold);
		$text_blue_bold_left->set_align('left');
		$text_blue_bold_left->set_color('blue_0x20');

		$text_blue_bold_center =& $workbook->addformat();
		$text_blue_bold_center->copy($text_bold);
		$text_blue_bold_center->set_align('center');
		$text_blue_bold_center->set_color('blue_0x20');
		
		$text_blue_bold_right =& $workbook->addformat();
		$text_blue_bold_right->copy($text_bold);
		$text_blue_bold_right->set_align('right');
		$text_blue_bold_right->set_color('blue_0x20');

		$text_nro_docto =& $workbook->addformat();
		$text_nro_docto->copy($text_blue_bold_right);
		$text_nro_docto->set_size(13);
		
		$text_nro_docto_2 =& $workbook->addformat();
		$text_nro_docto_2->copy($text_blue_bold_left);
		$text_nro_docto_2->set_size(13);
		
		$text_pie_de_pagina =& $workbook->addformat();
		$text_pie_de_pagina->copy($text_blue_bold_left);
		$text_pie_de_pagina->set_size(8);
		
		$text_normal_left =& $workbook->addformat();
		$text_normal_left->copy($text);
		$text_normal_left->set_align('left');
		
		$text_normal_center =& $workbook->addformat();
		$text_normal_center->copy($text);
		$text_normal_center->set_align('center');
		
		$text_normal_right =& $workbook->addformat();
		$text_normal_right->copy($text);
		$text_normal_right->set_align('right');
				
		$text_normal_bold_left =& $workbook->addformat();
		$text_normal_bold_left->copy($text_bold);
		$text_normal_bold_left->set_align('left');
		
		
		$text_normal_bold_center =& $workbook->addformat();
		$text_normal_bold_center->copy($text_bold);
		$text_normal_bold_center->set_align('center');
	
		$text_normal_bold_right =& $workbook->addformat();
		$text_normal_bold_right->copy($text_bold);
		$text_normal_bold_right->set_align('right');
	
		
		$titulo_item_border_all =& $workbook->addformat();
		$titulo_item_border_all->copy($text_blue_bold_center);
		$titulo_item_border_all->set_border_color('black');
		$titulo_item_border_all->set_top(2);
		$titulo_item_border_all->set_bottom(2);
		$titulo_item_border_all->set_right(2);
		$titulo_item_border_all->set_left(2);
		
		$titulo_item_border_all_2 =& $workbook->addformat();
		$titulo_item_border_all_2->copy($text_blue_bold_center);
		$titulo_item_border_all_2->set_border_color('black');
		$titulo_item_border_all_2->set_bottom(2);
		$titulo_item_border_all_2->set_right(2);
		$titulo_item_border_all_2->set_left(2);
		
		$titulo_item_border_all_3 =& $workbook->addformat();
		$titulo_item_border_all_3->copy($text_blue_bold_center);
		$titulo_item_border_all_3->set_border_color('black');
		$titulo_item_border_all_3->set_bottom(2);
		
		$titulo_item_border_all_4 =& $workbook->addformat();
		$titulo_item_border_all_4->copy($text_blue_bold_center);
		$titulo_item_border_all_4->set_border_color('black');
		$titulo_item_border_all_4->set_top(2);
		$titulo_item_border_all_4->set_right(2);
		$titulo_item_border_all_4->set_left(2);
		
		$titulo_item_border_all_5 =& $workbook->addformat();
		$titulo_item_border_all_5->copy($text_blue_bold_center);
		$titulo_item_border_all_5->set_border_color('black');
		$titulo_item_border_all_5->set_top(2);
		$titulo_item_border_all_5->set_left(2);
		
		
		$titulo_item_border_all_merge =& $workbook->addformat();
		$titulo_item_border_all_merge->copy($titulo_item_border_all);
		$titulo_item_border_all_merge->set_merge();
				
	
		$border_item_left = & $workbook->addformat();
		$border_item_left->copy($text_normal_left);
		$border_item_left->set_border_color('black');
		$border_item_left->set_left(2);
		
		$border_item_left_bold = & $workbook->addformat();
		$border_item_left_bold->copy($text_bold);
		$border_item_left_bold->set_border_color('black');
		$border_item_left_bold->set_left(2);
		
		$border_item_left_bold_2 = & $workbook->addformat();
		$border_item_left_bold_2->copy($text_bold);
		$border_item_left_bold_2->set_border_color('black');
		$border_item_left_bold_2->set_bottom(2);
		$border_item_left_bold_2->set_left(2);
		
		$border_item_center = & $workbook->addformat();
		$border_item_center->copy($text_normal_center);
		$border_item_center->set_border_color('black');
		$border_item_center->set_left(2);
		$border_item_center->set_right(2);
		
		$border_item_right = & $workbook->addformat();
		$border_item_right->copy($text_normal_right);
		$border_item_right->set_border_color('black');
		$border_item_right->set_right(2);		
		
		$cant_normal =& $workbook->addformat();
		$cant_normal->copy($border_item_right);
		$cant_normal->set_num_format('0.0');
		
		$cant_normal_total =& $workbook->addformat();
		$cant_normal_total->copy($border_item_right);
		$cant_normal_total->set_num_format('0.0');
		$cant_normal_total->set_border_color('black');
		$cant_normal_total->set_bold(1);
		$cant_normal_total->set_top(2);
		$cant_normal_total->set_bottom(2);
		$cant_normal_total->set_right(2);
		$cant_normal_total->set_left(2);
					
		$monto_normal =& $workbook->addformat();
		$monto_normal->copy($border_item_right);
		$monto_normal->set_num_format('#,##0');
		
		$border_item_top = & $workbook->addformat();
		$border_item_top->copy($text);
		$border_item_top->set_border_color('black');
		$border_item_top->set_top(2);
		
		$border_item_top_2 = & $workbook->addformat();
		$border_item_top_2->copy($text);
		$border_item_top_2->set_color('blue_0x20');
		$border_item_top_2->set_border_color('black');
		$border_item_top_2->set_top(2);
		$border_item_top_2->set_bold(1);
		
		
		$border_item_bottom = & $workbook->addformat();
		$border_item_bottom->copy($text);
		$border_item_bottom->set_border_color('black');
		$border_item_bottom->set_bottom(2);
		
		$border_item_total = & $workbook->addformat();
		$border_item_total->copy($text);
		$border_item_total->set_border_color('black');
		$border_item_total->set_top(2);
		$border_item_total->set_bottom(2);
		$border_item_total->set_right(2);
		
		$border_item_especial_left = & $workbook->addformat();
		$border_item_especial_left->copy($text_normal_left);
		$border_item_especial_left->set_border_color('black');
		$border_item_especial_left->set_left(2);
		$border_item_especial_left->set_right(2);
		
		$border_item_especial_right = & $workbook->addformat();
		$border_item_especial_right->copy($text_normal_right);
		$border_item_especial_right->set_border_color('black');
		$border_item_especial_right->set_left(2);
		$border_item_especial_right->set_right(2);
		
		$border_item_especial_total = & $workbook->addformat();
		$border_item_especial_total->copy($border_item_total);
		$border_item_especial_total->set_border_color('black');
		$border_item_especial_total->set_bold(1);
		
		
		$COD_COTIZACION = $result[0]['COD_COTIZACION'];
		$FECHA_IMPRESO = $result[0]['FECHA_IMPRESO'];
		$FECHA_COTIZACION = $result[0]['FECHA_COTIZACION'];
		$NOM_EMPRESA = $result[0]['NOM_EMPRESA'];
		$RUT = $result[0]['RUT'];
		$DIG_VERIF = $result[0]['DIG_VERIF'];
		$DIRECCION = $result[0]['DIRECCION'];
		$COMUNA = $result[0]['COMUNA'];
		$CIUDAD = $result[0]['CIUDAD'];
		$TELEFONO = $result[0]['TELEFONO'];
		$FAX = $result[0]['FAX'];	
		$NOM_PERSONA = $result[0]['NOM_PERSONA'];
		$EMAIL = $result[0]['EMAIL'];
		$REFERENCIA = $result[0]['REFERENCIA'];
		$SIMBOLO = $result[0]['SIMBOLO'];
		
		$worksheet->write(1, 9, "COTIZACION Nº".$COD_COTIZACION, $text_nro_docto);
		$worksheet->write(2, 1, "PRODUCTOS CON VENTILACION", $text_nro_docto_2);
		$worksheet->write(4, 1, "Santiago,".$FECHA_COTIZACION, $text_blue_bold_left);
		$worksheet->write(6, 1, "Razón Social", $text_blue_bold_left);
		
		$worksheet->write(6, 3, $NOM_EMPRESA, $text_normal_bold_left);
		$worksheet->write(6, 8, "Rut", $text_blue_bold_left);
		
		$rut=number_format($RUT, 0, ',', '.');
		$rut=$rut.'-'.$DIG_VERIF;
		
		$worksheet->write(6, 9, $rut, $text_normal_bold_left);
		
		$worksheet->write(7, 1, "Dirección", $text_blue_bold_left);
		$worksheet->write(7, 3, $DIRECCION, $text_normal_left);
		$worksheet->write(8, 1, "Comuna", $text_blue_bold_left);
		$worksheet->write(8, 3, $COMUNA, $text_normal_left);
		$worksheet->write(8, 4, "Ciudad", $text_blue_bold_left);
		$worksheet->write(8, 5, $CIUDAD, $text_normal_left);
		$worksheet->write(8, 6, "Fono", $text_blue_bold_left);
		$worksheet->write(8, 7, $TELEFONO, $text_normal_left);
		$worksheet->write(8, 8, "Fax",$text_blue_bold_left);
		$worksheet->write(8, 9, $FAX,$text_normal_left);
		$worksheet->write(9, 8, "Fono",$text_blue_bold_left);
		$worksheet->write(9, 9, $TELEFONO,$text_normal_left);
		$worksheet->write(9, 1, "Atención", $text_blue_bold_left);
		$worksheet->write(9, 3, $NOM_PERSONA." ".$EMAIL, $text_normal_left);
		$worksheet->write(10, 1, "Referencia",$text_blue_bold_left);
		$worksheet->write(10, 3, $REFERENCIA,$text_normal_left);
		
		$worksheet->write(12, 1, "Ítem", $titulo_item_border_all_4);
		$worksheet->write(13, 1, "", $titulo_item_border_all_2);
		$worksheet->write(12, 2, "Modelo", $titulo_item_border_all_4);
		$worksheet->write(13, 2, "", $titulo_item_border_all_2);
		$worksheet->write(12, 3, "",$titulo_item_border_all_4);
		$worksheet->write(13, 3, "",$titulo_item_border_all_3);
		$worksheet->write(12, 4, "", $titulo_item_border_all_4);
		$worksheet->write(13, 4, "", $titulo_item_border_all_3);
		$worksheet->write(12, 5, "", $titulo_item_border_all_4);
		$worksheet->write(13, 5, "", $titulo_item_border_all_3);
		$worksheet->write(12, 6, "", $titulo_item_border_all_4);
		$worksheet->write(13, 6, "", $titulo_item_border_all_3);
		$worksheet->write(12, 3, "Producto", $titulo_item_border_all_4);
		$worksheet->merge_cells(12, 3, 12, 6);
		$worksheet->write(12, 7, "Cantidad", $titulo_item_border_all_4);
		$worksheet->write(13, 7, "", $titulo_item_border_all_2);
		$worksheet->write(12, 8, "Vol. Vent.", $titulo_item_border_all_4);
		$worksheet->write(13, 8, "[m3/Hr]", $titulo_item_border_all_2);
		$worksheet->write(12, 9, "Total", $titulo_item_border_all_4);
		$worksheet->write(13, 9, "", $titulo_item_border_all_2);
		$worksheet->write(12, 10, "Caida Presión", $titulo_item_border_all_4);
		$worksheet->write(13, 10, "[mmCA]", $titulo_item_border_all_2);

		
		$SUMT=0;
		for ($i=0 ; $i <count($result); $i++) {
			$ITEM = $result[$i]['ITEM'];
			$NOM_PRODUCTO = $result[$i]['NOM_PRODUCTO'];
			$COD_PRODUCTO = $result[$i]['COD_PRODUCTO'];
			$CANTIDAD = $result[$i]['CANTIDAD'];
			$VOLUMEN = $result[$i]['VOLUMEN'];
			$TOTAL_VOL = $result[$i]['TOTAL_VOL'];
			$CAIDA_PRESION = $result[$i]['CAIDA_PRESION'];
			
			
			$SUMT=$SUMT+$TOTAL_VOL;
			$worksheet->write(14+$i, 1,$ITEM, $border_item_left);
			$worksheet->write(14+$i, 2,$COD_PRODUCTO,$border_item_left);
			
			if($COD_PRODUCTO == '')
				$worksheet->write(14+$i, 3, $NOM_PRODUCTO, $border_item_left_bold);
			else
				$worksheet->write(14+$i, 3, $NOM_PRODUCTO, $border_item_left);
		$worksheet->write(14+$i, 7,$CANTIDAD, $border_item_especial_right);
		$worksheet->write(14+$i, 8,$VOLUMEN, $cant_normal);
		$worksheet->write(14+$i, 9,$TOTAL_VOL, $cant_normal);
		$worksheet->write(14+$i, 10,$CAIDA_PRESION, $cant_normal);
	
		}
		
		$worksheet->write(14+$i, 1, " ", $border_item_top);
		$worksheet->write(14+$i, 2, " ", $border_item_top);
		$worksheet->write(14+$i, 3, " ", $border_item_top);
		$worksheet->write(14+$i, 4, " ", $border_item_top);
		$worksheet->write(14+$i, 5, " ", $border_item_top);
		$worksheet->write(14+$i, 6, " ", $border_item_top);
		$worksheet->write(14+$i, 7, " ", $border_item_top);
		$worksheet->write(14+$i, 8, "TOTAL", $border_item_top_2);
		$worksheet->write(14+$i, 9, $SUMT, $cant_normal_total);
		$worksheet->write(14+$i, 10, " ", $border_item_top);
	
	
		$NOM_EMPRESA_EMISOR = $result[0]['NOM_EMPRESA_EMISOR'];
		$RUT_EMPRESA = $result[0]['RUT_EMPRESA'];
		$DIR_EMPRESA = $result[0]['DIR_EMPRESA'];
		$CIUDAD_EMPRESA = $result[0]['CIUDAD_EMPRESA'];
		$PAIS_EMPRESA = $result[0]['PAIS_EMPRESA'];
		$TEL_EMPRESA = $result[0]['TEL_EMPRESA'];
		$FAX_EMPRESA = $result[0]['FAX_EMPRESA'];
		$MAIL_EMPRESA = $result[0]['MAIL_EMPRESA'];
		$SITIO_WEB_EMPRESA = $result[0]['SITIO_WEB_EMPRESA'];
		$NOM_USUARIO= $result[0]['NOM_USUARIO'];
		$MAIL_USUARIO= $result[0]['MAIL_USUARIO'];
		$CEL_USUARIO= $result[0]['CEL_USUARIO'];
		$FONO_USUARIO= $result[0]['FONO_USUARIO'];

		$FINAL = $result[0]['FINAL'];

		$worksheet->write($row_position+22, 9, $NOM_EMPRESA_EMISOR, $text_blue_bold_center);
		$worksheet->write($row_position+23, 9, $NOM_USUARIO, $text_blue_bold_center);
		$worksheet->write($row_position+24, 9, $MAIL_USUARIO, $text_blue_bold_center);
		$worksheet->write($row_position+25, 9, $FONO_USUARIO."-".$CEL_USUARIO, $text_blue_bold_center);

		$worksheet->write($row_position+28, 1, $NOM_EMPRESA_EMISOR." - RUT: ".$RUT_EMPRESA." - ".$DIR_EMPRESA." - ".$CIUDAD_EMPRESA." - ".$PAIS_EMPRESA." - ".$TEL_EMPRESA." - ".$FAX_EMPRESA, $text_pie_de_pagina);
		$worksheet->write($row_position+29, 5, $MAIL_EMPRESA." - ".$SITIO_WEB_EMPRESA, $text_pie_de_pagina);
		
		
		$workbook->close();
		
		header("Content-Type: application/x-msexcel; name=\"cotizacion_tecnico_ventilación.xls\"");
		header("Content-Disposition: inline; filename=\"cotizacion_tecnico_ventilación.xls\"");
		$fh=fopen($fname, "rb");
		fpassthru($fh);
		unlink($fname);
		
		error_reporting(E_ALL);	
	}
	function printcot_tecnica_desague_excel($con_logo) {
		
		$print_descto = session::get('PRINT_DESCUENTO');
		error_reporting(E_ALL & ~E_NOTICE);
		
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
		require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");
		
		$fname = tempnam("/tmp", "producto_desague.xls");
		$workbook = &new writeexcel_workbook($fname);
		$cod_cotizacion = $this->get_key();
		$worksheet = &$workbook->addworksheet('COTIZACION_'.$cod_cotizacion);
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$sql = "exec spr_cot_tecnica $cod_cotizacion, 'DESAGUE'";
				
		$result = $db->build_results($sql);
		
		$worksheet->set_row(0, 60);
		$worksheet->set_column(0, 0, 4);
		$worksheet->set_column(1, 2, 7);
		$worksheet->set_column(3, 9, 14);
		$worksheet->set_column(2, 10, 12);
		
		$worksheet->set_column(8, 10, 12);
		$worksheet->set_column(6, 6, 9);
		$worksheet->set_column(7, 7, 11);
		$worksheet->insert_bitmap('B1',$this->root_dir."images_appl/logo_reporte_excel.bmp");
		
	
		$text =& $workbook->addformat();
		$text->set_font("Verdana");
		$text->set_valign('vcenter');
    
		$text_bold =& $workbook->addformat();
		$text_bold->copy($text);
		$text_bold->set_bold(1);
	
		$text_blue_bold_left =& $workbook->addformat();
		$text_blue_bold_left->copy($text_bold);
		$text_blue_bold_left->set_align('left');
		$text_blue_bold_left->set_color('blue_0x20');

		$text_blue_bold_center =& $workbook->addformat();
		$text_blue_bold_center->copy($text_bold);
		$text_blue_bold_center->set_align('center');
		$text_blue_bold_center->set_color('blue_0x20');
		
		$text_blue_bold_right =& $workbook->addformat();
		$text_blue_bold_right->copy($text_bold);
		$text_blue_bold_right->set_align('right');
		$text_blue_bold_right->set_color('blue_0x20');

		$text_nro_docto =& $workbook->addformat();
		$text_nro_docto->copy($text_blue_bold_right);
		$text_nro_docto->set_size(13);
		
		$text_nro_docto_2 =& $workbook->addformat();
		$text_nro_docto_2->copy($text_blue_bold_left);
		$text_nro_docto_2->set_size(13);
		
		$text_pie_de_pagina =& $workbook->addformat();
		$text_pie_de_pagina->copy($text_blue_bold_left);
		$text_pie_de_pagina->set_size(8);
		
		$text_normal_left =& $workbook->addformat();
		$text_normal_left->copy($text);
		$text_normal_left->set_align('left');
		
		$text_normal_center =& $workbook->addformat();
		$text_normal_center->copy($text);
		$text_normal_center->set_align('center');
		
		$text_normal_right =& $workbook->addformat();
		$text_normal_right->copy($text);
		$text_normal_right->set_align('right');
				
		$text_normal_bold_left =& $workbook->addformat();
		$text_normal_bold_left->copy($text_bold);
		$text_normal_bold_left->set_align('left');
		
		
		$text_normal_bold_center =& $workbook->addformat();
		$text_normal_bold_center->copy($text_bold);
		$text_normal_bold_center->set_align('center');
	
		$text_normal_bold_right =& $workbook->addformat();
		$text_normal_bold_right->copy($text_bold);
		$text_normal_bold_right->set_align('right');
	
		
		$titulo_item_border_all =& $workbook->addformat();
		$titulo_item_border_all->copy($text_blue_bold_center);
		$titulo_item_border_all->set_border_color('black');
		$titulo_item_border_all->set_top(2);
		$titulo_item_border_all->set_bottom(2);
		$titulo_item_border_all->set_right(2);
		$titulo_item_border_all->set_left(2);
		
		$titulo_item_border_all_2 =& $workbook->addformat();
		$titulo_item_border_all_2->copy($text_blue_bold_center);
		$titulo_item_border_all_2->set_border_color('black');
		$titulo_item_border_all_2->set_bottom(2);
		$titulo_item_border_all_2->set_right(2);
		$titulo_item_border_all_2->set_left(2);
		
		$titulo_item_border_all_3 =& $workbook->addformat();
		$titulo_item_border_all_3->copy($text_blue_bold_center);
		$titulo_item_border_all_3->set_border_color('black');
		$titulo_item_border_all_3->set_bottom(2);
		
		$titulo_item_border_all_4 =& $workbook->addformat();
		$titulo_item_border_all_4->copy($text_blue_bold_center);
		$titulo_item_border_all_4->set_border_color('black');
		$titulo_item_border_all_4->set_top(2);
		$titulo_item_border_all_4->set_right(2);
		$titulo_item_border_all_4->set_left(2);
		
		$titulo_item_border_all_5 =& $workbook->addformat();
		$titulo_item_border_all_5->copy($text_blue_bold_center);
		$titulo_item_border_all_5->set_border_color('black');
		$titulo_item_border_all_5->set_top(2);
		$titulo_item_border_all_5->set_left(2);
		
		
		$titulo_item_border_all_merge =& $workbook->addformat();
		$titulo_item_border_all_merge->copy($titulo_item_border_all);
		$titulo_item_border_all_merge->set_merge();
				
	
		$border_item_left = & $workbook->addformat();
		$border_item_left->copy($text_normal_left);
		$border_item_left->set_border_color('black');
		$border_item_left->set_left(2);
		
		$border_item_left_bold = & $workbook->addformat();
		$border_item_left_bold->copy($text_bold);
		$border_item_left_bold->set_border_color('black');
		$border_item_left_bold->set_left(2);
		
		$border_item_left_bold_2 = & $workbook->addformat();
		$border_item_left_bold_2->copy($text_bold);
		$border_item_left_bold_2->set_border_color('black');
		$border_item_left_bold_2->set_bottom(2);
		$border_item_left_bold_2->set_left(2);
		
		$border_item_center = & $workbook->addformat();
		$border_item_center->copy($text_normal_center);
		$border_item_center->set_border_color('black');
		$border_item_center->set_left(2);
		$border_item_center->set_right(2);
		
		$border_item_right = & $workbook->addformat();
		$border_item_right->copy($text_normal_right);
		$border_item_right->set_border_color('black');
		$border_item_right->set_right(2);		
		
		$cant_normal =& $workbook->addformat();
		$cant_normal->copy($border_item_right);
		$cant_normal->set_num_format('0.0');
		
		$cant_normal_total =& $workbook->addformat();
		$cant_normal_total->copy($border_item_right);
		$cant_normal_total->set_num_format('0.0');
		$cant_normal_total->set_border_color('black');
		$cant_normal_total->set_bold(1);
		$cant_normal_total->set_top(2);
		$cant_normal_total->set_bottom(2);
		$cant_normal_total->set_right(2);
		$cant_normal_total->set_left(2);
					
		$monto_normal =& $workbook->addformat();
		$monto_normal->copy($border_item_right);
		$monto_normal->set_num_format('#,##0');
		
		$border_item_top = & $workbook->addformat();
		$border_item_top->copy($text);
		$border_item_top->set_border_color('black');
		$border_item_top->set_top(2);
		
		$border_item_top_2 = & $workbook->addformat();
		$border_item_top_2->copy($text);
		$border_item_top_2->set_color('blue_0x20');
		$border_item_top_2->set_border_color('black');
		$border_item_top_2->set_top(2);
		$border_item_top_2->set_bold(1);
		
		
		$border_item_bottom = & $workbook->addformat();
		$border_item_bottom->copy($text);
		$border_item_bottom->set_border_color('black');
		$border_item_bottom->set_bottom(2);
		
		$border_item_total = & $workbook->addformat();
		$border_item_total->copy($text);
		$border_item_total->set_border_color('black');
		$border_item_total->set_top(2);
		$border_item_total->set_bottom(2);
		$border_item_total->set_right(2);
		
		$border_item_especial_left = & $workbook->addformat();
		$border_item_especial_left->copy($text_normal_left);
		$border_item_especial_left->set_border_color('black');
		$border_item_especial_left->set_left(2);
		$border_item_especial_left->set_right(2);
		
		$border_item_especial_right = & $workbook->addformat();
		$border_item_especial_right->copy($text_normal_right);
		$border_item_especial_right->set_border_color('black');
		$border_item_especial_right->set_left(2);
		$border_item_especial_right->set_right(2);
		
		$border_item_especial_total = & $workbook->addformat();
		$border_item_especial_total->copy($border_item_total);
		$border_item_especial_total->set_border_color('black');
		$border_item_especial_total->set_bold(1);
		
		
		$COD_COTIZACION = $result[0]['COD_COTIZACION'];
		$FECHA_IMPRESO = $result[0]['FECHA_IMPRESO'];
		$FECHA_COTIZACION = $result[0]['FECHA_COTIZACION'];
		$NOM_EMPRESA = $result[0]['NOM_EMPRESA'];
		$RUT = $result[0]['RUT'];
		$DIG_VERIF = $result[0]['DIG_VERIF'];
		$DIRECCION = $result[0]['DIRECCION'];
		$COMUNA = $result[0]['COMUNA'];
		$CIUDAD = $result[0]['CIUDAD'];
		$TELEFONO = $result[0]['TELEFONO'];
		$FAX = $result[0]['FAX'];	
		$NOM_PERSONA = $result[0]['NOM_PERSONA'];
		$EMAIL = $result[0]['EMAIL'];
		$REFERENCIA = $result[0]['REFERENCIA'];
		$SIMBOLO = $result[0]['SIMBOLO'];
		
		$worksheet->write(1, 9, "COTIZACION Nº".$COD_COTIZACION, $text_nro_docto);
		$worksheet->write(2, 1, "PRODUCTO CON DESAGUE", $text_nro_docto_2);
		$worksheet->write(4, 1, "Santiago,".$FECHA_COTIZACION, $text_blue_bold_left);
		$worksheet->write(6, 1, "Razón Social", $text_blue_bold_left);
		
		$worksheet->write(6, 3, $NOM_EMPRESA, $text_normal_bold_left);
		$worksheet->write(6, 8, "Rut", $text_blue_bold_left);
		
		$rut=number_format($RUT, 0, ',', '.');
		$rut=$rut.'-'.$DIG_VERIF;
		
		$worksheet->write(6, 9, $rut, $text_normal_bold_left);
		
		$worksheet->write(7, 1, "Dirección", $text_blue_bold_left);
		$worksheet->write(7, 3, $DIRECCION, $text_normal_left);
		$worksheet->write(8, 1, "Comuna", $text_blue_bold_left);
		$worksheet->write(8, 3, $COMUNA, $text_normal_left);
		$worksheet->write(8, 4, "Ciudad", $text_blue_bold_left);
		$worksheet->write(8, 5, $CIUDAD, $text_normal_left);
		$worksheet->write(8, 6, "Fono", $text_blue_bold_left);
		$worksheet->write(8, 7, $TELEFONO, $text_normal_left);
		$worksheet->write(8, 8, "Fax",$text_blue_bold_left);
		$worksheet->write(8, 9, $FAX,$text_normal_left);
		$worksheet->write(9, 8, "Fono",$text_blue_bold_left);
		$worksheet->write(9, 9, $TELEFONO,$text_normal_left);
		$worksheet->write(9, 1, "Atención", $text_blue_bold_left);
		$worksheet->write(9, 3, $NOM_PERSONA." ".$EMAIL, $text_normal_left);
		$worksheet->write(10, 1, "Referencia",$text_blue_bold_left);
		$worksheet->write(10, 3, $REFERENCIA,$text_normal_left);
		
		$worksheet->write(12, 1, "Ítem", $titulo_item_border_all);
		$worksheet->write(12, 2, "Modelo", $titulo_item_border_all);
		$worksheet->write(12, 3, "",$titulo_item_border_all);
		$worksheet->write(12, 4, "", $titulo_item_border_all);
		$worksheet->write(12, 5, "", $titulo_item_border_all);
		$worksheet->write(12, 3, "Producto", $titulo_item_border_all);
		$worksheet->merge_cells(12, 3, 12, 7);
		$worksheet->write(12, 6, "", $titulo_item_border_all);
		$worksheet->write(12, 7, "", $titulo_item_border_all);
		$worksheet->write(12, 8, "Cantidad", $titulo_item_border_all);
		$worksheet->write(12, 9, "Diámetro", $titulo_item_border_all);
		
	
		for ($i=0 ; $i <count($result); $i++) {
			$ITEM = $result[$i]['ITEM'];
			$NOM_PRODUCTO = $result[$i]['NOM_PRODUCTO'];
			$COD_PRODUCTO = $result[$i]['COD_PRODUCTO'];
			$CANTIDAD = $result[$i]['CANTIDAD'];
			$DIAMETRO_DESAGUE = $result[$i]['DIAMETRO_DESAGUE'];
			
			$worksheet->write(13+$i, 1,$ITEM, $border_item_left);
			$worksheet->write(13+$i, 2,$COD_PRODUCTO,$border_item_left);
			
			if($COD_PRODUCTO == '')
				$worksheet->write(13+$i, 3, $NOM_PRODUCTO, $border_item_left_bold);
			else
				$worksheet->write(13+$i, 3, $NOM_PRODUCTO, $border_item_left);
		$worksheet->write(13+$i, 8,$CANTIDAD, $border_item_especial_right);
		$worksheet->write(13+$i, 9,$DIAMETRO_DESAGUE, $cant_normal);
		}
		
		$worksheet->write(13+$i, 1, " ", $border_item_top);
		$worksheet->write(13+$i, 2, " ", $border_item_top);
		$worksheet->write(13+$i, 3, " ", $border_item_top);
		$worksheet->write(13+$i, 4, " ", $border_item_top);
		$worksheet->write(13+$i, 5, " ", $border_item_top);
		$worksheet->write(13+$i, 6, " ", $border_item_top);
		$worksheet->write(13+$i, 7, " ", $border_item_top);
		$worksheet->write(13+$i, 8, " ", $border_item_top);
		$worksheet->write(13+$i, 9, "", $border_item_top);
		

		$NOM_EMPRESA_EMISOR = $result[0]['NOM_EMPRESA_EMISOR'];
		$RUT_EMPRESA = $result[0]['RUT_EMPRESA'];
		$DIR_EMPRESA = $result[0]['DIR_EMPRESA'];
		$CIUDAD_EMPRESA = $result[0]['CIUDAD_EMPRESA'];
		$PAIS_EMPRESA = $result[0]['PAIS_EMPRESA'];
		$TEL_EMPRESA = $result[0]['TEL_EMPRESA'];
		$FAX_EMPRESA = $result[0]['FAX_EMPRESA'];
		$MAIL_EMPRESA = $result[0]['MAIL_EMPRESA'];
		$SITIO_WEB_EMPRESA = $result[0]['SITIO_WEB_EMPRESA'];
		$NOM_USUARIO= $result[0]['NOM_USUARIO'];
		$MAIL_USUARIO= $result[0]['MAIL_USUARIO'];
		$CEL_USUARIO= $result[0]['CEL_USUARIO'];
		$FONO_USUARIO= $result[0]['FONO_USUARIO'];

		$FINAL = $result[0]['FINAL'];

		$worksheet->write($row_position+22, 8, $NOM_EMPRESA_EMISOR, $text_blue_bold_center);
		$worksheet->write($row_position+23, 8, $NOM_USUARIO, $text_blue_bold_center);
		$worksheet->write($row_position+24, 8, $MAIL_USUARIO, $text_blue_bold_center);
		$worksheet->write($row_position+25, 8, $FONO_USUARIO."-".$CEL_USUARIO, $text_blue_bold_center);

		$worksheet->write($row_position+28, 1, $NOM_EMPRESA_EMISOR." - RUT: ".$RUT_EMPRESA." - ".$DIR_EMPRESA." - ".$CIUDAD_EMPRESA." - ".$PAIS_EMPRESA." - ".$TEL_EMPRESA." - ".$FAX_EMPRESA, $text_pie_de_pagina);
		$worksheet->write($row_position+29, 5, $MAIL_EMPRESA." - ".$SITIO_WEB_EMPRESA, $text_pie_de_pagina);
		
		
		$workbook->close();
		
		header("Content-Type: application/x-msexcel; name=\"cotizacion_tecnica_desague.xls\"");
		header("Content-Disposition: inline; filename=\"cotizacion_tecnica_desague.xls\"");
		$fh=fopen($fname, "rb");
		fpassthru($fh);
		unlink($fname);
		
		error_reporting(E_ALL);
	}

	function creada_desde($cod_cotizacion) {			
		$this->load_cotizacion($cod_cotizacion);	
		$this->dws['dw_cotizacion']->set_item(0, 'COD_COTIZACION','');
		$this->dws['dw_cotizacion']->set_item(0, 'FECHA_COTIZACION', $this->current_date());
		$this->dws['dw_cotizacion']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_cotizacion']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->dws['dw_cotizacion']->set_item(0, 'COD_COTIZACION_DESDE', $cod_cotizacion);
		$this->dws['dw_cotizacion']->set_item(0, 'LL_LLAMADO','none');
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select	  COD_USUARIO
						, PORC_PARTICIPACION 
				from 	  USUARIO 
				where	  COD_USUARIO = $this->cod_usuario 
				and		  es_vendedor = 'S'";
		$result = $db->build_results($sql);
		
		if (count($result)>0) {
			$this->dws['dw_cotizacion']->set_item(0, 'COD_USUARIO_VENDEDOR1',$this->cod_usuario);
			$this->dws['dw_cotizacion']->set_item(0, 'PORC_VENDEDOR1', $result[0]['PORC_PARTICIPACION']);
		}
		else
		{
			$this->dws['dw_cotizacion']->set_item(0, 'COD_USUARIO_VENDEDOR1','');
			$this->dws['dw_cotizacion']->set_item(0, 'PORC_VENDEDOR1','');
		}
				
		$num_dif = 0;			

		// tiene priv para usar precio de la cot
		$cod_usuario = session::get("COD_USUARIO");
		$puede_usar_precio_cot = w_base::tiene_privilegio_opcion_usuario('990505', $cod_usuario);
		
		// validez de la oferta
		$sql_parametro="SELECT VALOR 
						FROM PARAMETRO
						WHERE COD_PARAMETRO = 7";
		$result_parametro = $db->build_results($sql_parametro);

		$sql="SELECT ".$result_parametro[0]['VALOR']." + DATEDIFF(DAY, GETDATE(), FECHA_COTIZACION) VALIDEZ
			  FROM COTIZACION
			  WHERE COD_COTIZACION = $cod_cotizacion";
		$result = $db->build_results($sql);
		
		//////////
		
		for($i=0; $i<$this->dws['dw_item_cotizacion']->row_count(); $i++){						
			$cod_producto 	= $this->dws['dw_item_cotizacion']->get_item($i, 'COD_PRODUCTO');
			$precio_cot		= $this->dws['dw_item_cotizacion']->get_item($i, 'PRECIO');
															
			$result			= $db->build_results("select 
															PRECIO_VENTA_PUBLICO
														  , PRECIO_LIBRE 
												  from 		PRODUCTO 
												  where 	COD_PRODUCTO = '$cod_producto'");
			// para los TE, E, I, etc Se los salta
			if ($result[0]['PRECIO_LIBRE']=='S') 
				continue;
			
			$precio_bd		= $result[0]['PRECIO_VENTA_PUBLICO'];
			if($precio_bd != $precio_cot ){
				$num_dif++;
			}	

			if (!$puede_usar_precio_cot) {
				$this->dws['dw_item_cotizacion']->set_item($i, 'PRECIO', $precio_bd);
			}													
		}


		// Cambia el status de las los items
		for($i=0; $i<$this->dws['dw_item_cotizacion']->row_count(); $i++){
			$this->dws['dw_item_cotizacion']->set_item($i, 'COD_ITEM_COTIZACION', '');
			$this->dws['dw_item_cotizacion']->set_status_row($i, K_ROW_NEW_MODIFIED);
		}

	/*	if($num_dif > 0)	// && $puede_usar_precio_cot)
			$this->que_precio_usa($cod_cotizacion);	*/
			
		if (session::is_set('usa_precio_prod')) {			
			session::un_set('usa_precio_prod');
			$this->usa_precio_prod();
		}

		$this->dws['dw_cotizacion']->set_item(0, 'DESDE_COTI','');
        $this->dws['dw_cotizacion']->set_item(0, 'DESDE_SOLI','none');
        $this->dws['dw_cotizacion']->set_item(0, 'CONTACTO_WEB','none');
        $this->dws['dw_cotizacion']->set_item(0, 'LL_LLAMADO','');
        
		if (session::is_set('CREADA_DESDE_COTIZACION_COD_RECHAZADA')) {
			$this->dws['dw_cotizacion']->set_item(0, 'NOM_ESTADO_COTIZACION','EMITIDA');
			$this->dws['dw_cotizacion']->set_item(0, 'RECHAZADA','N');
			$this->dws['dw_cotizacion']->set_item(0, 'COD_TIPO_RECHAZO','');
			$this->dws['dw_cotizacion']->set_item(0, 'TEXTO_RECHAZO','');
			$this->dws['dw_cotizacion']->set_item(0, 'DISPLAY_RECHAZO', 'none');
			session::un_set('CREADA_DESDE_COTIZACION_COD_RECHAZADA');
			$this->alert('La Cotización Nº '.$cod_cotizacion.' se encuentra en estado rechazada.\nAl crear una nueva Cotización, la Cotización Nº '.$cod_cotizacion.' quedara en estado RE-ABIERTA.\nFavor considere esta situación.');	
		}
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_VALIDA_OFERTA, $this->cod_usuario);
		if($priv <> 'E'){
			$this->dws['dw_cotizacion']->set_entrable('VALIDEZ_OFERTA', false);
		}
	}


////////////////////////////////////////////////////////////////////////////////
	function crear_desde_solicitud($cod_solicitud){
		session::un_set('SOLICITUD_COTIZACION');
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$num_dif = 0;
		// tiene priv para usar precio de la cot
		$cod_usuario = session::get("COD_USUARIO");
		$puede_usar_precio_cot = w_base::tiene_privilegio_opcion_usuario('990505', $cod_usuario);
		// validez de la oferta
		$sql_parametro="SELECT VALOR 
						FROM PARAMETRO
						WHERE COD_PARAMETRO = 7";
		$result_parametro = $db->build_results($sql_parametro);
		$sql="SELECT ".$result_parametro[0]['VALOR']." + DATEDIFF(DAY, GETDATE(), FECHA_SOLICITUD_COTIZACION) VALIDEZ
			  FROM SOLICITUD_COTIZACION
			  WHERE COD_SOLICITUD_COTIZACION = $cod_solicitud";
		$result = $db->build_results($sql);
		
		if($result[0]['VALIDEZ'] >= 0)
			$validez_oferta = true;
		else
			$validez_oferta = false;
		//////////
			
		$sql="SELECT	ISC.COD_SOLICITUD_COTIZACION 
						, ISC.COD_PRODUCTO
						, ISC.NOM_PRODUCTO
						, ISC.CANTIDAD
						, ISC.PRECIO
						, ISC.TOTAL_PRECIO
						, SC.COD_SOLICITUD_COTIZACION
						, C.NOM_CONTACTO 
						, C.RUT 
						, C.DIG_VERIF
						, C.NOM_CIUDAD
						,dbo.f_contacto_telefono(CP.COD_CONTACTO_PERSONA,1) TELEFONO 
						,dbo.f_contacto_telefono(CP.COD_CONTACTO_PERSONA,2) CELULAR 
						,CP.NOM_PERSONA
						,CP.MAIL 
						,LL.MENSAJE
						,dbo.f_get_parametro(1) PORC_IVA
						,P.PRECIO_VENTA_PUBLICO
				FROM	ITEM_SOLICITUD_COTIZACION ISC RIGHT OUTER JOIN SOLICITUD_COTIZACION SC ON  ISC.COD_SOLICITUD_COTIZACION = SC.COD_SOLICITUD_COTIZACION
															LEFT OUTER JOIN PRODUCTO P ON P.COD_PRODUCTO = ISC.COD_PRODUCTO
						, CONTACTO C
						,CONTACTO_PERSONA CP
						,LLAMADO LL
				WHERE	  SC.COD_SOLICITUD_COTIZACION = $cod_solicitud
				AND		  C.COD_CONTACTO = SC.COD_CONTACTO
				AND       C.COD_CONTACTO = CP.COD_CONTACTO
				AND       SC.COD_LLAMADO = LL.COD_LLAMADO
				ORDER BY  COD_ITEM_SOLICITUD_COTIZACION";
		 
        $result = $db->build_results($sql);
        for ($i=0; $i<count($result); $i++) {

	        $row = $this->dws['dw_item_cotizacion']->insert_row();
	        $this->dws['dw_item_cotizacion']->set_item($row, 'COD_PRODUCTO', $result[$i]['COD_PRODUCTO']);
	        $this->dws['dw_item_cotizacion']->set_item($row, 'NOM_PRODUCTO', $result[$i]['NOM_PRODUCTO']);
	        $cantidad = $result[$i]['CANTIDAD'];
	        $precio = $result[$i]['PRECIO'];
	        $this->dws['dw_item_cotizacion']->set_item($row, 'CANTIDAD', $cantidad);
	        $this->dws['dw_item_cotizacion']->set_item($row, 'PRECIO', $precio);
	        
			$precio_bd	= $result[$i]['PRECIO_VENTA_PUBLICO'];
			if($precio_bd != $precio)
				$num_dif++;
				
			if (!$puede_usar_precio_cot || !$validez_oferta) {
				$precio = $precio_bd;
				$this->dws['dw_item_cotizacion']->set_item($i, 'PRECIO', $precio_bd);
			}													
	        
	        $this->dws['dw_item_cotizacion']->set_item($row, 'TOTAL', $cantidad * $precio);
        }
		$this->dws['dw_item_cotizacion']->calc_computed();
		$sum_total = $this->dws['dw_item_cotizacion']->get_item(0, 'SUM_TOTAL');
        $this->dws['dw_cotizacion']->set_item(0, 'SUM_TOTAL', $sum_total); 
		$this->dws['dw_cotizacion']->set_item(0, 'PORC_IVA', $result[0]['PORC_IVA']);
		$this->dws['dw_cotizacion']->calc_computed();
        
		$rut = $result[0]['RUT'].'-'.$result[0]['DIG_VERIF'];
		
		
        $this->dws['dw_cotizacion']->set_item(0,'NOM_CONTACTO', $result[0]['NOM_PERSONA']);
        $this->dws['dw_cotizacion']->set_item(0,'RUT_CONTACTO', $rut);
        $this->dws['dw_cotizacion']->set_item(0,'EMPRESA_CONTACTO', $result[0]['NOM_CONTACTO']);
        $this->dws['dw_cotizacion']->set_item(0,'CIUDAD_CONTACTO', $result[0]['NOM_CIUDAD']);
        $this->dws['dw_cotizacion']->set_item(0,'TELEFONO_CONTACTO', $result[0]['TELEFONO']);
        $this->dws['dw_cotizacion']->set_item(0,'CELULAR_CONTACTO', $result[0]['CELULAR']);
        $this->dws['dw_cotizacion']->set_item(0,'EMAIL_CONTACTO', $result[0]['MAIL']);
        $this->dws['dw_cotizacion']->set_item(0,'COMENTARIO_CONTACTO', $result[0]['MENSAJE']);
        $this->dws['dw_cotizacion']->set_item(0, 'CONTACTO_WEB','');
        $this->dws['dw_cotizacion']->set_item(0, 'DESDE_COTI','none');
        $this->dws['dw_cotizacion']->set_item(0, 'DESDE_SOLI','');
        $this->dws['dw_cotizacion']->set_item(0, 'LL_LLAMADO','none');
        $this->dws['dw_cotizacion']->set_item(0, 'COD_SOLICITUD_COTIZACION',$cod_solicitud);
        $this->dws['dw_cotizacion']->set_item(0, 'COD_ORIGEN_COTIZACION',6);
          
        session::set('SOLICITUD_COTIZACION', $cod_solicitud);
		
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_VALIDA_OFERTA, $this->cod_usuario);
		if($priv <> 'E'){
			$this->dws['dw_cotizacion']->set_entrable('VALIDEZ_OFERTA', false);
		}
		/*if($num_dif > 0)	// && $puede_usar_precio_cot && $validez_oferta)
			$this->que_precio_usa_solicitud($cod_solicitud);*/
		if (session::is_set('usa_precio_prod')) {			
			session::un_set('usa_precio_prod');
			$this->usa_precio_prod();
		}
			
	}
}
?>