<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_cot_nv.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");

///////////////////////////////////////////
class dw_familia_subfamilia extends dw_item{
	function dw_familia_subfamilia(){
	$sql = "SELECT COD_FAMILIA_SUBFAMILIA,
					FS.COD_FAMILIA,
					FS.COD_FAMILIA COD_FAMILIA_SUB,
					COD_SUBFAMILIA,
					ORDEN ORDEN_SUB
			FROM FAMILIA_SUBFAMILIA FS,FAMILIA F
			WHERE F.COD_FAMILIA = FS.COD_FAMILIA 
			AND F.COD_FAMILIA = {KEY1}
			ORDER BY ORDEN_SUB ASC";
		
		parent::dw_item($sql, 'FAMILIA_SUBFAMILIA', true, true, 'COD_FAMILIA_SUB');
		$sql="SELECT COD_FAMILIA COD_SUBFAMILIA
					  ,NOM_FAMILIA 
				FROM FAMILIA
				WHERE ES_SUBFAMILIA = 'S'
				ORDER BY COD_FAMILIA";
		$this->add_control(new drop_down_dw('COD_SUBFAMILIA', $sql,400));
		$this->add_control(new edit_num('ORDEN_SUB', 10));
		$this->add_control(new edit_text_upper('COD_FAMILIA_SUBFAMILIA',10, 10, 'hidden'));
		
	}
	function insert_row($row = -1){
		$row = parent::insert_row($row);
		$this->set_item($row, 'ORDEN_SUB', $this->row_count() * 10);
		return $row;
	}
	function fill_template(&$temp) {
		
		for ($i=0; $i < $this->row_count(); $i++) {
			if ($this->label_record != '') {
				$temp->gotoNext($this->label_record);
				$this->fill_record($temp, $i);
			}
			else
				$this->fill_record($temp, $i);	
		}
		if ($this->b_add_line_visible) {
			if ($this->entrable)
				$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line3(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer"/>';
			else 
				$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line_d.jpg"/>';
			$temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);
		}
		

		// dibuja los datos de los los accumulate
		for ($i=0; $i<count($this->accumulate); $i++) {		
			$field = $this->accumulate[$i];
			$field = 'SUM_'.$field;
			if ($this->row_count() > 0)
				$dato_con_formato = $this->controls[$field]->draw_entrable($this->get_item(0, $field), 0);
			else
				$dato_con_formato = $this->controls[$field]->draw_entrable(0, 0);			
			$temp->setVar($field, $dato_con_formato);
		}
	}
		function update($db){
		$sp = 'spu_familia_subfamilia';	
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}
			$cod_familia_subfamilia = $this->get_item($i, 'COD_FAMILIA_SUBFAMILIA');
			$cod_familia = $this->get_item($i, 'COD_FAMILIA');				
			$cod_subfamilia = $this->get_item($i, 'COD_SUBFAMILIA');
			$orden = $this->get_item($i, 'ORDEN_SUB');
						
			$cod_familia_subfamilia = ($cod_familia_subfamilia == '') ? "null" : $cod_familia_subfamilia;

			if ($statuts == K_ROW_NEW_MODIFIED){
				$operacion = 'INSERT';
			}
			elseif ($statuts == K_ROW_MODIFIED){
				$operacion = 'UPDATE';
			}
			$param = "'$operacion'
						,$cod_familia_subfamilia
						,$cod_familia
						, $cod_subfamilia
						,$orden";
				
			if (!$db->EXECUTE_SP($sp, $param)){
				return false;
			}
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++){
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
			
			$cod_familia_producto = $this->get_item($i, 'COD_FAMILIA_SUBFAMILIA', 'delete');
			
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_familia_producto")){
				return false;
			}
		}

		return true;
	}
}

//////////////////////////////////////////
class dw_familia_accesorio extends dw_item{
	
