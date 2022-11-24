<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl('link_caducado.html');	

print $temp->toString();
?>