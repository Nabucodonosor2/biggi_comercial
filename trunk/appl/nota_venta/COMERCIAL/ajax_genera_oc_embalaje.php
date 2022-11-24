<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
function genera_item_orden_compra($cod_orden_compra,$precio_neto) {
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$operacion = 'INSERT';
	$orden	= 10;
	$item = 1;	
	$cod_producto = 'E';
	$nom_producto = 'EMBALAJE EN JABAS DE MADERA';			
	$cantidad	= 1.00 ;
	$cod_tipo_te = 'NULL';
	$motivo_te = 'NULL';		
	$cod_item_nota_venta = 'NULL';
	
	$param	= "'$operacion'
				,NULL
				,$cod_orden_compra
				,$orden
				,$item
				,'$cod_producto'
				,'$nom_producto'
				,$cantidad
				,$precio_neto
				,$cod_tipo_te
				,$motivo_te
				,$cod_item_nota_venta";
	$sp = 'spu_item_orden_compra';
	if(!$db->EXECUTE_SP($sp, $param))
		return "Fracaso";
	else
		return "Exito";	
}
$datos = $_REQUEST["datos"];
$array_datos = explode("|", $datos);
$cod_nota_venta = $array_datos[0];
$cod_usuario = $array_datos[1];
$precio_neto = $array_datos[2];
$resultado 	= '';
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql ="SELECT VALOR FROM PARAMETRO WHERE COD_PARAMETRO = 1";
$result = $db->build_results($sql);
$porc_iva = $result[0]['VALOR'];

$operacion = 'INSERT';
$cod_usuario_sol = $cod_usuario;
$cod_moneda = 1;
$cod_est_oc = 3;
$cod_cta_cte = 1;
$referencia = "OC por Embalaje desde Nota de Venta N�$cod_nota_venta";
$cod_empresa = 1601;
$cod_suc_factura = 1601;
$cod_persona = 2139;
$porc_descto1 = 0;
$monto_dscto1 = 0;
$porc_descto2 = 0;
$monto_dscto1 = 0;
$total_neto = 0;
$monto_iva = 0;
$total_con_iva = 0;
$obs = 'NULL';
$motivo_anula = 'NULL';
$cod_user_anula = 'NULL';
$ingreso_usuario_dscto1 = 'M';
$ingreso_usuario_dscto2 = 'M';
$tipo_orden_compra = 'NOTA_VENTA';
$cod_doc = 'NULL';
$autorizada = 'S';
$autorizada_20_proc = 'NULL';
$nro_orden_compra_4d = 'NULL';
$autoriza_facturacion = 'NULL';
$fecha_solicita_autorizacion = 'NULL';
$autoriza_monto_compra = 'NULL';
$sub_total = 0;
$monto_dscto2 = 0;
$creada_Desde ='BTN_EMBALAJE';

$param	= "'$operacion'
					,NULL				
					,$cod_usuario 		
					,$cod_usuario_sol 									
					,$cod_moneda		
					,$cod_est_oc
					,$cod_nota_venta			
					,$cod_cta_cte
					,'$referencia'																						
					,$cod_empresa		
					,$cod_suc_factura	
					,$cod_persona			
					,$sub_total		
					,$porc_descto1		
					,$monto_dscto1		
					,$porc_descto2		
					,$monto_dscto2		
					,$total_neto		
					,$porc_iva		
					,$monto_iva		
					,$total_con_iva				
					,$obs
					,$motivo_anula
					,$cod_user_anula
					,$ingreso_usuario_dscto1
					,$ingreso_usuario_dscto2
					,$tipo_orden_compra
					,$cod_doc
					,$autorizada
					,$autorizada_20_proc
					,$nro_orden_compra_4d
					,NULL 
					,$autoriza_facturacion
					,$fecha_solicita_autorizacion
					,$autoriza_monto_compra
					,NULL
					,'$creada_Desde'";

$sp = 'spu_orden_compra';

if($db->EXECUTE_SP($sp, $param)){
	$cod_orden_compra = $db->GET_IDENTITY();
	$item_oc = genera_item_orden_compra($cod_orden_compra,$precio_neto);
	if($item_oc == 'Exito'){
		$param = "'RECALCULA',$cod_orden_compra";
		if($db->EXECUTE_SP($sp, $param))
			$resultado = $cod_orden_compra;
	}		
	else	
		$resultado = "fracaso";
}
else
	$resultado = "fracaso";

print $resultado;
?>