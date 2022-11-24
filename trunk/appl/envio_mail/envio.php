<?php
// primero hay que incluir la clase phpmailer para poder instanciar
//un objeto de la misma
require_once("class_PHPMailer.php");
require_once("class_database.php");
require_once("../../appl.ini");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$db->EXECUTE_SP('sp_crontab_noche', "'MAIL_TO_VACIO'");
update_track_id($db);

$sql = "select dbo.f_get_parametro(53) 		URL_SMTP
				,dbo.f_get_parametro(54) 	USER_SMTP
				,dbo.f_get_parametro(55) 	PASS_SMTP
				,dbo.f_get_parametro(71) 	PORT_SMTP";
$result = $db->build_results($sql);
$URL_SMTP 	= $result[0]['URL_SMTP']; 
$USER_SMTP	= $result[0]['USER_SMTP']; 
$PASS_SMTP	= $result[0]['PASS_SMTP'];
$PORT_SMTP	= $result[0]['PORT_SMTP']; 

$sql = "select COD_ENVIO_MAIL
				,MAIL_FROM
				,MAIL_FROM_NAME
				,MAIL_CC
				,MAIL_CC_NAME
				,MAIL_BCC
				,MAIL_BCC_NAME
				,MAIL_TO
				,MAIL_TO_NAME
				,MAIL_SUBJECT
				,MAIL_BODY
				,MAIL_ALTBODY
		from ENVIO_MAIL
		where COD_ESTADO_ENVIO_MAIL = 1
		AND USUARIO_DTE IS NULL";	// por enviar
$result = $db->build_results($sql);
// 1ero marca como enviandose todos los registros que va a procesar

for($i=0; $i < count($result); $i++) {
	$COD_ENVIO_MAIL = $result[$i]['COD_ENVIO_MAIL'];
	$db->EXECUTE_SP('spu_envio_mail', "'ENVIANDOSE', $COD_ENVIO_MAIL");
}

