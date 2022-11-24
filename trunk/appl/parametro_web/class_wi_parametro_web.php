<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wi_parametro_web extends w_input {
	function wi_parametro_web() 	{
		$this->tiene_wo = false;
		parent::w_input('parametro_web', $_REQUEST['cod_item_menu']);
						
		$sql = "SELECT		dbo.f_get_parametro(57) ROW_PER_PAGE ,
							dbo.f_get_parametro(58) CANT_PAGE_VISIBLE";
									 
		$this->dws['dw_parametro'] = new datawindow($sql);
		
		$this->dws['dw_parametro']->add_control(new edit_num('ROW_PER_PAGE',10,10));
		$this->dws['dw_parametro']->add_control(new edit_num('CANT_PAGE_VISIBLE',10,10));
		
		$this->save_SESSION();
		$this->need_redraw();
		header("Location: wi_parametro_web.php"); // para borrra el REQUEST
	}
	function new_record() {				
		$this->dws['dw_parametro']->insert_row();
	}	
	function load_record() 	{
		$this->current_record = 0;
		$this->dws['dw_parametro']->retrieve();
	}
	/*
	function habilitar(&$temp, $habilita) { 
		//*************  dejar solo el perfil 1, usar consulta aBD
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        $sql = "SELECT COD_PERFIL from USUARIO where COD_USUARIO =".$this->cod_usuario;
        $result = $db->build_results($sql);
        $cod_perfil = $result[0]['COD_PERFIL'];
         
		if ($cod_perfil == 1) //perfil administrados siempre tendrá privilegios de escritura
			$this->habilita_boton($temp, 'modify', (true));
		else
			$this->habilita_boton($temp, 'modify', (false));
	} 
*/
	
	function get_key() 	{
		return 0;
	}

	function save_record($db) {
		$row_per_page	 	= $this->dws['dw_parametro']->get_item(0, 'ROW_PER_PAGE');
		$cant_page_visible 	= $this->dws['dw_parametro']->get_item(0, 'CANT_PAGE_VISIBLE');

		$sp = 'spu_parametro_web';
		$param = "'$row_per_page'
				 ,'$cant_page_visible'";

		if ($db->EXECUTE_SP($sp,$param))
			return true;
		else
			return false;
	}

	// Se reimplementa para que no se ejecute codigo respecto a los navegadores
	function navegacion(&$temp) 	{
		$temp->setVar("WI_RUTA_MENU", $this->ruta_menu);
		$temp->setVar("WI_FECHA_ACTUAL", 'Fecha Actual: ' . $this->current_date());
		$key = $this->limpia_key($this->get_key());
		$temp->setVar("WI_FECHA_MODIF", '');		
		$this->habilita_boton($temp, 'back', true);
	}
	function goto_list() 	{
		$this->unlock_record();
		header('Location:' . $this->root_url . '../../commonlib/trunk/php/presentacion.php');		
	}
	function procesa_event(){		
		if (session::is_set('REDRAW_' . $this->nom_tabla)) {
			session::un_set('REDRAW_' . $this->nom_tabla);
			$this->load_record();
			$this->redraw();
		} 
		else
			parent::procesa_event();
	}
}
?>