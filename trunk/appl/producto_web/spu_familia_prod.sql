CREATE PROCEDURE spu_familia_prod
		(@ve_operacion 				varchar(20)
		,@ve_cod_familia_producto	numeric
		,@ve_nom_familia_producto	varchar(1000)=NULL
		,@ve_cod_familia			numeric=NULL
		,@ve_cod_producto			varchar(30)=NULL
		,@ve_orden					numeric=NULL)
AS
BEGIN
		if (@ve_operacion='INSERT') 
		begin
			insert into FAMILIA_PRODUCTO
							(NOM_FAMILIA_PRODUCTO
							,ORDEN
							,COD_FAMILIA
							,COD_PRODUCTO)
			values 			(@ve_nom_familia_producto
							,@ve_orden
							,@ve_cod_familia
							,@ve_cod_producto)
		end 
	else if (@ve_operacion='UPDATE') 
		begin
			update FAMILIA_PRODUCTO
			set		NOM_FAMILIA_PRODUCTO	= @ve_nom_familia_producto
					,ORDEN					= @ve_orden
					,COD_FAMILIA			= @ve_cod_familia
					,COD_PRODUCTO			= @ve_cod_producto
			where	COD_FAMILIA_PRODUCTO	= @ve_cod_familia_producto	
		end 
	else if(@ve_operacion='DELETE') 
		begin
			delete FAMILIA_PRODUCTO
			where	COD_FAMILIA_PRODUCTO	= @ve_cod_familia_producto
		end 

END