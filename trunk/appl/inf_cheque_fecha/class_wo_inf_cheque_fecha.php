<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
include(dirname(__FILE__)."/../../appl.ini");

class wo_inf_cheque_fecha extends w_informe_pantalla{
   const K_AUTORIZA_EXPORTA_EXCEL	 = '995005';
   var $fecha;
   var $cod_usuario;
   function wo_inf_cheque_fecha(){
   		// El cod_usuario debe leerese desde la sesion porque no se puede usar $this->cod_usuario
   		//   hasta despues de llamar al ancestro
   		  
   		$cod_usuario =  session::get("COD_USUARIO");
   		
   		$db 	= new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$fecha	=	"select convert (varchar ,dbo.f_makedate(day(getdate()), month(getdate()), year(getdate())),103) FECHA";
		$result = $db->build_results($fecha);
		$fecha1 = $this->str2date($result[0]['FECHA']);
		
		$this->fecha = $fecha1;
		$this->cod_usuario = $cod_usuario;
		
		$db->EXECUTE_SP("spi_cheque_a_fecha", "$fecha1, $cod_usuario");
		$sql = "SELECT COD_INF_CHEQUE_FECHA
						,substring (I.COD_NOTA_VENTA,1,11) COD_NOTA_VENTA
						,I.COD_NOTA_VENTA COD_NOTA_VENTA_H
						,I.NOM_EMPRESA
						,I.RUT
						,I.COD_INGRESO_PAGO
						,I.FECHA_DOC DATE_FECHA_DOC
						,CONVERT(VARCHAR, I.FECHA_DOC, 103) FECHA_DOC
						,I.NRO_DOC
						,I.MONTO_DOC
						,0 CANT_DOC
						,NULL SELECCION
						,COD_DOC_INGRESO_PAGO
						,I.COD_BANCO
						,B.NOM_BANCO
				FROM INF_CHEQUE_FECHA I
					,BANCO B
				WHERE COD_USUARIO = $cod_usuario
				AND I.COD_BANCO = B.COD_BANCO
				ORDER BY COD_INF_CHEQUE_FECHA";

		parent::w_informe_pantalla('inf_cheque_fecha', $sql, $_REQUEST['cod_item_menu']);
		
		$this->dw->add_control(new edit_text_hidden('COD_NOTA_VENTA_H'));
		$this->dw->add_control(new edit_text_hidden('COD_DOC_INGRESO_PAGO'));
		$this->dw->entrable = true;
		$this->dw->add_control(new edit_check_box('SELECCION', 'S', 'N'));
		// headers
		$this->add_header(new header_text('COD_NOTA_VENTA', 'I.COD_NOTA_VENTA', 'N° NV'));
		$this->add_header(new header_text('NOM_EMPRESA', "I.NOM_EMPRESA", 'Cliente'));
		$this->add_header(new header_text('RUT', "I.RUT", 'Rut'));
		$this->add_header(new header_num('COD_INGRESO_PAGO', 'I.COD_INGRESO_PAGO', 'N° IP'));
		$this->add_header($header = new header_date('FECHA_DOC', 'FECHA_DOC', 'Fecha'));
		$header->field_bd_order = 'DATE_FECHA_DOC';	
		$this->add_header(new header_num('NRO_DOC', 'I.NRO_DOC', 'Número'));
		$this->add_header(new header_num('MONTO_DOC', 'I.MONTO_DOC', 'Monto', 0, true, 'SUM'));
		$sql = "SELECT COD_BANCO, NOM_BANCO FROM BANCO order by	ORDEN";
		$this->add_header(new header_drop_down('NOM_BANCO', 'I.COD_BANCO', 'Banco', $sql));

		$this->dw->add_control(new static_num('MONTO_DOC'));

		//$header->valor_filtro = $this->current_date();
		//$this->make_filtros();
   }
	function print_informe(){
		// reporte
		$sql = $this->dw->get_sql();
		$xml = session::get('K_ROOT_DIR').'appl/inf_cheque_fecha/inf_cheque_fecha.xml';
		$labels = array();
		$labels['str_fecha'] = $this->current_date();
		$labels['str_filtro'] = $this->nom_filtro;
		$rpt = new reporte($sql, $xml, $labels, "INFORME CHEQUES A FECHA", true);

		$this->_redraw();
	}
	function detalle_record($rec_no){
		session::set('DESDE_wo_ingreso_pago', 'desde output');	// para indicar que viene del output
		session::set('DESDE_wo_inf_cheque_fecha', 'true');
		$ROOT = $this->root_url;
		$url = $ROOT.'appl/ingreso_pago';
		header ('Location:'.$url.'/wi_ingreso_pago.php?rec_no='.$rec_no.'&cod_item_menu=2505');
	}
	function redraw(&$temp){
		parent::redraw($temp);
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_EXPORTA_EXCEL, $this->cod_usuario);
		if($priv == 'E'){
			$this->habilita_boton($temp, 'b_export', true);	
		}else{
			$this->habilita_boton($temp, 'b_export', false);	
		}
	}
	function paginacion(&$temp){
		parent::paginacion($temp);
		$temp->setVar("CANT_DOC", $this->row_count_output);
		$temp->setVar("CANT_REG_H", '<input id="CANT_REG_H_0" class="input_text" type="hidden" maxlength="100" size="100" value="'.$this->row_count_output.'" name="CANT_REG_H_0">');
		//
		$this->habilita_boton($temp, 'change_date_deposit', true);
	}
	
	function habilita_boton(&$temp, $boton, $habilita){
		parent::habilita_boton($temp, $boton, $habilita);
		if($boton == 'change_date_deposit')
		if($habilita){
			$ruta_over = "'../../images_appl/b_change_date_deposit_over.jpg'";
			$ruta_out = "'../../images_appl/b_change_date_deposit.jpg'";
			$ruta_click = "'../../images_appl/b_change_date_deposit_click.jpg'";
			$temp->setVar("WO_".strtoupper($boton), '<input name="b_'.$boton.'" id="b_'.$boton.'" type="button" onmouseover="entrada(this, '.$ruta_over.')" onmouseout="salida(this, '.$ruta_out.')" onmousedown="down(this, '.$ruta_click.')"'.
											'style="cursor:pointer;height:68px;width:66px;border: 0;background-image:url(../../images_appl/b_'.$boton.'.jpg);background-repeat:no-repeat;background-position:center;border-radius: 15px;"'.
											'onClick="request_fecha_ingreso(\'Cambio de Fecha Depósito\',\'\');" />');
		}else
			$temp->setVar("WO_".strtoupper($boton), '<img src="'.$ruta_imag.'b_'.$boton.'_d.jpg"/>');
		
	}
	
	function change_date_ingreso($ve_fecha){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$dia = substr($ve_fecha,0,2);
		$mes = substr($ve_fecha,3,2);
		$ano = substr($ve_fecha,6,4);
		
		$sql = "SELECT CONVERT(VARCHAR, DBO.F_MAKEDATE($dia, $mes, $ano), 103) FECHA_VALIDAR";
		$result = $db->build_results($sql);
		
		if($ve_fecha != $result[0]['FECHA_VALIDAR']){
			$this->_redraw();
			$this->alert('Ha ingresado una fecha no válida');
			return;
		}
		
		$ve_fecha = $this->str2date($ve_fecha);
		$this->dw->get_values_from_POST();
		$sp = 'spu_inf_cheque_fecha';
		
		$db->BEGIN_TRANSACTION();
		$error = false;
		// primer registro de la pagina
		$ind = $this->row_per_page * ($this->current_page - 1);		
		// loop en los registros de la pagina visible
		$i = 0;
		while (($i < $this->row_per_page) && ($ind < $this->row_count_output)){
			$seleccion = $this->dw->get_item($i, 'SELECCION');
			if ($seleccion=='S'){
				$cod_doc_ingreso_pago = $this->dw->get_item($i, 'COD_DOC_INGRESO_PAGO');
				$param = "'CAMBIAR_FECHA', $cod_doc_ingreso_pago, $ve_fecha";
	    		if (!$db->EXECUTE_SP($sp, $param)) {
	    			$error = true;
					$db->ROLLBACK_TRANSACTION();
					$error_sp = $db->GET_ERROR();
					$this->alert('No se pudo grabar el registro.\n\n'.$db->make_msg_error_bd());
					break;
	    		}
    		}

    		$i++;
			$ind++;
		}
		if (!$error) 
			$db->COMMIT_TRANSACTION();
		
		$db->EXECUTE_SP("spi_cheque_a_fecha", "$this->fecha, $this->cod_usuario");

		$this->headers['FECHA_DOC']->valor_filtro = $this->current_date();
			
		$this->save_SESSION();	
		$this->make_filtros();
		$this->retrieve();
	}
	
	function change_date_ingreso_es($ve_valores){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$vl_record = explode('|', $ve_valores);
		
		$vl_record[3] = $this->str2date($vl_record[3]);
		
		$param = "'CAMBIAR_FECHA_UNO', NULL, $vl_record[3], $vl_record[1], $vl_record[2]";
		$db->EXECUTE_SP("spu_inf_cheque_fecha", $param);
		
		$db->EXECUTE_SP("spi_cheque_a_fecha", "$this->fecha, $this->cod_usuario");
		$this->headers['FECHA_DOC']->valor_filtro = $this->current_date();
				
		$this->make_filtros();
		$this->retrieve();
	}
	
	function procesa_event() {
		if(isset($_POST['b_change_date_deposit_x'])){
			$vl_record = explode('|', $_POST['wo_hidden']);
			if($vl_record[0] == 'ESPECIFICO')
				$this->change_date_ingreso_es($_POST['wo_hidden']);
			else
				$this->change_date_ingreso($_POST['wo_hidden']);
		}else
			parent::procesa_event();	
	}
}
?>