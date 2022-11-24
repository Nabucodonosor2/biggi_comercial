<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_cotizacion = $_REQUEST['cod_cotizacion'];
$temp = new Template_appl('dlg_print_cotizacion.htm');	
$temp->setVar("COD_COTIZACION", $cod_cotizacion);

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT  COD_PRODUCTO
				        ,USA_ELECTRICIDAD
				        ,USA_GAS
				        ,USA_VAPOR
				        ,USA_AGUA_FRIA
				        ,USA_AGUA_CALIENTE
				        ,USA_VENTILACION
				        ,USA_DESAGUE   
				FROM    PRODUCTO
				WHERE   COD_PRODUCTO IN (SELECT DISTINCT COD_PRODUCTO
				                         FROM ITEM_COTIZACION
				                         WHERE COD_COTIZACION = ".$cod_cotizacion.")";
$result = $db->build_results($sql);		

$cant_electrico = 0;
$cant_gas = 0;
$cant_vapor = 0;
$cant_agua = 0;
$cant_ventilacion = 0;
$cant_desague = 0;

	for ($i = 0; $i< count($result); $i++){
  	if ($result[$i]['USA_ELECTRICIDAD']== "S")
  		$cant_electrico ++;
  	if ($result[$i]['USA_GAS']== "S")
  		$cant_gas ++;  		
  	if ($result[$i]['USA_VAPOR']== "S")
  		$cant_vapor ++;  
  	if ($result[$i]['USA_AGUA_FRIA']== "S")
  		$cant_agua ++;	
  	if ($result[$i]['USA_AGUA_CALIENTE']== "S")
  		$cant_agua ++;	 
  	if ($result[$i]['USA_VENTILACION']== "S")
  		$cant_ventilacion ++;	 
  	if ($result[$i]['USA_DESAGUE']== "S")
  		$cant_desague ++;	 			
  }


if ($cant_electrico == 0)
	$temp->setVar("VISIBLE_ELECTRICO", 'none');		
else
	$temp->setVar("VISIBLE_ELECTRICO", '');	
	
if ($cant_gas == 0)
	$temp->setVar("VISIBLE_GAS", 'none');		
else
	$temp->setVar("VISIBLE_GAS", '');
	
if ($cant_vapor == 0)
	$temp->setVar("VISIBLE_VAPOR", 'none');		
else
	$temp->setVar("VISIBLE_VAPOR", '');	

if ($cant_agua == 0)
	$temp->setVar("VISIBLE_AGUA", 'none');		
else
	$temp->setVar("VISIBLE_AGUA", '');	
		
if ($cant_ventilacion == 0)
	$temp->setVar("VISIBLE_VENTILACION", 'none');		
else
	$temp->setVar("VISIBLE_VENTILACION", '');	
	
if ($cant_desague == 0)
	$temp->setVar("VISIBLE_DESAGUE", 'none');		
else
	$temp->setVar("VISIBLE_DESAGUE", '');	

	
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);	
$cod_usuario = session::get("COD_USUARIO");
$sql_perfil="SELECT COD_PERFIL
			FROM USUARIO
			WHERE COD_USUARIO =$cod_usuario";
$result_perfil = $db->build_results($sql_perfil);		
$cod_perfil =$result_perfil[0]['COD_PERFIL'];
$cod_item_menu = 990515;
$sql_autoriza="SELECT AUTORIZA_MENU
	  FROM AUTORIZA_MENU
	  WHERE COD_PERFIL =$cod_perfil
	  AND COD_ITEM_MENU =$cod_item_menu";
$result_autoriza = $db->build_results($sql_autoriza);		
$autoriza =$result_autoriza[0]['AUTORIZA_MENU'];

$sql_descto="select isnull(PORC_DSCTO1,0) PORC_DSCTO1
					,isnull(PORC_DSCTO2,0) PORC_DSCTO2
				from COTIZACION
				where COD_COTIZACION =$cod_cotizacion";
$result_descto = $db->build_results($sql_descto);		
$descto1 = $result_descto[0]['PORC_DSCTO1'];
$descto2 = $result_descto[0]['PORC_DSCTO2'];

if(($descto1 > 0) or ($descto2 > 0))
	$muestra_descto = 'SI';
else
	$muestra_descto = 'NO';

if($autoriza == 'E')
	$input = '<label id="excel" style="display:"><input name="formato" type="radio" onclick="valida_descuento('."'$muestra_descto'".');" id="ch_excel" value="1">Excel-</label>';
else
	$input = '<label id="excel" style="display:none"><input name="formato" type="radio" onclick="valida_descuento('."'$muestra_descto'".');" id="ch_excel" value="1">Excel-</label>';
	
$temp->setVar("EXCEL", $input);

if($cod_usuario == 1 || $cod_usuario == 8)
	$temp->setVar("DISPLAY_CAD", '');
else
	$temp->setVar("DISPLAY_CAD", 'none');
	
print $temp->toString();
?>