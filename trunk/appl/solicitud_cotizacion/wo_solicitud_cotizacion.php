<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('solicitud_cotizacion'))
{
  $wo_solicitud_cotizacion = new wo_solicitud_cotizacion();
  $wo_solicitud_cotizacion->retrieve();
} else
{
  $wo = session::get('wo_solicitud_cotizacion');
  $wo->procesa_event();
}
?>