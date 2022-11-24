<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_guia_despacho extends w_output_biggi {
	const K_ESTADO_SII_EMITIDA 	= 1;
	const K_ESTADO_CONFIRMADA	= 4;
	const K_ESTADO_CERRADA		= 2;
	const K_TIPO_ARRIENDO		= 5;
	const K_PARAM_MAX_IT_GD = 29;
	var $autoriza_print;
	var $autoriza_xml;
   
	function wo_guia_despacho() {
		
		// MH DESDE 20032022 SE RESTRINGE VER LAS GD DE OTROS VENDEDORES PARA ARODRIGUEZ, CURTUBIA, EOLMEDO
		// Llama a w_base para que $this->cod_usuario contenga al usuario actual MH 20032022
		parent::w_base('guia_despacho', $_REQUEST['cod_item_menu']);		
		
		$sql = "select	GD.COD_GUIA_DESPACHO
						,GD.NRO_GUIA_DESPACHO
						,convert(varchar(20), GD.FECHA_GUIA_DESPACHO, 103) FECHA_GUIA_DESPACHO
						,GD.FECHA_GUIA_DESPACHO DATE_GUIA_DESPACHO
						,GD.RUT
						,GD.DIG_VERIF
						,GD.NOM_EMPRESA
						,EDS.COD_ESTADO_DOC_SII
						,EDS.NOM_ESTADO_DOC_SII
						,GD.COD_FACTURA
						,dbo.f_gd_nros_factura(GD.COD_GUIA_DESPACHO) NRO_FACTURA
						,TGD.COD_TIPO_GUIA_DESPACHO
						,TGD.NOM_TIPO_GUIA_DESPACHO
						,GD.COD_DOC
						,dbo.f_tipo_fa(EDS.NOM_ESTADO_DOC_SII) TIPO_GD
			from 		GUIA_DESPACHO GD LEFT OUTER JOIN FACTURA F 
					ON GD.COD_FACTURA = F.COD_FACTURA
						,ESTADO_DOC_SII EDS
						,TIPO_GUIA_DESPACHO TGD
			where		GD.COD_ESTADO_DOC_SII = EDS.COD_ESTADO_DOC_SII  and
						GD.COD_TIPO_GUIA_DESPACHO = TGD.COD_TIPO_GUIA_DESPACHO and
						GD.COD_TIPO_GUIA_DESPACHO <> ".self::K_TIPO_ARRIENDO."
						and dbo.f_get_tiene_acceso (".$this->cod_usuario.", 'GUIA_DESPACHO',GD.COD_DOC, NULL) = 1 
			order by	isnull(NRO_GUIA_DESPACHO, 9999999999) desc, COD_GUIA_DESPACHO desc";
			
		parent::w_output_biggi('guia_despacho', $sql, $_REQUEST['cod_item_menu']);
	
		$this->dw->add_control(new edit_nro_doc('COD_GUIA_DESPACHO','GUIA_DESPACHO'));
		$this->dw->add_control(new static_num('RUT'));
					
		// headers
		$this->add_header($control = new header_date('FECHA_GUIA_DESPACHO', 'FECHA_GUIA_DESPACHO', 'Fecha'));
		$control->field_bd_order = 'DATE_GUIA_DESPACHO';
		$this->add_header(new header_num('NRO_GUIA_DESPACHO', 'NRO_GUIA_DESPACHO', 'Nº GD'));
		$this->add_header(new header_rut('RUT', 'GD', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'GD.NOM_EMPRESA', 'Razón Social'));
		$sql_estado_doc_sii = "select COD_ESTADO_DOC_SII ,NOM_ESTADO_DOC_SII from ESTADO_DOC_SII order by	COD_ESTADO_DOC_SII";
		$this->add_header(new header_drop_down('NOM_ESTADO_DOC_SII', 'EDS.COD_ESTADO_DOC_SII', 'Estado', $sql_estado_doc_sii));
		$this->add_header($control = new header_num('NRO_FACTURA', '(dbo.f_gd_nros_factura(GD.COD_GUIA_DESPACHO))', 'Nº Factura'));
		$control->field_bd_order = 'NRO_FACTURA';
		
		$sql_tipo_guia_despacho = "select COD_TIPO_GUIA_DESPACHO ,NOM_TIPO_GUIA_DESPACHO from TIPO_GUIA_DESPACHO order by COD_TIPO_GUIA_DESPACHO";
		$this->add_header($control = new header_num('NRO_FACTURA', 'dbo.f_gd_nros_factura(COD_GUIA_DESPACHO)', 'Nº Factura'));
		$control->field_bd_order = 'NRO_FACTURA';
		$sql_tipo_guia_despacho = "select COD_TIPO_GUIA_DESPACHO ,NOM_TIPO_GUIA_DESPACHO from TIPO_GUIA_DESPACHO order by	COD_TIPO_GUIA_DESPACHO";

		$this->add_header(new header_drop_down('NOM_TIPO_GUIA_DESPACHO', 'TGD.COD_TIPO_GUIA_DESPACHO', 'Tipo Guía', $sql_tipo_guia_despacho));
		$this->add_header(new header_num('COD_DOC', 'GD.COD_DOC', 'N° Docto.'));
		$sql = "SELECT 'Sin tipo' ES_TIPO, 'Sin tipo' TIPO_GD 
				UNION 
				SELECT 'Papel' ES_TIPO , 'Papel' TIPO_GD
				UNION 
				SELECT 'Electrónica' ES_TIPO , 'Electrónica' TIPO_GD";

		$this->add_header($control = new header_drop_down_string('TIPO_GD', '(dbo.f_tipo_fa(EDS.NOM_ESTADO_DOC_SII))', 'Tipo GD', $sql));
  		$control->field_bd_order = 'TIPO_GD';
  		
  		$priv = $this->get_privilegio_opcion_usuario('994520', $this->cod_usuario); //print
		if($priv=='E')
			$this->autoriza_print = true;
      	else
			$this->autoriza_print = false;
			
		$priv = $this->get_privilegio_opcion_usuario('994530', $this->cod_usuario); //xml
		if($priv=='E')
			$this->autoriza_xml = true;
      	else
			$this->autoriza_xml = false;
	}

	function crear_gd_from_nv($valor_devuelto){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$cantidad_max = $this->get_parametro(self::K_PARAM_MAX_IT_GD);
		if ($cantidad_max=='' || $cantidad_max==0)
			$cantidad_max = 18;
		
		$array = explode("-", $valor_devuelto);
		$array2 = explode(".", $array[0]);
		$cod_nota_venta	= $array[0];
		$datos	= $array[1];
		$cod_usuario = $this->cod_usuario;			
		$sp = 'sp_gd_crear_desde_nv';
		$param = "$cod_nota_venta, $cod_usuario, '$datos'";
		$reg = explode("|", $datos);
		$vl_cantidad_gd = count($reg)/$cantidad_max;
		
		$db->BEGIN_TRANSACTION();
		if ($db->EXECUTE_SP($sp, $param)){ 
			$db->COMMIT_TRANSACTION();
			
			if(count($reg) <= $cantidad_max)
				$this->detalle_record_desde(true,1);
			else
				$this->retrieve();
		}
		else{ 
			$db->ROLLBACK_TRANSACTION();	
			$this->_redraw();
			$this->alert("No se pudo crear la guía de despacho. Error en 'sp_gd_crear_desde_nv', favor contacte a IntegraSystem.");
		}			
	}
	
	function redraw_item(&$temp, $ind, $record){
		$temp->gotoNext("wo_registro");
		if ($ind % 2 == 0) {
			$temp->setVar("wo_registro.WO_TR_CSS", $this->css_claro);
			$temp->setVar("wo_registro.WO_DETALLE", '<input name="b_detalle_'.$ind.'" id="b_detalle_'.$ind.'" value="'.$ind.'" src="../../../../com	monlib/trunk/images/lupa1.jpg" type="image">');
		}
		else {
			$temp->setVar("wo_registro.WO_TR_CSS", $this->css_oscuro);
			$temp->setVar("wo_registro.WO_DETALLE", '<input name="b_detalle_'.$ind.'" id="b_detalle_'.$ind.'" value="'.$ind.'" src="../../../../commonlib/trunk/images/lupa2.jpg" type="image">');
		}
		
		$COD_ESTADO_DOC_SII = $this->dw->get_item($record, 'COD_ESTADO_DOC_SII');
		$COD_GUIA_DESPACHO	= $this->dw->get_item($record, 'COD_GUIA_DESPACHO');
		
		if($COD_ESTADO_DOC_SII == 2 && $this->autoriza_print == true){
			$temp->setVar("wo_registro.WO_PRINT_DTE", '<input name="b_printDTE_'.$ind.'" id="b_printDTE_'.$ind.'" value="'.$ind.'" title="Imprimir" src="../../images_appl/b_dte_print.png" type="image">');
		}else if($COD_ESTADO_DOC_SII == 3 && $this->autoriza_print == true){
			$temp->setVar("wo_registro.WO_PRINT_DTE", '<input name="b_printDTE_'.$ind.'" id="b_printDTE_'.$ind.'" value="'.$ind.'" title="Imprimir" src="../../images_appl/b_dte_print.png" type="image">');
		}else{
			$temp->setVar("wo_registro.WO_PRINT_DTE", '<img src="../../images_appl/b_dte_print_d.png">');
		}
		
		if($COD_ESTADO_DOC_SII == 3 && $this->autoriza_xml == true && $COD_GUIA_DESPACHO > 26706){
			$temp->setVar("wo_registro.WO_XML_DTE", '<input name="b_xmlDTE_'.$ind.'" id="b_xmlDTE_'.$ind.'" value="'.$ind.'" title="Descargar XML" src="../../images_appl/b_dte_xml.png" type="image">');
		}else{
			$temp->setVar("wo_registro.WO_XML_DTE", '<img src="../../images_appl/b_dte_xml_d.png">');
		}
		
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
	
	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_gd_from_nv($_POST['wo_hidden']);
		else if ($this->clicked_boton('b_printDTE', $value_boton))
			$this->printdte($value_boton);
		else if ($this->clicked_boton('b_xmlDTE', $value_boton))
			$this->xmldte($value_boton);	
		else
			parent::procesa_event();
	}
	
	function printdte($rec_no){
  		$wi = new wi_guia_despacho('cod_item_menu');
		$wi->current_record = $rec_no;
		$wi->load_record();
		$wi->imprimir_dte(true);
		$this->goto_page($this->current_page);
  	}
  	
	function xmldte($rec_no){
  		$wi = new wi_guia_despacho('cod_item_menu');
		$wi->current_record = $rec_no;
		$wi->load_record();
		$wi->xml_dte();
  	}
}
?>