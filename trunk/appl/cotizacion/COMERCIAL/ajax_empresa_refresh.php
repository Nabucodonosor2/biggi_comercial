<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$cod_empresa = $_REQUEST['cod_empresa'];

$wi = session::get('wi_cotizacion');
$wi->dws['dw_seguimiento_cotizacion']->controls['SC_COD_PERSONA']->retrieve($cod_empresa);
$wi->save_SESSION();

$result = array('labels' => $wi->dws['dw_seguimiento_cotizacion']->controls['SC_COD_PERSONA']->aLabels
				,'values' => $wi->dws['dw_seguimiento_cotizacion']->controls['SC_COD_PERSONA']->aValues);

for ($i=0; $i < count($result['labels']); $i++)
	$result['labels'][$i] = urlencode($result['labels'][$i]);  
print urlencode(json_encode($result));

?>