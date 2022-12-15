<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$nro_autoriza_tb = $_REQUEST['nro_autoriza_tb'];
$respuesta = '';
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "select INP.COD_INGRESO_PAGO
              ,INP.NOM_TIPO_ORIGEN_PAGO
              ,convert(varchar(20), INP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
              ,dbo.f_get_datos_tb(NRO_DOC) COD_NOTA_VENTA
              ,SUBSTRING(E.NOM_EMPRESA, 1, 41) RAZON_SOCIAL
              ,E.NOM_EMPRESA RAZON_SOCIAL_I
              ,E.COD_EMPRESA
              ,case TDP.COD_TIPO_DOC_PAGO when 5 	then SUBSTRING('n/a',1,5) else SUBSTRING(CUOTAS_WEBPAY, 15, 18) end CUOTAS
              ,dbo.f_get_cuotas_tb(NRO_DOC) CUOTAS_N
              ,TDP.COD_TIPO_DOC_PAGO
              ,SUBSTRING(TDP.NOM_TIPO_DOC_PAGO,8,8) NOM_TIPO_DOC_PAGO
              ,year(INP.FECHA_INGRESO_PAGO) ANNO_INGRESO_PAGO
              ,case TDP.COD_TIPO_DOC_PAGO when 5 	then DINP.MONTO_DOC else DINP.MONTO_DOC / DINP.NRO_CUOTAS_TBK end MONTO_DOC
              ,INP.COD_ESTADO_INGRESO_PAGO
        FROM INGRESO_PAGO INP, DOC_INGRESO_PAGO DINP,TIPO_DOC_PAGO TDP,EMPRESA E
        WHERE DINP.NRO_DOC = $nro_autoriza_tb			
        AND DINP.COD_INGRESO_PAGO = INP.COD_INGRESO_PAGO
        AND DINP.COD_TIPO_DOC_PAGO = TDP.COD_TIPO_DOC_PAGO
        AND INP.COD_EMPRESA = E.COD_EMPRESA";

$result = $db->build_results($sql);
$row_count = $db->count_rows($result);

if ($row_count == 0)
	$respuesta = "err1";
else{
  if($result[0]['COD_TIPO_DOC_PAGO'] <> 5 && $result[0]['COD_TIPO_DOC_PAGO'] <> 6)
    $respuesta = "err2-1";
  else{
    if($result[0]['COD_ESTADO_INGRESO_PAGO'] <> 2)
      $respuesta = "err2-2";
    else{
      if($result[0]['COD_TIPO_DOC_PAGO'] == 5){ //Tipo 5 (DEBITO)

          $sql = "select CASE WHEN dbo.to_date('".$result[0]['FECHA_INGRESO_PAGO']."') >=
                        dbo.to_date(dbo.f_get_parametro(76)) THEN 'PERMITIDO' ELSE 'NO PERMITIDO' END PERMISO_DEBITO";
        $result_valida = $db->build_results($sql);
  
        if($result_valida[0]['PERMISO_DEBITO'] == 'NO PERMITIDO')
          $respuesta = "err3-1";
        else{
          $sql = "select COD_ESTADO_ENVIO
                  from ENVIO_TRANSBANK ET
                    ,ENVIO_SOFTLAND ES
                  where NRO_AUTORIZA_TB = $nro_autoriza_tb
                  and COD_ESTADO_ENVIO in (1, 2)
                  and ET.COD_ENVIO_SOFTLAND = ES.COD_ENVIO_SOFTLAND";
          $result_valida = $db->build_results($sql);
          $row_count = $db->count_rows($result_valida);
          
          if($row_count > 0)
            $respuesta = "err3-2";
        }  
      }else{//Tipo 6 (CREDITO)
        $sql = "select CASE WHEN DATEDIFF(MONTH, '".$result[0]['FECHA_INGRESO_PAGO']."', getdate()) <= 
                    CAST(dbo.f_get_parametro(77) as NUMERIC) THEN 'PERMITIDO' ELSE 'NO PERMITIDO' END PERMISO_CREDITO";
        $result_valida = $db->build_results($sql);
  
        if($result_valida[0]['PERMISO_CREDITO'] == 'NO PERMITIDO')
          $respuesta = "err4-1";
        else{
          $sql = "select LTRIM(REPLACE(CUOTAS, ': ', '')) CUOTAS_MAX
                      ,COUNT(*) CUOTA_ACTUAL
                  from ENVIO_TRANSBANK ET
                      ,ENVIO_SOFTLAND ES
                  where NRO_AUTORIZA_TB = $nro_autoriza_tb
                  and COD_ESTADO_ENVIO in (1, 2)
                  and ET.COD_ENVIO_SOFTLAND = ES.COD_ENVIO_SOFTLAND
                  group by LTRIM(REPLACE(CUOTAS, ': ', ''))";
          $result_valida = $db->build_results($sql);
          $row_count = $db->count_rows($result_valida);
          
          if($row_count <> 0){
            if($result_valida[0]['CUOTAS_MAX'] == $result_valida[0]['CUOTA_ACTUAL'])
              $respuesta = "err4-2";
          }
        }
      }
    }
  }
}

if($respuesta == ''){
  $respuesta =  $result[0]['COD_INGRESO_PAGO']."|";
  $respuesta .=  $result[0]['NOM_TIPO_ORIGEN_PAGO']."|";
  $respuesta .=  $result[0]['FECHA_INGRESO_PAGO']."|";
  $respuesta .=  $result[0]['COD_NOTA_VENTA']."|";
  $respuesta .=  $result[0]['RAZON_SOCIAL']."|";
  $respuesta .=  $result[0]['NOM_TIPO_DOC_PAGO']."|";
  $respuesta .=  $result[0]['CUOTAS']."|";
  $respuesta .=  $result[0]['CUOTAS_N']."|";
  $respuesta .=  $result[0]['COD_TIPO_DOC_PAGO']."|";
  $respuesta .=  $result[0]['ANNO_INGRESO_PAGO']."|";
  $respuesta .=  $result[0]['MONTO_DOC']."|";
  $respuesta .=  $result[0]['RAZON_SOCIAL_I']."|";
  $respuesta .=  $result[0]['COD_EMPRESA']."|";
  $respuesta .=  $result[0]['MONTO_DOC_T'];
}

print urlencode($respuesta);
?>
