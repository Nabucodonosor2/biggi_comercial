<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_vendedor.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_ingreso_pago extends w_output_biggi {
	const K_AUTORIZA_SUMAR = '992515';
	var $checkbox_sumar;

   	function wo_ingreso_pago(){
   		$this->checkbox_sumar = false;
   	
		$cod_usuario_auxip = session::get("COD_USUARIO");
		
		
	if(($cod_usuario_auxip <> 13) && ($cod_usuario_auxip <> 12) && ($cod_usuario_auxip <> 17))  {
	
		$sql = "SELECT 	IP.COD_INGRESO_PAGO
						,convert(varchar(20), IP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
						,IP.FECHA_INGRESO_PAGO DATE_INGRESO_PAGO
						,RUT
						,DIG_VERIF
						,E.NOM_EMPRESA
						,EIP.NOM_ESTADO_INGRESO_PAGO
						,EIP.COD_ESTADO_INGRESO_PAGO
						,dbo.f_ingreso_pago_get_saldo_output(IP.COD_INGRESO_PAGO) MONTO_DOC
						,dbo.f_ingreso_pago_get_cant_doc(IP.COD_INGRESO_PAGO) CANT_DOC
						,IP.NOM_TIPO_ORIGEN_PAGO
                        ,U.INI_USUARIO
						,IP.COD_USUARIO
				FROM 	INGRESO_PAGO IP, EMPRESA E, ESTADO_INGRESO_PAGO EIP, USUARIO U
				WHERE 	IP.COD_EMPRESA = E.COD_EMPRESA AND
						IP.COD_ESTADO_INGRESO_PAGO = EIP.COD_ESTADO_INGRESO_PAGO AND
                        U.COD_USUARIO = IP.COD_USUARIO
						ORDER BY COD_INGRESO_PAGO DESC";
	}else{	
		// SI EL USUARIO ES AR (13) y EO (12) y PV() SE FILTRA EL OUTPUT PARA QUE NO PUEDA VER LAS IP DE VENTAS DE SUS CLIENTES REALIZADAS POR OTROS VENDEDORES SOLICITA AS Y SP 14-12-2021
		$sql = "SELECT 	IP.COD_INGRESO_PAGO
						,convert(varchar(20), IP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
						,IP.FECHA_INGRESO_PAGO DATE_INGRESO_PAGO
						,RUT
						,DIG_VERIF
						,E.NOM_EMPRESA
						,EIP.NOM_ESTADO_INGRESO_PAGO
						,EIP.COD_ESTADO_INGRESO_PAGO
						,dbo.f_ingreso_pago_get_saldo_output(IP.COD_INGRESO_PAGO) MONTO_DOC
						,dbo.f_ingreso_pago_get_cant_doc(IP.COD_INGRESO_PAGO) CANT_DOC
						,IP.NOM_TIPO_ORIGEN_PAGO
                        ,U.INI_USUARIO
						,IP.COD_USUARIO
				FROM 	INGRESO_PAGO IP, EMPRESA E, ESTADO_INGRESO_PAGO EIP, USUARIO U
				WHERE 	IP.COD_EMPRESA = E.COD_EMPRESA AND
						IP.COD_ESTADO_INGRESO_PAGO = EIP.COD_ESTADO_INGRESO_PAGO AND
                        U.COD_USUARIO = IP.COD_USUARIO
						AND dbo.f_get_cod_v1_ip(IP.COD_INGRESO_PAGO, $cod_usuario_auxip) = $cod_usuario_auxip
						ORDER BY COD_INGRESO_PAGO DESC";
	}	
			
   		parent::w_output_biggi('ingreso_pago', $sql, $_REQUEST['cod_item_menu']);

   		$this->dw->add_control(new edit_nro_doc('COD_INGRESO_PAGO','INGRESO_PAGO'));
		$this->dw->add_control(new edit_precio('MONTO_DOC'));
		$this->dw->add_control(new static_num('RUT'));
   		
		// headers 
		$this->add_header(new header_num('COD_INGRESO_PAGO', 'COD_INGRESO_PAGO', 'Código'));
		$this->add_header($control = new header_date('FECHA_INGRESO_PAGO', 'FECHA_INGRESO_PAGO', 'Fecha '));
		$control->field_bd_order = 'DATE_INGRESO_PAGO';
		$this->add_header(new header_rut('RUT', 'E', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Cliente'));
		$sql_estado_ip = "select COD_ESTADO_INGRESO_PAGO, NOM_ESTADO_INGRESO_PAGO from ESTADO_INGRESO_PAGO order by	COD_ESTADO_INGRESO_PAGO";
      	$this->add_header(new header_drop_down('NOM_ESTADO_INGRESO_PAGO', 'EIP.COD_ESTADO_INGRESO_PAGO', 'Estado', $sql_estado_ip));
		$this->add_header($control = new header_num('CANT_DOC', '(dbo.f_ingreso_pago_get_cant_doc(IP.COD_INGRESO_PAGO))', 'Cant. Doc.'));
		$control->field_bd_order = 'CANT_DOC';
		$this->add_header($control = new header_num('MONTO_DOC','(dbo.f_ingreso_pago_get_saldo_output(IP.COD_INGRESO_PAGO))', 'Monto Doc.'));  
		$control->field_bd_order = 'MONTO_DOC';
		
		$sql = "select 'MANUAL' COD_TIPO_ORIGEN_PAGO
						,'MANUAL' NOM_TIPO_ORIGEN_PAGO
				UNION 
				select 'WEBPAY PLUS' COD_TIPO_ORIGEN_PAGO
						,'WEBPAY PLUS' NOM_TIPO_ORIGEN_PAGO";
        $this->add_header(new header_drop_down_string('NOM_TIPO_ORIGEN_PAGO', 'IP.NOM_TIPO_ORIGEN_PAGO', 'Tipo IP', $sql));
		$this->add_header(new header_vendedor('INI_USUARIO', 'IP.COD_USUARIO', 'Emisor'));
        //$this->add_header(new header_text('INI_USUARIO', 'INI_USUARIO', 'Emisor'));
        
        // dw checkbox
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

		$COD_ESTADO_INGRESO_PAGO = $this->dw->get_item($record, 'COD_ESTADO_INGRESO_PAGO');

		if($COD_ESTADO_INGRESO_PAGO == 3)//Anulada
			$temp->setVar("wo_registro.WO_COLOR_CSS", 'red');
		else
			$temp->setVar("wo_registro.WO_COLOR_CSS", '');	
	}

	function make_menu(&$temp){
		$menu = session::get('menu_appl');
		$menu->ancho_completa_menu = 286;
		$menu->draw($temp);
		$menu->ancho_completa_menu = 209;
	}

	function redraw(&$temp){
		parent::redraw($temp);
		$this->dw_check_box->habilitar($temp, true);
	}
	
	function procesa_event() {		
		if($_POST['HIZO_CLICK_0'] == 'S'){
			$this->checkbox_sumar = isset($_POST['CHECK_SUMAR_0']);
			
			// obtiene los datos del filtro aplicado
			$valor_filtro = $this->headers['MONTO_DOC']->valor_filtro;
			$valor_filtro2 = $this->headers['MONTO_DOC']->valor_filtro2;
			
			if($this->checkbox_sumar){
				$this->dw_check_box->set_item(0, 'CHECK_SUMAR', 'S');
				$this->add_header($control = new header_num('MONTO_DOC', '(dbo.f_ingreso_pago_get_saldo_output(IP.COD_INGRESO_PAGO))', 'Monto Doc.', 0, true, 'SUM'));
				$control->field_bd_order = 'MONTO_DOC';
			}
			else{
				$this->dw_check_box->set_item(0, 'CHECK_SUMAR', 'N');
				$this->add_header($control = new header_num('MONTO_DOC', '(dbo.f_ingreso_pago_get_saldo_output(IP.COD_INGRESO_PAGO))', 'Monto Doc.'));
				$control->field_bd_order = 'MONTO_DOC';  
			}

			// vuelve a setear el filtro aplicado
			$this->headers['MONTO_DOC']->valor_filtro = $valor_filtro;
			$this->headers['MONTO_DOC']->valor_filtro2 = $valor_filtro2;
			
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