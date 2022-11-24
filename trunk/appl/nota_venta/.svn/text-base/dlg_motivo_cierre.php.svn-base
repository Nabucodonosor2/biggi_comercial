<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once("../../appl.ini");

$cod_nota_venta = $_REQUEST['cod_nota_venta'];


$temp = new Template_appl('dlg_motivo_cierre.htm');
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	
	if($cod_nota_venta == ''){
	$sql="SELECT NULL MOTIVO_CIERRE_SIN_PART";
	}else{
	  $sql="SELECT  MOTIVO_CIERRE_SIN_PART
	  			    ,'none' DISPLAY_BOTON 
			FROM NOTA_VENTA 
			WHERE COD_NOTA_VENTA = $cod_nota_venta";
	}
	$dw_cierre = new datawindow($sql);
	
	$dw_cierre->add_control(new edit_text_multiline('MOTIVO_CIERRE_SIN_PART',47,17));
	if($cod_nota_venta != ''){	
	$dw_cierre->set_entrable('MOTIVO_CIERRE_SIN_PART', false);
	}
	$dw_cierre->retrieve();
	
	$dw_cierre->habilitar($temp, true);
	print $temp->toString();
?>