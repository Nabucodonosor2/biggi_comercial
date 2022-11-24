<?php
/*
/////////////////////
// Para redireccionar el link que le llega al usuario en el mail al calbuco
// de esta forma el que evia el nuevo correo es el calbuco
 
$cod_llamado = $_REQUEST['ll'];
$cod_destinatario = $_REQUEST['d'];
header( "Location: http://190.96.2.188/wan/comercial_biggi/biggi/trunk/appl/llamado/envio_mail/formulario.php?ll=$cod_llamado&d=$cod_destinatario" ) ;
/////////////////////
*/
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once ("../../../appl.ini");
require_once("funciones.php");

session::set('K_ROOT_URL', K_ROOT_URL);
session::set('K_ROOT_DIR', K_ROOT_DIR);
session::set('K_CLIENTE', K_CLIENTE);
session::set('K_APPL', K_APPL);

$cod_llamado = $_REQUEST['ll'];
$cod_destinatario = $_REQUEST['d'];
$array_cod_destinatario = $_REQUEST['arr'];

$cod_llamado = dencriptar_url($cod_llamado, 'envio_mail_llamado');
$cod_destinatario = dencriptar_url($cod_destinatario, 'envio_mail_llamado');

//carga todos los contactos.
$load_list = load_list();

$sql = "SELECT LL.COD_LLAMADO
			,NOM_CONTACTO
			,RUT
			,DIG_VERIF
			,NOM_PERSONA
			,CARGO
			,NOM_LLAMADO_ACCION
			,MENSAJE
			,LL.COD_LLAMADO_ACCION
			,COD_SOLICITUD_COTIZACION
			,(SELECT NOM_ESTADO_SOLICITUD_COTIZACION
			  FROM ESTADO_SOLICITUD_COTIZACION ESC
			  WHERE ESC.COD_ESTADO_SOLICITUD_COTIZACION = SC.COD_ESTADO_SOLICITUD_COTIZACION) NOM_EST_SOL_COT
			,COD_ESTADO_SOLICITUD_COTIZACION  COD_EST_SOL_COT
		FROM LLAMADO LL LEFT OUTER JOIN SOLICITUD_COTIZACION SC ON LL.COD_LLAMADO = SC.COD_LLAMADO
			,CONTACTO C
			,CONTACTO_PERSONA CP
			,LLAMADO_ACCION LLA
		WHERE LL.COD_LLAMADO = $cod_llamado
		AND C.COD_CONTACTO = LL.COD_CONTACTO
		AND CP.COD_CONTACTO_PERSONA = LL.COD_CONTACTO_PERSONA
		AND LLA.COD_LLAMADO_ACCION = LL.COD_LLAMADO_ACCION";

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$result = $db->build_results($sql);	
	
$NOM_CONTACTO = $result[0]['NOM_CONTACTO'];
$RUT = $result[0]['RUT'];
$DIG_VERIF = $result[0]['DIG_VERIF'];
$NOM_PERSONA = $result[0]['NOM_PERSONA'];
$CARGO = $result[0]['CARGO'];
$NOM_LLAMADO_ACCION = $result[0]['NOM_LLAMADO_ACCION'];
$MENSAJE = $result[0]['MENSAJE'];
$COD_LLAMADO_ACCION = $result[0]['COD_LLAMADO_ACCION'];
$COD_SOLICITUD_COTIZACION = $result[0]['COD_SOLICITUD_COTIZACION'];
$NOM_EST_SOL_COT = $result[0]['NOM_EST_SOL_COT'];
$COD_EST_SOL_COT = $result[0]['COD_EST_SOL_COT'];

$temp = new Template_appl("formulario.htm");
$temp->setVar("COD_LLAMADO", $cod_llamado);
$temp->setVar("COD_LLAMADO_H", '<input name="COD_LLAMADO_H" id="COD_LLAMADO_H" type="hidden" value="'.$cod_llamado.'">');
$temp->setVar("NOM_CONTACTO", $NOM_CONTACTO);
$temp->setVar("COD_LLAMADO_ACCION", '<input name="COD_LLAMADO_ACCION" id="COD_LLAMADO_ACCION" type="hidden" value="'.$COD_LLAMADO_ACCION.'">');
$temp->setVar("COD_SOLICITUD_COTIZACION", '<input name="COD_SOLICITUD_COTIZACION" id="COD_SOLICITUD_COTIZACION" type="hidden" value="'.$COD_SOLICITUD_COTIZACION.'">');
$temp->setVar("NOM_EST_SOL_COT", '<input name="NOM_EST_SOL_COT" id="NOM_EST_SOL_COT" type="hidden" value="'.$NOM_EST_SOL_COT.'">');
$temp->setVar("RUT", number_format($RUT, 0, ',', '.'));

