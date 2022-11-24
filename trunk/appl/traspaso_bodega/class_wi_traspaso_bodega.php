<?php
require_once(dirname(__FILE__) . "/../../../../commonlib/trunk/php/auto_load.php");

class dw_item_traspaso_bodega extends datawindow {
    function add_controls_producto_help() {
		/* Agrega los constrols standar para manejar la selección de productos con help					
			 Los anchos y maximos de cada campo quedan fijos, la idea es que sean iguales en todos los formularios
			 si se desean tamaños distintos se debe reiimplementar esta función
		*/
		
		if (isset($this->controls['PRECIO']))
			$num_dec = $this->controls['PRECIO']->num_dec;
		else
			$num_dec = 0;
		$java_script = "help_producto(this, ".$num_dec.");";

		$this->add_control($control = new edit_text_upper('COD_PRODUCTO', 10, 30));
		$control->set_onChange($java_script);
		$this->add_control($control = new edit_text_upper('NOM_PRODUCTO', 60, 100));
		$control->set_onChange($java_script);

		// Se guarda el old para los casos en que una validación necesite volver al valor OLD  
		$this->add_control($control = new edit_text_upper('COD_PRODUCTO_OLD', 30, 30, 'hidden'));
		
		// mandatorys
		$this->set_mandatory('COD_PRODUCTO', 'Código del producto');
		$this->set_mandatory('NOM_PRODUCTO', 'Descripción del producto');
	}
	function dw_item_traspaso_bodega() {		
		$sql = "SELECT	COD_ITEM_TRASPASO_BODEGA,
						ITEM,
                        ITEM ITEM_H,
						COD_PRODUCTO,
						NOM_PRODUCTO,
						CT_STOCK,
                        CT_STOCK CT_STOCK_H,
                        CT_TRASPASAR
				FROM	ITEM_TRASPASO_BODEGA
				WHERE 	COD_TRASPASO_BODEGA = {KEY1} ORDER BY ITEM ASC";

		parent::datawindow($sql, 'ITEM_TRASPASO_BODEGA', true, true);	

		$this->add_control(new edit_text('COD_ITEM_TRASPASO_BODEGA',10, 10, 'hidden'));
		$this->add_control(new static_text('ITEM',4 , 5));
		$this->add_control(new edit_text_hidden('ITEM_H'));
		$this->add_control($control = new edit_cantidad('CT_TRASPASAR',10,10));
		  $control->set_onChange("validaStock(this);");
		$this->add_control(new static_num('CT_STOCK'));
		$this->add_control(new edit_text_hidden('CT_STOCK_H'));
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		
		$this->set_first_focus('COD_PRODUCTO');

		// asigna los mandatorys
		$this->set_mandatory('COD_PRODUCTO', 'Código del producto');
		$this->set_mandatory('CT_TRASPASAR', 'Cantidad Solicitada');
		
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'ITEM', $this->row_count());
		return $row;
	}
	function update($db, $cod_traspaso_bodega)	{
		$sp = 'spu_item_traspaso_bodega';
										
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i); 
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW || $statuts == K_ROW_MODIFIED)
				continue;

			$cod_item_traspaso_bodega	= $this->get_item($i, 'COD_ITEM_TRASPASO_BODEGA');
			$item 						= $this->get_item($i, 'ITEM_H');
			$cod_producto 				= $this->get_item($i, 'COD_PRODUCTO');
			$nom_producto 				= $this->get_item($i, 'NOM_PRODUCTO');
			$ct_traspasar				= $this->get_item($i, 'CT_TRASPASAR');
			$ct_stock 					= $this->get_item($i, 'CT_STOCK_H');
            if(strlen($cod_producto)<1)
                continue;
			$cod_item_traspaso_bodega = ($cod_item_traspaso_bodega=='') ? "null" : $cod_item_traspaso_bodega;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			/*elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';*/

			$param = "'$operacion'
						,$cod_item_traspaso_bodega
						,$cod_traspaso_bodega
						,'$item'
						,'$cod_producto'
						,'$nom_producto'
						,$ct_stock
                        ,$ct_traspasar";
			
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
		}
		/*for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$cod_item_traspaso_bodega = $this->get_item($i, 'COD_ITEM_TRASPASO_BODEGA', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_item_traspaso_bodega")){
				return false;
			}
		}*/
		
		return true;
	}
}

