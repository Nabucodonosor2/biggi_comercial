------------------spu_contacto_persona----------------
ALTER PROCEDURE [dbo].[spu_contacto_telefono](@ve_operacion varchar(20)
									,@ve_cod_contacto_telefono numeric
									,@ve_cod_contacto numeric=NULL
									,@ve_telefono varchar(100)=NULL	
									)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into contacto_telefono (cod_contacto
									 ,nom_contacto_telefono
									 ,telefono)
							 values (@ve_cod_contacto
							 		 ,NULL
							 		 ,@ve_telefono)
		
	end 
	if (@ve_operacion='UPDATE') begin
		update contacto_telefono 
		set telefono = @ve_telefono
	    where cod_contacto_telefono = @ve_cod_contacto_telefono

		
	end
	else if (@ve_operacion='DELETE') begin
		delete contacto_telefono 
    	where cod_contacto_telefono = @ve_cod_contacto_telefono
	end
	
END
go
