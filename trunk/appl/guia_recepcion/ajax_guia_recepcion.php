<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_doc = $_REQUEST['cod_doc'];
$tipo_doc = $_REQUEST['tipo_doc'];
$fx = $_REQUEST['fx'];

if($fx == 'valida_doc'){
    $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
    $field = "";

    if($tipo_doc == 1){
        $field = "COTIZACION";
    }else if($tipo_doc == 2){
        $field = "NOTA_VENTA";
    }else if($tipo_doc == 3){
        $field = "FACTURA";
    }else if($tipo_doc == 4){
        $field = "NOTA_CREDITO";
    }else if($tipo_doc == 5){
        $field = "GUIA_DESPACHO";
    }

    if($tipo_doc == 1 || $tipo_doc == 2){
        $sql = "SELECT COD_ESTADO_$field
                FROM $field
                WHERE COD_$field = $cod_doc";
    }else{
        $sql = "SELECT COD_ESTADO_DOC_SII
                FROM $field
                WHERE NRO_$field = $cod_doc";
    }
    $result = $db->build_results($sql);
    $row_count = $db->count_rows();

    if($row_count > 0 ){
        //verifica si esta anulada
        if($tipo_doc == 1){
            $estado = $result[0]['COD_ESTADO_'.$field];
            if($estado == 7){
                print 'ANULADO';
                return;
            }
            
        }else if($tipo_doc == 2){
            $estado = $result[0]['COD_ESTADO_'.$field];
            if($estado == 3){
                print 'ANULADO';
                return;
            }

        }else{
            $estado = $result[0]['COD_ESTADO_DOC_SII'];
            print $estado;
            if($estado == 4){
                print 'ANULADO';
                return;
            }

        }

        print 'EXISTE';
    }else
        print 'NO_EXISTE';
}
?>