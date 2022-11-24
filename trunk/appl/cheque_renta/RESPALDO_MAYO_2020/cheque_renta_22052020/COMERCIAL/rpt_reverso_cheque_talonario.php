<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
								
class  rpt_reverso_cheque_talonario extends reporte {
	function  rpt_reverso_cheque_talonario($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);		
	}
	function modifica_pdf(&$pdf) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		
		// obtiene datos rut proveedor
		$rut_completo = $result[0]['RUT_COMPLETO'];
		
		// obtiene lista de docs		
		$cadena_faprov = '';
		$arrDocs = explode(",", $result[0]['DOCUMENTOS']);
		for($i=0; $i < count($arrDocs); $i++){
			$nro_faprov = $arrDocs[$i];
			$cadena_faprov = $cadena_faprov.$nro_faprov.'-';
		}
		if ($cadena_faprov != '')
			$cadena_faprov = substr($cadena_faprov, 0, strlen($cadena_faprov) - 1);
		// imprime todo
		//$pdf->Rotate(-90, 0, 0);
		$pdf->SetAutoPageBreak(false);
		
		$pdf->SetFont('Arial','',8);
		$pdf->Text(245, 50, "RUT Proveedor: $rut_completo");
		$pdf->Text(245, 60,'Documentosx:');

		$pdf->SetXY(243, 63);
		$pdf->MultiCell(160, 12, $cadena_faprov, '', '','L');
	}
}
?>