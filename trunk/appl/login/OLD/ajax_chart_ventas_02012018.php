<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql="SELECT dbo.f_chart_ventas(2014, 1) ENE_2014
			,dbo.f_chart_ventas(2014, 2) FEB_2014
			,dbo.f_chart_ventas(2014, 3) MAR_2014
			,dbo.f_chart_ventas(2014, 4) ABR_2014
			,dbo.f_chart_ventas(2014, 5) MAY_2014
			,dbo.f_chart_ventas(2014, 6) JUN_2014
			,dbo.f_chart_ventas(2014, 7) JUL_2014
			,dbo.f_chart_ventas(2014, 8) AGO_2014
			,dbo.f_chart_ventas(2014, 9) SEP_2014
			,dbo.f_chart_ventas(2014, 10) OCT_2014
			,dbo.f_chart_ventas(2014, 11) NOV_2014
			,dbo.f_chart_ventas(2014, 12) DIC_2014
			,dbo.f_chart_ventas(2015, 1) ENE_2015
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
			,dbo.f_chart_ventas(2017, 12) DIC_2017";
$result = $db->build_results($sql);
print urlencode(json_encode($result));
?>