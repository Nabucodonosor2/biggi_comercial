ALTER PROCEDURE spu_anula_solicitud_cotizacion(@ve_operacion				varchar(20)
											  ,@ve_cod_solicitud_cotizacion	numeric(10)=NULL
											  ,@ve_cod_estado_sc			numeric(10)=NULL
											  ,@ve_motivo_anula				text=null
											  ,@ve_cod_usuario				numeric(10)=null)
AS 
BEGIN
	IF(@ve_operacion='UPDATE')BEGIN
		DECLARE
			@vl_cod_log_cambio			numeric
		   ,@vl_cod_estado_sc_antiguo	numeric

		SELECT @vl_cod_estado_sc_antiguo = COD_ESTADO_SOLICITUD_COTIZACION
		FROM SOLICITUD_COTIZACION
		WHERE COD_SOLICITUD_COTIZACION = @ve_cod_solicitud_cotizacion

		UPDATE SOLICITUD_COTIZACION 
		SET COD_ESTADO_SOLICITUD_COTIZACION = @ve_cod_estado_sc
			,MOTIVO_ANULACION = @ve_motivo_anula
		WHERE COD_SOLICITUD_COTIZACION = @ve_cod_solicitud_cotizacion

		EXEC sp_log_cambio 'SOLICITUD_COTIZACION', @ve_cod_solicitud_cotizacion, @ve_cod_usuario, 'U'
		SET @vl_cod_log_cambio = @@IDENTITY

		EXEC sp_detalle_cambio @vl_cod_log_cambio, 'COD_ESTADO_SOLICITUD_COTIZACION', @vl_cod_estado_sc_antiguo, @ve_cod_estado_sc
	END 
END 