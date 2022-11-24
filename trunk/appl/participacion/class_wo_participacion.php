<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_usuario.php");


class wo_participacion extends w_output_biggi {
  const K_AUTORIZA_SUMAR = '991535';
  var $checkbox_sumar;
  
   	function wo_participacion() {
      $this->checkbox_sumar = false;
		// Llama a w_base para que $this->cod_usuario contenga al usuario actual 
   		parent::w_base('orden_pago', $_REQUEST['cod_item_menu']);
   		$sql = "SELECT COD_PARTICIPACION
				      ,convert(varchar(20), FECHA_PARTICIPACION, 103) FECHA_PARTICIPACION
				      ,FECHA_PARTICIPACION DATE_PARTICIPACION
				      ,COD_USUARIO_VENDEDOR
					  ,U.NOM_USUARIO
				      ,P.COD_ESTADO_PARTICIPACION
					  ,E.NOM_ESTADO_PARTICIPACION
				      ,TIPO_DOCUMENTO
				      ,TOTAL_NETO
				FROM PARTICIPACION P, ESTADO_PARTICIPACION E, USUARIO U
				where P.COD_ESTADO_PARTICIPACION = E.COD_ESTADO_PARTICIPACION
					and U.COD_USUARIO = P.COD_USUARIO_VENDEDOR
					and dbo.f_get_tiene_acceso (".$this->cod_usuario.", 'PARTICIPACION',COD_USUARIO_VENDEDOR, null) = 1 
				order by COD_PARTICIPACION desc";		
			
   		parent::w_output_biggi('participacion', $sql, $_REQUEST['cod_item_menu']);

   		$this->dw->add_control(new edit_nro_doc('COD_PARTICIPACION','PARTICIPACION'));
		$this->dw->add_control(new edit_precio('TOTAL_NETO'));
   		
		// headers 
		$this->add_header(new header_num('COD_PARTICIPACION', 'COD_PARTICIPACION', 'Código'));
		$this->add_header($control = new header_date('FECHA_PARTICIPACION', 'FECHA_PARTICIPACION', 'Fecha '));
		$control->field_bd_order = 'DATE_PARTICIPACION';
		
      	$this->add_header(new header_usuario('NOM_USUARIO', 'COD_USUARIO_VENDEDOR', 'Vendedor'));
      	
      	$sql_estado_participacion = "select COD_ESTADO_PARTICIPACION, NOM_ESTADO_PARTICIPACION from ESTADO_PARTICIPACION order by COD_ESTADO_PARTICIPACION";
      	$this->add_header(new header_drop_down('NOM_ESTADO_PARTICIPACION', 'P.COD_ESTADO_PARTICIPACION', 'Estado', $sql_estado_participacion));
		
      	
      	$sql="select 'FA' COD_FACTURA
					,'FA' TIENE_COMPROMISO
				union
				select 'BH' COD_FACTURA
					,'BH' TIENE_COMPROMISO";
      	
      	$this->add_header(new header_drop_down_string('TIPO_DOCUMENTO', 'TIPO_DOCUMENTO', 'Tipo Docto.',$sql));
      	
		$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));
   
   // dw checkbox
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_SUMAR, $this->cod_usuario);
		if ($priv=='E') {
			$DISPLAY_SUMAR = '';
      	}
      	else {
			$DISPLAY_SUMAR = 'none';
      	}
		
		$sql = "select '$DISPLAY_SUMAR' DISPLAY_SUMAR
						,'N' CHECK_SUMAR
					   ,'N' HIZO_CLICK";
		$this->dw_check_box = new datawindow($sql);
		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_SUMAR','S','N'));
		$control->set_onClick("sumar(); document.getElementById('loader').style.display='';");
		$this->dw_check_box->add_control(new edit_text_hidden('HIZO_CLICK'));
		$this->dw_check_box->retrieve();
   
	}
	function detalle_record_desde($modificar, $cant_participacion_a_hacer) 
	{
		// No se llama al ancestro porque se reimplementa toda la rutina
		session::set("cant_participacion_a_hacer", $cant_participacion_a_hacer);

		// retrieve
		$this->set_count_output();
		$this->last_page = Ceil($this->row_count_output / $this->row_per_page);
		$this->set_current_page(0);
		$this->save_SESSION();

		$pag_a_mostrar=$cant_participacion_a_hacer -1;

		$this->detalle_record($pag_a_mostrar);	// Se va al primer registro
	}
	
	function crear_desde($cod_usuario_tipo_op) {
		session::set('CREA_PARTICIPACION', $cod_usuario_tipo_op);
		$this->add();	
	}
   function redraw(&$temp){
		parent::redraw(&$temp);
		$this->dw_check_box->habilitar($temp, true);
	}
  function procesa_event() {
		if ($_POST['HIZO_CLICK_0'] == 'S') {
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

			// vuelve a setear el friltro aplicado
			$this->headers['TOTAL_NETO']->valor_filtro = $valor_filtro;
			$this->headers['TOTAL_NETO']->valor_filtro2 = $valor_filtro2;
			
			$this->save_SESSION();	
			$this->make_filtros();
			$this->retrieve();
    }else if(isset($_POST['b_create_x'])){
			$this->crear_desde($_POST['wo_hidden']);
			
		}else{ 
			$this->checkbox_sumar = 0;
			parent::procesa_event();
		}
   
	}
}
?>
