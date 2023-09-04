CREATE PROCEDURE sp_lista_nc_compra(@ve_cod_nota_venta numeric
									,@ve_nro_orden_compra	numeric
									,@ve_monto_orden_compra	numeric
									,@ve_cod_empresa		numeric
									,@ve_nro_factura		numeric
									,@ve_monto_factura		numeric
									,@ve_nro_nota_credito	numeric
									,@ve_monto_nota_credito	numeric)
AS
BEGIN
	DECLARE @vl_fecha_actual DATETIME = GETDATE()

	INSERT INTO REBAJA_COMPRA_NETA_NV(FECHA_REGISTRO,
									COD_NOTA_VENTA,
									COD_ORDEN_COMPRA,
									MONTO_ORDEN_COMPRA,
									COD_EMPRESA,
									NRO_FACTURA,
									MONTO_FACTURA,
									NRO_NOTA_CREDITO,
									MONTO_NOTA_CREDITO)
							  VALUES(@vl_fecha_actual
							        ,@ve_cod_nota_venta
									,@ve_nro_orden_compra
									,@ve_monto_orden_compra
									,@ve_cod_empresa
									,@ve_nro_factura
									,@ve_monto_factura
									,@ve_nro_nota_credito
									,@ve_monto_nota_credito)

END