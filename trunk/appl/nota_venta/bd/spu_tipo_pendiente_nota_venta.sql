alter PROCEDURE spu_tipo_pendiente_nota_venta(@ve_operacion varchar(20), @ve_cod_nota_venta numeric, @ve_cod_tipo_pendiente_nota_venta numeric = NULL, @ve_cod_usuario numeric = NULL, @ve_motivo varchar(100)= NULL)
AS
BEGIN
	if (@ve_operacion='LOAD') 
	begin	
		declare @vl_cant_tipo_pendiente_GD numeric,
				@vl_cant_por_despachar T_CANTIDAD,
				@vl_porc_pagos T_PORCENTAJE,
				@vl_cant_porc_pagos numeric

		delete tipo_pendiente_nota_venta
		where cod_nota_venta = @ve_cod_nota_venta and
			AUTORIZA = 'N'

		-----------------Tipo Pendiente = Guia Despacho-------------------
		select @vl_cant_tipo_pendiente_GD = count(*) 
		from tipo_pendiente_nota_venta
		where cod_nota_venta = @ve_cod_nota_venta and
			AUTORIZA = 'S' and
			cod_tipo_pendiente = 1 -- GD	
		
		if (@vl_cant_tipo_pendiente_GD = 0)	
		begin	
			select @vl_cant_por_despachar = sum(dbo.f_nv_cant_por_despachar(cod_item_nota_venta, default))
			from item_nota_venta
			where cod_nota_venta = @ve_cod_nota_venta
			
			if (@vl_cant_por_despachar > 0)
				insert into tipo_pendiente_nota_venta (cod_nota_venta, cod_tipo_pendiente, autoriza, motivo)		
				values (@ve_cod_nota_venta, 1, 'N', '')
		end	
		
		-----------------Tipo Pendiente = Pago cliente-------------------	
		select @vl_cant_porc_pagos = count(*) 
		from tipo_pendiente_nota_venta
		where cod_nota_venta = @ve_cod_nota_venta and
			AUTORIZA = 'S' and
			cod_tipo_pendiente = 2 -- PAGOS
		if (@vl_cant_porc_pagos = 0)	
		begin				
			select @vl_porc_pagos = Round((dbo.f_nv_total_pago(COD_NOTA_VENTA) / TOTAL_CON_IVA) * 100, 0)
			from nota_venta
			where cod_nota_venta = @ve_cod_nota_venta
			if (@vl_porc_pagos <> 100)
				insert into tipo_pendiente_nota_venta (cod_nota_venta, cod_tipo_pendiente, autoriza, motivo)		
				values (@ve_cod_nota_venta, 2, 'N', '')
		end

		-----------------Tipo Pendiente = Resultado sobre el 30%-------------------	
		declare
			@porc_resultado		numeric(14,2)
			,@porc_max			numeric(14,2)
			,@count				numeric
			,@participacion_permitida	numeric
			,@participacion1			numeric
			,@participacion2			numeric
		select @count = count(*) 
		from tipo_pendiente_nota_venta
		where cod_nota_venta = @ve_cod_nota_venta and
			AUTORIZA = 'S' and
			cod_tipo_pendiente = 3 -- PORCENTAJE MUY ALTO
		if (@count = 0)	
		begin				
			set @porc_resultado = dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'PORC_RESULTADO')
			set @porc_max = convert(numeric(14,2), dbo.f_get_parametro(45))	-- porc maximo
			if (@porc_resultado >= @porc_max)	
			begin				
				set @participacion_permitida = convert(numeric, dbo.f_get_parametro(51))	-- si el resultado es > 30% pero monto < param51 => peude cerrar
				set @participacion1 = dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_V1')
				set @participacion2 = dbo.f_nv_get_resultado(@ve_cod_nota_venta, 'COMISION_V2')
				if (@participacion1 > @participacion_permitida or @participacion2 > @participacion_permitida)
					insert into tipo_pendiente_nota_venta 
						(cod_nota_venta, cod_tipo_pendiente, autoriza, motivo)		
					values 
						(@ve_cod_nota_venta, 3, 'N', '')
			end
		end

		-----------------Tipo Pendiente = OC con item en CERO-------------------	
		select @count = count(*) 
		from tipo_pendiente_nota_venta
		where cod_nota_venta = @ve_cod_nota_venta and
			AUTORIZA = 'S' and
			cod_tipo_pendiente = 4 -- OC con item en CERO
		if (@count = 0)	
		begin				
			select @count = count(*) 
			from orden_compra oc, item_orden_compra it
			where oc.cod_nota_venta = @ve_cod_nota_venta
			  and cod_estado_orden_compra <> 2 -- anulada
			  and it.cod_orden_compra = oc.cod_orden_compra
			  and (it.precio = 0 or it.cantidad =0)
			if (@count > 0)	
			begin				
				insert into tipo_pendiente_nota_venta (cod_nota_venta, cod_tipo_pendiente, autoriza, motivo)		
				values (@ve_cod_nota_venta, 4, 'N', '')
			end
		end

		-----------------Tipo Pendiente = facturacion -------------------
		declare
			@vl_cant_tipo_pendiente_FA	numeric
			,@vl_porc_por_facturar		T_PORCENTAJE

		select @vl_cant_tipo_pendiente_FA = count(*) 
		from tipo_pendiente_nota_venta
		where cod_nota_venta = @ve_cod_nota_venta and
			AUTORIZA = 'S' and
			cod_tipo_pendiente = 5 -- FACTURACION
		
		if (@vl_cant_tipo_pendiente_FA = 0)	
		begin	
			select @vl_porc_por_facturar = dbo.f_nv_porc_facturado(@ve_cod_nota_venta)
			
			if (@vl_porc_por_facturar <> 100)
				insert into tipo_pendiente_nota_venta (cod_nota_venta, cod_tipo_pendiente, autoriza, motivo)		
				values (@ve_cod_nota_venta, 5, 'N', '')
		end

		-----------------Tipo Pendiente = facturas pendientes de pago  -------------------
		declare	
			@vl_count		numeric

		select @vl_cant_tipo_pendiente_FA = count(*) 
		from tipo_pendiente_nota_venta
		where cod_nota_venta = @ve_cod_nota_venta and
			AUTORIZA = 'S' and
			cod_tipo_pendiente = 6 -- FA pendiente de pago
		
		if (@vl_cant_tipo_pendiente_FA = 0)	begin	
			select @vl_count = count(*) 
			from   FACTURA
			where  COD_DOC = @ve_cod_nota_venta
  			and  COD_TIPO_FACTURA = 1	-- venta
  			and  COD_ESTADO_DOC_SII in (2,3)
  			and dbo.f_fa_saldo(cod_factura) > 0

			if (@vl_count > 0)
				insert into tipo_pendiente_nota_venta (cod_nota_venta, cod_tipo_pendiente, autoriza, motivo)		
				values (@ve_cod_nota_venta, 6, 'N', '')
		end
			-----------------Tipo Pendiente = nota credito   -------------------
		declare	
			@vl_cant_tipo_pendiente_NC		numeric,
			@vl_count_nc					numeric
			

		select @vl_cant_tipo_pendiente_NC = count(*) 
		from tipo_pendiente_nota_venta
		where cod_nota_venta = @ve_cod_nota_venta and
			AUTORIZA = 'S' and
			cod_tipo_pendiente = 7 -- FA pendiente de pago
		
		if (@vl_cant_tipo_pendiente_NC = 0)	begin	
			select  @vl_count_nc = COUNT(*)
			from   NOTA_CREDITO
			where  COD_ESTADO_DOC_SII in (2, 3)
			and	   COD_DOC IN (SELECT COD_FACTURA
					FROM FACTURA
					WHERE COD_DOC = @ve_cod_nota_venta
					AND	COD_TIPO_FACTURA = 1
					and	   COD_ESTADO_DOC_SII in (2, 3))

			if (@vl_count_nc > 0)
				insert into tipo_pendiente_nota_venta (cod_nota_venta, cod_tipo_pendiente, autoriza, motivo)		
				values (@ve_cod_nota_venta, 7, 'N', '')
		end
		
		--MH 20/01/2015 17:49: Marcelo solicitó comentar este codigo por el momento
			-----------------Tipo Pendiente = OC sin factura proveedor   -------------------
		/*declare	
			@vl_cant_tipo_pendiente		numeric,
			@vc_cod_oc					numeric,
			@vc_total_con_iva			numeric,
			@vl_monto_asig				numeric,
			@vl_count2					numeric
			
		select @vl_cant_tipo_pendiente = count(*) 
		from tipo_pendiente_nota_venta
		where cod_nota_venta = @ve_cod_nota_venta and
			AUTORIZA = 'S' and
			cod_tipo_pendiente = 8 -- FAPROV
		
		if (@vl_cant_tipo_pendiente = 0)	begin
			set @vl_count2 = 0
			
			DECLARE C_OC CURSOR FOR
			select cod_orden_compra, total_con_iva
			from orden_compra
			where cod_nota_venta = @ve_cod_nota_venta
			and tipo_orden_compra = 'NOTA_VENTA'
			
			OPEN C_OC
			FETCH C_OC INTO @vc_cod_oc, @vc_total_con_iva
			WHILE @@FETCH_STATUS = 0 BEGIN
				set @vl_monto_asig = 0
				
				select @vl_monto_asig = isnull(sum(monto_asignado), 0)
				from item_faprov ifa, faprov fa
				where cod_doc = @vc_cod_oc
				and cod_estado_faprov = 2	--aprobada
				and ifa.cod_faprov = fa.cod_faprov
				group by cod_doc
					
				if(@vl_monto_asig < @vc_total_con_iva)
					set @vl_count2 = 1
				
				FETCH C_OC INTO @vc_cod_oc, @vc_total_con_iva
			END
			CLOSE C_OC
			DEALLOCATE C_OC											
			
			if(@vl_count2 = 1)
				insert into tipo_pendiente_nota_venta (cod_nota_venta, cod_tipo_pendiente, autoriza, motivo)		
				values (@ve_cod_nota_venta, 8, 'N', '')
		end*/
		
	end
	else if (@ve_operacion='UPDATE')
		update tipo_pendiente_nota_venta
		set autoriza = 'S',
			fecha_autoriza = getdate(),
			cod_usuario = @ve_cod_usuario,
			motivo = @ve_motivo
		where cod_tipo_pendiente_nota_venta = @ve_cod_tipo_pendiente_nota_venta		
END
go