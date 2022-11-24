<?php
class header_usu_inf_factura extends  header_drop_down {
	function header_usu_inf_factura($field, $field_bd, $nom_header) {
		$sql = "select	distinct I.COD_USUARIO_VENDEDOR1 COD_USUARIO ,U.NOM_USUARIO 
				FROM INF_FACTURAS_POR_COBRAR I left outer join USUARIO U on U.COD_USUARIO = I.COD_USUARIO_VENDEDOR1 
				order by U.NOM_USUARIO";
		parent::header_drop_down($field, $field_bd, $nom_header, $sql);
	}
	function make_java_script() {
		return '"return dlg_find_usu_inf_factura(\''.$this->nom_header.'\', \''.$this->valor_filtro.'\', \''.$this->sql.'\', this);"';		
	}
}
?>