ALTER PROCEDURE [dbo].[spu_item_orden_compra](
					@ve_operacion					varchar(20)
					,@ve_cod_item_orden_compra		numeric
					,@ve_cod_orden_compra			numeric=NULL
					,@ve_orden						numeric=NULL
					,@ve_item						varchar(10)=NULL
					,@ve_cod_producto				varchar(100)=NULL
					,@ve_nom_producto				varchar(100)=NULL
					,@ve_cantidad					T_CANTIDAD=NULL
					,@ve_precio						T_PRECIO=NULL
					,@ve_cod_tipo_te				numeric		=NULL
					,@ve_motivo_te					varchar(100)=NULL
					,@ve_cod_item_nota_venta		numeric =NULL
					,@ve_rp_cliente_it				T_SI_NO='N')
AS
BEGIN
	if(@ve_operacion='INSERT')begin
		insert into item_orden_compra(
			cod_orden_compra,
			orden,
			item,
			cod_producto,
			nom_producto,
			cantidad,
			precio,
			cod_item_nota_venta,
			cod_tipo_te,
			motivo_te,
			rp_cliente_it)
		values		(
			@ve_cod_orden_compra,
			@ve_orden,
			@ve_item,
			@ve_cod_producto,
			@ve_nom_producto,
			@ve_cantidad,
			@ve_precio,
			@ve_cod_item_nota_venta,
			@ve_cod_tipo_te,
			@ve_motivo_te,
			@ve_rp_cliente_it) 
	end 
	else if (@ve_operacion='UPDATE')begin
		update item_orden_compra
		set cod_orden_compra	=	@ve_cod_orden_compra,
			orden				=	@ve_orden,
			item				=	@ve_item,
			cod_producto		=	@ve_cod_producto,
			nom_producto		=	@ve_nom_producto,
			cantidad			=	@ve_cantidad,
			precio				=	@ve_precio,
			cod_tipo_te			=	@ve_cod_tipo_te,
			motivo_te			=	@ve_motivo_te,
			rp_cliente_it		=	@ve_rp_cliente_it
		where cod_item_orden_compra	=	@ve_cod_item_orden_compra
	end	
	else if (@ve_operacion='DELETE')begin
		delete  item_orden_compra 
    	where cod_item_orden_compra = @ve_cod_item_orden_compra
	end 
END