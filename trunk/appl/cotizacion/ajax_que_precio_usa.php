<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$regreso = $_REQUEST['regreso'];
$seleccion = explode("|", $regreso);
$cod_cotizacion = $seleccion[1];
$num_dif = 0;
$respuesta = "";
//$tipo_selecction = $seleccion[0];
if ($seleccion[0]=="SOLICITUD") { 	
	$sql="SELECT	ISC.COD_SOLICITUD_COTIZACION 
					, ISC.PRECIO
					,P.PRECIO_VENTA_PUBLICO
			FROM	  ITEM_SOLICITUD_COTIZACION ISC
					, SOLICITUD_COTIZACION SC
					, CONTACTO C
					, PRODUCTO P
					,CONTACTO_PERSONA CP
					,LLAMADO LL
			WHERE	  ISC.COD_SOLICITUD_COTIZACION = $cod_cotizacion
			AND		  ISC.COD_SOLICITUD_COTIZACION = SC.COD_SOLICITUD_COTIZACION
			AND		  C.COD_CONTACTO = SC.COD_CONTACTO
			AND		  P.COD_PRODUCTO = ISC.COD_PRODUCTO
			AND       C.COD_CONTACTO = CP.COD_CONTACTO
			AND       SC.COD_LLAMADO = LL.COD_LLAMADO
			ORDER BY  COD_ITEM_SOLICITUD_COTIZACION";
		 
    $result = $db->build_results($sql);
    for ($i=0; $i<count($result); $i++) {
		$precio = $result[$i]['PRECIO'];
        $precio_bd	= $result[$i]['PRECIO_VENTA_PUBLICO'];
		if($precio_bd != $precio)
			$num_dif++;
	}
	if($num_dif > 0)
		$respuesta = "$cod_cotizacion|SOLICITUD|SI";
	else											  
		$respuesta = "$cod_cotizacion|SOLICITUD|NO";
}
elseif($seleccion[0]=="COTIZACION"){
	$sql = "SELECT		COD_PRODUCTO R_COD_PRODUCTO,
							NOM_PRODUCTO R_NOM_PRODUCTO,
							SUM(CANTIDAD) R_CANTIDAD,
							PRECIO R_PRECIO,
							SUM(CANTIDAD * PRECIO) R_TOTAL
				FROM		ITEM_COTIZACION
				WHERE		COD_COTIZACION = $cod_cotizacion
				AND			COD_PRODUCTO not in ('T', 'TE', 'F', 'I', 'E')
				GROUP BY COD_PRODUCTO, NOM_PRODUCTO, PRECIO
				UNION
				SELECT		COD_PRODUCTO R_COD_PRODUCTO,
							NOM_PRODUCTO R_NOM_PRODUCTO,
							SUM(CANTIDAD) R_CANTIDAD,
							PRECIO R_PRECIO,
							SUM(CANTIDAD * PRECIO) R_TOTAL
				FROM		ITEM_COTIZACION
				WHERE		COD_COTIZACION = $cod_cotizacion
				AND			COD_PRODUCTO in ('TE', 'F', 'I', 'E')
				GROUP BY COD_PRODUCTO, NOM_PRODUCTO, PRECIO";
	$result_i = $db->build_results($sql);
	for ($i=0 ; $i <count($result_i); $i++) {
		$cod_producto = $result_i[$i]['R_COD_PRODUCTO'];
		$precio_cot = $result_i[$i]['R_PRECIO'];
		$result	= $db->build_results("select PRECIO_VENTA_PUBLICO , PRECIO_LIBRE 
											  from 		PRODUCTO 
											  where 	COD_PRODUCTO = '$cod_producto'");
		if ($result[0]['PRECIO_LIBRE']=='S') 
				continue;											  
		$precio_bd	= $result[0]['PRECIO_VENTA_PUBLICO'];
		if($precio_bd != $precio_cot ){
			$num_dif++;
		}
	}
	
	if($num_dif > 0)	
		$respuesta = "$cod_cotizacion|COTIZACION|SI";
	else											  
		$respuesta = "$cod_cotizacion|COTIZACION|NO";
}
print $respuesta;	

?>