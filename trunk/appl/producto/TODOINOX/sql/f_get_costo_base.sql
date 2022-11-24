create function [dbo].[f_get_costo_base](@vc_cod_producto varchar(30))
RETURNS numeric(10,2) 
AS
BEGIN
		declare 
			@vl_precio_x_factor numeric(10,2),
			@vl_dolar numeric(10,2),
			@vl_factor_venta_int numeric(10,2),
			@vl_costo_base	numeric(10,2),
			@vl_numero_registro_ingreso numeric,
			@vl_dolar_param numeric
		
		SELECT @vl_numero_registro_ingreso = MAX(NUMERO_REGISTRO_INGRESO) 
		FROM ITEM_REGISTRO_4D WHERE MODELO  = @vc_cod_producto
		
		
		SELECT @vl_precio_x_factor =  IT.PRECIO  * RI.FACTOR_IMP
		FROM ITEM_REGISTRO_4D IT , REGISTRO_INGRESO_4D RI 
		WHERE IT.NUMERO_REGISTRO_INGRESO = RI.NUMERO_REGISTRO_INGRESO 
		AND RI.NUMERO_REGISTRO_INGRESO = @vl_numero_registro_ingreso
		
		set @vl_dolar_param = 5
		
		SELECT  @vl_dolar = dbo.f_get_parametro(@vl_dolar_param)
		FROM PRODUCTO 
		WHERE COD_PRODUCTO = @vc_cod_producto

		SELECT  @vl_costo_base = round(@vl_precio_x_factor * @vl_dolar,2)
		
		return @vl_costo_base;
END
