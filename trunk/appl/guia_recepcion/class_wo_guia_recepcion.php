<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_vendedor.php");

class static_gr extends static_text {
	function static_gr($field) {
		parent::static_text($field);
	}
	function draw_no_entrable($dato, $record) {
		$a = explode("|", $dato);
		if (count($a) > 1)
			return 'VARIOS';
		else
			return $a[0];
	}
	function draw_entrable($dato, $record) {
		return $this->draw_no_entrable($dato, $record);
	}
}



class wo_guia_recepcion extends w_output_biggi{
   	function wo_guia_recepcion(){
		$sql = "SELECT	GR.COD_GUIA_RECEPCION
						,convert(varchar(20),GR.FECHA_GUIA_RECEPCION, 103)FECHA_GUIA_RECEPCION
						,GR.FECHA_GUIA_RECEPCION DATE_GUIA_RECEPCION
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,EGR.COD_ESTADO_GUIA_RECEPCION
						,EGR.NOM_ESTADO_GUIA_RECEPCION
						,TGR.COD_TIPO_GUIA_RECEPCION
						,TGR.NOM_TIPO_GUIA_RECEPCION
						,GR.COD_USUARIO_RESPONSABLE
						,U.INI_USUARIO
						,dbo.f_get_nro_nota_venta(GR.COD_DOC, GR.TIPO_DOC, GR.COD_GUIA_RECEPCION, 'S') COD_NOTA_VENTA
						,dbo.f_get_modelo_gr(GR.COD_GUIA_RECEPCION) MODELO_GR
						,CASE
							WHEN GR_RESUELTA = 'S' THEN 'Resuelta'
							WHEN EGR.COD_ESTADO_GUIA_RECEPCION = 3 THEN 'Resuelta'
							ELSE 'No Resuelta'
						END GR_RESUELTA
				FROM	GUIA_RECEPCION GR LEFT OUTER JOIN USUARIO U ON GR.COD_USUARIO_RESPONSABLE = U.COD_USUARIO
						, EMPRESA E, ESTADO_GUIA_RECEPCION EGR, TIPO_GUIA_RECEPCION TGR 
				WHERE	GR.COD_EMPRESA = E.COD_EMPRESA AND
						isnull(GR.TIPO_DOC,'') NOT IN ('ARRIENDO', 'MOD_ARRIENDO') AND
						EGR.COD_ESTADO_GUIA_RECEPCION = GR.COD_ESTADO_GUIA_RECEPCION AND
						TGR.COD_TIPO_GUIA_RECEPCION = GR.COD_TIPO_GUIA_RECEPCION
						order by COD_GUIA_RECEPCION desc";		
	
   		parent::w_output_biggi('guia_recepcion', $sql, $_REQUEST['cod_item_menu']);

		$this->dw->add_control(new edit_precio('MONTO_DOCUMENTO'));
		$this->dw->add_control(new static_num('RUT'));
		$this->dw->add_control(new static_gr('MODELO_GR'));
		
		// headers
		$this->add_header(new header_num('COD_GUIA_RECEPCION', 'COD_GUIA_RECEPCION', 'Nº GR'));
		$this->add_header($control = new header_date('FECHA_GUIA_RECEPCION', 'FECHA_GUIA_RECEPCION', 'Fecha'));
		$control->field_bd_order = 'DATE_GUIA_RECEPCION';
		$this->add_header(new header_rut('RUT', 'E', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Cliente'));
		$sql_estado_gr = "select COD_ESTADO_GUIA_RECEPCION, NOM_ESTADO_GUIA_RECEPCION from ESTADO_GUIA_RECEPCION order by COD_ESTADO_GUIA_RECEPCION";
		$this->add_header(new header_drop_down('NOM_ESTADO_GUIA_RECEPCION', 'EGR.COD_ESTADO_GUIA_RECEPCION', 'Estado', $sql_estado_gr));
		$sql_tipo_gr = "select COD_TIPO_GUIA_RECEPCION, NOM_TIPO_GUIA_RECEPCION from TIPO_GUIA_RECEPCION order by COD_TIPO_GUIA_RECEPCION";
		$this->add_header(new header_drop_down('NOM_TIPO_GUIA_RECEPCION', 'TGR.COD_TIPO_GUIA_RECEPCION', 'Tipo GR', $sql_tipo_gr));
		
		$this->add_header(new header_vendedor('INI_USUARIO', 'GR.COD_USUARIO_RESPONSABLE', 'Responsable'));
		
		$this->add_header($header_nv = new header_text('COD_NOTA_VENTA', "dbo.f_get_nro_nota_venta(GR.COD_DOC, GR.TIPO_DOC, GR.COD_GUIA_RECEPCION, 'S')", 'Nº NV'));
		$header_nv->sorteable = false;
        $this->add_header($header = new header_text('MODELO_GR', "dbo.f_get_modelo_gr(GR.COD_GUIA_RECEPCION)", 'Modelo'));
		$header->sorteable = false;
		$sql = "SELECT 'S' GR_RESUELTA
						,'Resuelta' NOM_GR_RESUELTA
				UNION
				SELECT 'N' GR_RESUELTA
						,'No Resuelta' NOM_GR_RESUELTA";
		$this->add_header(new header_drop_down_string('GR_RESUELTA', "GR_RESUELTA", 'Estado Interno', $sql));
   	}
   	
   	function make_menu(&$temp) {
   	    $menu = session::get('menu_appl');
   	    $menu->ancho_completa_menu = 471;
   	    $menu->draw($temp);
   	    $menu->ancho_completa_menu = 209;    // volver a setear el tamaño original
   	}
}
?>