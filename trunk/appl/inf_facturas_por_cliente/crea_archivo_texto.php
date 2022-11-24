<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");

$VL_RUT			= $_POST['RUT_0'];
$VL_F_INICIAL	= $_POST['F_INICIAL_0'];
$VL_F_TERMINO	= $_POST['F_TERMINO_0'];

	
$res = explode('/', $VL_F_INICIAL);
if (strlen($res[2])==2)
	$res[2] = '20'.$res[2];
$F_INICIAL = sprintf("{ts '$res[2]-$res[1]-$res[0] 00:00:00.000'}");

$res = explode('/', $VL_F_TERMINO);
if (strlen($res[2])==2)
	$res[2] = '20'.$res[2];
$F_TERMINO = sprintf("{ts '$res[2]-$res[1]-$res[0] 23:59:59.000'}");

	//acceso al procedimiento
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql = "EXEC spr_facturas_por_cliente $VL_RUT, $F_INICIAL, $F_TERMINO";
	$result = $db->build_results($sql);
	$count = count($result);
		
		//consulta solo para obtener el digito y mostrar rut en tituloprincipal	
		$sql_dig = "SELECT	DIG_VERIF FROM EMPRESA
					WHERE	RUT = ".$VL_RUT;
		$result_dig = $db->build_results($sql_dig);
		$dig_ver = $result_dig[0]['DIG_VERIF'];
		$RUT_EMPRESA = $VL_RUT."-".$dig_ver;
		$RUT_SIN_DIG = $VL_RUT;
		
		$separador = "\t";	//Usar ; como separador de campos
		$otra_linea = "\r\n";
		
		//se crea clae para trabajar la fuente
		$fname = tempnam("/tmp", "FACTURAS_POR_CLIENTE.xls");
		$workbook = &new writeexcel_workbook($fname);
		$worksheet = &$workbook->addworksheet('FACTURAS_POR_CLIENTE');
		
		// anchos de las columnas
		$worksheet->set_column(0, 0, 4, null, false);
		$worksheet->set_column(1, 1, 13, null, false);
		$worksheet->set_column(2, 2, 20, null, false);
		$worksheet->set_column(3, 3, 11, null, false);
		$worksheet->set_column(4, 4, 75, null, false);
		$worksheet->set_column(5, 5, 15, null, false);
		$worksheet->set_column(6, 6, 25, null, false);
		$worksheet->set_column(7, 7, 25, null, false);
		$worksheet->set_column(8, 8, 25, null, false);
		$worksheet->set_column(9, 9, 50, null, false);
		
		//se les da formato a la fuente
		$text =& $workbook->addformat();
		$text->set_font("Verdana");
		$text->set_valign('vcenter');
    
		$text_bold =& $workbook->addformat();
		$text_bold->copy($text);
		$text_bold->set_bold(1);
	
		$text_blue_bold_left =& $workbook->addformat();
		$text_blue_bold_left->copy($text_bold);
		$text_blue_bold_left->set_align('left');
		$text_blue_bold_left->set_color('blue_0x20');

		$text_blue_bold_center =& $workbook->addformat();
		$text_blue_bold_center->copy($text_bold);
		$text_blue_bold_center->set_align('center');
		$text_blue_bold_center->set_color('blue_0x20');
		
		$text_blue_bold_right =& $workbook->addformat();
		$text_blue_bold_right->copy($text_bold);
		$text_blue_bold_right->set_align('right');
		$text_blue_bold_right->set_color('blue_0x20');

		$text_nro_docto =& $workbook->addformat();
		$text_nro_docto->copy($text_blue_bold_right);
		$text_nro_docto->set_size(13);
		
		$text_pie_de_pagina =& $workbook->addformat();
		$text_pie_de_pagina->copy($text_blue_bold_left);
		$text_pie_de_pagina->set_size(8);
		
		$text_normal_left =& $workbook->addformat();
		$text_normal_left->copy($text);
		$text_normal_left->set_align('left');
		
		$text_normal_center =& $workbook->addformat();
		$text_normal_center->copy($text);
		$text_normal_center->set_align('center');
		
		$text_normal_right =& $workbook->addformat();
		$text_normal_right->copy($text);
		$text_normal_right->set_align('right');
				
		$text_normal_bold_left =& $workbook->addformat();
		$text_normal_bold_left->copy($text_bold);
		$text_normal_bold_left->set_align('left');
		
		
		$text_normal_bold_center =& $workbook->addformat();
		$text_normal_bold_center->copy($text_bold);
		$text_normal_bold_center->set_align('center');
	
		$text_normal_bold_right =& $workbook->addformat();
		$text_normal_bold_right->copy($text_bold);
		$text_normal_bold_right->set_align('right');
	
		
		$titulo_item_border_all =& $workbook->addformat();
		$titulo_item_border_all->copy($text_blue_bold_center);
		$titulo_item_border_all->set_border_color('black');
		$titulo_item_border_all->set_top(2);
		$titulo_item_border_all->set_bottom(2);
		$titulo_item_border_all->set_right(2);
		$titulo_item_border_all->set_left(2);
		
		$titulo_item_border_all_text_left =& $workbook->addformat();
		$titulo_item_border_all_text_left->copy($text_blue_bold_left);
		$titulo_item_border_all_text_left->set_border_color('black');
		$titulo_item_border_all_text_left->set_top(2);
		$titulo_item_border_all_text_left->set_bottom(2);
		$titulo_item_border_all_text_left->set_right(2);
		$titulo_item_border_all_text_left->set_left(2);
		
		$titulo_item_border_all_text_right =& $workbook->addformat();
		$titulo_item_border_all_text_right->copy($text_blue_bold_right);
		$titulo_item_border_all_text_right->set_border_color('black');
		$titulo_item_border_all_text_right->set_top(2);
		$titulo_item_border_all_text_right->set_bottom(2);
		$titulo_item_border_all_text_right->set_right(2);
		$titulo_item_border_all_text_right->set_left(2);
		$titulo_item_border_all_text_right->set_num_format('#,##0');				
		
		$titulo_factura =& $workbook->addformat();
		$titulo_factura->copy($titulo_item_border_all);
		$titulo_factura->set_bg_color('silver');
		
	
		$border_item_left = & $workbook->addformat();
		$border_item_left->copy($text_normal_left);
		$border_item_left->set_border_color('black');
		$border_item_left->set_left(2);
		
		$border_item_left_bold = & $workbook->addformat();
		$border_item_left_bold->copy($text_bold);
		$border_item_left_bold->set_border_color('black');
		$border_item_left_bold->set_left(2);
		
		$border_item_center = & $workbook->addformat();
		$border_item_center->copy($text_normal_left);
		$border_item_center->set_border_color('black'); //////
		$border_item_center->set_left(2);
		$border_item_center->set_right(2);
		
		$border_item_right = & $workbook->addformat();
		$border_item_right->copy($text_normal_right);
		$border_item_right->set_border_color('black');
		$border_item_right->set_right(2);		
		$border_item_right->set_num_format('#,##0');				
		
		$cant_normal =& $workbook->addformat();
		$cant_normal->copy($border_item_right);
		$cant_normal->set_num_format('0.0');
					
		$monto_normal =& $workbook->addformat();
		$monto_normal->copy($border_item_right);
		$monto_normal->set_num_format('#,##0');
		
		$border_item_top = & $workbook->addformat();
		$border_item_top->copy($text);
		$border_item_top->set_border_color('black');
		$border_item_top->set_top(2);
		
		$border_item_bottom = & $workbook->addformat();
		$border_item_bottom->copy($text);
		$border_item_bottom->set_border_color('black');
		$border_item_bottom->set_bottom(2);
		
		$border_item_especial_left = & $workbook->addformat();
		$border_item_especial_left->copy($text_normal_left);
		$border_item_especial_left->set_border_color('black');
		$border_item_especial_left->set_left(2);
		
		
		$TITULO = "FACTURAS POR CLIENTES";
		$NOMBRE_EMPRESA = $result[0]['NOM_EMPRESA'];
		
		$fecha = getdate();
		$ano = $fecha["year"];
		$mes = $fecha["mon"];
		
		$meses = array(" ","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
		
		$FECHA_FA = 'FECHA';
		$ORDEN_COMPRA = 'ORDEN DE COMPRA';
		$NRO_FACTURA = 'Nº FACTURA';
		$RAZON_SOCIAL = 'REFERENCIA';
		$TOTAL_NETO = 'TOTAL NETO';
		$FECHA_INI = substr($VL_F_INICIAL, 0, 12);
		$FECHA_FIN = substr($VL_F_TERMINO, 0, 12);
		$ESTADO_FA = 'ESTADO FA';
		$EQ_ESPECIALES = 'EQ ESPECIALES';
		$EQUIPOS_TE = 'EQUIPOS TE';
		$DISTINTO_ACUERDO = 'DISTINTO ACUERDO';
		//IMPRESION TITULO PRINCIPAL
		$worksheet->write(1,4, "FACTURAS POR CLIENTE ".$NOMBRE_EMPRESA." RUT ".$RUT_EMPRESA, $text_blue_bold_center);
		$worksheet->write(2,4, "DESDE ".$FECHA_INI." HASTA ".$FECHA_FIN, $text_normal_bold_center);
		//SE IMPRIME TITULOS DE REGISTROS
		
		$worksheet->write(5,0,"FACTURAS", $titulo_factura);
		$worksheet->write(5,4,"", $border_item_especial_left);
		$worksheet->merge_cells(5, 0, 5, 3);
		$worksheet->write(6,0,"Nº", $titulo_item_border_all);
		$worksheet->write(6,1,$FECHA_FA, $titulo_item_border_all);
		$worksheet->write(6,2,$ORDEN_COMPRA, $titulo_item_border_all);
		$worksheet->write(6,3,$NRO_FACTURA, $titulo_item_border_all);
		$worksheet->write(6,4,$RAZON_SOCIAL, $titulo_item_border_all);
		$worksheet->write(6,5,$TOTAL_NETO, $titulo_item_border_all);
		
		
		if (($RUT_SIN_DIG == '76117696') || ($RUT_SIN_DIG == '76178390') || ($RUT_SIN_DIG == '76178360')) {
			$worksheet->write(6,6,$ESTADO_FA, $titulo_item_border_all);
			$worksheet->write(6,7,$EQ_ESPECIALES, $titulo_item_border_all);
			$worksheet->write(6,8,$EQUIPOS_TE, $titulo_item_border_all);
			$worksheet->write(6,9,$DISTINTO_ACUERDO, $titulo_item_border_all);				
		}
			

		//SE IMPRIMEN REGISTROS
		//GENERA ESPACIOS EN BLANCO
		$space = ' ';
		$i = 0; 
		while($i<=100){
			$space .= ' ';
		$i++;
		}
		
		$i=7;
		for($h=0; $h<$count; $h++){
			$fecha_factura = $result[$h]['FECHA_FACTURA'];
			$nro_orden_compra = $result[$h]['NRO_ORDEN_COMPRA'];
			$nro_factura	= $result[$h]['NRO_FACTURA'];
			$referencia	= $result[$h]['REFERENCIA'];
			$total_neto	= $result[$h]['TOTAL_NETO'];
			
			$estado_fa	= $result[$h]['ESTADO_FA'];
			$eq_especiales = $result[$h]['EQ_ESPECIALES'];
			$equipos_te	= $result[$h]['EQUIPOS_TE'];
			$distinto_acuerdo = $result[$h]['DISTINTO_ACUERDO'];
			
			
			
			$fecha_factura = substr($fecha_factura.$space, 0, 12);
			$nro_orden_compra = substr($nro_orden_compra.$space, 0, 18);
			$nro_factura = substr($nro_factura.$space, 0, 18);
			$referencia = substr($referencia.$space, 0, 100);

			$worksheet->write($i,0,$h+1, $border_item_center);
			$worksheet->write($i,1,$fecha_factura, $border_item_center);
			$worksheet->write($i,2,$nro_orden_compra, $border_item_center);
			$worksheet->write($i,3,$nro_factura, $border_item_center);
			$worksheet->write($i,4,$referencia, $border_item_center);
			$worksheet->write($i,5,$total_neto, $border_item_right);

			if (($RUT_SIN_DIG == '76117696') || ($RUT_SIN_DIG == '76178390') || ($RUT_SIN_DIG == '76178360')) {
				$worksheet->write($i,6,$estado_fa, $border_item_center);
				$worksheet->write($i,7,$eq_especiales, $border_item_center);
				$worksheet->write($i,8,$equipos_te, $border_item_center);
				$worksheet->write($i,9,$distinto_acuerdo, $border_item_center);
			}

			$i++;
		}

		$TOTAL_FACTURAS = 0;
		for($i=0; $i<$count; $i++){
			$total_neto	= $result[$i]['TOTAL_NETO'];
			$TOTAL_FACTURAS += $total_neto;
		}
        
		//se coloca el borde del ultimo registro y se muestra el TOTAL_NETO
		$cantidad_registros = $count+7;
		$worksheet->write($cantidad_registros,0," ", $border_item_top);
		$worksheet->write($cantidad_registros,1," ", $border_item_top);
		$worksheet->write($cantidad_registros,2," ", $border_item_top);
		$worksheet->write($cantidad_registros,3," ", $border_item_top);			
		$worksheet->write($cantidad_registros,4,"TOTAL NETO: ", $titulo_item_border_all_text_right);
		$worksheet->write($cantidad_registros,5,$TOTAL_FACTURAS, $titulo_item_border_all_text_right);
		
		/**NOTAS DE CREDITO**/
		$sql ="SELECT	F.NRO_FACTURA
				,NC.REFERENCIA
				,NC.NRO_NOTA_CREDITO
				,NC.FECHA_NOTA_CREDITO
				,NC.TOTAL_NETO
		FROM	EMPRESA E, FACTURA F,NOTA_CREDITO NC
		WHERE	E.rut = $VL_RUT
		AND		F.COD_EMPRESA = E.COD_EMPRESA
		AND		NC.FECHA_NOTA_CREDITO BETWEEN $F_INICIAL AND $F_TERMINO
		AND     NC.COD_DOC = F.COD_FACTURA
        AND		NC.COD_ESTADO_DOC_SII in (2,3)
		ORDER BY NRO_FACTURA DESC";
		
		$result_nc = $db->build_results($sql);
		$count_nc = count($result_nc);
		
		$Y = $cantidad_registros + 5;
	    $worksheet->write($Y-1,0,"NOTAS DE CREDITO", $titulo_factura);
		$worksheet->write($Y-1,4,"", $border_item_especial_left);
		$worksheet->merge_cells($Y-1, 0, $Y-1, 3);
		$worksheet->write($Y,0,"Nº", $titulo_item_border_all);
		$worksheet->write($Y,1,$FECHA_FA, $titulo_item_border_all);
		$worksheet->write($Y,2,"N° NOTA CREDITO", $titulo_item_border_all);
		$worksheet->write($Y,3,$NRO_FACTURA, $titulo_item_border_all);
		$worksheet->write($Y,4,$RAZON_SOCIAL, $titulo_item_border_all);
		$worksheet->write($Y,5,$TOTAL_NETO, $titulo_item_border_all);
		
		$i=$Y+1;
		for($h=0; $h<$count_nc; $h++){
			$fecha = $result_nc[$h]['FECHA_NOTA_CREDITO'];
			$nro_nc = $result_nc[$h]['NRO_NOTA_CREDITO'];
			$nro_factura	= $result_nc[$h]['NRO_FACTURA'];
			$referencia	= $result_nc[$h]['REFERENCIA'];
			$total_neto	= $result_nc[$h]['TOTAL_NETO'];

			
			
			$fecha = substr($fecha.$space, 0, 12);
			$nro_nc = substr($nro_nc.$space, 0, 18);
			$nro_factura = substr($nro_factura.$space, 0, 18);
			$referencia = substr($referencia.$space, 0, 100);

			$worksheet->write($i,0,$h+1, $border_item_center);
			$worksheet->write($i,1,$fecha, $border_item_center);
			$worksheet->write($i,2,$nro_nc, $border_item_center);
			$worksheet->write($i,3,$nro_factura, $border_item_center);
			$worksheet->write($i,4,$referencia, $border_item_center);
			$worksheet->write($i,5,$total_neto, $border_item_right);
			$i++;
		}
		$cantidad_registros = $i;
		$TOTAL_NC = 0;
		for($i=0; $i<$count_nc; $i++){
			$total_neto	= $result_nc[$i]['TOTAL_NETO'];
			$TOTAL_NC += $total_neto;
		}
        
		//se coloca el borde del ultimo registro y se muestra el TOTAL_NETO
		
		$worksheet->write($cantidad_registros,0," ", $border_item_top);
		$worksheet->write($cantidad_registros,1," ", $border_item_top);
		$worksheet->write($cantidad_registros,2," ", $border_item_top);
		$worksheet->write($cantidad_registros,3," ", $border_item_top);			
		$worksheet->write($cantidad_registros,4,"TOTAL NETO: ", $titulo_item_border_all_text_right);
		$worksheet->write($cantidad_registros,5,$TOTAL_NC, $titulo_item_border_all_text_right);
		
		/** SUMA DE TOTALES**/
		$total_fn = $TOTAL_FACTURAS - $TOTAL_NC;
		$cantidad_registros = $cantidad_registros + 3;
		$worksheet->write($cantidad_registros,0," ");
		$worksheet->write($cantidad_registros,1," ");
		$worksheet->write($cantidad_registros,2," ");
		$worksheet->write($cantidad_registros,3," ");			
		$worksheet->write($cantidad_registros,4,"TOTAL NETO (Facturas - Notas de Credito) ", $titulo_item_border_all_text_right);
		$worksheet->write($cantidad_registros,5,$total_fn, $titulo_item_border_all_text_right);
		
		$sql = "SELECT dbo.f_get_porc_dscto_corporativo_empresa(cod_empresa, getdate()) PORC_DSCTO_CORPORATIVO
        from EMPRESA where RUT = $VL_RUT";
        
		$result = $db->build_results($sql);
		
		$porc_desc = $result[0]['PORC_DSCTO_CORPORATIVO'];
		$monto_desc = ($porc_desc * $total_fn)/100;
		
		$cantidad_registros = $cantidad_registros + 2;
		$worksheet->write($cantidad_registros,0," ");
		$worksheet->write($cantidad_registros,1," ");
		$worksheet->write($cantidad_registros,2," ");
		$worksheet->write($cantidad_registros,3," ");			
		$worksheet->write($cantidad_registros,4,"$porc_desc% Descuento", $titulo_item_border_all_text_right);
		$worksheet->write($cantidad_registros,5,$monto_desc, $titulo_item_border_all_text_right);
		
		//se finaliza la escritura
		$workbook->close();
		header("Content-Type: application/x-msexcel; name=\"Facturas por cliente.xls\"");
		header("Content-Disposition: inline; filename=\"Facturas por cliente.xls\"");
		$fh=fopen($fname, "rb");
		fpassthru($fh);
		unlink($fname);
		
		error_reporting(E_ALL);

?>