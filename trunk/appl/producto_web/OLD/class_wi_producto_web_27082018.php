<?php
require_once(dirname(__FILE__) . "/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__) . "/../empresa/class_dw_help_empresa.php");

class dw_producto_especificacion extends datawindow{
	function dw_producto_especificacion(){
		$sql = "SELECT COD_PRODUCTO_ESPECIFICACION
					  ,COD_PRODUCTO
					  ,COD_ESPECIFICACION
					  ,ORDEN 
				FROM PRODUCTO_ESPECIFICACION
				WHERE COD_PRODUCTO = '{KEY1}'";
		
		parent::datawindow($sql, 'PRODUCTO_ESPECIFICACION', true, true);
		
		$sql = "SELECT COD_ESPECIFICACION
					  ,NOM_ESPECIFICACION
				FROM ESPECIFICACION
				ORDER BY NOM_ESPECIFICACION";
		$this->add_control(new drop_down_dw('COD_ESPECIFICACION', $sql,300));
		$this->add_control(new edit_num('ORDEN', 10, 10));
		
		// asigna los mandatorys
		$this->set_mandatory('COD_ESPECIFICACION', 'Especificacion');
		$this->set_mandatory('ORDEN', 'Orden');
	}
	
	function fill_template(&$temp) {
		parent::fill_template($temp);

		if ($this->b_add_line_visible) {
			if ($this->entrable)
				$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_ad(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer">';
			else 
				$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line_d.jpg">';
				
			$temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);	
		}
		
	}
	
	function update($db){
		$sp = 'spu_producto_especificacion';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
			
			$COD_PRODUCTO_ESPECIFICACION	= $this->get_item($i, 'COD_PRODUCTO_ESPECIFICACION');
			$COD_PRODUCTO					= $this->get_item($i, 'COD_PRODUCTO');
			$COD_ESPECIFICACION				= $this->get_item($i, 'COD_ESPECIFICACION');
			$ORDEN							= $this->get_item($i, 'ORDEN');
			
			$COD_PRODUCTO_ESPECIFICACION = ($COD_PRODUCTO_ESPECIFICACION == '') ? "null" : $COD_PRODUCTO_ESPECIFICACION;

			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
			
			$param = "'$operacion', $COD_PRODUCTO_ESPECIFICACION, '$COD_PRODUCTO', $COD_ESPECIFICACION, $ORDEN";
			
			if (!$db->EXECUTE_SP($sp, $param)){
				return false;
			}
		}
		
		for ($i = 0; $i < $this->row_count('delete'); $i++){
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED){
				continue;
			}
			$COD_PRODUCTO_ESPECIFICACION = $this->get_item($i, 'COD_PRODUCTO_ESPECIFICACION', 'delete');
			
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_PRODUCTO_ESPECIFICACION")){
				return false;
			}
		}
		return true;
	}
}

class dw_foto_ficha_folleto extends datawindow{
	function dw_foto_ficha_folleto(){
		$sql = "SELECT COD_FOTO_FICHA_FOLLETO
					  ,COD_PRODUCTO
					  ,NOM_FOTO
					  ,COORDENADA_X
					  ,COORDENADA_Y
				FROM FOTO_FICHA_FOLLETO
				WHERE COD_PRODUCTO = '{KEY1}'";
		
		parent::datawindow($sql, 'FOTO_FICHA_FOLLETO', true, true);
		
		$this->add_control($control = new edit_num('COORDENADA_X', 10, 10, 0, false));
		$this->add_control($control = new edit_num('COORDENADA_Y', 10, 10, 0, false));
		
		// asigna los mandatorys
		$this->set_mandatory('COORDENADA_X', 'Cordenada X');
		$this->set_mandatory('COORDENADA_Y', 'Cordenada Y');
	}
	
	function update($db){
		$sp = 'spu_foto_ficha_folleto';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
			
			$COD_FOTO_FICHA_FOLLETO	= $this->get_item($i, 'COD_FOTO_FICHA_FOLLETO');
			$COD_PRODUCTO			= $this->get_item($i, 'COD_PRODUCTO');
			$NOM_FOTO				= $this->get_item($i, 'NOM_FOTO');
			$COORDENADA_X			= $this->get_item($i, 'COORDENADA_X');
			$COORDENADA_Y			= $this->get_item($i, 'COORDENADA_Y');
			
			$COD_FOTO_FICHA_FOLLETO = ($COD_FOTO_FICHA_FOLLETO == '') ? "null" : $COD_FOTO_FICHA_FOLLETO;

			/*if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';*/
			
			$param = "'UPDATE', $COD_FOTO_FICHA_FOLLETO, '$COD_PRODUCTO','$NOM_FOTO',$COORDENADA_X, $COORDENADA_Y";
			
			if (!$db->EXECUTE_SP($sp, $param)){
				return false;
			}
		}
		return true;
	}
}

class dw_atributo_destacado extends datawindow{
	function dw_atributo_destacado(){
		$sql = "SELECT COD_ATRIBUTO_DESTACADO
					  ,COD_PRODUCTO				AD_COD_PRODUCTO
					  ,NOM_ATRIBUTO				AD_NOM_ATRIBUTO
					  ,ORDEN					AD_ORDEN
				FROM ATRIBUTO_DESTACADO
				WHERE COD_PRODUCTO = '{KEY1}'
				ORDER BY ORDEN";
		
		parent::datawindow($sql, 'ATRIBUTO_DESTACADO', true, true);
		
		$this->add_control(new edit_num('AD_ORDEN', 10));
		$this->add_control(new edit_text('AD_NOM_ATRIBUTO',40, 100));
		
		// asigna los mandatorys
		$this->set_mandatory('AD_NOM_ATRIBUTO', 'Nombre Atributo');
		$this->set_mandatory('AD_ORDEN', 'Orden');

		$this->set_first_focus('AD_NOM_ATRIBUTO');
	}
	function insert_row($row = -1){
		$row = parent::insert_row($row);
		$this->set_item($row, 'AD_ORDEN', $this->row_count() * 1);
		return $row;
	}
	
