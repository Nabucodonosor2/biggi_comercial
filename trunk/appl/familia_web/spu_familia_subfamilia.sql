alter PROCEDURE spu_familia_subfamilia(@ve_operacion varchar(20)
										,@ve_cod_familia_subfamilia	numeric
										,@ve_cod_familia	numeric=NULL
										,@ve_cod_subfamilia			varchar(30)=NULL
										,@ve_orden					numeric=NULL)
AS
BEGIN
		if (@ve_operacion='INSERT') 
		begin
			insert into FAMILIA_SUBFAMILIA
							(COD_FAMILIA
							,COD_SUBFAMILIA
							,ORDEN)
			values 			(@ve_cod_familia
							,@ve_cod_subfamilia
							,@ve_orden)
		end 
	else if (@ve_operacion='UPDATE') 
		begin
			update FAMILIA_SUBFAMILIA
			set		COD_FAMILIA	= @ve_cod_familia
					,COD_SUBFAMILIA			= @ve_cod_subfamilia
					,ORDEN					= @ve_orden
			where	COD_FAMILIA_SUBFAMILIA	= @ve_cod_familia_subfamilia
		end 
	else if(@ve_operacion='DELETE') 
		begin
			delete FAMILIA_SUBFAMILIA
			where	COD_FAMILIA_SUBFAMILIA	= @ve_cod_familia_subfamilia
		end 

END