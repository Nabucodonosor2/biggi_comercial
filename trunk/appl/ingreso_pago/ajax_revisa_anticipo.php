<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_empresa = $_REQUEST["cod_empresa"];
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$K_ESTADO_INGRESO_PAGO_ANULADA = 3;
$K_ESTADO_INGRESO_PAGO_CONFIR  = 2;

$sql = "SELECT COD_INGRESO_PAGO
        FROM 	INGRESO_PAGO
        WHERE 	COD_EMPRESA = $cod_empresa
        AND 	COD_ESTADO_INGRESO_PAGO = $K_ESTADO_INGRESO_PAGO_CONFIR
        AND		OTRO_ANTICIPO > 0
        AND COD_INGRESO_PAGO not in  (select 	NRO_DOC  
                                        from 	DOC_INGRESO_PAGO DIP, INGRESO_PAGO IP 
                                        where 	NRO_DOC is not null 
                                        AND 	COD_ESTADO_INGRESO_PAGO <> $K_ESTADO_INGRESO_PAGO_ANULADA 
                                        AND 	DIP.COD_INGRESO_PAGO = IP.COD_INGRESO_PAGO
                                        AND		COD_TIPO_DOC_PAGO = 9)";

$db->build_results($sql);
$row_count = $db->count_rows();	

if($row_count > 0)
    print 'SI';
else
    print 'NO';
?>