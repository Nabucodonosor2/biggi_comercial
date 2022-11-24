<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once("class_database.php");
include(dirname(__FILE__)."/../../appl.ini");
session::set('K_ROOT_DIR', K_ROOT_DIR);

$count_fa_no_reg_tot = 0;
$count_fa_acept_rep_tot = 0;
$count_fa_acept_rep_l_tot = 0;
$count_fa_rechazada_tot = 0;

$count_gd_no_reg_tot = 0;
$count_gd_acept_rep_tot = 0;
$count_gd_acept_rep_l_tot = 0;
$count_gd_rechazada_tot = 0;

$count_nc_no_reg_tot = 0;
$count_nc_acept_rep_tot = 0;
$count_nc_acept_rep_l_tot = 0;
$count_nc_rechazada_tot = 0;

$temp = new Template_appl('reporte_libre_dte.htm');

$db1 = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SET LANGUAGE Spanish;
		SELECT DATENAME(WEEKDAY, GETDATE()) DIA_ACTUAL;";
$result = $db1->build_results($sql);

if($result[0]['DIA_ACTUAL'] == 'Lunes')
	$dia_diff = 3;
else
	$dia_diff = 1;

for($a=0 ; $a < 4 ; $a++){
	if($a == 0)
		$K_BD = "BIGGI";
	else if($a == 1)
		$K_BD = "BODEGA_BIGGI";
	else if($a == 2)
		$K_BD = "RENTAL";
	else if($a == 3)
		$K_BD = "TODOINOX";
	
	/*********Se inicializan en 0 cada vez que finalicen el loop de cada uno de los sistemas**********/
	$count_fa_no_reg = 0;
	$count_fa_acept_rep = 0;
	$count_fa_acept_rep_l = 0;
	$count_fa_rechazada = 0;
	
	$count_gd_no_reg = 0;
	$count_gd_acept_rep = 0;
	$count_gd_acept_rep_l = 0;
	$count_gd_rechazada = 0;
	
	$count_nc_no_reg = 0;
	$count_nc_acept_rep = 0;
	$count_nc_acept_rep_l = 0;
	$count_nc_rechazada = 0;	
	
	$var_fa_no_registrada = "";
	$var_fa_acept_rep = "";
	$var_fa_acept_rep_l = "";
	$var_fa_rechazada = "";
	
	$var_gd_no_registrada = "";
	$var_gd_acept_rep = "";
	$var_gd_acept_rep_l = "";
	$var_gd_rechazada = "";
	
	$var_nc_no_registrada = "";
	$var_nc_acept_rep = "";
	$var_nc_acept_rep_l = "";
	$var_nc_rechazada = "";
	/**************************************************************************************************/
	
	$db = new database(K_TIPO_BD, K_SERVER, $K_BD, K_USER, K_PASS);
	if($a == 0){
		$sql_fecha_ayer = "SELECT CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103) FECHA_AYER";
		$result = $db->build_results($sql_fecha_ayer);
		$fecha_ayer = $result[0]['FECHA_AYER'];
		$temp->setVar("FECHA_AYER", $fecha_ayer);
	}
	/*******************************************FACTURA****************************************************/
	$sql = "SELECT COD_FACTURA
			FROM $K_BD.dbo.FACTURA F
				,$K_BD.dbo.ESTADO_LIBRE_DTE E
			WHERE F.COD_ESTADO_LIBRE_DTE = E.COD_ESTADO_LIBRE_DTE
			AND E.ES_TERMINAL = 'N'
			AND CONVERT(VARCHAR, FECHA_FACTURA, 103) = CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103)";
	$result = $db->build_results($sql);
	
	/*******************ACTUALIZA ESTADO*********************/
	for($i=0 ; $i < count($result) ; $i++){
		$cod_factura = $result[$i]['COD_FACTURA'];
		
		$sql = "SELECT '33' DTE
	              		,F.NRO_FACTURA
	              		,REPLACE(REPLACE(dbo.f_get_parametro(20),'.',''),'-7','') as RUTEMISOR		
				FROM $K_BD.dbo.FACTURA F
				WHERE F.COD_FACTURA = $cod_factura";
		$consultar = $db->build_results($sql);
		
		$tipodte		= $consultar[0]['DTE']; 
		$nro_factura	= $consultar[0]['NRO_FACTURA']; 
		$rutemisor		= $consultar[0]['RUTEMISOR'];
		
		//Llamamos a dte.
		$dte = new dte();
		
		//Se le pasa como variable hash de la clase obtenida en parametros en la BD
		$SqlHash = "SELECT $K_BD.dbo.f_get_parametro(200) K_HASH";  
		$Datos_Hash = $db->build_results($SqlHash);
		$dte->hash = $Datos_Hash[0]['K_HASH'];
		
		//Llamamos al envio consultar estado de socumento.
		$response = $dte->actualizar_estado($tipodte,$nro_factura,$rutemisor);
		
		$actualizar_estado	= $dte->respuesta_actualizar_estado($response);
		$revision_estado	= substr($actualizar_estado[6], 0, 3); //respuesta de rechazado.
		
		if($revision_estado <> ''){
			if($revision_estado == 'EPR')
				$cod_estado_libre_dte = 1; //Aceptada	
			else if($revision_estado == 'RPR')
				$cod_estado_libre_dte = 2; //Aceptado con Reparos
			else if($revision_estado == 'RLV')
				$cod_estado_libre_dte = 3; //Aceptada con Reparos Leves
			else if($revision_estado == 'RCH')
				$cod_estado_libre_dte = 4; //Rechazado
			else if($revision_estado == 'RCT')
				$cod_estado_libre_dte = 5; //Rechazado por Error en Carátula
			else if($revision_estado == 'RFR')
				$cod_estado_libre_dte = 6; //Rechazado por Error en Firma
			else if($revision_estado == 'RCS')
				$cod_estado_libre_dte = 7; //Rechazado por Error en Schema
				
			$db->EXECUTE_SP($K_BD.'.dbo.spu_libre_dte', "'UPDATE_ESTADO_DTE_FA', $cod_factura, $cod_estado_libre_dte");	
		}
	}
	/********************************************************/
	$sql = "SELECT COUNT(*) COUNT
			FROM $K_BD.dbo.FACTURA
			WHERE CONVERT(VARCHAR, FECHA_FACTURA, 103) = CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103)";
	$result = $db->build_results($sql);
	$temp->setVar("FACTURAS_EMITIDAS_$K_BD", $result[0]['COUNT']);

	$sql = "SELECT NRO_FACTURA NRO_FA_NO_REG_$K_BD
			FROM $K_BD.dbo.FACTURA F
				,$K_BD.dbo.ESTADO_LIBRE_DTE E
			WHERE E.ES_TERMINAL = 'S'
			AND E.COD_ESTADO_LIBRE_DTE = 0 -- Iniciada
			AND CONVERT(VARCHAR, FECHA_FACTURA, 103) = CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103)
			AND F.COD_ESTADO_LIBRE_DTE = E.COD_ESTADO_LIBRE_DTE";
	
	$fa_no_registrada = $db->build_results($sql);
	for($k=0; $k < count($fa_no_registrada) ; $k++){
		$var_fa_no_registrada .= $fa_no_registrada[$k]["NRO_FA_NO_REG_$K_BD"]."/";
		$count_fa_no_reg++;
	}
	if(count($fa_no_registrada) > 0){
		$var_fa_no_registrada = trim($var_fa_no_registrada,'/');
		$var_fa_no_registrada = '('.$var_fa_no_registrada.')';
	}
	$temp->setVar("NRO_FA_NO_REG_$K_BD", $var_fa_no_registrada);
	$temp->setVar("FA_NO_REG_COUNT_$K_BD", $count_fa_no_reg);
	
	$sql = "SELECT NRO_FACTURA NRO_FA_ACEPT_REP_$K_BD
			FROM $K_BD.dbo.FACTURA F
				,$K_BD.dbo.ESTADO_LIBRE_DTE E
			WHERE E.ES_TERMINAL = 'S'
			AND E.COD_ESTADO_LIBRE_DTE = 2 -- Aceptado con Reparos
			AND CONVERT(VARCHAR, FECHA_FACTURA, 103) = CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103)
			AND F.COD_ESTADO_LIBRE_DTE = E.COD_ESTADO_LIBRE_DTE";
	
	$fa_acept_rep = $db->build_results($sql);
	for($k=0; $k < count($fa_acept_rep) ; $k++){
		$var_fa_acept_rep .= $fa_acept_rep[$k]["NRO_FA_ACEPT_REP_$K_BD"]."/";
		$count_fa_acept_rep++;
	}
	if(count($fa_acept_rep) > 0){
		$var_fa_acept_rep = trim($var_fa_acept_rep,'/');
		$var_fa_acept_rep = '('.$var_fa_acept_rep.')';
	}
	$temp->setVar("NRO_FA_ACEPT_REP_$K_BD", $var_fa_acept_rep);
	$temp->setVar("FA_ACEPT_REP_COUNT_$K_BD", $count_fa_acept_rep);
	
	$sql = "SELECT NRO_FACTURA NRO_FA_ACEPT_REP_L_$K_BD
			FROM $K_BD.dbo.FACTURA F
				,$K_BD.dbo.ESTADO_LIBRE_DTE E
			WHERE E.ES_TERMINAL = 'S'
			AND E.COD_ESTADO_LIBRE_DTE = 3 -- Aceptada con Reparos Leves
			AND CONVERT(VARCHAR, FECHA_FACTURA, 103) = CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103)
			AND F.COD_ESTADO_LIBRE_DTE = E.COD_ESTADO_LIBRE_DTE";
	
	$fa_acept_rep_l = $db->build_results($sql);
	for($k=0; $k < count($fa_acept_rep_l) ; $k++){
		$var_fa_acept_rep_l .= $fa_acept_rep_l[$k]["NRO_FA_ACEPT_REP_L_$K_BD"]."/";
		$count_fa_acept_rep_l++;
	}
	if(count($fa_acept_rep_l) > 0){
		$var_fa_acept_rep_l = trim($var_fa_acept_rep_l,'/');
		$var_fa_acept_rep_l = '('.$var_fa_acept_rep_l.')';
	}
	$temp->setVar("NRO_FA_ACEPT_REP_L_$K_BD", $var_fa_acept_rep_l);
	$temp->setVar("FA_ACEPT_REP_L_COUNT_$K_BD", $count_fa_acept_rep_l);		
	
	$sql = "SELECT NRO_FACTURA NRO_FA_RECHAZADA_$K_BD
			FROM $K_BD.dbo.FACTURA F
				,$K_BD.dbo.ESTADO_LIBRE_DTE E
			WHERE E.ES_TERMINAL = 'S'
			AND E.COD_ESTADO_LIBRE_DTE in (4, 5, 6, 7) -- Rechazado
			AND CONVERT(VARCHAR, FECHA_FACTURA, 103) = CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103)
			AND F.COD_ESTADO_LIBRE_DTE = E.COD_ESTADO_LIBRE_DTE";
	
	$fa_rechazada = $db->build_results($sql);
	for($k=0; $k < count($fa_rechazada) ; $k++){
		$var_fa_rechazada .= $fa_rechazada[$k]["NRO_FA_RECHAZADA_$K_BD"]."/";
		$count_fa_rechazada++;
	}
	if(count($fa_rechazada) > 0){
		$var_fa_rechazada = trim($var_fa_rechazada,'/');
		$var_fa_rechazada = '('.$var_fa_rechazada.')';
	}
	$temp->setVar("NRO_FA_RECHAZADA_$K_BD", $var_fa_rechazada);
	$temp->setVar("FA_RECHAZADA_COUNT_$K_BD", $count_fa_rechazada);			
			
	/*******************************************GUIA DESPACHO**********************************************/
	$sql = "SELECT COD_GUIA_DESPACHO
			FROM $K_BD.dbo.GUIA_DESPACHO G
				,$K_BD.dbo.ESTADO_LIBRE_DTE E 
			WHERE G.COD_ESTADO_LIBRE_DTE = E.COD_ESTADO_LIBRE_DTE
			AND E.ES_TERMINAL = 'N'
			AND CONVERT(VARCHAR, FECHA_GUIA_DESPACHO, 103) = CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103)";
	$result = $db->build_results($sql);
	/*******************ACTUALIZA ESTADO*********************/
	for($i=0 ; $i < count($result) ; $i++){
		$cod_guia_despacho = $result[$i]['COD_GUIA_DESPACHO'];
	
		$sql = "SELECT '52' DTE
	              		,GD.NRO_GUIA_DESPACHO
	              		,REPLACE(REPLACE(dbo.f_get_parametro(20),'.',''),'-8','') as RUTEMISOR
				FROM $K_BD.dbo.GUIA_DESPACHO GD
				WHERE GD.COD_GUIA_DESPACHO = $cod_guia_despacho";
		$consultar = $db->build_results($sql);
		
		$tipodte			= $consultar[0]['DTE']; 
		$nro_guia_despacho	= $consultar[0]['NRO_GUIA_DESPACHO']; 
		$rutemisor			= $consultar[0]['RUTEMISOR'];
		
		//Llamamos a dte.
		$dte = new dte();
		
		//Se le pasa como variable hash de la clase obtenida en parametros en la BD
		$SqlHash = "SELECT dbo.f_get_parametro(200) K_HASH";  
		$Datos_Hash = $db->build_results($SqlHash);
		$dte->hash = $Datos_Hash[0]['K_HASH'];
		
		//Llamamos al envio consultar estado de socumento.
		$response = $dte->actualizar_estado($tipodte,$nro_guia_despacho,$rutemisor);
		
		$actualizar_estado	= $dte->respuesta_actualizar_estado($response);
		$revision_estado	= substr($actualizar_estado[6], 0, 3); //respuesta de rechazado.
	
		if($revision_estado <> ''){
			if($revision_estado == 'EPR')
				$cod_estado_libre_dte = 1; //Aceptada	
			else if($revision_estado == 'RPR')
				$cod_estado_libre_dte = 2; //Aceptado con Reparos
			else if($revision_estado == 'RLV')
				$cod_estado_libre_dte = 3; //Aceptada con Reparos Leves
			else if($revision_estado == 'RCH')
				$cod_estado_libre_dte = 4; //Rechazado
			else if($revision_estado == 'RCT')
				$cod_estado_libre_dte = 5; //Rechazado por Error en Carátula
			else if($revision_estado == 'RFR')
				$cod_estado_libre_dte = 6; //Rechazado por Error en Firma
			else if($revision_estado == 'RCS')
				$cod_estado_libre_dte = 7; //Rechazado por Error en Schema
				
			$db->EXECUTE_SP($K_BD.'.dbo.spu_libre_dte', "'UPDATE_ESTADO_DTE_GD', $cod_guia_despacho, $cod_estado_libre_dte");	
		}
	}
	/********************************************************/
	$sql = "SELECT COUNT(*) COUNT
			FROM $K_BD.dbo.GUIA_DESPACHO
			WHERE CONVERT(VARCHAR, FECHA_GUIA_DESPACHO, 103) = CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103)";
	$result = $db->build_results($sql);
	$temp->setVar("GD_EMITIDAS_$K_BD", $result[0]['COUNT']);

	$sql = "SELECT NRO_GUIA_DESPACHO NRO_GD_NO_REG_$K_BD
			FROM $K_BD.dbo.GUIA_DESPACHO GD
				,$K_BD.dbo.ESTADO_LIBRE_DTE E
			WHERE E.ES_TERMINAL = 'S'
			AND E.COD_ESTADO_LIBRE_DTE = 0 -- Iniciada
			AND CONVERT(VARCHAR, FECHA_GUIA_DESPACHO, 103) = CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103)
			AND GD.COD_ESTADO_LIBRE_DTE = E.COD_ESTADO_LIBRE_DTE";
	
	$gd_no_registrada = $db->build_results($sql);
	for($k=0; $k < count($gd_no_registrada) ; $k++){
		$var_gd_no_registrada .= $gd_no_registrada[$k]["NRO_GD_NO_REG_$K_BD"]."/";
		$count_gd_no_reg++;
	}
	if(count($gd_no_registrada) > 0){
		$var_gd_no_registrada = trim($var_gd_no_registrada,'/');
		$var_gd_no_registrada = '('.$var_gd_no_registrada.')';
	}
	$temp->setVar("NRO_GD_NO_REG_$K_BD", $var_gd_no_registrada);
	$temp->setVar("GD_NO_REG_COUNT_$K_BD", $count_gd_no_reg);
	
	$sql = "SELECT NRO_GUIA_DESPACHO NRO_GD_ACEPT_REP_$K_BD
			FROM $K_BD.dbo.GUIA_DESPACHO GD
				,$K_BD.dbo.ESTADO_LIBRE_DTE E
			WHERE E.ES_TERMINAL = 'S'
			AND E.COD_ESTADO_LIBRE_DTE = 2 -- Aceptado con Reparos
			AND CONVERT(VARCHAR, FECHA_GUIA_DESPACHO, 103) = CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103)
			AND GD.COD_ESTADO_LIBRE_DTE = E.COD_ESTADO_LIBRE_DTE";
	
	$gd_acept_rep = $db->build_results($sql);
	for($k=0; $k < count($gd_acept_rep) ; $k++){
		$var_gd_acept_rep .= $gd_acept_rep[$k]["NRO_GD_ACEPT_REP_$K_BD"]."/";
		$count_gd_acept_rep++;
	}
	if(count($gd_acept_rep) > 0){
		$var_gd_acept_rep = trim($var_gd_acept_rep,'/');
		$var_gd_acept_rep = '('.$var_gd_acept_rep.')';
	}
	$temp->setVar("NRO_GD_ACEPT_REP_$K_BD", $var_gd_acept_rep);
	$temp->setVar("GD_ACEPT_REP_COUNT_$K_BD", $count_gd_acept_rep);
	
	$sql = "SELECT NRO_GUIA_DESPACHO NRO_GD_ACEPT_REP_L_$K_BD
			FROM $K_BD.dbo.GUIA_DESPACHO GD
				,$K_BD.dbo.ESTADO_LIBRE_DTE E
			WHERE E.ES_TERMINAL = 'S'
			AND E.COD_ESTADO_LIBRE_DTE = 3 -- Aceptada con Reparos Leves
			AND CONVERT(VARCHAR, FECHA_GUIA_DESPACHO, 103) = CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103)
			AND GD.COD_ESTADO_LIBRE_DTE = E.COD_ESTADO_LIBRE_DTE";
	
	$gd_acept_rep_l = $db->build_results($sql);
	for($k=0; $k < count($gd_acept_rep_l) ; $k++){
		$var_gd_acept_rep_l .= $gd_acept_rep_l[$k]["NRO_GD_ACEPT_REP_$K_BD"]."/";
		$count_gd_acept_rep_l++;
	}
	if(count($gd_acept_rep_l) > 0){
		$var_gd_acept_rep_l = trim($var_gd_acept_rep_l,'/');
		$var_gd_acept_rep_l = '('.$var_gd_acept_rep_l.')';
	}
	$temp->setVar("NRO_GD_ACEPT_REP_L_$K_BD", $var_gd_acept_rep_l);
	$temp->setVar("GD_ACEPT_REP_L_COUNT_$K_BD", $count_gd_acept_rep_l);		
	
	$sql = "SELECT NRO_GUIA_DESPACHO NRO_GD_RECHAZADA_$K_BD
			FROM $K_BD.dbo.GUIA_DESPACHO GD
				,$K_BD.dbo.ESTADO_LIBRE_DTE E
			WHERE E.ES_TERMINAL = 'S'
			AND E.COD_ESTADO_LIBRE_DTE in (4, 5, 6, 7) -- Rechazado
			AND CONVERT(VARCHAR, FECHA_GUIA_DESPACHO, 103) = CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103)
			AND GD.COD_ESTADO_LIBRE_DTE = E.COD_ESTADO_LIBRE_DTE";
	
	$gd_rechazada = $db->build_results($sql);
	for($k=0; $k < count($gd_rechazada) ; $k++){
		$var_gd_rechazada .= $gd_rechazada[$k]["NRO_GD_ACEPT_REP_$K_BD"]."/";
		$count_gd_rechazada++;
	}
	if(count($gd_rechazada) > 0){
		$var_gd_rechazada = trim($var_gd_rechazada,'/');
		$var_gd_rechazada = '('.$var_gd_rechazada.')';
	}
	$temp->setVar("NRO_GD_RECHAZADA_$K_BD", $var_gd_rechazada);
	$temp->setVar("GD_RECHAZADA_COUNT_$K_BD", $count_gd_rechazada);

	/*******************************************NOTA CREDITO***********************************************/
	$sql = "SELECT COD_NOTA_CREDITO
			FROM $K_BD.dbo.NOTA_CREDITO NC
				,$K_BD.dbo.ESTADO_LIBRE_DTE E
			WHERE NC.COD_ESTADO_LIBRE_DTE = E.COD_ESTADO_LIBRE_DTE
			AND E.ES_TERMINAL = 'N'
			AND CONVERT(VARCHAR, FECHA_NOTA_CREDITO, 103) = CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103)";
	$result = $db->build_results($sql);
	
	/*******************ACTUALIZA ESTADO*********************/
	for($i=0 ; $i < count($result) ; $i++){
		$cod_nota_credito = $result[$i]['COD_NOTA_CREDITO'];
	
		$sql = "SELECT '61' DTE
	              		,N.NRO_NOTA_CREDITO
	              		,REPLACE(REPLACE(dbo.f_get_parametro(20),'.',''),'-8','') as RUTEMISOR
				FROM $K_BD.dbo.NOTA_CREDITO N
				WHERE N.COD_NOTA_CREDITO = $cod_nota_credito";
		$consultar = $db->build_results($sql);
		
		$tipodte			= $consultar[0]['DTE']; 
		$nro_nota_credito	= $consultar[0]['NRO_NOTA_CREDITO']; 
		$rutemisor			= $consultar[0]['RUTEMISOR'];
		
		//Llamamos a dte.
		$dte = new dte();
		
		//Se le pasa como variable hash de la clase obtenida en parametros en la BD
		$SqlHash = "SELECT dbo.f_get_parametro(200) K_HASH";
		$Datos_Hash = $db->build_results($SqlHash);
		$dte->hash = $Datos_Hash[0]['K_HASH'];
		
		//Llamamos al envio consultar estado de socumento.
		$response = $dte->actualizar_estado($tipodte,$nro_nota_credito,$rutemisor);
		
		$actualizar_estado = $dte->respuesta_actualizar_estado($response);
		$revision_estado	= substr($actualizar_estado[6], 0, 3);
		
		if($revision_estado <> ''){
			if($revision_estado == 'EPR')
				$cod_estado_libre_dte = 1; //Aceptada	
			else if($revision_estado == 'RPR')
				$cod_estado_libre_dte = 2; //Aceptado con Reparos
			else if($revision_estado == 'RLV')
				$cod_estado_libre_dte = 3; //Aceptada con Reparos Leves
			else if($revision_estado == 'RCH')
				$cod_estado_libre_dte = 4; //Rechazado
			else if($revision_estado == 'RCT')
				$cod_estado_libre_dte = 5; //Rechazado por Error en Carátula
			else if($revision_estado == 'RFR')
				$cod_estado_libre_dte = 6; //Rechazado por Error en Firma
			else if($revision_estado == 'RCS')
				$cod_estado_libre_dte = 7; //Rechazado por Error en Schema
				
			$db->EXECUTE_SP($K_BD.'.dbo.spu_libre_dte', "'UPDATE_ESTADO_DTE_NC', $cod_nota_credito, $cod_estado_libre_dte");	
		}		
	}
	/**********************************************************/
	$sql = "SELECT COUNT(*) COUNT
			FROM $K_BD.dbo.NOTA_CREDITO
			WHERE CONVERT(VARCHAR, FECHA_NOTA_CREDITO, 103) = CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103)";
	$result = $db->build_results($sql);
	$temp->setVar("NC_EMITIDAS_$K_BD", $result[0]['COUNT']);

	$sql = "SELECT NRO_NOTA_CREDITO NRO_NC_NO_REG_$K_BD
			FROM $K_BD.dbo.NOTA_CREDITO NC
				,$K_BD.dbo.ESTADO_LIBRE_DTE E
			WHERE E.ES_TERMINAL = 'S'
			AND E.COD_ESTADO_LIBRE_DTE = 0 -- Iniciada
			AND CONVERT(VARCHAR, FECHA_NOTA_CREDITO, 103) = CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103)
			AND NC.COD_ESTADO_LIBRE_DTE = E.COD_ESTADO_LIBRE_DTE";
	
	$nc_no_registrada = $db->build_results($sql);
	for($k=0; $k < count($nc_no_registrada) ; $k++){
		$var_nc_no_registrada .= $nc_no_registrada[$k]["NRO_NC_NO_REG_$K_BD"]."/";
		$count_nc_no_reg++;
	}
	if(count($nc_no_registrada) > 0){
		$var_nc_no_registrada = trim($var_nc_no_registrada,'/');
		$var_nc_no_registrada = '('.$var_nc_no_registrada.')';
	}
	$temp->setVar("NRO_NC_NO_REG_$K_BD", $var_nc_no_registrada);
	$temp->setVar("NC_NO_REG_COUNT_$K_BD", $count_nc_no_reg);
	
	$sql = "SELECT NRO_NOTA_CREDITO NRO_NC_ACEPT_REP_$K_BD
			FROM $K_BD.dbo.NOTA_CREDITO NC
				,$K_BD.dbo.ESTADO_LIBRE_DTE E
			WHERE E.ES_TERMINAL = 'S'
			AND E.COD_ESTADO_LIBRE_DTE = 2 -- Aceptado con Reparos
			AND CONVERT(VARCHAR, FECHA_NOTA_CREDITO, 103) = CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103)
			AND NC.COD_ESTADO_LIBRE_DTE = E.COD_ESTADO_LIBRE_DTE";
	
	$nc_acept_rep = $db->build_results($sql);
	for($k=0; $k < count($nc_acept_rep) ; $k++){
		$var_nc_acept_rep .= $nc_acept_rep[$k]["NRO_NC_ACEPT_REP_$K_BD"]."/";
		$count_nc_acept_rep++;
	}
	if(count($nc_acept_rep) > 0){
		$var_nc_acept_rep = trim($var_nc_acept_rep,'/');
		$var_nc_acept_rep = '('.$var_nc_acept_rep.')';
	}
	$temp->setVar("NRO_NC_ACEPT_REP_$K_BD", $var_nc_acept_rep);
	$temp->setVar("NC_ACEPT_REP_COUNT_$K_BD", $count_nc_acept_rep);
	
	$sql = "SELECT NRO_NOTA_CREDITO NRO_NC_ACEPT_REP_L_$K_BD
			FROM $K_BD.dbo.NOTA_CREDITO NC
				,$K_BD.dbo.ESTADO_LIBRE_DTE E
			WHERE E.ES_TERMINAL = 'S'
			AND E.COD_ESTADO_LIBRE_DTE = 3 -- Aceptada con Reparos Leves
			AND CONVERT(VARCHAR, FECHA_NOTA_CREDITO, 103) = CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103)
			AND NC.COD_ESTADO_LIBRE_DTE = E.COD_ESTADO_LIBRE_DTE";
	
	$nc_acept_rep_l = $db->build_results($sql);
	for($k=0; $k < count($nc_acept_rep_l) ; $k++){
		$var_nc_acept_rep_l .= $nc_acept_rep_l[$k]["NRO_NC_ACEPT_REP_L_$K_BD"]."/";
		$count_nc_acept_rep_l++;
	}
	if(count($nc_acept_rep_l) > 0){
		$var_nc_acept_rep_l = trim($var_nc_acept_rep_l,'/');
		$var_nc_acept_rep_l = '('.$var_nc_acept_rep_l.')';
	}
	$temp->setVar("NRO_NC_ACEPT_REP_L_$K_BD", $var_nc_acept_rep_l);
	$temp->setVar("NC_ACEPT_REP_L_COUNT_$K_BD", $count_nc_acept_rep_l);
	
	$sql = "SELECT NRO_NOTA_CREDITO NRO_NC_RECHAZADA_$K_BD
			FROM $K_BD.dbo.NOTA_CREDITO NC
				,$K_BD.dbo.ESTADO_LIBRE_DTE E
			WHERE E.ES_TERMINAL = 'S'
			AND E.COD_ESTADO_LIBRE_DTE in (4, 5, 6, 7) -- Rechazado
			AND CONVERT(VARCHAR, FECHA_NOTA_CREDITO, 103) = CONVERT(VARCHAR, DATEADD(DAY, -$dia_diff, GETDATE()), 103)
			AND NC.COD_ESTADO_LIBRE_DTE = E.COD_ESTADO_LIBRE_DTE";
	
	$nc_rechazada = $db->build_results($sql);
	for($k=0; $k < count($nc_rechazada) ; $k++){
		$var_nc_rechazada .= $nc_rechazada[$k]["NRO_NC_RECHAZADA_$K_BD"]."/";
		$count_nc_rechazada++;
	}
	if(count($nc_rechazada) > 0){
		$var_nc_rechazada = trim($var_nc_rechazada,'/');
		$var_nc_rechazada = '('.$var_nc_rechazada.')';
	}
	$temp->setVar("NRO_NC_RECHAZADA_$K_BD", $var_nc_rechazada);
	$temp->setVar("NC_RECHAZADA_COUNT_$K_BD", $count_nc_rechazada);
	
	$count_fa_no_reg_tot = $count_fa_no_reg_tot + $count_fa_no_reg;
	$count_fa_acept_rep_tot = $count_fa_acept_rep_tot + $count_fa_acept_rep;
	$count_fa_acept_rep_l_tot = $count_fa_acept_rep_l_tot + $count_fa_acept_rep_l;
	$count_fa_rechazada_tot = $count_fa_rechazada_tot + $count_fa_rechazada;
	
	$count_gd_no_reg_tot = $count_gd_no_reg_tot + $count_gd_no_reg;
	$count_gd_acept_rep_tot = $count_gd_acept_rep_tot + $count_gd_acept_rep;
	$count_gd_acept_rep_l_tot = $count_gd_acept_rep_l_tot + $count_gd_acept_rep_l;
	$count_gd_rechazada_tot = $count_gd_rechazada_tot + $count_gd_rechazada;
	
	$count_nc_no_reg_tot = $count_nc_no_reg_tot + $count_nc_no_reg;
	$count_nc_acept_rep_tot = $count_nc_acept_rep_tot + $count_nc_acept_rep;
	$count_nc_acept_rep_l_tot = $count_nc_acept_rep_l_tot + $count_nc_acept_rep_l;
	$count_nc_rechazada_tot = $count_nc_rechazada_tot + $count_nc_rechazada;
}

