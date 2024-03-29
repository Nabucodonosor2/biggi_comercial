<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
								
class rpt_pago_proveedor extends reporte {
	const K_TIPO_PAGO_FAPROV_CHEQUE			= 1;
	const K_TIPO_PAGO_FAPROV_TRANSFERENCIA	= 2;

	function rpt_pago_proveedor($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);		
	}

	function print_CMR(&$pdf, $plataforma) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		
		/*
		 * Aqui se declaran las variables para ser usadas tanto para agregar o quitar margen
		 * cuando la plataforma asignada para el usuario sea para MAC
		 */
		if($plataforma == 'MAC'){
			$variable_ejemplo = 0;
		}
		
		//margenes de cheque para su posicionamiento.
		$y_ini = 400;
		$x_ini = 160;
		
		//datos del documento.
		$pagese_a = $result[0]['PAGUESE_A'];
		$monto_documento = $result[0]['MONTO_DOCUMENTO'];
		$total_en_palabras =  Numbers_Words::toWords($monto_documento,"es");
		//$total_en_palabras = strtoupper($total_en_palabras.'.  pesos');
		$total_en_palabras = strtoupper(strtr($total_en_palabras.'.  pesos', "�����", "AEIOU"));

		//tipos de cheques
		$tipo_cruzado = $result[0]['TIPO_CRUZADO'];
		$tipo_nominativo = $result[0]['TIPO_NOMINATIVO'];
		$ambos_tipos = $result[0]['AMBOS_TIPOS'];
 
		$linea_nominativa = '********';
		$linea_total = '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - ';
		$linea_total = $linea_total.$linea_total;
		
		//formato y cantidad de caracteres para conversion de numeros a texto
		$total_palabras_linea = $total_en_palabras.'  '.$linea_total;
		
		//truncamos el tama�o de caracteres de totales en palabras. 
		if (strlen($total_palabras_linea) > 140){
			$total_palabras_linea = substr ($total_palabras_linea, 0, 170); 
		}
					
		//fecha del cheque
		$dia_pago_documento = $result[0]['DIA_PAGO_DOCUMENTO'];
		$mes_pago_documento = $result[0]['MES_PAGO_DOCUMENTO'];
		$a�o_pago_documento = $result[0]['A�O_PAGO_DOCUMENTO'];
		$fecha_cheque = $dia_pago_documento.'  de  '.strtoupper($mes_pago_documento).'  de  '.$a�o_pago_documento;

		//formatos de numeros para montos
		$monto_documento = number_format($monto_documento, 0, ',', '.');

		//formato del texto para el cheque
		$pdf->SetFont('Arial','',10);

		//llenado del cheque
		$pdf->Text($x_ini+328, $y_ini-2, $monto_documento.' .-');
		$pdf->Text($x_ini+205, $y_ini+21, 'Santiago,  '.$fecha_cheque);
		$pdf->SetFont('Arial','',11);
		$pdf->Text($x_ini+80, $y_ini+58, $pagese_a);
		$pdf->SetXY($x_ini+72, $y_ini+78);
		$pdf->MultiCell(300, 12,"$total_palabras_linea");

		//formatos de cheques 
		// cruzado
		if($tipo_cruzado == 'N-S'){
			$pdf->SetFont('Arial','',18);
			$pdf->Text($x_ini+15, $y_ini+68, $linea_nominativa);
			//linea vertical
			$pdf->SetLineWidth(1);
			$pdf->SetDrawColor(0,0,0);
			$pdf->Line($x_ini+40, $y_ini-15, $x_ini+40, $y_ini+165-30);
			$pdf->Line($x_ini+60, $y_ini-15, $x_ini+60, $y_ini+165-30);
		}//nominativo
		else if($tipo_nominativo == 'S-N'){
			$pdf->SetFont('Arial','',18);
			$pdf->Text($x_ini+15, $y_ini+69, $linea_nominativa);
			$pdf->Text($x_ini+385, $y_ini+80, $linea_nominativa);
		}//ambos
		else if($ambos_tipos == 'S-S'){
			$pdf->SetFont('Arial','',18);
			$pdf->Text($x_ini+15, $y_ini+68, $linea_nominativa);
			$pdf->Text($x_ini+385, $y_ini+79, $linea_nominativa);
			//linea vertical
			$pdf->SetLineWidth(1);
			$pdf->SetDrawColor(0,0,0);
			$pdf->Line($x_ini+40, $y_ini-15, $x_ini+40, $y_ini+165-30);
			$pdf->Line($x_ini+60, $y_ini-15, $x_ini+60, $y_ini+165-30);
		}
	}

	function print_normnal(&$pdf, $plataforma) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		
		/*
		 * Aqui se declaran las variables para ser usadas tanto para agregar o quitar margen
		 * cuando la plataforma asignada para el usuario sea para MAC
		 */
		
		$sql = "select VALOR from PARAMETRO  where COD_PARAMETRO=68";
		
		$result1 = $db->build_results($sql);
		$formato_cheque = $result1[0]['VALOR'];
				
		switch ($formato_cheque) {
	    case "N":
	        
				if($plataforma == 'MAC'){
					$variable_ejemplo = 0;
				}
				
				//margenes de cheque para su posicionamiento.
				$y_ini = 400;
				$x_ini = 160;
				
				//datos del documento.
				$pagese_a = $result[0]['PAGUESE_A'];
				$monto_documento = $result[0]['MONTO_DOCUMENTO'];
				$total_en_palabras =  Numbers_Words::toWords($monto_documento,"es");
				//$total_en_palabras = strtoupper($total_en_palabras.'.  pesos');
				$total_en_palabras = strtoupper(strtr($total_en_palabras.'.  pesos', "�����", "AEIOU"));
		
				//tipos de cheques
				$tipo_cruzado = $result[0]['TIPO_CRUZADO'];
				$tipo_nominativo = $result[0]['TIPO_NOMINATIVO'];
				$ambos_tipos = $result[0]['AMBOS_TIPOS'];
		 
				$linea_nominativa = '********';
				$linea_total = '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - ';
				$linea_total = $linea_total.$linea_total;
				
				//formato y cantidad de caracteres para conversion de numeros a texto
				$total_palabras_linea = $total_en_palabras.'  '.$linea_total;
				
				//truncamos el tama�o de caracteres de totales en palabras. 
				if (strlen($total_palabras_linea) > 140){
					$total_palabras_linea = substr ($total_palabras_linea, 0, 170); 
				}
							
				//fecha del cheque
				$dia_pago_documento = $result[0]['DIA_PAGO_DOCUMENTO'];
				$mes_pago_documento = $result[0]['MES_PAGO_DOCUMENTO'];
				$a�o_pago_documento = $result[0]['A�O_PAGO_DOCUMENTO'];
				$fecha_cheque = $dia_pago_documento.'  de  '.strtoupper($mes_pago_documento).'  de  '.$a�o_pago_documento;
		
				//formatos de numeros para montos
				$monto_documento = number_format($monto_documento, 0, ',', '.');
		
				//formato del texto para el cheque
				$pdf->SetFont('Arial','',10);
		
				//llenado del cheque
				$pdf->Text($x_ini+290, $y_ini-5, ' ****'.$monto_documento.' .-');
				$pdf->Text($x_ini+230, $y_ini+20, 'Santiago,  '.$fecha_cheque);
				$pdf->SetFont('Arial','',11);
				$pdf->Text($x_ini+80, $y_ini+64, $pagese_a);
				$pdf->SetXY($x_ini+75, $y_ini+83);
				$pdf->MultiCell(300, 12,"$total_palabras_linea");
		
				//formatos de cheques 
				// cruzado
				if($tipo_cruzado == 'N-S' ){
					$pdf->SetFont('Arial','',18);
					$pdf->Text($x_ini+15, $y_ini+70, $linea_nominativa);
					//linea vertical
					$pdf->SetLineWidth(1);
					$pdf->SetDrawColor(0,0,0);
					$pdf->Line($x_ini+40, $y_ini-15, $x_ini+40, $y_ini+165-30);
					$pdf->Line($x_ini+60, $y_ini-15, $x_ini+60, $y_ini+165-30);
				}//nominativo
				else if($tipo_nominativo == 'S-N'){
					$pdf->SetFont('Arial','',18);
					$pdf->Text($x_ini+15, $y_ini+70, $linea_nominativa);
					$pdf->Text($x_ini+380, $y_ini+75+12, $linea_nominativa);
				}//ambos
				else if($ambos_tipos == 'S-S'){
					$pdf->SetFont('Arial','',18);
					$pdf->Text($x_ini+15, $y_ini+70, $linea_nominativa);
					$pdf->Text($x_ini+380, $y_ini+75+12, $linea_nominativa);
					//linea vertical
					$pdf->SetLineWidth(1);
					$pdf->SetDrawColor(0,0,0);
					$pdf->Line($x_ini+40, $y_ini-15, $x_ini+40, $y_ini+165-30);
					$pdf->Line($x_ini+60, $y_ini-15, $x_ini+60, $y_ini+165-30);
				}
	    
	        break;
	    case "S":
	        
				if($plataforma == 'MAC'){
					$variable_ejemplo = 0;
				}
				
				//margenes de cheque para su posicionamiento.
				$y_ini = 400;
				$x_ini = 160;
				
				//datos del documento.
				$pagese_a = $result[0]['PAGUESE_A'];
				$monto_documento = $result[0]['MONTO_DOCUMENTO'];
				$total_en_palabras =  Numbers_Words::toWords($monto_documento,"es");
				//$total_en_palabras = strtoupper($total_en_palabras.'.  pesos');
				$total_en_palabras = strtoupper(strtr($total_en_palabras.'.  pesos', "�����", "AEIOU"));
		
				//tipos de cheques
				$tipo_cruzado = $result[0]['TIPO_CRUZADO'];
				$tipo_nominativo = $result[0]['TIPO_NOMINATIVO'];
				$ambos_tipos = $result[0]['AMBOS_TIPOS'];
		 
				$linea_nominativa = '******';
				$linea_total = '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - ';
				$linea_total = $linea_total.$linea_total;
				
				//formato y cantidad de caracteres para conversion de numeros a texto
				$total_palabras_linea = $total_en_palabras.'  '.$linea_total;
				
				//truncamos el tama�o de caracteres de totales en palabras. 
				if (strlen($total_palabras_linea) > 140){
					$total_palabras_linea = substr ($total_palabras_linea, 0, 170); 
				}
							
				//fecha del cheque
				$dia_pago_documento = $result[0]['DIA_PAGO_DOCUMENTO'];
				$mes_pago_documento = $result[0]['MES_PAGO_DOCUMENTO'];
				$a�o_pago_documento = $result[0]['A�O_PAGO_DOCUMENTO'];
				$fecha_cheque = $dia_pago_documento.'  de  '.strtoupper($mes_pago_documento).'  de  '.$a�o_pago_documento;
		
				//formatos de numeros para montos
				$monto_documento = number_format($monto_documento, 0, ',', '.');
		
				//formato del texto para el cheque
				$pdf->SetFont('Arial','',10);
		
				//llenado del cheque
				$pdf->Text($x_ini+280, $y_ini+5, ' ****'.$monto_documento.' .-');
				$pdf->Text($x_ini+251, $y_ini+30, 'Santiago,  '.$fecha_cheque);
				$pdf->SetFont('Arial','',11);
				$pdf->Text($x_ini+70, $y_ini+64, $pagese_a);
				$pdf->SetXY($x_ini+75, $y_ini+73);
				$pdf->MultiCell(300, 12,"$total_palabras_linea");
		
				//formatos de cheques 
				// cruzado
				if($tipo_cruzado == 'N-S' ){
					$pdf->SetFont('Arial','',18);
					$pdf->Text($x_ini+15, $y_ini+70, $linea_nominativa);
					//linea vertical
					$pdf->SetLineWidth(1);
					$pdf->SetDrawColor(0,0,0);
					$pdf->Line($x_ini+40, $y_ini-15, $x_ini+40, $y_ini+165-30);
					$pdf->Line($x_ini+60, $y_ini-15, $x_ini+60, $y_ini+165-30);
				}//nominativo
				else if($tipo_nominativo == 'S-N'){
					$pdf->SetFont('Arial','',18);
					$pdf->Text($x_ini+15, $y_ini+70, $linea_nominativa);
					$pdf->Text($x_ini+380, $y_ini+75+12, $linea_nominativa);
				}//ambos
				else if($ambos_tipos == 'S-S'){
					$pdf->SetFont('Arial','',18);
					$pdf->Text($x_ini+15, $y_ini+70, $linea_nominativa);
					$pdf->Text($x_ini+380, $y_ini+75+12, $linea_nominativa);
					//linea vertical
					$pdf->SetLineWidth(1);
					$pdf->SetDrawColor(0,0,0);
					$pdf->Line($x_ini+40, $y_ini-15, $x_ini+40, $y_ini+165-30);
					$pdf->Line($x_ini+60, $y_ini-15, $x_ini+60, $y_ini+165-30);
				}
			    
			        break;

		}		
	}

	function modifica_pdf(&$pdf) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		
		//tipo de pago cheque:'1' o transferencia:'2'
		$COD_TIPO_PAGO_FAPROV = $result[0]['COD_TIPO_PAGO_FAPROV'];
		
		/**************************************************/
		//Se obtiene usuario actual
		$cod_usuario = session::get('COD_USUARIO');
		
		//Se obtiene la plataforma que tiene asignado al usuario actual
		$sql_plat = "SELECT PLATAFORMA_PRINT_CHEQUE
					 FROM USUARIO
					 WHERE COD_USUARIO = $cod_usuario";
		$result_plat = $db->build_results($sql_plat);
		$plataforma = $result_plat[0]['PLATAFORMA_PRINT_CHEQUE'];
		/*
		 * Aqui se declaran las variables para ser usadas tanto para agregar o quitar margen
		 * cuando la plataforma asignada para el usuario sea para MAC
		 */
		if($plataforma == 'MAC'){
			$variable_ejemplo = 0;
		}
		/**************************************************/
		
		if ($COD_TIPO_PAGO_FAPROV == self::K_TIPO_PAGO_FAPROV_CHEQUE){	

			$y_Pago_prov = $pdf->GetY();
			
			$count = count($result);
			$pago_directorio = $result[0]['PAGO_DIRECTORIO'];
			
			if ($count == 1){
				$nro_faprov = $result[0]['NRO_FAPROV'];
				$fecha_faprov = $result[0]['FECHA_FAPROV'];
				$monto_asignado = $result[0]['MONTO_ASIGNADO'];
				$ordenes_compra = $result[0]['ORDENES_COMPRA'];
				$notas_venta = $result[0]['NOTAS_DE_VENTA'];
	
				//formatos de totales
				$monto_asignado = number_format($monto_asignado, 0, ',', '.');
				
				$pdf->SetFont('Arial','B',11);
				$pdf->SetTextColor(4, 22, 114);
				$pdf->SetXY(30,$y_Pago_prov + 5);
				if	($pago_directorio == 'S')
					$pdf->Cell(550,17,'Pago Participaci�n', '', '','L');
				else
					$pdf->Cell(550,17,'Facturas', '', '','L');
					
				//titulos de facturas de proveedores
				$pdf->SetFont('Arial','B',10);
				$pdf->SetXY(30,$y_Pago_prov + 20);
			
				if	($pago_directorio == 'S')
					$pdf->Cell(55,15,'C�digo', 'LTB', '','C');
				else
					$pdf->Cell(55,15,'Nro. FA', 'LTB', '','C');
					
				$pdf->SetXY(85,$y_Pago_prov + 20);
				$pdf->Cell(65,15,'Fecha', 'LTB', '','C');
				$pdf->SetXY(150,$y_Pago_prov + 20);
				$pdf->Cell(100,15,'Monto Pagado', 'LTRB', '','C');
				
				if	($pago_directorio == 'N'){
					$pdf->SetXY(250,$y_Pago_prov + 20);
					$pdf->Cell(170,15,'Ordenes de Compra', 'TRB', '','C');
					$pdf->SetXY(420,$y_Pago_prov + 20);
					$pdf->Cell(163, 15,'Notas de Venta', 'TRB', '','C');
				}
					
				//campos de factura proveedores
				$pdf->SetTextColor(1,1,1);
				$pdf->SetFont('Arial','',9);
				$pdf->SetXY(30,$y_Pago_prov + 35);
				$pdf->Cell(55,15, $nro_faprov, 'LTB', '','C');
				$pdf->SetXY(85,$y_Pago_prov + 35);
				$pdf->Cell(65,15, $fecha_faprov, 'LTB', '','C');
				$pdf->SetXY(150,$y_Pago_prov + 35);
				$pdf->Cell(100,15,$monto_asignado, 'LTRB', '','R');
				
				if	($pago_directorio == 'N'){
					$pdf->SetXY(250,$y_Pago_prov + 35);
					$pdf->Cell(170,15, $ordenes_compra, 'TRB', '','R');
					$pdf->SetXY(420,$y_Pago_prov + 35);
					$pdf->Cell(163, 15, $notas_venta, 'TRB', '','R');
				}
				
			}else{
				$nro_faprov = $result[0]['NRO_FAPROV'];
				
				$cadena_faprov = '';
				for($i=0; $i<$count; $i++){
					$nro_faprov = $result[$i]['NRO_FAPROV'];
					$cadena_faprov = $cadena_faprov.$nro_faprov.'-';
	
				}
				if ($cadena_faprov != '')
					$cadena_faprov = substr($cadena_faprov, 0, strlen($cadena_faprov) - 1);

					//titulo
					$pdf->SetFont('Arial','B',11);
					$pdf->SetTextColor(4, 22, 114);
					$pdf->SetXY(30,$y_Pago_prov + 5);
					if	($pago_directorio == 'S')
						$pdf->Cell(550,17,'Pago Participaci�n', '', '','L');
					else
						$pdf->Cell(550,17,'Facturassss', '', '','L');

					//campos de factura proveedores
					$pdf->SetTextColor(1, 1, 1);
					$pdf->SetFont('Arial','',9);				
					$pdf->SetXY(30, $y_Pago_prov + 22);
					$pdf->MultiCell(550,12, $cadena_faprov, '', '','L');
					
					//Recuadro 
					$pdf->SetFont('Arial','B',10);
					$pdf->SetXY(30,$y_Pago_prov + 20);
					$pdf->MultiCell(554, 40,'', 'LTBR', 'L','C');
			}
		}	
		
		//tipo de pago cheque:'1' o transferencia:'2'
		$COD_TIPO_PAGO_FAPROV = $result[0]['COD_TIPO_PAGO_FAPROV'];
		$usuario_cambio = $result[0]['USUARIO_CAMBIO'];

		if ($COD_TIPO_PAGO_FAPROV == self::K_TIPO_PAGO_FAPROV_CHEQUE){
			if ($usuario_cambio == 'CLAUDIA MORALES')
				$this->print_CMR($pdf, $plataforma);
			else
				$this->print_normnal($pdf, $plataforma);
		}
	}
}
?>