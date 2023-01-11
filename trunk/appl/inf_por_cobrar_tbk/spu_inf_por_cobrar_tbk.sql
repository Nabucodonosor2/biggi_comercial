ALTER PROCEDURE spu_inf_por_cobrar_tbk(@ve_operacion				varchar(20),
										@ve_cod_inf_por_cobrar_tbk	numeric=null,
										@ve_cod_ingreso_pago		numeric=null,
										@ve_fecha_ingreso_pago		datetime=null,
										@ve_cod_nota_venta			numeric=null,
										@ve_fecha_nota_venta		datetime=null,
										@ve_rut_cliente				numeric=null,
										@ve_dig_verif				varchar(1)=null,
										@ve_razon_social			varchar(100)=null,
										@ve_total_con_iva			numeric=null,
										@ve_monto_debito			numeric=null,
										@ve_monto_credito			numeric=null,
										@ve_cuotas_credito			numeric=null,
										@ve_comision_debito			numeric=null,
										@ve_comision_credito		numeric=null,
										@ve_total_por_cobrar		numeric=null,
										@ve_cod_usuario				numeric=null,
										@ve_cod_doc_ingreso_pago	numeric=null,
										@ve_monto_cuota_credito		numeric=null,
										@ve_cuotas_pendientes		numeric=null)
AS
BEGIN
	if(@ve_operacion = 'INSERT')BEGIN
		INSERT INTO INF_POR_COBRAR_TBK ([COD_INGRESO_PAGO],		[FECHA_INGRESO_PAGO],	[COD_NOTA_VENTA],		[FECHA_NOTA_VENTA],			[RUT_CLIENTE],				[DIG_VERIF],
										[RAZON_SOCIAL],			[TOTAL_CON_IVA],		[MONTO_DEBITO],			[MONTO_CREDITO],			[CUOTAS_CREDITO],			[COMISION_DEBITO],
										[COMISION_CREDITO],		[TOTAL_POR_COBRAR],		[COD_USUARIO],			[COD_DOC_INGRESO_PAGO],		[MONTO_CUOTA_CREDITO],		[CUOTAS_PENDIENTES])
								VALUES (@ve_cod_ingreso_pago,	@ve_fecha_ingreso_pago,	@ve_cod_nota_venta,		@ve_fecha_nota_venta,		@ve_rut_cliente,			@ve_dig_verif,
										@ve_razon_social,		@ve_total_con_iva,		@ve_monto_debito,		@ve_monto_credito,			@ve_cuotas_credito,			@ve_comision_debito,
										@ve_comision_credito,	@ve_total_por_cobrar,	@ve_cod_usuario,		@ve_cod_doc_ingreso_pago,	@ve_monto_cuota_credito,	@ve_cuotas_pendientes)
	END 
END