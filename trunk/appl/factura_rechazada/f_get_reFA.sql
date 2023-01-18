CREATE FUNCTION f_get_reFA(@ve_cod_factura NUMERIC
						  ,@ve_cod_nota_venta NUMERIC)	
RETURNS NUMERIC
AS
BEGIN
DECLARE @vl_nro_factura	NUMERIC
	
	SELECT TOP 1 @vl_nro_factura = NRO_FACTURA
	FROM FACTURA
	WHERE COD_DOC = @ve_cod_nota_venta
	and  COD_ESTADO_DOC_SII in (2, 3)
	and COD_FACTURA <> @ve_cod_factura
	ORDER BY FECHA_FACTURA ASC
	
	RETURN @vl_nro_factura
END