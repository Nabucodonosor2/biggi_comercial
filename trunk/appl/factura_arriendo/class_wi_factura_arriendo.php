<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../factura/class_wi_factura.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_cot_nv.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");
require_once(dirname(__FILE__)."/../ws_client_biggi/class_client_biggi.php");

class dw_item_factura_static extends datawindow {
	const K_ESTADO_SII_EMITIDA 			= 1;
	function dw_item_factura_static(){
		$sql = " SELECT ifa.COD_ITEM_FACTURA	IT_COD_ITEM_FACTURA,
						ifa.COD_FACTURA	IT_COD_FACTURA,
						ifa.ORDEN	IT_ORDEN,
						ifa.ITEM 	IT_ITEM,
						ifa.COD_PRODUCTO	IT_COD_PRODUCTO,
						ifa.COD_PRODUCTO IT_COD_PRODUCTO_OLD,
						ifa.NOM_PRODUCTO	IT_NOM_PRODUCTO,
						ifa.CANTIDAD	IT_CANTIDAD,
						ifa.PRECIO	IT_PRECIO,
						ifa.COD_ITEM_DOC	IT_COD_ITEM_DOC,
						ifa.TIPO_DOC	IT_TIPO_DOC,
						case ifa.TIPO_DOC
							when 'ITEM_NOTA_VENTA' then dbo.f_nv_cant_por_facturar(ifa.COD_ITEM_DOC, default)
							when 'ITEM_GUIA_DESPACHO' then dbo.f_gd_cant_por_facturar(ifa.COD_ITEM_DOC, default)
						end IT_CANTIDAD_POR_FACTURAR,
						case
							when f.COD_DOC IS not NULL and f.COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA." then ''
							else 'none'
						end IT_TD_DISPLAY_CANT_POR_FACT,	
						case
							when f.COD_DOC IS NULL then ''
							else 'none'
						end IT_TD_DISPLAY_ELIMINAR,
						COD_TIPO_TE	IT_COD_TIPO_TE,
						MOTIVO_TE	IT_MOTIVO_TE,
						COD_TIPO_GAS IT_COD_TIPO_GAS,
						COD_TIPO_ELECTRICIDAD IT_COD_TIPO_ELECTRICIDAD
				FROM    ITEM_FACTURA ifa, factura f
				WHERE   f.cod_factura=ifa.cod_factura and ifa.COD_FACTURA = {KEY1}
				ORDER BY ORDEN";
		
		 
		parent::datawindow($sql, 'ITEM_FACTURA_STATIC', true, true);
		
		$this->set_computed('IT_TOTAL', '[IT_CANTIDAD] * [IT_PRECIO]');
		$this->add_control(new static_num('IT_PRECIO'));
	}
	
}

