<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../empresa/class_dw_help_empresa.php");

class input_file extends edit_control {
	function input_file($field) {
		parent::edit_control($field);
	}
	function draw_entrable($dato, $record) {
		$field = $this->field.'_'.$record;
		return '<input type="file" name="'.$field.'" id="'.$field.'" class="Button" onChange="valida_archivo(this);"/>';
	}
	function draw_no_entrable($dato, $record) {
		return '';
	}
}

class dw_gr_foto extends datawindow{
	function dw_gr_foto(){
		$sql = "SELECT COD_GUIA_RECEPCION_FOTO
					  ,COD_GUIA_RECEPCION						D_COD_GUIA_RECEPCION
					  ,OBS										D_OBS
					  ,NOM_ARCHIVO								D_NOM_ARCHIVO
					  ,convert(varchar, FECHA_REGISTRO, 103)	D_FECHA_REGISTRO
					  ,GRF.COD_USUARIO	          				D_COD_USUARIO
    				  ,U.NOM_USUARIO            				D_NOM_USUARIO
					  ,null										D_FILE
					  ,''										D_DIV_LINK
					  ,null				      					D_COD_ENCRIPT
					  ,'none'          	  	  					D_DIV_FILE
					  ,RIGHT('000' + CAST(ROW_NUMBER()OVER(ORDER BY COD_GUIA_RECEPCION_FOTO DESC) AS VARCHAR), 3) CORRELATIVE
					  ,RUTA_ARCHIVO
	  				  ,NOM_ARCHIVO
				FROM GUIA_RECEPCION_FOTO GRF
					,USUARIO U
				WHERE COD_GUIA_RECEPCION = {KEY1}
				AND GRF.COD_USUARIO = U.COD_USUARIO
				ORDER BY COD_GUIA_RECEPCION_FOTO ASC";

		parent::datawindow($sql, 'GR_FOTO', true, true);

		$this->add_control(new edit_text_upper('D_OBS',78, 50));
		$this->add_control(new static_text('D_NOM_ARCHIVO'));
		$this->add_control(new input_file('D_FILE'));

		$this->set_mandatory('D_FILE', 'Archivo');
	}

	function draw_field($field, $record) {
		if ($field=='D_FILE') {
			$status = $this->get_status_row($record);
			if ($status==K_ROW_NEW || $status==K_ROW_NEW_MODIFIED) {
				$row = $this->redirect($record);
				$dato = $this->get_item($record, $field);
				return $this->controls[$field]->draw_entrable($dato, $row);
			}
			else 
				return $this->controls[$field]->draw_no_entrable($dato, $row);
		}
		else
			return parent::draw_field($field, $record);
	}

	function retrieve($cod_guia_recepcion_foto) {
		parent::retrieve($cod_guia_recepcion_foto);
		for($i=0; $i<$this->row_count(); $i++) {
			$cod_guia_recepcion_foto = $this->get_item($i, 'COD_GUIA_RECEPCION_FOTO');
			$this->set_item($i, 'D_COD_ENCRIPT', base64_encode($cod_guia_recepcion_foto));
		}
	}

	function insert_row($row = -1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'D_COD_USUARIO', $this->cod_usuario);
		$this->set_item($row, 'D_NOM_USUARIO', $this->nom_usuario);
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$this->set_item($row, 'D_FECHA_REGISTRO', $db->current_date());
		$this->set_item($row, 'D_FILE', 'NV_ARCHIVO_'.$this->redirect($row));
		$this->set_item($row, 'D_DIV_LINK', 'none');
		$this->set_item($row, 'D_DIV_FILE', '');
		$this->set_item($row, 'CORRELATIVE', str_pad($row+1, 3, '0', STR_PAD_LEFT));
		return $row;
	}
	
	function get_ruta($cod_guia_recepcion){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT P.VALOR RUTA
				FROM PARAMETRO P
				WHERE P.COD_PARAMETRO = 88";
				  
      	$result = $db->build_results($sql);
      	$folder = $result[0]['RUTA']."/".$cod_guia_recepcion."/";
		if (!file_exists($folder))	
			$res = mkdir($folder, 0777 , true);	// recursive = true		
			
		return $folder;
	}

	function update($db){
		$sp = 'spu_guia_recepcion_foto';

		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;			

			if($statuts == K_ROW_NEW_MODIFIED){
				$operacion = 'INSERT';
				$cod_guia_recepcion_foto = 'null';
				$cod_usuario = $this->cod_usuario;
				$cod_guia_recepcion = $this->get_item($i, 'D_COD_GUIA_RECEPCION');
				$correlative = $this->get_item($i, 'CORRELATIVE');

				// subir archivo
				$ruta_archivo = $this->get_ruta($cod_guia_recepcion);	// obtiene la ruta donde debe quedar 

				// direccion absoluta
				$row = $this->redirect($i);
				$file = 'D_FILE_'.$row;
				$nom_archivo = $_FILES[$file]['name'];
				$char = '';
				$pos  = 0;
				$nom_archivo_s='';

				$nom_archivo = $cod_guia_recepcion.'_'.$correlative.'_'.$nom_archivo;

				$e		= array(archivo::getTipoArchivo($nom_archivo));
				$t		= $_FILES[$file]['size'];
				$tmp	= $_FILES[$file]['tmp_name'];
				
				$archivo = new archivo($nom_archivo, $ruta_archivo, $e,$t,$tmp);
			 	$u = $archivo->upLoadFile();	// sube el archivo al directorio definitivo
			 	
			 	if(!file_exists($ruta_archivo.$nom_archivo)){
			 		$this->alert('Se ha producido un error en la carga del archivo seleccionado.\nPuede deberse a problemas de red y/o archivo da�ado.');
			 		return true;
			 	}
			 	
			}else if ($statuts == K_ROW_MODIFIED) {
				$operacion = 'UPDATE';
				$cod_guia_recepcion_foto = $this->get_item($i, 'COD_GUIA_RECEPCION_FOTO');
				$cod_usuario = 'null';
				$cod_guia_recepcion = 'null';
				$nom_archivo = 'null';
				$ruta_archivo = 'null';
			}

			$obs = $this->get_item($i, 'D_OBS');
			$obs = $obs =='' ? 'null' : "'$obs'";

			$param = "'$operacion'
					,$cod_guia_recepcion_foto 
					,$cod_guia_recepcion 
					,$cod_usuario
					,'$ruta_archivo'
					,'$nom_archivo'
					,$obs";
					
			if (!$db->EXECUTE_SP($sp, $param))
				return false;	
		}
		
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');			
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
			
			$cod_guia_recepcion_foto = $this->get_item($i, 'COD_GUIA_RECEPCION_FOTO', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_guia_recepcion_foto"))
				return false;
				
			$ruta_archivo = $this->get_item($i, 'RUTA_ARCHIVO', 'delete');
			$nom_archivo = $this->get_item($i, 'NOM_ARCHIVO', 'delete');
			
			if (file_exists($ruta_archivo.$nom_archivo))
				unlink($ruta_archivo.$nom_archivo);		
		}

		return true;
	}
}

class dw_item_guia_recepcion extends dw_item_guia_recepcion_base {
	
	const K_ESTADO_GR_EMITIDA 		= 1;
	const K_TIPO_GR_DEVOLUCION		= 1;
	const K_TIPO_GR_GARANTIA		= 2;
	const K_TIPO_GR_OTRO			= 3;
	
