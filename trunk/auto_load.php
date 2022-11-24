<?php
function auto_load($class_name) {
	if ($class_name=='print_dw_resultado_mes') {
		require_once(dirname(__FILE__)."/appl/inf_resultado/class_print_dw_resultado_mes.php");
   		return;
	}
	elseif ($class_name=='print_dw_resultado_resumen') {
		require_once(dirname(__FILE__)."/appl/inf_resultado/class_print_dw_resultado_resumen.php");
   		return;
	}
	elseif ($class_name=='rpt_cheque_talonario') {
		require_once(dirname(__FILE__)."/appl/cheque_renta/COMERCIAL/rpt_cheque_talonario.php");
   		return;
	}elseif ($class_name=='rpt_reverso_cheque_talonario') {
		require_once(dirname(__FILE__)."/appl/cheque_renta/COMERCIAL/rpt_reverso_cheque_talonario.php");
   		return;
	}elseif ($class_name=='rpt_santander') {
		require_once(dirname(__FILE__)."/appl/cheque_renta/COMERCIAL/rpt_santander.php");
   		return;
	}elseif ($class_name=='rpt_reverso_cheque_santander') {
		require_once(dirname(__FILE__)."/appl/cheque_renta/COMERCIAL/rpt_reverso_cheque_santander.php");
   		return;
	}
}
?>