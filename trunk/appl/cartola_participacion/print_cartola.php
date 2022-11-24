<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/TCPDF-master/tcpdf.php");


function arma_codigos($result){
    $cods = '';
    for ($i = 0; $i < count($result); $i++) {
        $cod = $result[$i]['COD_PARTICIPACION'];
        if($i == 0){
            $cods = $cods.$cod;
        }else{
            $cods = $cods.' / '.$cod;
        }
    }
    return $cods;
}

$COLOR_GRIZ_CLARO = array(191,191,191);



$COD_CARTOLA_PARTICIPACION             = $_REQUEST["COD_CARTOLA_PARTICIPACION"];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "select CP.COD_CARTOLA_PARTICIPACION
                        , CP.COD_USUARIO COD_USUARIO_VENDEDOR_H
                         ,NOM_USUARIO NOM_USUARIO_VENDEDOR
                         ,(  select top 1 saldo  from ITEM_CARTOLA_PARTICIPACION
                                WHERE COD_CARTOLA_PARTICIPACION = $COD_CARTOLA_PARTICIPACION
                                ORDER BY COD_ITEM_CARTOLA_PARTICIPACION DESC) SALDO_FINAL
                        ,'none' VISIBLE
                        ,PORC_PARTICIPACION
						,CP.SALDO_CARTOLA_ANTERIOR
                 from CARTOLA_PARTICIPACION CP , USUARIO U
                 where CP.COD_USUARIO=U.COD_USUARIO
                 and COD_CARTOLA_PARTICIPACION = $COD_CARTOLA_PARTICIPACION";
$result = $db->build_results($sql);

$select_year = "select top 1 year(fecha_movimiento)ANO_CARTOLA,convert(varchar,getdate(),103)FECHA_ACTUAL
	from ITEM_CARTOLA_PARTICIPACION icp
	WHERE COD_CARTOLA_PARTICIPACION = $COD_CARTOLA_PARTICIPACION";
$rs_year = $db->build_results($select_year);

$year_cartola = $rs_year[0]['ANO_CARTOLA'];
$fecha_actual = $rs_year[0]['FECHA_ACTUAL'];

$NOM_USUARIO_VENDEDOR = $result[0]['NOM_USUARIO_VENDEDOR'];
$PORC_PARTICIPACION = $result[0]['PORC_PARTICIPACION'];
$saldo_cartola_anterior = '$'.number_format ($result[0]['SALDO_CARTOLA_ANTERIOR'],0 ,"," ,"." );



$saldo_abono_ene = '';
$saldo_abono_feb = '';
$saldo_abono_mar = '';
$saldo_abono_abr = '';
$saldo_abono_may = '';
$saldo_abono_jun = '';
$saldo_abono_jul = '';
$saldo_abono_ago = '';
$saldo_abono_sep = '';
$saldo_abono_oct = '';
$saldo_abono_nov = '';
$saldo_abono_dic = '';

$abono_ene = '';
$abono_feb = '';
$abono_mar = '';
$abono_abr = '';
$abono_may = '';
$abono_jun = '';
$abono_jul = '';
$abono_ago = '';
$abono_sep = '';
$abono_oct = '';
$abono_nov = '';
$abono_dic = '';

$saldo_retiro_ene = '';
$saldo_retiro_feb = '';
$saldo_retiro_mar = '';
$saldo_retiro_abr = '';
$saldo_retiro_may = '';
$saldo_retiro_jun = '';
$saldo_retiro_jul = '';
$saldo_retiro_ago = '';
$saldo_retiro_sep = '';
$saldo_retiro_oct = '';
$saldo_retiro_nov = '';
$saldo_retiro_dic = '';

$monto_retiro_ene = '';
$monto_retiro_feb = '';
$monto_retiro_mar = '';
$monto_retiro_abr = '';
$monto_retiro_may = '';
$monto_retiro_jun = '';
$monto_retiro_jul = '';
$monto_retiro_ago = '';
$monto_retiro_sep = '';
$monto_retiro_oct = '';
$monto_retiro_nov = '';
$monto_retiro_dic = '';

$cods_p_ene = '';
$cods_p_feb = '';
$cods_p_mar = '';
$cods_p_abr = '';
$cods_p_may = '';
$cods_p_jun = '';
$cods_p_jul = '';
$cods_p_ago = '';
$cods_p_sep = '';
$cods_p_oct = '';
$cods_p_nov = '';
$cods_p_dic = '';

