CREATE PROCEDURE [dbo].[spi_ventas_x_vendedor_alt](@ve_ano          numeric,
                                                   @ve_cod_usuario  numeric)
AS
BEGIN
	declare
		@vc_cod_usuario_vendedor	numeric,
		@vc_nom_usuario_vendedor	varchar(100),
		@vc_orden					numeric,
		@vl_lista_rut_empresa		varchar(1000),
		@vl_sum_enero				numeric,
		@vl_sum_febrero				numeric,
		@vl_sum_marzo				numeric,
		@vl_sum_abril				numeric,
		@vl_sum_mayo				numeric,
		@vl_sum_junio				numeric,
		@vl_sum_julio				numeric,
		@vl_sum_agosto				numeric,
		@vl_sum_septiembre			numeric,
		@vl_sum_octubre				numeric,
		@vl_sum_noviembre			numeric,
		@vl_sum_diciembre			numeric,
		
		--duplica variables contadores
		@vl_sub_sum_enero				numeric,
		@vl_sub_sum_febrero				numeric,
		@vl_sub_sum_marzo				numeric,
		@vl_sub_sum_abril				numeric,
		@vl_sub_sum_mayo				numeric,
		@vl_sub_sum_junio				numeric,
		@vl_sub_sum_julio				numeric,
		@vl_sub_sum_agosto				numeric,
		@vl_sub_sum_septiembre			numeric,
		@vl_sub_sum_octubre				numeric,
		@vl_sub_sum_noviembre			numeric,
		@vl_sub_sum_diciembre			numeric,
		
		--duplica variables totales
		@vl_tot_enero				numeric,
		@vl_tot_febrero				numeric,
		@vl_tot_marzo				numeric,
		@vl_tot_abril				numeric,
		@vl_tot_mayo				numeric,
		@vl_tot_junio				numeric,
		@vl_tot_julio				numeric,
		@vl_tot_agosto				numeric,
		@vl_tot_septiembre			numeric,
		@vl_tot_octubre				numeric,
		@vl_tot_noviembre			numeric,
		@vl_tot_diciembre			numeric
		
	-- borra el resultado de informes anteriores
	delete INF_VENTAS_X_USUARIO
										
	DECLARE C_CURSOR CURSOR FOR									
	SELECT DISTINCT COD_USUARIO_VENDEDOR1
		,U.NOM_USUARIO
		,ORDEN
	FROM INF_VENTAS_POR_MES I
		,USUARIO U
	WHERE I.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO
	AND YEAR(FECHA_NOTA_VENTA) = @ve_ano
	AND I.COD_USUARIO = @ve_cod_usuario
	AND ORDEN <> 999
	ORDER BY ORDEN
	
	set @vl_sub_sum_enero		= 0
	set @vl_sub_sum_febrero		= 0
	set @vl_sub_sum_marzo		= 0
	set @vl_sub_sum_abril		= 0
	set @vl_sub_sum_mayo		= 0
	set @vl_sub_sum_junio		= 0
	set @vl_sub_sum_julio		= 0
	set @vl_sub_sum_agosto		= 0
	set @vl_sub_sum_septiembre	= 0
	set @vl_sub_sum_octubre		= 0
	set @vl_sub_sum_noviembre	= 0
	set @vl_sub_sum_diciembre	= 0
	
	set @vl_tot_enero		= 0
	set @vl_tot_febrero		= 0
	set @vl_tot_marzo		= 0
	set @vl_tot_abril		= 0
	set @vl_tot_mayo		= 0
	set @vl_tot_junio		= 0
	set @vl_tot_julio		= 0
	set @vl_tot_agosto		= 0
	set @vl_tot_septiembre	= 0
	set @vl_tot_octubre		= 0
	set @vl_tot_noviembre	= 0
	set @vl_tot_diciembre	= 0
						
	OPEN C_CURSOR 
	FETCH C_CURSOR INTO @vc_cod_usuario_vendedor, @vc_nom_usuario_vendedor, @vc_orden
	WHILE @@FETCH_STATUS = 0
	BEGIN 
		
		if(@vc_cod_usuario_vendedor = 6)
			set @vl_lista_rut_empresa = '76178360,88279900,96945020,76178390,76117696'
		else if(@vc_cod_usuario_vendedor = 10)
			set @vl_lista_rut_empresa = '79512160,94623000,96905180,96992160,96550960,96883290,76098841'
		else if(@vc_cod_usuario_vendedor = 11)
			set @vl_lista_rut_empresa = '76178360,88279900,96945020,76178390,76117696'			
		else
			set @vl_lista_rut_empresa = '0'
		
		SELECT @vl_sum_enero		= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 1, @vl_lista_rut_empresa, 'S', @ve_ano, @ve_cod_usuario)
		SELECT @vl_sum_febrero		= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 2, @vl_lista_rut_empresa, 'S', @ve_ano, @ve_cod_usuario)
		SELECT @vl_sum_marzo		= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 3, @vl_lista_rut_empresa, 'S', @ve_ano, @ve_cod_usuario)
		SELECT @vl_sum_abril		= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 4, @vl_lista_rut_empresa, 'S', @ve_ano, @ve_cod_usuario)
		SELECT @vl_sum_mayo			= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 5, @vl_lista_rut_empresa, 'S', @ve_ano, @ve_cod_usuario)
		SELECT @vl_sum_junio		= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 6, @vl_lista_rut_empresa, 'S', @ve_ano, @ve_cod_usuario)
		SELECT @vl_sum_julio		= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 7, @vl_lista_rut_empresa, 'S', @ve_ano, @ve_cod_usuario)
		SELECT @vl_sum_agosto		= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 8, @vl_lista_rut_empresa, 'S', @ve_ano, @ve_cod_usuario)
		SELECT @vl_sum_septiembre	= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 9, @vl_lista_rut_empresa, 'S', @ve_ano, @ve_cod_usuario)
		SELECT @vl_sum_octubre		= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 10, @vl_lista_rut_empresa, 'S', @ve_ano, @ve_cod_usuario)
		SELECT @vl_sum_noviembre	= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 11, @vl_lista_rut_empresa, 'S', @ve_ano, @ve_cod_usuario)
		SELECT @vl_sum_diciembre	= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 12, @vl_lista_rut_empresa, 'S', @ve_ano, @ve_cod_usuario)
		
		INSERT INF_VENTAS_X_USUARIO values (@vc_nom_usuario_vendedor
											,@vl_sum_enero
											,@vl_sum_febrero
											,@vl_sum_marzo
											,@vl_sum_abril
											,@vl_sum_mayo
											,@vl_sum_junio
											,@vl_sum_julio
											,@vl_sum_agosto
											,@vl_sum_septiembre
											,@vl_sum_octubre
											,@vl_sum_noviembre
											,@vl_sum_diciembre)
		
		set @vl_sub_sum_enero		= @vl_sub_sum_enero + @vl_sum_enero
		set @vl_sub_sum_febrero		= @vl_sub_sum_febrero + @vl_sum_febrero
		set @vl_sub_sum_marzo		= @vl_sub_sum_marzo + @vl_sum_marzo
		set @vl_sub_sum_abril		= @vl_sub_sum_abril + @vl_sum_abril
		set @vl_sub_sum_mayo		= @vl_sub_sum_mayo + @vl_sum_mayo
		set @vl_sub_sum_junio		= @vl_sub_sum_junio + @vl_sum_junio
		set @vl_sub_sum_julio		= @vl_sub_sum_julio + @vl_sum_julio
		set @vl_sub_sum_agosto		= @vl_sub_sum_agosto + @vl_sum_agosto
		set @vl_sub_sum_septiembre	= @vl_sub_sum_septiembre + @vl_sum_septiembre
		set @vl_sub_sum_octubre		= @vl_sub_sum_octubre + @vl_sum_octubre
		set @vl_sub_sum_noviembre	= @vl_sub_sum_noviembre + @vl_sum_noviembre
		set @vl_sub_sum_diciembre	= @vl_sub_sum_diciembre + @vl_sum_diciembre
		
		if(@vc_cod_usuario_vendedor = 6 OR @vc_cod_usuario_vendedor = 10 OR @vc_cod_usuario_vendedor = 11)BEGIN
			SELECT @vl_sum_enero		= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 1, @vl_lista_rut_empresa, 'N', @ve_ano, @ve_cod_usuario)
			SELECT @vl_sum_febrero		= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 2, @vl_lista_rut_empresa, 'N', @ve_ano, @ve_cod_usuario)
			SELECT @vl_sum_marzo		= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 3, @vl_lista_rut_empresa, 'N', @ve_ano, @ve_cod_usuario)
			SELECT @vl_sum_abril		= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 4, @vl_lista_rut_empresa, 'N', @ve_ano, @ve_cod_usuario)
			SELECT @vl_sum_mayo			= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 5, @vl_lista_rut_empresa, 'N', @ve_ano, @ve_cod_usuario)
			SELECT @vl_sum_junio		= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 6, @vl_lista_rut_empresa, 'N', @ve_ano, @ve_cod_usuario)
			SELECT @vl_sum_julio		= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 7, @vl_lista_rut_empresa, 'N', @ve_ano, @ve_cod_usuario)
			SELECT @vl_sum_agosto		= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 8, @vl_lista_rut_empresa, 'N', @ve_ano, @ve_cod_usuario)
			SELECT @vl_sum_septiembre	= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 9, @vl_lista_rut_empresa, 'N', @ve_ano, @ve_cod_usuario)
			SELECT @vl_sum_octubre		= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 10, @vl_lista_rut_empresa, 'N', @ve_ano, @ve_cod_usuario)
			SELECT @vl_sum_noviembre	= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 11, @vl_lista_rut_empresa, 'N', @ve_ano, @ve_cod_usuario)
			SELECT @vl_sum_diciembre	= dbo.f_get_total_neto_vendedor_alt(@vc_cod_usuario_vendedor, 12, @vl_lista_rut_empresa, 'N', @ve_ano, @ve_cod_usuario)
			
			INSERT INF_VENTAS_X_USUARIO values (@vc_nom_usuario_vendedor
												,@vl_sum_enero
												,@vl_sum_febrero
												,@vl_sum_marzo
												,@vl_sum_abril
												,@vl_sum_mayo
												,@vl_sum_junio
												,@vl_sum_julio
												,@vl_sum_agosto
												,@vl_sum_septiembre
												,@vl_sum_octubre
												,@vl_sum_noviembre
												,@vl_sum_diciembre)
			
			set @vl_sub_sum_enero		= @vl_sub_sum_enero + @vl_sum_enero
			set @vl_sub_sum_febrero		= @vl_sub_sum_febrero + @vl_sum_febrero
			set @vl_sub_sum_marzo		= @vl_sub_sum_marzo + @vl_sum_marzo
			set @vl_sub_sum_abril		= @vl_sub_sum_abril + @vl_sum_abril
			set @vl_sub_sum_mayo		= @vl_sub_sum_mayo + @vl_sum_mayo
			set @vl_sub_sum_junio		= @vl_sub_sum_junio + @vl_sum_junio
			set @vl_sub_sum_julio		= @vl_sub_sum_julio + @vl_sum_julio
			set @vl_sub_sum_agosto		= @vl_sub_sum_agosto + @vl_sum_agosto
			set @vl_sub_sum_septiembre	= @vl_sub_sum_septiembre + @vl_sum_septiembre
			set @vl_sub_sum_octubre		= @vl_sub_sum_octubre + @vl_sum_octubre
			set @vl_sub_sum_noviembre	= @vl_sub_sum_noviembre + @vl_sum_noviembre
			set @vl_sub_sum_diciembre	= @vl_sub_sum_diciembre + @vl_sum_diciembre
		END
		
		if(@vc_cod_usuario_vendedor = 38 OR @vc_cod_usuario_vendedor = 15)BEGIN
			INSERT INF_VENTAS_X_USUARIO values ('SUBTOTAL'
												,@vl_sub_sum_enero
												,@vl_sub_sum_febrero
												,@vl_sub_sum_marzo
												,@vl_sub_sum_abril
												,@vl_sub_sum_mayo
												,@vl_sub_sum_junio
												,@vl_sub_sum_julio
												,@vl_sub_sum_agosto
												,@vl_sub_sum_septiembre
												,@vl_sub_sum_octubre
												,@vl_sub_sum_noviembre
												,@vl_sub_sum_diciembre)
			
			set @vl_tot_enero		= @vl_tot_enero + @vl_sub_sum_enero
			set @vl_tot_febrero		= @vl_tot_febrero + @vl_sub_sum_febrero
			set @vl_tot_marzo		= @vl_tot_marzo + @vl_sub_sum_marzo
			set @vl_tot_abril		= @vl_tot_abril + @vl_sub_sum_abril
			set @vl_tot_mayo		= @vl_tot_mayo + @vl_sub_sum_mayo
			set @vl_tot_junio		= @vl_tot_junio + @vl_sub_sum_junio
			set @vl_tot_julio		= @vl_tot_julio + @vl_sub_sum_julio
			set @vl_tot_agosto		= @vl_tot_agosto + @vl_sub_sum_agosto
			set @vl_tot_septiembre	= @vl_tot_septiembre + @vl_sub_sum_septiembre
			set @vl_tot_octubre		= @vl_tot_octubre + @vl_sub_sum_octubre
			set @vl_tot_noviembre	= @vl_tot_noviembre + @vl_sub_sum_noviembre
			set @vl_tot_diciembre	= @vl_tot_diciembre + @vl_sub_sum_diciembre
												
			set @vl_sub_sum_enero		= 0
			set @vl_sub_sum_febrero		= 0
			set @vl_sub_sum_marzo		= 0
			set @vl_sub_sum_abril		= 0
			set @vl_sub_sum_mayo		= 0
			set @vl_sub_sum_junio		= 0
			set @vl_sub_sum_julio		= 0
			set @vl_sub_sum_agosto		= 0
			set @vl_sub_sum_septiembre	= 0
			set @vl_sub_sum_octubre		= 0
			set @vl_sub_sum_noviembre	= 0
			set @vl_sub_sum_diciembre	= 0
		END
		
		FETCH C_CURSOR INTO @vc_cod_usuario_vendedor, @vc_nom_usuario_vendedor, @vc_orden
	END
	CLOSE C_CURSOR
	DEALLOCATE C_CURSOR
	
	SELECT @vl_sum_enero		= dbo.f_get_total_neto_vendedor_alt(0, 1, '', 'X', @ve_ano, @ve_cod_usuario)
	SELECT @vl_sum_febrero		= dbo.f_get_total_neto_vendedor_alt(0, 2, '', 'X', @ve_ano, @ve_cod_usuario)
	SELECT @vl_sum_marzo		= dbo.f_get_total_neto_vendedor_alt(0, 3, '', 'X', @ve_ano, @ve_cod_usuario)
	SELECT @vl_sum_abril		= dbo.f_get_total_neto_vendedor_alt(0, 4, '', 'X', @ve_ano, @ve_cod_usuario)
	SELECT @vl_sum_mayo			= dbo.f_get_total_neto_vendedor_alt(0, 5, '', 'X', @ve_ano, @ve_cod_usuario)
	SELECT @vl_sum_junio		= dbo.f_get_total_neto_vendedor_alt(0, 6, '', 'X', @ve_ano, @ve_cod_usuario)
	SELECT @vl_sum_julio		= dbo.f_get_total_neto_vendedor_alt(0, 7, '', 'X', @ve_ano, @ve_cod_usuario)
	SELECT @vl_sum_agosto		= dbo.f_get_total_neto_vendedor_alt(0, 8, '', 'X', @ve_ano, @ve_cod_usuario)
	SELECT @vl_sum_septiembre	= dbo.f_get_total_neto_vendedor_alt(0, 9, '', 'X', @ve_ano, @ve_cod_usuario)
	SELECT @vl_sum_octubre		= dbo.f_get_total_neto_vendedor_alt(0, 10, '', 'X', @ve_ano, @ve_cod_usuario)
	SELECT @vl_sum_noviembre	= dbo.f_get_total_neto_vendedor_alt(0, 11, '', 'X', @ve_ano, @ve_cod_usuario)
	SELECT @vl_sum_diciembre	= dbo.f_get_total_neto_vendedor_alt(0, 12, '', 'X', @ve_ano, @ve_cod_usuario)
	
	INSERT INF_VENTAS_X_USUARIO values ('OTROS'
										,@vl_sum_enero
										,@vl_sum_febrero
										,@vl_sum_marzo
										,@vl_sum_abril
										,@vl_sum_mayo
										,@vl_sum_junio
										,@vl_sum_julio
										,@vl_sum_agosto
										,@vl_sum_septiembre
										,@vl_sum_octubre
										,@vl_sum_noviembre
										,@vl_sum_diciembre)
											
	set @vl_sub_sum_enero		= @vl_sub_sum_enero + @vl_sum_enero
	set @vl_sub_sum_febrero		= @vl_sub_sum_febrero + @vl_sum_febrero
	set @vl_sub_sum_marzo		= @vl_sub_sum_marzo + @vl_sum_marzo
	set @vl_sub_sum_abril		= @vl_sub_sum_abril + @vl_sum_abril
	set @vl_sub_sum_mayo		= @vl_sub_sum_mayo + @vl_sum_mayo
	set @vl_sub_sum_junio		= @vl_sub_sum_junio + @vl_sum_junio
	set @vl_sub_sum_julio		= @vl_sub_sum_julio + @vl_sum_julio
	set @vl_sub_sum_agosto		= @vl_sub_sum_agosto + @vl_sum_agosto
	set @vl_sub_sum_septiembre	= @vl_sub_sum_septiembre + @vl_sum_septiembre
	set @vl_sub_sum_octubre		= @vl_sub_sum_octubre + @vl_sum_octubre
	set @vl_sub_sum_noviembre	= @vl_sub_sum_noviembre + @vl_sum_noviembre
	set @vl_sub_sum_diciembre	= @vl_sub_sum_diciembre + @vl_sum_diciembre	
	
	INSERT INF_VENTAS_X_USUARIO values ('SUBTOTAL'
										,@vl_sub_sum_enero
										,@vl_sub_sum_febrero
										,@vl_sub_sum_marzo
										,@vl_sub_sum_abril
										,@vl_sub_sum_mayo
										,@vl_sub_sum_junio
										,@vl_sub_sum_julio
										,@vl_sub_sum_agosto
										,@vl_sub_sum_septiembre
										,@vl_sub_sum_octubre
										,@vl_sub_sum_noviembre
										,@vl_sub_sum_diciembre)									
	
	set @vl_tot_enero		= @vl_tot_enero + @vl_sub_sum_enero
	set @vl_tot_febrero		= @vl_tot_febrero + @vl_sub_sum_febrero
	set @vl_tot_marzo		= @vl_tot_marzo + @vl_sub_sum_marzo
	set @vl_tot_abril		= @vl_tot_abril + @vl_sub_sum_abril
	set @vl_tot_mayo		= @vl_tot_mayo + @vl_sub_sum_mayo
	set @vl_tot_junio		= @vl_tot_junio + @vl_sub_sum_junio
	set @vl_tot_julio		= @vl_tot_julio + @vl_sub_sum_julio
	set @vl_tot_agosto		= @vl_tot_agosto + @vl_sub_sum_agosto
	set @vl_tot_septiembre	= @vl_tot_septiembre + @vl_sub_sum_septiembre
	set @vl_tot_octubre		= @vl_tot_octubre + @vl_sub_sum_octubre
	set @vl_tot_noviembre	= @vl_tot_noviembre + @vl_sub_sum_noviembre
	set @vl_tot_diciembre	= @vl_tot_diciembre + @vl_sub_sum_diciembre
	
	INSERT INF_VENTAS_X_USUARIO values ('TOTAL'
										,@vl_tot_enero
										,@vl_tot_febrero
										,@vl_tot_marzo
										,@vl_tot_abril
										,@vl_tot_mayo
										,@vl_tot_junio
										,@vl_tot_julio
										,@vl_tot_agosto
										,@vl_tot_septiembre
										,@vl_tot_octubre
										,@vl_tot_noviembre
										,@vl_tot_diciembre)	
					
	SELECT * FROM INF_VENTAS_X_USUARIO								
END