	function dw_item_guia_recepcion() {
		$sql = " SELECT IGR.COD_ITEM_GUIA_RECEPCION 
						,IGR.COD_GUIA_RECEPCION 
						,IGR.COD_PRODUCTO
						,IGR.NOM_PRODUCTO
						,IGR.CANTIDAD
						,GR.COD_DOC
						,GR.COD_TIPO_GUIA_RECEPCION
						,IGR.COD_ITEM_DOC
						,case GR.TIPO_DOC 
							when 'FACTURA' THEN dbo.f_gr_fa_cant_por_recep(IGR.COD_ITEM_DOC)+ CANTIDAD
							ELSE CASE GR.TIPO_DOC
								WHEN 'GUIA_DESPACHO' THEN dbo.f_gr_gd_cant_por_recep(IGR.COD_ITEM_DOC)+ CANTIDAD
							end					
						end POR_RECEPCIONAR
						,case GR.TIPO_DOC 
							when 'FACTURA' THEN dbo.f_gr_fa_cant_por_recep(IGR.COD_ITEM_DOC) + CANTIDAD
							ELSE CASE GR.TIPO_DOC
								WHEN 'GUIA_DESPACHO' THEN dbo.f_gr_gd_cant_por_recep(IGR.COD_ITEM_DOC)+ CANTIDAD
							end
						end POR_RECEPCIONAR_H
						,case
							when IGR.COD_ITEM_DOC IS NULL then ''
							else 'none'
						end TD_DISPLAY_ELIMINAR
						,'none' TD_DISPLAY_POR_RECEP
						,GR.TIPO_DOC TIPO_DOC_GR
				FROM    ITEM_GUIA_RECEPCION IGR, GUIA_RECEPCION GR
				WHERE   IGR.COD_GUIA_RECEPCION = {KEY1} AND
						GR.COD_GUIA_RECEPCION  = IGR.COD_GUIA_RECEPCION 
						order by IGR.COD_ITEM_DOC";
		
		 
		parent::datawindow($sql, 'ITEM_GUIA_RECEPCION', true, true);
		$this->add_control(new edit_text_upper('COD_ITEM_GUIA_RECEPCION',10, 10, 'hidden'));
		$this->add_control($control = new edit_cantidad('CANTIDAD',12,10));
		$control->set_onChange("this.value = valida_ct_x_gd(this);");		
		$this->add_control(new static_num('POR_RECEPCIONAR',1));
		$this->add_control(new edit_cantidad('POR_RECEPCIONAR_H',10));
		$this->controls['POR_RECEPCIONAR_H']->type = 'hidden';
		$this->add_control(new edit_num('COD_ITEM_DOC',10, 10));
		$this->controls['COD_ITEM_DOC']->type = 'hidden';
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		$this->set_first_focus('COD_PRODUCTO');
		$this->add_control(new edit_text_upper('NOM_PRODUCTO',100, 100));
		
		// asigna los mandatorys
		$this->set_mandatory('COD_PRODUCTO', 'C�digo del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');
	}
	
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'COD_ITEM_GUIA_RECEPCION', $this->row_count());
		$this->set_item($row, 'TD_DISPLAY_POR_RECEP', 'none');
		return $row;
	}
	
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);
		// si existe COD_ITEM_DOC no despliega boton "-".
		$COD_ITEM = $this->get_item(0, 'COD_ITEM_DOC');
		if ($COD_ITEM != ''){ 
			$row = $this->redirect($record);
			$eliminar = '<img src="../../../../commonlib/trunk/images/b_delete_line.jpg" onClick="del_line_item(\''.$this->label_record.'_'.$row.'\', \''.$this->nom_tabla.'\');" style="display:none">';
			$temp->setVar($this->label_record.".ELIMINAR_".strtoupper($this->label_record), $eliminar);
		}
	}
	
	function fill_template(&$temp) {
		parent::fill_template($temp);
		// si existe COD_DOC no despliega boton "+".
		
		if ($this->row_count()==0)
			$COD_ITEM = '';		// debe ser == '' para que se agregue el boton "+"
		else
			$COD_ITEM = $this->get_item(0, 'COD_ITEM_DOC');

		if ($COD_ITEM != ''){ 
			$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_item(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="display:none">';
			$temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);
		}
	}
	
	function update($db, $COD_GUIA_RECEPCION){
		$sp = 'spu_item_guia_recepcion';
		$operacion = 'DELETE_ALL';
		$param = "'$operacion',null, $COD_GUIA_RECEPCION";			
		if (!$db->EXECUTE_SP($sp, $param)){
			return false;
		}

		for ($i = 0; $i < $this->row_count(); $i++){
		
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}

			$CANTIDAD = $this->get_item($i, 'CANTIDAD');

			if ($CANTIDAD == 0)
				continue;

			$COD_ITEM_GUIA_RECEPCION	= $this->get_item($i, 'COD_ITEM_GUIA_RECEPCION');
			$COD_GUIA_RECEPCION			= $this->get_item($i, 'COD_GUIA_RECEPCION');
			$COD_PRODUCTO	 			= $this->get_item($i, 'COD_PRODUCTO');
			$NOM_PRODUCTO 				= $this->get_item($i, 'NOM_PRODUCTO');
			$CANTIDAD 					= $this->get_item($i, 'CANTIDAD');		
			$COD_ITEM					= $this->get_item($i, 'COD_ITEM_DOC');
			$TIPO_DOC_GR 				= $this->get_item($i, 'TIPO_DOC_GR');
			
			if ($TIPO_DOC_GR == "'FACTURA'")
				$TIPO_DOC = "'ITEM_FACTURA'";
			else if ($TIPO_DOC_GR == "'GUIA_DESPACHO'")
				$TIPO_DOC = "'ITEM_GUIA_DESPACHO'";
			else if ($TIPO_DOC_GR == "'ARRIENDO'")
				$TIPO_DOC = "'ITEM_ARRIENDO'";
			else if ($TIPO_DOC_GR == "'NOTA_VENTA'")
				$TIPO_DOC = "'ITEM_NOTA_VENTA'";	
			else
				$TIPO_DOC = "null";
			
			$COD_ITEM_GUIA_RECEPCION   	= ($COD_ITEM_GUIA_RECEPCION =='') ? "null" : $COD_ITEM_GUIA_RECEPCION;
			$COD_ITEM					= ($COD_ITEM =='') ? "null" : $COD_ITEM;
			
			$operacion = 'INSERT';
			$param = "'$operacion', $COD_ITEM_GUIA_RECEPCION, $COD_GUIA_RECEPCION, '$COD_PRODUCTO', '$NOM_PRODUCTO', $CANTIDAD, $COD_ITEM, $TIPO_DOC";
			if (!$db->EXECUTE_SP($sp, $param))	
				return false;
		}
    	return true;
	}
}	
	
class dw_guia_recepcion extends dw_guia_recepcion_base{
	
	const K_ESTADO_GR_EMITIDA 		= 1;
	const K_ESTADO_GR_IMPRESA	 	= 2;
	const K_ESTADO_GR_ANULADA	 	= 3;
	
	const K_TIPO_GR_DEVOLUCION		= 1;
	const K_TIPO_GR_GARANTIA		= 2;
	const K_TIPO_GR_OTRO			= 3;
	const K_TIPO_GR_ARRIENDO		= 4;
	
	function dw_guia_recepcion() {
		$sql = "SELECT	GR.COD_GUIA_RECEPCION 
						,convert(varchar(20), GR.FECHA_GUIA_RECEPCION, 103) FECHA_GUIA_RECEPCION
						,GR.COD_USUARIO
						,GR.COD_EMPRESA
						,GR.COD_ESTADO_GUIA_RECEPCION 
						,GR.COD_ESTADO_GUIA_RECEPCION	COD_ESTADO_GUIA_RECEPCION_H
						,EGR.NOM_ESTADO_GUIA_RECEPCION 
						,GR.COD_TIPO_GUIA_RECEPCION
						,GR.TIPO_DOC
						,GR.NRO_DOC 
						,GR.COD_DOC
						,GR.OBS
						,case GR.COD_ESTADO_GUIA_RECEPCION
							when ".self::K_ESTADO_GR_ANULADA." then 'ANULADA'
							else ''
						end TITULO_ESTADO_GUIA_RECEPCION
						,GR.COD_USUARIO_ANULA	
						,convert(varchar(20), GR.FECHA_ANULA, 103) +'  '+ convert(varchar(20), GR.FECHA_ANULA, 8) FECHA_ANULA
						,GR.MOTIVO_ANULA
						,GR.COD_PERSONA
						,GR.COD_SUCURSAL  AS COD_SUCURSAL_FACTURA
						,E.NOM_EMPRESA
						,E.ALIAS
						,E.RUT
						,E.DIG_VERIF
						,E.GIRO
						,dbo.f_get_direccion('SUCURSAL', GR.COD_SUCURSAL, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO:[TELEFONO] - FAX:[FAX]') DIRECCION_FACTURA
						,U.NOM_USUARIO						
						,'' VISIBLE_TAB
						,case GR.COD_ESTADO_GUIA_RECEPCION
							when ".self::K_ESTADO_GR_ANULADA." then '' 
							else 'none'
						end TR_DISPLAY 	
						,case GR.COD_TIPO_GUIA_RECEPCION
							when ".self::K_TIPO_GR_OTRO." then 'none' 
							else ''
						end TR_DISPLAY_TIPO_DOC 
						,case
							when GR.COD_DOC IS NULL then ''
							else 'none'
						end TD_DISPLAY_ELIMINAR
						,'none' TD_DISPLAY_POR_RECEP
						,'' DISPLAY_RECEP_TODO
						,COD_USUARIO_RESPONSABLE
						,OBS_POST_VENTA
						,TIPO_RECEPCION
						,COD_USUARIO_RESPONSABLE COD_USUARIO_RESPONSABLE_H
						,OBS_POST_VENTA OBS_POST_VENTA_H
						,TIPO_RECEPCION TIPO_RECEPCION_H
						,dbo.f_get_nro_nota_venta(GR.COD_DOC, GR.TIPO_DOC, GR.COD_GUIA_RECEPCION, 'N') COD_NOTA_VENTA
                        ,GR.NO_BODEGA
						,GR_RESUELTA
						,COD_DOC_GR_RESUELTA
						,COD_DOC_RESUELTA
						,GR_RESUELTA_OBS
            ,convert(varchar(20), GR.FECHA_RESUELTA, 103) FECHA_RESUELTA
            ,case GR.COD_ESTADO_GUIA_RECEPCION
							when 1 then 'none' 
							else ''
						end TAB_153010
				FROM	GUIA_RECEPCION GR, EMPRESA E,ESTADO_GUIA_RECEPCION EGR,
						TIPO_GUIA_RECEPCION TGR, USUARIO U
				WHERE	GR.COD_GUIA_RECEPCION = {KEY1} AND
						GR.COD_EMPRESA = E.COD_EMPRESA AND 
						GR.COD_ESTADO_GUIA_RECEPCION = EGR.COD_ESTADO_GUIA_RECEPCION AND
						GR.COD_TIPO_GUIA_RECEPCION = TGR.COD_TIPO_GUIA_RECEPCION AND
						GR.COD_USUARIO = U.COD_USUARIO";


		////////////////////
		// tab GUIA_RECEPCION
		parent::dw_help_empresa($sql);

		// DATOS GENERALES
		$this->add_control(new edit_nro_doc('COD_GUIA_RECEPCION','GUIA_RECEPCION'));	
		$this->add_control(new static_text('COD_NOTA_VENTA'));
		$this->add_control(new edit_text_upper('TIPO_DOC',20,30));
		$this->add_control($control = new drop_down_list('TIPO_DOC',array('','FACTURA','GUIA_DESPACHO','NOTA_VENTA'),array('','FACTURA','GUIA DESPACHO','NOTA VENTA'),150));
		$control->set_onChange("mostrarOcultar_datos()");
		$this->add_control($control = new edit_num('NRO_DOC',10,10));
		$control->set_onChange("existe_fa_gd();mostrarOcultar_nro_doc();");
		$control->con_separador_miles = false;
		$this->add_control(new edit_num('COD_DOC',10,10));
		$this->controls['COD_DOC']->type = 'hidden';
		
		$this->add_control(new edit_text('COD_ESTADO_GUIA_RECEPCION',10,10, 'hidden'));
		$this->add_control(new static_text('NOM_ESTADO_GUIA_RECEPCION'));
	
		$sql_tipo_gr	= "select 	 COD_TIPO_GUIA_RECEPCION
									,NOM_TIPO_GUIA_RECEPCION
							from 	 TIPO_GUIA_RECEPCION
							where 	COD_TIPO_GUIA_RECEPCION <> ".self::K_TIPO_GR_ARRIENDO."
							order by ORDEN";
		$this->add_control($control = new drop_down_dw('COD_TIPO_GUIA_RECEPCION',$sql_tipo_gr,100));
		$control->set_onChange("mostrarOcultar_tipo_doc(); mostrarOcultar_item(this);");
		
		$this->add_control(new edit_text_multiline('OBS',54,3));
		
		//USUARIO_ANULA
		$sql = "select 	COD_USUARIO
						,NOM_USUARIO
				from USUARIO";
								
		$this->add_control(new drop_down_dw('COD_USUARIO_ANULA',$sql,150));	
		$this->set_entrable('COD_USUARIO_ANULA', false);

		$this->add_control(new edit_text('FECHA_ANULA',10,10));
		$this->set_entrable('FECHA_ANULA', false);
		
		
		$sql = "SELECT 1 TIPO_RECEPCION
					  ,'Devoluci�n de Equipos' NOM_TIPO_RECEPCION
				UNION
				SELECT 2 TIPO_RECEPCION
					  ,'Cambio en garant�a' NOM_TIPO_RECEPCION
				UNION
				SELECT 3 TIPO_RECEPCION
					  ,'Solicita Presupuesto' NOM_TIPO_RECEPCION
				UNION
				SELECT 4 TIPO_RECEPCION
					  ,'Recepci�n equipo en Demostraci�n' NOM_TIPO_RECEPCION";
								
		$this->add_control(new drop_down_dw('TIPO_RECEPCION',$sql,150));
		
		$sql = "SELECT COD_USUARIO COD_USUARIO_RESPONSABLE
					 ,NOM_USUARIO 
				FROM USUARIO 
				WHERE VENDEDOR_VISIBLE_FILTRO = 1 
				ORDER BY NOM_USUARIO ASC";
							
		$this->add_control(new drop_down_dw('COD_USUARIO_RESPONSABLE',$sql,150));
		$this->add_control(new edit_text_multiline('OBS_POST_VENTA',54,3));
		
		$this->add_control(new edit_text_hidden('COD_USUARIO_RESPONSABLE_H'));
		$this->add_control(new edit_text_hidden('OBS_POST_VENTA_H'));
		$this->add_control(new edit_text_hidden('TIPO_RECEPCION_H'));
		$this->add_control(new edit_text_hidden('COD_ESTADO_GUIA_RECEPCION_H'));
		
		$this->add_control($control = new edit_check_box('NO_BODEGA','S', 'N'));
		$control->set_onChange("compruebaBodega(this);");

		$this->add_control(new edit_check_box('GR_RESUELTA','S', 'N'));
		$sql	=  "SELECT COD_DOC_GR_RESUELTA
						  ,NOM_DOC_GR_RESUELTA
					FROM DOC_GR_RESUELTA";
		$this->add_control($control = new drop_down_dw('COD_DOC_GR_RESUELTA',$sql,100));
		$control->set_onChange("valida_doc();");
		

		$this->add_control($control = new edit_num('COD_DOC_RESUELTA', 10, 10, 0, true, false, false));
		$control->set_onChange("valida_doc();");
		$this->add_control(new edit_text_multiline('GR_RESUELTA_OBS',54,3));

		// asigna los mandatorys
		$this->set_mandatory('COD_TIPO_GUIA_RECEPCION', 'Tipo Guia Recepci�n');
		$this->set_mandatory('OBS', 'Observaciones');
		
	}
	function fill_record(&$temp, $record){
		parent::fill_record($temp, $record);
		
		$COD_DOC = $this->get_item(0, 'COD_DOC');
		$COD_ESTADO_GUIA_RECEPCION = $this->get_item(0, 'COD_ESTADO_GUIA_RECEPCION');
		
		if (($COD_DOC != '') or ($COD_ESTADO_GUIA_RECEPCION >= 1))  //la GD viene desde NV, o estado <> emitida
			$temp->setVar('DISABLE_BUTTON', 'style="display:none"');
		else{	
				if ($this->entrable)
					$temp->setVar('DISABLE_BUTTON', '');
				else
					$temp->setVar('DISABLE_BUTTON', 'disabled="disabled"');
		}				
	}
	
}


class wi_guia_recepcion extends wi_guia_recepcion_base {
	const K_ESTADO_GR_EMITIDA	 	= 1;
	const K_ESTADO_GR_IMPRESA	 	= 2;
	const K_ESTADO_GR_ANULADA	 	= 3;
	
