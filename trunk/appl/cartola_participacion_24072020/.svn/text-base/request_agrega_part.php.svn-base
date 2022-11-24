<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_usuario_vendedor	= $_REQUEST['cod_usuario_vendedor'];
$temp = new Template_appl('request_agrega_part.htm');
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT dbo.f_get_parametro(29) MAX_ITEM";
$result = $db->build_results($sql);

$title_doc = "Participaciones";
$sql = "select COD_PARTICIPACION
                    ,convert(varchar(20),FECHA_PARTICIPACION, 103) FECHA_PARTICIPACION
                   ,COD_USUARIO_VENDEDOR
                   ,U.NOM_USUARIO  NOM_USUARIO_VENDEDOR
                   ,P.COD_ESTADO_PARTICIPACION
                   ,EP.NOM_ESTADO_PARTICIPACION
                   ,TOTAL_CON_IVA
                    ,REFERENCIA
                    ,'N' SELECCION
            from PARTICIPACION P  , USUARIO U , ESTADO_PARTICIPACION EP 
            where P.COD_USUARIO=$cod_usuario_vendedor
            and YEAR(FECHA_PARTICIPACION) =YEAR(GETDATE())
            AND P.COD_ESTADO_PARTICIPACION=2
            AND P.COD_USUARIO_VENDEDOR=U.COD_USUARIO
            AND P.COD_ESTADO_PARTICIPACION=EP.COD_ESTADO_PARTICIPACION
            AND P.COD_PARTICIPACION NOT in (select ICP.COD_PARTICIPACION from  ITEM_CARTOLA_PARTICIPACION ICP )
           ORDER by COD_PARTICIPACION ASC";

$temp->setVar("TITLE_DOC", $title_doc);
$temp->setVar("MAX_ITEM", '<input class="input_text" name="MAX_ITEM_0" id="MAX_ITEM_0" value="'.$result[0]['MAX_ITEM'].'" size="100" maxlength="100" type="hidden">');
//$temp->setVar("COD_NOTA_VENTA", '<input class="input_text" name="COD_NOTA_VENTA_0" id="COD_NOTA_VENTA_0" value="'.$cod_doc.'" size="100" maxlength="100" type="hidden">');

$dw = new datawindow($sql, 'ITEM_CARTOLA_PARTICIPACION');
$dw->add_control(new edit_text_hidden('COD_DOC'));
$dw->add_control($control = new edit_check_box('SELECCION','S','N',''));
$control->set_onChange("value_check(this); sel_count();");
$dw->add_control(new static_text('COD_PARTICIPACION'));
$dw->add_control(new static_text('FECHA_PARTICIPACION'));
$dw->add_control(new static_text('NOM_USUARIO_VENDEDOR'));
$dw->add_control(new static_text('NOM_ESTADO_PARTICIPACION'));
$dw->add_control(new static_num('TOTAL_CON_IVA'));
$dw->add_control(new static_text('REFERENCIA'));
/* $dw->add_control($control = new edit_num('CANTIDAD_X_FACTURAR', 5, 10, 2));
$control->set_onChange("value_check(this); sel_count();"); */
$dw->retrieve();

$dw->habilitar($temp, true);
print $temp->toString();
?>