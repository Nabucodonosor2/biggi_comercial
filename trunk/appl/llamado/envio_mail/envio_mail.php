<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/clases/class_PHPMailer.php");
include("funciones.php");

ini_set("display_errors", "off");
if (file_exists('fetched.dat')) {
    del_list('fetched.dat');
}

$cod_llamado = $_REQUEST["COD_LLAMADO_H"];
$cod_destinatario = $_REQUEST["COD_DESTINATARIO_H"]; //quien envia el mail
$cod_destinatario_resp = $cod_destinatario; //quien envia el mail
$cod_destinatario_envio = $_REQUEST["COD_DESTINATARIO_ENVIO_H"]; //a quien se envia el mail
$cod_usuario_resp = $_REQUEST["COD_USUARIO_VENDEDOR1_RESP_0"];
$cod_llamado_accion = $_REQUEST["COD_LLAMADO_ACCION"];
$cod_destinatario_usu = '';
$mensaje = $_REQUEST["MENSAJE"];
$realizado = $_REQUEST["REALIZADO_RESP"];
if($realizado == 'S'){
	$realizado = 'S';
}else{
	$realizado = 'N'; 
} 
$ms_realizado = '';
$m_realizado = '';
if($realizado =='S'){
  $ms_realizado = '(COMPROMISO REALIZADO)';
  $m_realizado = 'COMPROMISO REALIZADO'	;	
}

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql_correo = "select dbo.f_get_parametro(53) 		URL_SMTP
			,dbo.f_get_parametro(54) 	USER_SMTP
			,dbo.f_get_parametro(55) 	PASS_SMTP
			,dbo.f_get_parametro(71) 	PORT_SMTP";
$result_correo = $db->build_results($sql_correo);

$host     = $result_correo[0]['URL_SMTP'];
$username = $result_correo[0]['USER_SMTP'];
$password = $result_correo[0]['PASS_SMTP'];
$Port 	  = $result_correo[0]['PORT_SMTP'];

$tipo_doc_realizado = $_REQUEST["TIPO_DOC_REALIZADO"];
$cod_doc_realizado = $_REQUEST["COD_DOC_REALIZADO"];

$cod_destinatario_envio = substr ($cod_destinatario_envio, 0, strlen($cod_destinatario_envio) - 1);

$cod_llamado_enc = encriptar_url($cod_llamado, 'envio_mail_llamado');

$link = "http://accsisgb.biggi.cl/sysbiggi_new/biggi_comercial/trunk/appl/llamado/envio_mail/formulario.php?";
//$link = "http://192.168.2.141/desarrolladores/icampos/biggi/trunk/appl/llamado/envio_mail/formulario.php?";


$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql_accion ="SELECT NOM_CONTACTO, NOM_PERSONA, CARGO, NOM_LLAMADO_ACCION, LLAMAR_TELEFONO
				,C.RUT,C.DIG_VERIF,C.DIRECCION,CP.MAIL,E.GIRO		 
			FROM CONTACTO C LEFT OUTER JOIN EMPRESA E ON C.COD_EMPRESA = E.COD_EMPRESA,
				LLAMADO LL, LLAMADO_ACCION LLA, CONTACTO_PERSONA CP 
			WHERE LL.COD_LLAMADO = $cod_llamado
				AND LL.COD_LLAMADO_ACCION = LLA.COD_LLAMADO_ACCION
				AND C.COD_CONTACTO = LL.COD_CONTACTO
				AND CP.COD_CONTACTO_PERSONA = LL.COD_CONTACTO_PERSONA";
$result_accion = $db->build_results($sql_accion);					
$nom_contacto = $result_accion[0]['NOM_CONTACTO'];
$nom_persona = $result_accion[0]['NOM_PERSONA'];
$cargo = $result_accion[0]['CARGO'];
$nom_llamado_accion = $result_accion[0]['NOM_LLAMADO_ACCION'];
$llamar_telefono = $result_accion[0]['LLAMAR_TELEFONO'];
//nuevos datos 
$rut_emp = $result_accion[0]['RUT'];
$direccion = $result_accion[0]['DIRECCION'];
$mail_contac = $result_accion[0]['MAIL'];
$giro = $result_accion[0]['GIRO'];
$dig_verf = $result_accion[0]['DIG_VERIF'];
// si no traen datos
if($cargo == '')
$cargo = '<i>No registrado</i>';
if($rut_emp == '')
$rut_emp = '<i>No registrado</i>';
if($direccion == '')
$direccion = '<i>No registrado</i>';
if($mail_contac == '')
$mail_contac = '<i>No registrado</i>';
if($giro == '')
$giro = '<i>No registrado</i>';

$sql_from ="SELECT NOM_DESTINATARIO, 
					MAIL 
			FROM DESTINATARIO 
			WHERE COD_DESTINATARIO = $cod_destinatario";

$result_from = $db->build_results($sql_from);					
$nom_from = $result_from[0]['NOM_DESTINATARIO'];
$mail_from = $result_from[0]['MAIL'];

//listado de todos a los que se enviara mail
$nom_todos_destinatario = "";
$array_des = explode('|', $cod_destinatario_envio);

if($cod_usuario_resp != ''){
	$sql_usu_resp = "SELECT COD_DESTINATARIO 
					 FROM DESTINATARIO
					 WHERE COD_USUARIO = $cod_usuario_resp";
	$result_usu_resp = $db->build_results($sql_usu_resp);
	
	$cod_destinatario_usu = $result_usu_resp[0]['COD_DESTINATARIO'];

	$existe = 0;
	for($k=0 ; $k < count($array_des) ; $k++){
		if($array_des[$k] == $cod_destinatario_usu)
			$existe++;
	}
	if($existe == 0){
		array_push($array_des, $cod_destinatario_usu);
		$count = count($array_des);
	}else
		$count = count($array_des);
}else
	$count = count($array_des);

for ($i = 0; $i < $count; $i++) {
	
	$sql_para ="SELECT NOM_DESTINATARIO
			FROM DESTINATARIO 
			WHERE COD_DESTINATARIO = $array_des[$i]";

	$result_para = $db->build_results($sql_para);
	$nom_todos_destinatario = $nom_todos_destinatario.$result_para[0]['NOM_DESTINATARIO']."<br/>";
}