/*data */
for ($i = 1; $i <= 12; $i++) {
    
    $sql = "select *
            from ITEM_CARTOLA_PARTICIPACION icp
            WHERE COD_CARTOLA_PARTICIPACION  = $COD_CARTOLA_PARTICIPACION
            and year(FECHA_MOVIMIENTO) = $year_cartola
            and MONTH(FECHA_MOVIMIENTO) = $i
            ORDER BY FECHA_MOVIMIENTO DESC";
    $rs_val = $db->build_results($sql);
    
    if(count($rs_val)>0){
        
        $sql = "select TOP 1 SALDO
            from ITEM_CARTOLA_PARTICIPACION icp
            WHERE TIPO_MOVIMIENTO = 'ABONO'
            AND COD_CARTOLA_PARTICIPACION  = $COD_CARTOLA_PARTICIPACION
            and year(FECHA_MOVIMIENTO) = $year_cartola
            and MONTH(FECHA_MOVIMIENTO) = $i
            ORDER BY FECHA_MOVIMIENTO DESC";
        $rs_saldo = $db->build_results($sql);
        
        $saldo_abono = '$'.number_format ($rs_saldo[0]['SALDO'],0 ,"," ,"." );
        
        $sql = "select SUM(MONTO)ABONO
            from ITEM_CARTOLA_PARTICIPACION icp
            WHERE TIPO_MOVIMIENTO = 'ABONO'
            AND COD_CARTOLA_PARTICIPACION  = $COD_CARTOLA_PARTICIPACION
            and year(FECHA_MOVIMIENTO) = $year_cartola
            and MONTH(FECHA_MOVIMIENTO) = $i";
        
        $rs_abono = $db->build_results($sql);
        $abono = '$'.number_format ($rs_abono[0]['ABONO'],0 ,"," ,"." );
        
        $sql = "select TOP 1 SALDO,MONTO
            from ITEM_CARTOLA_PARTICIPACION icp
            WHERE TIPO_MOVIMIENTO = 'RETIRO'
            AND COD_CARTOLA_PARTICIPACION  = $COD_CARTOLA_PARTICIPACION
            and year(FECHA_MOVIMIENTO) = $year_cartola
            and MONTH(FECHA_MOVIMIENTO) = $i
            ORDER BY FECHA_MOVIMIENTO DESC";
        
        $rs_retiro = $db->build_results($sql);
        $saldo_retiro = '$'.number_format ($rs_retiro[0]['SALDO'],0 ,"," ,"." );
        $monto_retiro = '$'.number_format ($rs_retiro[0]['MONTO'],0 ,"," ,"." );
        
        $sql = "select COD_PARTICIPACION
                from ITEM_CARTOLA_PARTICIPACION icp
                WHERE TIPO_MOVIMIENTO = 'ABONO'
                AND COD_CARTOLA_PARTICIPACION  = $COD_CARTOLA_PARTICIPACION
                and year(FECHA_MOVIMIENTO) = $year_cartola
                and MONTH(FECHA_MOVIMIENTO) = $i";
        
        $rs_cod_p = $db->build_results($sql);
        
        $cods_p = arma_codigos($rs_cod_p);
        
        if($i == 1){
            $saldo_abono_ene = $saldo_abono;
            $abono_ene = $abono;
            $saldo_retiro_ene = $saldo_retiro;
            $monto_retiro_ene = $monto_retiro;
            $cods_p_ene = $cods_p;
        }
        if($i == 2){
            $saldo_abono_feb = $saldo_abono;
            $abono_feb = $abono;
            $saldo_retiro_feb = $saldo_retiro;
            $monto_retiro_feb = $monto_retiro;
            $cods_p_feb = $cods_p;
        }
        if($i == 3){
            $saldo_abono_mar = $saldo_abono;
            $abono_mar = $abono;
            $saldo_retiro_mar = $saldo_retiro;
            $monto_retiro_mar = $monto_retiro;
            $cods_p_mar = $cods_p;
        }
        if($i == 4){
            $saldo_abono_abr = $saldo_abono;
            $abono_abr = $abono;
            $saldo_retiro_abr = $saldo_retiro;
            $monto_retiro_abr = $monto_retiro;
            $cods_p_abr = $cods_p;
        }
        if($i == 5){
            $saldo_abono_may = $saldo_abono;
            $abono_may = $abono;
            $saldo_retiro_may = $saldo_retiro;
            $monto_retiro_may = $monto_retiro;
            $cods_p_may = $cods_p;
        }
        if($i == 6){
            $saldo_abono_jun = $saldo_abono;
            $abono_jun = $abono;
            $saldo_retiro_jun = $saldo_retiro;
            $monto_retiro_jun = $monto_retiro;
            $cods_p_jun = $cods_p;
        }
        if($i == 7){
            $saldo_abono_jul = $saldo_abono;
            $abono_jul = $abono;
            $saldo_retiro_jul = $saldo_retiro; 
            $monto_retiro_jul = $monto_retiro;
            $cods_p_jul = $cods_p;
        }
        if($i == 8){
            $saldo_abono_ago = $saldo_abono;
            $abono_ago = $abono;
            $saldo_retiro_ago = $saldo_retiro;
            $monto_retiro_ago = $monto_retiro;
            $cods_p_ago = $cods_p;
        }
        if($i == 9){
            $saldo_abono_sep = $saldo_abono;
            $abono_sep = $abono;
            $saldo_retiro_sep = $saldo_retiro;
            $monto_retiro_sep = $monto_retiro;
            $cods_p_sep = $cods_p;
        }
        if($i == 10){
            $saldo_abono_oct = $saldo_abono;
            $abono_oct = $abono;
            $saldo_retiro_oct = $saldo_retiro;
            $monto_retiro_oct = $monto_retiro;
            $cods_p_oct = $cods_p;
        }
        if($i == 11){
            $saldo_abono_nov = $saldo_abono;
            $abono_nov = $abono;
            $saldo_retiro_nov = $saldo_retiro;
            $monto_retiro_nov = $monto_retiro;
            $cods_p_nov = $cods_p;
        }
        if($i == 12){
            $saldo_abono_dic = $saldo_abono;
            $abono_dic = $abono;
            $saldo_retiro_dic = $saldo_retiro;
            $monto_retiro_dic = $monto_retiro;
            $cods_p_dic = $cods_p;
        }
    }
    
}


