alter PROCEDURE [dbo].[spu_zona_web](@ve_operacion varchar(20)
									  ,@ve_cod_zona numeric = NULL
									  ,@ve_nom_zona varchar(100)=NULL
									  ,@ve_orden numeric =NULL
									  ,@ve_econoline varchar(1)=NULL
									  ,@ve_publica_web varchar(1)=NULL)
AS
BEGIN
	declare 
		@vl_cod_zona numeric

	if (@ve_operacion='INSERT') begin
	
	select @vl_cod_zona = max(COD_ZONA) + 1
	from ZONA 
	
	insert into zona (cod_zona
					  ,nom_zona
					  ,orden
					  ,econoline
					  ,publica_web)
			 values (@vl_cod_zona
			 		,@ve_nom_zona
			 		,@ve_orden
			 		,@ve_econoline
			 		,@ve_publica_web)
	end
	if (@ve_operacion='UPDATE') begin
		
	update zona 
	set nom_zona = @ve_nom_zona
		,orden = @ve_orden
		,econoline = @ve_econoline
		,publica_web = @ve_publica_web
    where cod_zona = @ve_cod_zona
    
	end
	else if (@ve_operacion='DELETE') begin
	delete zona 
    where cod_zona = @ve_cod_zona
	end		
END
go