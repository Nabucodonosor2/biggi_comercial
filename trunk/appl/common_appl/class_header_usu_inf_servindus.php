<?php
class header_usu_inf_servindus extends  header_drop_down {
	function header_usu_inf_servindus($field, $field_bd, $nom_header) {
		$sql = "SELECT DISTINCT COD_USUARIO_VENDEDOR,NOM_USUARIO 
				FROM INF_OC_POR_FACTURAR_TDNX 
				order by COD_USUARIO_VENDEDOR";
		parent::header_drop_down($field, $field_bd, $nom_header, $sql);
	}
	function make_java_script() {
		return '"return dlg_find_usu_inf_servindus(\''.$this->nom_header.'\', \''.$this->valor_filtro.'\', \''.$this->sql.'\', this);"';		
	}
}
?>