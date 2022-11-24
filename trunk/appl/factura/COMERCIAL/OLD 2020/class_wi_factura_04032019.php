<?php
////////////////////////////////////////
/////////// COMERCIA_BIGGI ///////////////
////////////////////////////////////////
class dw_referencias extends datawindow {
	function dw_referencias() {
		
		$sql = "SELECT COD_REFERENCIA
				      ,CONVERT(VARCHAR, FECHA_REFERENCIA, 103) FECHA_REFERENCIA
				      ,DOC_REFERENCIA
				      ,COD_TIPO_REFERENCIA
				      ,COD_FACTURA
				FROM REFERENCIA
				WHERE COD_FACTURA = {KEY1}";
		
		parent::datawindow($sql, 'REFERENCIAS', true, true);
		
		// controls
		$this->add_control(new edit_date('FECHA_REFERENCIA'));
		$this->add_control($control = new edit_text('DOC_REFERENCIA', 20, 100));
		$control->set_onChange("valida_referencias(this);");
		
		$sql = "select COD_TIPO_REFERENCIA
						,NOM_TIPO_REFERENCIA
				from TIPO_REFERENCIA
				order by NOM_TIPO_REFERENCIA";
		$this->add_control($control = new drop_down_dw('COD_TIPO_REFERENCIA', $sql, 103));
		$control->set_onChange("valida_referencias(this);");

		// mandatory
		$this->set_mandatory('DOC_REFERENCIA', 'Doc. Referencia');
		$this->set_mandatory('COD_TIPO_REFERENCIA', 'Tipo Referencia');
	}
	function fill_template(&$temp) {
		parent::fill_template($temp);
		
		if($this->b_add_line_visible){
			if ($this->entrable){
				$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_ref(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer">';
			}else 
				$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line_d.jpg">';
				
			$temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);	
		}
	}
	
	 
	function update($db){
		$sp = 'spu_referencia';
		
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
	
			$COD_REFERENCIA			= $this->get_item($i, 'COD_REFERENCIA');
			$FECHA_REFERENCIA		= $this->str2date($this->get_item($i, 'FECHA_REFERENCIA'));
			$DOC_REFERENCIA			= $this->get_item($i, 'DOC_REFERENCIA');
			$COD_TIPO_REFERENCIA	= $this->get_item($i, 'COD_TIPO_REFERENCIA');
			$COD_FACTURA			= $this->get_item($i, 'COD_FACTURA');
			
			$COD_REFERENCIA			= ($COD_REFERENCIA =='') ? "null" : $COD_REFERENCIA;							
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			else if ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';		
						
			$param = "'$operacion'
					,$COD_REFERENCIA
					,$FECHA_REFERENCIA
					,'$DOC_REFERENCIA'
					,$COD_TIPO_REFERENCIA
					,$COD_FACTURA";
			
			if(!$db->EXECUTE_SP($sp, $param))
				return false;
			else{
				if($statuts == K_ROW_NEW_MODIFIED) {
					$COD_REFERENCIA = $db->GET_IDENTITY();
					$this->set_item($i, 'COD_REFERENCIA', $COD_REFERENCIA);		
				}
			}
		}
		
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_REFERENCIA = $this->get_item($i, 'COD_REFERENCIA', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_REFERENCIA"))
				return false;
		}	
		return true;
	}
}


class wi_factura extends wi_factura_base {
	const K_BODEGA_TERMINADO = 2;
	const K_TIPO_FA_OC_COMERCIAL = 3;
	
	const K_AUTORIZA_ENVIAR_DTE = '992070';
	const K_AUTORIZA_IMPRIMIR_DTE = '992075';
	const K_AUTORIZA_CONSULTAR_DTE = '992080';
	const K_AUTORIZA_XML_DTE = '992085';
	const K_AUTORIZA_REENVIAR_DTE='992090';
	
	const K_PARAM_RUTEMISOR = 20;
	const K_PARAM_RZNSOC = 6;
	const K_PARAM_GIROEMIS = 21;
	const K_PARAM_DIRORIGEN = 10;
	const K_PARAM_CMNAORIGEN = 70;
	const K_TIPO_DOC = 33;//FA
	const K_ACTV_ECON = 292510;// FORJA, PRENSADO, ESTAMPADO Y LAMINADO DE METAL; INCLUYE PULVIMETALURGIA
	const K_PARAM_HASH = 200;
	const K_ESTADO_SII_EMITIDA = 1;
	
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
                                    CASE F.GENERA_SALIDA
                                    WHEN 'S' THEN 'FACTURA PERMITE DESPACHAR EQUIPOS'
                                    ELSE 'FACTURA NO PERMITE DESPACHAR EQUIPOS'
                                    END MSJ_GS,
                                    CASE F.GENERA_SALIDA
                                    WHEN 'S' THEN 'B-VERDE'
                                    ELSE 'B-ROJO'
                                    END CLASE_SALIDA
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
		
