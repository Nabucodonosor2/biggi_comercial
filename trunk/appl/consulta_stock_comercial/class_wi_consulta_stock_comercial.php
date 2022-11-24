<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class dw_stock_producto extends datawindow {
	var $tipo_mod_arriendo;
	
	function dw_stock_producto() {
		$sql_i = "SELECT '' ITEM 
				,P.COD_PRODUCTO
				,P.NOM_PRODUCTO
				,'' STOCK_TODOINOX
				,'' STOCK_BODEGA
				,'' COLOR_ROJO
        FROM PRODUCTO P";
		parent::datawindow($sql_i, 'STOCK_PRODUCTO', true, true);
		
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		// Agrega script adicional a COD_PRODUCTO 
		$this->controls['COD_PRODUCTO']->set_onChange("change_stock_producto(this, 'COD_PRODUCTO');");
		$this->set_first_focus('COD_PRODUCTO');
		//Agranda el tama�o del input sin perder su funcionabilidad heredada de help_producto
		$this->controls['NOM_PRODUCTO']->set_onChange("change_stock_producto(this, 'COD_PRODUCTO');");
		$this->controls['NOM_PRODUCTO']->size = 70;
		$this->add_control(new static_text('STOCK_TODOINOX'));
		$this->add_control(new static_text('STOCK_BODEGA'));
  }
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'ITEM', $this->row_count() * 10);
		return $row;
	}
}
class wi_consulta_stock_comercial extends w_input {
	
	function lock_record() {
		return true;
	}
	
	function wi_consulta_stock_comercial() 	{
		$this->tiene_wo = false;
		parent::w_input('consulta_stock_comercial', $_REQUEST['cod_item_menu']);
		$sql = "SELECT	'' COD_PRODUCTO ,CONVERT(varchar,getdate(),103)+' '+ CONVERT(varchar,getdate(),108) FECHA";
		$this->dws['consulta_stock_comercial'] = new datawindow($sql);
     $this->dws['consulta_stock_comercial']->add_control(new static_text('FECHA'));
		$this->dws['dw_stock_producto'] = new dw_stock_producto();
		
		$this->save_SESSION();
		$this->need_redraw();
		
		header("Location: wi_consulta_stock_comercial.php");
	}
	function load_record() 	{
		$this->current_record = 0;
		$this->dws['consulta_stock_comercial']->retrieve();
	}
	function get_key() 	{
		return 0;
	}
	// Se reimplementa para que no se ejecute codigo respecto a los navegadores
	function navegacion(&$temp) 	{
		$temp->setVar("WI_RUTA_MENU", $this->ruta_menu);
		$temp->setVar("WI_FECHA_ACTUAL", 'Fecha Actual: ' . $this->current_date());
		$key = $this->limpia_key($this->get_key());
		$temp->setVar("WI_FECHA_MODIF", '');		
		$this->habilita_boton($temp, 'back', true);
	}
	function procesa_event(){		
		if (session::is_set('REDRAW_' . $this->nom_tabla)) {
			session::un_set('REDRAW_' . $this->nom_tabla);
			$this->load_record();
			$this->modify_record();
			//$this->redraw();
		} 
		else
			parent::procesa_event();
	}
}
?>