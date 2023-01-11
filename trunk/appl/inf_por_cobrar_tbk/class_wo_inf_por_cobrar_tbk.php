<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_inf_por_cobrar_tbk extends w_informe_pantalla {
    function wo_inf_por_cobrar_tbk() {
   		$cod_usuario =  session::get("COD_USUARIO");

		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->EXECUTE_SP("spi_inf_por_cobrar_tbk", "$cod_usuario");
   		$sql = "select COD_INF_POR_COBRAR_TBK
				,COD_INGRESO_PAGO
				,COD_DOC_INGRESO_PAGO
				,CONVERT(VARCHAR, FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
				,FECHA_INGRESO_PAGO DATE_FECHA_INGRESO_PAGO
				,COD_NOTA_VENTA
				,CONVERT(VARCHAR, FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA
				,FECHA_NOTA_VENTA DATE_FECHA_NOTA_VENTA
				,RUT_CLIENTE
				,DIG_VERIF
				,RAZON_SOCIAL
				,TOTAL_CON_IVA
				,MONTO_DEBITO
				,MONTO_CREDITO
				,CUOTAS_CREDITO
				,COMISION_DEBITO
				,COMISION_CREDITO
				,TOTAL_POR_COBRAR
				,MONTO_CUOTA_CREDITO
				,CUOTAS_PENDIENTES
			FROM INF_POR_COBRAR_TBK IPCT
			WHERE COD_USUARIO = $cod_usuario
			ORDER BY COD_INGRESO_PAGO DESC";
				
		parent::w_informe_pantalla('inf_por_cobrar_tbk', $sql, $_REQUEST['cod_item_menu']);

		$this->add_header(new header_num('COD_INGRESO_PAGO', 'COD_INGRESO_PAGO', 'IP'));
		$this->add_header($control = new header_date('FECHA_INGRESO_PAGO', 'FECHA_INGRESO_PAGO', 'F. IP'));
		$control->field_bd_order = 'DATE_FECHA_INGRESO_PAGO';
		$this->add_header(new header_num('COD_NOTA_VENTA', 'COD_NOTA_VENTA', 'NV'));
		$this->add_header($control = new header_date('FECHA_NOTA_VENTA', 'FECHA_NOTA_VENTA', 'F. NV'));
		$control->field_bd_order = 'DATE_FECHA_NOTA_VENTA';
		$this->add_header(new header_rut('RUT_CLIENTE', 'IPCT', 'Rut'));
		$this->add_header(new header_text('RAZON_SOCIAL', "RAZON_SOCIAL", 'Cliente'));
		$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'Total Con IVA NV'));
		$this->add_header(new header_num('MONTO_DEBITO', 'MONTO_DEBITO', 'Monto Débito', 0, true, 'SUM'));
		$this->add_header(new header_num('COMISION_DEBITO', 'COMISION_DEBITO', 'Comisión Débito'));
		$this->add_header(new header_num('MONTO_CREDITO', 'MONTO_CREDITO', 'Monto Crédito', 0, true, 'SUM'));
		$this->add_header(new header_num('COMISION_CREDITO', 'COMISION_CREDITO', 'Comisión Crédito'));
		$this->add_header(new header_num('CUOTAS_CREDITO', 'CUOTAS_CREDITO', 'Cuotas Crédito'));
		$this->add_header(new header_num('MONTO_CUOTA_CREDITO', 'MONTO_CUOTA_CREDITO', 'Monto Cuota Crédito'));
		$this->add_header(new header_num('CUOTAS_PENDIENTES', 'CUOTAS_PENDIENTES', 'Cuotas pendientes'));
		$this->add_header(new header_num('TOTAL_POR_COBRAR', 'TOTAL_POR_COBRAR', 'X Cobrar TBK', 0, true, 'SUM'));

		$this->dw->add_control(new static_num('RUT_CLIENTE'));
		$this->dw->add_control(new static_num('TOTAL_CON_IVA'));
		$this->dw->add_control(new static_num('MONTO_DEBITO'));
		$this->dw->add_control(new static_num('MONTO_CREDITO'));
		$this->dw->add_control(new static_num('CUOTAS_CREDITO'));
		$this->dw->add_control(new static_num('COMISION_DEBITO'));
		$this->dw->add_control(new static_num('COMISION_CREDITO'));
		$this->dw->add_control(new static_num('MONTO_CUOTA_CREDITO'));
		$this->dw->add_control(new static_num('CUOTAS_PENDIENTES'));
		$this->dw->add_control(new static_num('TOTAL_POR_COBRAR'));

		//Registros por pagina
		$this->row_per_page = 200;
   	}

	function make_menu(&$temp) {
		$menu = session::get('menu_appl');
		$menu->ancho_completa_menu = 475; //765
		$menu->draw($temp);
		$menu->ancho_completa_menu = 280;    // volver a setear el tamaño original
	}
}
?>