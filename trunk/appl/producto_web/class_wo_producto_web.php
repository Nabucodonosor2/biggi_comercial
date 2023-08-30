<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_text_rango.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
include(dirname(__FILE__)."/../../appl.ini");

class wo_producto_web extends w_output_biggi{
	function wo_producto_web(){

		$sql = "SELECT FP.COD_PRODUCTO
						,Z.NOM_ZONA
						,F.NOM_PUBLICO
						,CASE
							WHEN P.PUBLICA_WEB_NEW_VENTA IS NULL then 'N'
							ELSE P.PUBLICA_WEB_NEW_VENTA
						END PUBLICA_WEB
						,P.PRECIO_VENTA_PUBLICO
						,P.LARGO
						,P.ANCHO
						,P.ALTO
						,P.PESO
						,Z.COD_ZONA
						,F.COD_FAMILIA
				FROM FAMILIA_PRODUCTO FP
					,FAMILIA F
					,ZONA_FAMILIA ZF
					,ZONA Z
					,PRODUCTO P
				WHERE FP.COD_FAMILIA = F.COD_FAMILIA
				AND F.COD_FAMILIA = ZF.COD_FAMILIA
				AND ZF.COD_ZONA = Z.COD_ZONA
				AND FP.COD_PRODUCTO = P.COD_PRODUCTO
				ORDER BY Z.COD_ZONA, F.COD_FAMILIA, FP.COD_PRODUCTO";

		parent::w_output_biggi('producto_web', $sql, $_REQUEST['cod_item_menu']);

		
		// headers
		$this->add_header(new header_text_rango('COD_PRODUCTO', 'FP.COD_PRODUCTO', 'Modelo'));
		$sql = "SELECT COD_ZONA
					 ,NOM_ZONA
				FROM ZONA
				ORDER BY ORDEN";
		$this->add_header($header = new header_drop_down('NOM_ZONA', 'Z.COD_ZONA', 'Zona', $sql));
		$sql = "SELECT COD_FAMILIA
					  ,NOM_PUBLICO
				FROM FAMILIA";
		$this->add_header($header = new header_drop_down('NOM_PUBLICO', 'F.COD_FAMILIA', 'Nombre', $sql));
		$sql = "SELECT 'S' PUBLICA_WEB,
						'S' NOM_PUBLICA_WEB
				UNION 
				SELECT 'N' PUBLICA_WEB,
						'N' NOM_PUBLICA_WEB";
		$this->add_header( new header_drop_down_string('PUBLICA_WEB', 'PUBLICA_WEB', 'Publica Web', $sql));
		$this->add_header(new header_num('PRECIO_VENTA_PUBLICO', 'PRECIO_VENTA_PUBLICO', 'Precio'));
		$this->add_header(new header_num('LARGO', 'LARGO', 'Largo'));
		$this->add_header(new header_num('ANCHO', 'ANCHO', 'Ancho'));
		$this->add_header(new header_num('ALTO', 'ALTO', 'Alto'));
		$this->add_header(new header_num('PESO', 'PESO', 'Peso'));

		$this->dw->add_control(new edit_num('PRECIO_VENTA_PUBLICO'));
	}

	function make_menu(&$temp){
		$menu = session::get('menu_appl');
		$menu->ancho_completa_menu = 410;
		$menu->draw($temp);
		$menu->ancho_completa_menu = 209;
	}

	function redraw_item(&$temp, $ind, $record) {
		parent::redraw_item($temp, $ind, $record);

		$PRECIO_VENTA_PUBLICO	= $this->dw->get_item($record, 'PRECIO_VENTA_PUBLICO');
		$LARGO					= $this->dw->get_item($record, 'LARGO');
		$ANCHO					= $this->dw->get_item($record, 'ANCHO');
		$ALTO					= $this->dw->get_item($record, 'ALTO');
		$PESO					= $this->dw->get_item($record, 'PESO');

		if($PRECIO_VENTA_PUBLICO == 0 || $LARGO == 0 || $ANCHO == 0 || $ALTO == 0 || $PESO == 0)
			$alert = "color: red; font-weight: bold;";
		else
			$alert = "";

		$temp->setVar("wo_registro.CSS_ALERT", $alert);
	}
}
?>