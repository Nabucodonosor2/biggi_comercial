<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$nro_autoriza_tb    = $_REQUEST['nro_autoriza_tb'];

$temp = new Template_appl('help_doc_transbank.htm');
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SET LANGUAGE Spanish;
        select IP.COD_INGRESO_PAGO
            ,IP.NOM_TIPO_ORIGEN_PAGO
            ,convert(varchar(20), IP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
            ,E.COD_EMPRESA
            ,'n/a' CUOTAS
            ,dbo.f_get_cuotas_tb(NRO_DOC, IP.COD_INGRESO_PAGO, DIP.COD_TIPO_DOC_PAGO) CUOTAS_N
            ,TDP.COD_TIPO_DOC_PAGO
            ,SUBSTRING(TDP.NOM_TIPO_DOC_PAGO,8,8) NOM_TIPO_DOC_PAGO
            ,year(IP.FECHA_INGRESO_PAGO) ANNO_INGRESO_PAGO
            ,DIP.MONTO_DOC MONTO_DOC
            ,TDP.NOM_TIPO_DOC_PAGO NOM_TIPO_DOC_PAGO_DOS
            ,DIP.NRO_CUOTAS_TBK
            ,NRO_DOC
            ,dbo.number_format(DIP.MONTO_DOC, 0, ',', '.') MONTO_DOC_NUM
        FROM DOC_INGRESO_PAGO DIP
            ,INGRESO_PAGO IP
            ,TIPO_DOC_PAGO TDP
            ,EMPRESA E
        WHERE NRO_DOC = $nro_autoriza_tb
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
            ,SUBSTRING(CUOTAS_WEBPAY, 15, 18) CUOTAS
            ,dbo.f_get_cuotas_tb(NRO_DOC, IP.COD_INGRESO_PAGO, DIP.COD_TIPO_DOC_PAGO) CUOTAS_N
            ,TDP.COD_TIPO_DOC_PAGO
            ,SUBSTRING(TDP.NOM_TIPO_DOC_PAGO,8,8) NOM_TIPO_DOC_PAGO
            ,year(IP.FECHA_INGRESO_PAGO) ANNO_INGRESO_PAGO
            ,DIP.MONTO_DOC / DIP.NRO_CUOTAS_TBK MONTO_DOC
            ,TDP.NOM_TIPO_DOC_PAGO NOM_TIPO_DOC_PAGO_DOS
            ,DIP.NRO_CUOTAS_TBK
            ,NRO_DOC
            ,dbo.number_format(DIP.MONTO_DOC / DIP.NRO_CUOTAS_TBK, 0, ',', '.') MONTO_DOC_NUM
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
$fields = $db->get_fields();
$count  = $db->count_rows();

for ($i=0 ; $i <$count; $i++){
    $respuesta =  $result[$i]['COD_INGRESO_PAGO']."|";
    $respuesta .=  $result[$i]['NOM_TIPO_ORIGEN_PAGO']."|";
    $respuesta .=  $result[$i]['FECHA_INGRESO_PAGO']."|";
    $respuesta .=  $result[$i]['NOM_TIPO_DOC_PAGO']."|";
    $respuesta .=  $result[$i]['CUOTAS']."|";
    $respuesta .=  $result[$i]['CUOTAS_N']."|";
    $respuesta .=  $result[$i]['COD_TIPO_DOC_PAGO']."|";
    $respuesta .=  $result[$i]['ANNO_INGRESO_PAGO']."|";
    $respuesta .=  $result[$i]['MONTO_DOC']."|";
    $respuesta .=  $result[$i]['COD_EMPRESA'];

    $returnValue = $respuesta;
    $temp->gotoNext("DOC_TRANSBANK");		

    if ($i % 2 == 0)
        $temp->setVar("DOC_TRANSBANK.DW_TR_CSS", datawindow::css_claro);
    else
        $temp->setVar("DOC_TRANSBANK.DW_TR_CSS", datawindow::css_oscuro);

    for($j=0; $j<count($fields); $j++) {
        if($j==0)
            $temp->setVar("DOC_TRANSBANK.".$fields[$j]->name, '<a href="#" onClick=" returnValue=\''.$returnValue.'\'; setWindowReturnValue(returnValue); window.close();">'.$result[$i][$fields[$j]->name].'</a>');              
        else
            $temp->setVar("DOC_TRANSBANK.".$fields[$j]->name, $result[$i][$fields[$j]->name]);			
    }
}

print $temp->toString();
?>