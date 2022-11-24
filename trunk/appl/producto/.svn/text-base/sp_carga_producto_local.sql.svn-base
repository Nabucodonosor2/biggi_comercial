alter PROCEDURE  sp_carga_producto_local
AS
BEGIN 
	DECLARE @vc_cod_producto varchar(100)
			,@vc_es_compuesto varchar(1)
			,@vl_count numeric
			,@vl_contador numeric

	DECLARE C_CARGA CURSOR FOR 
		
	SELECT COD_PRODUCTO 
		  ,ES_COMPUESTO	
	FROM PRODUCTO
		
	OPEN C_CARGA
	FETCH C_CARGA INTO @vc_cod_producto, @vc_es_compuesto
	WHILE @@FETCH_STATUS = 0 BEGIN
	
		SELECT @vl_count = COUNT(COD_PRODUCTO)
		FROM PRODUCTO_LOCAL
		WHERE COD_PRODUCTO =  @vc_cod_producto 		
		
	IF(@vl_count = 0)BEGIN
	
		insert into producto_local values (@vc_cod_producto,@vc_es_compuesto)

	END 	
	
	FETCH C_CARGA INTO @vc_cod_producto, @vc_es_compuesto
	END
	CLOSE C_CARGA
	DEALLOCATE C_CARGA
			
END