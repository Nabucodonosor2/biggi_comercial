<?php
	require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
	require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/class_PHPMailer.php");	
	include(dirname(__FILE__)."/../../appl.ini");
	session::set('K_ROOT_DIR', K_ROOT_DIR);

	ini_set('max_execution_time', 900); //900 seconds = 15 minutes 

	//funcion que crea el objeto del phpmailer con sus parametros
	function create_mail($asunto, $db){
		$K_host		= 53;
		$K_Username = 54;
		$K_Password = 55;
		$sql_host	= "SELECT VALOR
				   FROM PARAMETRO 
				   WHERE COD_PARAMETRO =$K_host
				   OR COD_PARAMETRO =$K_Username
				   OR COD_PARAMETRO =$K_Password";
		$result_host	= $db->build_results($sql_host);
		$host			= 	$result_host[0]['VALOR'];
		$Username		= $result_host[1]['VALOR'];
		$Password		= $result_host[2]['VALOR'];

		$mail				= new phpmailer();
		$mail->PluginDir	= dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/";
		$mail->Mailer		= "smtp";
		$mail->SMTPAuth		= true;
		$mail->Host			= $host;
		$mail->Username		= $Username;
		$mail->Password		= $Password; 
		$mail->From			= "soporte@biggi.cl";		
		$mail->FromName		= "Comercial Biggi S.A.";
		$mail->Timeout		= 90;
		$mail->Subject		= $asunto;

		$mail->ClearAddresses();
	
		return $mail;
	}
	
	/*  Esta función arma la consulta con los parámetros día y el código del usuario, en donde el día puede ser 'HOY', 'MAÑANA' o 'AYER' */

	function make_sql($dia, $cod_usuario = NULL){
		$sql = "SELECT C.COD_COTIZACION
          			,CONVERT(VARCHAR,C.FECHA_COTIZACION, 103) FECHA
          			,E.NOM_EMPRESA
          			,C.TOTAL_CON_IVA
          			,BC.GLOSA_COMPROMISO
          			,BC.CONTACTO
          			,' / F: '+BC.TELEFONO FONO
          			,U1.NOM_USUARIO VENDEDOR
					,CONVERT(VARCHAR,BC.FECHA_COMPROMISO, 103) FECHA_COMPROMISO
			FROM	BITACORA_COTIZACION BC LEFT OUTER JOIN USUARIO U2 ON U2.COD_USUARIO = BC.COD_USUARIO_REALIZADO, USUARIO U1, COTIZACION C, EMPRESA E
			WHERE	BC.COD_COTIZACION = C.COD_COTIZACION
			AND 	E.COD_EMPRESA = C.COD_EMPRESA
			AND 	C.COD_USUARIO_VENDEDOR1 = U1.COD_USUARIO
			AND 	BC.TIENE_COMPROMISO = 'S'";
	
		if($dia <> 'RECHAZADAS'){
			$sql .="AND	(BC.COMPROMISO_REALIZADO = 'N' OR BC.COMPROMISO_REALIZADO IS NULL)";
			$sql .="AND	(C.COD_ESTADO_COTIZACION <> 5)";
		}else{
			$sql .="AND C.COD_ESTADO_COTIZACION = 5
				AND BC.FECHA_COMPROMISO <= GETDATE()
				AND BC.FECHA_COMPROMISO >= DATEADD(day, -30 ,GETDATE())
				ORDER BY C.FECHA_COTIZACION DESC";
		}
	
		if($dia == 'HOY'){
			$sql .="AND BC.FECHA_COMPROMISO <= DATEADD(SECOND,86400 - 1, DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE())))
				AND BC.FECHA_COMPROMISO >= DATEADD(SECOND,0 , DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE())))";
		}else if($dia == 'AYER')
			$sql .="AND	BC.COD_COTIZACION > 126113)
				 AND	BC.FECHA_COMPROMISO < DATEADD(SECOND,0 , DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE())))";
		else if($dia == 'MANANA')
			$sql .="AND	BC.FECHA_COMPROMISO > DATEADD(SECOND,86400 - 1, DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE())))";
		
		if($cod_usuario <> NULL)
			$sql .="AND	C.COD_USUARIO_VENDEDOR1 = $cod_usuario";	

		if($dia <> 'RECHAZADAS')
			//Se solicitó filtro de las fechas compromiso solo del 2014	(28/05/2014)
			$sql .= " AND BC.FECHA_COMPROMISO >= {ts '2014-01-01 00:00:00.000'}
				 ORDER BY BC.FECHA_COMPROMISO ASC";

		return $sql;
	}





	////////////////////////// MAIN //////////////////////
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

	// para el ADMINISTRADOR
	//Se crean las consultas para hoy, mañana, ayer, y las rechazadas para los ultimos 30 días
	$sql_hoy = make_sql("HOY");

	$sql_bitacora_hoy = $db->build_results($sql_hoy);
	$count = count($sql_bitacora_hoy);

	$sql_mañana = make_sql("MANANA");

	$sql_bitacora_mañana = $db->build_results($sql_mañana);
	$count2 = count($sql_bitacora_mañana);

	$sql_ayer = make_sql("AYER");

	$sql_bitacora_ayer = $db->build_results($sql_ayer);
	$count3 = count($sql_bitacora_ayer);

	$sql_rechazadas = make_sql("RECHAZADAS");

	$sql_bitacora_rechazadas = $db->build_results($sql_rechazadas);
	$count4 = count($sql_bitacora_rechazadas);

	//DEPENDIENDO SI ENCONTRO REGISTROS DESPLIEGA HTM
	if($count <> 0 || $count2 <> 0 || $count3 <> 0 || $count4 <> 0)
		$temp = new Template_appl('mail_bitacora_cotizacion.htm');
	else	
		$temp = new Template_appl('mail_bitacora_cotizacion_b.htm');


	//BUSCA AL USUARIO SERGIO PECHOANTE
	$sql_usuario = "SELECT	NOM_USUARIO
						,MAIL
						,CONVERT(VARCHAR,GETDATE(),103) FECHA
				FROM	USUARIO WHERE COD_USUARIO in (4)";	// Si se desea agregar mas usuarios que reciban el mail ADM agregar los cod_usuario aqui 
	$sql_usuario	= $db->build_results($sql_usuario);

	$nom_usuario = $sql_usuario[0]['NOM_USUARIO'];
	$mail_usuario = $sql_usuario[0]['MAIL'];
	$fecha = $sql_usuario[0]['FECHA'];

	$temp->setVar("NOM_USUARIO", $nom_usuario);	
	$temp->setVar("MAIL", $mail_usuario);
	$temp->setVar("FECHA", $fecha);
	$temp->setVar("COUNT", $count);
	$temp->setVar("COUNT2", $count2);
	$temp->setVar("COUNT3", $count3);
	$temp->setVar("COUNT4", $count4);

	//creacion de dw
	$dw_bitacora_Cotizacion_hoy = new datawindow($sql_hoy, "BITACORA_COTIZACION_HOY");
	$dw_bitacora_Cotizacion_hoy->add_control(new static_num('TOTAL_CON_IVA'));
	$dw_bitacora_Cotizacion_hoy->retrieve();
	$dw_bitacora_Cotizacion_hoy->habilitar($temp, false);

	$dw_bitacora_Cotizacion_mañana = new datawindow($sql_mañana, "BITACORA_COTIZACION_MAÑANA");
	$dw_bitacora_Cotizacion_mañana->add_control(new static_num('TOTAL_CON_IVA'));
	$dw_bitacora_Cotizacion_mañana->retrieve();
	$dw_bitacora_Cotizacion_mañana->habilitar($temp, false);
	
	$dw_bitacora_Cotizacion_ayer = new datawindow($sql_ayer, "BITACORA_COTIZACION_AYER");
	$dw_bitacora_Cotizacion_ayer->add_control(new static_num('TOTAL_CON_IVA'));
	$dw_bitacora_Cotizacion_ayer->retrieve();
	$dw_bitacora_Cotizacion_ayer->habilitar($temp, false);

	$dw_bitacora_Cotizacion_rechazo = new datawindow($sql_rechazadas, "BITACORA_COTIZACION_RECHAZO");
	$dw_bitacora_Cotizacion_rechazo->add_control(new static_num('TOTAL_CON_IVA'));
	$dw_bitacora_Cotizacion_rechazo->retrieve();
	$dw_bitacora_Cotizacion_rechazo->habilitar($temp, false);

	$html = $temp->toString();

	//Envio de mail SP
	$asunto = 'Bitácora seguimiento de cotizaciones al '.$fecha;

	$mail = create_mail($asunto, $db);

	$mail->AddAddress('sergio.pechoante@biggi.cl', 'SERGIO PECHOANTE');
	$mail->AddCC('rescudero@biggi.cl', 'RAFAEL ESCUDERO');
	$mail->AddCC('kverdugo@biggi.cl', 'KARINA VERDUGO');
	$mail->AddBCC('mherrera@biggi.cl', 'MARCELO HERRERA');
	$mail->AddBCC('ecastillo@biggi.cl', 'EDUARDO CASTILLO');

	
	$mail->Body = $html;
	$mail->AltBody = "";
	$mail->ContentType="text/html";
	$exito = $mail->Send();


	// Enviar mails a los vendedores, distinto a SERGIO PECHOANTE	
	$sql_tiene_bitacora="EXEC spi_tiene_bitacora"; //SPECHOANTE, ASCIANCA
				
	$result = $db->build_results($sql_tiene_bitacora);
	for ($i=0; $i<count($result); $i++) {
	
		$cod_usuario = $result[$i]['COD_USUARIO'];
	
		//Se crean las consultas para hoy, mañana, ayer filtradas por usuario
		$sql_hoy = make_sql("HOY", $cod_usuario);

		$sql_bitacora_vendedor_hoy = $db->build_results($sql_hoy);
		$count = count($sql_bitacora_vendedor_hoy);
	
		$sql_mañana = make_sql("MANANA", $cod_usuario);

		$sql_bitacora_vendedor_mañana = $db->build_results($sql_mañana);
		$count1 = count($sql_bitacora_vendedor_mañana);
	
		$sql_ayer = make_sql("AYER", $cod_usuario);
	
		$sql_bitacora_vendedor_ayer = $db->build_results($sql_ayer);
		$count2 = count($sql_bitacora_vendedor_ayer);
	
		$temp = new Template_appl('mail_cotizacion_vendedor.htm');
	
    		$sql_usuario = "SELECT	NOM_USUARIO
							,MAIL
							,CONVERT(VARCHAR,GETDATE(),103) FECHA
					FROM	USUARIO WHERE COD_USUARIO = $cod_usuario";
		$sql_usuario	= $db->build_results($sql_usuario);
	
		$nom_usuario = $sql_usuario[0]['NOM_USUARIO'];
		$mail_usuario = $sql_usuario[0]['MAIL'];
		$fecha = $sql_usuario[0]['FECHA'];
	
		$temp->setVar("NOM_USUARIO", $nom_usuario);	
		$temp->setVar("MAIL", $mail_usuario);
		$temp->setVar("FECHA", $fecha);
		$temp->setVar("COUNT", $count);
		$temp->setVar("COUNT1", $count1);
		$temp->setVar("COUNT2", $count2);
	
		//creacion de dw
		$dw_bitacora_cotizacion_hoy = new datawindow($sql_hoy, "BITACORA_COTIZACION_HOY");
		$dw_bitacora_cotizacion_hoy->add_control(new static_num('TOTAL_CON_IVA'));
		$dw_bitacora_cotizacion_hoy->retrieve();
		$dw_bitacora_cotizacion_hoy->habilitar($temp, false);
	
		$dw_bitacora_cotizacion_mañana = new datawindow($sql_mañana, "BITACORA_COTIZACION_MANANA");
		$dw_bitacora_cotizacion_mañana->add_control(new static_num('TOTAL_CON_IVA'));
		$dw_bitacora_cotizacion_mañana->retrieve();
		$dw_bitacora_cotizacion_mañana->habilitar($temp, false);
	
		$dw_bitacora_cotizacion_ayer = new datawindow($sql_ayer, "BITACORA_COTIZACION_AYER");
		$dw_bitacora_cotizacion_ayer->add_control(new static_num('TOTAL_CON_IVA'));
		$dw_bitacora_cotizacion_ayer->retrieve();
		$dw_bitacora_cotizacion_ayer->habilitar($temp, false);
	
		$html = $temp->toString();

		$para = $mail_usuario;	

    		//Envio de mail
		$asunto = 'Bitácora seguimiento de cotizaciones al '.$fecha;
		////////////////////////////////////////// envio mail vendedores
	
		$mail = create_mail($asunto, $db);
	
		// copias para HE de los usuarios RB y PV
	
		/*
		if ($cod_usuario == 11 || $cod_usuario == 17){
    		$mail->AddCC('hescudero@biggi.cl', 'Hector Escudero');
		}*/
		
		$mail->AddAddress($para, $nom_usuario);
	
		$mail->Body 		= $html;
		$mail->AltBody 	= "";
		$mail->ContentType	= "text/html";
		$exito 		= $mail->Send();

	}

		$sql_no_tiene_bitacora="SELECT COD_USUARIO 
						FROM USUARIO 
						WHERE AUTORIZA_INGRESO = 'S'
						AND COD_USUARIO in (select COD_USUARIO_VENDEDOR1 from COTIZACION
							                where FECHA_REGISTRO_COTIZACION < DATEADD(SECOND,86400 - 1, DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE())))
							                AND  FECHA_REGISTRO_COTIZACION  > DATEADD(SECOND,86400 - 1, DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE())- 2,YEAR(GETDATE()))))
					    AND COD_USUARIO not in (";
		for ($i=0; $i<count($result); $i++) {
			$sql_no_tiene_bitacora .= $result[$i]['COD_USUARIO'].","; 
		}
		//se borra la ultima comilla
		$sql_no_tiene_bitacora = trim($sql_no_tiene_bitacora,',');
		//se cierra la sub-consulta
		$sql_no_tiene_bitacora .=")";
	

		$result_sin_bitacora = $db->build_results($sql_no_tiene_bitacora);
		for ($e=0; $e<count($result_sin_bitacora); $e++) {

			$cod_usuario = $result_sin_bitacora[$e]['COD_USUARIO'];
        
    			$temp = new Template_appl('mail_bitacora_cotizacion_b.htm');
	
    			$sql_usuario_sin_bitacora = "SELECT	NOM_USUARIO
								,MAIL
								,CONVERT(VARCHAR,GETDATE(),103) FECHA
						FROM	USUARIO WHERE COD_USUARIO = $cod_usuario";
		$sql_usuario_sin_bi	= $db->build_results($sql_usuario_sin_bitacora);
	
		$nom_usuario = $sql_usuario_sin_bi[0]['NOM_USUARIO'];
		$mail_usuario = $sql_usuario_sin_bi[0]['MAIL'];
		$fecha = $sql_usuario_sin_bi[0]['FECHA'];
	
		$temp->setVar("NOM_USUARIO", $nom_usuario);	
		$temp->setVar("MAIL", $mail_usuario);
		$temp->setVar("FECHA", $fecha);
		$html = $temp->toString();

		$para = $mail_usuario;
		//Envio de mail
		$asunto = 'Bitácora seguimiento de cotizaciones al '.$fecha;
		////////////////////////////////////////// envio mail vendedores
		$mail = create_mail($asunto, $db);
	
		// copias para HE de los usuarios RB y PV
	
		/*
		if ($cod_usuario == 11 || $cod_usuario == 17){
    		$mail->AddCC('hescudero@biggi.cl', 'Hector Escudero');
		}
		*/
			
		$mail->AddAddress($para, $nom_usuario);
		
	
		$mail->Body = $html;
		$mail->AltBody = "";
		$mail->ContentType="text/html";
		$exito = $mail->Send();
    
	}

	header('Location:mail_cotizacion.htm');
?>