	const K_DESCONTINUADO = 4;
	function dw_familia_accesorio(){
	$sql = "SELECT FA.COD_FAMILIA_ACCESORIO
				   ,FA.COD_FAMILIA_PRODUCTO
				   ,P.COD_PRODUCTO COD_PRODUCTO_FA
				   ,P.NOM_PRODUCTO NOM_PRODUCTO_FA
				   ,ORDEN ORDEN_FA
				   ,COD_FAMILIA
			FROM FAMILIA_ACCESORIO FA, PRODUCTO P
			WHERE COD_FAMILIA = {KEY1}
			AND P.COD_PRODUCTO  = FA.COD_PRODUCTO  
			ORDER BY ORDEN_FA ASC";
		
		
		parent::dw_item($sql, 'FAMILIA_ACCESORIO', true, true, 'COD_PRODUCTO_FA');
		$this->add_control(new edit_num('ORDEN_FA', 10));
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		
		$this->controls['COD_PRODUCTO_FA']->size = 15;
		$this->controls['NOM_PRODUCTO_FA']->size = 100;
	


		$this->set_mandatory('ORDEN_FA', 'Orden');
		$this->set_mandatory('NOM_PRODUCTO_FA', 'Nombre Producto');

		$this->set_first_focus('COD_PRODUCTO_FA');
		
	}
	function insert_row($row = -1){
		$row = parent::insert_row($row);
		$this->set_item($row, 'ORDEN_FA', $this->row_count() * 10);
		return $row;
	}
	function add_controls_producto_help() {
		/* Agrega los constrols standar para manejar la selección de productos con help					
			 Los anchos y maximos de cada campo quedan fijos, la idea es que sean iguales en todos los formularios
			 si se desean tamaños distintos se debe reiimplementar esta función
		*/
		
		if (isset($this->controls['PRECIO']))
			$num_dec = $this->controls['PRECIO']->num_dec;
		else
			$num_dec = 0;
		$java_script = "help_producto(this, ".$num_dec.");";

		$this->add_control($control = new edit_text_upper('COD_PRODUCTO_FA', 25, 30));
		$control->set_onChange($java_script);
		$this->add_control($control = new edit_text_upper('NOM_PRODUCTO_FA', 55, 100));
		$control->set_onChange($java_script);

		// Se guarda el old para los casos en que una validación necesite volver al valor OLD  
		$this->add_control($control = new edit_text_upper('COD_PRODUCTO_FA_OLD', 30, 30, 'hidden'));
		
		// mandatorys
		$this->set_mandatory('COD_PRODUCTO_FA', 'Código del producto');
		$this->set_mandatory('NOM_PRODUCTO_FA', 'Descripción del producto');
	}
	function fill_template(&$temp) {
		for ($i=0; $i < $this->row_count(); $i++) {
			if ($this->label_record != '') {
				$temp->gotoNext($this->label_record);
				$this->fill_record($temp, $i);
			}
			else
				$this->fill_record($temp, $i);	
		}
		if ($this->b_add_line_visible) {
			if ($this->entrable)
				$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line2(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer"/>';
			else 
				$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line_d.jpg"/>';
			$temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);
		}

		// dibuja los datos de los los accumulate
		for ($i=0; $i<count($this->accumulate); $i++) {		
			$field = $this->accumulate[$i];
			$field = 'SUM_'.$field;
			if ($this->row_count() > 0)
				$dato_con_formato = $this->controls[$field]->draw_entrable($this->get_item(0, $field), 0);
			else
				$dato_con_formato = $this->controls[$field]->draw_entrable(0, 0);			
			$temp->setVar($field, $dato_con_formato);
		}
	}
	function update($db){
		$sp = 'spu_familia_accesorio';	
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}
			$cod_familia_accesorio = $this->get_item($i, 'COD_FAMILIA_ACCESORIO');
			$orden = $this->get_item($i, 'ORDEN_FA');
			$cod_producto = $this->get_item($i, 'COD_PRODUCTO_FA');
			$cod_familia_producto = $this->get_item($i, 'COD_FAMILIA_PRODUCTO');
			$cod_familia_cc = $this->get_item($i, 'COD_FAMILIA');
			
			$cod_familia_accesorio = ($cod_familia_accesorio == '') ? "null" : $cod_familia_accesorio;
			
			if ($statuts == K_ROW_NEW_MODIFIED){
				$operacion = 'INSERT';
			}
			elseif ($statuts == K_ROW_MODIFIED){
				$operacion = 'UPDATE';
			}
			
