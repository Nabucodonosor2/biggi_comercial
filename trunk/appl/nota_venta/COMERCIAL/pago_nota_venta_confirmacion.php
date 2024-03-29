<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once ("../../../appl.ini");
/*PRUEBA*/
//session::set('K_ROOT_DIR',"/var/www/html/comercial_biggi/biggi/trunk/");
//session::set('K_ROOT_URL',"http://biggi.integrasystem.cl/comercial_biggi/biggi/trunk/");
/*OFICIAL*/
session::set('K_ROOT_DIR',"/var/www/html/sysbiggi_new/comercial_biggi/biggi/trunk/");
session::set('K_ROOT_URL',"http://accsisgb.biggi.cl/sysbiggi_new/comercial_biggi/biggi/trunk/");

$temp = new Template_appl('pago_nota_venta_confirmacion.html');

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

$sql_fecha= "select DATEDIFF(DAY,FECHA_WP_TRANSACCION, GetDate()) FECHA_TRANSACCION
			from WP_TRANSACCION 
			where LINK_VISIBLE = 'S'
			and COD_NOTA_VENTA=$cod_nota_venta";
			
$result = $db->build_results($sql_fecha);	
$fecha_transaccion = $result[0]["FECHA_TRANSACCION"];

//NV pagada
$sql = "SELECT EXITO
		FROM WP_TRANSACCION
		WHERE COD_WP_TRANSACCION =$cod_wp_transaccion";
$result = $db->build_results($sql);			
$exito = $result[0]['EXITO'];

if($exito!='S'){
	header ('Location: '.'http://biggi.cl/caducado');
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
			,PORC_IVA
			,SUBTOTAL SUM_TOTAL
			,TOTAL_NETO
			,MONTO_IVA
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
	$dw->add_control(new static_text('RUT'));
	$dw->add_control(new static_text('PORC_IVA'));
	$dw->add_control(new static_text('DIG_VERIF'));
	$dw->add_control(new static_text('ALIAS'));
	$dw->add_control(new static_text('COD_EMPRESA'));
	$dw->add_control(new static_text('NOM_EMPRESA'));
	$dw->add_control(new static_text('GIRO'));
	$dw->add_control(new static_text('COD_SUCURSAL_FACTURA'));
	$dw->add_control(new static_text('DIRECCION_FACTURA'));
	$dw->add_control(new static_text('COD_SUCURSAL_DESPACHO'));
	$dw->add_control(new static_text('DIRECCION_DESPACHO'));
	
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
					(CANTIDAD * PRECIO) SUBTOTAL,
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
	$dw_items->add_control(new static_num('SUBTOTAL'));
	$dw_items->add_control(new static_text('MOTIVO'));
	$dw_items->add_control(new static_num('TOTAL'));
	$dw_items->add_control(new static_text('COD_TIPO_GAS'));
	$dw_items->add_control(new static_text('COD_TIPO_ELECTRICIDAD'));
	
	$dw_items->retrieve();	
	$dw_items->habilitar($temp, true);
	print $temp->toString();
}
?>