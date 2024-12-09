<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_informe_comision extends w_output_biggi{
   	function wo_informe_comision(){

        $sql = "SELECT COD_INFORME_COMISION
                    ,CONVERT(VARCHAR, FECHA_REGISTRO, 103) FECHA_REGISTRO
                    ,FECHA_REGISTRO DATE_FECHA_REGISTRO
                    ,IC.COD_ESTADO_INFORME_COMISION
                    ,NOM_ESTADO_INFORME_COMISION
                    ,IC.COD_USUARIO
                    ,NOM_USUARIO
                    ,REFERENCIA
                FROM INFORME_COMISION IC
                    ,ESTADO_INFORME_COMISION EIC
                    ,USUARIO U
                WHERE IC.COD_USUARIO = U.COD_USUARIO
                AND IC.COD_ESTADO_INFORME_COMISION = EIC.COD_ESTADO_INFORME_COMISION
                ORDER BY COD_INFORME_COMISION DESC";
			
   		parent::w_output_biggi('informe_comision', $sql, $_REQUEST['cod_item_menu']);

        // headers 
        $this->add_header(new header_num('COD_INFORME_COMISION', 'COD_INFORME_COMISION', 'Código'));
        $this->add_header($control = new header_date('FECHA_REGISTRO', 'FECHA_REGISTRO', 'Fecha '));
		$control->field_bd_order = 'DATE_FECHA_REGISTRO';
        $sql = "SELECT COD_ESTADO_INFORME_COMISION
                      ,NOM_ESTADO_INFORME_COMISION
                FROM ESTADO_INFORME_COMISION";
        $this->add_header(new header_drop_down('NOM_ESTADO_INFORME_COMISION','NOM_ESTADO_INFORME_COMISION', 'Estado', $sql));
        $sql = "SELECT COD_USUARIO
                      ,NOM_USUARIO
                FROM USUARIO";
        $this->add_header(new header_drop_down('NOM_USUARIO', 'NOM_USUARIO', 'Usuario', $sql));
        $this->add_header(new header_text('REFERENCIA', 'REFERENCIA', 'Referencia'));
   	}

    function habilita_boton($temp, $boton, $habilita){
        parent::habilita_boton($temp, $boton, $habilita);

		if($boton == 'create'){
            if($habilita){
                $ruta_over = "'../../../../commonlib/trunk/images/b_create_over.jpg'";
                $ruta_out = "'../../../../commonlib/trunk/images/b_create.jpg'";
                $ruta_click = "'../../../../commonlib/trunk/images/b_create_click.jpg'";

                $control = '<input name="b_'.$boton.'" id="b_'.$boton.'" type="button" onmouseover="entrada(this, '.$ruta_over.')" onmouseout="salida(this, '.$ruta_out.')" onmousedown="down(this, '.$ruta_click.')"'.
							'style="cursor:pointer;height:68px;width:66px;border: 0;background-image:url(../../../../commonlib/trunk/images/b_'.$boton.'.jpg);background-repeat:no-repeat;background-position:center;border-radius: 15px;"'.
							'onClick="request_crear_desde();" />';
            }else{
                $control = '<img src="../../images_appl/b_'.$boton.'_d.jpg">';
            }

			
			$temp->setVar("WO_CREATE", $control);
		}	
	}

	function redraw($temp){
		parent::redraw($temp);
		$this->habilita_boton($temp, 'create', true);	
	}

    function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_comision($_POST['wo_hidden']);
		else
			parent::procesa_event();
	}

    function crear_comision($cod_empresa_sox){
        session::set('INF_COMISION', $cod_empresa_sox);
		$this->add();
    }
}
?>