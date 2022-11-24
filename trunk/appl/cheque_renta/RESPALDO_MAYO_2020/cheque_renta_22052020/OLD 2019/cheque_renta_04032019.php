<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
	$K_CLIENTE = session::get('K_CLIENTE');
require_once(dirname(__FILE__)."/".$K_CLIENTE."/rpt_pago_proveedor.php");
ini_set('max_execution_time', 900); //900 seconds = 15 minutes



$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);		
$temp = new Template_appl('cheque_renta.htm');	

$sql = "select   '' RUT_PROVEEDOR
				,'' DIG_VERIF
				,'' BOLETA_FACTURA
				,'' LISTA_FACTURA
                ,'' MONTO_TALONARIO
                ,CONVERT(VARCHAR,GETDATE(),103)FECHA_TALONARIO
                ,'' ES_NOMINATIVO_TALONARIO
				,'' ES_CRUZADO_TALONARIO
                ,'' DOCUMENTOS_TALONARIO";
$dw_datos = new datawindow($sql);

$dw_datos->add_control(new edit_date('FECHA_TALONARIO', 10, 10, false));
$dw_datos->add_control(new edit_num('MONTO_TALONARIO'));
$dw_datos->add_control(new edit_check_box('ES_NOMINATIVO_TALONARIO', 'S', 'N', 'Nominativo'));
$dw_datos->add_control(new edit_check_box('ES_CRUZADO_TALONARIO', 'S', 'N', 'Cruzado'));
$dw_datos->add_control(new edit_text('DOCUMENTOS_TALONARIO',60,500));

$entrable = true;
$dw_datos->insert_row();

$dw_datos->set_item(0,'FECHA_TALONARIO',$db->current_date());

if (isset($_POST['print_talonario'])){
    print_talonario();
}

function print_talonario() {
    $BENEFICIARIO =  $_POST['BENEFICIARIO'];
    $MONTO = $_POST['MONTO_TALONARIO_0'];
    $FECHA = str2date($_POST['FECHA_TALONARIO_0']);
    
    $arr_BENEFICIARIO = explode("/", $BENEFICIARIO);
    $PAGUESE_A = $arr_BENEFICIARIO[0];
    $RUT = $arr_BENEFICIARIO[1];
    
    $ES_NOMINATIVO = "";
    $ES_NOMINATIVO		= ($_POST['ES_NOMINATIVO_TALONARIO_0'] =='') ? "N" : $_POST['ES_NOMINATIVO_TALONARIO_0'];	

    $ES_CRUZADO = "";
	$ES_CRUZADO			= ($_POST['ES_CRUZADO_TALONARIO_0'] =='') ? "N" : $_POST['ES_CRUZADO_TALONARIO_0'];
    
    $DOCUMENTOS_TALONARIO = $_POST['DOCUMENTOS_TALONARIO_0'];
    
    $labels = array();
    $labels['strCOD_PAGO_FAPROV'] = '';
	$sql = "SELECT '$PAGUESE_A' PAGUESE_A
                    ,$MONTO MONTO_DOCUMENTO
                    ,'$ES_NOMINATIVO' +'-'+ '$ES_CRUZADO' AMBOS_TIPOS 
			        ,'$ES_NOMINATIVO' +'-'+ '$ES_CRUZADO' TIPO_NOMINATIVO
			        ,'$ES_NOMINATIVO' +'-'+ '$ES_CRUZADO' TIPO_CRUZADO
                    ,DATEPART(day, $FECHA)DIA_PAGO_DOCUMENTO
			        ,DATENAME(month, $FECHA) MES_PAGO_DOCUMENTO
			        ,DATEPART(year, $FECHA)ANO_PAGO_DOCUMENTO
                    ,'$RUT' RUT_COMPLETO
                    ,'$DOCUMENTOS_TALONARIO' DOCUMENTOS";
    
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$K_ROOT_DIR = session::get('K_ROOT_DIR');
	$rpt = new rpt_pago_proveedor($sql, $K_ROOT_DIR.'appl/cheque_renta/tipo_doc_cheque.xml', $labels, "Pago de Proveedores.pdf", 0);
	$rpt = new rpt_reverso_ch($sql, $K_ROOT_DIR.'appl/cheque_renta/reverso_cheque.xml', $labels, "Reverso_Cheque", 0);
	
				
}

function str2date($fecha_str, $hora_str='00:00:00') {
	if ($fecha_str=='')
		return 'null';
	// Entra la fecha en formato dd/mm/yyyy		
	if (K_TIPO_BD=='mssql') {
		$res = explode('/', $fecha_str);
		if (strlen($res[2])==2)
			$res[2] = '20'.$res[2];
		return sprintf("{ts '$res[2]-$res[1]-$res[0] $hora_str.000'}");
	}
	else if (K_TIPO_BD=='oci')
		return "to_date('$fecha_str $hora_str', 'dd/mm/yyyy hh24:mi:ss')";
	else
		base::error("base.str2date, no soportado para ".K_TIPO_BD);
}
	
$dw_datos->habilitar($temp, $entrable);
$menu = session::get('menu_appl');
$menu->draw($temp);
print $temp->toString();


?>