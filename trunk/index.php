<?php

include("appl.ini");
require_once(dirname(__FILE__)."/../../commonlib/trunk/php/auto_load.php");

//////////////////////
$file_name = dirname(__FILE__)."/menu/".K_CLIENTE."/menu.php";
if (file_exists($file_name)) 
	require_once($file_name);
								
session::set('menu_appl', $menu);
session::set('K_ROOT_URL', K_ROOT_URL);
session::set('K_ROOT_DIR', K_ROOT_DIR);
session::set('K_APPL', K_APPL);
session::set('K_CLIENTE', K_CLIENTE);
session::set('K_NOMBRE', K_NOMBRE);

$t = new Template_appl("index.htm");
$t->setVar('K_ROOT_URL', K_ROOT_URL);

print $t->toString();
?>
