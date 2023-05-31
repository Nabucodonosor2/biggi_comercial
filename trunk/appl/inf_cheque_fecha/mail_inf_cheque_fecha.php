<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/class_PHPMailer.php");
include(dirname(__FILE__)."/../../appl.ini");
session::set('K_ROOT_DIR', K_ROOT_DIR);

$cod_usuario    = 1;// se fuerza a que el cod usuario sea el 1 ya que este no pasa por el login
$db             = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$datetime_hoy   = "dbo.f_makedate(day(getdate()), month(getdate()), year(getdate()))";
$fecha          = "SET LANGUAGE Spanish;
                    select CONVERT(VARCHAR, GETDATE(), 103) FECHA
                        ,CONVERT(VARCHAR, DATEADD(DAY, -2, GETDATE()), 103) FECHA_ESPECIAL_LUNES
                        ,CONVERT(VARCHAR, DATEADD(DAY, -3, GETDATE()), 103) FECHA_ESPECIAL_MARTES
                        ,CONVERT(VARCHAR, DATEADD(DAY, -4, GETDATE()), 103) FECHA_ESPECIAL_MIERCOLES
                        ,dbo.f_get_parametro(53) URL_SMTP
                        ,dbo.f_get_parametro(54) USER_SMTP
                        ,dbo.f_get_parametro(55) PASS_SMTP
                        ,dbo.f_get_parametro(71) PORT_SMTP
                        ,CASE DATEPART(dw, GETDATE()) 
                            when 1 then 'Lunes' 
                            when 2 then 'Martes' 
                            when 3 then 'Miércoles' 
                            when 4 then 'Jueves' 
                            when 5 then 'Viernes' 
                            when 6 then 'Sábado' 
                            when 7 then 'Domingo' 
                        END NOMBRE_DAY";

$result         = $db->build_results($fecha);

$fecha_actual       = $result[0]['FECHA'];
$fecha_especial1    = $result[0]['FECHA_ESPECIAL_LUNES'];
$fecha_especial2    = $result[0]['FECHA_ESPECIAL_MARTES'];
$fecha_especial3    = $result[0]['FECHA_ESPECIAL_MIERCOLES'];
$nombre_day         = $result[0]['NOMBRE_DAY'];
$host               = $result[0]['URL_SMTP'];
$Username           = $result[0]['USER_SMTP'];
$Password           = $result[0]['PASS_SMTP'];
$Port 	            = $result[0]['PORT_SMTP'];

if($nombre_day == 'Lunes')
    $fecha1     = str2date($fecha_especial1);
else if($nombre_day == 'Martes')
    $fecha1     = str2date($fecha_especial2);
else if($nombre_day == 'Miércoles')
    $fecha1     = str2date($fecha_especial3);
else
    $fecha1     = str2date($fecha_actual);

$db->query("exec spi_cheque_a_fecha $fecha1, $cod_usuario");

$temp = new Template_appl('mail_inf_cheque_fecha.htm');
$temp->setVar("NOM_DIA", $nombre_day);
$temp->setVar("FECHA_DIA", $fecha_actual);

$sql = "SELECT ORIGEN_CHEQUE
                ,COD_NOTA_VENTA + ' / ' + NOM_EMPRESA NV_CLIENTE
                ,TIPO_DOC
                ,COD_DOC
                ,CONVERT(VARCHAR, INP.FECHA_INGRESO_PAGO, 103) FECHA_DOC
                ,I.NRO_DOC NRO_CHEQUE
                ,CASE
                    WHEN DIP.FECHA_DOC <> NEW_FECHA_DOC THEN '**'+CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103)
                    ELSE CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103)
                END FECHA_CHEQUE
                ,dbo.number_format(DIP.MONTO_DOC, 0, ',', '.') MONTO_CHEQUE
                ,NEW_FECHA_DOC
        FROM INF_CHEQUE_FECHA I
            ,INGRESO_PAGO INP
            ,DOC_INGRESO_PAGO DIP
        WHERE I.COD_USUARIO = $cod_usuario
        AND ORIGEN_CHEQUE = 'Comercial' ";