	const K_TIPO_GR_DEVOLUCION		= 1;
	const K_TIPO_GR_GARANTIA		= 2;
	const K_TIPO_GR_OTRO			= 3;
	
	const K_PARAM_NOM_EMPRESA        =6;
	const K_PARAM_RUT_EMPRESA        =20;
	const K_PARAM_DIR_EMPRESA        =10;
	const K_PARAM_TEL_EMPRESA        =11;
	const K_PARAM_FAX_EMPRESA        =12;
	const K_PARAM_MAIL_EMPRESA       =13;
	const K_PARAM_CIUDAD_EMPRESA     =14;
	const K_PARAM_PAIS_EMPRESA       =15;
	const K_PARAM_SMTP 				 =17;
	const K_PARAM_SITIO_WEB_EMPRESA  =25;
		
	function wi_guia_recepcion($cod_item_menu){		
		parent::w_input('guia_recepcion', $cod_item_menu);

		// tab guia_recepcion
		// DATAWINDOWS GUIA_RECEPCION
		$this->dws['dw_guia_recepcion'] = new dw_guia_recepcion();
		$this->dws['dw_guia_recepcion']->set_entrable('GR_RESUELTA', false);
		$this->dws['dw_guia_recepcion']->set_entrable('COD_DOC_GR_RESUELTA', false);
		$this->dws['dw_guia_recepcion']->set_entrable('COD_DOC_RESUELTA', false);
		$this->dws['dw_guia_recepcion']->set_entrable('GR_RESUELTA_OBS', false);
		
		if(($this->cod_usuario ==1) or ($this->cod_usuario == 4) or ($this->cod_usuario == 71) or ($this->cod_usuario == 46)){
			$this->dws['dw_guia_recepcion']->set_entrable('GR_RESUELTA', true);
			$this->dws['dw_guia_recepcion']->set_entrable('COD_DOC_GR_RESUELTA', true);
			$this->dws['dw_guia_recepcion']->set_entrable('COD_DOC_RESUELTA', true);
			$this->dws['dw_guia_recepcion']->set_entrable('GR_RESUELTA_OBS', true);
		}
		
		// tab items
		// DATAWINDOWS ITEMS GUIA_RECEPCION
		$this->dws['dw_item_guia_recepcion'] = new dw_item_guia_recepcion();
   
		// DATAWINDOWS BITACORA_GUIA_RECEPCION
		$this->dws['dw_bitacora_guia_recepcion'] = new dw_bitacora_guia_recepcion();
		$this->dws['dw_gr_foto'] = new dw_gr_foto();
		
		//auditoria Solicitado por IS.
		$this->add_auditoria('COD_TIPO_GUIA_RECEPCION');
		$this->add_auditoria('COD_ESTADO_GUIA_RECEPCION');
		$this->add_auditoria('COD_EMPRESA');
		$this->add_auditoria('COD_PERSONA');
	}

	function new_record(){
		$this->dws['dw_guia_recepcion']->insert_row();
		$this->dws['dw_guia_recepcion']->set_item(0, 'TR_DISPLAY', 'none');
		$this->dws['dw_guia_recepcion']->set_item(0, 'TR_DISPLAY_TIPO_DOC', 'none');
		$this->dws['dw_guia_recepcion']->set_item(0, 'FECHA_GUIA_RECEPCION', $this->current_date());
		$this->dws['dw_guia_recepcion']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_guia_recepcion']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->dws['dw_guia_recepcion']->set_item(0, 'COD_ESTADO_GUIA_RECEPCION', self::K_ESTADO_GR_EMITIDA);
		$this->dws['dw_guia_recepcion']->set_item(0, 'NOM_ESTADO_GUIA_RECEPCION', 'EMITIDA');
		$this->dws['dw_guia_recepcion']->set_item(0, 'VISIBLE_TAB', 'none');
    	$this->dws['dw_guia_recepcion']->set_item(0, 'TAB_153010', 'none');
		$this->dws['dw_guia_recepcion']->controls['RUT']->readonly 	 			 = true;
		$this->dws['dw_guia_recepcion']->controls['ALIAS']->readonly 			 = true;
		$this->dws['dw_guia_recepcion']->controls['COD_EMPRESA']->readonly 		 = true;
		$this->dws['dw_guia_recepcion']->controls['NOM_EMPRESA']->readonly 		 = true; 
		$this->dws['dw_guia_recepcion']->set_item(0, 'COD_TIPO_GUIA_RECEPCION', 3);//otro
		$this->dws['dw_guia_recepcion']->set_item(0, 'NO_BODEGA', 'S');
		
		$k_autoriza_no_bodega = 999605;
		$priv = $this->get_privilegio_opcion_usuario($k_autoriza_no_bodega, $this->cod_usuario); 
		
		if($priv =! 'E'){
			$this->dws['dw_orden_compra']->set_entrable('NO_BODEGA', false);
		}
	}
	
