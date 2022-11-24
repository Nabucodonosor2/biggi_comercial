<?php
////////////////////////////////////////
/////////// COMERCIA_BIGGI ///////////////
////////////////////////////////////////
class wi_factura extends wi_factura_base {
	const K_BODEGA_TERMINADO = 2;
	const K_TIPO_FA_OC_COMERCIAL = 3;
	
	function wi_factura($cod_item_menu) {
		parent::wi_factura_base($cod_item_menu);
	}
	function envia_FA_electronica(){
			if (!$this->lock_record())
				return false;
			
			$cod_factura = $this->get_key();
			$COD_ESTADO_DOC_SII = $this->dws['dw_factura']->get_item(0, 'COD_ESTADO_DOC_SII');

			if($COD_ESTADO_DOC_SII == 1){//Emitida
				/////////// reclacula la FA porsiaca
				$parametros_sp = "'RECALCULA',$cod_factura";   
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				$db->EXECUTE_SP('spu_factura', $parametros_sp);
	            /////////
			}	
	
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$count1= 0;
			
			$sql_valida="SELECT CANTIDAD 
				  		 FROM ITEM_FACTURA
				  		 WHERE COD_FACTURA = $cod_factura";
				  
			$result_valida = $db->build_results($sql_valida);

			for($i = 0 ; $i < count($result_valida) ; $i++){
				if($result_valida[$i] <> 0)
					$count1 = $count1 + 1;
			}
			if($count1 > 18){
				$this->_load_record();
				$this->alert('Se está ingresando más item que la cantidad permitida, favor contacte a IntegraSystem.');
				return false;
			}

			$this->sepa_decimales	= ',';	//Usar , como separador de decimales
			$this->vacio 			= ' ';	//Usar rellenos de blanco, CAMPO ALFANUMERICO
			$this->llena_cero		= 0;	//Usar rellenos con '0', CAMPO NUMERICO
			$this->separador		= ';';	//Usar ; como separador de campos
			$cod_usuario_impresion = $this->cod_usuario;
			$CMR = 9;
			$cod_impresora_dte = $_POST['wi_impresora_dte'];
			if($cod_impresora_dte == 100){
				$emisor_factura = 'SALA VENTA';
			}else{
			
			if ($cod_impresora_dte == '')
				$sql = "SELECT U.NOM_USUARIO EMISOR_FACTURA
						FROM USUARIO U, FACTURA F
						WHERE F.COD_FACTURA = $cod_factura
						  and U.COD_USUARIO = $cod_usuario_impresion";
			else
				$sql = "SELECT NOM_REGLA EMISOR_FACTURA
						FROM IMPRESORA_DTE
						WHERE COD_IMPRESORA_DTE = $cod_impresora_dte";
						
			$result = $db->build_results($sql);
			$emisor_factura = $result[0]['EMISOR_FACTURA'] ;
			}
			
			$db->BEGIN_TRANSACTION();
			$sp = 'spu_factura';
			$param = "'ENVIA_DTE', $cod_factura, $cod_usuario_impresion";

			if ($db->EXECUTE_SP($sp, $param)){
				//////nuevo////////////////////////////////////
				/*
				 En realidad no es un error de sistema. El sistema busca la ultima factura
				 exenta por numero mas alto y le suma 1 al correlativo.
        		 Siempre va a encontrar esa factura de compra de PITAGORA (8910), ingresada como factura de venta
				 , que es la mas alta
				*/
				$sql_exe = "SELECT PORC_IVA
								  ,NRO_FACTURA
							FROM FACTURA
							WHERE COD_FACTURA = $cod_factura";
							
				$result_exe = $db->build_results($sql_exe);
				if($result_exe[0]['PORC_IVA'] == 0){
					if($result_exe[0]['NRO_FACTURA'] > 1000){
						$db->ROLLBACK_TRANSACTION();
						$this->_load_record();
						$this->alert('No se pudo enviar Fatura Electronica Nº '.$result_exe[0]['NRO_FACTURA'].', Por favor contacte a IntegraSystem.');
						return false;
					}					
				}
				///////////////////////////////////////////////
				
				$db->COMMIT_TRANSACTION();
				
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				//declrar constante para que el monto con iva del reporte lo transpforme a palabras
				$sql_total = "select TOTAL_CON_IVA from FACTURA where COD_FACTURA = $cod_factura";
				$resul_total = $db->build_results($sql_total);
				$total_con_iva = $resul_total[0]['TOTAL_CON_IVA'] ;
				$total_en_palabras =  Numbers_Words::toWords($total_con_iva,"es"); 
				$total_en_palabras = strtr($total_en_palabras, "áéíóú", "aeiou");
				$total_en_palabras = strtoupper($total_en_palabras);
				
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				$sql_dte = "SELECT	F.COD_FACTURA,
									F.NRO_FACTURA,
									F.TIPO_DOC,
									dbo.f_format_date(FECHA_FACTURA,1)FECHA_FACTURA,
									F.COD_USUARIO_IMPRESION,
									'$emisor_factura' EMISOR_FACTURA,
									F.NRO_ORDEN_COMPRA,
									dbo.f_fa_nros_guia_despacho(".$cod_factura.") NRO_GUIAS_DESPACHO,	
									F.REFERENCIA,
									F.NOM_EMPRESA,
									F.GIRO,
									F.RUT,
									F.DIG_VERIF,
									F.DIRECCION,
									dbo.f_emp_get_mail_cargo_persona(F.COD_PERSONA, '[EMAIL]') MAIL_CARGO_PERSONA,
									F.TELEFONO,
									F.FAX,
									F.COD_DOC,
									F.SUBTOTAL,
									F.PORC_DSCTO1,
									F.MONTO_DSCTO1,
									F.PORC_DSCTO2,
									F.MONTO_DSCTO2,
									F.MONTO_DSCTO1 + F.MONTO_DSCTO2 TOTAL_DSCTO,
									F.TOTAL_NETO,
									F.PORC_IVA,
									F.MONTO_IVA,
									F.TOTAL_CON_IVA,
									F.RETIRADO_POR,
									F.RUT_RETIRADO_POR,
									F.DIG_VERIF_RETIRADO_POR,
									COM.NOM_COMUNA,
									CIU.NOM_CIUDAD,
									FP.NOM_FORMA_PAGO,
									FP.COD_PAGO_DTE,
									F.NOM_FORMA_PAGO_OTRO,
									ITF.COD_ITEM_FACTURA,
									ITF.ORDEN,								
									ITF.ITEM,
									ITF.CANTIDAD,
									ITF.COD_PRODUCTO,
									ITF.NOM_PRODUCTO,
									ITF.PRECIO,
									ITF.PRECIO * ITF.CANTIDAD  TOTAL_FA,
									'".$total_en_palabras."' TOTAL_EN_PALABRAS,
									convert(varchar(5), GETDATE(), 8) HORA,
									F.GENERA_SALIDA,
									F.OBS,
									F.CANCELADA,
									F.RETIRADO_POR,
									F.RUT_RETIRADO_POR,
									F.DIG_VERIF_RETIRADO_POR,
									F.GUIA_TRANSPORTE,
									F.PATENTE
							FROM 	FACTURA F left outer join COMUNA COM on F.COD_COMUNA = COM.COD_COMUNA,
									ITEM_FACTURA ITF, CIUDAD CIU, FORMA_PAGO FP 
							WHERE 	F.COD_FACTURA = ".$cod_factura." 
							AND	ITF.COD_FACTURA = F.COD_FACTURA
							AND	CIU.COD_CIUDAD = F.COD_CIUDAD
							AND	FP.COD_FORMA_PAGO = F.COD_FORMA_PAGO";
				$result_dte = $db->build_results($sql_dte);
				//CANTIDAD DE ITEM_FACTURA 
				$count = count($result_dte);
				
				// datos de factura
				$NRO_FACTURA		= $result_dte[0]['NRO_FACTURA'] ;		// 1 Numero Factura
				$FECHA_FACTURA		= $result_dte[0]['FECHA_FACTURA'] ;		// 2 Fecha Factura
				//Email - VE: =>En el caso de las Factura y otros documentos, no aplica por lo que se dejan 0;0 
				$TD					= $this->llena_cero;					// 3 Tipo Despacho
				$TT					= $this->llena_cero;					// 4 Tipo Traslado
				//Email - VE: => 
				$PAGO_DTE			= $result_dte[0]['COD_PAGO_DTE'];		// 5 Forma de Pago
				$FV					= $this->vacio;							// 6 Fecha Vencimiento
				$RUT				= $result_dte[0]['RUT'];				
				$DIG_VERIF			= $result_dte[0]['DIG_VERIF'];
				$RUT_EMPRESA		= $RUT.'-'.$DIG_VERIF;					// 7 Rut Empresa
				$NOM_EMPRESA		= $result_dte[0]['NOM_EMPRESA'] ;		// 8 Razol Social_Nombre Empresa
				$GIRO				= $result_dte[0]['GIRO'];				// 9 Giro Empresa
				$DIRECCION			= $result_dte[0]['DIRECCION'];			//10 Direccion empresa
				$MAIL_CARGO_PERSONA	= $result_dte[0]['MAIL_CARGO_PERSONA'];	//11 E-Mail Contacto
				$TELEFONO			= $result_dte[0]['TELEFONO'];			//12 Telefono Empresa
				$REFERENCIA			= $result_dte[0]['REFERENCIA'];			//12 Referencia de la Factura  //datos olvidado por VE.
				$NRO_GUIA_DESPACHO	= $result_dte[0]['NRO_GUIAS_DESPACHO'];	//Solicitado a VE por SP
				$GENERA_SALIDA		= $result_dte[0]['GENERA_SALIDA'];		//Solicitado a VE por SP "DESPACHADO"
				if ($GENERA_SALIDA == 'S'){
					$GENERA_SALIDA = 'DESPACHADO';
				}else{
					$GENERA_SALIDA = '';
				}
				$CANCELADA			= $result_dte[0]['CANCELADA'];			//Solicitado a VE por SP "CANCELADO"
				if ($CANCELADA == 'S'){
					$CANCELADA = 'CANCELADA';
				}else{
					$CANCELADA = '';
				}
				$SUBTOTAL			= number_format($result_dte[0]['SUBTOTAL'], 1, ',', '');	//Solicitado a VE por SP "SUBTOTAL"
				$PORC_DSCTO1		= number_format($result_dte[0]['PORC_DSCTO1'], 1, ',', '');	//Solicitado a VE por SP "PORC_DSCTO1"
				$PORC_DSCTO2		= number_format($result_dte[0]['PORC_DSCTO2'], 1, ',', '');	//Solicitado a VE por SP "PORC_DSCTO2"
				$EMISOR_FACTURA		= $result_dte[0]['EMISOR_FACTURA'];		//Solicitado a VE por SP "EMISOR_FACTURA"
				$NOM_COMUNA			= $result_dte[0]['NOM_COMUNA'];			//13 Comuna Recepcion
				$NOM_CIUDAD			= $result_dte[0]['NOM_CIUDAD'];			//14 Ciudad Recepcion
				$DP					= $result_dte[0]['DIRECCION'];			//15 Dirección Postal
				$COP				= $result_dte[0]['NOM_COMUNA'];			//16 Comuna Postal
				$CIP				= $result_dte[0]['NOM_CIUDAD'];			//17 Ciudad Postal
				
				
				//OBSERVACION
				$RETIRA_RECINTO		= $result_dte[0]['RETIRADO_POR'];		// Persona que Retira de Recinto
				$RECINTO			= $this->vacio;							// Recinto
				$PATENTE			= $result_dte[0]['PATENTE'];			// Patente de Vehiculo que retira
				$RUT_RETIRADO_POR	= $result_dte[0]['RUT_RETIRADO_POR'];				
				$DIG_VERIF_RETIRADO_POR	= $result_dte[0]['DIG_VERIF_RETIRADO_POR'];
				if($RUT_RETIRADO_POR == ''){
					$RUT_RETIRA = '';
				}else{
					$RUT_RETIRA		= $RUT_RETIRADO_POR.'-'.$DIG_VERIF_RETIRADO_POR; // 27 Rut quien Retira
				}
				
				
				
				//DATOS DE TOTALES number_format($result_dte[$i]['TOTAL_FA'], 0, ',', '.');
				$TOTAL_NETO			= number_format($result_dte[0]['TOTAL_NETO'], 1, ',', '');		//18 Monto Neto
				$PORC_IVA			= number_format($result_dte[0]['PORC_IVA'], 1, ',', '');		//19 Tasa IVA
				$MONTO_IVA			= number_format($result_dte[0]['MONTO_IVA'], 1, ',', '');		//20 Monto IVA
				$TOTAL_CON_IVA		= number_format($result_dte[0]['TOTAL_CON_IVA'], 1, ',', '');	//21 Monto Total
				$D1					= 'D1';															//22 Tipo de Mov 1 (Desc/Rec)
				$P1					= '$';															//23 Tipo de valor de Desc/Rec 1
				$MONTO_DSCTO1		= number_format($result_dte[0]['MONTO_DSCTO1'], 1, ',', '');	//24 Valor del Desc/Rec 1
				$D2					= 'D2';															//25 Tipo de Mov 2 (Desc/Rec)
				$P2					= '$';															//26 Tipo de valor de Desc/Rec 2
				$MONTO_DSCTO2		= number_format($result_dte[0]['MONTO_DSCTO2'], 1, ',', '');	//27 Valor del Desc/Rec 2
				$D3					= 'D3';															//28 Tipo de Mov 3 (Desc/Rec)
				$P3					= '$';															//29 Tipo de valor de Desc/Rec 3
				$MONTO_DSCTO3		= '';															//30 Valor del Desc/Rec 3
				$NOM_FORMA_PAGO		= $result_dte[0]['NOM_FORMA_PAGO'];								//Dato Especial forma de pago adicional
				$NRO_ORDEN_COMPRA	= $result_dte[0]['NRO_ORDEN_COMPRA'];							//Numero de Orden Pago
				$NRO_NOTA_VENTA		= $result_dte[0]['COD_DOC'];									//Numero de Nota Venta
				$OBSERVACIONES		= $result_dte[0]['OBS'];										//si la factura tiene notas u observaciones
				$OBSERVACIONES		=  eregi_replace("[\n|\r|\n\r]", ' ', $OBSERVACIONES); //elimina los saltos de linea. entre otros caracteres
				$TOTAL_EN_PALABRAS	= $result_dte[0]['TOTAL_EN_PALABRAS'];							//Total en palabras: Posterior al campo Notas
	
				
					
				//GENERA EL NOMBRE DEL ARCHIVO
				if($PORC_IVA != 0){
					$TIPO_FACT = 33;	//FACTURA AFECTA
				}else{
					$TIPO_FACT = 34;	//FACTURA EXENTA
				}
	
				//GENERA EL ALFANUMERICO ALETORIO Y LLENA LA VARIABLE $RES = ALETORIO
				$length = 36;
				$source = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				$source .= '1234567890';
				
				if($length>0){
			        $RES = "";
			        $source = str_split($source,1);
			        for($i=1; $i<=$length; $i++){
			            mt_srand((double)microtime() * 1000000);
			            $num	= mt_rand(1,count($source));
			            $RES	.= $source[$num-1];
			        }
				 
			    }			
				
				//GENERA ESPACIOS EN BLANCO
				$space = ' ';
				$i = 0; 
				while($i<=100){
					$space .= ' ';
				$i++;
				}
				
				//GENERA ESPACIOS CON CEROS
				$llena_cero = 0;
				$i = 0; 
				while($i<=100){
					$llena_cero .= 0;
				$i++;
				}
				
				//Asignando espacios en blanco Factura
				//LINEA 3
				$NRO_FACTURA	= substr($NRO_FACTURA.$space, 0, 10);		// 1 Numero Factura  
				$FECHA_FACTURA	= substr($FECHA_FACTURA.$space, 0, 10);		// 2 Fecha Factura
				$TD				= substr($TD.$space, 0, 1);					// 3 Tipo Despacho
				$TT				= substr($TT.$space, 0, 1);					// 4 Tipo Traslado
				$PAGO_DTE		= substr($PAGO_DTE.$space, 0, 1);			// 5 Forma de Pago
				$FV				= substr($FV.$space, 0, 10);				// 6 Fecha Vencimiento
				$RUT_EMPRESA	= substr($RUT_EMPRESA.$space, 0, 10);		// 7 Rut Empresa
				$NOM_EMPRESA	= substr($NOM_EMPRESA.$space, 0, 100);		// 8 Razol Social_Nombre Empresa
				$GIRO			= substr($GIRO.$space, 0, 40);				// 9 Giro Empresa
				$DIRECCION		= substr($DIRECCION.$space, 0, 60);			//10 Direccion empresa
				$MAIL_CARGO_PERSONA = substr($MAIL_CARGO_PERSONA.$space, 0, 60);//11 E-Mail Contacto
				$TELEFONO		= substr($TELEFONO.$space, 0, 15);			//12 Telefono Empresa
				$REFERENCIA		= substr($REFERENCIA.$space, 0, 80);
				$NRO_GUIA_DESPACHO	= substr($NRO_GUIA_DESPACHO.$space, 0, 20);//Solicitado a VE por SP
				$GENERA_SALIDA	= substr($GENERA_SALIDA.$space, 0, 30);		//DESPACHADO
				$CANCELADA		= substr($CANCELADA.$space, 0, 30);			//CANCELADO
				$SUBTOTAL		= substr($SUBTOTAL.$space, 0, 18);			//Solicitado a VE por SP "SUBTOTAL"
				$PORC_DSCTO1	= substr($PORC_DSCTO1.$space, 0, 5);		//Solicitado a VE por SP "PORC_DSCTO1"
				$PORC_DSCTO2	= substr($PORC_DSCTO2.$space, 0, 5);		//Solicitado a VE por SP "PORC_DSCTO2"
				$EMISOR_FACTURA	= substr($EMISOR_FACTURA.$space, 0, 50);	//Solicitado a VE por SP "EMISOR_FACTURA"
				
				
				//LINEA4
				$NOM_COMUNA		= substr($NOM_COMUNA.$space, 0, 20);		//13 Comuna Recepcion
				$NOM_CIUDAD		= substr($NOM_CIUDAD.$space, 0, 20);		//14 Ciudad Recepcion
				$DP				= substr($DP.$space, 0, 60);				//15 Dirección Postal
				$COP			= substr($COP.$space, 0, 20);				//16 Comuna Postal
				$CIP			= substr($CIP.$space, 0, 20);				//17 Ciudad Postal
	
				//Asignando espacios en blanco Totales de Factura
				$TOTAL_NETO		= substr($TOTAL_NETO.$space, 0, 18);		//18 Monto Neto
				$PORC_IVA		= substr($PORC_IVA.$space, 0, 5);			//19 Tasa IVA
				$MONTO_IVA		= substr($MONTO_IVA.$space, 0, 18);			//20 Monto IVA
				$TOTAL_CON_IVA	= substr($TOTAL_CON_IVA.$space, 0, 18);		//21 Monto Total
				$D1				= substr($D1.$space, 0, 1);					//22 Tipo de Mov 1 (Desc/Rec)
				$P1				= substr($P1.$space, 0, 1);					//23 Tipo de valor de Desc/Rec 1
				$MONTO_DSCTO1	= substr($MONTO_DSCTO1.$space, 0, 18);		//24 Valor del Desc/Rec 1
				$D2				= substr($D2.$space, 0, 1);					//25 Tipo de Mov 2 (Desc/Rec)
				$P2				= substr($P2.$space, 0, 1);					//26 Tipo de valor de Desc/Rec 2
				$MONTO_DSCTO2	= substr($MONTO_DSCTO2.$space, 0, 18);		//27 Valor del Desc/Rec 2
				$D3				= substr($D3.$space, 0, 1);					//28 Tipo de Mov 3 (Desc/Rec)
				$P3				= substr($P3.$space, 0, 1);					//29 Tipo de valor de Desc/Rec 3
				$MONTO_DSCTO3	= substr($MONTO_DSCTO3.$space, 0, 18);		//30 Valor del Desc/Rec 3
				$NOM_FORMA_PAGO = substr($NOM_FORMA_PAGO.$space, 0, 80);	//Dato Especial forma de pago adicional
				$NRO_ORDEN_COMPRA= substr($NRO_ORDEN_COMPRA.$space, 0, 20);	//Numero de Orden Pago
				$NRO_NOTA_VENTA = substr($NRO_NOTA_VENTA.$space, 0, 20);	//Numero de Nota Venta
				$OBSERVACIONES = substr($OBSERVACIONES.$space.$space.$space, 0, 250); //si la factura tiene notas u observaciones
				$TOTAL_EN_PALABRAS = substr($TOTAL_EN_PALABRAS.' PESOS.'.$space.$space, 0, 200);	//Total en palabras: Posterior al campo Notas
				
				$RETIRA_RECINTO	= substr($RETIRA_RECINTO.$space, 0, 30);	// Persona que Retira de Recinto
				$RECINTO		= substr($RECINTO.$space, 0, 30);			// Recinto
				$PATENTE		= substr($PATENTE.$space, 0, 30);			// Patente Vehiculo que retira
				$RUT_RETIRA		= substr($RUT_RETIRA.$space, 0, 18);		// Rut quien retira
				$FECHA_HORA_RETIRO = substr($FECHA_HORA_RETIRO.$space, 0, 20); // Fecha y hora de retiro del Recinto
				
				$name_archivo = $TIPO_FACT."_NPG_".$RES.".SPF";
				$fname = tempnam("/tmp", $name_archivo);
				$handle = fopen($fname,"w");
				//DATOS DE FACTURA A EXPORTAR 
				//linea 1 y 2
				fwrite($handle, "\r\n"); //salto de linea
				fwrite($handle, "\r\n"); //salto de linea
				//linea 3		
				fwrite($handle, ' ');									// 0 space 2
				fwrite($handle, $NRO_FACTURA.$this->separador);			// 1 Numero Factura					//OK MH	Linea 5
				fwrite($handle, $FECHA_FACTURA.$this->separador);		// 2 Fecha Factura					//OK MH	Linea 4
				fwrite($handle, $TD.$this->separador);					// 3 Tipo Despacho					//OK MH	Linea 7
				fwrite($handle, $TT.$this->separador);					// 4 Tipo Traslado					//OK MH	Linea 8
				fwrite($handle, $PAGO_DTE.$this->separador);			// 5 Forma de Pago					//OK MH	Linea 13
				fwrite($handle, $FV.$this->separador);					// 6 Fecha Vencimiento				//OK MH	Linea 31
				fwrite($handle, $RUT_EMPRESA.$this->separador);			// 7 Rut Empresa					//OK MH	Linea 52
				fwrite($handle, $NOM_EMPRESA.$this->separador);			// 8 Razol Social_Nombre Empresa	//OK MH Linea 54
				fwrite($handle, $GIRO.$this->separador);				// 9 Giro Empresa					//OK MH Linea 58
				fwrite($handle, $DIRECCION.$this->separador);			//10 Direccion empresa				//OK MH Linea 61
				//Personalizados Linea 3
				fwrite($handle, $MAIL_CARGO_PERSONA.$this->separador);	//11 E-Mail Contacto						//OK MH Linea 60
				fwrite($handle, $TELEFONO.$this->separador);			//12 Telefono Empresa						//OK MH Linea 59
				fwrite($handle, $REFERENCIA.$this->separador);			//Referencia de la Factura					//OK MH Linea 298
				fwrite($handle, $NRO_GUIA_DESPACHO.$this->separador);	//Solicitado a VE por SP					//Pendiente Se debe enviar en la linea 221 tantas veces como guias tenga referenciada la factura
				fwrite($handle, $GENERA_SALIDA.$this->separador);		//DESPACHADO Solicitado a VE por SP 		//OK MH Linea 297
				fwrite($handle, $CANCELADA.$this->separador);			//CANCELADO Solicitado a VE por SP			//OK MH Linea 296
				fwrite($handle, $SUBTOTAL.$this->separador);			//Solicitado a VE por SP "SUBTOTAL"			//OK MH Linea 200 Columna 48
				fwrite($handle, $PORC_DSCTO1.$this->separador);			//Solicitado a VE por SP "PORC_DSCTO1"		//OK MH Linea 201 Columna 48
				fwrite($handle, $PORC_DSCTO2.$this->separador);			//Solicitado a VE por SP "PORC_DSCTO2"		//OK MH Linea 202 Columna 48
				fwrite($handle, $EMISOR_FACTURA.$this->separador);		//Solicitado a VE por SP "EMISOR_FACTURA"	//OK MH Linea 48
				
				//fwrite($handle, "\r\n"); //salto de linea
				fwrite($handle, $RETIRA_RECINTO.$this->separador);		// Persona que Retira de Recinto		//OK MH Linea 295
				fwrite($handle, $RECINTO.$this->separador);				// Recinto								//OK MH No se Envia
				fwrite($handle, $PATENTE.$this->separador);				// Patente Vehiculo que retira			//OK MH Linea 294
				fwrite($handle, $RUT_RETIRA.$this->separador);			// Rut quien retira						//OK MH Linea 293
				fwrite($handle, $FECHA_HORA_RETIRO.$this->separador);	// Fecha y hora de retiro del Recinto	//OK MH No se Envia
				fwrite($handle, "\r\n"); //salto de linea
				//linea 4
				fwrite($handle, ' ');									// 0 space 2
				fwrite($handle, $NOM_COMUNA.$this->separador);			//13 Comuna Recepcion							//OK MH Linea 62
				fwrite($handle, $NOM_CIUDAD.$this->separador);			//14 Ciudad Recepcion							//OK MH Linea 63
				fwrite($handle, $DP.$this->separador);					//15 Dirección Postal							//OK MH Linea 64
				fwrite($handle, $COP.$this->separador);					//16 Comuna Postal								//OK MH Linea 65
				fwrite($handle, $CIP.$this->separador);					//17 Ciudad Postal								//OK MH Linea 66
				fwrite($handle, $TOTAL_NETO.$this->separador);			//18 Monto Neto									//OK MH Linea 108
				fwrite($handle, $PORC_IVA.$this->separador);			//19 Tasa IVA									//OK MH Linea 112
				fwrite($handle, $MONTO_IVA.$this->separador);			//20 Monto IVA									//OK MH Linea 113
				fwrite($handle, $TOTAL_CON_IVA.$this->separador);		//21 Monto Total								//OK MH Linea 128
				fwrite($handle, $D1.$this->separador);					//22 Tipo de Mov 1 (Desc/Rec)					//OK MH Linea 200 Columna 1
				fwrite($handle, $P1.$this->separador);					//23 Tipo de valor de Desc/Rec 1				//OK MH Linea 200 Columna 47
				fwrite($handle, $MONTO_DSCTO1.$this->separador);		//24 Valor del Desc/Rec 1						//OK MH Linea 201 Columna 48
				fwrite($handle, $D2.$this->separador);					//25 Tipo de Mov 2 (Desc/Rec)					//OK MH Linea 201 Columna 1
				fwrite($handle, $P2.$this->separador);					//26 Tipo de valor de Desc/Rec 2				//OK MH Linea 201 Columna 47
				fwrite($handle, $MONTO_DSCTO2.$this->separador);		//27 Valor del Desc/Rec 2						//OK MH Linea 202 Columna 48
				fwrite($handle, $D3.$this->separador);					//28 Tipo de Mov 3 (Desc/Rec)					//OK MH Linea 202 Columna 1
				fwrite($handle, $P3.$this->separador);					//29 Tipo de valor de Desc/Rec 3				//OK MH Linea 202 Columna 47	
				fwrite($handle, $MONTO_DSCTO3.$this->separador);		//30 Valor del Desc/Rec 3						//OK MH No se envia
				fwrite($handle, $NOM_FORMA_PAGO.$this->separador);		//Dato Especial forma de pago adicional			//OK MH Linea 292
				fwrite($handle, $NRO_ORDEN_COMPRA.$this->separador);	//Numero de Orden Pago							//OK MH Linea 221
				fwrite($handle, $NRO_NOTA_VENTA.$this->separador);		//Numero de Nota Venta							//OK MH Linea 291
				fwrite($handle, $OBSERVACIONES.$this->separador);		//si la factura tiene notas u observaciones		//OK MH Linea 290
				fwrite($handle, $TOTAL_EN_PALABRAS.$this->separador);	//Total en palabras: Posterior al campo Notas	//OK MH Linea 283
				fwrite($handle, "\r\n"); //salto de linea
				
				//datos de dw_item_factura linea 5 a 34
				for ($i = 0; $i < 30; $i++){
					if($i < $count){
						fwrite($handle, ' '); //0 space 2
						$ORDEN		= $result_dte[$i]['ORDEN'];	
						$MODELO		= $result_dte[$i]['COD_PRODUCTO'];
						$NOM_PRODUCTO = substr($result_dte[$i]['NOM_PRODUCTO'], 0, 60);
						$CANTIDAD	= number_format($result_dte[$i]['CANTIDAD'], 1, ',', '');
						$P_UNITARIO	= number_format($result_dte[$i]['PRECIO'], 1, ',', '');
						$TOTAL		= number_format($result_dte[$i]['TOTAL_FA'], 1, ',', '');
						$DESCRIPCION= $MODELO; // se repite el modelo
						$CANTIDAD_DETALLE = $CANTIDAD; // se repite el $CANTIDAD
						
						//Asignando espacios en blanco dw_item_factura
						$ORDEN = $ORDEN / 10; //ELIMINA EL CERO
						$ORDEN		= substr($ORDEN.$space, 0, 2);					
						$MODELO		= substr($MODELO.$space, 0, 35);
						$NOM_PRODUCTO= substr($NOM_PRODUCTO.$space, 0, 80);
						$CANTIDAD	= substr($CANTIDAD.$space, 0, 18);
						$P_UNITARIO	= substr($P_UNITARIO.$space, 0, 18);
						$TOTAL		= substr($TOTAL.$space, 0, 18);
						$DESCRIPCION= substr($DESCRIPCION.$space, 0, 59);
						$CANTIDAD_DETALLE = substr($CANTIDAD_DETALLE.$space, 0, 18);
	
						//DATOS DE ITEM_FACTURA A EXPORTAR
						fwrite($handle, $ORDEN.$this->separador);		//31 Número de Línea //OK MH Linea 147 Columna 2431
						fwrite($handle, $MODELO.$this->separador);		//32 Código item //OK MH Linea 147 Columna 60
						fwrite($handle, $NOM_PRODUCTO.$this->separador);//33 Nombre del Item //OK MH Linea 147 Columna 440
						fwrite($handle, $CANTIDAD.$this->separador);	//34 Cantidad //OK MH Linea 147 Columna 1560
						fwrite($handle, $P_UNITARIO.$this->separador);	//35 Precio Unitario //OK MH Linea 147 Columna 1893
						fwrite($handle, $TOTAL.$this->separador);		//36 Valor por linea de detalle //OK MH Linea 147 Columna 2353
						fwrite($handle, $DESCRIPCION.$this->separador);	//37 personalizados Zona Detalles(Modelo ítem) //OK MH Linea 147 Columna 2371
						fwrite($handle, $CANTIDAD_DETALLE.$this->separador);	//personalizados Zona Detalles SE REPITE $CANTIDAD //OK MH Linea 147 Columna 2401
					}
					fwrite($handle, "\r\n");
				}
				
				//LINEA 35 SOLICITU DE V ESPINOIZA FA MINERAS
				$sql_ref = "SELECT	 NRO_ORDEN_COMPRA
									,CONVERT(VARCHAR(10), FECHA_ORDEN_COMPRA_CLIENTE ,103) FECHA_OC
							FROM 	FACTURA 
							WHERE 	COD_FACTURA = $cod_factura";
				
				$result_ref = $db->build_results($sql_ref);
				$NRO_OC_FACTURA	= $result_ref[0]['NRO_ORDEN_COMPRA'];
				$FECHA_REF_OC	= $result_ref[0]['FECHA_OC'];
				
				//($a == $b) && ($c > $b)
				if(($NRO_OC_FACTURA == '') or ($FECHA_REF_OC == '')){
					//no existe OC en factura
					//Linea 36 a 44	Referencia
					$TDR	= $this->llena_cero;
					$FR		= $this->llena_cero;
					$FECHA_R= $this->vacio;
					$CR		= $this->llena_cero;
					$RER	= $this->vacio;
					
					//Asignando espacios en blanco Referencia
					$TDR	= substr($TDR.$space, 0, 3);
					$FR		= substr($FR.$space, 0, 18);
					$FECHA_R= substr($FECHA_R.$space, 0, 10);
					$CR		= substr($CR.$space, 0, 1);
					$RER	= substr($RER.$space, 0, 100);					
					
					fwrite($handle, ' '); //0 space 2
					fwrite($handle, $TDR.$this->separador);			//38 Tipo documento referencia
					fwrite($handle, $FR.$this->separador);			//39 Folio Referencia
					fwrite($handle, $FECHA_R.$this->separador);		//40 Fecha de Referencia
					fwrite($handle, $CR.$this->separador);			//41 Código de Referencia
					fwrite($handle, $RER.$this->separador);			//42 Razón explícita de la referencia
				}else{
					$TIPO_COD_REF		= '801';
					$NRO_OC_FACTURA		= $result_ref[0]['NRO_ORDEN_COMPRA'];	
					$FECHA_REF_OC		= $result_ref[0]['FECHA_OC'];
					$CR					= '1';
					$RAZON_REF_OC		= 'ORDEN DE COMPRA';
					
					$TIPO_COD_REF	= substr($TIPO_COD_REF.$space, 0, 3);
					$NRO_OC_FACTURA	= substr($NRO_OC_FACTURA.$space, 0, 18);
					$FECHA_REF_OC	= substr($FECHA_REF_OC.$space, 0, 10);
					$CR				= substr($CR.$space, 0, 1);
					$RAZON_REF_OC	= substr($RAZON_REF_OC.$space, 0, 100);
					
					fwrite($handle, ' '); //0 space 2
					fwrite($handle, $TIPO_COD_REF.$this->separador);			//TIPOCODREF. SOLI 
					fwrite($handle, $NRO_OC_FACTURA.$this->separador);			//FOLIOREF......Folio Referencia
					fwrite($handle, $FECHA_REF_OC.$this->separador);			//FECHA OC Código de Referencia
					fwrite($handle, $CR.$this->separador);						//41 Código de Referencia
					fwrite($handle, $RAZON_REF_OC.$this->separador);			//RAZON  KJNSK... Razón explícita de la referencia
				}
				fclose($handle);
				/*
				header("Content-Type: application/x-msexcel; name=\"$name_archivo\"");
				header("Content-Disposition: inline; filename=\"$name_archivo\"");
				$fh=fopen($fname, "rb");
				fpassthru($fh);*/
				
				$upload = $this->Envia_DTE($name_archivo, $fname);
				$NRO_FACTURA	= trim($NRO_FACTURA);
				if (!$upload) {
					$this->_load_record();
					$this->alert('No se pudo enviar Fatura Electronica Nº '.$NRO_FACTURA.', Por favor contacte a IntegraSystem.');								
				}else{
					if ($PORC_IVA == 0){
						$this->_load_record();
						$this->alert('Gestión Realizada con exíto. Factura Exenta Electronica Nº '.$NRO_FACTURA.'.');
					}else{
						$this->_load_record();
						$this->alert('Gestión Realizada con exíto. Factura Electronica Nº '.$NRO_FACTURA.'.');
					}								
				}
				unlink($fname);
			}else{
				$db->ROLLBACK_TRANSACTION();
				return false;
			}
			$this->unlock_record();
		}
		
