<?php 
	require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
	require_once(dirname(__FILE__)."/../../common_appl/TCPDF-master/tcpdf.php");

	$TIPO_COT                   = $_REQUEST["TIPO_COT"];
	$COD_COTIZACION             = $_REQUEST["COD_COTIZACION"];
	$TIPO_DESC             		= $_REQUEST["TIPO_DESC"]; // DSCTO_TOTAL || DSCTO_ITEM
	$webpay_logo                = $_REQUEST["webpay_logo"];
	$cod_usuario                = $_REQUEST["cu"];

	// APLICAR COD_COTIZACION AQUI PARA VER ERRORES DE SOLO ESA COTIZACION
	// IF ($COD_COTIZACION == 187759) {
	//  ini_set('display_errors', 'On');
	// }

	$K_ESTADO_EMITIDA 			= 1;	
	$K_PARAM_VALIDEZ_OFERTA 	= 7;
	$K_ESTADO_ANULADA			= 7;
	$K_PARAM_ENTREGA			= 8;
	$K_PARAM_GARANTIA	 		= 9;
	$K_PARAM_NOM_EMPRESA        = 6;
	$K_PARAM_RUT_EMPRESA        = 20;
	$K_PARAM_DIR_EMPRESA        = 10;
	$K_PARAM_TEL_EMPRESA        = 11;
	$K_PARAM_FAX_EMPRESA        = 12;
	$K_PARAM_MAIL_EMPRESA       = 13;
	$K_PARAM_CIUDAD_EMPRESA     = 14;
	$K_PARAM_PAIS_EMPRESA       = 15;
	$K_PARAM_SMTP 				= 17;
	$K_PARAM_GIRO_EMPRESA		= 21;
	$K_PARAM_SITIO_WEB_EMPRESA  = 25;
	$K_PARAM_BANCO				= 61;
	$K_PARAM_CTA_CTE  			= 62;
	$K_PARAM_PORC_DSCTO_MAX_ESP = 69;
	$K_PARAM_EQUIPO_ESPECIAL	= 72;
	$K_AUTORIZA_SOLO_BITACORA	= '990530';
	$K_AUTORIZA_MOD_COTIZACION  = '990535';
	$K_AUTORIZA_VALIDA_OFERTA   = '990545';

	$sql = "SELECT 	C.COD_COTIZACION,
					E.NOM_EMPRESA,
					E.RUT,
					E.DIG_VERIF,
					dbo.f_format_date(C.FECHA_COTIZACION, 3) FECHA_COTIZACION,				
					dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[DIRECCION]') DIRECCION,
					dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[NOM_COMUNA]') NOM_COMUNA,
					dbo.f_get_direccion('SUCURSAL', C.COD_SUCURSAL_FACTURA, '[NOM_CIUDAD]') NOM_CIUDAD,
					SF.TELEFONO TELEFONO_F,
					SF.FAX FAX_F,
					C.REFERENCIA,
					P.NOM_PERSONA,
					P.EMAIL,
					p.TELEFONO,
					IC.NOM_PRODUCTO,
					IC.ITEM,
					IC.COD_PRODUCTO COD_PRODUCTO_ORIGINAL,
					IC.COD_PRODUCTO,
					IC.CANTIDAD,
					IC.PRECIO,
					IC.CANTIDAD * IC.PRECIO TOTAL,
					CASE	
						WHEN C.MONTO_DSCTO1 = 0 AND C.MONTO_DSCTO2 = 0 THEN 1
						WHEN C.MONTO_DSCTO1 <> 0 AND C.MONTO_DSCTO2 = 0 THEN 2
						WHEN C.MONTO_DSCTO1 <> 0 AND C.MONTO_DSCTO2 <> 0 THEN 3
					END TIPO_DESCUENTOS,
					C.SUBTOTAL,
					C.PORC_DSCTO1,
					C.MONTO_DSCTO1,
					C.PORC_DSCTO2,
					C.MONTO_DSCTO2,
					C.MONTO_DSCTO1 + C.MONTO_DSCTO2 FINAL,
					C.TOTAL_NETO,
					C.PORC_IVA,
					C.MONTO_IVA,
					C.TOTAL_CON_IVA,
					FP.NOM_FORMA_PAGO,
					C.VALIDEZ_OFERTA,
					C.ENTREGA,
					C.OBS,
					EC.NOM_EMBALAJE_COTIZACION,
					FL.NOM_FLETE_COTIZACION,
					I.NOM_INSTALACION_COTIZACION,
					C.GARANTIA,
					M.SIMBOLO,
					U.NOM_USUARIO,
					U.MAIL MAIL_U,
					U.TELEFONO FONO_U,
					C.NOM_FORMA_PAGO_OTRO,
					C.COD_FORMA_PAGO,
					U.CELULAR CEL_U,
			        U.INI_USUARIO,
			        CASE dbo.f_prod_get_atributo(IC.COD_PRODUCTO) 
						WHEN '' THEN 'SIN_ATRIBUTO'
						ELSE convert(text, dbo.f_prod_get_atributo(IC.COD_PRODUCTO)) 
					END ATRIBUTO_PRODUCTO,
					dbo.f_get_parametro($K_PARAM_NOM_EMPRESA) NOM_EMPRESA_EMISOR,
					dbo.f_get_parametro($K_PARAM_RUT_EMPRESA) RUT_EMPRESA,
					dbo.f_get_parametro($K_PARAM_DIR_EMPRESA) DIR_EMPRESA,
					dbo.f_get_parametro($K_PARAM_GIRO_EMPRESA) GIRO_EMPRESA,
					dbo.f_get_parametro($K_PARAM_TEL_EMPRESA) TEL_EMPRESA,
					dbo.f_get_parametro($K_PARAM_BANCO) BANCO,
					dbo.f_get_parametro($K_PARAM_CTA_CTE) CTA_CTE,
					dbo.f_get_parametro($K_PARAM_FAX_EMPRESA) FAX_EMPRESA,
					dbo.f_get_parametro($K_PARAM_MAIL_EMPRESA) MAIL_EMPRESA,
					dbo.f_get_parametro($K_PARAM_CIUDAD_EMPRESA) CIUDAD_EMPRESA,
					dbo.f_get_parametro($K_PARAM_PAIS_EMPRESA) PAIS_EMPRESA,
					dbo.f_get_parametro($K_PARAM_SITIO_WEB_EMPRESA) SITIO_WEB_EMPRESA,
					dbo.f_get_parametro($K_PARAM_EQUIPO_ESPECIAL) EQUIPO_ESPECIAL,
			        PRO.USA_FOTO_ANTIGUA
			FROM 	COTIZACION C, EMPRESA E, PERSONA P, ITEM_COTIZACION IC,FORMA_PAGO FP,
			 		INSTALACION_COTIZACION I, FLETE_COTIZACION FL, EMBALAJE_COTIZACION EC,
			 		MONEDA M, USUARIO U, SUCURSAL SF, PRODUCTO PRO
			WHERE 	C.COD_COTIZACION = $COD_COTIZACION AND 
					E.COD_EMPRESA = C.COD_EMPRESA AND
					P.COD_PERSONA = C.COD_PERSONA AND
					IC.COD_COTIZACION = C.COD_COTIZACION AND
					FP.COD_FORMA_PAGO = C.COD_FORMA_PAGO AND
					I.COD_INSTALACION_COTIZACION =C.COD_INSTALACION_COTIZACION AND
					FL.COD_FLETE_COTIZACION = C.COD_FLETE_COTIZACION AND
					EC.COD_EMBALAJE_COTIZACION = C.COD_EMBALAJE_COTIZACION AND	
					M.COD_MONEDA = C.COD_MONEDA AND
					U.COD_USUARIO = C.COD_USUARIO_VENDEDOR1 AND
					SF.COD_SUCURSAL = C.COD_SUCURSAL_FACTURA
            AND 	PRO.COD_PRODUCTO = IC.COD_PRODUCTO
			ORDER BY IC.ORDEN ASC";

    $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$result = $db->build_results($sql);

	class MYPDF extends TCPDF {
	    var $dataArray = array();

		var $W_I 	=	1;	//$W_ITEM
		var $W_NP 	=	1;	//$W_NOM_PRODUCTO
		var $W_CP 	=	1;	//$W_COD_PRODUCTO
		var $W_C 	=	1;	//$W_CANTIDAD
		var $W_PU 	=	1;	//$W_PRECIO_UNITARIO
		var $W_MD 	=	1;	//$W_MONTO_DESCUENTO
		var $W_PCD  =	1;	//$W_PRECIO_CON_DESCUENTO
		var $W_T 	=	1; 	//$W_TOTAL

	    ///// INI SECCION LOGO ////
    	public function Header() {
        	if (!empty($this->dataArray)){
            	$result = $this->dataArray;
	            $bMargin = $this->getBreakMargin();
	            // GET CURRENT AUTO-PAGE-BREAK MODE
	            $auto_page_break = $this->getAutoPageBreak();
	            // DISABLE AUTO-PAGE-BREAK
	            $this->SetAutoPageBreak(false, 0);
	            // SET BACKGROUND IMAGE
				$IMAGEN_LOGO = K_ROOT_DIR."/images_appl/COMERCIAL/logo_cotizacion_new.jpg";
				$IMAGEN_PIE = K_ROOT_DIR."/images_appl/COMERCIAL/pie_cotizacion_new.jpg";
	            //$this->Image($imagen_fondo, 0, 0, 612, 792, '', '', '', false, 300, '', false, false, 0);
  				$this->Image($IMAGEN_LOGO,10,10,488,70,'','','',false,300,'',false,false,0,false,false,false);
    			$this->Image($IMAGEN_PIE,-2,750,612,48,'','','',false,300,'',false,false,0,false,false,false);
  				// FRANJA ROJA / PLOMA DE LOS COSTADOS
				$this->Rect(590, 0, 15, 792, 'F', $style4, array(218, 41, 28));
				$this->Rect(7, 0, 15, 792, 'F', $style4, array(211, 211, 211));
	            // RESTORE AUTO-PAGE-BREAK STATUS
	            $this->SetAutoPageBreak($auto_page_break, $bMargin);
	            // SET THE STARTING POINT FOR THE PAGE CONTENT
	            $this->setPageMark();
	            $this->SetTextColor(0,0,0);
	    	    $this->SetFont('helvetica','',9);	
	    		$this->SetTextColor(0,0,127);
	    		$this->SetFont('helvetica','',13);	
	    		$this->SetTextColor(0,0,127);
	    	    $this->SetFont('helvetica','B',15);	
	    		$this->SetXY(525, 20);
	    		$this->Cell(55, 20,utf8_encode('Cotización'),0,0,'R');
	    		$this->SetXY(525, 38);
	    		$this->Cell(55, 20,utf8_encode($result[0]['COD_COTIZACION']),0,0,'R');   
	    		$pagina = $this->PageNo();
	    		$pag_total = $this->getAliasNbPages();
	    		$this->SetTextColor(0,0,0);
	    		$this->SetFont('helvetica','',7);
	    		$this->SetXY(539, 60);
	    		$this->Cell(55, 20,utf8_encode('Pag.'.$pagina.'/'.$pag_total),0,0,'R');  
	    		$this->SetTextColor(0,0,127);
				$this->SetFont('helvetica','',9);	
				$this->SetXY(405, 74);
				$this->Cell(175, 16,utf8_encode('Santiago, '.$result[0]['FECHA_COTIZACION']),0,0,'R'); 
				$this->SetDrawColor(218, 41, 28);
				$this->SetLineWidth(1);
				$this->Line(25,83,440,83); 
        	}
    	}
	    ///// FIN SECCION LOGO ////
    	
		public function Footer() {
    		// POSITION AT 15 MM FROM BOTTOM
    		$this->SetY(-15);
    		// SET FONT
    		$this->SetFont('helvetica', 'I', 8);
    		// PAGE NUMBER
    		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		}
    
	    function folder_producto($cod_producto){
			$cod_producto_folder = preg_replace("%[^A-Z^0-9^-]%", "_", $cod_producto);
			return dirname(__FILE__)."/../../../../../producto_imagen/producto/$cod_producto_folder";
		}
	
		function cod_producto_char($cod_producto){
			$cod_producto = preg_replace("%[^A-Z^0-9^-]%", "_", $cod_producto);
			return $cod_producto;
		}
	}

	///// INI GENERA OBJETO PDF ////
    $pdf = new MYPDF('P', 'pt', 'LETTER', true, 'UTF-8', false);
	$pdf->dataArray = $result;     	//ASIGNO EL RESULT A LA VARIABLE GLOBAL PARA PODER OCUPARLA EN EL HEADER Y FOOTER
	$pdf->SetCreator('SetCreator');
	$pdf->SetAuthor('SetAuthor');
	$pdf->SetTitle('SetTitle');
	$pdf->SetSubject('SetSubject');
	$pdf->SetKeywords('SetKeywords');
    $pdf->setPrintHeader(true);//PARA EVITAR QUE SE DIBUJE UNA LINEA EN LA CABECERA
	$pdf->setPrintFooter(false);//PARA EVITAR QUE SE DIBUJE UNA LINEA EN EL PIE DE PAGINA
	$pdf->SetFooterMargin(0);
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->SetLineWidth(0.3);
	$pdf->AddPage();
	///// FIN GENERA OBJETO PDF ////

	//// INI CAMPOS FIJOS PRIMERA PAGINA ////
	$M_LEFT = 22;
	$M_TOP = 95;
    $pdf->SetTextColor(0,0,127);	    
    $pdf->SetFont('helvetica','B',10);
	$pdf->Text($M_LEFT, $M_TOP,utf8_encode('Señores:'));
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('helvetica','B',9);	
	$pdf->SetXY($M_LEFT, $M_TOP+15);
	$pdf->Cell(350, 20,utf8_encode(substr($result[0]['NOM_EMPRESA'],0,57)),0,0,'L');
	$pdf->SetFont('helvetica','',9);	
	$pdf->SetXY($M_LEFT, $M_TOP+30);
	$pdf->MultiCell(350,20,utf8_encode($result[0]['DIRECCION'].' - '.$result[0]['NOM_COMUNA'].' - '.$result[0]['NOM_CIUDAD'].'.'), 0, 'L');
	$RUT = $result[0]['RUT'];
	$DIG_VERIF = $result[0]['DIG_VERIF'];
	$RUT=number_format($RUT, 0, ',', '.');
	$RUT=$RUT.'-'.$DIG_VERIF;
	$pdf->SetTextColor(0,0,127);
	$pdf->SetFont('helvetica','B',9);
	$pdf->Text($M_LEFT, $M_TOP+57,utf8_encode('Rut:'));
	$pdf->SetFont('helvetica','B',9);
	$pdf->SetTextColor(0,0,0);
	$pdf->Text($M_LEFT+20, $M_TOP+57,utf8_encode($RUT));
	$pdf->SetTextColor(0,0,127);
	$pdf->SetFont('helvetica','B',9);
	$pdf->Text($M_LEFT+108, $M_TOP+57,utf8_encode('Fono:'));
	$pdf->SetFont('helvetica','',9);
	$pdf->SetTextColor(0,0,0);
	$pdf->Text(159, $M_TOP+57,utf8_encode($result[0]['TELEFONO_F']));
    $pdf->SetFont('helvetica','B',10);
    $pdf->SetTextColor(0,0,127);
	$pdf->Text(400, $M_TOP,utf8_encode('Atención:'));	
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('helvetica','',9);	
	$pdf->SetXY(400, $M_TOP+15);
	$pdf->Cell(340, 20,utf8_encode('Sr(a): '.$result[0]['NOM_PERSONA']),0,0,'L');
	$pdf->SetTextColor(0,0,127);
	$pdf->SetFont('helvetica','B',9);
	$pdf->Text(400, $M_TOP+57,utf8_encode('Fono:'));
	$pdf->SetFont('helvetica','',9);
	$pdf->SetTextColor(0,0,0);
	$pdf->Text(400, $M_TOP+30,utf8_encode($result[0]['EMAIL']));
	$pdf->SetTextColor(0,0,0);
	$pdf->Text(428, $M_TOP+57,utf8_encode($result[0]['TELEFONO']));
	$pdf->SetTextColor(0,0,127);
	$pdf->SetFont('helvetica','B',9);
	$pdf->Text($M_LEFT, $M_TOP+78,utf8_encode('Referencia:'));
	$pdf->SetFont('helvetica','B',9);
	$pdf->SetTextColor(0,0,0);
	$pdf->Text($M_LEFT+52, $M_TOP+78,utf8_encode($result[0]['REFERENCIA']));
	$pdf->SetTextColor(0,0,127);
	$pdf->SetFont('helvetica','B',9);
	$pdf->Text(516, $M_TOP+78,utf8_encode('Vendedor:'));
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('helvetica','B',9);
	$pdf->SetXY(548, $M_TOP+75);
	$pdf->Cell(35, 17,utf8_encode($result[0]['INI_USUARIO']),0,0,'R');
	//// FIN CAMPOS FIJOS PRIMERA PAGINA ////


	//// INI VARIABLES USADAS EN TODOS LOS TIPOS DE COTIZACION ////
	$PORC_DSCTO1 = $result[0]['PORC_DSCTO1'];
	$PORC_DSCTO2 = $result[0]['PORC_DSCTO2'];
	$COT_TIPO_DSCTO = $result[0]['TIPO_DESCUENTOS'];
	$M_LEFT = 26;	 
	$COD_FORMA_PAGO = $result[0]['COD_FORMA_PAGO']; 	
	//// FIN VARIABLES USADAS EN TODOS LOS TIPOS DE COTIZACION ////


	//// INI SI $TIPO_DESC == 'DSCTO_TOTAL' SE DEBE COMPORTAR COMO UNA COTIZACION SIN DESCUENTOS ////
	$AUX_COT_TIPO_DSCTO = $COT_TIPO_DSCTO;
	if ($TIPO_DESC == 'DSCTO_TOTAL'){	
		$COT_TIPO_DSCTO = 1;	
	}
	//// FIN SI $TIPO_DESC == 'DSCTO_TOTAL' SE DEBE COMPORTAR COMO UNA COTIZACION SIN DESCUENTOS ////

	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//// INI DESCUENTOS INGRESADOS A MANO EN COTIZACION PUEDEN PRESENTAR PROBLEMAS EN VERSION DE DESCT EN ITEMS MH 07-02-2020 ///
	$REV_SUBTOTAL = $result[0]['SUBTOTAL'];
	$REV_PORC_DSCTO1 = $result[0]['PORC_DSCTO1']; 
	$REV_MONTO_DSCTO1 = $result[0]['MONTO_DSCTO1']; 
	$REV_PORC_DSCTO2 = $result[0]['PORC_DSCTO2']; 
	$REV_MONTO_DSCTO2 = $result[0]['MONTO_DSCTO2']; 
	$NEW_PORC_DSCTO2_DSCTO2 = $result[0]['PORC_DSCTO2'];
	$AVISO_ERROR_MONTO_DSCTO2 = '0';	

	$C_SUBTOTAL_2 = $REV_SUBTOTAL - $REV_MONTO_DSCTO1;
	$C_MONTO_DESCTO2 = (($C_SUBTOTAL_2 * $REV_PORC_DSCTO2) / 100);
	
	IF ($C_MONTO_DESCTO2 <> $REV_MONTO_DSCTO2){
		$AVISO_ERROR_MONTO_DSCTO2 = '1';
		$NEW_PORC_DSCTO2_DSCTO2 = (($REV_MONTO_DSCTO2 * 100) / $C_SUBTOTAL_2);
	}ELSE{
		$AVISO_ERROR_MONTO_DSCTO2 = '0';
	}
	
	// SE CONTROLA SOLO DIFERENCIAS DE MONTO_DSCTO2 INGRESADO A MANO, MONTO_DSCTO1 NO SE CONTROLA EN ESTE INICIO MH 07-02-2020 
	
	//// FIN DESCUENTOS INGRESADOS A MANO EN COTIZACION PUEDEN PRESENTAR PROBLEMAS EN VERSION DE DESCT EN ITEMS MH 07-02-2020 ///
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//// INI DIMENSIONA LOS TAMAÑOS DE COLUMNAS ////
	if ($COT_TIPO_DSCTO == 1){


		if ($TIPO_COT == 'AMPLIADA_PDF'){
			$pdf->W_I 	=	27;		//$W_ITEM
			$pdf->W_NP 	=	276;	//$W_NOM_PRODUCTO
			$pdf->W_CP 	=	80;		//$W_COD_PRODUCTO
			$pdf->W_C 	=	50;		//$W_CANTIDAD
			$pdf->W_PU 	=	63;		//$W_PRECIO_UNITARIO
			$pdf->W_MD 	=	0;		//$W_MONTO_DESCUENTO
			$pdf->W_PCD =	0;		//$W_PRECIO_CON_DESCUENTO
			$pdf->W_T 	=	64; 	//$W_TOTAL
		}else{
			$pdf->W_I 	=	27;		//$W_ITEM
			$pdf->W_NP 	=	330;	//$W_NOM_PRODUCTO
			$pdf->W_CP 	=	50;		//$W_COD_PRODUCTO
			$pdf->W_C 	=	26;		//$W_CANTIDAD
			$pdf->W_PU 	=	63;		//$W_PRECIO_UNITARIO
			$pdf->W_MD 	=	0;		//$W_MONTO_DESCUENTO
			$pdf->W_PCD =	0;		//$W_PRECIO_CON_DESCUENTO
			$pdf->W_T 	=	64; 	//$W_TOTAL
		}

		/*
		$pdf->W_I 	=	27;		//$W_ITEM
		$pdf->W_NP 	=	276;	//$W_NOM_PRODUCTO
		$pdf->W_CP 	=	80;		//$W_COD_PRODUCTO
		$pdf->W_C 	=	50;		//$W_CANTIDAD
		$pdf->W_PU 	=	63;		//$W_PRECIO_UNITARIO
		$pdf->W_MD 	=	0;		//$W_MONTO_DESCUENTO
		$pdf->W_PCD =	0;		//$W_PRECIO_CON_DESCUENTO
		$pdf->W_T 	=	64; 	//$W_TOTAL
		*/
	}
	if ($COT_TIPO_DSCTO == 2){

		$pdf->W_I 	=	28;		//$W_ITEM
		$pdf->W_NP 	=	236;	//$W_NOM_PRODUCTO
		$pdf->W_CP 	=	59;		//$W_COD_PRODUCTO
		$pdf->W_C 	=	24;		//$W_CANTIDAD
		$pdf->W_PU 	=	52;		//$W_PRECIO_UNITARIO
		$pdf->W_MD 	=	52;		//$W_MONTO_DESCUENTO
		$pdf->W_PCD =	52;		//$W_PRECIO_CON_DESCUENTO
		$pdf->W_T 	=	57; 	//$W_TOTAL		
	}
	if ($COT_TIPO_DSCTO == 3){
	
		$pdf->W_I 	=	28;		//$W_ITEM
		$pdf->W_NP 	=	236;	//$W_NOM_PRODUCTO
		$pdf->W_CP 	=	59;		//$W_COD_PRODUCTO
		$pdf->W_C 	=	24;		//$W_CANTIDAD
		$pdf->W_PU 	=	52;		//$W_PRECIO_UNITARIO
		$pdf->W_MD 	=	52;		//$W_MONTO_DESCUENTO
		$pdf->W_PCD =	52;		//$W_PRECIO_CON_DESCUENTO
		$pdf->W_T 	=	57; 	//$W_TOTAL	
	}
	//// FIN DIMENSIONA LOS TAMAÑOS DE COLUMNAS ////

	//// INI CABECERAS COLUMNAS ////
	$pdf->SetFillColor(226, 226, 226);
	$pdf->SetTextColor(0,0,127);
	$pdf->SetFont('helvetica','B',8);
	$pdf->SetXY($M_LEFT, 200);
	$pdf->Cell($pdf->W_I, 23,utf8_encode('Item'),1,0,'C',1);
	$pdf->Cell($pdf->W_NP, 23,utf8_encode('Producto'),1,0,'C',1);
	$pdf->Cell($pdf->W_CP, 23,utf8_encode('Modelo'),1,0,'C',1);
	$pdf->Cell($pdf->W_C, 23,utf8_encode('Cant.'),1,0,'C',1);
	$pdf->Cell($pdf->W_PU, 23,utf8_encode('Precio'),1,0,'C',1);

	if ($COT_TIPO_DSCTO <> 1){
		if ($COT_TIPO_DSCTO == 2){
			$pdf->MultiCell($pdf->W_MD, 23,utf8_encode('Descuento '.$result[0]['PORC_DSCTO1'].'%'),1,'C',1,0);
		}else{
			$pdf->MultiCell($pdf->W_MD, 23,utf8_encode('Descuento '.$result[0]['PORC_DSCTO1'].'% + '.$result[0]['PORC_DSCTO2'].'%'),1,'C',1,0);			
		}
		$pdf->MultiCell($pdf->W_PCD, 23,utf8_encode('Precio c/Desc'),1,'C',1,0);
	}
	$pdf->Cell($pdf->W_T, 23,utf8_encode('Total'),1,0,'C',1);
	$pdf->setY(223);
	//// FIN CABECERAS COLUMNAS ////

	//// INI DESPLIEGUE DE ITEMS ////
	for($i = 0;$i < count($result); $i++){

		//// INI CABECERA 2DA PAGINA ////

		$ADD_PAGE = 0;

		if ($TIPO_COT == 'AMPLIADA_PDF'){
			if($pdf->GetY() > 550){
				$ADD_PAGE = 1;	
			}
		}else{
			if($pdf->GetY() > 680){
				$ADD_PAGE = 1;	
			}
		}

		if($ADD_PAGE == 1){
	        $pdf->AddPage();
			$pdf->SetTextColor(0,0,127);
			$pdf->SetFont('helvetica','B',8);
			$pdf->SetXY($M_LEFT, 95);
			$pdf->Cell($pdf->W_I, 23,utf8_encode('Item'),1,0,'C',1);
			$pdf->Cell($pdf->W_NP, 23,utf8_encode('Producto'),1,0,'C',1);
			$pdf->Cell($pdf->W_CP, 23,utf8_encode('Modelo'),1,0,'C',1);
			$pdf->Cell($pdf->W_C, 23,utf8_encode('Cant.'),1,0,'C',1);
			$pdf->Cell($pdf->W_PU, 23,utf8_encode('Precio'),1,0,'C',1);

			if ($COT_TIPO_DSCTO <> 1){
				if ($COT_TIPO_DSCTO == 2){
					$pdf->MultiCell($pdf->W_MD, 23,utf8_encode('Descuento '.$result[0]['PORC_DSCTO1'].'%'),1,'C',1,0);
				}else{
					$pdf->MultiCell($pdf->W_MD, 23,utf8_encode('Descuento '.$result[0]['PORC_DSCTO1'].'% + '.$result[0]['PORC_DSCTO2'].'%'),1,'C',1,0);			
				}
				$pdf->MultiCell($pdf->W_PCD, 23,utf8_encode('Precio c/Desc'),1,'C',1,0);
			}

			$pdf->Cell($pdf->W_T, 23,utf8_encode('Total'),1,0,'C',1);
	        $pdf->setY(118); // DEFINE POSICIONES PARA LAS SIGUIENTES FILAS DESPUES DE LAS CABECERAS
	    }
	    //// FIN CABECERA 2DA PAGINA ////


		//// INI CONTROLA DOBLE LINEA DE NOM_PRODUCTO ////

		    	// SE ASUME CALCULO QUE EL ALTO IDEAL PARA CADA ITEM ES 13
	    		$H_ITEM = 13;
		    	$H_PRODUCTO = $H_ITEM; 
	    		$H_ITEM_NB = 13;
		    	$H_PRODUCTO_NB = $H_ITEM_NB; 


    			//////// INI CALCULA LA ALTURA DE NOM_PRODUCTO CUANDO USA NEGRITA BY MH
				$pdf->startTransaction();
				$start_y = $pdf->GetY();
				$start_page = $pdf->getPage();
				// SE DEBE SETEAR MISMA FONT Y POS X QUE SE USARA EN LA IMPRESION OFICIAL DE NOM_PRODUCTO		
				$pdf->SetFont('helvetica','B',8);	
		   	 	$pdf->SetX($M_LEFT);
				$pdf->MultiCell($pdf->W_NP, $H_PRODUCTO,utf8_encode($result[$i]['NOM_PRODUCTO']),1,'L',false,1);
				$end_y = $pdf->GetY();
				$end_page = $pdf->getPage();
				$height = 0;
				if ($end_page == $start_page) {
					$height = $end_y - $start_y;
				} else {
						for ($page=$start_page; $page <= $end_page; ++$page) {
							$pdf->setPage($page);
					        if ($page == $start_page) {
					            // first page
					            $height = $pdf->getPageHeight() - $start_y - $pdf->getBreakMargin();
					        } elseif ($page == $end_page) {
					        //MH 10-03-2020 el ejemplo indicaba la linea que viene pero se cae por que getMargins() es un array
					            // last page
					            //$height = $end_y - $pdf->getMargins();
					        //MH 10-03-2020 optimizacion del ejemplo, ahora funciona.
					            // last page					            
								$v_getMargins = $pdf->getMargins();
								$v_mtop = $v_getMargins['top'];	
					            $height = $end_y - $v_mtop;
					            //$height = $end_y - $pdf->getMargins()['top'];	
					        } else {
					        	$v_getMargins = $pdf->getMargins();
								$v_mtop = $v_getMargins['top'];
					            $height = $pdf->getPageHeight() - $v_mtop - $pdf->getBreakMargin();
					        }
						}
				}
				$pdf = $pdf->rollbackTransaction();
				if ($H_PRODUCTO < $height) {
						$H_ITEM  = $height + 2;
				}


				//////// INI CALCULA LA ALTURA DE NOM_PRODUCTO CUANDO NO USA NEGRITA BY MH
				$pdf->startTransaction();
				$start_y = $pdf->GetY();
				$start_page = $pdf->getPage();
				// SE DEBE SETEAR MISMA FONT Y POS X QUE SE USARA EN LA IMPRESION OFICIAL DE NOM_PRODUCTO			
				$pdf->SetFont('helvetica','',8);	
		   	 	$pdf->SetX($M_LEFT);
				$pdf->MultiCell($pdf->W_NP, $H_PRODUCTO_NB,utf8_encode($result[$i]['NOM_PRODUCTO']),1,'L',false,1);
				$end_y = $pdf->GetY();
				$end_page = $pdf->getPage();
				$height = 0;
				if ($end_page == $start_page) {
					$height = $end_y - $start_y;
				} else {
						for ($page=$start_page; $page <= $end_page; ++$page) {
							$pdf->setPage($page);
					        if ($page == $start_page) {
					            // first page
					            $height = $pdf->getPageHeight() - $start_y - $pdf->getBreakMargin();
					        } elseif ($page == $end_page) {
					        //MH 10-03-2020 el ejemplo indicaba la linea que viene pero se cae por que getMargins() es un array
					            // last page
					            //$height = $end_y - $pdf->getMargins();
					        //MH 10-03-2020 optimizacion del ejemplo, ahora funciona.
					            // last page					            
								$v_getMargins = $pdf->getMargins();
								$v_mtop = $v_getMargins['top'];	
					            $height = $end_y - $v_mtop;
					            //$height = $end_y - $pdf->getMargins()['top'];	
					        } else {
					        	$v_getMargins = $pdf->getMargins();
								$v_mtop = $v_getMargins['top'];
					            $height = $pdf->getPageHeight() - $v_mtop - $pdf->getBreakMargin();
					        }
						}
				}
				$pdf = $pdf->rollbackTransaction();
				if ($H_PRODUCTO_NB < $height) {
						$H_ITEM_NB  = $height + 2;
				}	

				// CUANDO ES IMPRESION RESUMEN NINGUN ITEM VA CON NEGRITA
			    if ($TIPO_COT == 'RESUMEN_PDF'){
					$H_ITEM = $H_ITEM_NB;
				}

    			//////// FIN CALCULA LA ALTURA DE NOM_PRODUCTO	BY MH

		//// FIN CONTROLA DOBLE LINEA DE NOM_PRODUCTO ////
		
		$COD_PRODUCTO_ITEM = $result[$i]['COD_PRODUCTO'];
		

		if ($COD_PRODUCTO_ITEM <> 'T'){


	    	$pdf->SetFont('helvetica','',7);
	    	$pdf->SetTextColor(0,0,127);
		    $pdf->SetX($M_LEFT);
		    $pdf->MultiCell($pdf->W_I, $H_ITEM,utf8_encode($result[$i]['ITEM']),1,'C',false,0);
		    
			if ($TIPO_COT == 'AMPLIADA_PDF'){
				$pdf->SetFont('helvetica','B',8);
			}else{
				$pdf->SetFont('helvetica','',8);
			}
		    $pdf->MultiCell($pdf->W_NP, $H_ITEM,utf8_encode($result[$i]['NOM_PRODUCTO']),1,'L',false,0);
		    $pdf->MultiCell($pdf->W_CP, $H_ITEM,utf8_encode($result[$i]['COD_PRODUCTO']),1,'L',false,0);
		    $pdf->MultiCell($pdf->W_C, $H_ITEM,utf8_encode($result[$i]['CANTIDAD']),1,'R',false,0);



			// number_format($result[$i]['PRECIO'], 0, ',', '.') formato para decimales en las cantidades.


			if ($COT_TIPO_DSCTO == 1){
				$pdf->MultiCell($pdf->W_PU, $H_ITEM,utf8_encode(number_format($result[$i]['PRECIO'], 0, ',', '.')),1,'R',1,0);
			}else{
				$pdf->MultiCell($pdf->W_PU, $H_ITEM,utf8_encode(number_format($result[$i]['PRECIO'], 0, ',', '.')),1,'R',false,0);				
			}
	
			if ($COT_TIPO_DSCTO == 1){
				$PRECIO_TOTAL_ITEM = $result[$i]['PRECIO'] * $result[$i]['CANTIDAD'];
			}else{	
				if ($COT_TIPO_DSCTO == 2){

				    $MONTO_DSCTO_ITEM = ($result[$i]['PRECIO'] * ($result[0]['PORC_DSCTO1']/100));
				    $PRECIO_ITEM_CON_DSCTO = ($result[$i]['PRECIO'] - $MONTO_DSCTO_ITEM);
				    $PRECIO_TOTAL_ITEM = ($PRECIO_ITEM_CON_DSCTO * $result[$i]['CANTIDAD']);
				}else{
					if ($COT_TIPO_DSCTO == 3){

					    $MONTO_DSCTO_ITEM1 = ($result[$i]['PRECIO'] * ($result[0]['PORC_DSCTO1']/100));
					    $PRECIO_ITEM_CON_DSCTO1 = ($result[$i]['PRECIO'] - $MONTO_DSCTO_ITEM1);
						
					    $MONTO_DSCTO_ITEM2 = ($PRECIO_ITEM_CON_DSCTO1 * ($result[0]['PORC_DSCTO2']/100));
					    $PRECIO_ITEM_CON_DSCTO2 = ($PRECIO_ITEM_CON_DSCTO1 - $MONTO_DSCTO_ITEM2);

						/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						//// INI DESCUENTOS INGRESADOS A MANO EN COTIZACION PUEDEN PRESENTAR PROBLEMAS EN VERSION DE DESCT EN ITEMS MH 07-02-2020 ///
						IF ($AVISO_ERROR_MONTO_DSCTO2 == 1){
							
							$MONTO_DSCTO_ITEM2 = ($PRECIO_ITEM_CON_DSCTO1 * ($NEW_PORC_DSCTO2_DSCTO2/100));
							$PRECIO_ITEM_CON_DSCTO2 = ($PRECIO_ITEM_CON_DSCTO1 - $MONTO_DSCTO_ITEM2);
						}
						/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						//// INI DESCUENTOS INGRESADOS A MANO EN COTIZACION PUEDEN PRESENTAR PROBLEMAS EN VERSION DE DESCT EN ITEMS MH 07-02-2020 ///
	
	
					    $MONTO_DSCTO_ITEM =  ($MONTO_DSCTO_ITEM1 + $MONTO_DSCTO_ITEM2);
					    $PRECIO_ITEM_CON_DSCTO = ($result[$i]['PRECIO'] - ($MONTO_DSCTO_ITEM));
					    $PRECIO_TOTAL_ITEM = ($PRECIO_ITEM_CON_DSCTO2 * $result[$i]['CANTIDAD']);
					}
				}
			}	
			if ($COT_TIPO_DSCTO <> 1){
				$pdf->MultiCell($pdf->W_MD, $H_ITEM,utf8_encode(number_format($MONTO_DSCTO_ITEM, 0, ',', '.')),1,'R',false,0);    
			    $pdf->MultiCell($pdf->W_PCD, $H_ITEM,utf8_encode(number_format($PRECIO_ITEM_CON_DSCTO, 0, ',', '.')),1,'R',1,0);
			}
			$pdf->MultiCell($pdf->W_T, $H_ITEM,utf8_encode(number_format($PRECIO_TOTAL_ITEM, 0, ',', '.')),1,'R',false,1);
			
			//if (($COD_PRODUCTO_ITEM <> 'F') && ($COD_PRODUCTO_ITEM <> 'TE') && ($COD_PRODUCTO_ITEM <> 'E') && ($COD_PRODUCTO_ITEM <> 'I') && ($TIPO_COT == 'AMPLIADA_PDF')){
			// 07112019 RE SOLICITA QUE SE IMPRIMAN LOS ATRIBUTOS DE PRODUCTOS "I" Y "E"
			if (($COD_PRODUCTO_ITEM <> 'F') && ($COD_PRODUCTO_ITEM <> 'TE') && ($TIPO_COT == 'AMPLIADA_PDF')){
				
				$TIENE_FOTO_ITEM_PRODUCTO = 0;
				$TIENE_ATRIBUTO_ITEM_PRODUCTO = 0;
				$cod_producto = $result[$i]['COD_PRODUCTO'];  
				
		    	// SE DEFINE SI PRODUCTO TIENE FOTO DISPONIBLE EN FTP
			    if(file_exists($pdf->folder_producto($cod_producto).'/'.$pdf->cod_producto_char($cod_producto).'_CAT1.jpg')){    
			        $TIENE_FOTO_ITEM_PRODUCTO = 1;
			        $ruta_img = $pdf->folder_producto($cod_producto).'/'.$pdf->cod_producto_char($cod_producto).'_CAT1.jpg';
			    }else{
			    	$ruta_img = dirname(__FILE__).'/../../../../../producto_imagen/parametro/foto_no_disponible.jpg';
			    }			
				// DEFINE SI PRODUCTO TIENE ATRIBUTOS  
			    if ($result[$i]['ATRIBUTO_PRODUCTO'] <> 'SIN_ATRIBUTO') {
			    	$TIENE_ATRIBUTO_ITEM_PRODUCTO = 1;					    
			    }

		    	// SE ASUME CALCULO QUE EL ALTO IDEAL PARA LA IMAGEN ES DE 130
		    	$H_DETALLE = 130; 


    			//////// INI CALCULA LA ALTURA DE ATRIBUTO_PRODUCTO BY MH
				$pdf->startTransaction();
				$start_y = $pdf->GetY();
				$start_page = $pdf->getPage();
				// SE DEBE SETEAR MISMA FONT Y POS X QUE SE USARA EN LA IMPRESION OFICIAL DE ATRIBUTO_PRODUCTO		
				$pdf->SetFont('helvetica','',8);	
		   	 	$pdf->SetX($M_LEFT);
				$pdf->MultiCell($pdf->W_NP, $H_DETALLE,utf8_encode($result[$i]['ATRIBUTO_PRODUCTO']),1,'L',false,1);
				$end_y = $pdf->GetY();
				$end_page = $pdf->getPage();
				$height = 0;
				if ($end_page == $start_page) {
					$height = $end_y - $start_y;
				} else {
						for ($page=$start_page; $page <= $end_page; ++$page) {
							$pdf->setPage($page);
					        if ($page == $start_page) {
					            // first page
					            $height = $pdf->getPageHeight() - $start_y - $pdf->getBreakMargin();
					        } elseif ($page == $end_page) {
					        //MH 10-03-2020 el ejemplo indicaba la linea que viene pero se cae por que getMargins() es un array
					            // last page
					            //$height = $end_y - $pdf->getMargins();
					        //MH 10-03-2020 optimizacion del ejemplo, ahora funciona.
					            // last page					            
								$v_getMargins = $pdf->getMargins();
								$v_mtop = $v_getMargins['top'];	
					            $height = $end_y - $v_mtop;
					            //$height = $end_y - $pdf->getMargins()['top'];	
					        } else {
					        	$v_getMargins = $pdf->getMargins();
								$v_mtop = $v_getMargins['top'];
					            $height = $pdf->getPageHeight() - $v_mtop - $pdf->getBreakMargin();
					        }
						}
				}
				$pdf = $pdf->rollbackTransaction();
				if ($H_DETALLE < $height) {
						$H_DETALLE = $height + 2;
				}	
    			//////// FIN CALCULA LA ALTURA DE ATRIBUTO_PRODUCTO	BY MH

		    	// SE DEBE SETEAR MISMA FONT Y POS X QUE SE USO EN EL CALCULO DEL ALTO DE DE ATRIBUTO_PRODUCTO	
		    	$pdf->SetFont('helvetica','',8);	
		   	 	$pdf->SetX($M_LEFT);
		    	$pdf->MultiCell($pdf->W_I, $H_DETALLE,'',1,'C',false,0);


			    if ($result[$i]['ATRIBUTO_PRODUCTO'] <> 'SIN_ATRIBUTO') {
			    	$pdf->MultiCell($pdf->W_NP, $H_DETALLE,utf8_encode($result[$i]['ATRIBUTO_PRODUCTO']),1,'L',false,0);
			    	$TIENE_ATRIBUTO_ITEM_PRODUCTO = 1;					    
			    }else{
					$pdf->MultiCell($pdf->W_NP, $H_DETALLE,utf8_encode('  '),1,'L',false,0);
			    }



		    	//$pdf->MultiCell($pdf->W_NP, $H_DETALLE,utf8_encode($result[$i]['ATRIBUTO_PRODUCTO']),1,'L',false,0);
		    	$USA_FOTO_ANTIGUA = $result[$i]['USA_FOTO_ANTIGUA'];

				if ($COT_TIPO_DSCTO == 1){

		 				if($USA_FOTO_ANTIGUA == 'S'){
				        	$pdf->Image($ruta_img,$pdf->GetX()+17,$pdf->GetY()+1,95,0,'','','C',false,300,'',false,false,0,true,false,false,false,false);
				    	}else
				    		$pdf->Image($ruta_img,$pdf->GetX()+1,$pdf->GetY()+1,128,0,'','','C',false,300,'',false,false,0,true,false,false,false,false);
				    	$SET_X = 459;

				    	$pdf->SetX($SET_X);
				    	$pdf->MultiCell($pdf->W_PU, $H_DETALLE,'',1,'C',1,0);
				    	$pdf->MultiCell($pdf->W_T, $H_DETALLE,'',1,'C',false,1);
				    	$AUX_Y = $pdf->GetY();
		    			//LINEA PARA CERRAR CELDA DEL ULTIMO ITEM, EN COT_RESUMEN NO SE DEBE MOSTRAR ESTA LINEA	
						$pdf->Line(328,$AUX_Y,463,$AUX_Y);

				}else{	
					if ($COT_TIPO_DSCTO == 2){

		 				if($USA_FOTO_ANTIGUA == 'S'){
				        	$pdf->Image($ruta_img,$pdf->GetX()+20,$pdf->GetY()+1,95,0,'','','C',false,300,'',false,false,0,true,false,false,false,false);
				    	}else
				    		$pdf->Image($ruta_img,$pdf->GetX()+3,$pdf->GetY()+1,128,0,'','','C',false,300,'',false,false,0,true,false,false,false,false);

				    	$SET_X = 425;
				    	$pdf->SetX($SET_X);
				    	$pdf->MultiCell($pdf->W_MD, $H_DETALLE,'',1,'C',false,0);
				    	$pdf->MultiCell($pdf->W_PCD, $H_DETALLE,'',1,'C',1,0);
				    	$pdf->MultiCell($pdf->W_T, $H_DETALLE,'',1,'C',false,1);
				    	$AUX_Y = $pdf->GetY();
		    			//LINEA PARA CERRAR CELDA DEL ULTIMO ITEM, EN COT_RESUMEN NO SE DEBE MOSTRAR ESTA LINEA	
						$pdf->Line(281,$AUX_Y,425,$AUX_Y);
					}else{
						if ($COT_TIPO_DSCTO == 3){

			 				if($USA_FOTO_ANTIGUA == 'S'){
					        	$pdf->Image($ruta_img,$pdf->GetX()+20,$pdf->GetY()+1,95,0,'','','C',false,300,'',false,false,0,true,false,false,false,false);
					    	}else
					    		$pdf->Image($ruta_img,$pdf->GetX()+3,$pdf->GetY()+1,128,0,'','','C',false,300,'',false,false,0,true,false,false,false,false);

					    	$SET_X = 425;
					    	$pdf->SetX($SET_X);
					    	$pdf->MultiCell($pdf->W_MD, $H_DETALLE,'',1,'C',false,0);
					    	$pdf->MultiCell($pdf->W_PCD, $H_DETALLE,'',1,'C',1,0);
					    	$pdf->MultiCell($pdf->W_T, $H_DETALLE,'',1,'C',false,1);
					    	$AUX_Y = $pdf->GetY();
		    				//LINEA PARA CERRAR CELDA DEL ULTIMO ITEM, EN COT_RESUMEN NO SE DEBE MOSTRAR ESTA LINEA	
							$pdf->Line(281,$AUX_Y,425,$AUX_Y);
						}
					}
				}	
			}
			$AUX_Y = $pdf->GetY();


		}else{
			// CUANDO ES UN TITULO SOLO DESPLIEGA NOM_PRODUCTO 
			$pdf->SetFont('helvetica','B',8);
			$pdf->SetTextColor(0,0,0);
			$pdf->SetX($M_LEFT);
			$pdf->MultiCell($pdf->W_I, $H_ITEM,utf8_encode(''),1,'C',false,0);
			$pdf->MultiCell($pdf->W_NP, $H_ITEM,utf8_encode($result[$i]['NOM_PRODUCTO']),1,'L',false,0);
			$pdf->MultiCell($pdf->W_CP, $H_ITEM,utf8_encode(''),1,'L',false,0);
			$pdf->MultiCell($pdf->W_C, $H_ITEM,utf8_encode(''),1,'R',false,0);
			
			if ($COT_TIPO_DSCTO == 1){
				$pdf->MultiCell($pdf->W_PU, $H_ITEM,utf8_encode(''),1,'R',1,0);
			}else{
				$pdf->MultiCell($pdf->W_PU, $H_ITEM,utf8_encode(''),1,'R',false,0);
				$pdf->MultiCell($pdf->W_MD, $H_ITEM,utf8_encode(''),1,'R',false,0);    
				$pdf->MultiCell($pdf->W_PCD, $H_ITEM,utf8_encode(''),1,'R',1,0);
			}
			$pdf->MultiCell($pdf->W_T, $H_ITEM,utf8_encode(''),1,'R',false,1);
			$AUX_Y = $pdf->GetY();
		}
		
	}
	//// FIN DESPLIEGUE DE ITEMS ////
		
	//// INI PIE PAGINA COMUN ////
	
	// CONSULTA EL VALOR GetY() PARA SABER SI DESPLIEGA CUADRO DE TOTALES.
	if($pdf->GetY() > 650){
		$pdf->AddPage();
		$AUX_Y = 90;		
	}


	// CUADRO DE TOTALES
	if($AUX_COT_TIPO_DSCTO == 1){

			$pdf->SetFont('helvetica','B',8);
			$pdf->SetTextColor(0,0,127);//AZUL RE
			$pdf->SetXY($M_LEFT+451,$AUX_Y+10);
			$pdf->Cell(50, 18,utf8_encode('NETO $'),0,0,'R',0);
			$pdf->Cell(59, 18,utf8_encode(number_format($result[0]['TOTAL_NETO'], 0, ',', '.')),0,0,'R',0);
			$pdf->SetXY($M_LEFT+451,$AUX_Y+20);
			$pdf->Cell(50, 18,utf8_encode($result[0]['PORC_IVA'].'% IVA $'),0,0,'R',0);
			$pdf->Cell(59, 18,utf8_encode(number_format($result[0]['MONTO_IVA'], 0, ',', '.')),0,0,'R',0);
			$pdf->SetXY($M_LEFT+451,$AUX_Y+31);
			$pdf->Cell(50, 18,utf8_encode('TOTAL $'),0,0,'R',0);
			$pdf->Cell(59, 18,utf8_encode(number_format($result[0]['TOTAL_CON_IVA'], 0, ',', '.')),0,0,'R',0);
			$pdf->Rect($M_LEFT+433, $AUX_Y+10, 127, 25,			   0, 				       '', ''); // NETO + IVA
			$pdf->Rect($M_LEFT+433, $AUX_Y+35, 127, 15,			   0, 				       '', ''); // TOTAL CON IVA	
	}else{

			if($TIPO_DESC == 'DSCTO_ITEM'){

				$pdf->SetFont('helvetica','B',8);
				$pdf->SetTextColor(0,0,127);//AZUL RE
				$pdf->SetXY($M_LEFT+451,$AUX_Y+10);
				$pdf->Cell(50, 18,utf8_encode('NETO $'),0,0,'R',0);
				$pdf->Cell(59, 18,utf8_encode(number_format($result[0]['TOTAL_NETO'], 0, ',', '.')),0,0,'R',0);
				$pdf->SetXY($M_LEFT+451,$AUX_Y+20);
				$pdf->Cell(50, 18,utf8_encode($result[0]['PORC_IVA'].'% IVA $'),0,0,'R',0);
				$pdf->Cell(59, 18,utf8_encode(number_format($result[0]['MONTO_IVA'], 0, ',', '.')),0,0,'R',0);
				$pdf->SetXY($M_LEFT+451,$AUX_Y+31);
				$pdf->Cell(50, 18,utf8_encode('TOTAL $'),0,0,'R',0);
				$pdf->Cell(59, 18,utf8_encode(number_format($result[0]['TOTAL_CON_IVA'], 0, ',', '.')),0,0,'R',0);
				$pdf->Rect($M_LEFT+451, $AUX_Y+10, 109, 25,			   0, 				       '', ''); // NETO + IVA
				$pdf->Rect($M_LEFT+451, $AUX_Y+35, 109, 15,			   0, 				       '', ''); // TOTAL CON IVA
			
			}else{
				if($AUX_COT_TIPO_DSCTO == 2){
					$pdf->SetFont('helvetica','B',8);
					$pdf->SetTextColor(0,0,127);//AZUL RE
					$pdf->SetXY($M_LEFT+448,$AUX_Y+10);
					$pdf->Cell(50, 18,utf8_encode('SUBTOTAL $'),0,0,'R',0);
					$pdf->Cell(62, 18,utf8_encode(number_format($result[0]['SUBTOTAL'], 0, ',', '.')),0,0,'R',0);
					$pdf->SetXY($M_LEFT+448,$AUX_Y+20);
					$pdf->Cell(50, 18,utf8_encode('Descuento '.number_format($result[0]['PORC_DSCTO1'], 1, ',', '.').'% $'),0,0,'R',0);
					$pdf->Cell(62, 18,utf8_encode(number_format($result[0]['MONTO_DSCTO1'], 0, ',', '.')),0,0,'R',0);
					$pdf->SetXY($M_LEFT+448,$AUX_Y+30);
					$pdf->Cell(50, 18,utf8_encode('NETO $'),0,0,'R',0);
					$pdf->Cell(62, 18,utf8_encode(number_format($result[0]['TOTAL_NETO'], 0, ',', '.')),0,0,'R',0);
					$pdf->SetXY($M_LEFT+448,$AUX_Y+40);
					$pdf->Cell(50, 18,utf8_encode($result[0]['PORC_IVA'].'% IVA $'),0,0,'R',0);
					$pdf->Cell(62, 18,utf8_encode(number_format($result[0]['MONTO_IVA'], 0, ',', '.')),0,0,'R',0);
					$pdf->SetXY($M_LEFT+448,$AUX_Y+52);
					$pdf->Cell(50, 18,utf8_encode('TOTAL $'),0,0,'R',0);
					$pdf->Cell(62, 18,utf8_encode(number_format($result[0]['TOTAL_CON_IVA'], 0, ',', '.')),0,0,'R',0);
					$pdf->Rect($M_LEFT+421, $AUX_Y+10, 139, 45,			   0, 				       '', '');
					$pdf->Rect($M_LEFT+421, $AUX_Y+55, 139, 15,			   0, 				       '', '');
				
				}else{

					$pdf->SetFont('helvetica','B',8);
					$pdf->SetTextColor(0,0,127);//AZUL RE
					$pdf->SetXY($M_LEFT+448,$AUX_Y+10);
					$pdf->Cell(50, 18,utf8_encode('SUBTOTAL $'),0,0,'R',0);
					$pdf->Cell(62, 18,utf8_encode(number_format($result[0]['SUBTOTAL'], 0, ',', '.')),0,0,'R',0);
					$pdf->SetXY($M_LEFT+448,$AUX_Y+20);
					$pdf->Cell(50, 18,utf8_encode('Descuento '.number_format($result[0]['PORC_DSCTO1'], 1, ',', '.').'% $'),0,0,'R',0);
					$pdf->Cell(62, 18,utf8_encode(number_format($result[0]['MONTO_DSCTO1'], 0, ',', '.')),0,0,'R',0);
					$pdf->SetXY($M_LEFT+448,$AUX_Y+30);
					$pdf->Cell(50, 18,utf8_encode('Descuento '.number_format($result[0]['PORC_DSCTO2'], 1, ',', '.').'% $'),0,0,'R',0);
					$pdf->Cell(62, 18,utf8_encode(number_format($result[0]['MONTO_DSCTO2'], 0, ',', '.')),0,0,'R',0);
					$pdf->SetXY($M_LEFT+448,$AUX_Y+40);
					$pdf->Cell(50, 18,utf8_encode('NETO $'),0,0,'R',0);
					$pdf->Cell(62, 18,utf8_encode(number_format($result[0]['TOTAL_NETO'], 0, ',', '.')),0,0,'R',0);
					$pdf->SetXY($M_LEFT+448,$AUX_Y+50);
					$pdf->Cell(50, 18,utf8_encode($result[0]['PORC_IVA'].'% IVA $'),0,0,'R',0);
					$pdf->Cell(62, 18,utf8_encode(number_format($result[0]['MONTO_IVA'], 0, ',', '.')),0,0,'R',0);
					$pdf->SetXY($M_LEFT+448,$AUX_Y+62);
					$pdf->Cell(50, 18,utf8_encode('TOTAL $'),0,0,'R',0);
					$pdf->Cell(62, 18,utf8_encode(number_format($result[0]['TOTAL_CON_IVA'], 0, ',', '.')),0,0,'R',0);
					$pdf->Rect($M_LEFT+421, $AUX_Y+10, 139, 55,			   0, 				       '', '');
					$pdf->Rect($M_LEFT+421, $AUX_Y+65, 139, 15,			   0, 				       '', '');
				}

			}
	}
	
	$M_LEFT = 22;
	// LEYENDA DE DATOS BANCARIOS
	$pdf->Rect($M_LEFT+4, $AUX_Y+10, 288, 45,			   0, 				       '', '');
	$pdf->SetFont('helvetica','B',8);
	$pdf->Text($M_LEFT+3, $AUX_Y+15,utf8_encode('DEPÓSITOS O'));
	$pdf->Text($M_LEFT+3, $AUX_Y+25,utf8_encode('TRANSFERENCIAS:'));
	$pdf->Text($M_LEFT+90, $AUX_Y+11,utf8_encode('Comercial Biggi Chile S.A. '));
	$pdf->Text($M_LEFT+90, $AUX_Y+21,utf8_encode('RUT: 91.462.001-5'));
	$pdf->Text($M_LEFT+90, $AUX_Y+31,utf8_encode('Cuenta corriente 213284496 / Banco ITAU.'));
	$pdf->Text($M_LEFT+90, $AUX_Y+41,utf8_encode('E-Mail: info@biggi.cl'));
	$pdf->SetFont('helvetica','',9);
	
	// AS SOLICITA QUE SI COTIZACION TIENE DESCUENTO > 12% NO DEBE MOSTRAR IMAGEN DE 12 CUOTAS , ni WEBPAY logo
	$CALCULO_12P = ((12 * $result[0]['SUBTOTAL'])/100);
	$CALCULO_DSCTO_TOT = $result[0]['MONTO_DSCTO1'] + $result[0]['MONTO_DSCTO2'];
	
	// SELECTOR EN EL PRINT DE COTIZACION QUE INDICA SI SE INCLUYE O NO EL LOGO DE WEBPAY 09092020
	$MUESTRA_LOGOWP = $webpay_logo; 
	
	if ($MUESTRA_LOGOWP == 'S'){
		if($CALCULO_12P >= $CALCULO_DSCTO_TOT ){
			//$RUTA_IMG_TR12 = dirname(__FILE__).'/../../../../../producto_imagen/parametro/12cuotas.jpg';
			//$pdf->Image($RUTA_IMG_TR12,393,$AUX_Y+11,45,0,'','','C',false,300,'',false,false,0,true,false,false,false,false);
			$RUTA_IMG_TRWPP = dirname(__FILE__).'/../../../../../producto_imagen/parametro/webpayplus.jpg';
			$pdf->Image($RUTA_IMG_TRWPP,330,$AUX_Y+11,57,0,'','','C',false,300,'',false,false,0,true,false,false,false,false);
		}	
	}

	// CONSULTA EL VALOR GetY() PARA SABER SI DESPLIEGA CUADRO CONDICIONES GENERALES
	if($pdf->GetY() > 550){
		$pdf->AddPage();
		$AUX_Y = 40;		
	}

	// CONDICIONES GENERALES
	$pdf->SetFont('helvetica','B',10);
	$pdf->SetTextColor(0,0,127);//AZUL RE
	$pdf->Text($M_LEFT, $AUX_Y+60,utf8_encode('Condiciones Generales:'));
	$pdf->SetFont('helvetica','B',7);
	$pdf->Text($M_LEFT, $AUX_Y+75,utf8_encode('Forma de Pago'));
	$pdf->Text($M_LEFT, $AUX_Y+85,utf8_encode('Validez Oferta'));
	$pdf->Text($M_LEFT, $AUX_Y+95,utf8_encode('Entrega'));	
	$pdf->Text($M_LEFT, $AUX_Y+105,utf8_encode('Embalaje'));
	$pdf->Text($M_LEFT, $AUX_Y+115,utf8_encode('Flete'));
	$pdf->Text($M_LEFT, $AUX_Y+125,utf8_encode('Instalación'));
	$pdf->Text($M_LEFT, $AUX_Y+135,utf8_encode('Garantía'));
	$pdf->Text($M_LEFT, $AUX_Y+155,utf8_encode('Equipos especiales'));	
	$pdf->Text($M_LEFT, $AUX_Y+165,utf8_encode('Notas'));
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('helvetica','',7);

	//$pdf->Text(94, $AUX_Y+75,utf8_encode(': '.$result[0]['NOM_FORMA_PAGO']));

	if($COD_FORMA_PAGO == 1){
		$pdf->Text(94, $AUX_Y+75,utf8_encode(': '.'OTRO : '.$result[0]['NOM_FORMA_PAGO_OTRO']));
	}else{
		$pdf->Text(94, $AUX_Y+75,utf8_encode(': '.$result[0]['NOM_FORMA_PAGO']));
	}	

	$pdf->Text(94, $AUX_Y+85,utf8_encode(': '.$result[0]['VALIDEZ_OFERTA'].' DÍAS'));
	$pdf->Text(94, $AUX_Y+95,utf8_encode(': '.$result[0]['ENTREGA']));
	$pdf->SetFont('helvetica','B',7);
	$pdf->Text(94, $AUX_Y+105,utf8_encode(': '.$result[0]['NOM_EMBALAJE_COTIZACION']));
	$pdf->Text(94, $AUX_Y+115,utf8_encode(': '.$result[0]['NOM_FLETE_COTIZACION']));
	$pdf->Text(94, $AUX_Y+125,utf8_encode(': '.$result[0]['NOM_INSTALACION_COTIZACION']));
	$pdf->SetFont('helvetica','',7);
	$pdf->SetXY(94, $AUX_Y+135);
	$pdf->MultiCell(325,14,utf8_encode(': '.$result[0]['GARANTIA']), '', 'L');
	$pdf->Text(94, $AUX_Y+155,utf8_encode(': '.$result[0]['EQUIPO_ESPECIAL']));
	$pdf->SetFont('helvetica','',10);
	$pdf->SetXY($M_LEFT, $AUX_Y+175);
	
	$MODIFICA_PIE_V1 = 0;
	//if ($COD_COTIZACION == 198828 || $COD_COTIZACION == 198829){

		//////// INI CALCULA LA ALTURA DE OBS PARA VER SI SUMA NUEVA PAGINA
		$SABER_ALT = $pdf->GetY();
		$pdf->startTransaction();
		$start_y = $pdf->GetY();
		$start_page = $pdf->getPage();
		// SE DEBE SETEAR MISMA FONT Y POS X QUE SE USARA EN LA IMPRESION OFICIAL DE NOM_PRODUCTO			
		//$pdf->SetFont('helvetica','',8);	
		$pdf->SetFont('helvetica','B',10);
		$pdf->SetTextColor(0,0,127);//AZUL RE
		$pdf->SetX($M_LEFT);
		//$pdf->MultiCell($pdf->W_NP, $H_PRODUCTO_NB,utf8_encode($result[$i]['NOM_PRODUCTO']),1,'L',false,1);
		$pdf->MultiCell(380,14,utf8_encode($result[0]['OBS']), 0, 'L');
		$end_y = $pdf->GetY();
		$end_page = $pdf->getPage();
		$height = 0;
		
		if ($end_page == $start_page) {
			$height = $end_y - $start_y;
		} else {
			for ($page=$start_page; $page <= $end_page; ++$page) {
				$pdf->setPage($page);
				if ($page == $start_page) {
					// first page
					$height = $pdf->getPageHeight() - $start_y - $pdf->getBreakMargin();
				} elseif ($page == $end_page) {
				//MH 10-03-2020 el ejemplo indicaba la linea que viene pero se cae por que getMargins() es un array
					// last page
					//$height = $end_y - $pdf->getMargins();
				//MH 10-03-2020 optimizacion del ejemplo, ahora funciona.
					// last page					            
					$v_getMargins = $pdf->getMargins();
					$v_mtop = $v_getMargins['top'];	
					$height = $end_y - $v_mtop;
					//$height = $end_y - $pdf->getMargins()['top'];	
				} else {
					$v_getMargins = $pdf->getMargins();
					$v_mtop = $v_getMargins['top'];
					$height = $pdf->getPageHeight() - $v_mtop - $pdf->getBreakMargin();
				}
			}
		}
		// MH 31052022 OJO QUIZAS SE DEBERIA VALIDAD SEGUN SI ES COT RESUMEN O COT AMPLIADA
		// MH 31052022 ULTIMA REVISION FUE SEGUN COT 213757
		// MH 10062024 PARA LA COTIZACION 234207 DE RB SE TUVO QUE USAR $SABER_ALT_HEIGHT > 660
		$pdf = $pdf->rollbackTransaction();
		$SABER_ALT_HEIGHT = $SABER_ALT + $height;
		if ($SABER_ALT_HEIGHT > 580 ) {
			$pdf->AddPage();
			$pdf->SetXY($M_LEFT, $M_TOP+15);
			$MODIFICA_PIE_V1 = 1;
			$pdf->MultiCell(380,14,utf8_encode($result[0]['OBS']), 0, 'L');
			$AUX_Y = $pdf->GetY();
		} else {
			$pdf->MultiCell(380,14,utf8_encode($result[0]['OBS']), 0, 'L');
		}

				
	//} else {
	//	$pdf->MultiCell(380,14,utf8_encode($result[0]['OBS']), 0, 'L');		
	//}

	$pdf->SetFont('helvetica','',7);

	// DATOS Y FIRMA VENDEDOR
	$pdf->SetTextColor(0,0,127);
	$pdf->SetLineWidth(1);
	// SI LAS OBS FORZARON NUEVA PAGINA EL PIE DE FIRMA SE DEBE SUBIR UN POCO MAS
	// SI OBS NO FORZO NEWPAGE ENTONCES SE DEJA EL STANDARD
	if ($MODIFICA_PIE_V1 == 1 ){	
		$pdf->Line(445,$AUX_Y,583,$AUX_Y);
		$pdf->SetFont('helvetica','B',9);
		$pdf->SetXY(460, $AUX_Y+0);
		$pdf->Cell(110, 20,utf8_encode('COMERCIAL BIGGI (CHILE) S.A.'),0,0,'C');
		$pdf->SetXY(460, $AUX_Y+10);
		$pdf->Cell(110, 20,utf8_encode($result[0]['NOM_USUARIO']),0,0,'C');
		//$pdf->Cell(110, 20,utf8_encode($TIPO_DESC),0,0,'C');	
		$pdf->SetXY(460, $AUX_Y+20);
		$pdf->Cell(110, 20,utf8_encode($result[0]['MAIL_U']),0,0,'C');
		$pdf->Cell(110, 20,utf8_encode($height),0,0,'C');
		$pdf->SetXY(460, $AUX_Y+30);
		$pdf->Cell(110, 20,utf8_encode($result[0]['FONO_U'].' - '.$result[0]['CEL_U']),0,0,'C');
		//$pdf->Cell(110, 20,utf8_encode($SABER_ALT),0,0,'C');
	} else {
		$pdf->Line(445,$AUX_Y+128,583,$AUX_Y+128);
		$pdf->SetFont('helvetica','B',9);
		$pdf->SetXY(460, $AUX_Y+125);
		$pdf->Cell(110, 20,utf8_encode('COMERCIAL BIGGI (CHILE) S.A.'),0,0,'C');
		$pdf->SetXY(460, $AUX_Y+135);
		$pdf->Cell(110, 20,utf8_encode($result[0]['NOM_USUARIO']),0,0,'C');
		//$pdf->Cell(110, 20,utf8_encode($TIPO_DESC),0,0,'C');	
		$pdf->SetXY(460, $AUX_Y+145);
		$pdf->Cell(110, 20,utf8_encode($result[0]['MAIL_U']),0,0,'C');
		//$pdf->Cell(110, 20,utf8_encode($TIPO_COT),0,0,'C');
		$pdf->SetXY(460, $AUX_Y+155);
		$pdf->Cell(110, 20,utf8_encode($result[0]['FONO_U'].' - '.$result[0]['CEL_U']),0,0,'C');
		//$pdf->Cell(110, 20,utf8_encode($AUX_COT_TIPO_DSCTO),0,0,'C');
	}
	
	//TESTIGO DE ERROR EN PORC2
	//$pdf->SetTextColor(0,0,0);
	//$pdf->SetFont('helvetica','',6);
	//$pdf->SetXY($M_LEFT-20, $AUX_Y+170);
	//$pdf->Cell(110, 20,$AVISO_ERROR_MONTO_DSCTO2,0,0,'C');


	//// FIN PIE PAGINA COMUN ////

	// DEFINE EL TITULO DEL ARCHIVO SEGUN EL TIPO DE COTIZACION.
	if ($TIPO_COT == 'AMPLIADA_PDF'){
		$pdf->SetTitle('COTIZACION AMPLIADA '.$COD_COTIZACION);
		$COT_NAME = 'COTIZACION AMPLIADA ';
	}
	if ($TIPO_COT == 'RESUMEN_PDF'){
		$pdf->SetTitle('COTIZACION RESUMEN '.$COD_COTIZACION);
		$COT_NAME = 'COTIZACION RESUMEN ';
	}		


	$pdf->Output($COT_NAME.$COD_COTIZACION.".PDF", 'I');

?>