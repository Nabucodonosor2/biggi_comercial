function select_1_producto(valores, record) {
	/* en valores[1] va el cod_producto
	   en valores[3] va el precio con formato
	*/
	var ajax = nuevoAjax();
	ajax.open("GET", "../entrada_bodega/COMERCIAL/ajax_obtiene_pmp.php?cod_producto="+valores[1], false);
	ajax.send(null);		

	var resp = URLDecode(ajax.responseText);
	valores[3] = resp; 
	
	 set_values_producto(valores, record);
}