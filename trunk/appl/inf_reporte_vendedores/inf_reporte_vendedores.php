<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if(isset($_POST['b_back_x'])){
    base::presentacion();
}else if(isset($_POST['b_informe'])){
    $temp = new Template_appl('wi_inf_reporte_vendedores.htm');

	// make_menu
	$menu = session::get('menu_appl');
	$menu->ancho_completa_menu = 409;
	$menu->draw($temp);
	$menu->ancho_completa_menu = 209;
	
	$ano = $_POST['ANO_0'];
	$cod_usuario = session::get("COD_USUARIO");

	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   	$db->EXECUTE_SP("spi_ventas_por_mes_alt", "$cod_usuario, $ano");
	$result = $db->build_results("spi_ventas_x_vendedor_alt $ano, $cod_usuario");

	$nv = "";
	$table_content = "";

	//variables totales
	$tot_ca = "";
	$tot_ca_sod = "";
	$tot_cu = "";
	$tot_cu_cdr = "";
	$tot_ar = "";
	$tot_rb = "";
	$tot_rb_cdr = "";
	$tot_am = "";
	$tot_he = "";
	$tot_pv = "";
	$tot_eo = "";
	$tot_ll = "";
	$tot_otros = "";
	$tot_venta = "";
	$css = "";
	$linea_total = "";

	for ($i=0; $i < count($result); $i++){
		$ene = $result[$i]['SUM_ENERO']/1000;
		$feb = $result[$i]['SUM_FEBRERO']/1000;
		$mar = $result[$i]['SUM_MARZO']/1000;
		$abr = $result[$i]['SUM_ABRIL']/1000;
		$may = $result[$i]['SUM_MAYO']/1000;
		$jun = $result[$i]['SUM_JUNIO']/1000;
		$jul = $result[$i]['SUM_JULIO']/1000;
		$ago = $result[$i]['SUM_AGOSTO']/1000;
		$sep = $result[$i]['SUM_SEPTIEMBRE']/1000;
		$oct = $result[$i]['SUM_OCTUBRE']/1000;
		$nov = $result[$i]['SUM_NOVIMEBRE']/1000;
		$dic = $result[$i]['SUM_DICIEMBRE']/1000;

		$tot_linea = $ene + $feb + $mar + $abr + $may + $jun + $jul + $ago + $sep + $oct + $nov + $dic;
		$promedio = $tot_linea/12;

		if($i == 0){
			$nv = "<td width=\"6.6%\" class=\"box\" align=\"left\">PV</td>";
			$tot_pv = $tot_linea;
			$css = "claro";
		}else if($i == 1){
			$nv = "<td width=\"6.6%\" class=\"box\" align=\"left\">HE</td>";
			$tot_he = $tot_linea;
			$css = "claro";
		}else if($i == 2){
			$nv = "<td width=\"6.6%\" class=\"box\" align=\"left\">RB</td>";
			$tot_rb = $tot_linea;
			$css = "claro";
		}else if($i == 3){
			$nv = "<td width=\"6.6%\" class=\"box\" align=\"left\">RB CDR</td>";
			$tot_rb_cdr = $tot_linea;
			$css = "claro";
		}else if($i == 4){
			$nv = "<td width=\"6.6%\" class=\"box\" align=\"left\">LL</td>";
			$tot_ll = $tot_linea;
			$css = "claro";
		}else if($i == 5){
			$nv = "<td width=\"6.6%\" class=\"subtotal box\" align=\"right\"><i>SUBTOTAL<i/></td>";
			$css = "subtotal";
		}else if($i == 6){
			$nv = "<td width=\"6.6%\" class=\"box\" align=\"left\">CU</td>";
			$tot_cu = $tot_linea;
			$css = "claro";
		}else if($i == 7){
			$nv = "<td width=\"6.6%\" class=\"box\" align=\"left\">CU CDR</td>";
			$tot_cu_cdr = $tot_linea;
			$css = "claro";
		}else if($i == 8){
			$nv = "<td width=\"6.6%\" class=\"box\" align=\"left\">EO</td>";
			$tot_eo = $tot_linea;
			$css = "claro";
		}else if($i == 9){
			$nv = "<td width=\"6.6%\" class=\"box\" align=\"left\">AR</td>";
			$tot_ar = $tot_linea;
			$css = "claro";
		}else if($i == 10){
			$nv = "<td width=\"6.6%\" class=\"box\" align=\"left\">AM</td>";
			$tot_am = $tot_linea;
			$css = "claro";
		}else if($i == 11){
			$nv = "<td width=\"6.6%\" class=\"subtotal box\" align=\"right\"><i>SUBTOTAL<i/></td>";
			$css = "subtotal";
		}else if($i == 12){
			$nv = "<td width=\"6.6%\" class=\"box\" align=\"left\">CA</td>";
			$tot_ca = $tot_linea;
			$css = "claro";
		}else if($i == 13){
			$nv = "<td width=\"6.6%\" class=\"box\" align=\"left\">CA SODEXO</td>";
			$tot_ca_sod = $tot_linea;
			$css = "claro";
		}else if($i == 14){
			$nv = "<td width=\"6.6%\" class=\"box\" align=\"left\">OTROS</td>";
			$tot_otros = $tot_linea;
			$css = "claro";
		}else if($i == 15){
			$nv = "<td width=\"6.6%\" class=\"subtotal box\" align=\"right\"><i>SUBTOTAL<i/></td>";
			$css = "subtotal";
		}else{
			$nv = "<td width=\"6.6%\" class=\"box\" align=\"left\">TOTALES</td>";
			$tot_venta = $tot_linea;
			$css = "claro";
		}

		if($i == 16)//ultimo registro
			$linea_total = "<td width=\"7.6%\" class=\"subtotal box\" style=\"font-weight: bold;\" align=\"right\">".number_format($tot_linea, 0, ',', '.')."</td>";
		else
			$linea_total = "<td width=\"7.6%\" class=\"subtotal box\" align=\"right\">".number_format($tot_linea, 0, ',', '.')."</td>";

		$table_content .= " <tr class=\"encabezado_right\">
								$nv
								<td width=\"6.6%\" class=\"$css box\" align=\"right\">".number_format($ene, 0, ',', '.')."</td>
								<td width=\"6.6%\" class=\"$css box\" align=\"right\">".number_format($feb, 0, ',', '.')."</td>
								<td width=\"6.6%\" class=\"$css box\" align=\"right\">".number_format($mar, 0, ',', '.')."</td>
								<td width=\"6.6%\" class=\"$css box\" align=\"right\">".number_format($abr, 0, ',', '.')."</td>
								<td width=\"6.6%\" class=\"$css box\" align=\"right\">".number_format($may, 0, ',', '.')."</td>
								<td width=\"6.6%\" class=\"$css box\" align=\"right\">".number_format($jun, 0, ',', '.')."</td>
								<td width=\"6.6%\" class=\"$css box\" align=\"right\">".number_format($jul, 0, ',', '.')."</td>
								<td width=\"6.6%\" class=\"$css box\" align=\"right\">".number_format($ago, 0, ',', '.')."</td>
								<td width=\"6.6%\" class=\"$css box\" align=\"right\">".number_format($sep, 0, ',', '.')."</td>
								<td width=\"6.6%\" class=\"$css box\" align=\"right\">".number_format($oct, 0, ',', '.')."</td>
								<td width=\"6.6%\" class=\"$css box\" align=\"right\">".number_format($nov, 0, ',', '.')."</td>
								<td width=\"6.6%\" class=\"$css box\" align=\"right\">".number_format($dic, 0, ',', '.')."</td>
								<td width=\"6.6%\" class=\"$css box\" align=\"right\">".number_format($promedio, 0, ',', '.')."</td>
								$linea_total
							</tr>";
					
	}

	$sql = "SELECT '' FECHA_ANO";

	$dw = new datawindow($sql);

	$temp->setVar("W_FECHA_ACTUAL", 'Fecha Actual: '.$dw->current_date());
	$temp->setVar("TABLE_CONTENT", $table_content);

	$boton = "back";
	$ruta_imag = '../../../../commonlib/trunk/images/';

	if (file_exists('../../images_appl/'.K_CLIENTE.'/images/b_'.$boton.'.jpg')){
		$ruta_imag = '../../images_appl/'.K_CLIENTE.'/images/';
	}
	$control = '<input name="b_'.$boton.'" id="b_'.$boton.'" src="'.$ruta_imag.'b_'.$boton.'.jpg" type="image" '.
								 'onMouseDown="MM_swapImage(\'b_'.$boton.'\',\'\',\''.$ruta_imag.'b_'.$boton.'_click.jpg\',1)" '.
								 'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
								 'onMouseOver="MM_swapImage(\'b_'.$boton.'\',\'\',\''.$ruta_imag.'b_'.$boton.'_over.jpg\',1)" ';
						 
	$control .= '/>';
	$temp->setVar("W_BACK", $control);


	$boton = "print";
	$ruta_imag = '../../../../commonlib/trunk/images/';

	if (file_exists('../../images_appl/'.K_CLIENTE.'/images/b_'.$boton.'.jpg')){
		$ruta_imag = '../../images_appl/'.K_CLIENTE.'/images/';
	}
	$control = '<input name="b_'.$boton.'" id="b_'.$boton.'" src="'.$ruta_imag.'b_'.$boton.'.jpg" type="image" '.
								 'onMouseDown="MM_swapImage(\'b_'.$boton.'\',\'\',\''.$ruta_imag.'b_'.$boton.'_click.jpg\',1)" '.
								 'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
								 'onMouseOver="MM_swapImage(\'b_'.$boton.'\',\'\',\''.$ruta_imag.'b_'.$boton.'_over.jpg\',1)" ';
						 
	$control .= '/>';
	$temp->setVar("W_PRINT", $control);

	$temp->setVar("ANO_FILL", $ano);
	$temp->setVar("ANO_FILL2", $ano);

	//se rellena el grafico con los totales
	$temp->setVar("G_TOT_CA", round($tot_ca));
	$temp->setVar("G_TOT_CA_SOD", round($tot_ca_sod));
	$temp->setVar("G_TOT_CU", round($tot_cu));
	$temp->setVar("G_TOT_CU_CDR", round($tot_cu_cdr));
	$temp->setVar("G_TOT_AR", round($tot_ar));
	$temp->setVar("G_TOT_RB", round($tot_rb));
	$temp->setVar("G_TOT_RB_CDR", round($tot_rb_cdr));
	$temp->setVar("G_TOT_AM", round($tot_am));
	$temp->setVar("G_TOT_HE", round($tot_he));
	$temp->setVar("G_TOT_PV", round($tot_pv));
	$temp->setVar("G_TOT_EO", round($tot_eo));
	$temp->setVar("G_TOT_LL", round($tot_ll));
	$temp->setVar("G_TOT_OTROS", round($tot_otros));

	// draw
	$dw->retrieve();
	$dw->habilitar($temp, false);
	session::set("temp.inf_reporte_vendedores", $temp);
	session::set("temp.inf_reporte_vendedores.ano", $ano);
	
	print $temp->toString();
}else if(isset($_POST['b_print_x'])){
	print " <script>window.open('print_inf_reporte_vendedores.php');</script>";
	$temp = session::get('temp.inf_reporte_vendedores');
	print $temp->toString();
}else{
	$temp = new Template_appl('inf_reporte_vendedores.htm');	
	
	// make_menu
	$menu = session::get('menu_appl');
	$menu->ancho_completa_menu = 215;
	$menu->draw($temp);
	$menu->ancho_completa_menu = 209;
	
	$sql = "select year(getdate()) ANO";
	$dw_param = new datawindow($sql);
	
	$sql = "select ANO,NOM_ANO 
            from dbo.f_anos_ventas_x_mes() 
            order by ano DESC";
	$dw_param->add_control(new drop_down_dw('ANO',$sql));
	
	// draw
	$dw_param->retrieve();
	$dw_param->habilitar($temp, true);
	print $temp->toString();
}
?>