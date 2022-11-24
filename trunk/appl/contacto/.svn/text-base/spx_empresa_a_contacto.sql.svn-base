alter PROCEDURE spx_empresa_a_contacto
AS
DECLARE @vc_cod_empresa		numeric
		,@vc_rut			numeric
		,@vc_dig_verif		varchar(1)
		,@vc_nom_empresa	varchar(100)
		,@vl_direccion		varchar(100)
		,@vl_cod_ciudad		numeric
		,@vl_cod_comuna		numeric
		,@vl_telefono		varchar(100)
		,@vl_nom_comuna		varchar(100)
		,@vl_cod_contacto	numeric
		,@vc_nom_persona	varchar(100)
		,@vc_email			varchar(100)
		,@vc_nom_cargo		varchar(100)
		,@vc_telefono		varchar(100)
		,@vl_cod_contacto_persona	numeric

BEGIN
	declare c_empresa cursor for 
	select cod_empresa, rut, dig_verif, nom_empresa
	from empresa

	open c_empresa
	fetch c_empresa into @vc_cod_empresa, @vc_rut, @vc_dig_verif, @vc_nom_empresa
	while @@fetch_status = 0 
	begin
		
		select @vl_direccion = direccion
				,@vl_cod_ciudad = cod_ciudad
				,@vl_cod_comuna = cod_comuna
				,@vl_telefono = telefono  
		from sucursal 
		where cod_empresa = @vc_cod_empresa
			and direccion_factura = 'S'

		select @vl_nom_comuna = nom_comuna
		from comuna
		where cod_comuna = @vl_cod_comuna
		insert into CONTACTO (NOM_CONTACTO, RUT, DIG_VERIF, DIRECCION, COD_CIUDAD, COD_COMUNA, COD_EMPRESA) 
		values (@vc_nom_empresa, @vc_rut, @vc_dig_verif, @vl_direccion, @vl_cod_ciudad, @vl_cod_comuna, @vc_cod_empresa)
		
		set @vl_cod_contacto = @@identity
		
		if (@vl_telefono is not null) begin	
			insert into CONTACTO_TELEFONO (COD_CONTACTO, NOM_CONTACTO_TELEFONO, TELEFONO) 
			values (@vl_cod_contacto, @vl_nom_comuna, @vl_telefono)
		end

		declare c_persona cursor for 
		select nom_persona, email, nom_cargo, p.telefono
		from sucursal s, persona p left outer join cargo c on c.cod_cargo = p.cod_cargo
		where s.cod_empresa = @vc_cod_empresa
			and p.cod_sucursal = s.cod_sucursal
			
		open c_persona 
		fetch c_persona into @vc_nom_persona, @vc_email, @vc_nom_cargo, @vc_telefono
		while @@fetch_status = 0 
		begin
		
			insert into CONTACTO_PERSONA (COD_CONTACTO, NOM_PERSONA, MAIL, CARGO)
			values (@vl_cod_contacto, @vc_nom_persona, @vc_email, @vc_nom_cargo)
			set @vl_cod_contacto_persona = @@identity
			
			if (@vc_telefono is not null) begin
				insert into CONTACTO_PERSONA_TELEFONO (COD_CONTACTO_PERSONA, TELEFONO)
				values (@vl_cod_contacto_persona, @vc_telefono)
			end

			fetch c_persona into @vc_nom_persona, @vc_email, @vc_nom_cargo, @vc_telefono
		end
		close c_persona
		deallocate c_persona


		fetch c_empresa into @vc_cod_empresa, @vc_rut, @vc_dig_verif, @vc_nom_empresa
	end
	close c_empresa
	deallocate c_empresa

END
go
