<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/class_PHPMailer.php");
include(dirname(__FILE__)."/../../appl.ini");
session::set('K_ROOT_DIR', K_ROOT_DIR);

$cod_usuario    = 1;// se fuerza a que el cod usuario sea el 1 ya queeste no pasa por el login
$db             = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$fecha          = "select convert (varchar ,dbo.f_makedate(day(getdate()), month(getdate()), year(getdate())),103) FECHA
                        ,dbo.f_get_parametro(53) URL_SMTP
                        ,dbo.f_get_parametro(54) USER_SMTP
                        ,dbo.f_get_parametro(55) PASS_SMTP
                        ,dbo.f_get_parametro(71) PORT_SMTP
                        ,CASE DATEPART(dw, GETDATE()) 
                            when 2 then 'Lunes' 
                            when 3 then 'Martes' 
                            when 4 then 'Miércoles' 
                            when 5 then 'Jueves' 
                            when 6 then 'Viernes' 
                            when 7 then 'Sábado' 
                            when 1 then 'Domingo' 
                        END NOMBRE_DAY";

$result         = $db->build_results($fecha);

$fecha_actual   = $result[0]['FECHA'];
$nombre_day     = $result[0]['NOMBRE_DAY'];
$host           = $result[0]['URL_SMTP'];
$Username       = $result[0]['USER_SMTP'];
$Password       = $result[0]['PASS_SMTP'];
$Port 	        = $result[0]['PORT_SMTP'];

$fecha1         = str2date($fecha_actual);

$db->query("exec spi_cheque_a_fecha $fecha1, $cod_usuario");

$temp = new Template_appl('mail_inf_cheque_fecha.htm');
$temp->setVar("NOM_DIA", $nombre_day);
$temp->setVar("FECHA_DIA", $fecha_actual);

$sql = "SELECT ORIGEN_CHEQUE
                ,COD_NOTA_VENTA + ' / ' + NOM_EMPRESA NV_CLIENTE
                ,I.COD_INGRESO_PAGO
                ,CONVERT(VARCHAR, INP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
                ,I.NRO_DOC
                ,CASE
                    WHEN DIP.FECHA_DOC <> NEW_FECHA_DOC THEN '**'+CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103)
                    ELSE CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103)
                END FECHA_DOC
                ,dbo.number_format(DIP.MONTO_DOC, 0, ',', '.') MONTO_DOC
        FROM INF_CHEQUE_FECHA I
        ,INGRESO_PAGO INP
        ,DOC_INGRESO_PAGO DIP
        WHERE I.COD_USUARIO = $cod_usuario
        AND ORIGEN_CHEQUE = 'Comercial'
        AND CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103) = CONVERT(VARCHAR, GETDATE(), 103)
        AND I.COD_INGRESO_PAGO = INP.COD_INGRESO_PAGO
        AND DIP.COD_DOC_INGRESO_PAGO = I.COD_DOC_INGRESO_PAGO
        UNION
        SELECT ORIGEN_CHEQUE
                ,'-- / ' + NOM_EMPRESA NV_CLIENTE
                ,I.COD_INGRESO_PAGO
                ,CONVERT(VARCHAR, INP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
                ,I.NRO_DOC
                ,CASE
                    WHEN DIP.FECHA_DOC <> NEW_FECHA_DOC THEN '**'+CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103)
                    ELSE CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103)
                END FECHA_DOC
                ,dbo.number_format(DIP.MONTO_DOC, 0, ',', '.') MONTO_DOC
        FROM INF_CHEQUE_FECHA I
        ,RENTAL.dbo.INGRESO_PAGO INP
        ,RENTAL.dbo.DOC_INGRESO_PAGO DIP
        WHERE I.COD_USUARIO = $cod_usuario
        AND ORIGEN_CHEQUE = 'Rental'
        AND CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103) = CONVERT(VARCHAR, GETDATE(), 103)
        AND I.COD_INGRESO_PAGO = INP.COD_INGRESO_PAGO
        AND DIP.COD_DOC_INGRESO_PAGO = I.COD_DOC_INGRESO_PAGO
        ORDER BY FECHA_DOC ASC";

$result_tbody1 = $db->build_results($sql);

