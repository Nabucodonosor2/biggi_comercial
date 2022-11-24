<?php
class header_usu_por_fac_bo extends  header_drop_down {
	function header_usu_por_fac_bo($field, $field_bd, $nom_header) {
		$sql = "SELECT DISTINCT COD_USUARIO_VENDEDOR,NOM_USUARIO 
				FROM inf_oc_por_facturar_bodega 
				order by COD_USUARIO_VENDEDOR";
		parent::header_drop_down($field, $field_bd, $nom_header, $sql);
	}
	function make_java_script() {
		return '"return dlg_find_usu_por_fac_bo(\''.$this->nom_header.'\', \''.$this->valor_filtro.'\', \''.$this->sql.'\', this);"';		
	}
}
?>