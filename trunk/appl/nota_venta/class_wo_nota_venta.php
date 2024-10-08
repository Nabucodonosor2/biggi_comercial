<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_vendedor.php");

class wo_nota_venta extends w_output_biggi {
	const K_ESTADO_EMITIDA 				= 1;	
	const K_ESTADO_CERRADA				= 2;
	const K_ESTADO_ANULADA				= 3;
	const K_ESTADO_CONFIRMADA			= 4;
	const K_PARAM_NOM_EMPRESA 			= 6;
	const K_PARAM_DIR_EMPRESA 			= 10;
	const K_PARAM_TEL_EMPRESA 			= 11;
	const K_PARAM_FAX_EMPRESA 			= 12;
	const K_PARAM_MAIL_EMPRESA 			= 13;
	const K_PARAM_CIUDAD_EMPRESA		= 14;
	const K_PARAM_PAIS_EMPRESA 			= 15; 
	const K_PARAM_GTE_VTA 				= 16;
	const K_PARAM_RUT_EMPRESA 			= 20;
	const K_PARAM_SITIO_WEB_EMPRESA		= 25;
	const K_PARAM_PORC_DSCTO_MAX 		= 26;
	const K_PARAM_RANGO_DOC_NOTA_VENTA	= 27;
	const K_AUTORIZA_CIERRE 		 	= '991005';
	const K_CAMBIA_DSCTO_CORPORATIVO	= '991010';
	const K_AUTORIZA_SUMAR				= '991095';
	var $checkbox_sumar;
	
	function wo_nota_venta(){
		$this->checkbox_sumar = false;
		
		// Llama a w_base para que $this->cod_usuario contenga al usuario actual 
		parent::w_base('nota_venta', $_REQUEST['cod_item_menu']);
		$sql = "select		COD_NOTA_VENTA
							/*INI_CAMPOS*/
							,convert(varchar(20), FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA
							,FECHA_NOTA_VENTA DATE_NOTA_VENTA		
							,RUT
							,DIG_VERIF
							,NOM_EMPRESA
							,REFERENCIA
							,INI_USUARIO				
							,NOM_ESTADO_NOTA_VENTA
							,TOTAL_NETO
							,NRO_ORDEN_COMPRA
							,NV.COD_USUARIO_VENDEDOR1
							,ENV.COD_ESTADO_NOTA_VENTA		
							,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO') MONTO_DSCTO_CORPORATIVO
							,TOTAL_NETO - dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO') VENTA_NETA_FINAL
							,(((TOTAL_NETO - dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO')) *
							 dbo.f_get_parametro_porc('GF', isnull(NV.FECHA_NOTA_VENTA, getdate())))/100) MONTO_GASTO_FIJO
							,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'SUM_OC_TOTAL') SUM_OC_TOTAL
							,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'RESULTADO') RESULTADO
							,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DIRECTORIO') MONTO_DIRECTORIO
							,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_V1') COMISION_V1
							,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_ADM') COMISION_ADM
							,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'REMANENTE') REMANENTE
							,dbo.f_header_porc_facturado(NV.COD_NOTA_VENTA) PORC_FACTURADO
							,dbo.f_nv_porc_facturado(NV.COD_NOTA_VENTA) PORC_FACTURADO_S
							,Round((dbo.f_nv_total_pago(NV.COD_NOTA_VENTA) / TOTAL_CON_IVA) * 100, 1) PORC_PAGOS_MH

							,CASE NV.COD_ESTADO_NOTA_VENTA
								WHEN 3 THEN 0
								ELSE
									ROUND(dbo.f_nv_despachado_neto(NV.COD_NOTA_VENTA), 0)
							END DESPACHADO_NETO_MH

							,dbo.f_nv_tipo_venta_nv(NV.COD_NOTA_VENTA) TIPO_VENTA_NV

							/*FIN_CAMPOS*/
				from 		NOTA_VENTA NV,
							EMPRESA E,
							USUARIO U,
							ESTADO_NOTA_VENTA ENV
				where		NV.COD_EMPRESA = E.COD_EMPRESA
							and NV.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO
							and NV.COD_ESTADO_NOTA_VENTA = ENV.COD_ESTADO_NOTA_VENTA
							and dbo.f_get_tiene_acceso (".$this->cod_usuario.", 'NOTA_VENTA',COD_USUARIO_VENDEDOR1, COD_USUARIO_VENDEDOR2) = 1 
							/*FIN_CONDICION*/
				order by	COD_NOTA_VENTA desc";
		