	function fill_template(&$temp) {
		parent::fill_template($temp);

		if ($this->b_add_line_visible) {
			if ($this->entrable)
				$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_ad(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer">';
			else 
				$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line_d.jpg">';
				
			$temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);	
		}
		
	}
	
	function update($db){
		$sp = 'spu_atributo_destacado';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
			
			$cod_atributo_destacado	= $this->get_item($i, 'COD_ATRIBUTO_DESTACADO');
			$cod_producto			= $this->get_item($i, 'AD_COD_PRODUCTO');
			$nom_atributo			= $this->get_item($i, 'AD_NOM_ATRIBUTO');
			$orden					= $this->get_item($i, 'AD_ORDEN');
			
			$cod_atributo_destacado = ($cod_atributo_destacado == '') ? "null" : $cod_atributo_destacado;

			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
			
			$param = "'$operacion',$cod_atributo_destacado, '$cod_producto','$nom_atributo', $orden";
			
			if (!$db->EXECUTE_SP($sp, $param)){
				return false;
			}
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++){
			//$cod_producto = $this->get_item($i, 'COD_PRODUCTO');
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED){
				continue;
			}
			$cod_atributo_destacado = $this->get_item($i, 'COD_ATRIBUTO_DESTACADO', 'delete');
			
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_atributo_destacado")){
				return false;
			}
		}
		return true;
	}
}


class dw_familia_accesorio extends datawindow{
	function dw_familia_accesorio(){
		$sql = "SELECT COD_FAMILIA_ACCESORIO
	   					,ORDEN FA_ORDEN
	   					,COD_FAMILIA_PRODUCTO
	   					,COD_PRODUCTO
	   					,COD_FAMILIA COD_FAMILIA_ACC  
				FROM FAMILIA_ACCESORIO
				WHERE COD_PRODUCTO = '{KEY1}'
				ORDER BY	FA_ORDEN ASC";
		
		parent::datawindow($sql, 'FAMILIA_ACCESORIO', true, true);
		$this->add_control(new edit_num('FA_ORDEN', 10));
		
		$sql = "SELECT COD_FAMILIA COD_FAMILIA_ACC
					  ,NOM_FAMILIA
				 FROM FAMILIA
				ORDER BY NOM_FAMILIA";
		$this->add_control(new drop_down_dw('COD_FAMILIA_ACC', $sql,300));
		
		// asigna los mandatorys
		$this->set_mandatory('FA_ORDEN', 'Orden');
		$this->set_mandatory('NOM_FAMILIA_PRODUCTO', 'Familia');

		// Setea el focus en NOM_ATRIBUTO_PRODUCTO para las nuevas lineas
		$this->set_first_focus('NOM_FAMILIA_PRODUCTO');
	}
	function insert_row($row = -1){
		$row = parent::insert_row($row);
		$this->set_item($row, 'FA_ORDEN', $this->row_count() * 10);
		return $row;
	}
	function update($db){
		$sp = 'spu_familia_accesorio';	
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}
			$cod_familia_accesorio = $this->get_item($i, 'COD_FAMILIA_ACCESORIO');
			$orden = $this->get_item($i, 'FA_ORDEN');
			$cod_producto = $this->get_item($i, 'COD_PRODUCTO');
			$cod_familia_producto = $this->get_item($i, 'COD_FAMILIA_PRODUCTO');
			$cod_familia_cc = $this->get_item($i, 'COD_FAMILIA_ACC');
			
			$cod_familia_accesorio = ($cod_familia_accesorio == '') ? "null" : $cod_familia_accesorio;

			if ($statuts == K_ROW_NEW_MODIFIED){
				$operacion = 'INSERT';
			}
			elseif ($statuts == K_ROW_MODIFIED){
				$operacion = 'UPDATE';
			}
			
			$param = "'$operacion',$cod_familia_accesorio, NULL,'$cod_producto', $orden,$cod_familia_cc";
			
			if (!$db->EXECUTE_SP($sp, $param)){
				return false;
			}
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++){
			//$cod_producto = $this->get_item($i, 'COD_PRODUCTO');
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED){
				continue;
			}
			$cod_familia_accesorio = $this->get_item($i, 'COD_FAMILIA_ACCESORIO', 'delete');
			
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_familia_accesorio")){
				return false;
			}
		}
		return true;
	}
}

class dw_familia_producto extends datawindow{
	
	function dw_familia_producto(){
		
		$sql = "SELECT	COD_FAMILIA_PRODUCTO
						,ORDEN FP_ORDEN
						,NOM_FAMILIA_PRODUCTO
						,COD_PRODUCTO
						,COD_PRODUCTO COD_PRODUCTO_FP
						,COD_FAMILIA
				FROM FAMILIA_PRODUCTO
				WHERE COD_PRODUCTO = '{KEY1}'
				ORDER BY FP_ORDEN ASC";
		
		parent::datawindow($sql, 'FAMILIA_PRODUCTO', true, true);
		$this->add_control(new edit_num('FP_ORDEN', 10));
		
		$sql = "SELECT COD_FAMILIA
					  ,NOM_FAMILIA
				 FROM FAMILIA
				ORDER BY NOM_FAMILIA";
		$this->add_control(new drop_down_dw('COD_FAMILIA', $sql,300));
		// asigna los mandatorys
		$this->set_mandatory('FP_ORDEN', 'Orden');
		$this->set_mandatory('NOM_FAMILIA_PRODUCTO', 'Familia');
		// Setea el focus en NOM_ATRIBUTO_PRODUCTO para las nuevas lineas
		$this->set_first_focus('NOM_FAMILIA_PRODUCTO');
	}
	function insert_row($row = -1){
		$row = parent::insert_row($row);
		$this->set_item($row, 'FP_ORDEN', $this->row_count() * 10);
		return $row;
	}
	function update($db){
		$sp = 'spu_familia_prod';	
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}
			$cod_familia_producto = $this->get_item($i, 'COD_FAMILIA_PRODUCTO');
			$orden = $this->get_item($i, 'FP_ORDEN');
			$cod_producto = $this->get_item($i, 'COD_PRODUCTO');
			$nom_familia_producto = $this->get_item($i, 'NOM_FAMILIA_PRODUCTO');
			$cod_familia = $this->get_item($i, 'COD_FAMILIA');
			
