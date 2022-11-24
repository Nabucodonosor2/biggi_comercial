<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wi_factura_arriendo_aux extends wi_factura_arriendo  {
	var $cod_factura;
	
	function wi_factura_arriendo_aux() {
		parent::wi_factura_arriendo('2035');
	}
	function get_key() {
		return $this->cod_factura;
	}
	function _load_record() {
		return;
	}
}

class wo_factura_arriendo extends w_output_biggi {
	const K_ESTADO_SII_EMITIDA	= 1;
	const K_ESTADO_CONFIRMADA	= 4;
	const K_ESTADO_CERRADA = 2;
	const K_PARAM_MAX_IT_FA = 29;
	//const K_AUTORIZA_EXPORTAR = '992010';
	const K_AUTORIZA_SOLO_BITACORA = '992025';
	const K_TIPO_ARRIENDO = 2;
	const K_AUTORIZA_SUMAR = '992060';
	var $checkbox_sumar;

	function wo_factura_arriendo() {
		$this->checkbox_sumar = false;
		
		$sql = "select F.COD_FACTURA
						,convert(varchar(20), F.FECHA_FACTURA, 103) FECHA_FACTURA
						,F.FECHA_FACTURA DATE_FACTURA
						,F.NRO_FACTURA
						,F.RUT
						,F.DIG_VERIF
						,F.NOM_EMPRESA
						,F.COD_DOC
						,F.COD_USUARIO_VENDEDOR1
						,EDS.COD_ESTADO_DOC_SII
						,EDS.NOM_ESTADO_DOC_SII
						,F.TOTAL_CON_IVA
						,U.INI_USUARIO
						,dbo.f_fa_tipo_doc(F.COD_FACTURA) TIPO_FA
				 from	FACTURA F, ESTADO_DOC_SII EDS, USUARIO U
				where	F.COD_ESTADO_DOC_SII = EDS.COD_ESTADO_DOC_SII AND
						F.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO AND
						F.COD_TIPO_FACTURA = ".self::K_TIPO_ARRIENDO."
				order by	isnull(NRO_FACTURA, 9999999999) desc, COD_FACTURA desc";
				
	     parent::w_output_biggi('factura_arriendo', $sql, $_REQUEST['cod_item_menu']);
			
		$this->dw->add_control(new edit_nro_doc('COD_FACTURA','FACTURA'));
		$this->dw->add_control(new static_num('RUT'));
		$this->dw->add_control(new edit_precio('TOTAL_CON_IVA'));

		// headers
		$this->add_header($control = new header_date('FECHA_FACTURA', 'FECHA_FACTURA', 'Fecha'));
		$control->field_bd_order = 'DATE_FACTURA';
		$this->add_header(new header_num('NRO_FACTURA', 'NRO_FACTURA', 'Nº FA'));
		$this->add_header(new header_rut('RUT', 'F', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Razón Social'));
		$sql_estado_doc_sii = "select COD_ESTADO_DOC_SII ,NOM_ESTADO_DOC_SII from ESTADO_DOC_SII order by	COD_ESTADO_DOC_SII";
		$this->add_header(new header_drop_down('NOM_ESTADO_DOC_SII', 'EDS.COD_ESTADO_DOC_SII', 'Estado', $sql_estado_doc_sii));
		$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'Total c/IVA'));
		$this->add_header(new header_text('COD_DOC', 'COD_DOC', 'N° NV'));
		$sql = "select distinct U.COD_USUARIO, U.NOM_USUARIO from FACTURA F, USUARIO U where F.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO order by NOM_USUARIO";
		$this->add_header(new header_drop_down('INI_USUARIO', 'F.COD_USUARIO_VENDEDOR1', 'V1', $sql));
		
		$sql = "SELECT 'NORMAL' ES_TIPO, 'NORMAL' TIPO_FA UNION SELECT 'EXENTA' ES_TIPO , 'EXENTA' TIPO_FA";
		$this->add_header($control = new header_drop_down_string('TIPO_FA', '(dbo.f_fa_tipo_doc(COD_FACTURA))', 'Tipo FA', $sql)); 
  		$control->field_bd_order = 'TIPO_FA';
  		
  		// dw checkbox
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_SUMAR, $this->cod_usuario);
		if ($priv=='E')
			$DISPLAY_SUMAR = '';
      	else
			$DISPLAY_SUMAR = 'none';
			
