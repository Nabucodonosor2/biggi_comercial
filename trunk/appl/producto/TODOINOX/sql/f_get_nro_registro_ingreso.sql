CREATE function f_get_nro_registro_ingreso(@vc_cod_producto varchar(30))
RETURNS numeric(10,0) 
AS
BEGIN
		declare 
		@vl_numero_registro_ingreso numeric(10,0)
		
		SELECT @vl_numero_registro_ingreso = MAX(NUMERO_REGISTRO_INGRESO) 
		FROM ITEM_REGISTRO_4D 
		WHERE MODELO = @vc_cod_producto
			
		return @vl_numero_registro_ingreso;

END