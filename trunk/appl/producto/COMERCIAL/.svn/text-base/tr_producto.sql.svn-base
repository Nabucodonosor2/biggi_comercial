ALTER trigger [dbo].[tr_producto]
on [dbo].[PRODUCTO] for insert, update
as
begin
	declare @vl_cod_producto			varchar(30)
			,@vl_precio_venta_publico	numeric
			,@vl_precio_venta_interno	numeric
			,@vl_nom_producto			varchar(100)
			,@vl_nom_producto_ingles	varchar(100)
			,@vl_db_name				varchar(100)

	select @vl_cod_producto = cod_producto
			,@vl_precio_venta_publico = precio_venta_publico 
			,@vl_precio_venta_interno = precio_venta_interno
			,@vl_nom_producto	= nom_producto
			,@vl_nom_producto_ingles = nom_producto_ingles
	from inserted

	select @vl_db_name = DB_NAME()
	if (@vl_db_name='BIGGI') begin
		update  bodega_biggi.dbo.producto
		set precio_venta_publico = @vl_precio_venta_publico
			,precio_venta_interno = @vl_precio_venta_interno
			,nom_producto	= @vl_nom_producto
			,nom_producto_ingles = @vl_nom_producto_ingles
		where  cod_producto=@vl_cod_producto
	end
end
