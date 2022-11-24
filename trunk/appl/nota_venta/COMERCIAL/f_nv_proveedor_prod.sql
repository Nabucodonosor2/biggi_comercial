---------------------f_nv_proveedor_prod------------------
ALTER FUNCTION f_nv_proveedor_prod(@ve_cod_producto varchar(100))	
RETURNS numeric
AS
BEGIN
	declare @vl_cod_empresa		numeric
	set @vl_cod_empresa = 0;
	
	select  top(1) @vl_cod_empresa = PP.COD_EMPRESA
    from	PRODUCTO_PROVEEDOR PP
          	,EMPRESA E
	where	PP.COD_EMPRESA = E.COD_EMPRESA
  	and	COD_PRODUCTO = @ve_cod_producto
  	and PP.ELIMINADO = 'N'
  	and COD_PRODUCTO not in  ('TE','F','E','I','VI')
	order by	ORDEN asc
   
	return @vl_cod_empresa;
end