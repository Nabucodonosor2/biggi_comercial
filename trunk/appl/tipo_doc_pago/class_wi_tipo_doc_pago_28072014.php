<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wi_tipo_doc_pago extends w_input {
	function wi_tipo_doc_pago($cod_item_menu) {
		parent::w_input('tipo_doc_pago', $cod_item_menu);		
		$sql = "select	COD_TIPO_DOC_PAGO 
						,NOM_TIPO_DOC_PAGO
						,NOM_CORTO													 
						,ORDEN
						from TIPO_DOC_PAGO
						where COD_TIPO_DOC_PAGO = {KEY1}";
		$this->dws['dw_tipo_doc_pago'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_tipo_doc_pago']->add_control(new edit_text_upper('NOM_TIPO_DOC_PAGO', 80, 100));		
		$this->dws['dw_tipo_doc_pago']->add_control(new edit_num('ORDEN',12,10));
		$this->dws['dw_tipo_doc_pago']->add_control(new edit_text('NOM_CORTO',10,100));	
		
		// asigna los mandatorys
		$this->dws['dw_tipo_doc_pago']->set_mandatory('NOM_TIPO_DOC_PAGO', 'Nombre Documento');
		$this->dws['dw_tipo_doc_pago']->set_mandatory('ORDEN', 'Orden');
		$this->dws['dw_tipo_doc_pago']->set_mandatory('NOM_CORTO', 'Nombre Corto Softland');
		
		//asigna auditoria
		$this->add_auditoria('NOM_TIPO_DOC_PAGO');
		$this->add_auditoria('ORDEN');
		$this->add_auditoria('NOM_CORTO');
	}
	
	function new_record() {
		$this->dws['dw_tipo_doc_pago']->insert_row();
	}
	function load_record() {
		$cod_tipo_doc_pago = $this->get_item_wo($this->current_record, 'COD_TIPO_DOC_PAGO');
		$this->dws['dw_tipo_doc_pago']->retrieve($cod_tipo_doc_pago);
	}
	function get_key() {
		return $this->dws['dw_tipo_doc_pago']->get_item(0, 'COD_TIPO_DOC_PAGO');
	}
	
	function save_record($db) {
		$COD_TIPO_DOC_PAGO	= $this->get_key();
		$NOM_TIPO_DOC_PAGO	= $this->dws['dw_tipo_doc_pago']->get_item(0, 'NOM_TIPO_DOC_PAGO');
		$ORDEN 				= $this->dws['dw_tipo_doc_pago']->get_item(0, 'ORDEN');
		$NOM_CORTO			= $this->dws['dw_tipo_doc_pago']->get_item(0, 'NOM_CORTO');
		
		$COD_TIPO_DOC_PAGO	= ($COD_TIPO_DOC_PAGO=='') ? "null" : $COD_TIPO_DOC_PAGO;
		$NOM_CORTO 			= ($NOM_CORTO=='') ? "null" : $NOM_CORTO;		
	    
		$sp = 'spu_tipo_doc_pago';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_TIPO_DOC_PAGO, '$NOM_TIPO_DOC_PAGO', $ORDEN, '$NOM_CORTO'"; 
		
	    if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_tipo_doc_pago = $db->GET_IDENTITY();
				$this->dws['dw_tipo_doc_pago']->set_item(0, 'COD_TIPO_DOC_PAGO', $cod_tipo_doc_pago);
				
				$parametros_sp = "'RECALCULA',$cod_tipo_doc_pago";	
				if (!$db->EXECUTE_SP('spu_tipo_doc_pago', $parametros_sp));
				
			}
				
			return true;
		}
		return false;		
	}
}
?>