<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_usuario.php");

class wo_traspaso_bodega extends w_output {
   function wo_traspaso_bodega() {      
		$sql = "SELECT	COD_TRASPASO_BODEGA,
						convert(varchar(20), FECHA_TRASPASO_BODEGA, 103) FECHA_TRASPASO_BODEGA,
						U.NOM_USUARIO,
						U.COD_USUARIO,
						COD_BODEGA_ORIGEN,
						(SELECT NOM_BODEGA FROM BODEGA WHERE COD_BODEGA = COD_BODEGA_ORIGEN) NOM_BODEGA_ORIGEN,
						COD_BODEGA_DESTINO,
						(SELECT NOM_BODEGA FROM BODEGA WHERE COD_BODEGA = COD_BODEGA_DESTINO) NOM_BODEGA_DESTINO
				FROM	TRASPASO_BODEGA TB, USUARIO U
				WHERE	TB.COD_USUARIO = U.COD_USUARIO
				ORDER BY COD_TRASPASO_BODEGA DESC";
			
		parent::w_output('traspaso_bodega', $sql, $_REQUEST['cod_item_menu']);
		      
		// headers
		$this->add_header(new header_num('COD_TRASPASO_BODEGA', 'COD_TRASPASO_BODEGA', 'Código'));
		$this->add_header(new header_date('FECHA_TRASPASO_BODEGA', 'FECHA_TRASPASO_BODEGA', 'Fecha'));
		
		$this->add_header(new header_usuario('NOM_USUARIO', 'COD_USUARIO', 'Usuario'));
		
		$sql = "SELECT COD_BODEGA, NOM_BODEGA FROM BODEGA";
	  	$this->add_header(new header_drop_down('NOM_BODEGA_ORIGEN', 'COD_BODEGA_ORIGEN', 'Bodega Origen', $sql));
	  	$this->add_header(new header_drop_down('NOM_BODEGA_DESTINO', 'COD_BODEGA_DESTINO', 'Bodega Destino', $sql));
	  	
   }
}							
?>