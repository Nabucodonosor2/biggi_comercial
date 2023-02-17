<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$cod_nota_venta = $_REQUEST["cod_nota_venta"];
$comision       = $_REQUEST["comision"];
$cod_usuario    = $_REQUEST["cod_usuario"];

if($comision == '')
    $comision == NULL;

$temp = new Template_appl('dlg_anticipo_op.htm');
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "select NULL PRECIO_ANTICIPO
              ,ISNULL($comision, 0)  COMISION
              ,ISNULL(SUM(TOTAL_NETO), 0) SUM_TOTAL_NETO
              ,$cod_usuario COD_USUARIO
              ,$cod_nota_venta COD_NOTA_VENTA
        from ORDEN_PAGO
        WHERE COD_NOTA_VENTA = $cod_nota_venta
        AND ES_ANTICIPO = 'S'";

$dw = new datawindow($sql);
$dw->add_control(new edit_text_hidden('COMISION'));
$dw->add_control(new edit_text_hidden('SUM_TOTAL_NETO'));
$dw->add_control(new edit_text_hidden('COD_USUARIO'));
$dw->add_control(new edit_text_hidden('COD_NOTA_VENTA'));
$dw->retrieve();

$dw->habilitar($temp, true);
print $temp->toString();
?>