if($nombre_day == 'Lunes')
    $sql .= "AND DIP.NEW_FECHA_DOC BETWEEN DATEADD(DAY, -2, $datetime_hoy) AND $datetime_hoy ";
else if($nombre_day == 'Martes')
    $sql .= "AND DIP.NEW_FECHA_DOC in (DATEADD(DAY, -3, $datetime_hoy), DATEADD(DAY, -2, $datetime_hoy), $datetime_hoy) ";
else if($nombre_day == 'Miércoles')
    $sql .= "AND DIP.NEW_FECHA_DOC in (DATEADD(DAY, -4, $datetime_hoy), DATEADD(DAY, -3, $datetime_hoy), $datetime_hoy) ";
else
    $sql .= "AND DIP.NEW_FECHA_DOC = $datetime_hoy ";

$sql .= "AND I.COD_DOC = INP.COD_INGRESO_PAGO
        AND DIP.COD_DOC_INGRESO_PAGO = I.COD_ITEM_DOC
        UNION
        SELECT ORIGEN_CHEQUE
                ,'-- / ' + NOM_EMPRESA NV_CLIENTE
                ,TIPO_DOC
                ,COD_DOC
                ,CONVERT(VARCHAR, INP.FECHA_INGRESO_PAGO, 103) FECHA_DOC
                ,I.NRO_DOC NRO_CHEQUE
                ,CASE
                    WHEN DIP.FECHA_DOC <> NEW_FECHA_DOC THEN '**'+CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103)
                    ELSE CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103)
                END FECHA_CHEQUE
                ,dbo.number_format(DIP.MONTO_DOC, 0, ',', '.') MONTO_CHEQUE
                ,NEW_FECHA_DOC
        FROM INF_CHEQUE_FECHA I
            ,RENTAL.dbo.INGRESO_PAGO INP
            ,RENTAL.dbo.DOC_INGRESO_PAGO DIP
        WHERE I.COD_USUARIO = $cod_usuario
        AND ORIGEN_CHEQUE = 'Rental' ";

if($nombre_day == 'Lunes')
    $sql .= "AND DIP.NEW_FECHA_DOC BETWEEN DATEADD(DAY, -2, $datetime_hoy) AND $datetime_hoy ";
else if($nombre_day == 'Martes')
    $sql .= "AND DIP.NEW_FECHA_DOC in (DATEADD(DAY, -3, $datetime_hoy), DATEADD(DAY, -2, $datetime_hoy), $datetime_hoy) ";
else if($nombre_day == 'Miércoles')
    $sql .= "AND DIP.NEW_FECHA_DOC in (DATEADD(DAY, -4, $datetime_hoy), DATEADD(DAY, -3, $datetime_hoy), $datetime_hoy) ";
else
    $sql .= "AND DIP.NEW_FECHA_DOC = $datetime_hoy ";

$sql .= "AND I.COD_DOC = INP.COD_INGRESO_PAGO
        AND DIP.COD_DOC_INGRESO_PAGO = I.COD_ITEM_DOC
        UNION
        SELECT ORIGEN_CHEQUE
                ,'-- / ' + E.NOM_EMPRESA NV_CLIENTE
                ,TIPO_DOC
                ,COD_DOC
                ,CONVERT(VARCHAR, IC.FECHA_INGRESO_CHEQUE, 103) FECHA_DOC
                ,C.NRO_DOC NRO_CHEQUE
                ,CONVERT(VARCHAR, C.FECHA_DOC, 103) FECHA_CHEQUE
                ,dbo.number_format(C.MONTO_DOC, 0, ',', '.') MONTO_CHEQUE
                ,C.FECHA_DOC NEW_FECHA_DOC
        FROM INF_CHEQUE_FECHA I
            ,RENTAL.dbo.INGRESO_CHEQUE IC
            ,RENTAL.dbo.CHEQUE C
            ,RENTAL.dbo.EMPRESA E
        WHERE I.COD_USUARIO = $cod_usuario
        AND ORIGEN_CHEQUE = 'Rental' ";

