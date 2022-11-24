<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class print_folleto_producto{
	function __construct(){
	}
	
	function folleto($cod_producto, $pdf){
		$pdf->AddFont('FuturaBook','','futurabook.php');
		$pdf->AddFont('FuturaMedium','','futuramedium.php');
		$pdf->AddPage();
		$pdf->SetAutoPageBreak(true,0);
		$titulo = "Folleto";
		$pdf->SetTitle($titulo);
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		if(file_exists($this->folder_producto($cod_producto).'/FOLLETO_FOTO1.jpg')){
			$sql = "SELECT COORDENADA_X
						  ,COORDENADA_Y
					FROM FOTO_FICHA_FOLLETO
					WHERE COD_PRODUCTO = '$cod_producto'
					AND NOM_FOTO = 'FOLLETO_FOTO1'";
			$result = $db->build_results($sql);
			$POS_X = $result[0]['COORDENADA_X'];
			$POS_Y = $result[0]['COORDENADA_Y'];
			$factor = 0.35;
			$pdf->Image($this->folder_producto($cod_producto).'/FOLLETO_FOTO1.jpg', 33+$POS_X, 102+$POS_Y, 1000*$factor, 1000*$factor);
		}else
			$pdf->Image('../../../../producto_imagen/parametro/foto_no_disponible.jpg' , 108+$POS_X, 178+$POS_Y, 200, 200);
		
		$sql = "SELECT NOM_PRODUCTOT1
					  ,NOM_PRODUCTOT2
					  ,NOM_PRODUCTOT3
				FROM PRODUCTO
				WHERE COD_PRODUCTO = '$cod_producto'";
		$result = $db->build_results($sql);
		
		$factor = 0.94;
		$pdf->Image('../../images_appl/ficha_folleto.png', 24, 26,612*$factor,768*$factor);
		
		$pdf->SetXY(22, 36);
		$pdf->SetFont('FuturaBook','', 24);
		$pdf->SetTextColor(0, 0, 0);
		$x_t1 = $pdf->GetStringWidth($result[0]['NOM_PRODUCTOT1']);
		
		$pdf->Cell(70,17,$result[0]['NOM_PRODUCTOT1'], 0, '', 'L');
		$pdf->SetTextColor(162, 162, 162);
		$pdf->SetXY($x_t1+34, 36);
		$pdf->Cell(70,17,$result[0]['NOM_PRODUCTOT2'], 0, '', 'L');
		$pdf->SetFont('FuturaBook','', 14);
		$pdf->SetXY(22, 60);
		$pdf->Cell(70,17,$result[0]['NOM_PRODUCTOT3'], 0, '', 'L');
		
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('FuturaBook','', 18);
		$pdf->SetXY(380, 171);
		$pdf->Cell(70,17,'MODELO', 0, '', 'L');
		
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetFont('FuturaBook','', 31);
		$pdf->SetXY(392, 204);
		$pdf->Cell(70,17,$cod_producto, 0, '', 'L');
		
		$pdf->SetFont('FuturaBook','', 8);
		$pdf->SetXY(384, 262);
		$pdf->Cell(70,17, 'ECONOMIA', 0, '', 'L');
		$pdf->SetXY(438, 262);
		$pdf->Cell(70,17, 'DESPACHO', 0, '', 'L');
		$pdf->SetXY(483, 262);
		$pdf->Cell(70,17, 'INSTALACION', 0, '', 'L');
		$pdf->SetXY(534, 266);
		$pdf->MultiCell(50,8, 'SERV. TECNICO', 0, 'C');
		
		$sql = "SELECT NOM_ATRIBUTO 
				FROM ATRIBUTO_DESTACADO
				WHERE COD_PRODUCTO = '$cod_producto'
				ORDER BY ORDEN ASC";
		$result = $db->build_results($sql);
		
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('FuturaMedium','', 12);
		for($l=0 ; $l < count($result) ; $l++){
			$pdf->SetXY(430-($l*13), 320+($l*37));
			$pdf->Cell(70,17,$result[$l]['NOM_ATRIBUTO'], 0, '', 'L');
		}
		
		$pdf->SetXY(22, 468);
		$pdf->SetTextColor(255, 0, 0);
		$pdf->SetFont('FuturaBook','', 21);
		$pdf->Cell(70,17,'CARACTERISTICAS', 0, '', 'L');
		$pdf->SetXY(209, 468);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('FuturaBook','', 21);
		$pdf->Cell(70,17,'TECNICAS', 0, '', 'L');
		
		$sql = "SELECT NOM_ATRIBUTO_PRODUCTO
					  ,SALTO_LINEA
				FROM ATRIBUTO_PRODUCTO
				WHERE COD_PRODUCTO = '$cod_producto'
				ORDER BY ORDEN ASC";
		$result = $db->build_results($sql);
		
		$pdf->SetFont('FuturaBook','', 12);
		$pdf->SetXY(30, 494);
		$pdf->SetTextColor(0, 0, 0);
		
		$salto_linea = false;
		for($k=0 ; $k < count($result) ; $k++){
			if($result[$k]['SALTO_LINEA'] == 'S'){
				$pdf->SetY(494);
				$posX = 310;
				$salto_linea = true;
			}else{
				if(!$salto_linea)
					$posX = 30;
			}
			
			$posY = $pdf->getY();
			$pdf->SetXY($posX, $posY);
			$pdf->Image('../../images_appl/circle.png',$posX-7,$posY+4, 7, 7);
			$pdf->MultiCell(270,14,$result[$k]['NOM_ATRIBUTO_PRODUCTO'], 0, '');
		}
        
		$pdf->SetFont('FuturaBook','', 8);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY(127, 694);
		$pdf->Cell(70,17,'MODELO EQUIPO', 0, '', 'L');
		
		$posX = 204;
		$posY = 39;
		
		$pdf->SetFont('FuturaBook','', 6);
		$pdf->SetXY($posX, 711-$posY);
		$pdf->Cell(10,17,'D', 0, '', 'C');
		$pdf->SetXY($posX, 717-$posY);
		$pdf->Cell(10,17,'I', 0, '', 'C');
		$pdf->SetXY($posX, 723-$posY);
		$pdf->Cell(10,17,'M', 0, '', 'C');//
		$pdf->SetXY($posX, 730-$posY);
		$pdf->Cell(10,17,'E', 0, '', 'C');
		$pdf->SetXY($posX, 736-$posY);
		$pdf->Cell(10,17,'N', 0, '', 'C');
		$pdf->SetXY($posX, 742-$posY);
		$pdf->Cell(10,17,'S', 0, '', 'C');
		$pdf->SetXY($posX, 748-$posY);
		$pdf->Cell(10,17,'I', 0, '', 'C');
		$pdf->SetXY($posX, 754-$posY);
		$pdf->Cell(10,17,'O', 0, '', 'C');
		$pdf->SetXY($posX, 760-$posY);
		$pdf->Cell(10,17,'N', 0, '', 'C');
		$pdf->SetXY($posX, 766-$posY);
		$pdf->Cell(10,17,'E', 0, '', 'C');
		$pdf->SetXY($posX, 772-$posY);
		$pdf->Cell(10,17,'S', 0, '', 'C');
		
		$pdf->SetFont('FuturaBook','', 8);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY(220, 689);
		$pdf->Cell(70,17,'LARGO', 0, '', 'L');
		$pdf->SetXY(260, 689);
		$pdf->Cell(70,17,'ANCHO', 0, '', 'L');
		$pdf->SetXY(304, 689);
		$pdf->Cell(70,17,'ALTO', 0, '', 'L');
		$pdf->SetXY(342, 689);
		$pdf->Cell(70,17,'PESO', 0, '', 'L');
		
		$pdf->SetFont('FuturaBook','', 6);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY(225, 697);
		$pdf->Cell(70,17,'(CM)', 0, '', 'L');
		$pdf->SetXY(266, 697);
		$pdf->Cell(70,17,'(CM)', 0, '', 'L');
		$pdf->SetXY(308, 697);
		$pdf->Cell(70,17,'(CM)', 0, '', 'L');
		$pdf->SetXY(346, 697);
		$pdf->Cell(70,17,'(KG)', 0, '', 'L');
		
		$sql = "SELECT LARGO
					  ,ANCHO
					  ,ALTO
					  ,PESO
				FROM PRODUCTO
				WHERE COD_PRODUCTO = '$cod_producto'";
		$result = $db->build_results($sql);
		
		$pdf->SetFont('FuturaBook','', 10);
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetXY(137, 710);
		$pdf->Cell(70,17,$cod_producto, 0, '', 'L');
		
		$pdf->SetXY(223, 710);
		$pdf->Cell(70,17,$result[0]['LARGO'], 0, '', 'L');
		$pdf->SetXY(269, 710);
		$pdf->Cell(70,17,$result[0]['ANCHO'], 0, '', 'L');
		$pdf->SetXY(306, 710);
		$pdf->Cell(70,17,$result[0]['ALTO'], 0, '', 'L');
		$pdf->SetXY(344, 710);
		$pdf->Cell(70,17,$result[0]['PESO'], 0, '', 'L');
		
		$pdf->SetFont('FuturaBook','', 7);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY(380, 716);
		$pdf->Cell(70,17,'Comercial Biggi (Chile) S.A. - Av. Portugal 1726 Santiago, Chile', 0, '', 'L');
		$pdf->SetXY(398, 727);
		$pdf->Cell(70,17,'Tel. (56-2) 2412 6200 - www.biggi.cl - info@biggi.cl', 0, '', 'L');
		
		require_once("class_qrcode.php");
		$qr = new qrcode();
		$qr->link("http://www.biggi.cl/sysbiggi_new/biggi_web_2/biggi_web_2/print_detalle_producto.php?cod_producto=".urlencode($cod_producto));
		$file = $qr->get_image();
		$cod_producto_sin_slash = str_replace("/", "_", $cod_producto);
		$fname = dirname(__FILE__)."/img_temp/".$cod_producto_sin_slash.".png";
		$qr->save_image($file, $fname);
		$pdf->Image($fname,523,651,55,55);
		unlink($fname);
    }
	
	function folder_producto($cod_producto){
		$cod_producto_folder = preg_replace("%[^A-Z^0-9^-]%", "_", $cod_producto);
		return "../../../../producto_imagen/producto/$cod_producto_folder";
	}
    
	function lista_folleto($pdf, $lista_productos){
		$arr_productos = explode(',', $lista_productos);
		
		for($i=0; $i < count($arr_productos) ; $i++){
			$cod_producto = $arr_productos[$i];
			$this->folleto($cod_producto, $pdf);
		}
		
		$pdf->Output("Lista_folleto.pdf", 'I');
	}
	
    function ficha_tecnica($cod_producto, $pdf){
    	$pdf->AddFont('FuturaBook','','futurabook.php');
    	$pdf->AddPage();
		$pdf->SetAutoPageBreak(true,0);
		$titulo = "ficha_tecnica";
		$pdf->SetTitle($titulo);
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$sql = "SELECT NOM_PRODUCTOT1
					  ,NOM_PRODUCTOT2
					  ,NOM_PRODUCTOT3
				FROM PRODUCTO
				WHERE COD_PRODUCTO = '$cod_producto'";
		$result = $db->build_results($sql);
		
		$factor = 0.91;
		$pdf->Image('../../images_appl/ficha_tecnica.jpg', 25, 28,612*$factor,792*$factor);
		
		$pdf->SetXY(22, 40);
		$pdf->SetFont('FuturaBook','', 18);
		$pdf->Cell(70,17,'Ficha Técnica', 0, '', 'L');
		$pdf->SetXY(22, 71);
		$pdf->SetFont('FuturaBook','', 24);
		$x_t1 = $pdf->GetStringWidth($result[0]['NOM_PRODUCTOT1']);
		
		$pdf->Cell(70,17,$result[0]['NOM_PRODUCTOT1'], 0, '', 'L');
		$pdf->SetTextColor(162, 162, 162);
		$pdf->SetXY($x_t1+33, 71);
		$pdf->Cell(70,17,$result[0]['NOM_PRODUCTOT2'], 0, '', 'L');
		$pdf->SetFont('FuturaBook','', 14);
		$pdf->SetXY(22, 94);
		$pdf->Cell(70,17,$result[0]['NOM_PRODUCTOT3'], 0, '', 'L');
		
		$pdf->SetFont('FuturaBook','', 16);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY(491, 52);
		$pdf->Cell(70,17,'MODELO', 0, '', 'L');
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetXY(505, 89);
		$pdf->Cell(70,17,$cod_producto, 0, '', 'L');
		
		if(file_exists($this->folder_producto($cod_producto).'/FICHA_FOTO1.jpg')){
			$sql = "SELECT COORDENADA_X
						  ,COORDENADA_Y
					FROM FOTO_FICHA_FOLLETO
					WHERE COD_PRODUCTO = '$cod_producto'
					AND NOM_FOTO = 'FICHA_FOTO1'";
			$result = $db->build_results($sql);
			$POS_X = $result[0]['COORDENADA_X'];
			$POS_Y = $result[0]['COORDENADA_Y'];
		
			$pdf->Image($this->folder_producto($cod_producto).'/FICHA_FOTO1.jpg' , 78+$POS_X, 140+$POS_Y, 451, 491);
		}else
			$pdf->Image('../../../../producto_imagen/parametro/foto_no_disponible.jpg' , 78+$POS_X, 140+$POS_Y, 451, 491);
			
		if(file_exists($this->folder_producto($cod_producto).'/FICHA_FOTO2.jpg')){
			$sql = "SELECT COORDENADA_X
						  ,COORDENADA_Y
					FROM FOTO_FICHA_FOLLETO
					WHERE COD_PRODUCTO = '$cod_producto'
					AND NOM_FOTO = 'FICHA_FOTO2'";
			$result = $db->build_results($sql);
			$POS_X = $result[0]['COORDENADA_X'];
			$POS_Y = $result[0]['COORDENADA_Y'];
		
			$pdf->Image($this->folder_producto($cod_producto).'/FICHA_FOTO2.jpg' , 320+$POS_X, 397+$POS_Y, 208, 234);
		}else
			$pdf->Image('../../../../producto_imagen/parametro/foto_no_disponible.jpg' , 320+$POS_X, 397+$POS_Y, 208, 234);	
		
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('FuturaBook','', 18);
		
		$pdf->SetXY(328, 665);
		$pdf->Cell(70,17,'ESPECIFICACIONES', 0, '', 'L');
		$pdf->SetTextColor(255, 0, 0);
		$pdf->SetXY(493, 665);
		$pdf->Cell(70,17,'TECNICAS', 0, '', 'L');

		$pdf->SetTextColor(0, 0, 0);
		
		$sql = "SELECT LARGO
					  ,ANCHO
					  ,ALTO
					  ,PESO
				FROM PRODUCTO
				WHERE COD_PRODUCTO = '$cod_producto'";
		$result = $db->build_results($sql);

		//header
		$pdf->SetFont('FuturaBook','', 8);
		$pdf->SetXY(205, 701);
		$pdf->Cell(46,17,'LARGO', 0, '', 'C');
		$pdf->SetXY(251, 701);
		$pdf->Cell(46,17,'ANCHO', 0, '', 'C');
		$pdf->SetXY(297, 701);
		$pdf->Cell(46,17,'ALTO', 0, '', 'C');
		$pdf->SetXY(343, 701);
		$pdf->Cell(45,17,'PESO', 0, '', 'C');
		
		//u de medida
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('FuturaBook','', 6);
		$pdf->SetXY(205, 708);
		$pdf->Cell(46,17,'(CM)', 0, '', 'C');
		$pdf->SetXY(251, 708);
		$pdf->Cell(46,17,'(CM)', 0, '', 'C');
		$pdf->SetXY(297, 708);
		$pdf->Cell(46,17,'(CM)', 0, '', 'C');
		$pdf->SetXY(343, 708);
		$pdf->Cell(45,17,'(KG)', 0, '', 'C');
		
		//valores
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetFont('FuturaBook','', 10);
		$pdf->SetXY(205, 721);
		$pdf->Cell(46,17,$result[0]['LARGO'], 0, '', 'C');
		$pdf->SetXY(251, 721);
		$pdf->Cell(46,17,$result[0]['ANCHO'], 0, '', 'C');
		$pdf->SetXY(297, 721);
		$pdf->Cell(46,17,$result[0]['ALTO'], 0, '', 'C');
		$pdf->SetXY(343, 721);
		$pdf->Cell(46,17,$result[0]['PESO'], 0, '', 'C');
		
		$sql_esp = "SELECT E.NOM_CAMPO
					FROM PRODUCTO_ESPECIFICACION  PE
						,ESPECIFICACION E 
					WHERE PE.COD_PRODUCTO = '$cod_producto' 
					AND E.COD_ESPECIFICACION = PE.COD_ESPECIFICACION 
					ORDER BY PE.ORDEN ASC";
		$result_esp = $db->build_results($sql_esp);
		
		for($k=0 ; $k < count($result_esp) ; $k++){
			if($k == 0)
				$posX = 388;
			else if($k == 1)
				$posX = 452;
			else if($k == 2)
				$posX = 517;	
		
			$nom_campo = "p.".$result_esp[$k]['NOM_CAMPO'];
			
			$sql_esp2 = "SELECT E.LABEL
					            ,LABEL_UNIDAD
					            ,$nom_campo DATO
					     FROM PRODUCTO_ESPECIFICACION  PE
					     	 ,ESPECIFICACION E
					     	 ,PRODUCTO P
					     WHERE PE.COD_PRODUCTO = '$cod_producto'
					     AND E.COD_ESPECIFICACION = PE.COD_ESPECIFICACION
					     AND P.COD_PRODUCTO  = PE.COD_PRODUCTO
					     AND E.NOM_CAMPO = '".$result_esp[$k]['NOM_CAMPO']."'";
			$result_esp2 = $db->build_results($sql_esp2);		     

			$pdf->SetFont('FuturaBook','', 8);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetXY($posX, 701);
			$pdf->Cell(64,17,$result_esp2[0]['LABEL'], 0, '', 'C');
			$pdf->SetFont('FuturaBook','', 6);
			$pdf->SetXY($posX, 708);
			if($result_esp2[0]['LABEL_UNIDAD'] == '')
				$pdf->Cell(64,17,'', 0, '', 'C');
			else	
				$pdf->Cell(64,17,'('.$result_esp2[0]['LABEL_UNIDAD'].')', 0, '', 'C');
			$pdf->SetFont('FuturaBook','', 10);
			$pdf->SetTextColor(255, 255, 255);
			$pdf->SetXY($posX, 721);
			$pdf->Cell(64,17,$result_esp2[0]['DATO'], 0, '', 'C');
		}
    }
}
?>