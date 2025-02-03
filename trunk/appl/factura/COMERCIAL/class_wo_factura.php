<?php
////////////////////////////////////////
/////////// COMERCIAL_BIGGI ///////////////
////////////////////////////////////////
require_once(dirname(__FILE__)."/../../common_appl/class_header_vendedor.php");

class wo_factura extends wo_factura_base {
	const K_ESTADO_SII_EMITIDA	= 1;
	const K_ESTADO_CONFIRMADA	= 4;
	const K_ESTADO_CERRADA = 2;
	const K_PARAM_MAX_IT_FA = 29;
	//const K_AUTORIZA_EXPORTAR = '992010';
	const K_AUTORIZA_SOLO_BITACORA = '992025';
	const K_TIPO_VENTA = 1;
	var $autoriza_print;
	var $autoriza_xml;

	function wo_factura() {
		// Llama a w_base para que $this->cod_usuario contenga al usuario actual 
   		parent::wo_factura_base();

		$sql = "select F.COD_FACTURA
						,convert(varchar(20), F.FECHA_FACTURA, 103) FECHA_FACTURA
						,F.FECHA_FACTURA DATE_FACTURA
						,F.NRO_FACTURA
						,F.RUT
						,F.DIG_VERIF
						,F.NOM_EMPRESA
						,F.COD_DOC
						,EDS.NOM_ESTADO_DOC_SII
						,F.TOTAL_CON_IVA
						,U.INI_USUARIO
						,dbo.f_tipo_fa(EDS.NOM_ESTADO_DOC_SII) TIPO_FA
            			,dbo.f_get_nc_from_fa(f.COD_FACTURA) NC_FROM_FA                                              
						,F.COD_USUARIO_VENDEDOR1
						,EDS.COD_ESTADO_DOC_SII
				 from	FACTURA F, ESTADO_DOC_SII EDS, USUARIO U
				where	F.COD_ESTADO_DOC_SII = EDS.COD_ESTADO_DOC_SII AND
						F.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO AND
						F.COD_TIPO_FACTURA = ".self::K_TIPO_VENTA."
						and dbo.f_get_tiene_acceso (".$this->cod_usuario.", 'FACTURA',F.COD_USUARIO_VENDEDOR1, F.COD_USUARIO_VENDEDOR2) = 1
				order by	isnull(NRO_FACTURA, 9999999999) desc, COD_FACTURA desc";
				
	     parent::w_output_biggi('factura', $sql, $_REQUEST['cod_item_menu']);
			
		$this->dw->add_control(new edit_nro_doc('COD_FACTURA','FACTURA'));
		$this->dw->add_control(new static_num('RUT'));
		$this->dw->add_control(new edit_precio('TOTAL_CON_IVA'));

	   	$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_SOLO_BITACORA, $this->cod_usuario);	// acceso bitacora
		if ($priv=='E') {
			$this->b_add_visible = false;
      	}
      	else {
			$this->b_add_visible = true;
      	}
	   	
		// headers
		$this->add_header($control = new header_date('FECHA_FACTURA', 'FECHA_FACTURA', 'Fecha'));
		$control->field_bd_order = 'DATE_FACTURA';
		$this->add_header(new header_num('NRO_FACTURA', 'NRO_FACTURA', 'Nº FA'));
		$this->add_header(new header_rut('RUT', 'F', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Razón Social'));
		$sql_estado_doc_sii = "select COD_ESTADO_DOC_SII ,NOM_ESTADO_DOC_SII from ESTADO_DOC_SII order by	COD_ESTADO_DOC_SII";
		$this->add_header(new header_drop_down('NOM_ESTADO_DOC_SII', 'EDS.COD_ESTADO_DOC_SII', 'Estado', $sql_estado_doc_sii));
		$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'TOT C/IVA'));
		$this->add_header(new header_text('COD_DOC', 'COD_DOC', 'NV'));
		$this->add_header(new header_vendedor('INI_USUARIO', 'F.COD_USUARIO_VENDEDOR1', 'V1'));
   
		$this->add_header(new header_num('NC_FROM_FA', 'dbo.f_get_nc_from_fa(f.COD_FACTURA)', 'Nro NC'));
   
		$sql = "SELECT 'Sin tipo' ES_TIPO, 'Sin tipo' TIPO_FA 
				UNION 
				SELECT 'Papel' ES_TIPO , 'Papel' TIPO_FA
				UNION 
				SELECT 'Electrónica' ES_TIPO , 'Electrónica' TIPO_FA";
		$this->add_header(new header_drop_down_string('TIPO_FA', 'dbo.f_tipo_fa(EDS.NOM_ESTADO_DOC_SII)', 'Tipo FA', $sql));
		
		$priv = $this->get_privilegio_opcion_usuario('992075', $this->cod_usuario); //print
		if($priv=='E')
			$this->autoriza_print = true;
      	else
			$this->autoriza_print = false;
			