if($nombre_day == 'Lunes')
    $sql .= "AND C.FECHA_DOC BETWEEN DATEADD(DAY, -2, $datetime_hoy) AND $datetime_hoy ";
else if($nombre_day == 'Martes')
    $sql .= "AND C.FECHA_DOC in (DATEADD(DAY, -3, $datetime_hoy), DATEADD(DAY, -2, $datetime_hoy), $datetime_hoy) ";
else if($nombre_day == 'Miércoles')
    $sql .= "AND C.FECHA_DOC in (DATEADD(DAY, -4, $datetime_hoy), DATEADD(DAY, -3, $datetime_hoy), $datetime_hoy) ";
else
    $sql .= "AND C.FECHA_DOC = $datetime_hoy ";

$sql .= "AND I.COD_DOC = IC.COD_INGRESO_CHEQUE
        AND C.COD_CHEQUE = I.COD_ITEM_DOC
        AND IC.COD_EMPRESA = E.COD_EMPRESA
        ORDER BY NEW_FECHA_DOC ASC";

$result_tbody1 = $db->build_results($sql);

$tbody = "";
if(count($result_tbody1) > 0){
    for($i=0; $i < count($result_tbody1); $i++) { 
        $tbody .='<tbody>
                    <tr bgcolor="#f2f2f2">
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="left" valign="top">'.$result_tbody1[$i]['ORIGEN_CHEQUE'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="left" valign="top">'.$result_tbody1[$i]['NV_CLIENTE'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="left" valign="top">'.$result_tbody1[$i]['TIPO_DOC'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$result_tbody1[$i]['COD_DOC'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result_tbody1[$i]['FECHA_DOC'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$result_tbody1[$i]['NRO_CHEQUE'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result_tbody1[$i]['FECHA_CHEQUE'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$result_tbody1[$i]['MONTO_CHEQUE'].'</td>
                    </tr>
                </tbody>';
    }
}else{
    $tbody = '<tbody>
                <tr bgcolor="#f2f2f2">
                    <td colspan="8" style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">SIN DOCUMENTOS PARA HOY '.$nombre_day.' '.$fecha_actual.'</td>
                </tr>
            </tbody>';
}

$temp->setVar("TBODY_UNO", $tbody);

$sql = "SELECT ORIGEN_CHEQUE
                ,COD_NOTA_VENTA + ' / ' + NOM_EMPRESA NV_CLIENTE
                ,I.COD_DOC
                ,CONVERT(VARCHAR, INP.FECHA_INGRESO_PAGO, 103) FECHA_DOC
                ,I.NRO_DOC NRO_CHEQUE
                ,CASE
                    WHEN DIP.FECHA_DOC <> NEW_FECHA_DOC THEN '**'+CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103)
                    ELSE CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103)
                END FECHA_CHEQUE
                ,dbo.number_format(DIP.MONTO_DOC, 0, ',', '.') MONTO_CHEQUE
                ,DIP.NEW_FECHA_DOC
        FROM INF_CHEQUE_FECHA I
        ,INGRESO_PAGO INP
        ,DOC_INGRESO_PAGO DIP
        WHERE I.COD_USUARIO = $cod_usuario
        AND ORIGEN_CHEQUE = 'Comercial'
        AND DIP.NEW_FECHA_DOC > $datetime_hoy
        AND I.COD_DOC = INP.COD_INGRESO_PAGO
        AND DIP.COD_DOC_INGRESO_PAGO = I.COD_ITEM_DOC
        ORDER BY DIP.NEW_FECHA_DOC ASC";

$result_tbody2 = $db->build_results($sql);
$tbody = "";
if(count($result_tbody2) > 0){
    for($j=0; $j < count($result_tbody2); $j++) { 
        $tbody .='<tbody>
                    <tr bgcolor="#f2f2f2">
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="left" valign="top">'.$result_tbody2[$j]['NV_CLIENTE'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$result_tbody2[$j]['COD_DOC'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result_tbody2[$j]['FECHA_DOC'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$result_tbody2[$j]['NRO_CHEQUE'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result_tbody2[$j]['FECHA_CHEQUE'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$result_tbody2[$j]['MONTO_CHEQUE'].'</td>
                    </tr>
                </tbody>';
    }
}else{
    $tbody = '<tbody>
                <tr bgcolor="#f2f2f2">
                    <td colspan="6" style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">SIN DOCUMENTOS PARA LOS SIGUIENTES DIAS</td>
                </tr>
            </tbody>';
}

