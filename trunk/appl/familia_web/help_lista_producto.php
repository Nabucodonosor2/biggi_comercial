<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once("class_help_producto.php");

$sql = $_REQUEST['sql'];
$sql = str_replace("\\'", "'", $sql);		// Las comillas simples ', vuelven como \'

help_producto::draw_htm_lista_producto($sql);
?>