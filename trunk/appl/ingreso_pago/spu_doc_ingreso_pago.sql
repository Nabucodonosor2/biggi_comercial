-------------------- spu_doc_ingreso_pago ---------------------------------
ALTER PROCEDURE [dbo].[spu_doc_ingreso_pago](
				@ve_operacion				varchar(20)
				,@ve_cod_doc_ingreso_pago	numeric
				,@ve_cod_ingreso_pago		numeric		= NULL
				,@ve_cod_tipo_doc_pago		numeric		= NULL
				,@ve_cod_banco				numeric		= NULL
				,@ve_nro_doc				numeric		= NULL
				,@ve_fecha_doc				varchar(25)	= NULL
				,@ve_monto_doc				T_PRECIO	= NULL
				,@ve_cod_cheque				numeric		= NULL)

AS
BEGIN
	if (@ve_operacion='INSERT') 
		begin
			declare
				@vl_nom_tipo_origen_pago		varchar(100),
				@vl_tbk_numero_cuotas			numeric(10),
				@vl_cuotas_webpay				varchar(100),
				@vl_tipo_pago					varchar(10)
			
			set @vl_cuotas_webpay = NULL	
				
			SELECT @vl_nom_tipo_origen_pago = NOM_TIPO_ORIGEN_PAGO 
			FROM INGRESO_PAGO
			WHERE COD_INGRESO_PAGO = @ve_cod_ingreso_pago
			
			if(@vl_nom_tipo_origen_pago = 'WEBPAY PLUS')begin
				SELECT @vl_tbk_numero_cuotas = TBK_NUMERO_CUOTAS
					  ,@vl_tipo_pago = TBK_TIPO_PAGO
				FROM WP_PAGO_TRANSACCION
				WHERE TBK_CODIGO_AUTORIZACION = @ve_nro_doc
				
				if(@vl_tipo_pago = 'VD')
					set @vl_cuotas_webpay = 'Sin cuotas (Tarjeta Débito)'
				else begin
					if(@vl_tbk_numero_cuotas = 0)
						set @vl_tbk_numero_cuotas = 1	
					set @vl_cuotas_webpay = 'Nro de cuotas: ' + CONVERT(VARCHAR, @vl_tbk_numero_cuotas)
				end
			end
			
			insert into doc_ingreso_pago(
				cod_ingreso_pago
				,cod_tipo_doc_pago
				,cod_banco
				,nro_doc
				,fecha_doc
				,monto_doc
				,cod_cheque
				,cuotas_webpay)
			values		(
				@ve_cod_ingreso_pago
				,@ve_cod_tipo_doc_pago
				,@ve_cod_banco
				,@ve_nro_doc
				,dbo.to_date(@ve_fecha_doc)
				,@ve_monto_doc
				,@ve_cod_cheque
				,@vl_cuotas_webpay) 
		end 

	else if (@ve_operacion='UPDATE') 
		begin
			update doc_ingreso_pago
			set cod_ingreso_pago	=	@ve_cod_ingreso_pago
				,cod_tipo_doc_pago	=	@ve_cod_tipo_doc_pago
				,cod_banco			=	@ve_cod_banco
				,nro_doc			=	@ve_nro_doc
				,fecha_doc			=	dbo.to_date(@ve_fecha_doc)
				,monto_doc			=	@ve_monto_doc
				,cod_cheque			=	@ve_cod_cheque
			where cod_doc_ingreso_pago = @ve_cod_doc_ingreso_pago
		end	
	else if (@ve_operacion='DELETE') 
		begin
			delete  doc_ingreso_pago
    		where cod_doc_ingreso_pago = @ve_cod_doc_ingreso_pago
		end 
END