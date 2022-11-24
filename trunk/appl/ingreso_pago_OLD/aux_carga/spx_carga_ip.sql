CREATE PROCEDURE [dbo].[spx_carga_ip]
AS
BEGIN  
	declare @cod_ip				numeric
			,@fecha				varchar(10)
			,@count_fa			numeric
			,@cod_usuario		varchar(15)
			,@rut				numeric
			,@dig_verif			varchar(1)
			,@cod_empresa		numeric
			,@cod_usuario_ip	numeric
			,@cod_ingreso_pago	numeric
			,@cod_tipo_doc		numeric
			,@TIPO_DOC_PAGO		numeric
			,@cod_estado_ip_confirma numeric
			,@cod_usuario_confirma numeric
	
	--ojo se cae este select por RUT_CLIENTE, debe ser porque en algun registro queda con varchar y no puede convertir a numeric
	declare c_aux_ip cursor for 
	select aip.cod_ingreso_pago
			,aip.fecha_reg
			,aip.cod_usuario
			,convert(numeric, substring(RUT_CLIENTE, 1, len(RUT_CLIENTE) - 1)) --rut
			,substring(RUT_CLIENTE, len(RUT_CLIENTE), 1) --dig_verif
	from aux_ingreso_pago aip, ingreso_pago ip
	where aip.cod_ingreso_pago not in (select cod_ingreso_pago from ingreso_pago)
		
	open c_aux_ip
	fetch c_aux_ip into @cod_ip, @fecha, @cod_usuario, @rut, @dig_verif
	WHILE @@FETCH_STATUS = 0 BEGIN
		
		--busca que las asig_pago_fac del ingreso de pago tenga factura en el nuevo sistema		
		select @count_fa = count(*) from factura f, aux_asig_pago_fact apf 
		where f.nro_factura = apf.NRO_FACT and 
			apf.cod_ingreso_pago = @cod_ip
		/*	
		SELECT * FROM aux_asig_pago_fact
		WHERE NRO_FACT IN (SELECT NRO_FACTURA FROM FACTURA)
		*/

		if(@count_fa <> 0)begin
			-- obtiene datos de empresa
			set @cod_empresa = null
			set @cod_usuario_ip = null
			set @cod_estado_ip_confirma = 2
			set @cod_usuario_confirma = 1

			select @cod_empresa = cod_empresa
			from empresa where rut = @rut

			if (@cod_usuario='D' or @cod_usuario='D2' or @cod_usuario is null)
				set @cod_usuario_ip = 1
			else
				select @cod_usuario_ip = cod_usuario from usuario where ini_usuario=@cod_usuario

			insert into INGRESO_PAGO
			   (FECHA_INGRESO_PAGO
			   ,COD_USUARIO
			   ,COD_EMPRESA
			   ,REFERENCIA
			   ,OTRO_INGRESO
			   ,OTRO_GASTO
			   ,COD_ESTADO_INGRESO_PAGO
			   ,COD_USUARIO_ANULA
			   ,FECHA_ANULA
			   ,MOTIVO_ANULA
			   ,OTRO_ANTICIPO
			   ,COD_USUARIO_CONFIRMA
			   ,FECHA_CONFIRMA)
			select @fecha		--FECHA_INGRESO_PAGO
				,isnull(@cod_usuario_ip, 1)	--COD_USUARIO
				,@cod_empresa
				,null			--REFERENCIA	
				,0				--OTRO_INGRESO
				,0				--OTRO_GASTO
				,@cod_estado_ip_confirma	--COD_ESTADO_INGRESO_PAGO = CONFIRMADA
				,null			--COD_USUARIO_ANULA
				,null			--FECHA_ANULA
				,null			--MOTIVO_ANULA
				,0				--OTRO_ANTICIPO
				,@cod_usuario_confirma	--COD_USUARIO_CONFIRMA = ADMINISTRADOR
				,@fecha			--FECHA_CONFIRMA
			from aux_ingreso_pago
			where cod_ingreso_pago = @cod_ip
			
			select @cod_ingreso_pago = @@identity

			--marca aux_ingreso_pago, cuando se traspasa el IP
			update aux_ingreso_pago
			set cod_ingreso_pago_sql = @cod_ingreso_pago
			where cod_ingreso_pago = @cod_ip

			-- crear INGRESO_PAGO_FACTURA
			insert into INGRESO_PAGO_FACTURA
			   (COD_INGRESO_PAGO
			   ,MONTO_ASIGNADO
			   ,TIPO_DOC
			   ,COD_DOC)
			select @cod_ingreso_pago	--COD_INGRESO_PAGO
				,MONTO_ASIGNADO
				,'FACTURA'				--TIPO_DOC
				,(select f.cod_factura from factura f where f.nro_factura = apf.NRO_FACT)--COD_DOC
			from aux_asig_pago_fact apf
			where apf.cod_ingreso_pago = @cod_ip

		-- 
		/*
		select count(*) from aux_documento d, aux_ingreso_pago ipa
		where ipa.cod_ingreso_pago = d.cod_ingreso_pago
		group by d.cod_ingreso_pago
		*/
		-- dado el select anterior se comprueba que todos los IP tienen solo un documento	
		set @cod_tipo_doc = null
		set @TIPO_DOC_PAGO = null

		select @cod_tipo_doc = cod_tipo_doc from aux_documento
		where cod_ingreso_pago = @cod_ip
	
		if(@cod_tipo_doc = 1)--EFECTIVO
			set @TIPO_DOC_PAGO = 1
		else if(@cod_tipo_doc = 2)--CHEQUE
			set @TIPO_DOC_PAGO = 2
		else if(@cod_tipo_doc = 3)--VALE VISTA
			set @TIPO_DOC_PAGO = 3
		else if(@cod_tipo_doc = 4)--DEPOSITO EN CTA CTE
			set @TIPO_DOC_PAGO = 4
		else if(@cod_tipo_doc = 5)--NOTA DE CREDITO
			set @TIPO_DOC_PAGO = 7
		else if(@cod_tipo_doc = 7)--REGULARIZACION NO SE CREA ESTE DOCUMENTO, SE DEJARÁ COMO EFECTIVO = 5 REGISTROS
			set @TIPO_DOC_PAGO = 1 
		else if(@cod_tipo_doc = 10)--T.DEBITO
			set @TIPO_DOC_PAGO = 5
		else if(@cod_tipo_doc = 11)--T.CREDITO
			set @TIPO_DOC_PAGO = 6
		else if(@cod_tipo_doc = 12)--TRANSFERENCIA BANCARIA
			set @TIPO_DOC_PAGO = 10
		--@cod_tipo_doc = 6 = MULTA --no hay registros 
		--@cod_tipo_doc = 13 = FACTURA DE COMPRA --no hay registros
		
		-- crear DOC_INGRESO_PAGO
			insert into DOC_INGRESO_PAGO
			   (COD_INGRESO_PAGO
			   ,COD_TIPO_DOC_PAGO
			   ,COD_BANCO
			   ,COD_PLAZA
			   ,NRO_DOC
			   ,FECHA_DOC
			   ,MONTO_DOC)
			select @cod_ingreso_pago	--COD_INGRESO_PAGO
				,@TIPO_DOC_PAGO
				,COD_BANCO
				,null					--COD_PLAZA
				,NRO_DOC
				,FECHA_VENCIMIENTO
				,MONTO
			from aux_documento
			where cod_ingreso_pago = @cod_ip

		-- crear MONTO_DOC_ASIGNADO
		exec spu_ingreso_pago 'CONFIRMA', @cod_ingreso_pago, null, null, null, null, null, @cod_estado_ip_confirma, null, null, @cod_usuario_confirma, null

		end
		fetch c_aux_ip into @cod_ip, @fecha, @cod_usuario, @rut, @dig_verif
	end
	close c_aux_ip
	deallocate c_aux_ip
END
go