			$param = "'$operacion',$cod_familia_accesorio, NULL,'$cod_producto', $orden,$cod_familia_cc";
			if (!$db->EXECUTE_SP($sp, $param)){
				return false;
			}
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++){
			//$cod_producto = $this->get_item($i, 'COD_PRODUCTO');
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED){
				continue;
			}
			$cod_familia_accesorio = $this->get_item($i, 'COD_FAMILIA_ACCESORIO', 'delete');
			
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_familia_accesorio")){
				return false;
			}
		}
		return true;
	}
}

////////////////////////////////////////

class dw_familia_producto extends dw_item{
	
	const K_DESCONTINUADO = 4;
	function dw_familia_producto(){
	$sql = "SELECT FP.COD_FAMILIA_PRODUCTO
				   ,FP.NOM_FAMILIA_PRODUCTO
				   ,FP.ORDEN
				   ,FP.COD_FAMILIA
				   ,FP.COD_PRODUCTO
				   ,P.NOM_PRODUCTO
				   ,'' DISPLAY_SUBFAMLIA
			FROM FAMILIA_PRODUCTO FP, PRODUCTO P
			WHERE COD_FAMILIA = {KEY1}
			AND P.COD_PRODUCTO  = FP.COD_PRODUCTO  
			ORDER BY ORDEN ASC";
		
		
		parent::dw_item($sql, 'FAMILIA_PRODUCTO', true, true, 'COD_PRODUCTO');
		$this->add_control(new edit_num('ORDEN', 10));
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		
		$this->controls['COD_PRODUCTO']->size = 15;
		$this->controls['NOM_PRODUCTO']->size = 100;

		$this->set_mandatory('ORDEN', 'Orden');
		$this->set_mandatory('NOM_PRODUCTO', 'Nombre Producto');

		$this->set_first_focus('COD_PRODUCTO');
	}
	function insert_row($row = -1){
		$row = parent::insert_row($row);
		$this->set_item($row, 'ORDEN', $this->row_count() * 10);
		return $row;
	}
		function update($db){
		$sp = 'spu_familia_prod';	
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}
			$cod_familia_producto = $this->get_item($i, 'COD_FAMILIA_PRODUCTO');
			$orden = $this->get_item($i, 'ORDEN');
			$cod_producto = $this->get_item($i, 'COD_PRODUCTO');
			$nom_familia_producto = $this->get_item($i, 'NOM_FAMILIA_PRODUCTO');
			$cod_familia = $this->get_item($i, 'COD_FAMILIA');
			
			$cod_familia_producto = ($cod_familia_producto == '') ? "null" : $cod_familia_producto;
			$nom_familia_producto = ($nom_familia_producto == '') ? "null" : $nom_familia_producto;

			if ($statuts == K_ROW_NEW_MODIFIED){
				$operacion = 'INSERT';
			}
			elseif ($statuts == K_ROW_MODIFIED){
				$operacion = 'UPDATE';
			}
			$param = "'$operacion'
						,$cod_familia_producto
						,$nom_familia_producto
						, $cod_familia
						,'$cod_producto'
						, $orden";
					
			if (!$db->EXECUTE_SP($sp, $param)){
				return false;
			}
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++){
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
			
			$cod_familia_producto = $this->get_item($i, 'COD_FAMILIA_PRODUCTO', 'delete');
			
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_familia_producto")){
				return false;
			}
		}
		return true;
	}
}

