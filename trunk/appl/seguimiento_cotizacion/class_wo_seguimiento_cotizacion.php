<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_vendedor.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class drop_down_estado extends header_drop_down {
	function drop_down_estado() {
		$sql = "select COD_ESTADO_COTIZACION
					  ,NOM_ESTADO_COTIZACION
				from ESTADO_COTIZACION
				where COD_ESTADO_COTIZACION in (2, 3)
				order by COD_ESTADO_COTIZACION";
		parent::header_drop_down('NOM_ESTADO_COTIZACION', 'C.COD_ESTADO_COTIZACION', 'Est.', $sql);
	}
	function make_filtro() {
		if ($this->valor_filtro=='99')
			return "(".$this->field_bd." in (2,3)) and ";		
		else
			return parent::make_filtro();
	}
}

class wo_seguimiento_cotizacion extends w_output_biggi{
	var $checkbox_hoy;
	var $checkbox_mañana;
	var $checkbox_ayer;
	var $dw_check_box;
	
	function make_sql(){
		
		$tiene_acceso = $this->tiene_acceso($this->cod_usuario, 'C.COD_USUARIO_VENDEDOR1', 'C.COD_USUARIO_VENDEDOR2');
		$sql = "SELECT	 C.COD_COTIZACION
						,CONVERT(VARCHAR(20), FECHA_COTIZACION, 103) FECHA_COTIZACION
						,INI_USUARIO
						,C.COD_USUARIO_VENDEDOR1
						,(E.NOM_EMPRESA + ' <br>Ref: ' +C.REFERENCIA) NOM_EMPRESA
						,CONVERT(VARCHAR(20), BC.FECHA_COMPROMISO, 103) FECHA_COMPROMISO 
						,BC.FECHA_COMPROMISO DATE_COMPROMISO 
						,convert (varchar(1000),BC.GLOSA_COMPROMISO)GLOSA_COMPROMISO
						,TOTAL_NETO
						,case 
							when isnull(BC.CONTACTO, '')='' and isnull(BC.TELEFONO, '')='' then ''
							when isnull(BC.CONTACTO, '')='' then BC.TELEFONO
							when isnull(BC.TELEFONO, '')='' then BC.CONTACTO
							else BC.CONTACTO + ' ' + BC.TELEFONO
						end CONTACTO_TELEFONO
						,CASE
							WHEN DATEDIFF(DAY,GETDATE(),BC.FECHA_COMPROMISO) = 0 THEN 0
							WHEN DATEDIFF(DAY,GETDATE(),BC.FECHA_COMPROMISO) < 0 THEN 1
							WHEN DATEDIFF(DAY,GETDATE(),BC.FECHA_COMPROMISO) > 0 THEN 2
						END ORDEN
						,CASE
							WHEN DATEDIFF(DAY,GETDATE(),BC.FECHA_COMPROMISO) = 0 THEN '#FFE920'
							WHEN DATEDIFF(DAY,GETDATE(),BC.FECHA_COMPROMISO) < 0 THEN '#FF8A8A'
							WHEN DATEDIFF(DAY,GETDATE(),BC.FECHA_COMPROMISO) > 0 THEN '#4AE371'
						END COLOR
						,C.COD_ESTADO_COTIZACION
						,EC.NOM_ESTADO_COTIZACION
						,CASE
							WHEN DATEDIFF(DAY,GETDATE(),BC.FECHA_COMPROMISO) = 0 THEN 255
							WHEN DATEDIFF(DAY,GETDATE(),BC.FECHA_COMPROMISO) < 0 THEN 255
							WHEN DATEDIFF(DAY,GETDATE(),BC.FECHA_COMPROMISO) > 0 THEN 74
							else 255
						END REDB
						,CASE
							WHEN DATEDIFF(DAY,GETDATE(),BC.FECHA_COMPROMISO) = 0 THEN 233
							WHEN DATEDIFF(DAY,GETDATE(),BC.FECHA_COMPROMISO) < 0 THEN 138
							WHEN DATEDIFF(DAY,GETDATE(),BC.FECHA_COMPROMISO) > 0 THEN 227
							else 255
						END GREENB
						,CASE
							WHEN DATEDIFF(DAY,GETDATE(),BC.FECHA_COMPROMISO) = 0 THEN 32
							WHEN DATEDIFF(DAY,GETDATE(),BC.FECHA_COMPROMISO) < 0 THEN 138
							WHEN DATEDIFF(DAY,GETDATE(),BC.FECHA_COMPROMISO) > 0 THEN 113
							else 255
						END BLUEB
				FROM	 COTIZACION C
						,BITACORA_COTIZACION BC
						,EMPRESA E
						,USUARIO U
						,ESTADO_COTIZACION EC
				WHERE BC.COMPROMISO_REALIZADO = 'N'
				  AND C.COD_COTIZACION  = BC.COD_COTIZACION
				  AND C.COD_EMPRESA = E.COD_EMPRESA
				  AND C.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO
				  AND C.COD_ESTADO_COTIZACION = EC.COD_ESTADO_COTIZACION
				  AND $tiene_acceso";
   				
				if ($this->checkbox_hoy || $this->checkbox_mañana || $this->checkbox_ayer){
	   				$sql .= " AND (1=2 ";
					
					if ($this->checkbox_hoy == true)
	   					$sql .= " or DATEDIFF(DAY,GETDATE(),BC.FECHA_COMPROMISO) = 0";
	   				if ($this->checkbox_mañana == true)
	   					$sql .= " or DATEDIFF(DAY,GETDATE(),BC.FECHA_COMPROMISO) > 0";
	   				if ($this->checkbox_ayer == true)
	   					$sql .= " or DATEDIFF(DAY,GETDATE(),BC.FECHA_COMPROMISO) < 0";
	   				$sql .= " )";
	   					
				}	
				//Se solicitó filtro de las fechas compromiso solo del 2014 (28/05/2014)
				$sql .= " AND BC.FECHA_COMPROMISO >= {ts '2014-01-01 00:00:00.000'}
						 ORDER BY ORDEN ASC, DATE_COMPROMISO ASC";
		return $sql;		
	}
	
