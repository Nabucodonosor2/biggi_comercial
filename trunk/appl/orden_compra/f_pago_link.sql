CREATE FUNCTION [dbo].[f_pago_link] (@ve_cod_orden_compra	NUMERIC
								   ,@ve_cod_faprov			NUMERIC)
RETURNS VARCHAR(8000) 
AS
BEGIN
DECLARE  @vc_cod_pago_faprov	VARCHAR(100)
		,@vl_string_link		VARCHAR(8000)
		
	DECLARE C_PAGOS CURSOR FOR	
	SELECT PF.COD_PAGO_FAPROV
	FROM ORDEN_COMPRA O
		 ,FAPROV F left outer join PAGO_FAPROV_FAPROV PFF on F.COD_FAPROV = PFF.COD_FAPROV
				   left outer join PAGO_FAPROV PF on PFF.COD_PAGO_FAPROV = PF.COD_PAGO_FAPROV
		 ,ITEM_FAPROV I
	WHERE O.COD_ORDEN_COMPRA = @ve_cod_orden_compra
	AND F.ORIGEN_FAPROV = 'ORDEN_COMPRA'
	AND F.COD_ESTADO_FAPROV = 2	--CONFIRMADA
	AND I.COD_FAPROV = F.COD_FAPROV
	AND I.COD_FAPROV = @ve_cod_faprov
	AND PF.COD_ESTADO_PAGO_FAPROV = 2
	AND I.COD_DOC = O.COD_ORDEN_COMPRA
	
	
	set @vl_string_link = ''
	
	OPEN	C_PAGOS
	FETCH	C_PAGOS INTO @vc_cod_pago_faprov
	WHILE	@@FETCH_STATUS = 0 BEGIN		
		
		SET @vl_string_link = @vl_string_link + CONVERT(VARCHAR,@vc_cod_pago_faprov)+  '|'
		
	FETCH C_PAGOS INTO @vc_cod_pago_faprov
	END

	CLOSE C_PAGOS
	DEALLOCATE C_PAGOS
	
RETURN @vl_string_link
END