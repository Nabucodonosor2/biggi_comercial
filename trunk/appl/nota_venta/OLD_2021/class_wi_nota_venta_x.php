<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_cot_nv.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/class_PHPMailer.php");
require_once(dirname(__FILE__)."/../ws_client_biggi/class_client_biggi.php");

class dw_item_resumen_nv extends datawindow{
	function dw_item_resumen_nv(){
		$sql = "SELECT		COD_PRODUCTO R_COD_PRODUCTO,
							NOM_PRODUCTO R_NOM_PRODUCTO,
							SUM(CANTIDAD) R_CANTIDAD,
							PRECIO R_PRECIO,
							SUM(CANTIDAD * PRECIO) R_TOTAL
				FROM		ITEM_NOTA_VENTA
				WHERE		COD_NOTA_VENTA = {KEY1}
				AND			COD_PRODUCTO not in ('T', 'TE', 'F', 'I', 'E')
				GROUP BY COD_PRODUCTO, NOM_PRODUCTO, PRECIO
				UNION
				SELECT		COD_PRODUCTO R_COD_PRODUCTO,
							NOM_PRODUCTO R_NOM_PRODUCTO,
							SUM(CANTIDAD) R_CANTIDAD,
							PRECIO R_PRECIO,
							SUM(CANTIDAD * PRECIO) R_TOTAL
				FROM		ITEM_NOTA_VENTA
				WHERE		COD_NOTA_VENTA = {KEY1}
				AND			COD_PRODUCTO in ('TE', 'F', 'I', 'E')
				GROUP BY COD_PRODUCTO, NOM_PRODUCTO, PRECIO";


		parent::datawindow($sql, 'ITEM_RESUMEN_NV');

		$this->add_control(new static_text('R_COD_PRODUCTO'));
		$this->add_control(new static_text('R_NOM_PRODUCTO'));
		$this->add_control(new static_num('R_CANTIDAD',1));
		$this->add_control(new static_num('R_PRECIO'));
		$this->add_control(new static_num('R_TOTAL'));
	}
}

class dw_nv_item_od_ws extends datawindow{
	private	$cod_orden_despacho_old;
	private $count;

	function dw_nv_item_od_ws(){
		
		$sql = "SELECT COD_NV_ITEM_OD_WS
					  ,SISTEMA_WS
					  ,COD_NOTA_VENTA
					  ,COD_ORDEN_DESPACHO
					  ,COD_ITEM_ORDEN_DESPACHO
					  ,COD_PRODUCTO
					  ,NOM_PRODUCTO
					  ,CANTIDAD_ENTREGADA
					  ,FECHA_REGISTRO
					  ,CHEQ_RECIBIDO
					  ,NRO_FACTURA
					  ,CONVERT(VARCHAR, FECHA_FACTURA, 103) FECHA_FACTURA
					  ,CONVERT(VARCHAR, FECHA_ORDEN_DESPACHO, 103)+' '+CONVERT(VARCHAR, FECHA_ORDEN_DESPACHO, 108) FECHA_ORDEN_DESPACHO
					  ,NOM_USUARIO_OD
					  ,'N' NEW_CHECK_RECIBIDO
				FROM NV_ITEM_OD_WS
				WHERE COD_NOTA_VENTA = {KEY1}
				ORDER BY COD_ORDEN_DESPACHO ASC";

		parent::datawindow($sql, 'NV_ITEM_OD_WS', true, true);
		
		$this->add_control($control = new edit_check_box('CHEQ_RECIBIDO','S','N'));
		$control->set_onChange('new_check_recibido(this);');
		$this->add_control(new edit_text_hidden('NEW_CHECK_RECIBIDO'));
		$this->set_protect('CHEQ_RECIBIDO', "[CHEQ_RECIBIDO] == 'S'");
	}
	
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);
		$cod_orden_despacho = $this->get_item($record, 'COD_ORDEN_DESPACHO');
		
		if($record == 0)
			$this->cod_orden_despacho_old = $cod_orden_despacho;

		if($cod_orden_despacho <> $this->cod_orden_despacho_old)
			$this->count++;
		
		if($this->count % 2 == 0)
			$temp->setVar($this->label_record.'.DW_TR_POR_OD', 'claro');
		else
			$temp->setVar($this->label_record.'.DW_TR_POR_OD', 'oscuro');
			
		$this->cod_orden_despacho_old = $cod_orden_despacho;
	}
	
	function update($db){
		$sp = 'spu_pre_orden_compra';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
			
			$COD_NV_ITEM_OD_WS	= $this->get_item($i, 'COD_NV_ITEM_OD_WS');
			$CHEQ_RECIBIDO		= $this->get_item($i, 'CHEQ_RECIBIDO');
		
			$operacion = 'UPDATE';
			
			$sp = "spu_nv_item_from_od";	
			$param = "'$operacion'
					 ,$COD_NV_ITEM_OD_WS
					 ,NULL
					 ,NULL
					 ,NULL
					 ,NULL
					 ,NULL
					 ,NULL
					 ,NULL
					 ,'$CHEQ_RECIBIDO'";				
				
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
				
		}	
		return true;
	}
}

class dw_llamado extends datawindow {
	function dw_llamado() {
		
		$sql = "SELECT LL.COD_LLAMADO LL_COD_LLAMADO
						,CONVERT (VARCHAR(10), LL.FECHA_LLAMADO, 103) LL_FECHA_LLAMADO
						,LLA.NOM_LLAMADO_ACCION LL_NOM_LLAMADO_ACCION
						,C.NOM_CONTACTO LL_NOM_CONTACTO
						,dbo.f_llamado_telefono(LL.COD_CONTACTO, 'EMPRESA') LL_TELEFONO_CONTACTO
						,CP.NOM_PERSONA LL_NOM_PERSONA
						,dbo.f_llamado_telefono(LL.COD_CONTACTO_PERSONA, 'PERSONA') LL_TELEFONO_PERSONA
						,LL.MENSAJE LL_MENSAJE
					FROM LLAMADO LL
						,LLAMADO_ACCION LLA
						,CONTACTO C
						,CONTACTO_PERSONA CP
				   WHERE LL.TIPO_DOC_REALIZADO = 'NOTA VENTA'
				   	 AND LL.COD_DOC_REALIZADO = {KEY1}
					 AND LLA.COD_LLAMADO_ACCION = LL.COD_LLAMADO_ACCION
					 AND C.COD_CONTACTO = LL.COD_CONTACTO
					 AND CP.COD_CONTACTO_PERSONA = LL.COD_CONTACTO_PERSONA";

		parent::datawindow($sql);

		$this->add_control($control = new edit_num('LL_COD_LLAMADO', 23, 23));
		$control->set_onChange('find_1_llamado(this);');
		
		$this->add_control(new static_text('LL_FECHA_LLAMADO'));
		$this->add_control(new static_text('LL_NOM_LLAMADO_ACCION'));
		$this->add_control(new static_text('LL_NOM_CONTACTO'));
		$this->add_control(new static_text('LL_TELEFONO_CONTACTO'));
		$this->add_control(new static_text('LL_NOM_PERSONA'));
		$this->add_control(new static_text('LL_TELEFONO_PERSONA'));
		$this->add_control(new edit_text_multiline('LL_MENSAJE', 80, 3));
		$this->set_entrable('LL_MENSAJE', false);
	}
}
class input_file extends edit_control {
	function input_file($field) {
		parent::edit_control($field);
	}
	function draw_entrable($dato, $record) {
		$field = $this->field.'_'.$record;
		return '<input type="file" name="'.$field.'" id="'.$field.'" class="Button"/>';
	}
	function draw_no_entrable($dato, $record) {
		return '';
	}
}
class dw_docs extends datawindow {
	function dw_docs() {
		$sql = "select NVD.COD_NOTA_VENTA_DOCS    D_COD_NOTA_VENTA_DOCS  
    					,null				      D_COD_ENCRIPT
    					,NVD.COD_NOTA_VENTA       D_COD_NOTA_VENTA
    					,NVD.COD_USUARIO          D_COD_USUARIO
    					,U.NOM_USUARIO            D_NOM_USUARIO
    					,NVD.RUTA_ARCHIVO         D_RUTA_ARCHIVO
    					,NVD.NOM_ARCHIVO          D_NOM_ARCHIVO
    					,''						  ELIMINA_DOC
    					,NVD.NOM_ARCHIVO          D_NOM_ARCHIVO_REF
    					,convert(varchar, NVD.FECHA_REGISTRO, 103)       D_FECHA_REGISTRO
    					,NVD.OBS                  D_OBS
    					,null          	  		  D_FILE
    					,''          	  		  D_DIV_LINK
    					,'none'          	  	  D_DIV_FILE
    					,NVD.ES_OC				  D_ES_OC
    					,''						  D_VALUE_OPTION
				from NOTA_VENTA_DOCS NVD, USUARIO U
				where COD_NOTA_VENTA = {KEY1} 
				  and U.COD_USUARIO = NVD.COD_USUARIO"; 
		parent::datawindow($sql, 'NV_DOCS', true, true);
		$this->add_control(new edit_text_upper('D_OBS',100, 50));
		$this->add_control(new static_text('D_NOM_ARCHIVO'));
		$this->add_control(new input_file('D_FILE'));
		$this->add_control($control = new edit_radio_button('D_ES_OC', 'S', 'N','','OC'));
		$control->set_onChange("change_option();");
		$this->add_control(new edit_text_hidden('D_VALUE_OPTION'));

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
	function retrieve($cod_nota_venta) {
		parent::retrieve($cod_nota_venta);
		for($i=0; $i<$this->row_count(); $i++) {
			$cod_nota_venta_docs = $this->get_item($i, 'D_COD_NOTA_VENTA_DOCS');
			$this->set_item($i, 'D_COD_ENCRIPT', base64_encode($cod_nota_venta_docs));
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
		$this->set_item($row, 'D_VALUE_OPTION', 'N');
		return $row;
	}
	
	function get_ruta($cod_nota_venta) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select year(NV.FECHA_NOTA_VENTA) ANO
						,upper(M.NOM_MES) NOM_MES
						,replace(CONVERT(varchar, NV.FECHA_NOTA_VENTA, 103), '/', '-') FECHA
						,P.VALOR RUTA
				from NOTA_VENTA NV, MES M, PARAMETRO P
				where NV.COD_NOTA_VENTA = $cod_nota_venta
				  and M.COD_MES = month(NV.FECHA_NOTA_VENTA)
  				  and P.COD_PARAMETRO = 56";	// RUTA DOCS
      	$result = $db->build_results($sql);
      	$folder = $result[0]['RUTA'].$result[0]['ANO']."/".$result[0]['NOM_MES']."/".$result[0]['FECHA']."/".$cod_nota_venta."/";
		if (!file_exists($folder))	
			$res = mkdir($folder, 0777 , true);	// recursive = true		
			
		return $folder;
	}
	function update($db, $cod_nota_venta)	{
		$sp = 'spu_nota_venta_docs';

		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;			

			if ($statuts == K_ROW_NEW_MODIFIED) {
				$operacion = 'INSERT';
				$cod_nota_venta_docs = 'null';
				$cod_usuario = $this->cod_usuario;

				// subir archivo
				$ruta_archivo = $this->get_ruta($cod_nota_venta);	// obtiene la ruta donde debe quesdar 

				// direccion absoluta
				$row = $this->redirect($i);
				$file = 'D_FILE_'.$row;
				$nom_archivo = $_FILES[$file]['name'];
				$char = '';
				$pos  = 0;
				$nom_archivo_s='';
				/*
				 * Si el nombre del archivo tiene mas de 94 caracteres
				 * busca el ultimo punto para extraer los caracteres antes de la extension para
				 * acortar el nombre del archivo
				 */
				if(strlen($nom_archivo) > 94){
					for($j=0 ; $j < strlen($nom_archivo) ; $j++){
						$char = substr($nom_archivo, $j, 1);
						if($char == '.')
							$pos = $j;
					}
					$nom_archivo_s = substr($nom_archivo, 0, 90); //nombre archivo sin extension truncado
					$nom_archivo   = substr($nom_archivo, $pos, strlen($nom_archivo)); //la extension
					
					$nom_archivo = $nom_archivo_s.$nom_archivo;
				}
				$e		= array(archivo::getTipoArchivo($nom_archivo));
				$t		= $_FILES[$file]['size'];
				$tmp	= $_FILES[$file]['tmp_name'];
				
				$size_x_bytes = floor((($t/1024)/1024) * 1000) / 1000;
				
				if($t > (($this->get_parametro(67) * 1024) * 1024)){
					$this->alert('El archivo que intenta subir excede el peso máximo en disco ('.$this->get_parametro(67).' MB), pero su archivo pesa = '.number_format($size_x_bytes, 2, ',', '.').' MB');
			 		return true;
				}
				
				$archivo = new archivo($nom_archivo, $ruta_archivo, $e,$t,$tmp);
			 	$u = $archivo->upLoadFile();	// sube el archivo al directorio definitivo
			 	
			 	if(!file_exists($ruta_archivo.$nom_archivo)){
			 		$this->alert('Se ha producido un error en la carga del archivo seleccionado.\nPuede deberse a problemas de red y/o archivo dañado.\nInformar a Integrasystem el Nro de Nota Venta y archivo que se desea cargar.');
			 		return true;
			 	}
			 	
			}
			elseif ($statuts == K_ROW_MODIFIED) {
				$operacion = 'UPDATE';
				$cod_nota_venta_docs = $this->get_item($i, 'D_COD_NOTA_VENTA_DOCS');
				$cod_usuario = 'null';
				$nom_archivo = 'null';
				$ruta_archivo = 'null';
			}			
			$obs = $this->get_item($i, 'D_OBS');
			$obs = $obs =='' ? 'null' : "'$obs'";
			$es_oc = $this->get_item($i, 'D_VALUE_OPTION');

			$param = "'$operacion'
					,$cod_nota_venta_docs 
					,$cod_nota_venta 
					,$cod_usuario
					,'$ruta_archivo'
					,'$nom_archivo'
					,$obs
					,'$es_oc'";
					
			if (!$db->EXECUTE_SP($sp, $param))
				return false;	
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');			
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
			
			$cod_nota_venta_docs = $this->get_item($i, 'D_COD_NOTA_VENTA_DOCS', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_nota_venta_docs"))
				return false;
				
			$ruta_archivo = $this->get_item($i, 'D_RUTA_ARCHIVO', 'delete');
			$nom_archivo = $this->get_item($i, 'D_NOM_ARCHIVO', 'delete');
			
			if (file_exists($ruta_archivo.$nom_archivo))
				unlink($ruta_archivo.$nom_archivo);		
		}			
		return true;
	}
}
class dw_item_nota_venta extends dw_item {
	function dw_item_nota_venta () {		
	
		//todos los campos que se agreguen en el select se deben agregar en función "creada_desde"
		$sql = "select COD_ITEM_NOTA_VENTA,
					COD_NOTA_VENTA,
					ORDEN,
					ITEM,
					COD_PRODUCTO,
					COD_PRODUCTO COD_PRODUCTO_OLD,
					COD_PRODUCTO COD_PRODUCTO_H,
					NOM_PRODUCTO,
                    NOM_PRODUCTO NOM_PRODUCTO_H,
					CANTIDAD,
					CANTIDAD CANTIDAD_H,
					PRECIO,
					COD_TIPO_GAS,
					COD_TIPO_ELECTRICIDAD,
					'' MOTIVO,
					dbo.f_nv_get_ct_con_preorden(COD_ITEM_NOTA_VENTA) CANTIDAD_PRECOMPRA,			
					dbo.f_nv_get_ct_con_orden(COD_ITEM_NOTA_VENTA)  CANTIDAD_COMPRA,		
					COD_TIPO_TE,
					MOTIVO_TE,
					case COD_PRODUCTO when 'TE' 
						then dbo.f_nv_pend_te(COD_ITEM_NOTA_VENTA)
					else
						''
					end PEND_AUTORIZA,
					case COD_PRODUCTO when 'TE' 
						then dbo.f_nv_get_datos_autoriza_te(COD_ITEM_NOTA_VENTA, 'MOTIVO_AUTORIZA')
					else
						''
					end MOTIVO_AUTORIZA_TE,
					case COD_PRODUCTO when 'TE' 
						then dbo.f_nv_get_datos_autoriza_te(COD_ITEM_NOTA_VENTA, 'FECHA_AUTORIZA')
					else
						''
					end FECHA_AUTORIZA_TE,
					case COD_PRODUCTO when 'TE' 
						then dbo.f_nv_get_datos_autoriza_te(COD_ITEM_NOTA_VENTA, 'USUARIO_AUTORIZA')
					else
						''
					end NOM_USUARIO_AUTORIZA_TE
					,null ENTRABLE_PRECIO
					,'N' IS_NEW
				from ITEM_NOTA_VENTA
				where COD_NOTA_VENTA = {KEY1}
				order by ORDEN asc";
					
					
		parent::dw_item($sql, 'ITEM_NOTA_VENTA', true, true, 'COD_PRODUCTO');	
		
		$this->add_control(new edit_num('ORDEN',4, 5));
		$this->add_control(new edit_text_upper('ITEM',4, 5));
		$this->add_control(new edit_cantidad('CANTIDAD',5));
		$this->add_control(new edit_text_hidden('COD_ITEM_NOTA_VENTA'));
		$this->add_control(new edit_text_hidden('COD_PRODUCTO_H'));
		$this->add_control(new edit_text_hidden('CANTIDAD_H'));
		$this->add_control(new edit_text('MOTIVO',10, 100, 'hidden'));
		$this->add_control(new edit_text('COD_TIPO_TE',10, 100, 'hidden'));
		$this->add_control(new edit_text('MOTIVO_TE',10, 100, 'hidden'));
		$this->add_control(new edit_text('IS_NEW',3, 3, 'hidden'));
		
		$this->add_control(new computed('PRECIO', 0));		
		$this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]');
		$this->accumulate('TOTAL', "calc_dscto();");
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		
		// Agrega script adicional a ITEM para traspasar el cambi a PRE_ORDEN_COMPRA
		$this->controls['ITEM']->set_onChange("change_item_nota_venta(this, 'ITEM');");
		
		
		// Agrega script adicional a COD_PRODUCTO para traspasar el cambi a PRE_ORDEN_COMPRA
		$this->controls['COD_PRODUCTO']->set_onChange("change_item_nota_venta(this, 'COD_PRODUCTO');");
		
		// Agrega script adicional a NOM_PRODUCTO para traspasar el cambi a PRE_ORDEN_COMPRA
		$this->controls['NOM_PRODUCTO']->set_onChange("change_item_nota_venta(this, 'NOM_PRODUCTO');");
		
		// Agrega script adicional a CANTIDAD para traspasar el cambi a PRE_ORDEN_COMPRA
		$java_script = $this->controls['CANTIDAD']->get_onChange();
		$java_script .= " change_item_nota_venta(this, 'CANTIDAD'); change_forma_pago('', document.getElementById('COD_FORMA_PAGO_0'));";
		$this->controls['CANTIDAD']->set_onChange($java_script);
		
		$this->add_control(new edit_text('CANTIDAD_PRECOMPRA', 20, 20, 'hidden'));
		$this->add_control(new edit_text('CANTIDAD_COMPRA', 20, 20, 'hidden'));
		
		$sql = "select COD_TIPO_GAS, NOM_TIPO_GAS
				from TIPO_GAS
				order by ORDEN";
		$this->add_control(new drop_down_dw('COD_TIPO_GAS', $sql, 80));					

		$sql = "select COD_TIPO_ELECTRICIDAD, NOM_TIPO_ELECTRICIDAD
				from TIPO_ELECTRICIDAD
				order by ORDEN";
		$this->add_control(new drop_down_dw('COD_TIPO_ELECTRICIDAD', $sql, 80));					
		$this->add_control(new edit_text('MOTIVO_AUTORIZA_TE', 20, 100, 'hidden'));	
		$this->add_control(new edit_text('FECHA_AUTORIZA_TE', 10, 10, 'hidden'));	
		$this->add_control(new edit_text('NOM_USUARIO_AUTORIZA_TE', 10, 10, 'hidden'));
		$this->add_control(new edit_text('NOM_PRODUCTO_H', 20, 100, 'hidden'));	
		
		$this->set_first_focus('COD_PRODUCTO');
		
		$this->set_protect('COD_PRODUCTO', "[IS_NEW]=='N'");
		$this->set_protect('NOM_PRODUCTO', "[IS_NEW]=='N'");
		
		// asigna los mandatorys
		$this->set_mandatory('ORDEN', 'Orden');
		$this->set_mandatory('COD_PRODUCTO', 'Código del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');
		

	}
	function insert_row($row=-1) {
	
		$row = parent::insert_row($row);
		$this->set_item($row, 'IS_NEW', 'S');
		$this->set_item($row, 'COD_ITEM_NOTA_VENTA',  - $row - 100);	// Se suma -100 para asegura q que negativo (para el caso 0));
		$this->set_item($row, 'ORDEN', $this->row_count() * 10);
		return $row;
	}
	function fill_record(&$temp, $record) {
		if ($this->entrable) 
			$this->set_item($record, 'ENTRABLE_PRECIO', 'true');
		else
			$this->set_item($record, 'ENTRABLE_PRECIO', 'false');
		
		parent::fill_record($temp, $record);
		if ($this->entrable) {
			$row = $this->redirect($record);
			$eliminar = '<img src="../../../../commonlib/trunk/images/b_delete_line.jpg" onClick="del_line_item(\''.$this->label_record.'_'.$row.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer">';
			$temp->setVar($this->label_record.".ELIMINAR_".strtoupper($this->label_record), $eliminar);
		}
		
		// El boton de precio esta siempre habilitado
		$cod_producto = $this->get_item($record, 'COD_PRODUCTO');
		if ($cod_producto!='T')
			$temp->setVar($this->label_record.'.DISABLE_BUTTON', '');
		else
			$temp->setVar($this->label_record.'.DISABLE_BUTTON', 'disabled="disabled"');
	}
	function update($db, &$dw_preorden)	{
		$sp = 'spu_item_nota_venta';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
			
			$COD_ITEM_NOTA_VENTA = $this->get_item($i, 'COD_ITEM_NOTA_VENTA');
			$COD_NOTA_VENTA = $this->get_item($i, 'COD_NOTA_VENTA');			
			$ORDEN = $this->get_item($i, 'ORDEN');
			$ITEM = $this->get_item($i, 'ITEM');
			$COD_PRODUCTO = $this->get_item($i, 'COD_PRODUCTO');
			$NOM_PRODUCTO = $this->get_item($i, 'NOM_PRODUCTO');
			$NOM_PRODUCTO = str_replace("'", "''", $NOM_PRODUCTO);	
			$CANTIDAD = $this->get_item($i, 'CANTIDAD');
			$PRECIO = $this->get_item($i, 'PRECIO');
			$MOTIVO_MOD_PRECIO = $this->get_item($i, 'MOTIVO');
			$COD_TIPO_GAS = $this->get_item($i, 'COD_TIPO_GAS');
			$COD_TIPO_ELECTRICIDAD = $this->get_item($i, 'COD_TIPO_ELECTRICIDAD');
			$COD_TIPO_TE = $this->get_item($i, 'COD_TIPO_TE');
			$COD_TIPO_TE = ($COD_TIPO_TE =='') ? "null" : "$COD_TIPO_TE";			
			$MOTIVO_TE = $this->get_item($i, 'MOTIVO_TE');			
			$MOTIVO_TE = ($MOTIVO_TE == '') ? "null" : "'".$MOTIVO_TE."'";
			
			$MOTIVO_AUTORIZA = trim($this->get_item($i, 'MOTIVO_AUTORIZA_TE'));
								
			$MOTIVO_AUTORIZA_PROD = ($MOTIVO_AUTORIZA=='') ? "null" : "'".$MOTIVO_AUTORIZA."'";
			$COD_ITEM_NOTA_VENTA = ($COD_ITEM_NOTA_VENTA=='') ? "null" : $COD_ITEM_NOTA_VENTA;		
			$PRECIO = ($PRECIO=='') ? 0: $PRECIO;
			$COD_TIPO_GAS = ($COD_TIPO_GAS=='') ? "null" : $COD_TIPO_GAS;
			$COD_TIPO_ELECTRICIDAD = ($COD_TIPO_ELECTRICIDAD=='') ? "null" : $COD_TIPO_ELECTRICIDAD;
			
			$MOTIVO_MOD_PRECIO = ($MOTIVO_MOD_PRECIO=='') ? "null" : "'$MOTIVO_MOD_PRECIO'";
				
			if ($MOTIVO_MOD_PRECIO == 'null')
				$COD_USUARIO_MOD_PRECIO = 'null';
			else
				$COD_USUARIO_MOD_PRECIO = session::get("COD_USUARIO");
					
			$COD_USUARIO_ELIMINA_ITEM = "null";
			
			$COD_USUARIO_ACTUAL = session::get("COD_USUARIO");
			
			if ($statuts == K_ROW_NEW_MODIFIED || $COD_ITEM_NOTA_VENTA == 'null') 
			//se agrega $COD_ITEM_NOTA_VENTA == 'null', cuando la NV es creada desde cotización, ya que $statuts == K_ROW_NEW_MODIFIED
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
				
			$param = "'$operacion', $COD_ITEM_NOTA_VENTA, $COD_NOTA_VENTA, $ORDEN, '$ITEM', '$COD_PRODUCTO', '$NOM_PRODUCTO', $CANTIDAD, $PRECIO, $MOTIVO_MOD_PRECIO, $COD_USUARIO_MOD_PRECIO, $COD_TIPO_GAS, 		
			$COD_TIPO_ELECTRICIDAD,$COD_TIPO_TE,$MOTIVO_TE, $COD_USUARIO_ELIMINA_ITEM, $MOTIVO_AUTORIZA_PROD, $COD_USUARIO_ACTUAL";			
			
			if (!$db->EXECUTE_SP($sp, $param)) 
				return false;
			else {
				if ($statuts == K_ROW_NEW_MODIFIED) {
					$COD_ITEM_NOTA_VENTA_ant = $COD_ITEM_NOTA_VENTA;
					$COD_ITEM_NOTA_VENTA = $db->GET_IDENTITY();
					$this->set_item($i, 'COD_ITEM_NOTA_VENTA', $COD_ITEM_NOTA_VENTA);

					//reemplaza utilización de redirect 
					for ($j=0; $j < $dw_preorden->row_count(); $j++) {	
						$CC_COD_ITEM_NOTA_VENTA = $dw_preorden->get_item($j, 'CC_COD_ITEM_NOTA_VENTA');
						if ($CC_COD_ITEM_NOTA_VENTA == $COD_ITEM_NOTA_VENTA_ant) {
							$dw_preorden->set_item($j, 'CC_COD_ITEM_NOTA_VENTA', $COD_ITEM_NOTA_VENTA);		
						}
					}	
					
					if($MOTIVO_MOD_PRECIO != 'null' ){
						$param = "$COD_ITEM_NOTA_VENTA, '$COD_PRODUCTO', $COD_USUARIO_MOD_PRECIO, $PRECIO, $MOTIVO_MOD_PRECIO";	
						
						if (!$db->EXECUTE_SP('sp_modifica_precio_nota_venta', $param)) 
							return false; 
					}
				}
				//si el equipo es TE y tiene autorización, busca si es nueva para crear registro AUTORIZA_TE
				if ($COD_PRODUCTO == 'TE' && $MOTIVO_AUTORIZA != ''){
			        $sql = "select count(*) COUNT_AUTORIZA_TE 
			        		from AUTORIZA_TE
							where COD_ITEM_NOTA_VENTA = ".$COD_ITEM_NOTA_VENTA;
		        	$result = $db->build_results($sql);
		        	$count_autoriza_te = $result[0]['COUNT_AUTORIZA_TE'];
		        	if ($count_autoriza_te == 0){// no tiene autorización anterior de TE
		        		$COD_USUARIO_AUTORIZA_TE = session::get("COD_USUARIO");
						$parametros_sp = "$COD_ITEM_NOTA_VENTA, $COD_USUARIO_AUTORIZA_TE, '$MOTIVO_AUTORIZA'";   
		            	if (!$db->EXECUTE_SP('sp_autoriza_te', $parametros_sp))
		                	return false;
					}
				}
			}	
		}
		
		$COD_USUARIO_ELIMINA_ITEM = session::get("COD_USUARIO");
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_ITEM_NOTA_VENTA = $this->get_item($i, 'COD_ITEM_NOTA_VENTA', 'delete');
			$param = "'DELETE', $COD_ITEM_NOTA_VENTA, null, null, null, null, null, null, null, null, null, null, 		
			null, null, null, $COD_USUARIO_ELIMINA_ITEM";
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
		}
		
		return true;
	}
}	

class dw_pre_orden_compra extends datawindow {
	private		$subtotal = 0;
		
