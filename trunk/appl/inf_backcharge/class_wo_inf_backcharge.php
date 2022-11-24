<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_vendedor.php");
include(dirname(__FILE__)."/../../appl.ini");

class wo_inf_backcharge extends w_output{
	function wo_inf_backcharge() {
		$fecha_ini = session::get("inf_backcharge.FECHA_INICIO");
		$fecha_fin = session::get("inf_backcharge.FECHA_TERMINO");
		session::un_set("inf_backcharge.FECHA_INICIO");
		session::un_set("inf_backcharge.FECHA_TERMINO");
		
		$fecha_ini = $this->str2date($fecha_ini);
		$fecha_fin = $this->str2date($fecha_fin, '23:59:59');
		
		$sql = "SELECT N.COD_NOTA_VENTA
					  ,CONVERT(VARCHAR, N.FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA
					  ,U.INI_USUARIO
					  ,U.COD_USUARIO
					  ,N.TOTAL_NETO
					  ,dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'VENTA_NETA') VENTA_NETA
					  ,dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'RESULTADO') RESULTADO
					  ,dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'PORC_RESULTADO') PORC_RESULTADO
					  ,N.PORC_VENDEDOR1 PORC_VENDEDOR
					  ,dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'COMISION_V1') COMISION_VENDEDOR
					  ,dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'BACKCHARGE') BACKCHARGE
					  ,CASE
					  		WHEN dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'COMISION_V1_BACKCHARGE') < 0 THEN 0
							ELSE dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'COMISION_V1_BACKCHARGE')
					  END COMISION_VEN_BACKCHARGE
					  ,CASE
					  	WHEN dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'COMISION_V1_BACKCHARGE') < 0
					  		THEN dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'COMISION_V1')
					  	ELSE dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'COMISION_V1') - 
					  		 dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'COMISION_V1_BACKCHARGE')
					  END DIF
				FROM NOTA_VENTA N, USUARIO U
				WHERE N.FECHA_NOTA_VENTA BETWEEN $fecha_ini AND $fecha_fin
				AND U.COD_USUARIO = N.COD_USUARIO_VENDEDOR1
				AND dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'BACKCHARGE') > 0
				ORDER BY COD_NOTA_VENTA DESC";
				
		parent::w_output('inf_backcharge', $sql, $_REQUEST['cod_item_menu']);
		
		$this->dw->add_control(new static_num('TOTAL_NETO'));
		$this->dw->add_control(new static_num('VENTA_NETA'));
		$this->dw->add_control(new static_num('RESULTADO'));
		$this->dw->add_control(new static_num('PORC_RESULTADO', 1));
		$this->dw->add_control(new static_num('PORC_VENDEDOR', 1));
		$this->dw->add_control(new static_num('COMISION_VENDEDOR'));
		$this->dw->add_control(new static_num('BACKCHARGE'));
		$this->dw->add_control(new static_num('COMISION_VEN_BACKCHARGE'));
		$this->dw->add_control(new static_num('DIF'));
		
		// headers
		$this->add_header(new header_num('COD_NOTA_VENTA', 'COD_NOTA_VENTA', 'NV'));
		$this->add_header(new header_date('FECHA_NOTA_VENTA', 'FECHA_NOTA_VENTA', 'Fecha'));
		$this->add_header(new header_vendedor('INI_USUARIO', 'U.COD_USUARIO', 'V1'));
		$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Neto NV', 0, true, 'SUM'));
		$this->add_header($control = new header_num('VENTA_NETA', "dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'VENTA_NETA')", 'Venta Neta', 0, true, 'SUM'));
		$control->field_bd_order = 'VENTA_NETA';
		$this->add_header($control = new header_num('RESULTADO', "dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'RESULTADO')", 'Resultado', 0, true, 'SUM'));
		$control->field_bd_order = 'RESULTADO';
		$this->add_header($control = new header_num('PORC_RESULTADO', "dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'PORC_RESULTADO')", '% Resultado', 0, true, 'SUM'));
		$control->field_bd_order = 'PORC_RESULTADO';
		$this->add_header($control = new header_num('PORC_VENDEDOR', "N.PORC_VENDEDOR1", '% Vendedor', 0, true, 'SUM'));
		$control->field_bd_order = 'PORC_VENDEDOR';
		$this->add_header($control = new header_num('COMISION_VENDEDOR', "dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'COMISION_V1')", 'Comision V1', 0, true, 'SUM'));
		$control->field_bd_order = 'COMISION_VENDEDOR';
		$this->add_header($control = new header_num('BACKCHARGE', "dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'BACKCHARGE')", 'Monto Backcharge', 0, true, 'SUM'));
		$control->field_bd_order = 'BACKCHARGE';
		$this->add_header($control = new header_num('COMISION_VEN_BACKCHARGE', "CASE
																			  		WHEN dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'COMISION_V1_BACKCHARGE') < 0 THEN 0
																					ELSE dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'COMISION_V1_BACKCHARGE')
																			  	END", 'Com. V1', 0, true, 'SUM'));
		$control->field_bd_order = 'COMISION_VEN_BACKCHARGE';
		$this->add_header($control = new header_num('DIF', "CASE
															  	WHEN dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'COMISION_V1_BACKCHARGE') < 0
															  		THEN dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'COMISION_V1')
															  	ELSE dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'COMISION_V1') - 
															  		 dbo.f_nv_get_resultado(N.COD_NOTA_VENTA, 'COMISION_V1_BACKCHARGE')
															END", 'Diferencia', 0, true, 'SUM'));
		$control->field_bd_order = 'DIF';
	}
	function detalle_record($rec_no) {
		session::set('DESDE_wo_nota_venta', 'desde output');	// para indicar que viene del output
		session::set('DESDE_wo_inf_backcharge', 'true');
		$ROOT = $this->root_url;
		$url = $ROOT.'appl/nota_venta';
		header ('Location:'.$url.'/wi_nota_venta.php?rec_no='.$rec_no.'&cod_item_menu=1510');
	}
}
?>