	function load_record(){
		$cod_guia_recepcion = $this->get_item_wo($this->current_record, 'COD_GUIA_RECEPCION');
		$this->dws['dw_guia_recepcion']->retrieve($cod_guia_recepcion);
		$this->dws['dw_item_guia_recepcion']->retrieve($cod_guia_recepcion);
    	$this->dws['dw_bitacora_guia_recepcion']->retrieve($cod_guia_recepcion);
		$this->dws['dw_gr_foto']->retrieve($cod_guia_recepcion);
		$cod_empresa = $this->dws['dw_guia_recepcion']->get_item(0, 'COD_EMPRESA');
		$this->dws['dw_guia_recepcion']->controls['COD_SUCURSAL_FACTURA']->retrieve($cod_empresa);
		$this->dws['dw_guia_recepcion']->controls['COD_PERSONA']->retrieve($cod_empresa);
		
		$this->dws['dw_bitacora_guia_recepcion']->set_entrable_dw(true);
		$this->dws['dw_gr_foto']->set_entrable_dw(true);
		$COD_ESTADO_GUIA_RECEPCION = $this->dws['dw_guia_recepcion']->get_item(0, 'COD_ESTADO_GUIA_RECEPCION');
		
		$this->b_no_save_visible = true;
		$this->b_save_visible 	 = true;
		$this->b_modify_visible	 = true;
		
		$this->dws['dw_guia_recepcion']->set_entrable('COD_TIPO_GUIA_RECEPCION' , true);
		$this->dws['dw_guia_recepcion']->set_entrable('TIPO_DOC'				, true);
		$this->dws['dw_guia_recepcion']->set_entrable('NRO_DOC'				 	, true);
		$this->dws['dw_guia_recepcion']->set_entrable('OBS'					 	, true);
		
		$this->dws['dw_guia_recepcion']->set_entrable('NOM_EMPRESA'				, false);
		$this->dws['dw_guia_recepcion']->set_entrable('ALIAS'					, false);
		$this->dws['dw_guia_recepcion']->set_entrable('COD_EMPRESA'				, false);
		$this->dws['dw_guia_recepcion']->set_entrable('RUT'						, false);
		$this->dws['dw_guia_recepcion']->set_entrable('COD_SUCURSAL_FACTURA'	, true);
		$this->dws['dw_guia_recepcion']->set_entrable('COD_PERSONA'				, true);
		$this->dws['dw_item_guia_recepcion']->set_entrable('COD_PRODUCTO'   	, false);
		$this->dws['dw_item_guia_recepcion']->set_entrable('NOM_PRODUCTO'  		, false);
		$this->dws['dw_item_guia_recepcion']->set_entrable('CANTIDAD'  			, true);
		
		// aqui se dejan modificables los datos del tab items
		$this->dws['dw_item_guia_recepcion']->set_entrable_dw(true);
		$this->dws['dw_guia_recepcion']->set_item(0, 'DISPLAY_RECEP_TODO', 'none');
		$this->dws['dw_guia_recepcion']->set_item(0, 'TD_DISPLAY_POR_RECEP', 'none');
		
		if ($COD_ESTADO_GUIA_RECEPCION == self::K_ESTADO_GR_EMITIDA){
			if($this->modify == true)
				$this->b_print_visible	 = false;
			else	
				$this->b_print_visible	 = true;
			
			unset($this->dws['dw_guia_recepcion']->controls['COD_ESTADO_GUIA_RECEPCION']);
			$this->dws['dw_guia_recepcion']->add_control(new edit_text('COD_ESTADO_GUIA_RECEPCION',10,10, 'hidden'));
			$this->dws['dw_guia_recepcion']->controls['NOM_ESTADO_GUIA_RECEPCION']->type = '';

			$this->dws['dw_guia_recepcion']->set_entrable('NOM_EMPRESA'				, true);
			$this->dws['dw_guia_recepcion']->set_entrable('ALIAS'					, true);
			$this->dws['dw_guia_recepcion']->set_entrable('COD_EMPRESA'				, true);
			$this->dws['dw_guia_recepcion']->set_entrable('RUT'						, true);
			$this->dws['dw_item_guia_recepcion']->set_entrable('COD_PRODUCTO'   	, true);
			$this->dws['dw_item_guia_recepcion']->set_entrable('NOM_PRODUCTO'  		, true);
			$this->dws['dw_item_guia_recepcion']->set_entrable('CANTIDAD'  			, true);					

			$COD_TIPO_GUIA_RECEPCION = $this->dws['dw_guia_recepcion']->get_item(0, 'COD_TIPO_GUIA_RECEPCION');
			if ($COD_TIPO_GUIA_RECEPCION  == self::K_TIPO_GR_OTRO) {	
				$this->dws['dw_guia_recepcion']->set_entrable('TIPO_DOC'				, true);	
			}
			else if($COD_TIPO_GUIA_RECEPCION  == self::K_TIPO_GR_DEVOLUCION ||$COD_TIPO_GUIA_RECEPCION  == self::K_TIPO_GR_GARANTIA){
				$this->dws['dw_guia_recepcion']->set_entrable('COD_TIPO_GUIA_RECEPCION'	, false);					
			}

		}else if ($COD_ESTADO_GUIA_RECEPCION == self::K_ESTADO_GR_IMPRESA){
			$sql = "select 	COD_ESTADO_GUIA_RECEPCION
							,NOM_ESTADO_GUIA_RECEPCION
					from 	ESTADO_GUIA_RECEPCION
					where 	COD_ESTADO_GUIA_RECEPCION = ".self::K_ESTADO_GR_IMPRESA." or
							COD_ESTADO_GUIA_RECEPCION = ".self::K_ESTADO_GR_ANULADA."
					order by COD_ESTADO_GUIA_RECEPCION";
					
			unset($this->dws['dw_guia_recepcion']->controls['COD_ESTADO_GUIA_RECEPCION']);
			$this->dws['dw_guia_recepcion']->add_control($control = new drop_down_dw('COD_ESTADO_GUIA_RECEPCION',$sql,150));	
			$control->set_onChange("mostrarOcultar_Anula(this);");
			$this->dws['dw_guia_recepcion']->controls['NOM_ESTADO_GUIA_RECEPCION']->type = 'hidden';
			$this->dws['dw_guia_recepcion']->add_control(new edit_text_upper('MOTIVO_ANULA',110, 100));

			$this->dws['dw_guia_recepcion']->set_entrable('COD_TIPO_GUIA_RECEPCION' , false);
			$this->dws['dw_guia_recepcion']->set_entrable('NRO_DOC'					, false);
			$this->dws['dw_guia_recepcion']->set_entrable('COD_DOC'					, false);
			$this->dws['dw_guia_recepcion']->set_entrable('TIPO_DOC'				, false);
			$this->dws['dw_guia_recepcion']->set_entrable('OBS'					 	, false);
			
			$this->dws['dw_guia_recepcion']->set_entrable('NOM_EMPRESA'				, false);
			$this->dws['dw_guia_recepcion']->set_entrable('ALIAS'					, false);
			$this->dws['dw_guia_recepcion']->set_entrable('COD_EMPRESA'				, false);
			$this->dws['dw_guia_recepcion']->set_entrable('RUT'						, false);
			$this->dws['dw_guia_recepcion']->set_entrable('COD_SUCURSAL_FACTURA'	, false);
			$this->dws['dw_guia_recepcion']->set_entrable('COD_PERSONA'				, false);
			
			$this->dws['dw_guia_recepcion']->set_entrable('COD_USUARIO_RESPONSABLE'	, false);
			$this->dws['dw_guia_recepcion']->set_entrable('OBS_POST_VENTA'			, false);
			$this->dws['dw_guia_recepcion']->set_entrable('TIPO_RECEPCION'			, false);
			
			// aqui se dejan no modificables los datos del tab items
			$this->dws['dw_item_guia_recepcion']->set_entrable_dw(false);
				
		}else if ($COD_ESTADO_GUIA_RECEPCION == self::K_ESTADO_GR_ANULADA) {
			$this->b_print_visible 	 = false;
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
		}
		
		$k_autoriza_no_bodega = 999605; 
		$priv = $this->get_privilegio_opcion_usuario($k_autoriza_no_bodega, $this->cod_usuario); 
		
		if($priv =! 'E'){
			$this->dws['dw_orden_compra']->set_entrable('NO_BODEGA', false);
		}
		
		if($this->dws['dw_guia_recepcion']->get_item(0, 'GR_RESUELTA') == 'S'){
			$this->dws['dw_guia_recepcion']->set_entrable('GR_RESUELTA', false);
			$this->dws['dw_guia_recepcion']->set_entrable('COD_DOC_GR_RESUELTA', false);
			$this->dws['dw_guia_recepcion']->set_entrable('COD_DOC_RESUELTA', false);
			$this->dws['dw_guia_recepcion']->set_entrable('GR_RESUELTA_OBS', false);
			$this->dws['dw_bitacora_guia_recepcion']->set_entrable_dw(false);
			$this->dws['dw_gr_foto']->set_entrable_dw(false);
		}else{
			if(($this->cod_usuario ==1) or ($this->cod_usuario == 4) or ($this->cod_usuario == 71) or ($this->cod_usuario == 46)){
				$this->dws['dw_guia_recepcion']->set_entrable('GR_RESUELTA', true);
				$this->dws['dw_guia_recepcion']->set_entrable('COD_DOC_GR_RESUELTA', true);
				$this->dws['dw_guia_recepcion']->set_entrable('COD_DOC_RESUELTA', true);
				$this->dws['dw_guia_recepcion']->set_entrable('GR_RESUELTA_OBS', true);
			}
		}
	}

	function get_key() {
		return $this->dws['dw_guia_recepcion']->get_item(0, 'COD_GUIA_RECEPCION');
	}	
		
