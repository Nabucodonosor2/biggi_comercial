<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_nota_venta			= $_REQUEST['cod_nota_venta'];
$K_ESTADO_SII_EMITIDA	= 1;
$K_ESTADO_CERRADA		= 2;
$K_ESTADO_CONFIRMADA	= 4;
$K_TIPO_ARRIENDO		= 5;
$exito = true;
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

///valida que la NV exista
$sql = "SELECT COD_NOTA_VENTA
		FROM NOTA_VENTA
		WHERE COD_NOTA_VENTA = $cod_nota_venta";
$result = $db->build_results($sql);
if (count($result) == 0){
	print "ERROR|La Nota de Venta Nº '.$cod_nota_venta.' no existe.";
	return;
}

//validacion nueva
$sql_val = "SELECT AUTORIZA_PROCESAR_VENTA
				  ,COD_FORMA_PAGO
				  ,COD_EMPRESA
			FROM NOTA_VENTA
			WHERE COD_NOTA_VENTA = ".$cod_nota_venta;
$result_val					= $db->build_results($sql_val);
$cod_forma_pago				= $result_val[0]['COD_FORMA_PAGO'];
$autoriza_procesar_venta	= $result_val[0]['AUTORIZA_PROCESAR_VENTA'];
$cod_empresa				= $result_val[0]['COD_EMPRESA'];

if($autoriza_procesar_venta <> 'S'){
    /**SE COMENTA PORQUE YA NO ES NECESARIA LA VALIDACION**/
	/*
	$sql_val2 = "SELECT COUNT(*) COUNT
				 FROM CENTRO_COSTO CC
					 ,CENTRO_COSTO_EMPRESA CCE
				 WHERE COD_EMPRESA = $cod_empresa
				 AND CC.COD_CENTRO_COSTO = CCE.COD_CENTRO_COSTO
				 AND CC.COD_CENTRO_COSTO <> 001";
	$result_val2 = $db->build_results($sql_val2);
	
	if($result_val2[0]['COUNT'] == 0){*/

	// MH 10-12-2024 SE COMENTA EL SIGUIENTE IF PUES NO TIENE SENTIDO VALIDAR POR FORMA DE PAGO SI EXISTE LA AUTORIACION VIA ADM		
	//if($cod_forma_pago <> 30 && $cod_forma_pago <> 31 && $cod_forma_pago <> 32 && $cod_forma_pago <> 10 && $cod_forma_pago <> 7){
		

		// MH 10-12-2024 SE COMENTA EL SIGUIENTE BLOQUE PUES NO TIENE SENTIDO VALIDAR POR FORMA DE PAGO SI EXISTE LA AUTORIACION VIA ADM	
		//$sql_val3 = "SELECT COUNT(*) COUNT
		//			 FROM FORMA_PAGO
		//			 WHERE COD_FORMA_PAGO = $cod_forma_pago
		//			 AND CRITERIO_DESPACHO_FACTURA = 1";
		//$result_val3 = $db->build_results($sql_val3);
		
		//if($result_val3[0]['COUNT'] == 0){
			
			$sql_val4 = "SELECT COUNT(*) COUNT
						 FROM EMPRESA
						 WHERE SUJETO_A_APROBACION = 'S'
						 AND COD_EMPRESA = $cod_empresa";
			$result_val4 = $db->build_results($sql_val4);
			
			if($result_val4[0]['COUNT'] == 0)
				$exito = false;
		//}
	//}
	//}	
}

if(!$exito){
	print "ERROR|La Nota de Venta N° ".$cod_nota_venta." NO se encuentra autorizada para procesar despacho.\n\nFavor solicitar autorizacion con Administracion BIGGI.";
	return;
}

//valida que la NV este confirmada
$sql = "SELECT COD_NOTA_VENTA
		FROM NOTA_VENTA 
		WHERE COD_NOTA_VENTA = ".$cod_nota_venta." 
		AND	COD_ESTADO_NOTA_VENTA IN (".$K_ESTADO_CONFIRMADA.", ".$K_ESTADO_CERRADA.")";
$result = $db->build_results($sql);
if (count($result) == 0){
	print "ERROR|La Nota de Venta Nº '.$cod_nota_venta.' no esta confirmada.";
	return;
}

$sql = "SELECT COD_GUIA_DESPACHO
		FROM GUIA_DESPACHO
		WHERE COD_DOC = $cod_nota_venta 
		AND COD_TIPO_GUIA_DESPACHO <> ".$K_TIPO_ARRIENDO."
		AND COD_ESTADO_DOC_SII = ".$K_ESTADO_SII_EMITIDA;
$result = $db->build_results($sql);
if (count($result) != 0){
	print "ERROR|La Nota de Venta Nº '.$cod_nota_venta.' tiene Guía(s) pendientes(s) en estado emitido. Para poder generar más guías deberá imprimir los documentos emitidos.";
	return;
}

// valida que hayan item por despachar
$sql = "SELECT SUM(dbo.f_nv_cant_por_despachar(IT.COD_ITEM_NOTA_VENTA, 'TODO_ESTADO')) POR_DESPACHAR
		FROM ITEM_NOTA_VENTA IT, NOTA_VENTA NV
		WHERE NV.COD_NOTA_VENTA = $cod_nota_venta
		AND NV.COD_NOTA_VENTA = IT.COD_NOTA_VENTA";
$result = $db->build_results($sql);
$por_despachar = $result[0]['POR_DESPACHAR'];

if ($por_despachar <= 0){
	//S  =  genera salida. es decir que la factura se toma  como Guia de Despacho.
	$sql = "select f.cod_factura,
					u.nom_usuario
			from factura f, usuario u
			where f.cod_doc = $cod_nota_venta
			and f.cod_estado_doc_sii = ".$K_ESTADO_SII_EMITIDA."
			and f.genera_salida = 'S'
			and f.cod_usuario = u.cod_usuario";
	$result = $db->build_results($sql);
	$count_fa = count($result);
	$emisor = $result[0]['nom_usuario'];			
	
	if($count_fa == 0){
		print "ERROR|La Nota de Venta Nº '.$cod_nota_venta.' está totalmente despachada.";
		return;
	}else{
		print "ERROR|La Nota de Venta Nº '.$cod_nota_venta.' tiene asociada a una Factura que esta marcada como Genera Salida. \nEmisor: ".$emisor;
		return;
	}
}

print 'PASS';
?>