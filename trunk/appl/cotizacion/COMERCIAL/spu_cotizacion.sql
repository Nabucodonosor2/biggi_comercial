ALTER PROCEDURE [dbo].[spu_cotizacion]
			(@ve_operacion					varchar(30)
			,@ve_cod_cotizacion				numeric = null
			,@ve_fecha_cotizacion			varchar(10) = null
			,@ve_cod_usuario				numeric = null
			,@ve_cod_usuario_vend1			numeric = null
			,@ve_porc_vendedor1				T_PORCENTAJE = null
			,@ve_cod_usuario_vend2			numeric = null
			,@ve_porc_vendedor2				T_PORCENTAJE = null
			,@ve_cod_moneda					numeric = null
			,@ve_idioma						varchar(1) = null
			,@ve_referencia					varchar(100) = null
			,@ve_cod_est_cot				numeric = null
			,@ve_cod_orig_cot				numeric = null
			,@ve_cod_coti_desde				numeric = null
			,@ve_cod_empresa				numeric = null
			,@ve_cod_suc_despacho			numeric = null
			,@ve_cod_suc_factura			numeric = null
			,@ve_cod_persona				numeric = null
			,@ve_sumar_items				varchar(1) = null
			,@ve_sub_total					T_PRECIO = null
			,@ve_porc_dscto1				T_PORCENTAJE = null
			,@ve_monto_dscto1				T_PRECIO = null
			,@ve_porc_dscto2				T_PORCENTAJE = null
			,@ve_monto_dscto2				T_PRECIO = null
			,@ve_total_neto					T_PRECIO = null
			,@ve_porc_iva					T_PORCENTAJE = null
			,@ve_monto_iva					T_PRECIO = null
			,@ve_total_con_iva				T_PRECIO = null
			,@ve_cod_forma_pago				numeric = null
			,@ve_validez_oferta				numeric = null
			,@ve_entrega					varchar(100) = null
			,@ve_cod_embalaje_cot			numeric = null
			,@ve_cod_flete_cot				numeric = null
			,@ve_cod_inst_cot				numeric = null
			,@ve_garantia					varchar(100) = null
			,@ve_obs						text = null
			,@ve_posibilidad_cierre			T_PORCENTAJE = null
			,@ve_fecha_posible_cierre		varchar(10) = null
			,@ve_ingreso_usuario_dscto1		T_INGRESO_USUARIO_DSCTO = null
			,@ve_ingreso_usuario_dscto2		T_INGRESO_USUARIO_DSCTO = null
			,@ve_nom_forma_pago_otro		varchar(100) = null
			,@ve_cod_solicitud_cotizacion	numeric(10) = null
			,@ve_cod_tipo_rechazo			numeric(10) = null
			,@ve_rechazo					T_SI_NO = null
			,@ve_texto_rechazo				text = null)

AS
	declare @vl_ingreso_usuario_dscto1 T_INGRESO_USUARIO_DSCTO
			,@vl_ingreso_usuario_dscto2 T_INGRESO_USUARIO_DSCTO
			,@vl_porc_iva T_PORCENTAJE
			,@vl_monto_dscto1 T_PRECIO
			,@vl_porc_dscto1 T_PORCENTAJE
			,@vl_monto_dscto2 T_PRECIO
			,@vl_porc_dscto2 T_PORCENTAJE
			,@vl_sub_total T_PRECIO
			,@vl_sub_total_con_dscto1 T_PRECIO
			,@vl_total_neto T_PRECIO
			,@vl_monto_iva T_PRECIO
			,@vl_total_con_iva T_PRECIO
			,@K_ESTADO_COTIZACION	NUMERIC(2)
			,@vl_cod_cotizacion		NUMERIC
			,@vl_fecha_compromiso	DATETIME