		parent::w_output_biggi('nota_venta', $sql, $_REQUEST['cod_item_menu']);
		
		$this->dw->add_control(new edit_nro_doc('COD_NOTA_VENTA', 'NOTA_VENTA' ));
		$this->dw->add_control(new edit_precio('TOTAL_NETO'));
		$this->dw->add_control(new static_num('RUT'));

		// headers
		$this->add_header(new header_num('COD_NOTA_VENTA', 'COD_NOTA_VENTA', 'N� NV'));
		$this->add_header($control = new header_date('FECHA_NOTA_VENTA', 'FECHA_NOTA_VENTA', 'Fecha'));
		$control->field_bd_order = 'DATE_NOTA_VENTA';
		$this->add_header(new header_rut('RUT', 'E', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Raz�n Social'));
		$this->add_header(new header_text('REFERENCIA', 'REFERENCIA', 'Referencia'));
		
		$this->add_header(new header_vendedor('INI_USUARIO', 'NV.COD_USUARIO_VENDEDOR1', 'V1'));
		
		$this->add_header(new header_text('NRO_ORDEN_COMPRA', 'NRO_ORDEN_COMPRA', 'Nro OC'));
		  
		$sql_nv = "select COD_ESTADO_NOTA_VENTA ,NOM_ESTADO_NOTA_VENTA from ESTADO_NOTA_VENTA order by ORDEN";
		$this->add_header(new header_drop_down('NOM_ESTADO_NOTA_VENTA', 'ENV.COD_ESTADO_NOTA_VENTA', 'Estado', $sql_nv));
		$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));
		
		$cod_usuario = session::get('COD_USUARIO');
		
		//headers para el excel especial => solo a administraci�n
		if($cod_usuario == 1){
			$this->add_header(new header_num('MONTO_DSCTO_CORPORATIVO', 'MONTO_DSCTO_CORPORATIVO', 'Descto. Corporativo '));
			$this->add_header(new header_num('VENTA_NETA_FINAL', 'VENTA_NETA_FINAL', 'Venta Neta Final'));
			$this->add_header(new header_num('MONTO_GASTO_FIJO', 'MONTO_GASTO_FIJO', 'Gasto Fijo'));
			$this->add_header(new header_num('SUM_OC_TOTAL', 'SUM_OC_TOTAL', 'Compra Neta Total'));
			$this->add_header(new header_num('RESULTADO', 'RESULTADO', 'Resultado'));
			$this->add_header(new header_num('MONTO_DIRECTORIO', 'MONTO_DIRECTORIO', 'Aporte a Administracion'));
			$this->add_header(new header_num('COMISION_V1', 'COMISION_V1', 'PParticipacion'));
			$this->add_header(new header_num('COMISION_ADM', 'COMISION_ADM', 'Participacion Administracion'));
			$this->add_header(new header_num('REMANENTE', 'REMANENTE', 'Remanente'));
			$this->add_header(new header_num('PORC_FACTURADO_S', 'PORC_FACTURADO_S', 'PORCENTAJE FACTURADO'));
			$this->add_header(new header_num('PORC_PAGOS_MH', 'PORC_PAGOS_MH', 'PORC PAGO CLIENTE'));
			$this->add_header(new header_num('DESPACHADO_NETO_MH', 'DESPACHADO_NETO_MH', 'MONTO NETO DESPACHADO'));
		}	
		
		$sql = "SELECT 0 PORC_FACTURADO,
					  'Por Facturar' NOM_PORC_FACTURADO
				UNION 
				SELECT 1 PORC_FACTURADO,
					   'Facturado' NOM_PORC_FACTURADO";
		$this->add_header($control = new header_drop_down_string('PORC_FACTURADO', 'dbo.f_header_porc_facturado(NV.COD_NOTA_VENTA)', '% Facturado', $sql));
		
