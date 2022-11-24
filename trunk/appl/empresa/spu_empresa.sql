-------------------- spu_empresa ---------------------------------
alter PROCEDURE [dbo].[spu_empresa](@ve_operacion varchar(30)
							,@ve_cod_empresa numeric
							,@ve_rut numeric = null
							,@ve_dig_verif varchar(1) = null
							,@ve_alias varchar(30) = null
							,@ve_nom_empresa varchar(100) = null
							,@ve_giro varchar(100) = null
							,@ve_cod_clasif_empresa numeric = null
							,@ve_direccion_internet varchar(30) = null
							,@ve_rut_representante numeric = null
							,@ve_dig_verif_representante varchar(1) = null
							,@ve_nom_representante varchar(100) = null
							,@ve_es_cliente varchar(1) = null
							,@ve_es_proveedor_interno varchar(1) = null
							,@ve_es_proveedor_externo varchar(1) = null
							,@ve_es_personal varchar(1) = null
							,@ve_imprimir_emp_mas_suc varchar(1) = null
							,@ve_sujeto_a_aprobacion varchar(1) = null
							,@ve_porc_dscto_corporativo T_PORCENTAJE = null
							,@ve_cod_usuario numeric = null
							,@ve_tipo_participacion varchar(4) = null,
							,@ve_dscto_proveedor T_PORCENTAJE = null) 
