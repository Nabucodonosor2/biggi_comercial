CREATE PROCEDURE spx_carga_item_nc
AS
BEGIN 

	insert into ITEM_NOTA_CREDITO
           (COD_NOTA_CREDITO
           ,ORDEN
           ,ITEM
           ,COD_PRODUCTO
           ,NOM_PRODUCTO
           ,CANTIDAD
           ,PRECIO
           ,COD_ITEM_DOC
           ,TIPO_DOC
           ,COD_TIPO_TE
           ,MOTIVO_TE)
	select nc.cod_nota_credito_sql --COD_NOTA_CREDITO
			,10					--ORDEN, no hay forma de ordenar los items ya que no existe campo similar en 4D
			,isnull(ITEM, '')
			,MODELO
			,DESCRIPCION
			,CANTIDAD
			,PRECIO
			,null --COD_ITEM_DOC
			,'4D' --TIPO_DOC
			,null --COD_TIPO_TE
			,null --MOTIVO_TE
	from aux_nota_credito nc, aux_item_nota_credito inc
	where nc.cod_nota_credito_sql is not null
		and nc.numero_nc = inc.numero_nc
END
go