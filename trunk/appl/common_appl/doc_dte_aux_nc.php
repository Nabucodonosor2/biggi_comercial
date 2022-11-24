<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
include(dirname(__FILE__)."/../../appl.ini");
session::set('K_ROOT_DIR', K_ROOT_DIR);

require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");
							
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$fname = tempnam("/tmp", "valores_doc_dte.xls");
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
	if($s == 0)
		$BD = "BIGGI";
	else if($s == 1)
		$BD = "BODEGA_BIGGI";
	else if($s == 2)
		$BD = "RENTAL";
	else if($s == 3)
		$BD = "TODOINOX";		
	
	$worksheet = &$workbook->addworksheet($BD);
	$worksheet->set_column(0, 3, 21);
	$worksheet->write(0, 0, "Codigo Doc.", $text_blue_bold_center);
	$worksheet->write(0, 1, "Razon Distinto", $text_blue_bold_center);
	$worksheet->write(0, 2, "Valor BD", $text_blue_bold_center);
	$worksheet->write(0, 3, "Valor XML", $text_blue_bold_center);
	
	$sql = "SELECT XML_DTE
				  ,NRO_NOTA_CREDITO NRO_FACTURA
				  ,dbo.number_format(RUT, 0, ',', '.')+'-'+DIG_VERIF RUT
				  ,TOTAL_NETO
				  ,MONTO_IVA
				  ,TOTAL_CON_IVA
				  ,COD_NOTA_CREDITO COD_FACTURA
			FROM $BD.dbo.NOTA_CREDITO
			WHERE XML_DTE IS NOT NULL
			ORDER BY COD_NOTA_CREDITO";
	$result = $db->build_results($sql);
	for($i=0 ; $i < count($result) ; $i++){
		$cod_factura		= $result[$i]['COD_FACTURA'];
		$XML_DTE			= $result[$i]['XML_DTE'];
		$XML_DTE			= base64_decode($XML_DTE);
		$xml_resolucion		= simplexml_load_string($XML_DTE);
		
		$nro_factura_bd		= $result[$i]['NRO_FACTURA'];
		$rut_bd				= $result[$i]['RUT'];
		$total_neto_bd		= $result[$i]['TOTAL_NETO'];
		$monto_iva_bd		= $result[$i]['MONTO_IVA'];
		$total_con_iva_bd	= $result[$i]['TOTAL_CON_IVA'];
		
		$nro_factura		= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->IdDoc->Folio;
		$arr_rut			= explode("-", $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Receptor->RUTRecep);
		$rut				= number_format($arr_rut[0], 0, '', '.')."-".$arr_rut[1];
		$monto_iva			= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Totales->IVA;
		$total_con_iva		= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal;
		
		if($monto_iva == 0)
			$total_neto	= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Totales->MntExe;
		else	
			$total_neto	= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Totales->MntNeto;
		
		if($nro_factura_bd <> $nro_factura){
			$ln++;
			$worksheet->write($ln, 0, $cod_factura, $text);
			$worksheet->write($ln, 1, "Número factura", $text);
			$worksheet->write($ln, 2, $nro_factura_bd, $text);
			$worksheet->write($ln, 3, $nro_factura, $text);
		}
		if($rut_bd <> $rut){
			$ln++;
			$worksheet->write($ln, 0, $cod_factura, $text);
			$worksheet->write($ln, 1, "Rut", $text);
			$worksheet->write($ln, 2, $rut_bd, $text);
			$worksheet->write($ln, 3, $rut, $text);
		}	
		if($total_neto_bd <> $total_neto){
			$ln++;
			$worksheet->write($ln, 0, $cod_factura, $text);
			$worksheet->write($ln, 1, "Total Neto", $text);
			$worksheet->write($ln, 2, $total_neto_bd, $text);
			$worksheet->write($ln, 3, $total_neto, $text);
		}	
		if($monto_iva_bd <> $monto_iva){
			$ln++;
			$worksheet->write($ln, 0, $cod_factura, $text);
			$worksheet->write($ln, 1, "Monto Iva", $text);
			$worksheet->write($ln, 2, $monto_iva_bd, $text);
			$worksheet->write($ln, 3, $monto_iva, $text);
		}	
		if($total_con_iva_bd <> $total_con_iva){
			$ln++;
			$worksheet->write($ln, 0, $cod_factura, $text);
			$worksheet->write($ln, 1, "Total con IVA", $text);
			$worksheet->write($ln, 2, $total_con_iva_bd, $text);
			$worksheet->write($ln, 3, $total_con_iva, $text);
		}			
	}
}

$workbook->close();
header("Content-Type: application/x-msexcel; name=\"valores_doc_dte.xls\"");
header("Content-Disposition: inline; filename=\"valores_doc_dte.xls\"");
$fh=fopen($fname, "rb");
fpassthru($fh);
?>