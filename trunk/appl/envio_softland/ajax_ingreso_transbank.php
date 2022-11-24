<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$nro_autoriza_tb = $_REQUEST['nro_autoriza_tb'];
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "select INP.COD_INGRESO_PAGO
		,INP.NOM_TIPO_ORIGEN_PAGO
		,convert(varchar(20), INP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
		,dbo.f_get_datos_tb($nro_autoriza_tb) COD_NOTA_VENTA
    ,SUBSTRING(E.NOM_EMPRESA, 1, 41) RAZON_SOCIAL
    ,E.NOM_EMPRESA RAZON_SOCIAL_I
    ,E.COD_EMPRESA
		,case TDP.COD_TIPO_DOC_PAGO when 5 	then SUBSTRING('n/a',1,5) else SUBSTRING(CUOTAS_WEBPAY, 15, 18) end CUOTAS
		,dbo.f_get_cuotas_tb($nro_autoriza_tb) CUOTAS_N
		,TDP.COD_TIPO_DOC_PAGO
    ,SUBSTRING(TDP.NOM_TIPO_DOC_PAGO,8,8) NOM_TIPO_DOC_PAGO
    ,year (INP.FECHA_INGRESO_PAGO) ANNO_INGRESO_PAGO
    ,case TDP.COD_TIPO_DOC_PAGO when 5 	then DINP.MONTO_DOC else DINP.MONTO_DOC / DINP.NRO_CUOTAS_TBK end MONTO_DOC
from  INGRESO_PAGO INP, DOC_INGRESO_PAGO DINP,TIPO_DOC_PAGO TDP,EMPRESA E
where DINP.NRO_DOC = $nro_autoriza_tb
and DINP.PERMITE_ENVIO_SOFT_TBK <> 'N'
and DINP.COD_INGRESO_PAGO = INP.COD_INGRESO_PAGO
and DINP.COD_TIPO_DOC_PAGO = TDP.COD_TIPO_DOC_PAGO
and INP.COD_EMPRESA = E.COD_EMPRESA
and DINP.COD_TIPO_DOC_PAGO in (5,6)
and INP.COD_ESTADO_INGRESO_PAGO = 2
and DINP.COD_INGRESO_PAGO >= 46425";
$result_1 = $db->build_results($sql);

if ($result_1[0]['COD_TIPO_DOC_PAGO'] == 5){
  $sql = "select INP.COD_INGRESO_PAGO
  		,INP.NOM_TIPO_ORIGEN_PAGO
  		,convert(varchar(20), INP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
  		,dbo.f_get_datos_tb($nro_autoriza_tb) COD_NOTA_VENTA
      ,SUBSTRING(E.NOM_EMPRESA, 1, 41) RAZON_SOCIAL
      ,E.NOM_EMPRESA RAZON_SOCIAL_I
      ,E.COD_EMPRESA
  		,case TDP.COD_TIPO_DOC_PAGO when 5 	then SUBSTRING('n/a',1,5) else SUBSTRING(CUOTAS_WEBPAY, 15, 18) end CUOTAS
  		,dbo.f_get_cuotas_tb($nro_autoriza_tb) CUOTAS_N
  		,TDP.COD_TIPO_DOC_PAGO
      ,SUBSTRING(TDP.NOM_TIPO_DOC_PAGO,8,8) NOM_TIPO_DOC_PAGO
      ,year (INP.FECHA_INGRESO_PAGO) ANNO_INGRESO_PAGO
      ,case TDP.COD_TIPO_DOC_PAGO when 5 	then DINP.MONTO_DOC else DINP.MONTO_DOC / DINP.NRO_CUOTAS_TBK end MONTO_DOC
      ,DINP.MONTO_DOC
  from  INGRESO_PAGO INP, DOC_INGRESO_PAGO DINP,TIPO_DOC_PAGO TDP,EMPRESA E
  where DINP.NRO_DOC = $nro_autoriza_tb
  and DINP.PERMITE_ENVIO_SOFT_TBK <> 'N'
  and DINP.COD_INGRESO_PAGO = INP.COD_INGRESO_PAGO
  and DINP.COD_TIPO_DOC_PAGO = TDP.COD_TIPO_DOC_PAGO
  and INP.COD_EMPRESA = E.COD_EMPRESA
  and DINP.COD_TIPO_DOC_PAGO in (5,6)
  and INP.COD_ESTADO_INGRESO_PAGO = 2
  and DINP.COD_INGRESO_PAGO >= 46425
  and INP.FECHA_INGRESO_PAGO > Convert(datetime,'2022-01-01 23:59:00.000')";
  $result = $db->build_results($sql);

}else if($result_1[0]['COD_TIPO_DOC_PAGO'] == 6){
  $sql = "select INP.COD_INGRESO_PAGO
  		,INP.NOM_TIPO_ORIGEN_PAGO
  		,convert(varchar(20), INP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
  		,dbo.f_get_datos_tb($nro_autoriza_tb) COD_NOTA_VENTA
      ,SUBSTRING(E.NOM_EMPRESA, 1, 41) RAZON_SOCIAL
      ,E.NOM_EMPRESA RAZON_SOCIAL_I
      ,E.COD_EMPRESA
  		,case TDP.COD_TIPO_DOC_PAGO when 5 	then SUBSTRING('n/a',1,5) else SUBSTRING(CUOTAS_WEBPAY, 15, 18) end CUOTAS
  		,dbo.f_get_cuotas_tb($nro_autoriza_tb) CUOTAS_N
  		,TDP.COD_TIPO_DOC_PAGO
      ,SUBSTRING(TDP.NOM_TIPO_DOC_PAGO,8,8) NOM_TIPO_DOC_PAGO
      ,year (INP.FECHA_INGRESO_PAGO) ANNO_INGRESO_PAGO
      ,case TDP.COD_TIPO_DOC_PAGO when 5 	then DINP.MONTO_DOC else DINP.MONTO_DOC / DINP.NRO_CUOTAS_TBK end MONTO_DOC
      ,DINP.MONTO_DOC MONTO_DOC_T
  from  INGRESO_PAGO INP, DOC_INGRESO_PAGO DINP,TIPO_DOC_PAGO TDP,EMPRESA E
  where DINP.NRO_DOC = $nro_autoriza_tb
  and DINP.PERMITE_ENVIO_SOFT_TBK <> 'N'
  and DINP.COD_INGRESO_PAGO = INP.COD_INGRESO_PAGO
  and DINP.COD_TIPO_DOC_PAGO = TDP.COD_TIPO_DOC_PAGO
  and INP.COD_EMPRESA = E.COD_EMPRESA
  and DINP.COD_TIPO_DOC_PAGO in (5,6)
  and INP.COD_ESTADO_INGRESO_PAGO = 2
  and DINP.COD_INGRESO_PAGO >= 46425
  and INP.FECHA_INGRESO_PAGO > Convert(datetime,'2021-01-01 23:59:00.000')";
  $result = $db->build_results($sql);

}else{
  $sql = "select INP.COD_INGRESO_PAGO
  		,INP.NOM_TIPO_ORIGEN_PAGO
  		,convert(varchar(20), INP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
  		,dbo.f_get_datos_tb($nro_autoriza_tb) COD_NOTA_VENTA
      ,SUBSTRING(E.NOM_EMPRESA, 1, 41) RAZON_SOCIAL
      ,E.NOM_EMPRESA RAZON_SOCIAL_I
      ,E.COD_EMPRESA
  		,case TDP.COD_TIPO_DOC_PAGO when 5 	then SUBSTRING('n/a',1,5) else SUBSTRING(CUOTAS_WEBPAY, 15, 18) end CUOTAS
  		,dbo.f_get_cuotas_tb($nro_autoriza_tb) CUOTAS_N
  		,TDP.COD_TIPO_DOC_PAGO
      ,SUBSTRING(TDP.NOM_TIPO_DOC_PAGO,8,8) NOM_TIPO_DOC_PAGO
      ,year (INP.FECHA_INGRESO_PAGO) ANNO_INGRESO_PAGO
      ,case TDP.COD_TIPO_DOC_PAGO when 5 	then DINP.MONTO_DOC else DINP.MONTO_DOC / DINP.NRO_CUOTAS_TBK end MONTO_DOC
      ,DINP.MONTO_DOC MONTO_DOC_T
  from  INGRESO_PAGO INP, DOC_INGRESO_PAGO DINP,TIPO_DOC_PAGO TDP,EMPRESA E
  where DINP.NRO_DOC = $nro_autoriza_tb
  and DINP.PERMITE_ENVIO_SOFT_TBK <> 'N'
  and DINP.COD_INGRESO_PAGO = INP.COD_INGRESO_PAGO
  and DINP.COD_TIPO_DOC_PAGO = TDP.COD_TIPO_DOC_PAGO
  and INP.COD_EMPRESA = E.COD_EMPRESA
  and DINP.COD_TIPO_DOC_PAGO in (5,6)
  and INP.COD_ESTADO_INGRESO_PAGO = 2
  and DINP.COD_INGRESO_PAGO >= 46425";
  $result = $db->build_results($sql);
}

$respuesta = '';
$row_count = $db->count_rows($result);

if ($row_count == 0){
	$respuesta = "X|";
}
$respuesta =  $result[0]['COD_INGRESO_PAGO']."|";
$respuesta =  $respuesta.$result[0]['NOM_TIPO_ORIGEN_PAGO']."|";
$respuesta =  $respuesta.$result[0]['FECHA_INGRESO_PAGO']."|";
$respuesta =  $respuesta.$result[0]['COD_NOTA_VENTA']."|";
$respuesta =  $respuesta.$result[0]['RAZON_SOCIAL']."|";
$respuesta =  $respuesta.$result[0]['NOM_TIPO_DOC_PAGO']."|";
$respuesta =  $respuesta.$result[0]['CUOTAS']."|";
$respuesta =  $respuesta.$result[0]['CUOTAS_N']."|";
$respuesta =  $respuesta.$result[0]['COD_TIPO_DOC_PAGO']."|";
$respuesta =  $respuesta.$result[0]['ANNO_INGRESO_PAGO']."|";
$respuesta =  $respuesta.$result[0]['MONTO_DOC']."|";
$respuesta =  $respuesta.$result[0]['RAZON_SOCIAL_I']."|";
$respuesta =  $respuesta.$result[0]['COD_EMPRESA']."|";
$respuesta =  $respuesta.$result[0]['MONTO_DOC_T'];

print urlencode($respuesta);
?>
