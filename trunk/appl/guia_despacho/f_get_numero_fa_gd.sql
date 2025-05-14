CREATE FUNCTION f_get_numero_fa_gd(@ve_cod_guia_despacho NUMERIC)
RETURNS VARCHAR(8000)
AS
BEGIN
	DECLARE @vc_cod_factura	NUMERIC
		   ,@vl_estructura	VARCHAR(8000)
	
	DECLARE C_CURSOR CURSOR FOR 
	SELECT COD_FACTURA
	FROM FACTURA_GUIA_DESPACHO
	WHERE COD_GUIA_DESPACHO = @ve_cod_guia_despacho
	
	SET @vl_estructura = NULL

	OPEN C_CURSOR 
	FETCH C_CURSOR INTO @vc_cod_factura
	WHILE @@fetch_status = 0 BEGIN
		
		SET @vl_estructura = @vl_estructura + CONVERT(VARCHAR, @vc_cod_factura) + '|'

	FETCH C_CURSOR INTO @vc_cod_factura
	END
	CLOSE C_CURSOR
	DEALLOCATE C_CURSOR

	IF(@vl_estructura <> '')
		SET @vl_estructura = SUBSTRING(@vl_estructura, 1, len(@vl_estructura) - 1)

	RETURN @vl_estructura
END