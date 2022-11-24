<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_usuario.php");

class wo_envio_softland extends w_output_biggi {

	function wo_envio_softland() {
		$sql = "select ES.COD_ENVIO_SOFTLAND
						,convert(varchar(20), ES.FECHA_ENVIO_SOFTLAND, 103) FECHA_ENVIO_SOFTLAND
						,ES.FECHA_ENVIO_SOFTLAND DATE_ENVIO_SOFTLAND
						,T.NOM_TIPO_ENVIO
						,U.NOM_USUARIO
						,EE.NOM_ESTADO_ENVIO
						,T.COD_TIPO_ENVIO
						,U.COD_USUARIO
						,ES.COD_ESTADO_ENVIO
            ,case T.COD_TIPO_ENVIO when 5 then ES.TOTAL_BANCO else null end TOTAL_BANCO
				from	ENVIO_SOFTLAND ES, USUARIO U, TIPO_ENVIO T, ESTADO_ENVIO EE
				where	ES.COD_USUARIO = U.COD_USUARIO
				  and 	T.COD_TIPO_ENVIO = ES.COD_TIPO_ENVIO
				  and 	COD_ENVIO_SOFTLAND > 1	-- el uno es para traspaso inicial
				  and   EE.COD_ESTADO_ENVIO = ES.COD_ESTADO_ENVIO
				order by COD_ENVIO_SOFTLAND desc";

     	parent::w_output_biggi('envio_softland', $sql, $_REQUEST['cod_item_menu']);
      
      $this->dw->add_control(new edit_precio('TOTAL_BANCO'));

      // headers
		$this->add_header(new header_num('COD_ENVIO_SOFTLAND', 'COD_ENVIO_SOFTLAND', 'Código'));
		$this->add_header($control = new header_date('FECHA_ENVIO_SOFTLAND', 'FECHA_ENVIO_SOFTLAND', 'Fecha'));
		$control->field_bd_order = 'DATE_ENVIO_SOFTLAND';
		$sql = "select COD_TIPO_ENVIO, NOM_TIPO_ENVIO from TIPO_ENVIO order by COD_TIPO_ENVIO";
		$this->add_header(new header_drop_down('NOM_TIPO_ENVIO', 'T.COD_TIPO_ENVIO', 'Tipo de Envio', $sql));

		$this->add_header(new header_usuario('NOM_USUARIO', 'U.COD_USUARIO', 'Usuario'));
		$sql = "select COD_ESTADO_ENVIO, NOM_ESTADO_ENVIO from ESTADO_ENVIO order by COD_ESTADO_ENVIO";
		$this->add_header(new header_drop_down('NOM_ESTADO_ENVIO', 'ES.COD_ESTADO_ENVIO', 'Estado Envio', $sql));
    $this->add_header(new header_num('TOTAL_BANCO', 'TOTAL_BANCO', 'Total Banco'));
	}
	function habilita_boton(&$temp, $boton, $habilita) {
		if ($boton=='add' && $habilita){
			/*$temp->setVar("WO_".strtoupper($boton), '<input name="b_'.$boton.'" id="b_'.$boton.'" src="../../../../commonlib/trunk/images/b_'.$boton.'.jpg" type="image" '.
														'onMouseDown="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../../../commonlib/trunk/images/b_'.$boton.'_click.jpg\',1)" '.
														'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
														'onMouseOver="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../../../commonlib/trunk/images/b_'.$boton.'_over.jpg\',1)" '.
														'onClick="return request_tipo_envio();" '.
													'/>');*/

			$ruta_over = "'../../../../commonlib/trunk/images/b_'.$boton.'_over.jpg'";
			$ruta_out = "'../../../../commonlib/trunk/images/b_'.$boton.'.jpg'";
			$ruta_click = "'../../../../commonlib/trunk/images/b_'.$boton.'_click.jpg'";
			$temp->setVar("WO_ADD", '<input name="b_'.$boton.'" id="b_'.$boton.'" type="button" onmouseover="entrada(this, '.$ruta_over.')" onmouseout="salida(this, '.$ruta_out.')" onmousedown="down(this, '.$ruta_click.')"'.
												 'style="cursor:pointer;height:68px;width:66px;border: 0;background-image:url(../../../../commonlib/trunk/images/b_'.$boton.'.jpg);background-repeat:no-repeat;background-position:center;border-radius: 15px;"'.
												 'onClick="request_tipo_envio(this);" />');
		}else
			parent::habilita_boton($temp, $boton, $habilita);
	}
	function add() {
		$sel = $_POST['wo_hidden'];
		session::set("tipo_envio_softland", $sel);
		parent::add();
	}
	function procesa_event() {
		if(isset($_POST['wo_hidden']) && ($_POST['wo_hidden']=='VENTAS' || $_POST['wo_hidden']=='COMPRAS' || $_POST['wo_hidden']=='EGRESOS' || $_POST['wo_hidden']=='TRANSBANK' || $_POST['wo_hidden']=='INGRESOS'))
			$this->add();
		else
			parent::procesa_event();
	}
}
?>