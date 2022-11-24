alter PROCEDURE spx_revisa_biggi
/*
exec spx_revisa_biggi
*/
AS
BEGIN  
	declare
		@vl_asunto		varchar(100)
	if (1=1) begin
		set @vl_asunto = 'REVISA ' + convert(varchar, getdate(), 103)
		exec spu_envio_mail 'INSERT'
			,null
			,null
			,null
		 	,'mherrera@biggi.cl'
		 	,'REVISA'
		 	,'vmelo@integrasystem.cl'
		 	,'VM'
		 	,null
		 	,null
		 	,'mherrera@biggi.cl'
		 	,'MH'
		 	,@vl_asunto
		 	,'prueba'
		 	,''
		 	,null
		 	,1	--cualquiera FIJO
		 	
		 	
		 	
		 	
select cod_producto, count(*)
from PRODUCTO_LOCAL
group by cod_producto
having count(*) > 1
--debe dar vacio

select *
from producto
where cod_producto not in 
(select cod_producto from PRODUCTO_LOCAL)
--debe dar vacio


	end
END