			$cod_familia_producto = ($cod_familia_producto == '') ? "null" : $cod_familia_producto;
			$nom_familia_producto = ($nom_familia_producto == '') ? "null" : $nom_familia_producto;

			if ($statuts == K_ROW_NEW_MODIFIED){
				$operacion = 'INSERT';
			}
			elseif ($statuts == K_ROW_MODIFIED){
				$operacion = 'UPDATE';
			}
			$param = "'$operacion'
						,$cod_familia_producto
						,$nom_familia_producto
						, $cod_familia
						,'$cod_producto'
						, $orden";
						
			if (!$db->EXECUTE_SP($sp, $param)){
				return false;
			}
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++){

			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
			
			$cod_familia_producto = $this->get_item($i, 'COD_FAMILIA_PRODUCTO', 'delete');
			
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_familia_producto")){
				return false;
			}
		}
		return true;
	}
}

class dw_atributo_producto extends datawindow{
	
	function dw_atributo_producto(){
		
		$sql = "select    	COD_ATRIBUTO_PRODUCTO
	                        ,ORDEN AP_ORDEN
	                        ,NOM_ATRIBUTO_PRODUCTO
	                        ,COD_PRODUCTO
	                        ,SALTO_LINEA
              	from      	ATRIBUTO_PRODUCTO
              	where      	COD_PRODUCTO = '{KEY1}'
              	order by	AP_ORDEN asc";
		
		parent::datawindow($sql, 'ATRIBUTO_PRODUCTO', true, true);
		$this->add_control(new edit_num('AP_ORDEN', 10));
		$this->add_control(new edit_text('NOM_ATRIBUTO_PRODUCTO', 100, 1000));
		$this->add_control($control = new edit_check_box('SALTO_LINEA', 'S', 'N'));
		$control->set_onChange("check_salto_linea(this);");
		// asigna los mandatorys
		$this->set_mandatory('AP_ORDEN', 'Orden');
		$this->set_mandatory('NOM_ATRIBUTO_PRODUCTO', 'Atributo');

		// Setea el focus en NOM_ATRIBUTO_PRODUCTO para las nuevas lineas
		$this->set_first_focus('NOM_ATRIBUTO_PRODUCTO');
	}
	function insert_row($row = -1){
		$row = parent::insert_row($row);
		$this->set_item($row, 'AP_ORDEN', $this->row_count() * 10);
		return $row;
	}
	function update($db){
		$sp = 'spu_atributo_producto';	
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}
			$cod_atributo_producto = $this->get_item($i, 'COD_ATRIBUTO_PRODUCTO');
			$orden = $this->get_item($i, 'AP_ORDEN');
			$cod_producto = $this->get_item($i, 'COD_PRODUCTO');
			$nom_atributo_producto = $this->get_item($i, 'NOM_ATRIBUTO_PRODUCTO');
			$salto_linea = $this->get_item($i, 'SALTO_LINEA');
			$cod_atributo_producto = ($cod_atributo_producto == '') ? "null" : $cod_atributo_producto;

			if ($statuts == K_ROW_NEW_MODIFIED){
				$operacion = 'INSERT';
			}
			elseif ($statuts == K_ROW_MODIFIED){
				$operacion = 'UPDATE';
			}
			$param = "'$operacion',$cod_atributo_producto, '$nom_atributo_producto','$cod_producto', $orden, '$salto_linea'";

			if (!$db->EXECUTE_SP($sp, $param)){
				return false;
			}
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++){
			$cod_producto = $this->get_item($i, 'COD_PRODUCTO');
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED){
				continue;
			}
			$cod_atributo_producto = $this->get_item($i, 'COD_ATRIBUTO_PRODUCTO', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_atributo_producto")){
				return false;
			}
		}
		//  Ordernar
		if ($this->row_count() > 0){
			$cod_producto = $this->get_item(0, 'COD_PRODUCTO');
			$parametros_sp = "'ATRIBUTO_PRODUCTO','PRODUCTO', null, '$cod_producto'";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp)){
				return false;
			}
		}
		return true;
	}
}

class wi_producto_web extends w_input{
	