/*******************************************ENVIO MAIL (Primer Correo)*************************************************/
$db2 = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$html = $temp->toString();

$lista_mail_to = "sergio.pechoante@biggi.cl;rescudero@biggi.cl;fpuebla@biggi.cl";
$lista_mail_to_name = "Sergio Pechoante;Rafael Escudero;Felipe Puebla";

$lista_mail_cc = "mherrera@biggi.cl";
$lista_mail_cc_name = "Marcelo Herrera";

$lista_mail_bcc = "evergara@integrasystem.cl;vmelo@integrasystem.cl;icampos@integrasystem.cl";
$lista_mail_bcc_name = "Erick Vergara;Victor Melo;Israel Campos";

/*$lista_mail_to = "icampos@integrasystem.cl;evergara@integrasystem.cl;mherrera@biggi.cl";
$lista_mail_to_name = "Israel Campos;Erick Vergara;Marcelo Herrera";*/

$sp = "spu_envio_mail";	
$param = "'INSERT'
		,null
		,null
		,null
	 	,'modulo_alertas@biggi.cl'
	 	,'Módulo Alertas Sistemas Web BIGGI'
	 	,'$lista_mail_cc'
	 	,'$lista_mail_cc_name'
	 	,'$lista_mail_bcc'
	 	,'$lista_mail_bcc_name'
	 	,'$lista_mail_to'	
	 	,'$lista_mail_to_name'
	 	,'Estatus facturas del día ".$fecha_ayer."'
	 	,'".str_replace("'","''",$html)."'
	 	,NULL
	 	,'LIBRE_DTE'
	 	,0";

