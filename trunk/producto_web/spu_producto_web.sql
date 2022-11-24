alter PROCEDURE [dbo].[spu_producto_web]
			(@ve_operacion					varchar(20)
			,@ve_cod_producto				varchar(30) 
			,@ve_nom_producto				varchar(100)
			,@ve_largo						numeric
			,@ve_ancho						numeric
			,@ve_alto						numeric
			,@ve_peso						numeric
			,@ve_largo_embalado				numeric
			,@ve_ancho_embalado				numeric
			,@ve_alto_embalado				numeric
			,@ve_peso_embalado				numeric
			,@ve_usa_electricidad			varchar(1)
			,@ve_nro_fases					varchar(1)
			,@ve_consumo_electricidad		numeric(10,2)
			,@ve_rango_temperatura			varchar(100)
			,@ve_voltaje					numeric
			,@ve_frecuencia					numeric
			,@ve_nro_certificado_electrico 	varchar(100)
			,@ve_usa_gas					varchar(1)
			,@ve_potencia					numeric(10,2)
			,@ve_consumo_gas				numeric
			,@ve_nro_certificado_gas 		varchar(100)
			,@ve_usa_vapor					varchar(1)
			,@ve_consumo_vapor				numeric
			,@ve_presion_vapor				numeric
			,@ve_usa_agua_fria				varchar(1)
			,@ve_usa_agua_caliente			varchar(1)
			,@ve_caudal						numeric
			,@ve_presion_agua				numeric
			,@ve_diametro_caneria			varchar(10)
			,@ve_usa_ventilacion			varchar(1)
			,@ve_volumen					numeric
			,@ve_caida_presion				numeric
			,@ve_diametro_ducto				numeric
			,@ve_nro_filtros				numeric
			,@ve_usa_desague				varchar(1)
			,@ve_diametro_desague			varchar(10)
			,@ve_es_oferta					varchar(1)
			,@ve_precio_oferta				T_PRECIO
			,@ve_es_reciclado				varchar(1))
AS
BEGIN
		if (@ve_operacion='UPDATE') 
			begin
				update producto 
				set		nom_producto				= @ve_nom_producto
						,largo						= @ve_largo
						,ancho						= @ve_ancho
						,alto						= @ve_alto
						,peso						= @ve_peso
						,largo_embalado				= @ve_largo_embalado
						,ancho_embalado				= @ve_ancho_embalado
						,alto_embalado				= @ve_alto_embalado
						,peso_embalado				= @ve_peso_embalado
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
						,es_oferta 					= @ve_es_oferta
						,precio_oferta				= @ve_precio_oferta
						,es_reciclado				= @ve_es_reciclado
						--,maneja_inventario			= @ve_maneja_inventario
				where	cod_producto				= @ve_cod_producto
			end
		
END