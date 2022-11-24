-------------------- spu_llamado_accion---------------------------------
CREATE PROCEDURE [dbo].[spu_llamado_accion](@ve_operacion varchar(20), @ve_cod_llamado_accion numeric, @ve_nom_llamado_accion varchar(100)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
	insert into LLAMADO_ACCION (COD_LLAMADO_ACCION,NOM_LLAMADO_ACCION)
	values (@ve_cod_llamado_accion,@ve_nom_llamado_accion)
	end
	if (@ve_operacion='UPDATE') begin
	update LLAMADO_ACCION 
	set NOM_LLAMADO_ACCION = @ve_nom_llamado_accion
    where COD_LLAMADO_ACCION = @ve_cod_llamado_accion
	end
	else if (@ve_operacion='DELETE') begin
	delete LLAMADO_ACCION 
    where COD_LLAMADO_ACCION = @ve_cod_llamado_accion
	end		
END
go