if($db2->EXECUTE_SP($sp, $param))
	echo 'exito';
else
	echo 'fallo';
	
/*******************************************ENVIO MAIL (Segundo Correo)*************************************************/
if($count_fa_no_reg_tot <> 0 || $count_fa_acept_rep_tot <> 0 || $count_fa_acept_rep_l_tot <> 0 || $count_fa_rechazada_tot <> 0 ||
	$count_gd_no_reg_tot <> 0 || $count_gd_acept_rep_tot <> 0 || $count_gd_acept_rep_l_tot <> 0 || $count_gd_rechazada_tot <> 0 ||
	 $count_nc_no_reg_tot <> 0 || $count_nc_acept_rep_tot <> 0 || $count_nc_acept_rep_l_tot <> 0 || $count_nc_rechazada_tot <> 0){

$lista_mail_to = "mherrera@biggi.cl";
$lista_mail_to_name = "Marcelo Herrera";
	 
/*$lista_mail_to = "icampos@integrasystem.cl;evergara@integrasystem.cl;mherrera@biggi.cl";
$lista_mail_to_name = "Israel Campos;Erick Vergara;Marcelo Herrera";*/

$param = "'INSERT'
		,null
		,null
		,null
	 	,'modulo_alertas@biggi.cl'
	 	,'Módulo Alertas Sistemas Web BIGGI'
	 	,null
	 	,null
	 	,null
	 	,null
	 	,'$lista_mail_to'
	 	,'$lista_mail_to_name'
	 	,'Estatus facturas del día ".$fecha_ayer." (TECNICO)'
	 	,'".str_replace("'","''",$html)."'
	 	,NULL
	 	,'LIBRE_DTE'
	 	,0";

if($db2->EXECUTE_SP($sp, $param))
	echo 'exito';
else
	echo 'fallo';
}	
?>