	function save_record($db){
		$COD_GUIA_RECEPCION			= $this->get_key();
		$COD_USUARIO				= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_USUARIO');
		$COD_EMPRESA 				= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_EMPRESA');
		$COD_SUCURSAL	 			= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_SUCURSAL_FACTURA');
		$COD_PERSONA				= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_PERSONA');	
		$COD_ESTADO_GUIA_RECEPCION 	= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_ESTADO_GUIA_RECEPCION');
		$COD_TIPO_GUIA_RECEPCION	= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_TIPO_GUIA_RECEPCION');	
		$TIPO_DOC					= $this->dws['dw_guia_recepcion']->get_item(0, 'TIPO_DOC');	
		$NRO_DOC					= $this->dws['dw_guia_recepcion']->get_item(0, 'NRO_DOC');	
		$COD_DOC					= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_DOC');	
		$OBS						= $this->dws['dw_guia_recepcion']->get_item(0, 'OBS');
		$OBS 						= str_replace("'", "''", $OBS);	
		$MOTIVO_ANULA				= $this->dws['dw_guia_recepcion']->get_item(0, 'MOTIVO_ANULA');
		$MOTIVO_ANULA 				= str_replace("'", "''", $MOTIVO_ANULA);	
		$COD_USUARIO_ANULA			= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_USUARIO_ANULA');
		$TIPO_RECEPCION				= $this->dws['dw_guia_recepcion']->get_item(0, 'TIPO_RECEPCION');
		$COD_USUARIO_RESPONSABLE	= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_USUARIO_RESPONSABLE');
		$OBS_POST_VENTA				= $this->dws['dw_guia_recepcion']->get_item(0, 'OBS_POST_VENTA');
		$NO_BODEGA                  = $this->dws['dw_guia_recepcion']->get_item(0, 'NO_BODEGA');
		$GR_RESUELTA				= $this->dws['dw_guia_recepcion']->get_item(0, 'GR_RESUELTA');
     
		if($GR_RESUELTA == 'S'){
			$COD_USUARIO		= $this->cod_usuario;
		}

		$COD_DOC_GR_RESUELTA		= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_DOC_GR_RESUELTA');
		$COD_DOC_RESUELTA			= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_DOC_RESUELTA');
		$GR_RESUELTA_OBS            = $this->dws['dw_guia_recepcion']->get_item(0, 'GR_RESUELTA_OBS');
		
		if (($MOTIVO_ANULA != '') && ($COD_USUARIO_ANULA== '')) // se anula 
			$COD_USUARIO_ANULA		= $this->cod_usuario;
		else
			$COD_USUARIO_ANULA		= "null";
		
		$TIPO_DOC					= ($TIPO_DOC =='') ? "null" : "'$TIPO_DOC'";
		$NRO_DOC					= ($NRO_DOC =='') ? "null" : $NRO_DOC;
		$COD_DOC					= ($COD_DOC =='') ? "null" : $COD_DOC;
		$MOTIVO_ANULA				= ($MOTIVO_ANULA =='') ? "NULL" : "$MOTIVO_ANULA";
		$OBS						= ($OBS =='') ? "null" : "'$OBS'";
		$COD_GUIA_RECEPCION			= ($COD_GUIA_RECEPCION =='') ? "null" : $COD_GUIA_RECEPCION;
		$TIPO_RECEPCION 			= ($TIPO_RECEPCION =='') ? "null" : $TIPO_RECEPCION;
		$COD_USUARIO_RESPONSABLE	= ($COD_USUARIO_RESPONSABLE =='') ? "null" : $COD_USUARIO_RESPONSABLE;
		$OBS_POST_VENTA				= ($OBS_POST_VENTA =='') ? "null" : "'$OBS_POST_VENTA'";	
		$NO_BODEGA		            = ($NO_BODEGA =='') ? "NULL" : "'$NO_BODEGA'";
		$GR_RESUELTA		        = ($GR_RESUELTA =='') ? "NULL" : "'$GR_RESUELTA'";
		$COD_DOC_GR_RESUELTA		= ($COD_DOC_GR_RESUELTA =='') ? "NULL" : "$COD_DOC_GR_RESUELTA";
		$COD_DOC_RESUELTA		    = ($COD_DOC_RESUELTA =='') ? "NULL" : "$COD_DOC_RESUELTA";
		$GR_RESUELTA_OBS		    = ($NO_BODEGA =='') ? "NULL" : "'$GR_RESUELTA_OBS'";
    
		$sp = 'spu_guia_recepcion';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
		$param	= "	'$operacion'					
					,$COD_GUIA_RECEPCION			
					,$COD_USUARIO				
					,$COD_EMPRESA				
					,$COD_SUCURSAL				
					,$COD_PERSONA				
					,$COD_ESTADO_GUIA_RECEPCION	
					,$COD_TIPO_GUIA_RECEPCION	
					,$TIPO_DOC					
					,$NRO_DOC					
					,$COD_DOC					
					,$OBS						
					,$COD_USUARIO_ANULA		
					,'$MOTIVO_ANULA'
					,$TIPO_RECEPCION
					,$COD_USUARIO_RESPONSABLE
					,$OBS_POST_VENTA
          			,$NO_BODEGA
					,$GR_RESUELTA
					,$COD_DOC_GR_RESUELTA
					,$COD_DOC_RESUELTA
					,$GR_RESUELTA_OBS";
  
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()){
				$COD_GUIA_RECEPCION = $db->GET_IDENTITY();
				$this->dws['dw_guia_recepcion']->set_item(0, 'COD_GUIA_RECEPCION', $COD_GUIA_RECEPCION);
			}

			if (($MOTIVO_ANULA != 'null') && ($COD_USUARIO_ANULA != 'null')) // se anula 
				$this->f_envia_mail('ANULADA');

			for ($i=0; $i<$this->dws['dw_item_guia_recepcion']->row_count(); $i++){ 
				$this->dws['dw_item_guia_recepcion']->set_item($i, 'COD_GUIA_RECEPCION', $COD_GUIA_RECEPCION);
				$this->dws['dw_item_guia_recepcion']->set_item($i, 'TIPO_DOC_GR', $TIPO_DOC);
			}
			for ($i=0; $i<$this->dws['dw_gr_foto']->row_count(); $i++)
				$this->dws['dw_gr_foto']->set_item($i, 'D_COD_GUIA_RECEPCION', $COD_GUIA_RECEPCION);
			
			if (!$this->dws['dw_item_guia_recepcion']->update($db, $COD_GUIA_RECEPCION)) return false;
			
			if (!$this->dws['dw_bitacora_guia_recepcion']->update($db, $COD_GUIA_RECEPCION)) return false;

			if (!$this->dws['dw_gr_foto']->update($db)) return false;
			
			/*if ($GR_RESUELTA != 'N')// se da por resuelto
				$this->envia_mail_resuelta($db, $COD_GUIA_RECEPCION);*/
      
