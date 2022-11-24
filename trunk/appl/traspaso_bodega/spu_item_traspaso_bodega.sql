-------------------- spu_item_traspaso_bodega ---------------------------------
ALTER PROCEDURE [dbo].[spu_item_traspaso_bodega](@ve_operacion					varchar(20)
										,@ve_cod_item_traspaso_bodega	numeric
										,@ve_cod_traspaso_bodega		numeric=null
										,@ve_item 						varchar(20)=null
										,@ve_cod_producto 				varchar(30)=null
										,@ve_nom_producto 				varchar(100)=null
										,@ve_ct_stock 					T_CANTIDAD=null
                                        ,@ve_ct_traspasar				T_CANTIDAD=null
										)

AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into ITEM_TRASPASO_BODEGA
			(COD_TRASPASO_BODEGA
			,ITEM
			,COD_PRODUCTO
			,NOM_PRODUCTO
			,CT_STOCK
            ,CT_TRASPASAR
			)
		values 
			(@ve_cod_traspaso_bodega
			,@ve_item
			,@ve_cod_producto
			,@ve_nom_producto
			,@ve_ct_stock
            ,@ve_ct_traspasar
			)
	end
END
