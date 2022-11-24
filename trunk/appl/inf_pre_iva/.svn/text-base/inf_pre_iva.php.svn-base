<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if(isset($_POST['b_ok'])){
	$ANO = $_POST['ANO_0'];
	$MES = $_POST['COD_MES_0'];

	session::set("ANO", $ANO);
	session::set("MES", $MES);
	
	header('Location: inf_pre_iva_result.php');
	
}
else if(isset($_POST['b_cancel']))
	base::presentacion();
else{
	$temp = new Template_appl('inf_pre_iva.htm');	
	
	// make_menu
	$menu = session::get('menu_appl');
	$menu->draw($temp);
	
	$sql = "SELECT YEAR(GETDATE()) ANO
				  ,MONTH(GETDATE()) COD_MES";
	$dw_param = new datawindow($sql);
	$dw_param->add_control(new edit_num('ANO', 4, 4));
	$sql="SELECT '01' COD_MES
			    ,'Enero' NOM_MES
		  UNION
		  SELECT '02' COD_MES
		  	  ,'Febrero' NOM_MES
		  UNION
		  SELECT '03' COD_MES
			  ,'Marzo' NOM_MES
		  UNION
		  SELECT '04' COD_MES
			  ,'Abril' NOM_MES
		  UNION
		  SELECT '05' COD_MES
			  ,'Mayo' NOM_MES
		  UNION
		  SELECT '06' COD_MES
			  ,'Junio' NOM_MES
		  UNION
		  SELECT '07' COD_MES
			  ,'Julio' NOM_MES
		  UNION
		  SELECT '08' COD_MES
			  ,'Agosto' NOM_MES
		  UNION
		  SELECT '09' COD_MES
			  ,'Septiembre' NOM_MES
		  UNION
		  SELECT '10' COD_MES
			  ,'Octubre' NOM_MES
		  UNION
		  SELECT '11' COD_MES
			  ,'Noviembre' NOM_MES
		  UNION
		  SELECT '12' COD_MES
			  ,'Diciembre' NOM_MES";
	
	$dw_param->add_control(new drop_down_dw('COD_MES', $sql));
	
	// draw
	$dw_param->retrieve();
	$dw_param->habilitar($temp, true);
	
	print $temp->toString();
}
?>