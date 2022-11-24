<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

// Esta clase es auxiliar y la idea es que todo el codigo que sea comun entre help_empresa.php y helpo_lista_empresa.php este en esta clase
class help_producto {	
		
	const MAX_LISTA = 100;		// Cantidad máxima de empresas que se deben cargar en la lista
	const K_DESCONTINUADO = 4;	// COD_TIPO_PRODUCTO descontinuado, solo par BIGGI !!! 
	
	static function una_row($fields, $row) {
		/* Arma un string con todo el contenido cuando se selecciona una empresa desde la lista o cuando la busqueda dio por
			 resultado 1 registro
		*/	
		$row['PRECIO'] = number_format($row['PRECIO'], 2, ',', '.');	// da formato al precio
		$resp = '';
		for ($j=0; $j<count($fields); $j++)
			$resp .= $row[$fields[$j]->name]."|";

		$resp = substr($resp, 0, strlen($resp) - 1);	// borra el ultimo caracter
		return $resp;	
	}
	static function find_producto($cod_producto, $nom_producto) {
		/* Esta funcion es llamada desde el ajax y busca las empresas que cumplan con los datos ingresados
			 Si el resultado de la busqueda es mayor a 1 retorna el sql para que se despliegue la ventana de selección de productos
		*/
		if ($cod_producto=='' && $nom_producto=='') {
			$resp = "0|";
			print urlencode($resp);	
			return;
		}
		
		//**** se debe ver como se maneja cuando debe dar el precio interno
		$sql_base = "SELECT  COD_PRODUCTO,
							NOM_PRODUCTO,
							0 PRECIO
					FROM 	PRODUCTO
					WHERE dbo.f_prod_valido (COD_PRODUCTO) = 'S'
							AND COD_TIPO_PRODUCTO <> ".self::K_DESCONTINUADO." and ";
		
		// busqueda exacta				
		if ($cod_producto!='')
			$sql = $sql_base."(COD_PRODUCTO like '".$cod_producto."')";
		elseif ($nom_producto!='')
			$sql = $sql_base."(NOM_PRODUCTO like '".$nom_producto."')";


		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$db->query($sql);
		$count_rows = $db->count_rows();
		if ($count_rows==0) {
			// Busqueda con % (contiente) %
			if ($cod_producto!='')
				$sql = $sql_base."(COD_PRODUCTO like '%".$cod_producto."%')";
			elseif ($nom_producto!='')
				$sql = $sql_base."(NOM_PRODUCTO like '%".$nom_producto."%')";
			$db->query($sql);
			$count_rows = $db->count_rows();
		}
		if ($count_rows==0)
			$resp = "0|";
		elseif ($count_rows==1) {
			$row = $db->get_row();
			$resp = "1|";
			$fields = $db->get_fields();
			$resp .= help_producto::una_row($fields, $row);
		}
		else
			$resp = $count_rows."|".$sql;
		
		print urlencode($resp);	
		
	}
	static function draw_htm_lista_producto($sql) {
		/* Arma el html con la lista de productos
			 $sql, es el sql con qle que se buscan las productos
			 
			 Solo carga un máximo de registro definido en $MAX_LSTA
		*/
	
		$temp = new Template_appl('help_lista_producto.htm');	
	
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($sql, self::MAX_LISTA);
		
		if ($db->count_rows() > self::MAX_LISTA)
			$temp->setVar("TIENE_MAS_REGISTROS", 'Se cargaron los primeros '.self::MAX_LISTA.' registos de un total de '.$db->count_rows().' registros.<br>Sea má especifico en los datos de búsqueda.');			
		else
			$temp->setVar("TIENE_MAS_REGISTROS", '');			
		
		$fields = $db->get_fields();
		for ($i=0 ; $i <count($result); $i++) {
			$returnValue = '1|'.urlencode(help_producto::una_row($fields, $result[$i]));
			$temp->gotoNext("PRODUCTO");		

			if ($i % 2 == 0)
				$temp->setVar("PRODUCTO.DW_TR_CSS", datawindow::css_claro);
			else
				$temp->setVar("PRODUCTO.DW_TR_CSS", datawindow::css_oscuro);

			for ($j=0; $j<count($fields); $j++) {
				if ($j==0)
					$temp->setVar("PRODUCTO.".$fields[$j]->name, '<a href="#" onClick="window.close(); returnValue=\''.$returnValue.'\'">'.$result[$i][$fields[$j]->name].'</a>');
				else
					$temp->setVar("PRODUCTO.".$fields[$j]->name, $result[$i][$fields[$j]->name]);			
			}
		}
		print $temp->toString();
	}
}
?>