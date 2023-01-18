ALTER FUNCTION f_get_reFA(@ve_cod_factura		NUMERIC
						  ,@ve_cod_nota_venta	NUMERIC
						  ,@ve_total_con_iva	NUMERIC)	
RETURNS NUMERIC
AS
BEGIN
DECLARE @vl_nro_factura	NUMERIC
	
	SELECT top 1 @vl_nro_factura = NRO_FACTURA
	FROM FACTURA
	WHERE COD_DOC = @ve_cod_nota_venta
	and  COD_ESTADO_DOC_SII in (2, 3)
	and COD_FACTURA <> @ve_cod_factura
	and TOTAL_CON_IVA = @ve_total_con_iva
	ORDER BY FECHA_FACTURA ASC
	
	RETURN @vl_nro_factura
END
	