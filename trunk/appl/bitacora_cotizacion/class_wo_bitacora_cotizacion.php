<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_vendedor.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_bitacora_cotizacion extends w_output_biggi {
   function wo_bitacora_cotizacion() {
      	$sql = "select C.COD_COTIZACION
						,convert(varchar, C.FECHA_COTIZACION, 103) FECHA_COTIZACION
						,C.FECHA_COTIZACION DATE_COTIZACION
						,U.INI_USUARIO
						,B.COD_USUARIO
						,convert (varchar(2000),B.GLOSA) GLOSA
						,B.TIENE_COMPROMISO
						,convert(varchar, B.FECHA_COMPROMISO, 103) FECHA_COMPROMISO
						,B.FECHA_COMPROMISO DATE_COMPROMISO
						,B.GLOSA_COMPROMISO
						,B.COMPROMISO_REALIZADO
						,C.REFERENCIA
						,C.TOTAL_NETO
				from BITACORA_COTIZACION B, COTIZACION C, USUARIO U
				where C.COD_COTIZACION = B.COD_COTIZACION
				  and U.COD_USUARIO = B.COD_USUARIO
				order by DATE_COTIZACION asc,  C.TOTAL_NETO desc";
			
      	parent::w_output_biggi('bitacora_cotizacion', $sql, $_REQUEST['cod_item_menu']);
        
      	$this->dw->add_control(new edit_precio('TOTAL_NETO'));
	    // headers
      	$this->add_header(new header_num('COD_COTIZACION', 'C.COD_COTIZACION', 'Nº Cot.'));
      	$this->add_header($control = new header_date('FECHA_COTIZACION', 'FECHA_COTIZACION', 'F. Cot.'));
	    $control->field_bd_order = 'DATE_COTIZACION';
      	//$sql = "select distinct U.COD_USUARIO, U.NOM_USUARIO from BITACORA_COTIZACION B, USUARIO U where B.COD_USUARIO = U.COD_USUARIO order by NOM_USUARIO";
		$this->add_header(new header_vendedor('INI_USUARIO', 'B.COD_USUARIO', 'Vend.'));
		$this->add_header(new header_text('GLOSA_COMPROMISO', 'GLOSA', 'Glosa C.'));
      	$this->add_header(new header_text('REFERENCIA', 'C.REFERENCIA', 'Referencia Cot.'));
	    
      	$this->add_header(new header_text('TIENE_COMPROMISO', 'B.TIENE_COMPROMISO', 'R.'));
	    $this->add_header($control = new header_date('FECHA_COMPROMISO', 'B.FECHA_COMPROMISO', 'Fecha C.'));
        $sql = "select distinct COMPROMISO_REALIZADO, 
				        case COMPROMISO_REALIZADO 
					    when 'S' then 'REALIZADO'
					    when 'N' then 'NO REALIZADO'
				        end NOM_COMPROMISO_REALIZADO
			    from BITACORA_COTIZACION
			    order by COMPROMISO_REALIZADO";
        $this->add_header($compromiso = new header_drop_down_string('COMPROMISO_REALIZADO', 'B.COMPROMISO_REALIZADO', 'CR.', $sql));
        $this->add_header(new header_num('TOTAL_NETO', 'C.TOTAL_NETO', 'Total Neto'));
 		// Filtro inicial
 		$compromiso->valor_filtro = 'N';
		$this->make_filtros();
		
   	}
	function detalle_record($rec_no) {	
		session::set('DESDE_wo_cotizacion', 'desde output');	// para indicar que viene del output
		session::set('DESDE_wo_bitacora_cotizacion', 'true');
		$ROOT = $this->root_url;
		$url = $ROOT.'appl/cotizacion';
		header ('Location:'.$url.'/wi_cotizacion.php?rec_no='.$rec_no.'&cod_item_menu=1505');
	}

}
?>