	function wi_producto_web($cod_item_menu){
		parent::w_input('producto_web', $cod_item_menu);
		
		$sql = "select   P.COD_PRODUCTO
						,P.COD_PRODUCTO COD_PRODUCTO_PRINCIPAL
						,P.COD_PRODUCTO COD_PRODUCTO_H
			            ,P.NOM_PRODUCTO NOM_PRODUCTO_PRINCIPAL
			            ,LARGO
			            ,ANCHO
			            ,ALTO
			            ,PESO
			            ,(LARGO/100 * ANCHO/100 * ALTO/100) VOLUMEN
			            ,LARGO_EMBALADO
			            ,ANCHO_EMBALADO
			            ,ALTO_EMBALADO
			            ,PESO_EMBALADO
			            ,(LARGO_EMBALADO/100 * ANCHO_EMBALADO/100 * ALTO_EMBALADO/100) VOLUMEN_EMBALADO
			            ,P.COD_PRODUCTO COD_PRODUCTO_NO_ING 
			            ,P.NOM_PRODUCTO NOM_PRODUCTO_NO_ING
			            ,P.PRECIO_VENTA_PUBLICO PRECIO_VENTA_PUBLICO_NO_ING
			            ,NOM_TIPO_PRODUCTO
			            ,USA_ELECTRICIDAD
			            ,NRO_FASES MONOFASICO
			            ,NRO_FASES TRIFASICO
			            ,CONSUMO_ELECTRICIDAD
			            ,RANGO_TEMPERATURA
			            ,VOLTAJE
			            ,FRECUENCIA
			            ,NRO_CERTIFICADO_ELECTRICO
			            ,USA_GAS
			            ,POTENCIA
			            ,CONSUMO_GAS
			            ,USA_VAPOR
			            ,NRO_CERTIFICADO_GAS
			            ,CONSUMO_VAPOR
			            ,PRESION_VAPOR
			            ,USA_AGUA_FRIA
			            ,USA_AGUA_CALIENTE
			            ,CAUDAL
			            ,PRESION_AGUA
			            ,DIAMETRO_CANERIA
			            ,USA_VENTILACION
			            ,CAIDA_PRESION
			            ,DIAMETRO_DUCTO
			            ,VOLUMEN VOLUMEN_ESP
			            ,NRO_FILTROS
			            ,USA_DESAGUE
			            ,DIAMETRO_DESAGUE
			            ,ES_OFERTA 
			            ,ES_RECICLADO 
			            ,PRECIO_OFERTA
			            ,PUBLICAR_EN_HOME
			            ,CASE ES_OFERTA
			            	WHEN 'S' THEN ''
			            	ELSE 'none'
			            END DISPLAY_TR
			            ,PRECIO_ANTES_DE_OFERTA
			            ,NOM_PRODUCTOT1
			            ,NOM_PRODUCTOT2
			            ,NOM_PRODUCTOT3
        from   			PRODUCTO P
        				,TIPO_PRODUCTO TP
        where			P.COD_PRODUCTO = '{KEY1}'
        AND P.COD_TIPO_PRODUCTO = TP.COD_TIPO_PRODUCTO";
		$this->dws['dw_producto_web'] = new datawindow($sql);

		$this->set_first_focus('COD_PRODUCTO_PRINCIPAL');
		
		$this->dws['dw_producto_web']->add_control(new edit_text('COD_PRODUCTO_H',10, 10, 'hidden'));
		$this->dws['dw_producto_web']->add_control(new edit_text_upper('NOM_PRODUCTO_PRINCIPAL', 100, 100));
		$this->dws['dw_producto_web']->add_control(new edit_num('LARGO'));
		$this->dws['dw_producto_web']->add_control(new edit_num('ANCHO'));
		$this->dws['dw_producto_web']->add_control(new edit_num('ALTO'));
		$this->dws['dw_producto_web']->add_control(new edit_num('PESO'));
		$this->dws['dw_producto_web']->add_control(new edit_num('LARGO_EMBALADO'));
		$this->dws['dw_producto_web']->add_control(new edit_num('ANCHO_EMBALADO'));
		$this->dws['dw_producto_web']->add_control(new edit_num('ALTO_EMBALADO'));
		$this->dws['dw_producto_web']->add_control(new edit_num('PESO_EMBALADO'));
		$this->dws['dw_producto_web']->add_control(new edit_num('VOLUMEN_ESP'));
		$this->dws['dw_producto_web']->add_control(new edit_text('NOM_PRODUCTOT1',40, 100));
		$this->dws['dw_producto_web']->add_control(new edit_text('NOM_PRODUCTOT2',40, 100));
		$this->dws['dw_producto_web']->add_control(new edit_text('NOM_PRODUCTOT3',40, 100));
		$this->dws['dw_producto_web']->set_computed('VOLUMEN', '[LARGO] * [ANCHO] * [ALTO] / 1000000', 4);
		$this->dws['dw_producto_web']->set_computed('VOLUMEN_EMBALADO', '[LARGO_EMBALADO] * [ANCHO_EMBALADO] * [ALTO_EMBALADO] / 1000000', 4);
		
		//especificaciones
		$this->dws['dw_producto_web']->add_control(new edit_check_box('USA_ELECTRICIDAD', 'S', 'N'));
		$this->dws['dw_producto_web']->add_control(new edit_radio_button('TRIFASICO', 'T', 'M', 'TRIFASICO', 'NRO_FASES'));
		$this->dws['dw_producto_web']->add_control(new edit_radio_button('MONOFASICO', 'M', 'T', 'MONOFASICO', 'NRO_FASES'));
		$this->dws['dw_producto_web']->add_control(new edit_num('CONSUMO_ELECTRICIDAD', 16, 16, 2));
		$this->dws['dw_producto_web']->add_control(new edit_num('RANGO_TEMPERATURA'));
		$this->dws['dw_producto_web']->add_control(new edit_num('VOLTAJE'));
		$this->dws['dw_producto_web']->add_control(new edit_num('FRECUENCIA'));
		$this->dws['dw_producto_web']->add_control(new edit_text_upper('NRO_CERTIFICADO_ELECTRICO', 100, 100));
		$this->dws['dw_producto_web']->add_control(new edit_check_box('USA_GAS', 'S', 'N'));
		$this->dws['dw_producto_web']->add_control(new edit_num('POTENCIA'));
		// VMC, 17-08-2011 se deja no ingresable por solicitud de JJ a traves de MH 
		$this->dws['dw_producto_web']->add_control(new edit_num('CONSUMO_GAS'));
		$this->dws['dw_producto_web']->add_control(new edit_text_upper('NRO_CERTIFICADO_GAS', 100, 100));
		$this->dws['dw_producto_web']->add_control(new edit_check_box('USA_VAPOR', 'S', 'N'));
		$this->dws['dw_producto_web']->add_control(new edit_num('POTENCIA_KW'));
		$this->dws['dw_producto_web']->add_control(new edit_num('CONSUMO_VAPOR'));
		$this->dws['dw_producto_web']->add_control(new edit_num('PRESION_VAPOR'));
		$this->dws['dw_producto_web']->add_control(new edit_check_box('USA_AGUA_FRIA', 'S', 'N'));
		$this->dws['dw_producto_web']->add_control(new edit_check_box('USA_AGUA_CALIENTE', 'S', 'N'));
		$this->dws['dw_producto_web']->add_control(new edit_num('CAUDAL'));
		$this->dws['dw_producto_web']->add_control(new edit_num('PRESION_AGUA'));
		$this->dws['dw_producto_web']->add_control(new edit_text('DIAMETRO_CANERIA', 10, 10));
		$this->dws['dw_producto_web']->add_control(new edit_check_box('USA_VENTILACION', 'S', 'N'));
		$this->dws['dw_producto_web']->add_control(new edit_num('CAIDA_PRESION'));
		$this->dws['dw_producto_web']->add_control(new edit_num('DIAMETRO_DUCTO'));
		$this->dws['dw_producto_web']->add_control(new edit_num('NRO_FILTROS'));
		$this->dws['dw_producto_web']->add_control(new edit_check_box('USA_DESAGUE', 'S', 'N'));
		$this->dws['dw_producto_web']->add_control(new edit_text('DIAMETRO_DESAGUE', 10, 10));
		$this->dws['dw_producto_web']->add_control(new edit_text('FOTO_CON_CAMBIO', 10, 10, 'hidden'));
		
		$this->dws['dw_producto_web']->add_control($control = new edit_check_box('ES_OFERTA', 'S', 'N'));
		$control->set_onChange("home_oferta();");
		$this->dws['dw_producto_web']->add_control(new edit_check_box('ES_RECICLADO', 'S', 'N'));
		$this->dws['dw_producto_web']->add_control(new edit_num('PRECIO_OFERTA'));
		$this->dws['dw_producto_web']->add_control(new edit_check_box('PUBLICAR_EN_HOME', 'S', 'N'));
		$this->dws['dw_producto_web']->add_control(new edit_num('PRECIO_ANTES_DE_OFERTA'));
		
		$this->dws['dw_atributo_producto'] = new dw_atributo_producto();
		$this->dws['dw_familia_producto'] = new dw_familia_producto();
		$this->dws['dw_familia_accesorio'] = new dw_familia_accesorio();
		$this->dws['dw_atributo_destacado'] = new dw_atributo_destacado();
		$this->dws['dw_foto_ficha_folleto'] = new dw_foto_ficha_folleto();
		$this->dws['dw_producto_especificacion'] = new dw_producto_especificacion();
		
		$this->add_auditoria('NOM_PRODUCTO');
		
		$this->add_auditoria('LARGO');
		$this->add_auditoria('ANCHO');
		$this->add_auditoria('ALTO');
		$this->add_auditoria('PESO');
		
		$this->add_auditoria('LARGO_EMBALADO');
		$this->add_auditoria('ANCHO_EMBALADO');
		$this->add_auditoria('ALTO_EMBALADO');
		$this->add_auditoria('PESO_EMBALADO');
		
		$this->add_auditoria('USA_ELECTRICIDAD');
		$this->add_auditoria('CONSUMO_ELECTRICIDAD');
		$this->add_auditoria('VOLTAJE');
		$this->add_auditoria('NRO_FASES');
		$this->add_auditoria('RANGO_TEMPERATURA');
		$this->add_auditoria('FRECUENCIA');
		$this->add_auditoria('NRO_CERTIFICADO_ELECTRICO');
		$this->add_auditoria('USA_GAS');
		$this->add_auditoria('POTENCIA');
		
		$this->add_auditoria('CONSUMO_GAS');
		$this->add_auditoria('USA_VAPOR');
		$this->add_auditoria('CONSUMO_VAPOR');
		$this->add_auditoria('PRESION_VAPOR');
		$this->add_auditoria('USA_AGUA_FRIA');
		$this->add_auditoria('USA_AGUA_CALIENTE');
		$this->add_auditoria('CAUDAL');
		
		$this->add_auditoria('PRESION_AGUA');
		$this->add_auditoria('DIAMETRO_CANERIA');
		$this->add_auditoria('USA_VENTILACION');
		$this->add_auditoria('VOLUMEN');
		$this->add_auditoria('CAIDA_PRESION');
		$this->add_auditoria('DIAMETRO_DUCTO');
		$this->add_auditoria('NRO_FILTROS');
		$this->add_auditoria('USA_DESAGUE');
		$this->add_auditoria('DIAMETRO_DESAGUE');
		
		//$this->add_auditoria('FOTO_GRANDE');
		//$this->add_auditoria('FOTO_CHICA');
		
		$this->add_auditoria('ES_OFERTA');
		$this->add_auditoria('PRECIO_OFERTA');
		$this->add_auditoria('ES_RECICLADO');
		
		$this->add_auditoria_relacionada('ATRIBUTO_PRODUCTO','AP_ORDEN', 'ORDEN');
		$this->add_auditoria_relacionada('ATRIBUTO_PRODUCTO','NOM_ATRIBUTO_PRODUCTO');
		
		$this->add_auditoria_relacionada('FAMILIA_PRODUCTO','FP_ORDEN','ORDEN');
		$this->add_auditoria_relacionada('FAMILIA_PRODUCTO','COD_FAMILIA');
		
		$this->add_auditoria_relacionada('FAMILIA_ACCESORIO','FA_ORDEN','ORDEN');
		$this->add_auditoria_relacionada('FAMILIA_ACCESORIO','COD_FAMILIA_ACC','COD_FAMILIA');
		
		
	}
	function habilitar($temp, $habilita){		
		$html = '';
		for ($i = 0; $i < $this->dws['dw_atributo_producto']->row_count(); $i++)
		$html .= '<img src="../../../../commonlib/trunk/images/ico2.gif"width="14"height="15">' . $this->dws['dw_atributo_producto']->get_item($i, 'NOM_ATRIBUTO_PRODUCTO') . '<br>';
		$temp->setVar("LISTA_ATRIBUTOS", $html);
	}
	function make_sql_auditoria_relacionada($tabla) {
		
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'PRODUCTO';
		$sql = parent::make_sql_auditoria_relacionada($tabla);
		$this->nom_tabla = $nom_tabla; 
		return $sql;
		
	}
	function make_sql_auditoria() {
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'PRODUCTO';
		$sql = parent::make_sql_auditoria();
		$this->nom_tabla = $nom_tabla; 
		return $sql;
	}
	function load_record(){
		$cod_producto = $this->get_item_wo($this->current_record, 'COD_PRODUCTO');
		$this->dws['dw_producto_web']->retrieve($cod_producto);
		$this->dws['dw_atributo_producto']->retrieve($cod_producto);
		$this->dws['dw_familia_producto']->retrieve($cod_producto);
		$this->dws['dw_familia_accesorio']->retrieve($cod_producto);
		$this->dws['dw_atributo_destacado']->retrieve($cod_producto);
		$this->dws['dw_foto_ficha_folleto']->retrieve($cod_producto);
		$this->dws['dw_producto_especificacion']->retrieve($cod_producto);
	}