		$sql = "select '$DISPLAY_SUMAR' DISPLAY_SUMAR
						,'N' CHECK_SUMAR
					   ,'N' HIZO_CLICK";
		$this->dw_check_box = new datawindow($sql);
		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_SUMAR','S','N'));
		$control->set_onClick("sumar(); document.getElementById('loader').style.display='';");
		$this->dw_check_box->add_control(new edit_text_hidden('HIZO_CLICK'));
		$this->dw_check_box->retrieve();
	}
	function redraw(&$temp) {
  		if ($this->b_add_visible)
			$this->habilita_boton($temp, 'create', $this->get_privilegio_opcion_usuario($this->cod_item_menu, $this->cod_usuario)=='E');
		
		$this->habilita_boton($temp, 'print_dte', $this->get_privilegio_opcion_usuario($this->cod_item_menu, $this->cod_usuario)=='E');
		$this->habilita_boton($temp, 'print_anexo_masivo', true);
		$this->dw_check_box->habilitar($temp, true);
	}	
	function habilita_boton(&$temp, $boton, $habilita) {
		if ($boton=='add') {
			if ($habilita)
				$temp->setVar("WO_ADD", '<input name="b_add" id="b_add" src="../../../../commonlib/trunk/images/b_add.jpg" type="image" '.
											'onMouseDown="MM_swapImage(\'b_add\',\'\',\'../../../../commonlib/trunk/images/b_add_click.jpg\',1)" '.
											'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
											'onMouseOver="MM_swapImage(\'b_add\',\'\',\'../../../../commonlib/trunk/images/b_add_over.jpg\',1)" '.
											'onClick="return dlg_factura_contrato();"'.
											'/>');
			else
				$temp->setVar("WO_".strtoupper($boton), '<img src="../../../../commonlib/trunk/images/b_add_d.jpg"/>');
				
		}else if ($boton=='create') {
			if ($habilita)
				$temp->setVar("WO_CREATE", '<input name="b_create" id="b_create" src="../../../../commonlib/trunk/images/b_create.jpg" type="image" '.
											'onMouseDown="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_click.jpg\',1)" '.
											'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
											'onMouseOver="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_over.jpg\',1)" '.
											'onClick="return selecciona_contrato();"'.
											'/>');
			else
				$temp->setVar("WO_".strtoupper($boton), '<img src="../../../../commonlib/trunk/images/b_create_d.jpg"/>');
				
		}else if ($boton=='print_dte') {
			if ($habilita)
				$temp->setVar("WO_PRINT_DTE", '<input name="b_print_dte" id="b_print_dte" src="../../images_appl/b_dte.jpg" type="image" '. 
												'onMouseDown="MM_swapImage(\'b_print_dte\',\'\',\'../../images_appl/b_dte_click.jpg\',1)" '.
												'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
												'onMouseOver="MM_swapImage(\'b_print_dte\',\'\',\'../../images_appl/b_dte_over.jpg\',1)" '.
												'style="display:{VISIBLE_DTE}"'.
												'onclick=" facturas_dte(); if (TabbedPanels1) this.value =TabbedPanels1.getCurrentTabIndex();
															select_printer_dte();												
															if (validate_save()) {
																document.getElementById(\'wi_hidden\').value = \'save_desde_dte\';
																document.getElementById(\'b_save\').click();
																return true;
															}else
												return false;" '.
												'/>');
			else
				$temp->setVar("WO_".strtoupper($boton), '<img src="../../images_appl/b_dte_d.jpg"/>');
				
		}else if ($boton=='print_anexo_masivo') {
			if($habilita)
				$temp->setVar("WO_PRINT_ANEXO_MASIVO", '<input name="b_print_anexo" id="b_print_anexo" src="../../images_appl/b_print_anexo.jpg" type="image" '.
											  		   'onMouseDown="MM_swapImage(\'b_print_anexo\',\'\',\'../../images_appl/b_print_anexo_click.jpg\',1)" '.
											  		   'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
											  		   'onMouseOver="MM_swapImage(\'b_print_anexo\',\'\',\'../../images_appl/b_print_anexo_over.jpg\',1)" '.
											  		   'onClick = "return dlg_print_anexo_masivo();"');
			else
				$temp->setVar("WO_PRINT_ANEXO_MASIVO", '<img src="../../images_appl/b_print_anexo_d.jpg"/>');
				
		}else
			parent::habilita_boton($temp, $boton, $habilita);
	}	
  	function crear_fa_from_arriendo($lista_contratos) {
		$a_lista_contratos = explode("|",$lista_contratos);
		if($a_lista_contratos[0] == "1fac_1cont"){
			if("$a_lista_contratos[1]"<> ''){
				$fecha_stock = $this->str2date("$a_lista_contratos[1]");
			}else{
				$fecha_stock = "NULL";
			}
			
			$a_lista_contratos = array_reverse($a_lista_contratos);
			for($i = 0 ; $i < count($a_lista_contratos)-2 ; $i++){
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				$db->BEGIN_TRANSACTION();
				$cod_usuario = $this->cod_usuario;	
				$sp = 'sp_fa_arriendo';
				$param = "'$a_lista_contratos[$i]'
							,'S'
							,$cod_usuario
							,$fecha_stock";
				if ($db->EXECUTE_SP($sp, $param)) { 
					$db->COMMIT_TRANSACTION();
				}
				else{ 
					$db->ROLLBACK_TRANSACTION();
					
					//////////// registro del error
					$param2 = str_replace("'", "''", $param);	// reemplaza ' por ''
					$db->query("EXECUTE spu_execute_sp 'INSERT', NULL, '$sp', '$param2', $cod_usuario");
					////////////
					
					$this->_redraw();
					$this->alert("No se pudo crear la factura. Error en 'sp_fa_arriendo', favor contacte a IntegraSystem.");
				}
			}
			$this->retrieve();
		}else{
			if("$a_lista_contratos[0]" <> ''){
				$fecha_stock = $this->str2date("$a_lista_contratos[0]");
			}else{
				$fecha_stock = "NULL";
			}
			
			$a_lista_contratos = array_reverse($a_lista_contratos);
			for($i = 0 ; $i < count($a_lista_contratos)-1 ; $i++){
				$total_lista_contrato = $total_lista_contrato.'|'.$a_lista_contratos[$i];
			}
			
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$db->BEGIN_TRANSACTION();
			$cod_usuario = $this->cod_usuario;
			$sp = 'sp_fa_arriendo';
			$param = "'$total_lista_contrato'
						,'S'
						,$cod_usuario
						,$fecha_stock";
			
			if ($db->EXECUTE_SP($sp, $param)) { 
				$db->COMMIT_TRANSACTION();
				$this->retrieve();
			}
			else { 
				$db->ROLLBACK_TRANSACTION();
					
				//////////// registro del error
				$param2 = str_replace("'", "''", $param);	// reemplaza ' por ''
				$db->query("EXECUTE spu_execute_sp 'INSERT', NULL, '$sp', '$param2', $cod_usuario");
				////////////
					
				$this->_redraw();
				$this->alert("No se pudo crear la factura. Error en 'sp_fa_arriendo', favor contacte a IntegraSystem.");
			}	
		}	
	}
	function masivo_dte($lista_empresa) {
		$lista_empresa = substr($lista_empresa, 0, strlen($lista_empresa) - 1);	// borra ultimo caracter
		$lista_empresa = explode('|', $lista_empresa);
		$w = new wi_factura_arriendo_aux();
		$count_cant = 0;
		$reg_exito = 0;
		$reg_fallido = 0;
		
		for ($i=0; $i < count($lista_empresa); $i++){
			$cod_empresa = $lista_empresa[$i];
			
			if($cod_empresa <> ''){
				$sql = "select COD_FACTURA 
							,(SELECT COUNT(COD_EMPRESA) 
								FROM FACTURA
								WHERE NRO_FACTURA is null
								and COD_EMPRESA = $cod_empresa ) CANT_FA
							,NRO_ORDEN_COMPRA
							,FECHA_ORDEN_COMPRA_CLIENTE	
						from FACTURA
						where NRO_FACTURA is null
						  AND COD_EMPRESA = $cod_empresa";	  
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				$result = $db->build_results($sql);

				if($cod_empresa == 99){ //CASINO EXPRESS S.A.
					if($result[0]['NRO_ORDEN_COMPRA'] == '' || $result[0]['FECHA_ORDEN_COMPRA_CLIENTE'] == ''){
						$reg_fallido++;
						continue;
					}	
				}
				
				$count_cant = $count_cant + $result[0]['CANT_FA'];
				
				for ($j=0; $j < count($result); $j++) {
					$cod_factura = $result[$j]['COD_FACTURA'];
					$w->cod_factura = $cod_factura;
					session::set('FA_ARRIENDO', '');
					$w->envia_FA_electronica(false);
					$reg_exito++;
				}
			}else{
				$count = 1;
			}
			
			
		}
		
		unset($w);
		$this->retrieve();
		if($reg_fallido == 0){
			if($count <> 1)
				$this->alert("Se han generado:".$count_cant. " facturas electronicas");
		}else{
			$this->alert('Existen facturas asociadas al RUT 78.793.360-2 que no registran "Numero OC Cliente" y/o "Fecha OC cliente".\nEstas facturas no fueron enviadas al SII. Favor reviselas y complete dichos campos.');
		}
		
	}
	
	function add_factura_arr($cod_arriendo){
		if($cod_arriendo > 100000000000){
			$this->_redraw();
			$this->alert('No se puede crear Factura por recuperación de arriendo, contacte a IntegraSystem');
			return;	
		}
	
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT COUNT(*) COUNT
				FROM ARRIENDO
				WHERE COD_ARRIENDO = $cod_arriendo";
		
		$result = $db->build_results($sql);
		if($result[0]['COUNT'] == 0){
			$this->_redraw();
			$this->alert('El número de arriendo ingresado no existe');
			return;								
		}

		$sql = "SELECT COD_FACTURA
					  ,TOTAL_CON_IVA
					  ,NRO_FACTURA
				FROM FACTURA
				WHERE COD_CONTRATO_ANTICIPO = $cod_arriendo";

		$cod_factura = $result[0]['COD_FACTURA'];
		$fa_tot_con_iva = $result[0]['TOTAL_CON_IVA'];
		$nro_factura = $result[0]['NRO_FACTURA'];
				
		$result = $db->build_results($sql);
		
		if(count($result) > 0){
		
			$sql = "SELECT COUNT(*) COUNT
						  ,TOTAL_CON_IVA
					FROM NOTA_CREDITO
					WHERE COD_DOC = $cod_factura
					GROUP BY TOTAL_CON_IVA";
			
			$result = $db->build_results($sql);
			$nc_tot_con_iva = $result[0]['TOTAL_CON_IVA'];
			
			if($result[0]['COUNT'] > 1){
				$this->_redraw();
				$this->alert('No se puede crear factura por recuperación de arriendo, contacte a IntegraSystem');
				return;
			}else if($result[0]['COUNT'] == 0){
				$this->_redraw();
				$this->alert('Ya existe una factura por recuperación de este arriendo. Factura N° '.$nro_factura);
				return;
			}else{
				if($nc_tot_con_iva <> $fa_tot_con_iva){
					$this->_redraw();
					$this->alert('No se puede crear factura por recuperación de arriendo, contacte a IntegraSystem');
					return;
				}
			}								
		}		
		
		$sp = "sp_fa_arriendo_contrato";
		
		$param = "$cod_arriendo
				,".$this->cod_usuario;
				
		if(!$db->EXECUTE_SP($sp, $param)){
			$this->_redraw();
			$this->alert('No se pudo crear la factura. Error en \'sp_fa_arriendo_contrato\', favor contacte a IntegraSystem.');
		}else{
			$this->retrieve();
			$this->alert('Se ha creado la factura satisfactóriamente.');
		}
	}
	
	function print_anexo_masivo($str_fechas){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$str_cod_factura = "";
		$arr_fechas = explode("|", $str_fechas);
		$fecha_desde = $this->str2date($arr_fechas[0]);
		$fecha_hasta = $this->str2date($arr_fechas[1], "23:59:59");
		
		$sql = "SELECT COD_FACTURA
				FROM FACTURA
				WHERE FECHA_FACTURA BETWEEN $fecha_desde AND $fecha_hasta
				AND COD_ESTADO_DOC_SII = 3 -- ENVIADA A SII
				AND TIPO_DOC = 'ARRIENDO'
				ORDER BY FECHA_FACTURA ASC";

		// reporte
		$labels = array();
		/*$labels['strNRO_FACTURA'] = $nro_factura;					
		$labels['strNOM_EMPRESA'] = $nom_empresa;
		$labels['strMES_FACTURA'] = $mes_factura;
		$labels['strANO_FACTURA'] = $ano_factura;*/		
		$xml = $this->find_file('factura_arriendo', 'factura_anexo_masivo.xml');					
		$rpt = new reporte_prin_anexo_masivo($sql, $xml, $labels, "Factura Anexo Masivo.pdf", 1);
		$this->retrieve();
	}
	
	function procesa_event(){
		if(isset($_POST['b_add_x']))
			$this->add_factura_arr($_POST['wo_hidden']);
		else if(isset($_POST['b_create_x']))
			$this->crear_fa_from_arriendo($_POST['wo_hidden']);
		else if(isset($_POST['b_print_dte_x']))
			$this->masivo_dte($_POST['wo_hidden']);
		else if(isset($_POST['b_print_anexo_x']))
			$this->print_anexo_masivo($_POST['wo_hidden']);	
		else if($_POST['HIZO_CLICK_0'] == 'S'){
			$this->checkbox_sumar = isset($_POST['CHECK_SUMAR_0']);
			
			// obtiene los datos del filtro aplicado
			$valor_filtro = $this->headers['TOTAL_CON_IVA']->valor_filtro;
			$valor_filtro2 = $this->headers['TOTAL_CON_IVA']->valor_filtro2;
			
			if($this->checkbox_sumar){
				$this->dw_check_box->set_item(0, 'CHECK_SUMAR', 'S');
				$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'Total c/IVA', 0, true, 'SUM'));
			}
			else{
				$this->dw_check_box->set_item(0, 'CHECK_SUMAR', 'N');
				$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'Total c/IVA'));  
			}

			// vuelve a setear el filtro aplicado
			$this->headers['TOTAL_CON_IVA']->valor_filtro = $valor_filtro;
			$this->headers['TOTAL_CON_IVA']->valor_filtro2 = $valor_filtro2;
			
			$this->save_SESSION();	
			$this->make_filtros();
			$this->retrieve();
		}else{
			$this->checkbox_sumar = 0;
			parent::procesa_event();
		}	
	}
}

