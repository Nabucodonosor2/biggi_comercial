ALTER PROCEDURE [dbo].[spi_bodega_tarjeta_existencia](@ve_cod_bodega				numeric
												,@ve_cod_producto			varchar(100)
												,@ve_fecha_inicio			datetime
												,@ve_fecha_termino			datetime)
AS
BEGIN
	DECLARE @TEMPO TABLE    
	   (ID							numeric identity
		,FECHA						datetime
		,FECHA_STR					varchar(100)
		,COD_DOC					numeric
		,TIPO_DOC					varchar(100)
		,REFERENCIA					varchar(100)
		,PRECIO_UNITARIO			numeric(10,2)
		,CANTIDAD_ENTRADA_UNIDADES	numeric(10,2)
		,CANTIDAD_SALIDA_UNIDADES	numeric(10,2)
		,CANTIDAD_STOCK_UNIDADES	numeric(10,2)
		,CANTIDAD_ENTRADA_VALORES	numeric(10,2)
		,CANTIDAD_SALIDA_VALORES	numeric(10,2)
		,CANTIDAD_SALDO_VALORES		numeric(10,2)
		,NOM_PRODUCTO				varchar(100)
		)
	
	-- saldo inicial
	INSERT INTO @TEMPO
	   (FECHA
		,FECHA_STR
		,COD_DOC
		,TIPO_DOC
		,REFERENCIA
		,PRECIO_UNITARIO --NEW
		,CANTIDAD_ENTRADA_UNIDADES
		,CANTIDAD_SALIDA_UNIDADES
		,CANTIDAD_STOCK_UNIDADES
		,CANTIDAD_ENTRADA_VALORES --NEW
		,CANTIDAD_SALIDA_VALORES --NEW
		,CANTIDAD_SALDO_VALORES --NEW
		)
	VALUES
		({ts '1900-01-01 00:00:00.000'}											--FECHA
		,NULL																	--FECHA_STR
		,NULL																	--COD_DOC
		,NULL																	--TIPO_DOC
		,'SALDO INICIAL'														--REFERENCIA
		,dbo.f_bodega_pmp(@ve_cod_producto, @ve_cod_bodega, @ve_fecha_inicio)	--PRECIO_UNITARIO
		,dbo.f_bodega_stock(@ve_cod_producto, @ve_cod_bodega, @ve_fecha_inicio)	--CANTIDAD_ENTRADA_UNIDADES
		,0																		--CANTIDAD_SALIDA_UNIDADES
		,NULL																	--CANTIDAD_STOCK_UNIDADES
		,dbo.f_bodega_stock(@ve_cod_producto, @ve_cod_bodega, @ve_fecha_inicio) * 
			dbo.f_bodega_pmp(@ve_cod_producto, @ve_cod_bodega, @ve_fecha_inicio) --CANTIDAD_ENTRADA_VALORES
		,0																		--CANTIDAD_SALIDA_VALORES
		,NULL																	--CANTIDAD_SALDO_VALORES
		)

	-- entradas
	INSERT INTO @TEMPO
	   (FECHA
		,FECHA_STR
		,COD_DOC
		,TIPO_DOC
		,REFERENCIA
		,PRECIO_UNITARIO
		,CANTIDAD_ENTRADA_UNIDADES
		,CANTIDAD_SALIDA_UNIDADES
		,CANTIDAD_STOCK_UNIDADES
		,CANTIDAD_ENTRADA_VALORES
		,CANTIDAD_SALIDA_VALORES
		,CANTIDAD_SALDO_VALORES
		,NOM_PRODUCTO
		)
	SELECT e.fecha_entrada_bodega
			,CONVERT(VARCHAR, e.fecha_entrada_bodega, 103)
			,dbo.f_get_nro_doc(e.tipo_doc, e.cod_doc)
			,e.tipo_doc
			,e.referencia
			,i.PRECIO
			,i.cantidad
			,0
			,NULL
			,i.cantidad * i.PRECIO
			,0
			,NULL
			,i.nom_producto
	FROM entrada_bodega e, item_entrada_bodega i
	WHERE e.fecha_entrada_bodega > @ve_fecha_inicio
	  AND e.fecha_entrada_bodega <= @ve_fecha_termino
	  AND e.cod_bodega = @ve_cod_bodega
	  AND i.cod_entrada_bodega = e.cod_entrada_bodega
	  AND i.cod_producto = @ve_cod_producto

	-- salidas
	INSERT INTO @TEMPO
	   (FECHA
		,FECHA_STR
		,COD_DOC
		,TIPO_DOC
		,REFERENCIA
		,PRECIO_UNITARIO
		,CANTIDAD_ENTRADA_UNIDADES
		,CANTIDAD_SALIDA_UNIDADES
		,CANTIDAD_STOCK_UNIDADES
		,CANTIDAD_ENTRADA_VALORES
		,CANTIDAD_SALIDA_VALORES
		,CANTIDAD_SALDO_VALORES
		,NOM_PRODUCTO
		)
	SELECT s.fecha_salida_bodega
		,CONVERT(VARCHAR, s.fecha_salida_bodega, 103)
		,dbo.f_get_nro_doc(s.tipo_doc, s.cod_doc)
		,case s.tipo_doc
			when 'FACTURA' then 'FV'
			else s.tipo_doc
		end 
		,s.referencia
		,dbo.f_bodega_pmp(i.cod_producto, s.cod_bodega,  s.fecha_salida_bodega)
		,0
		,i.cantidad
		,NULL
		,0
		,i.cantidad * dbo.f_bodega_pmp(i.cod_producto, s.cod_bodega,  s.fecha_salida_bodega)
		,NULL
		,i.nom_producto
	FROM salida_bodega s, item_salida_bodega i
	WHERE s.fecha_salida_bodega > @ve_fecha_inicio
	  AND s.fecha_salida_bodega <= @ve_fecha_termino
	  AND s.cod_bodega = @ve_cod_bodega
	  AND i.cod_salida_bodega = s.cod_salida_bodega
	  AND i.cod_producto = @ve_cod_producto

	DECLARE C_TEMPO CURSOR FOR
    SELECT ID							
			,CANTIDAD_ENTRADA_UNIDADES			
			,CANTIDAD_SALIDA_UNIDADES			
			,CANTIDAD_ENTRADA_VALORES
			,CANTIDAD_SALIDA_VALORES
    FROM @TEMPO
	ORDER BY FECHA
            
	DECLARE
		@vc_id							numeric
		,@vc_cantidad_entrada_unidades	numeric(10,2)
		,@vc_cantidad_salida_unidades	numeric(10,2)
		,@vl_stock_unidades				numeric(10,2)
		,@vl_stock_valores				numeric(10,2)
		,@vc_cantidad_entrada_valores	numeric(10,2)
		,@vc_cantidad_salida_valores	numeric(10,2)

	SET @vl_stock_unidades = 0
	set @vl_stock_valores = 0

	OPEN C_TEMPO
	FETCH C_TEMPO INTO @vc_id, @vc_cantidad_entrada_unidades, @vc_cantidad_salida_unidades,@vc_cantidad_entrada_valores,@vc_cantidad_salida_valores	

	WHILE @@FETCH_STATUS = 0 BEGIN
		SET @vl_stock_unidades = @vl_stock_unidades + @vc_cantidad_entrada_unidades - @vc_cantidad_salida_unidades
		set @vl_stock_valores = @vl_stock_valores + @vc_cantidad_entrada_valores - @vc_cantidad_salida_valores	
		
		UPDATE @TEMPO
		SET CANTIDAD_STOCK_UNIDADES = @vl_stock_unidades
			,CANTIDAD_SALDO_VALORES = @vl_stock_valores
		WHERE ID = @vc_id
	    
		FETCH C_TEMPO INTO @vc_id, @vc_cantidad_entrada_unidades, @vc_cantidad_salida_unidades,@vc_cantidad_entrada_valores,@vc_cantidad_salida_valores	
	END
	CLOSE C_TEMPO
	DEALLOCATE C_TEMPO

	SELECT	@ve_cod_producto	COD_PRODUCTO
			,CONVERT(VARCHAR, @ve_fecha_inicio, 103) FECHA_INICIO
			,CONVERT(VARCHAR, @ve_fecha_termino, 103) FECHA_TERMINO
			,FECHA_STR
			,COD_DOC
			,TIPO_DOC
			,REFERENCIA
			,PRECIO_UNITARIO
			,CANTIDAD_ENTRADA_UNIDADES
			,CANTIDAD_SALIDA_UNIDADES
			,CANTIDAD_STOCK_UNIDADES
			,CANTIDAD_ENTRADA_VALORES
			,CANTIDAD_SALIDA_VALORES
			,CANTIDAD_SALDO_VALORES
			,NOM_PRODUCTO
	FROM	@TEMPO
	ORDER BY FECHA
END