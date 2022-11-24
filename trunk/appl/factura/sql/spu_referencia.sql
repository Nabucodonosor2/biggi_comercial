-------------------- spu_referencia ---------------------------------
CREATE PROCEDURE spu_referencia(@ve_operacion			VARCHAR(20),
								@ve_cod_referencia		NUMERIC,
								@ve_fecha_referencia	DATETIME=NULL,
								@ve_doc_referencia		VARCHAR(100)=NULL,
								@ve_cod_tipo_referencia NUMERIC=NULL,
								@ve_cod_factura			NUMERIC=NULL)

AS
BEGIN
	if(@ve_operacion='INSERT')begin
		insert into REFERENCIA(
			   fecha_referencia,
			   doc_referencia,
			   cod_tipo_referencia,
			   cod_factura)
		values(@ve_fecha_referencia,
			   @ve_doc_referencia,
			   @ve_cod_tipo_referencia,
			   @ve_cod_factura)
	end
	else if (@ve_operacion='UPDATE')begin
		update REFERENCIA
		set fecha_referencia	= @ve_fecha_referencia,
		   doc_referencia		= @ve_doc_referencia,
		   cod_tipo_referencia	= @ve_cod_tipo_referencia,
		   cod_factura			= @ve_cod_factura
		where cod_referencia  = @ve_cod_referencia
	end	
	else if (@ve_operacion='DELETE')begin
		delete REFERENCIA 
		where cod_referencia  = @ve_cod_referencia
	end 
END
go
