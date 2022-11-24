ALTER PROCEDURE [dbo].[spu_bitacora_cotizacion](@ve_operacion				varchar(20)
									, @ve_cod_bitacora_cotizacion	numeric
									, @ve_cod_usuario				numeric=NULL
									, @ve_cod_cotizacion			numeric=NULL
									, @ve_cod_accion_cotizacion		numeric=NULL
									, @ve_contacto					varchar(100)=NULL
									, @ve_telefono					varchar(100)=NULL
									, @ve_mail						varchar(100)=NULL
									, @ve_glosa						text=NULL
									, @ve_tiene_compromiso			varchar(1)=NULL
									, @ve_fecha_compromiso			datetime=NULL
									, @ve_glosa_compromiso			text=NULL
									, @ve_compromiso_realizado		varchar(1)=NULL
									, @ve_cod_persona				numeric=NULL
									, @ve_desde_ini_seguimiento		varchar(1)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		-- 4.- Cada vez que se agregue un compromiso en una cotización, se deben marcar los compromisos anteriores como realizados. 
		update BITACORA_COTIZACION
		set COMPROMISO_REALIZADO = 'S'
	    where COD_COTIZACION = @ve_cod_cotizacion
		
		if(@ve_desde_ini_seguimiento is null)begin
			UPDATE COTIZACION
			SET COD_ESTADO_COTIZACION = 3
			WHERE COD_COTIZACION = @ve_cod_cotizacion
		end
		
		if(@ve_telefono is NULL or @ve_telefono = '')begin
			select @ve_telefono = dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL_FACTURA, '[TELEFONO]')
			from cotizacion
			where cod_cotizacion = @ve_cod_cotizacion
		end
		
		insert into BITACORA_COTIZACION
			(FECHA_BITACORA
			,COD_USUARIO
			,COD_COTIZACION
			,COD_ACCION_COTIZACION
			,CONTACTO
			,TELEFONO
			,MAIL
			,GLOSA
			,TIENE_COMPROMISO
			,FECHA_COMPROMISO
			,GLOSA_COMPROMISO
			,COMPROMISO_REALIZADO
			,FECHA_REALIZADO
			,COD_USUARIO_REALIZADO
			,COD_PERSONA)
		values
			(getdate()
			,@ve_cod_usuario
			,@ve_cod_cotizacion
			,@ve_cod_accion_cotizacion
			,@ve_contacto
			,@ve_telefono
			,@ve_mail
			,@ve_glosa
			,@ve_tiene_compromiso
			,@ve_fecha_compromiso
			,@ve_glosa_compromiso
			,@ve_compromiso_realizado
			,null
			,null
			,@ve_cod_persona) 	
	end 
	if (@ve_operacion='UPDATE') begin
		declare
			@fecha_realizado		datetime
			,@cod_usuario_realizado	numeric

		if (@ve_compromiso_realizado = 'S') begin
			set @fecha_realizado = getdate()
			set @cod_usuario_realizado = @ve_cod_usuario
		end 
		else begin
			set @fecha_realizado = null
			set @cod_usuario_realizado = null
		end 
		update BITACORA_COTIZACION
		set COD_ACCION_COTIZACION = @ve_cod_accion_cotizacion
			,CONTACTO = @ve_contacto
			,TELEFONO = @ve_telefono
			,MAIL = @ve_mail
			,GLOSA = @ve_glosa
			,TIENE_COMPROMISO = @ve_tiene_compromiso
			,FECHA_COMPROMISO = @ve_fecha_compromiso
			,GLOSA_COMPROMISO = @ve_glosa_compromiso
			,COMPROMISO_REALIZADO = @ve_compromiso_realizado
			,FECHA_REALIZADO = @fecha_realizado
			,COD_USUARIO_REALIZADO = @cod_usuario_realizado
			,COD_PERSONA = @ve_cod_persona
	    where COD_BITACORA_COTIZACION = @ve_cod_bitacora_cotizacion
	end
	else if (@ve_operacion='INICIA_SEGUIMIENTO') begin
		--Debe crear un compromiso automáticamente con fecha de compromiso leyendo los dias en la tabla
		--parámetros con el codigo 59
		declare
			@vl_fecha_compromiso datetime,
			@vl_telefono		 varchar(100),
			@vl_mail			 varchar(100),
			@vl_fecha_cotizacion datetime,
			@vl_glosa			 varchar(100),
			@vl_dias_param		 numeric(18)
			
		SELECT @vl_dias_param = dbo.f_get_parametro(59)	--Número de días para los Compromisos automáticos
		
		SET @vl_fecha_compromiso = DATEADD(minute,570,(DATEADD(day, @vl_dias_param, dbo.f_makedate(day(getdate()), month(getdate()), year(getdate())))))
		SET @ve_cod_persona = @ve_contacto
		
		select @ve_contacto = NOM_PERSONA,
			   @vl_telefono = TELEFONO,
			   @vl_mail		= EMAIL
		from PERSONA
		WHERE COD_PERSONA = @ve_cod_persona
		
		if(@vl_telefono is NULL or @vl_telefono = '')begin
			select @vl_telefono = dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL_FACTURA, '[TELEFONO]')
			from cotizacion
			where cod_cotizacion = @ve_cod_cotizacion
		end 
		
		select @vl_fecha_cotizacion = fecha_cotizacion 
		from cotizacion
		where cod_cotizacion = @ve_cod_cotizacion
		
		set @vl_glosa = 'Contactar al cliente, cotización fue creada el ' + CONVERT(varchar,@vl_fecha_cotizacion,103)
		
		exec spu_bitacora_cotizacion 'INSERT'
						,@ve_cod_bitacora_cotizacion
						,@ve_cod_usuario
						,@ve_cod_cotizacion
						,1 
						,@ve_contacto
						,@vl_telefono				
						,@vl_mail				
						,@vl_glosa
						,'S'				
						,@vl_fecha_compromiso 
						,@vl_glosa 			
						,'N'
						,@ve_cod_persona
						,'S'			
	end
	else if (@ve_operacion='DELETE') begin
		delete BITACORA_COTIZACION
    	where COD_BITACORA_COTIZACION = @ve_cod_bitacora_cotizacion
	end
END