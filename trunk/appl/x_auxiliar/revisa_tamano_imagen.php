<?php
//http://www.biggi.cl/sysbiggi_new/comercial_biggi/biggi/trunk/appl/x_auxiliar/revisa_tamano_imagen.php

require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../appl.ini");


$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "select  COD_PRODUCTO
			, FOTO_CHICA
            ,FOTO_GRANDE
        from    PRODUCTO
        where (FOTO_CHICA is not null or FOTO_GRANDE is not null)
          and dbo.f_prod_web (COD_PRODUCTO) = 'S'";
$result = $db->build_results($sql);

$fname = tempnam("/tmp", "tmp");
$handle = fopen($fname,"w");
fwrite($handle, "cod_producto\ttipo_foto\twidth\thigth\r\n");
for ($i=0; $i < count($result); $i++) {
	//valida foto chica
	$file = tempnam("tmp", 'tmp');
	$fp = fopen($file, 'w');
	fwrite($fp, $result[$i]['FOTO_CHICA']);
	fclose($fp);
	$size = getimagesize($file);
	unlink ($file);
	if ($size[0] != 130 || $size[1] != 130)
		fwrite($handle, $result[$i]['COD_PRODUCTO']."\tFOTO_CHICA\t".$size[0]."\t".$size[1]."\r\n");

	//valida foto grande
	$file = tempnam("tmp", 'tmp');
	$fp = fopen($file, 'w');
	fwrite($fp, $result[$i]['FOTO_GRANDE']);
	fclose($fp);
	$size = getimagesize($file);
	unlink ($file);
	if ($size[0] != 300 || $size[1] != 400)
		fwrite($handle, $result[$i]['COD_PRODUCTO']."\tFOTO_GRANDE\t".$size[0]."\t".$size[1]."\r\n");
		
}

fclose($handle);
header("Content-Type: application/force-download; name=\"tmp.txt\"");
header("Content-Disposition: inline; filename=\"tmp.txt\"");
$fh=fopen($fname, "rb");
fpassthru($fh);
unlink($fname);
?>