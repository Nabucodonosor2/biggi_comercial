<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../common_appl/class_w_output_biggi.php");
require_once(dirname(__FILE__)."/../../common_appl/class_header_vendedor.php");
require_once(dirname(__FILE__)."/../../../appl.ini");

class header_estado extends header_output { 
	
	function header_estado() {
		parent::header_output('NOM_ESTADO_ORDEN_COMPRA', 'EOC.COD_ESTADO_ORDEN_COMPRA', 'Estado');
	}
	function make_java_script() {
		return '"return dlg_estado(\''.$this->nom_header.'\', \''.$this->valor_filtro.'\', this);"';		
	}
	function set_value_filtro($valor_filtro) {
		if ($valor_filtro == '__BORRAR_FILTRO__') {
			$this->valor_filtro = '';
		}
		else {
			$this->valor_filtro = $valor_filtro;
		}
	}
	function make_filtro() {
		if (strlen($this->valor_filtro)==0)
			return '';
			
		return "(".$this->field_bd." in (".$this->valor_filtro.")) and ";		
	}
	function make_nom_filtro() {
		if ($this->valor_filtro=='')
			return '';
		
		return $this->nom_header.": ".$this->valor_filtro;
	}	
}

class wo_orden_compra extends wo_orden_compra_base {
   	function wo_orden_compra() {
   		$this->checkbox_sumar = false;
   		
		// MH DESDE 20032022 SE RESTRINGE VER LAS OC DE OTROS VENDEDORES PARA ARODRIGUEZ, CURTUBIA, EOLMEDO
		// Llama a w_base para que $this->cod_usuario contenga al usuario actual MH 20032022
		parent::w_base('orden_compra', $_REQUEST['cod_item_menu']);
		
   		$sql = "select		COD_ORDEN_COMPRA                
							,convert(varchar(20), FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA
							,FECHA_ORDEN_COMPRA DATE_ORDEN_COMPRA              							                      
							,E.RUT
							,E.DIG_VERIF
							,E.NOM_EMPRESA              
							,REFERENCIA       
							,U.INI_USUARIO
							,NOM_ESTADO_ORDEN_COMPRA
							,ISNULL(dbo.f_get_oc_faprov(O.COD_NOTA_VENTA, O.COD_ORDEN_COMPRA, 'S'), 0) STATUS			
							,TOTAL_NETO 
							,U.COD_USUARIO  
							,EOC.COD_ESTADO_ORDEN_COMPRA
							,AUTORIZA_FACTURACION
							,CASE
								WHEN AUTORIZA_FACTURACION = 'S' AND E.COD_EMPRESA = 1302 THEN 'SI'
								WHEN (AUTORIZA_FACTURACION = 'N' OR AUTORIZA_FACTURACION IS NULL) AND E.COD_EMPRESA = 1302 THEN 'NO'
								WHEN E.COD_EMPRESA <> 1302 THEN 'N/A'
							END AUTORIZA_FA_TDNX
				from 		ORDEN_COMPRA O
							,EMPRESA E
							,USUARIO U
							,ESTADO_ORDEN_COMPRA EOC
				where		O.COD_EMPRESA = E.COD_EMPRESA and 
							O.COD_USUARIO = U.COD_USUARIO and
							O.COD_ESTADO_ORDEN_COMPRA = EOC.COD_ESTADO_ORDEN_COMPRA and
							TIPO_ORDEN_COMPRA not in ('GASTO_FIJO','ARRIENDO')
							and dbo.f_get_tiene_acceso (".$this->cod_usuario.", 'ORDEN_COMPRA',O.COD_NOTA_VENTA, NULL) = 1 							
				order by	COD_ORDEN_COMPRA desc";		
			
   		parent::w_output_biggi('orden_compra', $sql, $_REQUEST['cod_item_menu']);
	
		$this->dw->add_control(new edit_nro_doc('COD_ORDEN_COMPRA','ORDEN_COMPRA'));
		$this->dw->add_control(new edit_precio('TOTAL_NETO'));
      	$this->dw->add_control(new static_num('RUT'));
		
		// headers
		$this->add_header(new header_num('COD_ORDEN_COMPRA', 'COD_ORDEN_COMPRA', 'Nº OC'));
		$this->add_header($control = new header_date('FECHA_ORDEN_COMPRA', 'FECHA_ORDEN_COMPRA', 'Fecha'));
		$control->field_bd_order = 'DATE_ORDEN_COMPRA';
	    $this->add_header(new header_rut('RUT', 'E', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Proveedor'));
		$this->add_header(new header_text('REFERENCIA', 'REFERENCIA', 'Referencia'));
		$this->add_header(new header_vendedor('INI_USUARIO', 'U.COD_USUARIO', 'V1'));
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql_estado_oc = "select COD_ESTADO_ORDEN_COMPRA, NOM_ESTADO_ORDEN_COMPRA 
                        from ESTADO_ORDEN_COMPRA order by COD_ESTADO_ORDEN_COMPRA";
		$result = $db->build_results($sql_estado_oc);
		/*for ($i=0 ; $i < count($result); $i++) {
			$valor_filtro = $valor_filtro.$result[$i]['COD_ESTADO_ORDEN_COMPRA'].',';
		}
		$valor_filtro = substr($valor_filtro, 0, strlen($valor_filtro)-1);*/	//borra ultima coma
		$this->add_header($h = new header_estado());
		$h->valor_filtro = '1,0,3,4';//$valor_filtro; '1,3,4'
		
		$this->add_header($control = new header_num('STATUS', 'ISNULL(dbo.f_get_oc_faprov(O.COD_NOTA_VENTA, O.COD_ORDEN_COMPRA, \'S\'), 0)', '% Fact Prov.'));
		$control->field_bd_order = 'STATUS';
		$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));
		$sql = "SELECT 'SI' AUTORIZA_FA_TDNX
					   ,'SI'	NOM_AUTORIZA_FA_TDNX
				UNION
				SELECT 'NO' AUTORIZA_FA_TDNX
					   ,'NO'	NOM_AUTORIZA_FA_TDNX
				UNION
				SELECT 'N/A' AUTORIZA_FA_TDNX
					   ,'N/A'	NOM_AUTORIZA_FA_TDNX";
		$this->add_header($control = new header_drop_down_string('AUTORIZA_FA_TDNX', "CASE
																				WHEN AUTORIZA_FACTURACION = 'S' AND E.COD_EMPRESA = 1302 THEN 'SI'
																				WHEN (AUTORIZA_FACTURACION = 'N' OR AUTORIZA_FACTURACION IS NULL) AND E.COD_EMPRESA = 1302 THEN 'NO'
																				WHEN E.COD_EMPRESA <> 1302 THEN 'N/A'
																		   END", 'Autoriza Fa. Todoinox' ,$sql));
		$control->field_bd_order = 'AUTORIZA_FA_TDNX';

		// dw checkbox
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_SUMAR, $this->cod_usuario);
		if ($priv=='E') {
			$DISPLAY_SUMAR = '';
      	}
      	else {
			$DISPLAY_SUMAR = 'none';
      	}
		
		$sql = "select '$DISPLAY_SUMAR' DISPLAY_SUMAR
						,'N' CHECK_SUMAR
					   ,'N' HIZO_CLICK";
		$this->dw_check_box = new datawindow($sql);
		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_SUMAR','S','N'));
		$control->set_onClick("sumar(); document.getElementById('loader').style.display='';");
		$this->dw_check_box->add_control(new edit_text_hidden('HIZO_CLICK'));
		$this->dw_check_box->retrieve();
   	}
   	
	function redraw(&$temp){
		parent::redraw(&$temp);
		
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_AGREGAR, $this->cod_usuario);
		if ($priv=='E') {
			$this->habilita_boton($temp, 'add', true);
      	}
      	else {
			$this->habilita_boton($temp, 'add', false);
      	}
	}

}

?>