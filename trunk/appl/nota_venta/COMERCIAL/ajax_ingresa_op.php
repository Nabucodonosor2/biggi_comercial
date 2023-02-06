<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$cod_nota_venta     = $_REQUEST["cod_nota_venta"];
$cod_usuario        = $_REQUEST["cod_usuario"]; 
$cod_empresa        = $_REQUEST["cod_empresa"]; 
$precio_anticipo    = $_REQUEST["precio_anticipo"];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql_param = "select dbo.f_get_parametro(1) PORC_IVA";
$result_param = $db->build_results($sql_param);
//Precios
$total_neto     = $precio_anticipo;
$porc_iva       = $result_param[0]['PORC_IVA'];
$monto_iva      = round($total_neto * ($porc_iva/100), 0);
$total_con_iva  = $total_neto + $monto_iva;

$sp = 'spu_orden_pago';

$param	= "'INSERT'
          ,NULL
          ,$cod_usuario
          ,$cod_nota_venta
          ,$cod_empresa
          ,1
          ,$total_neto
          ,$porc_iva
          ,$monto_iva
          ,$total_con_iva
          ,'S'"; 

if ($db->EXECUTE_SP($sp, $param)){
    $cod_orden_pago = $db->GET_IDENTITY();
    print $cod_orden_pago.'|'.$total_neto;
}else
    print 'fallo';
?>