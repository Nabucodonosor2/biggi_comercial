<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$tipo_doc = $_REQUEST['cod_tipo_doc_sii'];
$ini = $_REQUEST['nro_inicio'];
$fin = $_REQUEST['nro_termino'];

if($tipo_doc == 1)
$tabla = 'FACTURA';
elseif($tipo_doc == 3) 
$tabla = 'NOTA_CREDITO';
elseif($tipo_doc == 5) 
$tabla = 'FACTURA';


$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "select count(*) RESULT 
		from asig_nro_doc_sii 
		where $ini > = nro_inicio and $ini < isnull(nro_inicio_devol, nro_termino + 1)";
$result = $db->build_results($sql);
$nro_ini = $result[0]['RESULT'];

$sql = "select count(*) RESULT 
		from asig_nro_doc_sii 
		where $fin > = nro_inicio and $fin < isnull(nro_inicio_devol, nro_termino + 1)";
$result = $db->build_results($sql);
$nro_fin = $result[0]['RESULT'];
		
$total_nro = $nro_ini + $nro_fin;

$sql ="select ES_DTE
		from $tabla
		where nro_$tabla between $ini and $fin";

$result = $db->build_results($sql);
	for($i=0; $i< count($result);$i++) {
		$es_dte = $result[$i]['ES_DTE'];	
		if($es_dte == 'N'){	
			print 'P';
		}else{
			if ($total_nro == 0)
			print 'N';
			else
			print 'S';
		}
	}



?>