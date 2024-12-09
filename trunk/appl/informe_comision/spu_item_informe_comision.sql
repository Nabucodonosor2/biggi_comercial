CREATE PROCEDURE spu_item_informe_comision  (@ve_operacion					varchar(20)
											,@ve_cod_item_informe_comision	numeric
											,@ve_cod_informe_comision		numeric = NULL
											,@ve_tipo_documento				varchar(100) = NULL
											,@ve_cod_doc_documento			numeric = NULL
											,@ve_total_neto					numeric = NULL
											,@ve_cod_doc					numeric = NULL
											,@ve_monto_comision				numeric = NULL)
AS
BEGIN
	IF(@ve_operacion='INSERT')BEGIN
		INSERT INTO ITEM_INFORME_COMISION(COD_INFORME_COMISION
										,TIPO_DOCUMENTO
										,COD_DOC_DOCUMENTO
										,TOTAL_NETO
										,COD_DOC
										,MONTO_COMISION)
								 VALUES(@ve_cod_informe_comision
									   ,@ve_tipo_documento
									   ,@ve_cod_doc_documento
									   ,@ve_total_neto
									   ,@ve_cod_doc
									   ,@ve_monto_comision)
	END 
END