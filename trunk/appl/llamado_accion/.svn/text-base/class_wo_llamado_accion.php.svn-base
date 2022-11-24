<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_llamado_accion extends w_parametrica
{
   function wo_llamado_accion()
   {
      $sql = "select COD_LLAMADO_ACCION, NOM_LLAMADO_ACCION
					 from LLAMADO_ACCION
					ORDER BY COD_LLAMADO_ACCION";
			
      parent::w_parametrica('llamado_accion', $sql, $_REQUEST['cod_item_menu'], '10250580');
      
      // headers
      $this->add_header(new header_num('COD_LLAMADO_ACCION', 'COD_LLAMADO_ACCION', 'Código'));
      $this->add_header(new header_text('NOM_LLAMADO_ACCION', 'NOM_LLAMADO_ACCION', 'Descripción'));
   }
	
    
}
?>