---------------------- spu_nota_venta_docs --------------------------
alter PROCEDURE spu_nota_venta_docs(@ve_operacion varchar(100)
									,@ve_cod_nota_venta_docs numeric
									,@ve_cod_nota_venta numeric=NULL
									,@ve_cod_usuario numeric=NULL
									,@ve_ruta_archivo varchar(500)=NULL
									,@ve_nom_archivo varchar(100)=NULL
									,@ve_obs text=NULL
									,@ve_es_oc varchar(1)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into NOTA_VENTA_DOCS
			(COD_NOTA_VENTA       
			,COD_USUARIO          
			,RUTA_ARCHIVO         
			,NOM_ARCHIVO          
			,FECHA_REGISTRO       
			,OBS
			,ES_OC
			)
		values		
			(@ve_cod_nota_venta 
			,@ve_cod_usuario 
			,@ve_ruta_archivo 
			,@ve_nom_archivo 
			,getdate()
			,@ve_obs
			,@ve_es_oc
			)
	end 	
	else if (@ve_operacion='UPDATE') begin
		update NOTA_VENTA_DOCS
		set OBS = @ve_obs,
			ES_OC = @ve_es_oc
		where COD_NOTA_VENTA_DOCS = @ve_cod_nota_venta_docs
	end
	else if (@ve_operacion='DELETE') begin
		delete NOTA_VENTA_DOCS
		where COD_NOTA_VENTA_DOCS = @ve_cod_nota_venta_docs
	end
END
go
