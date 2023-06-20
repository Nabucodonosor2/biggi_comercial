<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$nro_autoriza_tb = $_REQUEST['nro_autoriza_tb'];
$respuesta = '';
$sql_final = '';
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "select COD_TIPO_DOC_PAGO
              ,COD_ESTADO_INGRESO_PAGO
              ,convert(varchar(20), IP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
              ,IP.COD_INGRESO_PAGO
        FROM DOC_INGRESO_PAGO DIP
            ,INGRESO_PAGO IP
        WHERE NRO_DOC = $nro_autoriza_tb
        AND IP.COD_ESTADO_INGRESO_PAGO = 2
        AND IP.COD_INGRESO_PAGO = DIP.COD_INGRESO_PAGO
        ORDER BY IP.FECHA_INGRESO_PAGO DESC";

$result = $db->build_results($sql);
$row_count = $db->count_rows($result);

if($row_count == 0)
    $respuesta = "err1";
else if ($row_count == 1){
    if($result[0]['COD_TIPO_DOC_PAGO'] <> 5 && $result[0]['COD_TIPO_DOC_PAGO'] <> 6)
        $respuesta = "err2-1";
    else{
        if($result[0]['COD_ESTADO_INGRESO_PAGO'] <> 2)
            $respuesta = "err2-2";
        else{
            $cod_ingreso_pago = $result[0]['COD_INGRESO_PAGO'];

            $sql_final = "select INP.COD_INGRESO_PAGO
                                ,INP.NOM_TIPO_ORIGEN_PAGO
                                ,convert(varchar(20), INP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
                                ,E.COD_EMPRESA
                                ,case TDP.COD_TIPO_DOC_PAGO when 5 	then SUBSTRING('n/a',1,5) else SUBSTRING(CUOTAS_WEBPAY, 15, 18) end CUOTAS
                                ,dbo.f_get_cuotas_tb(NRO_DOC, INP.COD_INGRESO_PAGO, DINP.COD_TIPO_DOC_PAGO) CUOTAS_N
                                ,TDP.COD_TIPO_DOC_PAGO
                                ,SUBSTRING(TDP.NOM_TIPO_DOC_PAGO,8,8) NOM_TIPO_DOC_PAGO
                                ,year(INP.FECHA_INGRESO_PAGO) ANNO_INGRESO_PAGO
                                ,case TDP.COD_TIPO_DOC_PAGO when 5 then DINP.MONTO_DOC else DINP.MONTO_DOC / DINP.NRO_CUOTAS_TBK end MONTO_DOC
                            FROM INGRESO_PAGO INP, DOC_INGRESO_PAGO DINP,TIPO_DOC_PAGO TDP,EMPRESA E
                            WHERE DINP.NRO_DOC = $nro_autoriza_tb
                            AND INP.COD_ESTADO_INGRESO_PAGO = 2 ";

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
                            and COD_INGRESO_PAGO = $cod_ingreso_pago
                            and COD_ESTADO_ENVIO = 2
                            and COD_TIPO_DOC_PAGO = 5
                            and ET.COD_ENVIO_SOFTLAND = ES.COD_ENVIO_SOFTLAND";
                    $result_valida = $db->build_results($sql);
                    $row_count = $db->count_rows($result_valida);

                    if($row_count > 0)
                        $respuesta = "err1"; //"err3-2" => se reemplaza por el primer error por nueva definición
                    
                    $sql_final .= "and FECHA_INGRESO_PAGO >= dbo.to_date(dbo.f_get_parametro(76))
                                    and TDP.COD_TIPO_DOC_PAGO = 5 ";
                }

            }else{//Tipo 6 (CREDITO)
                $sql = "SET LANGUAGE Spanish;
                        select CASE WHEN DATEDIFF(MONTH, '".$result[0]['FECHA_INGRESO_PAGO']."', getdate()) <= 
                            CAST(dbo.f_get_parametro(77) as NUMERIC) THEN 'PERMITIDO' ELSE 'NO PERMITIDO' END PERMISO_CREDITO";
                $result_valida = $db->build_results($sql);
        
                if($result_valida[0]['PERMISO_CREDITO'] == 'NO PERMITIDO')
                    $respuesta = "err4-1";
                else{
                    $sql_uno = "select NRO_CUOTAS_TBK CUOTAS_MAX
                                FROM DOC_INGRESO_PAGO
                                WHERE NRO_DOC = $nro_autoriza_tb
                                AND COD_INGRESO_PAGO = $cod_ingreso_pago
                                AND COD_TIPO_DOC_PAGO = 6";
                    $result_uno = $db->build_results($sql_uno);

                    $sql_dos = "select COUNT(*) + 1 CUOTA_ACTUAL
                                FROM ENVIO_TRANSBANK ET
                                    ,ENVIO_SOFTLAND ES
                                WHERE COD_INGRESO_PAGO = $cod_ingreso_pago
                                AND NRO_AUTORIZA_TB = $nro_autoriza_tb
                                AND COD_TIPO_DOC_PAGO = 6
                                AND ES.COD_ESTADO_ENVIO = 2
                                AND ET.COD_ENVIO_SOFTLAND = ES.COD_ENVIO_SOFTLAND";
                    $result_dos = $db->build_results($sql_dos);

                    if($result_uno[0]['CUOTAS_MAX'] < $result_dos[0]['CUOTA_ACTUAL'])
                        $respuesta = "err1"; //"err4-2" => se reemplaza por el primer error por nueva definición

                    $sql_final .= "and DATEDIFF(MONTH, FECHA_INGRESO_PAGO, GETDATE()) <= CAST(dbo.f_get_parametro(77) as NUMERIC)
                                    and TDP.COD_TIPO_DOC_PAGO = 6 ";
                }
            }
        }
    }

    if($respuesta == ''){
        $sql_final  .= "and DINP.COD_INGRESO_PAGO = INP.COD_INGRESO_PAGO
                        AND DINP.COD_TIPO_DOC_PAGO = TDP.COD_TIPO_DOC_PAGO
                        AND INP.COD_EMPRESA = E.COD_EMPRESA
                        order by INP.FECHA_INGRESO_PAGO desc";
    
        $result_final = $db->build_results($sql_final);                
    
        $respuesta =  $result_final[0]['COD_INGRESO_PAGO']."|";
        $respuesta .=  $result_final[0]['NOM_TIPO_ORIGEN_PAGO']."|";
        $respuesta .=  $result_final[0]['FECHA_INGRESO_PAGO']."|";
        $respuesta .=  $result_final[0]['NOM_TIPO_DOC_PAGO']."|";
        $respuesta .=  $result_final[0]['CUOTAS']."|";
        $respuesta .=  $result_final[0]['CUOTAS_N']."|";
        $respuesta .=  $result_final[0]['COD_TIPO_DOC_PAGO']."|";
        $respuesta .=  $result_final[0]['ANNO_INGRESO_PAGO']."|";
        $respuesta .=  $result_final[0]['MONTO_DOC']."|";
        $respuesta .=  $result_final[0]['COD_EMPRESA'];
    }

}else{
    /*se verifica si de igual manera pasa por las validaciones como si fuera 1 registro pero por cada coincidencia
    que haya encontrado*/

    $sql = "SET LANGUAGE Spanish;
            select IP.COD_INGRESO_PAGO
                ,IP.NOM_TIPO_ORIGEN_PAGO
                ,convert(varchar(20), IP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
                ,E.COD_EMPRESA
                ,case TDP.COD_TIPO_DOC_PAGO when 5 	then SUBSTRING('n/a',1,5) else SUBSTRING(CUOTAS_WEBPAY, 15, 18) end CUOTAS
                ,dbo.f_get_cuotas_tb(NRO_DOC, IP.COD_INGRESO_PAGO, DIP.COD_TIPO_DOC_PAGO) CUOTAS_N
                ,TDP.COD_TIPO_DOC_PAGO
                ,SUBSTRING(TDP.NOM_TIPO_DOC_PAGO,8,8) NOM_TIPO_DOC_PAGO
                ,year(IP.FECHA_INGRESO_PAGO) ANNO_INGRESO_PAGO
                ,case TDP.COD_TIPO_DOC_PAGO when 5 then DIP.MONTO_DOC else DIP.MONTO_DOC / DIP.NRO_CUOTAS_TBK end MONTO_DOC
            FROM DOC_INGRESO_PAGO DIP
                ,INGRESO_PAGO IP
                ,TIPO_DOC_PAGO TDP
                ,EMPRESA E
            WHERE NRO_DOC = $nro_autoriza_tb
            AND (select COUNT(*)
                from ENVIO_TRANSBANK ET
                    ,ENVIO_SOFTLAND ES
                where NRO_AUTORIZA_TB = DIP.NRO_DOC
                and COD_INGRESO_PAGO = IP.COD_INGRESO_PAGO
                and COD_ESTADO_ENVIO = 2
                and COD_TIPO_DOC_PAGO = 5
                and ET.COD_ENVIO_SOFTLAND = ES.COD_ENVIO_SOFTLAND) = 0
            AND IP.COD_ESTADO_INGRESO_PAGO = 2
            AND DIP.COD_TIPO_DOC_PAGO = 5
            AND FECHA_INGRESO_PAGO >= dbo.to_date(dbo.f_get_parametro(76))
            AND IP.COD_INGRESO_PAGO = DIP.COD_INGRESO_PAGO
            AND E.COD_EMPRESA = IP.COD_EMPRESA
            AND TDP.COD_TIPO_DOC_PAGO = DIP.COD_TIPO_DOC_PAGO
            UNION
            select IP.COD_INGRESO_PAGO
                ,IP.NOM_TIPO_ORIGEN_PAGO
                ,convert(varchar(20), IP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
                ,E.COD_EMPRESA
                ,case TDP.COD_TIPO_DOC_PAGO when 5 	then SUBSTRING('n/a',1,5) else SUBSTRING(CUOTAS_WEBPAY, 15, 18) end CUOTAS
                ,dbo.f_get_cuotas_tb(NRO_DOC, IP.COD_INGRESO_PAGO, DIP.COD_TIPO_DOC_PAGO) CUOTAS_N
                ,TDP.COD_TIPO_DOC_PAGO
                ,SUBSTRING(TDP.NOM_TIPO_DOC_PAGO,8,8) NOM_TIPO_DOC_PAGO
                ,year(IP.FECHA_INGRESO_PAGO) ANNO_INGRESO_PAGO
                ,case TDP.COD_TIPO_DOC_PAGO when 5 then DIP.MONTO_DOC else DIP.MONTO_DOC / DIP.NRO_CUOTAS_TBK end MONTO_DOC
            FROM DOC_INGRESO_PAGO DIP
                ,INGRESO_PAGO IP
                ,TIPO_DOC_PAGO TDP
                ,EMPRESA E
            WHERE NRO_DOC = $nro_autoriza_tb
            AND IP.COD_ESTADO_INGRESO_PAGO = 2
            AND DIP.COD_TIPO_DOC_PAGO = 6
            AND NRO_CUOTAS_TBK >= (SELECT COUNT(*)+1
                                    FROM ENVIO_TRANSBANK ET
                                        ,ENVIO_SOFTLAND ES
                                    WHERE COD_INGRESO_PAGO = IP.COD_INGRESO_PAGO
                                    AND NRO_AUTORIZA_TB = DIP.NRO_DOC
                                    AND COD_TIPO_DOC_PAGO = 6
                                    AND ES.COD_ESTADO_ENVIO = 2
                                    AND ET.COD_ENVIO_SOFTLAND = ES.COD_ENVIO_SOFTLAND)
            AND IP.COD_INGRESO_PAGO = DIP.COD_INGRESO_PAGO
            AND DATEDIFF(MONTH, FECHA_INGRESO_PAGO, GETDATE()) <= CAST(dbo.f_get_parametro(77) as NUMERIC)
            AND E.COD_EMPRESA = IP.COD_EMPRESA
            AND TDP.COD_TIPO_DOC_PAGO = DIP.COD_TIPO_DOC_PAGO";

    $result = $db->build_results($sql);
    $row_count = $db->count_rows($result);
    
    if($row_count == 0)
        $respuesta = "err1";
    else if($row_count == 1){
        $respuesta =  $result_final[0]['COD_INGRESO_PAGO']."|";
        $respuesta .=  $result_final[0]['NOM_TIPO_ORIGEN_PAGO']."|";
        $respuesta .=  $result_final[0]['FECHA_INGRESO_PAGO']."|";
        $respuesta .=  $result_final[0]['NOM_TIPO_DOC_PAGO']."|";
        $respuesta .=  $result_final[0]['CUOTAS']."|";
        $respuesta .=  $result_final[0]['CUOTAS_N']."|";
        $respuesta .=  $result_final[0]['COD_TIPO_DOC_PAGO']."|";
        $respuesta .=  $result_final[0]['ANNO_INGRESO_PAGO']."|";
        $respuesta .=  $result_final[0]['MONTO_DOC']."|";
        $respuesta .=  $result_final[0]['COD_EMPRESA'];
    }else
        $respuesta = "2registros";
}

print urlencode($respuesta);
?>