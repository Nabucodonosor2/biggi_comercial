alter PROCEDURE spx_busca_FA_sin_salida
AS
BEGIN  
	declare c_item cursor for 
	select   cod_item_factura
			,COD_PRODUCTO  
			,CANTIDAD      
	from factura f, item_factura itf
	where f.fecha_factura between {ts '2011-01-01 00:00:00.000'} and getdate()
	and f.cod_estado_doc_sii = 2	
	and itf.cod_factura = f.cod_factura

	declare
		@vc_cod_item_factura	numeric
		,@vc_cod_producto		varchar(100)
		,@vc_cantidad			numeric(10,2)
		,@vl_cant_salida		numeric(10,2)

	open c_item
	fetch c_item into @vc_cod_item_factura,@vc_cod_producto,@vc_cantidad
	while @@fetch_status = 0 begin
		select @vl_cant_salida = count(*)
		from salida_bodega s, item_salida_bodega its
		where s.tipo_doc = 'FACTURA'
		  and its.cod_salida_bodega = s.cod_salida_bodega
		  and its.cod_item_doc = @vc_cod_item_factura
		  and its.cod_producto <> @vc_cod_producto
		if (@vl_cant_salida > 0)
			select 'ERROR: salida  a otro cod_producto', @vc_cod_item_factura, @vc_cod_producto, @vc_cantidad, @vl_cant_salida

		select @vl_cant_salida = sum(cantidad)
		from salida_bodega s, item_salida_bodega its
		where s.tipo_doc = 'FACTURA'
		  and its.cod_salida_bodega = s.cod_salida_bodega
		  and its.cod_item_doc = @vc_cod_item_factura
		if (@vl_cant_salida <> @vc_cantidad)
			select 'ERROR: no suma igual', @vc_cod_item_factura, @vc_cod_producto, @vc_cantidad, @vl_cant_salida

		fetch c_item into @vc_cod_item_factura,@vc_cod_producto,@vc_cantidad
	end
	close c_item
	deallocate c_item
END