class wi_traspaso_bodega extends w_input {
	function wi_traspaso_bodega($cod_item_menu) {
		parent::w_input('traspaso_bodega', $cod_item_menu);
		
		$sql = "SELECT	COD_TRASPASO_BODEGA,
                        COD_TRASPASO_BODEGA COD_TRASPASO_BODEGA_H,
						convert(varchar(20), FECHA_TRASPASO_BODEGA, 103) FECHA_TRASPASO_BODEGA,
                        convert(varchar(20), FECHA_REGISTRO, 103) FECHA_REGISTRO,
						U.NOM_USUARIO,
                        COD_ESTADO_TRASPASO,
						COD_BODEGA_ORIGEN,
						COD_BODEGA_DESTINO,
						REFERENCIA
                        ,OBS
                        ,'' CANT_ITEMS
				FROM	TRASPASO_BODEGA TB, USUARIO U
				WHERE 	COD_TRASPASO_BODEGA = {KEY1}
				AND		TB.COD_USUARIO = U.COD_USUARIO";

		$this->dws['dw_traspaso_bodega'] = new datawindow($sql);
		
		$sql = "select COD_BODEGA
						,NOM_BODEGA
				from BODEGA";
		
		$this->dws['dw_traspaso_bodega']->add_control($control = new drop_down_dw('COD_BODEGA_ORIGEN', $sql));
		  $control->set_onChange("limpiaTabla();");
		$this->dws['dw_traspaso_bodega']->add_control($control = new drop_down_dw('COD_BODEGA_DESTINO', $sql));
		  $control->set_onChange("chqueaOrigen();");
		$this->dws['dw_traspaso_bodega']->add_control(new edit_text_upper('REFERENCIA', 140,100));
		$this->dws['dw_traspaso_bodega']->add_control(new static_text('FECHA_TRASPASO_BODEGA'));
		$this->dws['dw_traspaso_bodega']->add_control(new static_text('FECHA_REGISTRO'));
		$this->dws['dw_traspaso_bodega']->add_control(new edit_text_hidden('COD_TRASPASO_BODEGA_H'));
		
		$sql = "SELECT COD_ESTADO_TRASPASO,NOM_ESTADO 
                FROM ESTADO_TRASPASO_BODEGA";
		
		$this->dws['dw_traspaso_bodega']->add_control(new drop_down_dw('COD_ESTADO_TRASPASO', $sql));
		$this->dws['dw_traspaso_bodega']->add_control(new edit_text_multiline('OBS', 130, 3));
		
		$this->dws['dw_traspaso_bodega']->set_mandatory('COD_BODEGA_ORIGEN', 'Bodega Origen');
		$this->dws['dw_traspaso_bodega']->set_mandatory('COD_BODEGA_DESTINO', 'Bodega Destino');
		$this->dws['dw_traspaso_bodega']->set_mandatory('FECHA_TRASPASO_BODEGA', 'Fecha traspaso');
		$this->dws['dw_traspaso_bodega']->set_mandatory('REFERENCIA', 'Refencia');

		$this->dws['dw_item_traspaso_bodega'] = new dw_item_traspaso_bodega();

		$this->b_print_visible = false;
	}
	function new_record() {
		$this->dws['dw_traspaso_bodega']->insert_row();	
		$this->dws['dw_traspaso_bodega']->set_item(0, 'FECHA_TRASPASO_BODEGA', $this->current_date());
		$this->dws['dw_traspaso_bodega']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->dws['dw_traspaso_bodega']->set_item(0, 'COD_ESTADO_TRASPASO', 1);
		$this->dws['dw_traspaso_bodega']->set_entrable('COD_ESTADO_TRASPASO', false);
	}
	function load_record() {
		$COD_TRASPASO_BODEGA = $this->get_item_wo($this->current_record, 'COD_TRASPASO_BODEGA');
		$this->dws['dw_traspaso_bodega']->retrieve($COD_TRASPASO_BODEGA);	
		$this->dws['dw_item_traspaso_bodega']->retrieve($COD_TRASPASO_BODEGA);
		$this->dws['dw_item_traspaso_bodega']->set_entrable_dw(false);
		$cod_estado = $this->dws['dw_traspaso_bodega']->get_item(0, 'COD_ESTADO_TRASPASO');
		$this->dws['dw_traspaso_bodega']->set_entrable('COD_BODEGA_ORIGEN', false);
		$this->dws['dw_traspaso_bodega']->set_entrable('COD_BODEGA_DESTINO', false);
		
		if($cod_estado == 2 || $cod_estado == 3){//confirmado o anulado
		  $this->dws['dw_traspaso_bodega']->set_entrable_dw(false);
		  $this->b_modify_visible	 = false;
		  $this->b_save_visible	 = false;
		  $this->b_no_save_visible	 = false;
		}
		$this->b_print_visible	 = true;
		
		$ITEMS =  $this->dws['dw_item_traspaso_bodega']->row_count();
		$this->dws['dw_traspaso_bodega']->set_item(0, 'CANT_ITEMS', $ITEMS);
	}

	function get_key() {
		return $this->dws['dw_traspaso_bodega']->get_item(0, 'COD_TRASPASO_BODEGA');
	}
	function save_record($db) {
		$cod_traspaso_bodega = $this->get_key();
		$cod_bodega_origen = $this->dws['dw_traspaso_bodega']->get_item(0, 'COD_BODEGA_ORIGEN');
		$cod_bodega_destino = $this->dws['dw_traspaso_bodega']->get_item(0, 'COD_BODEGA_DESTINO');
		$cod_estado = $this->dws['dw_traspaso_bodega']->get_item(0, 'COD_ESTADO_TRASPASO');
		$referencia = $this->dws['dw_traspaso_bodega']->get_item(0, 'REFERENCIA');
		$obs = $this->dws['dw_traspaso_bodega']->get_item(0, 'OBS');
		
		
		$cod_traspaso_bodega  = ($cod_traspaso_bodega=='') ? 'null' : $cod_traspaso_bodega;
		$referencia           = ($referencia=='') ? 'null' : "'$referencia'";
		$obs                  = ($obs=='') ? 'null' : "'$obs'";
		
								
		$sp = 'spu_traspaso_bodega';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    		    
	    $param	= "'$operacion'
	    			,$cod_traspaso_bodega
                    ,$this->cod_usuario
                    ,$cod_bodega_origen
	    			,$cod_bodega_destino
	    			,$cod_estado
	    			,$referencia
	    			,$obs";
	    	
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_traspaso_bodega = $db->GET_IDENTITY();
				$this->dws['dw_traspaso_bodega']->set_item(0, 'COD_TRASPASO_BODEGA', $cod_traspaso_bodega);
			}
			
			if (!$this->dws['dw_item_traspaso_bodega']->update($db, $cod_traspaso_bodega))
				return false;
				
			$this->dws['dw_traspaso_bodega']->set_entrable('COD_ESTADO_TRASPASO', true);
			return true;
		}
		return false;		
				
	}
}
?>