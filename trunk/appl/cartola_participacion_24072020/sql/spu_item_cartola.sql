SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER PROCEDURE [dbo].[spu_item_cartola](@ve_operacion varchar(20)
                                            , @ve_cod_item_cartola numeric
                                            , @ve_cod_cartola numeric
                                            , @ve_cod_participacion numeric
                                            ,@ve_fecha_movimiento  DATE
                                            , @ve_glosa varchar(100) = null
                                            , @ve_abono numeric = null
                                            , @ve_retiro numeric = null
                                            ,@ve_cod_usuario  NUMERIC
                                            ,@ve_saldo  NUMERIC
                                            )
AS

BEGIN
DECLARE  @vl_monto NUMERIC(10),
        @vl_tipo_mov VARCHAR(10),
        @vl_fecha_mov  DATE


IF(@ve_abono>0 ) BEGIN
  set  @vl_monto=@ve_abono
  set @vl_tipo_mov='ABONO'
  set @vl_fecha_mov=@ve_fecha_movimiento
END
ELSE BEGIN
   set  @vl_monto=@ve_retiro 
   set @vl_tipo_mov='RETIRO'
   set @vl_fecha_mov=GETDATE()
END

if (@ve_operacion='INSERT') begin
    insert into ITEM_CARTOLA_PARTICIPACION (
                                             COD_CARTOLA_PARTICIPACION
                                            ,TIPO_MOVIMIENTO
                                            ,COD_PARTICIPACION
                                            ,FECHA_MOVIMIENTO
                                            ,GLOSA
                                            ,MONTO
                                            ,COD_USUARIO
                                            ,SALDO)
    values ( @ve_cod_cartola
            ,@vl_tipo_mov
            ,@ve_cod_participacion
            ,@vl_fecha_mov
            ,@ve_glosa
            ,@vl_monto
            ,@ve_cod_usuario 
            ,@ve_saldo )
end

END

GO
