<?php
require_once(dirname(__FILE__) . "/../../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../common_appl/class_reporte_biggi.php");

class dw_item_entrada_bodega extends dw_item_entrada_bodega_base {
	function dw_item_entrada_bodega() {
		parent::dw_item_entrada_bodega_base(); 
	}
}
/*
class dw_item_entrada_bodega extends dw_item_entrada_bodega_base{
	function dw_item_entrada_bodega() {		
		$sql = "SELECT	IEB.COD_ITEM_ENTRADA_BODEGA,
						IEB.COD_ENTRADA_BODEGA,
						IEB.ORDEN,
						IEB.ITEM,
						IEB.COD_PRODUCTO,
						IEB.NOM_PRODUCTO,
						IEB.CANTIDAD,
						IEB.PRECIO,
						NULL CANTIDAD_MAX,
						NULL COD_ITEM_DOC
				FROM	ITEM_ENTRADA_BODEGA IEB
				WHERE 	IEB.COD_ENTRADA_BODEGA =  {KEY1}";
	
		parent::datawindow($sql, 'ITEM_ENTRADA_BODEGA', false, false);	
	
		$this->add_control(new edit_text('COD_ITEM_ENTRADA_BODEGA',10, 10, 'hidden'));
		$this->add_control(new edit_num('ORDEN',4, 10));
		$this->add_control(new edit_text('ITEM',4 , 5));
		$this->add_control($control = new edit_cantidad('CANTIDAD',12,10));
		$control->set_onChange("valida_cantidad_max(this);");
		
		$this->add_control(new edit_text('CANTIDAD_MAX',10, 10, 'hidden'));
		$this->add_control(new edit_text('COD_ITEM_DOC',10, 10, 'hidden'));
		
		$this->add_control(new computed('PRECIO', 0));
		$this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]');
		$this->accumulate('TOTAL');
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		
		$this->set_first_focus('COD_PRODUCTO');
	
		// asigna los mandatorys
		$this->set_mandatory('ORDEN', 'Orden');
		$this->set_mandatory('COD_PRODUCTO', 'Código del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');	
	}
	
	function update($db, $cod_entrada_bodega)	{
		$sp = 'spu_item_entrada_bodega';

		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$cod_item_entrada_bodega= $this->get_item($i, 'COD_ITEM_ENTRADA_BODEGA');
			$orden 					= $this->get_item($i, 'ORDEN');
			$item 					= $this->get_item($i, 'ITEM');
			$cod_producto 			= $this->get_item($i, 'COD_PRODUCTO');
			$nom_producto 			= $this->get_item($i, 'NOM_PRODUCTO');
			$cantidad 				= $this->get_item($i, 'CANTIDAD');
			$precio 				= $this->get_item($i, 'PRECIO');
			$cod_item_doc			= $this->get_item($i, 'COD_ITEM_DOC');

			$cod_item_entrada_bodega = ($cod_item_entrada_bodega=='') ? "null" : $cod_item_entrada_bodega;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';

			$param = "'$operacion'
						,$cod_item_entrada_bodega
						,$cod_entrada_bodega
						,$orden
						,'$item'
						,'$cod_producto'
						,'$nom_producto'
						,$cantidad
						,$precio
						,$cod_item_doc";
					
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$cod_item_entrada_bodega = $this->get_item($i, 'COD_ITEM_ENTRADA_BODEGA', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_item_entrada_bodega")){
				return false;
			}
		}
		//Ordernar
		if ($this->row_count() > 0) {
			$parametros_sp = "'ITEM_ENTRADA_BODEGA','ENTRADA_BODEGA', $cod_entrada_bodega";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp))
				return false;
		}
		return true;
	}
}
*/
class wi_entrada_bodega extends wi_entrada_bodega_base{
	function wi_entrada_bodega($cod_item_menu) {
		parent::wi_entrada_bodega_base($cod_item_menu);
		
		$sql = "select COD_BODEGA
						,NOM_BODEGA
				from BODEGA
				where COD_BODEGA = 4";	// sala venta
		$this->dws['dw_entrada_bodega']->controls['COD_BODEGA']->set_sql($sql);
		$this->dws['dw_entrada_bodega']->controls['COD_BODEGA']->retrieve();
		
		$sql = "select 1 COD_TIPO_DOC
						,'FACTURA' NOM_TIPO_DOC
				union
				select 2 COD_TIPO_DOC
						,'OCI' NOM_TIPO_DOC";
		
		$this->dws['dw_entrada_bodega']->add_control(new drop_down_dw('COD_TIPO_DOC', $sql, 120));
		
		$this->dws['dw_entrada_bodega']->add_control(new edit_text_multiline('OBS',90, 3));
		$this->dws['dw_entrada_bodega']->add_control(new edit_num('COD_DOC',21));
		if ($this->cod_usuario == 5){
			unset($this->dws['dw_entrada_bodega']->controls['COD_BODEGA']);
			$sql = "select COD_BODEGA
							,NOM_BODEGA
					from BODEGA
					where COD_BODEGA = 5"; //TRANSITO PL
			$this->dws['dw_entrada_bodega']->add_control(new drop_down_dw('COD_BODEGA', $sql, 120));
		}else if ($this->cod_usuario == 8 || $this->cod_usuario == 4 || $this->cod_usuario == 30 || $this->cod_usuario == 32 || $this->cod_usuario == 1 ){
			unset($this->dws['dw_entrada_bodega']->controls['COD_BODEGA']);
			$sql = "select COD_BODEGA
							,NOM_BODEGA
					from BODEGA"; //todos
			$this->dws['dw_entrada_bodega']->add_control(new drop_down_dw('COD_BODEGA', $sql, 120));	
		}else{
			unset($this->dws['dw_entrada_bodega']->controls['COD_BODEGA']);
			$sql = "select COD_BODEGA
							,NOM_BODEGA
					from BODEGA
					where COD_BODEGA = 4"; //SALA VENTA
			$this->dws['dw_entrada_bodega']->add_control(new drop_down_dw('COD_BODEGA', $sql, 120));
		}
		
		$this->dws['dw_entrada_bodega']->set_mandatory('REFERENCIA', 'Referencia');
		$this->dws['dw_entrada_bodega']->set_mandatory('COD_DOC', 'Nro. Documento');
		$this->dws['dw_entrada_bodega']->set_mandatory('COD_TIPO_DOC', 'Tipo Documento');
		$this->dws['dw_entrada_bodega']->set_mandatory('OBS', 'Observaciones');
	}
	function new_record() {
		parent::new_record();
		$this->dws['dw_entrada_bodega']->set_item(0, 'COD_BODEGA', 4);	// sala venta
		if ($this->cod_usuario == 5){
			$this->dws['dw_entrada_bodega']->set_item(0, 'COD_BODEGA',5);//TRANSITO PL
		}
	}
	function load_record() {
		$COD_ENTRADA_BODEGA = $this->get_item_wo($this->current_record, 'COD_ENTRADA_BODEGA');
		$this->dws['dw_entrada_bodega']->retrieve($COD_ENTRADA_BODEGA);	
		$this->dws['dw_item_entrada_bodega']->retrieve($COD_ENTRADA_BODEGA);
		
		$this->b_delete_visible  = false;
		$this->b_save_visible 	 = false;
		$this->b_no_save_visible = false;
		$this->b_modify_visible	 = false;
		$this->b_print_visible	 = true;
	}
	function save_record($db) {
		$cod_entrada_bodega = $this->get_key();
		$cod_bodega = $this->dws['dw_entrada_bodega']->get_item(0, 'COD_BODEGA');
		$tipo_doc = $this->dws['dw_entrada_bodega']->get_item(0, 'COD_TIPO_DOC');
		if ($tipo_doc == 1)
			$tipo_doc = 'FACTURA';
		else
			$tipo_doc = 'OCI'; 	
		$cod_doc= $this->dws['dw_entrada_bodega']->get_item(0, 'COD_DOC');
		$referencia = $this->dws['dw_entrada_bodega']->get_item(0, 'REFERENCIA');
		$obs = $this->dws['dw_entrada_bodega']->get_item(0, 'OBS');
		$nro_fa_proveedor = $this->dws['dw_entrada_bodega']->get_item(0, 'NRO_FA_PROVEEDOR');
		$fecha_fa_proveedor = $this->dws['dw_entrada_bodega']->get_item(0, 'FECHA_FA_PROVEEDOR');
		$tipo_fa_proveedor = 'NULL';
		
		$cod_entrada_bodega = ($cod_entrada_bodega=='') ? 'NULL' : $cod_entrada_bodega;
		
		$sp = 'spu_entrada_bodega';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    		    
	    $param	= "'$operacion'
	    			,$cod_entrada_bodega
	    			,$this->cod_usuario
	    			,$cod_bodega
	    			,$tipo_doc
	    			,$cod_doc
	    			,'$referencia'
	    			,'$obs'";

		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_entrada_bodega = $db->GET_IDENTITY();
				$this->dws['dw_entrada_bodega']->set_item(0, 'COD_ENTRADA_BODEGA', $cod_entrada_bodega);
			}
			
			if (!$this->dws['dw_item_entrada_bodega']->update($db, $cod_entrada_bodega))
				return false;
			
			return true;
		}
		return false;		
				
	}
	function print_record(){
		$cod_entrada = $this->get_key();
		$sql = "exec spi_entrada_bodega $cod_entrada";
		// reporte
		$labels = array();
		$labels['strCOD_ENTRADA'] = $cod_entrada;					
		$file_name = $this->find_file('entrada_bodega/COMERCIAL', 'entrada_bodega.xml');					
		$rpt = new print_entrada_bodega($sql, $file_name, $labels, "Entrada Bodega".$cod_entrada, 1);
		$this->_load_record();
		return true;
	}	
}
class print_entrada_bodega extends reporte_biggi {	
	function print_entrada_bodega($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
}
?>