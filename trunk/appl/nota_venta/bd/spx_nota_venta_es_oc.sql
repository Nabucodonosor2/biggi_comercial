CREATE PROCEDURE dbo.spx_nota_venta_es_oc
AS
BEGIN
	DECLARE
		@vc_cod_nota_venta		numeric,
		@vl_cod_nota_venta_docs	numeric

	DECLARE C_ITEM CURSOR FOR
	SELECT COD_NOTA_VENTA
	FROM NOTA_VENTA_DOCS
	WHERE ES_OC = 'S' 
	GROUP BY COD_NOTA_VENTA 
	HAVING COUNT(*) > 1
	
	OPEN C_ITEM
	FETCH C_ITEM INTO @vc_cod_nota_venta			
	WHILE @@FETCH_STATUS = 0 BEGIN
		
		UPDATE NOTA_VENTA_DOCS
		SET ES_OC = 'N'
		WHERE COD_NOTA_VENTA = @vc_cod_nota_venta
		
		SELECT TOP 1 @vl_cod_nota_venta_docs = COD_NOTA_VENTA_DOCS
		FROM NOTA_VENTA_DOCS
		WHERE COD_NOTA_VENTA = @vc_cod_nota_venta
		ORDER BY FECHA_REGISTRO DESC
		
		UPDATE NOTA_VENTA_DOCS
		SET ES_OC = 'S'
		WHERE COD_NOTA_VENTA_DOCS = @vl_cod_nota_venta_docs
		
		FETCH C_ITEM INTO @vc_cod_nota_venta	
	END
	CLOSE C_ITEM
	DEALLOCATE C_ITEM
END