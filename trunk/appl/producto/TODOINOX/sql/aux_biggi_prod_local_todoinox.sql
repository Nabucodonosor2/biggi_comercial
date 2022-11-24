
----------------Se ejecuta en todoinox 
alter procedure aux_biggi_prod_local_todoinox
as
begin 
	
	declare @vl_cod_producto varchar(30),
			@vl_count		 numeric 
	
	
	declare c_cursor cursor for
		SELECT COD_PRODUCTO 
		 FROM  BIGGI.DBO.PRODUCTO_LOCAL
		 WHERE COD_PRODUCTO NOT IN (SELECT COD_PRODUCTO  FROM PRODUCTO_LOCAL)
		 
	open c_cursor 
	fetch c_cursor into @vl_cod_producto
	while @@fetch_status = 0
	begin 
			print @vl_cod_producto;
			select @vl_count = count(COD_PRODUCTO)
			FROM PRODUCTO_LOCAL
			WHERE COD_PRODUCTO = @vl_cod_producto
			
			PRINT @vl_count;
			if (@vl_count = 0 ) 
				insert into PRODUCTO_LOCAL values (@vl_cod_producto , 'N')	
			
	
	fetch c_cursor into @vl_cod_producto
	end
	close c_cursor
	deallocate c_cursor
end 
