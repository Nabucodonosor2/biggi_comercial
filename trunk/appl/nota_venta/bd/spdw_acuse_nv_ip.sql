-------------------- spu_acuse_nv_ip ----------------------------------
CREATE PROCEDURE spdw_acuse_nv_ip
AS
BEGIN
	-- tabla temporal
	DECLARE @TEMPO TABLE  
		(COD_NOTA_VENTA	numeric)

	DECLARE
		@vc_cod_nota_venta	numeric,
		@vl_count_uno		numeric,
		@vl_count_dos		numeric

	DECLARE C_NOTA_VENTA CURSOR FOR  
	SELECT COD_NOTA_VENTA
	FROM NOTA_VENTA
	WHERE COD_ESTADO_NOTA_VENTA = 4	-- CONFIRMADA
	AND COD_FORMA_PAGO in (2, 16, 18, 19, 20, 21, 22)
	AND COD_NOTA_VENTA > 108850

	OPEN C_NOTA_VENTA
	FETCH C_NOTA_VENTA INTO @vc_cod_nota_venta
	WHILE @@FETCH_STATUS = 0
	BEGIN
		
		--Revisa si esta asociado a una nota_venta cuyo ingreso pago este confirmada
		SELECT @vl_count_uno = COUNT(*)
		FROM INGRESO_PAGO IPA
			,INGRESO_PAGO_FACTURA IPF
		WHERE IPA.COD_INGRESO_PAGO = IPF.COD_INGRESO_PAGO
		AND IPA.COD_ESTADO_INGRESO_PAGO = 2 --CONFIRMADA
		AND TIPO_DOC = 'NOTA_VENTA'
		AND COD_DOC = @vc_cod_nota_venta

		if(@vl_count_uno = 0)BEGIN
			--Revisa si esta asociado a una factura cuyo ingreso pago este confirmada
			SELECT @vl_count_dos = COUNT(*)
			FROM INGRESO_PAGO IPA
				,INGRESO_PAGO_FACTURA IPF
				,FACTURA F
			WHERE IPA.COD_INGRESO_PAGO = IPF.COD_INGRESO_PAGO
			AND IPF.COD_DOC = F.COD_FACTURA
			AND IPF.TIPO_DOC = 'FACTURA'
			AND IPA.COD_ESTADO_INGRESO_PAGO = 2 --CONFIRMADA
			AND F.COD_DOC = @vc_cod_nota_venta

			if(@vl_count_dos = 0)BEGIN
				INSERT INTO @TEMPO values (@vc_cod_nota_venta)
			END
		END
		
		FETCH C_NOTA_VENTA INTO @vc_cod_nota_venta
	END
	CLOSE C_NOTA_VENTA
	DEALLOCATE C_NOTA_VENTA

	SELECT COD_NOTA_VENTA
	FROM @TEMPO
END