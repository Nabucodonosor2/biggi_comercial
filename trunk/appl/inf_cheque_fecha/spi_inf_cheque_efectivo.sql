CREATE PROCEDURE spu_inf_cheque_efectivo(@ve_cod_usuario			NUMERIC
										,@ve_cod_doc_ingreso_pago	NUMERIC
										,@ve_fecha_deposito			DATETIME)
AS
BEGIN
	DECLARE
		@vl_fecha_registro DATETIME = GETDATE()

	INSERT INTO INF_CHEQUE_FECHA_EFECTIVO (FECHA_REGISTRO
										  ,COD_USUARIO
										  ,COD_DOC_INGRESO_PAGO
										  ,FECHA_DEPOSITO)
									VALUES(@vl_fecha_registro
										  ,@ve_cod_usuario
										  ,@ve_cod_doc_ingreso_pago
										  ,@ve_fecha_deposito)
END