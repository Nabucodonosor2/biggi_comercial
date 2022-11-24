<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_familia_web extends w_output
{
   function wo_familia_web()
   {
      $sql = "SELECT COD_FAMILIA
					   ,NOM_FAMILIA 
					   ,ECONOLINE 
					   ,NOM_PUBLICO
				FROM FAMILIA	
				ORDER BY COD_FAMILIA";
			
      parent::w_output('familia_web', $sql, $_REQUEST['cod_item_menu']);
      
      // headers
      $this->add_header(new header_num('COD_FAMILIA', 'COD_FAMILIA', 'Código'));
      $this->add_header(new header_text('NOM_FAMILIA', 'NOM_FAMILIA', 'Alias Familia'));
      $this->add_header(new header_text('NOM_PUBLICO', 'NOM_PUBLICO', 'Descripción Web'));
      $this->add_header(new header_text('ECONOLINE', 'ECONOLINE', 'Econoline'));
   }
	
    
}
?>