	function wo_seguimiento_cotizacion(){
		$this->checkbox_hoy = false;
		$this->checkbox_mañana = false;
		$this->checkbox_ayer = false;
		
   		parent::w_base('seguimiento_cotizacion', $_REQUEST['cod_item_menu']);
		 
		$sql = $this->make_sql();
		
     	parent::w_output_biggi('seguimiento_cotizacion', $sql, $_REQUEST['cod_item_menu']);
		
     	$this->dw->add_control(new edit_precio('TOTAL_NETO'));
     	
	    // headers
	    $this->add_header($h_estado = new drop_down_estado());
      	$this->add_header(new header_num('COD_COTIZACION', 'C.COD_COTIZACION', 'Nº Cot.'));
		$this->add_header(new header_date('FECHA_COTIZACION', 'C.FECHA_COTIZACION', 'F.Cot.'));
		$this->add_header(new header_vendedor('INI_USUARIO', 'C.COD_USUARIO_VENDEDOR1', 'V'));
		$this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Razón Social / Ref'));
	    $this->add_header(new header_text('CONTACTO_TELEFONO', 'CONTACTO_TELEFONO', 'Contacto'));
	    // $header->sorteable = false;
	    $this->add_header(new header_date('FECHA_COMPROMISO', 'BC.FECHA_COMPROMISO', 'F.Com.'));
	    $this->add_header(new header_text('GLOSA_COMPROMISO', 'GLOSA_COMPROMISO', 'Compromiso'));
	    $this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));
	    
	    $sql = "select 'N' CHECK_HOY,
					   'N' CHECK_AYER,
					   'N' CHECK_MANANA,	
					   'N' HIZO_CLICK";
	    $this->dw_check_box = new datawindow($sql);
	    $this->dw_check_box->add_control($control = new edit_check_box('CHECK_HOY','S','N'));
		$control->set_onClick("agrega_factura(); document.getElementById('loader1').style.display='';");
		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_AYER','S','N'));
		$control->set_onClick("agrega_factura(); document.getElementById('loader2').style.display='';");
		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_MANANA','S','N'));
		$control->set_onClick("agrega_factura(); document.getElementById('loader3').style.display='';");
		$this->dw_check_box->add_control(new edit_text_hidden('HIZO_CLICK'));
		$this->dw_check_box->retrieve();
		
		$h_estado->valor_filtro = 99;
		$this->make_filtros();	// filtro incial
	}
	
	function redraw(&$temp) {
		parent::redraw($temp);
		if($this->priv_impresion == 'S')
			$this->habilita_boton($temp, 'print', true);		
		else
			$this->habilita_boton($temp, 'print', false);
			
		$this->dw_check_box->habilitar($temp, true);
	}
	
	function habilita_boton(&$temp, $boton, $habilita) {
		if ($boton=='print' && $habilita)
			$temp->setVar("WO_PRINT", '<input name="b_'.$boton.'" id="b_'.$boton.'" src="../../../../commonlib/trunk/images/b_'.$boton.'.jpg" type="image" '.
											'onMouseDown="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../../../commonlib/trunk/images/b_'.$boton.'_click.jpg\',1)" '.
											'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
											'onMouseOver="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../../../commonlib/trunk/images/b_'.$boton.'_over.jpg\',1)" '.
											'onClick="return request_crear_desde();" />');
		else
			parent::habilita_boton($temp, $boton, $habilita);
	}
	
    function tiene_acceso($cod_usuario, $cod_usu1, $cod_usu2) {
    	if ($cod_usuario==1	or	//ADMINISTRADOR
    			$cod_usuario==2 or	//ANGEL SCIANCA
    				$cod_usuario==4 or	//SERGIO PECHOANTE
    					$cod_usuario==8 or	//RAFAEL ESCUDERO
    						$cod_usuario==30 or	//KARINA VERDUGO
    							$cod_usuario==40 or //FELIPE PUEBLA
    								$cod_usuario==75) //BARBARA VENEGAS
    	  	return "(1=1)";
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
    	$sql = "SELECT DISTINCT GU.COD_USUARIO
				FROM GRUPO G, GRUPO_USUARIO GU
				WHERE G.COD_USUARIO = $cod_usuario
		  		  AND GU.COD_GRUPO = G.COD_GRUPO";
		$result = $db->build_results($sql);
		
		$res = "$cod_usu1=$cod_usuario or $cod_usu2=$cod_usuario";
		for ($i=0; $i<count($result);$i++) {
			$cod_usuario = $result[$i]['COD_USUARIO'];
			$res .= " or $cod_usu1=$cod_usuario or $cod_usu2=$cod_usuario";
		}
		return "($res)";
    }
	function detalle_record($rec_no) {	
		session::set('DESDE_wo_cotizacion', 'desde output');	// para indicar que viene del output
		session::set('DESDE_wo_seguimiento_cotizacion', 'true');
		$ROOT = $this->root_url;
		$url = $ROOT.'appl/cotizacion';
		header ('Location:'.$url.'/wi_cotizacion.php?rec_no='.$rec_no.'&cod_item_menu=1505');
	}
	
	function print_seguimiento(){
		$this->make_filtros();
		$sql = $this->dw->get_sql();
		$fecha = $this->current_date();
		$time = $this->current_time();
		$labels = array();
		$labels['strFECHA'] = $fecha;
		$labels['strTIME'] = $time;
		$file_name = $this->root_dir.'appl/seguimiento_cotizacion/seguimiento_cotizacion.xml';
		$rpt = new reporte($sql, $file_name, $labels, "Seguimiento Cotización".".pdf", 0, false, 'L');
		$this->_redraw();
	}
	
	function procesa_event() {
		if ($_POST['HIZO_CLICK_0'] == 'S'){
			$this->checkbox_hoy = isset($_POST['CHECK_HOY_0']);
			$this->checkbox_ayer = isset($_POST['CHECK_AYER_0']);
			$this->checkbox_mañana = isset($_POST['CHECK_MANANA_0']);
			
			if ($this->checkbox_hoy)
				$this->dw_check_box->set_item(0, 'CHECK_HOY', 'S');
			else{
				$this->dw_check_box->set_item(0, 'CHECK_HOY', 'N');
			}
			
			if ($this->checkbox_ayer)
				$this->dw_check_box->set_item(0, 'CHECK_AYER', 'S');
			else
				$this->dw_check_box->set_item(0, 'CHECK_AYER', 'N');

			if ($this->checkbox_mañana)
				$this->dw_check_box->set_item(0, 'CHECK_MANANA', 'S');
			else
				$this->dw_check_box->set_item(0, 'CHECK_MANANA', 'N');

			$sql = $this->make_sql();
			$this->dw->set_sql($sql);
			$this->sql_original = $sql;
			$this->save_SESSION();	
			$this->make_filtros();
			//echo $this->dw->get_sql();
			$this->retrieve();	
		}else if(isset($_POST['b_print_x'])){
			$this->print_seguimiento();
		}else{ 
			$this->checkbox_comercial = 0;
			$this->checkbox_bodega = 0;
			$this->checkbox_servindus = 0;
			parent::procesa_event();
			
		}
	}   
}
?>