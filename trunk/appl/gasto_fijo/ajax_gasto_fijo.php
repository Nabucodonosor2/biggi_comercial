<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$variable   = $_REQUEST['variable'];
$fx         = $_REQUEST['fx'];

if($fx == 'getCotizacion'){

    $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
    $nro_cotizacion = $variable;

    $sql = "SELECT (SELECT NOM_USUARIO FROM USUARIO WHERE COD_USUARIO = COD_USUARIO_VENDEDOR1) NOM_VENDEDOR
                ,REFERENCIA
            FROM COTIZACION
            WHERE COD_COTIZACION = $nro_cotizacion";
    
    $result = $db->build_results($sql);

    if($db->count_rows() > 0)
        print urlencode($result[0]['NOM_VENDEDOR'].'|'.$result[0]['REFERENCIA']);
    else
        print 'NULL';

}else if($fx == 'getNotaVenta'){

    $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
    $nro_nota_venta = $variable;

    $sql = "SELECT (SELECT NOM_USUARIO FROM USUARIO WHERE COD_USUARIO = COD_USUARIO_VENDEDOR1) NOM_VENDEDOR
                ,REFERENCIA
            FROM NOTA_VENTA
            WHERE COD_NOTA_VENTA = $nro_nota_venta";
    
    $result = $db->build_results($sql);

    if($db->count_rows() > 0)
        print urlencode($result[0]['NOM_VENDEDOR'].'|'.$result[0]['REFERENCIA']);
    else
        print 'NULL';

}


?>