$pdf = new TCPDF('P', 'pt', 'LETTER', true, 'UTF-8', false);

// set document information
$pdf->SetCreator('SetCreator');
$pdf->SetAuthor('SetAuthor');
$pdf->SetTitle('Cartola Participacion');
$pdf->SetSubject('SetSubject');
$pdf->SetKeywords('SetKeywords');
$pdf->SetFooterMargin(0);
$pdf->SetHeaderMargin(0);
$pdf->SetAutoPageBreak(false, PDF_MARGIN_BOTTOM);
// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
//$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

$pdf->AddPage();

/*TITULO*/
$IMAGEN_LOGO = K_ROOT_DIR."/images_appl/COMERCIAL/logo_cotizacion_cartola.jpg";
$IMAGEN_PIE = K_ROOT_DIR."/images_appl/COMERCIAL/pie_cotizacion_new.jpg";
//$pdf->Image($imagen_fondo, 0, 0, 612, 792, '', '', '', false, 300, '', false, false, 0);
$pdf->Image($IMAGEN_LOGO,10,10,488,70,'','','',false,300,'',false,false,0,false,false,false);
$pdf->Image($IMAGEN_PIE,-2,750,612,48,'','','',false,300,'',false,false,0,false,false,false);
$pdf->SetFont('helvetica','B',10);
$pdf->Text(27, 85,utf8_encode('CARTOLA COMISIONES '.$NOM_USUARIO_VENDEDOR.' AÑO '.$year_cartola));
$pdf->SetFont('helvetica','',8);
$pdf->Text(480, 33,utf8_encode(' Fecha impresión: '.$fecha_actual));
$pdf->Text(480, 25,utf8_encode(' Código Cartola: '.$COD_CARTOLA_PARTICIPACION   ));

/*CABECERA*/
$Y = $pdf->GetY()+80;
$pdf->SetXY(30, $Y);
$pdf->SetFont('helvetica','B',10);
$pdf->SetFillColorArray($COLOR_GRIZ_CLARO);
$pdf->Cell(80, 20, 'MES',1,0,'C',true,'',0,false,'T','M');
$pdf->Cell(42, 20, 'COD PP',1,0,'C',true,'',0,false,'T','M');
$pdf->Cell(159, 20, 'PERIODO '.$year_cartola,1,0,'C',true,'',0,false,'T','M');
$pdf->Cell(90, 20, 'ABONO',1,0,'C',true,'',0,false,'T','M');
$pdf->Cell(90, 20, 'RETIRO',1,0,'C',true,'',0,false,'T','M');
$pdf->Cell(100, 20, 'SALDO',1,1,'C',true,'',0,false,'T','M');

