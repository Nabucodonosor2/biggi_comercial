------------------spu_contacto_persona----------------
alter PROCEDURE spu_contacto_persona(@ve_operacion varchar(20)
									,@ve_cod_contacto_persona numeric
									,@ve_cod_contacto numeric=NULL
									,@ve_nom_persona varchar(100)=NULL
									,@ve_mail varchar(100)=NULL
									,@ve_cargo varchar(100)=NULL
									,@ve_telefono1 varchar(100)=NULL
									,@ve_telefono2 varchar(100)=NULL
									,@ve_telefono3 varchar(100)=NULL	
									)
AS
BEGIN
	declare @vl_cod_contacto_persona numeric

	if (@ve_operacion='INSERT') begin
		insert into contacto_persona (cod_contacto, nom_persona, mail, cargo)
		values (@ve_cod_contacto, @ve_nom_persona, @ve_mail, @ve_cargo)
		
		set @vl_cod_contacto_persona = @@identity
		if (@ve_telefono1 is not null)
			insert into contacto_persona_telefono (cod_contacto_persona, telefono)
			values(@vl_cod_contacto_persona, @ve_telefono1)
		if (@ve_telefono2 is not null)
			insert into contacto_persona_telefono (cod_contacto_persona, telefono)
			values(@vl_cod_contacto_persona, @ve_telefono2)
		if (@ve_telefono3 is not null)
			insert into contacto_persona_telefono (cod_contacto_persona, telefono)
			values(@vl_cod_contacto_persona, @ve_telefono3)

	end 
	if (@ve_operacion='UPDATE') begin
		update contacto_persona 
		set nom_persona = @ve_nom_persona
			,mail = @ve_mail
			,cargo = @ve_cargo
	    where cod_contacto_persona = @ve_cod_contacto_persona

		delete contacto_persona_telefono
		where cod_contacto_persona = @ve_cod_contacto_persona

		if (@ve_telefono1 is not null)
			insert into contacto_persona_telefono (cod_contacto_persona, telefono)
			values(@ve_cod_contacto_persona, @ve_telefono1)
		if (@ve_telefono2 is not null)
			insert into contacto_persona_telefono (cod_contacto_persona, telefono)
			values(@ve_cod_contacto_persona, @ve_telefono2)
		if (@ve_telefono3 is not null)
			insert into contacto_persona_telefono (cod_contacto_persona, telefono)
			values(@ve_cod_contacto_persona, @ve_telefono3)

	end
	else if (@ve_operacion='DELETE') begin
		delete contacto_persona 
    	where cod_contacto_persona = @ve_cod_contacto_persona
	end
	
END
go
