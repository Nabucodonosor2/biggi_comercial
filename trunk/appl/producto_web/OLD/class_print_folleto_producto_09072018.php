<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class print_folleto_producto{
	function __construct(){
	}
	
	function folleto($cod_producto, $pdf){
		$pdf->AddPage();
		$pdf->SetAutoPageBreak(true,0);
		$titulo = "Folleto";
		$pdf->SetTitle($titulo);
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$sql = "SELECT NOM_PRODUCTOT1
					  ,NOM_PRODUCTOT2
					  ,NOM_PRODUCTOT3
				FROM PRODUCTO
				WHERE COD_PRODUCTO = '$cod_producto'";
		$result = $db->build_results($sql);
		
		$pdf->Image('../../images_appl/ficha_folleto.jpg', 0, 0,612,792);
		
		$pdf->SetXY(30, 15);
		$pdf->SetFont('Arial','', 24);
		$x_t1 = $pdf->GetStringWidth($result[0]['NOM_PRODUCTOT1']);
		
		$pdf->Cell(70,17,$result[0]['NOM_PRODUCTOT1'], 0, '', 'L');
		$pdf->SetTextColor(192, 192, 192);
		$pdf->SetXY($x_t1+40, 15);
		$pdf->Cell(70,17,$result[0]['NOM_PRODUCTOT2'], 0, '', 'L');
		$pdf->SetFont('Arial','', 14);
		$pdf->SetXY(30, 40);
		$pdf->Cell(70,17,$result[0]['NOM_PRODUCTOT3'], 0, '', 'L');
		
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Arial','', 20);
		$pdf->SetXY(380, 159);
		$pdf->Cell(70,17,'MODELO', 0, '', 'L');
		
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetFont('Arial','', 34);
		$pdf->SetXY(392, 195);
		$pdf->Cell(70,17,$cod_producto, 0, '', 'L');
		
		$pdf->SetFont('Arial','', 8);
		$pdf->SetXY(386, 260);
		$pdf->Cell(70,17, 'ECONOMIA', 0, '', 'L');
		$pdf->SetXY(439, 260);
		$pdf->Cell(70,17, 'DESPACHO', 0, '', 'L');
		$pdf->SetXY(487, 260);
		$pdf->Cell(70,17, 'INSTALACION', 0, '', 'L');
		$pdf->SetXY(544, 264);
		$pdf->MultiCell(50,8, 'SERV. TECNICO', 0, 'C');
		
		if(file_exists($this->folder_producto($cod_producto).'/FOLLETO_FOTO1.jpg')){
			$sql = "SELECT COORDENADA_X
						  ,COORDENADA_Y
					FROM FOTO_FICHA_FOLLETO
					WHERE COD_PRODUCTO = '$cod_producto'
					AND NOM_FOTO = 'FOLLETO_FOTO1'";
			$result = $db->build_results($sql);
			$POS_X = $result[0]['COORDENADA_X'];
			$POS_Y = $result[0]['COORDENADA_Y'];
		
			$pdf->Image($this->folder_producto($cod_producto).'/FOLLETO_FOTO1.jpg' , 95+$POS_X, 130+$POS_Y, 199, 292);
		}else
			$pdf->Image('../../../../producto_imagen/parametro/foto_no_disponible.jpg' , 95+$POS_X, 180+$POS_Y, 200, 200);
		
		$sql = "SELECT NOM_ATRIBUTO 
				FROM ATRIBUTO_DESTACADO
				WHERE COD_PRODUCTO = '$cod_producto'
				ORDER BY ORDEN ASC";
		$result = $db->build_results($sql);
		
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Arial','', 12);
		for($l=0 ; $l < count($result) ; $l++){
			$pdf->SetXY(430-($l*13), 322+($l*41));
			$pdf->Cell(70,17,$result[$l]['NOM_ATRIBUTO'], 0, '', 'L');
		}
		
		$pdf->SetXY(30, 490);
		$pdf->SetTextColor(255, 0, 0);
		$pdf->SetFont('Arial','', 24);
		$pdf->Cell(70,17,'CARACTERISTICAS', 0, '', 'L');
		$pdf->SetXY(260, 490);
		$pdf->SetTextColor(192, 192, 192);
		$pdf->SetFont('Arial','', 24);
		$pdf->Cell(70,17,'TECNICAS', 0, '', 'L');
		
		$sql = "SELECT NOM_ATRIBUTO_PRODUCTO
					  ,SALTO_LINEA
				FROM ATRIBUTO_PRODUCTO
				WHERE COD_PRODUCTO = '$cod_producto'
				ORDER BY ORDEN ASC";
		$result = $db->build_results($sql);
		
		$pdf->SetFont('Arial','', 10);
		$pdf->SetXY(30, 520);
		$pdf->SetTextColor(0, 0, 0);
		
		$salto_linea = false;
		for($k=0 ; $k < count($result) ; $k++){
			if($result[$k]['SALTO_LINEA'] == 'S'){
				$pdf->SetY(520);
				$posX = 310;
				$salto_linea = true;
			}else{
				if(!$salto_linea)
					$posX = 50;
			}
			
			$posY = $pdf->getY();
			$pdf->SetXY($posX, $posY);
			$pdf->Image('../../images_appl/circle.png',$posX-7,$posY+4, 7, 7);
			$pdf->MultiCell(250,14,$result[$k]['NOM_ATRIBUTO_PRODUCTO'], 0, '', 'L');
		}
        
		$pdf->SetFont('Arial','', 8);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY(114, 734);
		$pdf->Cell(70,17,'MODELO EQUIPO', 0, '', 'L');
		
		$pdf->SetFont('Arial','', 7);
		$pdf->SetXY(192, 711);
		$pdf->Cell(10,17,'D', 0, '', 'C');
		$pdf->SetXY(192, 718);
		$pdf->Cell(10,17,'I', 0, '', 'C');
		$pdf->SetXY(192, 725);
		$pdf->Cell(10,17,'M', 0, '', 'C');//
		$pdf->SetXY(192, 732);
		$pdf->Cell(10,17,'E', 0, '', 'C');
		$pdf->SetXY(192, 738);
		$pdf->Cell(10,17,'N', 0, '', 'C');
		$pdf->SetXY(192, 744);
		$pdf->Cell(10,17,'S', 0, '', 'C');
		$pdf->SetXY(192, 750);
		$pdf->Cell(10,17,'I', 0, '', 'C');
		$pdf->SetXY(192, 756);
		$pdf->Cell(10,17,'O', 0, '', 'C');
		$pdf->SetXY(192, 762);
		$pdf->Cell(10,17,'N', 0, '', 'C');
		$pdf->SetXY(192, 768);
		$pdf->Cell(10,17,'E', 0, '', 'C');
		$pdf->SetXY(192, 774);
		$pdf->Cell(10,17,'S', 0, '', 'C');
		
		$pdf->SetFont('Arial','', 8);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY(210, 727);
		$pdf->Cell(70,17,'LARGO', 0, '', 'L');
		$pdf->SetXY(252, 727);
		$pdf->Cell(70,17,'ANCHO', 0, '', 'L');
		$pdf->SetXY(299, 727);
		$pdf->Cell(70,17,'ALTO', 0, '', 'L');
		$pdf->SetXY(340, 727);
		$pdf->Cell(70,17,'PESO', 0, '', 'L');
		
		$pdf->SetFont('Arial','', 6);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY(216, 734);
		$pdf->Cell(70,17,'(CM)', 0, '', 'L');
		$pdf->SetXY(260, 734);
		$pdf->Cell(70,17,'(CM)', 0, '', 'L');
		$pdf->SetXY(303, 734);
		$pdf->Cell(70,17,'(CM)', 0, '', 'L');
		$pdf->SetXY(344, 734);
		$pdf->Cell(70,17,'(KG)', 0, '', 'L');
		
		$sql = "SELECT LARGO
					  ,ANCHO
					  ,ALTO
					  ,PESO
				FROM PRODUCTO
				WHERE COD_PRODUCTO = '$cod_producto'";
		$result = $db->build_results($sql);
		
		$pdf->SetFont('Arial','', 8);
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetXY(123, 750);
		$pdf->Cell(70,17,$cod_producto, 0, '', 'L');
		
		$pdf->SetXY(215, 750);
		$pdf->Cell(70,17,$result[0]['LARGO'], 0, '', 'L');
		$pdf->SetXY(263, 750);
		$pdf->Cell(70,17,$result[0]['ANCHO'], 0, '', 'L');
		$pdf->SetXY(302, 750);
		$pdf->Cell(70,17,$result[0]['ALTO'], 0, '', 'L');
		$pdf->SetXY(344, 750);
		$pdf->Cell(70,17,$result[0]['PESO'], 0, '', 'L');
		
		$pdf->SetFont('Arial','', 7.2);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY(380, 756);
		$pdf->Cell(70,17,'Comercial Biggi (Chile) S.A. - Av. Portugal 1726 Santiago, Chile', 0, '', 'L');
		$pdf->SetXY(400, 767);
		$pdf->Cell(70,17,'Tel. (56-2) 24126200 - www.biggi.cl - info@biggi.cl', 0, '', 'L');
		
		require_once("class_qrcode.php");
		$qr = new qrcode();
		$qr->link("http://www.biggi.cl/sysbiggi_new/biggi_web_2/biggi_web_2/print_detalle_producto.php?cod_producto=".urlencode($cod_producto));
		$file = $qr->get_image();
		$cod_producto_sin_slash = str_replace("/", "_", $cod_producto);
		$fname = dirname(__FILE__)."/img_temp/".$cod_producto_sin_slash.".png";
		$qr->save_image($file, $fname);
		$pdf->Image($fname,539,690,55,55);
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
		
		$pdf->Image('../../images_appl/ficha_tecnica.jpg', 0, 0,612,792);
		
		$pdf->SetXY(7, 18);
		$pdf->SetFont('Arial','', 18);
		$pdf->Cell(70,17,'Ficha Técnica', 0, '', 'L');
		$pdf->SetXY(7, 46);
		$pdf->SetFont('Arial','', 24);
		$x_t1 = $pdf->GetStringWidth($result[0]['NOM_PRODUCTOT1']);
		
		$pdf->Cell(70,17,$result[0]['NOM_PRODUCTOT1'], 0, '', 'L');
		$pdf->SetTextColor(192, 192, 192);
		$pdf->SetXY($x_t1+17, 46);
		$pdf->Cell(70,17,$result[0]['NOM_PRODUCTOT2'], 0, '', 'L');
		$pdf->SetFont('Arial','', 14);
		$pdf->SetXY(7, 71);
		$pdf->Cell(70,17,$result[0]['NOM_PRODUCTOT3'], 0, '', 'L');
		
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY(519, 30);
		$pdf->Cell(70,17,'MODELO', 0, '', 'L');
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetXY(529, 70);
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
		$pdf->SetFont('Arial','', 14);
		$pdf->SetXY(375, 703);
		$pdf->Cell(70,17,'ESPECIFICACIONES', 0, '', 'L');
		$pdf->SetTextColor(255, 0, 0);
		$pdf->SetXY(520, 703);
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
		$pdf->SetFont('Arial','', 7);
		$pdf->SetXY(200, 741);
		$pdf->Cell(45,17,'LARGO', 0, '', 'C');
		$pdf->SetXY(248, 741);
		$pdf->Cell(50,17,'ANCHO', 0, '', 'C');
		$pdf->SetXY(299, 741);
		$pdf->Cell(50,17,'ALTO', 0, '', 'C');
		$pdf->SetXY(348, 741);
		$pdf->Cell(50,17,'PESO', 0, '', 'C');
		
		//u de medida
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Arial','', 5);
		$pdf->SetXY(200, 748);
		$pdf->Cell(45,17,'(CM)', 0, '', 'C');
		$pdf->SetXY(248, 748);
		$pdf->Cell(50,17,'(CM)', 0, '', 'C');
		$pdf->SetXY(299, 748);
		$pdf->Cell(50,17,'(CM)', 0, '', 'C');
		$pdf->SetXY(348, 748);
		$pdf->Cell(50,17,'(KG)', 0, '', 'C');
		
		//valores
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetFont('Arial','', 7);
		$pdf->SetXY(200, 761);
		$pdf->Cell(45,17,$result[0]['LARGO'], 0, '', 'C');
		$pdf->SetXY(248, 761);
		$pdf->Cell(50,17,$result[0]['ANCHO'], 0, '', 'C');
		$pdf->SetXY(299, 761);
		$pdf->Cell(50,17,$result[0]['ALTO'], 0, '', 'C');
		$pdf->SetXY(348, 761);
		$pdf->Cell(50,17,$result[0]['PESO'], 0, '', 'C');
		
		$sql_esp = "SELECT E.NOM_CAMPO
					FROM PRODUCTO_ESPECIFICACION  PE
						,ESPECIFICACION E 
					WHERE PE.COD_PRODUCTO = '$cod_producto' 
					AND E.COD_ESPECIFICACION = PE.COD_ESPECIFICACION 
					ORDER BY PE.ORDEN DESC";
		$result_esp = $db->build_results($sql_esp);
		
		for($k=0 ; $k < count($result_esp) ; $k++){
			if($k == 0)
				$posX = 399.5;
			else if($k == 1)
				$posX = 470;
			else if($k == 2)
				$posX = 541;	
		
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

			$pdf->SetFont('Arial','', 7);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetXY($posX, 741);
			$pdf->Cell(70,17,$result_esp2[0]['LABEL'], 0, '', 'C');
			$pdf->SetFont('Arial','', 5);
			$pdf->SetXY($posX, 748);
			if($result_esp2[0]['LABEL_UNIDAD'] == '')
				$pdf->Cell(70,17,'', 0, '', 'C');
			else	
				$pdf->Cell(70,17,'('.$result_esp2[0]['LABEL_UNIDAD'].')', 0, '', 'C');
			$pdf->SetFont('Arial','', 7);
			$pdf->SetTextColor(255, 255, 255);
			$pdf->SetXY($posX, 761);
			$pdf->Cell(70,17,$result_esp2[0]['DATO'], 0, '', 'C');
		}
    }
}
?>