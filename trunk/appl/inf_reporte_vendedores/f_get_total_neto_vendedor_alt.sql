CREATE FUNCTION [dbo].[f_get_total_neto_vendedor_alt](@ve_cod_usuario_vendedor	numeric
													 ,@ve_mes					numeric
													 ,@ve_lista_rut				varchar(1000)
													 ,@ve_es_resto				varchar(1)
													 ,@ve_ano					numeric
                                                     ,@ve_cod_usuario           numeric)
RETURNS NUMERIC
AS
BEGIN
	declare
		@vl_result	numeric
		

	IF(@ve_es_resto = 'S')
		SELECT @vl_result = ISNULL(SUM(TOTAL_VENTA), 0)
		FROM INF_VENTAS_POR_MES
		WHERE COD_USUARIO_VENDEDOR1 = @ve_cod_usuario_vendedor
		AND MONTH(FECHA_NOTA_VENTA) = @ve_mes
		AND YEAR(FECHA_NOTA_VENTA) = @ve_ano
		AND COD_USUARIO = @ve_cod_usuario
		AND RUT_EMPRESA NOT IN (SELECT item FROM f_split(@ve_lista_rut, ','))
	ELSE IF(@ve_es_resto = 'N')
		SELECT @vl_result = ISNULL(SUM(TOTAL_VENTA), 0)
		FROM INF_VENTAS_POR_MES
		WHERE COD_USUARIO_VENDEDOR1 = @ve_cod_usuario_vendedor
		AND MONTH(FECHA_NOTA_VENTA) = @ve_mes
		AND YEAR(FECHA_NOTA_VENTA) = @ve_ano
		AND COD_USUARIO = @ve_cod_usuario
		AND RUT_EMPRESA IN (SELECT item FROM f_split(@ve_lista_rut, ','))
	ELSE
		SELECT @vl_result = ISNULL(SUM(TOTAL_VENTA), 0)
		FROM INF_VENTAS_POR_MES I
		WHERE YEAR(FECHA_NOTA_VENTA) = @ve_ano
		AND MONTH(FECHA_NOTA_VENTA) = @ve_mes
		AND COD_USUARIO = @ve_cod_usuario
		AND ORDEN = 999	
		
	return @vl_result
END