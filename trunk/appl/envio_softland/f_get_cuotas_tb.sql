ALTER FUNCTION f_get_cuotas_tb(@nro_autoriza_tb			NUMERIC
							   ,@ve_cod_ingreso_pago	NUMERIC
							   ,@ve_cod_tipo_doc_pago	NUMERIC)
RETURNS VARCHAR(100)
AS
BEGIN
	DECLARE
		@vl_tipo_doc_pago		NUMERIC
		,@vl_nro_cuotas_tbk		NUMERIC
		,@vl_count_cuotas_tbk	NUMERIC
		,@vl_retun				VARCHAR(100)

	IF(@ve_cod_tipo_doc_pago = 6)BEGIN
		SELECT @vl_nro_cuotas_tbk = NRO_CUOTAS_TBK
		FROM DOC_INGRESO_PAGO
		WHERE NRO_DOC = @nro_autoriza_tb
		AND COD_INGRESO_PAGO = @ve_cod_ingreso_pago
		AND COD_TIPO_DOC_PAGO = 6

		SELECT @vl_count_cuotas_tbk = COUNT(*) + 1
		FROM ENVIO_TRANSBANK ET
			,ENVIO_SOFTLAND ES
		WHERE COD_INGRESO_PAGO = @ve_cod_ingreso_pago
		AND NRO_AUTORIZA_TB = @nro_autoriza_tb
		AND COD_TIPO_DOC_PAGO = 6
		AND ES.COD_ESTADO_ENVIO = 2
		AND ET.COD_ENVIO_SOFTLAND = ES.COD_ENVIO_SOFTLAND

		SET @vl_retun = 'Cuota '+CAST(@vl_count_cuotas_tbk AS VARCHAR) +'/'+ CAST(@vl_nro_cuotas_tbk AS VARCHAR)
	END 
	ELSE BEGIN
		set @vl_retun =  'n/a'
	END
	
	RETURN @vl_retun
END