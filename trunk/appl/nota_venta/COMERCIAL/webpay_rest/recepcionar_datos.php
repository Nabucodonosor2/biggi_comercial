<?php
require_once(dirname(__FILE__)."/../../../../../../commonlib/trunk/php/auto_load.php");
require_once ("../../../../appl.ini");

function base64url_encode2($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

$TBK_ORDEN_COMPRA = $_POST['TBK_TRANSACCION'];
$TBK_MONTO = $_POST['TBK_MONTO'];


    $url  = 'https://server1.neongreen.cl/webpay_rest_biggi/webpay-plus/index.php?action=create';
    $url .= '&TBK_MONTO='.$TBK_MONTO;
    $url .= '&TBK_ORDEN_COMPRA='.$TBK_ORDEN_COMPRA;
    
    $fp=fopen("pre_publica.pem","r");
    $pub_key=fread($fp,8192);
    fclose($fp);
    $bd_type = 'sqlsrv';
    $bd_host = K_SERVER;
    $bd_name = K_BD;
    $bd_user = K_USER;
    $bd_pass = K_PASS;
    
    
    $bd_data = $bd_type.'&'.$bd_host.'&'.$bd_name.'&'.$bd_user.'&'.$bd_pass;
    
  
    if(!openssl_get_publickey($pub_key)) {
        //echo"Error abriendo clave";
        $param_descripter = "ERROR_CLAVE";
    }else if(!openssl_public_encrypt($bd_data, $param_descripter, openssl_get_publickey($pub_key))) {
        //echo "Error encriptando datos";
        $param_descripter = "ERROR_ENCRIPTADO";
        
    }
    $TBK_BD = base64url_encode2($param_descripter);

    $url .= '&TBK_BD='.$TBK_BD;

    header ('Location: '.$url);

?>