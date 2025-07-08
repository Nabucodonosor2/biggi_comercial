------------------f_get_precio_aramark----------------
ALTER FUNCTION f_get_precio_aramark(@ve_cod_producto		VARCHAR(30)
								   ,@ve_precio				NUMERIC
								   ,@ve_cod_cotizacion_ara	NUMERIC)
RETURNS NUMERIC
AS
BEGIN
	DECLARE @vl_precio			NUMERIC
	
	SELECT @vl_precio = PRECIO
	FROM ITEM_COTIZACION 
	WHERE COD_COTIZACION = @ve_cod_cotizacion_ara -- cotizacion aramark
	AND COD_PRODUCTO = @ve_cod_producto

	IF(ISNULL(@vl_precio, 0) = 0)
		SET @vl_precio = @ve_precio
	
	RETURN @vl_precio
END