	function get_key(){
		
		$cod_producto = $this->dws['dw_producto_web']->get_item(0, 'COD_PRODUCTO_PRINCIPAL');
		return "'" . $cod_producto . "'";
	}
	
	function save_record($db){		
		$cod_producto 				= $this->dws['dw_producto_web']->get_item(0, 'COD_PRODUCTO_PRINCIPAL');
		$nom_producto 				= $this->dws['dw_producto_web']->get_item(0, 'NOM_PRODUCTO_PRINCIPAL');
		
		$largo 						= $this->dws['dw_producto_web']->get_item(0, 'LARGO');
		$ancho 						= $this->dws['dw_producto_web']->get_item(0, 'ANCHO');
		$alto 						= $this->dws['dw_producto_web']->get_item(0, 'ALTO');
		$peso 						= $this->dws['dw_producto_web']->get_item(0, 'PESO');
		$largo_embalado 			= $this->dws['dw_producto_web']->get_item(0, 'LARGO_EMBALADO');
		$ancho_embalado 			= $this->dws['dw_producto_web']->get_item(0, 'ANCHO_EMBALADO');
		$alto_embalado 				= $this->dws['dw_producto_web']->get_item(0, 'ALTO_EMBALADO');
		$peso_embalado 				= $this->dws['dw_producto_web']->get_item(0, 'PESO_EMBALADO');
		
		$usa_electricidad 			= $this->dws['dw_producto_web']->get_item(0, 'USA_ELECTRICIDAD');
		$nro_fases 					= $this->dws['dw_producto_web']->get_item(0, 'TRIFASICO');
		$consumo_electricidad 		= $this->dws['dw_producto_web']->get_item(0, 'CONSUMO_ELECTRICIDAD');
		$rango_temperatura 			= $this->dws['dw_producto_web']->get_item(0, 'RANGO_TEMPERATURA');
		$voltaje 					= $this->dws['dw_producto_web']->get_item(0, 'VOLTAJE');
		$nro_certificado_electrico 	= $this->dws['dw_producto_web']->get_item(0, 'NRO_CERTIFICADO_ELECTRICO');
		$frecuencia 				= $this->dws['dw_producto_web']->get_item(0, 'FRECUENCIA');
		$usa_gas 					= $this->dws['dw_producto_web']->get_item(0, 'USA_GAS');
		$potencia 					= $this->dws['dw_producto_web']->get_item(0, 'POTENCIA');
		$consumo_gas 				= $this->dws['dw_producto_web']->get_item(0, 'CONSUMO_GAS');
		$nro_certificado_gas 		= $this->dws['dw_producto_web']->get_item(0, 'NRO_CERTIFICADO_GAS');
		$usa_vapor 					= $this->dws['dw_producto_web']->get_item(0, 'USA_VAPOR');
		$consumo_vapor 				= $this->dws['dw_producto_web']->get_item(0, 'CONSUMO_VAPOR');
		$presion_vapor 				= $this->dws['dw_producto_web']->get_item(0, 'PRESION_VAPOR');
		$usa_agua_fria 				= $this->dws['dw_producto_web']->get_item(0, 'USA_AGUA_FRIA');
		$usa_agua_caliente 			= $this->dws['dw_producto_web']->get_item(0, 'USA_AGUA_CALIENTE');
		$caudal 					= $this->dws['dw_producto_web']->get_item(0, 'CAUDAL');
		$presion_agua 				= $this->dws['dw_producto_web']->get_item(0, 'PRESION_AGUA');
		$diametro_caneria 			= $this->dws['dw_producto_web']->get_item(0, 'DIAMETRO_CANERIA');
		$usa_ventilacion 			= $this->dws['dw_producto_web']->get_item(0, 'USA_VENTILACION');
		$volumen					= $this->dws['dw_producto_web']->get_item(0, 'VOLUMEN_ESP');
		$caida_presion 				= $this->dws['dw_producto_web']->get_item(0, 'CAIDA_PRESION');
		$diametro_ducto 			= $this->dws['dw_producto_web']->get_item(0, 'DIAMETRO_DUCTO');
		$nro_filtros 				= $this->dws['dw_producto_web']->get_item(0, 'NRO_FILTROS');
		$usa_desague 				= $this->dws['dw_producto_web']->get_item(0, 'USA_DESAGUE');
		$diametro_desague			= $this->dws['dw_producto_web']->get_item(0, 'DIAMETRO_DESAGUE');
		$publicar_en_home			= $this->dws['dw_producto_web']->get_item(0, 'PUBLICAR_EN_HOME');
		$precio_antes_de_oferta		= $this->dws['dw_producto_web']->get_item(0, 'PRECIO_ANTES_DE_OFERTA');
		$nom_productot1				= $this->dws['dw_producto_web']->get_item(0, 'NOM_PRODUCTOT1');
		$nom_productot2				= $this->dws['dw_producto_web']->get_item(0, 'NOM_PRODUCTOT2');
		$nom_productot3				= $this->dws['dw_producto_web']->get_item(0, 'NOM_PRODUCTOT3');
		
		$es_oferta 				= $this->dws['dw_producto_web']->get_item(0, 'ES_OFERTA');
		$precio_oferta			= $this->dws['dw_producto_web']->get_item(0, 'PRECIO_OFERTA');
		if($precio_oferta == ''){
		$precio_oferta = 0; 
		}
		$es_reciclado			= $this->dws['dw_producto_web']->get_item(0, 'ES_RECICLADO');
		$nom_producto_ingles 		= ($nom_producto_ingles == '') ? "null" : "'$nom_producto_ingles'";
		$cod_familia_producto 		= ($cod_familia_producto == '') ? "null" : $cod_familia_producto;
		$nro_fases 					= ($nro_fases == '') ? "null" : "'$nro_fases'";
		$consumo_electricidad		= ($consumo_electricidad == '') ? "null" : $consumo_electricidad;
		$rango_temperatura 			= ($rango_temperatura == '') ? "null" : "'$rango_temperatura'";
		$voltaje 					= ($voltaje == '') ? "null" : $voltaje;
		$frecuencia 				= ($frecuencia == '') ? "null" : $frecuencia;
		$nro_certificado_electrico	= ($nro_certificado_electrico == '') ? "null" : "'$nro_certificado_electrico'";
		$potencia 					= ($potencia == '') ? "null" : $potencia;
		$consumo_gas 				= ($consumo_gas == '') ? "null" : $consumo_gas;
		$nro_certificado_gas 		= ($nro_certificado_gas == '') ? "null" : "'$nro_certificado_gas'";
		$consumo_vapor 				= ($consumo_vapor == '') ? "null" : $consumo_vapor;
		$presion_vapor 				= ($presion_vapor == '') ? "null" : $presion_vapor;
		$caudal 					= ($caudal == '') ? "null" : $caudal;
		$presion_agua 				= ($presion_agua == '') ? "null" : $presion_agua;
		$diametro_caneria 			= ($diametro_caneria == '') ? "null" : "'$diametro_caneria'";
		$volumen 					= ($volumen == '') ? "null" : $volumen;
		$caida_presion 				= ($caida_presion == '') ? "null" : $caida_presion;
		$potencia_kw				= ($potencia_kw == '') ? "null" : $potencia_kw;
		$diametro_ducto 			= ($diametro_ducto == '') ? "null" : $diametro_ducto;
		$nro_filtros 				= ($nro_filtros == '') ? "null" : $nro_filtros;
		$diametro_desague 			= ($diametro_desague == '') ? "null" : "'$diametro_desague'";
		$stock_critico 				= ($stock_critico == '') ? "null" : $stock_critico;
		$foto_grande 				= ($foto_grande == '') ? "null" : $foto_grande;
		$foto_chica 				= ($foto_chica == '') ? "null" : $foto_chica;
		$cod_producto 				= ($cod_producto == '') ? "null" : $cod_producto;
		$cod_producto_local			= ($cod_producto_local == '') ? "null" : $cod_producto_local;
		$precio_antes_de_oferta		= ($precio_antes_de_oferta == '') ? "null" : $precio_antes_de_oferta;
		$nom_productot1				= ($nom_productot1 == '') ? "null" : "'$nom_productot1'";
		$nom_productot2				= ($nom_productot2 == '') ? "null" : "'$nom_productot2'";
		$nom_productot3				= ($nom_productot3 == '') ? "null" : "'$nom_productot3'";
		
		$sp = 'spu_producto_web';
		$operacion = 'UPDATE';
		$param = "'$operacion'
				,'$cod_producto'
				,'$nom_producto'
				,$largo
				,$ancho
				,$alto
				,$peso
				,$largo_embalado
				,$ancho_embalado
				,$alto_embalado
				,$peso_embalado
				,'$usa_electricidad'
				,$nro_fases
				,$consumo_electricidad
				,$rango_temperatura
				,$voltaje
				,$frecuencia
				,$nro_certificado_electrico
				,'$usa_gas'
				,$potencia
				,$consumo_gas
				,$nro_certificado_gas
				,'$usa_vapor'
				,$consumo_vapor
				,$presion_vapor
				,'$usa_agua_fria'
				,'$usa_agua_caliente'
				,$caudal
				,$presion_agua
				,$diametro_caneria
				,'$usa_ventilacion'
				,$volumen
				,$caida_presion
				,$diametro_ducto
				,$nro_filtros
				,'$usa_desague'
				,$diametro_desague
				,'$es_oferta'
				,$precio_oferta
				,'$es_reciclado'
				,'$publicar_en_home'
				,$precio_antes_de_oferta
				,$nom_productot1
				,$nom_productot2
				,$nom_productot3";
		
		if ($db->EXECUTE_SP($sp, $param)){
			
			for ($i = 0; $i < $this->dws['dw_atributo_producto']->row_count(); $i++){
				$this->dws['dw_atributo_producto']->set_item($i, 'COD_PRODUCTO', $cod_producto);
			}
			for ($i = 0; $i < $this->dws['dw_familia_producto']->row_count(); $i++){
				$this->dws['dw_familia_producto']->set_item($i, 'COD_PRODUCTO', $cod_producto);
			}
			
			for ($i = 0; $i < $this->dws['dw_familia_accesorio']->row_count(); $i++){
					$this->dws['dw_familia_accesorio']->set_item($i, 'COD_PRODUCTO', $cod_producto);
			}
			
			for ($i = 0; $i < $this->dws['dw_atributo_destacado']->row_count(); $i++){
				$this->dws['dw_atributo_destacado']->set_item($i, 'AD_COD_PRODUCTO', $cod_producto);
			}
			
			for ($i = 0; $i < $this->dws['dw_foto_ficha_folleto']->row_count(); $i++){
				$this->dws['dw_foto_ficha_folleto']->set_item($i, 'COD_PRODUCTO', $cod_producto);
			}
			
			for ($i = 0; $i < $this->dws['dw_producto_especificacion']->row_count(); $i++){
				$this->dws['dw_producto_especificacion']->set_item($i, 'COD_PRODUCTO', $cod_producto);
			}
			
			if (!$this->dws['dw_atributo_producto']->update($db))
				return false;
				
			if (!$this->dws['dw_familia_producto']->update($db))
				return false;

			if (!$this->dws['dw_familia_accesorio']->update($db))
				return false;
				
			if (!$this->dws['dw_atributo_destacado']->update($db))
				return false;

			if (!$this->dws['dw_foto_ficha_folleto']->update($db))
				return false;	
			
			if (!$this->dws['dw_producto_especificacion']->update($db))
				return false;	
				
			if (!$this->subir_imagen($db, $cod_producto))
				return false;
				
			
			return true;
		}
		return false;
	}
	function subir_imagen($db, $cod_producto){
		$foto_chica = $_FILES['FOTO_CHICA']['tmp_name'];
		
		//echo $foto_chica.'<br>';
		$foto_grande = $_FILES['FOTO_GRANDE']['tmp_name'];
		//echo '---'.$foto_grande.'---';

		If ($foto_chica <> ''){
			$datastring_chica = file_get_contents($foto_chica);
			$data_chica = unpack("H*hex", $datastring_chica);
			$hexa_chica = '0x' . $data_chica['hex'];
		}
		else {
			$hexa_chica = '0x';
		}

		If ($foto_grande <> ''){
			$datastring_grande = file_get_contents($foto_grande);
			$data_grande = unpack("H*hex", $datastring_grande);
			$hexa_grande = '0x' . $data_grande['hex'];
		}
		else {
			$hexa_grande = '0x';
		}

		$sp = 'sp_subir_imagen';
		$param = "$hexa_chica, $hexa_grande, '$cod_producto'";

		if ($db->EXECUTE_SP($sp, $param)){
			return true;
		}
		return false;
	}
	