	function habilita_boton(&$temp, $boton, $habilita) {
		parent::habilita_boton($temp, $boton, $habilita);
		
		if($boton == 'enviar_dte'){
			if($habilita){
				$control = '<input name="b_enviar_dte" id="b_enviar_dte" src="../../images_appl/b_enviar_dte.jpg" type="image" '.
							 'onMouseDown="MM_swapImage(\'b_enviar_dte\',\'\',\'../../images_appl/b_enviar_dte_click.jpg\',1)" '.
							 'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
							 'onMouseOver="MM_swapImage(\'b_enviar_dte\',\'\',\'../../images_appl/b_enviar_dte_over.jpg\',1)" 
							 onClick="var vl_tab = document.getElementById(\'wi_current_tab_page\'); if (TabbedPanels1 && vl_tab) vl_tab.value =TabbedPanels1.getCurrentTabIndex();
									 if (document.getElementById(\'b_save\')) {
										 if (validate_save()) {
										 		document.getElementById(\'wi_hidden\').value = \'save_enviar_dte\';
										 		document.getElementById(\'b_save\').click();
										 		return true;
										 	}
										 	else
										 		return false;
									 }
								 	 else
								 	 		return true;"/>';
			}else{
				$control = '<img src="../../images_appl/b_enviar_dte_d.jpg">';
			}
			
			$temp->setVar("WSWAP_ENVIA_DTE", $control);
		}
		if($boton == 'consultar_dte'){
			if($habilita){
				$control = '<input name="b_consultar_dte" id="b_consultar_dte" src="../../images_appl/b_consultar_dte.jpg" type="image" '.
							 'onMouseDown="MM_swapImage(\'b_consultar_dte\',\'\',\'../../images_appl/b_consultar_dte_click.jpg\',1)" '.
							 'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
							 'onMouseOver="MM_swapImage(\'b_consultar_dte\',\'\',\'../../images_appl/b_consultar_dte_over.jpg\',1)"
							 onClick="return true;"/>';
			}else{
				$control = '<img src="../../images_appl/b_consultar_dte_d.jpg">';
			}
			
			$temp->setVar("WSWAP_CONSULTAR_DTE", $control);
		}
		if($boton == 'imprimir_dte'){
			if($habilita){
				$ruta_over = "'../../images_appl/b_reimprime_dte_over.jpg'";
				$ruta_out = "'../../images_appl/b_reimprime_dte.jpg'";
				$ruta_click = "'../../images_appl/b_reimprime_dte_click.jpg'";
				$control =  '<input name="b_imprimir_dte" id="b_imprimir_dte" type="button" onmouseover="entrada(this, '.$ruta_over.')" onmouseout="salida(this, '.$ruta_out.')" onmousedown="down(this, '.$ruta_click.')"'.
				   			'style="cursor:pointer;height:68px;width:66px;border: 0;background-image:url(../../images_appl/b_reimprime_dte.jpg);background-repeat:no-repeat;background-position:center;border-radius: 15px;"'.
				   			'onClick="return dlg_print_dte();" />';
			
			}else{
				$control = '<img src="../../images_appl/b_reimprime_dte_d.jpg">';
			}
			
			$temp->setVar("WSWAP_IMPRIMIR_DTE", $control);
		}
		if($boton == 'reenviar_dte'){
			if($habilita){
				$control = '<input name="b_reenviar_dte" id="b_reenviar_dte" src="../../images_appl/b_reenviar.jpg" type="image" '.
							 'onMouseDown="MM_swapImage(\'b_reenviar_dte\',\'\',\'../../images_appl/b_reenviar_click.jpg\',1)" '.
							 'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
							 'onMouseOver="MM_swapImage(\'b_reenviar_dte\',\'\',\'../../images_appl/b_reenviar_over.jpg\',1)"
							 onClick="return true;"/>';
			}else{
				$control = '<img src="../../images_appl/b_reenviar_d.jpg">';
			}
			
			$temp->setVar("WSWAP_REENVIAR_DTE", $control);
		}
		if($boton == 'xml_dte'){
			if($habilita){
				$control = '<input name="b_xml_dte" id="b_xml_dte" src="../../images_appl/b_xml_dte.jpg" type="image" '.
							 'onMouseDown="MM_swapImage(\'b_xml_dte\',\'\',\'../../images_appl/b_xml_dte_click.jpg\',1)" '.
							 'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
							 'onMouseOver="MM_swapImage(\'b_xml_dte\',\'\',\'../../images_appl/b_xml_dte_over.jpg\',1)"
							 onClick="return true;"/>';
			}else{
				$control = '<img src="../../images_appl/b_xml_dte_d.jpg">';
			}
			
			$temp->setVar("WSWAP_XML_DTE", $control);
		}
	}
		
	function navegacion(&$temp){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		parent::navegacion($temp);
		
		$cod_factura = $this->get_key();
		if($cod_factura <> ""){
			$Sql= "SELECT F.COD_ESTADO_DOC_SII
							,F.TRACK_ID_DTE
							,F.RESP_EMITIR_DTE
				    FROM FACTURA F
					WHERE F.COD_FACTURA = $cod_factura";
			$result = $db->build_results($Sql);
			$COD_ESTADO_DOC_SII = $result[0]['COD_ESTADO_DOC_SII'];
			$TRACK_ID_DTE		= $result[0]['TRACK_ID_DTE'];
			$RESP_EMITIR_DTE	= $result[0]['RESP_EMITIR_DTE'];
		}
		
		if($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_EMITIDA){
			if($RESP_EMITIR_DTE == '' && $TRACK_ID_DTE == ''){ //ingresa por primera vez
				if($this->tiene_privilegio_opcion(self::K_AUTORIZA_ENVIAR_DTE)== 'S')
					$this->habilita_boton($temp, 'enviar_dte', true);
				else
					$this->habilita_boton($temp, 'enviar_dte', false);
				
				
			}else if($RESP_EMITIR_DTE <> '' && $TRACK_ID_DTE == ''){ //Reimprime
				$this->habilita_boton($temp, 'enviar_dte', false);
			}
			$this->habilita_boton($temp, 'imprimir_dte', false);
			$this->habilita_boton($temp, 'consultar_dte', false);
			$this->habilita_boton($temp, 'xml_dte', false);
			$this->habilita_boton($temp, 'reenviar_dte', false);
		}else if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_ENVIADA){
			if($TRACK_ID_DTE <> ''){
				$this->habilita_boton($temp, 'enviar_dte', false);
			
				if($this->tiene_privilegio_opcion(self::K_AUTORIZA_CONSULTAR_DTE)== 'S')
					$this->habilita_boton($temp, 'consultar_dte', true);
				else
					$this->habilita_boton($temp, 'consultar_dte', false);
					
				if($this->tiene_privilegio_opcion(self::K_AUTORIZA_XML_DTE)== 'S')
					$this->habilita_boton($temp, 'xml_dte', true);
				else
					$this->habilita_boton($temp, 'xml_dte', false);
					
				if($this->tiene_privilegio_opcion(self::K_AUTORIZA_REENVIAR_DTE)== 'S')
					$this->habilita_boton($temp, 'reenviar_dte', true);
				else
					$this->habilita_boton($temp, 'reenviar_dte', false);	
			}
			
			if($this->tiene_privilegio_opcion(self::K_AUTORIZA_IMPRIMIR_DTE)== 'S')
				$this->habilita_boton($temp, 'imprimir_dte', true);
			else
				$this->habilita_boton($temp, 'imprimir_dte', false);
		}else{
			$this->habilita_boton($temp, 'enviar_dte', false);
			$this->habilita_boton($temp, 'imprimir_dte', false);
			$this->habilita_boton($temp, 'consultar_dte', false);
			$this->habilita_boton($temp, 'xml_dte', false);
			$this->habilita_boton($temp, 'reenviar_dte', false);
		}
	}
		
	function procesa_event() {		
		if(isset($_POST['b_save_x'])) {
			if (isset($_POST['b_save'])) $this->current_tab_page = $_POST['b_save'];
			if ($this->_save_record()) {
				if ($_POST['wi_hidden']=='save_desde_print')		// Si el save es gatillado desde el boton print, se fuerza que se ejecute nuevamente el print
					print '<script type="text/javascript"> document.getElementById(\'b_print\').click(); </script>';
				elseif ($_POST['wi_hidden']=='save_desde_dte')		// Es es el codigo NUEVO
					print '<script type="text/javascript"> document.getElementById(\'b_print_dte\').click(); </script>';
				elseif ($_POST['wi_hidden']=='save_enviar_dte')		// Es es el save enviar_dte
					print '<script type="text/javascript"> document.getElementById(\'b_enviar_dte\').click(); </script>';	
			}
		}
		/*else if(isset($_POST['b_print_dte_x']))
			$this->envia_FA_electronica();*/
		else if(isset($_POST['b_enviar_dte_x'])){
			$this->enviar_dte();
		}else if(isset($_POST['b_consultar_dte_x'])){
			$this->actualizar_estado_dte();
		}else if(isset($_POST['b_imprimir_dte_x'])){
			$this->imprimir_dte($_POST['wi_hidden']);
		}else if(isset($_POST['b_reenviar_dte_x'])){
			$this->reenviar_dte();
		}else if(isset($_POST['b_xml_dte_x'])){
			$this->xml_dte();
		}else
			parent::procesa_event();
	}
	
	function enviar_dte($reenviar = false){
		if (!$this->lock_record())
			return false;
	
		$cod_factura = $this->get_key();
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$PORC_IVA = $this->dws['dw_factura']->get_item(0, 'PORC_IVA');
		
		$sql = "SELECT NRO_FACTURA
				FROM FACTURA
				WHERE COD_FACTURA = $cod_factura";
		$result = $db->build_results($sql);
		
		if($result[0]['NRO_FACTURA'] <> '' && $reenviar == false)
			return false;
		
		if($reenviar){
			$dte = new dte();
				
			//Se le pasa como variable hash de la clase obtenida en parametros en la BD
			$SqlHash = "SELECT dbo.f_get_parametro(".self::K_PARAM_HASH.") K_HASH";  
			$Datos_Hash = $db->build_results($SqlHash);
			$dte->hash = $Datos_Hash[0]['K_HASH'];

			if ($PORC_IVA==0){
				$cod_tipo_dte = 34;
			}else{
				$cod_tipo_dte = 33;
			}
				
			$sql_folio = "select  NRO_FACTURA 
								,REPLACE(REPLACE(dbo.f_get_parametro(20),'.',''),'-5','') as RUTEMISOR
						 from FACTURA 
						 where COD_FACTURA=$cod_factura";
			
			$result_folio = $db->build_results($sql_folio);
			$nro_factura = $result_folio[0]['NRO_FACTURA'];
			$tipo_doc = $cod_tipo_dte;
			$rutemisor = $result_folio[0]['RUTEMISOR'];
			//resuelve la cadena entrega
			$objEnJson_genera = $dte->eliminar_dte($nro_factura,$tipo_doc,$rutemisor);
			
			if(trim($objEnJson_genera) <> 'true'){
				//Llamamos al envio consultar estado documento.
				$response = $dte->actualizar_estado($tipo_doc,$nro_factura,$rutemisor);
				$actualizar_estado = $dte->respuesta_actualizar_estado($response);
				$revision_estado	= substr($actualizar_estado[6], 0, 3);
				
				if($revision_estado == 'EPR')
					$estado_libre_dte = 'Aceptada';
				else if($revision_estado == 'RPR')
					$estado_libre_dte = 'Aceptado con Reparos';
				else if($revision_estado == 'RLV')
					$estado_libre_dte = 'Aceptada con Reparos Leves';
			
				$this->_load_record();
				print "<script>alert('No se puede reenviar el DTE al SII pues su estado actual es: ".$estado_libre_dte."');</script>";
				return;
			}
		}else{
			if($PORC_IVA <> 0){
				$sql_folio = "SELECT dbo.f_get_nro_dte('FA') NRO_FACTURA";
				$cod_tipo_dte = 33;
			}
			else{
				$sql_folio = "SELECT dbo.f_get_nro_dte('FAEX') NRO_FACTURA";
				$cod_tipo_dte = 34;
			}
			
			$result_folio = $db->build_results($sql_folio);
			$nro_factura = $result_folio[0]['NRO_FACTURA'];
		}

		$REFERENCIA_HEM	= $this->dws['dw_factura']->get_item(0, 'REFERENCIA_HEM');
		$REFERENCIA_HES	= $this->dws['dw_factura']->get_item(0, 'REFERENCIA_HES');
		$count_hem = 0;
		$count_hes = 0;
		$count_hep = 0;
		$count_802_ccu = 0;
		$count_migo = 0;
		$tipo = "";
		
		for($i=0 ; $i < $this->dws['dw_referencias']->row_count() ; $i++){
			$COD_TIPO_REFERENCIA = $this->dws['dw_referencias']->get_item($i, 'COD_TIPO_REFERENCIA');
			if($COD_TIPO_REFERENCIA == 1)//HEM
				$count_hem++;
				
			if($COD_TIPO_REFERENCIA == 2)//HES
				$count_hes++;
				
			if($COD_TIPO_REFERENCIA == 5)//HEP
				$count_hep++;

			if($COD_TIPO_REFERENCIA == 6)//802 CCU
				$count_802_ccu++;
				
			if($COD_TIPO_REFERENCIA == 7)//MIGO
				$count_migo++;	
		}
		/*
		 * DW de Referencia y de solo contacto
		 */
		$sqlcto ="SELECT DOC_REFERENCIA
						FROM REFERENCIA
						WHERE COD_FACTURA = $cod_factura
						AND COD_TIPO_REFERENCIA in(3,4)";
		$cto = $db->build_results($sqlcto);

		$DOC_REFERENCIA	= $cto[0]['DOC_REFERENCIA'];
		$MAIL_CONTACTO	= $cto[1]['DOC_REFERENCIA'];

		$sql = "SELECT (CAST(F.RUT AS NVARCHAR(8)))+'-'+(CAST (F.DIG_VERIF AS NVARCHAR(1))) as RUT_COMPLETO
						,F.NOM_EMPRESA
              			,F.GIRO
              			,F.DIRECCION
              			,F.NOM_COMUNA
              			,F.PORC_DSCTO1
              			,F.MONTO_DSCTO1
              			,F.MONTO_DSCTO2
              			,F.REFERENCIA TermPagoGlosa
              			,801 TpoDocRef
              			,NRO_ORDEN_COMPRA FolioRef
              			,replace (CONVERT(varchar,FECHA_ORDEN_COMPRA_CLIENTE,102),'.','-')FchRef
              			,'ORDEN DE COMPRA' RazonRef
				FROM FACTURA F
				WHERE F.COD_FACTURA =$cod_factura";
		$contenido = $db->build_results($sql);
		
		$SqlDetalles ="SELECT ROW_NUMBER()OVER(ORDER BY ITF.ORDEN) AS NroLinDet
							,('INT1')AS TpoCodigo
							,ITF.COD_PRODUCTO AS VlrCodigo
							,ITF.NOM_PRODUCTO AS NmbItem 
							,ITF.CANTIDAD
							,ITF.PRECIO
							,(ITF.CANTIDAD * ITF.PRECIO) AS MONTO_TOTAL
						FROM ITEM_FACTURA ITF WHERE ITF.COD_FACTURA = $cod_factura
						ORDER BY ITF.ORDEN";
		$Detalles = $db->build_results($SqlDetalles);

		for($i = 0; $i < count($Detalles); $i++) {
			$NmbItem	= substr($Detalles[$i]['NmbItem'], 0, 80);
			$VlrCodigo	= substr($Detalles[$i]['VlrCodigo'], 0, 35);
			$CANTIDAD	= substr($Detalles[$i]['CANTIDAD'], 0, 18);
			$PRECIO		= substr($Detalles[$i]['PRECIO'], 0, 18);
			
			if($cod_tipo_dte == 34){
				$ad['Detalle'][$i]["IndExe"]= 1;
			}
			
			$ad['Detalle'][$i]["NmbItem"]= utf8_encode(trim($NmbItem));
			$ad['Detalle'][$i]["CdgItem"]= $VlrCodigo;
			$ad['Detalle'][$i]["QtyItem"]= $CANTIDAD;
			$ad['Detalle'][$i]["PrcItem"]= $PRECIO;
		}
		
		$RutRecep		= substr($contenido[0]['RUT_COMPLETO'], 0, 10); 
		$RznSocRecep	= substr($contenido[0]['NOM_EMPRESA'], 0, 100);
		$GiroRecep		= substr($contenido[0]['GIRO'], 0, 40);
		$DirRecep		= substr($contenido[0]['DIRECCION'], 0, 70);
		$ComRecep		= substr($contenido[0]['NOM_COMUNA'], 0, 20);
		$DireccionC		= substr(str_replace("#","N",$DirRecep), 0, 70);
		$GiroRecep40	= substr($GiroRecep, 0, 40);
		$DescuentoMonto1= substr($contenido[0]['MONTO_DSCTO1'], 0, 18);
		$DescuentoMonto2= substr($contenido[0]['MONTO_DSCTO2'], 0, 18);
		$TpoDocRef		= substr($contenido[0]['TpoDocRef'], 0, 3);
		$FolioRef		= substr(trim($contenido[0]['FolioRef']), 0, 18);
		$FchRef			= substr($contenido[0]['FchRef'], 0, 10);
		$RazonRef		= substr($contenido[0]['RazonRef'], 0, 90);
		  
		if($ComRecep == ''){
			$this->_load_record();
			print " <script>alert('Error al Emitir Dte, la empresa de la factura no tiene asignada Comuna.');</script>";
			return;
		}
		
		$SqlEmisor ="SELECT	REPLACE(dbo.f_get_parametro(".self::K_PARAM_RUTEMISOR."),'.','') RUTEMISOR
							,dbo.f_get_parametro(".self::K_PARAM_RZNSOC.") RZNSOC
							,dbo.f_get_parametro(".self::K_PARAM_GIROEMIS.") GIROEMIS
							,dbo.f_get_parametro(".self::K_PARAM_DIRORIGEN.") DIRORIGEN
							,dbo.f_get_parametro(".self::K_PARAM_CMNAORIGEN.") CMNAORIGEN";  
		$Datos_Emisor = $db->build_results($SqlEmisor);
		
		$rutemisor	= $Datos_Emisor[0]['RUTEMISOR']; 
		$rznsoc		= $Datos_Emisor[0]['RZNSOC']; 
		$giroemis	= $Datos_Emisor[0]['GIROEMIS']; 
		$dirorigen	= $Datos_Emisor[0]['DIRORIGEN']; 
		$cmnaorigen	= $Datos_Emisor[0]['CMNAORIGEN']; 
		
		$a['Encabezado']['IdDoc']['TipoDTE']		= $cod_tipo_dte;
		$a['Encabezado']['IdDoc']['Folio']			= substr($nro_factura, 0, 10);
		
		if($RutRecep == '89257000-0' || $RutRecep == '80112900-5' || $RutRecep == '77773650-7' || $RutRecep == '91462001-5'){
			$a['Encabezado']['IdDoc']['FmaPago']	= 1;
		}else{
			$COD_FORMA_PAGO = $this->dws['dw_factura']->get_item(0, 'COD_FORMA_PAGO');
			
			$Sql_forma_pago_sii = "SELECT FORMA_PAGO_SII
								   FROM FORMA_PAGO
								   WHERE COD_FORMA_PAGO = $COD_FORMA_PAGO";
			$result_pago_sii = $db->build_results($Sql_forma_pago_sii);
			
			$a['Encabezado']['IdDoc']['FmaPago'] = $result_pago_sii[0]['FORMA_PAGO_SII'];
		}
			
		$a['Encabezado']['Emisor']['RUTEmisor']		= substr($rutemisor, 0, 10);
		$a['Encabezado']['Emisor']['RznSoc']		= utf8_encode(substr($rznsoc, 0, 100));
		$a['Encabezado']['Emisor']['GiroEmis']		= utf8_encode(substr($giroemis, 0, 80));
		$a['Encabezado']['Emisor']['Acteco']		= self::K_ACTV_ECON;
		$a['Encabezado']['Emisor']['DirOrigen']		= utf8_encode(substr($dirorigen, 0, 60));
		$a['Encabezado']['Emisor']['CmnaOrigen']	= utf8_encode(substr($cmnaorigen, 0, 20));//'San Miguel';
		$a['Encabezado']['Receptor']['RUTRecep']	= $RutRecep;
		$a['Encabezado']['Receptor']['RznSocRecep']	= utf8_encode($RznSocRecep);
		$a['Encabezado']['Receptor']['GiroRecep']	= utf8_encode($GiroRecep40);
		
		if($DOC_REFERENCIA <> ''){
			$a['Encabezado']['Receptor']['Contacto']= utf8_encode(substr($DOC_REFERENCIA, 0, 80)); //contacto solo si esta en referencias
		}
		if($MAIL_CONTACTO <> ''){
			$a['Encabezado']['Receptor']['CorreoRecep']= utf8_encode(substr($MAIL_CONTACTO, 0, 80)); //contacto solo si esta en el mail contacto
		}
		
		$a['Encabezado']['Receptor']['DirRecep']	= utf8_encode($DireccionC);
		$a['Encabezado']['Receptor']['CmnaRecep']	= utf8_encode($ComRecep);
		
		$tiene_Folio = 'N';
		$tiene_descuento = 'N';
		$i = 0;
		//////////////////REFERENCIAS///////////////////
		if ($FolioRef <> ''){
			$c['Referencia'][$i]['NroLinRef']	= $i+1;
			$c['Referencia'][$i]['TpoDocRef']	= $TpoDocRef;
			$c['Referencia'][$i]['FolioRef']	= $FolioRef;
			$c['Referencia'][$i]['FchRef']		= $FchRef;
			$c['Referencia'][$i]['RazonRef']	= $RazonRef;
			$tiene_Folio = 'S';
			$i++;
		}
		
		$sql_guia_despacho = "SELECT REPLACE(dbo.f_fa_nros_guia_despacho(COD_FACTURA), ' ', '') NRO_GUIAS_DESPACHO
							  FROM FACTURA
							  WHERE COD_FACTURA = $cod_factura";
		$result_gd = $db->build_results($sql_guia_despacho);
		
		if(trim($result_gd[0]['NRO_GUIAS_DESPACHO']) <> "")
			$arr_cod_gd = explode('-',$result_gd[0]['NRO_GUIAS_DESPACHO']);
		
		for($k=0 ; $k < count($arr_cod_gd) ; $k++){
			$FolioRef = $arr_cod_gd[$k];
			
			$sql = "SELECT replace (CONVERT(varchar,FECHA_GUIA_DESPACHO,102),'.','-') FECHA_GUIA_DESPACHO
					FROM GUIA_DESPACHO
					WHERE NRO_GUIA_DESPACHO = $FolioRef";
			$result = $db->build_results($sql);
			$FchRef = $result[0]['FECHA_GUIA_DESPACHO'];

			$c['Referencia'][$i]['NroLinRef']	= $i+1;
			$c['Referencia'][$i]['TpoDocRef']	= "52";
			$c['Referencia'][$i]['FolioRef']	= substr($FolioRef, 0, 18);
			$c['Referencia'][$i]['FchRef']		= substr($FchRef, 0, 10);
			$c['Referencia'][$i]['RazonRef']	= "GUIA DE DESPACHO ELECTRONICA";
			$i++;
			
			$tiene_Folio = 'S';
		}
		
		if($count_hem > 0){
			$sql = "SELECT REPLACE(CONVERT(varchar,FECHA_REFERENCIA,102),'.','-') FECHA_REFERENCIA
						  ,DOC_REFERENCIA
					FROM REFERENCIA
					WHERE COD_FACTURA = $cod_factura
					AND COD_TIPO_REFERENCIA = 1";
			$result = $db->build_results($sql);
			
			$FolioRef	= $result[0]['DOC_REFERENCIA'];
			$FchRef		= $result[0]['FECHA_REFERENCIA'];
			
			$c['Referencia'][$i]['NroLinRef']	= $i+1;
			$c['Referencia'][$i]['TpoDocRef']	= "HEM";
			$c['Referencia'][$i]['FolioRef']	= substr($FolioRef, 0, 18);
			$c['Referencia'][$i]['FchRef']		= substr($FchRef, 0, 10);
			$c['Referencia'][$i]['RazonRef']	= "HEM";
			$i++;
			
			$tiene_Folio = 'S';
		}
		
		if($count_hes > 0){
			$sql = "SELECT REPLACE(CONVERT(varchar,FECHA_REFERENCIA,102),'.','-') FECHA_REFERENCIA
						  ,DOC_REFERENCIA
					FROM REFERENCIA
					WHERE COD_FACTURA = $cod_factura
					AND COD_TIPO_REFERENCIA = 2";
			$result = $db->build_results($sql);
			
			$FolioRef	= $result[0]['DOC_REFERENCIA'];
			$FchRef		= $result[0]['FECHA_REFERENCIA'];
			
			$c['Referencia'][$i]['NroLinRef']	= $i+1;
			$c['Referencia'][$i]['TpoDocRef']	= "HES";
			$c['Referencia'][$i]['FolioRef']	= substr($FolioRef, 0, 18);
			$c['Referencia'][$i]['FchRef']		= substr($FchRef, 0, 10);
			$c['Referencia'][$i]['RazonRef']	= "HES";
			$i++;
			
			$tiene_Folio = 'S';
		}
		
		if($count_hep > 0){
			$sql = "SELECT REPLACE(CONVERT(varchar,FECHA_REFERENCIA,102),'.','-') FECHA_REFERENCIA
						  ,DOC_REFERENCIA
					FROM REFERENCIA
					WHERE COD_FACTURA = $cod_factura
					AND COD_TIPO_REFERENCIA = 5";
			$result = $db->build_results($sql);
			
			$FolioRef	= $result[0]['DOC_REFERENCIA'];
			$FchRef		= $result[0]['FECHA_REFERENCIA'];
			
			$c['Referencia'][$i]['NroLinRef']	= $i+1;
			$c['Referencia'][$i]['TpoDocRef']	= "HEP";
			$c['Referencia'][$i]['FolioRef']	= substr($FolioRef, 0, 18);
			$c['Referencia'][$i]['FchRef']		= substr($FchRef, 0, 10);
			$c['Referencia'][$i]['RazonRef']	= "HEP";
			$i++;
			
			$tiene_Folio = 'S';
		}

		if($count_802_ccu > 0){
			$sql = "SELECT REPLACE(CONVERT(varchar,FECHA_REFERENCIA,102),'.','-') FECHA_REFERENCIA
						  ,DOC_REFERENCIA
					FROM REFERENCIA
					WHERE COD_FACTURA = $cod_factura
					AND COD_TIPO_REFERENCIA = 6";
			$result = $db->build_results($sql);
			
			$FolioRef	= $result[0]['DOC_REFERENCIA'];
			$FchRef		= $result[0]['FECHA_REFERENCIA'];
			
			$c['Referencia'][$i]['NroLinRef']	= $i+1;
			$c['Referencia'][$i]['TpoDocRef']	= "802";
			$c['Referencia'][$i]['FolioRef']	= substr($FolioRef, 0, 18);
			$c['Referencia'][$i]['FchRef']		= substr($FchRef, 0, 10);
			$c['Referencia'][$i]['RazonRef']	= "802";
			$i++;
			
			$tiene_Folio = 'S';
		}
		
		if($count_migo > 0){
			$sql = "SELECT REPLACE(CONVERT(varchar,FECHA_REFERENCIA,102),'.','-') FECHA_REFERENCIA
						  ,DOC_REFERENCIA
					FROM REFERENCIA
					WHERE COD_FACTURA = $cod_factura
					AND COD_TIPO_REFERENCIA = 7";
			$result = $db->build_results($sql);
			
			$FolioRef	= $result[0]['DOC_REFERENCIA'];
			$FchRef		= $result[0]['FECHA_REFERENCIA'];
			
			$c['Referencia'][$i]['NroLinRef']	= $i+1;
			$c['Referencia'][$i]['TpoDocRef']	= "802";
			$c['Referencia'][$i]['FolioRef']	= substr($FolioRef, 0, 18);
			$c['Referencia'][$i]['FchRef']		= substr($FchRef, 0, 10);
			$c['Referencia'][$i]['RazonRef']	= "MIGO";
			$i++;
			
			$tiene_Folio = 'S';
		}
		///////////////////////////////////////////////////
		
		$tiene_descuento = 'N';
		if($DescuentoMonto1 <> 0){
			$b['DscRcgGlobal'][0]['NroLinDR']	= 1; //D(descuento) o R(recargo)
			$b['DscRcgGlobal'][0]['TpoMov']	= 'D'; //D(descuento) o R(recargo)
			$b['DscRcgGlobal'][0]['TpoValor']= '$';//Indica si es Porcentaje o Monto “%” o “$”
			$b['DscRcgGlobal'][0]['ValorDR']	= $DescuentoMonto1;//Valor del descuento o recargo en 16 enteros y 2 decimales
			if($cod_tipo_dte == 34){
				$b['DscRcgGlobal'][0]['IndExeDR']	= 1;//1: No afecto o exento de IVA 2: No facturable
			}
			
			$tiene_descuento = 'S';
			//junta los arreglos en uno.
		}
		
		if($DescuentoMonto2 <> 0){
			$b['DscRcgGlobal'][1]['NroLinDR']	= 2; //D(descuento) o R(recargo)
			$b['DscRcgGlobal'][1]['TpoMov']	= 'D'; //D(descuento) o R(recargo)
			$b['DscRcgGlobal'][1]['TpoValor']= '$';//Indica si es Porcentaje o Monto “%” o “$”
			$b['DscRcgGlobal'][1]['ValorDR']	= $DescuentoMonto2;//Valor del descuento o recargo en 16 enteros y 2 decimales
			if($cod_tipo_dte == 34){
				$b['DscRcgGlobal'][1]['IndExeDR']	= 1;//1: No afecto o exento de IVA 2: No facturable
			}
			$tiene_descuento = 'S';
			//junta los arreglos en uno.
		}
		
		if($tiene_Folio == 'S' && $tiene_descuento == 'N'){
			 $resultado = array_merge($a,$ad,$c);
		}else if ($tiene_Folio == 'N' && $tiene_descuento == 'S'){
			//junta los arreglos en uno.
			$resultado = array_merge($a,$ad,$b);
		}else if ($tiene_Folio == 'S' && $tiene_descuento == 'S'){
			$resultado = array_merge($a,$ad,$b,$c);
		}else{
			$resultado = array_merge($a,$ad);
		}
		
		//se agrega el json_para codificacion requerida por libre_dte.
		$objEnJson = json_encode($resultado);
		
		//LLamo a la nueva clase dte.
		$dte = new dte();
		
		//Se le pasa como variable hash de la clase obtenida en parametros en la BD
		$SqlHash = "SELECT dbo.f_get_parametro(".self::K_PARAM_HASH.") K_HASH";  
		$Datos_Hash = $db->build_results($SqlHash);
		$dte->hash = $Datos_Hash[0]['K_HASH'];
		
		//envio json al la funcion de la clase dte.
		$response = $dte->post_emitir_dte($objEnJson);
		
		//Guarda el response de la función emitir_dte.
		$db->BEGIN_TRANSACTION();
		$sp = 'spu_factura';
		$param = "'SAVE_EMITIR_DTE' 
								,$cod_factura 	--@ve_cod_factura
                                ,NULL 			--@ve_cod_usuario_impresion
                                ,NULL 			--@ve_cod_usuario
                                ,NULL 			--@ve_nro_factura
                                ,NULL 			--@ve_fecha_factura
                                ,NULL 			--@ve_cod_estado_doc_sii
                                ,NULL 			--@ve_cod_empresa
                                ,NULL 			--@ve_cod_sucursal_factura
                                ,NULL 			--@ve_cod_persona
                                ,NULL 			--@ve_referencia
                                ,NULL 			--@ve_nro_orden_compra
                                ,NULL 			--@ve_fecha_orden_compra_cliente
                                ,NULL 			--@ve_obs
                                ,NULL 			--@ve_retirado_por
                                ,NULL 			--@ve_rut_retirado_por
                                ,NULL 			--@ve_dig_verif_retirado_por
                                ,NULL 			--@ve_guia_transporte
                                ,NULL 			--@ve_patente
                                ,NULL 			--@ve_cod_bodega
                                ,NULL 			--@ve_cod_tipo_factura
                                ,NULL 			--@ve_cod_doc
                                ,NULL 			--@ve_motivo_anula
                                ,NULL 			--@ve_cod_usuario_anula
                                ,NULL 			--@ve_cod_usuario_vendedor1
                                ,NULL 			--@ve_porc_vendedor1
                                ,NULL 			--@ve_cod_usuario_vendedor2
                                ,NULL 			--@ve_porc_vendedor2
                                ,NULL 			--@ve_cod_forma_pago
                                ,NULL 			--@ve_cod_origen_venta
                                ,NULL 			--@ve_subtotal
                                ,NULL 			--@ve_porc_dscto1
                                ,NULL 			--@ve_ingreso_usuario_dscto1
                                ,NULL 			--@ve_monto_dscto1
                                ,NULL 			--@ve_porc_dscto2
                                ,NULL 			--@ve_ingreso_usuario_dscto2
                                ,NULL 			--@ve_monto_dscto2
                                ,NULL 			--@ve_total_neto
                                ,NULL 			--@ve_porc_iva
                                ,NULL 			--@ve_monto_iva
                                ,NULL 			--@ve_total_con_iva
                                ,NULL 			--@ve_porc_factura_parcial
                                ,NULL 			--@ve_nom_forma_pago_otro
                                ,NULL 			--@ve_genera_salida
                                ,NULL 			--@ve_tipo_doc
                                ,NULL 			--@ve_cancelada
                                ,NULL 			--@ve_cod_centro_costo
                                ,NULL 			--@ve_cod_vendedor_sofland
                                ,NULL 			--@ve_ws_origen
                                ,NULL 			--@ve_xml_dte
                                ,NULL 			--@ve_track_id_dte
                                ,'$response' 	--@ve_resp_emitir_dte respuesta del envio";
                       
		if ($db->EXECUTE_SP($sp, $param)) {
			$db->COMMIT_TRANSACTION();
		}else{
			$db->ROLLBACK_TRANSACTION();
		}
		
		//Verificamos que realice bien el documento emitido.
		$rep_response = explode("200 OK", $response);
		
		if($rep_response[1] <> ''){
			
			//resuelve la cadena entrega
			$objEnJson_genera = $dte->respuesta_emitir_dte($response);
			
			//se envia al genera.
			$response_genera = $dte->post_genera_dte($objEnJson_genera);
			
			//resuelve cadena enviada desde el genera
			$respuesta_genera_dte = $dte->respuesta_genera_dte($response_genera);
			
			$nro_fa_dte		= $respuesta_genera_dte [6];
			$EnvioDTExml	= $respuesta_genera_dte [28];
			$track_id		= $respuesta_genera_dte [30];
				
			if (($nro_fa_dte <> '') && ($EnvioDTExml <> '')&& ($track_id <> '')){
				$cod_factura = $this->get_key();
				
				if($reenviar)
					$operacion = 'REENVIA_SAVE_DTE';
				else
					$operacion = 'SAVE_DTE';
				
				$db->BEGIN_TRANSACTION();
				$sp = 'spu_factura';
				$param = "'$operacion'
								,$cod_factura
                                ,$this->cod_usuario
                                ,NULL 			--@ve_cod_usuario
                                ,$nro_fa_dte
                                ,NULL 			--@ve_fecha_factura
                                ,".self::K_ESTADO_SII_ENVIADA."
                                ,NULL 			--@ve_cod_empresa
                                ,NULL 			--@ve_cod_sucursal_factura
                                ,NULL 			--@ve_cod_persona
                                ,NULL 			--@ve_referencia
                                ,NULL 			--@ve_nro_orden_compra
                                ,NULL 			--@ve_fecha_orden_compra_cliente
                                ,NULL 			--@ve_obs
                                ,NULL 			--@ve_retirado_por
                                ,NULL 			--@ve_rut_retirado_por
                                ,NULL 			--@ve_dig_verif_retirado_por
                                ,NULL 			--@ve_guia_transporte
                                ,NULL 			--@ve_patente
                                ,NULL 			--@ve_cod_bodega
                                ,NULL 			--@ve_cod_tipo_factura
                                ,NULL 			--@ve_cod_doc
                                ,NULL 			--@ve_motivo_anula
                                ,NULL 			--@ve_cod_usuario_anula
                                ,NULL 			--@ve_cod_usuario_vendedor1
                                ,NULL 			--@ve_porc_vendedor1
                                ,NULL 			--@ve_cod_usuario_vendedor2
                                ,NULL 			--@ve_porc_vendedor2
                                ,NULL 			--@ve_cod_forma_pago
                                ,NULL 			--@ve_cod_origen_venta
                                ,NULL 			--@ve_subtotal
                                ,NULL 			--@ve_porc_dscto1
                                ,NULL 			--@ve_ingreso_usuario_dscto1
                                ,NULL 			--@ve_monto_dscto1
                                ,NULL 			--@ve_porc_dscto2
                                ,NULL 			--@ve_ingreso_usuario_dscto2
                                ,NULL 			--@ve_monto_dscto2
                                ,NULL 			--@ve_total_neto
                                ,NULL 			--@ve_porc_iva
                                ,NULL 			--@ve_monto_iva
                                ,NULL 			--@ve_total_con_iva
                                ,NULL 			--@ve_porc_factura_parcial
                                ,NULL 			--@ve_nom_forma_pago_otro
                                ,NULL 			--@ve_genera_salida
                                ,NULL 			--@ve_tipo_doc
                                ,NULL 			--@ve_cancelada
                                ,NULL 			--@ve_cod_centro_costo
                                ,NULL 			--@ve_cod_vendedor_sofland
                                ,NULL 			--@ve_ws_origen
                                ,'$EnvioDTExml'		--@ve_xml_dte
                                ,$track_id 			--@ve_track_id_dte";
	            if ($db->EXECUTE_SP($sp, $param)) {
					$db->COMMIT_TRANSACTION();
					$cod_factura = $this->get_key();
					
					if($reenviar)
						$this->alert('Se ha reenviado exitosamente el DTE al SII');
					
					print " <script>window.open('../common_appl/print_dte.php?cod_documento=$cod_factura&DTE_ORIGEN=$cod_tipo_dte&ES_CEDIBLE=N')</script>";
					$this->_load_record();
				}else{
					$db->ROLLBACK_TRANSACTION();
				}
			}else{
				$this->_load_record();
				print " <script>alert('Error al Generar Dte contactarse con Integrasystem. $respuesta_genera_dte[0]');</script>";
			}	
		}else{
			//responde al dte consultado.
			$this->_load_record();
			print " <script>alert('Error al Emitir Dte contactarse con Integrasystem.');</script>";
		}
		$this->unlock_record();
	}
	
	function actualizar_estado_dte(){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$cod_factura = $this->get_key();
		
		$sql = "SELECT F.NRO_FACTURA
              			,REPLACE(REPLACE(dbo.f_get_parametro(".self::K_PARAM_RUTEMISOR."),'.',''),'-7','') as RUTEMISOR
				FROM FACTURA F
				WHERE F.COD_FACTURA =$cod_factura";
		$consultar = $db->build_results($sql);

		$PORC_IVA = $this->dws['dw_factura']->get_item(0, 'PORC_IVA');
		
		if ($PORC_IVA==0)
			$tipodte = 34;
		else
			$tipodte = 33;
		
		$nro_factura	= $consultar[0]['NRO_FACTURA']; 
		$rutemisor		= $consultar[0]['RUTEMISOR'];
		
		//Llamamos a dte.
		$dte = new dte();
		
		//Se le pasa como variable hash de la clase obtenida en parametros en la BD
		$SqlHash = "SELECT dbo.f_get_parametro(".self::K_PARAM_HASH.") K_HASH";  
		$Datos_Hash = $db->build_results($SqlHash);
		$dte->hash = $Datos_Hash[0]['K_HASH'];
		
		//Llamamos al envio consultar estado de socumento.
		$response = $dte->actualizar_estado($tipodte,$nro_factura,$rutemisor);
		
		$actualizar_estado = $dte->respuesta_actualizar_estado($response);
		
		$revision_estado	= $actualizar_estado [9]; //respuesta de aceptado.
		if ($revision_estado == ''){
			$revision_estado	= $actualizar_estado [6]; //respuesta de rechazado.
		}
		//responde al dte consultado.
		$this->_load_record();
		print "<script>alert('Su documento electronico se encuentra en estado: $revision_estado');</script>";
	}
	
	function imprimir_dte($es_cedible, $desde_output=false){
		$cod_factura = $this->get_key();
		$PORC_IVA = $this->dws['dw_factura']->get_item(0, 'PORC_IVA');
		
		if ($PORC_IVA==0){
			$cod_tipo_dte = 34;
		}else{
			$cod_tipo_dte = 33;
		}
		
		if($cod_factura > 37204)
			print " <script>window.open('../common_appl/print_dte.php?cod_documento=$cod_factura&DTE_ORIGEN=$cod_tipo_dte&ES_CEDIBLE=$es_cedible')</script>";
		else{
			$nro_factura = $this->get_key_para_ruta_menu();
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql= "SELECT YEAR(FECHA_FACTURA) YEAR
				   FROM FACTURA
				   WHERE COD_FACTURA = $cod_factura";
			$result = $db->build_results($sql);
			$year = $result[0]['YEAR'];
			
			if(file_exists("../../../../PDF/PDFCOMERCIALBIGGI/$year/".$cod_tipo_dte."_$nro_factura.pdf"))
				print " <script>window.open('../../../../PDF/PDFCOMERCIALBIGGI/$year/".$cod_tipo_dte."_$nro_factura.pdf')</script>";
			else
				$this->alert('No se registra PDF del documento solicitado en respaldos Signature.');	
		}

		if(!$desde_output)
			$this->_load_record();
	}

	function reenviar_dte(){
		$this->enviar_dte(true);
	}
	
	function xml_dte(){
		$cod_factura = $this->get_key();
		$PORC_IVA = $this->dws['dw_factura']->get_item(0, 'PORC_IVA');
		
		if ($PORC_IVA==0)
			$cod_tipo_dte = 34;
		else
			$cod_tipo_dte = 33;
		
		$name_archivo = "XML_DTE_".$cod_tipo_dte."_".$this->get_key_para_ruta_menu().".xml";
		
		$fname = tempnam("/tmp", $name_archivo);
		$handle = fopen($fname,"w");
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

		$sql= "SELECT XML_DTE
			   FROM FACTURA
			   WHERE COD_FACTURA = $cod_factura";
		$result = $db->build_results($sql);
		
		$XML_DTE = base64_decode($result[0]['XML_DTE']);
		
		fwrite($handle, $XML_DTE);				
		fwrite($handle, "\r\n");
		
		fclose($handle);
		
		header("Content-Type: application/force-download; name=\"$name_archivo\"");
		header("Content-Disposition: inline; filename=\"$name_archivo\"");
		$fh=fopen($fname, "rb");
		fpassthru($fh);
	}
}

class print_factura extends print_factura_base {	
	function print_factura($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::print_factura_base($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);
	}			
}
?>