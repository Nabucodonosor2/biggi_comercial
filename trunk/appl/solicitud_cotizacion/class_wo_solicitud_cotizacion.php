<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_vendedor.php");

class header_vendedor_str extends header_vendedor{
	function header_vendedor_str($field, $field_bd, $nom_header){
		$sql = "SELECT NULL COD_USUARIO
					  ,'SIN ASIGNAR' NOM_USUARIO
				UNION	  
				SELECT U.COD_USUARIO, U.NOM_USUARIO 
					  FROM USUARIO U 
				WHERE U.VENDEDOR_VISIBLE_FILTRO = 1 
				ORDER BY NOM_USUARIO ASC";
		parent::header_drop_down($field, $field_bd, $nom_header, $sql);
	}
}

class drop_down_estado extends header_drop_down{
	function drop_down_estado() {
		$sql = "select COD_ESTADO_SOLICITUD_COTIZACION
					  ,NOM_ESTADO_SOLICITUD_COTIZACION
				from ESTADO_SOLICITUD_COTIZACION
				where COD_ESTADO_SOLICITUD_COTIZACION in (1,2,3,4,5,6)
				order by COD_ESTADO_SOLICITUD_COTIZACION";
		parent::header_drop_down('NOM_ESTADO_SOLICITUD', 'S.COD_ESTADO_SOLICITUD_COTIZACION', 'Estado', $sql);
	}
	function make_filtro() {
		if ($this->valor_filtro=='99')
			return "(".$this->field_bd." in (1,2,3,4,5)) and ";		
		else
			return parent::make_filtro();
	}
}

