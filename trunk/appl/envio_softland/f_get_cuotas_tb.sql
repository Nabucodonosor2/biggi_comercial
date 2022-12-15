ALTER FUNCTION [dbo].[f_get_cuotas_tb](@nro_autoriza_tb		numeric
									)
RETURNS varchar(100)
AS
BEGIN
	declare
		@vl_tipo_doc_pago	numeric
		,@vl_nro_cuotas_tbk	numeric
		,@vl_count_cuotas_tbk	numeric
		,@vl_retun varchar (100)

	Select @vl_tipo_doc_pago = COD_TIPO_DOC_PAGO from DOC_INGRESO_PAGO
	where COD_TIPO_DOC_PAGO in (5,6)
	and NRO_DOC = @nro_autoriza_tb
	if (@vl_tipo_doc_pago = 6)
	begin
			select @vl_nro_cuotas_tbk = NRO_CUOTAS_TBK from DOC_INGRESO_PAGO
			where NRO_DOC = @nro_autoriza_tb

			/*select @vl_count_cuotas_tbk = count(NRO_AUTORIZA_TB)+1 from envio_transbank
			where NRO_AUTORIZA_TB = @nro_autoriza_tb*/

			select @vl_count_cuotas_tbk = COUNT(NRO_AUTORIZA_TB)
            from ENVIO_TRANSBANK ET
                ,ENVIO_SOFTLAND ES
            where NRO_AUTORIZA_TB = @nro_autoriza_tb
            and COD_ESTADO_ENVIO in (1, 2)
            and ET.COD_ENVIO_SOFTLAND = ES.COD_ENVIO_SOFTLAND

			select @vl_count_cuotas_tbk = SUM(@vl_count_cuotas_tbk +1)
			set @vl_retun = 'Cuota '+cast(@vl_count_cuotas_tbk as varchar) +'/'+ cast(@vl_nro_cuotas_tbk as varchar)
		end 
	else begin
		
		set @vl_retun =  'n/a'
	end
	
	return @vl_retun
END