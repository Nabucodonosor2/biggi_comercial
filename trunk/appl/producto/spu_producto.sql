ALTER PROCEDURE [dbo].[spu_producto]
			(@ve_operacion					varchar(20)
			,@ve_cod_producto				varchar(30) 
			,@ve_nom_producto				varchar(100) =null
			,@ve_cod_tipo_producto			numeric =null
			,@ve_cod_marca					numeric =null
			,@ve_nom_producto_ingles		varchar(100) =null
			,@ve_cod_familia_producto		numeric =null
			,@ve_largo						numeric =null
			,@ve_ancho						numeric =null
			,@ve_alto						numeric =null
			,@ve_peso						numeric =null
			,@ve_largo_embalado				numeric =null
			,@ve_ancho_embalado				numeric =null
			,@ve_alto_embalado				numeric =null
			,@ve_peso_embalado				numeric =null
			,@ve_factor_venta_interno		T_PORCENTAJE =null
			,@ve_precio_venta_interno		T_PRECIO =null
			,@ve_factor_venta_publico		T_PORCENTAJE =null
			,@ve_precio_venta_publico		T_PRECIO =null
			,@ve_usa_electricidad			varchar(1) =null
			,@ve_nro_fases					varchar(1) =null
			,@ve_consumo_electricidad		numeric(10,2) =null
			,@ve_rango_temperatura			varchar(100) =null
			,@ve_voltaje					numeric =null
			,@ve_frecuencia					numeric =null
			,@ve_nro_certificado_electrico 	varchar(100) =null
			,@ve_usa_gas					varchar(1) =null
			,@ve_potencia					numeric(10,2) =null
			,@ve_consumo_gas				numeric =null
			,@ve_nro_certificado_gas 		varchar(100) =null
			,@ve_usa_vapor					varchar(1) =null
			,@ve_consumo_vapor				numeric =null
			,@ve_presion_vapor				numeric =null
			,@ve_usa_agua_fria				varchar(1) =null
			,@ve_usa_agua_caliente			varchar(1) =null
			,@ve_caudal						numeric =null
			,@ve_presion_agua				numeric =null
			,@ve_diametro_caneria			varchar(10) =null
			,@ve_usa_ventilacion			varchar(1) =null
			,@ve_volumen					numeric =null
			,@ve_caida_presion				numeric =null
			,@ve_diametro_ducto				numeric =null
			,@ve_nro_filtros				numeric =null
			,@ve_usa_desague				varchar(1) =null
			,@ve_diametro_desague			varchar(10) =null
			,@ve_maneja_inventario			varchar(1) =null
			,@ve_stock_critico				numeric =null
			,@ve_tiempo_reposicion			numeric =null
			,@ve_precio_libre				varchar(1) =null
			,@ve_es_despachable				varchar(1) =null
			,@ve_sistema_valido			varchar(10) =null
			,@ve_potencia_kw			numeric(10,2) =null)
