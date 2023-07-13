<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
//require_once("class_PHPMailer.php");
require_once ("../../../appl.ini");
ini_set('display_errors', 'OFF');

$TBK_TRANSACCION= $_GET['orden_compra'];
session::set('K_ROOT_DIR',"/var/www/sysbiggi_new/biggi_comercial/trunk/");
session::set('K_ROOT_URL',"http://accsisgb.biggi.cl/sysbiggi_new/biggi_comercial/trunk/");
//session::set('K_ROOT_DIR',"/var/www/desarrolladores/ecastillo/biggi_comercial/trunk/");
//session::set('K_ROOT_URL',"http://192.168.2.93/desarrolladores/ecastillo/biggi_comercial/trunk/");
//$dbc   = new database();

$dbc = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT COUNT(*) CANTIDAD
		FROM WP_TRANSACCION WP, WP_PAGO_TRANSACCION_WS WPT
		WHERE WP.COD_WP_TRANSACCION =$TBK_TRANSACCION
		AND WP.COD_WP_TRANSACCION = WPT.COD_WP_TRANSACCION
		AND EXITO = 'S'";
$dbc->query($sql);
$result = $dbc->get_row();
$cant = $result['CANTIDAD'];

if($cant == 0){
	header ('Location: '.'http://biggi.cl/fracaso');
	return;
}

