<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

//////////////////////
$archivo = array( new item_menu('Cambio password', '0505', "../appl/login/change_password.php"),
				new item_menu('-'),
				new item_menu('Mantencion', '0507', "../../../commonlib/trunk/php/mantenedor.php?modulo=mantencion_sw&cod_item_menu=0507"),
				new item_menu('Informe Mantención', '0508', "../../../commonlib/trunk/php/informe.php?informe=inf_mantencion_sw&cod_item_menu=0508"),
				new item_menu('Contactos', '0509', "../../../commonlib/trunk/php/mantenedor.php?modulo=llamado&cod_item_menu=0509"),
				new item_menu('-'),
				new item_menu('Salir', '0510', "../../../commonlib/trunk/php/cerrar_sesion.php"));
				
$maestro = array( new item_menu('Empresas', '1005', "../../../commonlib/trunk/php/mantenedor.php?modulo=empresa&cod_item_menu=1005"),
				new item_menu('Productos', '1010', "../../../commonlib/trunk/php/mantenedor.php?modulo=producto&cod_item_menu=1010"),
				new item_menu('-'),
				new item_menu('Zona Web', '1011', "../../../commonlib/trunk/php/mantenedor.php?modulo=zona_web&cod_item_menu=1011"),
				new item_menu('Familia Web', '1012', "../../../commonlib/trunk/php/mantenedor.php?modulo=familia_web&cod_item_menu=1012"),
				new item_menu('Productos Web', '1013', "../../../commonlib/trunk/php/mantenedor.php?modulo=producto_web&cod_item_menu=1013"),
				new item_menu('Parámetros Web', '1014', "../appl/parametro_web/wi_parametro_web.php?cod_item_menu=1014"),
				new item_menu('-'),
				new item_menu('Usuarios', '1015', "../../../commonlib/trunk/php/mantenedor.php?modulo=usuario&cod_item_menu=1015"),
				new item_menu('Perfiles', '1020', "../../../commonlib/trunk/php/mantenedor.php?modulo=perfil&cod_item_menu=1020"),
				new item_menu('-'),
				new item_menu('Parámetros', '1025', "../appl/parametro/wi_parametro.php?cod_item_menu=1025"));

$ventas = array(new item_menu('Solicitud Cotización', '1550', "../../../commonlib/trunk/php/mantenedor.php?modulo=solicitud_cotizacion&cod_item_menu=1550"),
				new item_menu('Seguimiento Cotización', '1508', "../../../commonlib/trunk/php/mantenedor.php?modulo=seguimiento_cotizacion&cod_item_menu=1508"),
				new item_menu('Cotización', '1505', "../../../commonlib/trunk/php/mantenedor.php?modulo=cotizacion&cod_item_menu=1505"),
				new item_menu('Bitácora Cotización', '1507', "../../../commonlib/trunk/php/mantenedor.php?modulo=bitacora_cotizacion&cod_item_menu=1507"),
				new item_menu('Nota Venta', '1510', "../../../commonlib/trunk/php/mantenedor.php?modulo=nota_venta&cod_item_menu=1510"),
				//new item_menu('Visita Técnica', '1515', "../../../commonlib/trunk/no_implementado.php"), = VM 13/07/2010 dice no VA
				new item_menu('Orden Compra', '1520', "../../../commonlib/trunk/php/mantenedor.php?modulo=orden_compra&cod_item_menu=1520"),
				//new item_menu('-'),
				new item_menu('Guía Despacho', '1525', "../../../commonlib/trunk/php/mantenedor.php?modulo=guia_despacho&cod_item_menu=1525"),
				new item_menu('Guía Recepción', '1530', "../../../commonlib/trunk/php/mantenedor.php?modulo=guia_recepcion&cod_item_menu=1530"),
				new item_menu('Factura', '1535', "../../../commonlib/trunk/php/mantenedor.php?modulo=factura&cod_item_menu=1535"),
				new item_menu('Nota Crédito', '1540', "../../../commonlib/trunk/php/mantenedor.php?modulo=nota_credito&cod_item_menu=1540"));				//new item_menu('Nota Débito', '1545', "../../../commonlib/trunk/php/mantenedor.php?modulo=nota_debito&cod_item_menu=1545"),
				//new item_menu('-'),
				//new item_menu('Demo', '1555', "../appl/demo/wo_demo.htm"));
				
