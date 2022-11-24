---------------------f_header_porc_facturado------------------
CREATE FUNCTION f_header_porc_facturado(@ve_cod_nota_venta numeric)	
RETURNS numeric
AS
BEGIN
declare @vl_result 			numeric
	   ,@vl_cod_estado_nv	numeric
		
	SELECT @vl_cod_estado_nv = COD_ESTADO_NOTA_VENTA
	FROM NOTA_VENTA
	WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
	AND YEAR(FECHA_NOTA_VENTA) >= 2015 
	
	if(@vl_cod_estado_nv = 2 OR @vl_cod_estado_nv = 4)begin -- Cerrada y Confirmada
	
		SELECT @vl_result = dbo.f_nv_porc_facturado(@ve_cod_nota_venta)
		if(@vl_result < 100)
			return 0
		else
			return 1
		
	end

	return 2
end