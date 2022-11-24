<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('llamado'))
{
  $wo_llamado = new wo_llamado();
  $wo_llamado->retrieve();
} else
{
  $wo = session::get('wo_llamado');
  $wo->procesa_event();
}
?>