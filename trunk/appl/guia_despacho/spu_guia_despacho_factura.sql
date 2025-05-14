CREATE PROCEDURE spu_guia_despacho_factura(@ve_cod_guia_despacho	NUMERIC
										  ,@ve_cod_facturas			VARCHAR(8000))
AS
BEGIN
	DECLARE @vc_cod_factura	NUMERIC

	DELETE FACTURA_GUIA_DESPACHO
	WHERE COD_GUIA_DESPACHO = @ve_cod_guia_despacho

	DECLARE C_CURSOR CURSOR FOR 
	SELECT item
	FROM dbo.f_split(@ve_cod_facturas, '|')
	
	OPEN C_CURSOR 
	FETCH C_CURSOR INTO @vc_cod_factura
	WHILE @@fetch_status = 0 BEGIN
		
		INSERT INTO FACTURA_GUIA_DESPACHO (COD_FACTURA, COD_GUIA_DESPACHO) VALUES (@vc_cod_factura, @ve_cod_guia_despacho)

	FETCH C_CURSOR INTO @vc_cod_factura
	END
	CLOSE C_CURSOR
	DEALLOCATE C_CURSOR
END