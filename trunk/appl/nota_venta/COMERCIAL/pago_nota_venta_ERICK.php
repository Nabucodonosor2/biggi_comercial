<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once ("../../../appl.ini");
ini_set('display_errors', 'Off');
/*PRUEBA*/
//session::set('K_ROOT_DIR',"/var/www/html/biggi_comercial/trunk/");
//session::set('K_ROOT_URL',"http://".$_SERVER['SERVER_NAME'].":8080/biggi_comercial/trunk/");
/*PRUEBA*/
/*OFICIAL*/
session::set('K_ROOT_DIR',"/var/www/sysbiggi_new/biggi_comercial/trunk/");
session::set('K_ROOT_URL',"http://www.biggi.cl/sysbiggi_new/biggi_comercial/trunk/");
/*OFICIAL*/
/*OFICIAL PRUEBA*/
//session::set('K_ROOT_DIR',"/var/www/html/sysbiggi_new/comercial_biggi_prueba/biggi/trunk/");
//session::set('K_ROOT_URL',"http://www.biggi.cl/sysbiggi_new/comercial_biggi_prueba/biggi/trunk/");
/*OFICIAL PRUEBA*/
$temp = new Template_appl('pago_nota_venta.html');

$cod_wp_transaccion = $_REQUEST["param"];
$cod_wp_transaccion = base64_decode($cod_wp_transaccion);

function encriptar_url($txt_input, $key){
	     $result = '';
	     for($i=0; $i<strlen($txt_input); $i++) {
	         $char = substr($txt_input, $i, 1);
	         $keychar = substr($key, ($i % strlen($key))-1, 1);
	         $char = chr(ord($char)+ord($keychar));
	         $result.=$char;
	     }
	     $txt_ouput = base64_encode($result);
	
	     return $txt_ouput;
	}
	
function dencriptar_url($txt_input, $key){
     $result = '';
     $string = base64_decode($txt_input);
     for($i=0; $i<strlen($string); $i++) {
         $char = substr($string, $i, 1);
         $keychar = substr($key, ($i % strlen($key))-1, 1);
         $char = chr(ord($char)-ord($keychar));
         $result.=$char;
     }
     $txt_ouput = $result;

     return $txt_ouput;
}


$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT COD_NOTA_VENTA
			,LINK_VISIBLE
		FROM WP_TRANSACCION
		WHERE COD_WP_TRANSACCION =$cod_wp_transaccion";
$result = $db->build_results($sql);			
$cod_nota_venta = $result[0]['COD_NOTA_VENTA'];
$link_visible 	= $result[0]['LINK_VISIBLE'];


//$cod_nota_venta = dencriptar_url($cod_nota_venta_enc,'nota_venta');

$sql_fecha= "select TOP 1 DATEDIFF(HOUR,FECHA_WP_TRANSACCION, GetDate()) FECHA_TRANSACCION
			from WP_TRANSACCION 
			where LINK_VISIBLE = 'S'
			and COD_NOTA_VENTA=$cod_nota_venta
			ORDER BY COD_NOTA_VENTA DESC";
			
$result = $db->build_results($sql_fecha);	
$fecha_transaccion = $result[0]["FECHA_TRANSACCION"];

//NV pagada
$sql = "SELECT EXITO
		FROM WP_TRANSACCION
		WHERE COD_WP_TRANSACCION =$cod_wp_transaccion";
$result = $db->build_results($sql);			
$exito = $result[0]['EXITO'];

//montos nv v/s transaccion
$sql = "SELECT NV.COD_NOTA_VENTA
			,NV.MONTO_WEBPAY
			,WP.MONTO_PAGO
			,NV.COD_EMPRESA COD_EMPRESA_NV
			,WP.COD_EMPRESA COD_EMPRESA_WP
			,NV.COD_ESTADO_NOTA_VENTA
			,WP.LINK_VISIBLE
			,WP.FECHA_ERROR
		FROM NOTA_VENTA NV, WP_TRANSACCION WP
		WHERE NV.COD_NOTA_VENTA = WP.COD_NOTA_VENTA
		AND WP.COD_WP_TRANSACCION =$cod_wp_transaccion";
