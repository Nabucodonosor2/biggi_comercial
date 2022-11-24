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


echo "cod_llamado=$cod_llamado<br>";
echo "cod_destinatario=$cod_destinatario<br>";
echo "array_cod_destinatario=$array_cod_destinatario<br>";
?>