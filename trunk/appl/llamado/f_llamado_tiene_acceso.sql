alter function f_llamado_tiene_acceso(@ve_cod_usuario numeric, @ve_cod_llamado	numeric)
RETURNS numeric
AS
BEGIN
	if (dbo.f_get_autoriza_menu(@ve_cod_usuario, '990210')='E')	-- ver todos los llamados
		return 1
	
	declare
		@vl_count		numeric
			
	select @vl_count = count(*)	
	from LLAMADO_DESTINATARIO l, destinatario d
	where l.cod_llamado = @ve_cod_llamado
	  and d.cod_usuario = @ve_cod_usuario
	  and l.cod_destinatario = d.cod_destinatario
	  
	if (@vl_count  > 0)
		return 1
		
	return 0
END
