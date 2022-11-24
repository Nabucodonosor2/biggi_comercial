<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_salida_bodega extends w_output
{
   function wo_salida_bodega()
   {      
		$sql = "SELECT	SB.COD_SALIDA_BODEGA,
						convert(varchar(20), SB.FECHA_SALIDA_BODEGA, 103) FECHA_SALIDA_BODEGA,
						U.NOM_USUARIO,
						B.NOM_BODEGA,
						SB.TIPO_DOC,
						dbo.f_get_nro_doc(SB.TIPO_DOC, SB.COD_DOC) NRO_DOC,
						SB.REFERENCIA
				FROM	SALIDA_BODEGA SB, USUARIO U, BODEGA B
				WHERE	SB.COD_USUARIO = U.COD_USUARIO
				AND		SB.COD_BODEGA = B.COD_BODEGA
				ORDER BY SB.COD_SALIDA_BODEGA desc";
			
		parent::w_output('salida_bodega', $sql, $_REQUEST['cod_item_menu']);
		      
		// headers
		$this->add_header(new header_num('COD_SALIDA_BODEGA', 'COD_SALIDA_BODEGA', 'Código'));
		$this->add_header(new header_date('FECHA_SALIDA_BODEGA', 'FECHA_SALIDA_BODEGA', 'Fecha'));
		$this->add_header(new header_text('NOM_USUARIO', 'NOM_USUARIO', 'Usuario'));
		$this->add_header(new header_text('NOM_BODEGA', 'NOM_BODEGA', 'Bodega'));
		$this->add_header(new header_text('TIPO_DOC', 'TIPO_DOC', 'Tipo Docto.'));
		$this->add_header(new header_num('NRO_DOC', 'dbo.f_get_nro_doc(SB.TIPO_DOC, SB.COD_DOC)', 'N° Docto.'));
   }
}
?>