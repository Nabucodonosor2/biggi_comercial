<?php
//require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once("class_print_anexo_softland.php");
require_once(dirname(__FILE__)."/FPDF/fpdf.php");

$cod_envio_softland	= $_REQUEST['cod_envio_softland'];


$pdf = new FPDF('P','pt','letter');
$print = new print_anexo_softland();


   $print->print_anexo($cod_envio_softland, $pdf);
   $pdf->Output("Traspaso Softland Anexo N° ".$cod_envio_softland.".pdf", 'I');

?>