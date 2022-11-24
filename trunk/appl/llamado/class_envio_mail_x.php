<?php
require_once(dirname(__FILE__)."/../../../../biggi/trunk/appl/llamado/envio_mail/funciones.php");

class class_envio_mail {
 	 static function envio_mail($cod_llamado){
	
		require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/class_PHPMailer.php");	
	
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	
	
		$sql="SELECT C.ORIGEN_CONTACTO
				FROM LLAMADO LL, CONTACTO C
				WHERE LL.COD_CONTACTO = C.COD_CONTACTO 
				  AND COD_LLAMADO = $cod_llamado";
		$result = $db->build_results($sql);
		$origen_contacto 	= $result[0]['ORIGEN_CONTACTO'];
		
			
					$sql="SELECT LL.COD_LLAMADO
						        ,C.NOM_CONTACTO
								,CP.NOM_PERSONA
								,CP.CARGO
								,LL.MENSAJE
								,LL.COD_LLAMADO_ACCION
								,LL.LLAMAR_TELEFONO
								,U.NOM_USUARIO
								,LL.COD_LLAMADO_ACCION
								,SC.COD_SOLICITUD_COTIZACION
							FROM LLAMADO LL LEFT OUTER JOIN SOLICITUD_COTIZACION SC on LL.COD_LLAMADO = SC.COD_LLAMADO
								,USUARIO U
								,CONTACTO C LEFT OUTER JOIN CIUDAD CI ON CI.COD_CIUDAD = C.COD_CIUDAD
										LEFT OUTER JOIN COMUNA CO ON CO.COD_COMUNA = C.COD_COMUNA
								,CONTACTO_PERSONA CP
								,LLAMADO_ACCION LLA
						   WHERE LL.COD_LLAMADO = $cod_llamado
							 AND U.COD_USUARIO = LL.COD_USUARIO
							 AND C.COD_CONTACTO = LL.COD_CONTACTO
							 AND CP.COD_CONTACTO_PERSONA = LL.COD_CONTACTO_PERSONA
							 AND LLA.COD_LLAMADO_ACCION = LL.COD_LLAMADO_ACCION";
		
					$result = $db->build_results($sql);
					$cargo 					= $result[0]['CARGO'];
					$nom_contacto 			= $result[0]['NOM_CONTACTO'];
					$nom_persona 			= $result[0]['NOM_PERSONA'];
					$mensaje 				= $result[0]['MENSAJE'];
					$cod_llamado_accion 	= $result[0]['COD_LLAMADO_ACCION'];
					$llamar_telefono 		= $result[0]['LLAMAR_TELEFONO'];
					$n_usuario 				= $result[0]['NOM_USUARIO'];
					$cod_llamado_accion 	= $result[0]['COD_LLAMADO_ACCION'];
					$cod_solicitud_cotizacion 	= $result[0]['COD_SOLICITUD_COTIZACION']; 
					
			    $cod_llamado_enc = encriptar_url($cod_llamado, 'envio_mail_llamado');
					
			   	$link = "http://www.biggi.cl/sysbiggi_new/comercial_biggi/biggi/trunk/appl/llamado/envio_mail/formulario.php?";
				//$link = "http://192.168.2.13/desarrolladores/vmelo/biggi/trunk/appl/llamado/envio_mail/formulario.php?";
				
				$K_host = 53;
				$K_Username = 54;
				$K_Password = 55;
				$sql_host = "SELECT VALOR
							   FROM PARAMETRO 
							  WHERE COD_PARAMETRO =$K_host
								 OR COD_PARAMETRO =$K_Username
								 OR COD_PARAMETRO =$K_Password
								ORDER BY COD_PARAMETRO";
				$result_host = $db->build_results($sql_host);
				$host = 	$result_host[0]['VALOR'];
				$Username = $result_host[1]['VALOR'];
		 		$Password = $result_host[2]['VALOR'];
//echo "$sql_host <br>"; 		 		
//echo "host=$host <br>"; 		 		
//echo "Username=$Username <br>kilo<br>"; 		 		
//echo "Password=$Password <br>"; 		 		
		 		
		 	$sql_accion ="SELECT C.RUT,C.DIG_VERIF ,C.DIRECCION,CP.MAIL,E.GIRO		 
						    FROM CONTACTO C LEFT OUTER JOIN EMPRESA E ON C.COD_EMPRESA = E.COD_EMPRESA,
						       		LLAMADO LL, LLAMADO_ACCION LLA, CONTACTO_PERSONA CP 
						   WHERE LL.COD_LLAMADO = $cod_llamado
						     AND LL.COD_LLAMADO_ACCION = LLA.COD_LLAMADO_ACCION
						     AND C.COD_CONTACTO = LL.COD_CONTACTO
						    AND CP.COD_CONTACTO_PERSONA = LL.COD_CONTACTO_PERSONA";
						    
			$result_accion = $db->build_results($sql_accion);					
			// nuevos datos 
			$rut_emp = $result_accion[0]['RUT'];
			$giro = $result_accion[0]['GIRO'];
			$direccion = $result_accion[0]['DIRECCION'];
			$mail_contac = $result_accion[0]['MAIL'];
			$dig_verif = $result_accion[0]['DIG_VERIF'];
		 		
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
				
				$mail = new phpmailer();
				$mail->PluginDir = dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/";
				$mail->Mailer 	= "smtp";
				$mail->SMTPAuth = true;
    				$mail->SMTPSecure = "ssl"; 
				$mail->Host 	= "$host";
				$mail->Username = "$Username";
				$mail->Password = "$Password"; 

				$mail->Port = 465;
				$mail->From 	="registrollamados@biggi.cl";		
				$mail->FromName = "Biggi - Registro de Llamados";
				$mail->Timeout=30;
				
				
				$sql_accion ="SELECT NOM_LLAMADO_ACCION 
								FROM LLAMADO_ACCION LLA, LLAMADO LL
							   WHERE LL.COD_LLAMADO = $cod_llamado
								 AND LL.COD_LLAMADO_ACCION = LLA.COD_LLAMADO_ACCION";
				$result_accion = $db->build_results($sql_accion);					
				$nom_llamado_accion = $result_accion[0]['NOM_LLAMADO_ACCION'];
				
				
				if($cod_llamado_accion == 4){
				$sql="SELECT  DW.COD_DESTINATARIO
								,NOM_DESTINATARIO
								,MAIL
						  from DESTINATARIO_WEB DW, DESTINATARIO D
						 where  ORIGEN = 'CONTACTO'
						   and D.COD_DESTINATARIO = DW.COD_DESTINATARIO and D.VIGENTE = 'S'
						 ORDER BY RESPONSABLE DESC, COD_DESTINATARIO_WEB ASC";
				}elseif($cod_llamado_accion == 7){
				$sql="SELECT  DW.COD_DESTINATARIO
								,NOM_DESTINATARIO
								,MAIL
						  from DESTINATARIO_WEB DW, DESTINATARIO D 
						 where  ORIGEN = 'CONTACTAR WEB'
						   and D.COD_DESTINATARIO = DW.COD_DESTINATARIO and D.VIGENTE = 'S'
						 ORDER BY RESPONSABLE DESC, COD_DESTINATARIO_WEB ASC";
				}else{
				$sql = "SELECT  LL.COD_DESTINATARIO
								,NOM_DESTINATARIO
								,MAIL
						  from LLAMADO_DESTINATARIO LL, DESTINATARIO D 
						 where COD_LLAMADO = $cod_llamado
						   and D.COD_DESTINATARIO = LL.COD_DESTINATARIO and D.VIGENTE = 'S'
						 ORDER BY RESPONSABLE DESC, COD_LLAMADO_DESTINATARIO ASC";//deja en primera posición responsable = 'S'
				}
				$result = $db->build_results($sql);
				$nom_responsable = $result[0]['NOM_DESTINATARIO'];
				$row_count = $db->count_rows();
				
				//listado de todos a los que se enviara mail
				$nom_todos_destinatario = "";
				for($i=0;$i<$row_count;$i++){
					$nom_todos_destinatario = $nom_todos_destinatario.$result[$i]['NOM_DESTINATARIO'].",";
				}
				
				$nom_todos_destinatario = substr ($nom_todos_destinatario, 0, strlen($nom_todos_destinatario) - 1);
				
				$mail->Subject = "[$cod_llamado] $nom_contacto : $nom_llamado_accion";
				//$n_usuario = $this->nom_usuario;
				$body = "<html>
		<head>
		<title>Documento sin t&iacute;tulo</title>
		<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
		<style type='text/css'>
		<!--
		.Estilo13 {color: #663300}
		.Estilo15 {color: #999999}
		.Estilo22 {color: #003366}
		-->
		</style>
		</head>
		
		<body>
		<table width='440' height='171' border='2' bordercolor='#660033'>
		   <tr>
		     <td bgcolor='#FFF3E8'> <table width='341' border='0'>
		         <tr>
		           <td width='73'><h4 class='Estilo22'>Estimado(a)</h4></td>
		           <td width='3'><h4 class='Estilo22'>:</h4></td>
		            <td width='243'><h4><span class='Estilo22'>$nom_responsable </span></h4></td>
		        </tr>
		     </table>     </td>
		   </tr>
		   <tr>
		     <td bgcolor='#FFF1EC'><table width='400' border='0' align='center'>
		        <tr>
		          <td width='147' bordercolor='#993399'><h5 class='Estilo22'>Mensaje</h5></td>
		          <td width='10'><h5 class='Estilo22'>:</h5></td>
		          <td width='362' bordercolor='#9933FF'><h5 class='Estilo22'>$mensaje</h5></td>
		        </tr>
		        <tr>
		          <td><h5 class='Estilo13'>Llamado N&ordm;</h5></td>
		          <td><h5 class='Estilo13'>:</h5></td>
		          <td><h5 class='Estilo13'>$cod_llamado</h5></td>
		       </tr>";
		       if($cod_solicitud_cotizacion <> ''){
		       $body .=" <tr>
		          <td><h5 class='Estilo13'>Nº de solicitud web</h5></td>
		          <td><h5 class='Estilo13'>:</h5></td>
		          <td><h5 class='Estilo13'>$cod_solicitud_cotizacion</h5></td>
		       </tr>";
		       }
		       $body .="<tr>
		          <td><h5 class='Estilo13'>Raz&oacute;n Social</h5></td>
		          <td><h5 class='Estilo13'>:</h5></td>
		          <td><h5 class='Estilo13'>$nom_contacto</h5></td>
		        </tr>
		          <tr>
		          <td><h5 class='Estilo13'>Rut</h5></td>
		          <td><h5 class='Estilo13'>:</h5></td>
		          <td><h5 class='Estilo13'>$rut_emp-$dig_verif</h5></td>
		        </tr>
		        <tr>
		          <td><h5 class='Estilo13'>Direccion</h5></td>
		          <td><h5 class='Estilo13'>:</h5></td>
		          <td><h5 class='Estilo13'>$direccion</h5></td>
		        </tr>
		        
		        <tr>
		          <td><h5 class='Estilo13'>Giro</h5></td>
		          <td><h5 class='Estilo13'>:</h5></td>
		          <td><h5 class='Estilo13'>$giro</h5></td>
		        </tr>
		        <tr>
		        <tr>
		          <td><h5 class='Estilo13'>Cont&aacute;cto</h5></td>
		          <td><h5 class='Estilo13'>:</h5></td>
		          <td><h5 class='Estilo13'>$nom_persona</h5></td>
		        </tr>
		        <tr>
		          <td><h5 class='Estilo13'>Cargo</h5></td>
		          <td><h5 class='Estilo13'>:</h5></td>
		          <td><h5 class='Estilo13'>$cargo</h5></td>
		        </tr>
		         <tr>
		          <td><h5 class='Estilo13'>mail</h5></td>
		          <td><h5 class='Estilo13'>:</h5></td>
		          <td><h5 class='Estilo13'>$mail_contac</h5></td>
		        </tr>
		        <tr>
		          <td><h5 class='Estilo13'>Acci&oacute;n</h5></td>
		          <td><h5 class='Estilo13'>:</h5></td>
		          <td><h5 class='Estilo13'>$nom_llamado_accion</h5></td>
		        </tr>
		        <tr>
		          <td><h5 class='Estilo13'>Llamar a</h5></td>
		          <td><h5 class='Estilo13'>:</h5></td>
		          <td><h5 class='Estilo13'>$llamar_telefono</h5></td>
		        </tr>
		        <tr>
		          <td><h5 class='Estilo13'>Registrado por</h5></td>
		          <td><h5 class='Estilo13'>:</h5></td>
		          <td><h5 class='Estilo13'>$n_usuario</h5></td>
		        </tr>
		        <tr>
		          <td colspan='3'><h6 class='Estilo15'>Enviado a los siguientes destinatarios:</h6>
		          <p class='claro'><h6>$nom_todos_destinatario<h6></p></td>
		        </tr>
		        <tr>
		          ";
		        
				$altbody = "";
				for($i=0;$i<$row_count+2;$i++){
					$mail->ClearAddresses();
					
					if ($i<$row_count) {
						$cod_destinatario = $result[$i]['COD_DESTINATARIO'];
						$mail_destinatario = $result[$i]['MAIL'];
						$nom_destinatario = $result[$i]['NOM_DESTINATARIO'];
					}
					else if ($i==$row_count) {	// copia MH
						$cod_destinatario = 32;	//MARCELO HERRERA
						$mail_destinatario = "mherrera@biggi.cl";
						$nom_destinatario = "MARCELO HERRERA";
					}
					else if ($i==$row_count+1) {	// copia EC
						$cod_destinatario = 82;	//EDUARDO CASTILLO
						$mail_destinatario = "ecastillo@biggi.cl";
						$nom_destinatario = "EDUARDO CASTILLO";
					}

					$cod_destinatario_enc = encriptar_url($cod_destinatario, 'envio_mail_llamado');
					$param_enc = "ll=".$cod_llamado_enc."&d=".$cod_destinatario_enc;
					
					$link_final = $link.$param_enc;		
					$final_html= "<td colspan='3' bgcolor='#fff1ec'><table width='350' border='1'>
						            <tr>
						              <td bgcolor='#eafdfd' class='estilo15'><h5>si desea responder, d&eacute; clic en el <em><a href='$link_final'>link</a></em><h5></td>
						              </tr>
						          </table>          
						          <h6 class='estilo15'>&nbsp;</h6>
						          </td>
						        </tr>
						          </table></td>
						   </tr>
						</table>
						</blockquote>
						</body>
							</html>";
		
					
					$mail->AddAddress($mail_destinatario, $nom_destinatario);
					
								
					$mail->Body = $body.$final_html;
					$mail->AltBody = $altbody.$link.$cod_destinatario_enc;

					// cuando se envia por CRONTAB cambiar true la linea siguiente
					$CRONTAB = true;
					if ($CRONTAB)
						$exito = true;
					else
						$exito = $mail->Send();	
					
					$sp = "spu_envio_mail";
					$param = "'INSERT'
							 ,null
							 ,4
							 ,null
						 	 ,'$mail->From'
						 	 ,'$mail->FromName'
						 	 ,null
						 	 ,null
						 	 ,null
						 	 ,null
						 	 ,'$mail_destinatario'
						 	 ,'$nom_destinatario'
						 	 ,'$mail->Subject'
						 	 ,'".str_replace("'","''",$mail->Body)."'
						 	 ,'$mail->AltBody'
						 	 ,null
						 	 ,$cod_llamado";

							$db->EXECUTE_SP($sp, $param);
		
					if(!$exito){
						echo "Problema al enviar correo electrónico a ".$mail_destinatario;
					}
				
					
				  }
				  return 0; 
	}
 }
 ?>