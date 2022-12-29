CREATE FUNCTION f_get_dia_habil_tbk(@ve_fecha			datetime
								   ,@ve_tipo_documento	varchar(1))
RETURNS datetime
AS
BEGIN
	declare @vl_fecha_habil		datetime,
			@vl_dia_string		varchar(20),
			@vl_dias_parametro	numeric
	
	IF(@ve_tipo_documento = 'D')
		SET @vl_dias_parametro = dbo.f_get_parametro(82)
	ELSE IF(@ve_tipo_documento = 'C')
		SET @vl_dias_parametro = dbo.f_get_parametro(83)

	SELECT @vl_dia_string = DATENAME(WEEKDAY, DATEADD(day, @vl_dias_parametro, @ve_fecha))

	IF(@vl_dia_string = 'Sábado')
		SET @vl_fecha_habil = DATEADD(day, @vl_dias_parametro+2, @ve_fecha)
	ELSE IF(@vl_dia_string = 'Domingo')
		SET @vl_fecha_habil = DATEADD(day, @vl_dias_parametro+1, @ve_fecha)
	ELSE
		SET @vl_fecha_habil = DATEADD(day, @vl_dias_parametro, @ve_fecha)

	return @vl_fecha_habil
END