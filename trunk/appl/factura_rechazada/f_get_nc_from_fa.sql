ALTER FUNCTION f_get_nc_from_fa(@ve_cod_factura NUMERIC)	
RETURNS NUMERIC
AS
BEGIN
DECLARE @vl_nro_nota_credito	NUMERIC
	
	SELECT @vl_nro_nota_credito = NRO_NOTA_CREDITO
	FROM NOTA_CREDITO
	WHERE COD_DOC = @ve_cod_factura
	AND COD_ESTADO_DOC_SII IN (2, 3)
	
	RETURN @vl_nro_nota_credito
END