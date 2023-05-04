<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_vendedor.php");
require_once("class_static_num_miles.php");

ini_set('max_execution_time', 900); //900 seconds = 15 minutes

class header_num_miles extends  header_num {
	function header_num_miles($field, $field_bd, $nom_header, $cant_decimal=0, $solo_positivos=true, $operacion_accumulate='') {
		parent:: header_num($field, $field_bd, $nom_header, $cant_decimal, $solo_positivos, $operacion_accumulate);
	}
	function draw_valor_accumulate() {
		return number_format(round($this->valor_accumulate /1000, 0), 0, ',', '.');
	}
}

class wo_inf_ventas_por_mes extends w_informe_pantalla
{
	function wo_inf_ventas_por_mes() {
   		// Construye el resultado del informe en un tabla AUXILIA de INFORME
		$ano = session::get("inf_ventas_por_mes.ANO");
		session::un_set("inf_ventas_por_mes.ANO");
		
		$cod_usuario = session::get("COD_USUARIO");;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->EXECUTE_SP("spi_ventas_por_mes", "$cod_usuario, $ano"); 
		$sql = "select	I.COD_NOTA_VENTA
						,I.COD_NOTA_VENTA COD_NOTA_VENTA_H
						,I.MES
						,I.ANO
						,I.NOM_MES
						,I.FECHA_NOTA_VENTA
						,convert(varchar, I.FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA_STR
						,I.NOM_EMPRESA 
						,I.COD_USUARIO_VENDEDOR1
						,I.INI_USUARIO
						,I.SUBTOTAL
						 ,I.TOTAL_NETO
						 ,I.TOTAL_VENTA
						 ,I.PORC_DSCTO
						 ,I.MONTO_DSCTO
						 ,I.MONTO_DSCTO_CORPORATIVO
						 ,I.DESPACHADO_NETO
						 ,I.COBRADO_NETO
						 ,I.POR_COBRAR_NETO
						 ,I.NV_CONFIRMADA
						 ,I.NV_X_CONFIRMAR
						 ,I.NOM_ESTADO_NOTA_VENTA
						 ,I.COD_ESTADO_NOTA_VENTA
						 ,I.CANT_NV
				FROM INF_VENTAS_POR_MES I
				where I.COD_USUARIO = $cod_usuario
				order by I.COD_NOTA_VENTA";   		
		parent::w_informe_pantalla('inf_ventas_por_mes', $sql, $_REQUEST['cod_item_menu']);

		// tiene privilegios de exportar
		$sql = "select A.EXPORTAR
				from   AUTORIZA_MENU A, USUARIO U
		        where  U.COD_USUARIO = $this->cod_usuario
		         and	A.COD_PERFIL = U.COD_PERFIL
		         and 	 A.COD_ITEM_MENU = '".$this->cod_item_menu."'";
		$result = $db->build_results($sql);
		$exportar = $result[0]['EXPORTAR'];
	
		if ($exportar =='S') {
			$this->b_export_visible = true;
	  	}else{
			$this->b_export_visible = false;
      	}
      	/////////////
		
		$this->b_print_visible = false;
		$this->css_oscuro = "";
		
		$this->dw->add_control(new edit_text_hidden('COD_NOTA_VENTA_H'));
		
		// headers
		$this->add_header($h_mes = new header_mes('MES', 'MES', 'Mes'));
		$this->add_header(new header_num('ANO', 'ANO', 'A�o'));
		$this->add_header(new header_date('FECHA_NOTA_VENTA_STR', 'I.FECHA_NOTA_VENTA', 'Fecha'));
		$this->add_header(new header_num('COD_NOTA_VENTA', 'COD_NOTA_VENTA', 'NV'));
		$this->add_header(new header_text('NOM_EMPRESA', "NOM_EMPRESA", 'Cliente'));
		//$sql = "select distinct U.COD_USUARIO, U.NOM_USUARIO from NOTA_VENTA N, USUARIO U where N.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO order by NOM_USUARIO";
		$this->add_header(new header_vendedor('INI_USUARIO', 'I.COD_USUARIO_VENDEDOR1', 'V1'));
		$this->add_header(new header_num_miles('SUBTOTAL', 'SUBTOTAL', 'TVN s/d', 0, true, 'SUM'));
		$this->add_header(new header_num('PORC_DSCTO', 'PORC_DSCTO', '% D', 0));  		
		$this->add_header(new header_num_miles('MONTO_DSCTO', 'MONTO_DSCTO', 'Dscto', 0, true, 'SUM'));  		
		$this->add_header(new header_num_miles('MONTO_DSCTO_CORPORATIVO', "MONTO_DSCTO_CORPORATIVO", 'Dscto Corporativo', 0, true, 'SUM'));  		
		$this->add_header(new header_num_miles('TOTAL_NETO', 'TOTAL_NETO', 'TVN c/d', 0, true, 'SUM'));
		$this->add_header(new header_num_miles('TOTAL_VENTA', "TOTAL_VENTA", 'TVenta', 0, true, 'SUM'));
		$this->add_header(new header_num_miles('DESPACHADO_NETO', 'DESPACHADO_NETO', 'TEN', 0, true, 'SUM'));      
		$this->add_header(new header_num_miles('COBRADO_NETO', 'COBRADO_NETO', 'TPN', 0, true, 'SUM'));      
		$this->add_header(new header_num_miles('POR_COBRAR_NETO', 'POR_COBRAR_NETO', 'TxCN', 0, true, 'SUM'));
		$sql = "select COD_ESTADO_NOTA_VENTA, NOM_ESTADO_NOTA_VENTA from ESTADO_NOTA_VENTA order by COD_ESTADO_NOTA_VENTA";      
		$this->add_header(new header_drop_down('NOM_ESTADO_NOTA_VENTA', 'COD_ESTADO_NOTA_VENTA', 'Estado', $sql));      
		
		$this->add_header(new header_num('NV_CONFIRMADA', 'NV_CONFIRMADA', '', 0, true, 'SUM'));      
		$this->add_header(new header_num('NV_X_CONFIRMAR', 'NV_X_CONFIRMAR', '', 0, true, 'SUM'));
		$this->add_header(new header_num('CANT_NV', '1', '', 0, true, 'SUM'));
		
		
		$this->dw->add_control(new static_num_miles('SUBTOTAL'));
		$this->dw->add_control(new edit_porcentaje('PORC_DSCTO'));
		$this->dw->add_control(new static_num_miles('TOTAL_NETO'));
		$this->dw->add_control(new static_num_miles('TOTAL_VENTA'));
		$this->dw->add_control(new static_num_miles('DESPACHADO_NETO'));
		$this->dw->add_control(new static_num_miles('COBRADO_NETO'));
		$this->dw->add_control(new static_num_miles('POR_COBRAR_NETO'));
   		
   		// Filtro inicial
		$mes_desde = session::get("inf_ventas_por_mes.MES_DESDE");
		$mes_hasta = session::get("inf_ventas_por_mes.MES_HASTA");
		
		session::un_set("inf_ventas_por_mes.MES_DESDE");
		session::un_set("inf_ventas_por_mes.MES_HASTA");
		
		$h_mes->valor_filtro = $mes_desde;
		$h_mes->valor_filtro2 = $mes_hasta;
		
		$this->row_per_page = 500;
		$this->make_filtros();	// filtro incial
	}
   	function make_menu(&$temp) {
   		/*  MODIFICACION PARA USUARIO ANGEL SCIANCA, EN EL INFORME DE VENTAS SE ENCOJE EL TAMA�O DEL MENU */   		
	   	$menu = session::get('menu_appl');
	    $menu->ancho_completa_menu = 1;
	    $menu->draw($temp);
	    $menu->ancho_completa_menu = 79;	    	    	    		
    }
	function redraw(&$temp) {
		$total_dscto = $this->headers['MONTO_DSCTO']->valor_accumulate + $this->headers['MONTO_DSCTO_CORPORATIVO']->valor_accumulate;
		$temp->setVar('SUM_MONTO_DSCTO_TOTAL', number_format(round($total_dscto/1000,0), 0, ',', '.'));
		
		if ($this->headers['SUBTOTAL']->valor_accumulate==0) {
			$porc_directo = 0;
			$porc_corporativo = 0;
			$porc_total = 0;
		}
		else {
			$porc_directo = round(($this->headers['MONTO_DSCTO']->valor_accumulate / $this->headers['SUBTOTAL']->valor_accumulate) * 100, 1);
			$porc_corporativo = round(($this->headers['MONTO_DSCTO_CORPORATIVO']->valor_accumulate / $this->headers['SUBTOTAL']->valor_accumulate) * 100, 1);
			$porc_total = round((($this->headers['MONTO_DSCTO']->valor_accumulate + $this->headers['MONTO_DSCTO_CORPORATIVO']->valor_accumulate) / $this->headers['SUBTOTAL']->valor_accumulate)* 100, 1);
		}
		$temp->setVar('PORC_DSCTO_DIRECTO', number_format($porc_directo, 1, ',', '.'));
		$temp->setVar('PORC_DSCTO_CORPORATIVO', number_format($porc_corporativo, 1, ',', '.'));
		$temp->setVar('PORC_DSCTO_TOTAL', number_format($porc_total, 1, ',', '.'));
				
	}
	function get_totales() {
		// Exporta la data
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql_no_sumar	= "select COD_NOTA_VENTA FROM NO_SUMAR_NOTA_VENTA";
		$result_no_sumar= $db->build_results($sql_no_sumar);
		$array_no_sumar;
		for ($i=0; $i < count($result_no_sumar); $i++) { 
			$array_no_sumar[] = $result_no_sumar[$i]['COD_NOTA_VENTA'];
		}
		
		$sql = $this->dw->get_sql();
		$res = $db->query($sql);

		$result[0]['SUBTOTAL'] = 0;
		$result[0]['MONTO_DSCTO'] = 0;
		$result[0]['MONTO_DSCTO_CORPORATIVO'] = 0;
		$result[0]['TOTAL_NETO'] = 0;
		$result[0]['TOTAL_VENTA'] = 0;
		$result[0]['DESPACHADO_NETO'] = 0;
		$result[0]['COBRADO_NETO'] = 0;
		$result[0]['POR_COBRAR_NETO'] = 0;
		$result[0]['NV_CONFIRMADA'] = 0;
		$result[0]['NV_X_CONFIRMAR'] = 0;
		$result[0]['CANT_NV'] = 0;
		
		while($my_row = $db->get_row()){
			if ($my_row['COD_ESTADO_NOTA_VENTA']==3)		// nula
				continue;
				
			if ($my_row['COD_ESTADO_NOTA_VENTA']==2 || $my_row['COD_ESTADO_NOTA_VENTA']==4){// cerrada o confirmada
				if(!in_array($my_row['COD_NOTA_VENTA'], $array_no_sumar))
					$result[0]['NV_CONFIRMADA'] += 1;

			}else if ($my_row['COD_ESTADO_NOTA_VENTA']==1)	// emitida
				$result[0]['NV_X_CONFIRMAR'] += 1;
			
			$result[0]['SUBTOTAL'] += $my_row['SUBTOTAL'];
			$result[0]['MONTO_DSCTO'] += $my_row['SUBTOTAL'] - $my_row['TOTAL_NETO'];
			$result[0]['MONTO_DSCTO_CORPORATIVO'] += $my_row['MONTO_DSCTO_CORPORATIVO'];
			$result[0]['TOTAL_NETO'] += $my_row['TOTAL_NETO'];
			$result[0]['DESPACHADO_NETO'] += $my_row['DESPACHADO_NETO'];
			$result[0]['COBRADO_NETO'] += $my_row['COBRADO_NETO'];
			if ($my_row['POR_COBRAR_NETO'] > 0)
				$result[0]['POR_COBRAR_NETO'] += $my_row['POR_COBRAR_NETO'];
			$result[0]['CANT_NV'] += 1;
		}
		$result[0]['TOTAL_VENTA'] = $result[0]['TOTAL_NETO'] - $result[0]['MONTO_DSCTO_CORPORATIVO'];
		
		$indices = array_keys($this->headers);
		for ($i=0; $i<count($this->headers); $i++) {
			$operacion = $this->headers[$indices[$i]]->operacion_accumulate;
			if ($operacion != '')
				$this->headers[$indices[$i]]->valor_accumulate = $result[0][$indices[$i]];
		}
	}
	function export() {
		/* es identica a la de la clase w_output pero se agrego un if para que no pesque algunas columnas
		 if ($field != 'NV_CONFIRMADA' && $field != 'NV_X_CONFIRMAR' && $field != 'CANT_NV')
		 */
		
		
		ini_set('memory_limit', '128M');
		ini_set('max_execution_time', 300); //300 seconds = 5 minutes

		require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
		require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");
		
		$fname = tempnam("/tmp", "export.xls");
		$workbook = new writeexcel_workbook($fname);
		$worksheet = $workbook->addworksheet($this->nom_tabla);
		
		// escribe encabezados
		# Create a format for the column headings
		$header =& $workbook->addformat();
		$header->set_bold();
		$header->set_color('blue');

		// titulos
		$columna = 0;
		for ($j=0; $j < count($this->dw->fields); $j++) { 
			$field = $this->dw->fields[$j]->name;
			if ($field != 'NV_CONFIRMADA' && $field != 'NV_X_CONFIRMAR' && $field != 'CANT_NV') {
				// Solo los campos que tienen Header
				if (isset($this->headers[$field])) {
					$nom_header = $this->headers[$field]->nom_header;
					$worksheet->write(0, $columna,  $nom_header, $header);
					$columna++;
				}
			}
		}
		
		// Exporta la data
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$this->make_filtros();
		$sql = $this->dw->get_sql();
		$res = $db->query($sql);
		$i = 0;
		while($my_row = $db->get_row()){
			if ($worksheet->_datasize > 7000000)
				break;
			$columna = 0;
			for ($j=0; $j < count($this->dw->fields); $j++) {
				$field = $this->dw->fields[$j]->name;
				if ($field != 'NV_CONFIRMADA' && $field != 'NV_X_CONFIRMAR' && $field != 'CANT_NV') {
					// 	Solo los campos que tienen Header
					if (isset($this->headers[$field])) {
						if ($field=='ROW')
							$worksheet->write($i + 1, $columna, $i + 1);
						else
							$worksheet->write($i + 1, $columna, $my_row[$field]);
						$columna++;
					}
				}
			}
			$i++;
		}
		if ($worksheet->_datasize > 7000000) {
			$worksheet->write($i + 1, 0, 'No se completo la exportaci�n de datos porque excede el m�ximo del tama�o de archivo 7 MB', $header);
		}
		
		if($db->database_type=="oci") {
			oci_free_statement($db->query_id);
			$db->query_id = false;
		}
		
		$workbook->close();
		
		header("Content-Type: application/x-msexcel; name=\"$this->nom_tabla\"");
		header("Content-Disposition: inline; filename=\"$this->nom_tabla.xls\"");
		$fh=fopen($fname, "rb");
		fpassthru($fh);
		unlink($fname);
	}
}
?>