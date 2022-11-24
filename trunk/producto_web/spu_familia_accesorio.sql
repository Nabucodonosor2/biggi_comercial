ALTER PROCEDURE [dbo].[spu_familia_accesorio]
		(@ve_operacion varchar(20)
		,@ve_cod_familia_accesorio	numeric
		,@ve_cod_familia_producto	numeric=NULL
		,@ve_cod_producto			varchar(30)=NULL
		,@ve_orden					numeric=NULL
		,@ve_cod_familia			numeric=NULL)
AS
BEGIN
		if (@ve_operacion='INSERT') 
		begin
			insert into FAMILIA_ACCESORIO
							(COD_FAMILIA_PRODUCTO
							,COD_PRODUCTO
							,ORDEN
							,COD_FAMILIA)
			values 			(@ve_cod_familia_accesorio
							,@ve_cod_producto
							,@ve_orden
							,@ve_cod_familia)
		end 
	else if (@ve_operacion='UPDATE') 
		begin
			update FAMILIA_ACCESORIO
			set		COD_FAMILIA_PRODUCTO	= @ve_cod_familia_producto
					,COD_PRODUCTO			= @ve_cod_producto
					,ORDEN					= @ve_orden
					,COD_FAMILIA			= @ve_cod_familia
			where	COD_FAMILIA_ACCESORIO	= @ve_cod_familia_accesorio	
		end 
	else if(@ve_operacion='DELETE') 
		begin
			delete FAMILIA_ACCESORIO
			where	COD_FAMILIA_ACCESORIO	= @ve_cod_familia_accesorio
		end 

END