<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_vendedor.php");

class wo_llamado extends w_output
{
   function wo_llamado()
   {   
   	  parent::w_base('llamado', $_REQUEST['cod_item_menu']);	
   	  $cod_usuario = $this->cod_usuario;
   	  
	  $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	  $sql = "select dbo.f_get_autoriza_menu($cod_usuario, '990210') ACCESO";
	  $result = $db->build_results($sql);
	  $acceso = $result[0]['ACCESO']; 

      $sql = "SELECT LL.COD_LLAMADO
					,CONVERT (VARCHAR(10), LL.FECHA_LLAMADO, 103)FECHA_LLAMADO
					,LL.FECHA_LLAMADO DATE_LLAMADO 
					,C.NOM_CONTACTO
					,CP.NOM_PERSONA
					,LLA.NOM_LLAMADO_ACCION
					,D.NOM_DESTINATARIO
					,LL.REALIZADO
					,LL.COD_DOC_REALIZADO
					,LL.TIPO_DOC_REALIZADO
					,LL.COD_LLAMADO_ACCION
					,LLD.COD_DESTINATARIO
					,CONVERT(VARCHAR(10),LL.FECHA_REALIZADO,103)+'  '+CONVERT(VARCHAR(5),LL.FECHA_REALIZADO,108) FECHA_REALIZADO
				FROM LLAMADO LL LEFT OUTER JOIN LLAMADO_DESTINATARIO LLD ON LLD.COD_LLAMADO = LL.COD_LLAMADO AND RESPONSABLE = 'S'
								LEFT OUTER JOIN DESTINATARIO D ON D.COD_DESTINATARIO = LLD.COD_DESTINATARIO
					,CONTACTO C, CONTACTO_PERSONA CP, LLAMADO_ACCION LLA
				WHERE C.COD_CONTACTO = LL.COD_CONTACTO
					AND CP.COD_CONTACTO_PERSONA = LL.COD_CONTACTO_PERSONA
					AND LLA.COD_LLAMADO_ACCION = LL.COD_LLAMADO_ACCION
					AND ('$acceso' = 'E' or dbo.f_llamado_tiene_acceso ($cod_usuario, LL.COD_LLAMADO) = 1)
                    AND LL.ORIGEN <> 'SPAM'
					ORDER BY LL.COD_LLAMADO DESC"; 
      
        // validad por usuario 
      	

      parent::w_output('llamado', $sql, $_REQUEST['cod_item_menu'], '0509');

	// headers
	$this->add_header(new header_num('COD_LLAMADO', 'LL.COD_LLAMADO', 'Nº'));
	$this->add_header($control = new header_date('FECHA_LLAMADO', 'FECHA_LLAMADO', 'Fecha'));
	$control->field_bd_order = 'DATE_LLAMADO';
	$this->add_header(new header_text('NOM_CONTACTO', 'NOM_CONTACTO', 'Empresa'));
	$this->add_header(new header_text('NOM_PERSONA', 'NOM_PERSONA', 'Contacto'));
	
	$sql_doc_realizado = "	SELECT 'COTIZACION' COD_DOC_REALIZADO, 'COTIZACION' TIPO_DOC_REALIZADO
							UNION 
							SELECT 'FACTURA' COD_DOC_REALIZADO, 'FACTURA' TIPO_DOC_REALIZADO
							UNION 
							SELECT 'NOTA VENTA' COD_DOC_REALIZADO, 'NOTA VENTA' TIPO_DOC_REALIZADO
							UNION 
							SELECT 'GUIA DESPACHO' COD_DOC_REALIZADO, 'GUIA DESPACHO' TIPO_DOC_REALIZADO";
	$this->add_header(new header_drop_down_string('TIPO_DOC_REALIZADO', 'LL.TIPO_DOC_REALIZADO', 'Tipo Doc', $sql_doc_realizado));
	$this->add_header(new header_num('COD_DOC_REALIZADO', 'LL.COD_DOC_REALIZADO', 'Cod Doc'));
		
	$sql_accion = "SELECT COD_LLAMADO_ACCION, NOM_LLAMADO_ACCION FROM LLAMADO_ACCION";
	$this->add_header(new header_drop_down('NOM_LLAMADO_ACCION', 'LL.COD_LLAMADO_ACCION', 'Acción', $sql_accion));
	//$this->add_header(new header_vendedor('INI_USUARIO', 'NV.COD_USUARIO_VENDEDOR1', 'V1'));
	//$sql_destinatario = "SELECT COD_DESTINATARIO, NOM_DESTINATARIO FROM DESTINATARIO";
	$this->add_header(new header_vendedor('NOM_DESTINATARIO', 'LLD.COD_DESTINATARIO', 'Responsable'));
	
	$sql_realizado= "SELECT 'S' REALIZADO
						,'SI' NOM_REALIZADO
					UNION 
					SELECT 'N' REALIZADO
						,'NO' NOM_REALIZADO";
	$this->add_header(new header_drop_down_string('REALIZADO', 'LL.REALIZADO', 'R', $sql_realizado));
	$this->add_header(new header_date('FECHA_REALIZADO', 'FECHA_REALIZADO', 'Fecha R.'));  	

   }
	function habilita_boton(&$temp, $boton, $habilita) {
		if ($boton=='add')
			$habilita = $habilita && $this->get_privilegio_opcion_usuario('990215', $this->cod_usuario)=='E';	// agregar llamado
		parent::habilita_boton($temp, $boton, $habilita);
	}
}
?>