<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_nota_venta			= $_REQUEST['cod_nota_venta'];
$tipo_doc				= $_REQUEST['tipo_doc'];
$K_ESTADO_SII_EMITIDA	= 1;
$K_ESTADO_CERRADA		= 2;
$K_ESTADO_CONFIRMADA	= 4;
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$exito = true;

$sql_val = "SELECT AUTORIZA_PROCESAR_VENTA
				  ,CREADA_EN_SV
				  ,COD_FORMA_PAGO
			FROM NOTA_VENTA
			WHERE COD_NOTA_VENTA = ".$cod_nota_venta."
			AND COD_EMPRESA not in (SELECT COD_EMPRESA 
									FROM CENTRO_COSTO CC
										,CENTRO_COSTO_EMPRESA CCE
									WHERE CC.COD_CENTRO_COSTO = CCE.COD_CENTRO_COSTO
									AND CC.COD_CENTRO_COSTO <> 001)";
$result_val					= $db->build_results($sql_val);
$cod_forma_pago				= $result_val[0]['COD_FORMA_PAGO'];
$autoriza_procesar_venta	= $result_val[0]['AUTORIZA_PROCESAR_VENTA'];
$creada_en_sv				= $result_val[0]['CREADA_EN_SV'];

if($autoriza_procesar_venta <> 'S' && $autoriza_procesar_venta <> ''){
	if($creada_en_sv == 'N'){
		if($cod_forma_pago <> 30 && $cod_forma_pago <> 31 && $cod_forma_pago <> 32 && $cod_forma_pago <> 10 && $cod_forma_pago <> 7)
			$exito = false;
	}
}

if(!$exito){
	print "ERROR|La Nota de Venta N� $cod_nota_venta NO se encuentra autorizada para procesar.\nSolicitar autorizacion con Administracion BIGGI.";
	return;
}

if($tipo_doc == 'NV'){
	///valida que la NV exista
	$sql = "SELECT COUNT(*) COUNT 
			FROM NOTA_VENTA
			WHERE COD_NOTA_VENTA = $cod_nota_venta";
	$result = $db->build_results($sql);
	if($result[0]['COUNT'] == 0){
		print 'ERROR|La Nota de Venta N� '.$cod_nota_venta.' no existe.';
		return;
	}

	//valida que la NV este confirmada
	$sql = "SELECT COUNT(*) COUNT
			FROM NOTA_VENTA 
			WHERE COD_NOTA_VENTA = ".$cod_nota_venta." 
			AND	COD_ESTADO_NOTA_VENTA IN (".$K_ESTADO_CONFIRMADA.", ".$K_ESTADO_CERRADA.")";
	$result = $db->build_results($sql);
	if($result[0]['COUNT'] == 0){
		print 'ERROR|La Nota de Venta N� '.$cod_nota_venta.' no esta confirmada.';	
		return;
	}

	/* valida que la NV no tenga FAs anteriores en estado = emitida
	ya que es suceptible a errores tener varias GD en estado emitida, ya que la cantidad por despachar 
	siempre ser� la misma cantidad de la NV.
	*/
	$sql = "SELECT COUNT(*) COUNT
			FROM FACTURA
			WHERE COD_DOC = $cod_nota_venta
			AND COD_ESTADO_DOC_SII = ".$K_ESTADO_SII_EMITIDA;
	$result = $db->build_results($sql);
	if($result[0]['COUNT'] <> 0) {
		print 'ERROR|La Nota de Venta N� '.$cod_nota_venta.' tiene Factura(s) pendientes(s) en estado emitido. Para poder generar m�s Facturas deber� imprimir los documentos emitidos.';	
		return;
	}

	//****************
	// valida que este pendiente de facturar
	$sql = "SELECT dbo.f_nv_porc_facturado($cod_nota_venta) PORC_FACTURA";
	$result = $db->build_results($sql);
	$porc_factura = $result[0]['PORC_FACTURA'];
	if($porc_factura >= 100) { 
		print 'ERROR|La Nota de Venta N� '.$cod_nota_venta.' est� totalmente Facturada.';	
		return;
	}

	print 'PASS';
}else if($tipo_doc == 'GD'){
	print 'PASS';
}else if($tipo_doc == 'NV_ANTICIPO'){
	print 'PASS';
}
?>