<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_usuario_vendedor	= $_REQUEST['cod_usuario_vendedor'];
$temp = new Template_appl('request_agrega_ret.htm');
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   
$sql = "select  '' MONTO , '' GLOSA";

$dw = new datawindow($sql);

$dw->add_control(new edit_text_upper('GLOSA', 50, 80));
$dw->add_control(new edit_num('MONTO',10, 10));


$dw->retrieve();

$dw->habilitar($temp, true);
print $temp->toString();
?>