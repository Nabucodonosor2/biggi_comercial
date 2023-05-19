-------------------- spu_ingreso_pago ---------------------------------
ALTER PROCEDURE spu_ingreso_pago
			(@ve_operacion					varchar(20)
			,@ve_cod_ingreso_pago			numeric
			,@ve_cod_usuario				numeric		 = NULL
			,@ve_cod_empresa				numeric		 = NULL
			,@ve_otro_ingreso				numeric		 = NULL
			,@ve_otro_gasto					numeric		 = NULL
			,@ve_cod_estado_ingreso_pago	numeric		 = NULL
			,@ve_cod_usuario_anula			numeric		 = NULL
			,@ve_motivo_anula				varchar(100) = NULL
			,@ve_cod_usuario_confirma		numeric		 = NULL
			,@ve_otro_anticipo				numeric		 = NULL
			,@ve_cod_proyecto_ingreso		numeric		 = NULL
			,@ve_ws_origen					varchar(50)	 = NULL
			,@ve_cod_doc_origen				numeric		 = NULL
			,@ve_tipo_origen_pago			varchar(100)	 = NULL)
AS
BEGIN
	declare		@kl_cod_estado_ingreso_pago_anula		numeric,
				@kl_cod_estado_ingreso_pago_confirma	numeric,
				@vl_cod_usuario_anula					numeric,
				@vl_cod_ingreso_pago_factura			numeric,
				@vl_cod_doc_ingreso_pago				numeric,
				@vl_monto								numeric,
				@vl_monto_doc_total						numeric,
				@vl_tipo_origen_pago					varchar(100)
	set @kl_cod_estado_ingreso_pago_anula	 = 3  --- estado_ingreso_pago = anulada
	set @kl_cod_estado_ingreso_pago_confirma = 2  --- estado_ingreso_pago = confirma
	if(@ve_tipo_origen_pago = 'WEBPAY_PLUS')BEGIN
		set @vl_tipo_origen_pago = 'WEBPAY PLUS'	 
	END
	else
		set @vl_tipo_origen_pago = 'MANUAL' 
    if (@ve_operacion='UPDATE') 
        begin
            UPDATE ingreso_pago		
            SET		
                        cod_empresa					=	@ve_cod_empresa	
                        ,otro_ingreso				=	@ve_otro_ingreso
                        ,otro_gasto					=	@ve_otro_gasto
                        ,cod_estado_ingreso_pago	=	@ve_cod_estado_ingreso_pago
                        ,otro_anticipo				=	@ve_otro_anticipo
                        ,cod_proyecto_ingreso		=	@ve_cod_proyecto_ingreso
            WHERE cod_ingreso_pago = @ve_cod_ingreso_pago
            if (@ve_cod_estado_ingreso_pago = @kl_cod_estado_ingreso_pago_anula) and (@vl_cod_usuario_anula is NULL) -- estado del ingreso_pago = anulada 
                update ingreso_pago
                set fecha_anula			= getdate ()
                    ,motivo_anula		= @ve_motivo_anula			
                    ,cod_usuario_anula	= @ve_cod_usuario_anula				
                where cod_ingreso_pago  = @ve_cod_ingreso_pago
        end
    else if (@ve_operacion='INSERT') 
        begin
            insert into ingreso_pago
                (fecha_ingreso_pago
                ,cod_usuario
                ,cod_empresa
                ,otro_ingreso
                ,otro_gasto
                ,cod_estado_ingreso_pago
                ,cod_usuario_confirma
                ,otro_anticipo
                ,cod_proyecto_ingreso
                ,ws_origen
                ,cod_doc_origen
                ,NOM_TIPO_ORIGEN_PAGO)
            values 
                (getdate()
                ,@ve_cod_usuario	
                ,@ve_cod_empresa	
                ,@ve_otro_ingreso
                ,@ve_otro_gasto
                ,@ve_cod_estado_ingreso_pago
                ,@ve_cod_usuario_confirma		
                ,@ve_otro_anticipo
                ,@ve_cod_proyecto_ingreso
                ,@ve_ws_origen
                ,@ve_cod_doc_origen
                ,@vl_tipo_origen_pago)
        end 
    else if(@ve_operacion='CONFIRMA')
        begin
            if (@ve_cod_estado_ingreso_pago = @kl_cod_estado_ingreso_pago_confirma) -- estado del ingreso_pago = confirmada
                update ingreso_pago
                    set fecha_confirma			= getdate ()
                        ,cod_usuario_confirma	= @ve_cod_usuario_confirma				
                    where cod_ingreso_pago		= @ve_cod_ingreso_pago					
            DECLARE C_DOC_INGRESO_PAGO CURSOR FOR 
            select cod_doc_ingreso_pago, monto_doc
            from doc_ingreso_pago
            where cod_ingreso_pago = @ve_cod_ingreso_pago
            declare
                @cod_doc_ingreso_pago		numeric,
                @monto_doc					T_PRECIO,
                @cod_ingreso_pago_factura	numeric,
                @saldo_por_relacionar		T_PRECIO,
                @monto_doc_asignado			T_PRECIO
            OPEN C_DOC_INGRESO_PAGO
            FETCH C_DOC_INGRESO_PAGO INTO @cod_doc_ingreso_pago, @monto_doc
            WHILE @@FETCH_STATUS = 0 BEGIN
                DECLARE C_INGRESO_PAGO_FA CURSOR FOR 
                select cod_ingreso_pago_factura
                        ,dbo.f_ingreso_pago_saldo_por_relacionar(cod_ingreso_pago_factura) saldo_por_relacionar
                from ingreso_pago_factura
                where cod_ingreso_pago = @ve_cod_ingreso_pago
                OPEN C_INGRESO_PAGO_FA
                FETCH C_INGRESO_PAGO_FA INTO @cod_ingreso_pago_factura, @saldo_por_relacionar
                WHILE @@FETCH_STATUS = 0 BEGIN
                    if (@saldo_por_relacionar > @monto_doc)
                        set @monto_doc_asignado = @monto_doc
                    else
                        set @monto_doc_asignado = @saldo_por_relacionar
                    set @monto_doc = @monto_doc - @monto_doc_asignado
                    insert into monto_doc_asignado
                        (cod_doc_ingreso_pago
                        ,cod_ingreso_pago_factura
                        ,monto_doc_asignado)
                    values
                        (@cod_doc_ingreso_pago
                        ,@cod_ingreso_pago_factura
                        ,@monto_doc_asignado)
                    if (@monto_doc = 0)
                        BREAK 
                    FETCH C_INGRESO_PAGO_FA INTO @cod_ingreso_pago_factura, @saldo_por_relacionar
                END
                CLOSE C_INGRESO_PAGO_FA
                DEALLOCATE C_INGRESO_PAGO_FA
                FETCH C_DOC_INGRESO_PAGO INTO @cod_doc_ingreso_pago, @monto_doc
            END
            CLOSE C_DOC_INGRESO_PAGO
            DEALLOCATE C_DOC_INGRESO_PAGO
            ---------------------------------
            -- Busca FA asociadas a la NV que tengan saldo > 0, para ver si se deben reasginar pagos desde NV a FA
            declare C_FA cursor for
                select f.cod_factura
                from ingreso_pago_factura ipf, factura f
                where ipf.cod_ingreso_pago = @ve_cod_ingreso_pago
                    and ipf.tipo_doc = 'NOTA_VENTA'
                    and f.cod_doc = ipf.cod_doc
                    and f.cod_tipo_factura = 1	-- venta
                    and f.cod_estado_doc_sii in (2, 3)	-- confirmada
                    and dbo.f_factura_get_saldo(f.cod_factura) > 0
            declare 
                @cod_factura			numeric
            OPEN C_FA
            FETCH C_FA INTO @cod_factura
            WHILE @@FETCH_STATUS = 0 BEGIN
                exec spu_factura 'REASIGNA_PAGO', @cod_factura
                FETCH C_FA INTO @cod_factura
            END
            CLOSE C_FA
            DEALLOCATE C_FA
            ----------------------------------	
            /*VALIDACION AUTORIZACION NOTA DE VENTA*/
            DECLARE
                @vl_cant_docs       NUMERIC
                ,@vl_cant_facturas  NUMERIC
                ,@vl_cant_nv        NUMERIC
                ,@vl_cod_nv         NUMERIC
                ,@vl_tot_iva_nv     NUMERIC
                ,@vl_total_ingreso  NUMERIC
                ,@vl_autoriza_venta VARCHAR(1)
                ,@vl_filtro_cant   VARCHAR(1)
                ,@vl_cant_doc_wp   NUMERIC
                ,@vl_nom_origen_pago VARCHAR(100)
				
            SET  @vl_filtro_cant = 'S'
			
            SELECT @vl_cant_docs = COUNT(*) 
			FROM DOC_INGRESO_PAGO 
			WHERE COD_INGRESO_PAGO = @ve_cod_ingreso_pago
            AND COD_TIPO_DOC_PAGO NOT IN (1,10,6,5) --1 EFECTIVO | 10 TRANSFERENCIA BANCARIA | 6 TARJETA CREDITO | TARJETA DÉBITO
			
            SELECT @vl_nom_origen_pago = NOM_TIPO_ORIGEN_PAGO 
			FROM INGRESO_PAGO 
			WHERE COD_INGRESO_PAGO = @ve_cod_ingreso_pago
			
            IF(@vl_nom_origen_pago = 'WEBPAY_PLUS')BEGIN
                SELECT @vl_cant_doc_wp = COUNT(*) 
				FROM DOC_INGRESO_PAGO  
				WHERE COD_INGRESO_PAGO = @ve_cod_ingreso_pago
                AND COD_TIPO_DOC_PAGO IN (6,5)
				
                IF(@vl_cant_doc_wp > 1)
                    SET @vl_filtro_cant = 'N'
			END 
			
            -- SI EL INGRESO_PAGO ESTA PAGANDO SOLO UNA NOTA_VENTA Y ESA NOTA DE VENTA NO TIENE FACTURAS ASOCIADAS EVALUA SI LA DEBE AUTORIZAR EL DESPACHO
            IF(@vl_cant_docs = 0 AND @vl_filtro_cant = 'S')BEGIN
			
                SELECT @vl_cant_facturas = COUNT(*) 
				FROM INGRESO_PAGO_FACTURA 
				WHERE COD_INGRESO_PAGO = @ve_cod_ingreso_pago
                AND TIPO_DOC = 'FACTURA'
				
                IF(@vl_cant_facturas = 0)BEGIN
				
                    SELECT @vl_cant_nv = COUNT(*) 
					FROM INGRESO_PAGO_FACTURA 
					WHERE COD_INGRESO_PAGO = @ve_cod_ingreso_pago
                    AND TIPO_DOC = 'NOTA_VENTA'
					
                    IF(@vl_cant_nv = 1)BEGIN
					
                        SELECT @vl_cod_nv = COD_DOC 
						FROM INGRESO_PAGO_FACTURA 
						WHERE COD_INGRESO_PAGO = @ve_cod_ingreso_pago
                        AND TIPO_DOC = 'NOTA_VENTA'
						
                        SELECT @vl_tot_iva_nv = TOTAL_CON_IVA 
                            ,@vl_autoriza_venta = AUTORIZA_PROCESAR_VENTA
                        FROM NOTA_VENTA 
						WHERE COD_NOTA_VENTA = @vl_cod_nv
						
                        SELECT @vl_total_ingreso = SUM(MONTO_DOC) 
						FROM DOC_INGRESO_PAGO 
						WHERE COD_INGRESO_PAGO = @ve_cod_ingreso_pago
						
                        /* 21-01-2020 RAFAEL ESCUDERO SOLICITA A MH DESACTIVAR LA AUTORIZACION DE DESPACHO VIA APROBACION DE INGRESO DE PAGO */
                        /* 21-04-2022 JCATALA y SPECHOANTE SOLICITA A MH ACTIVAR LA AUTORIZACION DE DESPACHO VIA APROBACION DE INGRESO DE PAGO */
                        IF(@vl_tot_iva_nv = @vl_total_ingreso AND (@vl_autoriza_venta = 'N' OR @vl_autoriza_venta IS NULL))BEGIN
						
                            UPDATE NOTA_VENTA 
							SET AUTORIZA_PROCESAR_VENTA = 'S'
								,COD_INGRESO_PAGO = @ve_cod_ingreso_pago
                            WHERE COD_NOTA_VENTA = @vl_cod_nv
							
                        END
						
                    END 
					
                END 
                ELSE IF (@vl_cant_facturas > 0)BEGIN /*SI TIENE FACTURAS ASOCIADAS*/
				
                    SELECT @vl_cant_nv = COUNT(*) 
					FROM INGRESO_PAGO_FACTURA 
					WHERE COD_INGRESO_PAGO = @ve_cod_ingreso_pago
                    AND TIPO_DOC = 'NOTA_VENTA'
                    AND MONTO_ASIGNADO <> 0
                    /*
                    19-05-2023 MH: CUANDO ENCUENTRA REGISTROS TIPO "NOTA_VENTA" EN LA TABLA "INGRESO_PAGO_FACTURA" PERO EL CAMPO
                    MONTO_ASIGNADO ES IGUAL A CERO ESOS REGISTRO NO AFECTAN EL PROCESO DE AUTORIZACION DE LA NOTA DE VENTA
                    */
					
                    IF(@vl_cant_nv = 0)BEGIN
                        DECLARE @vl_cod_fa         NUMERIC
                                ,@vl_cant_nv_fas   NUMERIC 

                        -- SI EL INGRESO_PAGO ESTA PAGANDO VARIAS SOLO 1 FACTURA PERO PARA UNA MISMA NOTA_VENTA EVALUA SI LA DEBE AUTORIZAR EL DESPACHO                         
                        IF(@vl_cant_facturas = 1)BEGIN
						
                            SELECT @vl_cod_fa = COD_DOC 
							FROM INGRESO_PAGO_FACTURA 
                            WHERE COD_INGRESO_PAGO = @ve_cod_ingreso_pago
                            AND TIPO_DOC = 'FACTURA'
							
							SELECT @vl_cod_nv = ISNULL(COD_DOC,0) 
							FROM FACTURA
							WHERE COD_FACTURA = @vl_cod_fa
							AND TIPO_DOC = 'NOTA_VENTA'
							
							IF (@vl_cod_nv <> 0)BEGIN
							
								SELECT @vl_tot_iva_nv = TOTAL_CON_IVA 
									,@vl_autoriza_venta = AUTORIZA_PROCESAR_VENTA
								FROM NOTA_VENTA 
								WHERE COD_NOTA_VENTA = @vl_cod_nv
								
								SELECT @vl_total_ingreso = SUM(MONTO_DOC) 
								FROM DOC_INGRESO_PAGO 
								WHERE COD_INGRESO_PAGO = @ve_cod_ingreso_pago
								
								IF(@vl_tot_iva_nv = @vl_total_ingreso AND (@vl_autoriza_venta = 'N' OR @vl_autoriza_venta IS NULL))BEGIN
								
									UPDATE NOTA_VENTA SET 
									AUTORIZA_PROCESAR_VENTA = 'S'
									,COD_INGRESO_PAGO = @ve_cod_ingreso_pago
									WHERE COD_NOTA_VENTA = @vl_cod_nv
									
								END 
							END
								
                        END 
                        -- SI EL INGRESO_PAGO ESTA PAGANDO VARIAS FACTURAS PERO PARA UNA MISMA NOTA_VENTA EVALUA SI LA DEBE AUTORIZAR EL DESPACHO
                        ELSE IF (@vl_cant_facturas > 1)BEGIN
						
                            SELECT @vl_cant_nv_fas = COUNT(*) 
							FROM NOTA_VENTA 
							where COD_NOTA_VENTA IN (SELECT COD_DOC 
													 FROM FACTURA 
													 WHERE TIPO_DOC = 'NOTA_VENTA' 
													 AND COD_FACTURA IN (SELECT COD_DOC 
																		 FROM INGRESO_PAGO_FACTURA 
																		 WHERE COD_INGRESO_PAGO = @ve_cod_ingreso_pago
																		 AND TIPO_DOC = 'FACTURA'))
                            
                            IF(@vl_cant_nv_fas = 1)BEGIN
							
                                SELECT @vl_cod_nv = COD_NOTA_VENTA 
								FROM NOTA_VENTA 
								where COD_NOTA_VENTA IN (SELECT COD_DOC 
														 FROM FACTURA 
														 WHERE TIPO_DOC = 'NOTA_VENTA' 
														 AND COD_FACTURA IN (SELECT COD_DOC 
																			 FROM INGRESO_PAGO_FACTURA 
																			 WHERE COD_INGRESO_PAGO = @ve_cod_ingreso_pago
																			 AND TIPO_DOC = 'FACTURA'))
                                
								
								
                                SELECT @vl_tot_iva_nv = TOTAL_CON_IVA 
                                    ,@vl_autoriza_venta = AUTORIZA_PROCESAR_VENTA
                                FROM NOTA_VENTA 
								WHERE COD_NOTA_VENTA = @vl_cod_nv
								
                                SELECT @vl_total_ingreso = SUM(MONTO_DOC) 
								FROM DOC_INGRESO_PAGO 
								WHERE COD_INGRESO_PAGO = @ve_cod_ingreso_pago
								
                                IF(@vl_tot_iva_nv = @vl_total_ingreso AND (@vl_autoriza_venta = 'N' OR @vl_autoriza_venta IS NULL))BEGIN
								
                                    UPDATE NOTA_VENTA SET 
                                    AUTORIZA_PROCESAR_VENTA = 'S'
                                    ,COD_INGRESO_PAGO = @ve_cod_ingreso_pago
                                    WHERE COD_NOTA_VENTA = @vl_cod_nv
									
                                END 
								
                            END
							
                        END 
                    END
                END 
            END 
        end
    else if (@ve_operacion='DELETE_ALL') 
    begin
        delete ingreso_pago_factura
        where cod_ingreso_pago = @ve_cod_ingreso_pago 
        delete ingreso_pago
        where cod_ingreso_pago = @ve_cod_ingreso_pago
    end 
END
