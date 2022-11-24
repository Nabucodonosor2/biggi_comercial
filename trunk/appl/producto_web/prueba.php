<?php
ini_set('memory_limit', '3000M');
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once("../../appl.ini");

$temp = new Template_appl('prueba.htm');
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "select FOTO_CHICA
			  ,FOTO_GRANDE
			  ,NULL IMG_FOTO_CHICA
			  ,NULL IMG_FOTO_GRANDE
			  ,COD_PRODUCTO
			  ,NULL COD_PRODUCTO_LABEL
			  ,NULL LABEL_GRANDE
			  ,NULL LABEL_CHICO
		from PRODUCTO
		WHERE COD_PRODUCTO IN
		('1/1-10'
		,'1/1-10P'
		,'1/1-10R'
		,'1/1-15'
		,'1/1-15R'
		,'1/1-20'
		,'1/2-10'
		,'1/2-6'
		,'1/2-6P'
		,'1/2-6R'
		,'1/2-8'
		,'1/2-8R'
		,'1/3-4'
		,'1/3-6'
		,'1/4-2'
		,'1/4-3'
		,'1/4-4'
		,'1/6-1'
		,'1/6-2'
		,'1/6-2R'
		,'QUE/MAS'
		,'QUE/SB')";
		//WHERE FOTO_CHICA IS NOT NULL";
$dw = new datawindow($sql, 'PRODUCTO');
$dw->retrieve();

for($i=0 ; $i < $dw->row_count() ; $i++){

	//$foto_chica = $dw->get_item($i, 'FOTO_CHICA');
	//$foto_grande = $dw->get_item($i, 'FOTO_GRANDE');

	$cod_producto = preg_replace("%[^A-Z^0-9^-]%", "_", $dw->get_item($i, 'COD_PRODUCTO'));

	//$dw->set_item($i, 'IMG_FOTO_CHICA', '<img src="data:url/jpeg;base64,'.base64_encode($foto_chica).'"/>');
	//$dw->set_item($i, 'IMG_FOTO_GRANDE', '<img src="data:url/jpeg;base64,'.base64_encode($foto_grande).'"/>');

	$dw->set_item($i, 'LABEL_CHICO', $cod_producto.'_C.jpg');
	$dw->set_item($i, 'LABEL_GRANDE', $cod_producto.'_G.jpg');
	$dw->set_item($i, 'COD_PRODUCTO_LABEL', $cod_producto);
}

$dw->habilitar($temp, true);
print $temp->toString();
?>