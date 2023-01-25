------------------    sp_nv_orden_pago    ----------------------------
ALTER PROCEDURE sp_nv_orden_pago(@ve_cod_nota_venta numeric, 
								 @ve_cod_usuario numeric		)
AS
BEGIN
	declare @cod_empresa_vendedor1	numeric, 
			@cod_empresa_vendedor2	numeric, 
			@cod_usuario_gte		numeric, 
			@cod_usuario_dir		numeric,
			@cod_usuario_adm		numeric,
			@cod_empresa_adm		numeric,
			@iva					decimal,
			@porc_iva				decimal(10,2),
			@cod_empresa_gte_vta	numeric,
			@cod_empresa_dir		numeric,
			@vl_count				numeric,
			@vl_total_neto			numeric,
			@vl_monto_iva			numeric,
			@vl_total_con_iva		numeric

	-- participación
	select @cod_usuario_gte = valor from parametro where cod_parametro=30
	select @cod_usuario_dir = valor from parametro where cod_parametro=31
	select @cod_usuario_adm = valor from parametro where cod_parametro=41

	select @cod_empresa_vendedor1 = u.cod_empresa 
	from nota_venta nv, usuario u
	where cod_nota_venta = @ve_cod_nota_venta
	and u.cod_usuario = nv.cod_usuario_vendedor1

	select @cod_empresa_vendedor2 = u.cod_empresa 
	from nota_venta nv, usuario u
	where cod_nota_venta = @ve_cod_nota_venta
	and u.cod_usuario = nv.cod_usuario_vendedor2

	-- IVA
	select @iva = valor from parametro where cod_parametro = 1
	select @porc_iva= convert(decimal(10,2),(convert(decimal(10,2),@iva)/convert(decimal(10,2),100)))

	-- crea OP vendedor 1
	if (@cod_empresa_vendedor1 is not null) begin
		select @vl_count = count(*)
		from ORDEN_PAGO
		where cod_nota_venta = @ve_cod_nota_venta
		and cod_tipo_orden_pago = 1		-- vendedor
		and ES_ANTICIPO = 'N'

		if(@vl_count=0)begin	
			/*Verifica si tiene un anticipo del tipo op 1*/
			select @vl_count = count(*)
			from ORDEN_PAGO
			where cod_nota_venta = @ve_cod_nota_venta
			and cod_tipo_orden_pago = 1		-- vendedor
			and ES_ANTICIPO = 'S'

			if(@vl_count=0)begin
				set @vl_total_neto		= dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_V1')
				set @vl_monto_iva		= round(dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_V1') * @porc_iva, 0)
				set @vl_total_con_iva	= dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_V1') + round((dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_V1') * @porc_iva), 0)
			end
			else begin
				select @vl_total_neto = SUM(TOTAL_NETO)
				from ORDEN_PAGO
				where cod_nota_venta = @ve_cod_nota_venta
				and cod_tipo_orden_pago = 1		-- vendedor
				and ES_ANTICIPO = 'S'

				set @vl_total_neto		= dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_V1') - @vl_total_neto
				set @vl_monto_iva		= round(@vl_total_neto * @porc_iva, 0)
				set @vl_total_con_iva	= @vl_total_neto + @vl_monto_iva
			end

			insert into ORDEN_PAGO (FECHA_ORDEN_PAGO,
										COD_USUARIO,
										COD_NOTA_VENTA,
										COD_EMPRESA,
										COD_TIPO_ORDEN_PAGO,
										TOTAL_NETO,
										PORC_IVA,		--	sale de tabla parametro
										MONTO_IVA,		--	neto * %iva
										TOTAL_CON_IVA)	--	neto + monto iva
								values (getdate(), 
										@ve_cod_usuario, 
										@ve_cod_nota_venta, 
										@cod_empresa_vendedor1, 
										1, 
										@vl_total_neto, 
										@iva, 
										@vl_monto_iva, 
										@vl_total_con_iva)
		end
	end 

	-- crea OP vendedor 2
	if (@cod_empresa_vendedor2 is not null) begin
		select @vl_count = count(*)
		from ORDEN_PAGO
		where cod_nota_venta = @ve_cod_nota_venta
		  and cod_tipo_orden_pago = 1	-- vendedor

		if (@vl_count=0) begin
			insert into ORDEN_PAGO
			(	FECHA_ORDEN_PAGO, COD_USUARIO, COD_NOTA_VENTA, COD_EMPRESA,COD_TIPO_ORDEN_PAGO, TOTAL_NETO,
				PORC_IVA,--    = sale de tabla parametro
				MONTO_IVA,--   =neto * %iva
				TOTAL_CON_IVA  -- = neto + monto iva
			)
			values
			(
				getdate(), 
				@ve_cod_usuario, 
				@ve_cod_nota_venta, 
				@cod_empresa_vendedor2, 
				1, 
				dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_V2'), 
				@iva, 
				round(dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_V2') * @porc_iva, 0), 
				dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_V2') + round((dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_V2') * @porc_iva), 0)
			)
		end
	end 

	--crea OP Gte. Venta
	if (@cod_usuario_gte is not null)
	begin
		select @cod_empresa_gte_vta = cod_empresa from usuario where cod_usuario = @cod_usuario_gte


		declare 
			@vl_porc_GV numeric

		select @vl_porc_GV = dbo.f_get_parametro_porc('GV', FECHA_NOTA_VENTA)
		from nota_venta
		where cod_nota_venta = @ve_cod_nota_venta


		if (@cod_empresa_gte_vta is not null and @vl_porc_GV <> 0) begin
			select @vl_count = count(*)
			from ORDEN_PAGO
			where cod_nota_venta = @ve_cod_nota_venta
			  and cod_tipo_orden_pago = 2	-- gerente ventas

			if (@vl_count=0) begin
				insert into ORDEN_PAGO
				(	FECHA_ORDEN_PAGO, COD_USUARIO, COD_NOTA_VENTA, COD_EMPRESA,COD_TIPO_ORDEN_PAGO, TOTAL_NETO,
					PORC_IVA,--    = sale de tabla parametro
					MONTO_IVA,--   =neto * %iva
					TOTAL_CON_IVA  -- = neto + monto iva
				)
				values
				(
					getdate(), 
					@ve_cod_usuario, 
					@ve_cod_nota_venta, 
					@cod_empresa_gte_vta, 
					2, 
					dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_GV'), 
					@iva, 
					round(dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_GV') * @porc_iva, 0), 
					dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_GV') + round((dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_GV') * @porc_iva), 0)
				)
			end
		end
	end

	-- crea OP Directorio
	if (@cod_usuario_dir is not null) 
	begin
		select @cod_empresa_dir = cod_empresa from usuario where cod_usuario = @cod_usuario_dir
		if (@cod_empresa_dir is not null) begin
			select @vl_count = count(*)
			from ORDEN_PAGO
			where cod_nota_venta = @ve_cod_nota_venta
			  and cod_tipo_orden_pago = 3	-- directorio

			if (@vl_count=0) begin
				insert into ORDEN_PAGO
				(	FECHA_ORDEN_PAGO, COD_USUARIO, COD_NOTA_VENTA, COD_EMPRESA,COD_TIPO_ORDEN_PAGO, TOTAL_NETO,
					PORC_IVA,--    = sale de tabla parametro
					MONTO_IVA,--   =neto * %iva
					TOTAL_CON_IVA  -- = neto + monto iva
				)
				values
				(
					getdate(), 
					@ve_cod_usuario, 
					@ve_cod_nota_venta, 
					@cod_empresa_dir, 
					3, 
					dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'MONTO_DIRECTORIO'), 
					@iva, 
					round(dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'MONTO_DIRECTORIO') * @porc_iva, 0), 
					dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'MONTO_DIRECTORIO') + round((dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'MONTO_DIRECTORIO') * @porc_iva), 0)
				)
			end	   
		end
	end

	--crea OP Administracion SP
	if (@cod_usuario_adm is not null)
	begin
		select @cod_empresa_adm = cod_empresa from usuario where cod_usuario = @cod_usuario_adm
		if (@cod_empresa_adm is not null) begin
			select @vl_count = count(*)
			from ORDEN_PAGO
			where cod_nota_venta = @ve_cod_nota_venta
			  and cod_tipo_orden_pago = 4	-- Administracion SP

			if (@vl_count=0) begin
				insert into ORDEN_PAGO
				(	FECHA_ORDEN_PAGO, COD_USUARIO, COD_NOTA_VENTA, COD_EMPRESA,COD_TIPO_ORDEN_PAGO, TOTAL_NETO,
					PORC_IVA,--    = sale de tabla parametro
					MONTO_IVA,--   =neto * %iva
					TOTAL_CON_IVA  -- = neto + monto iva
				)
				values
				(
					getdate(), 
					@ve_cod_usuario, 
					@ve_cod_nota_venta, 
					@cod_empresa_adm, 
					4, 
					dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_ADM'), 
					@iva, 
					round(dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_AMD') * @porc_iva, 0), 
					dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_ADM') + round((dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_ADM') * @porc_iva), 0)
				)
			end
		end
	end
END