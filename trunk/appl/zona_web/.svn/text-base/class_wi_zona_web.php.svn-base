<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class dw_familia_zona_web extends datawindow{
	function dw_familia_zona_web(){
		$sql = "SELECT COD_ZONA_FAMILIA
							,COD_ZONA_FAMILIA COD_ZONA_FAMILIA_H
						   ,COD_ZONA COD_ZONA_H
						   ,COD_FAMILIA
						   ,ORDEN
					FROM ZONA_FAMILIA
					WHERE COD_ZONA = {KEY1}
				ORDER BY	ORDEN ASC";
		
		parent::datawindow($sql, 'FAMILIA_ZONA_WEB', true, true);
		$this->add_control(new edit_num('ORDEN', 10));
		
		$sql = "SELECT COD_FAMILIA,
						NOM_FAMILIA
					FROM FAMILIA 
					ORDER BY NOM_FAMILIA";
		$this->add_control(new drop_down_dw('COD_FAMILIA', $sql,300));
		$this->add_control(new edit_text_upper('COD_ZONA_FAMILIA_H',10, 10, 'hidden'));
		
		// asigna los mandatorys
		$this->set_mandatory('ORDEN', 'Orden');
		$this->set_mandatory('NOM_FAMILIA', 'Familia');

		// Setea el focus en NOM_FAMILIA para las nuevas lineas
		$this->set_first_focus('NOM_FAMILIA');
	}
	function insert_row($row = -1){
		$row = parent::insert_row($row);
		$this->set_item($row, 'ORDEN', $this->row_count() * 10);
		return $row;
	}
	function update($db,$cod_zona){
		
		$sp = 'spu_zona_familia_web';
			
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}
			$cod_zona_familia = $this->get_item($i, 'COD_ZONA_FAMILIA_H');
			//$cod_zona = $this->get_item(0, 'COD_ZONA_H');
			$cod_familia = $this->get_item($i, 'COD_FAMILIA');
			
			$orden = $this->get_item($i, 'ORDEN');
			
			$cod_zona_familia = ($cod_zona_familia == '') ? "null" : $cod_zona_familia;

			if ($statuts == K_ROW_NEW_MODIFIED){
				$operacion = 'INSERT';
			}
			elseif ($statuts == K_ROW_MODIFIED){
				$operacion = 'UPDATE';
			}
			
			$param = "'$operacion'
						,$cod_zona_familia
						,$cod_zona
						,$cod_familia
						, $orden";
						
			
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
			$cod_zona_familia = $this->get_item($i, 'COD_ZONA_FAMILIA', 'delete');
			
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_zona_familia")){
				return false;
			}
		}
		
		return true;
	}
}

/*
Clase : WI_ZONA_WEB
*/
class wi_zona_web extends w_input {
	function wi_zona_web($cod_item_menu) {
		parent::w_input('zona_web', $cod_item_menu);
		
		$sql = "SELECT	Z.COD_ZONA ,
						Z.NOM_ZONA,
						Z.ECONOLINE,
						Z.ORDEN Z_ORDEN,
						Z.PUBLICA_WEB
				FROM ZONA Z 
				WHERE  Z.COD_ZONA  = {KEY1}
				ORDER BY COD_ZONA";
		
		$this->dws['dw_zona_web'] = new datawindow($sql);

		$this->dws['dw_zona_web']->add_control(new edit_text('NOM_ZONA', 100, 100));
		$this->dws['dw_zona_web']->add_control(new edit_num('Z_ORDEN'));
		$this->dws['dw_zona_web']->add_control(new edit_check_box('ECONOLINE', 'S', 'N'));
		$this->dws['dw_zona_web']->add_control(new edit_check_box('PUBLICA_WEB', 'S', 'N'));
		
		$this->dws['dw_familia_zona_web'] = new dw_familia_zona_web();
		
		$this->dws['dw_zona_web']->set_mandatory('COD_ZONA', 'Codigo de Zona');
		$this->dws['dw_zona_web']->set_mandatory('NOM_ZONA', 'Nombre de Zona');
	}
	function get_key() {
		return $this->dws['dw_zona_web']->get_item(0, 'COD_ZONA');
	}
	function new_record() {
		$this->dws['dw_zona_web']->insert_row();
		//$this->dws['dw_zona_web']->add_control(new edit_num('COD_ZONA',10,10));		
	}
	function load_record() {
		$cod_zona = $this->get_item_wo($this->current_record, 'COD_ZONA');

		$this->dws['dw_zona_web']->retrieve($cod_zona);
		$this->dws['dw_familia_zona_web']->retrieve($cod_zona);
	}
	function save_record($db) {
		$COD_ZONA = $this->get_key();
		$NOM_ZONA = $this->dws['dw_zona_web']->get_item(0, 'NOM_ZONA');
		$ORDEN =   $this->dws['dw_zona_web']->get_item(0, 'Z_ORDEN');
		$ECONOLINE = $this->dws['dw_zona_web']->get_item(0, 'ECONOLINE');
		$PUBLICA_WEB = $this->dws['dw_zona_web']->get_item(0, 'PUBLICA_WEB');

		
		$COD_ZONA = ($COD_ZONA=='') ? "null" : $COD_ZONA;		
    
		$sp = 'spu_zona_web';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    		    
	    $param	= "'$operacion', $COD_ZONA, '$NOM_ZONA',$ORDEN,'$ECONOLINE','$PUBLICA_WEB'";
		    
		if ($db->EXECUTE_SP($sp, $param)){
			
			if ($this->is_new_record()) {
				
				$sql_key = "select max(COD_ZONA) COD_ZONA
							from ZONA";
				$result = $db->build_results($sql_key);
				$COD_ZONA = $result[0]['COD_ZONA'];
				$this->dws['dw_zona_web']->set_item(0, 'COD_ZONA', $COD_ZONA);
			}
			
			if (!$this->dws['dw_familia_zona_web']->update($db,$COD_ZONA))
				return false;
				
			return true;
		}
		return false;		
				
	}
	
}

?>