$result = $db->build_results($sql);			
$cod_nota_venta_nv = $result[0]['COD_NOTA_VENTA'];
$monto_nv = $result[0]['MONTO_WEBPAY'];
$monto_wp = $result[0]['MONTO_PAGO'];
$cod_empresa_nv = $result[0]['COD_EMPRESA_NV'];
$cod_empresa_wp = $result[0]['COD_EMPRESA_WP'];
$cod_estado = $result[0]['COD_ESTADO_NOTA_VENTA'];
$link_visible = $result[0]['LINK_VISIBLE'];
$fecha_error = $result[0]['FECHA_ERROR'];


//se verifican otros LINK disponioble asociaciadas a la misma NV
$sql = "SELECT COUNT(*) CANT
		FROM WP_TRANSACCION WP
		WHERE WP.COD_NOTA_VENTA =$cod_nota_venta_nv
		AND WP.LINK_VISIBLE = 'S'";
$result = $db->build_results($sql);			
$cant_nv_validas = $result[0]['CANT'];


/*
SE REGISTRAN LOS ERRORES
*/

$error = 'N';
$vl_error ='';

//compracion de montos
if($monto_nv != $monto_wp){
	$vl_error = $vl_error.' - MONTO INVALIDO';
	$error = 'S';
}

//compracion de empresas
if($cod_empresa_nv != $cod_empresa_wp){
	$vl_error = $vl_error.' - EMPRESA INVALIDA';
	$error = 'S';
}

//compracion de estado anulada
if($cod_estado == 3){
	$vl_error = $vl_error.' - NV ANULADA';
	$error = 'S';
}

//compracion de estado anulada
if(($link_visible == 'N')&&($cant_nv_validas > 0)){
	$vl_error = $vl_error.' - NV INVALIDA';
	$error = 'S';
}
 
if($fecha_transaccion >24){
	$vl_error = $vl_error.' - FECHA CADUCADA';
	$error = 'S';
}

// INI BORRAR DESPUES DE PRUEBAS
if(($link_visible == 'N')){
	$vl_error = $vl_error.' - LINK NO VISIBLE';
	$error = 'S';
}

// INI BORRAR DESPUES DE PRUEBAS
if(($exito == 'S')){
	$vl_error = $vl_error.' - PAGO YA FUE REALIZADO';
	$error = 'S';
}

if($error == 'S'){
	$sql = "UPDATE WP_TRANSACCION
			SET ERROR_WEBPAY ='$vl_error'
			WHERE COD_WP_TRANSACCION =$cod_wp_transaccion";
	$db->query($sql);
}

