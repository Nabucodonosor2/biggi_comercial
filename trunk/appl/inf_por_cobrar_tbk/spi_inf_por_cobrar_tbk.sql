ALTER PROCEDURE spi_inf_por_cobrar_tbk(@ve_cod_usuario	numeric)
AS
BEGIN
	DECLARE
	@vl_count					numeric,
	@vl_cod_nota_venta			numeric,
	@vl_fecha_nota_venta		datetime,
	@vl_rut						numeric,
	@vl_dig_verif				varchar(1),
	@vl_nom_empresa				varchar(100),
	@vl_total_con_iva			numeric,
	@vl_comision				numeric,
	@vl_total_por_cobrar		numeric,
	@vc_cod_ingreso_pago		numeric,
	@vc_fecha_ingreso_pago		datetime,
	@vc_sum_monto_doc			numeric,
	@vc_nro_cuotas_tbk			numeric,
	@vc_cod_doc_ingreso_pago	numeric,
	@vl_cod_nota_venta_nv		numeric,
	@vl_cod_nota_venta_fa		numeric

	-- borra el resultado de informes anteriores del mismo usuario
	DELETE INF_POR_COBRAR_TBK
	WHERE cod_usuario = @ve_cod_usuario

	--------------------------------------------------Tarjeta Dédito------------------------------------------------------------------------

	DECLARE C_INF_POR_COBRAR_TBK_DEBITO cursor for
	SELECT INP.COD_INGRESO_PAGO
		  ,FECHA_INGRESO_PAGO
		  ,SUM(MONTO_DOC)
		  ,COD_DOC_INGRESO_PAGO
	FROM INGRESO_PAGO INP
		,DOC_INGRESO_PAGO DIP
	WHERE INCLUIR_INF_TBK = 'S'
	AND COD_TIPO_DOC_PAGO = 5										-- Débito
	AND INP.COD_ESTADO_INGRESO_PAGO = 2								-- Estado confirmado
	AND FECHA_INGRESO_PAGO >= dbo.to_date(dbo.f_get_parametro(78))
	AND INP.COD_INGRESO_PAGO = DIP.COD_INGRESO_PAGO
	GROUP BY INP.COD_INGRESO_PAGO, FECHA_INGRESO_PAGO, COD_DOC_INGRESO_PAGO
	ORDER BY INP.COD_INGRESO_PAGO ASC
	

	OPEN C_INF_POR_COBRAR_TBK_DEBITO
	FETCH C_INF_POR_COBRAR_TBK_DEBITO INTO @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, @vc_sum_monto_doc, @vc_cod_doc_ingreso_pago
	WHILE @@FETCH_STATUS = 0
	BEGIN
		/*CUANDO SON VARIAS NOTAS DE VENTA y DISTINTAS, LOS CAMPOS SIGUIENTES SE RELLENARAN CON LO SIGUIENTE:
			COD_NOTA_VENTA		=> 1
			FECHA_NOTA_VENTA	=> GETDATE()
			RUT y DIG_VERIF		=> 91462001-5
			RAZON_SOCIAL		=> Varios
			TOTAL_CON_IVA		=> 0
		*/
		SELECT @vl_count = COUNT(DISTINCT COD_DOC)
		FROM INGRESO_PAGO_FACTURA
		WHERE COD_INGRESO_PAGO = @vc_cod_ingreso_pago
		AND TIPO_DOC = 'NOTA_VENTA'

		IF(@vl_count = 0)BEGIN
			-- no hay NV que coincidan pero intentara si hay FA asociadas a una NV
			SELECT @vl_count = COUNT(DISTINCT dbo.f_get_nv_from_fa(COD_DOC))
			FROM INGRESO_PAGO_FACTURA
			WHERE COD_INGRESO_PAGO = @vc_cod_ingreso_pago
			AND TIPO_DOC = 'FACTURA'

			IF(@vl_count = 0)BEGIN
				-- Es un error
				return 'Error'
			END
			ELSE IF(@vl_count = 1)BEGIN
				-- Encontro FA asociada a una NV para desplegar datos
				SELECT DISTINCT @vl_cod_nota_venta = dbo.f_get_nv_from_fa(COD_DOC)
				FROM INGRESO_PAGO_FACTURA
				WHERE COD_INGRESO_PAGO = @vc_cod_ingreso_pago
				AND TIPO_DOC = 'FACTURA'

				SELECT @vl_fecha_nota_venta = FECHA_NOTA_VENTA
					 ,@vl_rut = RUT
					 ,@vl_dig_verif = DIG_VERIF
					 ,@vl_nom_empresa = NOM_EMPRESA
					 ,@vl_total_con_iva = TOTAL_CON_IVA
				FROM NOTA_VENTA NV
					,EMPRESA E
				WHERE COD_NOTA_VENTA = @vl_cod_nota_venta
				AND E.COD_EMPRESA = NV.COD_EMPRESA

				set @vl_comision			= (@vc_sum_monto_doc * CAST(dbo.f_get_parametro(80) AS NUMERIC(18,2)))/100
				set @vl_total_por_cobrar	= @vc_sum_monto_doc - @vl_comision

				INSERT INTO INF_POR_COBRAR_TBK ([COD_INGRESO_PAGO],		[FECHA_INGRESO_PAGO],	[COD_NOTA_VENTA],		[FECHA_NOTA_VENTA],			[RUT_CLIENTE],			[DIG_VERIF],
												[RAZON_SOCIAL],			[TOTAL_CON_IVA],		[MONTO_DEBITO],			[MONTO_CREDITO],			[CUOTAS_CREDITO],		[COMISION_DEBITO],
												[COMISION_CREDITO],		[TOTAL_POR_COBRAR],		[COD_USUARIO],			[COD_DOC_INGRESO_PAGO])
										VALUES (@vc_cod_ingreso_pago,	@vc_fecha_ingreso_pago,	@vl_cod_nota_venta,		@vl_fecha_nota_venta,		@vl_rut,				@vl_dig_verif,
												@vl_nom_empresa,		@vl_total_con_iva,		@vc_sum_monto_doc,		0,							0,						@vl_comision,
												0,						@vl_total_por_cobrar,	@ve_cod_usuario,		@vc_cod_doc_ingreso_pago)
			END
			ELSE BEGIN
				-- Encontro varias FA asociadas pero con distinto NV por ende son distintas

				set @vl_comision			= (@vc_sum_monto_doc * CAST(dbo.f_get_parametro(80) AS NUMERIC(18,2)))/100
				set @vl_total_por_cobrar	= @vc_sum_monto_doc - @vl_comision

				INSERT INTO INF_POR_COBRAR_TBK ([COD_INGRESO_PAGO],		[FECHA_INGRESO_PAGO],	[COD_NOTA_VENTA],	[FECHA_NOTA_VENTA],			[RUT_CLIENTE],		[DIG_VERIF],
												[RAZON_SOCIAL],			[TOTAL_CON_IVA],		[MONTO_DEBITO],		[MONTO_CREDITO],			[CUOTAS_CREDITO],	[COMISION_DEBITO],
												[COMISION_CREDITO],		[TOTAL_POR_COBRAR],		[COD_USUARIO],		[COD_DOC_INGRESO_PAGO])
										VALUES (@vc_cod_ingreso_pago,	@vc_fecha_ingreso_pago,	1,					GETDATE(),					91462001,			'5',
												'Varios',				0,						@vc_sum_monto_doc,	0,							0,					@vl_comision,
												0,						@vl_total_por_cobrar,	@ve_cod_usuario,	@vc_cod_doc_ingreso_pago)
			END
		END
		ELSE IF(@vl_count = 1)BEGIN
			-- Encontro NV pero vera si existe FA asociadas
			SELECT @vl_count = COUNT(DISTINCT dbo.f_get_nv_from_fa(COD_DOC))
			FROM INGRESO_PAGO_FACTURA
			WHERE COD_INGRESO_PAGO = @vc_cod_ingreso_pago
			AND TIPO_DOC = 'FACTURA'
			
			IF(@vl_count = 0)BEGIN
				-- hay NV pero no hay FA por ende lanza los datos como tal desde la NV
				
				SELECT DISTINCT @vl_cod_nota_venta = COD_DOC
				FROM INGRESO_PAGO_FACTURA
				WHERE COD_INGRESO_PAGO = @vc_cod_ingreso_pago
				AND TIPO_DOC = 'NOTA_VENTA'

				SELECT @vl_fecha_nota_venta = FECHA_NOTA_VENTA
					 ,@vl_rut = RUT
					 ,@vl_dig_verif = DIG_VERIF
					 ,@vl_nom_empresa = NOM_EMPRESA
					 ,@vl_total_con_iva = TOTAL_CON_IVA
				FROM NOTA_VENTA NV
					,EMPRESA E
				WHERE COD_NOTA_VENTA = @vl_cod_nota_venta
				AND E.COD_EMPRESA = NV.COD_EMPRESA

				set @vl_comision			= (@vc_sum_monto_doc * CAST(dbo.f_get_parametro(80) AS NUMERIC(18,2)))/100
				set @vl_total_por_cobrar	= @vc_sum_monto_doc - @vl_comision

				INSERT INTO INF_POR_COBRAR_TBK ([COD_INGRESO_PAGO],		[FECHA_INGRESO_PAGO],	[COD_NOTA_VENTA],	[FECHA_NOTA_VENTA],			[RUT_CLIENTE],		[DIG_VERIF],
												[RAZON_SOCIAL],			[TOTAL_CON_IVA],		[MONTO_DEBITO],		[MONTO_CREDITO],			[CUOTAS_CREDITO],	[COMISION_DEBITO],
												[COMISION_CREDITO],		[TOTAL_POR_COBRAR],		[COD_USUARIO],		[COD_DOC_INGRESO_PAGO])
										VALUES (@vc_cod_ingreso_pago,	@vc_fecha_ingreso_pago,	@vl_cod_nota_venta,	@vl_fecha_nota_venta,		@vl_rut,			@vl_dig_verif,	
												@vl_nom_empresa,		@vl_total_con_iva,		@vc_sum_monto_doc,	0,							0,					@vl_comision,
												0,						@vl_total_por_cobrar,	@ve_cod_usuario,	@vc_cod_doc_ingreso_pago)
			END
			ELSE IF(@vl_count = 1)BEGIN
				-- las fa que provienen de las nv coinciden y debe hacer match con la NV y FA
				SELECT DISTINCT @vl_cod_nota_venta_nv = COD_DOC
				FROM INGRESO_PAGO_FACTURA
				WHERE COD_INGRESO_PAGO = @vc_cod_ingreso_pago
				AND TIPO_DOC = 'NOTA_VENTA'

				SELECT DISTINCT @vl_cod_nota_venta_fa = dbo.f_get_nv_from_fa(COD_DOC)
				FROM INGRESO_PAGO_FACTURA
				WHERE COD_INGRESO_PAGO = @vc_cod_ingreso_pago
				AND TIPO_DOC = 'FACTURA'

				IF(@vl_cod_nota_venta_nv = @vl_cod_nota_venta_fa)BEGIN
					-- el match entre las fa y las nv son las mismas por ende se coloca 1 registro
					SELECT @vl_fecha_nota_venta = FECHA_NOTA_VENTA
						 ,@vl_rut = RUT
						 ,@vl_dig_verif = DIG_VERIF
						 ,@vl_nom_empresa = NOM_EMPRESA
						 ,@vl_total_con_iva = TOTAL_CON_IVA
					FROM NOTA_VENTA NV
						,EMPRESA E
					WHERE COD_NOTA_VENTA = @vl_cod_nota_venta_nv
					AND E.COD_EMPRESA = NV.COD_EMPRESA

					set @vl_comision			= (@vc_sum_monto_doc * CAST(dbo.f_get_parametro(80) AS NUMERIC(18,2)))/100
					set @vl_total_por_cobrar	= @vc_sum_monto_doc - @vl_comision

					INSERT INTO INF_POR_COBRAR_TBK ([COD_INGRESO_PAGO],		[FECHA_INGRESO_PAGO],	[COD_NOTA_VENTA],		[FECHA_NOTA_VENTA],			[RUT_CLIENTE],		[DIG_VERIF],
													[RAZON_SOCIAL],			[TOTAL_CON_IVA],		[MONTO_DEBITO],			[MONTO_CREDITO],			[CUOTAS_CREDITO],	[COMISION_DEBITO],
													[COMISION_CREDITO],		[TOTAL_POR_COBRAR],		[COD_USUARIO],			[COD_DOC_INGRESO_PAGO])
											VALUES (@vc_cod_ingreso_pago,	@vc_fecha_ingreso_pago,	@vl_cod_nota_venta_nv,	@vl_fecha_nota_venta,		@vl_rut,			@vl_dig_verif,
													@vl_nom_empresa,		@vl_total_con_iva,		@vc_sum_monto_doc,		0,							0,					@vl_comision,
													0,						@vl_total_por_cobrar,	@ve_cod_usuario,		@vc_cod_doc_ingreso_pago)
				END
				ELSE BEGIN
					-- el match entre las fa y las nv no son las mismas por ende se coloca varios
					set @vl_comision			= (@vc_sum_monto_doc * CAST(dbo.f_get_parametro(80) AS NUMERIC(18,2)))/100
					set @vl_total_por_cobrar	= @vc_sum_monto_doc - @vl_comision

					INSERT INTO INF_POR_COBRAR_TBK ([COD_INGRESO_PAGO],		[FECHA_INGRESO_PAGO],	[COD_NOTA_VENTA],		[FECHA_NOTA_VENTA],		[RUT_CLIENTE],		[DIG_VERIF],
													[RAZON_SOCIAL],			[TOTAL_CON_IVA],		[MONTO_DEBITO],			[MONTO_CREDITO],		[CUOTAS_CREDITO],	[COMISION_DEBITO],
													[COMISION_CREDITO],		[TOTAL_POR_COBRAR],		[COD_USUARIO],			[COD_DOC_INGRESO_PAGO])
											VALUES (@vc_cod_ingreso_pago,	@vc_fecha_ingreso_pago,	1,						GETDATE(),				91462001,			'5',
													'Varios',				0,						@vc_sum_monto_doc,		0,						0,					@vl_comision,
													0,						@vl_total_por_cobrar,	@ve_cod_usuario,		@vc_cod_doc_ingreso_pago)
					END
			END
			ELSE BEGIN
				-- las fa que provienen de las nv no coinciden y se deja en varios
				set @vl_comision		= (@vc_sum_monto_doc * CAST(dbo.f_get_parametro(80) AS NUMERIC(18,2)))/100
				set @vl_total_por_cobrar	= @vc_sum_monto_doc - @vl_comision

				INSERT INTO INF_POR_COBRAR_TBK ([COD_INGRESO_PAGO],		[FECHA_INGRESO_PAGO],	[COD_NOTA_VENTA],	[FECHA_NOTA_VENTA],			[RUT_CLIENTE],		[DIG_VERIF],
												[RAZON_SOCIAL],			[TOTAL_CON_IVA],		[MONTO_DEBITO],		[MONTO_CREDITO],			[CUOTAS_CREDITO],	[COMISION_DEBITO],
												[COMISION_CREDITO],		[TOTAL_POR_COBRAR],		[COD_USUARIO],		[COD_DOC_INGRESO_PAGO])
										VALUES (@vc_cod_ingreso_pago,	@vc_fecha_ingreso_pago,	1,					GETDATE(),					91462001,			'5',
												'Varios',				0,						@vc_sum_monto_doc,	0,							0,					@vl_comision,
												0,						@vl_total_por_cobrar,	@ve_cod_usuario,	@vc_cod_doc_ingreso_pago)
			END
		END
		ELSE BEGIN
			-- hay nv distintas varios
			set @vl_comision		= (@vc_sum_monto_doc * CAST(dbo.f_get_parametro(80) AS NUMERIC(18,2)))/100
			set @vl_total_por_cobrar	= @vc_sum_monto_doc - @vl_comision

			INSERT INTO INF_POR_COBRAR_TBK ([COD_INGRESO_PAGO],		[FECHA_INGRESO_PAGO],	[COD_NOTA_VENTA],	[FECHA_NOTA_VENTA],		[RUT_CLIENTE],			[DIG_VERIF],
											[RAZON_SOCIAL],			[TOTAL_CON_IVA],		[MONTO_DEBITO],		[MONTO_CREDITO],		[CUOTAS_CREDITO],		[COMISION_DEBITO],
											[COMISION_CREDITO],		[TOTAL_POR_COBRAR],		[COD_USUARIO],		[COD_DOC_INGRESO_PAGO])
									VALUES (@vc_cod_ingreso_pago,	@vc_fecha_ingreso_pago,	1,					GETDATE(),				91462001,				'5',
											'Varios',				0,						@vc_sum_monto_doc,	0,						0,						@vl_comision,
											0,						@vl_total_por_cobrar,	@ve_cod_usuario,	@vc_cod_doc_ingreso_pago)
		END

		FETCH C_INF_POR_COBRAR_TBK_DEBITO INTO @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, @vc_sum_monto_doc, @vc_cod_doc_ingreso_pago
	END
	CLOSE C_INF_POR_COBRAR_TBK_DEBITO
	DEALLOCATE C_INF_POR_COBRAR_TBK_DEBITO

	--------------------------------------------------Tarjeta Crédito------------------------------------------------------------------------

	DECLARE C_INF_POR_COBRAR_TBK_CREDITO cursor for
	SELECT INP.COD_INGRESO_PAGO
		  ,FECHA_INGRESO_PAGO
		  ,NRO_CUOTAS_TBK
		  ,SUM(MONTO_DOC)
		  ,COD_DOC_INGRESO_PAGO
	FROM INGRESO_PAGO INP
		,DOC_INGRESO_PAGO DIP
	WHERE INCLUIR_INF_TBK = 'S'
	AND COD_TIPO_DOC_PAGO = 6										-- Crédito
	AND INP.COD_ESTADO_INGRESO_PAGO = 2								-- Estado confirmado
	AND FECHA_INGRESO_PAGO >= dbo.to_date(dbo.f_get_parametro(79))
	AND NRO_CUOTAS_TBK IS NOT NULL
	AND INP.COD_INGRESO_PAGO = DIP.COD_INGRESO_PAGO
	GROUP BY INP.COD_INGRESO_PAGO, FECHA_INGRESO_PAGO, NRO_CUOTAS_TBK, COD_DOC_INGRESO_PAGO
	ORDER BY INP.COD_INGRESO_PAGO ASC

	OPEN C_INF_POR_COBRAR_TBK_CREDITO
	FETCH C_INF_POR_COBRAR_TBK_CREDITO INTO @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, @vc_nro_cuotas_tbk, @vc_sum_monto_doc, @vc_cod_doc_ingreso_pago
	WHILE @@FETCH_STATUS = 0
	BEGIN
		
		SELECT @vl_count = COUNT(DISTINCT COD_DOC)
		FROM INGRESO_PAGO_FACTURA
		WHERE COD_INGRESO_PAGO = @vc_cod_ingreso_pago
		AND TIPO_DOC = 'NOTA_VENTA'

		IF(@vl_count = 0)BEGIN
			-- no hay NV que coincidan pero intentara si hay FA asociadas a una NV
			SELECT @vl_count = COUNT(DISTINCT dbo.f_get_nv_from_fa(COD_DOC))
			FROM INGRESO_PAGO_FACTURA
			WHERE COD_INGRESO_PAGO = @vc_cod_ingreso_pago
			AND TIPO_DOC = 'FACTURA'

			IF(@vl_count = 0)BEGIN
				-- Es un error
				return 'Error'
			END
			ELSE IF(@vl_count = 1)BEGIN
				-- Encontro FA asociada a una NV para desplegar datos
				SELECT DISTINCT @vl_cod_nota_venta = dbo.f_get_nv_from_fa(COD_DOC)
				FROM INGRESO_PAGO_FACTURA
				WHERE COD_INGRESO_PAGO = @vc_cod_ingreso_pago
				AND TIPO_DOC = 'FACTURA'

				SELECT @vl_fecha_nota_venta = FECHA_NOTA_VENTA
					,@vl_rut = RUT
					,@vl_dig_verif = DIG_VERIF
					,@vl_nom_empresa = NOM_EMPRESA
					,@vl_total_con_iva = TOTAL_CON_IVA
				FROM NOTA_VENTA NV
					,EMPRESA E
				WHERE COD_NOTA_VENTA = @vl_cod_nota_venta
				AND E.COD_EMPRESA = NV.COD_EMPRESA

				set @vl_comision			= (@vc_sum_monto_doc * CAST(dbo.f_get_parametro(81) AS NUMERIC(18,2)))/100
				set @vl_total_por_cobrar	= @vc_sum_monto_doc - @vl_comision

				INSERT INTO INF_POR_COBRAR_TBK ([COD_INGRESO_PAGO],		[FECHA_INGRESO_PAGO],	[COD_NOTA_VENTA],		[FECHA_NOTA_VENTA],		[RUT_CLIENTE],		[DIG_VERIF],
												[RAZON_SOCIAL],			[TOTAL_CON_IVA],		[MONTO_DEBITO],			[MONTO_CREDITO],		[CUOTAS_CREDITO],	[COMISION_DEBITO],
												[COMISION_CREDITO],		[TOTAL_POR_COBRAR],		[COD_USUARIO],			[COD_DOC_INGRESO_PAGO])
										VALUES (@vc_cod_ingreso_pago,	@vc_fecha_ingreso_pago,	@vl_cod_nota_venta,		@vl_fecha_nota_venta,	@vl_rut,			@vl_dig_verif,
												@vl_nom_empresa,		@vl_total_con_iva,		0,						@vc_sum_monto_doc,		@vc_nro_cuotas_tbk,	0,
												@vl_comision,			@vl_total_por_cobrar,	@ve_cod_usuario,		@vc_cod_doc_ingreso_pago)
			END
			ELSE BEGIN
				-- Encontro varias FA asociadas pero con distinto NV por ende son distintas

				set @vl_comision			= (@vc_sum_monto_doc * CAST(dbo.f_get_parametro(81) AS NUMERIC(18,2)))/100
				set @vl_total_por_cobrar	= @vc_sum_monto_doc - @vl_comision

				INSERT INTO INF_POR_COBRAR_TBK ([COD_INGRESO_PAGO],		[FECHA_INGRESO_PAGO],	[COD_NOTA_VENTA],		[FECHA_NOTA_VENTA],		[RUT_CLIENTE],		[DIG_VERIF],
												[RAZON_SOCIAL],			[TOTAL_CON_IVA],		[MONTO_DEBITO],			[MONTO_CREDITO],		[CUOTAS_CREDITO],	[COMISION_DEBITO],
												[COMISION_CREDITO],		[TOTAL_POR_COBRAR],		[COD_USUARIO],			[COD_DOC_INGRESO_PAGO])
										VALUES (@vc_cod_ingreso_pago,	@vc_fecha_ingreso_pago,	1,						GETDATE(),				91462001,			'5',
												'Varios',				0,						0,						@vc_sum_monto_doc,		@vc_nro_cuotas_tbk,	0,
												@vl_comision,			@vl_total_por_cobrar,	@ve_cod_usuario,		@vc_cod_doc_ingreso_pago)
			END
		END
		ELSE IF(@vl_count = 1)BEGIN
			-- Encontro NV pero vera si existe FA asociadas
			SELECT @vl_count = COUNT(DISTINCT dbo.f_get_nv_from_fa(COD_DOC))
			FROM INGRESO_PAGO_FACTURA
			WHERE COD_INGRESO_PAGO = @vc_cod_ingreso_pago
			AND TIPO_DOC = 'FACTURA'
			
			IF(@vl_count = 0)BEGIN
				-- hay NV pero no hay FA por ende lanza los datos como tal desde la NV
				
				SELECT DISTINCT @vl_cod_nota_venta = COD_DOC
				FROM INGRESO_PAGO_FACTURA
				WHERE COD_INGRESO_PAGO = @vc_cod_ingreso_pago
				AND TIPO_DOC = 'NOTA_VENTA'

				SELECT @vl_fecha_nota_venta = FECHA_NOTA_VENTA
					,@vl_rut = RUT
					,@vl_dig_verif = DIG_VERIF
					,@vl_nom_empresa = NOM_EMPRESA
					,@vl_total_con_iva = TOTAL_CON_IVA
				FROM NOTA_VENTA NV
					,EMPRESA E
				WHERE COD_NOTA_VENTA = @vl_cod_nota_venta
				AND E.COD_EMPRESA = NV.COD_EMPRESA

				set @vl_comision			= (@vc_sum_monto_doc * CAST(dbo.f_get_parametro(81) AS NUMERIC(18,2)))/100
				set @vl_total_por_cobrar	= @vc_sum_monto_doc - @vl_comision

				INSERT INTO INF_POR_COBRAR_TBK ([COD_INGRESO_PAGO],		[FECHA_INGRESO_PAGO],	[COD_NOTA_VENTA],		[FECHA_NOTA_VENTA],			[RUT_CLIENTE],		[DIG_VERIF],
												[RAZON_SOCIAL],			[TOTAL_CON_IVA],		[MONTO_DEBITO],			[MONTO_CREDITO],			[CUOTAS_CREDITO],	[COMISION_DEBITO],
												[COMISION_CREDITO],		[TOTAL_POR_COBRAR],		[COD_USUARIO],			[COD_DOC_INGRESO_PAGO])
										VALUES (@vc_cod_ingreso_pago,	@vc_fecha_ingreso_pago,	@vl_cod_nota_venta,		@vl_fecha_nota_venta,		@vl_rut,			@vl_dig_verif,
												@vl_nom_empresa,		@vl_total_con_iva,		0,						@vc_sum_monto_doc,			@vc_nro_cuotas_tbk,	0,					
												@vl_comision,			@vl_total_por_cobrar,	@ve_cod_usuario,		@vc_cod_doc_ingreso_pago)
			END
			ELSE IF(@vl_count = 1)BEGIN
				-- las fa que provienen de las nv coinciden y debe hacer match con la NV y FA
				SELECT DISTINCT @vl_cod_nota_venta_nv = COD_DOC
				FROM INGRESO_PAGO_FACTURA
				WHERE COD_INGRESO_PAGO = @vc_cod_ingreso_pago
				AND TIPO_DOC = 'NOTA_VENTA'

				SELECT DISTINCT @vl_cod_nota_venta_fa = dbo.f_get_nv_from_fa(COD_DOC)
				FROM INGRESO_PAGO_FACTURA
				WHERE COD_INGRESO_PAGO = @vc_cod_ingreso_pago
				AND TIPO_DOC = 'FACTURA'

				IF(@vl_cod_nota_venta_nv = @vl_cod_nota_venta_fa)BEGIN
					-- el match entre las fa y las nv son las mismas por ende se coloca 1 registro
					SELECT @vl_fecha_nota_venta = FECHA_NOTA_VENTA
						,@vl_rut = RUT
						,@vl_dig_verif = DIG_VERIF
						,@vl_nom_empresa = NOM_EMPRESA
						,@vl_total_con_iva = TOTAL_CON_IVA
					FROM NOTA_VENTA NV
						,EMPRESA E
					WHERE COD_NOTA_VENTA = @vl_cod_nota_venta_nv
					AND E.COD_EMPRESA = NV.COD_EMPRESA

					set @vl_comision			= (@vc_sum_monto_doc * CAST(dbo.f_get_parametro(81) AS NUMERIC(18,2)))/100
					set @vl_total_por_cobrar	= @vc_sum_monto_doc - @vl_comision

					INSERT INTO INF_POR_COBRAR_TBK ([COD_INGRESO_PAGO],		[FECHA_INGRESO_PAGO],	[COD_NOTA_VENTA],		[FECHA_NOTA_VENTA],		[RUT_CLIENTE],		[DIG_VERIF],
													[RAZON_SOCIAL],			[TOTAL_CON_IVA],		[MONTO_DEBITO],			[MONTO_CREDITO],		[CUOTAS_CREDITO],	[COMISION_DEBITO],
													[COMISION_CREDITO],		[TOTAL_POR_COBRAR],		[COD_USUARIO],			[COD_DOC_INGRESO_PAGO])
											VALUES (@vc_cod_ingreso_pago,	@vc_fecha_ingreso_pago,	@vl_cod_nota_venta_nv,	@vl_fecha_nota_venta,	@vl_rut,			@vl_dig_verif,
													@vl_nom_empresa,		@vl_total_con_iva,		0,						@vc_sum_monto_doc,		@vc_nro_cuotas_tbk,	0,
													@vl_comision,			@vl_total_por_cobrar,	@ve_cod_usuario,		@vc_cod_doc_ingreso_pago)
				END
				ELSE BEGIN
					-- el match entre las fa y las nv no son las mismas por ende se coloca varios
					set @vl_comision			= (@vc_sum_monto_doc * CAST(dbo.f_get_parametro(81) AS NUMERIC(18,2)))/100
					set @vl_total_por_cobrar	= @vc_sum_monto_doc - @vl_comision

					INSERT INTO INF_POR_COBRAR_TBK ([COD_INGRESO_PAGO],		[FECHA_INGRESO_PAGO],	[COD_NOTA_VENTA],		[FECHA_NOTA_VENTA],		[RUT_CLIENTE],		[DIG_VERIF],
													[RAZON_SOCIAL],			[TOTAL_CON_IVA],		[MONTO_DEBITO],			[MONTO_CREDITO],		[CUOTAS_CREDITO],	[COMISION_DEBITO],
													[COMISION_CREDITO],		[TOTAL_POR_COBRAR],		[COD_USUARIO],			[COD_DOC_INGRESO_PAGO])
											VALUES (@vc_cod_ingreso_pago,	@vc_fecha_ingreso_pago,	1,						GETDATE(),				91462001,			'5',
													'Varios',				0,						0,						@vc_sum_monto_doc,		@vc_nro_cuotas_tbk,	0,
													@vl_comision,			@vl_total_por_cobrar,	@ve_cod_usuario,		@vc_cod_doc_ingreso_pago)
					END
			END
			ELSE BEGIN
				-- las fa que provienen de las nv no coinciden y se deja en varios
				set @vl_comision		= (@vc_sum_monto_doc * CAST(dbo.f_get_parametro(81) AS NUMERIC(18,2)))/100
				set @vl_total_por_cobrar	= @vc_sum_monto_doc - @vl_comision

				INSERT INTO INF_POR_COBRAR_TBK ([COD_INGRESO_PAGO],		[FECHA_INGRESO_PAGO],	[COD_NOTA_VENTA],		[FECHA_NOTA_VENTA],		[RUT_CLIENTE],		[DIG_VERIF],
												[RAZON_SOCIAL],			[TOTAL_CON_IVA],		[MONTO_DEBITO],			[MONTO_CREDITO],		[CUOTAS_CREDITO],	[COMISION_DEBITO],
												[COMISION_CREDITO],		[TOTAL_POR_COBRAR],		[COD_USUARIO],			[COD_DOC_INGRESO_PAGO])
										VALUES (@vc_cod_ingreso_pago,	@vc_fecha_ingreso_pago,	1,						GETDATE(),				91462001,			'5',
												'Varios',				0,						0,						@vc_sum_monto_doc,		@vc_nro_cuotas_tbk,	 0,
												@vl_comision,			@vl_total_por_cobrar,	@ve_cod_usuario,		@vc_cod_doc_ingreso_pago)
			END
		END
		ELSE BEGIN
			-- hay nv distintas varios
			set @vl_comision		= (@vc_sum_monto_doc * CAST(dbo.f_get_parametro(81) AS NUMERIC(18,2)))/100
			set @vl_total_por_cobrar	= @vc_sum_monto_doc - @vl_comision

			INSERT INTO INF_POR_COBRAR_TBK ([COD_INGRESO_PAGO],		[FECHA_INGRESO_PAGO],	[COD_NOTA_VENTA],		[FECHA_NOTA_VENTA],		[RUT_CLIENTE],		[DIG_VERIF],
											[RAZON_SOCIAL],			[TOTAL_CON_IVA],		[MONTO_DEBITO],			[MONTO_CREDITO],		[CUOTAS_CREDITO],	[COMISION_DEBITO],
											[COMISION_CREDITO],		[TOTAL_POR_COBRAR],		[COD_USUARIO],			[COD_DOC_INGRESO_PAGO])
									VALUES (@vc_cod_ingreso_pago,	@vc_fecha_ingreso_pago,	1,						GETDATE(),				91462001,			'5',
											'Varios',				0,						0,						@vc_sum_monto_doc,		@vc_nro_cuotas_tbk,	 0,
											@vl_comision,			@vl_total_por_cobrar,	@ve_cod_usuario,		@vc_cod_doc_ingreso_pago)
		END

		FETCH C_INF_POR_COBRAR_TBK_CREDITO INTO @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, @vc_nro_cuotas_tbk, @vc_sum_monto_doc, @vc_cod_doc_ingreso_pago
	END
	CLOSE C_INF_POR_COBRAR_TBK_CREDITO
	DEALLOCATE C_INF_POR_COBRAR_TBK_CREDITO
END