/*CUERPO*/
/**SALDO INICIAL**/
$pdf->SetX(30);
$pdf->SetFont('helvetica','B',10);
$pdf->MultiCell(80, 20, 'SALDO INICIAL', 1, 'C', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->MultiCell(42, 20, '', 1, 'L', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->SetFont('helvetica','',10);
$pdf->Cell(159, 20, 'SALDO CARTOLA ANTERIOR ',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $nada,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_cartola_anterior,1,1,'R',false,'',0,false,'T','M');



/**ENERO**/
$pdf->SetX(30);
$pdf->SetFont('helvetica','B',10);
$pdf->MultiCell(80, 40, 'ENERO', 1, 'C', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->MultiCell(42, 40, $cods_p_ene, 1, 'L', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->SetFont('helvetica','',10);
$pdf->Cell(159, 20, 'VENTAS COMERCIAL BIGGI '.$PORC_PARTICIPACION.'%',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $abono_ene,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_abono_ene,1,1,'R',false,'',0,false,'T','M');
$pdf->SetX(152);
$pdf->Cell(159, 20, 'RETIRO MAYO',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $monto_retiro_ene,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_retiro_ene,1,1,'R',false,'',0,false,'T','M');

/**FEBRERO**/
$pdf->SetX(30);
$pdf->SetFont('helvetica','B',10);
$pdf->MultiCell(80, 40, 'FEBRERO', 1, 'C', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->MultiCell(42, 40, $cods_p_feb, 1, 'L', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->SetFont('helvetica','',10);
$pdf->Cell(159, 20, 'VENTAS COMERCIAL BIGGI '.$PORC_PARTICIPACION.'%',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $abono_feb,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_abono_feb,1,1,'R',false,'',0,false,'T','M');
$pdf->SetX(152);
$pdf->Cell(159, 20, 'RETIRO MAYO',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $monto_retiro_feb,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_retiro_feb,1,1,'R',false,'',0,false,'T','M');

/**MARZO**/
$pdf->SetX(30);
$pdf->SetFont('helvetica','B',10);
$pdf->MultiCell(80, 40, 'MARZO', 1, 'C', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->MultiCell(42, 40, $cods_p_mar, 1, 'L', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->SetFont('helvetica','',10);
$pdf->Cell(159, 20, 'VENTAS COMERCIAL BIGGI '.$PORC_PARTICIPACION.'%',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $abono_mar,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_abono_mar,1,1,'R',false,'',0,false,'T','M');
$pdf->SetX(152);
$pdf->Cell(159, 20, 'RETIRO MAYO',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $monto_retiro_mar,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_retiro_mar,1,1,'R',false,'',0,false,'T','M');

/**ABRIL**/
$pdf->SetX(30);
$pdf->SetFont('helvetica','B',10);
$pdf->MultiCell(80, 40, 'ABRIL', 1, 'C', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->MultiCell(42, 40, $cods_p_abr, 1, 'L', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->SetFont('helvetica','',10);
$pdf->Cell(159, 20, 'VENTAS COMERCIAL BIGGI '.$PORC_PARTICIPACION.'%',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $abono_abr,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_abono_abr,1,1,'R',false,'',0,false,'T','M');
$pdf->SetX(152);
$pdf->Cell(159, 20, 'RETIRO MAYO',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $monto_retiro_abr,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_retiro_abr,1,1,'R',false,'',0,false,'T','M');

/**MAYO**/
$pdf->SetX(30);
$pdf->SetFont('helvetica','B',10);
$pdf->MultiCell(80, 40, 'MAYO', 1, 'C', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->MultiCell(42, 40, $cods_p_may, 1, 'L', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->SetFont('helvetica','',10);
$pdf->Cell(159, 20, 'VENTAS COMERCIAL BIGGI '.$PORC_PARTICIPACION.'%',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $abono_may,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_abono_may,1,1,'R',false,'',0,false,'T','M');
$pdf->SetX(152);
$pdf->Cell(159, 20, 'RETIRO MAYO',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $monto_retiro_may,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_retiro_may,1,1,'R',false,'',0,false,'T','M');

/**JUNIO**/
$pdf->SetFont('helvetica','B',10);
$pdf->SetX(30);
$pdf->MultiCell(80, 40, 'JUNIO', 1, 'C', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->MultiCell(42, 40, $cods_p_jun, 1, 'L', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->SetFont('helvetica','',10);
$pdf->Cell(159, 20, 'VENTAS COMERCIAL BIGGI '.$PORC_PARTICIPACION.'%',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $abono_jun,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_abono_jun,1,1,'R',false,'',0,false,'T','M');
$pdf->SetX(152);
$pdf->Cell(159, 20, 'RETIRO JUNIO',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $monto_retiro_jun,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_retiro_jun,1,1,'R',false,'',0,false,'T','M');
/**JULIO**/
$pdf->SetFont('helvetica','B',10);
$pdf->SetX(30);
$pdf->MultiCell(80, 40, 'JULIO', 1, 'C', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->MultiCell(42, 40, $cods_p_jul, 1, 'L', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->SetFont('helvetica','',10);
$pdf->Cell(159, 20, 'VENTAS COMERCIAL BIGGI '.$PORC_PARTICIPACION.'%',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $abono_jul,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_abono_jul,1,1,'R',false,'',0,false,'T','M');
$pdf->SetX(152);
$pdf->Cell(159, 20, 'RETIRO JULIO',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $monto_retiro_jul,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_retiro_jul,1,1,'R',false,'',0,false,'T','M');
/**AGOSTO**/
$pdf->SetFont('helvetica','B',10);
$pdf->SetX(30);
$pdf->MultiCell(80, 40, 'AGOSTO', 1, 'C', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->MultiCell(42, 40, $cods_p_ago, 1, 'L', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->SetFont('helvetica','',10);
$pdf->Cell(159, 20, 'VENTAS COMERCIAL BIGGI '.$PORC_PARTICIPACION.'%',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(90, 20,  $abono_ago,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_abono_ago,1,1,'R',false,'',0,false,'T','M');
$pdf->SetX(152);
$pdf->Cell(159, 20, 'RETIRO AGOSTO',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $monto_retiro_ago,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_retiro_ago,1,1,'R',false,'',0,false,'T','M');
/**SEPTIEMBRE**/
$pdf->SetFont('helvetica','B',10);
$pdf->SetX(30);
$pdf->MultiCell(80, 40, 'SEPTIEMBRE', 1, 'C', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->MultiCell(42, 40, $cods_p_sep, 1, 'L', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->SetFont('helvetica','',10);
$pdf->Cell(159, 20, 'VENTAS COMERCIAL BIGGI '.$PORC_PARTICIPACION.'%',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $abono_sep,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_abono_sep,1,1,'R',false,'',0,false,'T','M');
$pdf->SetX(152);
$pdf->Cell(159, 20, 'RETIRO SEPTIEMBRE',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $monto_retiro_sep,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_retiro_sep,1,1,'R',false,'',0,false,'T','M');
/**OCTUBRE**/
$pdf->SetFont('helvetica','B',10);
$pdf->SetX(30);
$pdf->MultiCell(80, 40, 'OCTUBRE', 1, 'C', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->MultiCell(42, 40, $cods_p_oct, 1, 'L', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->SetFont('helvetica','',10);
$pdf->Cell(159, 20, 'VENTAS COMERCIAL BIGGI '.$PORC_PARTICIPACION.'%',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $abono_oct,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_abono_oct,1,1,'R',false,'',0,false,'T','M');
$pdf->SetX(152);
$pdf->Cell(159, 20, 'RETIRO OCTUBRE',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $monto_retiro_oct,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_retiro_oct,1,1,'R',false,'',0,false,'T','M');
/**NOVIEMBRE**/
$pdf->SetFont('helvetica','B',10);
$pdf->SetX(30);
$pdf->MultiCell(80, 40, 'NOVIEMBRE', 1, 'C', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->MultiCell(42, 40, $cods_p_nov, 1, 'L', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->SetFont('helvetica','',10);
$pdf->Cell(159, 20, 'VENTAS COMERCIAL BIGGI '.$PORC_PARTICIPACION.'%',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $abono_nov,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_abono_nov,1,1,'R',false,'',0,false,'T','M');
$pdf->SetX(152);
$pdf->Cell(159, 20, 'RETIRO NOVIEMBRE',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $monto_retiro_nov,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_retiro_nov,1,1,'R',false,'',0,false,'T','M');
/**DICIEMBRE**/
$pdf->SetFont('helvetica','B',10);
$pdf->SetX(30);
$pdf->MultiCell(80, 40, 'DICIEMBRE', 1, 'C', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->MultiCell(42, 40, $cods_p_dic, 1, 'L', true, 0,'','',true,0,false,true,0,'M',true);
$pdf->SetFont('helvetica','',10);
$pdf->Cell(159, 20, 'VENTAS COMERCIAL BIGGI '.$PORC_PARTICIPACION.'%',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $abono_dic,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'C',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_abono_dic,1,1,'R',false,'',0,false,'T','M');
$pdf->SetX(152);
$pdf->Cell(159, 20, 'RETIRO DICIEMBRE',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, '',1,0,'L',false,'',0,false,'T','M');
$pdf->Cell(90, 20, $monto_retiro_dic,1,0,'R',false,'',0,false,'T','M');
$pdf->Cell(100, 20, $saldo_retiro_dic,1,1,'R',false,'',0,false,'T','M');

$pdf->Output("CARTOLA".utf8_encode($NOM_USUARIO_VENDEDOR).".PDF", 'I');

?>