<?php
require_once("class_database.php");
require_once("../../appl.ini");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$dte		= new dte();
$SqlHash	= "SELECT dbo.f_get_parametro(200) K_HASH
						,convert(varchar,dateadd(day,-1,getdate()),103) FECHA";
$Datos_Hash	= $db->build_results($SqlHash);
$dte->hash	= $Datos_Hash[0]['K_HASH'];
$fecha = $Datos_Hash[0]['FECHA'];

$ve_folio = $_REQUEST['FOLIO'];



    $SISTEMA = 'BIGGI';
                    
    $sql = "SELECT NRO_FACTURA
        		  ,CASE
        		  	WHEN PORC_IVA = CONVERT(NUMERIC,$SISTEMA.dbo.f_get_parametro(1)) THEN '33'
        		  	ELSE '34'
        		  END TIPO_DOCUMENTO
        		  ,REPLACE(REPLACE($SISTEMA.dbo.f_get_parametro(20),'.',''),'-5','') RUTEMISOR
        		  ,COD_FACTURA
        	FROM $SISTEMA.DBO.FACTURA F
        	WHERE NRO_FACTURA = $ve_folio";
             
    $result_factura = $db->build_results($sql);
    for($l=0 ; $l < count($result_factura) ; $l++){
        $ve_folio		= $result_factura[$l]['NRO_FACTURA'];
        $ve_tipo_doc	= $result_factura[$l]['TIPO_DOCUMENTO'];
        $ve_emisor		= $result_factura[$l]['RUTEMISOR'];
        $cod_factura	= $result_factura[$l]['COD_FACTURA'];
		
        if(($ve_folio !=174770)||($ve_folio !=174769)){
        
	        $result = $dte->acuse_comercial($ve_folio,$ve_tipo_doc,$ve_emisor);
	
		echo 'Nro Folio: '.$ve_folio;
		echo '<br>';
		echo 'Tipo Doc: '.$ve_tipo_doc;
		echo '<br>';
		echo 'Emisor: '.$ve_emisor;
		echo '<br>';
		echo '<br>';

	        $new_var= substr($result, 1, strlen($result)-4);
	        $array = explode(',', $new_var);
	        $array2 = explode(':', $array[21]);
	        $receptor_evento = trim(str_replace("\"", "", $array2[1]));
	        
		echo 'Respuesta';
		echo '<br>';
	        print_r($result);

	        
	        echo '<br>';
	        echo '<br>';
	        if($receptor_evento == 'R'){
	            echo 'factura rechazada';
	            echo '<br>';
	            echo '<br>';
	        }
        }
    }
?>
