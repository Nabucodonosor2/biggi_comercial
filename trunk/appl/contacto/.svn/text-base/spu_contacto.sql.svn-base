alter PROCEDURE spu_contacto (@ve_operacion varchar(20)
								,@ve_cod_contacto numeric
								,@ve_nom_contacto varchar(100) = NULL
								,@ve_rut numeric = NULL
								,@ve_dig_verif varchar(1) = NULL
								,@ve_direccion varchar(100) = NULL
								,@ve_cod_ciudad numeric = NULL
								,@ve_cod_comuna numeric = NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into contacto (nom_contacto, rut, dig_verif, direccion, cod_ciudad, cod_comuna)
		values (@ve_nom_contacto, @ve_rut, @ve_dig_verif, @ve_direccion, @ve_cod_ciudad, @ve_cod_comuna)
	end 
	if (@ve_operacion='UPDATE') begin
		update contacto 
		set nom_contacto = @ve_nom_contacto
			,rut = @ve_rut 
			,dig_verif = @ve_dig_verif
			,direccion = @ve_direccion
			,cod_ciudad = @ve_cod_ciudad 
			,cod_comuna = @ve_cod_comuna
	    where cod_contacto = @ve_cod_contacto
	end	
END
go