	function habilita_boton(&$temp, $boton, $habilita) {
		parent::habilita_boton($temp, $boton, $habilita);
		
		if($boton == 'print_folleto'){
			if($habilita){
				$control = '<input name="b_'.$boton.'" id="b_'.$boton.'" src="../../images_appl/b_'.$boton.'.jpg" type="image" '.
							'onMouseDown="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../images_appl/b_'.$boton.'_click.jpg\',1)" '.
							'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
							'onMouseOver="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../images_appl/b_'.$boton.'_over.jpg\',1)" '.
							'/>';
			}else{
				$control = '<img src="../../images_appl/b_'.$boton.'_d.jpg">';
			}
			
			$temp->setVar("WI_".strtoupper($boton), $control);
		}
		if($boton == 'f_tecnica'){
			if($habilita){
				$control = '<input name="b_'.$boton.'" id="b_'.$boton.'" src="../../images_appl/b_'.$boton.'.jpg" type="image" '.
							'onMouseDown="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../images_appl/b_'.$boton.'_click.jpg\',1)" '.
							'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
							'onMouseOver="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../images_appl/b_'.$boton.'_over.jpg\',1)" '.
							'/>';
			}else{
				$control = '<img src="../../images_appl/b_'.$boton.'_d.jpg">';
			}
			
			$temp->setVar("WI_".strtoupper($boton), $control);
		}
	}
	
