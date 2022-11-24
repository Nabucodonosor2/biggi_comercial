<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl('dlg_consulta_stock.htm');

//$cod_producto = urldecode($_REQUEST["cod_producto"]);
$cod_producto = base64_decode($_REQUEST["cod_producto"]);
//$cod_producto	= $_REQUEST['cod_producto'];

$array_cod_producto = explode("|", $cod_producto);
$productos = '';

for($i=0 ; $i < count($array_cod_producto)-1 ; $i++){
	$array_producto = explode("*", $array_cod_producto[$i]);
		
	if($productos == '')
		$productos = "'$array_producto[0]'";
	else	
		$productos = $productos.','."'$array_producto[0]'";
}
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT '' IT 
				,P.COD_PRODUCTO
				,P.NOM_PRODUCTO
				,'' CANTIDAD
				,'' STOCK_TODOINOX
				,'' STATUS_TODOINOX
				,'' STOCK_BODEGA
				,'' STATUS_BODEGA
				,'' COLOR_ROJO
		FROM PRODUCTO P
		where P.COD_PRODUCTO IN($productos)";
			
$dw = new datawindow($sql,'STOCK_PRODUCTO',true,true);
$dw->add_control(new static_text('COD_PRODUCTO'));
$dw->add_control(new static_text('NOM_PRODUCTO'));
$dw->add_control(new static_text('CANTIDAD'));
$dw->add_control(new static_text('STOCK_TODOINOX'));
$dw->add_control(new static_text('STATUS_TODOINOX'));
$dw->add_control(new static_text('STOCK_BODEGA'));
$dw->add_control(new static_text('STATUS_BODEGA'));
$dw->retrieve();

for($i=0 ; $i < count($array_cod_producto) ; $i++){
	$array_producto = explode("*", $array_cod_producto[$i]);
	for($h=0 ; $h < $dw->row_count(); $h++){
		$cod_producto_dw = $dw->get_item($h, 'COD_PRODUCTO');
		$cantidad = str_replace(",", ".", $array_producto[3]);
		if($cod_producto_dw == $array_producto[0]){
			$dw->set_item($h, 'IT',$h+1);
			$dw->set_item($h, 'CANTIDAD',$array_producto[3]);
			if($array_producto[2]=='BODEGA'){
				$dw->set_item($h, 'STOCK_BODEGA',$array_producto[1]);
				if($cantidad <= $array_producto[1]){
					$dw->set_item($h, 'STATUS_BODEGA','OK');
				}else{
					$dw->set_item($h, 'STATUS_BODEGA','INSUFICIENTE');
					$dw->set_item($h, 'COLOR_ROJO','#F78181');
				}
			}
			else{
				$dw->set_item($h, 'STOCK_BODEGA','-');
				$dw->set_item($h, 'STATUS_BODEGA','-');
			}	
			if($array_producto[2]=='TODOINOX'){
				$dw->set_item($h, 'STOCK_TODOINOX',$array_producto[1]);
				if($cantidad <= $array_producto[1]){
					$dw->set_item($h, 'STATUS_TODOINOX','OK');
				}else{
					$dw->set_item($h, 'STATUS_TODOINOX','INSUFICIENTE');
					$dw->set_item($h, 'COLOR_ROJO','#F78181');
				}	
			}else{
				$dw->set_item($h, 'STOCK_TODOINOX','-');
				$dw->set_item($h, 'STATUS_TODOINOX','-');
			}	
		}
	}
}

$dw->habilitar($temp, true);
print $temp->toString();
?>