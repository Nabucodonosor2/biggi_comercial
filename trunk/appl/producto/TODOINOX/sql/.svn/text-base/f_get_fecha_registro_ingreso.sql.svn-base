create function [dbo].[f_get_fecha_registro_ingreso](@vc_cod_producto varchar(30))
RETURNS datetime
AS
BEGIN
		declare 
		@vl_fecha_registro_ingreso datetime
		
		SELECT @vl_fecha_registro_ingreso = FECHA_REGISTRO_INGRESO
		 FROM ITEM_REGISTRO_4D IR,REGISTRO_INGRESO_4D RI
		WHERE IR.NUMERO_REGISTRO_INGRESO = RI.NUMERO_REGISTRO_INGRESO
		  AND IR.MODELO = @vc_cod_producto
			
		return @vl_fecha_registro_ingreso;
END