		$sql22 = "SELECT  'Normal',
					  'Venta Normal' NOM_TIPO_VENTA_NV
					UNION 
					SELECT 'Sala Venta' TIPO_VENTA_NV,
					   'Sala Venta' NOM_TIPO_VENTA_NV
					UNION
					SELECT 'Venta Web' TIPO_VENTA_NV,
					   'Venta Web' NOM_TIPO_VENTA_NV";
		$this->add_header($control = new header_drop_down_string('TIPO_VENTA_NV', 'dbo.f_nv_tipo_venta_nv(NV.COD_NOTA_VENTA)', 'Tipo NV', $sql22));
		

		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_SUMAR, $this->cod_usuario);
		if ($priv=='E')
			$DISPLAY_SUMAR = '';
      	else
			$DISPLAY_SUMAR = 'none';

		$sql = "select '$DISPLAY_SUMAR' DISPLAY_SUMAR
						,'N' CHECK_SUMAR
					   ,'N' HIZO_CLICK";
		$this->dw_check_box = new datawindow($sql);
		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_SUMAR','S','N'));
		$control->set_onClick("sumar(); document.getElementById('loader').style.display='';");
		$this->dw_check_box->add_control(new edit_text_hidden('HIZO_CLICK'));
		$this->dw_check_box->retrieve();
    }
	
	function redraw_item(&$temp, $ind, $record){
		parent::redraw_item($temp, $ind, $record);

		$COD_ESTADO_NOTA_VENTA = $this->dw->get_item($record, 'COD_ESTADO_NOTA_VENTA');
		$TIPO_VENTA_NV = $this->dw->get_item($record, 'TIPO_VENTA_NV');

		if($COD_ESTADO_NOTA_VENTA == 3)//Anulada
			$temp->setVar("wo_registro.WO_COLOR_CSS", 'red');
		else
			if($TIPO_VENTA_NV == 'Venta Web')
				$temp->setVar("wo_registro.WO_COLOR_CSS", '#044bf4');
			else
				$temp->setVar("wo_registro.WO_COLOR_CSS", '');
	}

	function crear_nv_from_cot($vl_result) {
		$array = explode("|", $vl_result);
		$cod_cotizacion = $array[0];
		$que_precio_usa = $array[1];
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT dbo.f_get_tiene_acceso (".$this->cod_usuario.", 'COTIZACION',C.COD_USUARIO_VENDEDOR1, C.COD_USUARIO_VENDEDOR2) TIENE_ACCESO
						,C.COD_ESTADO_COTIZACION
				FROM COTIZACION C 
				WHERE C.COD_COTIZACION = $cod_cotizacion";
		$result = $db->build_results($sql);
		if (count($result) == 0){
			$this->_redraw();
			$this->alert('La Cotizaci�n N� '.$cod_cotizacion.' no existe.');								
			return;
		}
		else if ($result[0]['TIENE_ACCESO']==0){
			$this->_redraw();
			$this->alert('Ud. no tiene acceso a a Cotizaci�n N� '.$cod_cotizacion);								
			return;
		}
		else if ($result[0]['COD_ESTADO_COTIZACION'] == 5){ //RECHAZADA
			session::set('CREADA_DESDE_COTIZACION_COD_RECHAZADA', 6);
		}

		session::set('NV_CREADA_DESDE', $vl_result);	//pasar los dos datos y usarlo en la otra parte
		$this->add();
	}

	function make_menu(&$temp){
		$menu = session::get('menu_appl');
		$menu->ancho_completa_menu = 410;
		$menu->draw($temp);
		$menu->ancho_completa_menu = 209;
	}

	function redraw($temp){
		parent::redraw($temp);
		$this->dw_check_box->habilitar($temp, true);
	}

	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_nv_from_cot($_POST['wo_hidden']);
		else if ($_POST['HIZO_CLICK_0'] == 'S') {
			$this->checkbox_sumar = isset($_POST['CHECK_SUMAR_0']);
			
			// obtiene los datos del filtro aplicado
			$valor_filtro = $this->headers['TOTAL_NETO']->valor_filtro;
			$valor_filtro2 = $this->headers['TOTAL_NETO']->valor_filtro2;
			
			if ($this->checkbox_sumar) {
				$this->dw_check_box->set_item(0, 'CHECK_SUMAR', 'S');
				$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto', 0, true, 'SUM'));
			}
			else{
				$this->dw_check_box->set_item(0, 'CHECK_SUMAR', 'N');
				$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));  
			}

			// vuelve a setear el filtro aplicado
			$this->headers['TOTAL_NETO']->valor_filtro = $valor_filtro;
			$this->headers['TOTAL_NETO']->valor_filtro2 = $valor_filtro2;
			
			$this->save_SESSION();	
			$this->make_filtros();
			$this->retrieve();	
		}else{
			$this->checkbox_sumar = 0;
			parent::procesa_event();
		}
	}
}
?>