$arriendo = array(new item_menu('Arriendo Cotizacion', '2005', "../../../commonlib/trunk/php/mantenedor.php?modulo=cotizacion_arriendo&cod_item_menu=2005"),
				new item_menu('Contrato Arriendo', '2010', "../../../commonlib/trunk/php/mantenedor.php?modulo=arriendo&cod_item_menu=2010"),
				new item_menu('Modificación Contrato', '2015', "../../../commonlib/trunk/php/mantenedor.php?modulo=mod_arriendo&cod_item_menu=2015"),
				new item_menu('Orden Compra', '2020', "../../../commonlib/trunk/php/mantenedor.php?modulo=orden_compra_arriendo&cod_item_menu=2020"),
				new item_menu('Guía Despacho', '2025', "../../../commonlib/trunk/php/mantenedor.php?modulo=guia_despacho_arriendo&cod_item_menu=2025"),
				new item_menu('Guía Recepción', '2030', "../../../commonlib/trunk/php/mantenedor.php?modulo=guia_recepcion_arriendo&cod_item_menu=2030"),
				new item_menu('Factura', '2035', "../../../commonlib/trunk/php/mantenedor.php?modulo=factura_arriendo&cod_item_menu=2035"),
				new item_menu('Nota Crédito', '2040', "../../../commonlib/trunk/php/mantenedor.php?modulo=nota_credito_arriendo&cod_item_menu=2040")
				,new item_menu('-')
				//new item_menu('Bodega', '2045', "../../../commonlib/trunk/php/mantenedor.php?modulo=bodega&cod_item_menu=2045")
				,new item_menu('Entrada bodega', '2050', "../../../commonlib/trunk/php/mantenedor.php?modulo=entrada_bodega&cod_item_menu=2050")
				,new item_menu('Salida bodega', '2055', "../../../commonlib/trunk/php/mantenedor.php?modulo=salida_bodega&cod_item_menu=2055")
				//,new item_menu('Traspaso entre bodega', '2060', "../../../commonlib/trunk/php/mantenedor.php?modulo=traspaso_bodega&cod_item_menu=2060")
				);
				
$administracion = array( new item_menu('Ingreso Pago', '2505', "../../../commonlib/trunk/php/mantenedor.php?modulo=ingreso_pago&cod_item_menu=2505"),
				new item_menu('Bitácora Cobranza', '2510', "../../../commonlib/trunk/php/mantenedor.php?modulo=bitacora_factura&cod_item_menu=2510"),
				new item_menu('-'),
				new item_menu('Depósito', '2515', "../../../commonlib/trunk/php/mantenedor.php?modulo=deposito&cod_item_menu=2515"),
				new item_menu('-'),
				new item_menu('Asignación Documentos', '2520', "../../../commonlib/trunk/php/mantenedor.php?modulo=asig_nro_doc_sii&cod_item_menu=2520"),
				new item_menu('-'),
				new item_menu('Participación Nota Venta', '2527', "../../../commonlib/trunk/php/mantenedor.php?modulo=orden_pago&cod_item_menu=2527"),
				new item_menu('Pago Participación', '2528', "../../../commonlib/trunk/php/mantenedor.php?modulo=participacion&cod_item_menu=2528"),
				new item_menu('-'),
				new item_menu('FA Proveedor', '2525', "../../../commonlib/trunk/php/mantenedor.php?modulo=faprov&cod_item_menu=2525"),
				new item_menu('NC Proveedor', '2526',"../../../commonlib/trunk/php/mantenedor.php?modulo=ncprov&cod_item_menu=2526"),
				new item_menu('Pago Proveedor', '2530', "../../../commonlib/trunk/php/mantenedor.php?modulo=pago_faprov&cod_item_menu=2530"),
				new item_menu('Pago Directorio', '2535', "../../../commonlib/trunk/php/mantenedor.php?modulo=pago_directorio&cod_item_menu=2535"),
				new item_menu('-'),
				new item_menu('Traspaso Softland', '2545', "../../../commonlib/trunk/php/mantenedor.php?modulo=envio_softland&cod_item_menu=2545"),
				new item_menu('-'),
				new item_menu('Gasto Fijo', '2550', "../../../commonlib/trunk/php/mantenedor.php?modulo=gasto_fijo&cod_item_menu=2550"),
				
				new item_menu('Cheque Rental', '2560', "../appl/cheque_renta/cheque_renta.php"));
																				
