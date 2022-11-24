<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_cartola_participacion extends w_output
{
    function wo_cartola_participacion()
   {   	
      	$sql = "select COD_CARTOLA_PARTICIPACION 
                        , CP.COD_USUARIO 
                        , NOM_USUARIO
						,CP.ANO_CARTOLA
                from CARTOLA_PARTICIPACION  CP
                    ,USUARIO U
                WHERE  CP.COD_USUARIO=U.COD_USUARIO
                ORDER by COD_CARTOLA_PARTICIPACION"; 
			
      parent::w_output('cartola_participacion', $sql, $_REQUEST['cod_item_menu']);
      
      // headers
      $this->add_header(new header_num('COD_CARTOLA_PARTICIPACION', 'COD_CARTOLA_PARTICIPACION', 'Código'));
      $this->add_header(new header_text('NOM_USUARIO', 'NOM_USUARIO', 'Vendedor'));
	  $this->add_header(new header_text('ANO_CARTOLA', 'ANO_CARTOLA', 'Ano'));

		$this->make_filtros();
	}
}
?>