$temp->setVar("TBODY_DOS", $tbody);

$sql = "SELECT ORIGEN_CHEQUE
            ,NOM_EMPRESA NV_CLIENTE
            ,I.COD_DOC
            ,TIPO_DOC
            ,CONVERT(VARCHAR, INP.FECHA_INGRESO_PAGO, 103) FECHA_DOC
            ,I.NRO_DOC NRO_CHEQUE
            ,CASE
                WHEN DIP.FECHA_DOC <> NEW_FECHA_DOC THEN '**'+CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103)
                ELSE CONVERT(VARCHAR, DIP.NEW_FECHA_DOC, 103)
            END FECHA_CHEQUE
            ,dbo.number_format(DIP.MONTO_DOC, 0, ',', '.') MONTO_CHEQUE
            ,DIP.NEW_FECHA_DOC
        FROM INF_CHEQUE_FECHA I
            ,RENTAL.dbo.INGRESO_PAGO INP
            ,RENTAL.dbo.DOC_INGRESO_PAGO DIP
        WHERE I.COD_USUARIO = $cod_usuario
        AND ORIGEN_CHEQUE = 'Rental'
        AND DIP.NEW_FECHA_DOC BETWEEN DATEADD(DAY, 1, $datetime_hoy) AND DATEADD(DAY, 31, $datetime_hoy)
        AND I.COD_DOC = INP.COD_INGRESO_PAGO
        AND DIP.COD_DOC_INGRESO_PAGO = I.COD_ITEM_DOC
        UNION
        SELECT ORIGEN_CHEQUE
            ,NOM_EMPRESA NV_CLIENTE
            ,I.COD_DOC
            ,TIPO_DOC
            ,CONVERT(VARCHAR, IC.FECHA_INGRESO_CHEQUE, 103) FECHA_DOC
            ,I.NRO_DOC NRO_CHEQUE
            ,CONVERT(VARCHAR, C.FECHA_DOC, 103) FECHA_CHEQUE
            ,dbo.number_format(C.MONTO_DOC, 0, ',', '.') MONTO_CHEQUE
            ,C.FECHA_DOC NEW_FECHA_DOC
        FROM INF_CHEQUE_FECHA I
            ,RENTAL.dbo.INGRESO_CHEQUE IC
            ,RENTAL.dbo.CHEQUE C
        WHERE I.COD_USUARIO = $cod_usuario
        AND ORIGEN_CHEQUE = 'Rental'
        AND C.FECHA_DOC BETWEEN DATEADD(DAY, 1, $datetime_hoy) AND DATEADD(DAY, 31, $datetime_hoy)
        AND I.COD_DOC = IC.COD_INGRESO_CHEQUE
        AND C.COD_CHEQUE = I.COD_ITEM_DOC
        ORDER BY NEW_FECHA_DOC ASC";

$result_tbody3 = $db->build_results($sql);
$tbody = "";
if(count($result_tbody3) > 0){
    for($j=0; $j < count($result_tbody3); $j++) { 
        $tbody .='<tbody>
                    <tr bgcolor="#f2f2f2">
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="left" valign="top">'.$result_tbody3[$j]['NV_CLIENTE'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$result_tbody3[$j]['TIPO_DOC'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$result_tbody3[$j]['COD_DOC'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result_tbody3[$j]['FECHA_DOC'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$result_tbody3[$j]['NRO_CHEQUE'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result_tbody3[$j]['FECHA_CHEQUE'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$result_tbody3[$j]['MONTO_CHEQUE'].'</td>
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

$temp->setVar("TBODY_TRES", $tbody);

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