$bodega = array( new item_menu('Entrada', '3015', "../../../commonlib/trunk/php/mantenedor.php?modulo=entrada_bodega&cod_item_menu=3015")
				,new item_menu('Salida', '3020', "../../../commonlib/trunk/php/mantenedor.php?modulo=salida_bodega&cod_item_menu=3020")
				,new item_menu('-')
				,new item_menu('Inventario', '3025', "../../../commonlib/trunk/php/informe.php?informe=inf_bodega_inventario&cod_item_menu=3025")
				,new item_menu('Inventario Valorizado', '3030', "../../../commonlib/trunk/php/informe.php?informe=inf_bodega_stock&cod_item_menu=3030")
				,new item_menu('Tarjeta Existencia', '3035', "../../../commonlib/trunk/php/informe.php?informe=inf_bodega_tarjeta_existencia&cod_item_menu=3035")
				);
								
$informes = array(new item_menu('Ventas por Mes', '4005', "../appl/inf_ventas_por_mes/inf_ventas_por_mes.php"),
				new item_menu('Facturas por Equipo', '4015', "../appl/inf_ventas_por_equipo/inf_ventas_por_equipo.php"),
				new item_menu('Equipos por despachar', '4017', "../../../commonlib/trunk/php/mantenedor.php?modulo=inf_por_despachar&cod_item_menu=4017"),
				new item_menu('GD por facturar', '4020', "../../../commonlib/trunk/php/mantenedor.php?modulo=inf_guia_despacho_por_facturar&cod_item_menu=4020"),				
				new item_menu('Facturas por Cobrar', '4035', "../../../commonlib/trunk/php/mantenedor.php?modulo=inf_facturas_por_cobrar&cod_item_menu=4035"),
				new item_menu('Facturas por Cliente', '4060', "../appl/inf_facturas_por_cliente/inf_facturas_por_cliente.php"),
				new item_menu('Facturas por Mes', '4065', "../appl/inf_facturas_por_mes/inf_facturas_por_mes.php"),
				new item_menu('Informe Resultado', '4070', "../appl/inf_resultado/inf_resultado.php"),
				new item_menu('OC Pendientes por Facturar desde TDNX', '4095', "../../../commonlib/trunk/php/mantenedor.php?modulo=inf_oc_por_facturar_tdnx&cod_item_menu=4095"),
				new item_menu('OC Pendientes por Facturar desde Bodega Biggi', '4097', "../../../commonlib/trunk/php/mantenedor.php?modulo=inf_oc_por_facturar_bodega&cod_item_menu=4097"),
				new item_menu('Informe Cheque a fecha', '4098', "../../../commonlib/trunk/php/mantenedor.php?modulo=inf_cheque_fecha&cod_item_menu=4098"),
				new item_menu('Informe Pre-IVA', '4099', "../appl/inf_pre_iva/inf_pre_iva.php")
				);
				
$menu = new menu(array(new item_menu('Archivo', '05', '', $archivo), 
						new item_menu('Maestros', '10', '', $maestro),
						new item_menu('Ventas', '10', '', $ventas),
						new item_menu('Administración', '10', '', $administracion),
						new item_menu('Bodega', '10', '', $bodega),
						new item_menu('Informes', '10', '', $informes))
				,280);
?>