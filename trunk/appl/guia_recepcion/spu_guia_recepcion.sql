-------------------- spu_guia_recepcion ---------------------------------
ALTER PROCEDURE [dbo].[spu_guia_recepcion]
			(@ve_operacion					varchar(20)
			,@ve_cod_guia_recepcion			numeric
			,@ve_cod_usuario				numeric		= NULL
			,@ve_cod_empresa				numeric		= NULL
			,@ve_cod_sucursal				numeric		= NULL
			,@ve_cod_persona				numeric		= NULL
			,@ve_cod_estado_guia_recepcion	numeric		= NULL
			,@ve_cod_tipo_guia_recepcion	numeric		= NULL
			,@ve_tipo_doc					varchar(30)	= NULL
			,@ve_nro_doc					numeric		= NULL
			,@ve_cod_doc					numeric		= NULL
			,@ve_obs						text		= NULL
			,@ve_cod_usuario_anula			numeric		= NULL
			,@ve_motivo_anula				varchar(100)= NULL
			,@ve_tipo_recepcion				numeric		= NULL
			,@ve_cod_usuario_responsable	numeric		= NULL
			,@ve_obs_post_venta				text		= NULL
            ,@ve_no_bodega                  varchar(1)  = NULL
			,@ve_gr_resuelta				T_SI_NO		= NULL
			,@ve_cod_doc_gr_resuelta		numeric		= NULL
			,@ve_cod_doc_resuelta			numeric		= NULL
			,@ve_gr_resuelta_obs			text		= NULL)

