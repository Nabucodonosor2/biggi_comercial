<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../common_appl/class_w_output_biggi.php");
require_once(dirname(__FILE__)."/../../common_appl/class_header_vendedor.php");
require_once(dirname(__FILE__)."/../../../appl.ini");

class wo_orden_compra extends wo_orden_compra_base {
   	function wo_orden_compra() {
   		$this->checkbox_sumar = false;
   		
   		$sql = "select		COD_ORDEN_COMPRA                
							,convert(varchar(20), FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA
							,FECHA_ORDEN_COMPRA DATE_ORDEN_COMPRA              							                      
							,E.RUT
							,E.DIG_VERIF
							,E.NOM_EMPRESA              
							,REFERENCIA       
							,U.INI_USUARIO
							,NOM_ESTADO_ORDEN_COMPRA			
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
		$sql_estado_oc = "select COD_ESTADO_ORDEN_COMPRA, NOM_ESTADO_ORDEN_COMPRA from ESTADO_ORDEN_COMPRA order by COD_ESTADO_ORDEN_COMPRA";
		$this->add_header(new header_drop_down('NOM_ESTADO_ORDEN_COMPRA', 'EOC.COD_ESTADO_ORDEN_COMPRA', 'Estado', $sql_estado_oc));
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

}

?>