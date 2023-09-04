<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
$total_neto = str_replace('.', '', $_REQUEST['total_neto']);

$temp = new Template_appl('dlg_costo_tbk.htm');	
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT dbo.number_format($total_neto * CONVERT(NUMERIC(18,2), dbo.f_get_parametro(81)), 0, ',', '.') COMISION_TBK_CREDITO
              ,dbo.number_format($total_neto * CONVERT(NUMERIC(18,2), dbo.f_get_parametro(80)), 0, ',', '.') COMISION_TBK_DEBITO";

$result = $db->build_results($sql);

$temp->setVar("COMISION_TBK_CREDITO", $result[0]['COMISION_TBK_CREDITO']);
$temp->setVar("COMISION_TBK_DEBITO", $result[0]['COMISION_TBK_DEBITO']);
$temp->setVar("TOTAL_NETO", $_REQUEST['total_neto']);
print $temp->toString();
?>