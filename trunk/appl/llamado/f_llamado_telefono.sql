alter FUNCTION f_llamado_telefono(@ve_codigo numeric, @ve_tipo varchar(10))
RETURNS varchar(2000)
AS
BEGIN
	declare @vl_telefono varchar(2000)
			,@vc_telefono varchar(100)
BEGIN	
	set @vl_telefono = ''

	if (@ve_tipo = 'EMPRESA') 
		declare c_telefono cursor for	
		select telefono from contacto_telefono
		where cod_contacto = @ve_codigo
	else if(@ve_tipo = 'PERSONA')
		declare c_telefono cursor for	
		select telefono from contacto_persona_telefono
		where cod_contacto_persona = @ve_codigo

	open c_telefono 
	fetch c_telefono into @vc_telefono
	while @@fetch_status = 0 
	begin
		set @vl_telefono = @vl_telefono + @vc_telefono + ', '
		fetch c_telefono into @vc_telefono
	end
	close c_telefono
	deallocate c_telefono
	
	if (Len(@vl_telefono)> 0)
		set @vl_telefono = SUBSTRING(@vl_telefono, 1, Len(@vl_telefono) - 1 )
			
	return @vl_telefono
END
END
go
