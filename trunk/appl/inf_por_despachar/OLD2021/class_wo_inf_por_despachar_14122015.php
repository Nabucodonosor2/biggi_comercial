<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_modelo.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_vendedor.php");

class wo_inf_por_despachar extends w_informe_pantalla {
   function wo_inf_por_despachar() {
   		/* El cod_usuario debe leerese desde la sesion porque no se puede usar $this->cod_usuario
   		   hasta despues de llamar al ancestro
   		 */  
   		$cod_usuario =  session::get("COD_USUARIO");
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->EXECUTE_SP("spi_por_despachar", "$cod_usuario"); 
   		
   		$sql = "select I.COD_NOTA_VENTA
						,convert(varchar(20), I.FECHA_NOTA_VENTA, 3) FECHA_NOTA_VENTA
						,I.FECHA_NOTA_VENTA DATE_NOTA_VENTA
						,I.NOM_EMPRESA
						,I.INI_USUARIO
						,I.ITEM
						,I.COD_PRODUCTO
						,I.NOM_PRODUCTO
						,I.CANTIDAD
						,I.CANTIDAD_POR_DESPACHAR
						,1 CANTIDAD_LINEA
						,convert (varchar(20) ,I.FECHA_ENTREGA, 3) FECHA_ENTREGA
						,I.DIAS_ATRASO
						,I.COD_USUARIO_VENDEDOR1
				from	INF_POR_DESPACHAR I
				where I.COD_USUARIO = $cod_usuario
				order by I.COD_NOTA_VENTA, I.ITEM";   		
		
		parent::w_informe_pantalla('inf_por_despachar', $sql, $_REQUEST['cod_item_menu']);
		
		// headers
		$this->add_header(new header_num('COD_NOTA_VENTA', 'I.COD_NOTA_VENTA', 'NV'));
		$this->add_header($control = new header_date('FECHA_NOTA_VENTA', 'I.FECHA_NOTA_VENTA', 'Fecha'));
		$control->field_bd_order = 'DATE_NOTA_VENTA';
		$this->add_header(new header_text('NOM_EMPRESA', "I.NOM_EMPRESA", 'Cliente'));
		//$sql = "select distinct U.COD_USUARIO, U.NOM_USUARIO from NOTA_VENTA NV, USUARIO U where NV.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO order by NOM_USUARIO";
		$this->add_header(new header_vendedor('INI_USUARIO', 'I.COD_USUARIO_VENDEDOR1', 'V1'));
		$this->add_header(new header_text('ITEM', "I.ITEM", 'Item'));
		$this->add_header(new header_modelo('COD_PRODUCTO', "I.COD_PRODUCTO", 'Modelo'));
		$this->add_header(new header_text('NOM_PRODUCTO', "I.NOM_PRODUCTO", 'Producto'));
		$this->add_header(new header_num('CANTIDAD', 'I.CANTIDAD', 'Cant', 0, true, 'SUM'));
		$this->add_header(new header_num('CANTIDAD_POR_DESPACHAR', 'I.CANTIDAD_POR_DESPACHAR', 'x Desp', 0, true, 'SUM'));
		$this->add_header(new header_num('CANTIDAD_LINEA', '1', '', 0, true, 'SUM'));
		$this->add_header(new header_date('FECHA_ENTREGA', 'I.FECHA_ENTREGA', 'Fecha_NV'));
   }
	function print_informe() {
		$cod_usuario = $_POST['wo_hidden'];

		// reporte
		//$sql = "exec spr_por_despachar $cod_usuario";
		$sql = $this->dw->get_sql();
		// selecciona xml
		if ($cod_usuario==0)
			$xml = session::get('K_ROOT_DIR').'appl/inf_por_despachar/inf_por_despachar_global.xml';
		else
			$xml = session::get('K_ROOT_DIR').'appl/inf_por_despachar/inf_por_despachar.xml';
		$labels = array();
		$labels['str_fecha'] = $this->current_date();
		$rpt = new reporte($sql, $xml, $labels, "Por despachar", true);
		
		$this->_redraw();
	}
}
?>