	function dw_pre_orden_compra () {		
		$sql = "select	POC.COD_PRE_ORDEN_COMPRA,
						POC.COD_ITEM_NOTA_VENTA CC_COD_ITEM_NOTA_VENTA,
						IT.ITEM CC_ITEM,
						IT.ORDEN CC_ORDEN,
						POC.COD_EMPRESA CC_COD_PROVEEDOR,
						E.ALIAS CC_ALIAS,
						POC.COD_PRODUCTO CC_COD_PRODUCTO,
						POC.COD_PRODUCTO CC_COD_PRODUCTO_H,
						case POC.COD_PRODUCTO 
							when 'TE' then IT.NOM_PRODUCTO
							else P.NOM_PRODUCTO 
						end CC_NOM_PRODUCTO,
						POC.CANTIDAD CC_CANTIDAD,
						POC.CANTIDAD CC_CANT_COMPUESTO_H,
						POC.PRECIO_COMPRA CC_PRECIO_COMPRA,
						POC.PRECIO_COMPRA CC_PRECIO_COMPRA_H,
						POC.CANTIDAD * POC.PRECIO_COMPRA CC_TOTAL,
						POC.GENERA_COMPRA CC_GENERA_COMPRA,
						'' MOTIVO_MOD_PRECIO
				from PRE_ORDEN_COMPRA POC left outer join EMPRESA E on E.COD_EMPRESA = POC.COD_EMPRESA, ITEM_NOTA_VENTA IT, PRODUCTO P
				where IT.COD_NOTA_VENTA = {KEY1} and
					  POC.COD_ITEM_NOTA_VENTA = IT.COD_ITEM_NOTA_VENTA and
					  P.COD_PRODUCTO = POC.COD_PRODUCTO
				order by IT.ITEM, POC.COD_EMPRESA";
					
					
		parent::datawindow($sql, 'PRE_ORDEN_COMPRA', false, false);	
		
		$this->add_control(new edit_text('CC_COD_ITEM_NOTA_VENTA', 20, 20, 'hidden'));
		$this->add_control(new edit_text('COD_PRE_ORDEN_COMPRA', 20, 20, 'hidden'));
		$this->add_control(new edit_text('MOTIVO_MOD_PRECIO',10, 100, 'hidden'));
		$this->add_control(new edit_text('CC_ORDEN', 20, 20, 'hidden'));
		
		$sql = "SELECT 	E.COD_EMPRESA, 
						E.ALIAS  SC_ALIAS,
						dbo.f_prod_get_precio_costo (PP.COD_PRODUCTO, PP.COD_EMPRESA, getdate()) PRECIO_COMPRA
				FROM PRODUCTO_PROVEEDOR PP, EMPRESA E
				WHERE PP.COD_PRODUCTO = '{KEY1}' AND
					  PP.ELIMINADO = 'N' AND
					  E.COD_EMPRESA = PP.COD_EMPRESA";
		$this->add_control($control = new drop_down_dw('CC_COD_PROVEEDOR', $sql, 150));
		
		$this->add_control(new static_text('CC_ITEM'));
	
		$this->add_control(new edit_check_box('CC_GENERA_COMPRA','S','N'));
		$this->add_control(new static_text('CC_COD_PRODUCTO'));
		$this->add_control(new edit_text('CC_COD_PRODUCTO_H',10, 10, 'hidden'));
		
		$this->add_control(new static_text('CC_NOM_PRODUCTO'));
		
		$this->add_control(new static_num('CC_CANTIDAD',1));
		$this->add_control(new edit_text('CC_CANT_COMPUESTO_H',10, 10, 'hidden'));
		
		$this->add_control(new static_num('CC_PRECIO_COMPRA',0));
		$this->add_control(new edit_text('CC_PRECIO_COMPRA_H',10, 10, 'hidden'));
		
		$this->add_control(new static_num('CC_TOTAL',0));

		$this->controls['CC_COD_PROVEEDOR']->set_onChange('precio_proveedor(this);');		
		
	}
	function fill_record(&$temp, $record) {
		$cod_producto = $this->get_item($record, 'CC_COD_PRODUCTO');
		$this->controls['CC_COD_PROVEEDOR']->retrieve($cod_producto);
		parent::fill_record($temp, $record);
	}
	function update($db, $COD_NOTA_VENTA) {
		$sp = 'spu_pre_orden_compra';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
			
			$COD_PRE_ORDEN_COMPRA 	= $this->get_item($i, 'COD_PRE_ORDEN_COMPRA');
			$CC_COD_ITEM_NOTA_VENTA = $this->get_item($i, 'CC_COD_ITEM_NOTA_VENTA');
			$CC_COD_PROVEEDOR 		= $this->get_item($i, 'CC_COD_PROVEEDOR');
			$CC_CANTIDAD 			= $this->get_item($i, 'CC_CANT_COMPUESTO_H');
			$CC_PRECIO_COMPRA 		= $this->get_item($i, 'CC_PRECIO_COMPRA_H');
			$CC_COD_PRODUCTO 		= $this->get_item($i, 'CC_COD_PRODUCTO_H');
			$GENERA_COMPRA	 		= $this->get_item($i, 'CC_GENERA_COMPRA');
					
			$COD_PRE_ORDEN_COMPRA 	= ($COD_PRE_ORDEN_COMPRA=='') ? "null" : $COD_PRE_ORDEN_COMPRA;	
			$CC_COD_PROVEEDOR 		= ($CC_COD_PROVEEDOR=='') ? "null" : $CC_COD_PROVEEDOR;
			$CC_PRECIO_COMPRA 		= ($CC_PRECIO_COMPRA=='') ? "null" : $CC_PRECIO_COMPRA;
			
			$MOTIVO_MOD_PRECIO 		= $this->get_item($i, 'MOTIVO_MOD_PRECIO');	
			
			$COD_USUARIO_MOD_PRECIO = session::get("COD_USUARIO");
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED){
				$operacion = 'UPDATE';
				
				$sql = "SELECT COD_EMPRESA CC_COD_PROVEEDOR
						FROM PRE_ORDEN_COMPRA
						WHERE COD_PRE_ORDEN_COMPRA = $COD_PRE_ORDEN_COMPRA";
				$result = $db->build_results($sql);
				$CC_COD_PROVEEDOR_OLD = $result[0]['CC_COD_PROVEEDOR'];

				if (!$db->EXECUTE_SP("sp_log_cambio", "'NOTA_VENTA', '$COD_NOTA_VENTA',".$this->cod_usuario.", 'U'"))
					return false;
				
				$COD_LOG_CAMBIO = $db->GET_IDENTITY();
	
				if($CC_COD_PROVEEDOR_OLD <> $CC_COD_PROVEEDOR){
					$param = "$COD_LOG_CAMBIO
							 ,'PRE_ORDEN_COMPRA'
							 ,'CC_COD_PROVEEDOR'
							 ,'$COD_PRE_ORDEN_COMPRA'
							 ,'$CC_COD_PROVEEDOR_OLD'
							 ,'$CC_COD_PROVEEDOR'
							 ,'U'";				
					
					if (!$db->EXECUTE_SP("sp_detalle_cambio_relacionada", $param))
						return false;
				}
			}
			
			$param = "'$operacion', $COD_PRE_ORDEN_COMPRA, $CC_COD_ITEM_NOTA_VENTA, $CC_COD_PROVEEDOR, $CC_CANTIDAD, $CC_PRECIO_COMPRA, '$CC_COD_PRODUCTO', '$MOTIVO_MOD_PRECIO',$COD_USUARIO_MOD_PRECIO, '$GENERA_COMPRA'";			 			
			
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
			
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_PRE_ORDEN_COMPRA = $this->get_item($i, 'COD_PRE_ORDEN_COMPRA', 'delete');
			$param = "'DELETE', $COD_PRE_ORDEN_COMPRA";			
			if (!$db->EXECUTE_SP($sp, $param))
				return false;

		}		
		return true;
	}		
}	

class dw_doc_nota_venta extends datawindow {	
	const K_TIPO_DOC_PAGO_EFECTIVO = 1;
	const K_TIPO_DOC_PAGO_POR_DEFINIR = 8;
	
	function dw_doc_nota_venta () {		

		$sql = "select	D.COD_DOC_NOTA_VENTA,
						D.COD_NOTA_VENTA,
						D.COD_TIPO_DOC_PAGO,
						D.FECHA_REGISTRO,
						Convert(varchar, D.FECHA_DOC, 103) FECHA_DOC, 
						D.NRO_DOC,
						D.COD_BANCO,
						D.COD_PLAZA,
						D.MONTO_DOC,
						D.NRO_AUTORIZA
				from DOC_NOTA_VENTA D
				where D.COD_NOTA_VENTA = {KEY1}
				order by COD_DOC_NOTA_VENTA";
					
		parent::datawindow($sql, 'DOC_NOTA_VENTA', false, false);
		
		$sql = "select COD_TIPO_DOC_PAGO, 
						NOM_TIPO_DOC_PAGO, 
						ORDEN
				from TIPO_DOC_PAGO
				order by ORDEN asc";
				
		$this->add_control($control = new drop_down_dw('COD_TIPO_DOC_PAGO', $sql, 150));
		$control->set_onChange("valida_tipo_doc_pago(this); ");
		
		$this->add_control($control = new edit_date('FECHA_DOC'));
		$control->forzar_js = true;
		$this->add_control(new edit_num('NRO_DOC',17, 15));
		
		$sql = "select COD_BANCO,
						NOM_BANCO from BANCO
				order by ORDEN asc";
		$this->add_control(new drop_down_dw('COD_BANCO', $sql, 175));

		$sql = "select COD_PLAZA,
						NOM_PLAZA 
				from PLAZA
				order by NOM_PLAZA asc";
		$this->add_control(new drop_down_dw('COD_PLAZA', $sql, 175));	
		
		$this->add_control($control = new edit_precio('MONTO_DOC',17, 15));
		//$control->set_onChange("valida_monto_doc(this); ");
		
		$this->set_computed('MONTO_DOC_H', '[MONTO_DOC]');	
		$this->controls['MONTO_DOC_H']->type = 'hidden';
		$this->accumulate('MONTO_DOC_H');
		
		$this->add_control(new edit_num('NRO_AUTORIZA',20, 15));

		$this->set_first_focus('COD_TIPO_DOC_PAGO');

	}
	function fill_record(&$temp, $record) {
		$COD_TIPO_DOC_PAGO = $this->get_item($record, 'COD_TIPO_DOC_PAGO');
			
		if ($COD_TIPO_DOC_PAGO==self::K_TIPO_DOC_PAGO_EFECTIVO) {
			//$this->controls['FECHA_DOC']->type = 'text';
			$this->controls['NRO_DOC']->type = 'hidden';
			$this->controls['COD_BANCO']->enabled = false;
			$this->controls['COD_PLAZA']->enabled = false;
			$this->controls['NRO_AUTORIZA']->type = 'hidden';
		}else if ($COD_TIPO_DOC_PAGO==self::K_TIPO_DOC_PAGO_POR_DEFINIR) {
			$this->controls['FECHA_DOC']->type = 'hidden';
			$this->controls['NRO_DOC']->type = 'hidden';
			$this->controls['COD_BANCO']->enabled = false;
			$this->controls['COD_PLAZA']->enabled = false;
			$this->controls['NRO_AUTORIZA']->type = 'hidden';
		}
		else{
			//$this->controls['FECHA_DOC']->type = 'text';
			$this->controls['NRO_DOC']->type = 'text';
			$this->controls['COD_BANCO']->enabled = true;
			$this->controls['COD_PLAZA']->enabled = true;
			$this->controls['NRO_AUTORIZA']->type = 'text';
		}
			
		// llama al ancestro
		parent::fill_record($temp, $record);
	}
	function update($db) {

		$sp = 'spu_doc_nota_venta';
		for ($i = 0; $i < $this->row_count(); $i++){
				
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
			
			$COD_DOC_NOTA_VENTA = $this->get_item($i, 'COD_DOC_NOTA_VENTA');
			$COD_NOTA_VENTA = $this->get_item($i, 'COD_NOTA_VENTA');
			$COD_TIPO_DOC_PAGO = $this->get_item($i, 'COD_TIPO_DOC_PAGO');
			$FECHA_DOC = $this->get_item($i, 'FECHA_DOC');
			$NRO_DOC = $this->get_item($i, 'NRO_DOC');
			$COD_BANCO = $this->get_item($i, 'COD_BANCO');
			
			$COD_PLAZA = $this->get_item($i, 'COD_PLAZA');
			$MONTO_DOC = $this->get_item($i, 'MONTO_DOC');
			$NRO_AUTORIZA = $this->get_item($i, 'NRO_AUTORIZA');
		
			$COD_DOC_NOTA_VENTA = ($COD_DOC_NOTA_VENTA=='') ? "null" : $COD_DOC_NOTA_VENTA;	
			$FECHA_DOC = ($FECHA_DOC=='') ? "null" : "'$FECHA_DOC'";
			$NRO_DOC = ($NRO_DOC=='') ? "null" : $NRO_DOC;		
			$COD_BANCO = ($COD_BANCO=='') ? "null" : $COD_BANCO;	
			
			$COD_PLAZA = ($COD_PLAZA=='') ? "null" : $COD_PLAZA;	
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
			$param = "'$operacion', $COD_DOC_NOTA_VENTA, $COD_NOTA_VENTA, $COD_TIPO_DOC_PAGO, $FECHA_DOC, $NRO_DOC,$COD_BANCO,$COD_PLAZA,$MONTO_DOC,'$NRO_AUTORIZA'";			 			
		
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_DOC_NOTA_VENTA = $this->get_item($i, 'COD_DOC_NOTA_VENTA', 'delete');
			$param = "'DELETE', $COD_DOC_NOTA_VENTA";			
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
		}			
		return true;
	}
}

class dw_participacion extends datawindow {	
	const K_TIPO_DOC_PAGO_EFECTIVO = 1;
	const K_TIPO_DOC_PAGO_POR_DEFINIR = 8;
	
	function dw_participacion () {
		//$sql = "EXEC spdw_nv_participacion 52325";		
		$sql = "exec spdw_nv_participacion {KEY1}";
		parent::datawindow($sql, 'PARTICIPACION');
		
		$this->add_control(new static_text('PA_NOM_TIPO_ORDEN_PAGO'));
		$this->add_control(new static_num('PA_TOTAL_NETO'));
		$this->add_control(new static_text('PA_COD_PARTICIPACION'));
		$this->add_control(new static_text('PA_COD_FAPROVS'));
		$this->add_control(new static_text('PA_COD_PAGOS'));
	}
	
}

