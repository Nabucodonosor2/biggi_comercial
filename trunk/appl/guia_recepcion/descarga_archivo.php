<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_doc = $_GET["cod_doc"];
$cod_doc = base64_decode($cod_doc);

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT RUTA_ARCHIVO
			,NOM_ARCHIVO
		FROM GUIA_RECEPCION_FOTO
		WHERE COD_GUIA_RECEPCION_FOTO = $cod_doc";

$result = $db->build_results($sql);
$nom_archivo = $result[0]['NOM_ARCHIVO'];
$archivo = $result[0]['RUTA_ARCHIVO'].$nom_archivo;

if (file_exists($archivo)){
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"$nom_archivo\"\n");
	$fp=fopen("$archivo", "r");
	fpassthru($fp);
}else
	base::alert("El archivo no se encontro!!");
?>