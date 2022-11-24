<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_nota_venta			= $_REQUEST['cod_nota_venta'];
$tipo_doc				= $_REQUEST['tipo_doc'];
$K_ESTADO_SII_EMITIDA	= 1;
$K_ESTADO_CERRADA		= 2;
$K_ESTADO_CONFIRMADA	= 4;
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$exito = true;



if($tipo_doc == 'NV'){
	///valida que la NV exista
	$sql = "SELECT COUNT(*) COUNT 
			FROM NOTA_VENTA
			WHERE COD_NOTA_VENTA = $cod_nota_venta";
	$result = $db->build_results($sql);
	if($result[0]['COUNT'] == 0){
		print 'ERROR|La Nota de Venta Nº '.$cod_nota_venta.' no existe.';
		return;
	}
    
	//valida que la NV este confirmada
	$sql = "SELECT COUNT(*) COUNT
			FROM NOTA_VENTA 
			WHERE COD_NOTA_VENTA = ".$cod_nota_venta." 
			AND	COD_ESTADO_NOTA_VENTA IN (".$K_ESTADO_CONFIRMADA.", ".$K_ESTADO_CERRADA.")";
	$result = $db->build_results($sql);
	if($result[0]['COUNT'] == 0){
		print 'ERROR|La Nota de Venta Nº '.$cod_nota_venta.' no esta confirmada.';	
		return;
	}

	/* valida que la NV no tenga FAs anteriores en estado = emitida
	ya que es suceptible a errores tener varias GD en estado emitida, ya que la cantidad por despachar 
	siempre será la misma cantidad de la NV.
	*/
	$sql = "SELECT COUNT(*) COUNT
			FROM FACTURA
			WHERE COD_DOC = $cod_nota_venta
			AND COD_ESTADO_DOC_SII = ".$K_ESTADO_SII_EMITIDA;
	$result = $db->build_results($sql);
	if($result[0]['COUNT'] <> 0) {
		print 'ERROR|La Nota de Venta Nº '.$cod_nota_venta.' tiene Factura(s) pendientes(s) en estado emitido. Para poder generar más Facturas deberá imprimir los documentos emitidos.';	
		return;
	}

	//****************
	// valida que este pendiente de facturar
	$sql = "SELECT dbo.f_nv_porc_facturado($cod_nota_venta) PORC_FACTURA";
	$result = $db->build_results($sql);
	$porc_factura = $result[0]['PORC_FACTURA'];
	if($porc_factura >= 100) { 
		print 'ERROR|La Nota de Venta Nº '.$cod_nota_venta.' está totalmente Facturada.';	
		return;
	}
	/** Si la NV ya fue autorizada por SP, RE, o JT, entonces NO requiere autorización. **/
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
    	/** Si el RUT de la NV pertenece a un Centro de Costo (Sodexo, Compass, CDR), entonces NO requiere autorización.. **/
    	$sql_val2 = "SELECT COUNT(*) COUNT
    				 FROM CENTRO_COSTO CC
    					 ,CENTRO_COSTO_EMPRESA CCE
    				 WHERE COD_EMPRESA = $cod_empresa
    				 AND CC.COD_CENTRO_COSTO = CCE.COD_CENTRO_COSTO
    				 AND CC.COD_CENTRO_COSTO <> 001";
    	$result_val2 = $db->build_results($sql_val2);
    	
    	if($result_val2[0]['COUNT'] == 0){/** Si la forma de pago de la NV es: CCF - 60 DIAS
		                                                                       CCF - 45 DIAS
		                                                                       CCF - 30 DIAS**/
    		if($cod_forma_pago <> 30 && $cod_forma_pago <> 31 && $cod_forma_pago <> 32 && $cod_forma_pago <> 10 && $cod_forma_pago <> 7){
    			/**		Si la forma de pago de no es ninguna de la anteriores, se consulta si la forma de pago 
    			 *      se considera como Contado para el SII.
		                Si lo es, entonces se permite Facturar y/o despachar.**/
    			$sql_val3 = "SELECT COUNT(*) COUNT
    						 FROM FORMA_PAGO
    						 WHERE COD_FORMA_PAGO = $cod_forma_pago
    						 AND FORMA_PAGO_SII = 1";
    			$result_val3 = $db->build_results($sql_val3);
    			
    			if($result_val3[0]['COUNT'] == 0){
    				
    				$sql_val4 = "SELECT COUNT(*) COUNT
    							 FROM EMPRESA
    							 WHERE SUJETO_A_APROBACION = 'S'
    							 AND COD_EMPRESA = $cod_empresa";
    				$result_val4 = $db->build_results($sql_val4);
    				
    				if($result_val4[0]['COUNT'] == 0)
    					$exito = false;
    			}
    		}
    	}	
    }
    
    if(!$exito){
    	print "NO_VALIDA|";
    	return;
    }

	print 'PASS';
}else if($tipo_doc == 'GD'){
	print 'PASS';
}else if($tipo_doc == 'NV_ANTICIPO'){
	print 'PASS';
}
?>