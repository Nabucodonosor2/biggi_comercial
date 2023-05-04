<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
$K_CLIENTE = session::get('K_CLIENTE');
require_once(dirname(__FILE__)."/".$K_CLIENTE."/rpt_cheque_talonario.php");
require_once(dirname(__FILE__)."/".$K_CLIENTE."/rpt_santander.php");

//ini_set('max_execution_time', 900); //900 seconds = 15 minutes
//ini_set('display_errors', 'On');

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);		
$temp = new Template_appl('cheque_renta.htm');	
/**TALONARIO Y SANTANDER OCUPAN LOS MISMOS CAMPOS**/

$sql = "select   '' RUT_PROVEEDOR
				,'' DIG_VERIF
				,'' AUX_RUT
				,'' BOLETA_FACTURA
				,'' LISTA_FACTURA
                ,'' MONTO_TALONARIO
                ,CONVERT(VARCHAR,GETDATE(),103)FECHA_TALONARIO
                ,'' ES_NOMINATIVO_TALONARIO
				,'' ES_CRUZADO_TALONARIO
                ,'' DOCUMENTOS_TALONARIO
                ,'' BANCO
                ,'' NRO_CHEQUE
                ,'' C_CONT
                ,'' NRO_FACTURA1
                ,'' NRO_FACTURA2
                ,'' NRO_FACTURA3
                ,'' NRO_FACTURA4
                ,'' NRO_FACTURA5
                ,'' NRO_FACTURA6
                ,'' N_C1
                ,'' N_C2
                ,'' N_C3
                ,'' N_C4
                ,'' N_C5
                ,'' N_C6
                ,'' REF1
                ,'' REF2
                ,'' REF3
                ,'' REF4
                ,'' REF5
                ,'' REF6
                ,'' MONTO_FA1
                ,'' MONTO_FA2
                ,'' MONTO_FA3
                ,'' MONTO_FA4
                ,'' MONTO_FA5
                ,'' MONTO_FA6
                ,'' RES1
                ,'' RES2
                ,'' RES3
                ,'' RES4
                ,'' RES5
                ,'' RES6
                ,'' OC1
                ,'' OC2
                ,'' OC3
                ,'' OC4
                ,'' OC5
                ,'' OC6
                ,'' REFERENCIA_GRAL";
$dw_datos = new datawindow($sql);

$dw_datos->add_control(new edit_date('FECHA_TALONARIO', 10, 10, false));
$dw_datos->add_control(new edit_num('MONTO_TALONARIO'));
$dw_datos->add_control(new edit_text('AUX_RUT'));
$dw_datos->add_control(new edit_check_box('ES_NOMINATIVO_TALONARIO', 'S', 'N', 'Nominativo'));
$dw_datos->add_control(new edit_check_box('ES_CRUZADO_TALONARIO', 'S', 'N', 'Cruzado'));
$dw_datos->add_control(new edit_check_box('ES_NOMINATIVO_SANTANDER', 'S', 'N', 'Nominativo'));
$dw_datos->add_control(new edit_check_box('ES_CRUZADO_SANTANDER', 'S', 'N', 'Cruzado'));
$dw_datos->add_control(new edit_text('DOCUMENTOS_TALONARIO',100,500));