AS
BEGIN
Declare @ve_porc numeric
Declare @existe_hoy numeric
Declare @cod_empresa_creada numeric
		,@vl_direccion varchar(100)
	    ,@vl_cod_ciudad numeric
	    ,@vl_cod_comuna numeric
	    ,@vl_telefono   varchar(20)
	    ,@vl_nom_empresa varchar(100)
		,@vl_rut		 numeric
		,@vl_dig_verif	 varchar(1)
		,@vl_cod_contacto numeric
		,@vl_nom_comuna varchar(100)
		,@vc_nom_persona	varchar(100)
		,@vc_email			varchar(100)
		,@vc_nom_cargo		varchar(100)
		,@vc_telefono		varchar(100)
		,@vl_cod_contacto_persona	numeric
		
	if (@ve_operacion='INSERT') 
		begin
			insert into empresa (rut, dig_verif, alias, nom_empresa, giro, cod_clasif_empresa, direccion_internet, rut_representante, dig_verif_representante, nom_representante, es_cliente, es_proveedor_interno, es_proveedor_externo, es_personal, imprimir_emp_mas_suc, sujeto_a_aprobacion, cod_usuario, tipo_participacion,dscto_proveedor)
			values (@ve_rut, upper(@ve_dig_verif), @ve_alias, @ve_nom_empresa, @ve_giro, @ve_cod_clasif_empresa, @ve_direccion_internet, @ve_rut_representante, @ve_dig_verif_representante, @ve_nom_representante, @ve_es_cliente, @ve_es_proveedor_interno, @ve_es_proveedor_externo, @ve_es_personal, @ve_imprimir_emp_mas_suc, @ve_sujeto_a_aprobacion, @ve_cod_usuario, @ve_tipo_participacion,@ve_dscto_proveedor)
		end
	else if (@ve_operacion='DSCTO_CORPORATIVO_EMPRESA')
		begin
			-- solo si se ingreso un descuento corporativo lo graba en la tabla DSCTO_CORPORATIVO_EMPRESA
			IF (@ve_porc_dscto_corporativo <> 0)
			BEGIN
				insert into DSCTO_CORPORATIVO_EMPRESA 
						   (cod_empresa, porc_dscto_corporativo, fecha_inicio_vigencia)
				values 
						   (@ve_cod_empresa, 
							@ve_porc_dscto_corporativo, 
							getdate())
			END
		end
	else if (@ve_operacion='UPDATE') 
		begin
			update empresa 
			set rut = @ve_rut, 
				dig_verif = upper(@ve_dig_verif), 
				alias = @ve_alias, 
				nom_empresa = @ve_nom_empresa, 
				giro = @ve_giro, 
				cod_clasif_empresa = @ve_cod_clasif_empresa, 
				direccion_internet = @ve_direccion_internet,
				rut_representante = @ve_rut_representante, 
				dig_verif_representante = @ve_dig_verif_representante, 
				nom_representante = @ve_nom_representante, 
				es_cliente = @ve_es_cliente, 
				es_proveedor_interno = @ve_es_proveedor_interno, 
				es_proveedor_externo = @ve_es_proveedor_externo, 
				es_personal = @ve_es_personal, 
				imprimir_emp_mas_suc = @ve_imprimir_emp_mas_suc, 
				sujeto_a_aprobacion = @ve_sujeto_a_aprobacion,
				cod_usuario = @ve_cod_usuario,
				-- porc_dscto_corporativo = @ve_porc_dscto_corporativo
				tipo_participacion = @ve_tipo_participacion,
				dscto_proveedor = @ve_dscto_proveedor
		    where cod_empresa = @ve_cod_empresa
		
			select @ve_porc = (dbo.f_get_porc_dscto_corporativo_empresa(@ve_cod_empresa, dbo.f_makedate(day(getdate()),month(getdate()),year(getdate())))) 
			IF (@ve_porc_dscto_corporativo <> @ve_porc)
			BEGIN
				select @existe_hoy = count(*)
				from dscto_corporativo_empresa
				where cod_empresa = @ve_cod_empresa 
					  and fecha_inicio_vigencia = dbo.f_makedate(day(getdate()),month(getdate()),year(getdate()))
				
				IF (@existe_hoy > 0)
				BEGIN
						update DSCTO_CORPORATIVO_EMPRESA
						set porc_dscto_corporativo = @ve_porc_dscto_corporativo
						where cod_empresa = @ve_cod_empresa and fecha_inicio_vigencia = dbo.f_makedate(day(getdate()),month(getdate()),year(getdate()))
				END
				ELSE
				BEGIN
					insert into DSCTO_CORPORATIVO_EMPRESA 
							    (cod_empresa, porc_dscto_corporativo, fecha_inicio_vigencia)
					values 
								(@ve_cod_empresa, 
								 @ve_porc_dscto_corporativo, 
								 dbo.f_makedate(day(getdate()),month(getdate()),year(getdate())))
				END
			END
		end
	else if (@ve_operacion='CONTACTO')BEGIN
			
		select @vl_direccion 	= s.direccion
			   ,@vl_cod_ciudad 	= s.cod_ciudad
			   ,@vl_cod_comuna 	= s.cod_comuna
			   ,@vl_telefono	= s.telefono
			   ,@vl_nom_empresa = e.nom_empresa
			   ,@vl_rut			= e.rut
			   ,@vl_dig_verif	= e.dig_verif
		from empresa e, sucursal s 
		where e.cod_empresa = @ve_cod_empresa
			and s.direccion_factura = 'S'
			and e.cod_empresa = s.cod_empresa
			
		insert into CONTACTO (NOM_CONTACTO, RUT, DIG_VERIF, DIRECCION, COD_CIUDAD, COD_COMUNA, COD_EMPRESA) 
		values (@vl_nom_empresa, @vl_rut, @vl_dig_verif, @vl_direccion, @vl_cod_ciudad, @vl_cod_comuna, @ve_cod_empresa) 
		
		set @vl_cod_contacto = @@identity
		
		select @vl_nom_comuna = nom_comuna
		from comuna
		where cod_comuna = @vl_cod_comuna
		
		if (@vl_telefono is not null) begin	
			insert into CONTACTO_TELEFONO (COD_CONTACTO, NOM_CONTACTO_TELEFONO, TELEFONO) 
			values (@vl_cod_contacto, @vl_nom_comuna, @vl_telefono)
		end
		
		declare c_persona cursor for 
		select nom_persona, email, nom_cargo, p.telefono
		from sucursal s, persona p left outer join cargo c on c.cod_cargo = p.cod_cargo
		where s.cod_empresa = @ve_cod_empresa
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
			
	END 
END
go