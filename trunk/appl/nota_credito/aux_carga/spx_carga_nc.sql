CREATE PROCEDURE [dbo].[spx_carga_nc]
AS
BEGIN  
	declare @nro_nc				numeric
			,@fecha				varchar(10)
			,@count				numeric
			,@rut				numeric
			,@dig_verif			varchar(1)
			,@estado			varchar(10)
			,@cod_doc			numeric
			,@emisor			varchar(30)
			,@cod_empresa		numeric
			,@nom_empresa		varchar(100)
			,@giro				varchar(100)
			,@cod_sucursal_factura numeric
			,@nom_sucursal		varchar(100)
			,@direccion			varchar(100)
			,@cod_comuna		numeric
			,@cod_ciudad		numeric
			,@cod_pais			numeric
			,@telefono			varchar(100)
			,@fax				varchar(100)
			,@cod_persona		numeric
			,@nom_persona		varchar(100)
			,@email				varchar(100)
			,@cod_cargo			numeric
			,@cod_estado_doc_sii numeric
			,@fecha_anula		varchar(10)
			,@motivo_anula		varchar(100)
			,@cod_usuario_anula	numeric
			,@count_fa			numeric
			,@cod_usuario_impresion numeric
			,@numero_factura numeric
			,@cod_tipo_nota_credito numeric
	
	declare c_aux_nc cursor for 
	select numero_nc
		,fecha
		,convert(numeric, substring(RUT_CLIENTE, 1, len(RUT_CLIENTE) - 1)) --rut
		,substring(RUT_CLIENTE, len(RUT_CLIENTE), 1) --dig_verif
		,numero_factura
		,emisor
	from aux_nota_credito
	where estado <> 'Anulada' --hay 12 registros con estado anulado: con rut 19, numero_factura = 0, por lo tanto no hay como obtener el dato del cliente
		and (numero_factura in (select nro_factura from factura) -- solo se cargan las NC que tengan factura creada en nuevo sistema
		or numero_factura = 0)
	
	open c_aux_nc 
	fetch c_aux_nc into @nro_nc, @fecha, @rut, @dig_verif, @numero_factura, @emisor
	WHILE @@FETCH_STATUS = 0 BEGIN
		select @count=count(*) from nota_credito where nro_nota_credito = @nro_nc
		if (@count = 0) begin
			-- obtiene datos de empresa, sucursal y persona
			set @cod_empresa = null
			set @nom_empresa = null
			set @giro = null
			set @cod_sucursal_factura = null
			set @nom_sucursal = null
			set @direccion = null
			set @cod_comuna = null
			set @cod_ciudad = null
			set @cod_pais = null
			set @telefono = null
			set @fax = null
			set @cod_persona = null
			set @nom_persona = null
			set @email = null
			set @cod_cargo = null
			set @cod_estado_doc_sii = null
			set @count_fa = 0

			-- obtiene el cod_doc
			if (@numero_factura = 0)
			begin
				set @cod_doc = null
				set @cod_tipo_nota_credito = null
				select @cod_empresa = cod_empresa
					,@nom_empresa = nom_empresa
					,@giro = giro
				from empresa where rut = @rut
			end
			else
			begin
				--los datos de la empresa los obtiene por la fa de la nc
				-- en 4D no hay integridad de datos
				select @count_fa = count(*) from factura where nro_factura = @numero_factura
				if(@count_fa = 0) begin--no existe la FA en el nuevo sistema, esta condición no debería darse por filtro en el select del cursor
					set @cod_doc = null
					set @cod_tipo_nota_credito = null
					select @cod_empresa = cod_empresa
						,@nom_empresa = nom_empresa
						,@giro = giro
					from empresa where rut = @rut
				end
				else
				begin
					set @cod_tipo_nota_credito = 1 -- FACTURA
					select @cod_empresa = e.cod_empresa
						,@nom_empresa = e.nom_empresa
						,@giro = e.giro
						,@cod_doc = f.cod_factura
					from empresa e, factura f
					where f.nro_factura = @numero_factura
						and e.cod_empresa = f.cod_empresa
				end
			end
			
			select @cod_sucursal_factura = cod_sucursal 
				,@nom_sucursal = nom_sucursal
				,@direccion = direccion
				,@cod_comuna = cod_comuna
				,@cod_ciudad = cod_ciudad
				,@cod_pais = cod_pais
				,@telefono = telefono
				,@fax = fax
			from sucursal 
			where cod_empresa = @cod_empresa
				and direccion_factura = 'S'


			select top 1 @cod_persona = cod_persona
						,@nom_persona = nom_persona
						,@email = email
						,@cod_cargo = cod_cargo 
			from persona where cod_sucursal = @cod_sucursal_factura 
			--hay casos donde la sucursal de factura no tiene personas, por ello se asigna cualquier persona de la empresa
			if (@cod_persona is null)
				select top 1 @cod_persona = cod_persona
					,@nom_persona = nom_persona
					,@email = email
					,@cod_cargo = cod_cargo 
				from persona p, sucursal s 
				where s.cod_empresa = @cod_empresa
				and p.cod_sucursal = s.cod_sucursal
 	
			--todos los documentos estan en estado impreso
			set @cod_estado_doc_sii = 2
			set @fecha_anula = null
			set @motivo_anula = null
			set @cod_usuario_anula = null

			set @cod_usuario_impresion = null
			select @cod_usuario_impresion = cod_usuario from usuario where nom_usuario=@emisor
			if (@cod_usuario_impresion is null)
				set @cod_usuario_impresion = 1

			insert into NOTA_CREDITO
			   (FECHA_REGISTRO
			   ,COD_USUARIO
			   ,NRO_NOTA_CREDITO
			   ,FECHA_NOTA_CREDITO
			   ,COD_ESTADO_DOC_SII
			   ,COD_EMPRESA
			   ,COD_SUCURSAL_FACTURA
			   ,COD_PERSONA
			   ,COD_TIPO_NOTA_CREDITO
			   ,COD_DOC
			   ,REFERENCIA
			   ,OBS
			   ,GENERA_ENTRADA
			   ,FECHA_ANULA
			   ,MOTIVO_ANULA
			   ,COD_USUARIO_ANULA
			   ,RUT
			   ,DIG_VERIF
			   ,NOM_EMPRESA
			   ,GIRO
			   ,NOM_SUCURSAL
			   ,DIRECCION
			   ,COD_COMUNA
			   ,COD_CIUDAD
			   ,COD_PAIS
			   ,TELEFONO
			   ,FAX
			   ,NOM_PERSONA
			   ,MAIL
			   ,COD_CARGO
			   ,COD_USUARIO_IMPRESION
			   ,COD_BODEGA
			   ,SUBTOTAL
			   ,PORC_DSCTO1
			   ,INGRESO_USUARIO_DSCTO1
			   ,MONTO_DSCTO1
			   ,PORC_DSCTO2
			   ,INGRESO_USUARIO_DSCTO2
			   ,MONTO_DSCTO2
			   ,TOTAL_NETO
			   ,PORC_IVA
			   ,MONTO_IVA
			   ,TOTAL_CON_IVA)
		select getdate()	--FECHA_REGISTRO
				,1			--COD_USUARIO
				,@nro_nc	--NRO_NOTA_CREDITO
				,@fecha
				,@cod_estado_doc_sii
				,@cod_empresa
				,@cod_sucursal_factura
				,@cod_persona
				,@cod_tipo_nota_credito
				,@cod_doc
				,isnull(substring(REF, 1, 100), '')
				,OBS
				,'N'		--GENERA_ENTRADA, todos estan en 'N'
				,@fecha_anula				
				,@motivo_anula
				,@cod_usuario_anula
				,@rut 
				,@dig_verif
				,@nom_empresa
				,@giro
				,@nom_sucursal
				,@direccion
				,@cod_comuna
				,@cod_ciudad
				,@cod_pais
				,@telefono
				,@fax
				,@nom_persona
				,@email
				,@cod_cargo
				,@cod_usuario_impresion
				,null		--COD_BODEGA
				,TOTAL		--SUBTOTAL
				,DESC1		--PORC_DSCTO1
				,'M'		--INGRESO_USUARIO_DSCTO1
				,TOTAL_DESC1	--MONTO_DSCTO1
				,DESC2		--PORC_DSCTO2
				,'M'		--INGRESO_USUARIO_DSCTO2
				,TOTAL_DESC2	--MONTO_DSCTO2
				,TOTAL_NETO
			    ,IVA
			    ,TOTAL_IVA
			    ,TOTAL_CON_IVA
			from aux_nota_credito
			where numero_nc = @nro_nc	
			
			--marca aux_nota_credito, cuando se traspasa la NC
			update aux_nota_credito
			set cod_nota_credito_sql = @@identity
			where numero_nc = @nro_nc
		end
		fetch c_aux_nc into @nro_nc, @fecha, @rut, @dig_verif, @numero_factura, @emisor	
	end
	close c_aux_nc
	deallocate c_aux_nc
END
go