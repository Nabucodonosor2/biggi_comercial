USE [BIGGI]
GO
/****** Object:  StoredProcedure [dbo].[spi_por_despachar]    Script Date: 02/02/2023 12:55:11 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
--------------------- spi_por_despachar -------------------
ALTER PROCEDURE [dbo].[spi_por_despachar](@ve_cod_usuario			numeric)
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
		,FECHA_ENTREGA
		,DIAS_ATRASO
        ,COD_GUIA_RECEPCION
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
			,NV.FECHA_ENTREGA
			,case 
				when ((DATEDIFF ( day , NV.FECHA_ENTREGA , getdate())) < 0) then 0 
				else DATEDIFF ( day , NV.FECHA_ENTREGA , getdate())
			end  DIAS_ATRASO
            ,dbo.f_nv_get_gr(NV.COD_NOTA_VENTA, 18870)
	from	NOTA_VENTA NV, ITEM_NOTA_VENTA INV, PRODUCTO P, EMPRESA E, USUARIO U
	where	
            INV.COD_NOTA_VENTA > 91551 AND
            NV.COD_NOTA_VENTA = INV.COD_NOTA_VENTA AND
			P.COD_PRODUCTO = INV.COD_PRODUCTO AND
			E.COD_EMPRESA = NV.COD_EMPRESA  AND
			NV.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO AND
			dbo.f_nv_cant_por_despachar(INV.COD_ITEM_NOTA_VENTA, default) > 0 and
			NV.cod_estado_nota_venta NOT IN (1, 3, 5) and --EMITIDA, ANULADA, CERRADA ADM
	        dbo.f_get_tiene_acceso(@ve_cod_usuario, 'NOTA_VENTA', NV.COD_USUARIO_VENDEDOR1,NV.COD_USUARIO_VENDEDOR2) = 1
	order by NV.COD_NOTA_VENTA, INV.ITEM
END
    -- MH 12-02-2021 SE ESTABLECE QUE SOLO CALCULE LAS NV DE FECHA MAYOR O IGUAL AÑO 2020.
    -- CON ESTE CAMBIO QUEDAN FUERA LAS NV 72275, 72562, 80455, 84348 Y 90244 LAS CUALES TENIAN AUN EQ POR DESPACHAR.