class reporte_prin_anexo_masivo extends reporte {
	
	function reporte_prin_anexo_masivo($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);
	}
	function modifica_pdf(&$pdf) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result_fac = $db->build_results($this->sql);
		
		for($l=0 ; $l < count($result_fac) ; $l++){
			$sql = "SELECT F.COD_FACTURA
						,F.NRO_FACTURA
						,F.FECHA_FACTURA
						,F.NOM_EMPRESA
						,F.TOTAL_NETO
						,F.MONTO_IVA
						,F.TOTAL_CON_IVA
						,F.PORC_IVA
						,dbo.f_get_month_text(MONTH(FECHA_FACTURA)) MES_FACTURA
						,YEAR(FECHA_FACTURA) ANO_FACTURA
						,E.NOM_EMPRESA
					FROM FACTURA F, EMPRESA E
					WHERE F.COD_FACTURA = ".$result_fac[$l]['COD_FACTURA']."
					AND F.COD_EMPRESA = E.COD_EMPRESA";
			$result = $db->build_results($sql);

			for($i=0 ; $i < count($result) ; $i++){
				$this->header($pdf, $result);

				$sql_arr = "SELECT DISTINCT A.COD_ARRIENDO
							FROM ITEM_FACTURA I, ARRIENDO A
							WHERE I.COD_FACTURA = ".$result[$i]['COD_FACTURA']."
							AND A.COD_ARRIENDO = I.COD_ITEM_DOC";
				$result_arr = $db->build_results($sql_arr);
				
				for($k=0 ; $k < count($result_arr) ; $k++){
					$margen_y = $pdf->getY() + 30;
					
					$sql_item = "SELECT ROW_NUMBER() OVER(PARTITION BY A.COD_ARRIENDO ORDER BY I.ORDEN ASC) AS ITEM
									 ,I.COD_PRODUCTO
									 ,A.COD_ARRIENDO
									 ,A.NOM_ARRIENDO
									 ,I.NOM_PRODUCTO	
									 ,I.CANTIDAD
									 ,I.PRECIO
									 ,dbo.number_format(I.PRECIO, 0, ',', '.') PRECIO
									 ,ROUND(I.CANTIDAD * I.PRECIO, 0) TOTAL
								 FROM ITEM_FACTURA I, ARRIENDO A
								 WHERE I.COD_FACTURA = ".$result[$i]['COD_FACTURA']."
								 AND A.COD_ARRIENDO = ".$result_arr[$k]['COD_ARRIENDO']."
								 AND A.COD_ARRIENDO = I.COD_ITEM_DOC";
					
					$result_item = $db->build_results($sql_item);
					
					$pdf->SetTextColor(0, 0, 128);
					$pdf->SetFont('Arial', 'B', 10);
					
					if($pdf->getY() > 690){
						$pdf->AddPage();
						$this->header($pdf, $result);
						$margen_y = $pdf->getY() + 30;
					}
					
					$pdf->SetXY(29 ,$margen_y);
					$pdf->Cell(55, 12, "Contrato:    ".$result_item[0]['COD_ARRIENDO']." ".$result_item[0]['NOM_ARRIENDO']);
					
					$margen_y = $pdf->getY() + 18;
					
					if($pdf->getY() > 690){
						$pdf->AddPage();
						$this->header($pdf, $result);
						$margen_y = $pdf->getY() + 30;
					}
					
					$pdf->SetXY(29 ,$margen_y);
					$pdf->SetTextColor(0, 0, 128);
					$pdf->SetFont('Arial','',7);
					$pdf->Cell(55, 12, "Item", 1, 0, 'C');
					$pdf->SetXY(84 ,$margen_y);
					$pdf->Cell(55, 12, "Modelo", 1, 0, 'C');
					$pdf->SetXY(139 ,$margen_y);
					$pdf->Cell(278, 12, "Descripción", 1, 0, 'C');
					$pdf->SetXY(417 ,$margen_y);
					$pdf->Cell(55, 12, "Cantidad", 1, 0, 'C');
					$pdf->SetXY(472 ,$margen_y);
					$pdf->Cell(56, 12, "Precio", 1, 0, 'C');
					$pdf->SetXY(528 ,$margen_y);
					$pdf->Cell(55, 12, "Total", 1, 0, 'C');
					
					$pdf->SetFont('Arial','',8);
					$pdf->SetTextColor(0, 0, 0);
					
					$pos_y = 0;
					$total_neto = 0;
					
					$margen_y = $pdf->getY() + 12;
					
					for($j=0 ; $j < count($result_item) ; $j++){
						if(strlen($result_item[$j]['NOM_PRODUCTO']) >= 58)
							$marg_nom_prod = 14;
						else
							$marg_nom_prod = 0;
						
						$pdf->SetXY(29 ,$pos_y+$margen_y);
						$pdf->Cell(55, 14+$marg_nom_prod, $result_item[$j]['ITEM'], 1, 0, 'C');
						$pdf->SetXY(84 ,$pos_y+$margen_y);
						$pdf->Cell(55, 14+$marg_nom_prod, $result_item[$j]['COD_PRODUCTO'], 1);
						$pdf->SetXY(139 ,$pos_y+$margen_y);
						$pdf->MultiCell(278, 14, $result_item[$j]['NOM_PRODUCTO'], 1); //58 caract
						$pdf->SetXY(417 ,$pos_y+$margen_y);
						$pdf->Cell(55, 14+$marg_nom_prod, $result_item[$j]['CANTIDAD'], 1, 0, 'R');
						$pdf->SetXY(472 ,$pos_y+$margen_y);
						$pdf->Cell(56, 14+$marg_nom_prod, $result_item[$j]['PRECIO'], 1, 0, 'R');
						$pdf->SetXY(528 ,$pos_y+$margen_y);
						$pdf->Cell(55, 14+$marg_nom_prod, number_format($result_item[$j]['TOTAL'], 0, ',', '.'), 1, 0, 'R');
						
						$pos_y = $pos_y + 14 + $marg_nom_prod;
						$total_neto = $total_neto + $result_item[$j]['TOTAL'];
						
						if($pdf->getY() > 690){
							$pdf->AddPage();
							$pos_y = 0;
							$this->header($pdf, $result);
							$margen_y = $pdf->getY() + 30;
							
							$pdf->SetFont('Arial','',8);
							$pdf->SetTextColor(0, 0, 0);
						}
					}
					
					$pdf->SetFont('Arial','B',8);
					$pdf->SetXY(472 ,$pos_y+$margen_y);
					$pdf->Cell(56, 14, "Total Neto", 0, 0, 'R');
					$pdf->SetXY(528 ,$pos_y+$margen_y);
					$pdf->Cell(55, 14, number_format($total_neto, 0, ',', '.'), 1, 0, 'R');	
				}
			}
			
			$total_neto = number_format($result[0]['TOTAL_NETO'], 0, ',', '.');
			$porc_iva = number_format($result[0]['PORC_IVA'], 1, ',', '.');
			$monto_iva = number_format($result[0]['MONTO_IVA'], 0, ',', '.');
			$total_con_iva = number_format($result[0]['TOTAL_CON_IVA'], 0, ',', '.');
			
			if ($pdf->GetY() < 690){
			///TOTALES
				$y_ini = $pdf->GetY() + 20;
				
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
				$this->header($pdf, $result);
				
				$y_ini = $pdf->GetY() + 30;
				
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
			
			if(count($result_fac)-1 <> $l)
				$pdf->AddPage();
		}
	}
	
	function header($pdf, $result){
		$pdf->SetXY(192 ,107);
		$pdf->SetTextColor(0, 0, 128);
		$pdf->SetFont('Arial','B',17);
		$pdf->Cell(45, 0, "ANEXO FACTURA Nº ".$result[0]['NRO_FACTURA'], 0);
		$pdf->SetXY(253 ,125);
		$pdf->SetTextColor(0, 0, 128);
		$pdf->SetFont('Arial','B', 8);
		$pdf->Cell(45, 0, "Arriendo ".$result[0]['MES_FACTURA']." ".$result[0]['ANO_FACTURA'], 0);
		$pdf->SetXY(177 ,139);
		$pdf->SetTextColor(0, 0, 128);
		$pdf->SetFont('Arial','B', 10);
		$pdf->Cell(45, 0, "Cliente: ".$result[0]['NOM_EMPRESA'], 0);
	}
}
?>