<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql="SELECT dbo.f_chart_ventas(2015, 1) ENE_2015
			,dbo.f_chart_ventas(2015, 2) FEB_2015
			,dbo.f_chart_ventas(2015, 3) MAR_2015
			,dbo.f_chart_ventas(2015, 4) ABR_2015
			,dbo.f_chart_ventas(2015, 5) MAY_2015
			,dbo.f_chart_ventas(2015, 6) JUN_2015
			,dbo.f_chart_ventas(2015, 7) JUL_2015
			,dbo.f_chart_ventas(2015, 8) AGO_2015
			,dbo.f_chart_ventas(2015, 9) SEP_2015
			,dbo.f_chart_ventas(2015, 10) OCT_2015
			,dbo.f_chart_ventas(2015, 11) NOV_2015
			,dbo.f_chart_ventas(2015, 12) DIC_2015
			,dbo.f_chart_ventas(2016, 1) ENE_2016
			,dbo.f_chart_ventas(2016, 2) FEB_2016
			,dbo.f_chart_ventas(2016, 3) MAR_2016
			,dbo.f_chart_ventas(2016, 4) ABR_2016
			,dbo.f_chart_ventas(2016, 5) MAY_2016
			,dbo.f_chart_ventas(2016, 6) JUN_2016
			,dbo.f_chart_ventas(2016, 7) JUL_2016
			,dbo.f_chart_ventas(2016, 8) AGO_2016
			,dbo.f_chart_ventas(2016, 9) SEP_2016
			,dbo.f_chart_ventas(2016, 10) OCT_2016
			,dbo.f_chart_ventas(2016, 11) NOV_2016
			,dbo.f_chart_ventas(2016, 12) DIC_2016
			,dbo.f_chart_ventas(2017, 1) ENE_2017
	  		,dbo.f_chart_ventas(2017, 2) FEB_2017
	  		,dbo.f_chart_ventas(2017, 3) MAR_2017
			,dbo.f_chart_ventas(2017, 4) ABR_2017
			,dbo.f_chart_ventas(2017, 5) MAY_2017
			,dbo.f_chart_ventas(2017, 6) JUN_2017
			,dbo.f_chart_ventas(2017, 7) JUL_2017
			,dbo.f_chart_ventas(2017, 8) AGO_2017
			,dbo.f_chart_ventas(2017, 9) SEP_2017
			,dbo.f_chart_ventas(2017, 10) OCT_2017
			,dbo.f_chart_ventas(2017, 11) NOV_2017
			,dbo.f_chart_ventas(2017, 12) DIC_2017
			,dbo.f_chart_ventas(2018, 1) ENE_2018
			,dbo.f_chart_ventas(2018, 2) FEB_2018
			,dbo.f_chart_ventas(2018, 3) MAR_2018
			,dbo.f_chart_ventas(2018, 4) ABR_2018
			,dbo.f_chart_ventas(2018, 5) MAY_2018
			,dbo.f_chart_ventas(2018, 6) JUN_2018
			,dbo.f_chart_ventas(2018, 7) JUL_2018
			,dbo.f_chart_ventas(2018, 8) AGO_2018
			,dbo.f_chart_ventas(2018, 9) SEP_2018
			,dbo.f_chart_ventas(2018, 10) OCT_2018
			,dbo.f_chart_ventas(2018, 11) NOV_2018
			,dbo.f_chart_ventas(2018, 12) DIC_2018";
$result = $db->build_results($sql);
print urlencode(json_encode($result));
?>