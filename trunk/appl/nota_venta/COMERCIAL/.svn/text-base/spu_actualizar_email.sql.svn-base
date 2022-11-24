create PROCEDURE spu_actualizar_email(@ve_cod_nota_venta		NUMERIC(10)  = NULL
									,@ve_email				VARCHAR(100)  = NULL
									)								
AS
BEGIN
		declare @vl_cod_persona NUMERIC;
	
		select @vl_cod_persona =  cod_persona from NOTA_VENTA where COD_NOTA_VENTA=@ve_cod_nota_venta;
	
		UPDATE	PERSONA
		  SET	EMAIL	= @ve_email
		WHERE	cod_persona = @vl_cod_persona
END