$temp->setVar("DIG_VERIF", $DIG_VERIF);
$temp->setVar("NOM_PERSONA", $NOM_PERSONA);
$temp->setVar("CARGO", $CARGO);
$temp->setVar("NOM_LLAMADO_ACCION", $NOM_LLAMADO_ACCION);
$temp->setVar("MENSAJE", "");
$temp->setVar("TIPO_DOC_REALIZADO", '<SELECT style="width:120px; font-size:9pt" NAME="TIPO_DOC_REALIZADO">
<OPTION SELECTED VALUE="">
<OPTION VALUE="COTIZACION">COTIZACION
<OPTION VALUE="NOTA VENTA">NOTA VENTA
<OPTION VALUE="GUIA DESPACHO">GUIA DESPACHO
<OPTION VALUE="FACTURA">FACTURA
</SELECT> ');
$temp->setVar("COD_DOC_REALIZADO", '<input name="COD_DOC_REALIZADO" type="text" onKeyPress="return numbersonly(this, event)" size="10" maxLength="10">');

$temp->setVar("MENSAJE_ORIGINAL", $MENSAJE);
$temp->setVar("COD_DESTINATARIO_ENVIO_H", '<input name="COD_DESTINATARIO_ENVIO_H" id="COD_DESTINATARIO_ENVIO_H" type="hidden" value="">');
$temp->setVar("COD_DESTINATARIO_H", '<input name="COD_DESTINATARIO_H" id="COD_DESTINATARIO_H" type="hidden" value="'.$cod_destinatario.'">');

if($COD_LLAMADO_ACCION != 4){
	$temp->setVar("DISPLAY_TABLE_UNO", "none");	
	$temp->setVar("DISPLAY_TABLE_DOS", "none");
	$temp->setVar("DIV_TABLE_UNO", "none");
}else{
	if($COD_EST_SOL_COT == 1 || $COD_EST_SOL_COT == 3)
		$temp->setVar("DISPLAY_TABLE_UNO", "");
	else
		$temp->setVar("DISPLAY_TABLE_UNO", "none");	
		
	$temp->setVar("DISPLAY_TABLE_DOS", "");
	$temp->setVar("DIV_TABLE_UNO", "");
}

///DESTINATARIO

class btn_eliminar extends edit_control {
	function input_file($field) {
		parent::edit_control($field);
	}
	function draw_entrable($dato, $record) {
		if($this->field == 'BTN_DEL_RESP')
			$js = "del_destinatario2(this);";
		else
			$js = "del_destinatario(this);";	
		
		$field = $this->field.'_'.$record;
		return '<img name="'.$field.'" id="'.$field.'" src="../../../../../commonlib/trunk/images/b_delete_line.jpg" onclick="'.$js.'" style="cursor:pointer">';
	}
	function draw_no_entrable($dato, $record) {
		return '';
	}
}


$db2 = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

if($array_cod_destinatario == ''){
$sql_des = "SELECT  LL.COD_DESTINATARIO
						,NOM_DESTINATARIO
						,'S' ENVIAR_MAIL
						,null BTN_DEL
				FROM LLAMADO_DESTINATARIO LL 
						LEFT OUTER JOIN DESTINATARIO D ON D.COD_DESTINATARIO = LL.COD_DESTINATARIO
				WHERE COD_LLAMADO = $cod_llamado
					AND LL.COD_DESTINATARIO <> $cod_destinatario
					AND VIGENTE = 'S'";
}else{
	$array_cod_destinatario = str_replace('|', ',', $array_cod_destinatario);
	$sql_des = "SELECT COD_DESTINATARIO
					,NOM_DESTINATARIO
					,'N' ENVIAR_MAIL
					,null BTN_DEL 
				FROM DESTINATARIO
				WHERE VIGENTE = 'S'
				AND COD_DESTINATARIO in ($array_cod_destinatario)
				AND COD_DESTINATARIO <> $cod_destinatario
				ORDER BY  COD_DESTINATARIO ASC";
}								

$result_des = $db2->build_results($sql_des);

$dw_datos = new datawindow($sql_des, 'DESTINATARIO', true, true);

$dw_datos->add_control(new edit_text_hidden('COD_DESTINATARIO'));
$dw_datos->add_control(new static_text('NOM_DESTINATARIO'));
$dw_datos->add_control(new btn_eliminar('BTN_DEL'));
$entrable = true;

$dw_datos->retrieve();
$dw_datos->habilitar($temp, $entrable);

///CONVERSACION
$db3 = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql_conv = "SELECT CONVERT(VARCHAR(10),FECHA_LLAMADO_CONVERSA,103)+'  '+CONVERT(VARCHAR(5),FECHA_LLAMADO_CONVERSA,108)  FECHA_LLAMADO_CONVERSA
					,NOM_DESTINATARIO
					,GLOSA
					,REALIZADO
				FROM LLAMADO_CONVERSA LL, DESTINATARIO D
				WHERE COD_LLAMADO = $cod_llamado
					AND D.COD_DESTINATARIO = LL.COD_DESTINATARIO
				ORDER BY COD_LLAMADO_CONVERSA DESC";				
		
$result_conv = $db3->build_results($sql_conv);
$dw_datos_conv = new datawindow($sql_conv, 'CONVERSACION');

$dw_datos_conv->add_control(new static_text('FECHA_LLAMADO_CONVERSA'));
$dw_datos_conv->add_control(new static_text('GLOSA'));
$dw_datos_conv->add_control(new edit_check_box('REALIZADO', 'S', 'N'));

$entrable = false;

$dw_datos_conv->retrieve();
$dw_datos_conv->habilitar($temp, $entrable);

//RESPONSABLE
$sql_resp = "SELECT COD_USUARIO_VENDEDOR1_RESP
					,NOM_USUARIO
					,NULL BTN_DEL_RESP
			 FROM SOLICITUD_COTIZACION SC 
				 ,USUARIO U
			 WHERE COD_LLAMADO = $cod_llamado
			 AND SC.COD_USUARIO_VENDEDOR1_RESP = U.COD_USUARIO";				
		
$dw_datos_resp = new datawindow($sql_resp, 'RESPONSABLE');

$dw_datos_resp->add_control(new edit_text_hidden('COD_USUARIO_VENDEDOR1_RESP'));
$dw_datos_resp->add_control(new static_text('NOM_USUARIO'));
$dw_datos_resp->add_control(new btn_eliminar('BTN_DEL_RESP'));

$dw_datos_resp->retrieve();
$dw_datos_resp->habilitar($temp, true);

print $temp->toString();
?>