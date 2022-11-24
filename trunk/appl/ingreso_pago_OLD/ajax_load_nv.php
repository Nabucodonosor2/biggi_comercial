 <?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_empresa = $_REQUEST['cod_empresa'];

$wi = session::get('wi_ingreso_pago');

$kl_estado_doc_impresa = 2;
$kl_estado_doc_enviada = 3;

$sql_original = $wi->dws['dw_ingreso_pago_nota_venta']->get_sql();
$sql = "select  'N' SELECCION_NV
				,0 COD_INGRESO_PAGO_FACTURA_NV
				,0 COD_INGRESO_PAGO_NV
				,NV.COD_NOTA_VENTA COD_DOC_NV
				--,F.NRO_FACTURA
				,convert(varchar(20), NV.FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA
				,NV.REFERENCIA REFERENCIA_NV
				,dbo.f_nv_get_saldo(NV.COD_NOTA_VENTA) SALDO_NV
				,dbo.f_nv_get_saldo(NV.COD_NOTA_VENTA) SALDO_C_NV
				,0 MONTO_ASIGNADO_NV
				,'none' DISPLAY_CERO_NV
				,0 MONTO_ASIGNADO_C_NV
				,dbo.f_nv_get_saldo(NV.COD_NOTA_VENTA)SALDO_T_NV
				--,TOTAL_CON_IVA
				--,f.cod_factura
				,U.INI_USUARIO
		from	NOTA_VENTA NV, USUARIO U
		where	NV.COD_EMPRESA = $cod_empresa and  
				dbo.f_nv_get_saldo(NV.COD_NOTA_VENTA) > 0 and
				U.COD_USUARIO = NV.COD_USUARIO_VENDEDOR1
		order by NV.COD_NOTA_VENTA asc";

$wi->dws['dw_ingreso_pago_nota_venta']->set_sql($sql);
$wi->dws['dw_ingreso_pago_nota_venta']->make_tabla_htm($wi->nom_template);
$wi->dws['dw_ingreso_pago_nota_venta']->set_sql($sql_original);
$wi->save_SESSION();
?>