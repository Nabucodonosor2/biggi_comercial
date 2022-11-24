<?php
class header_usuario_bitacora extends  header_drop_down {
	function header_usuario_bitacora($field, $field_bd, $nom_header) {
		$sql = "SELECT DISTINCT U.COD_USUARIO, U.NOM_USUARIO 
				FROM BITACORA_FACTURA B, USUARIO U 
				WHERE B.COD_USUARIO = U.COD_USUARIO 
				ORDER BY NOM_USUARIO";
		parent::header_drop_down($field, $field_bd, $nom_header, $sql);
	}
	function make_java_script() {
		return '"return dlg_find_bitacora(\''.$this->nom_header.'\', \''.$this->valor_filtro.'\', \''.$this->sql.'\', this);"';		
	}
}
?>