$dw_datos->add_control(new edit_num('NRO_CHEQUE'));
$dw_datos->add_control(new edit_num('C_CONT'));
$dw_datos->add_control(new edit_text('BANCO',54,100));
$dw_datos->add_control(new edit_text('NRO_FACTURA1',10,100));
$dw_datos->add_control(new edit_text('NRO_FACTURA2',10,100));
$dw_datos->add_control(new edit_text('NRO_FACTURA3',10,100));
$dw_datos->add_control(new edit_text('NRO_FACTURA4',10,100));
$dw_datos->add_control(new edit_text('NRO_FACTURA5',10,100));
$dw_datos->add_control(new edit_text('NRO_FACTURA6',10,100));
$dw_datos->add_control(new edit_text('N_C1',10,100));
$dw_datos->add_control(new edit_text('N_C2',10,100));
$dw_datos->add_control(new edit_text('N_C3',10,100));
$dw_datos->add_control(new edit_text('N_C4',10,100));
$dw_datos->add_control(new edit_text('N_C5',10,100));
$dw_datos->add_control(new edit_text('N_C6',10,100));
$dw_datos->add_control(new edit_text('REF1',40,100));
$dw_datos->add_control(new edit_text('REF2',40,100));
$dw_datos->add_control(new edit_text('REF3',40,100));
$dw_datos->add_control(new edit_text('REF4',40,100));
$dw_datos->add_control(new edit_text('REF5',40,100));
$dw_datos->add_control(new edit_text('REF6',40,100));
$dw_datos->add_control(new edit_num('MONTO_FA1'));
$dw_datos->add_control(new edit_num('MONTO_FA2'));
$dw_datos->add_control(new edit_num('MONTO_FA3'));
$dw_datos->add_control(new edit_num('MONTO_FA4'));
$dw_datos->add_control(new edit_num('MONTO_FA5'));
$dw_datos->add_control(new edit_num('MONTO_FA6'));
$dw_datos->add_control(new edit_num('RES1'));
$dw_datos->add_control(new edit_num('RES2'));
$dw_datos->add_control(new edit_num('RES3'));
$dw_datos->add_control(new edit_num('RES4'));
$dw_datos->add_control(new edit_num('RES5'));
$dw_datos->add_control(new edit_num('RES6'));
$dw_datos->add_control(new edit_text('OC1',20,100));
$dw_datos->add_control(new edit_text('OC2',20,100));
$dw_datos->add_control(new edit_text('OC3',20,100));
$dw_datos->add_control(new edit_text('OC4',20,100));
$dw_datos->add_control(new edit_text('OC5',20,100));
$dw_datos->add_control(new edit_text('OC6',20,100));
$dw_datos->add_control(new edit_text('REFERENCIA_GRAL',100,100));


$entrable = true;
$dw_datos->insert_row();

$dw_datos->set_item(0,'FECHA_TALONARIO',$db->current_date());

$dw_datos->set_item(0,'ES_NOMINATIVO_TALONARIO','S');
$dw_datos->set_item(0,'ES_CRUZADO_TALONARIO','S');

$dw_datos->set_item(0,'BANCO','SANTANDER');

if (isset($_POST['print_talonario'])){
    print_cheque('TALONARIO');
}
if (isset($_POST['print_tipo_dos'])){
    print_cheque('TALONARIO', 'T');
}
if (isset($_POST['print_santander'])){
    print_cheque('SANTANDER');
}

