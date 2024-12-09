CREATE PROCEDURE spu_informe_comision(@ve_operacion						varchar(20)
									,@ve_cod_informe_comision			numeric
									,@ve_cod_estado_informe_comision	numeric = NULL
									,@ve_cod_usuario					numeric = NULL
									,@ve_cod_empresa					numeric = NULL
									,@ve_referencia						varchar(500) = NULL)
AS
BEGIN
	IF(@ve_operacion='INSERT')BEGIN
		DECLARE
			@vl_fecha_registro DATETIME = GETDATE()

		INSERT INTO INFORME_COMISION(FECHA_REGISTRO
									,COD_ESTADO_INFORME_COMISION
									,COD_USUARIO
									,COD_EMPRESA
									,REFERENCIA)
							 VALUES(@vl_fecha_registro
								   ,@ve_cod_estado_informe_comision
								   ,@ve_cod_usuario
								   ,@ve_cod_empresa
								   ,@ve_referencia)
	END 
	ELSE IF (@ve_operacion='UPDATE')BEGIN
		UPDATE INFORME_COMISION
		SET COD_ESTADO_INFORME_COMISION = @ve_cod_estado_informe_comision
		   ,COD_USUARIO = @ve_cod_usuario
		   ,COD_EMPRESA = @ve_cod_empresa
		   ,REFERENCIA = @ve_referencia
		WHERE COD_INFORME_COMISION = @ve_cod_informe_comision
	END	
END