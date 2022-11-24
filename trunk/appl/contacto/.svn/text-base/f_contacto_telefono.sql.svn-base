alter FUNCTION f_contacto_telefono(@ve_cod_contacto_persona numeric, @ve_telefono_record numeric)
RETURNS varchar(100)
AS
BEGIN
	DECLARE @vl_telefono varchar(100)
			
	SELECT @vl_telefono = telefono 
	FROM (SELECT r.*, row_number() over (order by r.cod_contacto_persona)  ROWNUMBER
			FROM (select cod_contacto_persona, telefono 
					from contacto_persona_telefono 
					where cod_contacto_persona = @ve_cod_contacto_persona) R
					) A
	WHERE ROWNUMBER = @ve_telefono_record

	RETURN @vl_telefono
END
go