class dw_arriendo extends datawindow {
	function dw_arriendo() {
		$sql = "select A.COD_ARRIENDO
						,A.NOM_ARRIENDO
						,A.REFERENCIA
						,isnull(sum(I.CANTIDAD * I.PRECIO), 0)	TOTAL_ARRIENDO	
				from ITEM_FACTURA i, ARRIENDO a
				where i.COD_FACTURA = {KEY1}				
				  and TIPO_DOC = 'ARRIENDO'
				  and a.COD_ARRIENDO = i.COD_ITEM_DOC
				group by A.COD_ARRIENDO, A.NOM_ARRIENDO, A.REFERENCIA";
		parent::datawindow($sql, 'ARRIENDO');
		
		$this->add_control(new static_num('TOTAL_ARRIENDO'));
	}
}
class wi_factura_arriendo extends wi_factura {
	function wi_factura_arriendo($cod_item_menu) {
		parent::wi_factura($cod_item_menu);
		$this->nom_tabla = 'factura_arriendo';
		$this->nom_template = "wi_".$this->nom_tabla.".htm";

		// no se usa los item_fa, se cambia un el select para evitar que haga un load de muchos registros
		$sql = " SELECT null COD_ITEM_FACTURA,
						null COD_FACTURA,
						null ORDEN,
						null ITEM,
						null COD_PRODUCTO,
						null COD_PRODUCTO_OLD,
						null NOM_PRODUCTO,
						null CANTIDAD,
						null PRECIO,
						null COD_ITEM_DOC,
						null CANTIDAD_POR_FACTURAR,
						null TD_DISPLAY_CANT_POR_FACT,	
						null TD_DISPLAY_ELIMINAR,
						null COD_TIPO_TE,
						null MOTIVO_TE,
						null BOTON_PRECIO
					where 1=2";
		$this->dws['dw_item_factura']->set_sql($sql);
		////////
		
		$this->dws['dw_arriendo'] = new dw_arriendo();
		$this->dws['dw_item_factura_static'] = new dw_item_factura_static();
		$this->dws['dw_ingreso_pago_fa']->add_control(new static_link('COD_INGRESO_PAGO', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=factura_arriendo&modulo_destino=ingreso_pago&cod_modulo_destino=[COD_INGRESO_PAGO]&cod_item_menu=2505'));
	}
	function load_wo() {
		if ($this->tiene_wo)
			$this->wo = session::get("wo_factura_arriendo");
	}
	function habilitar(&$temp, $habilita) {
		$nro_factura 		= $this->dws['dw_factura']->get_item(0, 'NRO_FACTURA');
		$cod_empresa 		= $this->dws['dw_factura']->get_item(0, 'COD_EMPRESA');
		$cod_estado_doc_sii = $this->dws['dw_factura']->get_item(0, 'COD_ESTADO_DOC_SII');
		$priv				= $this->get_privilegio_opcion_usuario('997505', $this->cod_usuario);
		
		if ($nro_factura!='') {
			$control = '<input name="b_print_anexo" id="b_print_anexo" src="../../images_appl/b_print_anexo.jpg" type="image" '.
								 'onMouseDown="MM_swapImage(\'b_print_anexo\',\'\',\'../../images_appl/b_print_anexo_click.jpg\',1)" '.
								 'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
								 'onMouseOver="MM_swapImage(\'b_print_anexo\',\'\',\'../../images_appl/b_print_anexo_over.jpg\',1)" '.
								 'onClick = "dlg_print_anexo();"';
			$temp->setVar("WI_PRINT_ANEXO", $control);
		}
		else
			$temp->setVar("WI_PRINT_ANEXO", '<img src="../../images_appl/b_print_anexo_d.jpg"/>');

		if($cod_empresa == 4 && $cod_estado_doc_sii == 3 && $priv == 'E')	
			$this->habilita_boton($temp, 'factura_tdnx', true);
		else
			$this->habilita_boton($temp, 'factura_tdnx', false);
	}
	function make_sql_auditoria() {
		// cambia de factura_arriendo a factura y luego lo devuelve
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'factura';
		$sql = parent::make_sql_auditoria();
		$this->nom_tabla = $nom_tabla;
		return $sql;
	}
	function make_sql_auditoria_relacionada($tabla) {
		// cambia de factura_arriendo a factura y luego lo devuelve
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'factura';
		$sql = parent::make_sql_auditoria_relacionada($tabla);
		$this->nom_tabla = $nom_tabla;
		return $sql;
	}
	function delete_record($db) {
		return $db->EXECUTE_SP("spu_factura", "'DELETE', ".$this->get_key());
	}
	
	function habilita_boton(&$temp, $boton, $habilita) {
		if ($boton=='factura_tdnx') {
			if ($habilita)
				$temp->setVar("WI_".strtoupper($boton), '<input name="b_'.$boton.'" id="b_'.$boton.'" src="../../images_appl/b_'.$boton.'.jpg" type="image" '.
											'onMouseDown="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../images_appl/b_'.$boton.'_click.jpg\',1)" '.
											'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
											'onMouseOver="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../images_appl/b_'.$boton.'_over.jpg\',1)" '.
											'onClick="return dlg_seleccion_factura();"'.
											'/>');
										
			else
				$temp->setVar("WI_".strtoupper($boton), '<img src="../../images_appl/b_'.$boton.'_d.jpg"/>');
		}
		else
			parent::habilita_boton($temp, $boton, $habilita);
	}
	
	function load_record() {
		parent::load_record();
		$cod_factura = $this->get_key();
		$this->dws['dw_arriendo']->retrieve($cod_factura);
		$this->dws['dw_item_factura_static']->retrieve($cod_factura);

		// nada modificable
		if($this->dws['dw_factura']->get_item(0, 'COD_EMPRESA') == 99){	//CASINO EXPRESS S.A.
			$this->dws['dw_factura']->set_entrable('NRO_ORDEN_COMPRA'      	 	, true);
			$this->dws['dw_factura']->set_entrable('FECHA_ORDEN_COMPRA_CLIENTE' , true);
		}else{
			$this->dws['dw_factura']->set_entrable('NRO_ORDEN_COMPRA'      	 	, false);
			$this->dws['dw_factura']->set_entrable('FECHA_ORDEN_COMPRA_CLIENTE' , false);
		}
		
		$this->dws['dw_factura']->set_entrable('FECHA_FACTURA'			 , false);
		$this->dws['dw_factura']->set_entrable('REFERENCIA'				 , false);
		$this->dws['dw_factura']->set_entrable('RETIRADO_POR'			 , false);
		$this->dws['dw_factura']->set_entrable('GUIA_TRANSPORTE'		 , false);
		$this->dws['dw_factura']->set_entrable('PATENTE'				 , false);
		$this->dws['dw_factura']->set_entrable('OBS'					 , false);
		$this->dws['dw_factura']->set_entrable('RUT_RETIRADO_POR'		 , false);
		$this->dws['dw_factura']->set_entrable('DIG_VERIF_RETIRADO_POR'	 , false);
		
		$this->dws['dw_factura']->set_entrable('NOM_EMPRESA'			, false);
		$this->dws['dw_factura']->set_entrable('ALIAS'					, false);
		$this->dws['dw_factura']->set_entrable('COD_EMPRESA'			, false);
		$this->dws['dw_factura']->set_entrable('RUT'					, false);
		$this->dws['dw_factura']->set_entrable('COD_SUCURSAL_FACTURA'	, false);
		$this->dws['dw_factura']->set_entrable('COD_PERSONA'			, false);

		$this->dws['dw_factura']->set_entrable('COD_USUARIO_VENDEDOR2'	, false);
		$this->dws['dw_factura']->set_entrable('COD_ORIGEN_VENTA'		, false);
		$this->dws['dw_factura']->set_entrable('CANCELADA'				, false);
		$this->dws['dw_factura']->set_entrable('GENERA_SALIDA'			, false);
		$this->dws['dw_factura']->set_entrable('PORC_DSCTO1'			, false);
		$this->dws['dw_factura']->set_entrable('MONTO_DSCTO1'			, false);
		$this->dws['dw_factura']->set_entrable('PORC_DSCTO2'			, false);
		$this->dws['dw_factura']->set_entrable('MONTO_DSCTO2'			, false);
		$this->dws['dw_factura']->set_entrable('PORC_IVA'				, false);
		
		$COD_CONTRATO_ANTICIPO = $this->dws['dw_factura']->get_item(0, 'COD_CONTRATO_ANTICIPO');
		
		if($COD_CONTRATO_ANTICIPO == ''){
			$this->dws['dw_factura']->set_item(0, 'TABLE_ITEM_FACTURA', 'none');
			$this->dws['dw_factura']->set_item(0, 'TABLE_ITEM_CONTRATO', '');
		}else{
			$this->dws['dw_factura']->set_item(0, 'TABLE_ITEM_FACTURA', '');
			$this->dws['dw_factura']->set_item(0, 'TABLE_ITEM_CONTRATO', 'none');
		}
	}
	
	function factura_ws_tdnx($array_factura){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$array_factura = explode("|", $array_factura);
		
		$cod_factura = $array_factura[0];
		$factura_from = $array_factura[1];
		
		$sql = "SELECT COD_FACTURA
					  ,REFERENCIA
					  ,OBS
					  ,RETIRADO_POR
					  ,RUT_RETIRADO_POR
					  ,DIG_VERIF_RETIRADO_POR
					  ,GUIA_TRANSPORTE
					  ,PATENTE
					  ,PORC_DSCTO1
					  ,PORC_DSCTO2
					  ,PORC_IVA
					  ,SUBTOTAL
				FROM FACTURA
				WHERE COD_FACTURA = $cod_factura";
		$result_factura = $db->build_results($sql);
		
		$sql = "select SISTEMA
					  ,URL_WS
					  ,USER_WS
					  ,PASSWROD_WS
				from PARAMETRO_WS
				where SISTEMA = 'TODOINOX'";
		
		$result = $db->build_results($sql);
		$user_ws		= $result[0]['USER_WS'];
		$passwrod_ws	= $result[0]['PASSWROD_WS'];
		$url_ws			= $result[0]['URL_WS'];
		
		$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
		$result = $biggi->cli_factura_arriendo($result_factura, $factura_from);
		
		$this->_load_record();
		if($result == 'HECHO')
			$this->alert('Se ha duplicado la factura en Sistema Web Todoinox exitosamente.\nFavor revise la factura en estado emitida en Sistema Web Todoinox');
		else{
			if($result <> 'EXISTENTE_SIN_NRO_FA')
				$this->alert('Esta factura ya fue duplicada en Sistema web Todoinox (FA N° '.$result.' en Todoinox), no puede volver a duplicarla.');
			else
				$this->alert('Esta factura ya fue duplicada en Sistema web Todoinox (FA está en estado emitida en Todoinox), debe imprimir, o eliminar, la factura en Sistema Web Todoinox');
		}
	}
	
	function print_anexo() {
		$tipo_print = $_POST['wi_hidden'];
		$cod_factura = $this->get_key();
		$nro_factura = $this->dws['dw_factura']->get_item(0, 'NRO_FACTURA');
		$fecha_factura = $this->dws['dw_factura']->get_item(0, 'FECHA_FACTURA');
		$nom_empresa = $this->dws['dw_factura']->get_item(0, 'NOM_EMPRESA');
		$direccion_empresa = $this->dws['dw_factura']->get_item(0, 'DIRECCION_FACTURA');
		$referencia = $this->dws['dw_factura']->get_item(0, 'REFERENCIA');
		
		$aFecha = explode("/", $fecha_factura);
		$mes_factura = $this->nom_mes($aFecha[1]);		
		$ano_factura = $aFecha[2];
		
		if ($tipo_print=='PDF') {
			$nom_empresa = $this->dws['dw_factura']->get_item(0, 'NOM_EMPRESA');
			$sql = "select F.NOM_EMPRESA
							,A.COD_ARRIENDO
							,A.NOM_ARRIENDO
							,A.REFERENCIA
							,I.COD_PRODUCTO
							,I.NOM_PRODUCTO
							,ROW_NUMBER() OVER(PARTITION BY A.COD_ARRIENDO ORDER BY I.ORDEN ASC) AS ITEM
							,case A.NOM_ARRIENDO when ''
								then A.REFERENCIA
									else A.NOM_ARRIENDO
								end NOM_PRODUCTO_REF	
							,I.CANTIDAD
							,I.PRECIO
							,Round(I.CANTIDAD * I.PRECIO, 0) TOTAL
							,A.CENTRO_COSTO_CLIENTE 
							,F.TOTAL_NETO
							,F.MONTO_IVA
							,F.TOTAL_CON_IVA
							,F.PORC_IVA
					from ITEM_FACTURA I, FACTURA F, ARRIENDO A
					where I.COD_FACTURA =  $cod_factura
					  and F.COD_FACTURA = I.COD_FACTURA
					  and A.COD_ARRIENDO = I.COD_ITEM_DOC
					order by A.COD_ARRIENDO, I.ORDEN";
			// reporte
			$labels = array();
			$labels['strNRO_FACTURA'] = $nro_factura;					
			$labels['strNOM_EMPRESA'] = $nom_empresa;
			$labels['strMES_FACTURA'] = $mes_factura;
			$labels['strANO_FACTURA'] = $ano_factura;					
			$xml = $this->find_file('factura_arriendo', 'factura_anexo.xml');					
			$rpt = new reporte_prin_anexo($sql, $xml, $labels, "Factura_anexo ".$cod_factura.".pdf", 1);
		}
		else if ($tipo_print=='XLS') {
			
			error_reporting(E_ALL & ~E_NOTICE);
			require_once dirname(__FILE__)."/../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php";
			require_once dirname(__FILE__)."/../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php";		
			
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);				 	
			$sql = "select F.NRO_FACTURA
							,convert(varchar, F.FECHA_FACTURA, 103) FECHA_FACTURA
							,F.NOM_EMPRESA
							,F.RUT
							,F.DIG_VERIF
							,F.DIRECCION
							,COM.NOM_COMUNA
							,CIU.NOM_CIUDAD
							,PAI.NOM_PAIS
							,F.TELEFONO
							,F.FAX
							,F.REFERENCIA
							,A.COD_ARRIENDO
							,I.COD_PRODUCTO
							,I.NOM_PRODUCTO
							,I.CANTIDAD
							,I.PRECIO							
							,Round(I.CANTIDAD * I.PRECIO, 0) TOTAL
							,A.CENTRO_COSTO_CLIENTE
							,A.NOM_ARRIENDO
					from ITEM_FACTURA I, FACTURA F left outer join COMUNA COM on F.COD_COMUNA = COM.COD_COMUNA, CIUDAD CIU, PAIS PAI, ARRIENDO A
					where I.COD_FACTURA = $cod_factura
					  and F.COD_FACTURA = I.COD_FACTURA
					  and CIU.COD_CIUDAD = F.COD_CIUDAD
					  and PAI.COD_PAIS = F.COD_PAIS
					  and A.COD_ARRIENDO = I.COD_ITEM_DOC
					order by A.COD_ARRIENDO, I.ORDEN";

			$res = $db->query($sql);


			$fname = tempnam("/tmp", "export.xls");
			$workbook = &new writeexcel_workbook($fname);		
			$worksheet = $workbook->addworksheet('FACTURA_'.$nro_factura);
			
			//AYUDA FUNCIONES
			//set_row($row, $height, $XF) OJO $row parte en cero	
			//set_column($firstcol, $lastcol, $width, $format, $hidden)
						
			//SETEA TAMAÑOS DE COLUMNAS
			$worksheet->set_row(0, 60);
			$worksheet->set_column(0, 4, 15);
			$worksheet->set_column(5, 5, 15);
			$worksheet->set_column(6, 6, 65);
			$worksheet->set_column(7, 7, 14);
			$worksheet->set_column(8, 8, 15);
			$worksheet->set_column(9, 9, 15);
			$worksheet->set_column(10, 10, 10);
			$worksheet->set_column(11, 11, 35);
			
			
			//INICIO FORMATOS DE CELDA
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
			
			$titulo_item_border_all_right =& $workbook->addformat();
			$titulo_item_border_all_right->copy($titulo_item_border_all);
			$titulo_item_border_all_right->set_align('right');
			
			$titulo_item_border_all_merge =& $workbook->addformat();
			$titulo_item_border_all_merge->copy($titulo_item_border_all);
			$titulo_item_border_all_merge->set_merge();
					
		
			$border_item_left = & $workbook->addformat();
			$border_item_left->copy($text_normal_left);
			$border_item_left->set_border_color('black');
			$border_item_left->set_left(2);
			
			$border_item_left_nom_producto = & $workbook->addformat();
			$border_item_left_nom_producto->copy($border_item_left);
			$border_item_left_nom_producto->set_right(2);
			
			
			
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

			$total_por_contrato =& $workbook->addformat();
			$total_por_contrato->set_num_format('#,##0');
			$total_por_contrato->set_top(2);
			
			
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

			$border_item_vacio_bottom_right = & $workbook->addformat();
			$border_item_vacio_bottom_right->copy($text);
			$border_item_vacio_bottom_right->set_bottom(2);
			//$border_item_vacio_bottom_right->set_right(2);
			//FIN FORMATOS DE CELDA
			
			//LOGO
			$worksheet->insert_bitmap('A1',$this->root_dir."images_appl/logo_reporte_excel.bmp");
			
			//LINEA ROJA
			$i = 1;
			$worksheet->set_row(1, 5);
			$text_linea_roja =& $workbook->addformat();
			$text_linea_roja->set_bg_color("red");
			$worksheet->write($i, 0,'', $text_linea_roja);	
			$worksheet->write($i, 1,'', $text_linea_roja);		
			$worksheet->write($i, 2,'', $text_linea_roja);	
			$worksheet->write($i, 3,'', $text_linea_roja);	
			$worksheet->write($i, 4,'', $text_linea_roja);	
			$worksheet->write($i, 5,'', $text_linea_roja);	
			$worksheet->write($i, 6,'', $text_linea_roja);	
			$worksheet->write($i, 7,'', $text_linea_roja);
			$worksheet->write($i, 8,'', $text_linea_roja);
			$worksheet->write($i, 9,'', $text_linea_roja);			
			$worksheet->write($i, 10,'', $text_linea_roja);	
			$worksheet->write($i, 11,'', $text_linea_roja);	
				
			
			//ESCRIBE ENCABEZADOS		
			$header =& $workbook->addformat();
			$header->set_bold();
			$header->set_color('blue');	
			
			
			$text =& $workbook->addformat();
			$text->set_font("Verdana");
			$text->set_valign('vcenter');
			$text->set_bold();
			$text->set_color('blue');
			
			$text_subrayado =& $workbook->addformat();
			$text_subrayado->copy($text);
			$text_subrayado->set_underline();
		
					
			//CABECERA
			$i = 3;
			$worksheet->write($i, 0, 'Nº FACTURA: '.$nro_factura, $text);
			$i++;
			$worksheet->write($i, 0, 'FECHA : '.$fecha_factura, $text);
			$i++;
			$worksheet->write($i, 0, 'RAZON SOCIAL : '.$nom_empresa, $text);
			$i++;
			$worksheet->write($i, 0, '                              '.$direccion_empresa, $text_subrayado);
			$i++;
			$worksheet->write($i, 0, 'REFERENCIA : '.$referencia, $text);
			$i++;
			
			//TITULOS DE COLUMNA
			$i++;
			$worksheet->write($i, 0, 'Sociedad', $titulo_item_border_all);
			$worksheet->write($i, 1, 'Rut Proveedor', $titulo_item_border_all);
			$worksheet->write($i, 2, 'Nº Factura', $titulo_item_border_all_right);
			$worksheet->write($i, 3, 'Fecha Factura', $titulo_item_border_all);
			$worksheet->write($i, 4, 'Nº Contrato', $titulo_item_border_all);
			$worksheet->write($i, 5, 'Modelo', $titulo_item_border_all);		
			$worksheet->write($i, 6, 'Descripción Equipo o Servicio', $titulo_item_border_all);		
			$worksheet->write($i, 7, 'Cantidad', $titulo_item_border_all_right);		
			$worksheet->write($i, 8, 'Precio $', $titulo_item_border_all_right);
			$worksheet->write($i, 9, 'Total $', $titulo_item_border_all_right);			
			$worksheet->write($i, 10, 'CeCo', $titulo_item_border_all);		
			$worksheet->write($i, 11, 'Nombre CeCo', $titulo_item_border_all);		
			
			//ITEMS
			$i++;
			
			$a=0;
			$tot_arriendo = 0;
			while($my_row = $db->get_row()){
				
				if ($a < 1){
				$cod_arriendo_ant = $my_row['COD_ARRIENDO'];
				}
				if ($worksheet->_datasize > 7000000)
					break;
				
				if ($cod_arriendo_ant != $my_row['COD_ARRIENDO']) {
					
					//// CIERRA LOS BORDES INFERIORES DEL GRUPO
					$worksheet->write($i, 0, '', $border_item_top);
					$worksheet->write($i, 1, '', $border_item_top);
					$worksheet->write($i, 2, '', $border_item_top);
					$worksheet->write($i, 3, '', $border_item_top);
					$worksheet->write($i, 4, '', $border_item_top);
					$worksheet->write($i, 5, '', $border_item_top);						
					$worksheet->write($i, 6, '', $border_item_top);						
					$worksheet->write($i, 7, '', $border_item_top);						
					$worksheet->write($i, 8, 'Total Contrato', $border_item_top);

					$worksheet->write($i, 9, $tot_arriendo, $total_por_contrato);					
					
					$worksheet->write($i, 10, '', $border_item_top);						
					$worksheet->write($i, 11, '', $border_item_top);
					
					// ABRE LOS BORDES DEL GRUPO SIGUIENTE
					$i += 2;
					$worksheet->write($i, 0, '', $border_item_vacio_bottom_right);
					$worksheet->write($i, 1, '', $border_item_vacio_bottom_right);
					$worksheet->write($i, 2, '', $border_item_vacio_bottom_right);
					$worksheet->write($i, 3, '', $border_item_vacio_bottom_right);
					$worksheet->write($i, 4, '', $border_item_vacio_bottom_right);
					$worksheet->write($i, 5, '', $border_item_vacio_bottom_right);						
					$worksheet->write($i, 6, '', $border_item_vacio_bottom_right);						
					$worksheet->write($i, 7, '', $border_item_vacio_bottom_right);						
					$worksheet->write($i, 8, '', $border_item_vacio_bottom_right);

					$worksheet->write($i, 9, '', $border_item_vacio_bottom_right);					
					
					$worksheet->write($i, 10, '', $border_item_vacio_bottom_right);						
					$worksheet->write($i, 11, '', $border_item_vacio_bottom_right);
					
					//////
					$tot_arriendo = 0;
					$i += 1;
				}
				$worksheet->write($i, 0, $my_row['RUT'].'-'.$my_row['DIG_VERIF'], $border_item_right);
				$worksheet->write($i, 1, '914620015', $border_item_right);
				$worksheet->write($i, 2, $my_row['NRO_FACTURA'], $border_item_right);
				$worksheet->write($i, 3, $my_row['FECHA_FACTURA'], $border_item_right);
				$worksheet->write($i, 4, $my_row['COD_ARRIENDO'], $border_item_right);
				$worksheet->write($i, 5, $my_row['COD_PRODUCTO'], $border_item_right);						
				$worksheet->write($i, 6, $my_row['NOM_PRODUCTO'], $border_item_left_nom_producto);						
				$worksheet->write($i, 7, $my_row['CANTIDAD'], $cant_normal);						
				$worksheet->write($i, 8, $my_row['PRECIO'], $monto_normal);	
				
				$worksheet->write($i, 9, $my_row['TOTAL'], $monto_normal);					
				
				$worksheet->write($i, 10, $my_row['CENTRO_COSTO_CLIENTE'], $border_item_right);						
				$worksheet->write($i, 11, $my_row['NOM_ARRIENDO'], $border_item_left_nom_producto);	
				
				$tot_arriendo = $tot_arriendo + $my_row['TOTAL'];	
				$tot_arriendo_final	= $tot_arriendo_final+ $my_row['TOTAL'];	
				
				$i++;
				
				$cod_arriendo_ant = $my_row['COD_ARRIENDO'];
				$a++;
			}

			if ($worksheet->_datasize > 7000000) {
				$worksheet->write($i, 0, 'No se completo la exportación de datos porque excede el máximo del tamaño de archivo 7 MB', $header);
			}else{
					//// CIERRA LA ULTIMA COLUMNA DEL EXCEL
					$worksheet->write($i, 0, '', $border_item_top);
					$worksheet->write($i, 1, '', $border_item_top);
					$worksheet->write($i, 2, '', $border_item_top);
					$worksheet->write($i, 3, '', $border_item_top);
					$worksheet->write($i, 4, '', $border_item_top);
					$worksheet->write($i, 5, '', $border_item_top);						
					$worksheet->write($i, 6, '', $border_item_top);	
					$worksheet->write($i, 7, '', $border_item_top);					
					$worksheet->write($i, 8, 'Total Contrato', $border_item_top);						
					$worksheet->write($i, 9, $tot_arriendo, $total_por_contrato);	
					//$worksheet->write($i, 9, $tot_arriendo, $total_por_contrato);					
					$worksheet->write($i, 10, '', $border_item_top);						
					$worksheet->write($i, 11,'', $border_item_top);
			}
			
			//// CIERRA LA ULTIMA COLUMNA DEL EXCEL
					$worksheet->write($i+2, 0, '', $border_item_top);
					$worksheet->write($i+2, 1, '', $border_item_top);
					$worksheet->write($i+2, 2, '', $border_item_top);
					$worksheet->write($i+2, 3, '', $border_item_top);
					$worksheet->write($i+2, 4, '', $border_item_top);
					$worksheet->write($i+2, 5, '', $border_item_top);						
					$worksheet->write($i+2, 6, '', $border_item_top);	
					$worksheet->write($i+2, 7, '', $border_item_top);					
					$worksheet->write($i+2, 8, 'Total Contrato', $border_item_top);						
					$worksheet->write($i+2, 9, $tot_arriendo_final, $total_por_contrato);	
					//$worksheet->write($i, 9, $tot_arriendo, $total_por_contrato);					
					$worksheet->write($i+2, 10, '', $border_item_top);						
					$worksheet->write($i+2, 11,'', $border_item_top);
				
			$workbook->close();
	
			header("Content-Type: application/x-msexcel; name=\"FACTURA_".$nro_factura."\"");
			header("Content-Disposition: inline; filename=\"FACTURA_".$nro_factura.".xls\"");
			$fh=fopen($fname, "rb");
			fpassthru($fh);
			unlink($fname);	
		}
		$this->_load_record();
	}

	function procesa_event() {
		if(isset($_POST['b_factura_tdnx_x']))
			$this->factura_ws_tdnx($_POST['wi_hidden']);
		else if(isset($_POST['b_print_anexo_x']))
			$this->print_anexo();
		else
			parent::procesa_event();
	}
}

class reporte_prin_anexo extends reporte {
	