AS
BEGIN
	declare	@kl_cod_estado_gr_ingresada	numeric,
			@kl_cod_estado_gr_anulada	numeric,
			@kl_cod_estado_gr_impresa	numeric,
			@vl_cod_usuario_anula		numeric,
			@KL_BODEGA_ORIGEN			numeric,
			@KL_BODEGA_DESTINO			numeric,
			@vl_referencia				varchar(100),
			@vl_nom_bodega_origen		varchar(100),
			@vl_nom_bodega_destino		varchar(100),
			@vl_cod_estado_guia_recepcion	numeric,
			@vl_cod_tipo_guia_recepcion     numeric,
			@vl_cod_mod_arriendo		numeric,
			@vl_cod_bodega				numeric,
			@vl_cod_usuario				numeric,
			@vl_cod_arriendo			numeric,
			@vl_new_cod_salida_bodega	numeric,
			@K_GR_ARRIENDO					numeric,
			@K_BODEGA_POR_RECICLAR			numeric,
			@K_GR_EMITIDA					numeric,
			@vl_cod_salida_bodega			numeric,
			@vl_cod_entrada_bodega			numeric,
			@vc_cod_producto				varchar(30),
			@vc_nom_producto				varchar(100),
			@vc_cantidad					T_CANTIDAD,
			@vl_item						numeric,
            @K_BODEGA_NO_DISPONIBLES        NUMERIC,
            @vl_no_bodega					varchar(1),
            @vl_cant_entradas NUMERIC

	set @kl_cod_estado_gr_ingresada = 1  --- estado de la gr = ingresada
	set @kl_cod_estado_gr_impresa = 2  --- estado de la gr = impresa
	set @kl_cod_estado_gr_anulada = 3  --- estado de la gr = anulada	
	SET @K_BODEGA_NO_DISPONIBLES = 9
	if (@ve_operacion='UPDATE') 
		begin
            SELECT @vl_cant_entradas = COUNT(*) FROM ENTRADA_BODEGA WHERE TIPO_DOC = 'GUIA_RECEPCION' AND COD_DOC = @ve_cod_guia_recepcion

            IF(@vl_cant_entradas > 0)
                SET @ve_no_bodega = 'N'
                
			UPDATE guia_recepcion		
			SET		
						cod_empresa					=	@ve_cod_empresa	
						,cod_sucursal				=	@ve_cod_sucursal
						,cod_persona				=	@ve_cod_persona
						,cod_estado_guia_recepcion	=	@ve_cod_estado_guia_recepcion	
						,cod_tipo_guia_recepcion	=	@ve_cod_tipo_guia_recepcion	
						,tipo_doc					=	@ve_tipo_doc
						,nro_doc					=	@ve_nro_doc
						,obs						=	@ve_obs
						,tipo_recepcion				=	@ve_tipo_recepcion
						,cod_usuario_responsable	=	@ve_cod_usuario_responsable
						,obs_post_venta				=	@ve_obs_post_venta
                        ,NO_BODEGA                  =   @ve_no_bodega
						,gr_resuelta				=	@ve_gr_resuelta
						,cod_doc_gr_resuelta		=	@ve_cod_doc_gr_resuelta
						,cod_doc_resuelta			=	@ve_cod_doc_resuelta
						,gr_resuelta_obs			=	@ve_gr_resuelta_obs

			WHERE cod_guia_recepcion = @ve_cod_guia_recepcion
			if (@ve_cod_estado_guia_recepcion = @kl_cod_estado_gr_anulada) and (@vl_cod_usuario_anula is NULL)begin -- estado de la GR = anulada 
				update guia_recepcion
				set fecha_anula				= getdate ()
					,motivo_anula			= @ve_motivo_anula			
					,cod_usuario_anula		= @ve_cod_usuario_anula				
				where cod_guia_recepcion	= @ve_cod_guia_recepcion

				exec spu_guia_recepcion 'MUEVE_BODEGA', @ve_cod_guia_recepcion, @ve_cod_usuario_anula
			end

            if (@ve_cod_estado_guia_recepcion = @kl_cod_estado_gr_impresa AND @ve_no_bodega = 'S') begin 
				exec spu_guia_recepcion 'BODEGA_NO_DISPONIBLE', @ve_cod_guia_recepcion
			end
		end
	else if (@ve_operacion='INSERT') 
		begin
			insert into guia_recepcion
				(fecha_guia_recepcion
				,cod_usuario
				,cod_empresa
				,cod_sucursal
				,cod_persona
				,cod_estado_guia_recepcion	
				,cod_tipo_guia_recepcion
				,tipo_doc
				,nro_doc
				,cod_doc
				,obs
				,tipo_recepcion
				,cod_usuario_responsable
				,obs_post_venta
                ,NO_BODEGA
				,gr_resuelta
				,cod_doc_gr_resuelta
				,cod_doc_resuelta
				,gr_resuelta_obs)
			values 
				(getdate()
				,@ve_cod_usuario	
				,@ve_cod_empresa	
				,@ve_cod_sucursal
				,@ve_cod_persona
				,@ve_cod_estado_guia_recepcion
				,@ve_cod_tipo_guia_recepcion
				,@ve_tipo_doc
				,@ve_nro_doc
				,@ve_cod_doc
				,@ve_obs
				,@ve_tipo_recepcion
				,@ve_cod_usuario_responsable
				,@ve_obs_post_venta
                ,@ve_no_bodega
				,@ve_gr_resuelta
				,@ve_cod_doc_gr_resuelta
				,@ve_cod_doc_resuelta
				,@ve_gr_resuelta_obs)
		end 
	else if (@ve_operacion='DELETE_ALL') 
			begin
				delete item_guia_recepcion 
				where cod_guia_recepcion = @ve_cod_guia_recepcion
				
				delete guia_recepcion
				where cod_guia_recepcion = @ve_cod_guia_recepcion
			end 
	else if (@ve_operacion='PRINT') begin
		select @vl_cod_estado_guia_recepcion = cod_estado_guia_recepcion
				,@vl_no_bodega = NO_BODEGA
		from guia_recepcion
		where  cod_guia_recepcion = @ve_cod_guia_recepcion
		
		if (@vl_cod_estado_guia_recepcion = @kl_cod_estado_gr_ingresada)begin
			update guia_recepcion
			set cod_estado_guia_recepcion = @kl_cod_estado_gr_impresa
			where  cod_guia_recepcion = @ve_cod_guia_recepcion
			
			--realiza los cambios necesarios si la GR es de RENTAL
			select @vl_cod_tipo_guia_recepcion = COD_TIPO_GUIA_RECEPCION	
			from GUIA_RECEPCION
			where  cod_guia_recepcion = @ve_cod_guia_recepcion
			
			if (@vl_cod_tipo_guia_recepcion = 4)begin	-- ARRIENDO
				exec spu_guia_recepcion 'RENTAL', @ve_cod_guia_recepcion
			end
		
			exec spu_guia_recepcion 'MUEVE_BODEGA', @ve_cod_guia_recepcion, @ve_cod_usuario
			
			if(@vl_no_bodega = 'S')
				exec spu_guia_recepcion 'BODEGA_NO_DISPONIBLE', @ve_cod_guia_recepcion
		end
		
	end
	else if (@ve_operacion='MUEVE_BODEGA') begin
		--VMC, 15-10-2013 los traspasos entre bodegas quedan deshabilitados
		return
		
			
		set @K_GR_ARRIENDO = 4
		set @K_BODEGA_POR_RECICLAR = 2
		set @K_GR_EMITIDA = 1

		select @vl_cod_tipo_guia_recepcion = cod_tipo_guia_recepcion 
				,@vl_cod_arriendo = cod_doc
				,@vl_cod_estado_guia_recepcion = cod_estado_guia_recepcion
		from guia_recepcion
		where cod_guia_recepcion = @ve_cod_guia_recepcion
		if (@vl_cod_tipo_guia_recepcion = @K_GR_ARRIENDO) begin	-- MOVER INVENTARIO
			if (@vl_cod_estado_guia_recepcion = @kl_cod_estado_gr_impresa)begin
				select @vl_cod_bodega = cod_bodega
				from arriendo
				where cod_arriendo = @vl_cod_arriendo
				
				select @vl_nom_bodega_origen = nom_bodega
				from bodega
				where cod_bodega = @vl_cod_bodega

				set @KL_BODEGA_DESTINO = 2 --RENTAL POR RECICLAR

				select @vl_nom_bodega_destino = nom_bodega
				from bodega
				where cod_bodega = @KL_BODEGA_DESTINO

				set @vl_referencia = 'Traspaso desde bodega '+@vl_nom_bodega_origen+' a bodega '+@vl_nom_bodega_destino

				exec spu_traspaso_bodega 'DESDE_GR_ARR', null, @ve_cod_usuario, @vl_cod_bodega, @KL_BODEGA_DESTINO, 'GUIA_RECEPCION', @ve_cod_guia_recepcion, @vl_referencia 
			end
			else if(@vl_cod_estado_guia_recepcion = @kl_cod_estado_gr_anulada) begin

				set @KL_BODEGA_ORIGEN = 2 --RENTAL POR RECICLAR

				select @vl_nom_bodega_origen = nom_bodega
				from bodega
				where cod_bodega = @KL_BODEGA_ORIGEN

				select @vl_cod_bodega = cod_bodega
				from arriendo
				where cod_arriendo = @vl_cod_arriendo
				
				select @vl_nom_bodega_destino = nom_bodega
				from bodega
				where cod_bodega = @vl_cod_bodega

				set @vl_referencia = 'Anulación de GR: Traspaso desde bodega '+@vl_nom_bodega_origen+' a bodega '+@vl_nom_bodega_destino
				exec spu_traspaso_bodega 'DESDE_GR_ARR', null, @ve_cod_usuario, @KL_BODEGA_ORIGEN, @vl_cod_bodega, 'GUIA_RECEPCION', @ve_cod_guia_recepcion, @vl_referencia 
			end
		end
	end
	else if (@ve_operacion='RENTAL') begin
		select @vl_cod_tipo_guia_recepcion = COD_TIPO_GUIA_RECEPCION		
				,@vl_cod_mod_arriendo = COD_DOC
				,@vl_cod_usuario = COD_USUARIO
		from GUIA_RECEPCION	
		where  cod_guia_recepcion = @ve_cod_guia_recepcion

		--solo hace algo cona las GD de ARRIENDO				
		if (@vl_cod_tipo_guia_recepcion <> 4)	-- ARRIENDO
			return
		
		--	obtiene la bodega del arriendo
		select @vl_cod_bodega = a.cod_bodega
				,@vl_cod_arriendo = a.COD_ARRIENDO
		from mod_arriendo m, ARRIENDO a
		where m.COD_MOD_ARRIENDO = @vl_cod_mod_arriendo
		  and a.COD_ARRIENDO = m.COD_ARRIENDO
	
		--crea una entrada a la bodega de arriendo
        set @vl_referencia = 'Arriendo Nro: ' + convert(varchar, @vl_cod_arriendo) + ' Mod.Arriendo: ' + convert(varchar, @vl_cod_mod_arriendo)
        exec spu_salida_bodega 'INSERT', null, @vl_cod_usuario, @vl_cod_bodega, 'GUIA_RECEPCION', @ve_cod_guia_recepcion, @vl_referencia
        set @vl_new_cod_salida_bodega = @@identity
        
        insert into ITEM_SALIDA_BODEGA
			(COD_SALIDA_BODEGA
			,ORDEN
			,ITEM
			,COD_PRODUCTO
			,NOM_PRODUCTO
			,CANTIDAD
			,COD_ITEM_DOC
			)
		select @vl_new_cod_salida_bodega
			, 0 
			, 0 
			,i.COD_PRODUCTO
			,i.NOM_PRODUCTO
			,i.CANTIDAD
			,i.COD_ITEM_GUIA_RECEPCION
        from item_guia_recepcion i
        where i.cod_guia_recepcion = @ve_cod_guia_recepcion
        
        set @vl_referencia = 'ENTRADA DESDE GR Nº ' + convert(varchar, @ve_cod_guia_recepcion) + ' / CONTRATO Nº ' + convert(varchar, @vl_cod_arriendo)
        exec spu_entrada_bodega 'INSERT', null, @vl_cod_usuario, 2, 'GUIA_RECEPCION', @ve_cod_guia_recepcion, @vl_referencia
        set @vl_cod_entrada_bodega = @@identity
        
        insert into ITEM_ENTRADA_BODEGA
			(COD_ENTRADA_BODEGA
			,ORDEN
			,ITEM
			,COD_PRODUCTO
			,NOM_PRODUCTO
			,CANTIDAD
			,PRECIO
			,COD_ITEM_DOC
			)
		select @vl_cod_entrada_bodega
			,(ROW_NUMBER() OVER(ORDER BY COD_GUIA_RECEPCION)) * 10
			,(ROW_NUMBER() OVER(ORDER BY COD_GUIA_RECEPCION))
			,i.COD_PRODUCTO
			,i.NOM_PRODUCTO
			,i.CANTIDAD
			,0
			,i.COD_ITEM_GUIA_RECEPCION
        from item_guia_recepcion i
        where i.cod_guia_recepcion = @ve_cod_guia_recepcion 
	END
    else if (@ve_operacion='BODEGA_NO_DISPONIBLE') begin
	    
        SELECT @vl_cant_entradas = COUNT(*) FROM ENTRADA_BODEGA WHERE TIPO_DOC = 'GUIA_RECEPCION' AND COD_DOC = @ve_cod_guia_recepcion

        IF(@vl_cant_entradas = 0)BEGIN
            select @vl_cod_usuario = COD_USUARIO
            from GUIA_RECEPCION	
            where  cod_guia_recepcion = @ve_cod_guia_recepcion

            set @vl_referencia = 'ENTRADA DESDE GR Nº ' + convert(varchar, @ve_cod_guia_recepcion)

            EXEC spu_entrada_bodega 'INSERT',NULL,@vl_cod_usuario,@K_BODEGA_NO_DISPONIBLES,'GUIA_RECEPCION', @ve_cod_guia_recepcion,@vl_referencia

            select @vl_cod_entrada_bodega = IDENT_CURRENT('ENTRADA_BODEGA')
            
            UPDATE ENTRADA_BODEGA SET ENTRADA_BODEGA.OBS = G.OBS 
            FROM GUIA_RECEPCION G WHERE ENTRADA_BODEGA.COD_ENTRADA_BODEGA = @vl_cod_entrada_bodega
            AND G.COD_GUIA_RECEPCION = @ve_cod_guia_recepcion

            insert into ITEM_ENTRADA_BODEGA
                (COD_ENTRADA_BODEGA
                ,ORDEN
                ,ITEM
                ,COD_PRODUCTO
                ,NOM_PRODUCTO
                ,CANTIDAD
                ,PRECIO
                ,COD_ITEM_DOC
                )
            select @vl_cod_entrada_bodega
                ,(ROW_NUMBER() OVER(ORDER BY COD_GUIA_RECEPCION)) * 10
                ,(ROW_NUMBER() OVER(ORDER BY COD_GUIA_RECEPCION))
                ,CASE i.COD_PRODUCTO WHEN 'TE'
                    THEN 'TE_'+dbo.f_get_correlativo_te()
                    ELSE i.COD_PRODUCTO
                END   
                ,i.NOM_PRODUCTO
                ,i.CANTIDAD
                ,0
                ,i.COD_ITEM_GUIA_RECEPCION
            from item_guia_recepcion i
            where i.cod_guia_recepcion = @ve_cod_guia_recepcion 
        END
    END
END