$body = "<!DOCTYPE HTML PUBLIC '-//W3C//DTD XHTML 1.0 Transitional //EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html>
    <head>
        <title></title>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
        <meta name='viewport' content='width=320, target-densitydpi=device-dpi'>
        <style type='text/css'>
            /* Mobile-specific Styles */
            @media only screen and (max-width: 660px) { 
                table[class=w0], td[class=w0] { width: 0 !important; }
                table[class=w10], td[class=w10], img[class=w10] { width:10px !important; }
                table[class=w15], td[class=w15], img[class=w15] { width:5px !important; }
                table[class=w30], td[class=w30], img[class=w30] { width:10px !important; }
                table[class=w60], td[class=w60], img[class=w60] { width:10px !important; }
                table[class=w125], td[class=w125], img[class=w125] { width:80px !important; }
                table[class=w130], td[class=w130], img[class=w130] { width:55px !important; }
                table[class=w140], td[class=w140], img[class=w140] { width:90px !important; }
                table[class=w160], td[class=w160], img[class=w160] { width:180px !important; }
                table[class=w170], td[class=w170], img[class=w170] { width:100px !important; }
                table[class=w180], td[class=w180], img[class=w180] { width:80px !important; }
                table[class=w195], td[class=w195], img[class=w195] { width:80px !important; }
                table[class=w220], td[class=w220], img[class=w220] { width:80px !important; }
                table[class=w240], td[class=w240], img[class=w240] { width:180px !important; }
                table[class=w255], td[class=w255], img[class=w255] { width:185px !important; }
                table[class=w275], td[class=w275], img[class=w275] { width:135px !important; }
                table[class=w280], td[class=w280], img[class=w280] { width:135px !important; }
                table[class=w300], td[class=w300], img[class=w300] { width:140px !important; }
                table[class=w325], td[class=w325], img[class=w325] { width:95px !important; }
                table[class=w360], td[class=w360], img[class=w360] { width:140px !important; }
                table[class=w410], td[class=w410], img[class=w410] { width:180px !important; }
                table[class=w470], td[class=w470], img[class=w470] { width:200px !important; }
                table[class=w580], td[class=w580], img[class=w580] { width:280px !important; }
                table[class=w640], td[class=w640], img[class=w640] { width:300px !important; }
                table[class*=hide], td[class*=hide], img[class*=hide], p[class*=hide], span[class*=hide] { display:none !important; }
                table[class=h0], td[class=h0] { height: 0 !important; }
                p[class=footer-content-left] { text-align: center !important; }
                #headline p { font-size: 30px !important; }
                .article-content, #left-sidebar{ -webkit-text-size-adjust: 90% !important; -ms-text-size-adjust: 90% !important; }
                .header-content, .footer-content-left {-webkit-text-size-adjust: 80% !important; -ms-text-size-adjust: 80% !important;}
                img { height: auto; line-height: 100%;}
            } 
            /* Client-specific Styles */
            #outlook a { padding: 0; }	/* Force Outlook to provide a 'view in browser' button. */
            body { width: 100% !important; }
            .ReadMsgBody { width: 100%; }
            .ExternalClass { width: 100%; display:block !important; } /* Force Hotmail to display emails at full width */
            /* Reset Styles */
            /* Add 100px so mobile switch bar doesn't cover street address. */
            body { background-color: #ececec; margin: 0; padding: 0; }
            img { outline: none; text-decoration: none; display: block;}
            br, strong br, b br, em br, i br { line-height:100%; }
            h1, h2, h3, h4, h5, h6 { line-height: 100% !important; -webkit-font-smoothing: antialiased; }
            h1 a, h2 a, h3 a, h4 a, h5 a, h6 a { color: blue !important; }
            h1 a:active, h2 a:active,  h3 a:active, h4 a:active, h5 a:active, h6 a:active {	color: red !important; }
            /* Preferably not the same color as the normal header link color.  There is limited support for psuedo classes in email clients, this was added just for good measure. */
            h1 a:visited, h2 a:visited,  h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited { color: purple !important; }
            /* Preferably not the same color as the normal header link color. There is limited support for psuedo classes in email clients, this was added just for good measure. */  
            table td, table tr { border-collapse: collapse; }
            .yshortcuts, .yshortcuts a, .yshortcuts a:link,.yshortcuts a:visited, .yshortcuts a:hover, .yshortcuts a span {
                color: black; text-decoration: none !important; border-bottom: none !important; background: none !important;
            }	/* Body text color for the New Yahoo.  This example sets the font of Yahoo's Shortcuts to black. */
            /* This most probably won't work in all email clients. Don't include code blocks in email. */
            code {
                white-space: normal;
                word-break: break-all;
            }
            #background-table { background-color: #ececec; }
            /* Webkit Elements */
            #top-bar { border-radius:6px 6px 0px 0px; -moz-border-radius: 6px 6px 0px 0px; -webkit-border-radius:6px 6px 0px 0px; -webkit-font-smoothing: antialiased; background-color: #5E5E64; color: #e7cba3; }
            #top-bar a { font-weight: bold; color: #e7cba3; text-decoration: none;}
            #footer { border-radius:0px 0px 6px 6px; -moz-border-radius: 0px 0px 6px 6px; -webkit-border-radius:0px 0px 6px 6px; -webkit-font-smoothing: antialiased; }
            /* Fonts and Content */
            body, td { font-family: HelveticaNeue, sans-serif; }
            .header-content, .footer-content-left, .footer-content-right { -webkit-text-size-adjust: none; -ms-text-size-adjust: none; }
            /* Prevent Webkit and Windows Mobile platforms from changing default font sizes on header and footer. */
            .header-content { font-size: 12px; color: #e7cba3; }
            .header-content a { font-weight: bold; color: #e7cba3; text-decoration: none; }
            #headline p { color: #FFFFFF; font-family: HelveticaNeue, sans-serif; font-size: 20px; text-align: left; margin-top:0px; margin-bottom:20px; }
            #headline p a { color: #FFFFFF; text-decoration: none; }
            .article-title { font-size: 18px; line-height:24px; color: #9a9661; font-weight:bold; margin-top:0px; margin-bottom:18px; font-family: HelveticaNeue, sans-serif; }
			.article-title2 { font-size: 13px; line-height:24px; color: #444444; font-weight:normal; margin-top:0px; margin-bottom:18px; font-family: HelveticaNeue, sans-serif; }
            .article-title a { color: #9a9661; text-decoration: none; }
			.article-title2 a { color: #013ADF; text-decoration: none; }
            .article-title.with-meta {margin-bottom: 0;}
            .article-meta { font-size: 13px; line-height: 20px; color: #ccc; font-weight: bold; margin-top: 0;}
            .article-content { font-size: 13px; line-height: 18px; color: #444444; margin-top: 0px; margin-bottom: 18px; font-family: HelveticaNeue, sans-serif; }
            .article-content2 { font-size: 23px; line-height: 21px; color: #444444; margin-top: 0px; margin-bottom: 21px; font-family: HelveticaNeue, sans-serif; }
            .article-content3 { font-size: 18px; line-height: 18px; color: #444444; margin-top: 0px; margin-bottom: 18px; font-family: HelveticaNeue, sans-serif; }
            .article-content a { color: #00707b; font-weight:bold; text-decoration:none; }
            .article-content img { max-width: 100% }
            .article-content ol, .article-content ul { margin-top:0px; margin-bottom:18px; margin-left:19px; padding:0; }
            .article-content li { font-size: 13px; line-height: 18px; color: #444444; }
            .article-content li a { color: #00707b; text-decoration:underline; }
            .article-content p {margin-bottom: 15px;}
            .footer-content-left { font-size: 12px; line-height: 15px; color: #e2e2e2; margin-top: 0px; margin-bottom: 15px; }
            .footer-content-left a { color: #e7cba3; font-weight: bold; text-decoration: none; }
            .footer-content-right { font-size: 11px; line-height: 16px; color: #e2e2e2; margin-top: 0px; margin-bottom: 15px; }
            .footer-content-right a { color: #e7cba3; font-weight: bold; text-decoration: none; }
            #footer { background-color: #DB0210; color: #e2e2e2; }
            #footer a { color: #e7cba3; text-decoration: none; font-weight: bold; }
            #permission-reminder { white-space: normal; }
            #street-address { color: #e7cba3; white-space: normal; }
        </style>
        <!--[if gte mso 9]>
            <style _tmplitem='386' >
                .article-content ol, .article-content ul {
                    margin: 0 0 0 24px;
                    padding: 0;
                    list-style-position: inside;
                }
            </style>
        <![endif]-->
    </head>
    <body>
        <table id='background-table' border='0' cellpadding='0' cellspacing='0' width='100%'>
            <tbody>
                <tr>
                    <td align='center' bgcolor='#ececec'>
                        <table class='w640' style='margin:0 10px;' border='0' cellpadding='0' cellspacing='0' width='640'>
                            <tbody>
                                <tr>
                                    <td class='w640' height='20' width='640'></td>
                                </tr>
                                <tr>
                                    <td class='w640' width='640'>
                                        <table id='top-bar' class='w640' bgcolor='#DB0210' border='0' cellpadding='0' cellspacing='0' width='640'>
                                            <tbody>
                                                <tr>
                                                    <td class='w15' width='15'></td>
                                                    <td class='w325' align='left' width='350' valign='middle'>
                                                        <table class='w325' border='0' cellpadding='0' cellspacing='0' width='350'>
                                                            <tbody>
                                                                <tr>
                                                                    <td class='w325' height='8' width='350'></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <div class='header-content'>
                                                            <webversion>Registro de llamados N� $cod_llamado</webversion>
                                                            <span class='hide'>
                                                                <!-- &nbsp;&nbsp;|&nbsp; -->
                                                                <!-- <preferences lang='es-ES'>REGISTRO DE LLAMADO N {2345}</preferences> -->
                                                                <!-- &nbsp;&nbsp;|&nbsp; -->
                                                                <!-- <unsubscribe>Desuscribirse</unsubscribe> -->
                                                            </span>
                                                        </div>
                                                        <table class='w325' border='0' cellpadding='0' cellspacing='0' width='350'>
                                                            <tbody>
                                                                <tr>
                                                                    <td class='w325' height='8' width='350'></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                    <td class='w30' width='30'></td>
                                                    <td class='w255' align='right' width='255' valign='middle'>
                                                        <table class='w255' border='0' cellpadding='0' cellspacing='0' width='255'>
                                                            <tbody>
                                                                <tr>
                                                                    <td class='w255' height='8' width='255'></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <table border='0' cellpadding='0' cellspacing='0'>
                                                            <tbody>
                                                                <tr></tr>
                                                            </tbody>
                                                        </table>
                                                        <table class='w255' border='0' cellpadding='0' cellspacing='0' width='255'>
                                                            <tbody>
                                                                <tr>
                                                                    <td class='w255' height='8' width='255'></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                    <td class='w15' width='15'></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td id='header' class='w640' align='center' bgcolor='#DB0210' width='640'>
                                        <table class='w640' border='0' cellpadding='0' cellspacing='0' width='640'>
                                            <tbody>
                                                <tr>
                                                    <td class='w30' width='30'></td><td class='w580' height='30' width='580'></td>
                                                </tr>
                                                <tr>
                                                    <td class='w30' width='30'></td>
                                                    <td class='w580' width='580'>
                                                        <div id='headline' align='center'>
                                                            <p>
                                                                <strong>
                                                                	<singleline label='Title'>Atn.:$nom_todos_destinatario</singleline>
                                                                </strong>
                                                            </p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='w640' bgcolor='#ffffff' height='30' width='640'></td>
                                </tr>
                                <tr id='simple-content-row'>
                                    <td class='w640' bgcolor='#ffffff' width='640'>
                                        <table class='w640' align='left' border='0' cellpadding='0' cellspacing='0' width='640'>
                                            <tbody>
                                                <tr>
                                                    <td class='w30' width='30'></td>
                                                    <td class='w580' width='580'>
                                                        <repeater>
                                                            <layout label='Text only'>
                                                                <table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td class='w580' width='580'>
                                                                                <p class='article-title' align='left'>
                                                                                    <singleline label='Title'>Mensaje:</singleline>
                                                                                </p>
                                                                                <div class='article-content2' style='text-align:justify'>
                                                                                    <multiline label='Description'>$mensaje
                                                                                    </multiline>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class='w580' height='10' width='580'></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </layout>
															<hr >			
                                                            <layout label='Text with left-aligned image'>
                                                            	<table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
																		<tr>
																			<td class='w580' width='580'>
																				<p class='article-title' align='left'>
																					<singleline label='Title'>Acci&oacute;n:</singleline>
																					<font class='article-content3'>$nom_llamado_accion</font>
																				</p>
																			</td>
																		</tr>
																		<tr><td class='w580' height='10' width='580'></td></tr>
																	</tbody>
																</table>
                                                                <table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
																		<tr>
																			<td class='w580' width='580'>
																				<p class='article-title' align='left'>
																					<singleline label='Title'>Raz&oacute;n Social:</singleline>
																					<font class='article-content3'>$nom_contacto</font>
																				</p>
																			</td>
																		</tr>
																		<tr><td class='w580' height='10' width='580'></td></tr>
																	</tbody>
																</table>
																<table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
																		<tr>
																			<td class='w580' width='580'>
																				<p class='article-title' align='left'>
																					<singleline label='Title'>Rut:</singleline>
																					<font class='article-content3'>$rut_emp-$dig_verf</font>
																				</p>
																			</td>
																		</tr>
																		<tr><td class='w580' height='10' width='580'></td></tr>
																	</tbody>
																</table>
																<table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
																		<tr>
																			<td class='w580' width='580'>
																			<p class='article-title' align='left'>
																					<singleline label='Title'>Cont&aacute;cto:</singleline>
																					<font class='article-content3'>$nom_persona</font>
																				</p>
																			</td>
																		</tr>
																		<tr><td class='w580' height='10' width='580'></td></tr>
																	</tbody>
																</table>
																<table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
																		<tr>
																			<td class='w580' width='580'>
																				<p class='article-title' align='left'>
																					<singleline label='Title'>Llamar al:</singleline>
																					<font class='article-content3'>$llamar_telefono</font>
																				</p>
																			</td>
																		</tr>
																		<tr><td class='w580' height='10' width='580'></td></tr>
																	</tbody>
																</table>
																<table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
																		<tr>
																			<td class='w580' width='580'>
																				<p class='article-title' align='left'>
																					<singleline label='Title'>mail:</singleline>
																					<font class='article-title2'>$mail_contac</font>
																				</p>
																			</td>
																		</tr>
																		<tr><td class='w580' height='10' width='580'></td></tr>
																	</tbody>
																</table>
																<table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
																		<tr>
																			<td class='w580' width='580'>
																				<p class='article-title' align='left'>
																					<singleline label='Title'>Cargo:</singleline>
																					<font class='article-title2'>$cargo</font>
																				</p>
																			</td>
																		</tr>
																		<tr><td class='w580' height='10' width='580'></td></tr>
																	</tbody>
																</table>
																<table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
																		<tr>
																			<td class='w580' width='580'>
																				<p class='article-title' align='left'>
																					<singleline label='Title'>Direcci&oacute;n:</singleline>
																					<font class='article-title2'>$direccion</font>
																				</p>
																			</td>
																		</tr>
																		<tr><td class='w580' height='10' width='580'></td></tr>
																	</tbody>
																</table>
																<table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
																		<tr>
																			<td class='w580' width='580'>
																				<p class='article-title' align='left'>
																					<singleline label='Title'>Giro:</singleline>
																					<font class='article-title2'>$giro</font>
																				</p>
																			</td>
																		</tr>
																		<tr><td class='w580' height='10' width='580'></td></tr>
																	</tbody>
																</table>
																<hr/>";

$altbody = "";

//envia mail a cada uno de los destinatarios
$db1 = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
for ($i = 0; $i < $count; $i++) {
	$mail = new phpmailer();
	$mail->PluginDir = dirname(__FILE__)."/../../../../../commonlib/trunk/php/clases/";
	$mail->Mailer = "smtp";
	$mail->SMTPAuth = true;
	$mail->Host = "$host";
	$mail->Username = "$username";
	$mail->Password = "$password"; 
	$mail->Port = "$Port";
	$mail->SMTPSecure= 'ssl'; 
	$mail->From = $mail_from;
	$mail->FromName = $nom_from;
	$mail->Timeout=30;


	$sql = "SELECT COUNT(*) CANT
			FROM CONVERSACION
			WHERE COD_LLAMADO =$cod_llamado";
	$result_cant = $db1->build_results($sql);
	$nro = 	$result_cant[0]['CANT']+1;	
	$mail->Subject = "[N�: $cod_llamado] $nom_contacto : CONVERSACION $nro $ms_realizado";

	$sql_para ="SELECT COD_DESTINATARIO, MAIL 
			FROM DESTINATARIO 
			WHERE COD_DESTINATARIO = $array_des[$i]";

	$result_para = $db1->build_results($sql_para);
	
	$cod_destinatario = $result_para[0]['COD_DESTINATARIO'];
	$mail_para = $result_para[0]['MAIL'];
	
	$cod_destinatario_enc = encriptar_url($cod_destinatario, 'envio_mail_llamado');
	$param_enc = "ll=".$cod_llamado_enc."&d=".$cod_destinatario_enc."&arr=".$cod_destinatario_envio."|".$cod_destinatario_resp;
	$link_final = $link.$param_enc;					
	
	$mail->AddAddress($mail_para);
	$final_html= "<table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
																		<tr>
																			<td class='w580' width='580'>
																				<p class='article-title' align='left'>
																					<singleline label='Title'>Para responder este mensaje,</singleline>
																					<font class='article-title2'><a href='$link_final'>PRESIONE ESTE LINK.</a></font>
																				</p>
																			</td>
																		</tr>
																	</tbody>
																</table>
																<hr/>
																<table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
																		<tr>
																			<td class='w580' width='580'>
																				<p class='article-title' align='left'>
																					<singleline label='Title'>Registrado por:</singleline>
																					<font class='article-title2'>$nom_from</font>
																				</p>
																			</td>
																		</tr>
																		<tr><td class='w580' height='10' width='580'></td></tr>
																	</tbody>
																</table>
                                                            </layout>
                                                        </repeater>
                                                    </td>
                                                    <td class='w30' width='30'></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='w640' bgcolor='#ffffff' height='15' width='640'></td>
                                </tr>
                                <tr>
									<td width='640' class='w640'>
										<table width='640' border='0' bgcolor='#DB0210' cellspacing='0' cellpadding='0' class='w640' id='footer'>
											<tbody><tr><td width='30' class='w30'></td><td width='360' height='30' class='w580 h0'></td><td width='60' class='w0'></td><td width='160' class='w0'></td><td width='30' class='w30'></td></tr>
											<tr>
												<td width='30' class='w30'></td>
												<td width='360' valign='top' class='w580'>
												<span class='hide'><p align='left' class='footer-content-left' id='permission-reminder'><span></span></p></span>
												<p align='left' class='footer-content-left'><preferences lang='es-ES'>ESTE MENSAJE SE HA ENVIADO A:</preferences> <unsubscribe><br/>$nom_todos_destinatario <br/> $m_realizado</unsubscribe></p>
												</td>
												<td width='60' class='hide w0'></td>
												<td width='160' valign='top' class='hide w0'>
												<p align='right' class='footer-content-right' id='street-address'><span></span></p>
												</td>
												<td width='30' class='w30'></td>
											</tr>
											<tr><td width='30' class='w30'></td><td width='360' height='15' class='w580 h0'></td><td width='60' class='w0'></td><td width='160' class='w0'></td><td width='30' class='w30'></td></tr>
										</tbody></table>
									</td>
									</tr>
                                <tr>
                                    <td class='w640' height='60' width='640'></td>
                                </tr>            
                            </tbody>
                        </table>
                    </td>
	           </tr>
            </tbody>
        </table>
    </body>
</html>";

	
	$mail->Body = $body.$final_html;
	$mail->AltBody = $altbody.$link_final;

	// cuando se envia por CRONTAB cambiar true la linea siguiente
	$CRONTAB = true;
	if ($CRONTAB)
		$exito = true;
	else
		$exito = $mail->Send();	

	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	
	$sp = "spu_envio_mail";
	
	$param = "'INSERT'
			,null
			,null
			,null
		 	,'$mail->From'
		 	,'$nom_from'
		 	,null
		 	,null
		 	,null
		 	,null
		 	,'$mail_para'
		 	,null
		 	,'$mail->Subject'
		 	,'".str_replace("'","''",$mail->Body)."'
		 	,'$mail->AltBody'
		 	,null
		 	,$cod_llamado";

	$db->EXECUTE_SP($sp, $param);
	
	if(!$exito)
	{
		echo "Problema al enviar correo electr�nico a ".$mail_para;
	}
	
}

if($cod_usuario_resp <> '' && $cod_llamado_accion == 4){
	$sql_est = "SELECT COD_SOLICITUD_COTIZACION
					  ,COD_ESTADO_SOLICITUD_COTIZACION COD_ESTADO
				FROM SOLICITUD_COTIZACION
				WHERE COD_LLAMADO = $cod_llamado";
	$result_est = $db->build_results($sql_est);
	if($result_est[0]['COD_ESTADO'] == 1 || $result_est[0]['COD_ESTADO'] == 3){
		$cod_usuario_resp = ($cod_usuario_resp=='') ? "null" : $cod_usuario_resp;
		
		$param = "'DERIVADA_USU_RESP'
				 ,".$result_est[0]['COD_SOLICITUD_COTIZACION']."
				 ,$cod_usuario_resp";

		$db->EXECUTE_SP($sp, $param);
	}
}

$db_c = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$result = $db_c->build_results("exec spu_llamado_conversa 'INSERT', NULL, $cod_llamado, $cod_destinatario_resp,'$mensaje','$realizado','S','$cod_destinatario_envio'");

if($realizado == 'S'){													
	$result = $db_c->build_results("exec spu_llamado 'REALIZADO_WEB', 
								$cod_llamado, 
								NULL, 
								NULL,
								NULL, 
								NULL,
								NULL, 
								NULL,
								'S',
								'$mensaje',
								'$tipo_doc_realizado',
								$cod_doc_realizado");		
}							
										

//////////////////////////////////////////////////////////
/////  Se envia mail de confirmacion al destinatario que da respuesta


$db2 = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql2= "SELECT GLOSA
FROM LLAMADO_CONVERSA
WHERE COD_LLAMADO = $cod_llamado
order by COD_LLAMADO_CONVERSA desc";
$result = $db2->build_results($sql2);
$respon = $result[0]['GLOSA'];

$sql_dest = "SELECT d.NOM_DESTINATARIO
FROM LLAMADO_CONVERSA LC , DESTINATARIO D
WHERE LC.COD_DESTINATARIO = D.COD_DESTINATARIO
AND COD_LLAMADO = $cod_llamado
order by COD_LLAMADO_CONVERSA desc";
$result_dest = $db2->build_results($sql_dest);
$dest = $result_dest[0]['NOM_DESTINATARIO'];

$body ="<!DOCTYPE HTML PUBLIC '-//W3C//DTD XHTML 1.0 Transitional //EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html>
    <head>
        <title></title>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
        <meta name='viewport' content='width=320, target-densitydpi=device-dpi'>
        <style type='text/css'>
            /* Mobile-specific Styles */
            @media only screen and (max-width: 660px) { 
                table[class=w0], td[class=w0] { width: 0 !important; }
                table[class=w10], td[class=w10], img[class=w10] { width:10px !important; }
                table[class=w15], td[class=w15], img[class=w15] { width:5px !important; }
                table[class=w30], td[class=w30], img[class=w30] { width:10px !important; }
                table[class=w60], td[class=w60], img[class=w60] { width:10px !important; }
                table[class=w125], td[class=w125], img[class=w125] { width:80px !important; }
                table[class=w130], td[class=w130], img[class=w130] { width:55px !important; }
                table[class=w140], td[class=w140], img[class=w140] { width:90px !important; }
                table[class=w160], td[class=w160], img[class=w160] { width:180px !important; }
                table[class=w170], td[class=w170], img[class=w170] { width:100px !important; }
                table[class=w180], td[class=w180], img[class=w180] { width:80px !important; }
                table[class=w195], td[class=w195], img[class=w195] { width:80px !important; }
                table[class=w220], td[class=w220], img[class=w220] { width:80px !important; }
                table[class=w240], td[class=w240], img[class=w240] { width:180px !important; }
                table[class=w255], td[class=w255], img[class=w255] { width:185px !important; }
                table[class=w275], td[class=w275], img[class=w275] { width:135px !important; }
                table[class=w280], td[class=w280], img[class=w280] { width:135px !important; }
                table[class=w300], td[class=w300], img[class=w300] { width:140px !important; }
                table[class=w325], td[class=w325], img[class=w325] { width:95px !important; }
                table[class=w360], td[class=w360], img[class=w360] { width:140px !important; }
                table[class=w410], td[class=w410], img[class=w410] { width:180px !important; }
                table[class=w470], td[class=w470], img[class=w470] { width:200px !important; }
                table[class=w580], td[class=w580], img[class=w580] { width:280px !important; }
                table[class=w640], td[class=w640], img[class=w640] { width:300px !important; }
                table[class*=hide], td[class*=hide], img[class*=hide], p[class*=hide], span[class*=hide] { display:none !important; }
                table[class=h0], td[class=h0] { height: 0 !important; }
                p[class=footer-content-left] { text-align: center !important; }
                #headline p { font-size: 30px !important; }
                .article-content, #left-sidebar{ -webkit-text-size-adjust: 90% !important; -ms-text-size-adjust: 90% !important; }
                .header-content, .footer-content-left {-webkit-text-size-adjust: 80% !important; -ms-text-size-adjust: 80% !important;}
                img { height: auto; line-height: 100%;}
            } 
            /* Client-specific Styles */
            #outlook a { padding: 0; }	/* Force Outlook to provide a 'view in browser' button. */
            body { width: 100% !important; }
            .ReadMsgBody { width: 100%; }
            .ExternalClass { width: 100%; display:block !important; } /* Force Hotmail to display emails at full width */
            /* Reset Styles */
            /* Add 100px so mobile switch bar doesn't cover street address. */
            body { background-color: #ececec; margin: 0; padding: 0; }
            img { outline: none; text-decoration: none; display: block;}
            br, strong br, b br, em br, i br { line-height:100%; }
            h1, h2, h3, h4, h5, h6 { line-height: 100% !important; -webkit-font-smoothing: antialiased; }
            h1 a, h2 a, h3 a, h4 a, h5 a, h6 a { color: blue !important; }
            h1 a:active, h2 a:active,  h3 a:active, h4 a:active, h5 a:active, h6 a:active {	color: red !important; }
            /* Preferably not the same color as the normal header link color.  There is limited support for psuedo classes in email clients, this was added just for good measure. */
            h1 a:visited, h2 a:visited,  h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited { color: purple !important; }
            /* Preferably not the same color as the normal header link color. There is limited support for psuedo classes in email clients, this was added just for good measure. */  
            table td, table tr { border-collapse: collapse; }
            .yshortcuts, .yshortcuts a, .yshortcuts a:link,.yshortcuts a:visited, .yshortcuts a:hover, .yshortcuts a span {
                color: black; text-decoration: none !important; border-bottom: none !important; background: none !important;
            }	/* Body text color for the New Yahoo.  This example sets the font of Yahoo's Shortcuts to black. */
            /* This most probably won't work in all email clients. Don't include code blocks in email. */
            code {
                white-space: normal;
                word-break: break-all;
            }
            #background-table { background-color: #ececec; }
            /* Webkit Elements */
            #top-bar { border-radius:6px 6px 0px 0px; -moz-border-radius: 6px 6px 0px 0px; -webkit-border-radius:6px 6px 0px 0px; -webkit-font-smoothing: antialiased; background-color: #5E5E64; color: #e7cba3; }
            #top-bar a { font-weight: bold; color: #e7cba3; text-decoration: none;}
            #footer { border-radius:0px 0px 6px 6px; -moz-border-radius: 0px 0px 6px 6px; -webkit-border-radius:0px 0px 6px 6px; -webkit-font-smoothing: antialiased; }
            /* Fonts and Content */
            body, td { font-family: HelveticaNeue, sans-serif; }
            .header-content, .footer-content-left, .footer-content-right { -webkit-text-size-adjust: none; -ms-text-size-adjust: none; }
            /* Prevent Webkit and Windows Mobile platforms from changing default font sizes on header and footer. */
            .header-content { font-size: 12px; color: #e7cba3; }
            .header-content a { font-weight: bold; color: #e7cba3; text-decoration: none; }
            #headline p { color: #FFFFFF; font-family: HelveticaNeue, sans-serif; font-size: 20px; text-align: left; margin-top:0px; margin-bottom:20px; }
            #headline p a { color: #FFFFFF; text-decoration: none; }
            .article-title { font-size: 18px; line-height:24px; color: #9a9661; font-weight:bold; margin-top:0px; margin-bottom:18px; font-family: HelveticaNeue, sans-serif; }
			.article-title2 { font-size: 13px; line-height:24px; color: #444444; font-weight:normal; margin-top:0px; margin-bottom:18px; font-family: HelveticaNeue, sans-serif; }
            .article-title a { color: #9a9661; text-decoration: none; }
			.article-title2 a { color: #013ADF; text-decoration: none; }
            .article-title.with-meta {margin-bottom: 0;}
            .article-meta { font-size: 13px; line-height: 20px; color: #ccc; font-weight: bold; margin-top: 0;}
            .article-content { font-size: 13px; line-height: 18px; color: #444444; margin-top: 0px; margin-bottom: 18px; font-family: HelveticaNeue, sans-serif; }
            .article-content2 { font-size: 23px; line-height: 21px; color: #444444; margin-top: 0px; margin-bottom: 21px; font-family: HelveticaNeue, sans-serif; }
            .article-content3 { font-size: 18px; line-height: 18px; color: #444444; margin-top: 0px; margin-bottom: 18px; font-family: HelveticaNeue, sans-serif; }
            .article-content a { color: #00707b; font-weight:bold; text-decoration:none; }
            .article-content img { max-width: 100% }
            .article-content ol, .article-content ul { margin-top:0px; margin-bottom:18px; margin-left:19px; padding:0; }
            .article-content li { font-size: 13px; line-height: 18px; color: #444444; }
            .article-content li a { color: #00707b; text-decoration:underline; }
            .article-content p {margin-bottom: 15px;}
            .footer-content-left { font-size: 12px; line-height: 15px; color: #e2e2e2; margin-top: 0px; margin-bottom: 15px; }
            .footer-content-left a { color: #e7cba3; font-weight: bold; text-decoration: none; }
            .footer-content-right { font-size: 11px; line-height: 16px; color: #e2e2e2; margin-top: 0px; margin-bottom: 15px; }
            .footer-content-right a { color: #e7cba3; font-weight: bold; text-decoration: none; }
            #footer { background-color: #DB0210; color: #e2e2e2; }
            #footer a { color: #e7cba3; text-decoration: none; font-weight: bold; }
            #permission-reminder { white-space: normal; }
            #street-address { color: #e7cba3; white-space: normal; }
        </style>
        <!--[if gte mso 9]>
            <style _tmplitem='386' >
                .article-content ol, .article-content ul {
                    margin: 0 0 0 24px;
                    padding: 0;
                    list-style-position: inside;
                }
            </style>
        <![endif]-->
    </head>
    <body>
        <table id='background-table' border='0' cellpadding='0' cellspacing='0' width='100%'>
            <tbody>
                <tr>
                    <td align='center' bgcolor='#ececec'>
                        <table class='w640' style='margin:0 10px;' border='0' cellpadding='0' cellspacing='0' width='640'>
                            <tbody>
                                <tr>
                                    <td class='w640' height='20' width='640'></td>
                                </tr>
                                <tr>
                                    <td class='w640' width='640'>
                                        <table id='top-bar' class='w640' bgcolor='#DB0210' border='0' cellpadding='0' cellspacing='0' width='640'>
                                            <tbody>
                                                <tr>
                                                    <td class='w15' width='15'></td>
                                                    <td class='w325' align='left' width='350' valign='middle'>
                                                        <table class='w325' border='0' cellpadding='0' cellspacing='0' width='350'>
                                                            <tbody>
                                                                <tr>
                                                                    <td class='w325' height='8' width='350'></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <div class='header-content'>
                                                            <webversion>Registro de llamados N� $cod_llamado</webversion>
                                                            <span class='hide'>
                                                                <!-- &nbsp;&nbsp;|&nbsp; -->
                                                                <!-- <preferences lang='es-ES'>REGISTRO DE LLAMADO N {2345}</preferences> -->
                                                                <!-- &nbsp;&nbsp;|&nbsp; -->
                                                                <!-- <unsubscribe>Desuscribirse</unsubscribe> -->
                                                            </span>
                                                        </div>
                                                        <table class='w325' border='0' cellpadding='0' cellspacing='0' width='350'>
                                                            <tbody>
                                                                <tr>
                                                                    <td class='w325' height='8' width='350'></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                    <td class='w30' width='30'></td>
                                                    <td class='w255' align='right' width='255' valign='middle'>
                                                        <table class='w255' border='0' cellpadding='0' cellspacing='0' width='255'>
                                                            <tbody>
                                                                <tr>
                                                                    <td class='w255' height='8' width='255'></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <table border='0' cellpadding='0' cellspacing='0'>
                                                            <tbody>
                                                                <tr></tr>
                                                            </tbody>
                                                        </table>
                                                        <table class='w255' border='0' cellpadding='0' cellspacing='0' width='255'>
                                                            <tbody>
                                                                <tr>
                                                                    <td class='w255' height='8' width='255'></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                    <td class='w15' width='15'></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td id='header' class='w640' align='center' bgcolor='#DB0210' width='640'>
                                        <table class='w640' border='0' cellpadding='0' cellspacing='0' width='640'>
                                            <tbody>
                                                <tr>
                                                    <td class='w30' width='30'></td><td class='w580' height='30' width='580'></td>
                                                </tr>
                                                <tr>
                                                    <td class='w30' width='30'></td>
                                                    <td class='w580' width='580'>
                                                        <div id='headline' align='center'>
                                                            <p>
                                                                <strong>
                                                                    <singleline label='Title'>Atn.:$dest</singleline>
																		<br/><singleline label='Title'>ESTA ES UNA CONFIRMACI�N A SU RESPUESTA V�A WEB DEL LLAMADO N&deg;: $cod_llamado</singleline>
                                                                </strong>
                                                            </p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='w640' bgcolor='#ffffff' height='30' width='640'></td>
                                </tr>
                                <tr id='simple-content-row'>
                                    <td class='w640' bgcolor='#ffffff' width='640'>
                                        <table class='w640' align='left' border='0' cellpadding='0' cellspacing='0' width='640'>
                                            <tbody>
                                                <tr>
                                                    <td class='w30' width='30'></td>
                                                    <td class='w580' width='580'>
                                                        <repeater>
														<hr />	
                                                            <layout label='Text only'>
                                                                <table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td class='w580' width='580'>
                                                                                <p class='article-title' align='left'>
                                                                                    <singleline label='Title'>Su respuesta fue:</singleline>
                                                                                </p>
                                                                                <div class='article-content2' style='text-align:justify'>
                                                                                    <multiline label='Description'>$respon</multiline>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class='w580' height='10' width='580'></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </layout>
															<hr />
															<layout label='Text with left-aligned image'>
																<table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
																		<tr>
																			<td class='w580' width='580'>
																				<p class='article-title' align='left'>
																					<singleline label='Title'>Acci&oacute;n:</singleline>
																					<font class='article-content3'>$nom_llamado_accion</font>
																				</p>
																			</td>
																		</tr>
																		<tr><td class='w580' height='10' width='580'></td></tr>
																	</tbody>
																</table>
																<table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
																		<tr>
																			<td class='w580' width='580'>
																				<p class='article-title' align='left'>
																					<singleline label='Title'>Raz&oacute;n Social:</singleline>
																					<font class='article-content3'>$nom_contacto</font>
																				</p>
																			</td>
																		</tr>
																		<tr><td class='w580' height='10' width='580'></td></tr>
																	</tbody>
																</table>
																<table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
																		<tr>
																			<td class='w580' width='580'>
																				<p class='article-title' align='left'>
																					<singleline label='Title'>Cont&aacute;cto:</singleline>
																					<font class='article-content3'>$nom_persona</font>
																				</p>
																			</td>
																		</tr>
																		<tr><td class='w580' height='10' width='580'></td></tr>
																	</tbody>
																</table>
																<table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
																		<tr>
																			<td class='w580' width='580'>
																				<p class='article-title' align='left'>
																					<singleline label='Title'>Llamar al:</singleline>
																					<font class='article-content3'>$llamar_telefono</font>
																				</p>
																			</td>
																		</tr>
																		<tr><td class='w580' height='10' width='580'></td></tr>
																	</tbody>
																</table>
																<table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
																		<tr>
																			<td class='w580' width='580'>
																				<p class='article-title' align='left'>
																					<singleline label='Title'>Cargo:</singleline>
																					<font class='article-title2'>$cargo</font>
																				</p>
																			</td>
																		</tr>
																		<tr><td class='w580' height='10' width='580'></td></tr>
																	</tbody>
																</table>
																<table class='w580' border='0' cellpadding='0' cellspacing='0' width='580'>
                                                                    <tbody>
																		<tr>
																			<td class='w580' width='580'>
																				<p class='article-title' align='left'>
																					<singleline label='Title'>Registrado por:</singleline>
																					<font class='article-title2'>$nom_from</font>
																				</p>
																			</td>
																		</tr>
																		<tr><td class='w580' height='10' width='580'></td></tr>
																	</tbody>
																</table>
                                                            </layout>
                                                        </repeater>
                                                    </td>
                                                    <td class='w30' width='30'></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='w640' bgcolor='#ffffff' height='15' width='640'></td>
                                </tr>
                                <tr>
									<td width='640' class='w640'>
										<table width='640' border='0' bgcolor='#DB0210' cellspacing='0' cellpadding='0' class='w640' id='footer'>
											<tbody><tr><td width='30' class='w30'></td><td width='360' height='30' class='w580 h0'></td><td width='60' class='w0'></td><td width='160' class='w0'></td><td width='30' class='w30'></td></tr>
											<tr>
												<td width='30' class='w30'></td>
												<td width='360' valign='top' class='w580'>
												<span class='hide'><p align='left' class='footer-content-left' id='permission-reminder'><span></span></p></span>
												<p align='left' class='footer-content-left'><preferences lang='es-ES'>SU RESPUESTA FUE ENVIADA A LOS SIGUIENTES DESTINATARIOS:</preferences> <unsubscribe><br/>$nom_todos_destinatario</unsubscribe></p>
												</td>
												<td width='60' class='hide w0'></td>
												<td width='160' valign='top' class='hide w0'>
												<p align='right' class='footer-content-right' id='street-address'><span></span></p>
												</td>
												<td width='30' class='w30'></td>
											</tr>
											<tr><td width='30' class='w30'></td><td width='360' height='15' class='w580 h0'></td><td width='60' class='w0'></td><td width='160' class='w0'></td><td width='30' class='w30'></td></tr>
										</tbody></table>
									</td>
									</tr>
                                <tr>
                                    <td class='w640' height='60' width='640'></td>
                                </tr>            
                            </tbody>";
    
	$altbody = "";
        
    $final_html ="</table>
                    </td>
	           </tr>
            </tbody>
        </table>
    </body>
</html>";

$mail = new phpmailer();
$mail->PluginDir = dirname(__FILE__)."/../../../../../commonlib/trunk/php/clases/";
$mail->Mailer = "smtp";
$mail->SMTPAuth = true;
$mail->Host = "$host";
$mail->Username = "$username";
$mail->Password = "$password"; 
$mail->Port = "$Port";
$mail->SMTPSecure= 'ssl';  
$mail->From = $mail_from;
$mail->FromName = $nom_from;
$mail->Timeout=30;

$sql = "SELECT COUNT(*) CANT
		FROM CONVERSACION
		WHERE COD_LLAMADO =$cod_llamado";
$result_cant = $db1->build_results($sql);
$nro = 	$result_cant[0]['CANT']+1;	
$mail->Subject = "[$! CONFIRMACION DE RESPUESTA !$] [N�: $cod_llamado] $nom_contacto : CONVERSACION $nro $ms_realizado";
 
$sql_para ="SELECT MAIL 
		FROM DESTINATARIO 
		WHERE COD_DESTINATARIO = $cod_destinatario_resp";

$result_para = $db1->build_results($sql_para);

$mail_para = $result_para[0]['MAIL'];

$cod_destinatario_enc = encriptar_url($cod_destinatario_resp, 'envio_mail_llamado');
$param_enc = "ll=".$cod_llamado_enc."&d=".$cod_destinatario_enc."&arr=".$cod_destinatario_envio."|".$cod_destinatario_resp;
$link_final = $link.$param_enc;					

//$mail->AddAddress('icampos@integrasystem.cl');


$mail->Body = $body.$final_html;
$mail->AltBody = $altbody.$link_final;

// cuando se envia por CRONTAB cambiar true la linea siguiente
$CRONTAB = true;
if ($CRONTAB)
	$exito = true;
else
	$exito = $mail->Send();	

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sp = "spu_envio_mail";
$param = "'INSERT'
			,null
			,null
			,null
		 	,'$mail->From'
		 	,'$nom_from'
		 	,null
		 	,null
		 	,null
		 	,null
		 	,'$mail_para'
		 	,null
		 	,'$mail->Subject'
		 	,'".str_replace("'","''",$mail->Body)."'
		 	,'$mail->AltBody'
		 	,null
		 	,$cod_llamado";
	 
$db->EXECUTE_SP($sp, $param);

if(!$exito)
{
	print "Problema al enviar correo electr�nico a ".$mail_para;
}
else {
print("Mensaje enviado correctamente");
}	
?>