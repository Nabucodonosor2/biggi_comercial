<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_vendedor.php");

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
		
		// headers
		$this->add_header(new header_num('COD_GUIA_RECEPCION', 'COD_GUIA_RECEPCION', 'C�digo'));
		$this->add_header($control = new header_date('FECHA_GUIA_RECEPCION', 'FECHA_GUIA_RECEPCION', 'Fecha'));
		$control->field_bd_order = 'DATE_GUIA_RECEPCION';
		$this->add_header(new header_rut('RUT', 'E', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Cliente'));
		$sql_estado_gr = "select COD_ESTADO_GUIA_RECEPCION, NOM_ESTADO_GUIA_RECEPCION from ESTADO_GUIA_RECEPCION order by COD_ESTADO_GUIA_RECEPCION";
		$this->add_header(new header_drop_down('NOM_ESTADO_GUIA_RECEPCION', 'EGR.COD_ESTADO_GUIA_RECEPCION', 'Estado', $sql_estado_gr));
		$sql_tipo_gr = "select COD_TIPO_GUIA_RECEPCION, NOM_TIPO_GUIA_RECEPCION from TIPO_GUIA_RECEPCION order by COD_TIPO_GUIA_RECEPCION";
		$this->add_header(new header_drop_down('NOM_TIPO_GUIA_RECEPCION', 'TGR.COD_TIPO_GUIA_RECEPCION', 'Tipo Doc.', $sql_tipo_gr));
		
		$this->add_header(new header_vendedor('INI_USUARIO', 'GR.COD_USUARIO_RESPONSABLE', 'Emisor'));
		/*$sql = "SELECT NULL COD_USUARIO_RESPONSABLE
					  ,'SIN ASIGNAR' NOM_USUARIO
				UNION
				SELECT COD_USUARIO COD_USUARIO_RESPONSABLE
					 ,NOM_USUARIO 
				FROM USUARIO 
				WHERE VENDEDOR_VISIBLE_FILTRO = 1 
				ORDER BY COD_USUARIO_RESPONSABLE ASC";
		$this->add_header(new header_drop_down('NOM_USUARIO', 'COD_USUARIO_RESPONSABLE', 'Responsable', $sql));*/
		$this->add_header(new header_text('COD_NOTA_VENTA', "dbo.f_get_nro_nota_venta(GR.COD_DOC, GR.TIPO_DOC, GR.COD_GUIA_RECEPCION, 'S')", 'Nota Venta'));
	}
}
?>