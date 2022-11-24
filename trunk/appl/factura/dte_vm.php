<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
include(dirname(__FILE__)."/../../appl.ini");

class dte_vm extends base {
	var $dy = 50;
	var $tipo_doc;
	
	var $pdf;
	
	function pdf_libre_dte($tipo_doc, $cod_doc) {
		$this->tipo_doc = $tipo_doc;
		
		if ($this->tipo_doc == 33) {
			$sql = "SELECT  NRO_FACTURA NRO_DOC
							,REPLACE(REPLACE(dbo.f_get_parametro(20),'.',''),'-8','') as RUTEMISOR
					FROM FACTURA
					WHERE COD_FACTURA  = $cod_doc";
		}
					
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$Result_pdf = $db->build_results($sql);
		$folio	= $Result_pdf[0]['NRO_DOC'];
		$emisor	= $Result_pdf[0]['RUTEMISOR'];

		//LLamo a la nueva clase dte.
		$dte = new dte();
		$SqlHash = "SELECT dbo.f_get_parametro(200) K_HASH";  //PARAM_HASH=200
		$Datos_Hash = $db->build_results($SqlHash);
		$dte->hash = $Datos_Hash[0]['K_HASH'];

		$response_pdf = $dte->post_genera_pdf($this->tipo_doc,$folio,$emisor);	
		//revisamos esxiste un error
		$ERROR = explode('ERROR' ,$response_pdf);
		if($ERROR[1] != '')
			return $response_pdf;	//error
		else {
			//imprimimos el pdf
			$body = strstr($response_pdf, '%');
			$header_body = strstr($response_pdf, '%',true);
		
			$header_Type = explode('Content-Type:' ,$header_body);
			$header_Disposition = explode('Content-Disposition:',$header_Type[0]);
			$header_length = explode ('Content-Length:',$header_Disposition[0]);
			$name_pdf = explode('filename=',$header_Disposition[1]); 

			//crear archivo temposral
			$dir_tmp = dirname(__FILE__);		//deberia ser un directorio de temporales
			$this->clean_files($dir_tmp, "pdf");
			$fname = basename(tempnam($dir_tmp, 'tmp'));
			rename("$dir_tmp/".$fname, "$dir_tmp/".$fname.'.pdf');
			$fname .= '.pdf';
			
			//escribe el archivo temporal con el pdf
			$handle = fopen($fname,"w");
			fwrite($handle, $body);
			fclose($handle);
		
			return $fname;
		}
	}
	function barra_2D(&$pdf) {
		/*********RECTANGULOS PARA TAPAR******/
		/*Cabecera fija*/
		$pdf->SetFillColor(255,255,255);
		//$pdf->Rect(10, $this->dy, 195, 50, "DF");
		
		/*Cabecera datos*/
		$pdf->SetFillColor(255,255,255);
		$pdf->Rect(10, 50+$this->dy, 190, 25, "F");
		
		/*item*/
		$pdf->SetFillColor(255,255,255);
		$pdf->Rect(9, 75+$this->dy, 193, 111, "F");
		
		/*totales*/
		$pdf->SetFillColor(255,255,255);
		$pdf->Rect(150, 190+$this->dy, 50, 25, "F");
		/*****FIN RECTANGULOS PARA TAPAR***************/
	}
	function cabecerea(&$pdf) {
		//recuadro rojo de arriba derecha
		$pdf->SetDrawColor(255,0,0);	//rojo
		$pdf->SetLineWidth(0.7);
		$x = 130;
		$y = 10;
		$w = 71;
		$h = 32;
		$pdf->Rect($x, $y, $w, $h, "D");
		
		$pdf->SetFont('Arial','B',12);
		$pdf->SetTextColor(255,0,0);	//rojo
		$pdf->SetXY($x, $y+4);
		$pdf->MultiCell($w, 8, "R.U.T.: 91.462.001-5\nNOTA DE CRÉDITO DE EXPORTACIÓN ELECTRÓNICA\nN°: 166353", "", "C");	// [, mixed border [, string align [, boolean fill]]])
		
		/*
FACTURA ELECTRÓNICA
FACTURA NO AFECTA O EXENTA ELECTRÓNICA
GUÍA DE DESPACHO ELECTRÓNICA
NOTA DE DÉBITO ELECTRÓNICA
NOTA DE CRÉDITO ELECTRÓNICA
FACTURA DE COMPRA ELECTRÓNICA
LIQUIDACIÓN FACTURA ELECTRÓNICA
FACTURA DE EXPORTACIÓN ELECTRÓNICA
NOTA DE DÉBITO DE EXPORTACIÓN ELECTRÓNICA
NOTA DE CRÉDITO DE EXPORTACIÓN ELECTRÓNICA
		 */
		
		
	}
	function dte_vm() {
		session::set('K_ROOT_DIR', "/var/www/html/desarrolladores/vmelo/biggi_comercial/trunk/");
		$fname = $this->pdf_libre_dte(33, 35723);
		//revisamos esxiste un error
		$ERROR = explode('ERROR' ,$fname);
		if($ERROR[1] != '')
			return $fname;	//error
		else {
			require_once(dirname(__FILE__)."/../common_appl/FPDI-1.6.1/fpdi.php");
			
			$pdf = new FPDI();
			$pageCount = $pdf->setSourceFile($fname);
			$tplIdx = $pdf->importPage(1, '/MediaBox');
			
			$pdf->addPage();
			$pdf->useTemplate($tplIdx, 0, $this->dy);
			
			///*********los dte tienen mas de una hoja, se deberia hacer lo mismo siempre

			// comienza el dibujo
			$this->barra_2D($pdf);
			$this->cabecerea($pdf);
			
			$pdf->Output();
		}
	}
	
}

//192.168.2.141/desarrolladores/vmelo/biggi_comercial/trunk/appl/factura/dte_vm.php
new dte_vm();
?>