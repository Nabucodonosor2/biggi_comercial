alter PROCEDURE [dbo].[spu_familia_web](@ve_operacion varchar(20)
									  ,@ve_cod_familia numeric = NULL
									  ,@ve_nom_familia varchar(100)=NULL
									  ,@ve_econoline varchar(1)=NULL
									  ,@ve_nom_publica varchar(100)=NULL
									  ,@ve_es_subfamilia varchar(1)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
	
	insert into familia (nom_familia
					    ,econoline
					    ,nom_publico
					    ,es_subfamilia)
				 values (@ve_nom_familia
			 			,@ve_econoline
			 			,@ve_nom_publica
			 			,@ve_es_subfamilia)
	end
	if (@ve_operacion='UPDATE') begin
		
	update familia 
	set nom_familia = @ve_nom_familia
		,econoline =  @ve_econoline
		,nom_publico = @ve_nom_publica
		,es_subfamilia = @ve_es_subfamilia
    where cod_familia = @ve_cod_familia
    
	end
	else if (@ve_operacion='DELETE') begin
	delete familia 
    where cod_familia = @ve_cod_familia
	end		
END
go