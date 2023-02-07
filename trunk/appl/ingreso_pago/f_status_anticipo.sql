ALTER FUNCTION f_status_anticipo(@ve_cod_ingreso_pago NUMERIC)
RETURNS VARCHAR(100)
AS
BEGIN
	DECLARE @vl_status				VARCHAR(100),
		    @vl_cod_ingreso_pago	NUMERIC

	SELECT @vl_cod_ingreso_pago = COD_INGRESO_PAGO
	FROM DOC_INGRESO_PAGO
	WHERE NRO_DOC = @ve_cod_ingreso_pago
	AND COD_TIPO_DOC_PAGO = 9
	AND COD_DOC_INGRESO_PAGO <> 3
	
	if(@vl_cod_ingreso_pago IS NULL)
		set @vl_status = '(Anticipo sin usar al ' + convert(varchar(20), GETDATE(), 103) + ')'
	else
		set @vl_status = '(Anticipo usado en Ingreso de Pago ' + CONVERT(VARCHAR, @vl_cod_ingreso_pago) + ')'

	RETURN @vl_status
END