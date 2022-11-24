<?php
class header_usu_autoriza extends  header_drop_down {
	function header_usu_autoriza($field, $field_bd, $nom_header) {
		$sql = "select COD_USUARIO, NOM_USUARIO 
      			from USUARIO 
      			where AUTORIZA_INGRESO = 'S' 
      			order by COD_USUARIO";
		parent::header_drop_down($field, $field_bd, $nom_header, $sql);
	}
	function make_java_script() {
		return '"return dlg_find_usu_autoriza(\''.$this->nom_header.'\', \''.$this->valor_filtro.'\', \''.$this->sql.'\', this);"';		
	}
}
?>