$sql_mail = "SELECT  TOP 1 dbo.f_format_date(GETDATE(), 3) ENVIO_MAIL
					,E.NOM_EMPRESA
					,P.NOM_PERSONA
					,P.EMAIL
					,NV.COD_NOTA_VENTA
					,dbo.number_format(E.RUT, 0, ',', '.')+'-'+E.DIG_VERIF RUT
					,dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL_FACTURA, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA
					,REFERENCIA
					,U.NOM_USUARIO
					,dbo.number_format(TOTAL_CON_IVA, 0, ',', '.') TOTAL_CON_IVA
					,(SELECT TOP 1 WPT.TBK_TIPO_TRANSACCION FROM WP_PAGO_TRANSACCION WPT WHERE WPT.COD_WP_TRANSACCION = WT.COD_WP_TRANSACCION ORDER BY WPT.COD_WP_PAGO_TRANSACCION DESC) TBK_TIPO_TRANSACCION
					,dbo.number_format(MONTO_PAGO, 0, ',', '.') MONTO_PAGO
					,EXITO
					,MAIL
					,U.CELULAR
					,U.TELEFONO
					,CONVERT(VARCHAR, NV.FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA
					,dbo.number_format(SUBTOTAL, 0, ',', '.') SUBTOTAL
					,dbo.number_format(MONTO_DSCTO1, 0, ',', '.') MONTO_DSCTO1
					,dbo.number_format(MONTO_DSCTO2, 0, ',', '.') MONTO_DSCTO2
					,CONVERT(VARCHAR, WT.FECHA_WP_TRANSACCION, 103) FECHA_WP_TRANSACCION
					,(SELECT TOP 1 CODIGO_AUTORIZACION FROM WP_PAGO_TRANSACCION_WS WPT WHERE WPT.COD_WP_TRANSACCION = WT.COD_WP_TRANSACCION ORDER BY WPT.COD_WP_PAGO_TRANSACCION_WS DESC) TBK_CODIGO_AUTORIZACION
					,(SELECT TOP 1 CANTIDAD_CUOTA FROM WP_PAGO_TRANSACCION_WS WPT WHERE WPT.COD_WP_TRANSACCION = WT.COD_WP_TRANSACCION ORDER BY WPT.COD_WP_PAGO_TRANSACCION_WS DESC) TBK_NUMERO_CUOTAS
					,(SELECT TOP 1 TIPO_PAGO FROM WP_PAGO_TRANSACCION_WS WPT WHERE WPT.COD_WP_TRANSACCION = WT.COD_WP_TRANSACCION ORDER BY WPT.COD_WP_PAGO_TRANSACCION_WS DESC) TBK_TIPO_PAGO
					,(SELECT IP.COD_INGRESO_PAGO	FROM INGRESO_PAGO IP, INGRESO_PAGO_FACTURA IPF WHERE IPF.COD_DOC = WT.COD_NOTA_VENTA AND IPF.TIPO_DOC = 'NOTA_VENTA' AND IP.COD_INGRESO_PAGO = IPF.COD_INGRESO_PAGO AND IP.COD_ESTADO_INGRESO_PAGO <> 3) COD_INGRESO_PAGO
					FROM WP_TRANSACCION WT
					,NOTA_VENTA NV
					,EMPRESA E
					,PERSONA P
					,USUARIO U
				WHERE WT.COD_WP_TRANSACCION =$TBK_TRANSACCION
				AND WT.COD_NOTA_VENTA = NV.COD_NOTA_VENTA
				AND NV.COD_EMPRESA = E.COD_EMPRESA
				AND P.COD_PERSONA = NV.COD_PERSONA
				AND U.COD_USUARIO = NV.COD_USUARIO_VENDEDOR1
				ORDER BY WT.COD_WP_TRANSACCION";
$result_mail = $dbc->build_results($sql_mail);

$sql = "SELECT TOP 1 WP.COD_WP_TRANSACCION
				,E.NOM_EMPRESA
				,WP.NUMERO_TARJETA TBK_FINAL_NUMERO_TARJETA
				,dbo.f_get_separador_miles(WP.MONTO)MONTO
				,CONVERT(VARCHAR(10),WT.FECHA_PAGO,103) FECHA_TRANSACCION
				,CONVERT(VARCHAR(10),WT.FECHA_PAGO,108) HORA_TRANSACCION
				,WP.CODIGO_AUTORIZACION TBK_CODIGO_AUTORIZACION
				,WP.TIPO_PAGO TBK_TIPO_PAGO
				,WP.CANTIDAD_CUOTA TBK_NUMERO_CUOTAS
				,NV.COD_NOTA_VENTA
				,WT.LINK_PAGO
		FROM WP_PAGO_TRANSACCION_WS WP, WP_TRANSACCION WT, NOTA_VENTA NV, EMPRESA E
		WHERE WP.COD_WP_TRANSACCION=WT.COD_WP_TRANSACCION
		AND NV.COD_NOTA_VENTA=WT.COD_NOTA_VENTA
		AND NV.COD_EMPRESA=E.COD_EMPRESA
		AND WT.COD_WP_TRANSACCION=$TBK_TRANSACCION
		ORDER BY COD_WP_TRANSACCION DESC";
$dbc->query($sql);
$result = $dbc->get_row();

$NOM_EMPRESA 				= $result['NOM_EMPRESA'];
$TBK_FINAL_NUMERO_TARJETA	= $result['TBK_FINAL_NUMERO_TARJETA'];
$TBK_MONTO					= $result['MONTO'];
$FECHA_TRANSACCION			= $result['FECHA_TRANSACCION'];
$HORA_TRANSACCION			= $result['HORA_TRANSACCION'];
$TBK_CODIGO_AUTORIZACION	= $result['TBK_CODIGO_AUTORIZACION'];
$TBK_TIPO_PAGO				= $result['TBK_TIPO_PAGO'];
$TBK_NUMERO_CUOTAS			= $result['TBK_NUMERO_CUOTAS'];
$COD_NOTA_VENTA				= $result['COD_NOTA_VENTA'];

if($TBK_TIPO_PAGO=='VD'){
	$tipo_pago = 'Red Compra';
	$tipo_cuotas = 'Debito';
}
else if($TBK_TIPO_PAGO=='NC'){
	$tipo_pago = 'Credito';
	$tipo_cuotas = 'Sin interes';
}
else if($TBK_TIPO_PAGO=='SI'){
	$tipo_pago = 'Credito';
	$tipo_cuotas = '3 Cuotas Sin interes';
}
else if($TBK_TIPO_PAGO=='S2'){
	$tipo_pago = 'Credito';
	$tipo_cuotas = '2 Cuotas Sin interes';
}
else if($TBK_TIPO_PAGO=='VC'){
	$tipo_pago = 'Credito';
	$tipo_cuotas = 'Cuotas normales';
}
else if($TBK_TIPO_PAGO=='VN'){
	$tipo_pago = 'Credito';
	$tipo_cuotas = 'Sin Cuotas';
}
//href="biggi.integrasystem.cl:8080/biggi_comercial/trunk/appl/nota_venta/COMERCIAL/pago_nota_venta_confirmacion.php?param=<?php echo base64_encode($TBK_TRANSACCION);
/*?>"><?php echo $COD_NOTA_VENTA;?></td>*/
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>Sistema Grupo de Empresas Biggi</title>
		<link href="{W_CSS}" rel="stylesheet" type="text/css">
		<style type="text/css">
			{WI_JAVA_SCRIPT}
			<!--
			body {
				background-color: #E5E5E5;
				text-align: center;
				font-size: 14px;
				font-family: Verdana, Arial, Helvetica, sans-serif;
			}
			-->
		</style>
	</head>
	<body>
		<!-- DO NOT MOVE! The following AllWebMenus linking code section must always be placed right AFTER the BODY tag-->
		<!-- ******** BEGIN ALLWEBMENUS CODE FOR menu ******** -->
		<script type="text/javascript">var MenuLinkedBy="AllWebMenus [4]",awmMenuName="menu",awmBN="740";awmAltUrl="";</script>
		<script charset="UTF-8" src="{K_ROOT_URL}menu/menu.js" type="text/javascript"></script>
		<script type="text/javascript">{W_MENU}</script>
		<style type="text/css">
			.btn {
				background: #85888a;
				background-image: -webkit-linear-gradient(top, #85888a, #797d80);
				background-image: -moz-linear-gradient(top, #85888a, #797d80);
				background-image: -ms-linear-gradient(top, #85888a, #797d80);
				background-image: -o-linear-gradient(top, #85888a, #797d80);
				background-image: linear-gradient(to bottom, #85888a, #797d80);
				-webkit-border-radius: 28;
				-moz-border-radius: 28;
				border-radius: 28px;
				font-family: Arial;
				color: #fafafa;
				font-size: 20px;
				padding: 10px 20px 10px 20px;
				text-decoration: none;
			}
			.btn:hover {
				background: #e8eaeb;
				background-image: -webkit-linear-gradient(top, #e8eaeb, #969b9e);
				background-image: -moz-linear-gradient(top, #e8eaeb, #969b9e);
				background-image: -ms-linear-gradient(top, #e8eaeb, #969b9e);
				background-image: -o-linear-gradient(top, #e8eaeb, #969b9e);
				background-image: linear-gradient(to bottom, #e8eaeb, #969b9e);
				text-decoration: none;
			}
			.encabezado_right {
				background-color: #919191;
				font-size: 11px;
				font-weight: bold;
				color: #FFF;
				text-align: right;
				height: 23px;
			}
			.claro {
				background-color: #EAEAEA;
				font-size: 11px;
				font-weight: 100;
				color: #000000;
				height: 23px;
			}
			.oscuro {
				background-color: #D8D8D8;
				font-size: 11px;
				font-weight: 100;
				color: #000;
				height: 23px;
			}
			.letra_final {
				font-size: 11px;
				font-weight: 100;
				color: #000000;
				height: 23px;
			}
		</style>
		<!-- ******** END ALLWEBMENUS CODE FOR menu ******** -->
		<center>
			<table width="10%">
				<tr>
					<td colspan="3">
						<center>
							<img sr="http://accsisgb.biggi.cl/sysbiggi_new/biggi_comercial/trunk/appl/nota_venta/webpay/Header_venta_biggi.png" widht="100%"/> 
							<font color="red">
								<h3>Transaccion APROBADA</h3>
							</font>
						</center>
					</td>
				</tr>
				<tr>
					<table width="50%" height="300" valign="center" rules="none" border="1">
						<tr class="encabezado_right">
							<td width="15%" align="left">Numero orden compra:</td>
							<td width="35%" class="claro" align="left"><?php echo $TBK_TRANSACCION;?></td>
						</tr>
						<tr class="encabezado_right">
							<td width="15%" align="left">Comprador:</td>
							<td width="35%" class="oscuro" align="left"><?php echo $NOM_EMPRESA;?></td>
						</tr>
						<tr class="encabezado_right">
							<td width="15%" align="left">Numero de Tarjeta:</td>
							<td width="35%" class="claro" align="left">XXXX-XXXX-XXXX-<?php echo $TBK_FINAL_NUMERO_TARJETA;?></td>
						</tr>
						<tr class="encabezado_right">
							<td width="15%" align="left">Monto $:</td>
							<td width="35%" class="oscuro" align="left"><?php echo '$ '.$TBK_MONTO;?></td>
						</tr>
						<tr class="encabezado_right">
							<td width="15%" align="left">Fecha Transaccion:</td>
							<td width="35%" class="claro" align="left"><?php echo $FECHA_TRANSACCION;?></td>
						</tr>
						<tr class="encabezado_right">
							<td width="15%" align="left">Hora Transaccion:</td>
							<td width="35%" class="oscuro" align="left"><?php echo $HORA_TRANSACCION;?></td>
						</tr>
						<tr class="encabezado_right">
							<td width="15%" align="left">Codigo de Autorizacion:</td>
							<td width="35%" class="claro" align="left"><?php echo $TBK_CODIGO_AUTORIZACION;?></td>
						</tr>
						<tr class="encabezado_right">
							<td width="15%" align="left">Tipo de Transaccion:</td>
							<td width="35%" class="oscuro" align="left">Venta</td>
						</tr>
						<tr class="encabezado_right">
							<td width="15%" align="left">Tipo de Pago:</td>
							<td width="35%" class="claro" align="left"><?php echo $tipo_pago ;?></td>
						</tr>
						<tr class="encabezado_right">
							<td width="15%" align="left">Tipo de Cuotas:</td>
							<td width="35%" class="oscuro" align="left"><?php echo $tipo_cuotas;?></td>
						</tr>
						<tr class="encabezado_right">
							<td width="15%" align="left">Numero de Cuotas:</td>
							<td width="35%" class="claro" align="left"><?php echo $TBK_NUMERO_CUOTAS;?></td>
						</tr>
						<tr class="encabezado_right">
							<td width="15%" align="left">Descripcion:</td>
							<td width="35%" class="oscuro" align="left">Pago de la Nota Venta <a href="http://biggi.cl/confirm?param=<?php echo base64_encode($TBK_TRANSACCION);?>"><?php echo $COD_NOTA_VENTA;?></td>
						</tr>
						<tr class="encabezado_right">
							<td width="15%" align="left">Url del Sitio:</td>
							<td width="35%" class="claro" align="left"><a
								href="http://www.biggi.cl"> www.biggi.cl</a></td>
						</tr>
					</table>
				</tr>
				<tr>
					<td>
						<br/>
						<table width="50%" valign="center" rules="none" border="0">
							<tr class="letra_final">
								<td align="justify">
									<h3>Gracias por preferir BIGGI. Se ha enviado un correo de confirmacion a <?php echo $result_mail[0]['EMAIL'];?></h3>
								</td>
							</tr>
							<tr class="letra_final">
								<td align="center">
									<h3>Gracias por preferir BIGGI</h3>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<table width="50%" valign="center" rules="none">
						<tr>
							<td align="justify">
								<font size="1">"El cliente podra retractarse de
									la compra, dentro de los 10 dias siguientes, desde que recibio el
									producto, siempre y cuando el artefacto, se encuentre sin uso, y no
									se haya deteriorado por un hecho imputable al comprador. Ademas
									debera restituir en buen estado los elementos originales del
									embalaje, con las etiquetas, certificado de garantia, manuales de
									uso, cajas, elementos de proteccion y sus accesorios respectivos.
									Para ejercer este derecho debera presentarse con el producto y su
									respectiva factura, en nuestras dependencias, ubicadas en Portugal
									1726 - Santiago Centro, de lunes a viernes de 08:30 a 13:30 hrs. y
									de 14:15 a 18:00 hrs. El costo del flete sera asumido integramente
									por el comprador."</font> <font size=1>En caso de tener alguna duda
									favor de acceder <a href="http://accsisgb.biggi.cl/sysbiggi_new/biggi_web/biggi_web/trunk/contacto.php">AQUI</a>.
								</font>
							</td>
						</tr>
					</table>
				</tr>
				<tr>
					<br/>
					<td width="7%"></td>
					<td align="center" height="40" valign="center">Volver a <a href="http://www.biggi.cl"> www.biggi.cl</a></td>
				</tr>
				<tr>
					<table width="50%" valign="center" rules="none">
						<tr class="letra_final">
							<td align="left">
								<p>(*) Los acentos y caracteres especiales fueron omitidos para su
								correcta lectura en cualquier medio electronico</p>
							</td>
						</tr>
					</table>
				</tr>
			</table>
		</center>
	</body>
</html>
