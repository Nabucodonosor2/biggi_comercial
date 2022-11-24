<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (session::is_set('PRESENTACION_MESSAGE'))
	$mess = session::get('PRESENTACION_MESSAGE');		// mensaje que se desea aparezca al desplegar la ventana de presentacion
else
	$mess = '';
if (session::is_set('PRESENTACION_PRINT'))
	$print = session::get('PRESENTACION_PRINT'); 		// codigo javascrip o texto que se desea enviar con un print desde presentacion.php
else
	$print = '';
session::un_set('PRESENTACION_MESSAGE');
session::un_set('PRESENTACION_PRINT');

$t = new Template_appl(session::get('K_ROOT_DIR').'html/presentacion_chart.htm');
$menu = session::get('menu_appl');
$menu->draw($t);

print $t->toString();
if ($mess != '')
	print '<script type="text/javascript">
          alert("'.$mess.'");
					</script>';
if ($print != '')
		print $print;
?>