<?php
//	http://www.biggi.cl/sysbiggi_new/comercial_biggi/biggi/trunk/appl/revision/doc_dte_aux.php
ini_set("display_errors", "off");
$FECHA_REVISION = "{ts '2019-01-01 00:00:00.000'}";

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
	$worksheet->write(0, 0, "Tipo Doc.", $text_blue_bold_center);
	$worksheet->write(0, 1, "Codigo Doc.", $text_blue_bold_center);
	$worksheet->write(0, 2, "Razon Distinto", $text_blue_bold_center);
	$worksheet->write(0, 3, "Valor BD", $text_blue_bold_center);
	$worksheet->write(0, 4, "Valor XML", $text_blue_bold_center);
	
	$sql = "SELECT XML_DTE
				  ,NRO_FACTURA
				  ,dbo.number_format(RUT, 0, ',', '.')+'-'+DIG_VERIF RUT
				  ,TOTAL_NETO
				  ,MONTO_IVA
				  ,TOTAL_CON_IVA
				  ,COD_FACTURA
			FROM $BD.dbo.FACTURA
			WHERE XML_DTE IS NOT NULL
			and FECHA_FACTURA >= $FECHA_REVISION
			ORDER BY COD_FACTURA";
	$result = $db->build_results($sql);
	for($i=0 ; $i < count($result) ; $i++){
		$cod_factura		= $result[$i]['COD_FACTURA'];

		if ($s==1 && $cod_factura==12835) {
			//08-03-2018 se deja fuera cod_factura 12835, es una prueba de integrasystem del 08-11-2017 en BODEGA_BIGGI
			continue;
		}

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
			$worksheet->write($ln, 0, "FA", $text);
			$worksheet->write($ln, 1, $cod_factura, $text);
			$worksheet->write($ln, 2, "N�mero factura", $text);
			$worksheet->write($ln, 3, $nro_factura_bd, $text);
			$worksheet->write($ln, 4, $nro_factura, $text);
		}
		if($rut_bd <> $rut){
			$ln++;
			$worksheet->write($ln, 0, "FA", $text);
			$worksheet->write($ln, 1, $cod_factura, $text);
			$worksheet->write($ln, 2, "Rut", $text);
			$worksheet->write($ln, 3, $rut_bd, $text);
			$worksheet->write($ln, 4, $rut, $text);
		}	
		if($total_neto_bd <> $total_neto){
			$ln++;
			$worksheet->write($ln, 0, "FA", $text);
			$worksheet->write($ln, 1, $cod_factura, $text);
			$worksheet->write($ln, 2, "Total Neto", $text);
			$worksheet->write($ln, 3, $total_neto_bd, $text);
			$worksheet->write($ln, 4, $total_neto, $text);
		}	
		if($monto_iva_bd <> $monto_iva){
			$ln++;
			$worksheet->write($ln, 0, "FA", $text);
			$worksheet->write($ln, 1, $cod_factura, $text);
			$worksheet->write($ln, 2, "Monto Iva", $text);
			$worksheet->write($ln, 3, $monto_iva_bd, $text);
			$worksheet->write($ln, 4, $monto_iva, $text);
		}	
		if($total_con_iva_bd <> $total_con_iva){
			$ln++;
			$worksheet->write($ln, 0, "FA", $text);
			$worksheet->write($ln, 1, $cod_factura, $text);
			$worksheet->write($ln, 2, "Total con IVA", $text);
			$worksheet->write($ln, 3, $total_con_iva_bd, $text);
			$worksheet->write($ln, 4, $total_con_iva, $text);
		}			
	}
	
	$sql = "SELECT XML_DTE
				  ,NRO_GUIA_DESPACHO
				  ,dbo.number_format(RUT, 0, ',', '.')+'-'+DIG_VERIF RUT
				  ,COD_GUIA_DESPACHO
				  ,COD_TIPO_GD_INTERNO_SII
			FROM $BD.dbo.GUIA_DESPACHO
			WHERE XML_DTE IS NOT NULL
			and FECHA_GUIA_DESPACHO >= $FECHA_REVISION
			ORDER BY COD_GUIA_DESPACHO";
	$result = $db->build_results($sql);
	for($i=0 ; $i < count($result) ; $i++){
		$cod_guia_despacho	= $result[$i]['COD_GUIA_DESPACHO'];
	
		if ($s==2 && 
				($cod_guia_despacho==1659 || $cod_guia_despacho==1660 || $cod_guia_despacho==1662 || $cod_guia_despacho==1663 ||
				$cod_guia_despacho==1664 || $cod_guia_despacho==1665 || $cod_guia_despacho==1667 || $cod_guia_despacho==1668 ||
				$cod_guia_despacho==1669 || $cod_guia_despacho==1670 || $cod_guia_despacho==1671 || $cod_guia_despacho==1673 ||
				$cod_guia_despacho==1674 || $cod_guia_despacho==1676 || $cod_guia_despacho==1678 || $cod_guia_despacho==1679 ||
				$cod_guia_despacho==1680 || $cod_guia_despacho==1681 || $cod_guia_despacho==1683 || $cod_guia_despacho==1684 ||
				$cod_guia_despacho==1685 || $cod_guia_despacho==1688 || $cod_guia_despacho==1689 || $cod_guia_despacho==1690 ||
				$cod_guia_despacho==1691)
			) 
		{
			//09-03-2018 RENTAL son GD que quedaron malas por ser TRASLADO INTERNO, n
			continue;
		}
	
		$XML_DTE			= $result[$i]['XML_DTE'];
		$XML_DTE			= base64_decode($XML_DTE);
		$xml_resolucion		= simplexml_load_string($XML_DTE);
		$nro_guia_despacho_bd	= $result[$i]['NRO_GUIA_DESPACHO'];
		$cod_tipo_gd_interno_sii_bd = $result[$i]['COD_TIPO_GD_INTERNO_SII'];
		
		$rut_bd					= $result[$i]['RUT'];
		
		$nro_guia_despacho	= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->IdDoc->Folio;
		$arr_rut			= explode("-", $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Receptor->RUTRecep);
		$rut				= number_format($arr_rut[0], 0, '', '.')."-".$arr_rut[1];
		$cod_tipo_gd_interno_sii = $xml_resolucion->SetDTE->DTE->Documento->Encabezado->IdDoc->IndTraslado;

		if($nro_guia_despacho_bd <> $nro_guia_despacho){
			$ln++;
			$worksheet->write($ln, 0, "GD", $text);
			$worksheet->write($ln, 1, $cod_guia_despacho, $text);
			$worksheet->write($ln, 2, "N�mero Gu�a Despacho", $text);
			$worksheet->write($ln, 3, $nro_guia_despacho_bd, $text);
			$worksheet->write($ln, 4, $nro_guia_despacho, $text);
		}
		if($rut_bd <> $rut){
			$ln++;
			$worksheet->write($ln, 0, "GD", $text);
			$worksheet->write($ln, 1, $cod_guia_despacho, $text);
			$worksheet->write($ln, 2, "Rut", $text);
			$worksheet->write($ln, 3, $rut_bd, $text);
			$worksheet->write($ln, 4, $rut, $text);
		}	
		if($cod_tipo_gd_interno_sii_bd <> $cod_tipo_gd_interno_sii){
			$ln++;
			$worksheet->write($ln, 0, "GD", $text);
			$worksheet->write($ln, 1, $cod_guia_despacho, $text);
			$worksheet->write($ln, 2, "IndTraslado", $text);
			$worksheet->write($ln, 3, $cod_tipo_guia_despacho_bd, $text);
			$worksheet->write($ln, 4, $cod_tipo_guia_despacho, $text);
		}
		if($cod_tipo_gd_interno_sii <> 1 && $cod_tipo_gd_interno_sii <> 6){
			$ln++;
			$worksheet->write($ln, 0, "GD", $text);
			$worksheet->write($ln, 1, $cod_guia_despacho, $text);
			$worksheet->write($ln, 2, "IndTraslado<>1,6", $text);
			$worksheet->write($ln, 3, $cod_tipo_guia_despacho_bd, $text);
			$worksheet->write($ln, 4, $cod_tipo_guia_despacho, $text);
		}
	}
	
	$sql = "SELECT XML_DTE
				  ,NRO_NOTA_CREDITO
				  ,dbo.number_format(RUT, 0, ',', '.')+'-'+DIG_VERIF RUT
				  ,TOTAL_NETO
				  ,MONTO_IVA
				  ,TOTAL_CON_IVA
				  ,COD_NOTA_CREDITO
			FROM $BD.dbo.NOTA_CREDITO
			WHERE XML_DTE IS NOT NULL
			and FECHA_NOTA_CREDITO >= $FECHA_REVISION
			ORDER BY COD_NOTA_CREDITO";
	$result = $db->build_results($sql);
	for($i=0 ; $i < count($result) ; $i++){
		$cod_nota_credito	= $result[$i]['COD_NOTA_CREDITO'];

		if ($s==0 && $cod_nota_credito==3043) {
			/* 08-03-2018 Se deja fuera NC cod_nota_credito 3043
			Este caso fue revisado 08-03-2018 por VM y MH
			el RUT tiene 2 FA y 3 NC
			la 3ra NC esta correcta y es la que se envio al cliente
			las NC nro 11493, 11506 = estan anuladas con ND
			*/
			continue;
		}
		else if ($s==1 && $cod_nota_credito==405) {
			//08-03-2018 se deja fuera cod_nota_credito 405, es una prueba de integrasystem del 08-11-2017 en BODEGA_BIGGI
			continue;
		}


		$XML_DTE			= $result[$i]['XML_DTE'];
		$XML_DTE			= base64_decode($XML_DTE);
		$xml_resolucion		= simplexml_load_string($XML_DTE);
		
		$nro_nota_credito_bd	= $result[$i]['NRO_NOTA_CREDITO'];
		$rut_bd					= $result[$i]['RUT'];
		$total_neto_bd			= $result[$i]['TOTAL_NETO'];
		$monto_iva_bd			= $result[$i]['MONTO_IVA'];
		$total_con_iva_bd		= $result[$i]['TOTAL_CON_IVA'];
		
		$nro_nota_credito	= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->IdDoc->Folio;
		$arr_rut			= explode("-", $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Receptor->RUTRecep);
		$rut				= number_format($arr_rut[0], 0, '', '.')."-".$arr_rut[1];
		$monto_iva			= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Totales->IVA;
		$total_con_iva		= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Totales->MntTotal;
		
		if($monto_iva == 0)
			$total_neto	= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Totales->MntExe;
		else	
			$total_neto	= $xml_resolucion->SetDTE->DTE->Documento->Encabezado->Totales->MntNeto;
		
		if($nro_nota_credito_bd <> $nro_nota_credito){
			$ln++;
			$worksheet->write($ln, 0, "NC", $text);
			$worksheet->write($ln, 1, $cod_nota_credito, $text);
			$worksheet->write($ln, 2, "N�mero Nota Cr�dito", $text);
			$worksheet->write($ln, 3, $nro_nota_credito_bd, $text);
			$worksheet->write($ln, 4, $nro_nota_credito, $text);
		}
		if($rut_bd <> $rut){
			$ln++;
			$worksheet->write($ln, 0, "NC", $text);
			$worksheet->write($ln, 1, $cod_nota_credito, $text);
			$worksheet->write($ln, 2, "Rut", $text);
			$worksheet->write($ln, 3, $rut_bd, $text);
			$worksheet->write($ln, 4, $rut, $text);
		}	
		if($total_neto_bd <> $total_neto){
			$ln++;
			$worksheet->write($ln, 0, "NC", $text);
			$worksheet->write($ln, 1, $cod_nota_credito, $text);
			$worksheet->write($ln, 2, "Total Neto", $text);
			$worksheet->write($ln, 3, $total_neto_bd, $text);
			$worksheet->write($ln, 4, $total_neto, $text);
		}	
		if($monto_iva_bd <> $monto_iva){
			$ln++;
			$worksheet->write($ln, 0, "NC", $text);
			$worksheet->write($ln, 1, $cod_nota_credito, $text);
			$worksheet->write($ln, 2, "Monto Iva", $text);
			$worksheet->write($ln, 3, $monto_iva_bd, $text);
			$worksheet->write($ln, 4, $monto_iva, $text);
		}	
		if($total_con_iva_bd <> $total_con_iva){
			$ln++;
			$worksheet->write($ln, 0, "NC", $text);
			$worksheet->write($ln, 1, $cod_nota_credito, $text);
			$worksheet->write($ln, 2, "Total con IVA", $text);
			$worksheet->write($ln, 3, $total_con_iva_bd, $text);
			$worksheet->write($ln, 4, $total_con_iva, $text);
		}			
	}
}

$workbook->close();
header("Content-Type: application/x-msexcel; name=\"valores_doc_dte.xls\"");
header("Content-Disposition: inline; filename=\"valores_doc_dte.xls\"");
$fh=fopen($fname, "rb");
fpassthru($fh);
?>