class dw_nv_orden_compra extends datawindow {
	function dw_nv_orden_compra() {
		if(K_CLIENTE == 'COMERCIAL')
			$cod_empresa_tdx = 1302;
		else if(K_CLIENTE == 'BODEGA' || K_CLIENTE == 'RENTAL')
			$cod_empresa_tdx = 4;
		else if(K_CLIENTE == 'TODOINOX')
			$cod_empresa_tdx = 7;		
		
		$sql = "select convert(varchar, OC.COD_ORDEN_COMPRA)+'|'+convert(varchar, OC.COD_ORDEN_COMPRA) COD_ORDEN_COMPRA,
						dbo.f_format_date(OC.FECHA_ORDEN_COMPRA, 1) FECHA_ORDEN_COMPRA,
						E.NOM_EMPRESA OC_NOM_EMPRESA,
						OC.COD_ESTADO_ORDEN_COMPRA,
						OC.TOTAL_NETO OC_TOTAL_NETO,
						OC.MONTO_IVA OC_MONTO_IVA,
						OC.TOTAL_CON_IVA OC_TOTAL_CON_IVA,
						CASE OC.COD_ESTADO_ORDEN_COMPRA
							WHEN 2 
							THEN 'NULA'							 
						END ANULADA,
						case OC.COD_ESTADO_ORDEN_COMPRA
							when 2 then 0
							else OC.TOTAL_NETO
						end OC_NETO_SUMA, 
						case OC.COD_ESTADO_ORDEN_COMPRA
							when 2 then 0
							else OC.MONTO_IVA
						end OC_IVA_SUMA, 
						case OC.COD_ESTADO_ORDEN_COMPRA
							when 2 then 0
							else OC.TOTAL_CON_IVA
						end OC_TOTAL_SUMA,
						dbo.f_get_oc_faprov(OC.COD_NOTA_VENTA, OC.COD_ORDEN_COMPRA, 'S') STATUS
						,CASE
							WHEN AUTORIZA_FACTURACION = 'S' AND E.COD_EMPRESA = 1302 THEN 'SI'
							WHEN (AUTORIZA_FACTURACION = 'N' OR AUTORIZA_FACTURACION IS NULL) AND E.COD_EMPRESA = 1302 THEN 'NO'
							WHEN E.COD_EMPRESA <> $cod_empresa_tdx THEN 'N/A'
						END AUTORIZA_FA_TDNX
				from ORDEN_COMPRA OC, EMPRESA E
				where OC.COD_NOTA_VENTA = {KEY1} 
				  and E.COD_EMPRESA = OC.COD_EMPRESA
				  and OC.TIPO_ORDEN_COMPRA = 'NOTA_VENTA'";
		parent::datawindow($sql, "ORDEN_COMPRA");	
		
		$this->add_control(new static_link('COD_ORDEN_COMPRA', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=nota_venta&modulo_destino=orden_compra&cod_modulo_destino=[COD_ORDEN_COMPRA]&cod_item_menu=1520&current_tab_page=4'));
		$this->add_control(new static_text('OC_NOM_EMPRESA'));
		$this->add_control(new static_num('OC_TOTAL_NETO', 0));
		$this->add_control(new static_num('OC_MONTO_IVA', 0));
		$this->add_control(new static_num('OC_TOTAL_CON_IVA', 0));
		
		$this->add_control(new edit_text('COD_ESTADO_ORDEN_COMPRA',10,10, 'hidden'));

		$this->accumulate('OC_NETO_SUMA', '', false);
		$this->accumulate('OC_IVA_SUMA', '', false);
		$this->accumulate('OC_TOTAL_SUMA', '', false);
	}
}
class dw_nv_backcharge extends datawindow {
	function dw_nv_backcharge() {
		$sql = "select convert(varchar, OC.COD_ORDEN_COMPRA)+'|'+convert(varchar, OC.COD_ORDEN_COMPRA) OCBC_COD_ORDEN_COMPRA,
						dbo.f_format_date(OC.FECHA_ORDEN_COMPRA, 1) OCBC_FECHA_ORDEN_COMPRA,
						E.NOM_EMPRESA OCBC_NOM_EMPRESA,
						OC.TOTAL_NETO OCBC_TOTAL_NETO,
						OC.MONTO_IVA OCBC_MONTO_IVA,
						OC.TOTAL_CON_IVA OCBC_TOTAL_CON_IVA,
						CASE OC.COD_ESTADO_ORDEN_COMPRA
							WHEN 2 
							THEN 'NULA'							 
						END OCBC_ANULADA,
						case OC.COD_ESTADO_ORDEN_COMPRA
							when 2 then 0
							else OC.TOTAL_NETO
						end OCBC_NETO_SUMA, 
						case OC.COD_ESTADO_ORDEN_COMPRA
							when 2 then 0
							else OC.MONTO_IVA
						end OCBC_IVA_SUMA, 
						case OC.COD_ESTADO_ORDEN_COMPRA
							when 2 then 0
							else OC.TOTAL_CON_IVA
						end OCBC_TOTAL_SUMA 
				from ORDEN_COMPRA OC, EMPRESA E
				where OC.COD_NOTA_VENTA = {KEY1} 
				  and E.COD_EMPRESA = OC.COD_EMPRESA
				  and OC.TIPO_ORDEN_COMPRA = 'BACKCHARGE'";
		parent::datawindow($sql, "OC_BACKCHARGE");	
		
		$this->add_control(new static_link('OCBC_COD_ORDEN_COMPRA', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=nota_venta&modulo_destino=orden_compra&cod_modulo_destino=[OCBC_COD_ORDEN_COMPRA]&cod_item_menu=1520&current_tab_page=3'));
		$this->add_control(new static_text('OCBC_NOM_EMPRESA'));
		$this->add_control(new static_num('OCBC_TOTAL_NETO', 0));
		$this->add_control(new static_num('OCBC_MONTO_IVA', 0));
		$this->add_control(new static_num('OCBC_TOTAL_CON_IVA', 0));
		
		$this->accumulate('OCBC_NETO_SUMA', '', false);
		$this->accumulate('OCBC_IVA_SUMA', '', false);
		$this->accumulate('OCBC_TOTAL_SUMA', '', false);
	}
}
class dw_lista_guia_despacho extends datawindow {
	const K_TIPOGD_VENTA = 1;
	const K_TIPOGD_TRASLADO = 4;
	const K_ESTADO_SII_IMPRESA = 2;
	const K_ESTADO_SII_ENVIADA = 3;
	const K_ITEM_MENU_GUIA_DESPACHO = '1525';
	
	function dw_lista_guia_despacho() {
		$sql = "select convert(varchar, NRO_GUIA_DESPACHO)+'|'+convert(varchar, COD_GUIA_DESPACHO) NRO_GUIA_DESPACHO
				from   GUIA_DESPACHO
				where  COD_DOC = {KEY1}
	  			and  COD_TIPO_GUIA_DESPACHO in (".self::K_TIPOGD_VENTA.", ".self::K_TIPOGD_TRASLADO.")
	  			and  COD_ESTADO_DOC_SII in (".self::K_ESTADO_SII_IMPRESA.", ".self::K_ESTADO_SII_ENVIADA.")";
		parent::datawindow($sql, 'GD_RELACIONADA');

		$this->add_control(new static_link('NRO_GUIA_DESPACHO', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=nota_venta&modulo_destino=guia_despacho&cod_modulo_destino=[NRO_GUIA_DESPACHO]&cod_item_menu=1525'));
	}
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);
		if ($record < $this->row_count() - 1)
			$temp->setVar($this->label_record.'.GD_SEPARADOR', '-');
	}
}
class dw_lista_guia_recepcion extends datawindow {
	const K_TIPO_GR	= 3;
	const K_ESTADO_IMPRESA = 2;
	const K_TIPO_GD = 1;
	const K_TIPO_FA = 1;
	const K_ESTADO_SII_IMPRESA = 2;
	const K_ESTADO_SII_ENVIADA = 3;
	
	function dw_lista_guia_recepcion() {
		$sql = "select convert(varchar, COD_GUIA_RECEPCION) NRO_GUIA_RECEPCION
				from   GUIA_RECEPCION
				where  COD_TIPO_GUIA_RECEPCION <>".self::K_TIPO_GR."
				AND	   (TIPO_DOC = 'GUIA_DESPACHO' and (COD_DOC IN (SELECT COD_GUIA_DESPACHO
									FROM GUIA_DESPACHO
									WHERE COD_DOC = {KEY1}
									and  COD_TIPO_GUIA_DESPACHO = ".self::K_TIPO_GD." 
									and  COD_ESTADO_DOC_SII in (".self::K_ESTADO_SII_IMPRESA.", ".self::K_ESTADO_SII_ENVIADA.")))
				OR	   (TIPO_DOC = 'FACTURA' and COD_DOC IN (SELECT	COD_FACTURA
									FROM	FACTURA
									WHERE	COD_DOC = {KEY1}
									and  COD_TIPO_FACTURA = ".self::K_TIPO_FA."
	  								and  COD_ESTADO_DOC_SII in (".self::K_ESTADO_SII_IMPRESA.", ".self::K_ESTADO_SII_ENVIADA."))))";
		parent::datawindow($sql, 'GR_RELACIONADA');

		$this->add_control(new static_link('NRO_GUIA_RECEPCION', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=nota_venta&modulo_destino=guia_recepcion&cod_modulo_destino=[NRO_GUIA_RECEPCION]&cod_item_menu=1530'));
	}
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);
		if ($record < $this->row_count() - 1)
			$temp->setVar($this->label_record.'.GR_SEPARADOR', '-');
	}
}
class dw_lista_factura extends datawindow {
	const K_TIPOGD_VENTA = 1;
	const K_ESTADO_SII_IMPRESA = 2;
	const K_ESTADO_SII_ENVIADA = 3;
	
	function dw_lista_factura() {
		$sql = "select convert(varchar, NRO_FACTURA)+'|'+convert(varchar, COD_FACTURA) NRO_FACTURA
				from   FACTURA
				where  COD_DOC = {KEY1}
	  			and  COD_TIPO_FACTURA = ".self::K_TIPOGD_VENTA."
	  			and  COD_ESTADO_DOC_SII in (".self::K_ESTADO_SII_IMPRESA.", ".self::K_ESTADO_SII_ENVIADA.")";
		
		
		
		parent::datawindow($sql, 'FA_RELACIONADA');

		$this->add_control(new static_link('NRO_FACTURA', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=nota_venta&modulo_destino=factura&cod_modulo_destino=[NRO_FACTURA]&cod_item_menu=1535'));
	}
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);
		if ($record < $this->row_count() - 1)
			$temp->setVar($this->label_record.'.FA_SEPARADOR', '-');
	}
	
	function retrieve($COD_NOTA_VENTA){
		//MH 09/08/2016 = CASO FERROVIAL solicitado por administración BIGGI
		if($COD_NOTA_VENTA == 73403 || $COD_NOTA_VENTA == 73405 || $COD_NOTA_VENTA == 73406 || $COD_NOTA_VENTA == 73407){
			
			$sql = "select convert(varchar, NRO_FACTURA)+'|'+convert(varchar, COD_FACTURA) NRO_FACTURA
					from   FACTURA
					where  COD_FACTURA in (25795, 29477)
		  			and  COD_TIPO_FACTURA = ".self::K_TIPOGD_VENTA."
		  			and  COD_ESTADO_DOC_SII in (".self::K_ESTADO_SII_IMPRESA.", ".self::K_ESTADO_SII_ENVIADA.")";	

			$this->set_sql($sql);
			
			parent::retrieve();
		}else if($COD_NOTA_VENTA == 76672){
			
			$sql = "select convert(varchar, NRO_FACTURA)+'|'+convert(varchar, COD_FACTURA) NRO_FACTURA
					from   FACTURA
					where  COD_FACTURA in (29641, 31002)
		  			and  COD_TIPO_FACTURA = ".self::K_TIPOGD_VENTA."
		  			and  COD_ESTADO_DOC_SII in (".self::K_ESTADO_SII_IMPRESA.", ".self::K_ESTADO_SII_ENVIADA.")";	

			$this->set_sql($sql);
			
			parent::retrieve();
		}else if($COD_NOTA_VENTA == 76673){
			
			$sql = "select convert(varchar, NRO_FACTURA)+'|'+convert(varchar, COD_FACTURA) NRO_FACTURA
					from   FACTURA
					where  COD_FACTURA in (29641, 31002, 29577)
		  			and  COD_TIPO_FACTURA = ".self::K_TIPOGD_VENTA."
		  			and  COD_ESTADO_DOC_SII in (".self::K_ESTADO_SII_IMPRESA.", ".self::K_ESTADO_SII_ENVIADA.")";	

			$this->set_sql($sql);
			
			parent::retrieve();
		}else
			parent::retrieve($COD_NOTA_VENTA);
	}
}
class dw_lista_nota_credito extends datawindow {
	const K_TIPO_NOTA_CREDITO  = 1;
	const K_TIPOGD_VENTA	   = 1;
	const K_ESTADO_SII_IMPRESA = 2;
	const K_ESTADO_SII_ENVIADA = 3;

	function dw_lista_nota_credito() {
		$sql = "select convert(varchar, NRO_NOTA_CREDITO)+'|'+convert(varchar, COD_NOTA_CREDITO) NRO_NOTA_CREDITO
				from   NOTA_CREDITO
				where  COD_ESTADO_DOC_SII in (".self::K_ESTADO_SII_IMPRESA.", ".self::K_ESTADO_SII_ENVIADA.")
				and	   COD_DOC IN (SELECT COD_FACTURA
									FROM FACTURA
									WHERE COD_DOC = {KEY1}
									AND	COD_TIPO_FACTURA = ".self::K_TIPOGD_VENTA."
									and	   COD_ESTADO_DOC_SII in (".self::K_ESTADO_SII_IMPRESA.", ".self::K_ESTADO_SII_ENVIADA."))";
		parent::datawindow($sql, 'NC_RELACIONADA');

		$this->add_control(new static_link('NRO_NOTA_CREDITO', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=nota_venta&modulo_destino=nota_credito&cod_modulo_destino=[NRO_NOTA_CREDITO]&cod_item_menu=1540'));
	}
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);
		if ($record < $this->row_count() - 1)
			$temp->setVar($this->label_record.'.NC_SEPARADOR', '-');
	}
}
class dw_lista_pago extends datawindow {
	function dw_lista_pago() {
		$sql = "exec spdw_nv_ingreso_pago {KEY1}";
		parent::datawindow($sql, 'PAGO_RELACIONADA');
		$this->add_control(new static_link('COD_INGRESO_PAGO', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=nota_venta&modulo_destino=ingreso_pago&cod_modulo_destino=[COD_INGRESO_PAGO]&cod_item_menu=2505'));
	}
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);
		if ($record < $this->row_count() - 1)
			$temp->setVar($this->label_record.'.PAGO_SEPARADOR', '-');
	}
}


class dw_tipo_pendiente_nota_venta extends datawindow {
	function dw_tipo_pendiente_nota_venta() {
	
		$sql = "select COD_TIPO_PENDIENTE_NOTA_VENTA
				  ,COD_NOTA_VENTA		 
				  ,COD_TIPO_PENDIENTE
				  ,AUTORIZA
				  ,convert(varchar(20), FECHA_AUTORIZA, 103) +'  '+ convert(varchar(20), FECHA_AUTORIZA, 8) FECHA_AUTORIZA
				  ,COD_USUARIO
				  ,MOTIVO MOTIVO_AUTORIZA
				from TIPO_PENDIENTE_NOTA_VENTA
				where COD_NOTA_VENTA = {KEY1}
				order by COD_TIPO_PENDIENTE asc";
		parent::datawindow($sql, "TIPO_PENDIENTE_NOTA_VENTA");	
		
		$this->add_control(new edit_check_box('AUTORIZA','S','N'));
		$sql = "select COD_TIPO_PENDIENTE, 
					NOM_TIPO_PENDIENTE
				from TIPO_PENDIENTE";
		$this->add_control(new drop_down_dw('COD_TIPO_PENDIENTE', $sql, 120));		
		$this->set_entrable('COD_TIPO_PENDIENTE', false);
		$sql = "select COD_USUARIO,
					NOM_USUARIO
				from USUARIO";
		$this->add_control(new drop_down_dw('COD_USUARIO', $sql, 120));		
		$this->set_entrable('COD_USUARIO', false);		
		$this->add_control(new edit_text_upper('MOTIVO_AUTORIZA', 76, 100));
	}
	
	function update($db) {
		for ($i = 0; $i < $this->row_count(); $i++){
				
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
			
				
			$AUTORIZA = $this->get_item($i, 'AUTORIZA');
			if ($AUTORIZA == 'S'){
				$COD_NOTA_VENTA = $this->get_item($i, 'COD_NOTA_VENTA');
				$COD_USUARIO = session::get("COD_USUARIO");
				$MOTIVO_AUTORIZA = $this->get_item($i, 'MOTIVO_AUTORIZA');
				$COD_TIPO_PENDIENTE_NOTA_VENTA = $this->get_item($i, 'COD_TIPO_PENDIENTE_NOTA_VENTA');
				if (!$db->EXECUTE_SP('spu_tipo_pendiente_nota_venta', "'UPDATE', $COD_NOTA_VENTA, $COD_TIPO_PENDIENTE_NOTA_VENTA, $COD_USUARIO, '$MOTIVO_AUTORIZA'"))
					return false;
			}
		}			
		return true;
	}
}
class dw_nota_venta extends dw_help_empresa {
	function dw_nota_venta($sql) {
		parent::dw_help_empresa($sql);
	}
}
class wi_nota_venta extends w_cot_nv {
	const K_ESTADO_EMITIDA 			= 1;	
	const K_ESTADO_CERRADA			= 2;
	const K_ESTADO_ANULADA			= 3;
	const K_ESTADO_CONFIRMADA		= 4;
	const K_PARAM_NOM_EMPRESA 		= 6;
	const K_PARAM_DIR_EMPRESA 		= 10;
	const K_PARAM_TEL_EMPRESA 		= 11;
	const K_PARAM_FAX_EMPRESA 		= 12;
	const K_PARAM_MAIL_EMPRESA 		= 13;
	const K_PARAM_CIUDAD_EMPRESA	= 14;
	const K_PARAM_PAIS_EMPRESA 		= 15; 
	const K_PARAM_GTE_VTA 			= 16;
	const K_PARAM_RUT_EMPRESA 		= 20;
	const K_PARAM_SITIO_WEB_EMPRESA	= 25;
	const K_PARAM_RANGO_DOC_NOTA_VENTA = 27;
	const K_AUTORIZA_CIERRE 		 = '991005';
	const K_CAMBIA_DSCTO_CORPORATIVO = '991010';
	const K_MODIFICA_NOTA_VENTA		 = '991020';
	const K_AUTORIZA_ANULACION		 = '991025';
	const K_AUTORIZA_MOD_VENDEDOR2	 = '991055';
	var $porc_desc_permitido = 0;
	var $desde_wo_inf_backcharge = false;
	var $desde_wo_inf_por_despachar = false;
	
	function wi_nota_venta($cod_item_menu) {
		if (session::is_set('DESDE_wo_inf_por_despachar')) {
			session::un_set('DESDE_wo_inf_por_despachar');
			$this->desde_wo_inf_por_despachar = true;
		}
		parent::w_cot_nv('nota_venta', $cod_item_menu);
		// valida si el usuario puede autorizar cierre y modificar la fecha de cierre
		if ($this->tiene_privilegio_opcion(self::K_AUTORIZA_CIERRE))
			$autoriza_cierre = 'S';
		else
			$autoriza_cierre = 'N';
		// valida si el usuario puede modificar los desctos corporativos
		if ($this->tiene_privilegio_opcion(self::K_CAMBIA_DSCTO_CORPORATIVO))
			$cambia_dscto_corp = 'S';
		else
			$cambia_dscto_corp = 'N';
		// valida si el usuario puede modificar Nota Venta cuando esta confirmada
		if ($this->tiene_privilegio_opcion(self::K_MODIFICA_NOTA_VENTA))
			$modifica_nv = 'S';
		else
			$modifica_nv = 'N';
		//valida si el usuario puede modificar Nota Venta cuando esta confirmada
		if ($this->tiene_privilegio_opcion(self::K_AUTORIZA_ANULACION))
			$autoriza_anulacion = 'S';
		else
			$autoriza_anulacion = 'N';

		// Obtiene el perfil del usuario, si es administrador PREORDEN son siempre visible 
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select COD_PERFIL
					  ,PORC_DESCUENTO_PERMITIDO
				from USUARIO
				where COD_USUARIO = $this->cod_usuario";
		$result = $db->build_results($sql);
		$cod_perfil = $result[0]['COD_PERFIL'];
		$this->porc_desc_permitido = $result[0]['PORC_DESCUENTO_PERMITIDO'];
			
		$sql = "select COD_NOTA_VENTA,
                    COD_NOTA_VENTA COD_NOTA_VENTA_H,
					$this->porc_desc_permitido PORC_DESC_PERMITIDO, 
					convert(varchar, FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA,
					NV.COD_USUARIO,
					U.NOM_USUARIO,
					NRO_ORDEN_COMPRA,
					--CIERRE_SINPART,
					CENTRO_COSTO_CLIENTE,
					COD_COTIZACION,
					NV.COD_ESTADO_NOTA_VENTA,
					NV.COD_ESTADO_NOTA_VENTA COD_ESTADO_NOTA_VENTA_H,
					ENV.NOM_ESTADO_NOTA_VENTA, 
					COD_MONEDA,
					VALOR_TIPO_CAMBIO,  
					COD_USUARIO_VENDEDOR1, 
					COD_USUARIO_VENDEDOR1 COD_VENDEDOR1_H,
					PORC_VENDEDOR1, 
					COD_USUARIO_VENDEDOR2, 
					PORC_VENDEDOR2,
					'none' DISPLAY_DESCARGA,
					'' ELIMINA_DOC,
					NV.COD_CUENTA_CORRIENTE, 
					CC.NOM_CUENTA_CORRIENTE, 
					CC.NRO_CUENTA_CORRIENTE, 
					REFERENCIA,
					CREADA_EN_SV, 
					NV.COD_EMPRESA,
					E.ALIAS,
					E.RUT,
					'none' LL_LLAMADO,
					Convert(varchar, NV.FECHA_ORDEN_COMPRA_CLIENTE, 103) FECHA_ORDEN_COMPRA_CLIENTE,
					E.DIG_VERIF,
					E.NOM_EMPRESA,
					E.GIRO, 
					COD_SUCURSAL_DESPACHO, 
					dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL_DESPACHO, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_DESPACHO,
					COD_SUCURSAL_FACTURA, 
					dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL_FACTURA, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA,
					COD_PERSONA,
					dbo.f_emp_get_mail_cargo_persona(COD_PERSONA,  '[EMAIL] - [NOM_CARGO]') MAIL_CARGO_PERSONA,
					Convert(varchar, FECHA_ENTREGA, 103) FECHA_ENTREGA, 
					OBS_DESPACHO,
					OBS,
					Convert(varchar, FECHA_PLAZO_CIERRE, 103) FECHA_PLAZO_CIERRE, 
					-- historial de modificacion fecha_plazo_cierre
					(select count(*) from LOG_CAMBIO LG, DETALLE_CAMBIO DC where LG.NOM_TABLA = 'NOTA_VENTA' and
					LG.KEY_TABLA = CAST(NV.COD_NOTA_VENTA AS VARCHAR) and LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO and 
					DC.NOM_CAMPO = 'FECHA_PLAZO_CIERRE') CANT_CAMBIO_FECHA_PLAZO_CIERRE,
					SUBTOTAL SUM_TOTAL,
					SUBTOTAL SUM_TOTAL_L, 
					PORC_DSCTO1,
					PORC_DSCTO1 PORC_DSCTO1_L,
					MONTO_DSCTO1,
					MONTO_DSCTO1 MONTO_DSCTO1_L,
					PORC_DSCTO2, 
					PORC_DSCTO2 PORC_DSCTO2_L, 
					MONTO_DSCTO2,
					MONTO_DSCTO2 MONTO_DSCTO2_L, 
					null D_COD_NOTA_ENCRIPT,
					TOTAL_NETO,
					TOTAL_NETO TOTAL_NETO_L,
					TOTAL_NETO STATIC_TOTAL_NETO,
					TOTAL_NETO STATIC_TOTAL_NETO2,
					PORC_IVA,
					PORC_IVA PORC_IVA_L, 
					MONTO_IVA,
					MONTO_IVA MONTO_IVA_L, 
					TOTAL_CON_IVA,
					TOTAL_CON_IVA TOTAL_CON_IVA_L,   
					TOTAL_CON_IVA STATIC_TOTAL_CON_IVA,
					TOTAL_CON_IVA STATIC_TOTAL_CON_IVA2,
					dbo.f_nv_total_pago(NV.COD_NOTA_VENTA) TOTAL_PAGO,
					TOTAL_CON_IVA - dbo.f_nv_total_pago(NV.COD_NOTA_VENTA) TOTAL_POR_PAGAR,
					COD_FORMA_PAGO, 
					NOM_FORMA_PAGO_OTRO,
					CANTIDAD_DOC_FORMA_PAGO_OTRO,
					INGRESO_USUARIO_DSCTO1,  
					INGRESO_USUARIO_DSCTO2,
					V1.PORC_DESCUENTO_PERMITIDO PORC_DSCTO_MAX,
					datediff(d, FECHA_NOTA_VENTA, getdate()) EMITIDA_HACE,
					--resultados
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'VENTA_NETA') VENTA_NETA,
					PORC_DSCTO_CORPORATIVO,
					PORC_DSCTO_CORPORATIVO PORC_DSCTO_CORPORATIVO_STATIC, 
					PORC_DSCTO_CORPORATIVO PORC_DSCTO_CORPORATIVO_H,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO') MONTO_DSCTO_CORPORATIVO,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO') STATIC_MONTO_DSCTO_CORPORATIVO,
					dbo.f_get_parametro_porc('AA', isnull(NV.FECHA_NOTA_VENTA, getdate()))PORC_AA,
					dbo.f_get_parametro_porc('GF', isnull(NV.FECHA_NOTA_VENTA, getdate()))PORC_GF,
					dbo.f_get_parametro_porc('GV', isnull(NV.FECHA_NOTA_VENTA, getdate()))PORC_GV,
					dbo.f_get_parametro_porc('ADM', isnull(NV.FECHA_NOTA_VENTA, getdate()))PORC_ADM,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DIRECTORIO') MONTO_DIRECTORIO,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_GASTO_FIJO') MONTO_GASTO_FIJO,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'SUM_OC_TOTAL') SUM_OC_TOTAL,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'SUM_OC_TOTAL_NETA') SUM_OC_TOTAL_NETA,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'RESULTADO') RESULTADO,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'RESULTADO') STATIC_RESULTADO,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'SUMA_NC') SUMA_NC,			
					case NV.TOTAL_NETO when 0 then
						0
					else
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'PORC_RESULTADO')
					end PORC_RESULTADO,
					case NV.TOTAL_NETO when 0 then
						0
					else
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'PORC_RESULTADO')
					end STATIC_PORC_RESULTADO,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_V1')COMISION_V1,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_V2')COMISION_V2,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_GV')COMISION_GV,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_ADM')COMISION_ADM,					
					(select NOM_USUARIO from USUARIO USU where USU.COD_USUARIO = NV.COD_USUARIO_VENDEDOR1) VENDEDOR1,
					(select NOM_USUARIO from USUARIO USU where USU.COD_USUARIO = NV.COD_USUARIO_VENDEDOR2) VENDEDOR2,
					dbo.f_get_parametro(".self::K_PARAM_GTE_VTA.") GTE_VTA,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'REMANENTE') REMANENTE,
					-- no modificable en tab de resultados la comision del vendedor
					PORC_VENDEDOR1 PORC_VENDEDOR1_R,  
					PORC_VENDEDOR2 PORC_VENDEDOR2_R,
					dbo.f_get_parametro(".self::K_PARAM_RANGO_DOC_NOTA_VENTA.") RANGO_DOC_NOTA_VENTA,
					(select isnull(sum(MONTO_DOC),0) from DOC_NOTA_VENTA DNV where DNV.COD_NOTA_VENTA = NV.COD_NOTA_VENTA) TOTAL_MONTO_DOC,
					'".$cambia_dscto_corp."' CAMBIA_DSCTO_CORPORATIVO,
					-- despachado
					case NV.SUBTOTAL when 0 then
						0
					else
						Round((select isnull(sum((CANTIDAD - dbo.f_nv_cant_por_despachar(COD_ITEM_NOTA_VENTA, default)) * PRECIO), 0) 	from ITEM_NOTA_VENTA IT where IT.COD_NOTA_VENTA = NV.COD_NOTA_VENTA) * 100 / NV.SUBTOTAL, 1)
					end PORC_GD,
					-- facturado
					dbo.f_nv_porc_facturado(NV.COD_NOTA_VENTA) PORC_FACTURA,
					-- pagado
					Round((dbo.f_nv_total_pago(NV.COD_NOTA_VENTA) / TOTAL_CON_IVA) * 100, 1) PORC_PAGOS,
					-- historial de modificacion descto. corporativo
					(select count(*)
					from LOG_CAMBIO LG, DETALLE_CAMBIO DC
					where LG.NOM_TABLA = 'NOTA_VENTA' and LG.KEY_TABLA = CAST(NV.COD_NOTA_VENTA AS VARCHAR) and
						LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO and
						DC.NOM_CAMPO = 'PORC_DSCTO_CORPORATIVO') CANT_CAMBIO_PORC_DESCTO_CORP,
					-- datos cierre NV
					convert(varchar(20), FECHA_CIERRE, 103) +'  '+ convert(varchar(20), FECHA_CIERRE, 8) FECHA_CIERRE,
					COD_USUARIO_CIERRE,			
					case NV.COD_ESTADO_NOTA_VENTA
						when ".self::K_ESTADO_CERRADA." then ''
						else 'none'
					end TABLE_CIERRE_DISPLAY,
					case NV.COD_ESTADO_NOTA_VENTA
						when ".self::K_ESTADO_CERRADA." then 'none'
						else ''
					end BOTON_CIERRE_DISPLAY,
					'' TABLE_PENDIENTE_DISPLAY,
					'N' CIERRE_H,
					'N' CIERRE_SIN_P_H,
					''  MOTIVO_CIERRE_SIN_PART_H,
					'none' BOTON_CIERRE_SIN_P,
					'".$autoriza_cierre."'	AUTORIZA_CIERRE,
					'".$autoriza_cierre."'	VALIDA_MOTIVO_CIERRE_H,
					'".$modifica_nv."' 		MODIFICA_NV,
					'".$autoriza_anulacion."' AUTORIZA_ANULACION,

					case ".$this->cod_usuario."
						when NV.COD_USUARIO_VENDEDOR1 then 'S'
						when NV.COD_USUARIO_VENDEDOR2 then 'S'
						else 'N'
					end ES_VENDEDOR,
					
					-- datos anulación
					convert(varchar(20), NV.FECHA_ANULA, 103) +'  '+ convert(varchar(20), NV.FECHA_ANULA, 8) FECHA_ANULA,
					MOTIVO_ANULA,
					COD_USUARIO_ANULA,
					case NV.COD_ESTADO_NOTA_VENTA
						when ".self::K_ESTADO_ANULADA." then ''
						else 'none'
					end TR_DISPLAY_ANULADA,
					COD_USUARIO_CONFIRMA COD_USUARIO_CONFIRMA_H,
					FECHA_CONFIRMA,
					case NV.COD_ESTADO_NOTA_VENTA
						when ".self::K_ESTADO_CERRADA." then 'CERRADA'
						when ".self::K_ESTADO_ANULADA." then 'ANULADA'
						when ".self::K_ESTADO_CONFIRMADA." then 'CONFIRMADA' 
						else ''
					end TITULO_ESTADO_NOTA_VENTA
					,case NV.COD_ESTADO_NOTA_VENTA
						when ".self::K_ESTADO_EMITIDA." then '' 
						else case $cod_perfil
								when 1 then ''
								else 'none'
							 end
					end DISPLAY_PREORDEN
					,case 
						when (INGRESO_USUARIO_DSCTO1='M' and MONTO_DSCTO1>0) then 'Descuento ingresado como monto'
						else ''
					end ETIQUETA_DESCT1
					,case 
						when (INGRESO_USUARIO_DSCTO2='M' and MONTO_DSCTO2>0)  then 'Descuento ingresado como monto'
						else ''
					end ETIQUETA_DESCT2
					,null COD_ESTADO_COTIZACION
					,null BTN_CONSULTA_STK
					,(select top 1 convert( varchar(100),FECHA_CAMBIO,103) from LOG_CAMBIO where NOM_TABLA = 'ENVIA_MAIL_WEB_PAY' and KEY_TABLA = COD_NOTA_VENTA order by COD_LOG_CAMBIO desc) FECHA_CAMBIO
					,(select top 1 convert( varchar(100),FECHA_CAMBIO,108) from LOG_CAMBIO where NOM_TABLA = 'ENVIA_MAIL_WEB_PAY' and KEY_TABLA = COD_NOTA_VENTA order by COD_LOG_CAMBIO desc) FECHA_CAMBIO_HORA
					,(select top 1 UL.NOM_USUARIO from LOG_CAMBIO LG, USUARIO UL where NOM_TABLA = 'ENVIA_MAIL_WEB_PAY' and KEY_TABLA = COD_NOTA_VENTA and LG.COD_USUARIO = UL.COD_USUARIO order by COD_LOG_CAMBIO desc) NOM_USUARIO_UL
					,'N' RULES_MODIFIED
					,'S' LOAD_RECORD_STATE
					,dbo.f_get_no_suma_nv_informe(COD_NOTA_VENTA, 'CHECKED') ES_NO_SUMA_NV_INFORME
					,dbo.f_get_no_suma_nv_informe(COD_NOTA_VENTA, 'MOTIVO') NO_SUMA_NV_INFORME_MOTIVO
					,CASE
						WHEN dbo.f_get_no_suma_nv_informe(COD_NOTA_VENTA, 'CHECKED') = 'S' THEN ''
						ELSE 'none'
					END DISPLAY_MSG_NO_INF
					,CASE
						WHEN dbo.f_get_no_suma_nv_informe(COD_NOTA_VENTA, 'CHECKED') = 'S' THEN 'NV DEBE SUMAR EN INFORMES'
						ELSE 'NV NO DEBE SUMAR EN INFORMES'
					END TXT_BTN_NV_INFORME
					,'' DISABLED_BTN
					,NULL RESULT_NV_NC
					,'' BTN_LISTA_NC
					,ISNULL(REBAJA, 0) REBAJA
					,AUTORIZA_PROCESAR_VENTA
					,dbo.f_get_last_mod_aut_venta(COD_NOTA_VENTA) LAST_USUARIO_MOD
					,NO_TIENE_OC
                    , 'none' D_AUTORIZADO
                    , 'none' D_NO_AUTORIZADO
                    ,NV.COD_INGRESO_PAGO
				from NOTA_VENTA NV, USUARIO U, EMPRESA E, ESTADO_NOTA_VENTA ENV, CUENTA_CORRIENTE CC, USUARIO V1
				where COD_NOTA_VENTA = {KEY1} and
					U.COD_USUARIO = NV.COD_USUARIO and
					E.COD_EMPRESA = NV.COD_EMPRESA and
					ENV.COD_ESTADO_NOTA_VENTA = NV.COD_ESTADO_NOTA_VENTA and
					CC.COD_CUENTA_CORRIENTE = NV.COD_CUENTA_CORRIENTE
					and V1.COD_USUARIO = NV.COD_USUARIO_VENDEDOR1";
					
		////////////////////
		// tab NV
		// DATAWINDOWS NOTA_VENTA
	
		$this->dws['dw_nota_venta'] = new dw_nota_venta($sql);
		
		$this->dws['dw_nota_venta']->add_control(new edit_nro_doc('COD_NOTA_VENTA','NOTA_VENTA'));
		
		$this->dws['dw_nota_venta']->add_control($control = new edit_num('REBAJA',7, 10));
		$control->set_onChange("calcula_rebaja(this);");
		$this->dws['dw_nota_venta']->add_control($control = new edit_text_upper('NRO_ORDEN_COMPRA', 18, 18));
		$control->set_onChange("change_orden_compra(this);");
		$this->dws['dw_nota_venta']->add_control(new edit_date('FECHA_ORDEN_COMPRA_CLIENTE'));
		$this->dws['dw_nota_venta']->add_control(new edit_check_box('CREADA_EN_SV','S','N'));
		$this->dws['dw_nota_venta']->add_control(new edit_check_box('AUTORIZA_PROCESAR_VENTA','S','N', 'Autoriza Procesar Despacho'));
		$this->dws['dw_nota_venta']->add_control(new edit_text_hidden('ES_NO_SUMA_NV_INFORME'));
		$this->dws['dw_nota_venta']->add_control(new edit_text_hidden('COD_NOTA_VENTA_H'));
		$this->dws['dw_nota_venta']->add_control(new edit_text_hidden('RESULT_NV_NC'));
		$this->dws['dw_nota_venta']->add_control(new edit_text_multiline('NO_SUMA_NV_INFORME_MOTIVO',95,3));
		
		$this->dws['dw_nota_venta']->add_control($control = new edit_check_box('NO_TIENE_OC','S','N','Sin OC'));
		$control->set_onChange("f_valida_oc();");
		
		$this->dws['dw_nota_venta']->add_control(new edit_text_upper('CENTRO_COSTO_CLIENTE', 25, 30));	
		$this->dws['dw_nota_venta']->add_control(new edit_nro_doc('COD_COTIZACION', 'COTIZACION'));	
		
		$this->add_controls_cot_nv();
		$this->dws['dw_nota_venta']->set_computed('STATIC_TOTAL_NETO', '[TOTAL_NETO]');	
		$this->dws['dw_nota_venta']->set_computed('STATIC_TOTAL_NETO2', '[TOTAL_NETO]');	
		$this->dws['dw_nota_venta']->set_computed('STATIC_TOTAL_CON_IVA', '[TOTAL_CON_IVA]');	
		$this->dws['dw_nota_venta']->set_computed('STATIC_TOTAL_CON_IVA2', '[TOTAL_CON_IVA]');	
		$this->dws['dw_nota_venta']->add_control(new static_num('TOTAL_PAGO'));	
		$this->dws['dw_nota_venta']->set_computed('TOTAL_POR_PAGAR', '[TOTAL_CON_IVA] - [TOTAL_PAGO]');	
		$this->dws['dw_nota_venta']->add_control(new edit_text('COD_ESTADO_NOTA_VENTA',10,10, 'hidden'));
		$this->dws['dw_nota_venta']->add_control(new edit_text('COD_ESTADO_NOTA_VENTA_H',10,10, 'hidden'));
		$this->dws['dw_nota_venta']->add_control(new static_text('NOM_ESTADO_NOTA_VENTA'));
		$this->dws['dw_nota_venta']->add_control(new static_num('VALOR_TIPO_CAMBIO', 2));
		$this->dws['dw_nota_venta']->add_control(new edit_text('COD_ESTADO_COTIZACION',10,10, 'hidden'));
		
		$this->dws['dw_nota_venta']->add_control(new edit_text('COD_CUENTA_CORRIENTE', 20, 20, 'hidden'));		
		$this->dws['dw_nota_venta']->add_control(new static_text('NOM_CUENTA_CORRIENTE'));		
		$this->dws['dw_nota_venta']->add_control(new static_text('NRO_CUENTA_CORRIENTE'));
		$this->dws['dw_nota_venta']->add_control(new edit_text_hidden('RULES_MODIFIED'));
		$this->dws['dw_nota_venta']->add_control(new edit_text_hidden('LOAD_RECORD_STATE'));
		
		$this->dws['dw_nota_venta']->controls['REFERENCIA']->size = 95;
		
		$js1 = $this->dws['dw_nota_venta']->controls['PORC_DSCTO1']->get_onChange();
		$js2 = $this->dws['dw_nota_venta']->controls['PORC_DSCTO2']->get_onChange();
		$js3 = $this->dws['dw_nota_venta']->controls['MONTO_DSCTO1']->get_onChange();
		$js4 = $this->dws['dw_nota_venta']->controls['MONTO_DSCTO2']->get_onChange();
		$this->dws['dw_nota_venta']->controls['PORC_DSCTO1']->set_onChange($js1." cambia_regla();");
		$this->dws['dw_nota_venta']->controls['PORC_DSCTO2']->set_onChange($js2." cambia_regla();");
		$this->dws['dw_nota_venta']->controls['MONTO_DSCTO1']->set_onChange($js3." cambia_regla();");
		$this->dws['dw_nota_venta']->controls['MONTO_DSCTO2']->set_onChange($js4." cambia_regla();");
		
		// datos anulación
		$sql = "select 	COD_USUARIO
						,NOM_USUARIO
				from USUARIO";				
		$this->dws['dw_nota_venta']->add_control(new drop_down_dw('COD_USUARIO_ANULA',$sql,150));	
		$this->dws['dw_nota_venta']->set_entrable('COD_USUARIO_ANULA', false);	
		
		$this->dws['dw_nota_venta']->add_control(new static_num('SUM_TOTAL_L'));
		$this->dws['dw_nota_venta']->add_control(new static_num('TOTAL_NETO_L'));
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_DSCTO1_L', 1));
		$this->dws['dw_nota_venta']->add_control(new static_num('MONTO_DSCTO1_L'));
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_IVA_L', 1));
		$this->dws['dw_nota_venta']->add_control(new static_num('MONTO_IVA_L'));
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_DSCTO2_L', 1));
		$this->dws['dw_nota_venta']->add_control(new static_num('MONTO_DSCTO2_L'));
		$this->dws['dw_nota_venta']->add_control(new static_num('TOTAL_CON_IVA_L'));
		
		$this->dws['dw_nota_venta']->add_control(new edit_date('FECHA_ENTREGA'));
		$this->dws['dw_nota_venta']->add_control(new edit_text_multiline('OBS_DESPACHO',54,3));
		$this->dws['dw_nota_venta']->add_control(new edit_text_multiline('OBS',54,3));
		$this->dws['dw_nota_venta']->add_control(new edit_text('PORC_DSCTO_MAX',10, 10, 'hidden'));
		$this->dws['dw_nota_venta']->add_control(new edit_text_hidden('PORC_DESC_PERMITIDO'));
		
		$sql_forma_pago	= "	select COD_FORMA_PAGO
								,NOM_FORMA_PAGO
								,CANTIDAD_DOC
							from FORMA_PAGO
                           -- WHERE ES_VIGENTE = 'S'
						   	order by ORDEN";
		$this->dws['dw_nota_venta']->add_control($control = new drop_down_dw('COD_FORMA_PAGO', $sql_forma_pago, 180));
		$control->set_onChange("change_forma_pago('', this);");
		$this->dws['dw_nota_venta']->add_control(new edit_text_upper('NOM_FORMA_PAGO_OTRO',160, 100));
		
		$this->dws['dw_nota_venta']->add_control($control = new edit_num_doc_forma_pago('CANTIDAD_DOC_FORMA_PAGO_OTRO'));
		$control->set_onChange("change_forma_pago('OTRO', this);");
		
		$this->dws['dw_nota_venta']->add_control(new edit_text('COD_USUARIO_CONFIRMA_H',10, 10, 'hidden'));
		$this->dws['dw_nota_venta']->add_control($control = new button('BTN_CONSULTA_STK','Consulta stock'));
		$control->set_onClick("consulta_stock();");

		// asigna los mandatorys
		$this->dws['dw_nota_venta']->set_mandatory('COD_ESTADO_NOTA_VENTA', 'Estado');
		$this->dws['dw_nota_venta']->set_mandatory('REFERENCIA', 'Referencia');
		$this->dws['dw_nota_venta']->set_mandatory('COD_EMPRESA', 'Empresa');
		$this->dws['dw_nota_venta']->set_mandatory('COD_SUCURSAL_DESPACHO', 'Sucursal de Despacho');
		$this->dws['dw_nota_venta']->set_mandatory('COD_SUCURSAL_FACTURA', 'Sucursal de Factura');
		$this->dws['dw_nota_venta']->set_mandatory('COD_PERSONA', 'Persona');
		$this->dws['dw_nota_venta']->set_mandatory('FECHA_ENTREGA', 'Fecha de Entrega');
		$this->dws['dw_nota_venta']->set_mandatory('COD_FORMA_PAGO', 'Forma de Pago');

		$this->dws['dw_lista_guia_despacho'] = new dw_lista_guia_despacho();
		$this->dws['dw_lista_guia_recepcion'] = new dw_lista_guia_recepcion();
		$this->dws['dw_lista_factura'] = new dw_lista_factura();
		$this->dws['dw_lista_nota_credito'] = new dw_lista_nota_credito();
		$this->dws['dw_lista_pago'] = new dw_lista_pago();
		// dw_participacion
		$this->dws['dw_participacion'] = new dw_participacion();
		$this->dws['dw_llamado'] = new dw_llamado();
		
		////////////////////
		// tab items
		$this->dws['dw_item_nota_venta'] = new dw_item_nota_venta();
		$this->dws['dw_llamado'] = new dw_llamado();
					
		//tab compras
		$this->dws['dw_pre_orden_compra'] = new dw_pre_orden_compra();		
		
		//ordenes de compra
		$this->dws['dw_orden_compra'] = new dw_nv_orden_compra();
		$this->dws['dw_nv_backcharge'] = new dw_nv_backcharge();
		
		$this->dws['dw_item_resumen_nv'] = new dw_item_resumen_nv();
		
		//resultados	
		$sql = "select ITEM ITEM_R,
		COD_PRODUCTO COD_PRODUCTO_R,
		NOM_PRODUCTO NOM_PRODUCTO_R,
		CANTIDAD CANTIDAD_R,
		-- -- precio
		((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) PRECIO_CON_DESCTO_R,
		-- venta total
		(((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) * INV.CANTIDAD) VENTA_TOTAL_R,

		-- costo unitario 
		dbo.f_nv_costo_unitario (COD_ITEM_NOTA_VENTA, COD_PRODUCTO) COSTO_UNITARIO_R,
		-- costo total
		(dbo.f_nv_costo_unitario (COD_ITEM_NOTA_VENTA, COD_PRODUCTO)* CANTIDAD) COSTO_TOTAL_R,
							

		-- otros gastos
		(((((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) * INV.CANTIDAD) / NV.TOTAL_NETO) * dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_GASTO_FIJO')+
		((((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) * INV.CANTIDAD)/ NV.TOTAL_NETO) * dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO')) OTROS_GASTOS_R,					


		-- monto resultado = venta total - costo total - otros gastos (monto descto corporativo + monto GF)
		(((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) * INV.CANTIDAD)- 
		(dbo.f_nv_costo_unitario (COD_ITEM_NOTA_VENTA, COD_PRODUCTO)* CANTIDAD)- 
		-- otros gastos
		(((((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) * INV.CANTIDAD) / NV.TOTAL_NETO) * dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_GASTO_FIJO')+
		((((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) * INV.CANTIDAD)/ NV.TOTAL_NETO) * dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO')) RESULTADO_R,

		
		-- porc resultado = monto resultado / (venta total - monto descto corporativo)
		--(((((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) * INV.CANTIDAD)- (dbo.f_nv_costo_unitario (COD_ITEM_NOTA_VENTA, COD_PRODUCTO)* CANTIDAD)) / (((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) * INV.CANTIDAD))*100 PORC_RESULTADO_R,
		(((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) * INV.CANTIDAD)- 
		(dbo.f_nv_costo_unitario (COD_ITEM_NOTA_VENTA, COD_PRODUCTO)* CANTIDAD)- 
		-- otros gastos
		(((((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) * INV.CANTIDAD) / NV.TOTAL_NETO) * dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_GASTO_FIJO')+
		((((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) * INV.CANTIDAD)/ NV.TOTAL_NETO) * dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO'))/
		(dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'VENTA_NETA') -
		dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO')) / 100 PORC_RESULTADO_R

	from ITEM_NOTA_VENTA INV, NOTA_VENTA NV
	where INV.COD_NOTA_VENTA = {KEY1} and
	  NV.COD_NOTA_VENTA = INV.COD_NOTA_VENTA 
	order by INV.ORDEN asc";
					
		$this->dws['dw_item_nota_venta_resultado'] = new datawindow($sql, "ITEM_NOTA_VENTA_RESULTADO");	
		$this->dws['dw_item_nota_venta_resultado']->add_control(new static_text('ITEM_R'));
		$this->dws['dw_item_nota_venta_resultado']->add_control(new static_text('COD_PRODUCTO_R'));
		$this->dws['dw_item_nota_venta_resultado']->add_control(new static_text('NOM_PRODUCTO_R'));
		$this->dws['dw_item_nota_venta_resultado']->add_control(new static_num('CANTIDAD_R', 1));
		$this->dws['dw_item_nota_venta_resultado']->add_control(new static_num('PRECIO_CON_DESCTO_R', 0));
		$this->dws['dw_item_nota_venta_resultado']->add_control(new static_num('VENTA_TOTAL_R', 0));
		$this->dws['dw_item_nota_venta_resultado']->add_control(new static_num('COSTO_UNITARIO_R', 0));
		$this->dws['dw_item_nota_venta_resultado']->add_control(new static_num('COSTO_TOTAL_R', 0));
		$this->dws['dw_item_nota_venta_resultado']->add_control(new static_num('OTROS_GASTOS_R', 0));
		$this->dws['dw_item_nota_venta_resultado']->add_control(new static_num('RESULTADO_R', 0));
		$this->dws['dw_item_nota_venta_resultado']->add_control(new static_num('PORC_RESULTADO_R', 1));
		
		if ($cambia_dscto_corp == 'S'){	
			$this->dws['dw_nota_venta']->add_control($control = new edit_porcentaje('PORC_DSCTO_CORPORATIVO'));
			$control->set_onChange("actualiza_dscto_corp_hidden(this);");	
		} 
		else{
			$this->dws['dw_nota_venta']->add_control(new static_num('PORC_DSCTO_CORPORATIVO', 1));
		}
		$this->dws['dw_nota_venta']->add_control(new edit_text('PORC_DSCTO_CORPORATIVO_H',10, 10, 'hidden'));
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_DSCTO_CORPORATIVO_STATIC', 1));
		
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_AA', 1));
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_GF', 1));
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_GV', 1));
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_ADM', 1));
		$this->dws['dw_nota_venta']->add_control(new static_num('SUM_OC_TOTAL', 0));
		$this->dws['dw_nota_venta']->add_control(new static_num('SUM_OC_TOTAL_NETA'));
		$this->dws['dw_nota_venta']->add_control(new static_num('SUMA_NC', 0));
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_VENDEDOR1_R', 2));
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_VENDEDOR2_R', 2));
		$this->dws['dw_nota_venta']->add_control(new static_text('VENDEDOR1'));
		$this->dws['dw_nota_venta']->add_control(new static_text('VENDEDOR2'));
		$this->dws['dw_nota_venta']->add_control(new static_text('GTE_VTA'));
		$this->dws['dw_nota_venta']->add_control(new static_num('MONTO_DSCTO_CORPORATIVO'));	
		
		$this->dws['dw_nota_venta']->set_computed('STATIC_MONTO_DSCTO_CORPORATIVO', '(([TOTAL_NETO] - [SUMA_NC]) * [PORC_DSCTO_CORPORATIVO])/ 100');		
		$this->dws['dw_nota_venta']->set_computed('VENTA_NETA_FINAL', '[TOTAL_NETO] - [SUMA_NC] - [STATIC_MONTO_DSCTO_CORPORATIVO]');	
		$this->dws['dw_nota_venta']->set_computed('MONTO_DIRECTORIO', '[RESULTADO] * [PORC_AA] / 100');
		$this->dws['dw_nota_venta']->set_computed('MONTO_GASTO_FIJO', '[VENTA_NETA_FINAL] * [PORC_GF] / 100');
		$this->dws['dw_nota_venta']->set_computed('RESULTADO', '[TOTAL_NETO] - [SUM_OC_TOTAL_NETA] - [MONTO_GASTO_FIJO] - [STATIC_MONTO_DSCTO_CORPORATIVO] - [SUMA_NC]');
		$this->dws['dw_nota_venta']->set_computed('STATIC_RESULTADO', '[TOTAL_NETO] - [SUM_OC_TOTAL_NETA] - [MONTO_GASTO_FIJO] - [STATIC_MONTO_DSCTO_CORPORATIVO] - [SUMA_NC]');	
		$this->dws['dw_nota_venta']->set_computed('PORC_RESULTADO', '[RESULTADO] / ([TOTAL_NETO] - [STATIC_MONTO_DSCTO_CORPORATIVO] - [SUMA_NC]) * 100');
		$this->dws['dw_nota_venta']->set_computed('STATIC_PORC_RESULTADO', '[RESULTADO] / ([TOTAL_NETO] - [STATIC_MONTO_DSCTO_CORPORATIVO] - [SUMA_NC]) * 100');
		$this->dws['dw_nota_venta']->set_computed('COMISION_V1', '([PORC_VENDEDOR1] / 100) * [RESULTADO]');	
		$this->dws['dw_nota_venta']->set_computed('COMISION_V2', '([PORC_VENDEDOR2] / 100) * [RESULTADO]');	
		$this->dws['dw_nota_venta']->set_computed('COMISION_GV', '[RESULTADO] * [PORC_GV] / 100');	
		$this->dws['dw_nota_venta']->set_computed('COMISION_ADM', '[RESULTADO] * [PORC_ADM] / 100');	
		$this->dws['dw_nota_venta']->set_computed('REMANENTE', '[RESULTADO] - [MONTO_DIRECTORIO] - [COMISION_V1] - [COMISION_V2] - [COMISION_GV] - [COMISION_ADM]');	
		
		// registra historial de quien modifico comisiones
		$this->add_auditoria('PORC_VENDEDOR1');
		$this->add_auditoria('PORC_VENDEDOR2');
		$this->add_auditoria('CENTRO_COSTO_CLIENTE');
		$this->add_auditoria('AUTORIZA_PROCESAR_VENTA');
		
		// auditoria de descuentos
		$this->add_auditoria('PORC_DSCTO1');
		$this->add_auditoria('MONTO_DSCTO1');
		$this->add_auditoria('PORC_DSCTO2');
		$this->add_auditoria('MONTO_DSCTO2');
		$this->add_auditoria('REBAJA');
					
		// registra historial de quien modifico el descuento corporativo
		$this->add_auditoria('PORC_DSCTO_CORPORATIVO');
	
		$this->dws['dw_nota_venta']->add_control(new edit_text('CANT_CAMBIO_PORC_DESCTO_CORP',10, 10, 'hidden'));
		
		// dw pagos
		$this->dws['dw_doc_nota_venta'] = new dw_doc_nota_venta();
		
		$this->dws['dw_nv_item_od_ws'] = new dw_nv_item_od_ws();
		
		$this->dws['dw_nota_venta']->add_control(new edit_text('RANGO_DOC_NOTA_VENTA',10, 10, 'hidden'));
		$this->dws['dw_nota_venta']->add_control(new static_num('TOTAL_MONTO_DOC'));
		
		// cierre
		$this->dws['dw_tipo_pendiente_nota_venta'] = new dw_tipo_pendiente_nota_venta();	
		
		$sql = "select 	COD_USUARIO
						,NOM_USUARIO
				from USUARIO";						
		$this->dws['dw_nota_venta']->add_control(new drop_down_dw('COD_USUARIO_CIERRE',$sql,150));	
		$this->dws['dw_nota_venta']->set_entrable('COD_USUARIO_CIERRE', false);
		
		$this->dws['dw_nota_venta']->add_control(new edit_text('CIERRE_H',10, 10, 'hidden'));
		$this->dws['dw_nota_venta']->add_control(new edit_text('CIERRE_SIN_P_H',10, 10, 'hidden'));
		$this->dws['dw_nota_venta']->add_control(new edit_text('MOTIVO_CIERRE_SIN_PART_H',10, 10, 'hidden'));
		
		
		$this->dws['dw_nota_venta']->add_control(new edit_text('VALIDA_MOTIVO_CIERRE_H',10,10, 'hidden'));
		$this->add_auditoria('FECHA_PLAZO_CIERRE', 'convert(varchar, FECHA_PLAZO_CIERRE, 103)');
		$this->dws['dw_nota_venta']->add_control(new edit_text('CANT_CAMBIO_FECHA_PLAZO_CIERRE',10,10, 'hidden'));

		//auditoria Solicitado por IS. los porcentajes esta despues de los computed
		$this->add_auditoria('COD_USUARIO_VENDEDOR1');
		$this->add_auditoria('COD_USUARIO_VENDEDOR2');
		$this->add_auditoria('COD_ESTADO_NOTA_VENTA');
		$this->add_auditoria('COD_EMPRESA');
		$this->add_auditoria('COD_SUCURSAL_FACTURA');
		$this->add_auditoria('COD_SUCURSAL_DESPACHO');
		$this->add_auditoria('COD_PERSONA');
		$this->add_auditoria('NRO_ORDEN_COMPRA');

		$this->add_auditoria_relacionada('ITEM_NOTA_VENTA', 'COD_PRODUCTO');
		$this->add_auditoria_relacionada('ITEM_NOTA_VENTA', 'CANTIDAD');
		$this->add_auditoria_relacionada('ITEM_NOTA_VENTA', 'PRECIO');
		$this->add_auditoria_relacionada('NOTA_VENTA_DOCS', 'D_NOM_ARCHIVO', 'NOM_ARCHIVO');
		$this->add_auditoria_relacionada('NV_ITEM_OD_WS', 'CHEQ_RECIBIDO');
		
		// focus 
		$this->set_first_focus('NRO_ORDEN_COMPRA');		
		
		///////////////////////
		// solo si tiene privilegios es ingtresable
		if ($this->tiene_privilegio_opcion('991035')<>'E') {
			$this->dws['dw_nota_venta']->controls['MONTO_DSCTO1']->readonly = true;
			$this->dws['dw_nota_venta']->controls['MONTO_DSCTO2']->readonly = true;
		}
		$this->dws['dw_nota_venta']->add_control(new static_text('ETIQUETA_DESCT1'));
		$this->dws['dw_nota_venta']->add_control(new static_text('ETIQUETA_DESCT2'));
		//////////////////////		 
		
		// documentos relacionados a la NV
		$this->dws['dw_docs'] = new dw_docs();
		
		if($this->cod_usuario == 4 || $this->cod_usuario == 8 || $this->cod_usuario == 1 || $this->cod_usuario == 71)
			$this->dws['dw_nota_venta']->set_entrable('AUTORIZA_PROCESAR_VENTA', true);
		else
			$this->dws['dw_nota_venta']->set_entrable('AUTORIZA_PROCESAR_VENTA', false);
		
	}
	function add_controls_dscto($nro_dscto) {
		parent::add_controls_dscto($nro_dscto);
		$jsP = $this->dws[$this->dw_tabla]->controls['PORC_DSCTO'.$nro_dscto]->get_onChange();
		$jsM = $this->dws[$this->dw_tabla]->controls['MONTO_DSCTO'.$nro_dscto]->get_onChange();

		// maneja los static con los labes que indican el tipo dscto ingreso por el usuario		
		$java_script = " if (document.getElementById('INGRESO_USUARIO_DSCTO".$nro_dscto."_0').value == 'M') {
							if (document.getElementById('MONTO_DSCTO".$nro_dscto."_0').value == 0) 
								document.getElementById('ETIQUETA_DESCT".$nro_dscto."_0').innerHTML = '';
							else 
								document.getElementById('ETIQUETA_DESCT".$nro_dscto."_0').innerHTML = 'Descuento ingresado como monto';
						}
						else 
							document.getElementById('ETIQUETA_DESCT".$nro_dscto."_0').innerHTML = '';";
		
		$this->dws[$this->dw_tabla]->controls['PORC_DSCTO'.$nro_dscto]->set_onChange($jsP.$java_script);
		$this->dws[$this->dw_tabla]->controls['MONTO_DSCTO'.$nro_dscto]->set_onChange($jsM.$java_script);
	}
	function new_record() {
	    
		if (session::is_set('NV_CREADA_DESDE')) {
			$vl_result = session::get('NV_CREADA_DESDE');
			$this->creada_desde($vl_result);
			$this->dws['dw_nota_venta']->set_item(0, 'DISPLAY_DESCARGA','none');
			$this->dws['dw_nota_venta']->set_item(0, 'DISPLAY_MSG_NO_INF', 'none');
//			$this->set_entrable('BTN_CONSULTA_STK', false);
			$this->dws['dw_llamado']->insert_row();
			session::un_set('NV_CREADA_DESDE');	
			return;
		}
		$this->dws['dw_nota_venta']->insert_row();
        
		unset($this->dws['dw_nota_venta']->controls['COD_FORMA_PAGO']);
		$sql_forma_pago	= "SELECT COD_FORMA_PAGO
								 ,NOM_FORMA_PAGO
								 ,CANTIDAD_DOC
			   			   FROM FORMA_PAGO
			   			   WHERE ES_VIGENTE = 'S'
			   			   ORDER BY ORDEN";
		$this->dws['dw_nota_venta']->add_control($control = new drop_down_dw('COD_FORMA_PAGO', $sql_forma_pago, 180));
		$control->set_onChange("change_forma_pago('', this);");
		
		$this->dws['dw_nota_venta']->set_item(0, 'TXT_BTN_NV_INFORME', 'NV NO DEBE SUMAR EN INFORMES');
		$this->dws['dw_nota_venta']->set_item(0, 'DISABLED_BTN', 'disabled');
		$this->dws['dw_nota_venta']->set_item(0, 'DISPLAY_MSG_NO_INF', 'none');
		$this->dws['dw_nota_venta']->set_item(0, 'FECHA_NOTA_VENTA', $this->current_date());
		$this->dws['dw_nota_venta']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_nota_venta']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->dws['dw_nota_venta']->set_item(0, 'COD_ESTADO_NOTA_VENTA', self::K_ESTADO_EMITIDA);
		$this->dws['dw_nota_venta']->set_item(0, 'COD_ESTADO_NOTA_VENTA_H', self::K_ESTADO_EMITIDA);
		$this->dws['dw_nota_venta']->set_item(0, 'NOM_ESTADO_NOTA_VENTA', 'EMITIDA');
		$this->dws['dw_nota_venta']->set_item(0, 'COD_MONEDA', $this->get_orden_min('MONEDA'));
		$this->dws['dw_nota_venta']->set_item(0, 'VALOR_TIPO_CAMBIO', 1);
		$this->dws['dw_nota_venta']->controls['NOM_FORMA_PAGO_OTRO']->set_type('hidden');
		$this->dws['dw_nota_venta']->controls['CANTIDAD_DOC_FORMA_PAGO_OTRO']->set_type('hidden');
		$this->dws['dw_nota_venta']->set_item(0, 'PORC_DSCTO_CORPORATIVO', '0');
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_DSCTO_CORPORATIVO', 1));
		$this->dws['dw_nota_venta']->set_entrable('BTN_CONSULTA_STK', false);
		$this->dws['dw_nota_venta']->set_item(0, 'LOAD_RECORD_STATE', 'N');

		$this->valores_default_vend();
		
		$this->dws['dw_nota_venta']->set_item(0, 'RANGO_DOC_NOTA_VENTA',$this->get_parametro(self::K_PARAM_RANGO_DOC_NOTA_VENTA));
				
		// no se ven tablas asociadas al cierre
		$this->dws['dw_nota_venta']->set_item(0, 'TABLE_CIERRE_DISPLAY', 'none');
		$this->dws['dw_nota_venta']->set_item(0, 'BOTON_CIERRE_DISPLAY', 'none');
		$this->dws['dw_nota_venta']->set_item(0, 'TABLE_PENDIENTE_DISPLAY', 'none');
		
		//datos de anulación
		$this->dws['dw_nota_venta']->set_item(0, 'TR_DISPLAY_ANULADA', 'none');
		$this->dws['dw_nota_venta']->set_item(0, 'DISPLAY_DESCARGA','none');
		$this->dws['dw_nota_venta']->set_item(0, 'PORC_DESC_PERMITIDO', $this->porc_desc_permitido);
		$this->dws['dw_nota_venta']->set_item(0, 'BTN_LISTA_NC', 'disabled');
		$this->dws['dw_llamado']->insert_row();
		
		$this->dws['dw_nota_venta']->set_item(0, 'NO_TIENE_OC', 'N');
		
		$this->dws['dw_nota_venta']->set_item(0, 'D_NO_AUTORIZADO', 'none');
		$this->dws['dw_nota_venta']->set_item(0, 'D_AUTORIZADO', 'none');
	}
	//deshabilita boton valida_correo(); cuando se esta agregando una nueva nota de venta 
	/*function habilita_boton(&$temp, $boton, $habilita) {
		if ($boton=='envia_mail_pago') {
			if ($habilita)
				$temp->setVar("WI_ENVIA_MAIL_PAGO", '<input name="b_create" id="b_create" src="../../../../commonlib/trunk/images/b_create.jpg" type="image" '.
											'onMouseDown="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_click.jpg\',1)" '.
											'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
											'onMouseOver="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_over.jpg\',1)" '.
											'onClick="return request_factura(\'Ingrese Nº de la Nota de Venta\',\'\');"'.
											'/>');
										
			else
				$temp->setVar("WO_".strtoupper($boton), '<img src="../../images_appl/boton_enviar_d.png"/>');
		}
		else
			parent::habilita_boton($temp, $boton, $habilita);
	}*/
	function habilita_boton(&$temp, $boton, $habilita) {
		if ($boton=='envia_mail_pago'){
			if ($habilita){
				$ruta_over = "'../../images_appl/b_".$boton."_over.jpg'";
				$ruta_out = "'../../images_appl/b_".$boton.".jpg'";
				$ruta_click = "'../../images_appl/b_".$boton."_click.jpg'";
				$temp->setVar("WI_ENVIA_MAIL_PAGO", '<input name="b_'.$boton.'" id="b_'.$boton.'" type="button" onmouseover="entrada(this, '.$ruta_over.')" onmouseout="salida(this, '.$ruta_out.')" onmousedown="down(this, '.$ruta_click.')"'.
								'style="cursor:pointer;height:68px;width:66px;border: 0;background-image:url(../../images_appl/b_'.$boton.'.jpg);background-repeat:no-repeat;background-position:center;border-radius: 15px;"'.
								'onClick="valida_correo();"/>');
			}else{
				//$temp->setVar("WI_ENVIA_MAIL_PAGO", '<img src="../../images_appl/b_'.$boton.'_d.jpg"/>');
				$temp->setVar("WI_ENVIA_MAIL_PAGO", '');
			}	
		}else if($boton == 'print_folleto'){
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
		}else if($boton == 'f_tecnica'){
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
		}else
			parent::habilita_boton($temp, $boton, $habilita);
	}
	function habilita_boton_print(&$temp, $boton, $habilita) {
		if($boton == 'print' && $habilita == true){
			$ruta_over = "'../../../../commonlib/trunk/images/b_print_over.jpg'";
			$ruta_out = "'../../../../commonlib/trunk/images/b_print.jpg'";
			$ruta_click = "'../../../../commonlib/trunk/images/b_print_click.jpg'";
			$temp->setVar("WI_PRINT_2", '<input name="b_'.$boton.'" id="b_'.$boton.'" type="button" onmouseover="entrada(this, '.$ruta_over.')" onmouseout="salida(this, '.$ruta_out.')" onmousedown="down(this, '.$ruta_click.')"'.
										 'style="cursor:pointer;height:68px;width:66px;border: 0;background-image:url(../../../../commonlib/trunk/images/b_print.jpg);background-repeat:no-repeat;background-position:center;border-radius: 15px;"'.
										 'onClick="dlg_print();" />');
		
		}else{
				$temp->setVar("WI_PRINT_2", '<img src="../../../../commonlib/trunk/images/b_print_d.jpg"/>');
			}	
	}
	function navegacion(&$temp){
		parent::navegacion($temp);		
		$cod_estado_nota_venta = $this->dws['dw_nota_venta']->get_item(0, 'COD_ESTADO_NOTA_VENTA_H');
		$cod_nota_venta = $this->dws['dw_nota_venta']->get_item(0, 'COD_NOTA_VENTA');
		
		if($cod_estado_nota_venta == 2 or $cod_estado_nota_venta == 4){
			
			$priv_adm = $this->get_privilegio_opcion_usuario('991065', $this->cod_usuario);
			$priv_user = $this->get_privilegio_opcion_usuario('991060', $this->cod_usuario);
			
			if($priv_adm == 'E')
				$this->habilita_boton($temp, 'envia_mail_pago', true);
			else{

				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				$sql="SELECT TOP 1 LINK_VISIBLE
			     	      FROM WP_TRANSACCION
			      	      WHERE COD_NOTA_VENTA = $cod_nota_venta
			      	      ORDER BY COD_WP_TRANSACCION DESC";
		           
				$result = $db->build_results($sql);

				if($result[0]['LINK_VISIBLE'] == 'S')
					$this->habilita_boton($temp, 'envia_mail_pago', false);
				else if($priv_user == 'E')
					$this->habilita_boton($temp, 'envia_mail_pago', true);
				else
					$this->habilita_boton($temp, 'envia_mail_pago', false);
			}

		}else
 			$this->habilita_boton($temp, 'envia_mail_pago', false);
		
 		$priv = $this->get_privilegio_opcion_usuario('991080', $this->cod_usuario);
		if($priv == 'E')
			$this->habilita_boton($temp, 'print_folleto', true);
		else
			$this->habilita_boton($temp, 'print_folleto', false);
		
		$priv = $this->get_privilegio_opcion_usuario('991085', $this->cod_usuario);
		if($priv == 'E')
			$this->habilita_boton($temp, 'f_tecnica', true);
		else
			$this->habilita_boton($temp, 'f_tecnica', false);
		
	}

	function habilitar(&$temp, $habilita){
		parent::habilitar($temp, $habilita);
		$cod_estado_nota_venta = $this->dws['dw_nota_venta']->get_item(0, 'COD_ESTADO_NOTA_VENTA_H');
		
		if ($cod_estado_nota_venta == self::K_ESTADO_ANULADA){
			$priv = $this->get_privilegio_opcion_usuario('991075', $this->cod_usuario);
			if($priv == 'E'){
				$this->habilita_boton_print($temp, 'print', true);
			}else{
				$this->habilita_boton_print($temp, 'print', false);
			}
		}else	
			$this->habilita_boton_print($temp, 'print', true);
			
		if($this->is_new_record()){
			$temp->setVar('DISABLE_BTN_EMBALAJE', 'disabled="disabled"');
			$temp->setVar('DISABLE_BTN_FLETE', 'disabled="disabled"');
			$temp->setVar('DISABLE_BTN_GENERA_OC', 'disabled="disabled"');
		}
		else{
			$cod_estado_nota_venta = $this->dws['dw_nota_venta']->get_item(0, 'COD_ESTADO_NOTA_VENTA_H');
			$COD_NOTA_VENTA = $this->get_key();	
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				
			$sql = "select count(I.COD_PRODUCTO)CANT 
					from ITEM_NOTA_VENTA I 
					where I.COD_NOTA_VENTA = $COD_NOTA_VENTA AND
					I.COD_PRODUCTO = 'F'";
			$result = $db->build_results($sql);
			$CANT_F = $result[0]['CANT'];
			$sql = "select count(I.COD_PRODUCTO)CANT 
					from ITEM_NOTA_VENTA I 
					where I.COD_NOTA_VENTA = $COD_NOTA_VENTA AND
					I.COD_PRODUCTO = 'E' ";
			$result = $db->build_results($sql);
			$CANT_E = $result[0]['CANT'];				
			if($this->modify){
				if($cod_estado_nota_venta == 4){
					if($CANT_E == 0 && $CANT_F == 0){
						$temp->setVar('DISABLE_BTN_EMBALAJE', '');
						$temp->setVar('DISABLE_BTN_FLETE', '');
					}else if($CANT_E >= 1 && $CANT_F == 0){	
						$temp->setVar('DISABLE_BTN_EMBALAJE', 'disabled="disabled"');
						$temp->setVar('DISABLE_BTN_FLETE', '');
					}else if($CANT_E == 0 && $CANT_F >= 1){	
						$temp->setVar('DISABLE_BTN_EMBALAJE', '');
						$temp->setVar('DISABLE_BTN_FLETE', 'disabled="disabled"');
					}else if($CANT_E >= 1 && $CANT_F >= 1){	
						$temp->setVar('DISABLE_BTN_EMBALAJE', 'disabled="disabled"');
						$temp->setVar('DISABLE_BTN_FLETE', 'disabled="disabled"');
					}
					
					$priv = $this->get_privilegio_opcion_usuario('991070', $this->cod_usuario);
					if($priv == 'E')
						$temp->setVar('DISABLE_BTN_GENERA_OC', '');
					else
						$temp->setVar('DISABLE_BTN_GENERA_OC', 'disabled="disabled"');
					
				}else{
					$temp->setVar('DISABLE_BTN_FLETE', 'disabled="disabled"');
					$temp->setVar('DISABLE_BTN_EMBALAJE', 'disabled="disabled"');
					$temp->setVar('DISABLE_BTN_GENERA_OC', 'disabled="disabled"');
				}		
			}		
			else{
				$temp->setVar('DISABLE_BTN_FLETE', 'disabled="disabled"');
				$temp->setVar('DISABLE_BTN_EMBALAJE', 'disabled="disabled"');
				$temp->setVar('DISABLE_BTN_GENERA_OC', 'disabled="disabled"');
			}	
		}
		
	}

	function load_record() {
		$COD_NOTA_VENTA = $this->get_item_wo($this->current_record, 'COD_NOTA_VENTA');
		$this->dws['dw_nota_venta']->retrieve($COD_NOTA_VENTA);
		
		$COD_EMPRESA = $this->dws['dw_nota_venta']->get_item(0, 'COD_EMPRESA');
		$this->dws['dw_nota_venta']->controls['COD_SUCURSAL_FACTURA']->retrieve($COD_EMPRESA);
		$this->dws['dw_nota_venta']->controls['COD_SUCURSAL_DESPACHO']->retrieve($COD_EMPRESA);
		$this->dws['dw_nota_venta']->controls['COD_PERSONA']->retrieve($COD_EMPRESA);		
		$this->dws['dw_lista_guia_despacho']->retrieve($COD_NOTA_VENTA);
		$this->dws['dw_lista_guia_recepcion']->retrieve($COD_NOTA_VENTA);	
		$this->dws['dw_lista_factura']->retrieve($COD_NOTA_VENTA);
		$this->dws['dw_lista_nota_credito']->retrieve($COD_NOTA_VENTA);	
		$this->dws['dw_lista_pago']->retrieve($COD_NOTA_VENTA);
		$this->dws['dw_participacion']->retrieve($COD_NOTA_VENTA);	
		$this->dws['dw_item_nota_venta']->retrieve($COD_NOTA_VENTA);	
		$this->dws['dw_orden_compra']->retrieve($COD_NOTA_VENTA);
		$this->dws['dw_nv_backcharge']->retrieve($COD_NOTA_VENTA);
		$this->dws['dw_pre_orden_compra']->retrieve($COD_NOTA_VENTA);
		$this->dws['dw_item_nota_venta_resultado']->retrieve($COD_NOTA_VENTA);
		$this->dws['dw_doc_nota_venta']->retrieve($COD_NOTA_VENTA);
		$this->dws['dw_item_resumen_nv']->retrieve($COD_NOTA_VENTA);
		$MODIFICA_NV = $this->dws['dw_nota_venta']->get_item(0, 'MODIFICA_NV');
		$AUTORIZA_ANULACION = $this->dws['dw_nota_venta']->get_item(0, 'AUTORIZA_ANULACION');
		$this->dws['dw_nota_venta']->set_item(0, 'D_COD_NOTA_ENCRIPT',$COD_NOTA_VENTA);
		$this->dws['dw_nota_venta']->set_entrable('COD_USUARIO_VENDEDOR2', true);
		$this->dws['dw_nota_venta']->set_entrable('PORC_VENDEDOR2', true);
		
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_MOD_VENDEDOR2, $this->cod_usuario);
		
		if($priv == 'N'){
			$this->dws['dw_nota_venta']->controls['COD_USUARIO_VENDEDOR2']->enabled = false;
			$this->dws['dw_nota_venta']->controls['PORC_VENDEDOR2']->readonly = true;
		}
		
		//DISPLAY_DESCARGA
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql="	SELECT COUNT(*) TIENE_DESCARGA
				  FROM NOTA_VENTA_DOCS NVD
				 WHERE NVD.ES_OC = 'S'
		           AND NVD.COD_NOTA_VENTA = $COD_NOTA_VENTA";
		           
		 $result = $db->build_results($sql);					
		 $tiene_descarga = $result[0]['TIENE_DESCARGA'];
		 
		 if($tiene_descarga == 0){
		 $this->dws['dw_nota_venta']->set_item(0, 'DISPLAY_DESCARGA','none');	
		 }else{
		 $this->dws['dw_nota_venta']->set_item(0, 'DISPLAY_DESCARGA','');	
		 }
		$cod_forma_pago		= $this->dws['dw_nota_venta']->get_item(0, 'COD_FORMA_PAGO');
		
		unset($this->dws['dw_nota_venta']->controls['COD_FORMA_PAGO']);
		$sql_forma_pago	= "SELECT COD_FORMA_PAGO
								 ,NOM_FORMA_PAGO
								 ,CANTIDAD_DOC
			   			   FROM FORMA_PAGO
			   			  where ES_VIGENTE = 'S' OR COD_FORMA_PAGO = $cod_forma_pago
			   			   ORDER BY ORDEN";
		$this->dws['dw_nota_venta']->add_control($control = new drop_down_dw('COD_FORMA_PAGO', $sql_forma_pago, 180));
		$control->set_onChange("change_forma_pago('', this);");	
		
		if ($cod_forma_pago==1){
			$this->dws['dw_nota_venta']->controls['NOM_FORMA_PAGO_OTRO']->set_type('text');
			$this->dws['dw_nota_venta']->controls['CANTIDAD_DOC_FORMA_PAGO_OTRO']->set_type('text');
		}	
		else{
			$this->dws['dw_nota_venta']->controls['NOM_FORMA_PAGO_OTRO']->set_type('hidden');
			$this->dws['dw_nota_venta']->controls['CANTIDAD_DOC_FORMA_PAGO_OTRO']->set_type('hidden');
		}	
		
		$COD_ESTADO_NOTA_VENTA = $this->dws['dw_nota_venta']->get_item(0, 'COD_ESTADO_NOTA_VENTA_H');
		
		//$this->b_print_visible 	 = true;
		$this->b_no_save_visible = true;
		$this->b_save_visible 	 = true;
		$this->b_modify_visible  = true;
		$this->b_delete_visible  = true;		
		if ($COD_ESTADO_NOTA_VENTA == self::K_ESTADO_EMITIDA){	
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			if (!$db->EXECUTE_SP('spu_tipo_pendiente_nota_venta', "'LOAD', $COD_NOTA_VENTA"))
				return false;
			if ($AUTORIZA_ANULACION == 'S'){
				//si su perfil tiene permiso de anular nuestra el codigo de anulacion
				$sql = "select COD_ESTADO_NOTA_VENTA,
							NOM_ESTADO_NOTA_VENTA
						from ESTADO_NOTA_VENTA
						where COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_EMITIDA." or
							COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_CONFIRMADA." or
							COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_ANULADA."
						order by ORDEN";
			}else{
				//si su perfil no tiene permiso de anular elimina el codigo de anulacion
				$sql = "select COD_ESTADO_NOTA_VENTA,
							NOM_ESTADO_NOTA_VENTA
						from ESTADO_NOTA_VENTA
						where COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_EMITIDA." or
							COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_CONFIRMADA."
						order by ORDEN";
			}

			unset($this->dws['dw_nota_venta']->controls['COD_ESTADO_NOTA_VENTA']);
			$this->dws['dw_nota_venta']->add_control($control = new drop_down_dw('COD_ESTADO_NOTA_VENTA',$sql,141));	
			$control->set_onChange("cambia_estado(this);");
			$this->dws['dw_nota_venta']->controls['NOM_ESTADO_NOTA_VENTA']->type = 'hidden';
			$this->dws['dw_nota_venta']->add_control(new edit_text_upper('MOTIVO_ANULA',100, 100));
			
			$this->dws['dw_item_nota_venta']->unset_protect('COD_PRODUCTO');
			$this->dws['dw_item_nota_venta']->unset_protect('NOM_PRODUCTO');				
		   
		}
		else if ($COD_ESTADO_NOTA_VENTA == self::K_ESTADO_CONFIRMADA){
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			if (!$db->EXECUTE_SP('spu_tipo_pendiente_nota_venta', "'LOAD', $COD_NOTA_VENTA"))
				return false;
			
			
            
			// si estado = confirmada se puede CERRAR, ANULAR
			$sql = "select COD_ESTADO_NOTA_VENTA,
						NOM_ESTADO_NOTA_VENTA
					from ESTADO_NOTA_VENTA
					where COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_CONFIRMADA." or
						COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_ANULADA."
					order by ORDEN";


			unset($this->dws['dw_nota_venta']->controls['COD_ESTADO_NOTA_VENTA']);
			$this->dws['dw_nota_venta']->add_control($control = new drop_down_dw('COD_ESTADO_NOTA_VENTA',$sql,141));	
			$control->set_onChange("cambia_estado(this);");
			$this->dws['dw_nota_venta']->controls['NOM_ESTADO_NOTA_VENTA']->type = 'hidden';
			$this->dws['dw_nota_venta']->add_control(new edit_text_upper('MOTIVO_ANULA',100, 100));
			
			if ($AUTORIZA_ANULACION == 'S'){
				//permite cambiar a anulado siempre que su perfil lo autorize
				$this->dws['dw_nota_venta']->set_entrable('COD_ESTADO_NOTA_VENTA'	, true);
			}else{
				$this->dws['dw_nota_venta']->set_entrable('COD_ESTADO_NOTA_VENTA'	, false);
			}
			
			if ($MODIFICA_NV == 'N'){
				// deja no entrable campos tab1 Nota Venta
				$this->dws['dw_nota_venta']->set_entrable('NRO_ORDEN_COMPRA'        , false);
				$this->dws['dw_nota_venta']->set_entrable('FECHA_ORDEN_COMPRA_CLIENTE', false);
				$this->dws['dw_nota_venta']->set_entrable('CENTRO_COSTO_CLIENTE'    , false);
				$this->dws['dw_nota_venta']->set_entrable('REFERENCIA'       		, false);
				$this->dws['dw_nota_venta']->set_entrable('COD_EMPRESA'        	 	, false);
				$this->dws['dw_nota_venta']->set_entrable('ALIAS'        			, false);
				$this->dws['dw_nota_venta']->set_entrable('RUT'        			 	, false);
				$this->dws['dw_nota_venta']->set_entrable('NOM_EMPRESA'        	 	, false);
				$this->dws['dw_nota_venta']->set_entrable('COD_SUCURSAL_DESPACHO'   , false);
				$this->dws['dw_nota_venta']->set_entrable('COD_SUCURSAL_FACTURA'    , false);
				$this->dws['dw_nota_venta']->set_entrable('COD_PERSONA'        	 	, false);
				$this->dws['dw_nota_venta']->set_entrable('NO_TIENE_OC'        		, false);
				$this->dws['dw_item_nota_venta']->set_entrable_dw(false);
			}
			else{
				// deja entrable campos tab1 Nota Venta siempre que su perfil este autorizado
				$this->dws['dw_nota_venta']->set_entrable('NRO_ORDEN_COMPRA'        , true);
				$this->dws['dw_nota_venta']->set_entrable('FECHA_ORDEN_COMPRA_CLIENTE', true);
				$this->dws['dw_nota_venta']->set_entrable('CENTRO_COSTO_CLIENTE'    , true);
				$this->dws['dw_nota_venta']->set_entrable('REFERENCIA'       		, true);
				$this->dws['dw_nota_venta']->set_entrable('COD_EMPRESA'        	 	, true);
				$this->dws['dw_nota_venta']->set_entrable('ALIAS'        			, true);
				$this->dws['dw_nota_venta']->set_entrable('RUT'        			 	, true);
				$this->dws['dw_nota_venta']->set_entrable('NOM_EMPRESA'        	 	, true);
				$this->dws['dw_nota_venta']->set_entrable('COD_SUCURSAL_DESPACHO'   , true);
				$this->dws['dw_nota_venta']->set_entrable('COD_SUCURSAL_FACTURA'    , true);
				$this->dws['dw_nota_venta']->set_entrable('COD_PERSONA'        	 	, true);
				$this->dws['dw_nota_venta']->set_entrable('NO_TIENE_OC'        		, true);
			}
			
			// deja no entrable campos tab Compras
			$this->dws['dw_pre_orden_compra']->set_entrable_dw(false);
			
			///////////////webservice///////////////
			$sql = "SELECT COD_ORDEN_COMPRA
					FROM ORDEN_COMPRA
					WHERE COD_NOTA_VENTA = $COD_NOTA_VENTA
					and COD_EMPRESA = 1302	-- Todoinox
					and COD_ESTADO_ORDEN_COMPRA <> 2";
			$result = $db->build_results($sql);
			
			for($j=0 ; $j < count($result) ; $j++){ 
				$cod_orden_compra = $result[$j]['COD_ORDEN_COMPRA'];
				
				if($cod_orden_compra <> ''){
					$sql = "select OD.COD_ORDEN_DESPACHO
										  ,COD_ITEM_ORDEN_DESPACHO
										  ,COD_PRODUCTO
										  ,NOM_PRODUCTO
										  ,CANTIDAD
										  ,NRO_FACTURA
										  ,CONVERT(VARCHAR, FECHA_FACTURA, 103) FECHA_FACTURA
										  ,CONVERT(VARCHAR, FECHA_ORDEN_DESPACHO, 103) FECHA_ORDEN_DESPACHO
										  ,CONVERT(VARCHAR, FECHA_ORDEN_DESPACHO, 108) FECHA_ORDEN_DESPACHO_HORA
										  ,NOM_USUARIO
									from TODOINOX.dbo.FACTURA F
										,TODOINOX.dbo.ORDEN_DESPACHO OD	
										,TODOINOX.dbo.ITEM_ORDEN_DESPACHO IOD
										,TODOINOX.dbo.USUARIO U
									where NRO_ORDEN_COMPRA = '$cod_orden_compra'
									and WS_ORIGEN = 'COMERCIAL'
									and TIPO_DOC_ORIGEN = 'FACTURA'
									and OD.COD_DOC_ORIGEN = F.COD_FACTURA
									and IOD.COD_ORDEN_DESPACHO = OD.COD_ORDEN_DESPACHO
									and U.COD_USUARIO = OD.COD_USUARIO";
					$result_ws = $db->build_results($sql);
					
					for($i=0 ; $i < count($result_ws) ; $i++){
						$COD_ORDEN_DESPACHO			= $result_ws[$i]['COD_ORDEN_DESPACHO'];
						$COD_ITEM_ORDEN_DESPACHO	= $result_ws[$i]['COD_ITEM_ORDEN_DESPACHO'];
						$COD_PRODUCTO				= $result_ws[$i]['COD_PRODUCTO'];
						$NOM_PRODUCTO				= $result_ws[$i]['NOM_PRODUCTO'];
						$CANTIDAD					= $result_ws[$i]['CANTIDAD'];
						$NRO_FACTURA				= $result_ws[$i]['NRO_FACTURA'];
						$FECHA_FACTURA				= $result_ws[$i]['FECHA_FACTURA'];
						$FECHA_ORDEN_DESPACHO		= $result_ws[$i]['FECHA_ORDEN_DESPACHO'];
						$FECHA_ORDEN_DESPACHO_HORA	= $result_ws[$i]['FECHA_ORDEN_DESPACHO_HORA'];
						$NOM_USUARIO				= $result_ws[$i]['NOM_USUARIO'];
					/////////////// fin cambio eliminacion WS				
				
						$sql = "SELECT COUNT(*) COUNT
								FROM NV_ITEM_OD_WS
								WHERE COD_ITEM_ORDEN_DESPACHO = $COD_ITEM_ORDEN_DESPACHO";
						$result_val = $db->build_results($sql);
						
						if($result_val[0]['COUNT'] > 0)
							continue;
								
						$FECHA_FACTURA = $this->str2date($FECHA_FACTURA);
						$FECHA_ORDEN_DESPACHO = $this->str2date($FECHA_ORDEN_DESPACHO, $FECHA_ORDEN_DESPACHO_HORA);
						$NOM_PRODUCTO = str_replace("'", "''", $NOM_PRODUCTO);
						
						$sp = "spu_nv_item_from_od";	
						$param = "'INSERT'
								 ,NULL
								 ,'TODOINOX'
								 ,$COD_NOTA_VENTA
								 ,$COD_ORDEN_DESPACHO
								 ,$COD_ITEM_ORDEN_DESPACHO
								 ,'$COD_PRODUCTO'
								 ,'$NOM_PRODUCTO'
								 ,$CANTIDAD
								 ,'N'
								 ,$NRO_FACTURA
								 ,$FECHA_FACTURA
								 ,$FECHA_ORDEN_DESPACHO
								 ,'$NOM_USUARIO'";
								 
						$db->EXECUTE_SP($sp, $param);		 
					}
					
				}
				////////////////////////////////////////
			}	
			$this->dws['dw_nv_item_od_ws']->retrieve($COD_NOTA_VENTA);
			/** SEMAFORO **/
			$exito = true;
			/** Si la NV ya fue autorizada por SP, RE, o JT, entonces NO requiere autorización. **/
			$sql_val = "SELECT AUTORIZA_PROCESAR_VENTA
        				  ,COD_FORMA_PAGO
        				  ,COD_EMPRESA
        			FROM NOTA_VENTA
        			WHERE COD_NOTA_VENTA = ".$COD_NOTA_VENTA;
            $result_val					= $db->build_results($sql_val); 
            $cod_forma_pago				= $result_val[0]['COD_FORMA_PAGO'];
            $autoriza_procesar_venta	= $result_val[0]['AUTORIZA_PROCESAR_VENTA'];
            $cod_empresa				= $result_val[0]['COD_EMPRESA'];
            
            if($autoriza_procesar_venta <> 'S'){
            	/** Si el RUT de la NV pertenece a un Centro de Costo (Sodexo, Compass, CDR), entonces NO requiere autorización.. **/
            	$sql_val2 = "SELECT COUNT(*) COUNT
            				 FROM CENTRO_COSTO CC
            					 ,CENTRO_COSTO_EMPRESA CCE
            				 WHERE COD_EMPRESA = $cod_empresa
            				 AND CC.COD_CENTRO_COSTO = CCE.COD_CENTRO_COSTO
            				 AND CC.COD_CENTRO_COSTO <> 001";
            	$result_val2 = $db->build_results($sql_val2);
            	
            	if($result_val2[0]['COUNT'] == 0){/** Si la forma de pago de la NV es: CCF - 60 DIAS
        		                                                                       CCF - 45 DIAS
        		                                                                       CCF - 30 DIAS**/
            		if($cod_forma_pago <> 30 && $cod_forma_pago <> 31 && $cod_forma_pago <> 32 && $cod_forma_pago <> 10 && $cod_forma_pago <> 7){
            			/**		Si la forma de pago de no es ninguna de la anteriores, se consulta si la forma de pago 
            			 *      se considera como Contado para BIGGI
        		                Si lo es, entonces se permite Facturar y/o despachar.**/
            			$sql_val3 = "SELECT COUNT(*) COUNT
            						 FROM FORMA_PAGO
            						 WHERE COD_FORMA_PAGO = $cod_forma_pago
            						 AND CRITERIO_DESPACHO_FACTURA = 1";
            			$result_val3 = $db->build_results($sql_val3);
            			
            			if($result_val3[0]['COUNT'] == 0){
            				
            				$sql_val4 = "SELECT COUNT(*) COUNT
            							 FROM EMPRESA
            							 WHERE SUJETO_A_APROBACION = 'S'
            							 AND COD_EMPRESA = $cod_empresa";
            				$result_val4 = $db->build_results($sql_val4);
            				
            				if($result_val4[0]['COUNT'] == 0)
            					$exito = false;
            			}
            		}
            	}	
            }
            if(!$exito){
            	$this->dws['dw_nota_venta']->set_item(0, 'D_NO_AUTORIZADO', '');
            }else 
            	$this->dws['dw_nota_venta']->set_item(0, 'D_AUTORIZADO', '');
		}
		else if ($COD_ESTADO_NOTA_VENTA == self::K_ESTADO_ANULADA) {
			$sql = "select COD_ESTADO_NOTA_VENTA,
						NOM_ESTADO_NOTA_VENTA
					from ESTADO_NOTA_VENTA
					where COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_ANULADA;

			unset($this->dws['dw_nota_venta']->controls['COD_ESTADO_NOTA_VENTA']);
			$this->dws['dw_nota_venta']->add_control(new drop_down_dw('COD_ESTADO_NOTA_VENTA',$sql,141));	
			$this->dws['dw_nota_venta']->controls['NOM_ESTADO_NOTA_VENTA']->type = 'hidden';
			
			//$this->b_print_visible 	 = false;
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			$this->b_delete_visible  = false;		
			
			// ANULADA => porc despacho siempre en cero
			$this->dws['dw_nota_venta']->set_item(0, 'PORC_GD', 0);
		}
		else if ($COD_ESTADO_NOTA_VENTA == self::K_ESTADO_CERRADA) {
			$sql = "select COD_ESTADO_NOTA_VENTA,
						NOM_ESTADO_NOTA_VENTA
					from ESTADO_NOTA_VENTA
					where COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_CERRADA;

			unset($this->dws['dw_nota_venta']->controls['COD_ESTADO_NOTA_VENTA']);
			$this->dws['dw_nota_venta']->add_control(new drop_down_dw('COD_ESTADO_NOTA_VENTA',$sql,141));	
			$this->dws['dw_nota_venta']->controls['NOM_ESTADO_NOTA_VENTA']->type = 'hidden';
			
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			$this->b_delete_visible  = false;		
		}
		
		if ($this->tiene_privilegio_opcion(self::K_AUTORIZA_CIERRE))
			$this->dws['dw_nota_venta']->add_control(new edit_date('FECHA_PLAZO_CIERRE'));
		
		else
			$this->dws['dw_nota_venta']->add_control(new static_text('FECHA_PLAZO_CIERRE'));
			
		
			
		$AUTORIZA_CIERRE = $this->dws['dw_nota_venta']->get_item(0, 'AUTORIZA_CIERRE');
		if ($AUTORIZA_CIERRE == 'S'){
			$this->dws['dw_nota_venta']->set_item(0, 'BTN_LISTA_NC', '');
			$this->dws['dw_tipo_pendiente_nota_venta']->set_entrable_dw(true);
		}else{
			$this->dws['dw_nota_venta']->set_item(0, 'BTN_LISTA_NC', 'disabled');
			$this->dws['dw_tipo_pendiente_nota_venta']->set_entrable_dw(false);
			$this->dws['dw_nota_venta']->set_entrable('REBAJA', false);
		}
		$this->dws['dw_tipo_pendiente_nota_venta']->retrieve($COD_NOTA_VENTA);
		$this->dws['dw_docs']->retrieve($COD_NOTA_VENTA);
		
		$cod_usuario = session::get("COD_USUARIO"); 
		 $sql_usuario = "select COD_PERFIL
					from USUARIO
					where COD_USUARIO =$cod_usuario";			 
		 $result_usuario = $db->build_results($sql_usuario);					
		 $cod_perfil = $result_usuario[0]['COD_PERFIL'];
		 $item_menu = '991040';
		 $sql_autoriza ="SELECT AUTORIZA_MENU
							FROM AUTORIZA_MENU
						WHERE COD_ITEM_MENU = $item_menu
						AND COD_PERFIL = $cod_perfil"; 
		$result_autoriza = $db->build_results($sql_autoriza);					
		 $autoriza = $result_autoriza[0]['AUTORIZA_MENU'];		
			for($i=0; $i<$this->dws['dw_docs']->row_count(); $i++) {
				 if($autoriza == 'E'){
				 $this->dws['dw_docs']->set_item($i, 'ELIMINA_DOC', '');	
				 $this->dws['dw_nota_venta']->set_item(0, 'ELIMINA_DOC', '');
				 }else{
				 $this->dws['dw_docs']->set_item($i, 'ELIMINA_DOC', 'none');	
				 $this->dws['dw_nota_venta']->set_item(0, 'ELIMINA_DOC', 'none');
				 }
			}
		$this->dws['dw_llamado']->retrieve($COD_NOTA_VENTA);
		if ($this->dws['dw_llamado']->row_count()==0)
			$this->dws['dw_llamado']->insert_row();
			
		$this->dws['dw_nota_venta']->set_item(0, 'LL_LLAMADO','');
	   $sql = "SELECT LL.COD_LLAMADO LL_COD_LLAMADO
						,CONVERT (VARCHAR(10), LL.FECHA_LLAMADO, 103) LL_FECHA_LLAMADO
						,LLA.NOM_LLAMADO_ACCION LL_NOM_LLAMADO_ACCION
						,C.NOM_CONTACTO LL_NOM_CONTACTO
						,dbo.f_llamado_telefono(LL.COD_CONTACTO, 'EMPRESA') LL_TELEFONO_CONTACTO
						,CP.NOM_PERSONA LL_NOM_PERSONA
						,dbo.f_llamado_telefono(LL.COD_CONTACTO_PERSONA, 'PERSONA') LL_TELEFONO_PERSONA
						,LL.MENSAJE LL_MENSAJE
					FROM LLAMADO LL
						,LLAMADO_ACCION LLA
						,CONTACTO C
						,CONTACTO_PERSONA CP
				   WHERE LL.TIPO_DOC_REALIZADO = 'NOTA VENTA'
				   	 AND LL.COD_DOC_REALIZADO = $COD_NOTA_VENTA
					 AND LLA.COD_LLAMADO_ACCION = LL.COD_LLAMADO_ACCION
					 AND C.COD_CONTACTO = LL.COD_CONTACTO
					 AND CP.COD_CONTACTO_PERSONA = LL.COD_CONTACTO_PERSONA";

					 $result = $db->build_results($sql);
					 $cod_llamado = $result[0]['LL_COD_LLAMADO'];
			 
		 if($cod_llamado <> ''){
		$this->dws['dw_nota_venta']->set_item(0,'LL_LLAMADO','');
        $this->dws['dw_llamado']->set_item(0,'LL_COD_LLAMADO', $result[0]['LL_COD_LLAMADO']);
		$this->dws['dw_llamado']->set_item(0,'LL_FECHA_LLAMADO', $result[0]['LL_FECHA_LLAMADO']);
		$this->dws['dw_llamado']->set_item(0,'LL_NOM_LLAMADO_ACCION', $result[0]['LL_NOM_LLAMADO_ACCION']);
		$this->dws['dw_llamado']->set_item(0,'LL_NOM_CONTACTO', $result[0]['LL_NOM_CONTACTO']);
		$this->dws['dw_llamado']->set_item(0,'LL_TELEFONO_CONTACTO', $result[0]['LL_TELEFONO_CONTACTO']);
		$this->dws['dw_llamado']->set_item(0,'LL_NOM_PERSONA', $result[0]['LL_NOM_PERSONA']);
		$this->dws['dw_llamado']->set_item(0,'LL_TELEFONO_PERSONA', $result[0]['LL_TELEFONO_PERSONA']);
		$this->dws['dw_llamado']->set_item(0,'LL_MENSAJE', $result[0]['LL_MENSAJE']);
		}	
		
		 $item_menu = '991050';
		 $sql_autoriza_cierre ="SELECT AUTORIZA_MENU
							FROM AUTORIZA_MENU
						WHERE COD_ITEM_MENU = $item_menu
						AND COD_PERFIL = $cod_perfil"; 
		$result_autoriza_cierre = $db->build_results($sql_autoriza_cierre);					
		$autoriza_cierre = $result_autoriza_cierre[0]['AUTORIZA_MENU'];
		
		if($autoriza_cierre == 'E'){
			$this->dws['dw_nota_venta']->set_item(0,'BOTON_CIERRE_SIN_P','');
		}				
		
		//Validacion de caso especial en modificacion de porcentaje por MH
		$sql="SELECT PORC_DESCUENTO_PERMITIDO
			  FROM USUARIO
			  WHERE COD_USUARIO = ".$this->cod_usuario;
		$result = $db->build_results($sql);				
		
		$porc_desc_permitido = $result[0]['PORC_DESCUENTO_PERMITIDO'];
		$monto_desc1 = $this->dws['dw_nota_venta']->get_item(0, 'MONTO_DSCTO1');
		$monto_desc2 = $this->dws['dw_nota_venta']->get_item(0, 'MONTO_DSCTO2');
		$sum_total = $this->dws['dw_nota_venta']->get_item(0, 'SUM_TOTAL');
		
		$porc_desc_total = ((($monto_desc1 + $monto_desc2) * 100) / $sum_total);
		
		if($porc_desc_total > $porc_desc_permitido){
			$this->dws['dw_nota_venta']->controls['PORC_DSCTO1']->readonly = true;
			$this->dws['dw_nota_venta']->controls['PORC_DSCTO2']->readonly = true;
			$this->dws['dw_nota_venta']->controls['MONTO_DSCTO1']->readonly = true;
			$this->dws['dw_nota_venta']->controls['MONTO_DSCTO2']->readonly = true;
		}else{
			$this->dws['dw_nota_venta']->controls['PORC_DSCTO1']->readonly = false;
			$this->dws['dw_nota_venta']->controls['PORC_DSCTO2']->readonly = false;
			$this->dws['dw_nota_venta']->controls['MONTO_DSCTO1']->readonly = false;
			$this->dws['dw_nota_venta']->controls['MONTO_DSCTO2']->readonly = false;
		}
		//////////////////////////////////////////////////////////////////
		
		for($j=0 ; $j < $this->dws['dw_docs']->row_count(); $j++){
			$es_oc = $this->dws['dw_docs']->get_item($j, 'D_ES_OC');
			if($es_oc == 'S')
				$this->dws['dw_docs']->set_item($j, 'D_VALUE_OPTION', 'S');
			else
				$this->dws['dw_docs']->set_item($j, 'D_VALUE_OPTION', 'N');
		}
		
		if (session::is_set('DESDE_wo_inf_backcharge')){
			session::un_set('DESDE_wo_inf_backcharge');
			//$this->b_print_visible 	 = true;
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			$this->b_delete_visible  = false;
		}
		if($this->desde_wo_inf_por_despachar){	
			//$this->b_print_visible 	 = false;
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
		}
		$COD_INGRESO_PAGO = $this->dws['dw_nota_venta']->get_item(0, 'COD_INGRESO_PAGO');
		if($COD_INGRESO_PAGO != ''){
		    $this->dws['dw_nota_venta']->set_item(0, 'LAST_USUARIO_MOD', 'DESDE INGRESO DE PAGO N°'.$COD_INGRESO_PAGO);
		}
		
		
		//15-11-2019 VM+MH
		//si la NV no es una de las que debe procesar a la antigua, reasigna computed parab el calclo de MONTO_DSCTO_CORPORATIVO
		if ($COD_NOTA_VENTA==88860 ||
			$COD_NOTA_VENTA==88196 ||
			$COD_NOTA_VENTA==88194 ||
			$COD_NOTA_VENTA==87985 ||
			$COD_NOTA_VENTA==87918 ||
			$COD_NOTA_VENTA==87397 ||
			$COD_NOTA_VENTA==87198 ||
			$COD_NOTA_VENTA==87134 ||
			$COD_NOTA_VENTA==85388 ||
			$COD_NOTA_VENTA==85335 ||
			$COD_NOTA_VENTA==85334 ||
			$COD_NOTA_VENTA==85126 ||
			$COD_NOTA_VENTA==84698 ||
			$COD_NOTA_VENTA==84086 ||
			$COD_NOTA_VENTA==82126)
			$this->dws['dw_nota_venta']->set_computed('STATIC_MONTO_DSCTO_CORPORATIVO', '([TOTAL_NETO] * [PORC_DSCTO_CORPORATIVO])/ 100');		
	}
	function redraw() {
		parent::redraw();

		$COD_ESTADO_NOTA_VENTA = $this->dws['dw_nota_venta']->get_item(0, 'COD_ESTADO_NOTA_VENTA_H');
		// En el tab de compras parte en OC y no en preorden
		if ($COD_ESTADO_NOTA_VENTA != self::K_ESTADO_EMITIDA)
			print '<script type="text/javascript">TabbedPanels2.showPanel(1);</script>';
	}
	function get_key() {
		return $this->dws['dw_nota_venta']->get_item(0, 'COD_NOTA_VENTA');
	}
	
	function save_record($db) {
		//$db->debug=1;
		
		$COD_NOTA_VENTA = $this->get_key();	
		$FECHA_NOTA_VENTA = $this->dws['dw_nota_venta']->get_item(0, 'FECHA_NOTA_VENTA');
		$COD_USUARIO = $this->dws['dw_nota_venta']->get_item(0, 'COD_USUARIO');
		$NRO_ORDEN_COMPRA = $this->dws['dw_nota_venta']->get_item(0, 'NRO_ORDEN_COMPRA');
		$FECHA_ORDEN_COMPRA_CLIENTE = $this->dws['dw_nota_venta']->get_item(0, 'FECHA_ORDEN_COMPRA_CLIENTE');
		$CENTRO_COSTO_CLIENTE = $this->dws['dw_nota_venta']->get_item(0, 'CENTRO_COSTO_CLIENTE');
		$COD_COTIZACION = $this->dws['dw_nota_venta']->get_item(0, 'COD_COTIZACION');
		$COD_ESTADO_NOTA_VENTA = $this->dws['dw_nota_venta']->get_item(0, 'COD_ESTADO_NOTA_VENTA_H');
		$COD_MONEDA = $this->dws['dw_nota_venta']->get_item(0, 'COD_MONEDA');
		$VALOR_TIPO_CAMBIO = $this->dws['dw_nota_venta']->get_item(0, 'VALOR_TIPO_CAMBIO');
		$COD_USUARIO_VENDEDOR1 = $this->dws['dw_nota_venta']->get_item(0, 'COD_USUARIO_VENDEDOR1');
		$PORC_VENDEDOR1 = $this->dws['dw_nota_venta']->get_item(0, 'PORC_VENDEDOR1');
		$COD_USUARIO_VENDEDOR2 = $this->dws['dw_nota_venta']->get_item(0, 'COD_USUARIO_VENDEDOR2');
		$PORC_VENDEDOR2 = $this->dws['dw_nota_venta']->get_item(0, 'PORC_VENDEDOR2');
		$COD_ORIGEN_VENTA = "null";
		$COD_CUENTA_CORRIENTE = $this->dws['dw_nota_venta']->get_item(0, 'COD_CUENTA_CORRIENTE');
		$REFERENCIA = $this->dws['dw_nota_venta']->get_item(0, 'REFERENCIA');
		$REFERENCIA = str_replace("'", "''", $REFERENCIA);
		$COD_EMPRESA = $this->dws['dw_nota_venta']->get_item(0, 'COD_EMPRESA');
		$COD_SUCURSAL_DESPACHO = $this->dws['dw_nota_venta']->get_item(0, 'COD_SUCURSAL_DESPACHO');
		$COD_SUCURSAL_FACTURA = $this->dws['dw_nota_venta']->get_item(0, 'COD_SUCURSAL_FACTURA');
		$COD_PERSONA = $this->dws['dw_nota_venta']->get_item(0, 'COD_PERSONA');
		$MOTIVO_ANULA = $this->dws['dw_nota_venta']->get_item(0, 'MOTIVO_ANULA');
		$MOTIVO_ANULA = str_replace("'", "''", $MOTIVO_ANULA);
		$COD_USUARIO_ANULA = $this->dws['dw_nota_venta']->get_item(0, 'COD_USUARIO_ANULA');
		$FECHA_ENTREGA = $this->dws['dw_nota_venta']->get_item(0, 'FECHA_ENTREGA');
		$OBS_DESPACHO = $this->dws['dw_nota_venta']->get_item(0, 'OBS_DESPACHO');
		$OBS_DESPACHO = str_replace("'", "''", $OBS_DESPACHO);
		$OBS = $this->dws['dw_nota_venta']->get_item(0, 'OBS');
		$OBS = str_replace("'", "''", $OBS);
		$SUBTOTAL = $this->dws['dw_nota_venta']->get_item(0, 'SUM_TOTAL');
		$PORC_DSCTO1 = $this->dws['dw_nota_venta']->get_item(0, 'PORC_DSCTO1');
		$MONTO_DSCTO1 = $this->dws['dw_nota_venta']->get_item(0, 'MONTO_DSCTO1');
		$PORC_DSCTO2 = $this->dws['dw_nota_venta']->get_item(0, 'PORC_DSCTO2');
		$MONTO_DSCTO2 = $this->dws['dw_nota_venta']->get_item(0, 'MONTO_DSCTO2');
		$PORC_IVA = $this->dws['dw_nota_venta']->get_item(0, 'PORC_IVA');
		$AUTORIZA_PROCESAR_VENTA = $this->dws['dw_nota_venta']->get_item(0, 'AUTORIZA_PROCESAR_VENTA');
		
		$MONTO_IVA = $this->dws['dw_nota_venta']->get_item(0, 'MONTO_IVA');
		$TOTAL_CON_IVA = $this->dws['dw_nota_venta']->get_item(0, 'TOTAL_CON_IVA');
		$TOTAL_NETO = $this->dws['dw_nota_venta']->get_item(0, 'TOTAL_NETO');
		$INGRESO_USUARIO_DSCTO1 = $this->dws['dw_nota_venta']->get_item(0, 'INGRESO_USUARIO_DSCTO1');
		$INGRESO_USUARIO_DSCTO2 = $this->dws['dw_nota_venta']->get_item(0, 'INGRESO_USUARIO_DSCTO2');
		$PORC_DSCTO_CORPORATIVO = $this->dws['dw_nota_venta']->get_item(0, 'PORC_DSCTO_CORPORATIVO_H');
		$NO_TIENE_OC			= $this->dws['dw_nota_venta']->get_item(0, 'NO_TIENE_OC');
		
		$CIERRE_H			= $this->dws['dw_nota_venta']->get_item(0, 'CIERRE_H');
		$CIERRE_SIN_P_H		= $this->dws['dw_nota_venta']->get_item(0, 'CIERRE_SIN_P_H');
		$MOTIVO_CIERRE_SIN_PART_H		= $this->dws['dw_nota_venta']->get_item(0, 'MOTIVO_CIERRE_SIN_PART_H');
		$CREADA_EN_SV = $this->dws['dw_nota_venta']->get_item(0, 'CREADA_EN_SV');
		if($CIERRE_SIN_P_H == 'N'){
			$MOTIVO_CIERRE_SIN_PART_H = "null";
		}else{
			$MOTIVO_CIERRE_SIN_PART_H = "'$MOTIVO_CIERRE_SIN_PART_H'";
			
		}
		$REBAJA = $this->dws['dw_nota_venta']->get_item(0, 'REBAJA');
		
		$FECHA_PLAZO_CIERRE = $this->dws['dw_nota_venta']->get_item(0, 'FECHA_PLAZO_CIERRE');
		$COD_USUARIO_CONFIRMA = $this->dws['dw_nota_venta']->get_item(0, 'COD_USUARIO_CONFIRMA_H');
		
		
		if (($COD_ESTADO_NOTA_VENTA == self::K_ESTADO_CONFIRMADA) && ($COD_USUARIO_CONFIRMA == ''))// se confirma
			$COD_USUARIO_CONFIRMA		= $this->cod_usuario;
		else
			$COD_USUARIO_CONFIRMA		= "null";
		
		if ($CIERRE_H == 'S' ){ // se da clic en boton cerrar
			$COD_USUARIO_CIERRE = $this->cod_usuario;
		}elseif($CIERRE_SIN_P_H == 'S'){
			$COD_USUARIO_CIERRE = $this->cod_usuario;
		}else{
			$COD_USUARIO_CIERRE			= "null";
		}	
		if (($MOTIVO_ANULA != '') && ($COD_USUARIO_ANULA == '')) // se anula 
			$COD_USUARIO_ANULA			= $this->cod_usuario;
		else
			$COD_USUARIO_ANULA			= "null";

		$NRO_ORDEN_COMPRA = ($NRO_ORDEN_COMPRA =='') ? "null" : "'$NRO_ORDEN_COMPRA'";
		$FECHA_ORDEN_COMPRA_CLIENTE = $this->str2date($FECHA_ORDEN_COMPRA_CLIENTE);
		$CENTRO_COSTO_CLIENTE = ($CENTRO_COSTO_CLIENTE =='') ? "null" : "'$CENTRO_COSTO_CLIENTE'";
		$COD_COTIZACION	= ($COD_COTIZACION =='') ? "null" : $COD_COTIZACION;
		$COD_USUARIO_VENDEDOR2 = ($COD_USUARIO_VENDEDOR2 =='') ? "null" : $COD_USUARIO_VENDEDOR2;
		$PORC_VENDEDOR2	= ($PORC_VENDEDOR2 =='') ? "null" : $PORC_VENDEDOR2;		
		$COD_CUENTA_CORRIENTE	= ($COD_CUENTA_CORRIENTE =='') ? "null" : $COD_CUENTA_CORRIENTE;
		$OBS_DESPACHO = ($OBS_DESPACHO =='') ? "null" : "'$OBS_DESPACHO'";
		$OBS = ($OBS =='') ? "null" : "'$OBS'";
		$MOTIVO_ANULA = ($MOTIVO_ANULA =='') ? "null" : "'$MOTIVO_ANULA'";
		$INGRESO_USUARIO_DSCTO1 = ($INGRESO_USUARIO_DSCTO1 =='') ? "null" : "'$INGRESO_USUARIO_DSCTO1'";
		$INGRESO_USUARIO_DSCTO2 = ($INGRESO_USUARIO_DSCTO2 =='') ? "null" : "'$INGRESO_USUARIO_DSCTO2'";
		$COD_ORIGEN_VENTA = ($COD_ORIGEN_VENTA =='') ? "null" : $COD_ORIGEN_VENTA;		
		
		$SUBTOTAL = ($SUBTOTAL == '' ? 0: "$SUBTOTAL");
		$PORC_DSCTO1 = ($PORC_DSCTO1 == '' ? 0: "$PORC_DSCTO1");
		$MONTO_DSCTO1 = ($MONTO_DSCTO1 == '' ? 0: "$MONTO_DSCTO1");
		$PORC_DSCTO2 = ($PORC_DSCTO2 == '' ? 0: "$PORC_DSCTO2");
		$MONTO_DSCTO2 = ($MONTO_DSCTO2 == '' ? 0: "$MONTO_DSCTO2");
		$PORC_IVA = ($PORC_IVA == '' ? 0: "$PORC_IVA");
		$MONTO_IVA = ($MONTO_IVA == '' ? 0: "$MONTO_IVA");
		$TOTAL_CON_IVA = ($TOTAL_CON_IVA == '' ? 0: "$TOTAL_CON_IVA");
		$TOTAL_NETO = ($TOTAL_NETO == '' ? 0: "$TOTAL_NETO");
		$PORC_DSCTO_CORPORATIVO = ($PORC_DSCTO_CORPORATIVO == '' ? 0: "$PORC_DSCTO_CORPORATIVO");
		$REBAJA = ($REBAJA == '' ? 0: "$REBAJA");
		$AUTORIZA_PROCESAR_VENTA = ($AUTORIZA_PROCESAR_VENTA == '' ? 'N' : "'$AUTORIZA_PROCESAR_VENTA'");
		
		$COD_FORMA_PAGO = $this->dws['dw_nota_venta']->get_item(0, 'COD_FORMA_PAGO');
		if ($COD_FORMA_PAGO==1){ // forma de pago = OTRO
			$NOM_FORMA_PAGO_OTRO= $this->dws['dw_nota_venta']->get_item(0, 'NOM_FORMA_PAGO_OTRO');
			$CANTIDAD_DOC_FORMA_PAGO_OTRO= $this->dws['dw_nota_venta']->get_item(0, 'CANTIDAD_DOC_FORMA_PAGO_OTRO');
			
		}else{
			$NOM_FORMA_PAGO_OTRO= "";
			$CANTIDAD_DOC_FORMA_PAGO_OTRO= "";
		}
		$NOM_FORMA_PAGO_OTRO= ($NOM_FORMA_PAGO_OTRO =='') ? "null" : "'$NOM_FORMA_PAGO_OTRO'";
		$CANTIDAD_DOC_FORMA_PAGO_OTRO= ($CANTIDAD_DOC_FORMA_PAGO_OTRO =='') ? "null" : "$CANTIDAD_DOC_FORMA_PAGO_OTRO";
		
		$COD_NOTA_VENTA = ($COD_NOTA_VENTA=='') ? "null" : $COD_NOTA_VENTA;		
    	
		/*MH 14:59 25/06/2018 = Cuando estos rut estén con el vendedor1: CARLOS URTUBIA, el % siempre el procentaje del vendedor sera del 15%*/
		if($COD_USUARIO_VENDEDOR1 == 6){
			$RUT = $this->dws['dw_nota_venta']->get_item(0, 'RUT');
			if($RUT == '76178360' || $RUT == '88279900' || $RUT == '96945020' || $RUT == '76178390' || $RUT == '76117696')
				$PORC_VENDEDOR1 = 15;
		/*MH 11:49 24/07/2018 = Cuando estos rut estén con el vendedor1: RODRIGO BARRAZA, el procentaje del vendedor sera del 10%*/
		}
		if($COD_USUARIO_VENDEDOR1 == 11){
			$RUT = $this->dws['dw_nota_venta']->get_item(0, 'RUT');
			if($RUT == '76178360' || $RUT == '88279900' || $RUT == '96945020' || $RUT == '76178390' || $RUT == '76117696')
				$PORC_VENDEDOR1 = 10;
		}

		
		$sp = 'spu_nota_venta';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
		$param	= "	'$operacion',
					$COD_NOTA_VENTA,
					'$FECHA_NOTA_VENTA',
					$COD_USUARIO,  
					$COD_ESTADO_NOTA_VENTA, 
					$NRO_ORDEN_COMPRA,
					$FECHA_ORDEN_COMPRA_CLIENTE,
					$CENTRO_COSTO_CLIENTE, 
					$COD_MONEDA, 
					$VALOR_TIPO_CAMBIO, 
					$COD_COTIZACION,
					$COD_USUARIO_VENDEDOR1,
					$PORC_VENDEDOR1,
					$COD_USUARIO_VENDEDOR2,
					$PORC_VENDEDOR2,
					$COD_CUENTA_CORRIENTE,
					$COD_ORIGEN_VENTA,
					'$REFERENCIA',
					$COD_EMPRESA,
					$COD_SUCURSAL_DESPACHO,
					$COD_SUCURSAL_FACTURA,
					$COD_PERSONA,
					$SUBTOTAL,
					$PORC_DSCTO1,
					$MONTO_DSCTO1,
					$PORC_DSCTO2,
					$MONTO_DSCTO2,
					$PORC_IVA,
					$MONTO_IVA,
					$TOTAL_CON_IVA,
					'$FECHA_ENTREGA',
					$OBS_DESPACHO,
					$OBS,	
					$COD_FORMA_PAGO,
					$MOTIVO_ANULA,
					$COD_USUARIO_ANULA,
					$TOTAL_NETO, 
					$INGRESO_USUARIO_DSCTO1,
					$INGRESO_USUARIO_DSCTO2,
					$NOM_FORMA_PAGO_OTRO,
					$CANTIDAD_DOC_FORMA_PAGO_OTRO,
					$PORC_DSCTO_CORPORATIVO,
					$COD_USUARIO_CIERRE,
					'$FECHA_PLAZO_CIERRE',
					$COD_USUARIO_CONFIRMA,
					$MOTIVO_CIERRE_SIN_PART_H,
					'$CIERRE_SIN_P_H',
					'$CREADA_EN_SV'
					,$REBAJA
					,$AUTORIZA_PROCESAR_VENTA
					,'$NO_TIENE_OC'";
							
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				//$COD_NOTA_VENTA = $db->GET_IDENTITY();
				$sql = "select max(COD_NOTA_VENTA) COD_NOTA_VENTA from NOTA_VENTA";
				$result = $db->build_results($sql);
				$COD_NOTA_VENTA = $result[0]['COD_NOTA_VENTA'];
				/////
				
				$this->dws['dw_nota_venta']->set_item(0, 'COD_NOTA_VENTA', $COD_NOTA_VENTA);
				$this->f_envia_mail('EMITIDO');//$operacion, $COD_ESTADO_NOTA_VENTA, $COD_NOTA_VENTA);
			}
		
			if($COD_USUARIO_CONFIRMA != "null")
				$this->f_envia_mail('CONFIRMADO');
				
			if (($MOTIVO_ANULA != 'null') && ($COD_USUARIO_ANULA != 'null')){ // se anula 
				$this->f_envia_mail('ANULADA');
			}
			for ($i=0; $i<$this->dws['dw_item_nota_venta']->row_count(); $i++) 
				$this->dws['dw_item_nota_venta']->set_item($i, 'COD_NOTA_VENTA', $COD_NOTA_VENTA);
			if (!$this->dws['dw_item_nota_venta']->update($db, $this->dws['dw_pre_orden_compra'])) return false;

			if (!$this->dws['dw_nv_item_od_ws']->update($db)) return false;
			
			$parametros_sp = "'item_nota_venta','nota_venta',$COD_NOTA_VENTA";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp)) return false;
			
			$cod_llamado	= $this->dws['dw_llamado']->get_item(0, 'LL_COD_LLAMADO');
			$parametros_sp="'REALIZADO_WEB'
							,$cod_llamado
							,null
							,null
							,null
							,null
							,null
							,null
							,'S'
							,null
							,'NOTA VENTA'
							,$COD_NOTA_VENTA
							,$this->cod_usuario";
			
			if($cod_llamado <> ''){
		
				if (!$db->EXECUTE_SP('spu_llamado', $parametros_sp)){
						  return false;
				}else{
					$param="'INSERT'
							,NULL
							,$cod_llamado
							,NULL
							,'realizado con exito'
							,'S'
							,'N'";

					if (!$db->EXECUTE_SP('spu_llamado_conversa', $param))
						return false;						
				}
			}							

			for ($i=0; $i<$this->dws['dw_doc_nota_venta']->row_count(); $i++)
				$this->dws['dw_doc_nota_venta']->set_item($i, 'COD_NOTA_VENTA', $COD_NOTA_VENTA);

			if (!$this->dws['dw_doc_nota_venta']->update($db)) return false;
					
			if (!$this->dws['dw_tipo_pendiente_nota_venta']->update($db)) return false;

			if (!$this->dws['dw_docs']->update($db, $COD_NOTA_VENTA)) return false;

			$COD_USUARIO_CONFIRMA = $this->dws['dw_nota_venta']->get_item(0, 'COD_USUARIO_CONFIRMA_H');
			if (($COD_ESTADO_NOTA_VENTA == self::K_ESTADO_CONFIRMADA) && ($COD_USUARIO_CONFIRMA == '')){// se confirma
				$parametros_sp = "$COD_NOTA_VENTA";
				if (!$db->EXECUTE_SP('sp_nv_crea_orden_compra', $parametros_sp)) return false;
			}
					
			$parametros_sp = "'RECALCULA',$COD_NOTA_VENTA";   
            if (!$db->EXECUTE_SP('spu_nota_venta', $parametros_sp))
                return false;
			
            if (!$this->dws['dw_pre_orden_compra']->update($db, $COD_NOTA_VENTA)) return false;    
            
            $result_nv_nc = $this->dws['dw_nota_venta']->get_item(0, 'RESULT_NV_NC');
            
            if($result_nv_nc <> ''){
            	$reg = explode("|", $result_nv_nc);
            	
            	for($l=0 ; $l < count($reg) ; $l++){
            		$col = explode(",", $reg[$l]);
            		
            		$cod_nv_nc	= $col[0];
            		$seleccion	= $col[1];
            		$tot_con	= $col[2];
            		
            		$sql	= "SELECT SELECCION			SELECCION_OLD
            						 ,TOTAL_CONSIDERADO	TOTAL_CONSIDERADO_OLD
            				   FROM NOTA_VENTA_NC
            				   WHERE COD_NOTA_VENTA_NC = $cod_nv_nc"; 
					$result = $db->build_results($sql);	
            		$SELECCION_OLD			= $result[0]['SELECCION_OLD'];
            		$TOTAL_CONSIDERADO_OLD	= $result[0]['TOTAL_CONSIDERADO_OLD'];
					
					if(($seleccion <> $SELECCION_OLD) || ($tot_con <> $TOTAL_CONSIDERADO_OLD)){
	            		$parametros_sp = "'UPDATE'
										,$cod_nv_nc
										,$tot_con
										,'$seleccion'";
									
						if(!$db->EXECUTE_SP('spu_nota_venta_nc', $parametros_sp))
	                		return false;
						
		            	if (!$db->EXECUTE_SP("sp_log_cambio", "'NOTA_VENTA', '$COD_NOTA_VENTA',".$this->cod_usuario.", 'U'"))
							return false;
						          
						$COD_LOG_CAMBIO = $db->GET_IDENTITY('LOG_CAMBIO');
							
						if($seleccion <> $SELECCION_OLD){
							$OLD_VALUE		= $SELECCION_OLD;
							$VALUE			= $seleccion;
							$FIELD_VALUE	= 'SELECCION';
						}else{
							$OLD_VALUE		= $TOTAL_CONSIDERADO_OLD;
							$VALUE			= $tot_con;
							$FIELD_VALUE	= 'TOTAL_CON_IVA';
						}
						
						$param = "$COD_LOG_CAMBIO
								 ,'NOTA_VENTA_NC'
								 ,'$FIELD_VALUE'
								 ,'$cod_nv_nc'
								 ,'$OLD_VALUE'
								 ,'$VALUE'
								 ,'U'";				
						
						if (!$db->EXECUTE_SP("sp_detalle_cambio_relacionada", $param))
							return false;
					}		
					
            	}
            }
            
            $param="'CREA_PARTICIPACION'
							,$COD_NOTA_VENTA
							,NULL
							,$COD_USUARIO_CIERRE";

			if(($COD_ESTADO_NOTA_VENTA == self::K_ESTADO_CONFIRMADA)&&($COD_USUARIO_CIERRE != 'null')&&($CIERRE_SIN_P_H == 'N')){
            	if (!$db->EXECUTE_SP($sp, $param))
							return false;
            }
            
            return true;
		}
		return false;	
					
	}
	function creada_desde($ve_result){
		$array = explode("|", $ve_result);
		$cod_cotizacion = $array[0];
		$que_precio_usa = $array[1];	
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$sql_crea_desde = "select null COD_NOTA_VENTA
					,null CREADA_EN_SV
					,NULL PORC_DESC_PERMITIDO
					,convert(nvarchar, getdate(), 103) FECHA_NOTA_VENTA
					,".$this->cod_usuario." COD_USUARIO
					,U.NOM_USUARIO
					,null NRO_ORDEN_COMPRA					
					,null CENTRO_COSTO_CLIENTE
					,".$cod_cotizacion." COD_COTIZACION
					,".self::K_ESTADO_EMITIDA." COD_ESTADO_NOTA_VENTA
					,".self::K_ESTADO_EMITIDA." COD_ESTADO_NOTA_VENTA_H	
					,'EMITIDA' NOM_ESTADO_NOTA_VENTA		
					,COD_MONEDA
					,'none' DISPLAY_DESCARGA
					,1 VALOR_TIPO_CAMBIO 
					-- vendedor 1
					,'' LL_LLAMADO
					,".$this->cod_usuario." COD_USUARIO_VENDEDOR1
					,(select PORC_PARTICIPACION from USUARIO where COD_USUARIO = ".$this->cod_usuario." and ES_VENDEDOR = 'S') PORC_VENDEDOR1
					--vendedor 2
					,NULL COD_USUARIO_VENDEDOR2
					,NULL PORC_VENDEDOR2
					,0 COD_ORIGEN_VENTA
					,CTA.COD_CUENTA_CORRIENTE
					,CTA.NOM_CUENTA_CORRIENTE 
					,CTA.NRO_CUENTA_CORRIENTE 
					,REFERENCIA
					,C.COD_EMPRESA
					,E.ALIAS
					,E.RUT
					,'N' CIERRE_SIN_P_H
					,'N' MOTIVO_CIERRE_SIN_PART_H
					,E.DIG_VERIF
					,E.NOM_EMPRESA
					,E.GIRO
					,COD_SUCURSAL_DESPACHO
					,dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL_DESPACHO, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_DESPACHO
					,COD_SUCURSAL_FACTURA
					,dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL_FACTURA, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA
					,COD_PERSONA
					,dbo.f_emp_get_mail_cargo_persona(COD_PERSONA,  '[EMAIL] - [NOM_CARGO]') MAIL_CARGO_PERSONA
					,null FECHA_ENTREGA
					,null OBS_DESPACHO
					,null OBS
					,'' FECHA_PLAZO_CIERRE
					,0 CANT_CAMBIO_FECHA_PLAZO_CIERRE
					,SUBTOTAL SUM_TOTAL
					,PORC_DSCTO1
					,MONTO_DSCTO1 
					,PORC_DSCTO2 
					,MONTO_DSCTO2 
					,TOTAL_NETO
					,TOTAL_NETO STATIC_TOTAL_NETO
					,TOTAL_NETO STATIC_TOTAL_NETO2
					,PORC_IVA
					,MONTO_IVA 
					,TOTAL_CON_IVA   
					,TOTAL_CON_IVA STATIC_TOTAL_CON_IVA
					,TOTAL_CON_IVA STATIC_TOTAL_CON_IVA2
					,0 TOTAL_PAGO	
					,TOTAL_CON_IVA TOTAL_POR_PAGAR
					,0 COD_FORMA_PAGO
					,null NOM_FORMA_PAGO_OTRO
					,0 CANTIDAD_DOC_FORMA_PAGO_OTRO
					,'P' INGRESO_USUARIO_DSCTO1
					,'P' INGRESO_USUARIO_DSCTO2
					,V1.PORC_DESCUENTO_PERMITIDO PORC_DSCTO_MAX
					,0 EMITIDA_HACE
					,(select dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate()) from EMPRESA where COD_EMPRESA = C.COD_EMPRESA) PORC_DSCTO_CORPORATIVO
					,(select dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate()) from EMPRESA where COD_EMPRESA = C.COD_EMPRESA) PORC_DSCTO_CORPORATIVO_H							
					-- RESULTADOS
					-- MONTO_DSCTO_CORPORATIVO = TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100
					-- SUM_OC_TOTAL = 0
					-- MONTO_GASTO_FIJO = (TOTAL_NETO - (TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)) * (dbo.f_get_parametro_porc('GF', getdate())/100)
					/*
					-- RESULTADO =
					TOTAL_NETO -
					(TOTAL_NETO - (TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)) * (dbo.f_get_parametro_porc('GF', getdate())/100) -
					(TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)
					*/
				
					-- VENTA_NETA = TOTAL_NETO - MONTO_DSCTO_CORPORATIVO
					,(TOTAL_NETO - (TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)) VENTA_NETA
					
					-- MONTO_DSCTO_CORPORATIVO = TOTAL_NETO * PORC_DSCTO_CORPORATIVO
					,(TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100) MONTO_DSCTO_CORPORATIVO	
				
					-- PORC_AA, PORC_GF, PORC_GV, PORC%ADM
					,dbo.f_get_parametro_porc('AA', getdate())PORC_AA
					,dbo.f_get_parametro_porc('GF', getdate())PORC_GF
					,dbo.f_get_parametro_porc('GV', getdate())PORC_GV
					,dbo.f_get_parametro_porc('ADM', getdate())PORC_ADM
					
					--MONTO_DIRECTORIO = PORC_AA * RESULTADO
					,(dbo.f_get_parametro_porc('AA', getdate())/100) *
					(TOTAL_NETO -
					(TOTAL_NETO - (TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)) * (dbo.f_get_parametro_porc('GF', getdate())/100) -
					(TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)) MONTO_DIRECTORIO
					
					-- MONTO_GASTO_FIJO = (TOTAL_NETO - MONTO_DSCTO_CORPORATIVO) * PORC_GF/100
					,(TOTAL_NETO - (TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)) * (dbo.f_get_parametro_porc('GF', getdate())/100) MONTO_GASTO_FIJO
				
					-- SUM_OC_TOTAL
					,0 SUM_OC_TOTAL
					,0 SUM_OC_TOTAL_NETA
				
					-- RESULTADO
					,(TOTAL_NETO -
					(TOTAL_NETO - (TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)) * (dbo.f_get_parametro_porc('GF', getdate())/100) -
					(TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)) RESULTADO
				
					-- STATIC_RESULTADO
					,(TOTAL_NETO -
					(TOTAL_NETO - (TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)) * (dbo.f_get_parametro_porc('GF', getdate())/100) -
					(TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)) STATIC_RESULTADO
					
					-- PORC_RESULTADO = (RESULTADO / (TOTAL_NETO - MONTO_DSCTO_CORPORATIVO - SUM_NC)) * 100
					,(((TOTAL_NETO -
					(TOTAL_NETO - (TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)) * (dbo.f_get_parametro_porc('GF', getdate())/100) -
					(TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)) /
					(TOTAL_NETO -
					(TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100))) * 100) PORC_RESULTADO
				
					-- STATIC_PORC_RESULTADO
					,(((TOTAL_NETO -
					(TOTAL_NETO - (TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)) * (dbo.f_get_parametro_porc('GF', getdate())/100) -
					(TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)) /
					(TOTAL_NETO -
					(TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100))) * 100) STATIC_PORC_RESULTADO
				
					-- COMISION_V1 = (PORC_VENDEDOR1/100) * RESULTADO
					,((PORC_VENDEDOR1/100)*
					(TOTAL_NETO -
					(TOTAL_NETO - (TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)) * (dbo.f_get_parametro_porc('GF', getdate())/100) -
					(TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100))) COMISION_V1
								
					-- COMISION_V2 = (PORC_VENDEDOR2/100) * RESULTADO
					,((PORC_VENDEDOR2/100)*
					(TOTAL_NETO -
					(TOTAL_NETO - (TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)) * (dbo.f_get_parametro_porc('GF', getdate())/100) -
					(TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100))) COMISION_V2
				
					-- COMISION_GV = (PORC_GV/100) * RESULTADO
					,((dbo.f_get_parametro_porc('GV', getdate())/100) *
					(TOTAL_NETO -
					(TOTAL_NETO - (TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)) * (dbo.f_get_parametro_porc('GF', getdate())/100) -
					(TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100))) COMISION_GV
				
					-- COMISION_ADM = (PORC_ADM/100) * RESULTADO
					,((dbo.f_get_parametro_porc('ADM', getdate())/100) *
					(TOTAL_NETO -
					(TOTAL_NETO - (TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100)) * (dbo.f_get_parametro_porc('GF', getdate())/100) -
					(TOTAL_NETO * dbo.f_get_porc_dscto_corporativo_empresa(C.COD_EMPRESA, getdate())/100))) COMISION_ADM
					
					-- VENDEDOR1
					,(select NOM_USUARIO from USUARIO USU where USU.COD_USUARIO = C.COD_USUARIO_VENDEDOR1) VENDEDOR1
					-- VENDEDOR2
					,(select NOM_USUARIO from USUARIO USU where USU.COD_USUARIO = C.COD_USUARIO_VENDEDOR2) VENDEDOR2
					-- GTE_VTA
					,dbo.f_get_parametro(".self::K_PARAM_GTE_VTA.") GTE_VTA
					--,dbo.f_get_parametro(16) GTE_VTA
					
					-- no modificable en tab de resultados la comision del vendedor
					,PORC_VENDEDOR1 PORC_VENDEDOR1_R  
					,PORC_VENDEDOR2 PORC_VENDEDOR2_R
					,dbo.f_get_parametro(".self::K_PARAM_RANGO_DOC_NOTA_VENTA.") RANGO_DOC_NOTA_VENTA
					,0 TOTAL_MONTO_DOC
					,'N' CAMBIA_DSCTO_CORPORATIVO
					-- despachado
					,0 PORC_GD
					-- facturado
					,0 PORC_FACTURA
					-- pagado
					,0 PORC_PAGOS
					-- historial de modificacion descto. corporativo
					,0 CANT_CAMBIO_PORC_DESCTO_CORP
					-- datos cierre NV
					,null FECHA_CIERRE
					,null COD_USUARIO_CIERRE
					,'none' TABLE_CIERRE_DISPLAY
					,'none' BOTON_CIERRE_DISPLAY
					,'none' TABLE_PENDIENTE_DISPLAY
					,'N' CIERRE_H
					,'N' AUTORIZA_CIERRE
					,'N' VALIDA_MOTIVO_CIERRE_H
					,'' ES_VENDEDOR 
					-- datos anulación
					,null FECHA_ANULA
					,null MOTIVO_ANULA
					,null COD_USUARIO_ANULA
					,'none' TR_DISPLAY_ANULADA
					, '' COD_USUARIO_CONFIRMA_H
					,null FECHA_CONFIRMA
					,'' TITULO_ESTADO_NOTA_VENTA
					,'none' DISPLAY_PREORDEN
					,null FECHA_ORDEN_COMPRA_CLIENTE
					,case 
						when (C.INGRESO_USUARIO_DSCTO1='M' and C.MONTO_DSCTO1>0) then 'Descuento ingresado como monto'
						else ''
					end ETIQUETA_DESCT1
					,case 
						when (C.INGRESO_USUARIO_DSCTO2='M' and C.MONTO_DSCTO2>0)  then 'Descuento ingresado como monto'
						else ''
					end ETIQUETA_DESCT2
					,null COD_ESTADO_COTIZACION
					,'N' RULES_MODIFIED
					,'N' LOAD_RECORD_STATE
					,'none' DISPLAY_MSG_NO_INF
					,'N' ES_NO_SUMA_NV_INFORME
					,NULL NO_SUMA_NV_INFORME_MOTIVO
					,'NV NO DEBE SUMAR EN INFORMES' TXT_BTN_NV_INFORME
					,'disabled' DISABLED_BTN
					,NULL RESULT_NV_NC
					,'' BTN_LISTA_NC
					,0 REBAJA
					,0 SUMA_NC
					,'N' AUTORIZA_PROCESAR_VENTA
					,'N' NO_TIENE_OC
                    , 'none' D_AUTORIZADO
                    , 'none' D_NO_AUTORIZADO
				from COTIZACION C, USUARIO U, EMPRESA E, CUENTA_CORRIENTE CTA, USUARIO V1
				WHERE C.COD_COTIZACION = ".$cod_cotizacion." and
					U.COD_USUARIO = ".$this->cod_usuario." and
					E.COD_EMPRESA = C.COD_EMPRESA and
					CTA.COD_CUENTA_CORRIENTE = dbo.f_emp_get_cta_cte(E.COD_EMPRESA)
					and V1.COD_USUARIO = C.COD_USUARIO_VENDEDOR1";
		
		$sql = $this->dws['dw_nota_venta']->get_sql();
		$this->dws['dw_nota_venta']->set_sql($sql_crea_desde);
		$this->dws['dw_nota_venta']->retrieve($cod_cotizacion);
		$this->dws['dw_nota_venta']->set_sql($sql);
		
		if(session::is_set('CREADA_DESDE_COTIZACION_COD_RECHAZADA')){
			$this->alert('La Cotización Nº '.$cod_cotizacion.' se encuentra en estado rechazada.\nAl crear una Nota de Venta desde la Cotización Nº '.$cod_cotizacion.', esta quedará en estado RE-ABIERTA.\nFavor considere esta situación.');
			$this->dws['dw_nota_venta']->set_item(0, 'COD_ESTADO_COTIZACION', session::get('CREADA_DESDE_COTIZACION_COD_RECHAZADA'));
			session::un_set('CREADA_DESDE_COTIZACION_COD_RECHAZADA');
		}
		
		$result = $db->build_results($sql_crea_desde);
		$this->dws['dw_nota_venta']->controls['COD_SUCURSAL_FACTURA']->retrieve($result[0]['COD_EMPRESA']);
		$this->dws['dw_nota_venta']->controls['COD_SUCURSAL_DESPACHO']->retrieve($result[0]['COD_EMPRESA']);
		$this->dws['dw_nota_venta']->controls['COD_PERSONA']->retrieve($result[0]['COD_EMPRESA']);
		
		$this->dws['dw_nota_venta']->controls['NOM_FORMA_PAGO_OTRO']->set_type('hidden');
		$this->dws['dw_nota_venta']->controls['CANTIDAD_DOC_FORMA_PAGO_OTRO']->set_type('hidden');
		
		// valida si el usuario puede modificar los desctos corporativos
		if ($this->tiene_privilegio_opcion(self::K_CAMBIA_DSCTO_CORPORATIVO)){
			$this->dws['dw_nota_venta']->add_control(new edit_porcentaje('PORC_DSCTO_CORPORATIVO'));
		}
		else{
			$this->dws['dw_nota_venta']->add_control(new static_num('PORC_DSCTO_CORPORATIVO', 1));
		}		

		// crea ítems, excepto los T
		//todos los campos que se agreguen en el select se deben agregar en función "dw_item_nota_venta"
		$sql_item_crea_desde = "select  ROW_NUMBER() OVER (ORDER BY ORDEN) - 1 - 100 COD_ITEM_NOTA_VENTA,
							null COD_NOTA_VENTA,
							ORDEN,
							ITEM,
							COD_PRODUCTO,
							COD_PRODUCTO COD_PRODUCTO_OLD,
							COD_PRODUCTO COD_PRODUCTO_H,
							NOM_PRODUCTO,
							CANTIDAD,
							PRECIO,
							null COD_TIPO_GAS,
							null COD_TIPO_ELECTRICIDAD,
							null MOTIVO,
							0 CANTIDAD_PRECOMPRA,			
							0 CANTIDAD_COMPRA,		
							COD_TIPO_TE,
							MOTIVO_TE,
							null PEND_AUTORIZA,
							null MOTIVO_AUTORIZA_TE,
							null FECHA_AUTORIZA_TE,
							null NOM_USUARIO_AUTORIZA_TE,
							null ENTRABLE_PRECIO,
							'S' IS_NEW
						from ITEM_COTIZACION
						where COD_COTIZACION = ".$cod_cotizacion."
						AND	COD_PRODUCTO <> 'T'
						order by ORDEN asc";
		
		$sql = $this->dws['dw_item_nota_venta']->get_sql();
		$this->dws['dw_item_nota_venta']->set_sql($sql_item_crea_desde);
		$this->dws['dw_item_nota_venta']->retrieve($cod_cotizacion);
		$this->dws['dw_item_nota_venta']->set_sql($sql);

		// Fuerza los items para que queden con status K_ROW_NEW_MODIFIED
		// de esta forma en $this->dws['dw_item_nota_venta']->update() se lee el identity de la BD y se asigna
					
		for ($i=0; $i<$this->dws['dw_item_nota_venta']->row_count(); $i++){
			$this->dws['dw_item_nota_venta']->set_status_row($i, K_ROW_NEW_MODIFIED);
			$cod_producto 	= $this->dws['dw_item_nota_venta']->get_item($i, 'COD_PRODUCTO');
			$precio			= $this->dws['dw_item_nota_venta']->get_item($i, 'PRECIO');													
			$result			= $db->build_results("select PRECIO_VENTA_PUBLICO, PRECIO_LIBRE from PRODUCTO where COD_PRODUCTO = '$cod_producto'");
			$precio_bd		= $result[0]['PRECIO_VENTA_PUBLICO'];
			
			// para los TE, E, I, etc Se los salta
			if ($result[0]['PRECIO_LIBRE']=='S') 
				continue;
				
			if($que_precio_usa == 1) {
				$this->dws['dw_item_nota_venta']->set_item($i, 'PRECIO', $precio_bd);
			}else{
				$this->dws['dw_item_nota_venta']->set_item($i, 'PRECIO', $precio);
			}										
		}
		$this->dws['dw_item_nota_venta']->calc_computed();
		
        $sql_preoc_crea_desde = "select  ROW_NUMBER() OVER (ORDER BY ORDEN) - 1 - 100 COD_ITEM_NOTA_VENTA,
										NULL COD_PRE_ORDEN_COMPRA,
										dbo.f_nv_get_first_proveedor (P.COD_PRODUCTO) COD_PROVEEDOR,
								        P.COD_PRODUCTO,
										IT.NOM_PRODUCTO,
								        CANTIDAD,
										dbo.f_prod_get_precio_costo (IT.COD_PRODUCTO, dbo.f_nv_get_first_proveedor (IT.COD_PRODUCTO), getdate()) PRECIO_COSTO,
								        PL.ES_COMPUESTO
								from ITEM_COTIZACION IT, PRODUCTO P ,PRODUCTO_LOCAL PL
								where COD_COTIZACION = ".$cod_cotizacion." AND 
										P.COD_PRODUCTO = IT.COD_PRODUCTO AND
										P.COD_PRODUCTO = PL.COD_PRODUCTO AND
										IT.COD_PRODUCTO <> 'T'
								order by ORDEN asc";
        
        $result = $db-> build_results($sql_preoc_crea_desde);

   		for($i=0; $i<count($result); $i++){
			if ($result[$i]['ES_COMPUESTO']== 'N'){
				$row = $this->dws['dw_pre_orden_compra']->insert_row();
				$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_ITEM', $i+1);
				$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_ORDEN', ($i+1)*10);
				$this->dws['dw_pre_orden_compra']->set_item($row, 'COD_PRE_ORDEN_COMPRA', $result[$i]['COD_PRE_ORDEN_COMPRA']);
				$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_GENERA_COMPRA', 'S');
				$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_COD_ITEM_NOTA_VENTA', $result[$i]['COD_ITEM_NOTA_VENTA']);
				$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_COD_PROVEEDOR', $result[$i]['COD_PROVEEDOR']);
				$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_COD_PRODUCTO', $result[$i]['COD_PRODUCTO']);
				$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_COD_PRODUCTO_H', $result[$i]['COD_PRODUCTO']);		
				$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_NOM_PRODUCTO', $result[$i]['NOM_PRODUCTO']);
				$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_CANTIDAD', $result[$i]['CANTIDAD']);
				$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_CANT_COMPUESTO_H', $result[$i]['CANTIDAD']);
				$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_PRECIO_COMPRA', $result[$i]['PRECIO_COSTO']);
				$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_PRECIO_COMPRA_H', $result[$i]['PRECIO_COSTO']);
				$this->dws['dw_pre_orden_compra']->set_item($row, 'MOTIVO_MOD_PRECIO', '');
				$total_compra = $result[$i]['PRECIO_COSTO'] * $result[$i]['CANTIDAD']; 
				$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_TOTAL', $total_compra);		
			}
			else{
				$cod_producto_cot  = $result[$i]['COD_PRODUCTO'];
				$cantidad_cot = $result[$i]['CANTIDAD'];
				
				$sql_preoc_crea_desde_comp = "select dbo.f_nv_get_first_proveedor (COD_PRODUCTO_HIJO) COD_PROVEEDOR,
													NULL COD_PRE_ORDEN_COMPRA,
													ORDEN,
											   		COD_PRODUCTO_HIJO,
													NOM_PRODUCTO,
													CANTIDAD,
											   		dbo.f_prod_get_precio_costo (COD_PRODUCTO_HIJO, dbo.f_nv_get_first_proveedor (COD_PRODUCTO_HIJO), getdate()) PRECIO_COSTO,
											   		GENERA_COMPRA							   	
											from PRODUCTO_COMPUESTO PC, PRODUCTO P
											where PC.COD_PRODUCTO = '".$cod_producto_cot."' and
												P.COD_PRODUCTO = PC.COD_PRODUCTO_HIJO";
												
				$result_preoc = $db-> build_results($sql_preoc_crea_desde_comp);
				for($j=0; $j<count($result_preoc); $j++){
					$row = $this->dws['dw_pre_orden_compra']->insert_row();
					$cantidad_compra = $result_preoc[$j]['CANTIDAD'] * $cantidad_cot;
					$total_compra = $cantidad_compra * $result_preoc[$j]['PRECIO_COSTO'];

					$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_ITEM', $i+1);
					$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_ORDEN', ($i+1)*10);
					$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_COD_ITEM_NOTA_VENTA', $result[$i]['COD_ITEM_NOTA_VENTA']);
					$this->dws['dw_pre_orden_compra']->set_item($row, 'COD_PRE_ORDEN_COMPRA', $result[$j]['COD_PRE_ORDEN_COMPRA']);
					if($result_preoc[$j]['GENERA_COMPRA']=='S')
						$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_GENERA_COMPRA', 'S');
					else
						$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_GENERA_COMPRA', 'N');
						
					$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_COD_PROVEEDOR', $result_preoc[$j]['COD_PROVEEDOR']);
					$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_COD_PRODUCTO', $result_preoc[$j]['COD_PRODUCTO_HIJO']);
					$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_COD_PRODUCTO_H', $result_preoc[$j]['COD_PRODUCTO_HIJO']);		
					$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_NOM_PRODUCTO', $result_preoc[$j]['NOM_PRODUCTO']);
					$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_CANTIDAD', $cantidad_compra);
					$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_CANT_COMPUESTO_H', $cantidad_compra);
					$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_PRECIO_COMPRA', $result_preoc[$j]['PRECIO_COSTO']);
					$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_PRECIO_COMPRA_H', $result_preoc[$j]['PRECIO_COSTO']);
					$this->dws['dw_pre_orden_compra']->set_item($row, 'MOTIVO_MOD_PRECIO', '');
					$this->dws['dw_pre_orden_compra']->set_item($row, 'CC_TOTAL', $total_compra);
				}												
			}
		}

		$this->dws['dw_nota_venta']->set_item(0, 'BTN_LISTA_NC', 'disabled');

		/*if($num_dif > 0)	// && $puede_usar_precio_cot && $validez_oferta) SE CAMBIO PARA QUE SE OCUPE UN AJAX
			$this->que_precio_usa($cod_cotizacion);*/
	}
	
	function print_record() {
		$sel_print_nv = $_POST['wi_hidden'];
		$print_nv = explode("|", $sel_print_nv);
		switch ($print_nv[0]) {
			
    	case "resumen":
			$this->printnv_resumen_pdf($print_nv[1] == 'logo', $print_nv[2]);
       		break;
    	case "marca":
    		$aux = substr($sel_print_nv, 6);
			$this->printnv_marca_pdf($print_nv[1] == 'logo', $aux);
			break;
    	case "resultado":
			$this->printnv_resultado_pdf($print_nv[1] == 'logo', $print_nv[2]);
       		break;
		}
		$this->_load_record();
	}
	function printnv_resumen_pdf($con_logo, $con_precio) {
		$cod_nota_venta = $this->get_key();
		$sql= "SELECT NV.COD_NOTA_VENTA,
				NV.REFERENCIA,
				NV.NRO_ORDEN_COMPRA,
				dbo.f_format_date(NV.FECHA_NOTA_VENTA,3)FECHA_NOTA_VENTA,				
				dbo.f_get_direccion('SUCURSAL', NV.COD_SUCURSAL_FACTURA, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD]') DIR_FACTURA,
				dbo.f_get_direccion('SUCURSAL', NV.COD_SUCURSAL_DESPACHO, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD]') DIR_DESPACHO,		
				E.NOM_EMPRESA,
				case
					when '$con_precio' = 'NO' then 0
					else NV.SUBTOTAL
				end SUBTOTAL,
				case
					when '$con_precio' = 'NO' then 0
					else NV.MONTO_DSCTO1
				end MONTO_DSCTO1,
				case
					when '$con_precio' = 'NO' then 0
					else NV.MONTO_DSCTO2
				end MONTO_DSCTO2,
				case
					when '$con_precio' = 'NO' then 0
					else NV.TOTAL_NETO
				end TOTAL_NETO,
				case
					when '$con_precio' = 'NO' then 0
					else NV.MONTO_IVA
				end MONTO_IVA,
				case
					when '$con_precio' = 'NO' then 0
					else NV.TOTAL_CON_IVA
				end TOTAL_CON_IVA,
				NV.PORC_DSCTO1,
				NV.PORC_DSCTO2,
				NV.MONTO_DSCTO1 + NV.MONTO_DSCTO2 FINAL,
				NV.PORC_IVA,
				NV.OBS_DESPACHO,
				NV.COD_COTIZACION,
				dbo.f_format_date(NV.FECHA_ENTREGA, 1) FECHA_ENTREGA,
				NV.OBS,	
				ENV.NOM_ESTADO_NOTA_VENTA,
				E.RUT,
				E.DIG_VERIF,
				SF.TELEFONO TELEFONO_F,
				SF.FAX FAX_F,
				SD.TELEFONO TELEFONO_D,
				SD.FAX FAX_D,
				P.NOM_PERSONA,
				M.SIMBOLO,
				U2.NOM_USUARIO,
				U2.INI_USUARIO,
				INV.NOM_PRODUCTO,
				case INV.COD_PRODUCTO
					when 'T' then ''
					else INV.ITEM
				end ITEM,
				case INV.COD_PRODUCTO
					when 'T' then null
					else INV.COD_PRODUCTO
				end COD_PRODUCTO,
				case INV.COD_PRODUCTO
					when 'T' then null
					else INV.CANTIDAD
				end CANTIDAD,
				case 
					when INV.COD_PRODUCTO = 'T' then null
					when '$con_precio' = 'NO' then 0
					else INV.PRECIO
				end PRECIO,
				case 
					when INV.COD_PRODUCTO = 'T' then null
					when '$con_precio' = 'NO' then 0
					else INV.CANTIDAD * INV.PRECIO
				end TOTAL,
				F.NOM_FORMA_PAGO,
				NV.NOM_FORMA_PAGO_OTRO,
		       	dbo.f_get_parametro(".self::K_PARAM_NOM_EMPRESA.") NOM_EMPRESA_EMISOR,
		       	dbo.f_get_parametro(".self::K_PARAM_RUT_EMPRESA.") RUT_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_DIR_EMPRESA.") DIR_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_TEL_EMPRESA.") TEL_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_FAX_EMPRESA.") FAX_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_MAIL_EMPRESA.") MAIL_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_CIUDAD_EMPRESA.") CIUDAD_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_PAIS_EMPRESA.") PAIS_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_SITIO_WEB_EMPRESA.") SITIO_WEB_EMPRESA
			FROM	NOTA_VENTA NV left outer join ITEM_NOTA_VENTA INV on NV.COD_NOTA_VENTA = INV.COD_NOTA_VENTA,
					EMPRESA E, SUCURSAL SF, SUCURSAL SD,
					PERSONA P, MONEDA M, USUARIO U,
					FORMA_PAGO F,
					USUARIO U2, 
					ESTADO_NOTA_VENTA ENV
			WHERE   NV.COD_NOTA_VENTA = ".$cod_nota_venta." AND
					E.COD_EMPRESA = NV.COD_EMPRESA AND
					SF.COD_SUCURSAL = NV.COD_SUCURSAL_FACTURA AND						
					SD.COD_SUCURSAL = NV.COD_SUCURSAL_DESPACHO AND
					P.COD_PERSONA = NV.COD_PERSONA AND
					M.COD_MONEDA = NV.COD_MONEDA AND
					U.COD_USUARIO = NV.COD_USUARIO AND										
					F.COD_FORMA_PAGO = NV.COD_FORMA_PAGO AND
					ENV.COD_ESTADO_NOTA_VENTA = NV.COD_ESTADO_NOTA_VENTA AND
					U2.COD_USUARIO = NV.COD_USUARIO_VENDEDOR1
			order by INV.ORDEN asc";
		// reporte
		$labels = array();
		$labels['strCOD_NOTA_VENTA'] = $cod_nota_venta;
		$rpt = new reporte($sql, $this->root_dir.'appl/nota_venta/nv_resumen.xml', $labels, "Nota Venta Resumen ".$cod_nota_venta.".pdf", $con_logo);
		
	}
	function printnv_marca_pdf($con_logo, $aux) {
		$cod_nota_venta = $this->get_key();
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "exec spr_nv_marca $cod_nota_venta, '$aux'";
		$labels = array();
		$file_name = $this->find_file('nota_venta', 'nv_marca.xml');
		$rpt = new print_nv_marca($sql, $file_name, $labels, "nota venta ".$cod_nota_venta.".pdf", 0);
		// fin reporte de marca despacho	
	}
	
	function printnv_resultado_pdf($con_logo, $con_precio) {
		$cod_nota_venta = $this->get_key();
		
		if($con_precio == 'SI'){
			$sql="SELECT  NV.COD_NOTA_VENTA,
						dbo.f_format_date(NV.FECHA_NOTA_VENTA, 3) FECHA_NOTA_VENTA,
						NV.REFERENCIA,
						NV.NRO_ORDEN_COMPRA,
						NV.COD_COTIZACION,
						NV.PORC_VENDEDOR1,
						NV.PORC_VENDEDOR2,
						E.NOM_EMPRESA,
						E.RUT,
						E.DIG_VERIF,
						dbo.f_get_direccion('SUCURSAL', NV.COD_SUCURSAL_FACTURA, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD]') DIR_FACTURA,
						SF.TELEFONO TELEFONO_FACTURA,
						SF.FAX FAX_FACTURA,		
						dbo.f_get_direccion('SUCURSAL', NV.COD_SUCURSAL_DESPACHO, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD]') DIR_DESPACHO,
						SD.TELEFONO TELEFONO_DESPACHO,
						SD.FAX FAX_DESPACHO,
						P.NOM_PERSONA,
						M.SIMBOLO,
	
						ITEM ITEM_R,
						COD_PRODUCTO COD_PRODUCTO_R,
						NOM_PRODUCTO NOM_PRODUCTO_R,
						CANTIDAD CANTIDAD_R,
						((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) PRECIO_CON_DESCTO_R,
						(((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) * INV.CANTIDAD) VENTA_TOTAL_R,
						COD_ITEM_NOTA_VENTA,
						-- costo unitario 
						dbo.f_nv_costo_unitario (COD_ITEM_NOTA_VENTA, COD_PRODUCTO) COSTO_UNITARIO_R,
						-- costo total
						(dbo.f_nv_costo_unitario (COD_ITEM_NOTA_VENTA, COD_PRODUCTO)* CANTIDAD) COSTO_TOTAL_R,
						-- otrso gastos
						(((((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) * INV.CANTIDAD) / NV.TOTAL_NETO) * dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_GASTO_FIJO')+
						((((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) * INV.CANTIDAD)/ NV.TOTAL_NETO) * dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO')) OTROS_GASTOS_R,
						-- monto resultado
						(((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) * INV.CANTIDAD)- (dbo.f_nv_costo_unitario (COD_ITEM_NOTA_VENTA, COD_PRODUCTO)* CANTIDAD) RESULTADO_R,
						-- porc resultado
						(((((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) * INV.CANTIDAD)- (dbo.f_nv_costo_unitario (COD_ITEM_NOTA_VENTA, COD_PRODUCTO)* CANTIDAD)) / (((NV.TOTAL_NETO/NV.SUBTOTAL)* INV.PRECIO) * INV.CANTIDAD))*100 PORC_RESULTADO_R,
						
						--resultados
						TOTAL_NETO,
						PORC_DSCTO_CORPORATIVO,
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO') MONTO_DSCTO_CORPORATIVO,
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'VENTA_NETA_FINAL') VENTA_NETA_FINAL,
						dbo.f_get_parametro_porc('AA', isnull(NV.FECHA_NOTA_VENTA, getdate()))PORC_AA,
						dbo.f_get_parametro_porc('GF', isnull(NV.FECHA_NOTA_VENTA, getdate()))PORC_GF,
						dbo.f_get_parametro_porc('GV', isnull(NV.FECHA_NOTA_VENTA, getdate()))PORC_GV,
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'SUMA_NC') SUMA_NC,
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DIRECTORIO') MONTO_DIRECTORIO,
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_GASTO_FIJO') MONTO_GASTO_FIJO,
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'SUM_OC_TOTAL') SUM_OC_TOTAL,
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'RESULTADO') RESULTADO,
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'RESULTADO') STATIC_RESULTADO,
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'PORC_RESULTADO') PORC_RESULTADO,
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'PORC_RESULTADO') STATIC_PORC_RESULTADO,
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_V1')COMISION_V1,
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_V2')COMISION_V2,
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_GV')COMISION_GV,
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_ADM')COMISION_ADM,					
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'REMANENTE') REMANENTE,
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_ADM')COMISION_ADM,
						dbo.f_get_parametro_porc('ADM', isnull(NV.FECHA_NOTA_VENTA, getdate()))PORC_ADM,
						(select NOM_USUARIO from USUARIO USU where USU.COD_USUARIO = NV.COD_USUARIO_VENDEDOR1) VENDEDOR1,
						(select NOM_USUARIO from USUARIO USU where USU.COD_USUARIO = NV.COD_USUARIO_VENDEDOR2) VENDEDOR2,
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'SUM_OC_TOTAL_NETA') SUM_OC_TOTAL_NETA,
						dbo.f_get_parametro(".self::K_PARAM_GTE_VTA.") GTE_VTA,
						dbo.f_get_parametro(".self::K_PARAM_NOM_EMPRESA.") NOM_EMPRESA_EMISOR,
						dbo.f_get_parametro(".self::K_PARAM_RUT_EMPRESA.") RUT_EMPRESA,
						dbo.f_get_parametro(".self::K_PARAM_DIR_EMPRESA.") DIR_EMPRESA,
						dbo.f_get_parametro(".self::K_PARAM_TEL_EMPRESA.") TEL_EMPRESA,
						dbo.f_get_parametro(".self::K_PARAM_FAX_EMPRESA.") FAX_EMPRESA,
						dbo.f_get_parametro(".self::K_PARAM_MAIL_EMPRESA.") MAIL_EMPRESA,
						dbo.f_get_parametro(".self::K_PARAM_CIUDAD_EMPRESA.") CIUDAD_EMPRESA,
						dbo.f_get_parametro(".self::K_PARAM_PAIS_EMPRESA.") PAIS_EMPRESA,
						dbo.f_get_parametro(".self::K_PARAM_SITIO_WEB_EMPRESA.") SITIO_WEB_EMPRESA,
						ENV.NOM_ESTADO_NOTA_VENTA,
						ISNULL(REBAJA, 0) REBAJA
				FROM	NOTA_VENTA NV left outer join ITEM_NOTA_VENTA INV on NV.COD_NOTA_VENTA = INV.COD_NOTA_VENTA,
						SUCURSAL SF, SUCURSAL SD, EMPRESA E,
						MONEDA M, PERSONA P, ESTADO_NOTA_VENTA ENV
	
				WHERE 	NV.COD_NOTA_VENTA = ".$cod_nota_venta." AND
						INV.COD_NOTA_VENTA = NV.COD_NOTA_VENTA AND
						E.COD_EMPRESA = NV.COD_EMPRESA AND
						SF.COD_SUCURSAL = NV.COD_SUCURSAL_FACTURA AND
						SD.COD_SUCURSAL = NV.COD_SUCURSAL_DESPACHO AND
						M.COD_MONEDA = NV.COD_MONEDA AND
						P.COD_PERSONA = NV.COD_PERSONA AND
						ENV.COD_ESTADO_NOTA_VENTA = NV.COD_ESTADO_NOTA_VENTA
						order by INV.ORDEN asc";
		}else{
			$sql="SELECT  NV.COD_NOTA_VENTA,
					dbo.f_format_date(NV.FECHA_NOTA_VENTA, 3) FECHA_NOTA_VENTA,
					NV.REFERENCIA,
					NV.NRO_ORDEN_COMPRA,
					NV.COD_COTIZACION,
					0 PORC_VENDEDOR1,
					0 PORC_VENDEDOR2,
					E.NOM_EMPRESA,
					E.RUT,
					E.DIG_VERIF,
					dbo.f_get_direccion('SUCURSAL', NV.COD_SUCURSAL_FACTURA, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD]') DIR_FACTURA,
					SF.TELEFONO TELEFONO_FACTURA,
					SF.FAX FAX_FACTURA,		
					dbo.f_get_direccion('SUCURSAL', NV.COD_SUCURSAL_DESPACHO, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD]') DIR_DESPACHO,
					SD.TELEFONO TELEFONO_DESPACHO,
					SD.FAX FAX_DESPACHO,
					P.NOM_PERSONA,
					M.SIMBOLO,
					ITEM ITEM_R,
					COD_PRODUCTO COD_PRODUCTO_R,
					NOM_PRODUCTO NOM_PRODUCTO_R,
					CANTIDAD CANTIDAD_R,
					0 PRECIO_CON_DESCTO_R,
					0 VENTA_TOTAL_R,
					COD_ITEM_NOTA_VENTA,
					-- costo unitario 
					0 COSTO_UNITARIO_R,
					-- costo total
					0 COSTO_TOTAL_R,
					-- otrso gastos
					0 OTROS_GASTOS_R,
					-- monto resultado
					0 RESULTADO_R,
					-- porc resultado
					0 PORC_RESULTADO_R,
					--resultados
					0 TOTAL_NETO,
					0 PORC_DSCTO_CORPORATIVO,
					0 MONTO_DSCTO_CORPORATIVO,
					0 VENTA_NETA_FINAL,
					0 PORC_AA,
					0 PORC_GF,
					0 PORC_GV,
					0 SUMA_NC,
					0 MONTO_DIRECTORIO,
					0 MONTO_GASTO_FIJO,
					0 SUM_OC_TOTAL,
					0 RESULTADO,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'RESULTADO') STATIC_RESULTADO,
					0 PORC_RESULTADO,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'PORC_RESULTADO') STATIC_PORC_RESULTADO,
					0 COMISION_V1,
					0 COMISION_V2,
					0 COMISION_GV,
					0 COMISION_ADM,					
					0 REMANENTE,
					0 PORC_ADM,
					(select NOM_USUARIO from USUARIO USU where USU.COD_USUARIO = NV.COD_USUARIO_VENDEDOR1) VENDEDOR1,
					(select NOM_USUARIO from USUARIO USU where USU.COD_USUARIO = NV.COD_USUARIO_VENDEDOR2) VENDEDOR2,
					0 SUM_OC_TOTAL_NETA,
					dbo.f_get_parametro(".self::K_PARAM_GTE_VTA.") GTE_VTA,
					dbo.f_get_parametro(".self::K_PARAM_NOM_EMPRESA.") NOM_EMPRESA_EMISOR,
					dbo.f_get_parametro(".self::K_PARAM_RUT_EMPRESA.") RUT_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_DIR_EMPRESA.") DIR_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_TEL_EMPRESA.") TEL_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_FAX_EMPRESA.") FAX_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_MAIL_EMPRESA.") MAIL_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_CIUDAD_EMPRESA.") CIUDAD_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_PAIS_EMPRESA.") PAIS_EMPRESA,
					dbo.f_get_parametro(".self::K_PARAM_SITIO_WEB_EMPRESA.") SITIO_WEB_EMPRESA,
					ENV.NOM_ESTADO_NOTA_VENTA,
					0 REBAJA
			FROM	NOTA_VENTA NV left outer join ITEM_NOTA_VENTA INV on NV.COD_NOTA_VENTA = INV.COD_NOTA_VENTA,
					SUCURSAL SF, SUCURSAL SD, EMPRESA E,
					MONEDA M, PERSONA P, ESTADO_NOTA_VENTA ENV

			WHERE 	NV.COD_NOTA_VENTA = ".$cod_nota_venta." AND
					INV.COD_NOTA_VENTA = NV.COD_NOTA_VENTA AND
					E.COD_EMPRESA = NV.COD_EMPRESA AND
					SF.COD_SUCURSAL = NV.COD_SUCURSAL_FACTURA AND
					SD.COD_SUCURSAL = NV.COD_SUCURSAL_DESPACHO AND
					M.COD_MONEDA = NV.COD_MONEDA AND
					P.COD_PERSONA = NV.COD_PERSONA AND
					ENV.COD_ESTADO_NOTA_VENTA = NV.COD_ESTADO_NOTA_VENTA
					order by INV.ORDEN asc";
		
		}	
	
	// reporte
	$labels = array();
	$labels['strCOD_NOTA_VENTA'] = $cod_nota_venta;
	$rpt = new reporte($sql, $this->root_dir.'appl/nota_venta/nv_resultado1.xml', $labels, "Nota Venta Resultado ".$cod_nota_venta.".pdf", $con_logo);

	}
	
	function por_despachar() {
		$cod_nota_venta = $this->get_key();
		$sql = $sql = "exec spr_nv_por_fact_por_desp $cod_nota_venta, 'POR_DESPACHAR'";
		$labels = array();
		$labels['strCOD_NOTA_VENTA'] = $cod_nota_venta;
		$rpt = new reporte($sql, $this->root_dir.'appl/nota_venta/por_despachar.xml', $labels, "Por despachar Nota Venta ".$cod_nota_venta.".pdf", true);
		$this->redraw();
		return;
	}
	function por_facturar() {
		$cod_nota_venta = $this->get_key();
		$sql = $sql = "exec spr_nv_por_fact_por_desp $cod_nota_venta, 'POR_FACTURAR'";
		$labels = array();
		$labels['strCOD_NOTA_VENTA'] = $cod_nota_venta;
		$rpt = new reporte($sql, $this->root_dir.'appl/nota_venta/por_facturar.xml', $labels, "Por facturar Nota Venta ".$cod_nota_venta.".pdf", true);
		$this->redraw();
		return;
	}
	
	function procesa_event() {
		$cod_nota_venta = $this->get_key();
		if(isset($_POST['b_save_x'])) {
			if (isset($_POST['b_save'])) $this->current_tab_page = $_POST['b_save'];
			if ($this->_save_record()) {
				if ($_POST['wi_hidden']=='save_desde_print')		// Si el save es gatillado desde el boton print, se fuerza que se ejecute nuevamente el print
					print '<script type="text/javascript"> document.getElementById(\'b_print\').click(); </script>';
				if ($_POST['wi_hidden']=='save_desde_por_despachar')
					$this->por_despachar();
				else if ($_POST['wi_hidden']=='save_desde_por_facturar')
					$this->por_facturar();
			}
		}
		elseif (isset($_POST['b_print_x']))
			$this->print_record();
		elseif ($_POST['wi_hidden']=='save_desde_por_despachar')
			$this->por_despachar();
		elseif ($_POST['wi_hidden']=='save_desde_por_facturar')
			$this->por_facturar();
		elseif(isset($_POST['b_envia_mail_pago_x'])){
			$print_nv = explode("|", $_POST['wi_hidden']);
			if($print_nv[0]=='S'){
				$this->actualiza_mail($cod_nota_venta,$print_nv[1]);
			}
			$this->actualiza_webpay($cod_nota_venta,$print_nv[2]);
			$link_pago = $this->inicia_pago($cod_nota_venta);

			$this->envia_mail_pago($link_pago,$cod_nota_venta,$print_nv[1], $print_nv[3]);
			$this->log_cambio($cod_nota_venta);

		}else if($_POST['b_no_suma_nv_informe']){
			$this->suma_nv_informe();
			$this->_load_record();
		}else if($_POST['b_genera_oc_nv']){
			if($this->genera_oc()){
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				$COD_NOTA_VENTA	= $this->get_key();
				$sql = "SELECT DISTINCT TOP 1 COD_ORDEN_COMPRA
						FROM ORDEN_COMPRA
						WHERE COD_NOTA_VENTA = $COD_NOTA_VENTA
						AND CREADA_DESDE = 'BTN_GENERA_OC'
						ORDER BY COD_ORDEN_COMPRA DESC";
				$result = $db-> build_results($sql);
				$cod_orden_compra = $result[0]['COD_ORDEN_COMPRA'];
				$this->alert('Se ha creado la OC Nº '.$cod_orden_compra.', ahora puede revisar esta OC y modificarla según sea la necesidad.');
			}else
				$this->alert('ha ocurrido un Error');
			
			$this->_load_record();
		}else if(isset($_POST['b_print_folleto_x']))
			$this->print_folleto('1');
		else if(isset($_POST['b_f_tecnica_x']))
			$this->print_folleto('2');
		else
			parent::procesa_event();
	}
	
	function print_folleto($tipo_print){
		$cod_nota_venta	= $this->get_key();
	
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT DISTINCT COD_PRODUCTO, MIN(ORDEN)
				FROM ITEM_NOTA_VENTA
				WHERE COD_NOTA_VENTA = $cod_nota_venta
				AND COD_PRODUCTO NOT IN ('T', 'TE', 'E', 'F', 'I')
				GROUP BY COD_PRODUCTO
				ORDER BY MIN(ORDEN)";
		$result = $db->build_results($sql);
		
		for($i=0 ; $i < count($result) ; $i++)
			$lista_productos .= $result[$i]['COD_PRODUCTO'].",";
		
		$lista_productos = base64_encode(trim($lista_productos, ','));
		print " <script>window.open('print_especial.php?RESULT_PRODUCTO=$lista_productos&TIPO_PRINT=$tipo_print')</script>";	
		$this->_load_record();
	}
	
	function genera_oc(){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$db->BEGIN_TRANSACTION();
		$sp = "spu_orden_compra";
		
		$cod_usuario	= $this->dws['dw_nota_venta']->get_item(0, 'COD_USUARIO_VENDEDOR1');
		$COD_NOTA_VENTA	= $this->get_key();
		$referencia		= "'OC CREADA EXTERNAMENTE DESDE NV $COD_NOTA_VENTA'";
		
		$param = "'INSERT'			--@ve_operacion
				 ,NULL				--@ve_cod_orden_compra
				 ,$cod_usuario		--@ve_cod_usuario
				 ,$cod_usuario		--@ve_cod_usuario_solicita
				 ,1					--@ve_cod_moneda			
				 ,1					--@ve_cod_estado_orden_compra
				 ,$COD_NOTA_VENTA	--@ve_cod_nota_venta
				 ,1					--@ve_cod_cuenta_corriente
				 ,$referencia		--@ve_referencia			
				 ,1337				--@ve_cod_empresa			
				 ,6506				--@ve_cod_suc_factura
				 ,1373				--@ve_cod_persona
				 ,0					--@ve_sub_total
				 ,0					--@ve_porc_dscto1
				 ,0					--@ve_monto_dscto1
				 ,0					--@ve_porc_dscto2
				 ,0					--@ve_monto_dscto2
				 ,0					--@ve_total_neto
				 ,19				--@ve_porc_iva
				 ,0					--@ve_monto_iva
				 ,0					--@ve_total_con_iva
				 ,NULL				--@ve_obs
				 ,NULL				--@ve_motivo_anula
				 ,NULL				--@ve_cod_usuario_anula
				 ,'M'				--@ve_ingreso_usuario_dscto1
				 ,'M'				--@ve_ingreso_usuario_dscto2
				 ,'NOTA_VENTA'		--@ve_tipo_orden_compra
				 ,NULL				--@ve_cod_doc
				 ,'S'				--@ve_autorizada
				 ,'S'				--@ve_autorizada_20_proc
				 ,NULL				--@nro_orden_compra_4d
				 ,NULL				--@ve_es_oc_gf_plano
				 ,NULL				--@ve_autoriza_facturacion
				 ,NULL				--@ve_fecha_aut_facturacion
				 ,'N'				--@ve_autoriza_monto_compra
				 ,NULL				--@ve_usuario_autoriza_monto_compra
				 ,'BTN_GENERA_OC'	--@ve_creada_desde";
		
		if($db->EXECUTE_SP($sp, $param)){
			if(!$db->EXECUTE_SP("sp_log_cambio", "'NOTA_VENTA', '$COD_NOTA_VENTA',".$this->cod_usuario.", 'I'")){
				$db->ROLLBACK_TRANSACTION();
				return false;
			}
			          
			$COD_LOG_CAMBIO = $db->GET_IDENTITY('LOG_CAMBIO');
			$FIELD_VALUE	= 'COD_ORDEN_COMPRA';
			
			$sql = "SELECT DISTINCT TOP 1 COD_ORDEN_COMPRA
					FROM ORDEN_COMPRA
					WHERE COD_NOTA_VENTA = $COD_NOTA_VENTA
					AND CREADA_DESDE = 'BTN_GENERA_OC'
					ORDER BY COD_ORDEN_COMPRA DESC";
			$result = $db-> build_results($sql);
			
			$VALUE = $result[0]['COD_ORDEN_COMPRA'];
			
			$param = "$COD_LOG_CAMBIO
					 ,'ORDEN_COMPRA'
					 ,'$FIELD_VALUE'
					 ,'$VALUE'
					 ,''
					 ,'$VALUE'
					 ,'I'";				
			
			if(!$db->EXECUTE_SP("sp_detalle_cambio_relacionada", $param)){
				$db->ROLLBACK_TRANSACTION();
				return false;
			}	
			
			$db->COMMIT_TRANSACTION();
			return true;
		}else{
			$db->ROLLBACK_TRANSACTION();
			return false;
		}	
	}
	
	function suma_nv_informe(){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$COD_NOTA_VENTA = $this->get_key();
		$cod_usuario = $this->cod_usuario;
		
		$sql = "SELECT dbo.f_get_no_suma_nv_informe($COD_NOTA_VENTA, 'CHECKED') ES_NO_SUMA_NV_INFORME";
		$result = $db->build_results($sql);
		$ES_NO_SUMA_NV_INFORME = $result[0]['ES_NO_SUMA_NV_INFORME'];
		
		$sp = "spu_no_suma_inf_nv";
		if($ES_NO_SUMA_NV_INFORME == 'S'){
			$operacion1 = "'DELETE'";
		}else{
			$operacion1 = "'INSERT'";
		}
		
		$db->EXECUTE_SP($sp, "$operacion1, $COD_NOTA_VENTA, NULL, $cod_usuario");
	}

	function encriptar_url($txt_input, $key){
	     $result = '';
	     for($i=0; $i<strlen($txt_input); $i++) {
	         $char = substr($txt_input, $i, 1);
	         $keychar = substr($key, ($i % strlen($key))-1, 1);
	         $char = chr(ord($char)+ord($keychar));
	         $result.=$char;
	     }
	     $txt_ouput = base64_encode($result);
	
	     return $txt_ouput;
	}
	
	function dencriptar_url($txt_input, $key){
	     $result = '';
	     $string = base64_decode($txt_input);
	     for($i=0; $i<strlen($string); $i++) {
	         $char = substr($string, $i, 1);
	         $keychar = substr($key, ($i % strlen($key))-1, 1);
	         $char = chr(ord($char)-ord($keychar));
	         $result.=$char;
	     }
	     $txt_ouput = $result;
	
	     return $txt_ouput;
	}
	
	function inicia_pago($cod_nota_venta){
		
		$K_EST_PAGO_GENERADO = 1;
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$sql = "SELECT MONTO_WEBPAY
					,COD_EMPRESA
				FROM NOTA_VENTA
				WHERE COD_NOTA_VENTA =$cod_nota_venta";
		$result = $db->build_results($sql);			
		$monto_pago = $result[0]['MONTO_WEBPAY'];
		$cod_empresa = $result[0]['COD_EMPRESA'];
		
		$param = "'INSERT'
					,null--$cod_wp_transaccion
					,$cod_nota_venta
					,$monto_pago
					,'$link_pago'
					,$cod_empresa
					,'S' --ve_link_visible
					,'N' --ve_exito";

		$sp = "spw_wp_transaccion";
		
		if ($db->EXECUTE_SP($sp, $param)){
			$cod_wp_transaccion = $db->GET_IDENTITY('WP_TRANSACCION');
			$cod_wp_transaccion = base64_encode($cod_wp_transaccion);
			$link_pago = "http://webpay.biggi.cl/venta?param=$cod_wp_transaccion";
			
			return $link_pago;
			
	    }
	}
	function actualiza_mail($cod_nota_venta,$nombre_correo){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

		$sp = 'spu_actualizar_email';	
		$param = "$cod_nota_venta
				,'$nombre_correo'";

	    if (!$db->EXECUTE_SP($sp, $param)){
			return true;
	    }
	    return false;
	}
	function actualiza_webpay($cod_nota_venta,$monto_webpay){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

		$sp = 'spu_nota_venta';	
		$param = "'ACTUALIZA_WEBPAY'
				 ,$cod_nota_venta
				 ,null
				 ,$monto_webpay";

	    if (!$db->EXECUTE_SP($sp, $param)){
			return true;
	    }
	    return false;
	}
 	function f_envia_mail($estado_nota_venta){
        $COD_NOTA_VENTA = $this->get_key();
        $remitente = $this->nom_usuario;
        $cod_remitente = $this->cod_usuario;

        $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        // obtiene el mail de quien creo la tarea y manda el mail
        $sql_remitente = "SELECT MAIL from USUARIO where COD_USUARIO =".$cod_remitente;
        $result_remitente = $db->build_results($sql_remitente);

        $mail_remitente = $result_remitente[0]['MAIL'];
        
        // Mail destinatarios
        $para_admin1 = 'mherrera@integrasystem.cl';
        $para_admin2 = 'mherrera@integrasystem.cl';
        /*
        $para_admin1 = 'mherrera@integrasystem.cl';
        $para_admin2 = 'imeza@integrasystem.cl';
		*/
        
 		if($estado_nota_venta == 'CONFIRMADO')
		{
	        $asunto = 'Confimación de Nota de Venta Nº '.$COD_NOTA_VENTA;
	        $mensaje = 'Se ha <b>CONFIRMADO</b> la <b>NOTA DE VENTA Nº '.$COD_NOTA_VENTA.'</b> por el usuario <b><i>'.$remitente.'<i><b>';
		}        
 		if($estado_nota_venta == 'EMITIDO')
		{
			$asunto = 'Creación Nueva Nota de Venta Nº '.$COD_NOTA_VENTA;
	        $mensaje = 'Se ha <b>CREADO</b> una nueva <b>NOTA DE VENTA Nº '.$COD_NOTA_VENTA.'</b> por el usuario <b><i>'.$remitente.'<i><b>';
	  	}
 		if($estado_nota_venta == 'ANULADA')
		{
	        $asunto = 'Anulacion de Nota de Venta Nº '.$COD_NOTA_VENTA;
	        $mensaje = 'Se ha <b>ANULADO</b> la <b>NOTA DE VENTA Nº '.$COD_NOTA_VENTA.'</b> por el usuario <b><i>'.$remitente.'<i><b>';
		}
	  	$cabeceras  = 'MIME-Version: 1.0' . "\n";
        $cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
        $cabeceras .= 'From: '.$mail_remitente. "\n";
        //se comenta el envio de mail por q ya no es necesario => Vmelo. 
        //mail($para_admin1, $asunto, $mensaje, $cabeceras);
        //mail($para_admin2, $asunto, $mensaje, $cabeceras);
 		return 0;
    }   
	function goto_list() {
		// Usado en INFORME_RESULTADO
		if ($this->desde_link && $this->modulo_origen=='INF_RESULTADO') {
			$mes = session::get('wi_CURRENT_TAB_'.$this->modulo_origen);
			session::un_set('wi_CURRENT_TAB_'.$this->modulo_origen);				
			$url = $this->root_url."appl/inf_resultado/inf_resultado_mes.php?mes=".$mes;
			header ('Location:'.$url);
		}
		else if ($this->desde_link && $this->modulo_origen=='inf_guia_despacho_por_facturar') {
			session::set('ULTIMA_NV_CONSULTADA', $this->get_key());
			$url = $this->root_url."../../commonlib/trunk/php/mantenedor.php?modulo=inf_guia_despacho_por_facturar&cod_item_menu=4020";
			header ('Location:'.$url);
		}
		else
			parent::goto_list();
	}
	function load_wo(){
		if (session::is_set('DESDE_wo_inf_backcharge')){
			//session::un_set('DESDE_wo_inf_backcharge');
			$this->desde_wo_inf_backcharge = true;
			$this->wo = session::get("wo_inf_backcharge");
		}else if($this->desde_wo_inf_por_despachar)
			$this->wo = session::get("wo_inf_por_despachar");
		else{
			parent::load_wo();
		}	
	}
	function get_url_wo(){
		if ($this->desde_wo_inf_backcharge) 
			return $this->root_url.'appl/inf_backcharge/wo_inf_backcharge.php';
		else if($this->desde_wo_inf_por_despachar)
			return $this->root_url.'appl/inf_por_despachar/wo_inf_por_despachar.php';
		else
			return parent::get_url_wo();
	}
	
	function envia_mail_pago($link_pago,$cod_nota_venta,$email,$nombre_correo){
		$temp = new Template('COMERCIAL/correo.html');
		$temp->setVar("LINK_PAGO", "$link_pago");
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS); 
		$sql = "SELECT COD_NOTA_VENTA	
						,CONVERT(VARCHAR,E.RUT) RUT
						,E.DIG_VERIF 
						,E.NOM_EMPRESA
						,TOTAL_CON_IVA
						,MONTO_WEBPAY
						,P.NOM_PERSONA
						,(select top 1 convert( varchar(100),FECHA_CAMBIO,103) from LOG_CAMBIO where NOM_TABLA = 'ENVIA_MAIL_WEB_PAY' and KEY_TABLA = COD_NOTA_VENTA order by COD_LOG_CAMBIO desc) FECHA_ENVIO
						,(select top 1 convert( varchar(100),FECHA_CAMBIO,108) from LOG_CAMBIO where NOM_TABLA = 'ENVIA_MAIL_WEB_PAY' and KEY_TABLA = COD_NOTA_VENTA order by COD_LOG_CAMBIO desc) FECHA_ENVIO_HORA
						,(select VALOR from PARAMETRO where COD_PARAMETRO=65) HORA_PARAMETRO
				FROM 	NOTA_VENTA NV, EMPRESA E, PERSONA P
				WHERE	COD_NOTA_VENTA =$cod_nota_venta
					and E.COD_EMPRESA = NV.COD_EMPRESA 
					and P.COD_PERSONA = NV.COD_PERSONA";
		$result = $db->build_results($sql);
		
		$rut 		 = number_format($result[0]['RUT'], 0, ',', '.');
		$dig_verif	 = $result[0]['DIG_VERIF'];
		$nom_empresa = $result[0]['NOM_EMPRESA'];
		$monto_total = number_format($result[0]['TOTAL_CON_IVA'], 0, ',', '.');
		$nom_persona = $result[0]['NOM_PERSONA'];
		$fecha_envio = $result[0]['FECHA_ENVIO'];
		$fecha_envio_hora = $result[0]['FECHA_ENVIO_HORA'];
		$hora_parametro = $result[0]['HORA_PARAMETRO'];
		// SE AGREGA MONTO WEB PAY AL CORREO PARA LA IMPLEMENTACION DE LOS PAGOS PARCIALES
		$monto_webpay = number_format($result[0]['MONTO_WEBPAY'], 0, ',', '.');
		
		$temp->setVar("COD_NOTA_VENTA", "$cod_nota_venta");
		$temp->setVar("RUT", "$rut");	
		$temp->setVar("DIG_VERIF", "$dig_verif");	
		$temp->setVar("NOM_EMPRESA", "$nom_empresa");
		$temp->setVar("TOTAL_CON_IVA", "$monto_total");
		$temp->setVar("NOM_PERSONA", "$nom_persona");
		$temp->setVar("EMAIL","$email");
		$temp->setVar("FECHA_ENVIO","$fecha_envio");
		$temp->setVar("FECHA_ENVIO_HORA","$fecha_envio_hora");
		$temp->setVar("HORA_PARAMETRO","$hora_parametro");
		$temp->setVar("MONTO_WEBPAY", "$monto_webpay");
		//CREACION DE EMAIL
		$autorizacion ="AUTORIZACION PARA PAGO VIA WEB PAY NOTA DE VENTA Nº";
		$temp->setVar("AUTORIZACION_WEB","$autorizacion");
		
		$saludo_persona = "Estimado(a):	$nom_persona,"; 
		$temp->setVar("SALUDO_PERSONA","$saludo_persona");
		$saludo_empresa = "$nom_empresa"; 
		$temp->setVar("SALUDO_EMPRESA","$saludo_empresa");
		$informamos = "Informamos a usted que se ha generado una autorizacion para el pago via 
				WebPay de la Nota de Venta $cod_nota_venta  por un total de $ $monto_webpay ";
		$temp->setVar("INFORMAMOS","$informamos");		
		$para_concretar = "Para concretar su pago, favor acceda a nuestro sitio web mediante el
				    siguiente link:<br><a href=$link_pago>$link_pago</a>";
		$temp->setVar("PARA_CONCRETAR","$para_concretar");	
		$link_generado = "El LINK generado tiene una validez de 24 horas a contar de nuestra fecha
					    de envio $fecha_envio.";
		$temp->setVar("LINK_GENERADO","$link_generado");
		$confirmacion = "Una vez confirmado y recibido su pago, nuestro departamento de ventas se
		    pondra en contacto con usted dentro de un plazo maximo de $hora_parametro horas habiles.";
		$temp->setVar("CONFIRMACION","$confirmacion");
		$agradecemos = "Agradecemos su preferencia y le invitamos a seguir revisando toda nuestra oferta 
					    de productos en <a href=www.biggi.cl>www.biggi.cl</a>";
		$temp->setVar("AGRADECEMOS","$agradecemos");
		
		$db2 = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql2 = "select NOM_PARAMETRO,
						VALOR 
				from parametro
				where COD_PARAMETRO=10
				OR COD_PARAMETRO=11
				OR COD_PARAMETRO=12
				OR COD_PARAMETRO=6";
		$result2 = $db2->build_results($sql2);
		
		$nom_empresa = $result2[0]['VALOR'];
		$direccion_empresa = $result2[1]['VALOR'];
		
		$fono_empresa = $result2[2]['NOM_PARAMETRO'];
		$telefono = $result2[2]['VALOR'];
		
		$fax_empresa = $result2[3]['NOM_PARAMETRO'];
		$fax = $result2[3]['VALOR'];
		
		$temp->setVar("NOM_EMPRESA","$nom_empresa");
		$temp->setVar("DIRECCION_EMPRESA","$direccion_empresa");
		$temp->setVar("FONO_EMPRESA","$fono_empresa");
		$temp->setVar("TELEFONO","$telefono");
		$temp->setVar("FAX_EMPRESA","$fax_empresa");
		$temp->setVar("FAX","$fax");
						
		$glosa_final = "(*)Los acentos y caracteres especiales fueron omitidos para su correcta lectura en cualquier medio electronico";
		$temp->setVar("GLOSA_FINAL","$glosa_final");
		 
	    $db3 = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql3 = "select MAIL 
						,NOM_USUARIO
				from usuario u,NOTA_VENTA nv
				where u.COD_USUARIO = nv.COD_USUARIO_VENDEDOR1
				and nv.COD_NOTA_VENTA = $cod_nota_venta";
		$result3 = $db3->build_results($sql3);
	    
	    $email_vendedor = $result3[0]['MAIL'];
	    $nombre_vendedor = $result3[0]['NOM_USUARIO'];

		$mail_from			= "'$email_vendedor'";
		$mail_from_name		= "'$nombre_vendedor'";
		$asunto 			= "'Autorización  Pago Web Pay N° Nota Venta: $cod_nota_venta'";
		$html_temp			= $temp->toString();
		
		$lista_mail_to		= "'$email'";
		$lista_mail_to_name	= "'$nombre_correo'";
		$lista_mail_cc		= "NULL";
		$lista_mail_cc_name	= "NULL";
		$lista_mail_bcc		= "'rescudero@biggi.cl;evergara@integrasystem.cl;mherrera@biggi.cl;$email_vendedor'";
		$lista_mail_bcc_name= "NULL";
		
		$sp = "spu_envio_mail";
		$param = "'INSERT'
				,null
				,null
				,null
			 	,$mail_from
			 	,$mail_from_name
			 	,$lista_mail_cc
			 	,$lista_mail_cc_name
			 	,$lista_mail_bcc
			 	,$lista_mail_bcc_name
			 	,$lista_mail_to
			 	,$lista_mail_to_name
			 	,$asunto
			 	,'".str_replace("'","''",$html_temp)."'
			 	,NULL
			 	,'MAIL_PAGO_WEBPAY1'
			 	,$cod_nota_venta";
		
		$this->_load_record();
		
		if($db3->EXECUTE_SP($sp, $param))
		    $this->alert('El Mail ha sido enviado.');
		else
		    $this->alert('Error al enviar mail.');
		
	}

	function log_cambio($cod_nota_venta){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sp = "sp_log_cambio";
			$param = "'ENVIA_MAIL_WEB_PAY','".$cod_nota_venta."',".$this->cod_usuario;
			$param .= ",'I'";
			$db->EXECUTE_SP($sp, $param);
	}	

}

