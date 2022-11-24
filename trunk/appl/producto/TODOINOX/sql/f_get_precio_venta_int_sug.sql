------------------f_get_precio_venta_int_sug----------------
create function f_get_precio_venta_int_sug(@vc_cod_producto varchar(30))
RETURNS numeric(10,2) 
AS
BEGIN
		declare 
			@vl_precio_x_factor numeric(10,2),
			@vl_dolar numeric(10,2),
			@vl_factor_venta_int numeric(10,2),
			@vl_precio_venta_int_sug	numeric(10,2),
			@vl_numero_registro_ingreso umeric(10,2)
		
		
		SELECT @vl_numero_registro_ingreso = MAX(NUMERO_REGISTRO_INGRESO) 
		FROM ITEM_REGISTRO_4D WHERE MODELO  = @vc_cod_producto
		
		
		SELECT @vl_precio_x_factor =  IT.PRECIO  * RI.FACTOR_IMP
		FROM ITEM_REGISTRO_4D IT , REGISTRO_INGRESO_4D RI 
		WHERE IT.NUMERO_REGISTRO_INGRESO = RI.NUMERO_REGISTRO_INGRESO 
		AND RI.NUMERO_REGISTRO_INGRESO = @vl_numero_registro_ingreso

		SELECT  @vl_factor_venta_int = FACTOR_VENTA_INTERNO ,
		 @vl_dolar = dbo.f_get_parametro(5)
		FROM PRODUCTO 
		WHERE COD_PRODUCTO = @vc_cod_producto
		
		SELECT  @vl_precio_venta_int_sug = round(@vl_precio_x_factor * (@vl_factor_venta_int * @vl_dolar),2)
	
		return @vl_precio_venta_int_sug;
END
go