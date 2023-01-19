<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_vendedor.php");

class wo_factura_rechazada extends w_output_biggi{
	var $autoriza_print;
   	function wo_factura_rechazada(){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->query("exec spx_resuelve_fa_rechazadas");

		$sql = "SELECT COD_FACTURA_RECHAZADA
						,NRO_FACTURA
						,CONVERT(VARCHAR, FECHA_RECHAZO, 103) FECHA_RECHAZO
						,FECHA_RECHAZO DATE_FECHA_RECHAZO
						,CASE RESUELTA
							WHEN 'S' THEN 'Si'
							ELSE 'No'
						END RESUELTA
						,COD_USUARIO_RESUELTA
						,convert(varchar(20), F.FECHA_FACTURA, 103) FECHA_FACTURA
						,F.FECHA_FACTURA DATE_FACTURA
						,F.RUT
						,F.DIG_VERIF
						,F.NOM_EMPRESA
						,UV1.INI_USUARIO
						,F.TOTAL_CON_IVA
						,F.COD_DOC
						,F.COD_FACTURA
						,COD_ESTADO_DOC_SII
						,dbo.f_get_nc_from_fa(F.COD_FACTURA) NRO_NOTA_CREDITO
						,CASE
							WHEN dbo.f_get_nc_from_fa(F.COD_FACTURA) IS NULL THEN NULL
							ELSE dbo.f_get_reFA(F.COD_FACTURA, F.COD_DOC, F.TOTAL_CON_IVA)
						END NRO_RE_FACTURA
				FROM FACTURA_RECHAZADA FR LEFT OUTER JOIN USUARIO U ON U.COD_USUARIO = FR.COD_USUARIO_RESUELTA
					,FACTURA F
					,USUARIO UV1
				WHERE FR.COD_FACTURA = F.COD_FACTURA
				AND UV1.COD_USUARIO = F.COD_USUARIO_VENDEDOR1
				ORDER BY COD_FACTURA_RECHAZADA DESC";		
	
   		parent::w_output_biggi('factura_rechazada', $sql, $_REQUEST['cod_item_menu']);
		
   		$this->add_header(new header_num('NRO_FACTURA', 'NRO_FACTURA', 'N° Factura'));
		$this->add_header($control = new header_date('FECHA_FACTURA', 'FECHA_FACTURA', 'Fecha Factura'));
		$control->field_bd_order = 'DATE_FACTURA';
   		$this->add_header($control = new header_date('FECHA_RECHAZO', 'FECHA_RECHAZO', 'Fecha Rechazo'));
		$control->field_bd_order = 'DATE_FECHA_RECHAZO';
		$this->add_header(new header_rut('RUT', 'F', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Razón Social'));
		$this->add_header(new header_vendedor('INI_USUARIO', 'F.COD_USUARIO_VENDEDOR1', 'V1'));
		$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'Total con IVA'));
		$this->add_header(new header_text('COD_DOC', 'COD_DOC', 'N° NV'));

		$sql_s_n = "select 'S' RESUELTA,
							'Si' NOM_RESUELTA
					UNION 
					select 'N' RESUELTA,
						   'No' NOM_RESUELTA";
		$this->add_header($header = new header_drop_down_string('RESUELTA', 'RESUELTA', 'Resuelta',$sql_s_n));	
		$this->add_header(new header_num('NRO_NOTA_CREDITO', 'dbo.f_get_nc_from_fa(F.COD_FACTURA)', 'NC'));
		$this->add_header(new header_num('NRO_RE_FACTURA', 'dbo.f_get_nc_from_fa(F.COD_FACTURA)', 'FA'));

		$this->dw->add_control(new static_num('RUT'));
		$this->dw->add_control(new static_num('TOTAL_CON_IVA'));

		$priv = $this->get_privilegio_opcion_usuario('992075', $this->cod_usuario); //print
		if($priv=='E')
			$this->autoriza_print = true;
      	else
			$this->autoriza_print = false;

		$header->valor_filtro = 'N';
		$this->make_filtros();	
	}

	function make_menu(&$temp){
		$menu = session::get('menu_appl');
		$menu->ancho_completa_menu = 454;
		$menu->draw($temp);
		$menu->ancho_completa_menu = 280;
	}

	function redraw_item(&$temp, $ind, $record){
		parent::redraw_item($temp, $ind, $record);
		$COD_FACTURA		= $this->dw->get_item($record, 'COD_FACTURA');
		$COD_ESTADO_DOC_SII = $this->dw->get_item($record, 'COD_ESTADO_DOC_SII');

		if($COD_ESTADO_DOC_SII == 2 && $this->autoriza_print == true){
			$control =  '<input name="b_printDTE_'.$ind.'" id="b_printDTE_'.$ind.'" type="button" title="Imprimir"'.
			   			'style="cursor:pointer;height:26px;width:17px;border: 0;background-image:url(../../images_appl/b_dte_print.png);background-repeat:no-repeat;background-position:center;"'.
			   			'onClick="return dlg_print_dte_wo(this, '.$COD_FACTURA.');"/>';

		}else if($COD_ESTADO_DOC_SII == 3 && $this->autoriza_print == true){
			$control =  '<input name="b_printDTE_'.$ind.'" id="b_printDTE_'.$ind.'" type="button" title="Imprimir"'.
			   			'style="cursor:pointer;height:26px;width:17px;border: 0;background-image:url(../../images_appl/b_dte_print.png);background-repeat:no-repeat;background-position:center;"'.
			   			'onClick="return dlg_print_dte_wo(this, '.$COD_FACTURA.');"/>';
		
		}else
			$control = '<img src="../../images_appl/b_dte_print_d.png">';

		$temp->setVar("wo_registro.WO_PRINT_DTE", $control);
	}

	function procesa_event(){
		if($this->clicked_boton('b_printDTE', $value_boton))
			$this->printdte();
		else 
			parent::procesa_event();
	}

	function printdte(){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$COD_FACTURA = $_POST['wo_hidden'];
		$es_cedible = $_POST['wo_hidden2'];

		$sql= "SELECT PORC_IVA
					 ,YEAR(FECHA_FACTURA) YEAR
					 ,NRO_FACTURA
			   FROM FACTURA
			   WHERE COD_FACTURA = $COD_FACTURA";
		$result		= $db->build_results($sql);
		$PORC_IVA	= $result[0]['PORC_IVA'];
		$nro_factura	= $result[0]['NRO_FACTURA'];
		$year			= $result[0]['YEAR'];
		
		if ($PORC_IVA==0)
			$cod_tipo_dte = 34;
		else
			$cod_tipo_dte = 33;
		
		if($COD_FACTURA > 37204)
			print " <script>window.open('../common_appl/print_dte.php?cod_documento=$COD_FACTURA&DTE_ORIGEN=$cod_tipo_dte&ES_CEDIBLE=$es_cedible')</script>";
		else{
			if(file_exists("../../../../PDF/PDFCOMERCIALBIGGI/$year/".$cod_tipo_dte."_$nro_factura.pdf"))
				print " <script>window.open('../../../../PDF/PDFCOMERCIALBIGGI/$year/".$cod_tipo_dte."_$nro_factura.pdf')</script>";
			else
				$this->alert('No se registra PDF del documento solicitado en respaldos Signature.');	
		}

		$this->goto_page($this->current_page);
  	}
}
?>