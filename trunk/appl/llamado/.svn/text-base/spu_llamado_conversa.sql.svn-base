-------------------- spu_llamado_conversa---------------------------------
ALTER procedure [dbo].[spu_llamado_conversa](@ve_operacion					varchar(20)
	    								,@ve_cod_llamado_conversa	numeric
	    								,@ve_cod_llamado			numeric = null
	    								,@ve_cod_destinatario		numeric = null
										,@ve_glosa					text = null
										,@ve_realizado				varchar(1)= null
										,@ve_desde_mail				varchar(1)= null)

AS
BEGIN
	if (@ve_operacion='INSERT')
		insert into llamado_conversa 
			(cod_llamado
			,fecha_llamado_conversa
			,cod_destinatario
			,glosa
			,realizado
			,desde_mail)
		values 
			(@ve_cod_llamado
			,getdate()
			,@ve_cod_destinatario
			,@ve_glosa
			,@ve_realizado
			,@ve_desde_mail)

	else if (@ve_operacion='UPDATE')
		update llamado_conversa 
		set cod_llamado				= @ve_cod_llamado
			,cod_destinatario		= @ve_cod_destinatario
			,glosa					= @ve_glosa
			,realizado				= @ve_realizado
			,desde_mail				= @ve_desde_mail
		where cod_llamado_conversa = @ve_cod_llamado_conversa

	else if (@ve_operacion='DELETE')
		delete llamado_conversa 
		where cod_llamado_conversa = @ve_cod_llamado_conversa
END