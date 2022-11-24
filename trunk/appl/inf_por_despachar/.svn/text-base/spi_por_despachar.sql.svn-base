--------------------- spi_por_despachar -------------------
alter PROCEDURE spi_por_despachar(@ve_cod_usuario			numeric)
AS
BEGIN
declare
	@vl_fecha_actual		datetime

	set @vl_fecha_actual = getdate()

	-- borra el resultado de informes anteriores del mismo usuario
	delete INF_POR_DESPACHAR
	where cod_usuario = @ve_cod_usuario

	insert into INF_POR_DESPACHAR
		(FECHA_INF_POR_DESPACHAR  
		,COD_USUARIO              
		,COD_NOTA_VENTA           
		,FECHA_NOTA_VENTA         
		,NOM_EMPRESA              
		,INI_USUARIO   
		,COD_USUARIO_VENDEDOR1           
		,ITEM                     
		,COD_PRODUCTO             
		,NOM_PRODUCTO             
		,CANTIDAD                 
		,CANTIDAD_POR_DESPACHAR   
		)
	select @vl_fecha_actual
			,@ve_cod_usuario
			,NV.COD_NOTA_VENTA
			,NV.FECHA_NOTA_VENTA
			,E.NOM_EMPRESA
			,U.INI_USUARIO
			,NV.COD_USUARIO_VENDEDOR1
			,INV.ITEM
			,INV.COD_PRODUCTO
			,INV.NOM_PRODUCTO
			,INV.CANTIDAD
			,dbo.f_nv_cant_por_despachar(INV.COD_ITEM_NOTA_VENTA, default) CANTIDAD_POR_DESPACHAR
	from	NOTA_VENTA NV, ITEM_NOTA_VENTA INV, PRODUCTO P, EMPRESA E, USUARIO U
	where	NV.COD_NOTA_VENTA = INV.COD_NOTA_VENTA AND
			P.COD_PRODUCTO = INV.COD_PRODUCTO AND
			E.COD_EMPRESA = NV.COD_EMPRESA  AND
			NV.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO AND
			dbo.f_nv_cant_por_despachar(INV.COD_ITEM_NOTA_VENTA, default) > 0 and
			NV.cod_estado_nota_venta <> 3 and	-- Anulada
	        dbo.f_get_tiene_acceso(@ve_cod_usuario, 'NOTA_VENTA', NV.COD_USUARIO_VENDEDOR1,NV.COD_USUARIO_VENDEDOR2) = 1
	order by NV.COD_NOTA_VENTA, INV.ITEM
END
