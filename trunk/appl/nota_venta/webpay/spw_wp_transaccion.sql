CREATE PROCEDURE [dbo].[spw_wp_transaccion](@ve_operacion			varchar(100)	
											,@cod_wp_transaccion	NUMERIC			= null
											,@ve_cod_nota_venta 	NUMERIC			= null
											,@ve_monto				NUMERIC			= null
											,@ve_link_pago			VARCHAR(500)	= null
											,@ve_cod_empresa		NUMERIC(10)		= null
											,@ve_link_visible		VARCHAR(1)		= null
											,@ve_exito				VARCHAR(1)		= null)
AS
BEGIN
  DECLARE @vl_cod_wp_transaccion NUMERIC
	--validar si existen mas transacciones a nota venta y caducarlas "LINK_PAGO = N"
	if (@ve_operacion = 'INSERT')
  	begin
	  	UPDATE WP_TRANSACCION
	  	SET LINK_VISIBLE = 'N'
	  	WHERE COD_NOTA_VENTA = @ve_cod_nota_venta
	  
	  	insert into WP_TRANSACCION 
							(COD_NOTA_VENTA
							,FECHA_WP_TRANSACCION
							,MONTO_PAGO
							,FECHA_PAGO
							,LINK_PAGO
							,COD_EMPRESA
							,LINK_VISIBLE
							,EXITO)
				values (@ve_cod_nota_venta
						,GETDATE()
						,@ve_monto
						,NULL
						,@ve_link_pago
						,@ve_cod_empresa
						,@ve_link_visible
						,@ve_exito)
		
		set @vl_cod_wp_transaccion = @@identity
	
		UPDATE WP_TRANSACCION
		SET LINK_PAGO = 'http://accsisgb.biggi.cl/sysbiggi_new/comercial_biggi/biggi/trunk/appl/nota_venta/COMERCIAL/pago_nota_venta.php?param='+CONVERT(VARCHAR,@vl_cod_wp_transaccion)
		WHERE COD_WP_TRANSACCION = @vl_cod_wp_transaccion
	end	
	else if (@ve_operacion = 'UPDATE')
	begin
		UPDATE WP_TRANSACCION
	  	SET EXITO = 'S'
	  		,FECHA_PAGO = GETDATE()
	  	WHERE COD_WP_TRANSACCION = @cod_wp_transaccion
  	end
END 