BEGIN
	SET @K_ESTADO_COTIZACION	= 2 --estado Pendiente
		if (@ve_operacion='UPDATE')begin
				
				if(@ve_rechazo = 'S')begin
					declare 
						@ve_contacto			varchar(100),
						@vl_telefono			varchar(100),
						@vl_mail				varchar(100),
						@vl_glosa				varchar(500),
						@vl_nom_tipo_rechazo	varchar(100)
						
					select @ve_contacto = NOM_PERSONA
						  ,@vl_telefono = TELEFONO
						  ,@vl_mail		= EMAIL
					from PERSONA
					where COD_PERSONA = @ve_cod_persona	
					
					if(@vl_telefono is NULL or @vl_telefono = '')begin
						select @vl_telefono = dbo.f_get_direccion('SUCURSAL', @ve_cod_suc_factura, '[TELEFONO]')
					end
					
					select @vl_nom_tipo_rechazo = nom_tipo_rechazo 
					from tipo_rechazo
					where cod_tipo_rechazo = @ve_cod_tipo_rechazo
					
					set @ve_cod_est_cot = 5 -- estado rechazada
					set @vl_fecha_compromiso = getdate()
					set @vl_glosa = 'Cotización Rechazada '+ convert(varchar,getdate(),103) + ' ' + @vl_nom_tipo_rechazo +' '+ convert(varchar,@ve_texto_rechazo)
					
					exec spu_bitacora_cotizacion 'INSERT'
						,null
						,@ve_cod_usuario
						,@ve_cod_cotizacion
						,3 
						,@ve_contacto
						,@vl_telefono				
						,@vl_mail				
						,@vl_glosa	
						,'S'				
						,@vl_fecha_compromiso 
						,@ve_texto_rechazo			
						,'S'
						,@ve_cod_persona
						,'S'
						
						-- exec spu_cotizacion 'ANULADA_SOLI_COT',@ve_cod_cotizacion
				end
								
				UPDATE cotizacion		
				SET			fecha_cotizacion			=	dbo.to_date(@ve_fecha_cotizacion)		 
							,cod_usuario				=	@ve_cod_usuario	
							,cod_usuario_vendedor1		=	@ve_cod_usuario_vend1	
							,porc_vendedor1				=	@ve_porc_vendedor1	
							,cod_usuario_vendedor2		=	@ve_cod_usuario_vend2	
							,porc_vendedor2				=	@ve_porc_vendedor2
							,cod_moneda					=	@ve_cod_moneda		
							,idioma						=	@ve_idioma		
							,referencia					=	@ve_referencia		
							,cod_estado_cotizacion		=	@ve_cod_est_cot	
							,cod_origen_cotizacion		=	@ve_cod_orig_cot	
							,cod_cotizacion_desde		=	@ve_cod_coti_desde
							,cod_empresa				=	@ve_cod_empresa	
							,cod_sucursal_despacho		=	@ve_cod_suc_despacho	
							,cod_sucursal_factura		=	@ve_cod_suc_factura	
							,cod_persona				=	@ve_cod_persona	
							,sumar_items				=	@ve_sumar_items	
							,subtotal					=	@ve_sub_total		
							,porc_dscto1				=	@ve_porc_dscto1	
							,monto_dscto1				=	@ve_monto_dscto1	
							,porc_dscto2				=	@ve_porc_dscto2	
							,monto_dscto2				=	@ve_monto_dscto2	
							,total_neto					=	@ve_total_neto	
							,porc_iva					=	@ve_porc_iva		
							,monto_iva					=	@ve_monto_iva		
							,total_con_iva				=	@ve_total_con_iva	
							,cod_forma_pago				=	@ve_cod_forma_pago
							,validez_oferta				=	@ve_validez_oferta	
							,entrega					=	@ve_entrega		
							,cod_embalaje_cotizacion	=	@ve_cod_embalaje_cot	
							,cod_flete_cotizacion		=	@ve_cod_flete_cot	
							,cod_instalacion_cotizacion	=	@ve_cod_inst_cot	
							,garantia					=	@ve_garantia		
							,obs						=	@ve_obs	
							,posibilidad_cierre			=	@ve_posibilidad_cierre	
							,fecha_posible_cierre		=	dbo.to_date(@ve_fecha_posible_cierre)
							,ingreso_usuario_dscto1		=	@ve_ingreso_usuario_dscto1
							,ingreso_usuario_dscto2		=	@ve_ingreso_usuario_dscto2
							,nom_forma_pago_otro		=	@ve_nom_forma_pago_otro
							,cod_solicitud_cotizacion	=	@ve_cod_solicitud_cotizacion
							,cod_tipo_rechazo			=	@ve_cod_tipo_rechazo
							,rechazada					=	@ve_rechazo
							,texto_rechazo				=	@ve_texto_rechazo
				WHERE cod_cotizacion = @ve_cod_cotizacion
				
			end
		else if (@ve_operacion='INSERT') 
			begin
				insert into cotizacion
					(fecha_registro_cotizacion
					,fecha_cotizacion
					,cod_usuario
					,cod_usuario_vendedor1
					,porc_vendedor1
					,cod_usuario_vendedor2
					,porc_vendedor2
					,cod_moneda
					,idioma
					,referencia
					,cod_estado_cotizacion
					,cod_origen_cotizacion
					,cod_cotizacion_desde
					,cod_empresa
					,cod_sucursal_despacho
					,cod_sucursal_factura
					,cod_persona
					,sumar_items
					,subtotal
					,porc_dscto1
					,monto_dscto1
					,porc_dscto2
					,monto_dscto2
					,total_neto
					,porc_iva
					,monto_iva
					,total_con_iva
					,cod_forma_pago
					,validez_oferta
					,entrega
					,cod_embalaje_cotizacion
					,cod_flete_cotizacion
					,cod_instalacion_cotizacion
					,garantia
					,obs
					,posibilidad_cierre
					,fecha_posible_cierre
					,ingreso_usuario_dscto1
					,ingreso_usuario_dscto2
					,nom_forma_pago_otro
					,VALOR_TIPO_CAMBIO
					,cod_solicitud_cotizacion
					,cod_tipo_rechazo
					,rechazada
					,texto_rechazo)
				values 
					(getdate()
					,dbo.f_makedate(day(getdate()), month(getdate()), year(getdate()))
					,@ve_cod_usuario	
					,@ve_cod_usuario_vend1	
					,@ve_porc_vendedor1	
					,@ve_cod_usuario_vend2	
					,@ve_porc_vendedor2	
					,@ve_cod_moneda		
					,@ve_idioma		
					,@ve_referencia		
					,@K_ESTADO_COTIZACION					--@ve_cod_est_cot  = dicha cotización debe quedar en cod_estado_cotizacion = 2 (PENDIENTE)	
					,@ve_cod_orig_cot	
					,@ve_cod_coti_desde
					,@ve_cod_empresa	
					,@ve_cod_suc_despacho	
					,@ve_cod_suc_factura	
					,@ve_cod_persona	
					,@ve_sumar_items	
					,@ve_sub_total		
					,@ve_porc_dscto1	
					,@ve_monto_dscto1	
					,@ve_porc_dscto2	
					,@ve_monto_dscto2	
					,@ve_total_neto		
					,@ve_porc_iva		
					,@ve_monto_iva		
					,@ve_total_con_iva	
					,@ve_cod_forma_pago	
					,@ve_validez_oferta	
					,@ve_entrega		
					,@ve_cod_embalaje_cot	
					,@ve_cod_flete_cot	
					,@ve_cod_inst_cot	
					,@ve_garantia		
					,@ve_obs		
					,@ve_posibilidad_cierre	
					,dbo.to_date(@ve_fecha_posible_cierre)
					,@ve_ingreso_usuario_dscto1
					,@ve_ingreso_usuario_dscto2
					,@ve_nom_forma_pago_otro
					,1
					,@ve_cod_solicitud_cotizacion
					,@ve_cod_tipo_rechazo
					,@ve_rechazo
					,@ve_texto_rechazo)
					
					set @vl_cod_cotizacion = @@identity
					exec spu_cotizacion 'COTIZADA_SOLI_COT',@vl_cod_cotizacion
			end
		
		else if (@ve_operacion='RE-ABRIR') 
			begin
				declare
					@vl_cod_usuario			numeric(10,0),
					@vl_cod_persona			numeric(10,0),
					@vl_contacto			varchar(100),
					@vl_telefono2			varchar(100),
					@vl_mail2				varchar(100),
					@vl_glosa2				varchar(1000),
					@vl_cot_cotizacion_new	numeric(10),
					@vl_fecha_compromiso2	datetime
				
				update cotizacion
				set COD_ESTADO_COTIZACION = 6	-- Re-Abierta
				where COD_COTIZACION = @ve_cod_cotizacion
				
				select @vl_cod_usuario = cod_usuario
					  ,@vl_cod_persona = cod_persona
				from cotizacion
				where COD_COTIZACION = @ve_cod_cotizacion
				
				select @vl_contacto		= NOM_PERSONA
					  ,@vl_telefono2	= TELEFONO
					  ,@vl_mail2		= EMAIL
				from PERSONA
				where COD_PERSONA = @ve_cod_persona
				
				select @vl_cot_cotizacion_new = MAX(COD_COTIZACION) 
				from COTIZACION
			
				set @vl_fecha_compromiso2 = getdate()
				
				--@ve_cod_usuario: Se re-utiliza esta variable para pasar como parametro si viene desde una nota_venta
				if(@ve_cod_usuario is null)
					set @vl_glosa2 = 'Cotizacion Re-Abierta el '+ convert(varchar,getdate(),103) +' , segun Cotización N° ' + convert(varchar,@vl_cot_cotizacion_new)
				else
					set @vl_glosa2 = 'Cotizacion Re-Abierta el '+ convert(varchar,getdate(),103) +' , segun Nota Venta N° ' + convert(varchar,@ve_cod_usuario)	
					
				exec spu_bitacora_cotizacion 'INSERT'
					,null
					,@vl_cod_usuario
					,@ve_cod_cotizacion
					,4
					,@vl_contacto
					,@vl_telefono2
					,@vl_mail2
					,@vl_glosa2
					,'S'
					,@vl_fecha_compromiso2
					,@vl_glosa2
					,'S'
					,@vl_cod_persona
					,'N'
						
			end	
			
		else if(@ve_operacion='ERROR_PRECIO_DIV_1000')
		begin
			declare	@vc_cod_item_cotizacion	numeric(10),
					@vc_cod_producto		varchar(30),
					@vc_precio				numeric(14,2),
					@vl_precio				numeric(14,2),
					@vl_count				numeric(10),
					@vl_cod_log_cambio		numeric(10)
		
			declare c_item_cot insensitive cursor for
			select cod_item_cotizacion
				  ,cod_producto
				  ,precio
			from item_cotizacion
			where cod_cotizacion = @ve_cod_cotizacion
			and COD_PRODUCTO not in ('TE','T','E','I','F')
		
			open c_item_cot
			fetch c_item_cot into @vc_cod_item_cotizacion, @vc_cod_producto, @vc_precio
			while @@fetch_status = 0 begin
				
				select @vl_count = COUNT(*)
				from modifica_precio_cotizacion
				where cod_item_cotizacion = @vc_cod_item_cotizacion
			
				if(@vl_count = 0)begin
					select @vl_precio = precio_venta_publico
					from producto
					where cod_producto = @vc_cod_producto
					
					if(@vc_precio = floor(@vl_precio/1000))begin	
						update item_cotizacion
						set precio = @vl_precio
						where cod_item_cotizacion = @vc_cod_item_cotizacion
					
						select @vl_cod_log_cambio = max(cod_log_cambio)
						from log_cambio
						where nom_tabla = 'COTIZACION'
						and key_tabla = convert(varchar, @ve_cod_cotizacion)
						
						insert into detalle_cambio_relacionada
						values (@vl_cod_log_cambio, 'ITEM_COTIZACION', 'PRECIO_RECALCULA', @vc_cod_item_cotizacion, @vl_precio, @vc_precio, 'U')
					end
				end
		
				fetch c_item_cot into @vc_cod_item_cotizacion, @vc_cod_producto, @vc_precio
			end
			close c_item_cot
			deallocate c_item_cot
		end			
		else if(@ve_operacion='RECALCULA')
		begin
			DECLARE
				@vl_porc_participacion1	T_PORCENTAJE,
				@vl_porc_participacion2	T_PORCENTAJE,
				@vl_porc_vendedor1		T_PORCENTAJE,
				@vl_porc_vendedor2		T_PORCENTAJE,
				@vl_cod_usuario_vend1	NUMERIC(3),
				@vl_cod_usuario_vend2	NUMERIC(3)
			
			exec spu_cotizacion 'ERROR_PRECIO_DIV_1000', @ve_cod_cotizacion
			
			select @vl_ingreso_usuario_dscto1 = ingreso_usuario_dscto1
					,@vl_ingreso_usuario_dscto2 = ingreso_usuario_dscto2
					,@vl_porc_iva = isnull(porc_iva, 0)
					,@vl_monto_dscto1 = isnull(monto_dscto1, 0)
					,@vl_porc_dscto1 = isnull(porc_dscto1, 0)
					,@vl_monto_dscto2 = isnull(monto_dscto2, 0)
					,@vl_porc_dscto2 = isnull(porc_dscto2, 0)
					,@vl_porc_vendedor1 = porc_vendedor1
					,@vl_porc_vendedor2 = isnull(porc_vendedor2,0)
					,@vl_cod_usuario_vend1 = cod_usuario_vendedor1
					,@vl_cod_usuario_vend2 = cod_usuario_vendedor2
			from cotizacion
			where cod_cotizacion = @ve_cod_cotizacion

			select @vl_sub_total = isnull(sum(round(cantidad * precio, 0)), 0)
			from item_cotizacion
			where cod_cotizacion = @ve_cod_cotizacion

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

			update cotizacion		
			set	subtotal					=	@vl_sub_total		
				,porc_dscto1				=	@vl_porc_dscto1	
				,monto_dscto1				=	@vl_monto_dscto1	
				,porc_dscto2				=	@vl_porc_dscto2	
				,monto_dscto2				=	@vl_monto_dscto2	
				,total_neto					=	@vl_total_neto				
				,monto_iva					=	@vl_monto_iva		
				,total_con_iva				=	@vl_total_con_iva	
			where cod_cotizacion = @ve_cod_cotizacion 
				-- and sumar_items = 'S' = esta condición no esta implementada, todas las cotizaciones estan "sumar_items" = 'N'
			
			-- Recalcula porcentaje usuario vendedores
			SELECT @vl_porc_participacion1 = PORC_PARTICIPACION
			FROM USUARIO
			WHERE COD_USUARIO = @vl_cod_usuario_vend1
			
			SELECT @vl_porc_participacion2 = PORC_PARTICIPACION
			FROM USUARIO
			WHERE COD_USUARIO = @vl_cod_usuario_vend2
			
			if(@vl_porc_vendedor1 > @vl_porc_participacion1)begin
				UPDATE COTIZACION
				SET PORC_VENDEDOR1 = @vl_porc_participacion1
				WHERE COD_COTIZACION = @ve_cod_cotizacion
			end
			
			if(@vl_porc_vendedor2 > @vl_porc_participacion2)begin
				UPDATE COTIZACION
				SET PORC_VENDEDOR2 = @vl_porc_participacion2
				WHERE COD_COTIZACION = @ve_cod_cotizacion
			end
					
		end
		else if(@ve_operacion='COTIZADA_SOLI_COT')
		begin
			DECLARE
					@K_COD_ESTADO_COTIZADA numeric 
					,@vl_cod_solicitud_cot	numeric
					,@vl_cod_usuario_soli_cot numeric
					
				SET @K_COD_ESTADO_COTIZADA	= 2 --estado cotizada
				
			select @vl_cod_solicitud_cot = COD_SOLICITUD_COTIZACION
					,@vl_cod_usuario_soli_cot = COD_USUARIO_VENDEDOR1	
			from COTIZACION
			where COD_COTIZACION = @ve_cod_cotizacion
			
			update SOLICITUD_COTIZACION
			set	cod_estado_solicitud_cotizacion = @K_COD_ESTADO_COTIZADA
				,COD_USUARIO_VENDEDOR1_RESP = @vl_cod_usuario_soli_cot
			where cod_solicitud_cotizacion = @vl_cod_solicitud_cot
		end	
		else if(@ve_operacion='ANULADA_SOLI_COT')
		begin
			
			DECLARE
					@K_COD_ESTADO_ANULADA	numeric
					
				SET @K_COD_ESTADO_ANULADA	= 5 --estado anulada
			
			select @vl_cod_solicitud_cot = COD_SOLICITUD_COTIZACION from COTIZACION
			where COD_COTIZACION = @ve_cod_cotizacion
			
			update SOLICITUD_COTIZACION
			set	cod_estado_solicitud_cotizacion = @K_COD_ESTADO_ANULADA
			where cod_solicitud_cotizacion = @vl_cod_solicitud_cot
		end	
END