for($i=0; $i < count($result); $i++) {
	$COD_ENVIO_MAIL = $result[$i]['COD_ENVIO_MAIL']; 
	$MAIL_FROM		= $result[$i]['MAIL_FROM']; 
	$MAIL_FROM_NAME	= $result[$i]['MAIL_FROM_NAME']; 
	$MAIL_CC		= $result[$i]['MAIL_CC']; 
	$MAIL_CC_NAME	= $result[$i]['MAIL_CC_NAME']; 
	$MAIL_BCC 		= $result[$i]['MAIL_BCC']; 
	$MAIL_BCC_NAME	= $result[$i]['MAIL_BCC_NAME']; 
	$MAIL_TO 		= $result[$i]['MAIL_TO'];
	$MAIL_TO_NAME	= $result[$i]['MAIL_TO_NAME'];
	$MAIL_SUBJECT	= $result[$i]['MAIL_SUBJECT'];
	$MAIL_BODY 		= $result[$i]['MAIL_BODY'];
	$MAIL_ALTBODY 	= $result[$i]['MAIL_ALTBODY'];
	
	//instanciamos un objeto de la clase phpmailer al que llamamos 
	//por ejemplo mail
	  $mail = new phpmailer();
	
	  //Definimos las propiedades y llamamos a los métodos 
	  //correspondientes del objeto mail
	
	  //Con PluginDir le indicamos a la clase phpmailer donde se 
	  //encuentra la clase smtp que como he comentado al principio de 
	  //este ejemplo va a estar en el subdirectorio includes
	  $mail->PluginDir = dirname(__FILE__)."/";
	
	  //Con la propiedad Mailer le indicamos que vamos a usar un 
	  //servidor smtp
	  $mail->Mailer = "smtp";
	
	  //Asignamos a Host el nombre de nuestro servidor smtp
	 // $mail->Host = "smtp.hotpop.com";
	
	  //Le indicamos que el servidor smtp requiere autenticación
	  $mail->SMTPAuth = true;
	  
	  $mail->Host = $URL_SMTP;
	  $mail->Username = $USER_SMTP;
	  $mail->Password = $PASS_SMTP; 
	  $mail->Port = $PORT_SMTP;
	  $mail->SMTPSecure= 'ssl';
	  
	  
	  $mail->ClearAddresses();
	  $mail->ContentType="text/html";

	  //Indicamos cual es nuestra dirección de correo y el nombre que 
	  //queremos que vea el usuario que lee nuestro correo
	  $mail->From = $MAIL_FROM;			
	  $mail->FromName = $MAIL_FROM_NAME;
	
	  //el valor por defecto 10 de Timeout es un poco escaso dado que voy a usar 
	  //una cuenta gratuita, por tanto lo pongo a 30  
	  $mail->Timeout=30;
	
	  //Indicamos cual es la dirección de destino del correo
	  $aMAIL_TO = explode(';', $MAIL_TO);
	  $aMAIL_TO_NAME = explode(';', $MAIL_TO_NAME);
	  for ($j=0; $j < count($aMAIL_TO); $j++){
	  	//if($aMAIL_TO[$j] <> 'rescudero@biggi.cl')
			$mail->AddAddress($aMAIL_TO[$j], $aMAIL_TO_NAME[$j]);
	  }
	  if ($MAIL_CC != '') {
		  $aMAIL_CC = explode(';', $MAIL_CC);
		  $aMAIL_CC_NAME = explode(';', $MAIL_CC_NAME);
		  for ($j=0; $j < count($aMAIL_CC); $j++){
		  	//if($aMAIL_CC[$j] <> 'rescudero@biggi.cl')
			  $mail->AddCC($aMAIL_CC[$j], $aMAIL_CC_NAME[$j]);
		  }	  
	  }

	  if ($MAIL_BCC != '') {
		  $aMAIL_BCC = explode(';', $MAIL_BCC);
		  $aMAIL_BCC_NAME = explode(';', $MAIL_BCC_NAME);
		  for ($j=0; $j < count($aMAIL_BCC); $j++){
		  	//if($aMAIL_BCC[$j] <> 'rescudero@biggi.cl')
			  	$mail->AddBCC($aMAIL_BCC[$j], $aMAIL_BCC_NAME[$j]);
		  }	  
	  }
	  
	  //Asignamos asunto y cuerpo del mensaje
	  //El cuerpo del mensaje lo ponemos en formato html, haciendo 
	  //que se vea en negrita
	  $mail->Subject = $MAIL_SUBJECT;
	  
	  $MAIL_BODY_LEN	= strlen($MAIL_BODY);
	  $MAIL_BODY_A_POS	= strpos($MAIL_BODY, "</body>");
	  
	  if($MAIL_BODY_A_POS  !== false){
		  $MAIL_BODY_A	= substr($MAIL_BODY, 0, $MAIL_BODY_A_POS);
		  $MAIL_BODY_B	= substr($MAIL_BODY, $MAIL_BODY_A_POS, ($MAIL_BODY_LEN - $MAIL_BODY_A_POS+7));
		  $MAIL_BODY	= $MAIL_BODY_A."<font color=\"#FFFFFF\" size=\"1\">$COD_ENVIO_MAIL</font>".$MAIL_BODY_B;
	  }else{
	  	  $MAIL_BODY	= $MAIL_BODY."<br><br><font color=\"#FFFFFF\" size=\"1\">$COD_ENVIO_MAIL</font>";
	  }
	  
	  $mail->Body = $MAIL_BODY;
	
	  //Definimos AltBody por si el destinatario del correo no admite email con formato html 
	  $mail->AltBody = $MAIL_ALTBODY;
	
	  //se envia el mensaje, si no ha habido problemas 
	  //la variable $exito tendra el valor true
	  $exito = $mail->Send();
	
	  //Si el mensaje no ha podido ser enviado se realizaran 4 intentos mas como mucho 
	  //para intentar enviar el mensaje, cada intento se hara 5 segundos despues 
	  //del anterior, para ello se usa la funcion sleep	
	  $intentos=1; 
	  while ((!$exito) && ($intentos < 5)) {
		sleep(5);
	     	//echo $mail->ErrorInfo;
	     	$exito = $mail->Send();
	     	$intentos=$intentos+1;	
		
	   }
	 
			
	   if(!$exito)
	   {
		echo "Problemas enviando correo electrónico a ".$valor;
		echo "<br/>".$mail->ErrorInfo;	
	   }
	   else
	   {
		//echo "Mensaje enviado correctamente 150";
		$db->EXECUTE_SP('spu_envio_mail', "'ENVIANDO', $COD_ENVIO_MAIL");
	   }
}

