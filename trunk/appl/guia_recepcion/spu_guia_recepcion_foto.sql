-------------------- spu_guia_recepcion_foto---------------------------------
CREATE PROCEDURE spu_guia_recepcion_foto(@ve_operacion					varchar(20)
										,@ve_cod_guia_recepcion_foto	numeric = NULL
										,@ve_cod_guia_recepcion			numeric	= NULL	
										,@ve_cod_usuario				numeric	=NULL
										,@ve_ruta_archivo				varchar(500)=NULL
										,@ve_nom_archivo				varchar(100)=NULL
										,@ve_obs						text=NULL)

AS
BEGIN
	DECLARE @vl_fecha_registro DATETIME = GETDATE()

	if (@ve_operacion='INSERT')begin
			insert into GUIA_RECEPCION_FOTO (COD_GUIA_RECEPCION
											,FECHA_REGISTRO
											,COD_USUARIO
											,RUTA_ARCHIVO
											,NOM_ARCHIVO
											,OBS)
									values  (@ve_cod_guia_recepcion
											,@vl_fecha_registro
											,@ve_cod_usuario
											,@ve_ruta_archivo
											,@ve_nom_archivo
											,@ve_obs) 
	end 
	else if (@ve_operacion='UPDATE')begin
		UPDATE GUIA_RECEPCION_FOTO
		SET OBS = @ve_obs
		WHERE COD_GUIA_RECEPCION_FOTO = @ve_cod_guia_recepcion_foto
	end	
	else if (@ve_operacion='DELETE')begin
		DELETE GUIA_RECEPCION_FOTO
    	WHERE COD_GUIA_RECEPCION_FOTO = @ve_cod_guia_recepcion_foto 
	end 
END