function print_cheque($filtro, $reverse = 'F') {
    $BENEFICIARIO =  $_POST['BENEFICIARIO']; 
    $MONTO = $_POST['MONTO_TALONARIO_0'];
    $FECHA = str2date($_POST['FECHA_TALONARIO_0']);
    
    $arr_BENEFICIARIO = explode("/", $BENEFICIARIO);
    $PAGUESE_A = $BENEFICIARIO;//$arr_BENEFICIARIO[0];
    //$RUT = $arr_BENEFICIARIO[1];
    $RUT = $_POST['AUX_RUT_0'];
    
	//SE SOBRE ESCRIBE VALOR ORIGINAL DE "$PAGUESE_A" PARA QUE USE VALOR DEL INPUT Y NO DEL DROP DOWN
    $PAGUESE_A =  $_POST['BENEFICIARIO_N'];
	//SE SOBRE ESCRIBE VALOR ORIGINAL DE "$RUT" PARA QUE USE VALOR DEL INPUT Y NO DEL DROP DOWN
    $RUT =  $_POST['BENEFICIARIO_R'];
	
    $DOCUMENTOS_TALONARIO = $_POST['DOCUMENTOS_TALONARIO_0'];
    
    $labels = array();
    $labels['strCOD_PAGO_FAPROV'] = '';
	
    
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$K_ROOT_DIR = session::get('K_ROOT_DIR');
	if($filtro == 'TALONARIO'){
	    $ES_NOMINATIVO = "";
        $ES_NOMINATIVO		= ($_POST['ES_NOMINATIVO_TALONARIO_0'] =='') ? "N" : $_POST['ES_NOMINATIVO_TALONARIO_0'];	
    
        $ES_CRUZADO = "";
    	$ES_CRUZADO			= ($_POST['ES_CRUZADO_TALONARIO_0'] =='') ? "N" : $_POST['ES_CRUZADO_TALONARIO_0'];
	
	    $sql = "set LANGUAGE Spanish 
                    SELECT '$PAGUESE_A' PAGUESE_A
                    ,$MONTO MONTO_DOCUMENTO
                    ,'$ES_NOMINATIVO' +'-'+ '$ES_CRUZADO' AMBOS_TIPOS 
			        ,'$ES_NOMINATIVO' +'-'+ '$ES_CRUZADO' TIPO_NOMINATIVO
			        ,'$ES_NOMINATIVO' +'-'+ '$ES_CRUZADO' TIPO_CRUZADO
                    ,DATEPART(day, $FECHA)DIA_PAGO_DOCUMENTO
			        ,DATENAME(month, $FECHA) MES_PAGO_DOCUMENTO
			        ,DATEPART(year, $FECHA)ANO_PAGO_DOCUMENTO
                    ,'$RUT' RUT_COMPLETO
                    ,'$DOCUMENTOS_TALONARIO' DOCUMENTOS";
	   
        if($reverse == 'F')
	        new rpt_cheque_talonario($sql, $K_ROOT_DIR.'appl/cheque_renta/tipo_doc_cheque.xml', $labels, "Talonario_1.pdf", 0);
        else
            print " <script>window.open('../cheque_renta/COMERCIAL/rpt_cheque_talonario_reverse.php?sql=".base64_encode($sql)."')</script>";
            
	    new rpt_reverso_cheque_talonario($sql, $K_ROOT_DIR.'appl/cheque_renta/reverso_cheque.xml', $labels, "Reverso_Cheque", 0);

	}else if($filtro == 'SANTANDER'){
	   $ES_NOMINATIVO = "";
       $ES_NOMINATIVO		= ($_POST['ES_NOMINATIVO_SANTANDER_0'] =='') ? "N" : $_POST['ES_NOMINATIVO_SANTANDER_0'];	

       $ES_CRUZADO = "";
	   $ES_CRUZADO			= ($_POST['ES_CRUZADO_SANTANDER_0'] =='') ? "N" : $_POST['ES_CRUZADO_SANTANDER_0'];
	
	   $BANCO              =  $_POST['BANCO_0'];
	   $NRO_CHEQUE         =  $_POST['NRO_CHEQUE_0'];
	   $C_CONT             =  $_POST['C_CONT_0'];
	   $NRO_FACTURA1       =  $_POST['NRO_FACTURA1_0'];
	   $NRO_FACTURA2       =  $_POST['NRO_FACTURA2_0'];
	   $NRO_FACTURA3       =  $_POST['NRO_FACTURA3_0'];
	   $NRO_FACTURA4       =  $_POST['NRO_FACTURA4_0'];
	   $NRO_FACTURA5       =  $_POST['NRO_FACTURA5_0'];
	   $NRO_FACTURA6       =  $_POST['NRO_FACTURA6_0'];
	   $N_C1               =  $_POST['N_C1_0'];
	   $N_C2               =  $_POST['N_C2_0'];
	   $N_C3               =  $_POST['N_C3_0'];
	   $N_C4               =  $_POST['N_C4_0'];
	   $N_C5               =  $_POST['N_C5_0'];
	   $N_C6               =  $_POST['N_C6_0'];
	   $REF1               =  $_POST['REF1_0'];
	   $REF2               =  $_POST['REF2_0'];
	   $REF3               =  $_POST['REF3_0'];
	   $REF4               =  $_POST['REF4_0'];
	   $REF5               =  $_POST['REF5_0'];
	   $REF6               =  $_POST['REF6_0'];
	   $MONTO_FA1          =  ($_POST['MONTO_FA1_0'] =='') ? "" : number_format ($_POST['MONTO_FA1_0'],0,",","." );	
	   $MONTO_FA2          =  ($_POST['MONTO_FA2_0'] =='') ? "" : number_format ($_POST['MONTO_FA2_0'],0,",","." );
	   $MONTO_FA3          =  ($_POST['MONTO_FA3_0'] =='') ? "" : number_format ($_POST['MONTO_FA3_0'],0,",","." );
	   $MONTO_FA4          =  ($_POST['MONTO_FA4_0'] =='') ? "" : number_format ($_POST['MONTO_FA4_0'],0,",","." );
	   $MONTO_FA5          =  ($_POST['MONTO_FA5_0'] =='') ? "" : number_format ($_POST['MONTO_FA5_0'],0,",","." );
	   $MONTO_FA6          =  ($_POST['MONTO_FA6_0'] =='') ? "" : number_format ($_POST['MONTO_FA6_0'],0,",","." );
	   $RES1               =  $_POST['RES1_0'];
	   $RES2               =  $_POST['RES2_0'];
	   $RES3               =  $_POST['RES3_0'];
	   $RES4               =  $_POST['RES4_0'];
	   $RES5               =  $_POST['RES5_0'];
	   $RES6               =  $_POST['RES6_0'];
	   $OC1                =  $_POST['OC1_0'];
	   $OC2                =  $_POST['OC2_0'];
	   $OC3                =  $_POST['OC3_0'];
	   $OC4                =  $_POST['OC4_0'];
	   $OC5                =  $_POST['OC5_0'];
	   $OC6                =  $_POST['OC6_0'];
	   $REFERENCIA_GRAL    =  $_POST['REFERENCIA_GRAL_0'];
	   
	   $MONTO1          =  ($_POST['MONTO_FA1_0'] =='') ? 0 : $_POST['MONTO_FA1_0'];	
	   $MONTO2          =  ($_POST['MONTO_FA2_0'] =='') ? 0 : $_POST['MONTO_FA2_0'];
	   $MONTO3          =  ($_POST['MONTO_FA3_0'] =='') ? 0 : $_POST['MONTO_FA3_0'];
	   $MONTO4          =  ($_POST['MONTO_FA4_0'] =='') ? 0 : $_POST['MONTO_FA4_0'];
	   $MONTO5          =  ($_POST['MONTO_FA5_0'] =='') ? 0 : $_POST['MONTO_FA5_0'];
	   $MONTO6          =  ($_POST['MONTO_FA6_0'] =='') ? 0 : $_POST['MONTO_FA6_0'];
	   
	   $TOTAL = $MONTO1 + $MONTO2 + $MONTO3 + $MONTO4 + $MONTO5 + $MONTO6;
	   
	   $sql = "set LANGUAGE Spanish
                    SELECT '$PAGUESE_A' PAGUESE_A
                    ,$MONTO MONTO_DOCUMENTO
                    ,'$ES_NOMINATIVO' +'-'+ '$ES_CRUZADO' AMBOS_TIPOS 
			        ,'$ES_NOMINATIVO' +'-'+ '$ES_CRUZADO' TIPO_NOMINATIVO
			        ,'$ES_NOMINATIVO' +'-'+ '$ES_CRUZADO' TIPO_CRUZADO
                    ,DATEPART(day, $FECHA)DIA_PAGO_DOCUMENTO
			        ,DATENAME(month, $FECHA) MES_PAGO_DOCUMENTO
			        ,DATEPART(year, $FECHA)ANO_PAGO_DOCUMENTO
                    ,convert(varchar(20), $FECHA, 103) +'  '+ convert(varchar(20), $FECHA, 8) FECHA_COMPLETA
                    ,'$RUT' RUT_COMPLETO
                    ,'$DOCUMENTOS_TALONARIO' DOCUMENTOS
                    ,'$BANCO' BANCO
                    ,'$NRO_CHEQUE' NRO_CHEQUE
                    ,'$C_CONT' C_CONT
                    ,'$NRO_FACTURA1' NRO_FACTURA1
                    ,'$NRO_FACTURA2' NRO_FACTURA2
                    ,'$NRO_FACTURA3' NRO_FACTURA3
                    ,'$NRO_FACTURA4' NRO_FACTURA4
                    ,'$NRO_FACTURA5' NRO_FACTURA5
                    ,'$NRO_FACTURA6' NRO_FACTURA6
                    ,'$N_C1' N_C1
                    ,'$N_C2' N_C2
                    ,'$N_C3' N_C3
                    ,'$N_C4' N_C4
                    ,'$N_C5' N_C5
                    ,'$N_C6' N_C6
                    ,'$REF1' REF1
                    ,'$REF2' REF2
                    ,'$REF3' REF3
                    ,'$REF4' REF4
                    ,'$REF5' REF5
                    ,'$REF6' REF6
                    ,'$MONTO_FA1' MONTO_FA1
                    ,'$MONTO_FA2' MONTO_FA2
                    ,'$MONTO_FA3' MONTO_FA3
                    ,'$MONTO_FA4' MONTO_FA4
                    ,'$MONTO_FA5' MONTO_FA5
                    ,'$MONTO_FA6' MONTO_FA6
                    ,'$RES1' RES1
                    ,'$RES2' RES2
                    ,'$RES3' RES3
                    ,'$RES4' RES4
                    ,'$RES5' RES5
                    ,'$RES6' RES6
                    ,'$OC1' OC1
                    ,'$OC2' OC2
                    ,'$OC3' OC3
                    ,'$OC4' OC4
                    ,'$OC5' OC5
                    ,'$OC6' OC6
                    ,'$REFERENCIA_GRAL' REFERENCIA_GRAL
                    ,$TOTAL TOTAL";
        
        $rpt = new rpt_santander($sql, $K_ROOT_DIR.'appl/cheque_renta/cheque_santander.xml', $labels, "Santander.pdf", 0);
	    $rpt = new rpt_reverso_cheque_santander($sql, $K_ROOT_DIR.'appl/cheque_renta/reverso_cheque.xml', $labels, "Reverso_Cheque", 0);    
	}	
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