/*
Clase : WI_FAMILIA_WEB
*/
class wi_familia_web extends w_input {
	function wi_familia_web($cod_item_menu) {
		parent::w_input('familia_web', $cod_item_menu);
		
		$sql = "SELECT COD_FAMILIA
					  ,NOM_FAMILIA
					  ,NOM_PUBLICO
					  ,ECONOLINE
					  ,ES_SUBFAMILIA
					  ,'' DISPLAY_SUBFAMILIA
				FROM FAMILIA
				WHERE COD_FAMILIA = {KEY1}
				ORDER BY COD_FAMILIA";
		
		$this->dws['wi_familia_web'] = new datawindow($sql);

		$this->dws['wi_familia_web']->add_control(new edit_text('NOM_FAMILIA', 100, 100));
		$this->dws['wi_familia_web']->add_control(new edit_text('NOM_PUBLICO', 100, 100));
		$this->dws['wi_familia_web']->add_control(new edit_check_box('ECONOLINE', 'S', 'N'));
		$this->dws['wi_familia_web']->add_control($control = new edit_check_box('ES_SUBFAMILIA', 'S', 'N'));
		$control->set_onChange("es_subfamilia();");
		//$this->dws['wi_familia_web']->add_control(new static_text('DISPLAY_SUBFAMILIA'));
		$this->dws['wi_familia_web']->set_mandatory('COD_FAMILIA', 'Codigo de Familia');
		$this->dws['wi_familia_web']->set_mandatory('NOM_FAMILIA', 'Nombre de Familia');
		$this->dws['dw_familia_producto'] = new dw_familia_producto();
		$this->dws['dw_familia_accesorio'] = new dw_familia_accesorio();
		$this->dws['dw_familia_subfamilia'] = new dw_familia_subfamilia();
		
	}
	function get_key() {
		return $this->dws['wi_familia_web']->get_item(0, 'COD_FAMILIA');
	}
	function new_record() {
		$this->dws['wi_familia_web']->insert_row();
	}
	function load_record() {
		$cod_familia = $this->get_item_wo($this->current_record, 'COD_FAMILIA');
		$this->dws['wi_familia_web']->retrieve($cod_familia);
		$es_subfamilia = $this->dws['wi_familia_web']->get_item(0, 'ES_SUBFAMILIA');
		if($es_subfamilia == 'S'){
		$this->dws['wi_familia_web']->set_item(0, 'DISPLAY_SUBFAMILIA', "none");
		}
		
		
		
		$this->dws['dw_familia_producto']->retrieve($cod_familia);
		$this->dws['dw_familia_accesorio']->retrieve($cod_familia);
		$this->dws['dw_familia_subfamilia']->retrieve($cod_familia);
		
	
		
		
	}
	
	
	
	
	function save_record($db) {
		$COD_FAMILIA = $this->get_key();
		$NOM_FAMILIA = $this->dws['wi_familia_web']->get_item(0, 'NOM_FAMILIA');
		$ECONOLINE =   'N';
		$NOM_PUBLICO =   $this->dws['wi_familia_web']->get_item(0, 'NOM_PUBLICO');
		$ES_SUBFAMILIA =   $this->dws['wi_familia_web']->get_item(0, 'ES_SUBFAMILIA');
		
		$COD_FAMILIA = ($COD_FAMILIA=='') ? "null" : $COD_FAMILIA;		
		
		$sp = 'spu_familia_web';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    		    
	    $param	= "'$operacion', $COD_FAMILIA, '$NOM_FAMILIA','$ECONOLINE','$NOM_PUBLICO','$ES_SUBFAMILIA'";
		    
		if ($db->EXECUTE_SP($sp, $param)){
			
			if ($this->is_new_record()) {
				$COD_FAMILIA = $db->GET_IDENTITY();
				$this->dws['wi_familia_web']->set_item(0, 'COD_FAMILIA', $COD_FAMILIA);
			}

			for ($i = 0; $i < $this->dws['dw_familia_producto']->row_count(); $i++){
				$this->dws['dw_familia_producto']->set_item($i, 'COD_FAMILIA', $COD_FAMILIA);
			}
			
			for ($i = 0; $i < $this->dws['dw_familia_accesorio']->row_count(); $i++){
				$this->dws['dw_familia_accesorio']->set_item($i, 'COD_FAMILIA', $COD_FAMILIA);
			}
			
			for ($i = 0; $i < $this->dws['dw_familia_subfamilia']->row_count(); $i++){
				$this->dws['dw_familia_subfamilia']->set_item($i, 'COD_FAMILIA', $COD_FAMILIA);
			}
			
			if (!$this->dws['dw_familia_producto']->update($db))
				return false;
				
			if (!$this->dws['dw_familia_accesorio']->update($db))
				return false;
				
			if (!$this->dws['dw_familia_subfamilia']->update($db))
				return false;				
				
			return true;
		}
		return false;		
	}
}
?>