<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class w_param_informe_biggi extends w_param_informe {
	function w_param_informe_biggi($nom_tabla, $cod_item_menu, $nom_informe, $xml, $sql_informe, $sp='') {
		parent::w_param_informe($nom_tabla, $cod_item_menu, $nom_informe, $xml, $sql_informe, $sp);
	}
	function make_filtro() {
		$this->filtro = '';
		$this->filtro2 = '';
	}
	function genera_pdf($labels = array(), $con_logo = true,$orientation='P',$unit='pt',$format='letter') {
		$labels['str_filtro'] = $this->filtro;
		$labels['str_filtro2'] = $this->filtro2;
		$rpt = new reporte_biggi($this->sql_informe, $this->xml, $labels, $this->nom_informe, $con_logo, true, $this->sp, $this->param,$orientation,$unit,$format);
	}
}
?>