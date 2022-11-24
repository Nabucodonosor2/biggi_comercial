<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl('wo_seguimiento_cotizacion.htm');	

if (w_output::f_viene_del_menu('seguimiento_cotizacion'))
{
  $wo_seguimiento_cotizacion = new wo_seguimiento_cotizacion();
  $wo_seguimiento_cotizacion->retrieve();
} else
{
  $wo = session::get('wo_seguimiento_cotizacion');
  $wo->procesa_event();
}




?>