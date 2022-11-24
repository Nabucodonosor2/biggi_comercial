-------------------- spu_zona_familia_web---------------------------------
CREATE PROCEDURE [dbo].[spu_zona_familia_web](@ve_operacion varchar(20)
											  ,@ve_cod_zona_familia numeric
											  ,@ve_cod_zona numeric = NULL
											  ,@ve_cod_familia numeric = NULL
											  ,@ve_orden numeric = NULL)
									  
AS
BEGIN
	if (@ve_operacion='INSERT') begin
	
		insert into zona_familia (cod_zona
					  			  ,cod_familia
					  			  ,orden)
			 			  values (@ve_cod_zona
			 					  ,@ve_cod_familia
			 					  ,@ve_orden)
			 					  
	end
	if (@ve_operacion='UPDATE') begin
		
	update zona_familia 
	set cod_zona = @ve_cod_zona
		,cod_familia = @ve_cod_familia
		,orden = @ve_orden
    where cod_zona_familia = @ve_cod_zona_familia
    
	end
	else if (@ve_operacion='DELETE') begin
	delete zona_familia 
    where cod_zona_familia = @ve_cod_zona_familia
	end		
END
go