	function navegacion(&$temp){
		parent::navegacion($temp);
		
		$priv = $this->get_privilegio_opcion_usuario('999505', $this->cod_usuario);
		if($priv == 'E')
			$this->habilita_boton($temp, 'print_folleto', true);
		else
			$this->habilita_boton($temp, 'print_folleto', false);
		
		$priv = $this->get_privilegio_opcion_usuario('999510', $this->cod_usuario);
		if($priv == 'E')
			$this->habilita_boton($temp, 'f_tecnica', true);
		else
			$this->habilita_boton($temp, 'f_tecnica', false);	
		
	}
	
	function procesa_event(){
		if(isset($_POST['b_print_folleto_x'])){
			$this->print_folleto('1');
		}else if(isset($_POST['b_f_tecnica_x'])){
			$this->print_folleto('2');
		}else{
			parent::procesa_event();
		}	
	}
	
	function print_folleto($tipo_print){
		$cod_producto = str_replace("'", "", $this->get_key());
		$cod_producto_folder = preg_replace("%[^A-Z^0-9^-]%", "_", $cod_producto);
		
		print " <script>window.open('print_especial.php?COD_PRODUCTO=$cod_producto&TIPO_PRINT=$tipo_print')</script>";
		$this->_load_record();
	}
}
?>