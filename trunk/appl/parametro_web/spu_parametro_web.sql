-------------------- spu_parametro_web ---------------------------------
CREATE PROCEDURE spu_parametro_web(
					@row_per_page			varchar(100), 		
					@cant_page_visible		varchar(100))
AS
BEGIN
	update parametro set valor =  @row_per_page 	 where cod_parametro = 57;
	update parametro set valor =  @cant_page_visible where cod_parametro = 58;  	
END
