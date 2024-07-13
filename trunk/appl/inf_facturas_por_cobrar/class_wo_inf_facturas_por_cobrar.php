<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
include(dirname(__FILE__)."/../../appl.ini");

class wo_inf_facturas_por_cobrar_base extends w_informe_pantalla {
	
   const K_AUTORIZA_EXPORTA_EXCEL	 = '995005';
   function wo_inf_facturas_por_cobrar_base() {
   		/* El cod_usuario debe leerese desde la sesion porque no se puede usar $this->cod_usuario
   		   hasta despues de llamar al ancestro
   		 */  
   		$cod_usuario =  session::get("COD_USUARIO");
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->EXECUTE_SP("spi_facturas_por_cobrar", "$cod_usuario"); 
   		$sql = "select	I.COD_FACTURA
						,I.NRO_FACTURA
						,I.FECHA_FACTURA
						,I.FECHA_FACTURA_STR
						,I.DATE_FACTURA
						,I.RUT
						,I.DIG_VERIF
						,I.NOM_EMPRESA
						,I.INI_USUARIO_VENDEDOR_A
						,I.INI_USUARIO_VENDEDOR_B
						,I.TOTAL_CON_IVA
						,I.SALDO
						,I.PAGO
						,I.CANTIDAD_FA 
						,I.COD_USUARIO_VENDEDOR1
				FROM INF_FACTURAS_POR_COBRAR I
				where I.COD_USUARIO = $cod_usuario
				ORDER BY I.FECHA_FACTURA";
				
		parent::w_informe_pantalla('inf_facturas_por_cobrar', $sql, $_REQUEST['cod_item_menu']);
		
		$this->dw->add_control(new edit_text_hidden('COD_NOTA_VENTA_H'));
		
		// headers
		$this->add_header(new header_num('NRO_FACTURA', 'NRO_FACTURA', 'Número'));
		$this->add_header($control = new header_date('FECHA_FACTURA_STR', 'FECHA_FACTURA', 'Fecha'));
		$control->field_bd_order = 'DATE_FACTURA';
		$this->add_header(new header_text('NOM_EMPRESA', "NOM_EMPRESA", 'Cliente'));
		$sql = "select	distinct I.COD_USUARIO_VENDEDOR1 COD_USUARIO ,U.NOM_USUARIO 
				FROM INF_FACTURAS_POR_COBRAR I left outer join USUARIO U on U.COD_USUARIO = I.COD_USUARIO_VENDEDOR1 
				where I.COD_USUARIO = $cod_usuario
				order by U.NOM_USUARIO";

		$this->add_header(new header_drop_down('INI_USUARIO_VENDEDOR_A', 'COD_USUARIO_VENDEDOR1', 'V1', $sql));
		$this->add_header(new header_rut('RUT', 'I', 'Rut'));
		$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'Total', 0, true, 'SUM'));
		$this->add_header($control = new header_num('SALDO', 'SALDO', 'Saldo', 0, true, 'SUM'));
		$this->add_header($control = new header_num('PAGO', 'PAGO', 'Pagos', 0, true, 'SUM'));
		$this->add_header(new header_num('CANTIDAD_FA', '1', '', 0, true, 'SUM'));

		// controls
		$this->dw->add_control(new static_num('RUT'));
		$this->dw->add_control(new static_num('TOTAL_CON_IVA'));
		$this->dw->add_control(new static_num('SALDO'));
		$this->dw->add_control(new static_num('PAGO'));
		
   	}
   	function redraw($temp){
   		parent::redraw($temp);
   		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT EXPORTAR
				FROM AUTORIZA_MENU
				WHERE COD_ITEM_MENU = 4035
				AND COD_PERFIL = (SELECT COD_PERFIL 
								  FROM USUARIO 
								  WHERE COD_USUARIO = $this->cod_usuario)";
		$result = $db->build_results($sql);
		if($result[0]['EXPORTAR'] == 'S')
			$this->habilita_boton($temp, 'export', true);
		else
			$this->habilita_boton($temp, 'export', false);
			
		$this->habilita_boton($temp, 'print_vendedor', true);	
   	}
   	
	function print_informe() {
		// reporte
		$sql = $this->dw->get_sql();
		$xml = session::get('K_ROOT_DIR').'appl/inf_facturas_por_cobrar/inf_facturas_por_cobrar_global.xml';
		
		$labels = array();
		$labels['str_fecha'] = $this->current_date();
		$labels['str_filtro'] = $this->nom_filtro;
		$rpt = new reporte($sql, $xml, $labels, "Facturas por cobrar.pdf", true);

		$this->_redraw();
	}
	function detalle_record($rec_no) {
		session::set('DESDE_wo_factura', 'desde output');	// para indicar que viene del output
		session::set('DESDE_wo_inf_facturas_por_cobrar', 'true');
		//registro selecionado 
    	session::set('COD_registro',$rec_no);
		$ROOT = $this->root_url;
		$url = $ROOT.'appl/factura';
		header ('Location:'.$url.'/wi_factura.php?rec_no='.$rec_no.'&cod_item_menu=1535');
	}
	
	function print_vendedor(){
		// reporte
		$cod_usuario =  session::get("COD_USUARIO");
		$sql = "select	I.COD_FACTURA
						,I.NRO_FACTURA
						,I.FECHA_FACTURA
						,I.FECHA_FACTURA_STR
						,I.DATE_FACTURA
						,I.RUT
						,I.DIG_VERIF
						,I.NOM_EMPRESA
						,I.INI_USUARIO_VENDEDOR_A
						,I.INI_USUARIO_VENDEDOR_B
						,I.TOTAL_CON_IVA
						,I.SALDO
						,I.PAGO
						,I.CANTIDAD_FA 
						,I.COD_USUARIO_VENDEDOR1
				FROM INF_FACTURAS_POR_COBRAR I
				where I.COD_USUARIO = $cod_usuario
				ORDER BY I.INI_USUARIO_VENDEDOR_A, I.FECHA_FACTURA";

		$xml = session::get('K_ROOT_DIR').'appl/inf_facturas_por_cobrar/inf_facturas_por_cobrar_vend.xml';
	
		$labels = array();
		$labels['str_fecha'] = $this->current_date();
		$labels['str_filtro'] = $this->nom_filtro;
		$rpt = new reporte($sql, $xml, $labels, "Facturas por cobrar.pdf", true);

		$this->_redraw();
	}
	
	function habilita_boton(&$temp, $boton, $habilita) {
		parent::habilita_boton($temp, $boton, $habilita);
		if ($boton=='print') {
			if ($habilita){
				$temp->setVar("WO_PRINT", '<input name="b_print" id="b_print" src="../../images_appl/b_print_seleccion.jpg" type="image" '.
														'onMouseDown="MM_swapImage(\'b_print\',\'\',\'../../images_appl/b_print_seleccion_click.jpg\',1)" '.
														'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
														'onMouseOver="MM_swapImage(\'b_print\',\'\',\'../../images_appl/b_print_seleccion_over.jpg\',1)" '.
														'onClick="dlg_print();" '.
														'/>');
			}else{
				$temp->setVar("WO_PRINT", '<img src="../../images_appl/b_print_seleccion_d.jpg"/>');
			}
		}
		if ($boton=='print_vendedor') {
			if ($habilita){
				$temp->setVar("WO_PRINT_VENDEDOR", '<input name="b_print_vendedor" id="b_print_vendedor" src="../../images_appl/b_print_todo.jpg" type="image" '.
														'onMouseDown="MM_swapImage(\'b_print_vendedor\',\'\',\'../../images_appl/b_print_todo_click.jpg\',1)" '.
														'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
														'onMouseOver="MM_swapImage(\'b_print_vendedor\',\'\',\'../../images_appl/b_print_todo_over.jpg\',1)" '.
														'onClick="return true;"'.
														'/>');
			}else{
				$temp->setVar("WO_PRINT_VENDEDOR", '<img src="../../images_appl/b_print_todo_d.jpg"/>');
			}
		}
	}
 
	function procesa_event() {
		if(isset($_POST['b_print_vendedor_x'])){
			$this->print_vendedor();
		}else{ 
			parent::procesa_event();
		}
	}
}

// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wo_inf_facturas_por_cobrar.php";
if (file_exists($file_name)) 
	require_once($file_name);
else {
	class wo_inf_facturas_por_cobrar extends wo_inf_facturas_por_cobrar_base {
		function wo_inf_facturas_por_cobrar() {
			parent::wo_inf_facturas_por_cobrar_base(); 
		}
	}
}
?>