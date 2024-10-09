<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_vendedor.php");

ini_set('max_execution_time', 900); //900 seconds = 15 minutes

class header_num_miles extends  header_num {
	function header_num_miles($field, $field_bd, $nom_header, $cant_decimal=0, $solo_positivos=true, $operacion_accumulate='') {
		parent:: header_num($field, $field_bd, $nom_header, $cant_decimal, $solo_positivos, $operacion_accumulate);
	}
	function draw_valor_accumulate() {
		return number_format(round($this->valor_accumulate /1000, 0), 0, ',', '.');
	}
}

class static_num_miles extends static_num {
	function static_num_miles($field) {
		parent::static_num($field, 0);
	}
	function draw_no_entrable($dato, $record) {
		if ($dato!='')
			$dato = number_format(round($dato/1000, 0), 0, ',', '.');

		return $dato;		
	}
}

class wo_inf_ventas_por_mes_fa extends w_informe_pantalla{
	function wo_inf_ventas_por_mes_fa(){
   		// Construye el resultado del informe en un tabla AUXILIA de INFORME
		$ano = session::get("inf_ventas_por_mes_fa.ANO");
		session::un_set("inf_ventas_por_mes_fa.ANO");
		
		$cod_usuario = session::get("COD_USUARIO");;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->EXECUTE_SP("spi_ventas_por_mes_fa", "$cod_usuario, $ano");

		$sql = "select	COD_FACTURA
						,COD_FACTURA COD_FACTURA_H
						,MES
						,ANO
						,NOM_MES
						,FECHA_FACTURA
						,convert(varchar, FECHA_FACTURA, 103) FECHA_FACTURA_STR
						,NOM_EMPRESA 
						,COD_USUARIO_VENDEDOR1
						,INI_USUARIO
						,SUBTOTAL
						,TOTAL_NETO
						,PORC_DSCTO
						,MONTO_DSCTO
				FROM INF_VENTAS_POR_MES_FA
				where COD_USUARIO = $cod_usuario
				order by COD_FACTURA";

		parent::w_informe_pantalla('inf_ventas_por_mes_fa', $sql, $_REQUEST['cod_item_menu']);
		
		$this->dw->add_control(new edit_text_hidden('COD_FACTURA_H'));
		
		// headers
		$this->add_header($h_mes = new header_mes('MES', 'MES', 'Mes'));
		$this->add_header(new header_num('ANO', 'ANO', 'Año'));
		$this->add_header(new header_date('FECHA_FACTURA_STR', 'FECHA_FACTURA_STR', 'Fecha'));
		$this->add_header(new header_num('COD_FACTURA', 'COD_FACTURA', 'FA'));
		$this->add_header(new header_text('NOM_EMPRESA', "NOM_EMPRESA", 'Cliente'));
		$this->add_header(new header_vendedor('INI_USUARIO', 'COD_USUARIO_VENDEDOR1', 'V1'));
		$this->add_header(new header_num_miles('SUBTOTAL', 'SUBTOTAL', 'TVN s/d', 0, true, 'SUM'));
		$this->add_header(new header_num('PORC_DSCTO', 'PORC_DSCTO', '% D', 0));  		
		$this->add_header(new header_num_miles('MONTO_DSCTO', 'MONTO_DSCTO', 'Dscto', 0, true, 'SUM'));		
		$this->add_header(new header_num_miles('TOTAL_NETO', 'TOTAL_NETO', 'TVN c/d', 0, true, 'SUM'));
		
		$this->dw->add_control(new static_num_miles('SUBTOTAL'));
		$this->dw->add_control(new edit_porcentaje('PORC_DSCTO'));
		$this->dw->add_control(new static_num_miles('TOTAL_NETO'));
		$this->dw->add_control(new static_num_miles('TOTAL_VENTA'));
   		
   		// Filtro inicial
		$mes_desde = session::get("inf_ventas_por_mes_fa.MES_DESDE");
		$mes_hasta = session::get("inf_ventas_por_mes_fa.MES_HASTA");
		
		session::un_set("inf_ventas_por_mes_fa.MES_DESDE");
		session::un_set("inf_ventas_por_mes_fa.MES_HASTA");
		
		$h_mes->valor_filtro = $mes_desde;
		$h_mes->valor_filtro2 = $mes_hasta;
		
		$this->row_per_page = 500;
		$this->make_filtros();	// filtro incial
	}

   	function make_menu($temp) {
   		/*  MODIFICACION PARA USUARIO ANGEL SCIANCA, EN EL INFORME DE VENTAS SE ENCOJE EL TAMAÑO DEL MENU */   		
	   	$menu = session::get('menu_appl');
	    $menu->ancho_completa_menu = 1;
	    $menu->draw($temp);
	    $menu->ancho_completa_menu = 79;	    	    	    		
    }
	
	function redraw($temp) {
		$total_dscto = $this->headers['MONTO_DSCTO']->valor_accumulate;
		$temp->setVar('SUM_MONTO_DSCTO_TOTAL', number_format(round($total_dscto/1000,0), 0, ',', '.'));
		
		if ($this->headers['SUBTOTAL']->valor_accumulate==0)
			$porc_total = 0;
		else
			$porc_total = round((($this->headers['MONTO_DSCTO']->valor_accumulate) / $this->headers['SUBTOTAL']->valor_accumulate)* 100, 1);

		$temp->setVar('PORC_DSCTO_TOTAL', number_format($porc_total, 1, ',', '.'));
	}
}
?>