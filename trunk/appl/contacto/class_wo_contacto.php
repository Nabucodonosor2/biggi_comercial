<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_contacto extends w_parametrica
{
   function wo_contacto()
   {   	
   		$sql = "SELECT COD_CONTACTO, RUT, DIG_VERIF, NOM_CONTACTO 
				FROM CONTACTO
				ORDER BY COD_CONTACTO ASC";

      	parent::w_parametrica('contacto', $sql, $_REQUEST['cod_item_menu'], '10250575');
      
      	$this->dw->add_control(new static_num('RUT'));
      	
      	// headers	
      	$this->add_header(new header_num('COD_CONTACTO', 'COD_CONTACTO', 'Código'));
      	$this->add_header(new header_rut('RUT', 'CONTACTO', 'Rut'));
      	$this->add_header(new header_text('NOM_CONTACTO', 'NOM_CONTACTO', 'Razón Social'));
   }
   
	function detalle_record($rec_no) {
		parent::detalle_record($rec_no);
		session::set('contacto_desde_output', 'OK');	// para indicar que viene del output
	}
}
?>