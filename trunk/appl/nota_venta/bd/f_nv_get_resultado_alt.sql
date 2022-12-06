------------------f_nv_get_resultado_alt----------------
CREATE FUNCTION f_nv_get_resultado_alt(@ve_cod_nota_venta numeric, @ve_formato varchar(25))
RETURNS NUMERIC
AS
BEGIN
	declare @res numeric,
			@result numeric

	IF(@ve_formato = 'COD_OP_DIR')BEGIN
		SELECT @result = COUNT(*)
		FROM ORDEN_PAGO
		WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
		AND COD_TIPO_ORDEN_PAGO = 1

		IF(@result = 0 OR @result > 1)BEGIN
			set @res = NULL
		END
		ELSE IF(@result = 1)BEGIN
			SELECT @res = COD_ORDEN_PAGO
			FROM ORDEN_PAGO
			WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
			AND COD_TIPO_ORDEN_PAGO = 1
		END
	END
	ELSE IF(@ve_formato = 'OP_CT_DIR')BEGIN
		SELECT @result = COUNT(*)
		FROM ORDEN_PAGO
		WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
		AND COD_TIPO_ORDEN_PAGO = 1

		IF(@result = 0)
			SET @res = NULL
		ELSE
			SET @res = @result
	END
	ELSE IF(@ve_formato = 'MONTO_OP_DIR')BEGIN
		SELECT @res = SUM(TOTAL_NETO)
		FROM ORDEN_PAGO
		WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
		AND COD_TIPO_ORDEN_PAGO = 1

		IF(@result = 0)BEGIN
			set @res = NULL
		END
	END
	IF(@ve_formato = 'COD_OP_V1')BEGIN
		SELECT @result = COUNT(*)
		FROM ORDEN_PAGO
		WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
		AND COD_TIPO_ORDEN_PAGO IN (1, 5)

		IF(@result = 0 OR @result > 1)BEGIN
			set @res = NULL
		END
		ELSE IF(@result = 1)BEGIN
			SELECT @res = COD_ORDEN_PAGO
			FROM ORDEN_PAGO
			WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
			AND COD_TIPO_ORDEN_PAGO IN (1, 5)
		END
	END
	ELSE IF(@ve_formato = 'OP_CT_V1')BEGIN
		SELECT @result = COUNT(*)
		FROM ORDEN_PAGO
		WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
		AND COD_TIPO_ORDEN_PAGO IN (1, 5)

		IF(@result = 0)
			SET @res = NULL
		ELSE
			SET @res = @result
	END
	ELSE IF(@ve_formato = 'MONTO_OP_V1')BEGIN
		SELECT @res = SUM(TOTAL_NETO)
		FROM ORDEN_PAGO
		WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
		AND COD_TIPO_ORDEN_PAGO IN (1, 5)

		IF(@result = 0)BEGIN
			set @res = NULL
		END
	END
	IF(@ve_formato = 'COD_OP_ADM')BEGIN
		SELECT @result = COUNT(*)
		FROM ORDEN_PAGO
		WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
		AND COD_TIPO_ORDEN_PAGO = 4

		IF(@result = 0 OR @result > 1)BEGIN
			set @res = NULL
		END
		ELSE IF(@result = 1)BEGIN
			SELECT @res = COD_ORDEN_PAGO
			FROM ORDEN_PAGO
			WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
			AND COD_TIPO_ORDEN_PAGO = 4
		END
	END
	ELSE IF(@ve_formato = 'OP_CT_ADM')BEGIN
		SELECT @result = COUNT(*)
		FROM ORDEN_PAGO
		WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
		AND COD_TIPO_ORDEN_PAGO = 4

		IF(@result = 0)
			SET @res = NULL
		ELSE
			SET @res = @result
	END
	ELSE IF(@ve_formato = 'MONTO_OP_ADM')BEGIN
		SELECT @res = SUM(TOTAL_NETO)
		FROM ORDEN_PAGO
		WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
		AND COD_TIPO_ORDEN_PAGO = 4

		IF(@result = 0)BEGIN
			set @res = NULL
		END
	END
		
return @res;
END