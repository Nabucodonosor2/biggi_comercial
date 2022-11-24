<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_bodega = $_REQUEST["cod_bodega"];


$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "select P.COD_PRODUCTO
			,P.NOM_PRODUCTO
			,dbo.f_bodega_stock(P.COD_PRODUCTO, $cod_bodega, getdate()) STOCK
	from PRODUCTO P 
	where substring(sistema_valido, 1, 1) = 'S'
     and dbo.f_bodega_stock(P.COD_PRODUCTO, $cod_bodega, getdate()) > 0
	 AND P.COD_PRODUCTO NOT LIKE 'TE[_]%'
    UNION
    SELECT I.COD_PRODUCTO
            ,I.NOM_PRODUCTO
            ,dbo.f_bodega_stock(I.COD_PRODUCTO, $cod_bodega, getdate())
    FROM ITEM_ENTRADA_BODEGA I
    WHERE dbo.f_bodega_stock(I.COD_PRODUCTO,$cod_bodega, getdate()) > 0
    AND I.COD_PRODUCTO LIKE 'TE[_]%'";

$result = $db->build_results($sql);	

print urlencode(json_encode($result));

?>