////******************////
class print_nv_marca extends reporte {	
	function print_nv_marca($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);
	}
	function dibuja_uno(&$pdf, $result) {
		$pdf->SetFont('Helvetica','B',24);
		$pdf->Text(530, 80, $result['INICIAL_VENDEDOR']);
		$pdf->Text(50, 150,'Nº NV');
		$pdf->SetFont('Helvetica','',14);
		$pdf->Text(50,190,'Cliente');
		$pdf->Text(50,250,'Referencia');
		$pdf->Text(50,310,'Atención Sr(a)');		
		$pdf->Text(50,340,'Despacho');		
		$pdf->Text(50,400,'Obs. Despacho');		
		$pdf->Text(50,490,'Nº OC');		
		$pdf->Text(50,520,'Ítem');	
		$pdf->Text(50,550,'Módelo');		
		$pdf->Text(50,580,'Producto');		
		$pdf->Text(50,640,'Remitente');		
		$pdf->Text(50,670,'Dirección');
		
		
		
		$pdf->SetFont('Helvetica','B',24);
		$pdf->Text(180, 150, $result['COD_NOTA_VENTA']);
		$pdf->SetFont('Helvetica','',14);
		
		$pdf->Text(180, 310, $result['NOM_PERSONA']);
		$pdf->Text(180, 340, $result['DIR_DESPACHO']);
		$pdf->Text(180, 355, $result['COMUNA_CIUDAD']);
		$pdf->Text(180, 370, $result['FONO_FAX']);
		
		
		$pdf->Text(180, 490, $result['NRO_ORDEN_COMPRA']);
		$pdf->Text(180, 520, $result['ITEM']);
		$pdf->Text(180, 550, $result['COD_PRODUCTO']);
		$pdf->Text(180, 640, $result['NOM_EMPRESA_EMISOR']);
		$pdf->Text(215, 670, $result['DIR_EMPRESA']);
		$pdf->Text(370, 670, $result['CIUDAD_EMPRESA']);
		$pdf->Text(460, 670, $result['PAIS_EMPRESA']);
		$pdf->Text(275, 690, $result['TEL_EMPRESA']);
		$pdf->Text(400, 690, $result['MAIL_EMPRESA']);
		$pdf->Text(360, 668,'-');
		$pdf->Text(450, 668,'-');
		$pdf->Text(230, 690,'FONO:');
		$pdf->Text(393, 688,'-');
		
		
		$pdf->Rect(40,120, 130, 50, 'f');
		$pdf->Rect(40,120, 530, 50, 'f');
		$pdf->Rect(40,170, 130, 60, 'f');
		$pdf->Rect(40,170, 530, 60, 'f');
		$pdf->Rect(40,230, 130, 60, 'f');
		$pdf->Rect(40,230, 530, 60, 'f');
		$pdf->Rect(40,290, 130, 30, 'f');
		$pdf->Rect(40,290, 530, 30, 'f');
		$pdf->Rect(40,320, 130, 60, 'f');
		$pdf->Rect(40,320, 530, 60, 'f');
		$pdf->Rect(40,380, 130, 90, 'f');
		
		$pdf->Rect(40,380, 530, 90, 'f');
		$pdf->Rect(40,470, 130, 30, 'f');
		$pdf->Rect(40,470, 530, 30, 'f');
		$pdf->Rect(40,500, 130, 30, 'f');
		$pdf->Rect(40,500, 530, 30, 'f');
		$pdf->Rect(40,530, 130, 30, 'f');
		$pdf->Rect(40,530, 530, 30, 'f');
		$pdf->Rect(40,560, 130, 60, 'f');
		$pdf->Rect(40,560, 530, 60, 'f');
		$pdf->Rect(40,620, 130, 30, 'f');
		$pdf->Rect(40,620, 530, 30, 'f');
		$pdf->Rect(40,650, 130, 60, 'f');
		$pdf->Rect(40,650, 530, 60, 'f');
		
		
		$pdf->SetXY(175 , 390);
		$pdf->MultiCell(400, 15,$result['OBS_DESPACHO'], 0, 'T');
		$pdf->SetXY(175 , 570);
		$pdf->MultiCell(400, 15,$result['NOM_PRODUCTO'], 0, 'T');
		$pdf->SetXY(175 , 180);
		$pdf->MultiCell(400, 15,$result['NOM_EMPRESA'], 0, 'T');
		$pdf->SetXY(175 , 240);
		$pdf->MultiCell(400, 15,$result['REFERENCIA'], 0, 'T');
		
		$pdf->SetFont('Helvetica','',8);
		$factor = 0.5;
		$pdf->Image(dirname(__FILE__)."/../../images_appl/logo_nota_venta.jpg",40,20, $factor * 850, $factor * 170);

	}
	function modifica_pdf(&$pdf) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		
		for($i=0; $i<count($result); $i++) {
			$this->dibuja_uno($pdf, $result[$i]);
			if ($i < count($result) - 1)
				$pdf->AddPage();
		}
	}
}	
?>