      		return true;
		}
		return false;
	}
   
 	function envia_mail_resuelta ($db, $COD_GUIA_RECEPCION){
		$temp = new Template_appl('mail_resuelta.htm');
		
		$sql = "SELECT COD_GUIA_RECEPCION
					  ,(SELECT NOM_USUARIO 
						FROM USUARIO US
						WHERE US.COD_USUARIO = GR.COD_USUARIO) NOM_USUARIO
					  ,(SELECT MAIL 
						FROM USUARIO US
						WHERE US.COD_USUARIO = GR.COD_USUARIO) MAIL_EMISOR	
					  ,NOM_USUARIO	NOM_USUARIO_RESPONSABLE
					  ,MAIL
					  ,CONVERT(VARCHAR, FECHA_GUIA_RECEPCION, 103) FECHA_GUIA_RECEPCION
					  ,(dbo.number_format(CONVERT(VARCHAR, RUT), 0, ',', '.')  +'-'+ DIG_VERIF) RUT
					  ,NOM_EMPRESA
					  ,COD_DOC
					  ,CASE
						WHEN GR.TIPO_DOC = 'FACTURA' THEN (SELECT CONVERT(VARCHAR,COD_DOC)
														   FROM FACTURA
														   WHERE COD_FACTURA = GR.COD_DOC)
						WHEN GR.TIPO_DOC = 'GUIA_DESPACHO' THEN (SELECT CONVERT(VARCHAR,COD_DOC)
																 FROM GUIA_DESPACHO
																 WHERE COD_GUIA_DESPACHO = GR.COD_DOC)
						ELSE 'No indicada'
					  END COD_NOTA_VENTA											 
					  ,CASE TIPO_RECEPCION	
						WHEN 1 THEN 'Devoluci�n de Equipos'
						WHEN 2 THEN 'Cambio en garant�a'
						WHEN 3 THEN 'Solicita Presupuesto'
						WHEN 4 THEN 'Recepci�n equipo en Demostraci�n'
					   END TIPO_INGRESO
					  ,OBS_POST_VENTA
            ,(select TOP 1 ORDEN    FROM BITACORA_GUIA_RECEPCION WHERE COD_GUIA_RECEPCION = $COD_GUIA_RECEPCION  ORDER BY COD_BITACORA_GUIA_RECEPCION DESC) ORDEN_BITACORA
            ,GR_RESUELTA
						,GR_RESUELTA_OBS
            ,(SELECT NOM_USUARIO FROM USUARIO US WHERE US.COD_USUARIO = GR.COD_USUARIO_RESUELTA) NOM_USUARIO_RESUELTA
            ,(SELECT NOM_DOC_GR_RESUELTA FROM DOC_GR_RESUELTA WHERE COD_DOC_GR_RESUELTA = GR.COD_DOC_GR_RESUELTA) NOM_DOC_GR_RESUELTA
				FROM GUIA_RECEPCION GR
					,EMPRESA E
					,USUARIO U
				WHERE COD_GUIA_RECEPCION = $COD_GUIA_RECEPCION
				AND E.COD_EMPRESA = GR.COD_EMPRESA
				AND U.COD_USUARIO = COD_USUARIO_RESPONSABLE";
		
		$result = $db->build_results($sql);
		$dw = new datawindow($sql);
		$dw->retrieve();
		$dw->habilitar($temp, false);
		
		////////////////////////////////
		
		$sql = "SELECT COD_PRODUCTO
					  ,NOM_PRODUCTO
					  ,CANTIDAD
				FROM ITEM_GUIA_RECEPCION
				WHERE COD_GUIA_RECEPCION = $COD_GUIA_RECEPCION";
		
		$dw_item = new datawindow($sql, 'ITEM_GUIA_RECEPCION');
		$dw_item->retrieve();
		$dw_item->habilitar($temp, false);
   		$sql_bitacora = "select COD_BITACORA_GUIA_RECEPCION 
                	,ORDEN
                  ,convert(varchar(20), FECHA_BITACORA_GUIA_RECEPCION, 103)+'  '+ convert(varchar(20), FECHA_BITACORA_GUIA_RECEPCION, 8) FECHA_BITACORA_GUIA_RECEPCION
                  ,(select NOM_USUARIO from usuario where  B.COD_USUARIO = cod_usuario ) NOM_USUARIO
                	,GLOSA 
                from BITACORA_GUIA_RECEPCION B 
                where COD_GUIA_RECEPCION = $COD_GUIA_RECEPCION
                ORDER BY ORDEN desc";
		
		$dw_bitacora = new datawindow($sql_bitacora, 'ITEM_BITACORA_RECEPCION');
		$dw_bitacora->retrieve();
		$dw_bitacora->habilitar($temp, false);
   

		
		$subject = "Aviso Guia Recepcion N� ".$result[0]['COD_GUIA_RECEPCION']." asignada a: ".$result[0]['NOM_USUARIO_RESPONSABLE']." / [GR RESUELTA]";
		$html = $temp->toString();
		
		$mailto = $result[0]['MAIL'];
		$mailtoname = $result[0]['NOM_USUARIO_RESPONSABLE'];
    //$mailcc = array('hirvingomezgamboa@gmail.com');
    //$mailccname = array('Hirvin Gomez');
		$mailcc = array('ascianca@biggi.cl', 'sergio.pechoante@biggi.cl','fpuebla@biggi.cl', 'jcatalan@biggi.cl', 'psilva@biggi.cl', 'mscianca@todoinox.cl', 'lsun@todoinox.cl', 'rsanchez@todoinox.cl', 'lwu@todoinox.cl');
		$mailccname = array('ANGEL SCIANCA','SERGIO PECHOANTE','FELIPE PUEBLA','JOSE CATALAN', 'PIERO SILVA', 'Margarita Scianca', 'Lifen Sun', 'Ricardo Sanchez', 'Loreto Wu');
			
		if($result[0]['NOM_USUARIO'] <> $mailtoname){
			$mailcc[]		= $result[0]['MAIL_EMISOR'];
			$mailccname[]	= $result[0]['NOM_USUARIO'];
		}
		
		for($i=0 ; $i < count($mailcc) ; $i++){
			if($mailto <> $mailcc[$i]){
				$str_mailcc		.= $mailcc[$i].";";
				$str_mailccname .= $mailccname[$i].";";
			}
		}
		
		$str_mailcc		= trim($str_mailcc,';');
		$str_mailccname	= trim($str_mailccname,';');
		
		$mailbcc = array('mherrera@biggi.cl');
		$mailbccname = array('MARCELO HERRERA');
			
		for($i=0 ; $i < count($mailbcc) ; $i++){
			if($mailto <> $mailbcc[$i]){
				$str_mailbcc		.= $mailbcc[$i].";";
				$str_mailbccname	.= $mailbccname[$i].";";
			}
		}
		
		$str_mailbcc		= trim($str_mailbcc,';');
		$str_mailbccname	= trim($str_mailbccname,';');

		$sp = "spu_envio_mail";
	
		$param = "'INSERT'							--@ve_operacion
				,null								--@ve_cod_envio_mail
				,1									--@ve_cod_estado_envio_mail
				,null								--@ve_fecha_envio
			 	,'soporte@biggi.cl'					--@ve_mail_from
			 	,'Comercial Biggi S.A.'				--@ve_mail_from_name
			 	,'$str_mailcc'						--@ve_mail_cc
			 	,'$str_mailccname'					--@ve_mail_cc_name
			 	,'$str_mailbcc'						--@ve_mail_bcc
			 	,'$str_mailbccname'					--@ve_mail_bcc_name
			 	,'$mailto'							--@ve_mail_to
			 	,'$mailtoname'						--@ve_mail_to_name
			 	,'$subject'							--@ve_mail_subject
			 	,'".str_replace("'","''",$html)."'	--@ve_mail_body
			 	,null								--@ve_mail_altbody
			 	,'GUIA_RECEPCION'					--@ve_tipo_doc
			 	,$COD_GUIA_RECEPCION";				//@ve_cod_doc
		
		$db->EXECUTE_SP($sp, $param);
	}
	
	function envia_mail($db, $COD_GUIA_RECEPCION){
		$temp = new Template_appl('plantilla_envio_mail.htm');
		
		$sql = "SELECT COD_GUIA_RECEPCION
					  ,(SELECT NOM_USUARIO 
						FROM USUARIO US
						WHERE US.COD_USUARIO = GR.COD_USUARIO) NOM_USUARIO
					  ,(SELECT MAIL 
						FROM USUARIO US
						WHERE US.COD_USUARIO = GR.COD_USUARIO) MAIL_EMISOR	
					  ,NOM_USUARIO	NOM_USUARIO_RESPONSABLE
					  ,MAIL
					  ,CONVERT(VARCHAR, FECHA_GUIA_RECEPCION, 103) FECHA_GUIA_RECEPCION
					  ,(dbo.number_format(CONVERT(VARCHAR, RUT), 0, ',', '.')  +'-'+ DIG_VERIF) RUT
					  ,NOM_EMPRESA
					  ,COD_DOC
					  ,CASE
						WHEN GR.TIPO_DOC = 'FACTURA' THEN (SELECT CONVERT(VARCHAR,COD_DOC)
														   FROM FACTURA
														   WHERE COD_FACTURA = GR.COD_DOC)
						WHEN GR.TIPO_DOC = 'GUIA_DESPACHO' THEN (SELECT CONVERT(VARCHAR,COD_DOC)
																 FROM GUIA_DESPACHO
																 WHERE COD_GUIA_DESPACHO = GR.COD_DOC)
						ELSE 'No indicada'
					  END COD_NOTA_VENTA											 
					  ,CASE TIPO_RECEPCION	
						WHEN 1 THEN 'Devoluci�n de Equipos'
						WHEN 2 THEN 'Cambio en garant�a'
						WHEN 3 THEN 'Solicita Presupuesto'
						WHEN 4 THEN 'Recepci�n equipo en Demostraci�n'
					   END TIPO_INGRESO
					  ,OBS_POST_VENTA
				FROM GUIA_RECEPCION GR
					,EMPRESA E
					,USUARIO U
				WHERE COD_GUIA_RECEPCION = $COD_GUIA_RECEPCION
				AND E.COD_EMPRESA = GR.COD_EMPRESA
				AND U.COD_USUARIO = COD_USUARIO_RESPONSABLE";
		
		$result = $db->build_results($sql);
		$dw = new datawindow($sql);
		$dw->retrieve();
		$dw->habilitar($temp, false);
		
		////////////////////////////////
		
		$sql = "SELECT COD_PRODUCTO
					  ,NOM_PRODUCTO
					  ,CANTIDAD
				FROM ITEM_GUIA_RECEPCION
				WHERE COD_GUIA_RECEPCION = $COD_GUIA_RECEPCION";
		
		$dw_item = new datawindow($sql, 'ITEM_GUIA_RECEPCION');
		$dw_item->retrieve();
		$dw_item->habilitar($temp, false);
		
		$subject = "Aviso Guia Recepcion N� ".$result[0]['COD_GUIA_RECEPCION']." asignada a: ".$result[0]['NOM_USUARIO_RESPONSABLE'];
		$html = $temp->toString();
		
		$mailto = $result[0]['MAIL'];
		$mailtoname = $result[0]['NOM_USUARIO_RESPONSABLE'];

		$mailcc = array('ascianca@biggi.cl', 'sergio.pechoante@biggi.cl','fpuebla@biggi.cl', 'jcatalan@biggi.cl', 'psilva@biggi.cl', 'mscianca@todoinox.cl', 'lsun@todoinox.cl', 'rsanchez@todoinox.cl', 'lwu@todoinox.cl');
		$mailccname = array('ANGEL SCIANCA','SERGIO PECHOANTE','FELIPE PUEBLA','JOSE CATALAN', 'PIERO SILVA', 'Margarita Scianca', 'Lifen Sun', 'Ricardo Sanchez', 'Loreto Wu');
			
		if($result[0]['NOM_USUARIO'] <> $mailtoname){
			$mailcc[]		= $result[0]['MAIL_EMISOR'];
			$mailccname[]	= $result[0]['NOM_USUARIO'];
		}
		
		for($i=0 ; $i < count($mailcc) ; $i++){
			if($mailto <> $mailcc[$i]){
				$str_mailcc		.= $mailcc[$i].";";
				$str_mailccname .= $mailccname[$i].";";
			}
		}
		
		$str_mailcc		= trim($str_mailcc,';');
		$str_mailccname	= trim($str_mailccname,';');
		
		$mailbcc = array('mherrera@biggi.cl');
		$mailbccname = array('MARCELO HERRERA');
			
		for($i=0 ; $i < count($mailbcc) ; $i++){
			if($mailto <> $mailbcc[$i]){
				$str_mailbcc		.= $mailbcc[$i].";";
				$str_mailbccname	.= $mailbccname[$i].";";
			}
		}
		
		$str_mailbcc		= trim($str_mailbcc,';');
		$str_mailbccname	= trim($str_mailbccname,';');

		$sp = "spu_envio_mail";
	
		$param = "'INSERT'							--@ve_operacion
				,null								--@ve_cod_envio_mail
				,1									--@ve_cod_estado_envio_mail
				,null								--@ve_fecha_envio
			 	,'soporte@biggi.cl'					--@ve_mail_from
			 	,'Comercial Biggi S.A.'				--@ve_mail_from_name
			 	,'$str_mailcc'						--@ve_mail_cc
			 	,'$str_mailccname'					--@ve_mail_cc_name
			 	,'$str_mailbcc'						--@ve_mail_bcc
			 	,'$str_mailbccname'					--@ve_mail_bcc_name
			 	,'$mailto'							--@ve_mail_to
			 	,'$mailtoname'						--@ve_mail_to_name
			 	,'$subject'							--@ve_mail_subject
			 	,'".str_replace("'","''",$html)."'	--@ve_mail_body
			 	,null								--@ve_mail_altbody
			 	,'GUIA_RECEPCION'					--@ve_tipo_doc
			 	,$COD_GUIA_RECEPCION";				//@ve_cod_doc
		
		$db->EXECUTE_SP($sp, $param);
	}
	
	// esta funcio envia mail  cuando se imprime e documento de guia despacho 
 	function f_envia_mail($estado_guia_recepcion){
 		$cod_guia_recepcion = $this->get_key();
 		$remitente = $this->nom_usuario;
        $cod_remitente = $this->cod_usuario;

        $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        $sql = "select COD_GUIA_RECEPCION from GUIA_RECEPCION WHERE COD_GUIA_RECEPCION = $cod_guia_recepcion";
        $result = $db->build_results($sql);
        $nro_guia_recepcion = $result[0]['COD_GUIA_RECEPCION'];		
		
        $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        // obtiene el mail de quien creo la tarea y manda el mail
        $sql_remitente = "SELECT MAIL from USUARIO where COD_USUARIO = $cod_remitente";
        $result_remitente = $db->build_results($sql_remitente);
        $mail_remitente = $result_remitente[0]['MAIL'];
		
 		// Mail destinatarios
        
        /*
        $para_admin1 = 'mherrera@integrasystem.cl';
        $para_admin2 = 'imeza@integrasystem.cl';
		*/
        
        if($estado_guia_recepcion == 'IMPRESO')
		{
	        $asunto = 'Impresion de Guia de Recepcion N� '.$nro_guia_recepcion;
	        $mensaje = 'Se ha <b>IMPRESO</b> la <b>Guia de Recepcion N� '.$nro_guia_recepcion.'</b> por el usuario <b><i>'.$remitente.'<i><b>';  
		}
	  	
	 	if($estado_guia_recepcion == 'ANULADA')
		{
	        $asunto = 'Anulacion de Guia de Recepcion N� '.$nro_guia_recepcion;
	        $mensaje = 'Se ha <b>ANULADO</b> la <b>Guia de Recepcion N� '.$nro_guia_recepcion.'</b> por el usuario <b><i>'.$remitente.'<i><b>';
		}
		
	  	$cabeceras  = 'MIME-Version: 1.0' . "\n";
        $cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
        $cabeceras .= 'From: '.$mail_remitente. "\n";
        //se comenta el envio de mail por q ya no es necesario => Vmelo. 
        //mail($para_admin1, $asunto, $mensaje, $cabeceras);
        //mail($para_admin2, $asunto, $mensaje, $cabeceras);
 		return 0;
   	}
	
	function print_record($tipo_impresion) {
		if($tipo_impresion == 'S'){
			$cod_guia_recepcion = $this->get_key();
			$COD_ESTADO_GUIA_RECEPCION = $this->dws['dw_guia_recepcion']->get_item(0, 'COD_ESTADO_GUIA_RECEPCION');
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$db->BEGIN_TRANSACTION();
			$sp = 'spu_guia_recepcion';
			$param = "'PRINT', $cod_guia_recepcion, $this->cod_usuario";
		
			if ($db->EXECUTE_SP($sp, $param)){	// aqui dentro del sp se cambia el estado y se graba todo lo relacionado
				$db->COMMIT_TRANSACTION();
					
				$estado_gr_impresa = self::K_ESTADO_GR_IMPRESA; 
				$cod_estado_guia_recepcion = $this->dws['dw_guia_recepcion']->get_item(0, 'COD_ESTADO_GUIA_RECEPCION');
				if ($cod_estado_guia_recepcion != $estado_gr_impresa){//es la 1era vez que se imprime la Guia de Despacho
					$this->envia_mail($db, $cod_guia_recepcion);
					$this->f_envia_mail('IMPRESO');
				}
				$sql= "SELECT	GR.COD_GUIA_RECEPCION 
								,dbo.f_format_date(GR.FECHA_GUIA_RECEPCION,3)FECHA_GUIA_RECEPCION
								,GR.COD_TIPO_GUIA_RECEPCION
								,CASE GR.TIPO_DOC 
									WHEN 'GUIA_DESPACHO' THEN 'GUIA DESPACHO' 
									WHEN 'FACTURA' THEN 'FACTURA'
									WHEN 'ARRIENDO' THEN 'CONTRATO ARRIENDO'
									ELSE NULL
								END TIPO_DOC
								,GR.NRO_DOC
								,OBS
								,E.NOM_EMPRESA
								,E.RUT
								,E.DIG_VERIF
								,U.NOM_USUARIO
								,dbo.f_get_emisor_doc(".$cod_guia_recepcion.",GR.COD_TIPO_GUIA_RECEPCION,GR.TIPO_DOC) INI_USUARIO
								,P.NOM_PERSONA
								,TGR.NOM_TIPO_GUIA_RECEPCION
								,S.DIRECCION
								,S.TELEFONO
								,S.FAX
								,IGR.COD_PRODUCTO
								,IGR.NOM_PRODUCTO
								,IGR.CANTIDAD
								,COM.NOM_COMUNA
								,CIU.NOM_CIUDAD
								,CASE
									WHEN GR.TIPO_DOC = 'FACTURA' THEN (SELECT CONVERT(VARCHAR,COD_DOC)
																	FROM FACTURA
																	WHERE COD_FACTURA = GR.COD_DOC)
									WHEN GR.TIPO_DOC = 'GUIA_DESPACHO' THEN (SELECT CONVERT(VARCHAR,COD_DOC)
																			FROM GUIA_DESPACHO
																			WHERE COD_GUIA_DESPACHO = GR.COD_DOC)
									WHEN GR.TIPO_DOC = 'NOTA_VENTA' THEN NRO_DOC
								END COD_NOTA_VENTA
						FROM	GUIA_RECEPCION GR,
								SUCURSAL S left outer join COMUNA COM on S.COD_COMUNA = COM.COD_COMUNA, 
								ITEM_GUIA_RECEPCION IGR, EMPRESA E, USUARIO U, PERSONA P,
								TIPO_GUIA_RECEPCION TGR, CIUDAD CIU
						WHERE	GR.COD_GUIA_RECEPCION = ".$cod_guia_recepcion." AND
								IGR.COD_GUIA_RECEPCION = GR.COD_GUIA_RECEPCION AND
								E.COD_EMPRESA = GR.COD_EMPRESA AND
								U.COD_USUARIO = GR.COD_USUARIO AND
								P.COD_PERSONA = GR.COD_PERSONA AND
								TGR.COD_TIPO_GUIA_RECEPCION = GR.COD_TIPO_GUIA_RECEPCION AND
								S.COD_SUCURSAL = GR.COD_SUCURSAL AND
								S.COD_CIUDAD = CIU.COD_CIUDAD";
				//// reporte
				$labels = array();
				$labels['strCOD_GUIA_RECEPCION'] = $cod_guia_recepcion;
				$rpt = new print_guia_recepcion($sql, $this->root_dir.'appl/guia_recepcion/guia_recepcion.xml', $labels, "Guia de Recepcion ".$cod_guia_recepcion.".pdf", 1);
				$this->_load_record();
				return true;
			}else{
				$db->ROLLBACK_TRANSACTION();
				return false;
			}
		}else{
			
			$sql= "SELECT	GR.COD_GUIA_RECEPCION 
								,dbo.f_format_date(GR.FECHA_GUIA_RECEPCION,3)FECHA_GUIA_RECEPCION
								,GR.COD_TIPO_GUIA_RECEPCION
								,CASE GR.TIPO_DOC 
									WHEN 'GUIA_DESPACHO' THEN 'GUIA DESPACHO' 
									WHEN 'FACTURA' THEN 'FACTURA'
									WHEN 'ARRIENDO' THEN 'CONTRATO ARRIENDO'
									ELSE NULL
								END TIPO_DOC
								,GR.NRO_DOC
								,OBS
								,E.NOM_EMPRESA
								,E.RUT
								,E.DIG_VERIF
								,U.NOM_USUARIO
								,dbo.f_get_emisor_doc(".$cod_guia_recepcion.",GR.COD_TIPO_GUIA_RECEPCION,GR.TIPO_DOC) INI_USUARIO
								,P.NOM_PERSONA
								,TGR.NOM_TIPO_GUIA_RECEPCION
								,S.DIRECCION
								,S.TELEFONO
								,S.FAX
								,IGR.COD_PRODUCTO
								,IGR.NOM_PRODUCTO
								,IGR.CANTIDAD
								,COM.NOM_COMUNA
								,CIU.NOM_CIUDAD
								,CASE
									WHEN GR.TIPO_DOC = 'FACTURA' THEN (SELECT CONVERT(VARCHAR,COD_DOC)
																	FROM FACTURA
																	WHERE COD_FACTURA = GR.COD_DOC)
									WHEN GR.TIPO_DOC = 'GUIA_DESPACHO' THEN (SELECT CONVERT(VARCHAR,COD_DOC)
																			FROM GUIA_DESPACHO
																			WHERE COD_GUIA_DESPACHO = GR.COD_DOC)
									WHEN GR.TIPO_DOC = 'NOTA_VENTA' THEN NRO_DOC
								END COD_NOTA_VENTA
						FROM	GUIA_RECEPCION GR,
								SUCURSAL S left outer join COMUNA COM on S.COD_COMUNA = COM.COD_COMUNA, 
								ITEM_GUIA_RECEPCION IGR, EMPRESA E, USUARIO U, PERSONA P,
								TIPO_GUIA_RECEPCION TGR, CIUDAD CIU
						WHERE	GR.COD_GUIA_RECEPCION = ".$cod_guia_recepcion." AND
								IGR.COD_GUIA_RECEPCION = GR.COD_GUIA_RECEPCION AND
								E.COD_EMPRESA = GR.COD_EMPRESA AND
								U.COD_USUARIO = GR.COD_USUARIO AND
								P.COD_PERSONA = GR.COD_PERSONA AND
								TGR.COD_TIPO_GUIA_RECEPCION = GR.COD_TIPO_GUIA_RECEPCION AND
								S.COD_SUCURSAL = GR.COD_SUCURSAL AND
								S.COD_CIUDAD = CIU.COD_CIUDAD";
			
			//// reporte
			$labels = array();
			$labels['strCOD_GUIA_RECEPCION'] = $cod_guia_recepcion;
			$rpt = new print_guia_recepcion_secundario($sql, $this->root_dir.'appl/guia_recepcion/guia_recepcion_secundario.xml', $labels, "Guia de Recepcion ".$cod_guia_recepcion.".pdf", 1);
			$this->_load_record();
			return true;
		}		
	}
}

