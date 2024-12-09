<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
$temp = new Template_appl('request_crear_desde.htm');	

$sql = "select '' COD_EMPRESA";
	
$dw = new datawindow($sql);

$sql_dp =  "SELECT COD_EMPRESA
				,NOM_EMPRESA
			FROM EMPRESA
			WHERE RUT IN (SELECT RUT_SODEXO FROM EMPRESA_SODEXO)";
$dw->add_control(new drop_down_dw('COD_EMPRESA', $sql_dp, 120));
$dw->insert_row();

$dw->habilitar($temp, true);
print $temp->toString();
?>