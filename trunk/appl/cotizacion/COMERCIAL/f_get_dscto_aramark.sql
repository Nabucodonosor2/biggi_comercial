------------------f_get_dscto_aramark----------------
CREATE FUNCTION f_get_dscto_aramark(@ve_precio_venta_publico	NUMERIC
								   ,@ve_precio					NUMERIC)
RETURNS NUMERIC(18, 1)
AS
BEGIN
	DECLARE @vl_valor_dscto_aramark NUMERIC
	
	SELECT @vl_valor_dscto_aramark = ROUND(CASE 
										WHEN (@ve_precio * 0.75) > @ve_precio_venta_publico THEN
											ABS(100 - (((@ve_precio * 0.75) * 100.0) / @ve_precio_venta_publico))  -- positivo
										ELSE
											-1 * ABS(100 - (((@ve_precio * 0.75) * 100.0) / @ve_precio_venta_publico))  -- negativo
									 END
									 , 1)
	
	RETURN @vl_valor_dscto_aramark
END