function update_track_id($db){
	$sql_fecha_revisa = "SELECT CONVERT(DATETIME, VALOR, 121) - 1
						 FROM PARAMETRO
						 WHERE COD_PARAMETRO = 501";
	
	$dte		= new dte();
	$SqlHash	= "SELECT dbo.f_get_parametro(200) K_HASH";  
	$Datos_Hash	= $db->build_results($SqlHash);
	$dte->hash	= $Datos_Hash[0]['K_HASH'];
	
	for($a=0 ; $a < 4 ; $a++){
		$doc_fa = "";
		$doc_gd = "";
		$doc_nc = "";
		
		if($a == 0)
			$SISTEMA = 'BIGGI';
		else if($a == 1)
			$SISTEMA = 'TODOINOX';
		else if($a == 2)
			$SISTEMA = 'BODEGA_BIGGI';
		else if($a == 3)
			$SISTEMA = 'RENTAL';
	
		$sql = "SELECT NRO_FACTURA
					  ,CASE
					  	WHEN PORC_IVA = CONVERT(NUMERIC,$SISTEMA.dbo.f_get_parametro(1)) THEN '33'
					  	ELSE '34'
					  END TIPO_DOCUMENTO
					  ,REPLACE(REPLACE($SISTEMA.dbo.f_get_parametro(20),'.',''),'-5','') RUTEMISOR
					  ,COD_FACTURA
				FROM $SISTEMA.DBO.FACTURA F
				WHERE F.FECHA_FACTURA >= ($sql_fecha_revisa)
				AND F.COD_FACTURA NOT IN(23999, 24000)
				AND (F.TRACK_ID_DTE IS NULL OR ISNUMERIC(F.TRACK_ID_DTE)=0 OR F.TRACK_ID_DTE='0')";

		$result_factura = $db->build_results($sql);
		for($l=0 ; $l < count($result_factura) ; $l++){
			$ve_folio		= $result_factura[$l]['NRO_FACTURA'];
			$ve_tipo_doc	= $result_factura[$l]['TIPO_DOCUMENTO'];
			$ve_emisor		= $result_factura[$l]['RUTEMISOR'];
			$cod_factura	= $result_factura[$l]['COD_FACTURA'];
		
			$result = $dte->enviar_sii($ve_folio,$ve_tipo_doc,$ve_emisor);

			$new_var = substr($result, 1, strlen($result)-2);
			$array = explode(',', $new_var);
			$array2 = explode(':', $array[14]);
			$track_id = $array2[1];
			
			$db->EXECUTE_SP($SISTEMA.'.dbo.spu_track_id', "'UPDATE_TRACK_ID_FA', $cod_factura, $track_id");
			$doc_fa .= $cod_factura.":$result,";
		}
		
		$sql = "SELECT NRO_GUIA_DESPACHO
					  ,'52' TIPO_DOCUMENTO
					  ,REPLACE(REPLACE($SISTEMA.dbo.f_get_parametro(20),'.',''),'-5','') RUTEMISOR
					  ,COD_GUIA_DESPACHO
				FROM $SISTEMA.DBO.GUIA_DESPACHO GD	
				WHERE GD.FECHA_GUIA_DESPACHO >= ($sql_fecha_revisa)
				AND (GD.TRACK_ID_DTE IS NULL OR ISNUMERIC(GD.TRACK_ID_DTE)=0 OR GD.TRACK_ID_DTE='0')";
		$result_gd = $db->build_results($sql);
		for($l=0 ; $l < count($result_gd) ; $l++){
			$ve_folio			= $result_gd[$l]['NRO_GUIA_DESPACHO'];
			$ve_tipo_doc		= $result_gd[$l]['TIPO_DOCUMENTO'];
			$ve_emisor			= $result_gd[$l]['RUTEMISOR'];
			$cod_guia_despacho	= $result_gd[$l]['COD_GUIA_DESPACHO'];
			
			$result = $dte->enviar_sii($ve_folio,$ve_tipo_doc,$ve_emisor);
			
			$new_var = substr($result, 1, strlen($result)-2);
			$array = explode(',', $new_var);
			$array2 = explode(':', $array[14]);
			$track_id = $array2[1];
			
			$db->EXECUTE_SP($SISTEMA.'.dbo.spu_track_id', "'UPDATE_TRACK_ID_GD', $cod_guia_despacho, $track_id");
			$doc_gd .= $cod_guia_despacho.":$result,";
		}
	
		$sql = "SELECT NRO_NOTA_CREDITO
					  ,'61' TIPO_DOCUMENTO
					  ,REPLACE(REPLACE($SISTEMA.dbo.f_get_parametro(20),'.',''),'-5','') RUTEMISOR
					  ,COD_NOTA_CREDITO
				FROM $SISTEMA.DBO.NOTA_CREDITO NC
				WHERE NC.FECHA_NOTA_CREDITO >= ($sql_fecha_revisa)
				AND (NC.TRACK_ID_DTE IS NULL OR ISNUMERIC(NC.TRACK_ID_DTE)=0 OR NC.TRACK_ID_DTE='0')";
		$result_nc = $db->build_results($sql);
		for($l=0 ; $l < count($result_nc) ; $l++){
			$ve_folio			= $result_nc[$l]['NRO_NOTA_CREDITO'];
			$ve_tipo_doc		= $result_nc[$l]['TIPO_DOCUMENTO'];
			$ve_emisor			= $result_nc[$l]['RUTEMISOR'];
			$cod_nota_credito	= $result_nc[$l]['COD_NOTA_CREDITO'];
		
			$result = $dte->enviar_sii($ve_folio,$ve_tipo_doc,$ve_emisor);
			
			$new_var = substr($result, 1, strlen($result)-2);
			$array = explode(',', $new_var);
			$array2 = explode(':', $array[14]);
			$track_id = $array2[1];
			
			$db->EXECUTE_SP($SISTEMA.'.dbo.spu_track_id', "'UPDATE_TRACK_ID_NC', $cod_nota_credito, $track_id");
			$doc_nc .= $cod_nota_credito.":$result,";
		}
		
		if($a == 0){
			$biggi_count_doc = count($result_factura) + count($result_gd) + count($result_nc);
			$biggi_doc_fa = trim($doc_fa, ',');
			$biggi_doc_gd = trim($doc_gd, ',');
			$biggi_doc_nc = trim($doc_nc, ',');
		}else if($a == 1){
			$tdnx_count_doc = count($result_factura) + count($result_gd) + count($result_nc);
			$tdnx_doc_fa = trim($doc_fa, ',');
			$tdnx_doc_gd = trim($doc_gd, ',');
			$tdnx_doc_nc = trim($doc_nc, ',');
		}else if($a == 2){
			$bodega_count_doc = count($result_factura) + count($result_gd) + count($result_nc);
			$bodega_doc_fa = trim($doc_fa, ',');
			$bodega_doc_gd = trim($doc_gd, ',');
			$bodega_doc_nc = trim($doc_nc, ',');
		}else if($a == 3){
			$rental_count_doc = count($result_factura) + count($result_gd) + count($result_nc);
			$rental_doc_fa = trim($doc_fa, ',');
			$rental_doc_gd = trim($doc_gd, ',');
			$rental_doc_nc = trim($doc_nc, ',');
		}	
	}

	////////////////Envio Correo/////////////////////////

	if($biggi_count_doc > 0 || $tdnx_count_doc > 0 || $bodega_count_doc > 0 || $rental_count_doc > 0){
		$temp = new Template_appl('trackid_update.htm');
		
		if($biggi_count_doc == 0)
			$display_comercial = 'none';
		if($tdnx_count_doc == 0)
			$display_tdnx = 'none';
		if($bodega_count_doc == 0)
			$display_bodega = 'none';
		if($rental_count_doc == 0)
			$display_rental = 'none';			

		$temp->setVar("NRO_FA_COMERCIAL", "$biggi_doc_fa");
		$temp->setVar("NRO_GD_COMERCIAL", "$biggi_doc_gd");
		$temp->setVar("NRO_NC_COMERCIAL", "$biggi_doc_nc");
		
		$temp->setVar("NRO_FA_TODOINOX", "$tdnx_doc_fa");
		$temp->setVar("NRO_GD_TODOINOX", "$tdnx_doc_gd");
		$temp->setVar("NRO_NC_TODOINOX", "$tdnx_doc_nc");
		
		$temp->setVar("NRO_FA_BODEGA", "$bodega_doc_fa");
		$temp->setVar("NRO_GD_BODEGA", "$bodega_doc_gd");
		$temp->setVar("NRO_NC_BODEGA", "$bodega_doc_nc");
		
		$temp->setVar("NRO_FA_RENTAL", "$rental_doc_fa");
		$temp->setVar("NRO_GD_RENTAL", "$rental_doc_gd");
		$temp->setVar("NRO_NC_RENTAL", "$rental_doc_nc");
			
		$temp->setVar("DISPLAY_COMERCIAL", "$display_comercial");
		$temp->setVar("DISPLAY_TODOINOX", "$display_tdnx");
		$temp->setVar("DISPLAY_BODEGA", "$display_bodega");
		$temp->setVar("DISPLAY_RENTAL", "$display_rental");
		$html = $temp->toString();
	
		$lista_mail_to		= "'mherrera@biggi.cl'";
		$lista_mail_to_name = "'Marcelo Herrera'";
		
		$lista_mail_cc		= "NULL";
		$lista_mail_cc_name = "NULL";
		
		$lista_mail_bcc		= "NULL";
		$lista_mail_bcc_name= "NULL";

		$sp = "spu_envio_mail";	
		$param = "'INSERT'
				,null
				,null
				,null
			 	,'modulo_alertas@biggi.cl'
			 	,'Módulo Alertas Sistemas Web BIGGI'
			 	,$lista_mail_cc
			 	,$lista_mail_cc_name
			 	,$lista_mail_bcc
			 	,$lista_mail_bcc_name
			 	,$lista_mail_to
			 	,$lista_mail_to_name
			 	,'Alerta DOC sin track ID'
			 	,'".str_replace("'","''",$html)."'
			 	,NULL
			 	,'LIBRE_DTE_TRACKID'
			 	,0";
		
		if($db->EXECUTE_SP($sp, $param))
			echo 'exito';
		else
			echo 'fallo';
	}
	
	$db->EXECUTE_SP('spu_track_id', "'UPDATE_PARAM'");

	/////////////////////////////////////////////////////
}
?>