		function envia_FA_electronica2(){
			if(!$this->lock_record())
				return false;
			
			$cod_factura = $this->get_key();	
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$count1= 0;
			
			$sql_valida="SELECT CANTIDAD 
				  		 FROM ITEM_FACTURA
				  		 WHERE COD_FACTURA = $cod_factura";
				  
			$result_valida = $db->build_results($sql_valida);

			for($i = 0 ; $i < count($result_valida) ; $i++){
				if($result_valida[$i] <> 0)
					$count1 = $count1 + 1;
			}
			if($count1 > 18){
				$this->_load_record();
				$this->alert('Se está ingresando más item que la cantidad permitida, favor contacte a IntegraSystem.');
				return false;
			}

			$this->sepa_decimales	= ',';	//Usar , como separador de decimales
			$this->vacio 			= ' ';	//Usar rellenos de blanco, CAMPO ALFANUMERICO
			$this->llena_cero		= 0;	//Usar rellenos con '0', CAMPO NUMERICO
			$this->separador		= ';';	//Usar ; como separador de campos
			$cod_usuario_impresion = $this->cod_usuario;
			
			$cod_impresora_dte = $_POST['wi_impresora_dte'];
			if($cod_impresora_dte == 100)
				$emisor_factura = 'SALA VENTA';
			else{
				if ($cod_impresora_dte == '')
					$sql = "SELECT U.NOM_USUARIO EMISOR_FACTURA
							FROM USUARIO U, FACTURA F
							WHERE F.COD_FACTURA = $cod_factura
							  and U.COD_USUARIO = $cod_usuario_impresion";
				else
					$sql = "SELECT NOM_REGLA EMISOR_FACTURA
							FROM IMPRESORA_DTE
							WHERE COD_IMPRESORA_DTE = $cod_impresora_dte";
							
				$result = $db->build_results($sql);
				$emisor_factura = $result[0]['EMISOR_FACTURA'];
			}
			
			$db->BEGIN_TRANSACTION();
			$sp = 'spu_factura';
			$param = "'ENVIA_DTE', $cod_factura, $cod_usuario_impresion";

			if ($db->EXECUTE_SP($sp, $param)){
				$db->COMMIT_TRANSACTION();
				
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				//declrar constante para que el monto con iva del reporte lo transpforme a palabras
				$sql_total = "select TOTAL_CON_IVA from FACTURA where COD_FACTURA = $cod_factura";
				$resul_total = $db->build_results($sql_total);
				$total_con_iva = $resul_total[0]['TOTAL_CON_IVA'] ;
				$total_en_palabras =  Numbers_Words::toWords($total_con_iva,"es"); 
				$total_en_palabras = strtr($total_en_palabras, "áéíóú", "aeiou");
				$total_en_palabras = strtoupper($total_en_palabras);
				
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				$sql_dte = "SELECT	F.COD_FACTURA,
									F.NRO_FACTURA,
									F.TIPO_DOC,
									dbo.f_format_date(FECHA_FACTURA,1)FECHA_FACTURA,
									F.COD_USUARIO_IMPRESION,
									'$emisor_factura' EMISOR_FACTURA,
									F.NRO_ORDEN_COMPRA,
									dbo.f_fa_nros_guia_despacho(".$cod_factura.") NRO_GUIAS_DESPACHO,	
									F.REFERENCIA,
									F.NOM_EMPRESA,
									F.GIRO,
									F.RUT,
									F.DIG_VERIF,
									F.DIRECCION,
									dbo.f_emp_get_mail_cargo_persona(F.COD_PERSONA, '[EMAIL]') MAIL_CARGO_PERSONA,
									F.TELEFONO,
									F.FAX,
									F.COD_DOC,
									F.SUBTOTAL,
									F.PORC_DSCTO1,
									F.MONTO_DSCTO1,
									F.PORC_DSCTO2,
									F.MONTO_DSCTO2,
									F.MONTO_DSCTO1 + F.MONTO_DSCTO2 TOTAL_DSCTO,
									F.TOTAL_NETO,
									F.PORC_IVA,
									F.MONTO_IVA,
									F.TOTAL_CON_IVA,
									F.RETIRADO_POR,
									F.RUT_RETIRADO_POR,
									F.DIG_VERIF_RETIRADO_POR,
									COM.NOM_COMUNA,
									CIU.NOM_CIUDAD,
									FP.NOM_FORMA_PAGO,
									FP.COD_PAGO_DTE,
									F.NOM_FORMA_PAGO_OTRO,
									ITF.COD_ITEM_FACTURA,
									ITF.ORDEN,								
									ITF.ITEM,
									ITF.CANTIDAD,
									ITF.COD_PRODUCTO,
									ITF.NOM_PRODUCTO,
									ITF.PRECIO,
									ITF.PRECIO * ITF.CANTIDAD  TOTAL_FA,
									'".$total_en_palabras."' TOTAL_EN_PALABRAS,
									convert(varchar(5), GETDATE(), 8) HORA,
									F.GENERA_SALIDA,
									F.OBS,
									F.CANCELADA,
									F.RETIRADO_POR,
									F.RUT_RETIRADO_POR,
									F.DIG_VERIF_RETIRADO_POR,
									F.GUIA_TRANSPORTE,
									F.PATENTE
							FROM 	FACTURA F left outer join COMUNA COM on F.COD_COMUNA = COM.COD_COMUNA,
									ITEM_FACTURA ITF, CIUDAD CIU, FORMA_PAGO FP 
							WHERE 	F.COD_FACTURA = ".$cod_factura." 
							AND	ITF.COD_FACTURA = F.COD_FACTURA
							AND	CIU.COD_CIUDAD = F.COD_CIUDAD
							AND	FP.COD_FORMA_PAGO = F.COD_FORMA_PAGO";
									
				$result_dte = $db->build_results($sql_dte);
				//CANTIDAD DE ITEM_FACTURA 
				$count = count($result_dte);
				
				// datos de factura
				$NRO_FACTURA		= $result_dte[0]['NRO_FACTURA'] ;		// 1 Numero Factura
				$FECHA_FACTURA		= $result_dte[0]['FECHA_FACTURA'] ;		// 2 Fecha Factura
				//Email - VE: =>En el caso de las Factura y otros documentos, no aplica por lo que se dejan 0;0 
				$TD					= $this->llena_cero;					// 3 Tipo Despacho
				$TT					= $this->llena_cero;					// 4 Tipo Traslado
				//Email - VE: => 
				$PAGO_DTE			= $result_dte[0]['COD_PAGO_DTE'];		// 5 Forma de Pago
				$FV					= $this->vacio;							// 6 Fecha Vencimiento
				$RUT				= $result_dte[0]['RUT'];				
				$DIG_VERIF			= $result_dte[0]['DIG_VERIF'];
				$RUT_EMPRESA		= $RUT.'-'.$DIG_VERIF;					// 7 Rut Empresa
				$NOM_EMPRESA		= $result_dte[0]['NOM_EMPRESA'] ;		// 8 Razol Social_Nombre Empresa
				$GIRO				= $result_dte[0]['GIRO'];				// 9 Giro Empresa
				$DIRECCION			= $result_dte[0]['DIRECCION'];			//10 Direccion empresa
				$MAIL_CARGO_PERSONA	= $result_dte[0]['MAIL_CARGO_PERSONA'];	//11 E-Mail Contacto
				$TELEFONO			= $result_dte[0]['TELEFONO'];			//12 Telefono Empresa
				$REFERENCIA			= $result_dte[0]['REFERENCIA'];			//12 Referencia de la Factura  //datos olvidado por VE.
				$NRO_GUIA_DESPACHO	= $result_dte[0]['NRO_GUIAS_DESPACHO'];	//Solicitado a VE por SP
				$GENERA_SALIDA		= $result_dte[0]['GENERA_SALIDA'];		//Solicitado a VE por SP "DESPACHADO"
				
				if ($GENERA_SALIDA == 'S')
					$GENERA_SALIDA = 'DESPACHADO';
				else
					$GENERA_SALIDA = '';
				
				
				$CANCELADA	= $result_dte[0]['CANCELADA'];	//Solicitado a VE por SP "CANCELADO"
				if ($CANCELADA == 'S')
					$CANCELADA = 'CANCELADA';
				else
					$CANCELADA = '';
				
				$SUBTOTAL			= number_format($result_dte[0]['SUBTOTAL'], 1, ',', '');	//Solicitado a VE por SP "SUBTOTAL"
				$PORC_DSCTO1		= number_format($result_dte[0]['PORC_DSCTO1'], 1, ',', '');	//Solicitado a VE por SP "PORC_DSCTO1"
				$PORC_DSCTO2		= number_format($result_dte[0]['PORC_DSCTO2'], 1, ',', '');	//Solicitado a VE por SP "PORC_DSCTO2"
				$EMISOR_FACTURA		= $result_dte[0]['EMISOR_FACTURA'];		//Solicitado a VE por SP "EMISOR_FACTURA"
				$NOM_COMUNA			= $result_dte[0]['NOM_COMUNA'];			//13 Comuna Recepcion
				$NOM_CIUDAD			= $result_dte[0]['NOM_CIUDAD'];			//14 Ciudad Recepcion
				$DP					= $result_dte[0]['DIRECCION'];			//15 Dirección Postal
				$COP				= $result_dte[0]['NOM_COMUNA'];			//16 Comuna Postal
				$CIP				= $result_dte[0]['NOM_CIUDAD'];			//17 Ciudad Postal
				
				
				//OBSERVACION
				$RETIRA_RECINTO		= $result_dte[0]['RETIRADO_POR'];		// Persona que Retira de Recinto
				$RECINTO			= $this->vacio;							// Recinto
				$PATENTE			= $result_dte[0]['PATENTE'];			// Patente de Vehiculo que retira
				$RUT_RETIRADO_POR	= $result_dte[0]['RUT_RETIRADO_POR'];				
				$DIG_VERIF_RETIRADO_POR	= $result_dte[0]['DIG_VERIF_RETIRADO_POR'];
				if($RUT_RETIRADO_POR == ''){
					$RUT_RETIRA = '';
				}else{
					$RUT_RETIRA		= $RUT_RETIRADO_POR.'-'.$DIG_VERIF_RETIRADO_POR; // 27 Rut quien Retira
				}
				
				$TOTAL_NETO			= number_format($result_dte[0]['TOTAL_NETO'], 0, ',', '');		//18 Monto Neto
				$PORC_IVA			= number_format($result_dte[0]['PORC_IVA'], 2, '.', '');									//19 Tasa IVA
				$MONTO_IVA			= number_format($result_dte[0]['MONTO_IVA'], 0, ',', '');		//20 Monto IVA
				$TOTAL_CON_IVA		= number_format($result_dte[0]['TOTAL_CON_IVA'], 0, ',', '');	//21 Monto Total
				$D1					= 'D';															//22 Tipo de Mov 1 (Desc/Rec)
				$P1					= '$';															//23 Tipo de valor de Desc/Rec 1
				$MONTO_DSCTO1		= number_format($result_dte[0]['MONTO_DSCTO1'], 0, ',', '');	//24 Valor del Desc/Rec 1
				$D2					= 'D';															//25 Tipo de Mov 2 (Desc/Rec)
				$P2					= '$';															//26 Tipo de valor de Desc/Rec 2
				$MONTO_DSCTO2		= number_format($result_dte[0]['MONTO_DSCTO2'], 0, ',', '');	//27 Valor del Desc/Rec 2
				$D3					= 'D';															//28 Tipo de Mov 3 (Desc/Rec)
				$P3					= '$';															//29 Tipo de valor de Desc/Rec 3
				$MONTO_DSCTO3		= '';															//30 Valor del Desc/Rec 3
				$NOM_FORMA_PAGO		= $result_dte[0]['NOM_FORMA_PAGO'];								//Dato Especial forma de pago adicional
				$NRO_ORDEN_COMPRA	= $result_dte[0]['NRO_ORDEN_COMPRA'];							//Numero de Orden Pago
				$NRO_NOTA_VENTA		= $result_dte[0]['COD_DOC'];									//Numero de Nota Venta
				$OBSERVACIONES		= $result_dte[0]['OBS'];										//si la factura tiene notas u observaciones
				$OBSERVACIONES		=  eregi_replace("[\n|\r|\n\r]", ' ', $OBSERVACIONES); //elimina los saltos de linea. entre otros caracteres
				$TOTAL_EN_PALABRAS	= $result_dte[0]['TOTAL_EN_PALABRAS'];							//Total en palabras: Posterior al campo Notas
		
				//GENERA EL NOMBRE DEL ARCHIVO
				if($PORC_IVA != 0){
					$TIPO_FACT = 33;	//FACTURA AFECTA
				}else{
					$TIPO_FACT = 34;	//FACTURA EXENTA
				}
	
				//GENERA EL ALFANUMERICO ALETORIO Y LLENA LA VARIABLE $RES = ALETORIO
				$length = 36;
				$source = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				$source .= '1234567890';
				
				if($length>0){
			        $RES = "";
			        $source = str_split($source,1);
			        for($i=1; $i<=$length; $i++){
			            mt_srand((double)microtime() * 1000000);
			            $num	= mt_rand(1,count($source));
			            $RES	.= $source[$num-1];
			        }
				 
			    }			
				
				//GENERA ESPACIOS EN BLANCO
				function space($string, $cant_espacio=NULL){
					if($cant_espacio <> NULL)
						$spacio = $cant_espacio;
					else
						$spacio = 49;
					$string .= str_repeat(" ", $spacio); 
					$string = substr($string, 0, $spacio); 
					if($cant_espacio == NULL)
						$string .= ': ';
					return $string;
				}
				
				//GENERA ESPACIOS CON CEROS
				$llena_cero = 0;
				$i = 0; 
				while($i<=100){
					$llena_cero .= 0;
				$i++;
				}
				
				//Asignando espacios en blanco Factura
				//LINEA 3
				$NRO_FACTURA	= substr($NRO_FACTURA, 0, 10);		// 1 Numero Factura
				$FECHA_FACTURA	= substr($FECHA_FACTURA, 0, 10);		// 2 Fecha Factura
				$TD				= substr($TD, 0, 1);					// 3 Tipo Despacho	(Solo para Guías de Despacho)
				$TT				= substr($TT, 0, 1);					// 4 Tipo Traslado
				$PAGO_DTE		= substr($PAGO_DTE, 0, 1);			// 5 Forma de Pago
				$FV				= substr($FV, 0, 10);				// 6 Fecha Vencimiento
				$RUT_EMPRESA	= substr($RUT_EMPRESA, 0, 10);		// 7 Rut Empresa
				$NOM_EMPRESA	= substr($NOM_EMPRESA, 0, 100);		// 8 Razol Social_Nombre Empresa
				$GIRO			= substr($GIRO, 0, 40);				// 9 Giro Empresa
				$DIRECCION		= substr($DIRECCION, 0, 60);			//10 Direccion empresa
				$MAIL_CARGO_PERSONA = substr($MAIL_CARGO_PERSONA, 0, 60);//11 E-Mail Contacto
				$TELEFONO		= substr($TELEFONO, 0, 15);			//12 Telefono Empresa
				$REFERENCIA		= substr($REFERENCIA, 0, 80);
				$NRO_GUIA_DESPACHO	= substr($NRO_GUIA_DESPACHO, 0, 20);//Solicitado a VE por SP
				$GENERA_SALIDA	= substr($GENERA_SALIDA, 0, 30);		//DESPACHADO
				$CANCELADA		= substr($CANCELADA, 0, 30);			//CANCELADO
				$SUBTOTAL		= substr($SUBTOTAL, 0, 18);			//Solicitado a VE por SP "SUBTOTAL"
				$PORC_DSCTO1	= substr($PORC_DSCTO1, 0, 5);		//Solicitado a VE por SP "PORC_DSCTO1"
				$PORC_DSCTO2	= substr($PORC_DSCTO2, 0, 5);		//Solicitado a VE por SP "PORC_DSCTO2"
				$EMISOR_FACTURA	= substr($EMISOR_FACTURA, 0, 50);	//Solicitado a VE por SP "EMISOR_FACTURA"
				
				
				//LINEA4
				$NOM_COMUNA		= substr($NOM_COMUNA, 0, 20);		//13 Comuna Recepcion
				$NOM_CIUDAD		= substr($NOM_CIUDAD, 0, 20);		//14 Ciudad Recepcion
				$DP				= substr($DP, 0, 60);				//15 Dirección Postal
				$COP			= substr($COP, 0, 20);				//16 Comuna Postal
				$CIP			= substr($CIP, 0, 20);				//17 Ciudad Postal
	
				//Asignando espacios en blanco Totales de Factura
				$TOTAL_NETO		= substr($TOTAL_NETO, 0, 18);		//18 Monto Neto
				$PORC_IVA		= substr($PORC_IVA, 0, 5);			//19 Tasa IVA
				$MONTO_IVA		= substr($MONTO_IVA, 0, 18);			//20 Monto IVA
				$TOTAL_CON_IVA	= substr($TOTAL_CON_IVA, 0, 18);		//21 Monto Total
				$D1				= substr($D1, 0, 1);					//22 Tipo de Mov 1 (Desc/Rec)
				$P1				= substr($P1, 0, 1);					//23 Tipo de valor de Desc/Rec 1
				$MONTO_DSCTO1	= substr($MONTO_DSCTO1, 0, 18);		//24 Valor del Desc/Rec 1
				$D2				= substr($D2, 0, 1);					//25 Tipo de Mov 2 (Desc/Rec)
				$P2				= substr($P2, 0, 1);					//26 Tipo de valor de Desc/Rec 2
				$MONTO_DSCTO2	= substr($MONTO_DSCTO2, 0, 18);		//27 Valor del Desc/Rec 2
				$D3				= substr($D3, 0, 1);					//28 Tipo de Mov 3 (Desc/Rec)
				$P3				= substr($P3, 0, 1);					//29 Tipo de valor de Desc/Rec 3
				$MONTO_DSCTO3	= substr($MONTO_DSCTO3, 0, 18);		//30 Valor del Desc/Rec 3
				$NOM_FORMA_PAGO = substr($NOM_FORMA_PAGO, 0, 80);	//Dato Especial forma de pago adicional
				$NRO_ORDEN_COMPRA= substr($NRO_ORDEN_COMPRA, 0, 20);	//Numero de Orden Pago
				$NRO_NOTA_VENTA = substr($NRO_NOTA_VENTA, 0, 20);	//Numero de Nota Venta
				$OBSERVACIONES = substr($OBSERVACIONES, 0, 250); //si la factura tiene notas u observaciones
				$TOTAL_EN_PALABRAS = substr($TOTAL_EN_PALABRAS.' PESOS.', 0, 200);	//Total en palabras: Posterior al campo Notas
				
				$RETIRA_RECINTO	= substr($RETIRA_RECINTO, 0, 30);	// Persona que Retira de Recinto
				$RECINTO		= substr($RECINTO, 0, 30);			// Recinto
				$PATENTE		= substr($PATENTE, 0, 30);			// Patente Vehiculo que retira
				$RUT_RETIRA		= substr($RUT_RETIRA, 0, 18);		// Rut quien retira
							
				$name_archivo = $TIPO_FACT."_NPG_".$RES.".SPF";
				$fname = tempnam("/tmp", $name_archivo);
				$handle = fopen($fname,"w");
				//DATOS DE FACTURA A EXPORTAR 
				fwrite($handle, 'XXX INICIO DOCUMENTO');
				fwrite($handle, "\r\n");
				fwrite($handle, '========== AREA IDENTIFICACION DEL DOCUMENTO');
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo Documento Tributario Electronico')); 
				fwrite($handle, space($TIPO_FACT, 3));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Folio Documento'));
				fwrite($handle, space($NRO_FACTURA, 10));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha de Emisión'));
				fwrite($handle, space($FECHA_FACTURA, 10)); 
				fwrite($handle, "\r\n");
				fwrite($handle, space('Indicador No rebaja'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo de Despacho'));// Solo para guía despacho
				fwrite($handle, $TD);
				fwrite($handle, "\r\n");
				fwrite($handle, space('Indicador de Traslado'));
				fwrite($handle, $TT);
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo Impresión'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Indicador de Servicio'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Indicador de Montos Brutos'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Indicador de Montos Netos'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Forma de Pago'));
				fwrite($handle, $PAGO_DTE);
				fwrite($handle, "\r\n");
				fwrite($handle, space('Forma de Pago Exportación'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha de Cancelación'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Cancelado'));
				fwrite($handle, space($TOTAL_CON_IVA, 18));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Saldo Insoluto'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Periodo Desde'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Periodo Hasta'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Medio de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo de Cuenta de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Numero de Cuenta de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Banco de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Terminos de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Glosa del Termino de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Días del Termino de Pago'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha Vencimiento'));
				fwrite($handle, space($FV, 10));
				fwrite($handle, "\r\n");
				fwrite($handle, '========== AREA EMISOR');
				fwrite($handle, "\r\n");
				fwrite($handle, space('Rut Emisor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Razón Social Emisor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Giro del Emisor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Telefono'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Correo Emisor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('ACTECO'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Emisor Traslado Excepcional'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Folio Autorizacion'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Fecha Autorizacion'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Dirección de Origen Emisor'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Comuna de Origen Emisor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Ciudad de Origen Emisor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Nombre Sucursal'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Sucursal'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Adicional Sucursal'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Vendedor'));//Revisar con MH
				fwrite($handle, space($EMISOR_FACTURA, 60));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Identificador Adicional del Emisor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('RUT Mandante'));//
				fwrite($handle, "\r\n");
				fwrite($handle, '========== AREA RECEPTOR');
				fwrite($handle, "\r\n");
				fwrite($handle, space('Rut Receptor'));
				fwrite($handle, space($RUT_EMPRESA, 10));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Interno Receptor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Nombre o Razón Social Receptor'));
				fwrite($handle, space($NOM_EMPRESA, 100));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Numero Identificador Receptor Extranjero'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Nacionalidad del Receptor Extranjero'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Identificador Adicional Receptor Extranjero'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Giro del negocio del Receptor'));
				fwrite($handle, space($GIRO, 40));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Contacto')); //Revisar con MH
				fwrite($handle, space($TELEFONO, 80));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Correo Receptor'));
				fwrite($handle, space($MAIL_CARGO_PERSONA, 80));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Dirección Receptor'));
				fwrite($handle, space($DIRECCION, 70));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Comuna Receptor'));
				fwrite($handle, space($NOM_COMUNA, 20));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Ciudad Receptor'));
				fwrite($handle, space($NOM_CIUDAD, 20));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Dirección Postal Receptor'));
				fwrite($handle, space($DP, 70));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Comuna Postal Receptor'));
				fwrite($handle, space($COP, 20));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Ciudad Postal Receptor'));
				fwrite($handle, space($CIP, 20));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Rut Solicitante de Factura'));//
				fwrite($handle, "\r\n");
				fwrite($handle, '========== AREA TRANSPORTES');
				fwrite($handle, "\r\n");
				fwrite($handle, space('Patente'));
				fwrite($handle, space($PATENTE, 8));
				fwrite($handle, "\r\n");
				fwrite($handle, space('RUT Transportista'));
				fwrite($handle, space($RUT_RETIRA, 10));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Rut Chofer'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Nombre del Chofer'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Dirección Destino'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Comuna Destino'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Ciudad Destino'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Modalidad De Ventas'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Clausula de Venta Exportación'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Total Clausula de Venta Exportacion'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Via de Transporte'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Nombre del Medio de Transporte'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('RUT Compañía de Transporte'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Nombre Compañía de Transporte'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Identificacion Adicional Compañía de Transporte'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Booking'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Operador'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Puerto de Embarque'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Identificador Adicional Puerto de Embarque'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Puerto Desembarque'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Identificador Adicional Puerto de Desembarque'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tara'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Unidad de Medida Tara'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Total Peso Bruto'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Unidad de Peso Bruto'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Total Peso Neto'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Unidad de Peso Neto'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Total Items'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Total Bultos'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Tipo de Bulto'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Tipo de Bulto'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Tipo de Bulto'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Tipo de Bulto'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Flete'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Seguro'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Pais Receptor'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Pais Destino'));//
				fwrite($handle, "\r\n");
				fwrite($handle, '========== AREA TOTALES');
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo Moneda Transacción'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Neto'));
				fwrite($handle, space($TOTAL_NETO, 18));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Exento'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Base Faenamiento de Carne'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Base de Margen de  Comercialización'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tasa IVA'));
				fwrite($handle, space($PORC_IVA, 5));
				fwrite($handle, "\r\n");
				fwrite($handle, space('IVA'));
				fwrite($handle, space($MONTO_IVA, 18));
				fwrite($handle, "\r\n");
				fwrite($handle, space('IVA Propio'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('IVA Terceros'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Impuesto Adicional'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Impuesto Adicional'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Impuesto Adicional'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Impuesto Adicional'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Impuesto Adicional'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Codigo Impuesto Adicional'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('IVA no Retenido'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Crédito Especial Emp. Constructoras'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Garantía por Deposito de Envases'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Valor Neto Comisiones'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Valor Exento Comisiones'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('IVA Comisiones'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Total'));
				fwrite($handle, space($TOTAL_CON_IVA, 18));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto No Facturable'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Periodo'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Saldo Anterior'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Valor a Pagar'));//
				fwrite($handle, "\r\n");
				fwrite($handle, '========== OTRA MONEDA');
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo Cambio'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Neto Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Exento Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Base Faenamiento de Carne Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Margen Comerc. Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('IVA Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tipo Imp. Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Tasa Imp. Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Valor Imp. Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('IVA No Retenido Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Total Otra Moneda'));//
				fwrite($handle, "\r\n");
				fwrite($handle, '========== DETALLE DE PRODUCTOS Y SERVICIOS');
				fwrite($handle, "\r\n");
				$j = 0;
				while($j < 30){
					if($j == 0){
						for($i=0 ; $i < $count ; $i++){
							$MODELO		= $result_dte[$i]['COD_PRODUCTO'];
							fwrite($handle, ' '); //Indicador Exención
							fwrite($handle, space('', 3));	//Tipo Doc. Liquidación
							fwrite($handle, space('', 10));	//Tipo Codigo (Mineras)
							fwrite($handle, space('', 35));	//Codigo Item (Mineras)
							fwrite($handle, space('', 10));	//Tipo Codigo
							fwrite($handle, space($MODELO, 35));	//Codigo Item
							fwrite($handle, space('', 10));	//Tipo Codigo
							fwrite($handle, space('', 35));	//Codigo Item
							fwrite($handle, space('', 2));	//Item Espectaculo
							fwrite($handle, space('', 10));	//RUT Mandante
							fwrite($handle, space('', 6));	//Folio Ticket
							fwrite($handle, space('', 10));	//Fecha Ticktet
							fwrite($handle, space('', 80));	//Nombre Evento
							fwrite($handle, space('', 10));	//Tipo Ticket
							fwrite($handle, space('', 5));	//Codigo Evento
							fwrite($handle, space('', 16));	//Fecha y Hora Evento
							fwrite($handle, space('', 80));	//Lugar del Evento
							fwrite($handle, space('', 20));	//Ubicación del Evento
							fwrite($handle, space('', 3));	//Fila Ubicación
							fwrite($handle, space('', 3));	//Asiento Ubicación
							fwrite($handle, ' '); 			//Indicador Agente Retenedor
							fwrite($handle, space('', 18));	//Monto Base Faenamiento
							fwrite($handle, space('', 18));	//Monto Base Margenes
							fwrite($handle, space('', 18));	//Precio Unitario Neto Consumidor Final
							
							$NOM_PRODUCTO = $result_dte[$i]['NOM_PRODUCTO'];
							fwrite($handle, space($NOM_PRODUCTO, 80)); //nombre item
							fwrite($handle, space('', 1000)); //descripcion item
							fwrite($handle, space('', 18));	//Cantidad de Referencia
							fwrite($handle, space('', 4));	//Unidad de Referencia
							fwrite($handle, space('', 18));	//Precio de Referencia
							
							$CANTIDAD	= number_format($result_dte[$i]['CANTIDAD'], 6, '.', '');
							fwrite($handle, space($CANTIDAD, 18));//Cantidad
							fwrite($handle, space('', 18));	//Sub Cantidad Distribuida
							fwrite($handle, space('', 35));	//Codigo Sub Cantidad 
							fwrite($handle, space('', 10));	//Tipo Codigo Subcantidad
							fwrite($handle, space('', 18));	//Sub Cantidad Distribuida
							fwrite($handle, space('', 35));	//Codigo Sub Cantidad 
							fwrite($handle, space('', 10));	//Tipo Codigo Subcantidad
							fwrite($handle, space('', 18));	//Sub Cantidad Distribuida
							fwrite($handle, space('', 35));	//Codigo Sub Cantidad 
							fwrite($handle, space('', 10));	//Tipo Codigo Subcantidad
							fwrite($handle, space('', 18));	//Sub Cantidad Distribuida
							fwrite($handle, space('', 35));	//Codigo Sub Cantidad 
							fwrite($handle, space('', 10));	//Tipo Codigo Subcantidad
							fwrite($handle, space('', 18));	//Sub Cantidad Distribuida
							fwrite($handle, space('', 35));	//Codigo Sub Cantidad 
							fwrite($handle, space('', 10));	//Tipo Codigo Subcantidad
							
							$P_UNITARIO	= number_format($result_dte[$i]['PRECIO'], 6, '.', '');
							fwrite($handle, space($P_UNITARIO, 18)); //Precio Unitario
							fwrite($handle, space('', 10));	//Fecha Elaboración Item
							fwrite($handle, space('', 10));	//Fecha de Vencimiento 
							fwrite($handle, space('', 4));	//Unidad de Medida
							fwrite($handle, space('', 18));	//Precio Otra Moneda
							fwrite($handle, space('', 3));	//Moneda
							fwrite($handle, space('', 10));	//Factor de Conversión 
							fwrite($handle, space('', 18));	//Descuento Otra Moneda
							fwrite($handle, space('', 18));	//Recargo Otra Moneda
							fwrite($handle, space('', 18));	//Valor Item Otra Moneda
							fwrite($handle, space('', 18));	//Precio Otra Moneda
							fwrite($handle, space('', 3));	//Moneda
							fwrite($handle, space('', 10));	//Factor de Conversión 
							fwrite($handle, space('', 18));	//Descuento Otra Moneda
							fwrite($handle, space('', 18));	//Recargo Otra Moneda
							fwrite($handle, space('', 18));	//Valor Item Otra Moneda
							fwrite($handle, space('', 5));	//Porcentaje de Descuento
							fwrite($handle, space('', 18));	//Monto de Descuento
							fwrite($handle, ' ');			//Tipo Descuento
							fwrite($handle, space('', 18));	//Valor Descuento
							fwrite($handle, ' ');			//Tipo Descuento
							fwrite($handle, space('', 18));	//Valor Descuento
							fwrite($handle, ' ');			//Tipo Descuento
							fwrite($handle, space('', 18));	//Valor Descuento
							fwrite($handle, ' ');			//Tipo Descuento
							fwrite($handle, space('', 18));	//Valor Descuento
							fwrite($handle, ' ');			//Tipo Descuento
							fwrite($handle, space('', 18));	//Valor Descuento
							fwrite($handle, space('', 5));	//Porcentaje de Recargo
							fwrite($handle, space('', 18));	//Monto de Recargo
							fwrite($handle, ' ');			//Tipo Recargo
							fwrite($handle, space('', 18));	//Valor Recargo
							fwrite($handle, ' ');			//Tipo Recargo
							fwrite($handle, space('', 18));	//Valor Recargo 
							fwrite($handle, ' ');			//Tipo Recargo
							fwrite($handle, space('', 18));	//Valor Recargo
							fwrite($handle, ' ');			//Tipo Recargo
							fwrite($handle, space('', 18));	//Valor Recargo
							fwrite($handle, ' ');			//Tipo Recargo
							fwrite($handle, space('', 18));	//Valor Recargo 
							fwrite($handle, space('', 6));	//Codigo Impuesto Adicional
							fwrite($handle, space('', 6));	//Codigo Impuesto Adicional
							
							$TOTAL = number_format($TOTAL, 6, '.', '');
							fwrite($handle, space($TOTAL, 18));				//Monto Item
							fwrite($handle, space($MODELO, 30));			//Campo Personalizado Detalle
							fwrite($handle, space($CANTIDAD, 30));			//Campo Personalizado Detalle
							fwrite($handle, space($ORDEN, 30));				//Campo Personalizado Detalle
							fwrite($handle, space('', 30));					//Campo Personalizado Detalle
							fwrite($handle, space('', 30));					//Campo Personalizado Detalle
							fwrite($handle, space('', 30));					//Campo Personalizado Detalle
							fwrite($handle, space('', 5000));				//Descripción Extendida
							
							fwrite($handle, "\r\n");
							$j++;
						}
					}	
					
					fwrite($handle, "\r\n");
					$j++;
				} 
				fwrite($handle, '========== FIN DETALLE');
				fwrite($handle, "\r\n");
				fwrite($handle, '========== SUB TOTALES INFORMATIVO');
				fwrite($handle, "\r\n");
				$j = 0;
				while($j < 20){
					fwrite($handle, space('', 2)); //Numero Sub Total Informativo
					fwrite($handle, space('', 40)); //Glosa
					fwrite($handle, space('', 2)); //Orden
					fwrite($handle, space('', 18)); //Subtotal Neto
					fwrite($handle, space('', 18)); //Subtotal IVA
					fwrite($handle, space('', 18)); //Subtotal Imp. Adidcional
					fwrite($handle, space('', 18)); //Subtotal Exento
					fwrite($handle, space('', 18)); //Valor Subtotal
					fwrite($handle, space('', 2)); //Lineas
					fwrite($handle, "\r\n");
					$j++;
				}
				fwrite($handle, '========== DESCUENTOS Y RECARGOS');
				fwrite($handle, "\r\n");
				// Subtotal
				fwrite($handle, $D1);
				fwrite($handle, space("SUBTOTAL", 45));
				fwrite($handle, $P1);
				fwrite($handle, space($SUBTOTAL, 18));
				fwrite($handle, " ");
				fwrite($handle, "\r\n");
				// Descuento 1
				fwrite($handle, $D2);
				fwrite($handle, space($PORC_DSCTO1."% "."DESCUENTO", 45));
				fwrite($handle, $P2);
				fwrite($handle, space($MONTO_DSCTO1, 18));
				fwrite($handle, " ");
				fwrite($handle, "\r\n");
				// Descuento 2
				fwrite($handle, $D3);
				fwrite($handle, space($PORC_DSCTO2."% "."DESCUENTO ADIC.", 45));
				fwrite($handle, $P3);
				fwrite($handle, space($MONTO_DSCTO2, 18));
				fwrite($handle, " ");
				$j = 0;
				while($j < 18){
					fwrite($handle, "\r\n");
					$j++;
				}
				fwrite($handle, '========== INFORMACION DE REFERENCIA');
				fwrite($handle, "\r\n");
				
				$sql_ref = "SELECT	 NRO_ORDEN_COMPRA
									,CONVERT(VARCHAR(10), FECHA_ORDEN_COMPRA_CLIENTE ,103) FECHA_OC
							FROM 	FACTURA 
							WHERE 	COD_FACTURA = $cod_factura";
				
				$result_ref = $db->build_results($sql_ref);
				$NRO_OC_FACTURA	= $result_ref[0]['NRO_ORDEN_COMPRA'];
				$FECHA_REF_OC	= $result_ref[0]['FECHA_OC'];

				if(($NRO_OC_FACTURA == '') or ($FECHA_REF_OC == ''))
					fwrite($handle, "\r\n");
				else{
					$TIPO_COD_REF		= '801';
					$NRO_OC_FACTURA		= $result_ref[0]['NRO_ORDEN_COMPRA'];	
					$FECHA_REF_OC		= $result_ref[0]['FECHA_OC'];
					$CR					= '1';
					$RAZON_REF_OC		= 'ORDEN DE COMPRA';
					
					$TIPO_COD_REF	= substr($TIPO_COD_REF, 0, 3);
					$NRO_OC_FACTURA	= substr($NRO_OC_FACTURA, 0, 18);
					$FECHA_REF_OC	= substr($FECHA_REF_OC, 0, 10);
					$CR				= substr($CR, 0, 1);
					$RAZON_REF_OC	= substr($RAZON_REF_OC, 0, 100);
					
					fwrite($handle, $TIPO_COD_REF);
					fwrite($handle, ' ');
					fwrite($handle, space($NRO_OC_FACTURA,18));
					fwrite($handle, space($FECHA_REF_OC, 10));
					fwrite($handle, $CR);
					fwrite($handle, space($RAZON_REF_OC, 90));
					fwrite($handle, space('', 8));
					fwrite($handle, space('', 8));
					fwrite($handle, "\r\n");
				}
				$j = 0;
				while($j < 39){
					fwrite($handle, "\r\n");
					$j++;
				}
				fwrite($handle, '========== COMISIONES Y OTROS CARGOS');
				fwrite($handle, "\r\n");
				$j = 0;
				while($j < 20){
					fwrite($handle, "\r\n");
					$j++;
				}
				fwrite($handle, '========== CAMPOS PERSONALIZADOS');
				fwrite($handle, "\r\n");
				fwrite($handle, space('Monto Palabras'));
				fwrite($handle, space($total_en_palabras, 200));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, space($NOM_FORMA_PAGO, 50));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, space($CANCELADA, 50));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, space($GENERA_SALIDA, 50));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, space($OBSERVACIONES, 50));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, space($NRO_NOTA_VENTA, 50));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, space($RUT_RETIRA, 50));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, space($PATENTE, 50));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, space($RETIRA_RECINTO, 50));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, "\r\n");
				fwrite($handle, space('Campo Personalizado General'));
				fwrite($handle, space($REFERENCIA, 50));
				fwrite($handle, "\r\n");
				fwrite($handle, 'XXX FIN DOCUMENTO');
				
				$upload = $this->Envia_DTE($name_archivo, $fname);
				$NRO_FACTURA	= trim($NRO_FACTURA);
				if (!$upload){
					$this->_load_record();
					$this->alert('No se pudo enviar Fatura Electronica Nº '.$NRO_FACTURA.', Por favor contacte a IntegraSystem.');								
				}else{
					if ($PORC_IVA == 0){
						$this->_load_record();
						$this->alert('Gestión Realizada con exíto. Factura Exenta Electronica Nº '.$NRO_FACTURA.'.');
					}else{
						$this->_load_record();
						$this->alert('Gestión Realizada con exíto. Factura Electronica Nº '.$NRO_FACTURA.'.');
					}								
				}
				unlink($fname);
			}else{
				$db->ROLLBACK_TRANSACTION();
				return false;
			}
			$this->unlock_record();
		}
		
		function procesa_event() {		
			if(isset($_POST['b_save_x'])) {
				if (isset($_POST['b_save'])) $this->current_tab_page = $_POST['b_save'];
				if ($this->_save_record()) {
					if ($_POST['wi_hidden']=='save_desde_print')		// Si el save es gatillado desde el boton print, se fuerza que se ejecute nuevamente el print
						print '<script type="text/javascript"> document.getElementById(\'b_print\').click(); </script>';
					elseif ($_POST['wi_hidden']=='save_desde_dte')		// Es es el codigo NUEVO
						print '<script type="text/javascript"> document.getElementById(\'b_print_dte\').click(); </script>';
				}
			}
			else if(isset($_POST['b_print_dte_x']))
				$this->envia_FA_electronica();
			else
				parent::procesa_event();
		}					
}

class print_factura extends print_factura_base {	
	function print_factura($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::print_factura_base($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);
	}			
}
?>