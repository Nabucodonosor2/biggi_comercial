<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql="SELECT dbo.f_chart_ventas(YEAR(GETDATE())-3, 1) ENE_ANTERIOR3
			,dbo.f_chart_ventas(YEAR(GETDATE())-3, 2) FEB_ANTERIOR3
			,dbo.f_chart_ventas(YEAR(GETDATE())-3, 3) MAR_ANTERIOR3
			,dbo.f_chart_ventas(YEAR(GETDATE())-3, 4) ABR_ANTERIOR3
			,dbo.f_chart_ventas(YEAR(GETDATE())-3, 5) MAY_ANTERIOR3
			,dbo.f_chart_ventas(YEAR(GETDATE())-3, 6) JUN_ANTERIOR3
			,dbo.f_chart_ventas(YEAR(GETDATE())-3, 7) JUL_ANTERIOR3
			,dbo.f_chart_ventas(YEAR(GETDATE())-3, 8) AGO_ANTERIOR3
			,dbo.f_chart_ventas(YEAR(GETDATE())-3, 9) SEP_ANTERIOR3
			,dbo.f_chart_ventas(YEAR(GETDATE())-3, 10) OCT_ANTERIOR3
			,dbo.f_chart_ventas(YEAR(GETDATE())-3, 11) NOV_ANTERIOR3
			,dbo.f_chart_ventas(YEAR(GETDATE())-3, 12) DIC_ANTERIOR3
			,dbo.f_chart_ventas(YEAR(GETDATE())-2, 1) ENE_ANTERIOR2
			,dbo.f_chart_ventas(YEAR(GETDATE())-2, 2) FEB_ANTERIOR2
			,dbo.f_chart_ventas(YEAR(GETDATE())-2, 3) MAR_ANTERIOR2
			,dbo.f_chart_ventas(YEAR(GETDATE())-2, 4) ABR_ANTERIOR2
			,dbo.f_chart_ventas(YEAR(GETDATE())-2, 5) MAY_ANTERIOR2
			,dbo.f_chart_ventas(YEAR(GETDATE())-2, 6) JUN_ANTERIOR2
			,dbo.f_chart_ventas(YEAR(GETDATE())-2, 7) JUL_ANTERIOR2
			,dbo.f_chart_ventas(YEAR(GETDATE())-2, 8) AGO_ANTERIOR2
			,dbo.f_chart_ventas(YEAR(GETDATE())-2, 9) SEP_ANTERIOR2
			,dbo.f_chart_ventas(YEAR(GETDATE())-2, 10) OCT_ANTERIOR2
			,dbo.f_chart_ventas(YEAR(GETDATE())-2, 11) NOV_ANTERIOR2
			,dbo.f_chart_ventas(YEAR(GETDATE())-2, 12) DIC_ANTERIOR2
			,dbo.f_chart_ventas(YEAR(GETDATE())-1, 1) ENE_ANTERIOR
	  		,dbo.f_chart_ventas(YEAR(GETDATE())-1, 2) FEB_ANTERIOR
	  		,dbo.f_chart_ventas(YEAR(GETDATE())-1, 3) MAR_ANTERIOR
			,dbo.f_chart_ventas(YEAR(GETDATE())-1, 4) ABR_ANTERIOR
			,dbo.f_chart_ventas(YEAR(GETDATE())-1, 5) MAY_ANTERIOR
			,dbo.f_chart_ventas(YEAR(GETDATE())-1, 6) JUN_ANTERIOR
			,dbo.f_chart_ventas(YEAR(GETDATE())-1, 7) JUL_ANTERIOR
			,dbo.f_chart_ventas(YEAR(GETDATE())-1, 8) AGO_ANTERIOR
			,dbo.f_chart_ventas(YEAR(GETDATE())-1, 9) SEP_ANTERIOR
			,dbo.f_chart_ventas(YEAR(GETDATE())-1, 10) OCT_ANTERIOR
			,dbo.f_chart_ventas(YEAR(GETDATE())-1, 11) NOV_ANTERIOR
			,dbo.f_chart_ventas(YEAR(GETDATE())-1, 12) DIC_ANTERIOR
			,dbo.f_chart_ventas(YEAR(GETDATE()), 1) ENE_ACTUAL
			,dbo.f_chart_ventas(YEAR(GETDATE()), 2) FEB_ACTUAL
			,dbo.f_chart_ventas(YEAR(GETDATE()), 3) MAR_ACTUAL
			,dbo.f_chart_ventas(YEAR(GETDATE()), 4) ABR_ACTUAL
			,dbo.f_chart_ventas(YEAR(GETDATE()), 5) MAY_ACTUAL
			,dbo.f_chart_ventas(YEAR(GETDATE()), 6) JUN_ACTUAL
			,dbo.f_chart_ventas(YEAR(GETDATE()), 7) JUL_ACTUAL
			,dbo.f_chart_ventas(YEAR(GETDATE()), 8) AGO_ACTUAL
			,dbo.f_chart_ventas(YEAR(GETDATE()), 9) SEP_ACTUAL
			,dbo.f_chart_ventas(YEAR(GETDATE()), 10) OCT_ACTUAL
			,dbo.f_chart_ventas(YEAR(GETDATE()), 11) NOV_ACTUAL
			,dbo.f_chart_ventas(YEAR(GETDATE()), 12) DIC_ACTUAL
			,YEAR(GETDATE())-3 TXT_ANO_ANTERIOR3
			,YEAR(GETDATE())-2 TXT_ANO_ANTERIOR2
			,YEAR(GETDATE())-1 TXT_ANO_ANTERIOR
			,YEAR(GETDATE()) TXT_ANO_ACTUAL";
$result = $db->build_results($sql);
print urlencode(json_encode($result));
?>