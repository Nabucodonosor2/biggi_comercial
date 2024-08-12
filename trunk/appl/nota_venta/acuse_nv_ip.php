<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/class_PHPMailer.php");
include(dirname(__FILE__)."/../../appl.ini");
session::set('K_ROOT_DIR', K_ROOT_DIR);

$temp = new Template_appl('acuse_nv_ip.htm');

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "exec spdw_acuse_nv_ip";
$result = $db->build_results($sql);

for ($i=0; $i < count($result); $i++){
    $cod_nota_venta = $result[$i]['COD_NOTA_VENTA'];

    $sql_result =  "SELECT COD_NOTA_VENTA
                        ,U.MAIL MAIL_VENDEDOR
                        ,NOM_USUARIO NOM_USUARIO_VENDEDOR
                        ,NOM_EMPRESA
                        ,dbo.number_format(CONVERT(VARCHAR,RUT), 0, ',', '.')+'-'+DIG_VERIF RUT
                        ,REFERENCIA
                        ,dbo.number_format(TOTAL_CON_IVA, 0, ',', '.') TOTAL_CON_IVA
                        ,NOM_FORMA_PAGO
                    FROM NOTA_VENTA NV
                        ,EMPRESA E
                        ,FORMA_PAGO FP
                        ,USUARIO U
                    WHERE COD_NOTA_VENTA = $cod_nota_venta
                    AND NV.COD_EMPRESA = E.COD_EMPRESA
                    AND NV.COD_FORMA_PAGO = FP.COD_FORMA_PAGO
                    AND NV.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO";
    $result_sql = $db->build_results($sql_result);

    $temp->setVar("COD_NOTA_VENTA", $result_sql[0]['COD_NOTA_VENTA']);
    $temp->setVar("NOM_USUARIO_VENDEDOR", $result_sql[0]['NOM_USUARIO_VENDEDOR']);
    $temp->setVar("NOM_EMPRESA", $result_sql[0]['NOM_EMPRESA']);
    $temp->setVar("RUT", $result_sql[0]['RUT']);
    $temp->setVar("REFERENCIA", $result_sql[0]['REFERENCIA']);
    $temp->setVar("TOTAL_CON_IVA", $result_sql[0]['TOTAL_CON_IVA']);
    $temp->setVar("NOM_FORMA_PAGO", $result_sql[0]['NOM_FORMA_PAGO']);
    $html = $temp->toString();
 
    $sp = "spu_envio_mail";
	
	/*$param = "'ACUSE_DTE'
			,null
			,1
			,null
		 	,'soporte@biggi.cl'
		 	,'Comercial Biggi S.A'
		 	,'sergio.pechoante@biggi.cl;jcatalan@biggi.cl;fpuebla@biggi.cl'
		 	,'Sergio Pechoante;José Catalán;Felipe Puebla'
		 	,'mherrera@biggi.cl'
		 	,'Marcelo Herrera'
		 	,'".$result_sql[0]['MAIL_VENDEDOR']."'
		 	,'".$result_sql[0]['NOM_USUARIO_VENDEDOR']."'
		 	,'INGRESO DE PAGO PENDIENTE / NOTA DE VENTA NRO ".$result_sql[0]['COD_NOTA_VENTA']."'
		 	,'".str_replace("'","''",$html)."'
            ,NULL
		 	,'ACUSE_NV_IP'
            ,0";*/
    
    $param = "'ACUSE_DTE'
			,null
			,1
			,null
		 	,'soporte@biggi.cl'
		 	,'Comercial Biggi S.A'
		 	,'isra.campos.o@gmail.com'
		 	,'Israel Campos'
		 	,NULL
		 	,NULL
		 	,'mherrera@biggi.cl'
		 	,'Marcelo Herrera'
		 	,'INGRESO DE PAGO PENDIENTE / NOTA DE VENTA NRO ".$result_sql[0]['COD_NOTA_VENTA']."'
		 	,'".str_replace("'","''",$html)."'
            ,NULL
		 	,'ACUSE_NV_IP'
            ,0";
    
	if($db->EXECUTE_SP($sp, $param))
        echo "Correo NV: ".$cod_nota_venta." enviado <br>";
    else
        echo "Error NV: ".$cod_nota_venta." <br>";
}
?>