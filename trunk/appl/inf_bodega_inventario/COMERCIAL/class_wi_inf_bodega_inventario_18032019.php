<?php
class wi_inf_bodega_inventario extends wi_inf_bodega_inventario_base {
	function wi_inf_bodega_inventario($cod_item_menu) {
		parent::wi_inf_bodega_inventario_base($cod_item_menu); 

		$this->xml = session::get('K_ROOT_DIR').'appl/inf_bodega_inventario/COMERCIAL/inf_bodega_inventario.xml';

		$sql_bodega="SELECT COD_BODEGA
							,NOM_BODEGA
					FROM BODEGA
					where COD_BODEGA = 4	-- salaventa
					ORDER BY COD_BODEGA ASC";
		$this->dws['dw_param']->add_control(new drop_down_dw('COD_BODEGA', $sql_bodega, 0, '', false));
	}
}
?>