$tbody = "";
if(count($result_tbody1) > 0){
    for($i=0; $i < count($result_tbody1); $i++) { 
        $tbody .='<tbody>
                    <tr bgcolor="#f2f2f2">
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="left" valign="top">'.$result_tbody1[$i]['ORIGEN_CHEQUE'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="left" valign="top">'.$result_tbody1[$i]['NV_CLIENTE'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$result_tbody1[$i]['COD_INGRESO_PAGO'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result_tbody1[$i]['FECHA_INGRESO_PAGO'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$result_tbody1[$i]['NRO_DOC'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result_tbody1[$i]['FECHA_DOC'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$result_tbody1[$i]['MONTO_DOC'].'</td>
                    </tr>
                </tbody>';
    }
}else{
    $tbody = '<tbody>
                <tr bgcolor="#f2f2f2">
                    <td colspan="7" style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">SIN DOCUMENTOS PARA HOY '.$nombre_day.' '.$fecha_actual.'</td>
                </tr>
            </tbody>';
}

$temp->setVar("TBODY_UNO", $tbody);

$sql = "SELECT ORIGEN_CHEQUE
                ,COD_NOTA_VENTA + ' / ' + NOM_EMPRESA NV_CLIENTE
                ,I.COD_INGRESO_PAGO
                ,CONVERT(VARCHAR, INP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
                ,I.NRO_DOC
                ,CASE
                    WHEN DIP.FECHA_DOC <> NEW_FECHA_DOC THEN '**'+CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103)
                    ELSE CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103)
                END FECHA_DOC
                ,dbo.number_format(DIP.MONTO_DOC, 0, ',', '.') MONTO_DOC
        FROM INF_CHEQUE_FECHA I
        ,INGRESO_PAGO INP
        ,DOC_INGRESO_PAGO DIP
        WHERE I.COD_USUARIO = $cod_usuario
        AND ORIGEN_CHEQUE = 'Comercial'
        AND CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103) > CONVERT(VARCHAR, GETDATE(), 103)
        AND I.COD_INGRESO_PAGO = INP.COD_INGRESO_PAGO
        AND DIP.COD_DOC_INGRESO_PAGO = I.COD_DOC_INGRESO_PAGO
        UNION
        SELECT ORIGEN_CHEQUE
                ,'-- / ' + NOM_EMPRESA NV_CLIENTE
                ,I.COD_INGRESO_PAGO
                ,CONVERT(VARCHAR, INP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
                ,I.NRO_DOC
                ,CASE
                    WHEN DIP.FECHA_DOC <> NEW_FECHA_DOC THEN '**'+CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103)
                    ELSE CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103)
                END FECHA_DOC
                ,dbo.number_format(DIP.MONTO_DOC, 0, ',', '.') MONTO_DOC
        FROM INF_CHEQUE_FECHA I
        ,RENTAL.dbo.INGRESO_PAGO INP
        ,RENTAL.dbo.DOC_INGRESO_PAGO DIP
        WHERE I.COD_USUARIO = $cod_usuario
        AND ORIGEN_CHEQUE = 'Rental'
        AND CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103) > CONVERT(VARCHAR, GETDATE(), 103)
        AND I.COD_INGRESO_PAGO = INP.COD_INGRESO_PAGO
        AND DIP.COD_DOC_INGRESO_PAGO = I.COD_DOC_INGRESO_PAGO
        ORDER BY FECHA_DOC ASC";

$result_tbody2 = $db->build_results($sql);
$tbody = "";
if(count($result_tbody2) > 0){
    for($j=0; $j < count($result_tbody2); $j++) { 
        $tbody .='<tbody>
                    <tr bgcolor="#f2f2f2">
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="left" valign="top">'.$result_tbody2[$j]['ORIGEN_CHEQUE'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="left" valign="top">'.$result_tbody2[$j]['NV_CLIENTE'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$result_tbody2[$j]['COD_INGRESO_PAGO'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result_tbody2[$j]['FECHA_INGRESO_PAGO'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$result_tbody2[$j]['NRO_DOC'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result_tbody2[$j]['FECHA_DOC'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$result_tbody2[$j]['MONTO_DOC'].'</td>
                    </tr>
                </tbody>';
    }
}else{
    $tbody = '<tbody>
                <tr bgcolor="#f2f2f2">
                    <td colspan="7" style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">SIN DOCUMENTOS PARA LOS SIGUIENTES DIAS</td>
                </tr>
            </tbody>';
}

$temp->setVar("TBODY_DOS", $tbody);
$html = $temp->toString();
            
$mail               = new phpmailer();
$mail->PluginDir    = dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/";
$mail->Mailer       = "smtp";
$mail->SMTPAuth     = true;
$mail->Host         = "$host";
$mail->Username     = "$Username";
$mail->Password     = "$Password";
$mail->Port         = "$Port";
$mail->SMTPSecure   = 'ssl';
$mail->From         = "modulo_alertas@biggi.cl";		
$mail->FromName     = "Módulo Alertas Grupo BIGGI";
$mail->Timeout      = 30;
$mail->Subject      = "Informe cheques en cartera al $fecha_actual";
$mail->ClearAddresses();

$mail->AddAddress('sergio.pechoante@biggi.cl', 'Sergio Pechoante');
$mail->AddAddress('jcatalan@biggi.cl', 'José Catalán');
$mail->AddBCC('mherrera@biggi.cl','Marcelo Herrera');

//$mail->AddEmbeddedImage("../../images_appl/logobiggipo.jpg",'logo_biggi');
$mail->Body         = $html;
$mail->AltBody      = "";
$mail->ContentType  ="text/html";

$exito = $mail->Send();

if(!$exito)
    echo "Problema al enviar correo electrónico";
else
    echo "Se ha enviado con exito";


function str2date($fecha_str, $hora_str='00:00:00') {
	if ($fecha_str=='')
		return 'null';
	// Entra la fecha en formato dd/mm/yyyy		
	if (K_TIPO_BD=='mssql') {
		$res = explode('/', $fecha_str);
		if (strlen($res[2])==2)
			$res[2] = '20'.$res[2];
		return sprintf("{ts '$res[2]-$res[1]-$res[0] $hora_str.000'}");
	}
	else if (K_TIPO_BD=='oci')
		return "to_date('$fecha_str $hora_str', 'dd/mm/yyyy hh24:mi:ss')";
	else
		base::error("base.str2date, no soportado para ".K_TIPO_BD);
}
?>