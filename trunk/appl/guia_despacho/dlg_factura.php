<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once("../../appl.ini");

$cod_nota_venta   = $_REQUEST['cod_nota_venta'];
$nro_factura      = $_REQUEST['nro_factura'];
$nro_factura      = str_replace("|", ",", $_REQUEST['nro_factura']);
$temp = new Template_appl('dlg_factura.htm');
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
//print '$nro_factura='.$nro_factura;	
$sql = "SELECT 'N' SELECCIONAR
              ,COD_FACTURA
              ,NRO_FACTURA
              ,REFERENCIA
        FROM FACTURA
        WHERE COD_DOC = $cod_nota_venta
        AND TIPO_DOC = 'NOTA_VENTA'
        AND COD_ESTADO_DOC_SII = 3";

if($nro_factura <> ""){
      $sql .= " AND COD_FACTURA NOT IN ($nro_factura)
                UNION
                SELECT 'S' SELECCIONAR
                        ,COD_FACTURA
                        ,NRO_FACTURA
                        ,REFERENCIA
                FROM FACTURA
                WHERE COD_DOC = $cod_nota_venta
                AND TIPO_DOC = 'NOTA_VENTA'
                AND COD_ESTADO_DOC_SII = 3
                AND COD_FACTURA IN ($nro_factura)
                ORDER BY COD_FACTURA ASC";   
}else
      $sql .= " ORDER BY COD_FACTURA ASC";

$dw = new datawindow($sql, 'DW_FACTURAS');
$dw->add_control(new edit_check_box('SELECCIONAR', 'S', 'N'));
$dw->add_control(new edit_text_hidden('COD_FACTURA'));
$dw->retrieve();

$dw->habilitar($temp, true);
print $temp->toString();
?>