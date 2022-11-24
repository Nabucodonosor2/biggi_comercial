<?php
class header_usuario extends  header_drop_down {
	function header_usuario($field, $field_bd, $nom_header) {
		$sql = "SELECT COD_USUARIO, NOM_USUARIO 
				FROM USUARIO
				WHERE VENDEDOR_VISIBLE_FILTRO = 1
				ORDER BY COD_USUARIO";
		parent::header_drop_down($field, $field_bd, $nom_header, $sql);
	}
	function make_java_script() {
		return '"return dlg_find_usuario(\''.$this->nom_header.'\', \''.$this->valor_filtro.'\', \''.$this->sql.'\', this);"';		
	}
}
?>