<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_bitacora_cotizacion extends w_output {
   function wo_bitacora_cotizacion() {
      	$sql = "select C.COD_COTIZACION
						,convert(varchar, B.FECHA_BITACORA, 103) FECHA_BITACORA
						,B.FECHA_BITACORA DATE_BITACORA_COTIZACION
						,convert(varchar, C.FECHA_COTIZACION, 103) FECHA_COTIZACION
						,U.INI_USUARIO
						,B.GLOSA
						,B.TIENE_COMPROMISO
						,convert(varchar, B.FECHA_COMPROMISO, 103) FECHA_COMPROMISO
						,B.FECHA_COMPROMISO DATE_COMPROMISO
						,B.GLOSA_COMPROMISO
						,B.COMPROMISO_REALIZADO
						,C.REFERENCIA
				from BITACORA_COTIZACION B, COTIZACION C, USUARIO U
				where C.COD_COTIZACION = B.COD_COTIZACION
				  and U.COD_USUARIO = B.COD_USUARIO
				order by DATE_COMPROMISO, C.COD_COTIZACION";
			
      	parent::w_output('bitacora_cotizacion', $sql, $_REQUEST['cod_item_menu']);
      
	    // headers
	    $this->add_header(new header_num('COD_BITACORA_COTIZACION', 'B.COD_BITACORA_COTIZACION', 'Cód.'));
	    $this->add_header($control = new header_date('FECHA_BITACORA', 'B.FECHA_BITACORA', 'Fecha'));
	    $control->field_bd_order = 'DATE_BITACORA_COTIZACION';
	    $this->add_header(new header_num('COD_COTIZACION', 'C.COD_COTIZACION', 'Cotizacion'));
	   
	    $this->add_header(new header_date('FECHA_COTIZACION', 'C.FECHA_COTIZACION', 'Fecha'));
		$sql = "select distinct U.COD_USUARIO, U.NOM_USUARIO from BITACORA_COTIZACION B, USUARIO U where B.COD_USUARIO = U.COD_USUARIO order by NOM_USUARIO";
		$this->add_header(new header_drop_down('INI_USUARIO', 'B.COD_USUARIO', 'Usuario.', $sql));
		$this->add_header(new header_text('REFERENCIA', 'C.REFERENCIA', 'Referencia'));

		$this->add_header($compromiso = new header_text('TIENE_COMPROMISO', 'B.TIENE_COMPROMISO', 'C.'));
		$this->add_header(new header_text('GLOSA_COMPROMISO', 'B.GLOSA_COMPROMISO', 'Glosa'));
	    $this->add_header($control = new header_date('FECHA_COMPROMISO', 'B.FECHA_COMPROMISO', 'Fecha'));
	    //$control->field_bd_order = 'DATE_BOLETA';
		$this->add_header($realizado = new header_text('COMPROMISO_REALIZADO', 'B.COMPROMISO_REALIZADO', 'R.'));
 		
 		// Filtro inicial
 		
		$compromiso->valor_filtro = 'S';
		$realizado->valor_filtro = 'N';
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