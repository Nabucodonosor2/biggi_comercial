<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_usuario_bitacora.php");

class wo_bitacora_factura extends w_output {
   function wo_bitacora_factura() {
      	$sql = "select F.COD_FACTURA
      					,B.COD_USUARIO
						,convert(varchar, B.FECHA_BITACORA_FACTURA, 103) FECHA_BITACORA_FACTURA
						,B.FECHA_BITACORA_FACTURA DATE_BITACORA_FACTURA
						,F.NRO_FACTURA
						,convert(varchar, F.FECHA_FACTURA, 103) FECHA_FACTURA
						,U.INI_USUARIO
						,B.GLOSA
						,B.TIENE_COMPROMISO
						,convert(varchar, B.FECHA_COMPROMISO, 103) FECHA_COMPROMISO
						,B.FECHA_COMPROMISO DATE_COMPROMISO
						,B.GLOSA_COMPROMISO
						,B.COMPROMISO_REALIZADO
						,F.NOM_EMPRESA
				from BITACORA_FACTURA B, FACTURA F, USUARIO U
				where F.COD_FACTURA = B.COD_FACTURA
				  and U.COD_USUARIO = B.COD_USUARIO
				order by DATE_COMPROMISO, F.NRO_FACTURA";
			
      	parent::w_output('bitacora_factura', $sql, $_REQUEST['cod_item_menu']);
      
	    // headers
	    $this->add_header(new header_num('COD_BITACORA_FACTURA', 'B.COD_BITACORA_FACTURA', 'Cód.'));
	    $this->add_header($control = new header_date('FECHA_BITACORA_FACTURA', 'B.FECHA_BITACORA_FACTURA', 'Fecha'));
	    $control->field_bd_order = 'DATE_BITACORA_FACTURA';
	    $this->add_header(new header_num('NRO_FACTURA', 'F.NRO_FACTURA', 'Factura'));
	    $this->add_header(new header_date('FECHA_FACTURA', 'F.FECHA_FACTURA', 'F. FA'));
		
		$this->add_header(new header_usuario_bitacora('INI_USUARIO', 'B.COD_USUARIO', 'Usu.'));
		$this->add_header(new header_text('NOM_EMPRESA', 'F.NOM_EMPRESA', 'Razón Social'));

		$sql = "select 'S' COD_FACTURA
	     				,'SI' TIENE_COMPROMISO
	      		  union
	      		  select 'N' COD_FACTURA
	      				,'NO' TIENE_COMPROMISO";
		$this->add_header($compromiso = new header_drop_down_string('TIENE_COMPROMISO', 'TIENE_COMPROMISO', 'C.',$sql));
		$this->add_header(new header_text('GLOSA_COMPROMISO', 'B.GLOSA_COMPROMISO', 'Glosa'));
	    $this->add_header($control = new header_date('FECHA_COMPROMISO', 'B.FECHA_COMPROMISO', 'Fecha'));
	    $control->field_bd_order = 'DATE_BOLETA';
	    
	    $sql = "select 'S' COD_FACTURA
	     				,'SI' COMPROMISO_REALIZADO
	      		  union
	      		  select 'N' COD_FACTURA
	      				,'NO' COMPROMISO_REALIZADO";
		$this->add_header($realizado = new header_drop_down_string('COMPROMISO_REALIZADO', 'B.COMPROMISO_REALIZADO', 'R.',$sql));
 
 		// Filtro inicial
		$compromiso->valor_filtro = 'S';
		$realizado->valor_filtro = 'N';
		$this->make_filtros();
   	}
	function detalle_record($rec_no) {
		session::set('DESDE_wo_factura', 'desde output');	// para indicar que viene del output
		session::set('DESDE_wo_bitacora_factura', 'true');
		$ROOT = $this->root_url;
		$url = $ROOT.'appl/factura';
		header ('Location:'.$url.'/wi_factura.php?rec_no='.$rec_no.'&cod_item_menu=1535');
	}
}
?>