<?php
ini_set('display_errors', 'Off');
require_once('wss_validation_master/wss/soap-wsse.php');
require_once('wss_validation_master/wss/soap-validation.php');
require_once('WebService.php');
require_once("class_database.php");
require_once("class_Template.php");
//define('SERVER_CERT', 'tbk_server.pem'); //prueba
define('SERVER_CERT', 'serverTBK.crt'); // para produccion
$token = $_POST['token_ws'];
$url_fracaso = "http://www.biggi.cl/fracaso";

$webpayService = new WebService();
$getTransactionResult = new getTransactionResult();
$getTransactionResult->tokenInput = $token;
$getTransactionResultResponse = $webpayService->getTransactionResult($getTransactionResult);
$transactionResultOutput = $getTransactionResultResponse->return;

$xmlResponse = $webpayService->soapClient->__getLastResponse();
$soapValidation = new SoapValidation($xmlResponse, SERVER_CERT);
$validationResult = $soapValidation->getValidationResult();
if($validationResult === true){
	//detalle transaccion
	$orden_compra = $transactionResultOutput->buyOrder;
	$sessionId = $transactionResultOutput->sessionId;
	$fecha_autorizacion = $transactionResultOutput->accountingDate;
	$fecha_hora_autorizacion = $transactionResultOutput->transactionDate;
	$resultado_autenticacion = $transactionResultOutput->VCI;
	$urlRedirection = $transactionResultOutput->urlRedirection;
	$detailsOutput = $transactionResultOutput->detailOutput;
	//detalle tarjeta
	$numeroTarjeta = $transactionResultOutput->cardDetail->cardNumber;
	$expiracion_tarjeta = $transactionResultOutput->cardDetail->cardExpirationDate;
	
	//detalle compra
	$codigo_autorizacion = $transactionResultOutput->detailOutput->authorizationCode;
	$tipo_transaccion = $transactionResultOutput->detailOutput->paymentTypeCode;
	$respuesta_pago = $transactionResultOutput->detailOutput->responseCode;
	//$valor_cuota = $transactionResultOutput->detailOutput->sharesAmount; 
	$cantidad_cuota = $transactionResultOutput->detailOutput->sharesNumber;
	$monto_pago = $transactionResultOutput->detailOutput->amount;
	$codigo_comercio = $transactionResultOutput->detailOutput->commerceCode;
	$orden_compra_tienda = $transactionResultOutput->detailOutput->buyOrder;
	
	$dbc   = new database();
	
	//se insertan los datos del ws a BD
	$param = "'INSERT'
			,$orden_compra
			,'$sessionId'
			,'$numeroTarjeta'
			,'$expiracion_tarjeta'
			,'$fecha_autorizacion'
			,'$fecha_hora_autorizacion'
			,'$resultado_autenticacion'
			,'$urlRedirection'
			,'$codigo_autorizacion'
			,'$tipo_transaccion'
			,'$respuesta_pago'
			,$monto_pago
			,null
			,$cantidad_cuota
			,'$codigo_comercio'
			,'$orden_compra_tienda'";
	
	$sp = 'sp_wp_pago_transaccion_ws';
	
	if(!$dbc->EXECUTE_SP($sp,$param)) 
		$urlRedirection = $url_fracaso;
	else{ 
		//- validar orden de compra
		$sql = "SELECT COUNT(*) CANT
				FROM WP_TRANSACCION
				WHERE COD_WP_TRANSACCION =$orden_compra
				AND EXITO = 'S'";
		$dbc->query($sql);
		$result = $dbc->get_row();
		$cant = $result['CANT'];
		
		// validar monto
		$sql = "SELECT MONTO_PAGO
				,TOKEN
				FROM WP_TRANSACCION
				WHERE COD_WP_TRANSACCION =$orden_compra";
		$dbc->query($sql);
		$result = $dbc->get_row();
		$monto_guardado = $result['MONTO_PAGO'];
		$token_guardado = $result['TOKEN'];
		if($cant > 0){//COMPROBAR QUE NO ESTE DUPLICADA
				$urlRedirection = $url_fracaso; 
		}else{
			//SE INFORMA A TRANSBANK QUE SE HIZO UNA TRANSACCION
			$webpayService = new WebService();
			$acknowledgeTransaction = new acknowledgeTransaction();
			$acknowledgeTransaction->tokenInput = $token;
			$acknowledgeTransactionResponse = $webpayService->acknowledgeTransaction($acknowledgeTransaction);
			$xmlResponse = $webpayService->soapClient->__getLastResponse();
			$soapValidation = new SoapValidation($xmlResponse, SERVER_CERT);
			$validationResult = $soapValidation->getValidationResult();
			if($validationResult === true){ 
				 if($monto_guardado != $monto_pago){ 
				 	$urlRedirection = $url_fracaso; 
				}else if($token_guardado != $token){ 
					$urlRedirection = $url_fracaso; 
				}else if($respuesta_pago != '0'){	
						$urlRedirection = $url_fracaso; 
				}else if($respuesta_pago == '0'){ 
					//si todas las validaciones OK
					//Se confirma Pago a Transbank
					$urlRedirection = $transactionResultOutput->urlRedirection;
					$sql = "select N.COD_EMPRESA
								  ,CONVERT(VARCHAR(100),GETDATE(),103) FECHA_REGISTRO 
								  ,N.COD_NOTA_VENTA
							from NOTA_VENTA N, WP_TRANSACCION W
							where W.COD_WP_TRANSACCION = $orden_compra
							AND N.COD_NOTA_VENTA = W.COD_NOTA_VENTA";
							
					$dbc->query($sql);
					$result = $dbc->get_row();
					$COD_EMPRESA = $result['COD_EMPRESA'];
					$FECHA_DOC = $result['FECHA_REGISTRO'];
					$COD_DOC = $result['COD_NOTA_VENTA'];
			
					$sp = "spu_ingreso_pago";
					$param = "'INSERT'
								,null 	--COD_INGRESO_PAGO
								,1 		--COD_USUARIO				
								,$COD_EMPRESA				
								,0		--OTRO_INGRESO
								,0		--OTRO_GASTO	
								,1		--COD_ESTADO_INGRESO_PAGO				
								,null	--COD_USUARIO_ANULA
								,null	--MOTIVO_ANULA
								,null	--COD_USUARIO_CONFIRMA
								,0		--OTRO_ANTICIPO
								,2		--COD_PROYECTO_INGRESO
								,NULL
								,NULL
								,'WEBPAY_PLUS'";
					
					if(!$dbc->EXECUTE_SP($sp,$param)) 
						$urlRedirection = $url_fracaso; 

					$COD_INGRESO_PAGO = $dbc->GET_IDENTITY();
					
					if($tipo_transaccion== 'VD')
					$COD_TIPO_DOC_PAGO = 5;//TARJETA DÉBITO
					else
					$COD_TIPO_DOC_PAGO = 6;//TARJETA CRÉDITO
					
					$sp = "spu_doc_ingreso_pago";
					$param = "'INSERT'
								,null	--COD_DOC_INGRESO_PAGO
								,$COD_INGRESO_PAGO
								,$COD_TIPO_DOC_PAGO
								,1		--COD_BANCO
								,$codigo_autorizacion
								,'$FECHA_DOC'
								,$monto_pago --MONTO_DOC
								, null		--COD_CHEQUE";
					if(!$dbc->EXECUTE_SP($sp,$param)) 
						$urlRedirection = $url_fracaso;

					// crea la linea de pago de la NV
					$sp = "spu_ingreso_pago_factura";
					$param = "'INSERT'
							  ,null
							  ,$COD_INGRESO_PAGO
							  ,$COD_DOC
							  ,'NOTA_VENTA'
							  ,$monto_pago";
					if(!$dbc->EXECUTE_SP($sp,$param)) 
						$urlRedirection = $url_fracaso;
						
					// confirma el ingreso_pago
					$sp = "spu_ingreso_pago";
					$param = "'CONFIRMA'
							 ,$COD_INGRESO_PAGO
							 ,null	--COD_USUARIO
							 ,null	--COD_EMPRESA
							 ,null 	--OTRO_INGRESO
							 ,null	--OTRO_GASTO	
							 ,2 	--COD_ESTADO_INGRESO_PAGO
							 ,null	--COD_USUARIO_ANULA
							 ,null	--MOTIVO_ANULA
							 ,1		--COD_USUARIO_CONFIRMA"; 
					if(!$dbc->EXECUTE_SP($sp,$param)) 
						$urlRedirection = $url_fracaso; 
					
					/* SI LA TRANSACCION RESULTO EXITOSA SE ACTUALIZA LA TABLA WP_TRANSACCION */
					$sp = "spw_wp_transaccion";
					$param = "'UPDATE',$orden_compra";
					if(!$dbc->EXECUTE_SP($sp,$param)) 
						$urlRedirection = $url_fracaso;
					
					///////////////////////Datos correo.html (PRIMER CORREO)////////////////////
					$temp = new Template('correo.html');
					
					$sql_mail = "SELECT  TOP 1 dbo.f_format_date(GETDATE(), 3) ENVIO_MAIL
										,E.NOM_EMPRESA
										,P.NOM_PERSONA
										,P.EMAIL
										,NV.COD_NOTA_VENTA
										,dbo.number_format(E.RUT, 0, ',', '.')+'-'+E.DIG_VERIF RUT
										,dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL_FACTURA, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA
										,REFERENCIA
										,U.NOM_USUARIO
										,dbo.number_format(TOTAL_CON_IVA, 0, ',', '.') TOTAL_CON_IVA
										,(SELECT TOP 1 WPT.TBK_TIPO_TRANSACCION FROM WP_PAGO_TRANSACCION WPT WHERE WPT.COD_WP_TRANSACCION = WT.COD_WP_TRANSACCION ORDER BY WPT.COD_WP_PAGO_TRANSACCION DESC) TBK_TIPO_TRANSACCION
										,dbo.number_format(MONTO_PAGO, 0, ',', '.') MONTO_PAGO
										,EXITO
										,MAIL
										,U.CELULAR
										,U.TELEFONO
										,CONVERT(VARCHAR, NV.FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA
										,dbo.number_format(SUBTOTAL, 0, ',', '.') SUBTOTAL
										,dbo.number_format(MONTO_DSCTO1, 0, ',', '.') MONTO_DSCTO1
										,dbo.number_format(MONTO_DSCTO2, 0, ',', '.') MONTO_DSCTO2
										,CONVERT(VARCHAR, WT.FECHA_WP_TRANSACCION, 103) FECHA_WP_TRANSACCION
										,(SELECT TOP 1 CODIGO_AUTORIZACION FROM WP_PAGO_TRANSACCION_WS WPT WHERE WPT.COD_WP_TRANSACCION = WT.COD_WP_TRANSACCION ORDER BY WPT.COD_WP_PAGO_TRANSACCION_WS DESC) TBK_CODIGO_AUTORIZACION
										,(SELECT TOP 1 CANTIDAD_CUOTA FROM WP_PAGO_TRANSACCION_WS WPT WHERE WPT.COD_WP_TRANSACCION = WT.COD_WP_TRANSACCION ORDER BY WPT.COD_WP_PAGO_TRANSACCION_WS DESC) TBK_NUMERO_CUOTAS
										,(SELECT TOP 1 TIPO_PAGO FROM WP_PAGO_TRANSACCION_WS WPT WHERE WPT.COD_WP_TRANSACCION = WT.COD_WP_TRANSACCION ORDER BY WPT.COD_WP_PAGO_TRANSACCION_WS DESC) TBK_TIPO_PAGO
										,(SELECT IP.COD_INGRESO_PAGO	FROM INGRESO_PAGO IP, INGRESO_PAGO_FACTURA IPF WHERE IPF.COD_DOC = WT.COD_NOTA_VENTA AND IPF.TIPO_DOC = 'NOTA_VENTA' AND IP.COD_INGRESO_PAGO = IPF.COD_INGRESO_PAGO AND IP.COD_ESTADO_INGRESO_PAGO <> 3) COD_INGRESO_PAGO
										FROM WP_TRANSACCION WT
										,NOTA_VENTA NV
										,EMPRESA E
										,PERSONA P
										,USUARIO U
									WHERE WT.COD_WP_TRANSACCION = 100286 --$ orden_compra
									AND WT.COD_NOTA_VENTA = NV.COD_NOTA_VENTA
									AND NV.COD_EMPRESA = E.COD_EMPRESA
									AND P.COD_PERSONA = NV.COD_PERSONA
									AND U.COD_USUARIO = NV.COD_USUARIO_VENDEDOR1
									ORDER BY WT.COD_WP_TRANSACCION";
					$result_mail = $dbc->build_results($sql_mail);
					
					
					$sql_persona = "SELECT top 1 MAIL_TO
                                        ,MAIL_TO_NAME
                                    FROM ENVIO_MAIL
                                    WHERE TIPO_DOC = 'MAIL_PAGO_WEBPAY1' 
                                    AND COD_DOC =".$result_mail[0]['COD_NOTA_VENTA'];
					$result_persona = $dbc->build_results($sql_persona);
					
					$mail_persona          = $result_persona[0]['MAIL_TO'];
					$mail_persona_nombre   = $result_persona[0]['MAIL_TO_NAME'];
                    
					$temp->setVar('NOM_PERSONA', $mail_persona_nombre);
					$temp->setVar('ENVIO_MAIL', $result_mail[0]['ENVIO_MAIL']);
					$temp->setVar('NOM_EMPRESA', $result_mail[0]['NOM_EMPRESA']);
					$temp->setVar('COD_NOTA_VENTA', $result_mail[0]['COD_NOTA_VENTA']);
					$temp->setVar('RUT', $result_mail[0]['RUT']);
					$temp->setVar('DIRECCION_FACTURA', $result_mail[0]['DIRECCION_FACTURA']);
					$temp->setVar('REFERENCIA', $result_mail[0]['REFERENCIA']);
					$temp->setVar('NOM_USUARIO', $result_mail[0]['NOM_USUARIO']);
					$temp->setVar('TOTAL_CON_IVA', $result_mail[0]['TOTAL_CON_IVA']);
					$temp->setVar('CELULAR', $result_mail[0]['CELULAR']);
					$temp->setVar('TELEFONO', $result_mail[0]['TELEFONO']);
					$temp->setVar('MAIL', $result_mail[0]['MAIL']);
					$temp->setVar('COD_INGRESO_PAGO', $result_mail[0]['COD_INGRESO_PAGO']);
					
					if($result_mail[0]['TBK_TIPO_PAGO'] == 'VD'){
						$tipo_pago = "Debito";
						$tipo_pago_str = "Sin cuotas (Tarjeta Débito)";
					}else{
						$tipo_pago = "Credito";
					
						if($result_mail[0]['TBK_TIPO_PAGO']=='SI')
							$tipo_pago_str = $result_mail[0]['TBK_NUMERO_CUOTAS'].' Cuotas Sin Interés';
						else if($result_mail[0]['TBK_TIPO_PAGO']=='S2')
							$tipo_pago_str = $result_mail[0]['TBK_NUMERO_CUOTAS'].' Cuotas Sin Interés';
						else if($result_mail[0]['TBK_TIPO_PAGO']=='VC')
							$tipo_pago_str = $result_mail[0]['TBK_NUMERO_CUOTAS'].' Cuotas normales';
						else if($result_mail[0]['TBK_TIPO_PAGO']=='VN')
							$tipo_pago_str = 'Crédito Sin cuotas';
						else if($result_mail[0]['TBK_TIPO_PAGO']=='NC')
							$tipo_pago_str = $result_mail[0]['TBK_NUMERO_CUOTAS'].' Cuotas Sin Interés';
					}
					$telefono = $result_mail[0]['TELEFONO'];
					$celular = $result_mail[0]['CELULAR'];
					$email = $result_mail[0]['MAIL'];
					
					if(($telefono!='')&&($celular!='')&&($email!='')){//tiene telefono,celular,mail
						$temp->setVar('FRASE_TELEFONO',"al fono : ");
						$temp->setVar('FRASE_CELULAR',", o al movil : ");
						$temp->setVar('FRASE_MAIL',", o bien al email : ");
					}else if(($telefono!='')&&($celular!='')&&($email=='')){//tiene telefono,celular
						$temp->setVar('FRASE_TELEFONO',"al fono : ");
						$temp->setVar('FRASE_CELULAR',", o al movil : ");
						$temp->setVar('FRASE_MAIL',"");
					}else if(($telefono!='')&&($celular=='')&&($email=='')){//tiene telefono
						$temp->setVar('FRASE_TELEFONO',"al fono : ");
						$temp->setVar('FRASE_CELULAR',"");
						$temp->setVar('FRASE_MAIL',"");
					}else if(($telefono=='')&&($celular!='')&&($email!='')){//tiene celular,mail
						$temp->setVar('FRASE_TELEFONO',"");
						$temp->setVar('FRASE_CELULAR',"al movil : ");
						$temp->setVar('FRASE_MAIL',", o al email : ");
					}else if(($telefono=='')&&($celular!='')&&($email=='')){//tiene celular
						$temp->setVar('FRASE_TELEFONO',"");
						$temp->setVar('FRASE_CELULAR',"al movil : ");
						$temp->setVar('FRASE_MAIL',"");
					}else if(($telefono=='')&&($celular=='')&&($email!='')){//tiene mail
						$temp->setVar('FRASE_TELEFONO',"");
						$temp->setVar('FRASE_CELULAR',"");
						$temp->setVar('FRASE_MAIL',"al email : ");
					}else if(($telefono=='')&&($celular!='')&&($email!='')){//tiene movil,mail
						$temp->setVar('FRASE_TELEFONO',"");
						$temp->setVar('FRASE_CELULAR',"al movil : ");
						$temp->setVar('FRASE_MAIL',", o al email : ");
					}else if(($telefono!='')&&($celular=='')&&($email!='')){//tiene telfono,mail
						$temp->setVar('FRASE_TELEFONO',"al fono : ");
						$temp->setVar('FRASE_CELULAR',"");
						$temp->setVar('FRASE_MAIL',", o al email : ");
					}else if(($telefono=='')&&($celular=='')&&($email=='')){//no tiene nada
						$temp->setVar('FRASE_TELEFONO',"");
						$temp->setVar('FRASE_CELULAR',"");
						$temp->setVar('FRASE_MAIL',"");
					}
					
					$temp->setVar('TIPO_PAGO_STR', $tipo_pago_str);
					$temp->setVar('TBK_TIPO_PAGO', "Tarjeta $tipo_pago");
					$temp->setVar('MONTO_PAGO', $result_mail[0]['MONTO_PAGO']);
					
					$mail_from			= "'webpay@biggi.cl'";
					$mail_from_name		= "'Comercial Biggi S.A / Web Pay'";
					$asunto 			= "'Confirmación de pago recibido / Nota de venta Nº ".$result_mail[0]['COD_NOTA_VENTA']."'";
					$html_temp			= $temp->toString();
					
					$lista_mail_to		= "'$mail_persona'";
					$lista_mail_to_name	= "'$mail_persona_nombre'";
					$lista_mail_cc		= "NULL";
					$lista_mail_cc_name	= "NULL";
					$lista_mail_bcc		= "'".$result_mail[0]['MAIL'].";sergio.pechoante@biggi.cl;jcatalan@biggi.cl;mherrera@biggi.cl'";
					$lista_mail_bcc_name= "NULL";

					$sp = "spu_envio_mail";
					$param = "'INSERT'
							,null
							,null
							,null
						 	,$mail_from
						 	,$mail_from_name
						 	,$lista_mail_cc
						 	,$lista_mail_cc_name
						 	,$lista_mail_bcc
						 	,$lista_mail_bcc_name
						 	,$lista_mail_to
						 	,$lista_mail_to_name
						 	,$asunto
						 	,'".str_replace("'","''",$html_temp)."'
						 	,NULL
						 	,'MAIL_PAGO_WEBPAY2'
						 	,".$result_mail[0]['COD_NOTA_VENTA'];
					if(!$dbc->EXECUTE_SP($sp, $param))
						$urlRedirection = $url_fracaso;
					/////////////////////////////////////////////////////////////////////////////
					
					///////////////////////Datos correo_interno.html (SEGUNDO CORREO)////////////////////
					$temp = new Template('correo_interno.html');
					
					$temp->setVar('ENVIO_MAIL', $result_mail[0]['ENVIO_MAIL']);
					$temp->setVar('NOM_PERSONA', $mail_persona_nombre);
					$temp->setVar('EMAIL', $mail_persona);
					$temp->setVar('NOM_EMPRESA', $result_mail[0]['NOM_EMPRESA']);
					$temp->setVar('COD_NOTA_VENTA', $result_mail[0]['COD_NOTA_VENTA']);
					$temp->setVar('RUT', $result_mail[0]['RUT']);
					$temp->setVar('DIRECCION_FACTURA', $result_mail[0]['DIRECCION_FACTURA']);
					$temp->setVar('REFERENCIA', $result_mail[0]['REFERENCIA']);
					$temp->setVar('NOM_USUARIO', $result_mail[0]['NOM_USUARIO']);
					$temp->setVar('TOTAL_CON_IVA', $result_mail[0]['TOTAL_CON_IVA']);
					$temp->setVar('FECHA_NOTA_VENTA', $result_mail[0]['FECHA_NOTA_VENTA']);
					$temp->setVar('SUBTOTAL', $result_mail[0]['SUBTOTAL']);
					$temp->setVar('MONTO_DSCTO1', $result_mail[0]['MONTO_DSCTO1']);
					$temp->setVar('MONTO_DSCTO2', $result_mail[0]['MONTO_DSCTO2']);
					$temp->setVar('FECHA_WP_TRANSACCION', $result_mail[0]['FECHA_WP_TRANSACCION']);
					$temp->setVar('TBK_AUTORIZACION', $result_mail[0]['TBK_CODIGO_AUTORIZACION']);
					$temp->setVar('COD_INGRESO_PAGO', $result_mail[0]['COD_INGRESO_PAGO']);
					
					if($result_mail[0]['TBK_TIPO_PAGO'] == 'VD'){
						$tipo_pago = "Debito";
						$tipo_pago_str = "Sin cuotas (Tarjeta Débito)";
					}else{
						$tipo_pago = "Credito";
					
						if($result_mail[0]['TBK_TIPO_PAGO']=='SI')
							$tipo_pago_str = $result_mail[0]['TBK_NUMERO_CUOTAS'].' Cuotas Sin Interés';
						else if($result_mail[0]['TBK_TIPO_PAGO']=='S2')
							$tipo_pago_str = $result_mail[0]['TBK_NUMERO_CUOTAS'].' Cuotas Sin Interés';
						else if($result_mail[0]['TBK_TIPO_PAGO']=='VC')
							$tipo_pago_str = $result_mail[0]['TBK_NUMERO_CUOTAS'].' Cuotas normales';
						else if($result_mail[0]['TBK_TIPO_PAGO']=='VN')
							$tipo_pago_str = 'Crédito Sin cuotas';
						else if($result_mail[0]['TBK_TIPO_PAGO']=='NC')
							$tipo_pago_str = $result_mail[0]['TBK_NUMERO_CUOTAS'].' Cuotas Sin Interés';
					}
					
					$temp->setVar('TIPO_PAGO_STR', $tipo_pago_str);
					$temp->setVar('TBK_TIPO_PAGO', "Tarjeta $tipo_pago");
					$temp->setVar('MONTO_PAGO', $result_mail[0]['MONTO_PAGO']);

					$mail_from			= "'webpay@biggi.cl'";
					$mail_from_name		= "'Comercial Biggi S.A / Web Pay'";
					$asunto 			= "'Confirmación de pago recibido / Nota de venta Nº ".$result_mail[0]['COD_NOTA_VENTA']." / Comprobante interno'";
					$html_temp			= $temp->toString();
					
					$lista_mail_to		= "'".$result_mail[0]['MAIL'].";sergio.pechoante@biggi.cl;jcatalan@biggi.cl'";
					$lista_mail_to_name	= "'".$result_mail[0]['NOM_USUARIO']."'";
					$lista_mail_cc		= "NULL";
					$lista_mail_cc_name	= "NULL";
					$lista_mail_bcc		= "'mherrera@biggi.cl'";
					$lista_mail_bcc_name= "NULL";
					
					$sp = "spu_envio_mail";
					$param = "'INSERT'
							,null
							,null
							,null
						 	,$mail_from
						 	,$mail_from_name
						 	,$lista_mail_cc
						 	,$lista_mail_cc_name
						 	,$lista_mail_bcc
						 	,$lista_mail_bcc_name
						 	,$lista_mail_to
						 	,$lista_mail_to_name
						 	,$asunto
						 	,'".str_replace("'","''",$html_temp)."'
						 	,NULL
						 	,'MAIL_PAGO_WEBPAY3'
						 	,".$result_mail[0]['COD_NOTA_VENTA'];
						 	
					if(!$dbc->EXECUTE_SP($sp, $param))
						$urlRedirection = $url_fracaso;
					/////////////////////////////////////////////////////////////////////////////
					
				} 
				
			}else{ 
				$urlRedirection = $url_fracaso;
			}
		}
	}
}else{  
	$urlRedirection = $url_fracaso;
	}
 //SI SE QUIERE VER EL REQUEST Y EL RESPONSE
/*	print_r($getTransactionResult);
	print_r($transactionResultOutput);
	print_r($acknowledgeTransaction);
*/
	//OnLoad="window.document.forms['imput'].submit();"
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title></title>
</head>
<body OnLoad="window.document.forms['imput'].submit();">
<form action="<?=$urlRedirection?>" method="post" id="imput" TARGET="_self">
<p><input type="hidden" name="token_ws" size="60" value="<?=$token?>"/></p>
<p><input type="hidden" name="orden_compra" size="60" value="<?=$orden_compra?>"/></p>
</form>
</body>
</html>