class print_guia_recepcion extends print_guia_recepcion_base {	
	function print_guia_recepcion($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
	
	function modifica_pdf(&$pdf) {
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$result = $db->build_results($this->sql);
			
			$cod_guia_recepcion	= $result[0]['COD_GUIA_RECEPCION'];
			
			$y_ini = $pdf->GetY() + 50;
			
			$sql=	"SELECT	'OBSERVACION:' TITULO_OBS
							,OBS
							,'RECIBI CONFORME' CONFORME
					FROM	GUIA_RECEPCION GR,
							SUCURSAL S left outer join COMUNA COM on S.COD_COMUNA = COM.COD_COMUNA, 
							ITEM_GUIA_RECEPCION IGR, EMPRESA E, USUARIO U, PERSONA P,
							TIPO_GUIA_RECEPCION TGR, CIUDAD CIU
					WHERE	GR.COD_GUIA_RECEPCION = $cod_guia_recepcion AND
							IGR.COD_GUIA_RECEPCION = GR.COD_GUIA_RECEPCION AND
							E.COD_EMPRESA = GR.COD_EMPRESA AND
							U.COD_USUARIO = GR.COD_USUARIO AND
							P.COD_PERSONA = GR.COD_PERSONA AND
							TGR.COD_TIPO_GUIA_RECEPCION = GR.COD_TIPO_GUIA_RECEPCION AND
							S.COD_SUCURSAL = GR.COD_SUCURSAL AND
							S.COD_CIUDAD = CIU.COD_CIUDAD";
			
			$result_guia_recepcion = $db->build_results($sql);
			
			$obs	=	$result_guia_recepcion[0]['OBS'];
			$titulo_obs	=	$result_guia_recepcion[0]['TITULO_OBS'];
			$conforme	=	$result_guia_recepcion[0]['CONFORME'];	
			
			$pdf->SetFont('Arial','',8.5);
			$pdf->SetXY(30,$y_ini-15);
			$pdf->Cell(555, 15, $titulo_obs, '', '','L');
			
			$pdf->SetXY(30,$y_ini);
			$pdf->Cell(355,90, '', 'LTRB', '','C');
			
			$pdf->SetXY(30,$y_ini+5);
			$pdf->MultiCell(355, 10, $obs, '', 'L');
			
			$pdf->SetXY(385,$y_ini);
			$pdf->Cell(200,75,'', 'TR', '','C');
			
			$pdf->SetXY(385,$y_ini+65);
			$pdf->Cell(200,25, $conforme, 'TRB', '','C');
			
	}
}

class print_guia_recepcion_secundario extends print_guia_recepcion_base {	
	function print_guia_recepcion_secundario($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
	
	function modifica_pdf(&$pdf) {
			//$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			//$result = $db->build_results($this->sql);
			
			//$pdf->SetFont('Arial','',8.5);
			//$pdf->SetXY(30, 100);
			//$pdf->Cell(30, 15, 'Hola Mundo', '0', '','L');


			$y=0;
			$pdf->SetTextColor(0,0,10);//TEXTOS azul
			$pdf->SetFont('Arial','B',14);
			$pdf->SetXY(30, $y+65+20);
			$pdf->Cell(550, 17, 'T�rminos y Condiciones.', 0, 0, 'C');

			$pdf->SetTextColor(0,0,10);//TEXTOS azul
			$pdf->SetFont('Arial','B',9);
			$pdf->SetXY(30, $y+96);
			$pdf->Cell(70, 15, 'Garant�a.', 0, 0, 'L');
			$pdf->Line(33,108,75,108);

			$pdf->SetFont('Arial','',9);
			$pdf->SetXY(30, $y+112);
			$pdf->MultiCell(550, 14, "Los productos comercializados por BIGGI, poseen una garant�a de 06 (SEIS) MESES a contar de su fecha de adquisici�n (fecha de facturaci�n), contra defectos de materiales y/o fabricaci�n, siempre y cuando, se cumpla con el uso para el cual fue dise�ado (USO EXCLUSIVO EN COCINA INDUSTRIAL), y se realicen las mantenciones preventivas y/o correctivas conforme al manual de usuario proporcionado con el equipo.\nSe deja expresa constancia que la garant�a no cubre un uso distinto al se�alado, ni desperfectos originados por el mal uso, instalaci�n inadecuada (los equipos comercializados est�n dise�ado para uso dentro de un bien inmueble, no as� para el uso en veh�culos u otros medios de transporte), uso inadecuado, mal trato, da�os por actos maliciosos, da�os por actos terroristas, da�os por actos vand�licos, uso indebido por personal carente de capacitaci�n (PRODUCTO DE USO INDUSTRIAL), malos manejos durante el bodegaje o traslado, da�os causados por la naturaleza (sismos, lluvias, inundaciones, etc.), da�os ocasionados por fallas en las redes de alimentaci�n (electricidad, gas y/o agua), productos intervenidos o adulterados por terceras personas, como tampoco los producidos por caso fortuito y/o fuerza mayor.\nLa garant�a de los equipos es efectiva en f�brica, ubicada en Portugal N� 1726, comuna de Santiago, ciudad de Santiago. Los gastos de fletes de equipos en garant�a correr�n por parte del Cliente.\nLos productos �hechos con materiales usados o reciclados�, de �segunda selecci�n� u otras equivalentes, conforme a la ley, no tienen garant�a legal. A su vez, �stos no tendr�n derecho a cambio o devoluci�n, si al momento de realizar la compra se informa de dicha condici�n.\nLos plazos de garant�a legal o voluntaria se cuentan desde la fecha de compra del equipo. En caso de ser reparados o reemplazados, se mantendr� el plazo de garant�a original a partir de la fecha de compra. El plazo de garant�a legal, se suspender�n durante el tiempo en que el bien est� siendo reparado en ejercicio de la garant�a.\nAntes de proceder con la devoluci�n, cambio � reparaci�n de un producto, este deber� ser revisado por el servicio t�cnico de BIGGI, con la finalidad de establecer que la falla est� cubierta por la garant�a antes mencionada.", 0, 'J', false);

			$pdf->SetFont('Arial','B',9);
			$pdf->SetXY(30, $y+407);
			$pdf->MultiCell(550, 14, 'Derecho a retracto en compras online y telef�nicas.', 0, 'J', false);
			$pdf->Line(33,418,255,418);
			$pdf->SetFont('Arial','',9);

			$pdf->SetXY(30, $y+421);
			$pdf->MultiCell(550, 14, 'Seg�n la ley vigente, el COMPRADOR puede arrepentirse de una compra realizada online (por Internet) o por tel�fono, por un periodo de 10 (DIEZ) D�AS DESDE QUE RECIBI� EL PRODUCTO. Siempre y cuando, el producto haya sido adquirido por INTERNET o POR TEL�FONO, y antes de HABER SIDO UTILIZADO. El costo del flete correr� por parte del cliente.', 0, 'J', false);

			$pdf->SetFont('Arial','B',9);
			$pdf->SetXY(30, $y+468);
			$pdf->MultiCell(550, 14, 'Servicio T�cnico.', 0, 'J', false);
			$pdf->Line(33,480,109,480);
			$pdf->SetFont('Arial','',9);

			$pdf->SetXY(30, $y+482);
			$pdf->MultiCell(550, 14,
				"BIGGI se reserva el derecho a decidir aut�nomamente sobre si aceptar en reparaci�n un equipo o art�culo fuera de garant�a legal, despu�s de un an�lisis de antecedentes relevante tales como: la antig�edad de este, la disponibilidad de los repuestos correspondiente, la factibilidad de la reparaci�n, entre otros.\nTodos los diagn�sticos realizados por el servicio t�cnico de BIGGI, los cuales no est�n cubiertos por la p�liza de garant�a, tienen un costo asociado equivalente a 2 (DOS) U.F., el cual deber� ser pagado al momento del ingreso del producto al servicio t�cnico.\nEl resultado de la evaluaci�n, plazos de reparaci�n y costo de esta, ser�n informados al cliente mediante una cotizaci�n formal, dentro de un plazo no superior a 5 (CINCO) D�AS H�BILES. En el caso de hacerse efectiva la reparaci�n, el valor abonado ser� descontado del total a pagar.\nSolo se recibir�n productos que hayan sido comprados a COMERCIAL BIGGI (CHILE) S.A. y que vengan acompa�ados de su respectivo comprobante de compra (Factura).\nPara hacer el retiro del o los productos ingresados al servicio t�cnico, se debe presentar �nica y exclusivamente el comprobante de ingreso, el cual acredita al portador para el retiro del o los bienes reparados. BIGGI no se responsabiliza por el extrav�o del comprobante de ingreso a servicio t�cnico.\nUna vez transcurrido 15 (QUINCE) D�AS H�BILES desde que se haya notificado que el o los productos est�n disponibles para su retiro, BIGGI estar� facultada para cobrar por concepto de bodegaje el equivalente a 1% (UNO POR CIENTO) DIARIO DEL VALOR DEL PRODUCTO.\nBIGGI no se har� responsable por las especies entregadas en reparaci�n, cuando �stas no sean retiradas en el plazo de 6 (SEIS) MESES A CONTAR DE LA FECHA DE RECEPCI�N."
			, 0, 'J', false);


	}
}
?>