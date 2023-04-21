<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

class rpt_cheque_talonario extends reporte {
	const K_TIPO_PAGO_FAPROV_CHEQUE			= 1;
	const K_TIPO_PAGO_FAPROV_TRANSFERENCIA	= 2;

	function rpt_cheque_talonario($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);		
	}

    function print_normnal(&$pdf, $plataforma) {
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		
		//margenes de cheque para su posicionamiento.
		//MH $y_ini = -375;
		//MH $x_ini = 0;
		$y_ini = 400;
		$x_ini = 160;

				
		//DATOS DEL CHEQUE
		$pagese_a = $result[0]['PAGUESE_A'];
		$monto_documento = $result[0]['MONTO_DOCUMENTO'];
		$total_en_palabras =  Numbers_Words::toWords($monto_documento,"es");
		$total_en_palabras = strtoupper(strtr($total_en_palabras.'.  pesos', "αινσϊ", "AEIOU"));
		
		//tipos de cheques
		$tipo_cruzado = $result[0]['TIPO_CRUZADO'];
		$tipo_nominativo = $result[0]['TIPO_NOMINATIVO'];
		$ambos_tipos = $result[0]['AMBOS_TIPOS'];
 
		$linea_nominativa = '******';
		$linea_total = '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - ';
		$linea_total = $linea_total.$linea_total;
		
		//formato y cantidad de caracteres para conversion de numeros a texto
		$total_palabras_linea = $total_en_palabras.'  '.$linea_total;
				
		//truncamos el tamaρo de caracteres de totales en palabras. 
		if (strlen($total_palabras_linea) > 140){
			$total_palabras_linea = substr ($total_palabras_linea, 0, 170); 
		}
							
		//fecha del cheque
		$dia_pago_documento = $result[0]['DIA_PAGO_DOCUMENTO'];
		$mes_pago_documento = $result[0]['MES_PAGO_DOCUMENTO'];
		$ANO_PAGO_DOCUMENTO = $result[0]['ANO_PAGO_DOCUMENTO'];
		$fecha_cheque = $dia_pago_documento.'  de  '.strtoupper($mes_pago_documento).'  de  '.$ANO_PAGO_DOCUMENTO;

		//formatos de numeros para montos
		$monto_documento = number_format($monto_documento, 0, ',', '.');
        //$pdf->Rotate(-90, 0, 0);
		//formato del texto para el cheque
		$pdf->SetFont('Arial','',10);


		//IMPRESION DEL CHEQUE
		$pdf->Text($x_ini+290, $y_ini+10, ' ****'.$monto_documento.' .-');
		$pdf->Text($x_ini+245, $y_ini+35, 'Santiago,  '.$fecha_cheque);
		$pdf->SetFont('Arial','',11);
		$pdf->Text($x_ini+70, $y_ini+60, $pagese_a);
		$pdf->SetXY($x_ini+75, $y_ini+66);
		$pdf->MultiCell(300, 12,"$total_palabras_linea");
				
		//formatos de cheques 
		// cruzado
		if($tipo_cruzado == 'N-S' ){
			$pdf->SetFont('Arial','',18);
			$pdf->Text($x_ini+18, $y_ini+70, $linea_nominativa);
			//linea vertical
			$pdf->SetLineWidth(1);
			$pdf->SetDrawColor(0,0,0);
			$pdf->Line($x_ini+40, $y_ini-15, $x_ini+40, $y_ini+165-30);
			$pdf->Line($x_ini+60, $y_ini-15, $x_ini+60, $y_ini+165-30);

		}//nominativo
		else if($tipo_nominativo == 'S-N'){
			$pdf->SetFont('Arial','',18);
			$pdf->Text($x_ini+18, $y_ini+70, $linea_nominativa);
			// Se necesita linea mas larga para cubrir bien la frase al portador 16/06/2018
			$linea_nominativa = '********';
			// Se necesita fuente mas grande para cubrir bien la frase al portador 16/06/2018
			$pdf->SetFont('Arial','',25);
			$pdf->Text($x_ini+390, $y_ini+87, $linea_nominativa);
			// Se setea fuente a como venia antes de la frase al portador 16/06/2018					
			$pdf->SetFont('Arial','',18);
		}//ambos
		else if($ambos_tipos == 'S-S'){
			$pdf->SetFont('Arial','',18);
			$pdf->Text($x_ini+18, $y_ini+70, $linea_nominativa);
			// Se necesita linea mas larga para cubrir bien la frase al portador 16/06/2018
			$linea_nominativa = '********';
			// Se necesita fuente mas grande para cubrir bien la frase al portador 16/06/2018
			$pdf->SetFont('Arial','',25);
			$pdf->Text($x_ini+390, $y_ini+87, $linea_nominativa);
			// Se setea fuente a como venia antes de la frase al portador 16/06/2018					
			$pdf->SetFont('Arial','',18);
			//linea vertical
			$pdf->SetLineWidth(1);
			$pdf->SetDrawColor(0,0,0);
			$pdf->Line($x_ini+40, $y_ini-15, $x_ini+40, $y_ini+165-30);
			$pdf->Line($x_ini+60, $y_ini-15, $x_ini+60, $y_ini+165-30);
		}	
	}

	function modifica_pdf(&$pdf) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

		//Se obtiene usuario actual
		$cod_usuario = session::get('COD_USUARIO');
		//Se obtiene la plataforma que tiene asignado al usuario actual
		$sql_plat = "SELECT PLATAFORMA_PRINT_CHEQUE FROM USUARIO WHERE COD_USUARIO = $cod_usuario";
		$result_plat = $db->build_results($sql_plat);
		$plataforma = $result_plat[0]['PLATAFORMA_PRINT_CHEQUE'];
		//tipo de pago cheque:'1' o transferencia:'2'
		$this->print_normnal($pdf, $plataforma);
		
	}
}
?>