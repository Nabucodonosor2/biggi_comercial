ALTER PROCEDURE [dbo].[spu_orden_compra]
			(@ve_operacion				 		varchar(20)
			,@ve_cod_orden_compra		 		numeric
			,@ve_cod_usuario		 	 		numeric = NULL
			,@ve_cod_usuario_solicita	 		numeric = NULL
			,@ve_cod_moneda				 		numeric = NULL			
			,@ve_cod_estado_orden_compra 		numeric = NULL
			,@ve_cod_nota_venta			 		numeric = NULL
			,@ve_cod_cuenta_corriente 	 		numeric = NULL
			,@ve_referencia				 		varchar(100)= NULL			
			,@ve_cod_empresa			 		numeric = NULL			
			,@ve_cod_suc_factura		 		numeric = NULL
			,@ve_cod_persona			 		numeric = NULL
			,@ve_sub_total				 		T_PRECIO = NULL
			,@ve_porc_dscto1			 		T_PORCENTAJE = NULL
			,@ve_monto_dscto1			 		T_PRECIO = NULL
			,@ve_porc_dscto2			 		T_PORCENTAJE = NULL
			,@ve_monto_dscto2			 		T_PRECIO = NULL
			,@ve_total_neto				 		T_PRECIO = NULL
			,@ve_porc_iva				 		T_PORCENTAJE = NULL
			,@ve_monto_iva				 		T_PRECIO = NULL
			,@ve_total_con_iva			 		T_PRECIO = NULL
			,@ve_obs					 		text = NULL
			,@ve_motivo_anula			 		varchar(100)= NULL
			,@ve_cod_usuario_anula		 		numeric = NULL
			,@ve_ingreso_usuario_dscto1  		varchar(1) = NULL
			,@ve_ingreso_usuario_dscto2 		varchar(1) = NULL
			,@ve_tipo_orden_compra		 		varchar(30) = NULL
			,@ve_cod_doc				 		numeric = NULL
			,@ve_autorizada				 		varchar(1) = NULL
			,@ve_autorizada_20_proc		 		varchar(1) = NULL
			,@nro_orden_compra_4d		 		numeric = NULL
			,@ve_es_oc_gf_plano			 		varchar(1) = NULL
			,@ve_autoriza_facturacion	 		varchar(1) = NULL
			,@ve_fecha_aut_facturacion	 		datetime = NULL
			,@ve_autoriza_monto_compra	 		varchar(1) = NULL
			,@ve_usuario_autoriza_monto_compra	numeric = NULL
			,@ve_creada_desde					varchar(100) = NULL
			,@ve_rp_cliente						T_SI_NO = 'N'
			,@ve_estado_oc_plano				varchar(1) = NULL
			,@ve_nro_cotizacion					numeric = NULL
			,@ve_nro_nota_venta					numeric = NULL
			,@ve_observaciones					text = NULL)

			

