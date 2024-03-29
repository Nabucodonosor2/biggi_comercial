<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_usuario.php");

class wi_envio_softland_aux extends wi_envio_softland {
	var $cod_envio_softland;
	
	function wi_envio_softland_aux() {
		parent::wi_envio_softland('2545');
	}
	function get_key() {
		return $this->cod_envio_softland;
	}
	function _load_record() {
		return;
	}
}

class wo_envio_softland extends w_output_biggi {
	function wo_envio_softland(){
		$sql = "select ES.COD_ENVIO_SOFTLAND
						,convert(varchar(20), ES.FECHA_ENVIO_SOFTLAND, 103) FECHA_ENVIO_SOFTLAND
						,ES.FECHA_ENVIO_SOFTLAND DATE_ENVIO_SOFTLAND
						,T.NOM_TIPO_ENVIO
						,U.NOM_USUARIO
						,EE.NOM_ESTADO_ENVIO
						,T.COD_TIPO_ENVIO
						,U.COD_USUARIO
						,ES.COD_ESTADO_ENVIO
						,NULL SELECCION
            ,case T.COD_TIPO_ENVIO when 5 then ES.TOTAL_BANCO else null end TOTAL_BANCO
				from	ENVIO_SOFTLAND ES, USUARIO U, TIPO_ENVIO T, ESTADO_ENVIO EE
				where	ES.COD_USUARIO = U.COD_USUARIO
				  and 	T.COD_TIPO_ENVIO = ES.COD_TIPO_ENVIO
				  and 	COD_ENVIO_SOFTLAND > 1	-- el uno es para traspaso inicial
				  and   EE.COD_ESTADO_ENVIO = ES.COD_ESTADO_ENVIO
				order by COD_ENVIO_SOFTLAND desc";

     	parent::w_output_biggi('envio_softland', $sql, $_REQUEST['cod_item_menu']);
      
      	$this->dw->add_control(new edit_precio('TOTAL_BANCO'));
		$this->dw->entrable = true;
		$this->dw->add_control(new edit_check_box('SELECCION', 'S', 'N'));

      	// headers
		$this->add_header(new header_num('COD_ENVIO_SOFTLAND', 'COD_ENVIO_SOFTLAND', 'C�digo'));
		$this->add_header($control = new header_date('FECHA_ENVIO_SOFTLAND', 'FECHA_ENVIO_SOFTLAND', 'Fecha'));
		$control->field_bd_order = 'DATE_ENVIO_SOFTLAND';
		$sql = "select COD_TIPO_ENVIO, NOM_TIPO_ENVIO from TIPO_ENVIO order by COD_TIPO_ENVIO";
		$this->add_header(new header_drop_down('NOM_TIPO_ENVIO', 'T.COD_TIPO_ENVIO', 'Tipo de Envio', $sql));

		$this->add_header(new header_usuario('NOM_USUARIO', 'U.COD_USUARIO', 'Usuario'));
		$sql = "select COD_ESTADO_ENVIO, NOM_ESTADO_ENVIO from ESTADO_ENVIO order by COD_ESTADO_ENVIO";
		$this->add_header(new header_drop_down('NOM_ESTADO_ENVIO', 'ES.COD_ESTADO_ENVIO', 'Estado Envio', $sql));
 		$this->add_header(new header_num('TOTAL_BANCO', 'TOTAL_BANCO', 'Total Banco'));
	}

	function habilita_boton(&$temp, $boton, $habilita){
		parent::habilita_boton($temp, $boton, $habilita);
		
		if($boton=='add' && $habilita){
			$ruta_over = "'../../../../commonlib/trunk/images/b_'.$boton.'_over.jpg'";
			$ruta_out = "'../../../../commonlib/trunk/images/b_'.$boton.'.jpg'";
			$ruta_click = "'../../../../commonlib/trunk/images/b_'.$boton.'_click.jpg'";
			$temp->setVar("WO_ADD", '<input name="b_'.$boton.'" id="b_'.$boton.'" type="button" onmouseover="entrada(this, '.$ruta_over.')" onmouseout="salida(this, '.$ruta_out.')" onmousedown="down(this, '.$ruta_click.')"'.
												 'style="cursor:pointer;height:68px;width:66px;border: 0;background-image:url(../../../../commonlib/trunk/images/b_'.$boton.'.jpg);background-repeat:no-repeat;background-position:center;border-radius: 15px;"'.
												 'onClick="request_tipo_envio(this);" />');
		}
		
		if($boton=='print') {
			if($habilita){
				$ruta_over = "'../../images_appl/b_print.jpg'";
				$ruta_out = "'../../images_appl/b_print.jpg'";
				$ruta_click = "'../../images_appl/b_print.jpg'";
				$temp->setVar("WO_PRINT_ANEXO_MASIVO", '<input name="b_'.$boton.'" id="b_'.$boton.'" type="button" onmouseover="entrada(this, '.$ruta_over.')" onmouseout="salida(this, '.$ruta_out.')" onmousedown="down(this, '.$ruta_click.')"'.
												 'style="cursor:pointer;height:68px;width:66px;border: 0;background-image:url('.$ruta_out.');background-repeat:no-repeat;background-position:center;border-radius: 15px;"'.
												 '/>');
			}	
		}	
	}

	function add() {
		$sel = $_POST['wo_hidden'];
		session::set("tipo_envio_softland", $sel);
		parent::add();
	}

	function redraw(&$temp){
		parent::redraw($temp);
	 	$this->habilita_boton($temp, 'print', true);
  	}

	function procesa_event() {
		if(isset($_POST['wo_hidden']) && ($_POST['wo_hidden']=='VENTAS' || $_POST['wo_hidden']=='COMPRAS' || $_POST['wo_hidden']=='EGRESOS' || $_POST['wo_hidden']=='TRANSBANK' || $_POST['wo_hidden']=='INGRESOS'))
			$this->add();
		else if(isset($_POST['b_print_x']))
			$this->print_transbank($value_boton);
		else
			parent::procesa_event();
	}

	function print_transbank($rec_no){
		$array;
		$this->dw->get_values_from_POST();
		$ind = $this->row_per_page * ($this->current_page - 1);
		$i = 0;

		while (($i < $this->row_per_page) && ($ind < $this->row_count_output)){
			$seleccion		= $this->dw->get_item($i, 'SELECCION');
			$cod_tipo_envio	= $this->dw->get_item($i, 'COD_TIPO_ENVIO');

			if ($seleccion=='S' && $cod_tipo_envio == 5){
				$cod_envio_softland = $this->dw->get_item($i, 'COD_ENVIO_SOFTLAND');
				$array[] = $cod_envio_softland;
    		}

    		$i++;
			$ind++;
		}

		if(count($array) <> 0){
			$wi = new wi_envio_softland_aux('cod_item_menu');
			$wi->export_comprobantes($array);
		}else
			$this->retrieve();
	}
}
?>