		$priv = $this->get_privilegio_opcion_usuario('992085', $this->cod_usuario); //xml
		if($priv=='E')
			$this->autoriza_xml = true;
      	else
			$this->autoriza_xml = false;	
  	}
	
	function redraw_item(&$temp, $ind, $record){
		$COD_FACTURA		= $this->dw->get_item($record, 'COD_FACTURA');
		$COD_ESTADO_DOC_SII = $this->dw->get_item($record, 'COD_ESTADO_DOC_SII');
	
		$temp->gotoNext("wo_registro");
		if ($ind % 2 == 0) {
			$temp->setVar("wo_registro.WO_TR_CSS", $this->css_claro);
			$temp->setVar("wo_registro.WO_DETALLE", '<input name="b_detalle_'.$ind.'" id="b_detalle_'.$ind.'" value="'.$ind.'" src="../../../../com	monlib/trunk/images/lupa1.jpg" type="image">');
		}
		else {
			$temp->setVar("wo_registro.WO_TR_CSS", $this->css_oscuro);
			$temp->setVar("wo_registro.WO_DETALLE", '<input name="b_detalle_'.$ind.'" id="b_detalle_'.$ind.'" value="'.$ind.'" src="../../../../commonlib/trunk/images/lupa2.jpg" type="image">');
		}
		
		if($COD_ESTADO_DOC_SII == 2 && $this->autoriza_print == true){
			$control =  '<input name="b_printDTE_'.$ind.'" id="b_printDTE_'.$ind.'" type="button" title="Imprimir"'.
			   			'style="cursor:pointer;height:26px;width:17px;border: 0;background-image:url(../../images_appl/b_dte_print.png);background-repeat:no-repeat;background-position:center;"'.
			   			'onClick="return dlg_print_dte_wo(this, '.$COD_FACTURA.');"/>';
			
			$temp->setVar("wo_registro.WO_PRINT_DTE", $control);
		}else if($COD_ESTADO_DOC_SII == 3 && $this->autoriza_print == true){
			$control =  '<input name="b_printDTE_'.$ind.'" id="b_printDTE_'.$ind.'" type="button" title="Imprimir"'.
			   			'style="cursor:pointer;height:26px;width:17px;border: 0;background-image:url(../../images_appl/b_dte_print.png);background-repeat:no-repeat;background-position:center;"'.
			   			'onClick="return dlg_print_dte_wo(this, '.$COD_FACTURA.');"/>';
			
			$temp->setVar("wo_registro.WO_PRINT_DTE", $control);
		}else
			$temp->setVar("wo_registro.WO_PRINT_DTE", '<img src="../../images_appl/b_dte_print_d.png">');
		
		
		if($COD_ESTADO_DOC_SII == 3 && $this->autoriza_xml == true && $COD_FACTURA > 37204)
			$temp->setVar("wo_registro.WO_XML_DTE", '<input name="b_xmlDTE_'.$ind.'" id="b_xmlDTE_'.$ind.'" value="'.$ind.'" title="Descargar XML" src="../../images_appl/b_dte_xml.png" type="image">');
		else
			$temp->setVar("wo_registro.WO_XML_DTE", '<img src="../../images_appl/b_dte_xml_d.png">');
		
		
		$this->dw->fill_record($temp, $record);
		
		//////////////////
		// llama al js para grabar scrol
		$temp->setVar("wo_registro.WO_DETALLE", '<input name="b_detalle_'.$ind.'" id="b_detalle_'.$ind.'" value="'.$ind.'" src="../../../../commonlib/trunk/images/lupa2.jpg" type="image" onClick="graba_scroll(\''.$this->nom_tabla.'\');">');
		
		if (session::is_set('W_OUTPUT_RECNO_'.$this->nom_tabla)) {	
			$rec_no = session::get('W_OUTPUT_RECNO_'.$this->nom_tabla);	
			if ($rec_no==$ind) {
				session::un_set('W_OUTPUT_RECNO_'.$this->nom_tabla);	
				$temp->setVar("wo_registro.WO_TR_CSS", 'linea_selected');
			}
		}
		//////////////////
	}
  	
	function procesa_event(){
		if ($this->clicked_boton('b_printDTE', $value_boton))
			$this->printdte($value_boton);
		else if ($this->clicked_boton('b_xmlDTE', $value_boton))
			$this->xmldte($value_boton);		
		else 
			parent::procesa_event();
	}
	
	function printdte($rec_no){
		$es_cedible = $_POST['wo_hidden2'];
  		$wi = new wi_factura('cod_item_menu');
		$wi->current_record = $rec_no;
		$wi->load_record();
		$wi->imprimir_dte($es_cedible, true);
		$this->goto_page($this->current_page);
  	}
  	
	function xmldte($rec_no){
  		$wi = new wi_factura('cod_item_menu');
		$wi->current_record = $rec_no;
		$wi->load_record();
		$wi->xml_dte();
  	}
}
?>