AS
BEGIN
		if (@ve_operacion='UPDATE') 
			begin
				update producto 
				set		nom_producto				= @ve_nom_producto
						,cod_tipo_producto			= @ve_cod_tipo_producto
						,cod_marca					= @ve_cod_marca
						,nom_producto_ingles		= @ve_nom_producto_ingles
						,cod_familia_producto		= @ve_cod_familia_producto
						,largo						= @ve_largo
						,ancho						= @ve_ancho
						,alto						= @ve_alto
						,peso						= @ve_peso
						,largo_embalado				= @ve_largo_embalado
						,ancho_embalado				= @ve_ancho_embalado
						,alto_embalado				= @ve_alto_embalado
						,peso_embalado				= @ve_peso_embalado
						,factor_venta_interno		= @ve_factor_venta_interno
						,precio_venta_interno		= @ve_precio_venta_interno
						,factor_venta_publico		= @ve_factor_venta_publico
						,precio_venta_publico		= @ve_precio_venta_publico
						,usa_electricidad			= @ve_usa_electricidad
						,nro_fases					= @ve_nro_fases
						,consumo_electricidad		= @ve_consumo_electricidad
						,rango_temperatura			= @ve_rango_temperatura
						,voltaje					= @ve_voltaje
						,frecuencia					= @ve_frecuencia
						,nro_certificado_electrico 	= @ve_nro_certificado_electrico
						,usa_gas					= @ve_usa_gas
						,potencia					= @ve_potencia
						,consumo_gas				= @ve_consumo_gas
						,nro_certificado_gas 		= @ve_nro_certificado_gas
						,usa_vapor					= @ve_usa_vapor
						,consumo_vapor				= @ve_consumo_vapor
						,presion_vapor				= @ve_presion_vapor
						,usa_agua_fria				= @ve_usa_agua_fria
						,usa_agua_caliente			= @ve_usa_agua_caliente
						,caudal						= @ve_caudal
						,presion_agua				= @ve_presion_agua
						,diametro_caneria			= @ve_diametro_caneria
						,usa_ventilacion			= @ve_usa_ventilacion
						,volumen					= @ve_volumen
						,caida_presion				= @ve_caida_presion
						,diametro_ducto				= @ve_diametro_ducto
						,nro_filtros				= @ve_nro_filtros
						,usa_desague				= @ve_usa_desague
						,diametro_desague			= @ve_diametro_desague
						--,maneja_inventario			= @ve_maneja_inventario
						,stock_critico				= @ve_stock_critico
						,tiempo_reposicion			= @ve_tiempo_reposicion
						,precio_libre				= @ve_precio_libre						
						,es_despachable				= @ve_es_despachable
						,potencia_kw				= @ve_potencia_kw
						,sistema_valido				= @ve_sistema_valido
				where	cod_producto				= @ve_cod_producto
				
				if(@ve_cod_tipo_producto = 4 or @ve_precio_venta_publico = 0)begin	-- @ve_cod_tipo_producto = 4 Descontinuado
					delete familia_producto
					where cod_producto = @ve_cod_producto
				end 
				
				exec spu_producto 'PRODUCTO_BUSQUEDA', @ve_cod_producto
			end
		else if (@ve_operacion='INSERT') 
			begin
				insert into producto(cod_producto
							,nom_producto
							,cod_tipo_producto
							,cod_marca
							,nom_producto_ingles
							,cod_familia_producto
							,largo
							,ancho
							,alto
							,peso
							,largo_embalado
							,ancho_embalado
							,alto_embalado
							,peso_embalado
							,factor_venta_interno
							,precio_venta_interno
							,factor_venta_publico
							,precio_venta_publico
							,usa_electricidad
							,nro_fases
							,consumo_electricidad
							,rango_temperatura
							,voltaje
							,frecuencia
							,nro_certificado_electrico
							,usa_gas
							,potencia
							,consumo_gas
							,nro_certificado_gas
							,usa_vapor
							,consumo_vapor
							,presion_vapor
							,usa_agua_fria
							,usa_agua_caliente
							,caudal
							,presion_agua
							,diametro_caneria
							,usa_ventilacion
							,volumen
							,caida_presion
							,diametro_ducto
							,nro_filtros
							,usa_desague
							,diametro_desague
							,maneja_inventario
							,stock_critico
							,tiempo_reposicion
							,precio_libre
							,es_despachable
							,sistema_valido
							,potencia_kw)
				values (
						@ve_cod_producto 
						,@ve_nom_producto
						,@ve_cod_tipo_producto
						,@ve_cod_marca
						,@ve_nom_producto_ingles
						,@ve_cod_familia_producto
						,@ve_largo
						,@ve_ancho
						,@ve_alto
						,@ve_peso
						,@ve_largo_embalado
						,@ve_ancho_embalado
						,@ve_alto_embalado
						,@ve_peso_embalado
						,@ve_factor_venta_interno
						,@ve_precio_venta_interno
						,@ve_factor_venta_publico
						,@ve_precio_venta_publico
						,@ve_usa_electricidad
						,@ve_nro_fases
						,@ve_consumo_electricidad
						,@ve_rango_temperatura
						,@ve_voltaje
						,@ve_frecuencia
						,@ve_nro_certificado_electrico
						,@ve_usa_gas
						,@ve_potencia
						,@ve_consumo_gas
						,@ve_nro_certificado_gas
						,@ve_usa_vapor
						,@ve_consumo_vapor
						,@ve_presion_vapor
						,@ve_usa_agua_fria
						,@ve_usa_agua_caliente
						,@ve_caudal
						,@ve_presion_agua
						,@ve_diametro_caneria
						,@ve_usa_ventilacion
						,@ve_volumen
						,@ve_caida_presion
						,@ve_diametro_ducto
						,@ve_nro_filtros
						,@ve_usa_desague
						,@ve_diametro_desague
						,@ve_maneja_inventario
						,@ve_stock_critico
						,@ve_tiempo_reposicion
						,@ve_precio_libre
						,@ve_es_despachable
						,@ve_sistema_valido
						,@ve_potencia_kw)			

				exec spu_producto 'PRODUCTO_BUSQUEDA', @ve_cod_producto
			end 
		else if (@ve_operacion='PRODUCTO_BUSQUEDA') begin
			--borra lo del producto
			delete producto_busqueda
			where COD_PRODUCTO = @ve_cod_producto

			declare 
				@vl_nom_producto				varchar(100)
				,@vc_nom_atributo_producto		varchar(1000)
				,@vc_cod_atributo_producto		numeric
				,@vl_prod_web					varchar(100)

			select @vl_prod_web = dbo.f_prod_web (@ve_cod_producto)

			--si no publica en la web no hace nada			
			if (@vl_prod_web <> 'S')
				return

				
			--se inserta como palabra el cod_producto
			insert into producto_busqueda
				(COD_PRODUCTO
				,PALABRA
				,CAMPO_UBICACION
				,COD_ATRIBUTO_PRODUCTO
				)
			values
				(@ve_cod_producto
				,@ve_cod_producto
				,'COD_PRODUCTO'
				,null
				)
	
			--buscamos las pabras en el nom_producto
			select @vl_nom_producto = NOM_PRODUCTO
			from PRODUCTO
			where COD_PRODUCTO = @ve_cod_producto

			insert into producto_busqueda
				(COD_PRODUCTO
				,PALABRA
				,CAMPO_UBICACION
				,COD_ATRIBUTO_PRODUCTO
				)
			select @ve_cod_producto
					,item
					,'NOM_PRODUCTO'
					,null
			from dbo.f_prod_busq_palabra(@vl_nom_producto)

			--ATRIBUTOS
			DECLARE C_ATRIB CURSOR FOR 
			SELECT nom_atributo_producto
					,cod_atributo_producto
			FROM atributo_producto
			where cod_producto = @ve_cod_producto

			OPEN C_ATRIB
			FETCH C_ATRIB INTO @vc_nom_atributo_producto, @vc_cod_atributo_producto
			WHILE @@FETCH_STATUS = 0 BEGIN
				insert into producto_busqueda
					(COD_PRODUCTO
					,PALABRA
					,CAMPO_UBICACION
					,COD_ATRIBUTO_PRODUCTO
					)
				select @ve_cod_producto
						,item
						,'NOM_ATRIBUTO'
						,@vc_cod_atributo_producto
				from dbo.f_prod_busq_palabra(@vc_nom_atributo_producto)

				FETCH C_ATRIB INTO @vc_nom_atributo_producto, @vc_cod_atributo_producto
			END
			CLOSE C_ATRIB
			DEALLOCATE C_ATRIB
		end
END