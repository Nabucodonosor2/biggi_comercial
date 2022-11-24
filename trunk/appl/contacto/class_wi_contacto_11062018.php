<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class dw_contacto_telefono extends datawindow {

	function dw_contacto_telefono() {

		$sql_telefono = "SELECT TELEFONO
						,CT.COD_CONTACTO_TELEFONO	
						 FROM CONTACTO_TELEFONO CT, CONTACTO C 
						 WHERE CT.COD_CONTACTO = C.COD_CONTACTO 
						 		AND  CT.COD_CONTACTO = {KEY1}";	

		parent::datawindow($sql_telefono, 'CONTACTO_TELEFONO', true, true);

		$this->add_control(new edit_text('TELEFONO', 15, 100));
		$this->add_control(new edit_text('COD_CONTACTO_TELEFONO', 10, 10, 'hidden'));

		$this->set_mandatory('TELEFONO', 'Teléfono');
	}
	function update($db, $cod_contacto)	{
		$sp = 'spu_contacto_telefono';

		for ($i = 0; $i < $this->row_count(); $i++)		{
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$cod_contacto_telefono  = $this->get_item($i, 'COD_CONTACTO_TELEFONO');
			$telefono				= $this->get_item($i, 'TELEFONO');

			$cod_contacto_telefono = ($cod_contacto_telefono=='') ? "null" : $cod_contacto_telefono;

			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';

			$param = "'$operacion',$cod_contacto_telefono,$cod_contacto, '$telefono'";

			if (!$db->EXECUTE_SP($sp, $param))
				return false;
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;

			$cod_contacto_telefono = $this->get_item($i, 'COD_CONTACTO_TELEFONO', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_contacto_telefono"))
				return false;
		}

		return true;
	}
}	
class dw_contacto_persona extends datawindow {
	function dw_contacto_persona() {

		$sql = "SELECT COD_CONTACTO_PERSONA
					,NOM_PERSONA
					,MAIL
					,CARGO
					,DBO.F_CONTACTO_TELEFONO (COD_CONTACTO_PERSONA,1) TELEFONO1
					,DBO.F_CONTACTO_TELEFONO (COD_CONTACTO_PERSONA,2) TELEFONO2
					,DBO.F_CONTACTO_TELEFONO (COD_CONTACTO_PERSONA,3) TELEFONO3
				FROM CONTACTO_PERSONA
				WHERE COD_CONTACTO = {KEY1}";	

		parent::datawindow($sql, 'CONTACTO_PERSONA', true, true);

		$this->add_control(new edit_text('COD_CONTACTO_PERSONA', 10, 10, 'hidden'));
		$this->add_control(new edit_text_upper('NOM_PERSONA', 35, 100));
		$this->add_control(new edit_mail('MAIL', 40));
		$this->add_control(new edit_text_upper('CARGO', 35, 100));
		$this->add_control(new edit_text('TELEFONO1', 15, 100));
		$this->add_control(new edit_text('TELEFONO2', 15, 100));
		$this->add_control(new edit_text('TELEFONO3', 15, 100));

		$this->set_mandatory('NOM_PERSONA', 'Nombre Persona');

	}

	function update($db, $cod_contacto)	{
		$sp = 'spu_contacto_persona';

		for ($i = 0; $i < $this->row_count(); $i++)		{
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$cod_contacto_persona = $this->get_item($i, 'COD_CONTACTO_PERSONA');
			$nom_persona		= $this->get_item($i, 'NOM_PERSONA');
			$mail				= $this->get_item($i, 'MAIL');
			$cargo				= $this->get_item($i, 'CARGO');
			$telefono1			= $this->get_item($i, 'TELEFONO1');
			$telefono2 			= $this->get_item($i, 'TELEFONO2');
			$telefono3 			= $this->get_item($i, 'TELEFONO3');

			$cod_contacto_persona = ($cod_contacto_persona=='') ? "null" : $cod_contacto_persona;
			$mail = ($mail=='') ? "null" : "'$mail'";
			$cargo = ($cargo=='') ? "null" : "'$cargo'";
			$telefono1 = ($telefono1=='') ? "null" : "'$telefono1'";
			$telefono2 = ($telefono2=='') ? "null" : "'$telefono2'";
			$telefono3 = ($telefono3=='') ? "null" : "'$telefono3'";

			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';

			$param = "'$operacion', $cod_contacto_persona,$cod_contacto,'$nom_persona', $mail, $cargo, $telefono1, $telefono2, $telefono3";
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;

			$cod_contacto_persona = $this->get_item($i, 'COD_CONTACTO_PERSONA', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_contacto_persona"))
				return false;
		}

		return true;
	}

}

class wi_contacto extends w_input {
	function wi_contacto($cod_item_menu) {
		parent::w_input('contacto', $cod_item_menu);
		$sql = "SELECT COD_CONTACTO
					,NOM_CONTACTO
					,RUT
					,DIG_VERIF
					,DIRECCION
					,COD_CIUDAD
					,COD_COMUNA
				FROM CONTACTO
				WHERE COD_CONTACTO = {KEY1}";

		$this->dws['dw_contacto'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_contacto']->add_control(new static_num('COD_CONTACTO'));
		$this->dws['dw_contacto']->add_control(new edit_text_upper('NOM_CONTACTO', 80, 100));
		$this->dws['dw_contacto']->add_control(new edit_num('RUT', 10, 10));
		$this->dws['dw_contacto']->add_control(new edit_num('DIG_VERIF', 1, 1));
		$this->dws['dw_contacto']->add_control(new edit_text_upper('DIRECCION', 80, 100));

		$sql_ciudad= "SELECT 	COD_CIUDAD, 
								NOM_CIUDAD
					FROM 		CIUDAD
					ORDER BY 	NOM_CIUDAD";
		$this->dws['dw_contacto']->add_control($control = new drop_down_dw('COD_CIUDAD', $sql_ciudad, 90));	
		$control->set_drop_down_dependiente('contacto', 'COD_COMUNA');

		$sql_comuna = "select 	COD_COMUNA, 
								NOM_COMUNA
						from 	COMUNA
						where 	COD_CIUDAD = {KEY1}
					order by 	NOM_COMUNA";
		$this->dws['dw_contacto']->add_control(new drop_down_dw('COD_COMUNA', $sql_comuna, 90));

		// asigna los mandatorys
		$this->dws['dw_contacto']->set_mandatory('NOM_CONTACTO', 'Razón Social');

		$this->dws['dw_contacto_persona'] = new dw_contacto_persona();
		$this->dws['dw_contacto_telefono'] = new dw_contacto_telefono();

	}
	function new_record() {
		$this->dws['dw_contacto']->insert_row();
	}
	function load_record() {
		$cod_contacto = $this->get_item_wo($this->current_record, 'COD_CONTACTO');
		$this->dws['dw_contacto']->retrieve($cod_contacto);

		$this->dws['dw_contacto_persona']->retrieve($cod_contacto);
		$this->dws['dw_contacto_telefono']->retrieve($cod_contacto);
		
		if (!session::is_set("contacto_desde_output")){
			session::un_set("contacto_desde_output");
			$this->b_modify_visible = false;
		}else{
			session::un_set("contacto_desde_output");
			$this->b_modify_visible = true;
		}		

		//si el contacto es llamado desde el modulo llamado no son accesible los datos de la empresa
		/*if (!session::is_set("contacto_desde_output")) {

			session::un_set("contacto_desde_output");
			$this->dws['dw_contacto']->set_entrable('NOM_CONTACTO', false);
			$this->dws['dw_contacto']->set_entrable('DIRECCION', false);
			$this->dws['dw_contacto']->set_entrable('COD_CIUDAD', false);
			$this->dws['dw_contacto']->set_entrable('COD_COMUNA', false);
		}
		$this->dws['dw_contacto']->set_entrable('RUT', false);
		$this->dws['dw_contacto']->set_entrable('DIG_VERIF', false);*/

	}
	function get_key() {
		return $this->dws['dw_contacto']->get_item(0, 'COD_CONTACTO');
	}

	function save_record($db) {
		$cod_contacto = $this->get_key();
		$nom_contacto = $this->dws['dw_contacto']->get_item(0, 'NOM_CONTACTO');
		$rut 		= $this->dws['dw_contacto']->get_item(0, 'RUT');
		$dig_verif	= $this->dws['dw_contacto']->get_item(0, 'DIG_VERIF');
		$direccion 	= $this->dws['dw_contacto']->get_item(0, 'DIRECCION');
		$cod_ciudad	= $this->dws['dw_contacto']->get_item(0, 'COD_CIUDAD');
		$cod_comuna = $this->dws['dw_contacto']->get_item(0, 'COD_COMUNA');

		$cod_contacto = ($cod_contacto=='') ? "null" : $cod_contacto;
		$rut = ($rut=='') ? "null" : $rut;
		$dig_verif = ($dig_verif=='') ? "null" : "'$dig_verif'";
		$direccion = ($direccion=='') ? "null" : "'$direccion'";
		$cod_ciudad = ($cod_ciudad=='') ? "null" : $cod_ciudad;
		$cod_comuna = ($cod_comuna=='') ? "null" : $cod_comuna;

		$sp = 'spu_contacto';
		if ($this->is_new_record())
			$operacion = 'INSERT';
		else
			$operacion = 'UPDATE';

		$param	= "'$operacion', $cod_contacto, '$nom_contacto', $rut, $dig_verif, $direccion, $cod_ciudad, $cod_comuna";

		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_contacto = $db->GET_IDENTITY();
				$this->dws['dw_contacto']->set_item(0, 'COD_CONTACTO', $cod_contacto);
			}
			$this->dws['dw_contacto_persona']->update($db, $cod_contacto);
			$this->dws['dw_contacto_telefono']->update($db, $cod_contacto);
			return true;
		}
		return false;
	}
}
?>