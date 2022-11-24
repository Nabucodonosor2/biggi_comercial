<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

/*
Clase : WI_LLAMADO_ACCION
*/
class wi_llamado_accion extends w_input {
	function wi_llamado_accion($cod_item_menu) {
		parent::w_input('llamado_accion', $cod_item_menu);
		$this->valida_llave = true;		// valida que la PK sea unica

		$sql = "select COD_LLAMADO_ACCION, NOM_LLAMADO_ACCION
	 			from LLAMADO_ACCION
				where COD_LLAMADO_ACCION = {KEY1}
				ORDER BY COD_LLAMADO_ACCION";
		
		$this->dws['dw_llamado_accion'] = new datawindow($sql);

		// asigna los formatos		
		$this->dws['dw_llamado_accion']->add_control(new edit_text_upper('NOM_LLAMADO_ACCION', 80, 100));		
		
		// asigna los mandatorys
		$this->dws['dw_llamado_accion']->set_mandatory('COD_LLAMADO_ACCION', 'Código Llamado Acción');
		$this->dws['dw_llamado_accion']->set_mandatory('NOM_LLAMADO_ACCION', 'Nombre Llamado acción');
		
	}
	function new_record() {
		$this->dws['dw_llamado_accion']->insert_row();
		$this->dws['dw_llamado_accion']->add_control(new edit_num('COD_LLAMADO_ACCION', 12, 10));		
	}
	function load_record() {
		$cod_llamado_accion = $this->get_item_wo($this->current_record, 'COD_LLAMADO_ACCION');
		$this->dws['dw_llamado_accion']->retrieve($cod_llamado_accion);
	}
	function get_key() {
		return $this->dws['dw_llamado_accion']->get_item(0, 'COD_LLAMADO_ACCION');
	}
	
	function save_record($db) {
		$cod_llamado_accion = $this->get_key();
		$nom_llamado_accion = $this->dws['dw_llamado_accion']->get_item(0, 'NOM_LLAMADO_ACCION');

		
		$cod_llamado_accion = ($cod_llamado_accion=='') ? "null" : $cod_llamado_accion;		
    
		$sp = 'spu_llamado_accion';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    		    
	    $param	= "'$operacion', $cod_llamado_accion, '$nom_llamado_accion'";
	    	
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_llamado_accion = $this->get_key();
				$this->dws['dw_llamado_accion']->set_item(0, 'COD_LLAMADO_ACCION', $cod_llamado_accion);
			}
			return true;
		}
		return false;		
				
	}
	
}

?>