AS
BEGIN
	declare	@kl_cod_estado_oc_anulada			numeric
			,@vl_cod_usuario_anula				numeric
			,@vl_ingreso_usuario_dscto1			T_INGRESO_USUARIO_DSCTO
			,@vl_ingreso_usuario_dscto2			T_INGRESO_USUARIO_DSCTO
			,@vl_porc_iva						T_PORCENTAJE
			,@vl_monto_dscto1					T_PRECIO
			,@vl_porc_dscto1					T_PORCENTAJE
			,@vl_monto_dscto2					T_PRECIO
			,@vl_porc_dscto2					T_PORCENTAJE
			,@vl_sub_total						T_PRECIO
			,@vl_sub_total_con_dscto1			T_PRECIO
			,@vl_total_neto						T_PRECIO
			,@vl_monto_iva						T_PRECIO
			,@vl_total_con_iva					T_PRECIO
			,@vl_total_neto_original			T_PRECIO
			,@vl_usuario_autoriza_monto_compra	numeric
			,@vl_fecha_autoriza_monto_compra	datetime
			,@vl_fecha_rp						datetime
			,@vl_usuario_rp						numeric
			,@vl_rp_cliente						T_SI_NO	
			
	set @kl_cod_estado_oc_anulada = 2  --- estado de la oc = anulada

	if(@ve_rp_cliente = 'S')BEGIN
		select @vl_rp_cliente = rp_cliente
			  ,@vl_usuario_rp = cod_usuario_rp_cliente
			  ,@vl_fecha_rp	= fecha_rp_cliente
		from orden_compra
		where cod_orden_compra = @ve_cod_orden_compra

		if(@vl_rp_cliente = 'N')begin
			set @vl_fecha_rp = GETDATE()
			set @vl_usuario_rp = @ve_usuario_autoriza_monto_compra -- se usara este parametro ya que siempre trae al usuario actual
		end
		else begin
			set @vl_fecha_rp = @vl_fecha_rp
			set @vl_usuario_rp = @vl_usuario_rp
		end
	END
	ELSE BEGIN
		set @vl_fecha_rp = NULL
		set @vl_usuario_rp = NULL
	END
   			
	if (@ve_operacion='UPDATE') 
		begin
			select @vl_usuario_autoriza_monto_compra = usuario_autoriza_monto_compra
			from orden_compra
			where cod_orden_compra = @ve_cod_orden_compra
			
			if(@vl_usuario_autoriza_monto_compra IS NULL)BEGIN
				if(@ve_autoriza_monto_compra = 'S')
					update orden_compra
					set usuario_autoriza_monto_compra = @ve_usuario_autoriza_monto_compra
						,fecha_autoriza_monto_compra = getdate()
					where cod_orden_compra = @ve_cod_orden_compra
			END
			
			UPDATE [dbo].[ORDEN_COMPRA]
			SET 	
		        [COD_USUARIO]				=	 @ve_cod_usuario		
		        ,[COD_USUARIO_SOLICITA]		=	 @ve_cod_usuario_solicita	
		        ,[COD_MONEDA]				=	 @ve_cod_moneda			
		        ,[COD_ESTADO_ORDEN_COMPRA]	=	 @ve_cod_estado_orden_compra	
		        ,[COD_NOTA_VENTA]			=	 @ve_cod_nota_venta		
		        ,[COD_CUENTA_CORRIENTE]		=	 @ve_cod_cuenta_corriente	
		        ,[REFERENCIA]				=	 @ve_referencia			
		        ,[COD_EMPRESA]				=	 @ve_cod_empresa		
		        ,[COD_SUCURSAL]				=	 @ve_cod_suc_factura		
		        ,[COD_PERSONA]				=	 @ve_cod_persona		
		        ,[SUBTOTAL]					=	 @ve_sub_total			
		        ,[PORC_DSCTO1]				=	 @ve_porc_dscto1		
		        ,[MONTO_DSCTO1]				=	 @ve_monto_dscto1		
		        ,[PORC_DSCTO2]				=	 @ve_porc_dscto2		
		        ,[MONTO_DSCTO2]				=	 @ve_monto_dscto2		
		        ,[TOTAL_NETO]				=	 @ve_total_neto			
		        ,[PORC_IVA]					=	 @ve_porc_iva			
		        ,[MONTO_IVA]				=	 @ve_monto_iva			
		        ,[TOTAL_CON_IVA]			=	 @ve_total_con_iva		
		        ,[OBS]						=	 @ve_obs			
				,[INGRESO_USUARIO_DSCTO1]	=	 @ve_ingreso_usuario_dscto1	
				,[INGRESO_USUARIO_DSCTO2]	=	 @ve_ingreso_usuario_dscto2	
           	   	,[TIPO_ORDEN_COMPRA]		=	 @ve_tipo_orden_compra
           	   	,COD_DOC 					= 	 @ve_cod_doc
           	   	,AUTORIZADA					=	 @ve_autorizada
           	   	,AUTORIZADA_20_PROC			=	 @ve_autorizada_20_proc
           	   	,NRO_ORDEN_COMPRA_4D		=	 @nro_orden_compra_4d
           	   	,AUTORIZA_FACTURACION		=	 @ve_autoriza_facturacion
           	   	,FECHA_SOLICITA_FACTURACION	=	 @ve_fecha_aut_facturacion
           	   	,AUTORIZA_MONTO_COMPRA		=	 @ve_autoriza_monto_compra
           	   	,CREADA_DESDE				=	 @ve_creada_desde
				,RP_CLIENTE					=	 @ve_rp_cliente
				,FECHA_RP_CLIENTE			=	 @vl_fecha_rp
				,COD_USUARIO_RP_CLIENTE		=	 @vl_usuario_rp
				,ESTADO_OC_PLANO			=	 @ve_estado_oc_plano
				,NRO_COTIZACION				=	 @ve_nro_cotizacion
				,NRO_NOTA_VENTA				=	 @ve_nro_nota_venta
				,OBSERVACIONES				=	 @ve_observaciones
           	   	
				WHERE cod_orden_compra = @ve_cod_orden_compra
				

				-- update a anulación de OC
				select	@vl_cod_usuario_anula = cod_usuario_anula
				from orden_compra
				where cod_orden_compra = @ve_cod_orden_compra
				if (@ve_cod_estado_orden_compra = @kl_cod_estado_oc_anulada) and (@vl_cod_usuario_anula is NULL)
					update orden_compra
					set fecha_anula			= getdate ()
						,motivo_anula		= @ve_motivo_anula			
						,cod_usuario_anula	= @ve_cod_usuario_anula				
					where cod_orden_compra = @ve_cod_orden_compra
			end 
	else if (@ve_operacion='INSERT') 
		begin
			if(@ve_autoriza_monto_compra = 'S')BEGIN
				set @vl_usuario_autoriza_monto_compra = @ve_cod_usuario
				set @vl_fecha_autoriza_monto_compra = getdate()
			END
			
			/*caso especial, se puede autorizar al momento de hacer insert siempre y cuando la empresa
			  sea para TODOINOX, de lo contrario no tomara en cuenta los valores que se ingresen*/
			if(@ve_cod_empresa <> 1302)begin
				set @ve_autoriza_facturacion = NULL
           	   	set @ve_fecha_aut_facturacion = NULL
           	end   	
			
			INSERT INTO [dbo].[ORDEN_COMPRA]
		       	([FECHA_ORDEN_COMPRA]
		       	,[COD_USUARIO]
		       	,[COD_USUARIO_SOLICITA]
		        ,[COD_MONEDA]
		        ,[COD_ESTADO_ORDEN_COMPRA]
		        ,[COD_NOTA_VENTA]
		        ,[COD_CUENTA_CORRIENTE]
		        ,[REFERENCIA]
		        ,[COD_EMPRESA]
		        ,[COD_SUCURSAL]
		        ,[COD_PERSONA]
		        ,[SUBTOTAL]
		        ,[PORC_DSCTO1]
		        ,[MONTO_DSCTO1]
		        ,[PORC_DSCTO2]
		        ,[MONTO_DSCTO2]
		        ,[TOTAL_NETO]
		        ,[PORC_IVA]
		        ,[MONTO_IVA]
		        ,[TOTAL_CON_IVA]
		        ,[OBS]
				,[INGRESO_USUARIO_DSCTO1]
				,[INGRESO_USUARIO_DSCTO2]
				,[TIPO_ORDEN_COMPRA]
				,COD_DOC
				,[TOTAL_NETO_ORIGINAL]
				,AUTORIZADA
				,AUTORIZADA_20_PROC
				,NRO_ORDEN_COMPRA_4D
				,AUTORIZA_MONTO_COMPRA
				,USUARIO_AUTORIZA_MONTO_COMPRA
				,FECHA_AUTORIZA_MONTO_COMPRA
				,CREADA_DESDE
				,AUTORIZA_FACTURACION
           	   	,FECHA_SOLICITA_FACTURACION
				,RP_CLIENTE
				,FECHA_RP_CLIENTE
				,COD_USUARIO_RP_CLIENTE
				,ESTADO_OC_PLANO
				,NRO_COTIZACION
				,NRO_NOTA_VENTA
				,OBSERVACIONES)
			values
				(getdate()		 					
				,@ve_cod_usuario		
				,@ve_cod_usuario_solicita	
				,@ve_cod_moneda			
				,@ve_cod_estado_orden_compra	
				,@ve_cod_nota_venta		
				,@ve_cod_cuenta_corriente	
				,@ve_referencia			
				,@ve_cod_empresa		
				,@ve_cod_suc_factura		
				,@ve_cod_persona		
				,@ve_sub_total			
				,@ve_porc_dscto1		
				,@ve_monto_dscto1		
				,@ve_porc_dscto2		
				,@ve_monto_dscto2		
				,@ve_total_neto			
				,@ve_porc_iva			
				,@ve_monto_iva			
				,@ve_total_con_iva		
				,@ve_obs			
				,@ve_ingreso_usuario_dscto1
				,@ve_ingreso_usuario_dscto2
				,@ve_tipo_orden_compra
				,@ve_cod_doc
				,@ve_total_neto
				,@ve_autorizada
				,'S'
				,@nro_orden_compra_4d
				,@ve_autoriza_monto_compra
				,@vl_usuario_autoriza_monto_compra
				,@vl_fecha_autoriza_monto_compra
				,@ve_creada_desde
				,@ve_autoriza_facturacion
           	   	,@ve_fecha_aut_facturacion
				,@ve_rp_cliente
				,@vl_fecha_rp
				,@vl_usuario_rp
				,@ve_estado_oc_plano
				,@ve_nro_cotizacion
				,@ve_nro_nota_venta
				,@ve_observaciones)
				
			end 
		else if(@ve_operacion='RECALCULA')
		begin
			select @vl_ingreso_usuario_dscto1 = ingreso_usuario_dscto1
					,@vl_ingreso_usuario_dscto2 = ingreso_usuario_dscto2
					,@vl_porc_iva = isnull(porc_iva, 0)
					,@vl_monto_dscto1 = isnull(monto_dscto1, 0)
					,@vl_porc_dscto1 = isnull(porc_dscto1, 0)
					,@vl_monto_dscto2 = isnull(monto_dscto2, 0)
					,@vl_porc_dscto2 = isnull(porc_dscto2, 0)
					,@vl_total_neto_original = isnull(total_neto_original,0) 
			from orden_compra
			where cod_orden_compra = @ve_cod_orden_compra

			select @vl_sub_total = sum(round(cantidad * precio, 0))
			from item_orden_compra
			where cod_orden_compra = @ve_cod_orden_compra

			if (@vl_ingreso_usuario_dscto1='M')
				set @vl_porc_dscto1 = round((@vl_monto_dscto1 / @vl_sub_total) * 100, 1)
			else
				set @vl_monto_dscto1 = round(@vl_sub_total * @vl_porc_dscto1 /100, 0)
				
			set @vl_sub_total_con_dscto1 = @vl_sub_total - @vl_monto_dscto1
			if (@vl_ingreso_usuario_dscto2='M')
				set @vl_porc_dscto2 = round((@vl_monto_dscto2 / @vl_sub_total_con_dscto1) * 100, 1)
			else
				set @vl_monto_dscto2 = round(@vl_sub_total_con_dscto1 * @vl_porc_dscto2 / 100, 0)
			
			set @vl_total_neto = @vl_sub_total - @vl_monto_dscto1 - @vl_monto_dscto2
			set @vl_monto_iva = round(@vl_total_neto * @vl_porc_iva / 100, 0) 
			set @vl_total_con_iva = @vl_total_neto + @vl_monto_iva

			update orden_compra		
			set	subtotal					=	@vl_sub_total		
				,porc_dscto1				=	@vl_porc_dscto1	
				,monto_dscto1				=	@vl_monto_dscto1	
				,porc_dscto2				=	@vl_porc_dscto2	
				,monto_dscto2				=	@vl_monto_dscto2	
				,total_neto					=	@vl_total_neto				
				,monto_iva					=	@vl_monto_iva		
				,total_con_iva				=	@vl_total_con_iva	
			where cod_orden_compra = @ve_cod_orden_compra 	
			
			if(@vl_total_neto_original = 0)
			begin
				update orden_compra		
				set	total_neto_original = @vl_total_neto				
				where cod_orden_compra	= @ve_cod_orden_compra 
			end	
		end
		else if(@ve_operacion='AUTORIZA_MONTO_OC')begin
			SET @vl_fecha_autoriza_monto_compra = GETDATE()
			
			UPDATE ORDEN_COMPRA
			SET  AUTORIZA_MONTO_COMPRA = 'S'
				,USUARIO_AUTORIZA_MONTO_COMPRA = @ve_cod_usuario
				,FECHA_AUTORIZA_MONTO_COMPRA = @vl_fecha_autoriza_monto_compra
			WHERE COD_ORDEN_COMPRA = @ve_cod_orden_compra
		end
		else if(@ve_operacion='ACTUALIZA_DESDE_WO')begin		
			UPDATE ORDEN_COMPRA
			SET COD_ESTADO_ORDEN_COMPRA = @ve_cod_estado_orden_compra
			WHERE COD_ORDEN_COMPRA = @ve_cod_orden_compra
		end
END