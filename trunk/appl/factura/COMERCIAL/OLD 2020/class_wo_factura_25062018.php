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
/*
	   	$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_EXPORTAR, $this->cod_usuario);
		if ($priv=='E') {
			$this->b_export_visible = true;
      	}
      	else {
			$this->b_export_visible = false;
      	}*/
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
		$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'Total c/IVA'));
		$this->add_header(new header_text('COD_DOC', 'COD_DOC', 'N° NV'));
		$this->add_header(new header_vendedor('INI_USUARIO', 'F.COD_USUARIO_VENDEDOR1', 'V1'));
		
		$sql = "SELECT 'Sin tipo' ES_TIPO, 'Sin tipo' TIPO_FA 
				UNION 
				SELECT 'Papel' ES_TIPO , 'Papel' TIPO_FA
				UNION 
				SELECT 'Electrónica' ES_TIPO , 'Electrónica' TIPO_FA";
		$this->add_header(new header_drop_down_string('TIPO_FA', 'dbo.f_tipo_fa(EDS.NOM_ESTADO_DOC_SII)', 'Tipo FA', $sql)); 
  	}
	
}
?>