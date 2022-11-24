<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_traspaso = $_REQUEST["cod_traspaso"];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT COUNT(I.COD_ITEM_TRASPASO_BODEGA)CANTIDAD
        from ITEM_TRASPASO_BODEGA I,TRASPASO_BODEGA T  
        where T.COD_TRASPASO_BODEGA = $cod_traspaso      
        AND I.COD_TRASPASO_BODEGA = T.COD_TRASPASO_BODEGA
        and I.CT_TRASPASAR >  dbo.f_bodega_stock(I.COD_PRODUCTO, T.COD_BODEGA_ORIGEN,GETDATE())";

$result = $db->build_results($sql);	

print $result[0]['CANTIDAD'];

?>