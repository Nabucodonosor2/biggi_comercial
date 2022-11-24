<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_cotizacion = $_REQUEST['cod_cotizacion'];

$temp = new Template_appl('que_precio_usa.htm');	

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "select  I.ITEM,
								I.COD_PRODUCTO,
								I.NOM_PRODUCTO,
								I.PRECIO,
								P.PRECIO_VENTA_PUBLICO
			  FROM ITEM_COTIZACION I, PRODUCTO P
			  WHERE I.COD_COTIZACION = $cod_cotizacion AND
			  			P.COD_PRODUCTO = I.COD_PRODUCTO AND
			  			I.PRECIO <> P.PRECIO_VENTA_PUBLICO AND
			  			P.PRECIO_LIBRE = 'N'";
$result = $db->build_results($sql);
for($i=0; $i<count($result); $i++){						
	$temp->gotoNext('PRODUCTO');
	if ($i % 2 == 0)
		$temp->setVar("PRODUCTO.DW_TR_CSS", datawindow::css_claro);
	else
		$temp->setVar("PRODUCTO.DW_TR_CSS", datawindow::css_oscuro);
	$temp->setVar("PRODUCTO.ITEM", $result[$i]['ITEM']);
	$temp->setVar("PRODUCTO.COD_PRODUCTO", $result[$i]['COD_PRODUCTO']);
	$temp->setVar("PRODUCTO.NOM_PRODUCTO", $result[$i]['NOM_PRODUCTO']);
	$temp->setVar("PRODUCTO.PRECIO", number_format($result[$i]['PRECIO'], 0, ',', '.'));
	$temp->setVar("PRODUCTO.PRECIO_VENTA_PUBLICO", number_format($result[$i]['PRECIO_VENTA_PUBLICO'], 0, ',', '.'));
}
	
// validez de la oferta
$sql_parametro="SELECT VALOR 
				FROM PARAMETRO
				WHERE COD_PARAMETRO = 7";
$result_parametro = $db->build_results($sql_parametro);

//$sql="SELECT ".$result_parametro[0]['VALOR']." + DATEDIFF(DAY, GETDATE(), FECHA_COTIZACION) VALIDEZ
//	  FROM COTIZACION
//	  WHERE COD_COTIZACION = $cod_cotizacion";
//$result = $db->build_results($sql);

$sql = "SELECT case when ".$result_parametro[0]['VALOR']." <> VALIDEZ_OFERTA then 
	VALIDEZ_OFERTA + DATEDIFF(DAY, GETDATE(), FECHA_COTIZACION)
	else 
	10 + DATEDIFF(DAY, GETDATE(), FECHA_COTIZACION) 
	end VALIDEZ
	  FROM COTIZACION
	  WHERE COD_COTIZACION =$cod_cotizacion";
	  $result = $db->build_results($sql);


if($result[0]['VALIDEZ'] >= 0)
	$validez_oferta = true;
else
	$validez_oferta = false;

$cod_usuario = session::get("COD_USUARIO");
$priv_cambio_precio = w_base::tiene_privilegio_opcion_usuario('990505', $cod_usuario);

if($priv_cambio_precio){
	$temp->setVar("W_AUT_PRECIO_COT",'<input type="button" value="Usar precios desde Cotizaci�n" onclick="setWindowReturnValue(0); closeMe();" class="button">');
	$temp->setVar("W_AUT_PRECIO_ACTUAL",'<input type="button" value="Usar precios actuales." onclick="okMe();" class="button">');
}else if($validez_oferta){
	$temp->setVar("W_AUT_PRECIO_COT",'<input type="button" value="Usar precios desde Cotizaci�n" onclick="setWindowReturnValue(0); closeMe();" class="button">');
	$temp->setVar("W_AUT_PRECIO_ACTUAL",'<input type="button" value="Usar precios actuales." onclick="okMe();" class="button">');
}else{
	echo '<script type="text/javascript">alert(\'Atenci�n: la Cotizaci�n N� '.$cod_cotizacion.', esta fuera de la validez de oferta, por lo tanto, la Nota de Venta se crear� utilizando precios actualizados.\n\nSi desea respetar los precios de la cotizaci�n, favor solicitar autorizaci�n en Administraci�n BIGGI\');</script>';
	$temp->setVar("W_AUT_PRECIO_ACTUAL",'<input type="button" value="Usar precios actuales." onclick="okMe();" class="button">');
}
	//nueva variable para idicar si esta fuera de la validez de oferta
if ($validez_oferta == false)
	$temp->setVar("W_AUT_LABEL_OFERTA", 'Cotizaci�n fuera de validez por '.($result[0]['VALIDEZ'] * -1).' d�as.');

print $temp->toString();
?>