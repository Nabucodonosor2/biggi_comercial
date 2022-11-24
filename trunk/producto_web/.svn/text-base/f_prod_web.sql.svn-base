ALTER function [dbo].[f_prod_web](@ve_cod_producto varchar(100))
RETURNS	VARCHAR(1)
AS
BEGIN
	declare @vl_descontinuado   numeric,
			@vl_count    numeric,
			@vl_count2    numeric,
			@resultado    numeric,
			@valor				varchar(1)
				
	set @vl_descontinuado = 4	
	
	
	SELECT  @vl_count = COUNT(P.COD_PRODUCTO)
	FROM 	PRODUCTO P, 
			FAMILIA_PRODUCTO  FP
	WHERE	P.COD_PRODUCTO = FP.COD_PRODUCTO
			AND dbo.f_prod_valido (P.COD_PRODUCTO) = 'S'
			AND COD_TIPO_PRODUCTO <> @vl_descontinuado
			AND P.COD_PRODUCTO = @ve_cod_producto
	
	SELECT  @vl_count2 = COUNT(P.COD_PRODUCTO)
	FROM 	PRODUCTO P, 
			FAMILIA_ACCESORIO FA 
	WHERE	P.COD_PRODUCTO = FA.COD_PRODUCTO
			AND dbo.f_prod_valido (P.COD_PRODUCTO) = 'S'
			AND COD_TIPO_PRODUCTO <> @vl_descontinuado
			AND P.COD_PRODUCTO = @ve_cod_producto		
			
		set @resultado = @vl_count + @vl_count2	 
			 
			if(@resultado = 0)
				set @valor = 'N'
			else if(@resultado > 0)	
				set @valor = 'S' 


		return @valor 
END