	function reporte_prin_anexo ($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);
	}
	function modifica_pdf(&$pdf) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		
		$total_neto = number_format($result[0]['TOTAL_NETO'], 0, ',', '.');
		$porc_iva = number_format($result[0]['PORC_IVA'], 1, ',', '.');
		$monto_iva = number_format($result[0]['MONTO_IVA'], 0, ',', '.');
		$total_con_iva = number_format($result[0]['TOTAL_CON_IVA'], 0, ',', '.');
		
		/// NON RETORNA LA ULTIMAPOSION DE Y .
		
		if ($pdf->GetY() < 630){
		///TOTALES
			$y_ini = $pdf->GetY();
			
			$pdf->SetXY(450 ,$y_ini + 20);
			$pdf->SetTextColor(4, 22, 114);
			$pdf->SetFont('Arial','',7);
			$pdf->MultiCell(80,4, 'TOTAL NETO $ ',0, 'R');
			$pdf->SetXY(490, $y_ini + 20);
			$pdf->SetFont('Arial','B',8);
			$pdf->MultiCell(93,4,$total_neto,0, 'R');
			$pdf->SetXY(450, $y_ini + 35);
			$pdf->SetFont('Arial','',7);
			$pdf->MultiCell(80,4,$porc_iva.' % IVA  $ ',0, 'R');
			$pdf->SetXY(490, $y_ini + 35);
			$pdf->SetFont('Arial','B',8);
			$pdf->MultiCell(93,4,$monto_iva,0, 'R');
			$pdf->Rect(472, $y_ini + 50, 110, 2, 'f');
			$pdf->SetXY(450, $y_ini + 65);
			$pdf->SetFont('Arial','',7);
			$pdf->MultiCell(80,4,'TOTAL CON IVA $ ',0, 'R');
			$pdf->SetXY(490, $y_ini + 65);
			$pdf->SetFont('Arial','B',8);
			$pdf->MultiCell(93,4,$total_con_iva,0, 'R');	
		}else{
			$pdf->AddPage();	
			
			$y_ini = $pdf->GetY();
			
			$pdf->SetXY(450 ,$y_ini + 20);
			$pdf->SetTextColor(4, 22, 114);
			$pdf->SetFont('Arial','',7);
			$pdf->MultiCell(80,4, 'TOTAL NETO $ ',0, 'R');
			$pdf->SetXY(490, $y_ini + 20);
			$pdf->SetFont('Arial','B',8);
			$pdf->MultiCell(93,4,$total_neto,0, 'R');
			$pdf->SetXY(450, $y_ini + 35);
			$pdf->SetFont('Arial','',7);
			$pdf->MultiCell(80,4,$porc_iva.' % IVA  $ ',0, 'R');
			$pdf->SetXY(490, $y_ini + 35);
			$pdf->SetFont('Arial','B',8);
			$pdf->MultiCell(93,4,$monto_iva,0, 'R');
			$pdf->Rect(472, $y_ini + 50, 110, 2, 'f');
			$pdf->SetXY(450, $y_ini + 65);
			$pdf->SetFont('Arial','',7);
			$pdf->MultiCell(80,4,'TOTAL CON IVA $ ',0, 'R');
			$pdf->SetXY(490, $y_ini + 65);
			$pdf->SetFont('Arial','B',8);
			$pdf->MultiCell(93,4,$total_con_iva,0, 'R');	
		}
	}
}	
?>