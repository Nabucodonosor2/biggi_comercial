<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (isset($_POST['b_excel'])){
	$ANO			= session::get("ANO");
	$MES_STR		= session::get("MES_STR");
	$TOT_IVA_VENTAS = session::get("TOT_IVA_VENTAS");
	$TOT_IVA_COMPRA = session::get("TOT_IVA_COMPRA");
	$TOT_IVA		= session::get("TOT_IVA");
	
	require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
	require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");
	
	$fname = tempnam("/tmp", "resumen.xls");
	$workbook = &new writeexcel_workbook($fname);
	
	$worksheet = &$workbook->addworksheet('Informe Pre-IVA');
	
	$worksheet->set_row(0, 60);
	$worksheet->set_column(0, 0, 4);
	$worksheet->set_column(1, 2, 7);
	$worksheet->set_column(3, 9, 14);
	$worksheet->set_column(5, 5, 60);
	$worksheet->insert_bitmap('B1',dirname(__FILE__)."/../../images_appl/logo_reporte_excel.bmp");

	$text =& $workbook->addformat();
	$text->set_font("Verdana");
	$text->set_valign('vcenter');
	
	$text_border_top =& $workbook->addformat();
	$text_border_top->copy($text);
	$text_border_top->set_top(2);
	
	$text_blue_bold_right =& $workbook->addformat();
	$text_blue_bold_right->copy($text);
	$text_blue_bold_right->set_valign('right');
	$text_blue_bold_right->set_color('blue_0x20');
	$text_blue_bold_right->set_bold(1);
    
	$worksheet->write(4, 1, "IVA Ventas :$", $text_blue_bold_right);
	$worksheet->write(6, 1, "IVA Compras :$", $text_blue_bold_right);
	$worksheet->write(7, 2, "", $text_border_top);
	$worksheet->write(7, 3, "", $text_border_top);
	$worksheet->write(7, 4, "", $text_border_top);
	$worksheet->write(8, 1, "Total IVA :$", $text_blue_bold_right);
	
	$worksheet->write(4, 4, $TOT_IVA_VENTAS, $text);
	$worksheet->write(6, 4, $TOT_IVA_COMPRA, $text);
	$worksheet->write(8, 4, $TOT_IVA, $text);
	
	$worksheet->merge_cells(4, 1, 4, 3);
	$worksheet->merge_cells(6, 1, 6, 3);
	$worksheet->merge_cells(8, 1, 8, 3);

	$workbook->close();
	
	header("Content-Type: application/x-msexcel; name=\"Inf Pre-IVA Mes $MES_STR Año $ANO.xls\"");
	header("Content-Disposition: inline; filename=\"Inf Pre-IVA Mes $MES_STR Año $ANO.xls\"");
	$fh=fopen($fname, "rb");
	fpassthru($fh);

}
else if (isset($_POST['b_cancel'])) {
	session::un_set("ANO");
	session::un_set("MES");
	session::un_set("MES_STR");
	session::un_set("TOT_IVA_VENTAS");
	session::un_set("TOT_IVA_COMPRA");
	session::un_set("TOT_IVA");
	base::presentacion();
}
else{
	$temp = new Template_appl('inf_pre_iva_result.htm');	
		
	// make_menu
	$menu = session::get('menu_appl');
	$menu->draw($temp);
	
	$ANO = session::get("ANO");
	$MES = session::get("MES");
	
	//Se extrae el ultimo dia del mes y año seleccionado
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql = "SELECT DAY(DATEADD(ms,-3,DATEADD(mm,0,DATEADD(mm, DATEDIFF(mm,0,{ts '$ANO-$MES-01 00:00:00.000'})+1,0)))) DIA
				  ,CASE $MES
				  	WHEN 01 THEN 'Enero'
				  	WHEN 02 THEN 'Febrero'
				  	WHEN 03 THEN 'Marzo'
				  	WHEN 04 THEN 'Abril'
				  	WHEN 05 THEN 'Mayo'
				  	WHEN 06 THEN 'Junio'
				  	WHEN 07 THEN 'Julio'
				  	WHEN 08 THEN 'Agosto'
				  	WHEN 09 THEN 'Septiembre'
				  	WHEN 10 THEN 'Octubre'
				  	WHEN 11 THEN 'Noviembre'
				  	WHEN 12 THEN 'Diciembre'
				  END MES_STR";
	$result = $db->build_results($sql);
	$DIA = $result[0]['DIA'];
	$MES_STR = $result[0]['MES_STR'];
	
	$sql = "SELECT SUM(MONTO_IVA) IVA_VENTAS
			FROM FACTURA
			WHERE COD_ESTADO_DOC_SII IN (2, 3) -- Impresa o Enviada a SII
			AND FECHA_FACTURA BETWEEN {ts '$ANO-$MES-01 00:00:00.000'} AND {ts '$ANO-$MES-$DIA 23:59:59.997'}";
	$result2 = $db->build_results($sql);
	
	$sql = "SELECT SUM(MONTO_IVA) IVA_VENTAS_NC
			FROM NOTA_CREDITO
			WHERE COD_ESTADO_DOC_SII IN (2, 3) -- Impresa o Enviada a SII
			AND FECHA_NOTA_CREDITO BETWEEN {ts '$ANO-$MES-01 00:00:00.000'} AND {ts '$ANO-$MES-$DIA 23:59:59.997'}";
	$result3 = $db->build_results($sql);
	
	$sql = "SELECT SUM(MONTO_IVA) IVA_COMPRA
			FROM FAPROV
			WHERE COD_ESTADO_FAPROV = 2 -- Aprobada
			AND ES_NORMALIZACION = 'N'
			AND COD_TIPO_FAPROV = 4 -- FACTURA ELECTRONICA
			AND FECHA_FAPROV BETWEEN {ts '$ANO-$MES-01 00:00:00.000'} AND {ts '$ANO-$MES-$DIA 23:59:59.997'}";
	$result4 = $db->build_results($sql);
	
	$sql = "SELECT SUM(MONTO_IVA) IVA_COMPRA_NC
			FROM NCPROV
			WHERE COD_ESTADO_NCPROV = 2 -- Aprobada
			AND FECHA_NCPROV BETWEEN {ts '$ANO-$MES-01 00:00:00.000'} AND {ts '$ANO-$MES-$DIA 23:59:59.997'}";
	$result5 = $db->build_results($sql);
	
	$IVA_VENTAS		= $result2[0]['IVA_VENTAS'];
	$IVA_VENTAS_NC	= $result3[0]['IVA_VENTAS_NC'];
	$IVA_COMPRA		= $result4[0]['IVA_COMPRA'];
	$IVA_COMPRA_NC	= $result5[0]['IVA_COMPRA_NC'];
	$TOT_IVA_VENTAS = number_format($IVA_VENTAS - $IVA_VENTAS_NC, 0, '', '.');
	$TOT_IVA_COMPRA = number_format($IVA_COMPRA - $IVA_COMPRA_NC, 0, '', '.');
	$TOT_IVA		= number_format(($IVA_VENTAS - $IVA_VENTAS_NC) - ($IVA_COMPRA - $IVA_COMPRA_NC), 0, '', '.');
	
	$temp->setVar('ANO', $ANO);
	$temp->setVar('MES', $MES_STR);
	$temp->setVar('IVA_VENTAS', $TOT_IVA_VENTAS);
	$temp->setVar('IVA_COMPRA', $TOT_IVA_COMPRA);
	$temp->setVar('TOT_IVA', $TOT_IVA);
	
	session::set("MES_STR", $MES_STR);
	session::set("TOT_IVA_VENTAS", $TOT_IVA_VENTAS);
	session::set("TOT_IVA_COMPRA", $TOT_IVA_COMPRA);
	session::set("TOT_IVA", $TOT_IVA);
	
	print $temp->toString();
}
?>