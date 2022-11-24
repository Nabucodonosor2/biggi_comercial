CREATE PROCEDURE spx_relaciona_nc_fa
AS
BEGIN  
	declare
		@cod_factura		numeric
		,@cod_producto		varchar(100)
		,@count				numeric
		,@cod_item_nota_credito	numeric
		,@cod_item_factura		numeric
		,@cantidad		T_CANTIDAD

	declare c_item_nc cursor for 
	select nc.cod_doc
			,i.cod_producto
			,i.cod_item_nota_credito
			,i.cantidad
	from item_nota_credito i, nota_credito nc
	where i.tipo_doc = '4D'
	  and nc.cod_nota_credito = i.cod_nota_credito
	  and nc.cod_tipo_nota_credito = 1

	open c_item_nc
	fetch c_item_nc into @cod_factura,@cod_producto,@cod_item_nota_credito, @cantidad
	while @@fetch_status = 0 begin
		select @count=count(*) 
		from item_factura
		where cod_factura = @cod_factura
		  and cod_producto = @cod_producto

		if (@count=1) begin
			select @cod_item_factura=cod_item_factura
			from item_factura
			where cod_factura = @cod_factura
			  and cod_producto = @cod_producto

			update item_nota_credito
			set tipo_doc = 'ITEM_FACTURA'
				,cod_item_doc = @cod_item_factura
				,motivo_te='x'
			where cod_item_nota_credito = @cod_item_nota_credito
		end

		fetch c_item_nc into @cod_factura,@cod_producto,@cod_item_nota_credito, @cantidad
	end
	close c_item_nc
	deallocate c_item_nc
END
go