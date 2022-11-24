<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_solicitud_cotizacion extends w_output_biggi {
   function wo_solicitud_cotizacion() {
   	
   		parent::w_base('solicitud_cotizacion', $_REQUEST['cod_item_menu']);
		 
		$sql = "select	S.COD_SOLICITUD_COTIZACION	
						,convert(varchar(20), S.FECHA_SOLICITUD_COTIZACION, 103) FECHA_SOLICITUD_COTIZACION
						,CONVERT(VARCHAR(8),S.FECHA_SOLICITUD_COTIZACION, 108) HORA_SOLICITUD_COTIZACION
						,S.FECHA_SOLICITUD_COTIZACION DATE_FECHA_SOLICITUD_COTIZACION
						,C.RUT
						,C.NOM_CONTACTO EMPRESA
						,CP.NOM_PERSONA
						,COD_COTIZACION 
						,CP.NOM_PERSONA NOM_CONTACTO
						,S.TOTAL_NETO
						,(SELECT INI_USUARIO FROM USUARIO WHERE COD_USUARIO = S.COD_USUARIO_VENDEDOR1_RESP) NOM_RESPONSABLE
						,ESC.NOM_ESTADO_SOLICITUD_COTIZACION NOM_ESTADO_SOLICITUD
						,(select nom_estado_cotizacion from ESTADO_COTIZACION EC where COD_ESTADO_COTIZACION = CO.COD_ESTADO_COTIZACION)  NOM_ESTADO_COTIZACION
			from		SOLICITUD_COTIZACION S LEFT OUTER JOIN COTIZACION CO ON S.COD_SOLICITUD_COTIZACION = CO.COD_SOLICITUD_COTIZACION
												LEFT OUTER JOIN ESTADO_SOLICITUD_COTIZACION ESC  on S.COD_ESTADO_SOLICITUD_COTIZACION = ESC.COD_ESTADO_SOLICITUD_COTIZACION
						,CONTACTO_PERSONA CP
						,CONTACTO C
			where		C.COD_CONTACTO = S.COD_CONTACTO
				  AND   CP.COD_CONTACTO = C.COD_CONTACTO
			order by	S.COD_SOLICITUD_COTIZACION DESC";

     	parent::w_output_biggi('solicitud_cotizacion', $sql, $_REQUEST['cod_item_menu']);
				
		$this->dw->add_control(new static_link('COD_COTIZACION', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=solicitud_cotizacion&modulo_destino=cotizacion&cod_modulo_destino=[COD_COTIZACION]&cod_item_menu=1505&DESDE_OUTPUT=0'));
		$this->dw->add_control(new edit_precio('TOTAL_NETO'));
      	
	    // headers
      	$this->add_header($control = new header_date('FECHA_SOLICITUD_COTIZACION', 'S.FECHA_SOLICITUD_COTIZACION', 'Fecha'));
	    $control->field_bd_order = 'DATE_FECHA_SOLICITUD_COTIZACION';
	    $this->add_header(new header_num('COD_SOLICITUD_COTIZACION', 'S.COD_SOLICITUD_COTIZACION', 'Cod'));
	    $this->add_header(new header_text('HORA_SOLICITUD_COTIZACION', 'HORA_SOLICITUD_COTIZACION', 'Hora'));
	  	$this->add_header(new header_text('RUT', 'C.RUT', 'Rut'));
	 	$this->add_header(new header_text('NOM_CONTACTO', 'CP.NOM_PERSONA', 'Nombre Contacto'));
	    $this->add_header(new header_text('EMPRESA', 'C.NOM_CONTACTO', 'Empresa'));
	    $this->add_header(new header_num('COD_COTIZACION', 'COD_COTIZACION', 'N� Cotizaci�n'));
	    $this->add_header(new header_num('TOTAL_NETO', 'S.TOTAL_NETO', 'Total Neto'));
		$this->add_header(new header_text('NOM_RESPONSABLE', 'NOM_RESPONSABLE', 'Resp'));
		
		$sql_estado = "select COD_ESTADO_SOLICITUD_COTIZACION ,NOM_ESTADO_SOLICITUD_COTIZACION from ESTADO_SOLICITUD_COTIZACION order by	COD_ESTADO_SOLICITUD_COTIZACION";
      	$this->add_header(new header_drop_down('NOM_ESTADO_SOLICITUD', 'ESC.COD_ESTADO_SOLICITUD_COTIZACION', 'Estado', $sql_estado));
      	
      	$sql = "select NOM_ESTADO_COTIZACION from ESTADO_COTIZACION EC where COD_ESTADO_COTIZACION = CO.COD_ESTADO_COTIZACION";
      	$this->add_header(new header_drop_down('NOM_ESTADO_COTIZACION', 'CO.COD_ESTADO_COTIZACION', 'Estado Cotizaci�n', $sql));
	}
}
?>