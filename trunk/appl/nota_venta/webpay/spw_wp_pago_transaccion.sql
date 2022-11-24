CREATE PROCEDURE [dbo].[spw_wp_pago_transaccion](@ve_operacion					varchar(100)	
												,@ve_cod_wp_pago_transaccion	NUMERIC			= null
												,@ve_cod_wp_transaccion			NUMERIC			= null
												,@ve_tbk_orden_compra 			VARCHAR(26)		= null
												,@ve_tbk_codigo_comercio		NUMERIC			= null
												,@ve_tbk_codigo_comercio_enc	NUMERIC			= null
												,@ve_tbk_tipo_transaccion		VARCHAR(50)		= null
												,@ve_tbk_respuesta				NUMERIC			= null
												,@ve_tbk_monto					NUMERIC			= null
												,@ve_tbk_codigo_autorizacion	VARCHAR(8)		= null
												,@ve_tbk_final_numero_tarjeta	NUMERIC			= null
												,@ve_tbk_fecha_contable			NUMERIC			= null
												,@ve_tbk_fecha_transaccion		NUMERIC			= null
												,@ve_tbk_fecha_expiracion		NUMERIC			= null
												,@ve_tbk_hora_transaccion 		NUMERIC			= null
												,@ve_tbk_id_sesion				VARCHAR(61)		= null
												,@ve_tbk_id_transaccion			NUMERIC			= null
												,@ve_tbk_tipo_pago				VARCHAR(2)		= null	
												,@ve_tbk_numero_cuotas			NUMERIC			= null
												,@ve_tbk_vci					VARCHAR(3)		= null
												,@ve_tbk_mac					VARCHAR(256)	= null
												)
AS
BEGIN
	if (@ve_operacion = 'INSERT')
  	begin
	  	insert into WP_PAGO_TRANSACCION 
					(COD_WP_TRANSACCION
					,TBK_ORDEN_COMPRA
					,TBK_CODIGO_COMERCIO
					,TBK_CODIGO_COMERCIO_ENC
					,TBK_TIPO_TRANSACCION
					,TBK_RESPUESTA
					,TBK_MONTO
					,TBK_CODIGO_AUTORIZACION
					,TBK_FINAL_NUMERO_TARJETA
					,TBK_FECHA_CONTABLE
					,TBK_FECHA_TRANSACCION
					,TBK_FECHA_EXPIRACION
					,TBK_HORA_TRANSACCION
					,TBK_ID_SESION
					,TBK_ID_TRANSACCION
					,TBK_TIPO_PAGO
					,TBK_NUMERO_CUOTAS
					,TBK_VCI
					,TBK_MAC)
		values (@ve_cod_wp_transaccion
				,@ve_tbk_orden_compra
				,@ve_tbk_codigo_comercio
				,@ve_tbk_codigo_comercio_enc
				,@ve_tbk_tipo_transaccion
				,@ve_tbk_respuesta
				,@ve_tbk_monto
				,@ve_tbk_codigo_autorizacion
				,@ve_tbk_final_numero_tarjeta
				,@ve_tbk_fecha_contable
				,@ve_tbk_fecha_transaccion
				,@ve_tbk_fecha_expiracion
				,@ve_tbk_hora_transaccion
				,@ve_tbk_id_sesion
				,@ve_tbk_id_transaccion
				,@ve_tbk_tipo_pago	
				,@ve_tbk_numero_cuotas
				,@ve_tbk_vci
				,@ve_tbk_mac)
	end	
END 