if($error == 'S'){
	header ('Location: '.'http://webpay.biggi.cl/caducado');
}
else{
	
	$sql = "SELECT NV.COD_NOTA_VENTA	
			,E.RUT
			,E.DIG_VERIF
			,E.ALIAS
			,NV.COD_EMPRESA
			,E.NOM_EMPRESA
			,E.GIRO
			,COD_SUCURSAL_FACTURA
			,dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL_FACTURA, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA
			,COD_SUCURSAL_DESPACHO
			,dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL_DESPACHO, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_DESPACHO
			,COD_PERSONA
			,dbo.f_emp_get_mail_cargo_persona(COD_PERSONA,  '[EMAIL] - [NOM_CARGO]') MAIL_CARGO_PERSONA
			/*TOTAL*/
			,SUBTOTAL SUM_TOTAL
			,TOTAL_NETO
			,MONTO_IVA
			,PORC_IVA
			,TOTAL_CON_IVA 
			,WP.COD_WP_TRANSACCION TBK_COD_WP_TRANSACCION
			,MONTO_WEBPAY TBK_MONTO
			,MONTO_DSCTO1
			,MONTO_DSCTO2
			,PORC_DSCTO1
			,PORC_DSCTO2
			,CASE 
			 	WHEN MONTO_DSCTO1 > 0 THEN ''
			 	ELSE 'none'
			END DISPLAY_PORC1
			,CASE 
			 	WHEN MONTO_DSCTO1 > 0 THEN ''
			 	ELSE 'none'
			END DISPLAY_MONTO1
			,CASE 
			 	WHEN MONTO_DSCTO1 > 0 THEN 'oscuro'
			 	ELSE ''
			END CLASS_CLARO_UNO
			,CASE 
			 	WHEN MONTO_DSCTO2 > 0 THEN ''
			 	ELSE 'none'
			END DISPLAY_PORC2
			,CASE 
			 	WHEN MONTO_DSCTO2 > 0 THEN ''
			 	ELSE 'none'
			END DISPLAY_MONTO2
			,CASE 
			 	WHEN MONTO_DSCTO2 > 0 THEN 'oscuro'
			 	ELSE ''
			END CLASS_CLARO_DOS	
			,CASE 
			 	WHEN MONTO_DSCTO1 > 0 THEN ''
			 	ELSE 'none'
			END TR_DESCTO1
			,CASE 
			 	WHEN MONTO_DSCTO2 > 0 THEN ''
			 	ELSE 'none'
			END TR_DESCTO2
	FROM 	NOTA_VENTA NV, USUARIO U, EMPRESA E, ESTADO_NOTA_VENTA ENV, CUENTA_CORRIENTE CC, USUARIO V1, WP_TRANSACCION WP
	WHERE	NV.COD_NOTA_VENTA =$cod_nota_venta and
			U.COD_USUARIO = NV.COD_USUARIO and
			E.COD_EMPRESA = NV.COD_EMPRESA and
			ENV.COD_ESTADO_NOTA_VENTA = NV.COD_ESTADO_NOTA_VENTA and
			CC.COD_CUENTA_CORRIENTE = NV.COD_CUENTA_CORRIENTE
			and V1.COD_USUARIO = NV.COD_USUARIO_VENDEDOR1
			and WP.COD_NOTA_VENTA = NV.COD_NOTA_VENTA
			and WP.LINK_VISIBLE = 'S'";
	
	$dw = new datawindow($sql);
	
	$dw->add_control(new static_text('COD_NOTA_VENTA'));
	$dw->add_control(new static_num('RUT'));
	$dw->add_control(new static_num('PORC_IVA'));
	$dw->add_control(new static_text('DIG_VERIF'));
	$dw->add_control(new static_text('ALIAS'));
	$dw->add_control(new static_text('COD_EMPRESA'));
	$dw->add_control(new static_text('NOM_EMPRESA'));
	$dw->add_control(new static_text('GIRO'));
	$dw->add_control(new static_text('COD_SUCURSAL_FACTURA'));
	$dw->add_control(new static_text('DIRECCION_FACTURA'));
	$dw->add_control(new static_text('COD_SUCURSAL_DESPACHO'));
	$dw->add_control(new static_text('DIRECCION_DESPACHO'));
			
	//$dw->add_control(new edit_text('TBK_MONTO',10,10,'hidden'));
	//$dw->add_control(new edit_text('TBK_COD_WP_TRANSACCION',10,10,'hidden'));
	
	$sql = "SELECT COD_PERSONA
	  	      ,NOM_PERSONA 
		FROM PERSONA";
	$dw->add_control(new drop_down_dw('COD_PERSONA', $sql));

	$dw->add_control(new static_text('MAIL_CARGO_PERSONA'));
	$dw->add_control(new static_num('SUM_TOTAL'));
	$dw->add_control(new static_num('TOTAL_NETO'));
	$dw->add_control(new static_num('MONTO_IVA'));
	$dw->add_control(new static_num('TOTAL_CON_IVA'));
	$dw->add_control(new static_num('MONTO_DSCTO1'));
	$dw->add_control(new static_num('MONTO_DSCTO2'));

	$dw->set_entrable('COD_PERSONA', false);

	$dw->retrieve();
	$dw->habilitar($temp, true);
	
	$sql="SELECT	COD_ITEM_NOTA_VENTA,
					COD_NOTA_VENTA,
					ORDEN,
					ITEM,
					COD_PRODUCTO,
					NOM_PRODUCTO,
					CANTIDAD,
					PRECIO,
					COD_TIPO_GAS,
					COD_TIPO_ELECTRICIDAD,
					'' MOTIVO,
					dbo.f_nv_get_ct_con_preorden(COD_ITEM_NOTA_VENTA) CANTIDAD_PRECOMPRA,			
					dbo.f_nv_get_ct_con_orden(COD_ITEM_NOTA_VENTA)  CANTIDAD_COMPRA,		
					COD_TIPO_TE,
					MOTIVO_TE,
					case COD_PRODUCTO when 'TE' 
						then dbo.f_nv_pend_te(COD_ITEM_NOTA_VENTA)
					else
						''
					end PEND_AUTORIZA,
					case COD_PRODUCTO when 'TE' 
						then dbo.f_nv_get_datos_autoriza_te(COD_ITEM_NOTA_VENTA, 'MOTIVO_AUTORIZA')
					else
						''
					end MOTIVO_AUTORIZA_TE,
					case COD_PRODUCTO when 'TE' 
						then dbo.f_nv_get_datos_autoriza_te(COD_ITEM_NOTA_VENTA, 'FECHA_AUTORIZA')
					else
						''
					end FECHA_AUTORIZA_TE,
					case COD_PRODUCTO when 'TE' 
						then dbo.f_nv_get_datos_autoriza_te(COD_ITEM_NOTA_VENTA, 'USUARIO_AUTORIZA')
					else
						''
					end NOM_USUARIO_AUTORIZA_TE
					,null ENTRABLE_PRECIO
					,'N' IS_NEW
					,(PRECIO * CANTIDAD) TOTAL
			FROM 	ITEM_NOTA_VENTA
			WHERE 	COD_NOTA_VENTA =($cod_nota_venta)
					order by ORDEN asc";
					
	$dw_items = new datawindow($sql,"ITEM_NOTA_VENTA");
	
	$dw_items->add_control(new edit_text('COD_ITEM_NOTA_VENTA',10,10,'hidden'));
	$dw_items->add_control(new static_num('ORDEN'));
	$dw_items->add_control(new static_num('IS_NEW'));
	$dw_items->add_control(new static_text('ITEM'));
	$dw_items->add_control(new static_text('COD_PRODUCTO'));
	$dw_items->add_control(new static_text('COD_PRODUCTO_OLD'));
	$dw_items->add_control(new static_text('COD_PRODUCTO_H'));
	$dw_items->add_control(new static_text('NOM_PRODUCTO'));
	$dw_items->add_control(new static_num('CANTIDAD',1));
	$dw_items->add_control(new static_text('CANTIDAD_PRECOMPRA'));
	$dw_items->add_control(new static_text('CANTIDAD_COMPRA'));
	$dw_items->add_control(new static_num('PRECIO'));
	$dw_items->add_control(new static_text('MOTIVO'));
	$dw_items->add_control(new static_num('TOTAL'));
	$dw_items->add_control(new static_text('COD_TIPO_GAS'));
	$dw_items->add_control(new static_text('COD_TIPO_ELECTRICIDAD'));
	
	
	
	$dw_items->retrieve();	
	
	$monto = $dw->get_item(0,'TBK_MONTO');
	$trans = $dw->get_item(0,'TBK_COD_WP_TRANSACCION');
	
	$dw->set_item(0,'TBK_MONTO',base64_encode($monto));
	$dw->set_item(0,'TBK_COD_WP_TRANSACCION',base64_encode($trans));
	
	$dw_items->habilitar($temp, true);
	print $temp->toString();
}
?>