class wo_solicitud_cotizacion extends w_output_biggi{
	var $cod_filtro_ini;
   function wo_solicitud_cotizacion() {
   		parent::w_base('solicitud_cotizacion', $_REQUEST['cod_item_menu']);
		 
		$sql = "select	S.COD_SOLICITUD_COTIZACION	
						,convert(varchar(20), S.FECHA_SOLICITUD_COTIZACION, 103) FECHA_SOLICITUD_COTIZACION
						,CONVERT(VARCHAR(8),S.FECHA_SOLICITUD_COTIZACION, 108) HORA_SOLICITUD_COTIZACION
						,S.FECHA_SOLICITUD_COTIZACION DATE_FECHA_SOLICITUD_COTIZACION
						,C.RUT
						,SUBSTRING(C.NOM_CONTACTO, 1, 25) EMPRESA
						,C.NOM_CONTACTO 	EMPRESA_TOOLTIPS				
						,CP.NOM_PERSONA
						,COD_COTIZACION 
						,CP.NOM_PERSONA NOM_CONTACTO
						,'' ANULA
						,S.TOTAL_NETO
						,(SELECT INI_USUARIO FROM USUARIO WHERE COD_USUARIO = S.COD_USUARIO_VENDEDOR1_RESP) NOM_RESPONSABLE
						,ESC.NOM_ESTADO_SOLICITUD_COTIZACION NOM_ESTADO_SOLICITUD
						,(select nom_estado_cotizacion from ESTADO_COTIZACION EC where COD_ESTADO_COTIZACION = CO.COD_ESTADO_COTIZACION)  NOM_ESTADO_COTIZACION
						,INI_USUARIO
			from		SOLICITUD_COTIZACION S LEFT OUTER JOIN COTIZACION CO ON S.COD_SOLICITUD_COTIZACION = CO.COD_SOLICITUD_COTIZACION
												LEFT OUTER JOIN ESTADO_SOLICITUD_COTIZACION ESC  on S.COD_ESTADO_SOLICITUD_COTIZACION = ESC.COD_ESTADO_SOLICITUD_COTIZACION
												LEFT OUTER JOIN USUARIO U ON S.COD_USUARIO_VENDEDOR1_RESP = U.COD_USUARIO
						,CONTACTO_PERSONA CP
						,CONTACTO C
			where		S.COD_ESTADO_SOLICITUD_COTIZACION <> 7
			and			C.COD_CONTACTO = S.COD_CONTACTO";
			
		$priv = $this->get_privilegio_opcion_usuario('999005', $this->cod_usuario);
		
		if($priv <> 'E')
			$sql .= " AND S.COD_USUARIO_VENDEDOR1_RESP = $this->cod_usuario";
		
		$sql .=	 " AND   CP.COD_CONTACTO = C.COD_CONTACTO
		order by	S.COD_SOLICITUD_COTIZACION DESC";

     	parent::w_output_biggi('solicitud_cotizacion', $sql, $_REQUEST['cod_item_menu']);
				
		$this->dw->add_control(new static_link('COD_COTIZACION', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=solicitud_cotizacion&modulo_destino=cotizacion&cod_modulo_destino=[COD_COTIZACION]&cod_item_menu=1505&DESDE_OUTPUT=0'));
		$this->dw->add_control(new static_num('TOTAL_NETO'));
		$this->dw->add_control(new edit_check_box('ANULA','S','N'));
      	
	    // headers
      	$this->add_header($control = new header_date('FECHA_SOLICITUD_COTIZACION', 'S.FECHA_SOLICITUD_COTIZACION', 'Fecha'));
	    $control->field_bd_order = 'DATE_FECHA_SOLICITUD_COTIZACION';
	    $this->add_header(new header_num('COD_SOLICITUD_COTIZACION', 'S.COD_SOLICITUD_COTIZACION', 'Cod'));
	    $this->add_header(new header_text('HORA_SOLICITUD_COTIZACION', 'HORA_SOLICITUD_COTIZACION', 'Hora'));
	  	$this->add_header(new header_text('RUT', 'C.RUT', 'Rut'));
	 	$this->add_header(new header_text('NOM_CONTACTO', 'CP.NOM_PERSONA', 'Nombre Contacto'));
	    $this->add_header(new header_text('EMPRESA', 'C.NOM_CONTACTO', 'Empresa'));
	    $this->add_header(new header_num('COD_COTIZACION', 'COD_COTIZACION', 'N° Cotización'));
	    $this->add_header(new header_num('TOTAL_NETO', 'S.TOTAL_NETO', 'Total Neto'));
		$this->add_header($control = new header_vendedor_str('INI_USUARIO', 'S.COD_USUARIO_VENDEDOR1_RESP', 'Resp'));
		$control->field_bd_order = 'INI_USUARIO';
		
		$this->add_header($h_estado = new drop_down_estado());

      	$sql = "select COD_ESTADO_COTIZACION, NOM_ESTADO_COTIZACION from ESTADO_COTIZACION EC where COD_ESTADO_COTIZACION = EC.COD_ESTADO_COTIZACION";
      	$this->add_header(new header_drop_down('NOM_ESTADO_COTIZACION', 'CO.COD_ESTADO_COTIZACION', 'Estado Cotización', $sql));
      	
      	//$h_estado->valor_filtro = 99;
	    //$this->make_filtros();
	}
		
	function save(){
		$this->dw->get_values_from_POST();
		$arr_dlg = explode('|', $_POST['wo_hidden']);
		$cod_estado_sc = $arr_dlg[0];
		$motivo_anula = $arr_dlg[1];
		$cod_usuario = $this->cod_usuario;

		$sp = 'spu_anula_solicitud_cotizacion';
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$db->BEGIN_TRANSACTION();
		$error = false;
		
		$ind = $this->row_per_page * ($this->current_page - 1);		
		$i = 0;
		while (($i < $this->row_per_page) && ($ind < $this->row_count_output)){
			$chek = $this->dw->get_item($i, 'ANULA');
			$cod_sc = $this->dw->get_item($i, 'COD_SOLICITUD_COTIZACION');

			if($chek ==	'S'){
				$param="'UPDATE'
				,$cod_sc
				,$cod_estado_sc
				,'$motivo_anula'
				,$cod_usuario";
				
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

		if(!$error)
			$db->COMMIT_TRANSACTION();
			
		$this->modify = false;
		$this->dw->entrable = false;
		$this->retrieve();		
	}

	function habilita_boton($temp, $boton, $habilita) {
		parent::habilita_boton($temp, $boton, $habilita);
		
		$ruta_imag = '../../../../commonlib/trunk/images/';
		if (defined('K_CLIENTE')) {
			if (file_exists('../../images_appl/'.K_CLIENTE.'/images/b_'.$boton.'.jpg')){
				$ruta_imag = '../../images_appl/'.K_CLIENTE.'/images/';
			}
		}

		if($boton == 'export'){
			if ($habilita){
				$temp->setVar("WO_".strtoupper($boton), '<input name="b_'.$boton.'" id="b_'.$boton.'" src="'.$ruta_imag.'b_'.$boton.'.jpg" type="image" '.
																								'onMouseDown="MM_swapImage(\'b_'.$boton.'\',\'\',\''.$ruta_imag.'b_'.$boton.'_click.jpg\',1)" '.
																								'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
																								'onMouseOver="MM_swapImage(\'b_'.$boton.'\',\'\',\''.$ruta_imag.'b_'.$boton.'_over.jpg\',1)" '.
																								'/>');
			}else{
				$temp->setVar("WO_".strtoupper($boton), '');
			}
		}

		if($boton == 'save'){
			if ($habilita){
				$ruta_over = "'../../../../commonlib/trunk/images/b_".strtoupper($boton)."_over.jpg'";
				$ruta_out = "'../../../../commonlib/trunk/images/b_".strtoupper($boton).".jpg'";
				$ruta_click = "'../../../../commonlib/trunk/images/b_".strtoupper($boton)."_click.jpg'";
				$temp->setVar("WO_".strtoupper($boton), '<input name="b_'.$boton.'" id="b_'.$boton.'" type="button" onmouseover="entrada(this, '.$ruta_over.')" onmouseout="salida(this, '.$ruta_out.')" onmousedown="down(this, '.$ruta_click.')"'.
														'style="cursor:pointer;height:68px;width:66px;border: 0;background-image:url(../../../../commonlib/trunk/images/b_'.$boton.'.jpg);background-repeat:no-repeat;background-position:center;border-radius: 15px;"'.
														'onClick="request_anular(\'Ingrese motivo de anulación\',\'\');" />');
			}else{
				$temp->setVar("WO_".strtoupper($boton), '<img src="'.$ruta_imag.'b_'.$boton.'_d.jpg"/>');
			}
		}
	}

	function make_menu(&$temp){
		$menu = session::get('menu_appl');
		$menu->ancho_completa_menu = 365;
		$menu->draw($temp);
		$menu->ancho_completa_menu = 209;
	}

	function redraw($temp) {
		parent::redraw($temp);

		if($this->cod_usuario == 1 || $this->cod_usuario == 4 || $this->cod_usuario == 15){
			$this->habilita_boton($temp, 'no_save', $this->modify);		
			$this->habilita_boton($temp, 'save', $this->modify);		
			$this->habilita_boton($temp, 'modify', !$this->modify);
			$this->habilita_boton($temp, 'export', true);
		}else{
			$this->habilita_boton($temp, 'export', false);
		}
	}

	function procesa_event(){
		if(isset($_POST['b_modify_x'])){
			$this->modify = true;
			$this->dw->entrable = true;
			$this->_redraw();
		}elseif(isset($_POST['b_no_save_x'])){
			$this->modify = false;
			$this->dw->entrable = false;
			$this->_redraw();
		}elseif(isset($_POST['b_save_x'])){
			$this->save();
		}else
			parent::procesa_event();
	}
}
?>