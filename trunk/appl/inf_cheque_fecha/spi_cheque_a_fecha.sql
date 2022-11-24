--------------- spi_cheque_a_fecha --------------
ALTER PROCEDURE spi_cheque_a_fecha(@ve_fecha	datetime
									,@ve_cod_usuario numeric)
/*
exec  spi_cheque_a_fecha {ts '2014-01-21 00:00:00.000'}
*/
AS
BEGIN
	
	declare
	@vl_fecha_actual		datetime

	set @vl_fecha_actual = getdate()
	
	delete INF_CHEQUE_FECHA
	where cod_usuario = @ve_cod_usuario
	
	insert into INF_CHEQUE_FECHA
		(FECHA_INF_CHEQUE_FECHA
		,COD_USUARIO
		,COD_NOTA_VENTA			
		,NOM_EMPRESA			
		,REFERENCIA				
		,COD_INGRESO_PAGO		
		,FECHA_DOC				
		,NRO_DOC				
		,MONTO_DOC				
		)
	select @vl_fecha_actual
			,@ve_cod_usuario 
			,null					--COD_NOTA_VENTA
			,e.NOM_EMPRESA			--NOM_EMPRESA			
			,null					--REFERENCIA
			,ip.COD_INGRESO_PAGO	--COD_INGRESO_PAGO
			,CONVERT(varchar, dip.FECHA_DOC, 103) --FECHA_DOC
			,dip.NRO_DOC			--NRO_DOC
			,dip.MONTO_DOC			--MONTO_DOC
	from doc_ingreso_pago dip, INGRESO_PAGO ip, EMPRESA e
	where dip.COD_TIPO_DOC_PAGO = 2	--cheque
	and dip.FECHA_DOC >= @ve_fecha
	and ip.COD_INGRESO_PAGO = dip.COD_INGRESO_PAGO
	and ip.COD_ESTADO_INGRESO_PAGO = 2	--confirmado
	and e.COD_EMPRESA = ip.COD_EMPRESA

	declare C_TEMP INSENSITIVE  cursor for
	select COD_INGRESO_PAGO
	from INF_CHEQUE_FECHA
	
	declare
		@vc_cod_ingreso_pago		numeric
		,@vc_cod_nota_venta			numeric
		,@vl_NVs					varchar(100)
		,@vl_referencia				varchar(100)

	OPEN C_TEMP
	FETCH C_TEMP INTO @vc_cod_ingreso_pago
	WHILE @@FETCH_STATUS = 0 BEGIN
		set @vl_NVs = ''
		set @vl_referencia = ''
		--pagos a FA
		declare C_NV_FA INSENSITIVE  cursor for
		select f.COD_DOC	
		from ingreso_pago_factura ipf, FACTURA f
		where ipf.COD_INGRESO_PAGO = @vc_cod_ingreso_pago
		  and ipf.TIPO_DOC = 'FACTURA'
		  and f.COD_FACTURA = ipf.COD_DOC
		  and f.COD_TIPO_FACTURA = 1	--venta (desde NV)
		  
		OPEN C_NV_FA
		FETCH C_NV_FA INTO @vc_cod_nota_venta
		WHILE @@FETCH_STATUS = 0 BEGIN
			set @vl_NVs = @vl_NVs + CONVERT(varchar, @vc_cod_nota_venta) + '-'
			if (@vl_referencia = '')
				select @vl_referencia = REFERENCIA
				from NOTA_VENTA
				where COD_NOTA_VENTA = @vc_cod_nota_venta
			else
				set @vl_referencia = 'VARIAS NV'
				
			FETCH C_NV_FA INTO @vc_cod_nota_venta
		END
		CLOSE C_NV_FA
		DEALLOCATE C_NV_FA
	
		--pagos a NV
		declare C_NV INSENSITIVE  cursor for
		select n.cod_nota_venta
		from ingreso_pago_factura ipf, nota_venta n
		where ipf.COD_INGRESO_PAGO = @vc_cod_ingreso_pago
		  and ipf.TIPO_DOC = 'NOTA_VENTA'
		  and n.COD_nota_venta = ipf.COD_DOC
		  
		OPEN C_NV
		FETCH C_NV INTO @vc_cod_nota_venta
		WHILE @@FETCH_STATUS = 0 BEGIN
			set @vl_NVs = @vl_NVs + CONVERT(varchar, @vc_cod_nota_venta) + '-'
			if (@vl_referencia = '')
				select @vl_referencia = REFERENCIA
				from NOTA_VENTA
				where COD_NOTA_VENTA = @vc_cod_nota_venta
			else
				set @vl_referencia = 'VARIAS NV'

			FETCH C_NV INTO @vc_cod_nota_venta
		END
		CLOSE C_NV
		DEALLOCATE C_NV
		
		if (@vl_NVs <> '')
			set @vl_NVs = LEFT(@vl_NVs, len(@vl_NVs) - 1)	--borra el ultimo "-"
			
		update INF_CHEQUE_FECHA
		set COD_NOTA_VENTA = @vl_NVs
			,REFERENCIA = @vl_referencia
		where cod_ingreso_pago = @vc_cod_ingreso_pago
	
		FETCH C_TEMP INTO @vc_cod_ingreso_pago
	END
	CLOSE C_TEMP
	DEALLOCATE C_TEMP
	
	select * from INF_CHEQUE_FECHA
END