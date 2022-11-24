<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class dw_item_solicitud_cotizacion extends datawindow {
	function dw_item_solicitud_cotizacion() {
		$sql = "SELECT		COD_PRODUCTO
							,NOM_PRODUCTO
							,CANTIDAD
							,PRECIO
							,TOTAL_PRECIO
				from		ITEM_SOLICITUD_COTIZACION
				where		COD_SOLICITUD_COTIZACION = {KEY1}";


		parent::datawindow($sql, 'ITEM_SOLICITUD_COTIZACION', false, false);

		$this->add_control(new static_text('COD_PRODUCTO',12,10));
		$this->add_control(new static_text('NOM_PRODUCTO',12,10));
		$this->add_control(new static_num('CANTIDAD',1));
		$this->add_control(new static_num('PRECIO'));
		$this->add_control(new static_num('TOTAL_PRECIO'));
		
	}	
}
class wi_solicitud_cotizacion extends w_input{		
	function wi_solicitud_cotizacion($cod_item_menu) {
		parent::w_input('solicitud_cotizacion', $cod_item_menu);		
		$sql = "SELECT	S.COD_SOLICITUD_COTIZACION	
						,convert(varchar(20), S.FECHA_SOLICITUD_COTIZACION, 103) FECHA_SOLICITUD_COTIZACION
						,C.RUT
						,CP.NOM_PERSONA 
						,C.NOM_CONTACTO 
						,C.NOM_CIUDAD
						,CP.MAIL
						,dbo.f_contacto_telefono(CP.COD_CONTACTO_PERSONA,1) TELEFONO 
						,dbo.f_contacto_telefono(CP.COD_CONTACTO_PERSONA,2) CELULAR 
						,LL.MENSAJE
						,TOTAL_NETO
						,PORC_IVA 
						,MONTO_IVA
						,TOTAL_CON_IVA
 			from		SOLICITUD_COTIZACION S
 						,CONTACTO C LEFT OUTER JOIN CONTACTO_TELEFONO CT ON CT.COD_CONTACTO  = C.COD_CONTACTO 
						            LEFT OUTER JOIN CONTACTO_PERSONA CP ON CP.COD_CONTACTO = C.COD_CONTACTO
								   LEFT OUTER JOIN LLAMADO LL ON LL.COD_CONTACTO  = C.COD_CONTACTO
 			where		S.COD_SOLICITUD_COTIZACION = {KEY1}  
			AND			C.COD_CONTACTO = S.COD_CONTACTO";

		
		// DATAWINDOWS SOLICITUD_COTIZACION
		$this->dws['dw_solicitud_cotizacion'] = new  datawindow($sql);
		$this->dws['dw_item_solicitud_cotizacion'] = new dw_item_solicitud_cotizacion();
		

		// DATOS GENERALES
		$this->dws['dw_solicitud_cotizacion']->add_control(new static_text('FECHA_SOLICITUD_COTIZACION'));
		$this->dws['dw_solicitud_cotizacion']->add_control(new static_text('RUT'));
		$this->dws['dw_solicitud_cotizacion']->add_control(new static_num('TOTAL_NETO'));
		$this->dws['dw_solicitud_cotizacion']->add_control(new static_num('PORC_IVA',1));
		$this->dws['dw_solicitud_cotizacion']->add_control(new static_num('MONTO_IVA'));
		$this->dws['dw_solicitud_cotizacion']->add_control(new static_num('TOTAL_CON_IVA'));
		
}
	function load_record() {
		$cod_solicitud_cotizacion = $this->get_item_wo($this->current_record, 'COD_SOLICITUD_COTIZACION');
		$this->dws['dw_solicitud_cotizacion']->retrieve($cod_solicitud_cotizacion);
		$this->dws['dw_item_solicitud_cotizacion']->retrieve($cod_solicitud_cotizacion);
		
	}
	function get_key() {
		return $this->dws['dw_solicitud_cotizacion']->get_item(0, 'COD_SOLICITUD_COTIZACION');
	}	
}
?>