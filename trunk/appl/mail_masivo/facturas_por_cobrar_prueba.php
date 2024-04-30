<?php
	require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
	require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/class_PHPMailer.php");
	include(dirname(__FILE__)."/../../appl.ini");
	session::set('K_ROOT_DIR', K_ROOT_DIR);

	ini_set('max_execution_time', 900); //900 seconds = 15 minutes 
	
	$K_ESTADO_SII_IMPRESA 	= 2;
	$K_ESTADO_SII_ENVIADA	= 3;

	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

	//select me trae todos lo usuarios con saldo en factura
	$sql	=	"SELECT DISTINCT F.COD_USUARIO_VENDEDOR1 COD_USUARIO
						,U.NOM_USUARIO
						,U.MAIL
				FROM	FACTURA F, USUARIO U
				WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
				AND		F.COD_ESTADO_DOC_SII in (".$K_ESTADO_SII_IMPRESA.", ".$K_ESTADO_SII_ENVIADA.")
				AND		U.COD_USUARIO = COD_USUARIO_VENDEDOR1
				ORDER BY COD_USUARIO_VENDEDOR1 asc";
	$sql_cod_vendedor	= $db->build_results($sql);
	
	for($i=0;$i < count($sql_cod_vendedor); $i++) {
		$cod_vendedor = $sql_cod_vendedor[$i]['COD_USUARIO'];
		$nom_vendedor = $sql_cod_vendedor[$i]['NOM_USUARIO'];
		$mail_vendedor = $sql_cod_vendedor[$i]['MAIL'];
		
		//creando el template enviando nombre y cod_usuario
		$temp = new Template_appl('facturas_por_cobrar.htm');
		$temp->setVar("NOM_VENDEDOR", $nom_vendedor);		
		
		// Calculando el total de sus facturas con saldos
		$sql_total	= 	"SELECT sum(dbo.f_fa_saldo(F.COD_FACTURA)) TOTAL
								FROM	FACTURA F
								WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
								AND		F.COD_ESTADO_DOC_SII in (".$K_ESTADO_SII_IMPRESA.", ".$K_ESTADO_SII_ENVIADA.")
								AND		F.COD_USUARIO_VENDEDOR1 = $cod_vendedor";
		$total_fa	= $db->build_results($sql_total);
		$total_fa	= $total_fa[0]['TOTAL'];
	
		$temp->setVar("TOTAL_SALDO", number_format($total_fa, 0, ',','.'));
		
        //ENVIANDO DATOS AL HTML. TODAS LAS FACTURAS CON SALDO MAS DE 60 DÍAS
		$sql_60_dias = "SELECT F.NRO_FACTURA
                                ,dbo.f_format_date(F.FECHA_FACTURA, 1) FECHA_FACTURA
                                ,F.NOM_EMPRESA
                                ,dbo.f_fa_saldo(F.COD_FACTURA) MONTO
                                ,dbo.f_fa_saldo(F.COD_FACTURA) * 100/$total_fa PORC
                                ,F.COD_DOC
                        FROM	FACTURA F
                        WHERE	DATEDIFF(DAY, F.FECHA_FACTURA, GETDATE()) > 60
                        AND		dbo.f_fa_saldo(F.COD_FACTURA) > 0
                        AND		F.COD_ESTADO_DOC_SII in (".$K_ESTADO_SII_IMPRESA.", ".$K_ESTADO_SII_ENVIADA.")
                        AND		F.COD_USUARIO_VENDEDOR1 = $cod_vendedor
                        ORDER BY F.FECHA_FACTURA ASC";	
        $dw_60_dias = new datawindow($sql_60_dias, "FA_60_DIAS");
        $dw_60_dias->add_control(new static_num('MONTO'));
        $dw_60_dias->add_control(new static_num('PORC'));
        $dw_60_dias->retrieve();

        //ENVIANDO DATOS AL HTML. TODAS LAS FACTURAS CON SALDO 30 DIAS Y MENOS DE 60
		$sql_30_60_dias =  "SELECT F.NRO_FACTURA
                                    ,dbo.f_format_date(F.FECHA_FACTURA, 1) FECHA_FACTURA
                                    ,F.NOM_EMPRESA
                                    ,dbo.f_fa_saldo(F.COD_FACTURA) MONTO
                                    ,dbo.f_fa_saldo(F.COD_FACTURA) * 100/$total_fa PORC
                                    ,F.COD_DOC
                            FROM	FACTURA F
                            WHERE	DATEDIFF(DAY, F.FECHA_FACTURA, GETDATE()) BETWEEN 31 AND 59
                            AND		dbo.f_fa_saldo(F.COD_FACTURA) > 0
                            AND		F.COD_ESTADO_DOC_SII in (".$K_ESTADO_SII_IMPRESA.", ".$K_ESTADO_SII_ENVIADA.")
                            AND		F.COD_USUARIO_VENDEDOR1 = $cod_vendedor
                            ORDER BY F.FECHA_FACTURA ASC";	
        $dw_30_60_dias = new datawindow($sql_30_60_dias, "FA_30_60_DIAS");
        $dw_30_60_dias->add_control(new static_num('MONTO'));
        $dw_30_60_dias->add_control(new static_num('PORC'));
        $dw_30_60_dias->retrieve();

        //ENVIANDO DATOS AL HTML. TODAS LAS FACTURAS CON SALDO MENOS DE 30 DIAS
		$sql_30_dias = "SELECT F.NRO_FACTURA
                                ,dbo.f_format_date(F.FECHA_FACTURA, 1) FECHA_FACTURA
                                ,F.NOM_EMPRESA
                                ,dbo.f_fa_saldo(F.COD_FACTURA) MONTO
                                ,dbo.f_fa_saldo(F.COD_FACTURA) * 100/$total_fa PORC
                                ,F.COD_DOC
                        FROM	FACTURA F
                        WHERE	DATEDIFF(DAY, F.FECHA_FACTURA, GETDATE()) < 30
                        AND		dbo.f_fa_saldo(F.COD_FACTURA) > 0
                        AND		F.COD_ESTADO_DOC_SII in (".$K_ESTADO_SII_IMPRESA.", ".$K_ESTADO_SII_ENVIADA.")
                        AND		F.COD_USUARIO_VENDEDOR1 = $cod_vendedor
                        ORDER BY F.FECHA_FACTURA ASC";

        $dw_30_dias = new datawindow($sql_30_dias, "FA_30_DIAS");
        $dw_30_dias->add_control(new static_num('MONTO'));
        $dw_30_dias->add_control(new static_num('PORC'));
        $dw_30_dias->retrieve();

		//ENVIANDO DATOS AL HTML. TODAS LAS FACTURAS CON SALDO
		$sql_antigua = "SELECT F.NRO_FACTURA
								,dbo.f_format_date(F.FECHA_FACTURA, 1) FECHA_FACTURA
                                ,F.COD_DOC
								,F.NOM_EMPRESA
								,dbo.f_fa_saldo(F.COD_FACTURA) MONTO
								,dbo.f_fa_saldo(F.COD_FACTURA) * 100/$total_fa PORC
						FROM	FACTURA F
						WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
						AND		F.COD_ESTADO_DOC_SII in (".$K_ESTADO_SII_IMPRESA.", ".$K_ESTADO_SII_ENVIADA.")
						AND		F.COD_USUARIO_VENDEDOR1 = $cod_vendedor
						ORDER BY F.FECHA_FACTURA ASC";	
		$dw_fa_antigua = new datawindow($sql_antigua, "FA_ANTIGUA");
		$dw_fa_antigua->add_control(new static_num('MONTO'));
		$dw_fa_antigua->add_control(new static_num('PORC'));
		$dw_fa_antigua->retrieve();

		// habilitando el template
        $dw_60_dias->habilitar($temp, false);
        $dw_30_60_dias->habilitar($temp, false);
        $dw_30_dias->habilitar($temp, false);
		$dw_fa_antigua->habilitar($temp, false);
		$html = $temp->toString();

        // Envio de mail
        $asunto = ' Facturas por cobrar '.$nom_vendedor;

	    /// Inicio MH regulariza el 24/06/2013
		$K_host = 53;
		$K_Username = 54;
		$K_Password = 55;
			      
		$sql = "select dbo.f_get_parametro(53) 		URL_SMTP
				,dbo.f_get_parametro(54) 	USER_SMTP
				,dbo.f_get_parametro(55) 	PASS_SMTP
				,dbo.f_get_parametro(71) 	PORT_SMTP";
		$result = $db->build_results($sql);
		
		$host     = $result[0]['URL_SMTP'];
		$Username = $result[0]['USER_SMTP'];
		$Password = $result[0]['PASS_SMTP'];
		$Port 	  = $result[0]['PORT_SMTP'];

		$mail = new phpmailer();
		$mail->PluginDir = dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/";
		$mail->Mailer 	= "smtp";
		$mail->SMTPAuth = true;
		$mail->Host 	= "$host";
		$mail->Username = "$Username";
		$mail->Password = "$Password";
		$mail->Port = "$Port";
		$mail->SMTPSecure= 'ssl';
		/*$mail->From 	= "sergio.pechoante@biggi.cl";		
		$mail->FromName = "Sergio Pechoante";*/
        $mail->From         = "modulo_alertas@biggi.cl";		
        $mail->FromName     = "Módulo Alertas Grupo BIGGI";
		$mail->Timeout=30;
		$mail->Subject = $asunto;

		$mail->ClearAddresses();
		
        $mail->AddAddress('isra.campos.o@gmail.com', 'PRUEBA');
        $mail->AddAddress('mherrera@biggi.cl', 'Marcelo Herrera');
		/*$mail->AddAddress($mail_vendedor, $nom_vendedor);
		
		$mail->AddAddress('ascianca@biggi.cl', 'Angel Scianca');
		$mail->AddAddress('sergio.pechoante@biggi.cl', 'Sergio Pechoante');
		$mail->AddAddress('jcatalan@biggi.cl', 'José Catalan');
		
		$mail->AddBCC('mherrera@biggi.cl', 'Marcelo Herrera');*/	
		
		$mail->Body = $html;
		$mail->AltBody = "";
		$mail->ContentType="text/html";
		
		$exito = $mail->Send();

		if(!$exito){
			echo "Problema al enviar correo electrónico a ".$result[$i]['MAIL'];
		}else
            echo 'Correo enviado <br>';
	    /// Fin MH regulariza el 24/06/2013
	
	}
	//header('Location:mail_masivo.htm');
?>