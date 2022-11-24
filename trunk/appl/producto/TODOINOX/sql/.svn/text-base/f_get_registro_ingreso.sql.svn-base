create function [dbo].[f_get_registro_ingreso](@vc_cod_producto varchar(30),@vc_valor varchar(1))
RETURNS numeric(10,2) 
AS
BEGIN
		declare 
		@vl_numero_registro_ingreso numeric(10,0), 
		@vl_precio					numeric(10,2),
		@vl_factor_imp				numeric(10,2)
		
		SELECT @vl_numero_registro_ingreso = MAX(NUMERO_REGISTRO_INGRESO) 
		FROM ITEM_REGISTRO_4D 
		WHERE MODELO = @vc_cod_producto
		
		
		
		IF(@vc_valor = 'P')BEGIN 
			
			SELECT @vl_precio = PRECIO 
			FROM ITEM_REGISTRO_4D 
			WHERE NUMERO_REGISTRO_INGRESO = @VL_NUMERO_REGISTRO_INGRESO
		
			return @vl_precio;
		END
		ELSE IF(@vc_valor = 'F')BEGIN
		
			SELECT @vl_factor_imp =  FACTOR_IMP 
			FROM REGISTRO_INGRESO_4D 
			WHERE NUMERO_REGISTRO_INGRESO = @VL_NUMERO_REGISTRO_INGRESO
			
			return @vl_factor_imp;
		END
		RETURN '';
END