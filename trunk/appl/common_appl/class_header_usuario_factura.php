<?php
class header_usuario_factura extends  header_drop_down {
	function header_usuario_factura($field, $field_bd, $nom_header) {
		$sql = "SELECT DISTINCT U.COD_USUARIO, U.NOM_USUARIO 
				FROM FACTURA F, USUARIO U 
				WHERE F.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO ORDER BY NOM_USUARIO";
		parent::header_drop_down($field, $field_bd, $nom_header, $sql);
	}
	function make_java_script() {
		return '"return dlg_find_factura(\''.$this->nom_header.'\', \''.$this->valor_filtro.'\', \''.$this->sql.'\', this);"';		
	}
}
?>