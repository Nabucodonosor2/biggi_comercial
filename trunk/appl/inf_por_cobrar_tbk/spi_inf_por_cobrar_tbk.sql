ALTER PROCEDURE [dbo].[spi_inf_por_cobrar_tbk](@ve_cod_usuario	numeric)
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
	@vl_cod_nota_venta_nv		numeric,
	@vl_cod_nota_venta_fa		numeric,
	@vl_monto_cuota				numeric,
	@vl_count_cuotas			numeric,
	@vl_month_add				numeric,
	@vl_monto_total_cuota		numeric,
	@vl_fecha_actual			datetime,
	@vl_fecha_abono				datetime,
	@vl_fecha_abono_cuota		datetime,
	@vl_resto_cuotas			numeric,
	@vc_cod_ingreso_pago		numeric,
	@vc_fecha_ingreso_pago		datetime,
	@vc_sum_monto_doc			numeric,
	@vc_nro_cuotas_tbk			numeric,
	@vc_cod_doc_ingreso_pago	numeric,
	@vc_fecha_doc				datetime,
	@vc_nro_doc					numeric
	
	-- borra el resultado de informes anteriores del mismo usuario
	DELETE DETALLE_ABONO_TBK
	WHERE cod_usuario = @ve_cod_usuario

	DELETE INF_POR_COBRAR_TBK
	WHERE cod_usuario = @ve_cod_usuario

	SET @vl_fecha_actual = GETDATE()
	--------------------------------------------------Tarjeta Dédito------------------------------------------------------------------------

	DECLARE C_INF_POR_COBRAR_TBK_DEBITO cursor for
	SELECT INP.COD_INGRESO_PAGO
		  ,FECHA_INGRESO_PAGO
		  ,SUM(MONTO_DOC)
		  ,COD_DOC_INGRESO_PAGO
		  ,DIP.FECHA_DOC
		  ,DIP.NRO_DOC
	FROM INGRESO_PAGO INP
		,DOC_INGRESO_PAGO DIP
	WHERE INCLUIR_INF_TBK = 'S'
	AND COD_TIPO_DOC_PAGO = 5										-- Débito
	AND INP.COD_ESTADO_INGRESO_PAGO = 2								-- Estado confirmado
	AND FECHA_INGRESO_PAGO >= dbo.to_date(dbo.f_get_parametro(78))
	AND INP.COD_INGRESO_PAGO = DIP.COD_INGRESO_PAGO
	GROUP BY INP.COD_INGRESO_PAGO, FECHA_INGRESO_PAGO, COD_DOC_INGRESO_PAGO, DIP.FECHA_DOC, DIP.NRO_DOC
	ORDER BY INP.COD_INGRESO_PAGO ASC
	
	OPEN C_INF_POR_COBRAR_TBK_DEBITO
	FETCH C_INF_POR_COBRAR_TBK_DEBITO INTO @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, @vc_sum_monto_doc, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc
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

		set @vl_comision			= (@vc_sum_monto_doc * CAST(dbo.f_get_parametro(80) AS NUMERIC(18,2)))/100
		set @vl_total_por_cobrar	= @vc_sum_monto_doc - @vl_comision
		set @vl_fecha_abono			= dbo.f_get_dia_habil_tbk(@vc_fecha_doc, 'D')

		IF(@vl_count = 0)BEGIN
			-- no hay NV que coincidan pero intentara si hay FA asociadas a una NV
			SELECT @vl_count = COUNT(DISTINCT dbo.f_get_nv_from_fa(COD_DOC))
			FROM INGRESO_PAGO_FACTURA
			WHERE COD_INGRESO_PAGO = @vc_cod_ingreso_pago
			AND TIPO_DOC = 'FACTURA'

			IF(@vl_count = 0)BEGIN
				-- Es un error
				print 'Error ingreso pago: '+CONVERT(VARCHAR, @vc_cod_ingreso_pago)
			END
			ELSE IF(@vl_count = 1)BEGIN
				-- Encontro FA asociada a una NV para desplegar datos
				SELECT DISTINCT @vl_cod_nota_venta = dbo.f_get_nv_from_fa(COD_DOC)
				FROM INGRESO_PAGO_FACTURA
				WHERE COD_INGRESO_PAGO = @vc_cod_ingreso_pago
				AND TIPO_DOC = 'FACTURA'

				if(@vl_fecha_actual <= @vl_fecha_abono)BEGIN
					SELECT @vl_fecha_nota_venta = FECHA_NOTA_VENTA
						 ,@vl_rut = RUT
						 ,@vl_dig_verif = DIG_VERIF
						 ,@vl_nom_empresa = NOM_EMPRESA
						 ,@vl_total_con_iva = TOTAL_CON_IVA
					FROM NOTA_VENTA NV
						,EMPRESA E
					WHERE COD_NOTA_VENTA = @vl_cod_nota_venta
					AND E.COD_EMPRESA = NV.COD_EMPRESA

					exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, @vl_cod_nota_venta, @vl_fecha_nota_venta, @vl_rut, @vl_dig_verif,
												@vl_nom_empresa, @vl_total_con_iva, @vc_sum_monto_doc, 0, 0, @vl_comision, 0, @vl_total_por_cobrar, @ve_cod_usuario, @vc_cod_doc_ingreso_pago, 0, 0

					exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc, @vc_sum_monto_doc, 0, 0, 0, @vl_fecha_abono,
												@ve_cod_usuario, 5
				END
			END
			ELSE BEGIN
				-- Encontro varias FA asociadas pero con distinto NV por ende son distintas
				if(@vl_fecha_actual <= @vl_fecha_abono)BEGIN
					exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, 1, @vl_fecha_actual, 91462001, '5', 'Varios', 0, @vc_sum_monto_doc, 0, 0,
												@vl_comision, 0, @vl_total_por_cobrar, @ve_cod_usuario,	@vc_cod_doc_ingreso_pago, 0, 0

					exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vl_fecha_actual, 0, @vc_sum_monto_doc, 0, 0, 0, @vl_fecha_abono,
												@ve_cod_usuario, 5
				END
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

				if(@vl_fecha_actual <= @vl_fecha_abono)BEGIN
					SELECT @vl_fecha_nota_venta = FECHA_NOTA_VENTA
						 ,@vl_rut = RUT
						 ,@vl_dig_verif = DIG_VERIF
						 ,@vl_nom_empresa = NOM_EMPRESA
						 ,@vl_total_con_iva = TOTAL_CON_IVA
					FROM NOTA_VENTA NV
						,EMPRESA E
					WHERE COD_NOTA_VENTA = @vl_cod_nota_venta
					AND E.COD_EMPRESA = NV.COD_EMPRESA

					exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, @vl_cod_nota_venta, @vl_fecha_nota_venta, @vl_rut, @vl_dig_verif,	
												@vl_nom_empresa, @vl_total_con_iva, @vc_sum_monto_doc, 0, 0, @vl_comision, 0, @vl_total_por_cobrar,	@ve_cod_usuario, @vc_cod_doc_ingreso_pago, 0, 0

					exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc, @vc_sum_monto_doc, 0, 0, 0, @vl_fecha_abono,
												@ve_cod_usuario, 5
				END
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
					if(@vl_fecha_actual <= @vl_fecha_abono)BEGIN
						SELECT @vl_fecha_nota_venta = FECHA_NOTA_VENTA
							 ,@vl_rut = RUT
							 ,@vl_dig_verif = DIG_VERIF
							 ,@vl_nom_empresa = NOM_EMPRESA
							 ,@vl_total_con_iva = TOTAL_CON_IVA
						FROM NOTA_VENTA NV
							,EMPRESA E
						WHERE COD_NOTA_VENTA = @vl_cod_nota_venta_nv
						AND E.COD_EMPRESA = NV.COD_EMPRESA

						exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, @vl_cod_nota_venta_nv, @vl_fecha_nota_venta, @vl_rut, @vl_dig_verif,
													@vl_nom_empresa, @vl_total_con_iva, @vc_sum_monto_doc, 0, 0, @vl_comision, 0, @vl_total_por_cobrar,	@ve_cod_usuario, @vc_cod_doc_ingreso_pago, 0, 0
						
						exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc, @vc_sum_monto_doc, 0, 0, 0, @vl_fecha_abono,
													@ve_cod_usuario, 5
					END
				END
				ELSE BEGIN
					-- el match entre las fa y las nv no son las mismas por ende se coloca varios
					if(@vl_fecha_actual <= @vl_fecha_abono)BEGIN
						exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, 1, @vl_fecha_actual, 91462001, '5', 'Varios', 0, @vc_sum_monto_doc, 0, 0,
													@vl_comision, 0, @vl_total_por_cobrar, @ve_cod_usuario, @vc_cod_doc_ingreso_pago, 0, 0

						exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vl_fecha_actual, 0, @vc_sum_monto_doc, 0, 0, 0, @vl_fecha_abono,
												@ve_cod_usuario, 5
					END
				END
			END
			ELSE BEGIN
				-- las fa que provienen de las nv no coinciden y se deja en varios
				if(@vl_fecha_actual <= @vl_fecha_abono)BEGIN
					exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, 1, @vl_fecha_actual, 91462001, '5', 'Varios', 0, @vc_sum_monto_doc, 0, 0, @vl_comision,
												0, @vl_total_por_cobrar, @ve_cod_usuario, @vc_cod_doc_ingreso_pago, 0, 0
					
					exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vl_fecha_actual, 0, @vc_sum_monto_doc, 0, 0, 0, @vl_fecha_abono,
												@ve_cod_usuario, 5
				END
			END
		END
		ELSE BEGIN
			-- hay nv distintas varios
			if(@vl_fecha_actual <= @vl_fecha_abono)BEGIN
				exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, 1, @vl_fecha_actual, 91462001, '5', 'Varios', 0, @vc_sum_monto_doc, 0, 0, @vl_comision,
											0, @vl_total_por_cobrar, @ve_cod_usuario, @vc_cod_doc_ingreso_pago, 0, 0

				exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vl_fecha_actual, 0, @vc_sum_monto_doc, 0, 0, 0, @vl_fecha_abono,
												@ve_cod_usuario, 5
			END
		END

		FETCH C_INF_POR_COBRAR_TBK_DEBITO INTO @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, @vc_sum_monto_doc, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc
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
		  ,DIP.FECHA_DOC
		  ,DIP.NRO_DOC
	FROM INGRESO_PAGO INP
		,DOC_INGRESO_PAGO DIP
	WHERE INCLUIR_INF_TBK = 'S'
	AND COD_TIPO_DOC_PAGO = 6										-- Crédito
	AND INP.COD_ESTADO_INGRESO_PAGO = 2								-- Estado confirmado
	AND FECHA_INGRESO_PAGO >= dbo.to_date(dbo.f_get_parametro(79))
	AND NRO_CUOTAS_TBK IS NOT NULL
	AND INP.COD_INGRESO_PAGO = DIP.COD_INGRESO_PAGO
	GROUP BY INP.COD_INGRESO_PAGO, FECHA_INGRESO_PAGO, NRO_CUOTAS_TBK, COD_DOC_INGRESO_PAGO, DIP.FECHA_DOC, DIP.NRO_DOC
	ORDER BY INP.COD_INGRESO_PAGO ASC

	OPEN C_INF_POR_COBRAR_TBK_CREDITO
	FETCH C_INF_POR_COBRAR_TBK_CREDITO INTO @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, @vc_nro_cuotas_tbk, @vc_sum_monto_doc, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc
	WHILE @@FETCH_STATUS = 0
	BEGIN
		
		SELECT @vl_count = COUNT(DISTINCT COD_DOC)
		FROM INGRESO_PAGO_FACTURA
		WHERE COD_INGRESO_PAGO = @vc_cod_ingreso_pago
		AND TIPO_DOC = 'NOTA_VENTA'

		set @vl_comision			= (@vc_sum_monto_doc * CAST(dbo.f_get_parametro(81) AS NUMERIC(18,2)))/100
		set @vl_total_por_cobrar	= @vc_sum_monto_doc - @vl_comision
		set @vl_monto_cuota			= @vl_total_por_cobrar/@vc_nro_cuotas_tbk
		set @vl_fecha_abono			= dbo.f_get_dia_habil_tbk(@vc_fecha_doc, 'C')

		IF(@vl_count = 0)BEGIN
			-- no hay NV que coincidan pero intentara si hay FA asociadas a una NV
			SELECT @vl_count = COUNT(DISTINCT dbo.f_get_nv_from_fa(COD_DOC))
			FROM INGRESO_PAGO_FACTURA
			WHERE COD_INGRESO_PAGO = @vc_cod_ingreso_pago
			AND TIPO_DOC = 'FACTURA'

			IF(@vl_count = 0)BEGIN
				-- Es un error
				print 'Error ingreso pago: '+CONVERT(VARCHAR, @vc_cod_ingreso_pago)
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

				if(@vl_fecha_actual >= @vl_fecha_abono)BEGIN
					--Hay cuotas vencidas por ende tiene que saber en que cuota esta parada
					set @vl_count_cuotas = @vc_nro_cuotas_tbk - 1
					set @vl_month_add = 1

					WHILE @vl_count_cuotas > 0 BEGIN
						set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)
						IF(@vl_fecha_actual >= @vl_fecha_abono_cuota)BEGIN
							set @vl_count_cuotas = @vl_count_cuotas - 1
							set @vl_month_add = @vl_month_add + 1
						END
						ELSE
							BREAK;
					END

					if(@vl_count_cuotas > 0)BEGIN
						set @vl_monto_total_cuota	= @vl_total_por_cobrar - ((@vc_nro_cuotas_tbk - @vl_count_cuotas) * @vl_monto_cuota)

						exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, @vl_cod_nota_venta, @vl_fecha_nota_venta, @vl_rut, @vl_dig_verif, @vl_nom_empresa,
													@vl_total_con_iva, 0, @vc_sum_monto_doc, @vc_nro_cuotas_tbk, 0, @vl_comision, @vl_monto_total_cuota, @ve_cod_usuario, @vc_cod_doc_ingreso_pago,	
													@vl_monto_cuota, @vl_count_cuotas
						
						--Registra los detalles de los abonos
						set @vl_resto_cuotas = @vc_nro_cuotas_tbk - @vl_count_cuotas
						WHILE @vl_resto_cuotas < @vc_nro_cuotas_tbk BEGIN
							set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)

							exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc, @vc_sum_monto_doc, @vl_monto_cuota, @vc_nro_cuotas_tbk,
														@vl_count_cuotas, @vl_fecha_abono_cuota, @ve_cod_usuario, 6

							set @vl_resto_cuotas = @vl_resto_cuotas + 1
							set @vl_month_add = @vl_month_add + 1
							set @vl_count_cuotas = @vl_count_cuotas - 1
						END
					END
				END
				ELSE BEGIN
					--No excede nonguna cuota a la fecha actual por ende lo tira con todas las cuotas vigentes
					exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, @vl_cod_nota_venta, @vl_fecha_nota_venta, @vl_rut, @vl_dig_verif, @vl_nom_empresa,
												@vl_total_con_iva, 0, @vc_sum_monto_doc, @vc_nro_cuotas_tbk, 0, @vl_comision, @vl_total_por_cobrar, @ve_cod_usuario, @vc_cod_doc_ingreso_pago,
												@vl_monto_cuota, @vc_nro_cuotas_tbk
					
					--Registra los detalles de los abonos
					set @vl_month_add = 0
					set @vl_count_cuotas = 0

					WHILE @vl_count_cuotas < @vc_nro_cuotas_tbk BEGIN
						set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)

						exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc, @vc_sum_monto_doc, @vl_monto_cuota, @vc_nro_cuotas_tbk,
													@vl_count_cuotas, @vl_fecha_abono_cuota, @ve_cod_usuario, 6

						set @vl_month_add = @vl_month_add + 1
						set @vl_count_cuotas = @vl_count_cuotas - 1
					END
				END
			END
			ELSE BEGIN
				-- Encontro varias FA asociadas pero con distinto NV por ende son distintas
				if(@vl_fecha_actual >= @vl_fecha_abono)BEGIN
					--Hay cuotas vencidas por ende tiene que saber en que cuota esta parada
					set @vl_count_cuotas = @vc_nro_cuotas_tbk - 1
					set @vl_month_add = 1

					WHILE @vl_count_cuotas > 0 BEGIN
						set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)
						IF(@vl_fecha_actual >= @vl_fecha_abono_cuota)BEGIN
							set @vl_count_cuotas = @vl_count_cuotas - 1
							set @vl_month_add = @vl_month_add + 1
						END
						ELSE
							BREAK;
					END

					if(@vl_count_cuotas > 0)BEGIN
						set @vl_monto_total_cuota	= @vl_total_por_cobrar - ((@vc_nro_cuotas_tbk - @vl_count_cuotas) * @vl_monto_cuota)

						exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, 1, @vl_fecha_actual, 91462001, '5', 'Varios', 0, 0, @vc_sum_monto_doc, @vc_nro_cuotas_tbk,
													0, @vl_comision, @vl_monto_total_cuota, @ve_cod_usuario, @vc_cod_doc_ingreso_pago, @vl_monto_cuota, @vl_count_cuotas

						--Registra los detalles de los abonos
						set @vl_resto_cuotas = @vc_nro_cuotas_tbk - @vl_count_cuotas
						WHILE @vl_resto_cuotas < @vc_nro_cuotas_tbk BEGIN
							set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)

							exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc, @vc_sum_monto_doc, @vl_monto_cuota, @vc_nro_cuotas_tbk,
														@vl_count_cuotas, @vl_fecha_abono_cuota, @ve_cod_usuario, 6

							set @vl_resto_cuotas = @vl_resto_cuotas + 1
							set @vl_month_add = @vl_month_add + 1
							set @vl_count_cuotas = @vl_count_cuotas - 1
						END
					END
				END
				ELSE BEGIN
					--No excede nonguna cuota a la fecha actual por ende lo tira con todas las cuotas vigentes
					exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, 1, @vl_fecha_actual, 91462001, '5', 'Varios', 0, 0, @vc_sum_monto_doc, @vc_nro_cuotas_tbk, 0,
												@vl_comision, @vl_total_por_cobrar,	@ve_cod_usuario, @vc_cod_doc_ingreso_pago, @vl_monto_cuota, @vc_nro_cuotas_tbk

					--Registra los detalles de los abonos
					set @vl_month_add = 0
					set @vl_count_cuotas = 0

					WHILE @vl_count_cuotas < @vc_nro_cuotas_tbk BEGIN
						set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)

						exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc, @vc_sum_monto_doc, @vl_monto_cuota, @vc_nro_cuotas_tbk,
													@vl_count_cuotas, @vl_fecha_abono_cuota, @ve_cod_usuario, 6

						set @vl_month_add = @vl_month_add + 1
						set @vl_count_cuotas = @vl_count_cuotas - 1
					END
				END
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

				if(@vl_fecha_actual >= @vl_fecha_abono)BEGIN
					--Hay cuotas vencidas por ende tiene que saber en que cuota esta parada
					set @vl_count_cuotas = @vc_nro_cuotas_tbk - 1
					set @vl_month_add = 1

					WHILE @vl_count_cuotas > 0 BEGIN
						set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)
						IF(@vl_fecha_actual >= @vl_fecha_abono_cuota)BEGIN
							set @vl_count_cuotas = @vl_count_cuotas - 1
							set @vl_month_add = @vl_month_add + 1
						END
						ELSE
							BREAK;
					END

					if(@vl_count_cuotas > 0)BEGIN
						set @vl_monto_total_cuota	= @vl_total_por_cobrar - ((@vc_nro_cuotas_tbk - @vl_count_cuotas) * @vl_monto_cuota)

						exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, @vl_cod_nota_venta, @vl_fecha_nota_venta, @vl_rut, @vl_dig_verif, @vl_nom_empresa,
													@vl_total_con_iva, 0, @vc_sum_monto_doc, @vc_nro_cuotas_tbk, 0, @vl_comision, @vl_monto_total_cuota, @ve_cod_usuario, @vc_cod_doc_ingreso_pago,	@vl_monto_cuota,
													@vl_count_cuotas
						
						--Registra los detalles de los abonos
						set @vl_resto_cuotas = @vc_nro_cuotas_tbk - @vl_count_cuotas
						WHILE @vl_resto_cuotas < @vc_nro_cuotas_tbk BEGIN
							set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)

							exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc, @vc_sum_monto_doc, @vl_monto_cuota, @vc_nro_cuotas_tbk,
														@vl_count_cuotas, @vl_fecha_abono_cuota, @ve_cod_usuario, 6

							set @vl_resto_cuotas = @vl_resto_cuotas + 1
							set @vl_month_add = @vl_month_add + 1
							set @vl_count_cuotas = @vl_count_cuotas - 1
						END
					END
				END
				ELSE BEGIN
					exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, @vl_cod_nota_venta, @vl_fecha_nota_venta, @vl_rut, @vl_dig_verif, @vl_nom_empresa, @vl_total_con_iva,
												0, @vc_sum_monto_doc, @vc_nro_cuotas_tbk, 0, @vl_comision, @vl_total_por_cobrar, @ve_cod_usuario, @vc_cod_doc_ingreso_pago,	@vl_monto_cuota, @vc_nro_cuotas_tbk
					
					--Registra los detalles de los abonos
					set @vl_month_add = 0
					set @vl_count_cuotas = 0

					WHILE @vl_count_cuotas < @vc_nro_cuotas_tbk BEGIN
						set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)

						exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc, @vc_sum_monto_doc, @vl_monto_cuota, @vc_nro_cuotas_tbk,
													@vl_count_cuotas, @vl_fecha_abono_cuota, @ve_cod_usuario, 6

						set @vl_month_add = @vl_month_add + 1
						set @vl_count_cuotas = @vl_count_cuotas - 1
					END
				END
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

					if(@vl_fecha_actual >= @vl_fecha_abono)BEGIN
						--Hay cuotas vencidas por ende tiene que saber en que cuota esta parada
						set @vl_count_cuotas = @vc_nro_cuotas_tbk - 1
						set @vl_month_add = 1

						WHILE @vl_count_cuotas > 0 BEGIN
							set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)
							IF(@vl_fecha_actual >= @vl_fecha_abono_cuota)BEGIN
								set @vl_count_cuotas = @vl_count_cuotas - 1
								set @vl_month_add = @vl_month_add + 1
							END
							ELSE
								BREAK;
						END

						if(@vl_count_cuotas > 0)BEGIN
							set @vl_monto_total_cuota	= @vl_total_por_cobrar - ((@vc_nro_cuotas_tbk - @vl_count_cuotas) * @vl_monto_cuota)

							exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, @vl_cod_nota_venta_nv, @vl_fecha_nota_venta, @vl_rut, @vl_dig_verif, @vl_nom_empresa,
														@vl_total_con_iva, 0, @vc_sum_monto_doc, @vc_nro_cuotas_tbk, 0, @vl_comision, @vl_monto_total_cuota, @ve_cod_usuario, @vc_cod_doc_ingreso_pago,
														@vl_monto_cuota, @vl_count_cuotas
							--Registra los detalles de los abonos
							set @vl_resto_cuotas = @vc_nro_cuotas_tbk - @vl_count_cuotas
							WHILE @vl_resto_cuotas < @vc_nro_cuotas_tbk BEGIN
								set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)

								exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc, @vc_sum_monto_doc, @vl_monto_cuota, @vc_nro_cuotas_tbk,
															@vl_count_cuotas, @vl_fecha_abono_cuota, @ve_cod_usuario, 6

								set @vl_resto_cuotas = @vl_resto_cuotas + 1
								set @vl_month_add = @vl_month_add + 1
								set @vl_count_cuotas = @vl_count_cuotas - 1
							END
						END
					END
					ELSE BEGIN
						exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, @vl_cod_nota_venta_nv, @vl_fecha_nota_venta, @vl_rut, @vl_dig_verif, @vl_nom_empresa,
													@vl_total_con_iva, 0, @vc_sum_monto_doc, @vc_nro_cuotas_tbk, 0,	@vl_comision, @vl_total_por_cobrar,	@ve_cod_usuario, @vc_cod_doc_ingreso_pago,	
													@vl_monto_cuota, @vc_nro_cuotas_tbk
						--Registra los detalles de los abonos
						set @vl_month_add = 0
						set @vl_count_cuotas = 0

						WHILE @vl_count_cuotas < @vc_nro_cuotas_tbk BEGIN
							set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)

							exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc, @vc_sum_monto_doc, @vl_monto_cuota, @vc_nro_cuotas_tbk,
														@vl_count_cuotas, @vl_fecha_abono_cuota, @ve_cod_usuario, 6

							set @vl_month_add = @vl_month_add + 1
							set @vl_count_cuotas = @vl_count_cuotas - 1
						END
					END
				END
				ELSE BEGIN
					-- el match entre las fa y las nv no son las mismas por ende se coloca varios
					if(@vl_fecha_actual >= @vl_fecha_abono)BEGIN
						--Hay cuotas vencidas por ende tiene que saber en que cuota esta parada
						set @vl_count_cuotas = @vc_nro_cuotas_tbk - 1
						set @vl_month_add = 1

						WHILE @vl_count_cuotas > 0 BEGIN
							set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)
							IF(@vl_fecha_actual >= @vl_fecha_abono_cuota)BEGIN
								set @vl_count_cuotas = @vl_count_cuotas - 1
								set @vl_month_add = @vl_month_add + 1
							END
							ELSE
								BREAK;
						END

						if(@vl_count_cuotas > 0)BEGIN
							set @vl_monto_total_cuota	= @vl_total_por_cobrar - ((@vc_nro_cuotas_tbk - @vl_count_cuotas) * @vl_monto_cuota)

							exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, 1, @vl_fecha_actual, 91462001, '5', 'Varios', 0, 0, @vc_sum_monto_doc, @vc_nro_cuotas_tbk, 0,
														@vl_comision, @vl_monto_total_cuota, @ve_cod_usuario, @vc_cod_doc_ingreso_pago,	@vl_monto_cuota, @vl_count_cuotas
							--Registra los detalles de los abonos
							set @vl_resto_cuotas = @vc_nro_cuotas_tbk - @vl_count_cuotas
							WHILE @vl_resto_cuotas < @vc_nro_cuotas_tbk BEGIN
								set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)

								exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc, @vc_sum_monto_doc, @vl_monto_cuota, @vc_nro_cuotas_tbk,
															@vl_count_cuotas, @vl_fecha_abono_cuota, @ve_cod_usuario, 6

								set @vl_resto_cuotas = @vl_resto_cuotas + 1
								set @vl_month_add = @vl_month_add + 1
								set @vl_count_cuotas = @vl_count_cuotas - 1
							END
						END
					END
					ELSE BEGIN
						--No excede nonguna cuota a la fecha actual por ende lo tira con todas las cuotas vigentes
						exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, 1, @vl_fecha_actual, 91462001, '5', 'Varios', 0, 0, @vc_sum_monto_doc, @vc_nro_cuotas_tbk, 0,
													@vl_comision, @vl_total_por_cobrar,	@ve_cod_usuario, @vc_cod_doc_ingreso_pago, @vl_monto_cuota, @vc_nro_cuotas_tbk
						--Registra los detalles de los abonos
						set @vl_month_add = 0
						set @vl_count_cuotas = 0

						WHILE @vl_count_cuotas < @vc_nro_cuotas_tbk BEGIN
							set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)

							exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc, @vc_sum_monto_doc, @vl_monto_cuota, @vc_nro_cuotas_tbk,
														@vl_count_cuotas, @vl_fecha_abono_cuota, @ve_cod_usuario, 6

							set @vl_month_add = @vl_month_add + 1
							set @vl_count_cuotas = @vl_count_cuotas - 1
						END
					END
				END
			END
			ELSE BEGIN
				-- las fa que provienen de las nv no coinciden y se deja en varios
				if(@vl_fecha_actual >= @vl_fecha_abono)BEGIN
					--Hay cuotas vencidas por ende tiene que saber en que cuota esta parada
					set @vl_count_cuotas = @vc_nro_cuotas_tbk - 1
					set @vl_month_add = 1

					WHILE @vl_count_cuotas > 0 BEGIN
						set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)
						IF(@vl_fecha_actual >= @vl_fecha_abono_cuota)BEGIN
							set @vl_count_cuotas = @vl_count_cuotas - 1
							set @vl_month_add = @vl_month_add + 1
						END
						ELSE
							BREAK;
					END

					if(@vl_count_cuotas > 0)BEGIN
						set @vl_monto_total_cuota	= @vl_total_por_cobrar - ((@vc_nro_cuotas_tbk - @vl_count_cuotas) * @vl_monto_cuota)

						exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, 1, @vl_fecha_actual, 91462001, '5', 'Varios', 0, 0, @vc_sum_monto_doc, @vc_nro_cuotas_tbk, 0,
													@vl_comision, @vl_monto_total_cuota, @ve_cod_usuario, @vc_cod_doc_ingreso_pago,	@vl_monto_cuota, @vl_count_cuotas
						--Registra los detalles de los abonos
						set @vl_resto_cuotas = @vc_nro_cuotas_tbk - @vl_count_cuotas
						WHILE @vl_resto_cuotas < @vc_nro_cuotas_tbk BEGIN
							set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)

							exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc, @vc_sum_monto_doc, @vl_monto_cuota, @vc_nro_cuotas_tbk,
														@vl_count_cuotas, @vl_fecha_abono_cuota, @ve_cod_usuario, 6

							set @vl_resto_cuotas = @vl_resto_cuotas + 1
							set @vl_month_add = @vl_month_add + 1
							set @vl_count_cuotas = @vl_count_cuotas - 1
						END
					END
				END
				ELSE BEGIN
					--No excede nonguna cuota a la fecha actual por ende lo tira con todas las cuotas vigentes
					exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, 1, @vl_fecha_actual, 91462001, '5', 'Varios', 0, 0, @vc_sum_monto_doc, @vc_nro_cuotas_tbk, 0,
												@vl_comision, @vl_total_por_cobrar,	@ve_cod_usuario, @vc_cod_doc_ingreso_pago, @vl_monto_cuota, @vc_nro_cuotas_tbk
					--Registra los detalles de los abonos
					set @vl_month_add = 0
					set @vl_count_cuotas = 0

					WHILE @vl_count_cuotas < @vc_nro_cuotas_tbk BEGIN
						set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)

						exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc, @vc_sum_monto_doc, @vl_monto_cuota, @vc_nro_cuotas_tbk,
													@vl_count_cuotas, @vl_fecha_abono_cuota, @ve_cod_usuario, 6

						set @vl_month_add = @vl_month_add + 1
						set @vl_count_cuotas = @vl_count_cuotas - 1
					END
				END
			END
		END
		ELSE BEGIN
			-- hay nv distintas varios
			if(@vl_fecha_actual >= @vl_fecha_abono)BEGIN
				--Hay cuotas vencidas por ende tiene que saber en que cuota esta parada
				set @vl_count_cuotas = @vc_nro_cuotas_tbk - 1
				set @vl_month_add = 1

				WHILE @vl_count_cuotas > 0 BEGIN
					set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)
					IF(@vl_fecha_actual >= @vl_fecha_abono_cuota)BEGIN
						set @vl_count_cuotas = @vl_count_cuotas - 1
						set @vl_month_add = @vl_month_add + 1
					END
					ELSE
						BREAK;
				END

				if(@vl_count_cuotas > 0)BEGIN
					set @vl_monto_total_cuota	= @vl_total_por_cobrar - ((@vc_nro_cuotas_tbk - @vl_count_cuotas) * @vl_monto_cuota)

					exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, 1, @vl_fecha_actual, 91462001, '5', 'Varios', 0, 0, @vc_sum_monto_doc, @vc_nro_cuotas_tbk, 0,
												@vl_comision, @vl_monto_total_cuota, @ve_cod_usuario, @vc_cod_doc_ingreso_pago,	@vl_monto_cuota, @vl_count_cuotas
					--Registra los detalles de los abonos
					set @vl_resto_cuotas = @vc_nro_cuotas_tbk - @vl_count_cuotas
					WHILE @vl_resto_cuotas < @vc_nro_cuotas_tbk BEGIN
						set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)

						exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc, @vc_sum_monto_doc, @vl_monto_cuota, @vc_nro_cuotas_tbk,
													@vl_count_cuotas, @vl_fecha_abono_cuota, @ve_cod_usuario, 6

						set @vl_resto_cuotas = @vl_resto_cuotas + 1
						set @vl_month_add = @vl_month_add + 1
						set @vl_count_cuotas = @vl_count_cuotas - 1
					END
				END
			END
			ELSE BEGIN
				--No excede nonguna cuota a la fecha actual por ende lo tira con todas las cuotas vigentes
				exec spu_inf_por_cobrar_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, 1, @vl_fecha_actual, 91462001, '5', 'Varios', 0, 0, @vc_sum_monto_doc, @vc_nro_cuotas_tbk, 0,
											@vl_comision, @vl_total_por_cobrar,	@ve_cod_usuario, @vc_cod_doc_ingreso_pago, @vl_monto_cuota, @vc_nro_cuotas_tbk
				--Registra los detalles de los abonos
				set @vl_month_add = 0
				set @vl_count_cuotas = 0

				WHILE @vl_count_cuotas < @vc_nro_cuotas_tbk BEGIN
					set @vl_fecha_abono_cuota = DATEADD(month, @vl_month_add, @vl_fecha_abono)

					exec spu_detalle_abono_tbk 'INSERT', null, @vc_cod_ingreso_pago, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc, @vc_sum_monto_doc, @vl_monto_cuota, @vc_nro_cuotas_tbk,
												@vl_count_cuotas, @vl_fecha_abono_cuota, @ve_cod_usuario, 6

					set @vl_month_add = @vl_month_add + 1
					set @vl_count_cuotas = @vl_count_cuotas - 1
				END
			END
		END

		FETCH C_INF_POR_COBRAR_TBK_CREDITO INTO @vc_cod_ingreso_pago, @vc_fecha_ingreso_pago, @vc_nro_cuotas_tbk, @vc_sum_monto_doc, @vc_cod_doc_ingreso_pago, @vc_fecha_doc, @vc_nro_doc
	END
	CLOSE C_INF_POR_COBRAR_TBK_CREDITO
	DEALLOCATE C_INF_POR_COBRAR_TBK_CREDITO
END