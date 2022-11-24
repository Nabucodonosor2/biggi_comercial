<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/class_PHPMailer.php");	
include(dirname(__FILE__)."/../../appl.ini");
session::set('K_ROOT_DIR', K_ROOT_DIR);


$Emisor = $_REQUEST['Emisor']; 
if($Emisor== null){
	$Emisor = "'777'";
}
//session::set($Emisor);

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

// para el ADMINISTRADOR
//Se crean las consultas para hoy, mañana, ayer, y las rechazadas para los ultimos 30 días

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "insert ACUSE (NOM_ACUSE,FECHA) values($Emisor,GETDATE())";
$result = $db->build_results($sql);
print $sql;
?>