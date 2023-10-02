<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/class_PHPMailer.php");
include(dirname(__FILE__)."/../../appl.ini");
session::set('K_ROOT_DIR', K_ROOT_DIR);

$temp = new Template_appl('mail_modifica_precio_producto.htm');

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql_param = "select dbo.f_get_parametro(53) URL_SMTP
                    ,dbo.f_get_parametro(54) USER_SMTP
                    ,dbo.f_get_parametro(55) PASS_SMTP
                    ,dbo.f_get_parametro(71) PORT_SMTP
                    ,CONVERT(VARCHAR, GETDATE(), 103) FECHA_HOY";
$result_param = $db->build_results($sql_param);

$host           = $result_param[0]['URL_SMTP'];
$Username       = $result_param[0]['USER_SMTP'];
$Password       = $result_param[0]['PASS_SMTP'];
$Port 	        = $result_param[0]['PORT_SMTP'];
$fecha_hoy      = $result_param[0]['FECHA_HOY'];

$db->query("exec spdw_modifica_precio_producto 'UPDATE'");
$sql = "select T_COD_LOG_CAMBIO
            ,CONVERT(VARCHAR, T_FECHA_CAMBIO, 103) T_FECHA_CAMBIO
            ,T_NOM_USUARIO
            ,T_PRODUCTO
            ,T_VALOR_ANTIGUO
            ,T_VALOR_NUEVO
            ,T_ORIGEN
        from DETALLE_PRODUCTO_CAMBIO";

$result = $db->build_results($sql);
$tbody = '';

for ($i=0; $i < count($result); $i++){
    $valor_antiguo  = ($result[$i]['T_VALOR_ANTIGUO']=='') ? 0 : number_format($result[$i]['T_VALOR_ANTIGUO'], 0, ',', '.');
    $valor_nuevo    = ($result[$i]['T_VALOR_NUEVO']=='') ? 0 : number_format($result[$i]['T_VALOR_NUEVO'], 0, ',', '.');

    $tbody .='<tbody>
                    <tr bgcolor="#f2f2f2">
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result[$i]['T_COD_LOG_CAMBIO'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result[$i]['T_FECHA_CAMBIO'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="left" valign="top">'.$result[$i]['T_NOM_USUARIO'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result[$i]['T_PRODUCTO'].'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$valor_antiguo.'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="right" valign="top">'.$valor_nuevo.'</td>
                        <td style="line-height: 24px; font-size: 16px; margin: 0; padding: 12px; border: 1px solid #e2e8f0;" align="center" valign="top">'.$result[$i]['T_ORIGEN'].'</td>
                    </tr>
              </tbody>'; 
}
$temp->setVar("TBODY", $tbody);
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
$mail->Subject      = "Informe cambio de precios equipos BIGGI al $fecha_hoy";
$mail->ClearAddresses();

$mail->AddAddress('fabiola@12del9.cl','Fabiola Scianca');
$mail->AddAddress('adiaz@biggi.cl','Ariel Diaz');
$mail->AddAddress('mherrera@biggi.cl','Marcelo Herrera');

//$mail->AddEmbeddedImage("../../images_appl/logobiggipo.jpg",'logo_biggi');
$mail->Body         = $html;
$mail->AltBody      = "";
$mail->ContentType  ="text/html";

$exito = $mail->Send();

if(!$exito)
    echo "Problema al enviar correo electrónico";
else
    echo "Se ha enviado con exito";
?>