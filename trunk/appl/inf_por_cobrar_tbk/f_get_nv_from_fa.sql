CREATE FUNCTION f_get_nv_from_fa(@ve_cod_factura numeric)
RETURNS numeric
AS
BEGIN
	declare @vl_cod_nota_venta numeric

	SELECT @vl_cod_nota_venta = COD_DOC 
	FROM factura
	WHERE COD_FACTURA = @ve_cod_factura

	return isnull(@vl_cod_nota_venta,0)
END