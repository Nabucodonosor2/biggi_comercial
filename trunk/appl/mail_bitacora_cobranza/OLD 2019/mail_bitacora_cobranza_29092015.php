<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/class_PHPMailer.php");	
include(dirname(__FILE__)."/../../appl.ini");
session::set('K_ROOT_DIR', K_ROOT_DIR);

ini_set('max_execution_time', 900); //900 seconds = 15 minutes 

function make_sql($dia){
	$sql = "SELECT	F.NRO_FACTURA
						,convert(varchar, F.FECHA_FACTURA, 103) FECHA
						,E.NOM_EMPRESA
						,F.TOTAL_CON_IVA
						,BF.GLOSA_COMPROMISO
						,BF.CONTACTO
						,' / F: '+BF.TELEFONO FONO
			FROM BITACORA_FACTURA BF left outer join USUARIO U2 on U2.COD_USUARIO = BF.COD_USUARIO_REALIZADO, USUARIO U1, FACTURA F, EMPRESA E
			WHERE BF.COD_FACTURA = F.COD_FACTURA
			AND E.COD_EMPRESA = F.COD_EMPRESA
			AND BF.COD_USUARIO = U1.COD_USUARIO
			AND	BF.TIENE_COMPROMISO = 'S'
			AND	(BF.COMPROMISO_REALIZADO =  'N' or BF.COMPROMISO_REALIZADO is null)";
	
	if($dia == 'HOY'){
		$sql .=" AND BF.FECHA_COMPROMISO <= DATEADD(SECOND,86400 - 1, DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE())))
				AND BF.FECHA_COMPROMISO >= DATEADD(SECOND,0 , DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE())))";
	}else if($dia == 'AYER')
		$sql .=" AND	BF.FECHA_COMPROMISO < DATEADD(SECOND,0 , DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE())))";
	else if($dia == 'MANANA')
		$sql .=" AND	BF.FECHA_COMPROMISO > DATEADD(SECOND,86400 - 1, DBO.F_MAKEDATE(DAY(GETDATE()),MONTH(GETDATE()),YEAR(GETDATE())))";
		
	return $sql;	
}

//usuario a quien se le enviará la bitacora
$K_COD_USUARIO = 4;
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = make_sql('HOY');echo $sql.'<br><br>';
$sql_bitacora_hoy	= $db->build_results($sql);
$count_hoy = count($sql_bitacora_hoy);

$dw_bitacora_factura_hoy = new datawindow($sql, "BITACORA_FACTURA_HOY");
$dw_bitacora_factura_hoy->add_control(new static_num('TOTAL_CON_IVA'));
$dw_bitacora_factura_hoy->retrieve();

$sql = make_sql('AYER');echo $sql.'<br><br>';
$sql_bitacora_ayer	= $db->build_results($sql);
$count_ayer = count($sql_bitacora_ayer);

$dw_bitacora_factura_ayer = new datawindow($sql, "BITACORA_FACTURA_AYER");
$dw_bitacora_factura_ayer->add_control(new static_num('TOTAL_CON_IVA'));
$dw_bitacora_factura_ayer->retrieve();

$sql = make_sql('MANANA');echo $sql.'<br><br>';
$sql_bitacora_manana = $db->build_results($sql);
$count_manana = count($sql_bitacora_manana);

$dw_bitacora_factura_manana = new datawindow($sql, "BITACORA_FACTURA_MANANA");
$dw_bitacora_factura_manana->add_control(new static_num('TOTAL_CON_IVA'));
$dw_bitacora_factura_manana->retrieve();

if($count_hoy <> 0 || $count_ayer <> 0 || $count_manana <> 0)
	$temp = new Template_appl('mail_bitacora_cobranza.htm');
else	
	$temp = new Template_appl('mail_bitacora_cobranza_b.htm');

$dw_bitacora_factura_hoy->habilitar($temp, false);
$dw_bitacora_factura_ayer->habilitar($temp, false);
$dw_bitacora_factura_manana->habilitar($temp, false);

$sql_usuario = "SELECT	NOM_USUARIO
						,MAIL
						,convert(varchar, getdate(), 103) FECHA
				FROM	usuario WHERE cod_usuario = $K_COD_USUARIO";
$sql_usuario	= $db->build_results($sql_usuario);

$nom_usuario = $sql_usuario[0]['NOM_USUARIO'];
$mail_usuario = $sql_usuario[0]['MAIL'];
$fecha = $sql_usuario[0]['FECHA'];

$temp->setVar("NOM_USUARIO", $nom_usuario);	
$temp->setVar("MAIL", $mail_usuario);
$temp->setVar("FECHA", $fecha);
$temp->setVar("COUNT_HOY", $count_hoy);
$temp->setVar("COUNT_AYER", $count_ayer);
$temp->setVar("COUNT_MANANA", $count_manana);

$html = $temp->toString();

$para = $mail_usuario.', rescudero@biggi.cl, cscianca@biggi.cl, kverdugo@biggi.cl';

    //Envio de mail

// USA LAS CABECERAS ???	
$asunto = 'Bitácora seguimiento de cobranzas al '.$fecha;

/// Inicio MH regulariza el 24/06/2013

	$K_host = 53;
	$K_Username = 54;
	$K_Password = 55;
	$sql_host = "SELECT VALOR
		      FROM PARAMETRO 
		      WHERE COD_PARAMETRO =$K_host
		      OR COD_PARAMETRO =$K_Username
		      OR COD_PARAMETRO =$K_Password";

	$result_host = $db->build_results($sql_host);

	$host = 	$result_host[0]['VALOR'];
	$Username = $result_host[1]['VALOR'];
	$Password = $result_host[2]['VALOR'];

	$mail = new phpmailer();
	$mail->PluginDir = dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/";
	$mail->Mailer 	= "smtp";
	$mail->SMTPAuth = true;
	$mail->Host 	= "$host";
	$mail->Username = "$Username";
	$mail->Password = "$Password"; 
	$mail->From 	="soporte@biggi.cl";		
	$mail->FromName = "Comercial Biggi S.A.";
	$mail->Timeout=30;
	$mail->Subject = $asunto;

	$mail->ClearAddresses();
	
	$mail->AddAddress($mail_usuario, $nom_usuario);
	$mail->AddCC('rescudero@biggi.cl', 'Rafael Escudero');	
	$mail->AddCC('kverdugo@biggi.cl', 'Karina Verdugo');
	$mail->AddCC('cscianca@biggi.cl', 'Claudia Scianca');
	
	$mail->AddBCC('mherrera@biggi.cl', 'Marcelo Herrera');	
	$mail->AddBCC('ecastillo@biggi.cl', 'Eduardo Castillo');			

	$mail->Body = $html;
	$mail->AltBody = "";
	$mail->ContentType="text/html";
	$exito = $mail->Send();
	
/// Fin MH regulariza el 24/06/2013		

// La siguiente linea debiese borrarse
// mail($para, $asunto, $html, $cabeceras);

header('Location:mail_cobranza.htm');
?>