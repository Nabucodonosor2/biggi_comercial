--------------- spi_ventas_por_mes_fa --------------
CREATE PROCEDURE spi_ventas_por_mes_fa(@ve_cod_usuario	numeric
									  ,@ve_ano			numeric)
AS
BEGIN
	DECLARE
	@vl_fecha_actual		DATETIME

	set @vl_fecha_actual = getdate()
	
	-- borra el resultado de informes anteriores del mismo usuario
	delete INF_VENTAS_POR_MES_FA
	where cod_usuario = @ve_cod_usuario

	insert into	INF_VENTAS_POR_MES_FA   (FECHA_INF_VENTAS_POR_MES  
										,COD_USUARIO               
										,COD_FACTURA            
    									,MES                       
    									,ANO                       
    									,NOM_MES                   
    									,FECHA_FACTURA          
    									,NOM_EMPRESA                   
    									,INI_USUARIO  
										,COD_USUARIO_VENDEDOR1             
    									,SUBTOTAL                  
    									,TOTAL_NETO                            
    									,PORC_DSCTO                
    									,MONTO_DSCTO
    									,RUT_EMPRESA
    									,ORDEN)

	select @vl_fecha_actual
			,@ve_cod_usuario
			,COD_FACTURA
			,MONTH(F.FECHA_FACTURA) MES
			,YEAR(F.FECHA_FACTURA) ANO
			,M.NOM_MES
			,F.FECHA_FACTURA
			,E.NOM_EMPRESA
			,U.INI_USUARIO
			,F.COD_USUARIO_VENDEDOR1
			,ROUND(F.SUBTOTAL, 0) SUBTOTAL
			,ROUND(F.TOTAL_NETO, 0) TOTAL_NETO
			,ROUND((F.SUBTOTAL - F.TOTAL_NETO) / F.SUBTOTAL * 100, 1) PORC_DSCTO
			,ROUND((F.SUBTOTAL - F.TOTAL_NETO), 1) MONTO_DSCTO
			,E.RUT
			,CASE
			 	WHEN F.COD_USUARIO_VENDEDOR1 = 17 then 1
			 	WHEN F.COD_USUARIO_VENDEDOR1 = 7 then 2
			 	WHEN F.COD_USUARIO_VENDEDOR1 = 11 then 3	
			 	WHEN F.COD_USUARIO_VENDEDOR1 = 14 then 4
			 	WHEN F.COD_USUARIO_VENDEDOR1 = 6 then 5
			 	WHEN F.COD_USUARIO_VENDEDOR1 = 12 then 6	
			 	WHEN F.COD_USUARIO_VENDEDOR1 = 13 then 7
			 	WHEN F.COD_USUARIO_VENDEDOR1 = 15 then 8
			 	WHEN F.COD_USUARIO_VENDEDOR1 = 10 then 9
			 	WHEN F.COD_USUARIO_VENDEDOR1 = 63 then 10	
			 	WHEN F.COD_USUARIO_VENDEDOR1 = 38 then 11
			 	ELSE 999					 					 					 				 				 					 				 	 	
			END ORDEN
	FROM FACTURA F
		,EMPRESA E
		,USUARIO U
		,MES M
	WHERE COD_ESTADO_DOC_SII = 3	--ENVIADA A SII
	  AND E.COD_EMPRESA = F.COD_EMPRESA
	  AND U.COD_USUARIO = F.COD_USUARIO_VENDEDOR1
	  AND M.COD_MES = MONTH(F.FECHA_FACTURA)
	  AND YEAR(F.FECHA_FACTURA) = @ve_ano

END