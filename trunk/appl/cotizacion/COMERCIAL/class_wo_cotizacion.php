<?php

class wo_cotizacion extends wo_cotizacion_base {
    //const K_AUTORIZA_EXPORTAR = '990525';
	function wo_cotizacion() {
		// Llama a w_base para que $this->cod_usuario contenga al usuario actual 
   		//parent::w_base('cotizacion', $_REQUEST['cod_item_menu']);
   		
		parent::wo_cotizacion_base();
		$sql = "select	C.COD_COTIZACION
						,convert(varchar(20), C.FECHA_COTIZACION, 103) FECHA_COTIZACION
						,C.FECHA_COTIZACION DATE_COTIZACION
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,C.REFERENCIA
						,U.INI_USUARIO
						,C.COD_USUARIO_VENDEDOR1
						,EC.NOM_ESTADO_COTIZACION
						,OCOT.NOM_ORIGEN_COTIZACION
						,C.TOTAL_NETO
						,dbo.f_get_cod_nv_from_cot(C.COD_COTIZACION) COD_NOTA_VENTA
						,C.COD_ESTADO_COTIZACION
						,C.COD_ORIGEN_COTIZACION
			from 		COTIZACION C
						,EMPRESA E
						,USUARIO U
						,ESTADO_COTIZACION EC
						,ORIGEN_COTIZACION OCOT
			where		C.COD_EMPRESA = E.COD_EMPRESA and 
						C.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO and 
						C.COD_ESTADO_COTIZACION = EC.COD_ESTADO_COTIZACION AND
						C.COD_ORIGEN_COTIZACION = OCOT.COD_ORIGEN_COTIZACION";
		
		$priv = $this->get_privilegio_opcion_usuario('990540', $this->cod_usuario);
			
		if($priv <> 'E')
			$sql .= " and C.COD_USUARIO = $this->cod_usuario";
		
		$sql .=	" and dbo.f_get_tiene_acceso (".$this->cod_usuario.", 'COTIZACION',C.COD_USUARIO_VENDEDOR1, C.COD_USUARIO_VENDEDOR2) = 1
				  order by	C.COD_COTIZACION desc";
			
     		parent::w_output('cotizacion', $sql, $_REQUEST['cod_item_menu']);

		$this->dw->add_control(new edit_nro_doc('COD_COTIZACION','COTIZACION'));
		$this->dw->add_control(new edit_precio('TOTAL_NETO'));
      	$this->dw->add_control(new static_num('RUT'));
			
	      // headers
	    $sql = "SELECT COD_ESTADO_COTIZACION, NOM_ESTADO_COTIZACION FROM ESTADO_COTIZACION ORDER BY ORDEN";
		$this->add_header(new header_drop_down('NOM_ESTADO_COTIZACION', 'C.COD_ESTADO_COTIZACION', 'Estado', $sql)); 

	    $sql1 = "SELECT COD_ORIGEN_COTIZACION, NOM_ORIGEN_COTIZACION FROM ORIGEN_COTIZACION ORDER BY ORDEN";
		$this->add_header(new header_drop_down('NOM_ORIGEN_COTIZACION', 'C.COD_ORIGEN_COTIZACION', 'Origen', $sql1)); 
		
      	$this->add_header($control = new header_date('FECHA_COTIZACION', 'C.FECHA_COTIZACION', 'Fecha'));
	    $control->field_bd_order = 'DATE_COTIZACION';
	    $this->add_header(new header_num('COD_COTIZACION', 'C.COD_COTIZACION', 'N� Cot.'));
	    $this->add_header(new header_rut('RUT', 'E', 'Rut'));
	      
	    $this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Raz�n Social'));
	    $this->add_header(new header_text('REFERENCIA', 'C.REFERENCIA', 'Referencia'));
		$this->add_header(new header_vendedor('INI_USUARIO', 'C.COD_USUARIO_VENDEDOR1', 'V.'));

	    $this->add_header($header = new header_num('COD_NOTA_VENTA', 'dbo.f_get_cod_nv_from_cot(C.COD_COTIZACION)', 'NV'));
	    $header->field_bd_order = 'COD_NOTA_VENTA';
	    $this->add_header(new header_num('TOTAL_NETO', 'C.TOTAL_NETO', 'Total Neto'));
		/*
	    $priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_EXPORTAR, $this->cod_usuario);
		if ($priv=='E') {
			$this->b_export_visible = true;
      	}
      	else {
			$this->b_export_visible = false;
      	}*/
	    
  	}
  	// Boton Crear Desde
	function habilita_boton(&$temp, $boton, $habilita) {
		if ($boton=='create' && $habilita){
			$ruta_over = "'../../../../commonlib/trunk/images/b_create_over.jpg'";
			$ruta_out = "'../../../../commonlib/trunk/images/b_create.jpg'";
			$ruta_click = "'../../../../commonlib/trunk/images/b_create_click.jpg'";
			$temp->setVar("WO_ADD_DESDE", '<input name="b_'.$boton.'" id="b_'.$boton.'" type="button" onmouseover="entrada(this, '.$ruta_over.')" onmouseout="salida(this, '.$ruta_out.')" onmousedown="down(this, '.$ruta_click.')"'.
							'style="cursor:pointer;height:68px;width:66px;border: 0;background-image:url(../../../../commonlib/trunk/images/b_'.$boton.'.jpg);background-repeat:no-repeat;background-position:center;border-radius: 15px;"'.
							'onClick="request_crear_desde();" />');
			
		}else
			parent::habilita_boton($temp, $boton, $habilita);
	}
	function redraw(&$temp) {
		parent::redraw($temp);
		$this->habilita_boton($temp, 'create', true);		
			
	}
	
	function crear_cot_from_cot($seleccion) {
		$seleccion = explode("|", $seleccion);
		if ($seleccion[0]=="SOLICITUD") { 		
			$cod_solicitud = $seleccion[1];
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "SELECT * FROM SOLICITUD_COTIZACION WHERE COD_SOLICITUD_COTIZACION  = $cod_solicitud";
			$result = $db->build_results($sql);
			
				if (count($result) == 0){
					$this->_redraw();
					$this->alert('La solicitud cotizaci�n N� '.$cod_solicitud.' no existe.');								
					return;
				}else{
			session::set('CREADA_DESDE_SOLICITUD', $cod_solicitud);
			$this->add();
			
			
			
			}
		}
		else if ($seleccion[0]=="COTIZACION") { 
			$cod_cotizacion = $seleccion[1];
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "SELECT * FROM COTIZACION WHERE COD_COTIZACION = $cod_cotizacion";
			$result = $db->build_results($sql);
				if (count($result) == 0){
					$this->_redraw();
					$this->alert('La cotizaci�n N� '.$cod_cotizacion.' no existe.');								
					return;
				}
				
			$sql2 = "SELECT COD_ESTADO_COTIZACION FROM COTIZACION WHERE COD_COTIZACION = $cod_cotizacion";
			$result2 = $db->build_results($sql2);
			if($result2[0]['COD_ESTADO_COTIZACION'] == 5){
				session::set('CREADA_DESDE_COTIZACION_COD_RECHAZADA', 6);						
			}
			session::set('CREADA_DESDE_COTIZACION', $cod_cotizacion);
			$this->add();
		}
	}
	
	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_cot_from_cot($_POST['wo_hidden']);
		else
			parent::procesa_event();
	}
}
?>