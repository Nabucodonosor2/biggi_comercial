CREATE PROCEDURE spu_detalle_abono_tbk( @ve_operacion					varchar(20),
										@ve_cod_detalle_abono_tbk		numeric=null,
										@ve_cod_ingreso_pago			numeric=null,
										@ve_cod_doc_ingreso_pago		numeric=null,
										@ve_fecha_doc_ingreso_pago		datetime=null,
										@ve_nro_doc						numeric=null,
										@ve_monto_doc					numeric=null,
										@ve_monto_cuota					numeric=null,
										@ve_cuotas_totales				numeric=null,
										@ve_cuotas_pendientes			numeric=null,
										@ve_fecha_abono					datetime=null,
										@ve_cod_usuario					numeric=null,
										@ve_cod_tipo_doc_pago			numeric=null)
AS
BEGIN
	if(@ve_operacion = 'INSERT')BEGIN
		INSERT INTO DETALLE_ABONO_TBK  ([COD_INGRESO_PAGO],
										[COD_DOC_INGRESO_PAGO],
										[FECHA_DOC_INGRESO_PAGO],
										[NRO_DOC],
										[MONTO_DOC],
										[MONTO_CUOTA],
										[CUOTAS_TOTALES],
										[CUOTAS_PENDIENTES],
										[FECHA_ABONO],
										[COD_USUARIO],
										[COD_TIPO_DOC_PAGO])
								VALUES (@ve_cod_ingreso_pago,
										@ve_cod_doc_ingreso_pago,
										@ve_fecha_doc_ingreso_pago,
										@ve_nro_doc,
										@ve_monto_doc,
										@ve_monto_cuota,
										@ve_cuotas_totales,
										@ve_cuotas_pendientes,
										@ve_fecha_abono,
										@ve_cod_usuario,
										@ve_cod_tipo_doc_pago)
	END 
END