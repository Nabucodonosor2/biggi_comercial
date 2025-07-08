-------------------- spu_item_cotizacion ---------------------------------
ALTER PROCEDURE [dbo].[spu_item_cotizacion](@ve_operacion varchar(20),
											@ve_cod_item_cotizacion numeric,
											@ve_cod_cotizacion numeric=NULL, 
											@ve_orden numeric=NULL,
											@ve_item varchar(10)=NULL, 
											@ve_cod_producto varchar(100)=NULL, 
											@ve_nom_producto varchar(100)=NULL, 
											@ve_cantidad T_CANTIDAD=NULL, 
											@ve_precio T_PRECIO=NULL, 
											@ve_motivo_mod_precio varchar(100)=NULL, 
											@ve_cod_usuario_mod_precio numeric=NULL,
											@ve_tipo_te varchar(100)=NULL,
											@ve_motivo_te varchar(100)=NULL)
AS
	DECLARE @precio_old NUMERIC,
			@cod_item_cotizacion NUMERIC,
			@vl_cod_empresa		 NUMERIC

	IF(@ve_cod_cotizacion >= 244862)BEGIN
		SELECT @vl_cod_empresa	= COD_EMPRESA
		FROM COTIZACION
		WHERE COD_COTIZACION = @ve_cod_cotizacion
		
		IF(@vl_cod_empresa = 630 OR @vl_cod_empresa = 3109 OR @vl_cod_empresa = 5308 OR @vl_cod_empresa = 7326)
			SET @ve_precio = dbo.f_get_precio_aramark(@ve_cod_producto, @ve_precio, 242662)
		
	END
	
BEGIN
	IF (@ve_operacion='INSERT') BEGIN
		INSERT INTO item_cotizacion(
					cod_cotizacion,
					orden,
					item,
					cod_producto,
					nom_producto,
					cantidad,
					precio,
					cod_tipo_te,		
					motivo_te)
		VALUES		(
					@ve_cod_cotizacion,
					@ve_orden,
					@ve_item,
					@ve_cod_producto,
					@ve_nom_producto,
					@ve_cantidad,
					@ve_precio,
					@ve_tipo_te,
					@ve_motivo_te
					) 
	
		SET @cod_item_cotizacion = @@identity
		IF(@ve_motivo_mod_precio<>'')BEGIN -- tiene motivo, por lo tanto se modificó el precio
			SELECT @precio_old = precio_venta_publico
			FROM producto
			WHERE cod_producto = @ve_cod_producto	

			INSERT INTO modifica_precio_cotizacion 
			VALUES (@cod_item_cotizacion, @ve_cod_usuario_mod_precio, getdate(), @precio_old, @ve_precio, @ve_motivo_mod_precio)	
		END
	END 
	ELSE IF(@ve_operacion='UPDATE')BEGIN
		SELECT @precio_old = precio
		FROM item_cotizacion
		WHERE cod_item_cotizacion = @ve_cod_item_cotizacion
	
		IF(@ve_motivo_mod_precio<>'') -- tiene motivo, por lo tanto se modificó el precio
			INSERT INTO modifica_precio_cotizacion 
			VALUES (@ve_cod_item_cotizacion, @ve_cod_usuario_mod_precio, getdate(), @precio_old, @ve_precio, @ve_motivo_mod_precio)
		
		UPDATE item_cotizacion
		SET cod_cotizacion		=	@ve_cod_cotizacion,
			orden				=	@ve_orden,
			item				=	@ve_item,
			cod_producto		=	@ve_cod_producto,
			nom_producto		=	@ve_nom_producto,
			cantidad			=	@ve_cantidad,
			precio				=	@ve_precio,
			cod_tipo_te			=	@ve_tipo_te,				
			motivo_te			=	@ve_motivo_te   
		WHERE cod_item_cotizacion	=	@ve_cod_item_cotizacion
	END
	ELSE IF(@ve_operacion='DELETE')BEGIN
		DELETE modifica_precio_cotizacion
		WHERE cod_item_cotizacion = @ve_cod_item_cotizacion
	
		DELETE	item_cotizacion 
	    WHERE	cod_item_cotizacion = @ve_cod_item_cotizacion
	END	
END