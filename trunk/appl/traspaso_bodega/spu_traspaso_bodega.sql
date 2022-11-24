ALTER PROCEDURE [dbo].[spu_traspaso_bodega] (@ve_operacion				varchar(20)
								,@ve_cod_traspaso_bodega		numeric
								,@ve_cod_usuario				numeric = NULL
                                ,@ve_cod_bodega_origen			numeric = NULL
								,@ve_cod_bodega_destino			numeric = NULL
								,@ve_cod_estado_traspaso		numeric = NULL
								,@ve_referencia					varchar(100) = NULL
                                ,@ve_obs                        text = null)
								
AS
BEGIN  
	declare	@vl_cod_traspaso_bodega numeric
			,@vl_item	numeric
			,@vc_cod_producto varchar(30)
			,@vc_nom_producto varchar(100)
			,@vc_cantidad	T_CANTIDAD

	if (@ve_operacion='INSERT')
		insert into traspaso_bodega
		   (FECHA_REGISTRO
		   ,FECHA_TRASPASO_BODEGA
		   ,COD_USUARIO
		   ,COD_ESTADO_TRASPASO
		   ,COD_BODEGA_ORIGEN
		   ,COD_BODEGA_DESTINO
		   ,REFERENCIA
           ,OBS)
		values (getdate()
		   ,getdate()
		   ,@ve_cod_usuario
		   ,@ve_cod_estado_traspaso
		   ,@ve_cod_bodega_origen
		   ,@ve_cod_bodega_destino
		   ,@ve_referencia
           ,@ve_obs)

	else if (@ve_operacion='UPDATE')
		update traspaso_bodega
		set cod_usuario			    = @ve_cod_usuario 
		   ,cod_bodega_origen	    = @ve_cod_bodega_origen
		   ,cod_bodega_destino	    = @ve_cod_bodega_destino 
		   ,COD_ESTADO_TRASPASO	    = @ve_cod_estado_traspaso
		   ,referencia			    = @ve_referencia
           ,OBS                     = @ve_obs
		where cod_traspaso_bodega = @ve_cod_traspaso_bodega

        if(@ve_cod_estado_traspaso = 2)
            exec spu_traspaso_bodega 'CONFIRMA', @ve_cod_traspaso_bodega
	
	else if (@ve_operacion='CONFIRMA')
	begin
		

		set @vl_cod_traspaso_bodega = @@identity
        /*
		declare C_ITEM_GR cursor for  
		select cod_producto
				,nom_producto
				,cantidad
		from item_guia_recepcion
		where cod_guia_recepcion = @ve_cod_doc
		  and cantidad > 0

		set @vl_item = 1
		OPEN C_ITEM_GR
		FETCH C_ITEM_GR INTO @vc_cod_producto, @vc_nom_producto, @vc_cantidad
		WHILE @@FETCH_STATUS = 0 BEGIN
           insert into item_traspaso_bodega
				(cod_traspaso_bodega
				,orden
				,item
				,cod_producto
				,nom_producto
				,cantidad)				
			values
				(@vl_cod_traspaso_bodega
				,@vl_item * 10
				,@vl_item
				,@vc_cod_producto
				,@vc_nom_producto
				,@vc_cantidad
				)

			set @vl_item = @vl_item + 1

			FETCH C_ITEM_GR INTO @vc_cod_producto, @vc_nom_producto, @vc_cantidad
		END
		CLOSE C_ITEM_GR
		DEALLOCATE C_ITEM_GR*/
	end
	else if (@ve_operacion='DESDE_GD_ARR')
	begin
	    set @vl_cod_traspaso_bodega = @@identity
        /*
		declare C_ITEM_GD cursor for  
		select cod_producto
				,nom_producto
				,cantidad
		from item_guia_despacho
		where cod_guia_despacho = @ve_cod_doc
		  and cantidad > 0

		set @vl_item = 1
		OPEN C_ITEM_GD
		FETCH C_ITEM_GD INTO @vc_cod_producto, @vc_nom_producto, @vc_cantidad
		WHILE @@FETCH_STATUS = 0 BEGIN

			insert into item_traspaso_bodega
				(cod_traspaso_bodega
				,orden
				,item
				,cod_producto
				,nom_producto
				,cantidad)				
			values
				(@vl_cod_traspaso_bodega
				,@vl_item * 10
				,@vl_item
				,@vc_cod_producto
				,@vc_nom_producto
				,@vc_cantidad
				)

			set @vl_item = @vl_item + 1

			FETCH C_ITEM_GD INTO @vc_cod_producto, @vc_nom_producto, @vc_cantidad
		END
		CLOSE C_ITEM_GD
		DEALLOCATE C_ITEM_GD
        */
	end 
END

