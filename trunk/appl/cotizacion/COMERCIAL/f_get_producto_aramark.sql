------------------f_get_precio_aramark----------------
CREATE FUNCTION f_get_producto_aramark(@ve_cod_producto				VARCHAR(30)
									  ,@ve_cod_empresa				NUMERIC
									  ,@ve_cod_cotizacion_aramark	NUMERIC
									  ,@ve_cod_cotizacion			NUMERIC)
RETURNS VARCHAR(50)
AS
BEGIN
	--Al cambiar cotizacion base se debe modificar tambien en el procedimiento spu_item_cotizacion en linea 20
	IF(@ve_cod_empresa IN (630, 3109, 5308, 7326) AND @ve_cod_cotizacion >= 244862)BEGIN
		DECLARE @vl_expresion_html	VARCHAR(50)
			   ,@vl_count			NUMERIC
	
		SELECT @vl_count = ISNULL(COUNT(*), 0)
		FROM ITEM_COTIZACION 
		WHERE COD_COTIZACION = @ve_cod_cotizacion_aramark -- cotizacion aramark
		AND COD_PRODUCTO = @ve_cod_producto

		IF(@vl_count > 0)
			SET @vl_expresion_html = 'style="background: #ffb0b0;"'
		ELSE
			SET @vl_expresion_html = ''
	END
	ELSE
		SET @vl_expresion_html = ''
	
	RETURN @vl_expresion_html
END