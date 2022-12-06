------------------f_nv_get_resultado----------------
ALTER FUNCTION [dbo].[f_nv_get_resultado](@ve_cod_nota_venta numeric, @ve_formato varchar(25))
RETURNS numeric (14,2)
AS
BEGIN
	--si la NV no es una de las que debe procesar a la antigua, llama la funcion antigua f_nv_get_resultado
	if (@ve_cod_nota_venta in (88860,88196,88194,87985,87918,87397,87198,87134,85388,85335,85334,85126,84698,84086,82126))
		return dbo.f_nv_get_resultado_old(@ve_cod_nota_venta, @ve_formato)

	declare @res T_PRECIO,
			@k_estado_oc_anulada numeric,
			@porc_dscto_corporativo T_PORCENTAJE

	set @k_estado_oc_anulada = 2
	
	if (@ve_formato = 'PORC_DSCTO_TOTAL')
		SELECT @res = (((MONTO_DSCTO1 + MONTO_DSCTO2)/SUBTOTAL)) *100
		FROM NOTA_VENTA 
		WHERE cod_nota_venta = @ve_cod_nota_venta
	
	else if (@ve_formato = 'MONTO_DSCTO_TOTAL')
		SELECT @res = MONTO_DSCTO1 + MONTO_DSCTO2
		FROM NOTA_VENTA
		WHERE cod_nota_venta = @ve_cod_nota_venta
	
	else if (@ve_formato = 'TOTAL_NETO')
		SELECT @res = TOTAL_NETO	
		FROM NOTA_VENTA 
		WHERE cod_nota_venta = @ve_cod_nota_venta
		
	else if (@ve_formato = 'PORC_DSCTO_CORPORATIVO')
		SELECT @res = dbo.f_get_porc_dscto_corporativo_empresa(COD_EMPRESA, FECHA_NOTA_VENTA)/100
		FROM NOTA_VENTA
		WHERE cod_nota_venta = @ve_cod_nota_venta
	
	else if (@ve_formato = 'MONTO_DSCTO_CORPORATIVO')begin
		SELECT @porc_dscto_corporativo = porc_dscto_corporativo 
		FROM NOTA_VENTA
		WHERE cod_nota_venta = @ve_cod_nota_venta 
			
		if(@porc_dscto_corporativo = 0.00)	
			set @res = 0
		else
			SELECT @res = (TOTAL_NETO - dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'SUMA_NC')) * porc_dscto_corporativo / 100
			FROM NOTA_VENTA
			WHERE cod_nota_venta = @ve_cod_nota_venta 
	end
	else if (@ve_formato = 'VENTA_NETA')
		SELECT @res = (dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'TOTAL_NETO')) - 
						(dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO'))
		FROM NOTA_VENTA
		WHERE cod_nota_venta = @ve_cod_nota_venta

	else if (@ve_formato = 'MONTO_GASTO_FIJO')
		SELECT @res = (dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'VENTA_NETA_FINAL') * dbo.f_get_parametro_porc('GF', FECHA_NOTA_VENTA)/100)
		FROM NOTA_VENTA
		WHERE cod_nota_venta = @ve_cod_nota_venta

	else if (@ve_formato = 'VENTA_NETA_FINAL')
		SELECT @res = TOTAL_NETO - dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'SUMA_NC') -
			dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO') 
		FROM NOTA_VENTA
		WHERE cod_nota_venta = @ve_cod_nota_venta

	else if (@ve_formato = 'SUM_OC_TOTAL')BEGIN
		SELECT @res = sum(TOTAL_NETO)
		FROM  ORDEN_COMPRA
		WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
		AND COD_ESTADO_ORDEN_COMPRA <> @k_estado_oc_anulada
		AND TIPO_ORDEN_COMPRA = 'NOTA_VENTA';
	END
	else if (@ve_formato = 'SUM_OC_TOTAL_NETA')BEGIN
		declare @rebaja			numeric,
				@sum_total_neto	numeric
				
		SELECT @sum_total_neto = sum(TOTAL_NETO)
		FROM  ORDEN_COMPRA
		WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
		AND COD_ESTADO_ORDEN_COMPRA <> @k_estado_oc_anulada
		AND TIPO_ORDEN_COMPRA = 'NOTA_VENTA';

		SELECT @rebaja = ISNULL(REBAJA, 0)
		FROM NOTA_VENTA
		WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
		
		set @res = @sum_total_neto - @rebaja
	END
	else if (@ve_formato = 'RESULTADO')
		SELECT @res = TOTAL_NETO -
					dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'SUM_OC_TOTAL_NETA') - 
					dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'MONTO_GASTO_FIJO') -	
					dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO') -
					dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'SUMA_NC')
		FROM  NOTA_VENTA
		WHERE cod_nota_venta = @ve_cod_nota_venta 

	else if (@ve_formato = 'PORC_RESULTADO')
		SELECT @res = (dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'RESULTADO') /
						dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'VENTA_NETA_FINAL')) * 100
		FROM  NOTA_VENTA
		WHERE cod_nota_venta = @ve_cod_nota_venta

	else if (@ve_formato = 'MONTO_DIRECTORIO')
		SELECT @res = (dbo.f_get_parametro_porc('AA', FECHA_NOTA_VENTA)/100) * 
					dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'RESULTADO')

		FROM NOTA_VENTA
		WHERE cod_nota_venta = @ve_cod_nota_venta
	
	else if (@ve_formato = 'COMISION_V1')
		SELECT @res = (PORC_VENDEDOR1/100)*
					dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'RESULTADO')
		FROM  NOTA_VENTA
		WHERE cod_nota_venta = @ve_cod_nota_venta 
	
	else if (@ve_formato = 'COMISION_V2')
		SELECT @res = (PORC_VENDEDOR2/100)*
						dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'RESULTADO')
		FROM  NOTA_VENTA
		WHERE cod_nota_venta = @ve_cod_nota_venta 
		
	else if (@ve_formato = 'COMISION_GV')
		SELECT @res = (dbo.f_get_parametro_porc('GV', FECHA_NOTA_VENTA)/100) * 
					dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'RESULTADO') 
					
		FROM  NOTA_VENTA
		WHERE cod_nota_venta = @ve_cod_nota_venta 

	else if (@ve_formato = 'COMISION_ADM')
		SELECT @res = (dbo.f_get_parametro_porc('ADM', FECHA_NOTA_VENTA)/100) * 
					dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'RESULTADO') 
					
		FROM  NOTA_VENTA
		WHERE cod_nota_venta = @ve_cod_nota_venta 

	else if (@ve_formato = 'REMANENTE')
		SELECT @res = dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'RESULTADO') -
						dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'MONTO_DIRECTORIO') -
						dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'COMISION_V1') - 
						dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'COMISION_V2') -
						dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'COMISION_GV') -
						dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'COMISION_ADM')
		FROM  NOTA_VENTA
		WHERE cod_nota_venta = @ve_cod_nota_venta

	else if (@ve_formato = 'PAGO_DIRECTORIO' or
				@ve_formato = 'PAGO_GV' or
				@ve_formato = 'PAGO_ADM' or
				@ve_formato = 'PAGO_VENDEDOR') BEGIN

		declare
			@vl_cod_tipo_orden_pago		numeric

			
		if (@ve_formato = 'PAGO_DIRECTORIO')
			set @vl_cod_tipo_orden_pago = 3
		else if (@ve_formato = 'PAGO_GV')
			set @vl_cod_tipo_orden_pago = 2
		else if (@ve_formato = 'PAGO_ADM')
			set @vl_cod_tipo_orden_pago = 4
		else if (@ve_formato = 'PAGO_VENDEDOR')
			set @vl_cod_tipo_orden_pago = 1			

		
		-- pagos
		select @res = isnull(sum(dbo.f_op_pago(cod_orden_pago)), 0)
		from orden_pago
		where cod_nota_venta = @ve_cod_nota_venta 
			and cod_tipo_orden_pago = @vl_cod_tipo_orden_pago
	END

	else if (@ve_formato = 'BACKCHARGE') 
		SELECT @res = sum(TOTAL_NETO)
		FROM  ORDEN_COMPRA
		WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
		AND COD_ESTADO_ORDEN_COMPRA <> @k_estado_oc_anulada
		AND TIPO_ORDEN_COMPRA = 'BACKCHARGE';
	else if (@ve_formato = 'RESULTADO_BACKCHARGE')
		SELECT @res = dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'TOTAL_NETO') -
					dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'SUM_OC_TOTAL') - 
					dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'BACKCHARGE') - 
					dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'MONTO_GASTO_FIJO') -	
					dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO')		
		FROM  NOTA_VENTA
		WHERE cod_nota_venta = @ve_cod_nota_venta 
	else if (@ve_formato = 'COMISION_V1_BACKCHARGE')
		SELECT @res = (PORC_VENDEDOR1/100)*
					dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'RESULTADO_BACKCHARGE')
		FROM  NOTA_VENTA
		WHERE cod_nota_venta = @ve_cod_nota_venta
	else if (@ve_formato = 'SUMA_NC')begin
		SELECT @res = SUM(TOTAL_CONSIDERADO)
		FROM NOTA_VENTA_NC
		WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
		AND SELECCION = 'S'
	end 
		
return isnull (round(@res, 0), 0);
END