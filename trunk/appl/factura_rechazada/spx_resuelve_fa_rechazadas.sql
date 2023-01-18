ALTER PROCEDURE spx_resuelve_fa_rechazadas
AS
BEGIN
	DECLARE
		@vc_cod_factura_rechazada	numeric,
		@vc_nro_nota_credito		numeric,
		@vc_nro_re_factura			numeric,
		@vc_cod_nota_venta			numeric,
		@vc_cod_factura				numeric,
		@vc_total_con_iva			numeric,
		@vl_total_con_iva			numeric
	
	DECLARE C_CURSOR CURSOR FOR	
	SELECT COD_FACTURA_RECHAZADA
		  ,dbo.f_get_nc_from_fa(F.COD_FACTURA) NRO_NOTA_CREDITO
		  ,dbo.f_get_reFA(F.COD_FACTURA, F.COD_DOC) NRO_RE_FACTURA
		  ,F.COD_DOC
		  ,F.COD_FACTURA
		  ,TOTAL_CON_IVA
	FROM FACTURA_RECHAZADA FR LEFT OUTER JOIN USUARIO U ON U.COD_USUARIO = FR.COD_USUARIO_RESUELTA
		,FACTURA F
		,USUARIO UV1
	WHERE RESUELTA = 'N'
	AND FR.COD_FACTURA = F.COD_FACTURA
	AND UV1.COD_USUARIO = F.COD_USUARIO_VENDEDOR1
	ORDER BY COD_FACTURA_RECHAZADA DESC
	
	OPEN C_CURSOR 
	FETCH C_CURSOR INTO @vc_cod_factura_rechazada, @vc_nro_nota_credito, @vc_nro_re_factura, @vc_cod_nota_venta, @vc_cod_factura, @vc_total_con_iva
	WHILE @@FETCH_STATUS = 0
	BEGIN
		IF(@vc_nro_nota_credito IS NOT NULL AND @vc_nro_re_factura IS NOT NULL)BEGIN
			
			SELECT top 1 @vl_total_con_iva = TOTAL_CON_IVA
			FROM FACTURA
			WHERE COD_DOC = @vc_cod_nota_venta
			and  COD_ESTADO_DOC_SII in (2, 3)
			and COD_FACTURA <> @vc_cod_factura
			and TOTAL_CON_IVA = @vc_total_con_iva
			ORDER BY FECHA_FACTURA ASC

			IF(@vc_total_con_iva = @vl_total_con_iva)BEGIN
				UPDATE FACTURA_RECHAZADA
				SET RESUELTA = 'S'
				WHERE COD_FACTURA_RECHAZADA = @vc_cod_factura_rechazada
			END

		END
		FETCH C_CURSOR INTO @vc_cod_factura_rechazada, @vc_nro_nota_credito, @vc_nro_re_factura, @vc_cod_nota_venta, @vc_cod_factura, @vc_total_con_iva
	END
	CLOSE C_CURSOR
	DEALLOCATE C_CURSOR
END