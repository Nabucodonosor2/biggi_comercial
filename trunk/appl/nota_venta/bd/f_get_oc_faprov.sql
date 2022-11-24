---------------------f_get_oc_faprov------------------
CREATE FUNCTION f_get_oc_faprov(@ve_cod_nota_venta numeric, @ve_cod_orden_compra numeric)	
RETURNS numeric
AS
BEGIN
declare @vl_total_con_iva	numeric,
		@vl_monto_asig		numeric,
		@vl_result			numeric
		
	select @vl_total_con_iva = total_con_iva
	from orden_compra
	where cod_nota_venta = @ve_cod_nota_venta
	and cod_orden_compra = @ve_cod_orden_compra
	and tipo_orden_compra = 'NOTA_VENTA'
	
	set @vl_monto_asig = 0
	
	select @vl_monto_asig = isnull(sum(monto_asignado), 0)
	from item_faprov ifa, faprov fa
	where cod_doc = @ve_cod_orden_compra
	and cod_estado_faprov = 2	--aprobada
	and ifa.cod_faprov = fa.cod_faprov
	group by cod_doc
	
	set @vl_result = (@vl_monto_asig * 100)/@vl_total_con_iva	
	
	return @vl_result
end