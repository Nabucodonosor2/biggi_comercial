<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once("class_print_dw_resultado_mes.php");
ini_set('memory_limit', '720M');
ini_set('max_execution_time', 900); //900 seconds = 15 minutes

$mes = $_REQUEST['mes'];
$cod_usuario = session::get("COD_USUARIO");
$ano = $_SESSION["anno"];

$sql = "SELECT ME.NOM_MES
				,RE.COD_NOTA_VENTA				
				,RE.PORC_RESULTADO
				,RE.MONTO_RESULTADO
				,RE.PORC_AA
				,RE.MONTO_AA
				,RE.PAGO_AA
				,RE.PORC_GV
				,RE.MONTO_GV
				,RE.PAGO_GV
				,RE.PORC_ADM
				,RE.MONTO_ADM
				,RE.PAGO_ADM
				,RE.PORC_VENDEDOR
				,RE.MONTO_VENDEDOR
				,RE.PAGO_VENDEDOR
		FROM INF_RESULTADO RE, MES ME
		WHERE MONTH(FECHA_NOTA_VENTA)=$mes
		AND ME.COD_MES = MONTH(FECHA_NOTA_VENTA)
		AND RE.COD_USUARIO =".$cod_usuario;

$dw_mes = new datawindow($sql,'ITEMS');
$dw_mes->add_control(new static_link('COD_NOTA_VENTA', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=INF_RESULTADO&modulo_destino=nota_venta&cod_modulo_destino=[COD_NOTA_VENTA]&cod_item_menu=1510&current_tab_page='.$mes));
$dw_mes->add_control(new static_text('COD_MES'));
$dw_mes->add_control(new static_num('PORC_RESULTADO',2));
$dw_mes->add_control(new static_num('MONTO_RESULTADO'));
$dw_mes->add_control(new static_num('PORC_AA',2));
$dw_mes->add_control(new static_num('MONTO_AA'));
$dw_mes->add_control(new static_num('PAGO_AA'));
$dw_mes->add_control(new static_num('PORC_GV',2));
$dw_mes->add_control(new static_num('MONTO_GV'));
$dw_mes->add_control(new static_num('PAGO_GV'));
$dw_mes->add_control(new static_num('PORC_ADM',2));
$dw_mes->add_control(new static_num('MONTO_ADM'));
$dw_mes->add_control(new static_num('PAGO_ADM'));
$dw_mes->add_control(new static_num('PORC_VENDEDOR',2));
$dw_mes->add_control(new static_num('MONTO_VENDEDOR'));
$dw_mes->add_control(new static_num('PAGO_VENDEDOR'));
$dw_mes->add_control(new static_text('ANO'));

// sumas
$dw_mes->accumulate('MONTO_RESULTADO', '', false);
$dw_mes->accumulate('MONTO_AA', '', false);
$dw_mes->accumulate('PAGO_AA', '', false);
$dw_mes->accumulate('MONTO_GV', '', false);
$dw_mes->accumulate('PAGO_GV', '', false);
$dw_mes->accumulate('MONTO_ADM', '', false);
$dw_mes->accumulate('PAGO_ADM', '', false);
$dw_mes->accumulate('MONTO_VENDEDOR', '', false);
$dw_mes->accumulate('PAGO_VENDEDOR', '', false);

$temp = new Template_appl('inf_resultado_mes.htm');	

// make_menu
$menu = session::get('menu_appl');
$menu->draw($temp);
	$sql_mes = "SELECT NOM_MES
				FROM MES
				WHERE COD_MES=".$mes;
	$db_mes = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$result_mes = $db_mes->build_results($sql_mes);
	$nom_mes = $result_mes[0]['NOM_MES'];
	
	
if (isset($_POST['b_print_x'])) {
	$xml = session::get('K_ROOT_DIR').'appl/inf_resultado/inf_resultado_mes.xml';
	$labels = array();
	$labels['str_mes'] = $result_mes[0]['NOM_MES'];
  $labels['str_ano'] = $ano;
		
	$rpt = new print_dw_resultado_mes($sql, $xml, $labels, "Resultado.pdf", 0);
}else if (isset($_POST['b_export_x'])) {
	ini_set('memory_limit', '30M');
	//error_reporting(E_ALL & ~E_NOTICE);
	require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
	require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");
	
	$fname = tempnam("/tmp", "resultado_resumen_$nom_mes.xls");
	$workbook = &new writeexcel_workbook($fname);
	$worksheet = &$workbook->addworksheet('RESULTADO_RESUMEN');
	
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql = "SELECT	 ME.NOM_MES
					,RE.COD_NOTA_VENTA				
					,RE.PORC_RESULTADO
					,RE.MONTO_RESULTADO
					,RE.PORC_AA
					,RE.MONTO_AA
					,RE.PAGO_AA
					,RE.PORC_GV
					,RE.MONTO_GV
					,RE.PAGO_GV
					,RE.PORC_ADM
					,RE.MONTO_ADM
					,RE.PAGO_ADM
					,RE.PORC_VENDEDOR
					,RE.MONTO_VENDEDOR
					,RE.PAGO_VENDEDOR
					,CENTRO_COSTO
					,CC.NOM_CENTRO_COSTO
			FROM INF_RESULTADO RE, MES ME, CENTRO_COSTO CC
			WHERE MONTH(FECHA_NOTA_VENTA)=$mes
			AND RE.CENTRO_COSTO = CC.COD_CENTRO_COSTO
			AND ME.COD_MES = MONTH(FECHA_NOTA_VENTA)
			AND RE.COD_USUARIO =".$cod_usuario;
	$result = $db->build_results($sql);
	$count = count($result);
	
		//se les da formato a la fuente
	$text =& $workbook->addformat();
	$text->set_font("Verdana");
	$text->set_valign('vcenter');
    
	$text_bold =& $workbook->addformat();
	$text_bold->copy($text);
	$text_bold->set_bold(1);

	$text_normal_left =& $workbook->addformat();
	$text_normal_left->copy($text);
	$text_normal_left->set_align('left');
	$text_normal_center =& $workbook->addformat();
	$text_normal_center->copy($text);
	$text_normal_center->set_align('center');
	$text_normal_right =& $workbook->addformat();
	$text_normal_right->copy($text);
	$text_normal_right->set_align('right');
	
	//DECIMALES
	$porc_normal =& $workbook->addformat();
	$porc_normal->copy($text_normal_center);
	$porc_normal->set_num_format('0.00');
				
	$monto_normal =& $workbook->addformat();
	$monto_normal->copy($text_normal_right);
	$monto_normal->set_num_format('#,##0');
	////////////////////////////////////////////////////////////////	
	
	$text_normal_bold_left =& $workbook->addformat();
	$text_normal_bold_left->copy($text_bold);
	$text_normal_bold_left->set_align('left');
	$text_normal_bold_center =& $workbook->addformat();
	$text_normal_bold_center->copy($text_bold);
	$text_normal_bold_center->set_align('center');
	$text_normal_bold_right =& $workbook->addformat();
	$text_normal_bold_right->copy($text_bold);
	$text_normal_bold_right->set_align('right');
	
	$text_blue_bold_left =& $workbook->addformat();
	$text_blue_bold_left->copy($text_bold);
	$text_blue_bold_left->set_align('left');
	$text_blue_bold_left->set_color('blue_0x20');
	$text_blue_bold_center =& $workbook->addformat();
	$text_blue_bold_center->copy($text_bold);
	$text_blue_bold_center->set_align('center');
	$text_blue_bold_center->set_color('blue_0x20');
	$text_blue_bold_right =& $workbook->addformat();
	$text_blue_bold_right->copy($text_bold);
	
	$text_blue_bold_center_top =& $workbook->addformat();
	$text_blue_bold_center_top->copy($text_bold);
	$text_blue_bold_center_top->set_align('center');
	$text_blue_bold_center_top->set_color('blue_0x20');
	$text_blue_bold_center_top->set_top(2);

	$space_item_left_bold = & $workbook->addformat();
	$space_item_left_bold->copy($text_blue_bold_center);
	$space_item_left_bold->set_border_color('black');
	$space_item_left_bold->set_top(2);
	$space_item_left_bold->set_left(2);
	
	$space_item_right_bold = & $workbook->addformat();
	$space_item_right_bold->copy($text_blue_bold_center);
	$space_item_right_bold->set_border_color('black');
	$space_item_right_bold->set_top(2);
	$space_item_right_bold->set_right(2);
	
	$space_item_bold_top = & $workbook->addformat();
	$space_item_bold_top->copy($text_blue_bold_center);
	$space_item_bold_top->set_border_color('black');
	$space_item_bold_top->set_top(2);
	$space_item_bold_top->set_right(2);
	
	$space_item_bold_top_2 = & $workbook->addformat();
	$space_item_bold_top_2->copy($text_blue_bold_center);
	$space_item_bold_top_2->set_border_color('black');
	$space_item_bold_top_2->set_top(2);
	$space_item_bold_top_2->set_right(2);
	
	$space_item_bold_top_3 = & $workbook->addformat();
	$space_item_bold_top_3->copy($text_blue_bold_center);
	$space_item_bold_top_3->set_border_color('black');
	$space_item_bold_top_3->set_top(2);
	
	$space_item_bold_bottom_1 = & $workbook->addformat();
	$space_item_bold_bottom_1->copy($text_blue_bold_center);
	$space_item_bold_bottom_1->set_border_color('black');
	$space_item_bold_bottom_1->set_bottom(2);
	
	//bordes
	$border_item_left_bold = & $workbook->addformat();
	$border_item_left_bold->copy($text_blue_bold_left);
	$border_item_left_bold->set_border_color('black');
	$border_item_left_bold->set_top(2);
	$border_item_left_bold->set_left(2);
	$border_item_left_bold->set_right(2);
	$border_item_left_bold->set_bottom(2);
	
	$border_item_left_bold_2 = & $workbook->addformat();
	$border_item_left_bold_2->copy($text_blue_bold_center);
	$border_item_left_bold_2->set_border_color('black');
	$border_item_left_bold_2->set_top(2);
	$border_item_left_bold_2->set_left(2);
	$border_item_left_bold_2->set_right(2);
	$border_item_left_bold_2->set_bottom(2);
	
	$worksheet->write(0, 0, "Centro de Costo:",$border_item_left_bold);
	$worksheet->merge_cells(0,0,0,1);
	$worksheet->write(0, 2, $result[0]['NOM_CENTRO_COSTO'], $text_normal_left);
	
	$worksheet->write(1, 0, "NRO. NV", $border_item_left_bold_2);
	$worksheet->merge_cells(1,0,2,0);
	$worksheet->write(2, 0, " ", $space_item_bold_bottom_1);
	$worksheet->write(1, 1, "RESULTADOS", $space_item_left_bold);
	$worksheet->merge_cells(1,1,1,2);
	$worksheet->write(1, 2, " ", $space_item_bold_top_2);
	$worksheet->write(1, 3, "DIRECTORIO", $text_blue_bold_center_top);
	$worksheet->merge_cells(1,3,1,5);
	$worksheet->write(1, 4, " ", $space_item_bold_top_3);
	$worksheet->write(1, 5, " ", $space_item_bold_top);
	$worksheet->write(1, 6, "GTE. VENTA", $text_blue_bold_center_top);
	$worksheet->merge_cells(1,6,1,8);
	$worksheet->write(1, 7, " ", $space_item_bold_top_3);
	$worksheet->write(1, 8, " ", $space_item_bold_top);
	$worksheet->write(1, 9, "ADMINISTRACION", $text_blue_bold_center_top);
	$worksheet->merge_cells(1,9,1,11);
	$worksheet->write(1, 10, " ", $space_item_bold_top_3);
	$worksheet->write(1, 11, " ", $space_item_bold_top);
	$worksheet->write(1, 12, "VENDEDOR", $text_blue_bold_center_top);
	$worksheet->merge_cells(1,12,1,14);
	$worksheet->write(1, 13, " ", $space_item_bold_top_3);
	$worksheet->write(1, 14, " ", $space_item_bold_top);
	
	$worksheet->write(2, 1, "Porc. %", $border_item_left_bold_2);
	$worksheet->write(2, 2, "Monto $", $border_item_left_bold_2);
	$worksheet->write(2, 3, "Porc. %", $border_item_left_bold_2);
	$worksheet->write(2, 4, "Monto $", $border_item_left_bold_2);
	$worksheet->write(2, 5, "Pagado $", $border_item_left_bold_2);
	$worksheet->write(2, 6, "Porc. %", $border_item_left_bold_2);
	$worksheet->write(2, 7, "Monto $", $border_item_left_bold_2);
	$worksheet->write(2, 8, "Pagado $", $border_item_left_bold_2);
	$worksheet->write(2, 9, "Porc. %", $border_item_left_bold_2);
	$worksheet->write(2, 10, "Monto $", $border_item_left_bold_2);
	$worksheet->write(2, 11, "Pagado $", $border_item_left_bold_2);
	$worksheet->write(2, 12, "Porc. %", $border_item_left_bold_2);
	$worksheet->write(2, 13, "Monto $", $border_item_left_bold_2);
	$worksheet->write(2, 14, "Pagado $", $border_item_left_bold_2);

	$sum_monto_resultado	= 0;
	$sum_monto_directorio	= 0;
	$sum_pago_directorio	= 0;
	$sum_monto_gv			= 0;
	$sum_pago_gv			= 0;
	$sum_monto_adm			= 0;
	$sum_pago_adm			= 0;
	$sum_monto_vendedor		= 0;
	$sum_pago_vendedor		= 0;
	$i=3;
	for($h=0; $h<$count; $h++){
		$cod_nota_venta		= $result[$h]['COD_NOTA_VENTA'];
		$porc_resultado 	= $result[$h]['PORC_RESULTADO'];
		$monto_resultado	= $result[$h]['MONTO_RESULTADO'];
		$porc_directorio	= $result[$h]['PORC_AA'];
		$monto_directorio	= $result[$h]['MONTO_AA'];
		$pago_directorio	= $result[$h]['PAGO_AA'];
		$porc_gv			= $result[$h]['PORC_GV'];
		$monto_gv			= $result[$h]['MONTO_GV'];
		$pago_gv			= $result[$h]['PAGO_GV'];
		$porc_adm			= $result[$h]['PORC_ADM'];
		$monto_adm			= $result[$h]['MONTO_ADM'];
		$pago_adm			= $result[$h]['PAGO_ADM'];
		$porc_vendedor		= $result[$h]['PORC_VENDEDOR'];
		$monto_vendedor		= $result[$h]['MONTO_VENDEDOR'];
		$pago_vendedor		= $result[$h]['PAGO_VENDEDOR'];
		
		$sum_monto_resultado	+= $monto_resultado;
		$sum_monto_directorio	+= $monto_directorio;
		$sum_pago_directorio	+= $pago_directorio;
		$sum_monto_gv			+= $monto_gv;
		$sum_pago_gv			+= $pago_gv;
		$sum_monto_adm			+= $monto_adm;
		$sum_pago_adm			+= $pago_adm;
		$sum_monto_vendedor		+= $monto_vendedor;
		$sum_pago_vendedor		+= $pago_vendedor;
		
		$worksheet->write($i, 0,$cod_nota_venta, $text_normal_left);
		$worksheet->write($i, 1,$porc_resultado, $porc_normal);
		$worksheet->write($i, 2,$monto_resultado, $monto_normal);
		$worksheet->write($i, 3,$porc_directorio, $porc_normal);
		$worksheet->write($i, 4,$monto_directorio, $monto_normal);
		$worksheet->write($i, 5,$pago_directorio, $monto_normal);
		$worksheet->write($i, 6,$porc_gv, $porc_normal);
		$worksheet->write($i, 7,$monto_gv, $monto_normal);
		$worksheet->write($i, 8,$pago_gv, $monto_normal);
		$worksheet->write($i, 9,$porc_adm, $porc_normal);
		$worksheet->write($i,10,$monto_adm, $monto_normal);
		$worksheet->write($i,11,$pago_adm, $monto_normal);
		$worksheet->write($i,12,$porc_vendedor, $porc_normal);
		$worksheet->write($i,13,$monto_vendedor, $monto_normal);
		$worksheet->write($i,14,$pago_vendedor, $monto_normal);
		
		$i++;
	}	

	$worksheet->write($i, 2,$sum_monto_resultado+1+1+1, $monto_normal);
	$worksheet->write($i, 4,$sum_monto_directorio, $monto_normal);
	$worksheet->write($i, 5,$sum_pago_directorio, $monto_normal);
	$worksheet->write($i, 7,$sum_monto_gv, $monto_normal);
	$worksheet->write($i, 8,$sum_pago_gv, $monto_normal);
	$worksheet->write($i,10,$sum_monto_adm, $monto_normal);
	$worksheet->write($i,11,$sum_pago_adm, $monto_normal);
	$worksheet->write($i,13,$sum_monto_vendedor, $monto_normal);
	$worksheet->write($i,14,$sum_pago_vendedor, $monto_normal);

	$workbook->close();
	
	header("Content-Type: application/x-msexcel; name=\"resultado_resumen_$nom_mes.xls\"");
	header("Content-Disposition: inline; filename=\"resultado_resumen_$nom_mes.xls\"");
	$fh=fopen($fname, "rb");
	fpassthru($fh);
}

	$db_user = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql_usuario = "SELECT	NOM_USUARIO
							,convert(varchar, getdate(), 103) FECHA_ACTUAL
					FROM	usuario 
					WHERE cod_usuario = $cod_usuario";
	$sql_usuario = $db_user->build_results($sql_usuario);
	
	$nom_usuario = $sql_usuario[0]['NOM_USUARIO'];
	$fecha_actual = $sql_usuario[0]['FECHA_ACTUAL'];
	
	$sql_centro_costo = "SELECT	DISTINCT(CC.NOM_CENTRO_COSTO) CENTRO_COSTO,'$ano' ANO
					FROM	INF_RESULTADO INF, CENTRO_COSTO CC
					WHERE CC.COD_CENTRO_COSTO = INF.CENTRO_COSTO 
					AND INF.COD_USUARIO =$cod_usuario";
	$result_centro_costo = $db_user->build_results($sql_centro_costo);
	
	$centro_costo = $result_centro_costo[0]['CENTRO_COSTO'];
  $ANO = $result_centro_costo[0]['ANO'];
	
	$temp->setVar("NOM_USUARIO", $nom_usuario);
	$temp->setVar("FECHA_ACTUAL", $fecha_actual);
	$temp->setVar("CENTRO_COSTO", $centro_costo);
  $temp->setVar("ANO", $ANO);

// draw
$dw_mes->retrieve();
$dw_mes->habilitar($temp, false);
$temp->setVar("NOM_MES", $result_mes[0]['NOM_MES']);
print $temp->toString();

?>