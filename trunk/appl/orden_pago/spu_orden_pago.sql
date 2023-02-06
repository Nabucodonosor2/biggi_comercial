CREATE PROCEDURE spu_orden_pago	(@ve_operacion				varchar(20)
								,@ve_cod_orden_pago			numeric=NULL
								,@ve_cod_usuario			numeric=NULL
								,@ve_cod_nota_venta			numeric=NULL
								,@ve_cod_empresa			numeric=NULL
								,@ve_cod_tipo_orden_pago	numeric=NULL
								,@ve_total_neto				numeric=NULL
								,@ve_porc_iva				numeric=NULL
								,@ve_monto_iva				numeric=NULL
								,@ve_total_con_iva			numeric=NULL
								,@ve_es_anticipo			T_SI_NO=NULL)
AS
BEGIN
	IF(@ve_operacion='INSERT')BEGIN
		INSERT INTO ORDEN_PAGO (FECHA_ORDEN_PAGO
							   ,COD_USUARIO
							   ,COD_NOTA_VENTA
							   ,COD_EMPRESA
							   ,COD_TIPO_ORDEN_PAGO
							   ,TOTAL_NETO
							   ,PORC_IVA
							   ,MONTO_IVA
							   ,TOTAL_CON_IVA
							   ,ES_ANTICIPO)
						VALUES (GETDATE()
								,@ve_cod_usuario
								,@ve_cod_nota_venta
								,@ve_cod_empresa
								,@ve_cod_tipo_orden_pago
								,@ve_total_neto
								,@ve_porc_iva
								,@ve_monto_iva
								,@ve_total_con_iva
								,@ve_es_anticipo)
	END
END