<?php
require_once("class_database.php");
require_once("../../appl.ini");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$dte		= new dte();
$SqlHash	= "SELECT dbo.f_get_parametro(200) K_HASH";
$Datos_Hash	= $db->build_results($SqlHash);
$dte->hash	= $Datos_Hash[0]['K_HASH'];

for($a=0 ; $a < 4 ; $a++){
    $doc_fa = "";
    
    if($a == 0)
        $SISTEMA = 'BIGGI';
    else if($a == 1)
        $SISTEMA = 'TODOINOX';
    else if($a == 2)
        $SISTEMA = 'BODEGA_BIGGI';
    else if($a == 3)
        $SISTEMA = 'RENTAL';
                    
    $sql = "SELECT NRO_FACTURA
        		  ,CASE
        		  	WHEN PORC_IVA = CONVERT(NUMERIC,$SISTEMA.dbo.f_get_parametro(1)) THEN '33'
        		  	ELSE '34'
        		  END TIPO_DOCUMENTO
        		  ,REPLACE(REPLACE($SISTEMA.dbo.f_get_parametro(20),'.',''),'-5','') RUTEMISOR
        		  ,COD_FACTURA
        	FROM $SISTEMA.DBO.FACTURA F
        	WHERE dbo.f_makedate(DAY(F.FECHA_FACTURA), MONTH(F.FECHA_FACTURA), YEAR(F.FECHA_FACTURA)) > DATEADD(DAY, -14, dbo.f_makedate(DAY(GETDATE()), MONTH(GETDATE()), YEAR(GETDATE())))
            AND NRO_FACTURA NOT IN (SELECT COD_DOC FROM ACUSE_COMERCIAL_MAIL WHERE SISTEMA = '$SISTEMA')";
             
    $result_factura = $db->build_results($sql);
    for($l=0 ; $l < count($result_factura) ; $l++){
        $ve_folio		= $result_factura[$l]['NRO_FACTURA'];
        $ve_tipo_doc	= $result_factura[$l]['TIPO_DOCUMENTO'];
        $ve_emisor		= $result_factura[$l]['RUTEMISOR'];
        $cod_factura	= $result_factura[$l]['COD_FACTURA'];
		
        if(($ve_folio !=174770)||($ve_folio !=174769)){
        
	        $result = $dte->acuse_comercial($ve_folio,$ve_tipo_doc,$ve_emisor);
	
	        $new_var= substr($result, 1, strlen($result)-4);
	        $array = explode(',', $new_var);
	        $array2 = explode(':', $array[21]);
	        $receptor_evento = trim(str_replace("\"", "", $array2[1]));
	        
	        if($receptor_evento == 'R'){
	            $doc_fa .= $ve_folio.",";
	            
	            $db->EXECUTE_SP('spu_factura_rechazada', "'INSERT', NULL, $cod_factura, NULL, NULL, NULL, '$SISTEMA'");
	        }
        }
    }
                    
    if($a == 0){
        $biggi_count_doc = strlen($doc_fa);
        $biggi_doc_fa = trim($doc_fa, ',');
    }else if($a == 1){
        $tdnx_count_doc = strlen($doc_fa);
        $tdnx_doc_fa = trim($doc_fa, ',');
    }else if($a == 2){
        $bodega_count_doc = strlen($doc_fa);
        $bodega_doc_fa = trim($doc_fa, ',');
    }else if($a == 3){
        $rental_count_doc = strlen($doc_fa);
        $rental_doc_fa = trim($doc_fa, ',');
    }
}

////////////////Envio Correo/////////////////////////
$temp = new Template_appl('fa_acuse_comercial.htm');

if($biggi_count_doc == 0)
    $display_comercial = 'none';
if($tdnx_count_doc == 0)
    $display_tdnx = 'none';
if($bodega_count_doc == 0)
    $display_bodega = 'none';
if($rental_count_doc == 0)
    $display_rental = 'none';
                
if($biggi_count_doc == 0 && $tdnx_count_doc == 0 && $bodega_count_doc == 0 && $rental_count_doc == 0)
    $display_none = '';
else
    $display_none = 'none';
                        
$temp->setVar("NRO_FA_COMERCIAL", "$biggi_doc_fa");
$temp->setVar("NRO_FA_TODOINOX", "$tdnx_doc_fa");
$temp->setVar("NRO_FA_BODEGA", "$bodega_doc_fa");
$temp->setVar("NRO_FA_RENTAL", "$rental_doc_fa");

$temp->setVar("DISPLAY_COMERCIAL", "$display_comercial");
$temp->setVar("DISPLAY_TODOINOX", "$display_tdnx");
$temp->setVar("DISPLAY_BODEGA", "$display_bodega");
$temp->setVar("DISPLAY_RENTAL", "$display_rental");
$temp->setVar("DISPLAY_NONE", "$display_none");
$html = $temp->toString();

$lista_mail_to		= "'mherrera@biggi.cl'";
$lista_mail_to_name = "'Marcelo Herrera'";

$lista_mail_cc		= 'NULL';
$lista_mail_cc_name = 'NULL';

$lista_mail_bcc		= "'vmelo@integrasystem.cl;evergara@integrasystem.cl'";
$lista_mail_bcc_name= "'Victor Melo;Erick Vergara'";

$sp = "spu_envio_mail";
$param = "'INSERT'
         ,null
         ,null
         ,null
         ,'modulo_alertas@biggi.cl'
         ,'Módulo Alertas Sistemas Web BIGGI'
         ,$lista_mail_cc
         ,$lista_mail_cc_name
         ,$lista_mail_bcc
         ,$lista_mail_bcc_name
         ,$lista_mail_to
         ,$lista_mail_to_name
         ,'Informe de Facturas Rechazadas'
         ,'".str_replace("'","''",$html)."'
         ,NULL
         ,'LIBRE_DTE_FA_RECHAZADA'
         ,0";

if($db->EXECUTE_SP($sp, $param)){
    
    if($display_none == 'none'){
        $cod_envio_mail = $db->GET_IDENTITY();
        
        $biggi_doc_fa   = ($biggi_doc_fa=='') ? "null" : "'$biggi_doc_fa'";
        $tdnx_doc_fa    = ($tdnx_doc_fa=='') ? "null" : "'$tdnx_doc_fa'";
        $bodega_doc_fa  = ($bodega_doc_fa=='') ? "null" : "'$bodega_doc_fa'";
        $rental_doc_fa  = ($rental_doc_fa=='') ? "null" : "'$rental_doc_fa'";
        
        $sp = "spu_acuse_comercial";
        $param = "'INSERT'
                 ,$cod_envio_mail
                 ,$biggi_doc_fa
                 ,$tdnx_doc_fa
                 ,$bodega_doc_fa
                 ,$rental_doc_fa";
        
        if($db->EXECUTE_SP($sp, $param)){
            echo 'exito2';
        }else{
            echo 'fallo2';
        }
        
    }
    
    echo 'exito1';
}else
    echo 'fallo1';
        
/////////////////////////////////////////////////////
?>
