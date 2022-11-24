<?php 
$TBK_TRANSACCION= $_POST['orden_compra']; 
?>

<html>
<head>
<title>Transaccion</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
<title>Sistema Grupo de Empresas Biggi</title>
<link href="{W_CSS}" rel="stylesheet" type="text/css">
<script src="../../../../commonlib/trunk/script_js/SpryTabbedPanels.js" type="text/javascript"></script>
<link href="../../../../commonlib/trunk/css/SpryTabbedPanels.css" rel="stylesheet" type="text/css"/>
<script charset="iso-8859-1" src="../../../../commonlib/trunk/script_js/edit_filter.js" type="text/javascript"></script>
<script charset="iso-8859-1" src="../../../../commonlib/trunk/script_js/general.js" type="text/javascript"></script>
<style type="text/css">
<!--
body {
	background-color: #E5E5E5;
	text-align:center;
	font-size:14px;
	font-family:Verdana, Arial, Helvetica, sans-serif;
}

-->
</style>

</head>
<body>

<!-- ******** END ALLWEBMENUS CODE FOR menu ******** -->
<center>
<table width="100%">
	<tr>
		<td colspan="3">
		<center>
			<img src="Header_venta_biggi.png" widht="100%">
			<font color="red"><h3> La transaccion N: <?php echo $TBK_TRANSACCION;?> ha sido rechazada.</h3></font>
		</center>	
		</td>
	</tr>
	<tr>
 		<table width="60%">
			<tr>
				<td>
				<b></b>
				</td>
			</tr>
			<tr>
				<td>
					Las posibles causas de este rechazo son:<br>
					<br>
					<ul>
					<li>Error en el ingreso de los datos de su tarjeta de credito o debito (Fecha y/o codigo de seguridad).</li>
					<li>Su tarjeta de credito o debito no cuenta con los fondos suficientes para cancelar la compra.</li> 
					<li>Tarjeta aun no habilitada para operar en el sistema financiero.</li> 
					</ul>
				</td>
			</tr>
			<tr>
				<td>Volver a <a href="http://www.biggi.cl"> www.biggi.cl</a></td> 
			</tr>
		</table>
	</tr>
	<tr>
		<p>(*)Los acentos y caracteres especiales fueron omitidos para su correcta lectura en cualquier medio electronico</p>
	</tr>
</table>	
</center>
</body>
</html>