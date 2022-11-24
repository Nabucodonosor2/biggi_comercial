<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_doc = $_GET["cod_doc"];
$cod_doc = base64_decode($cod_doc);
$cod_nota_venta = $_GET["cod_nota_venta"]; 
$nota_venta = $_GET["nota_venta"];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

if($nota_venta == 'nota_venta'){
	$sql = "SELECT NVD.RUTA_ARCHIVO
				   ,NVD.NOM_ARCHIVO
			FROM NOTA_VENTA_DOCS NVD
			WHERE NVD.ES_OC = 'S'
			AND NVD.COD_NOTA_VENTA = $cod_nota_venta";
}else{
	$sql = "select NVD.RUTA_ARCHIVO
					,NVD.NOM_ARCHIVO
			from NOTA_VENTA_DOCS NVD
			where NVD.COD_NOTA_VENTA_DOCS = $cod_doc"; 
}		
$result = $db->build_results($sql);
$nom_archivo = $result[0]['NOM_ARCHIVO'];
$archivo = $result[0]['RUTA_ARCHIVO'].$nom_archivo;
if (file_exists($archivo)) {
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"$nom_archivo\"\n");
	$fp=fopen("$archivo", "r");
	fpassthru($fp);
}
else
	base::alert("El archivo no se encontro!!");
?>