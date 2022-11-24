<?php
//ini_set('display_errors', 'On');
ini_set('memory_limit', '480M');

$fecha_inicio = "{ts '2020-06-01 00:00:00.000'}";

require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
include(dirname(__FILE__)."/../../appl.ini");
session::set('K_ROOT_DIR', K_ROOT_DIR);

require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");
							
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);


$sql = "select convert(varchar, max(dbo.to_date(replace(fecha_documento, '-', '/'))), 103) FECHA_MAX
	from DOCUMENTO_DTE_AUX";
$result = $db->build_results($sql);
$fecha_termino = base::str2date($result[0]['FECHA_MAX'], '23:59:59');


$fname = tempnam("/tmp", "valores_doc_dte2.xls");
$workbook = &new writeexcel_workbook($fname);

//se les da formato a la fuente
$text =& $workbook->addformat();
$text->set_font("Arial");
$text->set_valign('vbottom');
$text->set_align('left');

$text_blue_bold_center =& $workbook->addformat();
$text_blue_bold_center->copy($text);
$text_blue_bold_center->set_bold(1);
$text_blue_bold_center->set_color('blue');

for($s=0 ; $s < 4 ; $s++){
	$ln = 0;
	$lna = 0;
	$ln2 = 0;
	if($s == 0)
		$BD = "BIGGI";
	else if($s == 1)
		$BD = "BODEGA_BIGGI";
	else if($s == 2)
		$BD = "RENTAL";
	else if($s == 3)
		$BD = "TODOINOX";		
	
	//nro que esten en bd y no esten sii
	if($s == 2)	// RENTTAL
		$sql = "SELECT NRO_FACTURA
				FROM $BD.dbo.FACTURA
				WHERE NRO_FACTURA NOT IN (SELECT NRO_DOCUMENTO
										  FROM BIGGI.dbo.DOCUMENTO_DTE_AUX
										  WHERE TIPO_DOCUMENTO in ('FA', 'FAEX'))
				AND FECHA_FACTURA > $fecha_inicio
				AND FECHA_FACTURA <= $fecha_termino
				AND COD_ESTADO_DOC_SII = 3";
	else
		$sql = "SELECT NRO_FACTURA
				FROM $BD.dbo.FACTURA
				WHERE NRO_FACTURA NOT IN (SELECT NRO_DOCUMENTO
										  FROM $BD.dbo.DOCUMENTO_DTE_AUX
										  WHERE TIPO_DOCUMENTO in ('FA', 'FAEX'))
				AND FECHA_FACTURA > $fecha_inicio
				AND FECHA_FACTURA <= $fecha_termino
				AND COD_ESTADO_DOC_SII = 3";
				
	$result_fa = $db->build_results($sql);
	
	if($s == 2)
		$sql = "SELECT NRO_NOTA_CREDITO
				FROM $BD.dbo.NOTA_CREDITO
				WHERE NRO_NOTA_CREDITO NOT IN (SELECT NRO_DOCUMENTO
										  	   FROM BIGGI.dbo.DOCUMENTO_DTE_AUX
										  	   WHERE TIPO_DOCUMENTO = 'NC')
				AND FECHA_NOTA_CREDITO > $fecha_inicio
				AND FECHA_NOTA_CREDITO <= $fecha_termino
				AND COD_ESTADO_DOC_SII = 3";
	else
		$sql = "SELECT NRO_NOTA_CREDITO
				FROM $BD.dbo.NOTA_CREDITO
				WHERE NRO_NOTA_CREDITO NOT IN (SELECT NRO_DOCUMENTO
										  	   FROM $BD.dbo.DOCUMENTO_DTE_AUX
										  	   WHERE TIPO_DOCUMENTO = 'NC')
				AND FECHA_NOTA_CREDITO > $fecha_inicio
				AND FECHA_NOTA_CREDITO <= $fecha_termino
				AND COD_ESTADO_DOC_SII = 3";
				
	$result_nc = $db->build_results($sql);
	
	$worksheet = &$workbook->addworksheet("N° bd y no sii($BD)");
	$worksheet->set_column(0, 1, 21);
	$worksheet->write(0, 0, "Tipo Doc.", $text_blue_bold_center);
	$worksheet->write(0, 1, "Numero Doc.", $text_blue_bold_center);
	
	for($j=0 ; $j < count($result_fa) ; $j++){
		$lna++;
		$worksheet->write($lna, 0, "FA", $text);
		$worksheet->write($lna, 1, $result_fa[$j]['NRO_FACTURA'], $text);
	}
	
	for($j=0 ; $j < count($result_nc) ; $j++){
		$lna++;
		$worksheet->write($lna, 0, "NC", $text);
		$worksheet->write($lna, 1, $result_nc[$j]['NRO_NOTA_CREDITO'], $text);
	}
		
	if($s == 0){
		//que estan en sii y no estan en bd
		$sql = "SELECT NRO_DOCUMENTO
				FROM BIGGI.dbo.DOCUMENTO_DTE_AUX
				WHERE NRO_DOCUMENTO NOT IN (SELECT NRO_FACTURA
											FROM BIGGI.dbo.FACTURA
											WHERE FECHA_FACTURA > $fecha_inicio
											AND COD_ESTADO_DOC_SII = 3 
											UNION
											SELECT NRO_FACTURA
											FROM RENTAL.dbo.FACTURA
											WHERE FECHA_FACTURA > $fecha_inicio
											AND COD_ESTADO_DOC_SII = 3)
				AND TIPO_DOCUMENTO in ('FA', 'FAEX')
				and dbo.to_date(replace(FECHA_DOCUMENTO, '-', '/'))	> $fecha_inicio";
		$result_fa = $db->build_results($sql);
		
		$sql = "SELECT NRO_DOCUMENTO
				FROM BIGGI.dbo.DOCUMENTO_DTE_AUX
				WHERE NRO_DOCUMENTO NOT IN (SELECT NRO_NOTA_CREDITO
											FROM BIGGI.dbo.NOTA_CREDITO
											WHERE FECHA_NOTA_CREDITO > $fecha_inicio
											AND COD_ESTADO_DOC_SII = 3 
											UNION
											SELECT NRO_NOTA_CREDITO
											FROM RENTAL.dbo.NOTA_CREDITO
											WHERE FECHA_NOTA_CREDITO > $fecha_inicio
											AND COD_ESTADO_DOC_SII = 3)
				AND TIPO_DOCUMENTO = 'NC'
				and dbo.to_date(replace(FECHA_DOCUMENTO, '-', '/'))	> $fecha_inicio";
		$result_nc = $db->build_results($sql);
		
		$worksheet = &$workbook->addworksheet("N° sii y no bd(BIGGI)");
		$worksheet->set_column(0, 1, 21);
		$worksheet->write(0, 0, "Tipo Doc.", $text_blue_bold_center);
		$worksheet->write(0, 1, "Numero Doc.", $text_blue_bold_center);
		
		for($j=0 ; $j < count($result_fa) ; $j++){
			if ($s==0 && ($result_fa[$j]['NRO_DOCUMENTO'] == 201)) {
				//05-09-2019 COMERCIAL se deja fuera FA_EXENTA nro_factura 201, se hizo en portal lbredte 
				continue;
			}
			$ln2++;
			$worksheet->write($ln2, 0, "FA", $text);
			$worksheet->write($ln2, 1, $result_fa[$j]['NRO_DOCUMENTO'], $text);
		}
		
		for($j=0 ; $j < count($result_nc) ; $j++){
			$ln2++;
			$worksheet->write($ln2, 0, "NC", $text);
			$worksheet->write($ln2, 1, $result_nc[$j]['NRO_DOCUMENTO'], $text);
		}
	}else if($s == 2){
		//que estan en sii y no estan en bd
		//NADA
	}else{
		//que estan en sii y no estan en bd
		$sql = "SELECT NRO_DOCUMENTO
				FROM $BD.dbo.DOCUMENTO_DTE_AUX
				WHERE NRO_DOCUMENTO NOT IN (SELECT NRO_FACTURA
											FROM $BD.dbo.FACTURA
											WHERE FECHA_FACTURA > $fecha_inicio
											AND COD_ESTADO_DOC_SII = 3)
				AND TIPO_DOCUMENTO in ('FA', 'FAEX')
				and dbo.to_date(replace(FECHA_DOCUMENTO, '-', '/'))	> $fecha_inicio";
		$result_fa = $db->build_results($sql);
		
		$sql = "SELECT NRO_DOCUMENTO
				FROM $BD.dbo.DOCUMENTO_DTE_AUX
				WHERE NRO_DOCUMENTO NOT IN (SELECT NRO_NOTA_CREDITO
											FROM $BD.dbo.NOTA_CREDITO
											WHERE FECHA_NOTA_CREDITO > $fecha_inicio
											AND COD_ESTADO_DOC_SII = 3)
				AND TIPO_DOCUMENTO = 'NC'
				and dbo.to_date(replace(FECHA_DOCUMENTO, '-', '/'))	> $fecha_inicio";
		$result_nc = $db->build_results($sql);
		
		$worksheet = &$workbook->addworksheet("N° sii y no bd($BD)");
		$worksheet->set_column(0, 1, 21);
		$worksheet->write(0, 0, "Tipo Doc.", $text_blue_bold_center);
		$worksheet->write(0, 1, "Numero Doc.", $text_blue_bold_center);
		
		for($j=0 ; $j < count($result_fa) ; $j++){
			if ($s==3 && ($result_fa[$j]['NRO_DOCUMENTO'] == 215267 || $result_fa[$j]['NRO_DOCUMENTO'] == 215268)) {
				//08-03-2018 TODOINOX se deja fuera nro_factura 215267 y 215268, es una prueba facturacion de tdnx a MH, se deben agregar bd 
				continue;
			}
			
			$ln2++;
			$worksheet->write($ln2, 0, "FA", $text);
			$worksheet->write($ln2, 1, $result_fa[$j]['NRO_DOCUMENTO'], $text);
		}
		
		for($j=0 ; $j < count($result_nc) ; $j++){
			if ($s==1 && ($result_nc[$j]['NRO_DOCUMENTO'] == 103022)) {
				//08-03-2018 BODEGA_BIGGI se deja fuera nro_nota_credito 103022, en bd se encuentra anulkada MH regularizara... 
				continue;
			}
			
			$ln2++;
			$worksheet->write($ln2, 0, "NC", $text);
			$worksheet->write($ln2, 1, $result_nc[$j]['NRO_DOCUMENTO'], $text);
		}
	}

	$worksheet = &$workbook->addworksheet($BD);
	$worksheet->set_column(0, 3, 21);
	$worksheet->write(0, 0, "Tipo Doc.", $text_blue_bold_center);
	$worksheet->write(0, 1, "Numero Doc.", $text_blue_bold_center);
	$worksheet->write(0, 2, "Razon Distinto", $text_blue_bold_center);
	$worksheet->write(0, 3, "Valor BD", $text_blue_bold_center);
	$worksheet->write(0, 4, "Valor SII", $text_blue_bold_center);
	
	if($s <> 2)
		$sql = "SELECT D.NRO_DOCUMENTO												NRO_FACTURA_SII
					  ,D.RUT														RUT_SII
					  ,TOTAL_NETO													TOTAL_NETO_SII
					  ,D.MONTO_IVA													MONTO_IVA_SII
					  ,D.TOTAL_CON_IVA												TOTAL_CON_IVA_SII
					  ,D.FECHA_DOCUMENTO											FECHA_DOCUMENTO_SII
					  ,CONVERT(VARCHAR, F.RUT)+'-'+F.DIG_VERIF						RUT_BD
					  ,F.TOTAL_NETO													TOTAL_NETO_BD
					  ,F.MONTO_IVA													MONTO_IVA_BD
					  ,F.TOTAL_CON_IVA												TOTAL_CON_IVA_BD
					  ,REPLACE(CONVERT(VARCHAR, F.FECHA_FACTURA, 103), '/', '-')	FECHA_DOCUMENTO_BD
				FROM $BD.dbo.DOCUMENTO_DTE_AUX D
					,$BD.dbo.FACTURA F
				WHERE TIPO_DOCUMENTO in ('FA', 'FAEX')
				AND F.NRO_FACTURA = D.NRO_DOCUMENTO";
	else
		$sql = "SELECT D.NRO_DOCUMENTO												NRO_FACTURA_SII
					  ,D.RUT														RUT_SII
					  ,TOTAL_NETO													TOTAL_NETO_SII
					  ,D.MONTO_IVA													MONTO_IVA_SII
					  ,D.TOTAL_CON_IVA												TOTAL_CON_IVA_SII
					  ,D.FECHA_DOCUMENTO											FECHA_DOCUMENTO_SII
					  ,CONVERT(VARCHAR, F.RUT)+'-'+F.DIG_VERIF						RUT_BD
					  ,F.TOTAL_NETO													TOTAL_NETO_BD
					  ,F.MONTO_IVA													MONTO_IVA_BD
					  ,F.TOTAL_CON_IVA												TOTAL_CON_IVA_BD
					  ,REPLACE(CONVERT(VARCHAR, F.FECHA_FACTURA, 103), '/', '-')	FECHA_DOCUMENTO_BD
				FROM BIGGI.dbo.DOCUMENTO_DTE_AUX D
					,$BD.dbo.FACTURA F
				WHERE TIPO_DOCUMENTO in ('FA', 'FAEX')
				AND F.NRO_FACTURA = D.NRO_DOCUMENTO";		
			
	$result = $db->build_results($sql);
	for($i=0 ; $i < count($result) ; $i++){
		$nro_factura		= $result[$i]['NRO_FACTURA_SII'];
		
		if ($s==3 && $nro_factura == 215270) {
			//08-03-2018 se deja fuera nro_factura 215270, es una prueba facturacion de tdnx a MH cambiando fecha
			continue;
		}
		
		$rut_bd				= $result[$i]['RUT_BD'];
		$total_neto_bd		= $result[$i]['TOTAL_NETO_BD'];
		$monto_iva_bd		= $result[$i]['MONTO_IVA_BD'];
		$total_con_iva_bd	= $result[$i]['TOTAL_CON_IVA_BD'];
		$fecha_documento_bd	= $result[$i]['FECHA_DOCUMENTO_BD'];
		
		$rut				= $result[$i]['RUT_SII'];
		$total_neto			= $result[$i]['TOTAL_NETO_SII'];
		$monto_iva			= $result[$i]['MONTO_IVA_SII'];
		$total_con_iva		= $result[$i]['TOTAL_CON_IVA_SII'];
		$fecha_documento	= $result[$i]['FECHA_DOCUMENTO_SII'];
		
		if($rut_bd <> $rut){
			$ln++;
			$worksheet->write($ln, 0, "FA", $text);
			$worksheet->write($ln, 1, $nro_factura, $text);
			$worksheet->write($ln, 2, "Rut", $text);
			$worksheet->write($ln, 3, $rut_bd, $text);
			$worksheet->write($ln, 4, $rut, $text);
		}	
		if($total_neto_bd <> $total_neto){
			$ln++;
			$worksheet->write($ln, 0, "FA", $text);
			$worksheet->write($ln, 1, $nro_factura, $text);
			$worksheet->write($ln, 2, "Total Neto", $text);
			$worksheet->write($ln, 3, $total_neto_bd, $text);
			$worksheet->write($ln, 4, $total_neto, $text);
		}	
		if($monto_iva_bd <> $monto_iva){
			$ln++;
			$worksheet->write($ln, 0, "FA", $text);
			$worksheet->write($ln, 1, $nro_factura, $text);
			$worksheet->write($ln, 2, "Monto Iva", $text);
			$worksheet->write($ln, 3, $monto_iva_bd, $text);
			$worksheet->write($ln, 4, $monto_iva, $text);
		}	
		if($total_con_iva_bd <> $total_con_iva){
			$ln++;
			$worksheet->write($ln, 0, "FA", $text);
			$worksheet->write($ln, 1, $nro_factura, $text);
			$worksheet->write($ln, 2, "Total con IVA", $text);
			$worksheet->write($ln, 3, $total_con_iva_bd, $text);
			$worksheet->write($ln, 4, $total_con_iva, $text);
		}
		if($fecha_documento_bd <> $fecha_documento){
			$ln++;
			$worksheet->write($ln, 0, "FA", $text);
			$worksheet->write($ln, 1, $nro_factura, $text);
			$worksheet->write($ln, 2, "Fecha doc.", $text);
			$worksheet->write($ln, 3, $fecha_documento_bd, $text);
			$worksheet->write($ln, 4, $fecha_documento, $text);
		}		
	}
	
	if($s <> 2)
		$sql = "SELECT D.NRO_DOCUMENTO													NRO_NOTA_CREDITO_SII
					  ,D.RUT															RUT_SII
					  ,TOTAL_NETO														TOTAL_NETO_SII
					  ,D.MONTO_IVA														MONTO_IVA_SII
					  ,D.TOTAL_CON_IVA													TOTAL_CON_IVA_SII
					  ,D.FECHA_DOCUMENTO												FECHA_DOCUMENTO_SII
					  ,CONVERT(VARCHAR, F.RUT)+'-'+F.DIG_VERIF							RUT_BD
					  ,F.TOTAL_NETO														TOTAL_NETO_BD
					  ,F.MONTO_IVA														MONTO_IVA_BD
					  ,F.TOTAL_CON_IVA													TOTAL_CON_IVA_BD
					  ,REPLACE(CONVERT(VARCHAR, F.FECHA_NOTA_CREDITO, 103), '/', '-')	FECHA_DOCUMENTO_BD
				FROM $BD.dbo.DOCUMENTO_DTE_AUX D
					,$BD.dbo.NOTA_CREDITO F
				WHERE TIPO_DOCUMENTO = 'NC'
				AND F.NRO_NOTA_CREDITO = D.NRO_DOCUMENTO";
	else
		$sql = "SELECT D.NRO_DOCUMENTO													NRO_NOTA_CREDITO_SII
					  ,D.RUT															RUT_SII
					  ,TOTAL_NETO														TOTAL_NETO_SII
					  ,D.MONTO_IVA														MONTO_IVA_SII
					  ,D.TOTAL_CON_IVA													TOTAL_CON_IVA_SII
					  ,D.FECHA_DOCUMENTO												FECHA_DOCUMENTO_SII
					  ,CONVERT(VARCHAR, F.RUT)+'-'+F.DIG_VERIF							RUT_BD
					  ,F.TOTAL_NETO														TOTAL_NETO_BD
					  ,F.MONTO_IVA														MONTO_IVA_BD
					  ,F.TOTAL_CON_IVA													TOTAL_CON_IVA_BD
					  ,REPLACE(CONVERT(VARCHAR, F.FECHA_NOTA_CREDITO, 103), '/', '-')	FECHA_DOCUMENTO_BD
				FROM BIGGI.dbo.DOCUMENTO_DTE_AUX D
					,$BD.dbo.NOTA_CREDITO F
				WHERE TIPO_DOCUMENTO = 'NC'
				AND F.NRO_NOTA_CREDITO = D.NRO_DOCUMENTO";
			
	$result = $db->build_results($sql);
	for($i=0 ; $i < count($result) ; $i++){	
		$nro_nota_credito		= $result[$i]['NRO_NOTA_CREDITO_SII'];	
	
		if ($s==1 && ($nro_nota_credito == 103052 || $nro_nota_credito==103053)) {
			//08-03-2018 se deja fuera NC 103052 y 103053, es un error y MH las regularizara
			continue;
		}
		
		$rut_bd					= $result[$i]['RUT_BD'];
		$total_neto_bd			= $result[$i]['TOTAL_NETO_BD'];
		$monto_iva_bd			= $result[$i]['MONTO_IVA_BD'];
		$total_con_iva_bd		= $result[$i]['TOTAL_CON_IVA_BD'];
		$fecha_documento_bd		= $result[$i]['FECHA_DOCUMENTO_BD'];
		
		$rut				= $result[$i]['RUT_SII'];
		$total_neto			= $result[$i]['TOTAL_NETO_SII'];
		$monto_iva			= $result[$i]['MONTO_IVA_SII'];
		$total_con_iva		= $result[$i]['TOTAL_CON_IVA_SII'];
		$fecha_documento	= $result[$i]['FECHA_DOCUMENTO_SII'];
		
		if($rut_bd <> $rut){
			$ln++;
			$worksheet->write($ln, 0, "NC", $text);
			$worksheet->write($ln, 1, $nro_nota_credito, $text);
			$worksheet->write($ln, 2, "Rut", $text);
			$worksheet->write($ln, 3, $rut_bd, $text);
			$worksheet->write($ln, 4, $rut, $text);
		}	
		if($total_neto_bd <> $total_neto){
			$ln++;
			$worksheet->write($ln, 0, "NC", $text);
			$worksheet->write($ln, 1, $nro_nota_credito, $text);
			$worksheet->write($ln, 2, "Total Neto", $text);
			$worksheet->write($ln, 3, $total_neto_bd, $text);
			$worksheet->write($ln, 4, $total_neto, $text);
		}	
		if($monto_iva_bd <> $monto_iva){
			$ln++;
			$worksheet->write($ln, 0, "NC", $text);
			$worksheet->write($ln, 1, $nro_nota_credito, $text);
			$worksheet->write($ln, 2, "Monto Iva", $text);
			$worksheet->write($ln, 3, $monto_iva_bd, $text);
			$worksheet->write($ln, 4, $monto_iva, $text);
		}	
		if($total_con_iva_bd <> $total_con_iva){
			$ln++;
			$worksheet->write($ln, 0, "NC", $text);
			$worksheet->write($ln, 1, $nro_nota_credito, $text);
			$worksheet->write($ln, 2, "Total con IVA", $text);
			$worksheet->write($ln, 3, $total_con_iva_bd, $text);
			$worksheet->write($ln, 4, $total_con_iva, $text);
		}
		if($fecha_documento_bd <> $fecha_documento){
			$ln++;
			$worksheet->write($ln, 0, "NC", $text);
			$worksheet->write($ln, 1, $nro_nota_credito, $text);
			$worksheet->write($ln, 2, "Fecha doc.", $text);
			$worksheet->write($ln, 3, $fecha_documento_bd, $text);
			$worksheet->write($ln, 4, $fecha_documento, $text);
		}	
	}
}

$workbook->close();
header("Content-Type: application/x-msexcel; name=\"valores_doc_dte2.xls\"");
header("Content-Disposition: inline; filename=\"valores_doc_dte2.xls\"");
$fh=fopen($fname, "rb");
fpassthru($fh);
?>