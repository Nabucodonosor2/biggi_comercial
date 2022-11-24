-------------------- spu_llamado---------------------------------
alter PROCEDURE spu_llamado(@ve_operacion					varchar(20)
	    								,@ve_cod_llamado	numeric
	    								,@ve_cod_usuario	numeric = null
	    								,@ve_cod_contacto	numeric = null
										,@ve_cod_contacto_persona	numeric = null
	    								,@ve_mensaje		text = null
	    								,@ve_cod_llamado_accion	numeric = null
										,@ve_llamar_telefono	text = null
	    								,@ve_realizado			varchar(1) = null
										,@ve_glosa_realizado	varchar(100) = null
										,@ve_tipo_doc_realizado	varchar(100) = null
										,@ve_cod_doc_realizado	numeric = null)
AS
BEGIN
	declare @vl_realizado varchar(1)

	if (@ve_operacion='INSERT')
		insert into llamado
			(FECHA_LLAMADO
	    	,COD_USUARIO
	    	,COD_CONTACTO
			,COD_CONTACTO_PERSONA
	    	,MENSAJE
	    	,COD_LLAMADO_ACCION
			,LLAMAR_TELEFONO
	    	,REALIZADO
			,FECHA_REALIZADO
			,GLOSA_REALIZADO
			,TIPO_DOC_REALIZADO
			,COD_DOC_REALIZADO)
		values 
			(getdate()
			,@ve_cod_usuario
			,@ve_cod_contacto
			,@ve_cod_contacto_persona
			,@ve_mensaje
			,@ve_cod_llamado_accion
			,@ve_llamar_telefono
			,@ve_realizado
			,NULL
			,NULL
			,@ve_tipo_doc_realizado
			,@ve_cod_doc_realizado)

	else if (@ve_operacion='UPDATE') begin
		
		select @vl_realizado = realizado
		from llamado
		where cod_llamado = @ve_cod_llamado
		
		if (@vl_realizado = 'N' and @ve_realizado = 'S')
			update llamado
			set realizado			= @ve_realizado
				,fecha_realizado	= getdate()
				,glosa_realizado	= @ve_glosa_realizado
			where cod_llamado = @ve_cod_llamado
		
		update llamado
		set cod_contacto		= @ve_cod_contacto
			,cod_contacto_persona = @ve_cod_contacto_persona
	    	,mensaje			= @ve_mensaje
	    	,cod_llamado_accion	= @ve_cod_llamado_accion
			,llamar_telefono	= @ve_llamar_telefono
			,tipo_doc_realizado	= @ve_tipo_doc_realizado
			,cod_doc_realizado	= @ve_cod_doc_realizado
		where cod_llamado = @ve_cod_llamado

	end	
	else if (@ve_operacion='REALIZADO_WEB') begin
		update llamado
		set realizado			= @ve_realizado
			,fecha_realizado	= getdate()
			,tipo_doc_realizado	= @ve_tipo_doc_realizado
			,cod_doc_realizado	= @ve_cod_doc_realizado
			,glosa_realizado= @ve_glosa_realizado
		where cod_llamado = @ve_cod_llamado
		
	end 
END
go