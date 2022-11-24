<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$cod_nota_venta = $_REQUEST["cod_nota_venta"]; 
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "select top 1   KEY_TABLA
				,DATEDIFF(HOUR, fecha_cambio , dbo.f_makedate(day(getdate()), month(getdate()), year(getdate())) + CONVERT(varchar(100),GETDATE(),108)) HORA
from LOG_CAMBIO		
where NOM_TABLA = 'ENVIA_MAIL_WEB_PAY' and KEY_TABLA = $cod_nota_venta 
and  KEY_TABLA = (select COD_NOTA_VENTA from WP_TRANSACCION
where COD_NOTA_VENTA = $cod_nota_venta
and LINK_VISIBLE = 'S')
order by COD_LOG_CAMBIO desc";
$result = $db->build_results($sql);
	
$HORA		= $result[0]['HORA'];
$KEY_TABLA		= $result[0]['KEY_TABLA'];

if($HORA > 24)
	print 'CADUCADO';
else if ($KEY_TABLA == '')
	print 'NUEVO';
else
	print 'VIGENTE'
?>