<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_orden_compra = $_REQUEST['cod_orden_compra'];
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT COD_ESTADO_ORDEN_COMPRA
              ,DATEDIFF(MONTH, FECHA_ORDEN_COMPRA, GETDATE()) MES_DIFF
        FROM ORDEN_COMPRA
        WHERE COD_ORDEN_COMPRA = $cod_orden_compra";  
$result = $db->build_results($sql);

if(count($result) > 0){
    if($result[0]['MES_DIFF'] > 12){
        print '-1';
    }else{
        if($result[0]['COD_ESTADO_ORDEN_COMPRA'] == 1)//EMITIDA
            print '1';
        if($result[0]['COD_ESTADO_ORDEN_COMPRA'] == 2)//ANULADA
            print '2';
        if($result[0]['COD_ESTADO_ORDEN_COMPRA'] == 3)//CERRADA
            print '3';
        if($result[0]['COD_ESTADO_ORDEN_COMPRA'] == 4)//AUTORIZADA
            print '4';
    }
            
}else
    print '0';
?>