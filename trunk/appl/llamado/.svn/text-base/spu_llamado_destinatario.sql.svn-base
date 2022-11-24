-------------------- spu_llamado_destinatario---------------------------------
alter PROCEDURE spu_llamado_destinatario(@ve_operacion					varchar(20)
	    								,@ve_cod_llamado_destinatario	numeric
	    								,@ve_cod_llamado				numeric = null
	    								,@ve_cod_destinatario			numeric = null
	    								,@ve_responsable				varchar(1) = null)	 
AS
BEGIN
	if (@ve_operacion='INSERT')
		insert into llamado_destinatario 
			(cod_llamado
			,cod_destinatario
	    	,responsable)
		values 
			(@ve_cod_llamado
			,@ve_cod_destinatario
	    	,@ve_responsable)

	else if (@ve_operacion='UPDATE')
		update llamado_destinatario 
		set cod_llamado = @ve_cod_llamado
			,cod_destinatario = @ve_cod_destinatario
	    	,responsable = @ve_responsable
		where cod_llamado_destinatario = @ve_cod_llamado_destinatario

	else if (@ve_operacion='DELETE')
		delete llamado_destinatario 
		where cod_llamado_destinatario = @ve_cod_llamado_destinatario
END