function validate() {
	var vl_cod_estado = document.getElementById('COD_ESTADO_SOLICITUD_COMPRA_0').value;
	if (vl_cod_estado==2) { // confirmada
		var vl_sel_armado = false;
		var vl_tabla = document.getElementById('ITEM_SOLICITUD_COMPRA');
		var aTR = vl_tabla.getElementsByTagName("tr");
		for (var i=0; i<aTR.length; i++) {
			var vl_record = get_num_rec_field(aTR[i].id); 
			if (document.getElementById('IT_ARMA_COMPUESTO_' + vl_record).checked) {
				vl_sel_armado = true;
			}

			if (document.getElementById('IT_GENERA_COMPRA_' + vl_record).checked) {
				var vl_cod_empresa = document.getElementById('IT_COD_EMPRESA_' + vl_record).value;
				if (vl_cod_empresa=='') {
					alert('Debe indicar el proveedor quien se compra');
					document.getElementById('IT_COD_EMPRESA_' + vl_record).focus();
					return false;
				}
			}
		}
		if (!vl_sel_armado) {
			alert('Debe seleccionar quien arma el equipo.');
			return false;
		}
	}
	return true;
	
	
}
function select_1_producto(valores, record) {
	// Se reimpleneta es funcion para adionar codigo
	 set_values_producto(valores, record);

	 /////////////
	var vl_cantidad = to_num(document.getElementById('CANTIDAD_0').value);
	if (vl_cantidad=='')
		vl_cantidad = 0;

	busca_items(false);
}
function busca_items(ve_siempre_terminado) {
	// borrar los items	 
	var aTR = get_TR('ITEM_SOLICITUD_COMPRA');
	for (i=0; i<aTR.length; i++)
		del_line(aTR[i].id, 'solicitud_compra'); 

 	// agrega a los items los productos relacionados
	var vl_cod_producto = document.getElementById('COD_PRODUCTO_0').value;
	if (vl_cod_producto=='')
		return;
	
	/////////////
	var vl_cantidad = to_num(document.getElementById('CANTIDAD_0').value);
	if (vl_cantidad=='')
		vl_cantidad = 0;
		
	// Si viene en true siempre lo maneja como equipo terminado, sino lo maneja como compuesto si es compuesto
	if (ve_siempre_terminado) 
		var result = new Array();
	else {
		var ajax = nuevoAjax();
		ajax.open("GET", "ajax_producto_compuesto.php?cod_producto="+URLEncode(vl_cod_producto), false);
		ajax.send(null);
		var resp = URLDecode(ajax.responseText);
		var result = eval("(" + resp + ")");

		if(result.length > 0){
			// es compuesto
			// Habilita la opcion de comprar como compuesto
			document.getElementById("COMPUESTO_0").removeAttribute("disabled");
			document.getElementById("COMPUESTO_0").checked = true;
			document.getElementById("TERMINADO_0").checked = false;
		}
		else {
			// NO es compuesto
			// DESHabilita la opcion de comprar como compuesto
			document.getElementById("COMPUESTO_0").setAttribute("disabled", 0);
			document.getElementById("COMPUESTO_0").checked = false;
			document.getElementById("TERMINADO_0").checked = true;
		}
	}
	
	if(result.length > 0){
		// es compuesto
		// Habilita la opcion de comprar como compuesto
		document.getElementById("COMPUESTO_0").removeAttribute("disabled");
		document.getElementById("COMPUESTO_0").checked = true;
		document.getElementById("TERMINADO_0").checked = false;
		 
		for (var i=0; i< result.length; i++) {
			var vl_row = add_line('ITEM_SOLICITUD_COMPRA', 'solicitud_compra');
			document.getElementById('IT_COD_PRODUCTO_' + vl_row).innerHTML = result[i]['COD_PRODUCTO_HIJO'];
			document.getElementById('IT_COD_PRODUCTO_H_' + vl_row).value = result[i]['COD_PRODUCTO_HIJO'];
			document.getElementById('IT_NOM_PRODUCTO_' + vl_row).innerHTML = URLDecode(result[i]['NOM_PRODUCTO']);
			document.getElementById('IT_CANTIDAD_' + vl_row).value = result[i]['CANTIDAD'];
			document.getElementById('IT_CANTIDAD_TOTAL_' + vl_row).innerHTML = vl_cantidad * result[i]['CANTIDAD'];
			document.getElementById('IT_CANTIDAD_TOTAL_H_' + vl_row).value = vl_cantidad * result[i]['CANTIDAD'];
			document.getElementById('IT_GENERA_COMPRA_' + vl_row).checked = (result[i]['GENERA_COMPRA']=='S'); 
	
			var vl_cod_empresa = document.getElementById('IT_COD_EMPRESA_' + vl_row);
			vl_cod_empresa.length = 0;
			// item vacio
			var vl_opcion = document.createElement("option");
			vl_opcion.value = '';
			vl_opcion.innerHTML = '';
			vl_cod_empresa.appendChild(vl_opcion);
			for (var j=0; j < result[i]['COD_EMPRESA'].length; j++) {
				var vl_opcion = document.createElement("option");
				vl_opcion.value = result[i]['COD_EMPRESA'][j]['IT_COD_EMPRESA'];
				vl_opcion.innerHTML = URLDecode(result[i]['COD_EMPRESA'][j]['IT_ALIAS']);
				vl_opcion.label = result[i]['COD_EMPRESA'][j]['PRECIO_COMPRA'];
				vl_cod_empresa.appendChild(vl_opcion);
			}
		}	
		
	}else{
		var vl_cod_producto = document.getElementById('COD_PRODUCTO_0').value;
			
		var ajax = nuevoAjax();
		ajax.open("GET", "ajax_producto.php?cod_producto="+URLEncode(vl_cod_producto), false);
		ajax.send(null);
		var resp = URLDecode(ajax.responseText);
		var result = eval("(" + resp + ")");
		
		var vl_row = add_line('ITEM_SOLICITUD_COMPRA', 'solicitud_compra');
		var vl_cantidad = to_num(document.getElementById('CANTIDAD_0').value);
		
		for (var i=0; i< result.length; i++) {
			document.getElementById('IT_COD_PRODUCTO_' + vl_row).innerHTML = result[i]['COD_PRODUCTO'];
			document.getElementById('IT_COD_PRODUCTO_H_' + vl_row).value = result[i]['COD_PRODUCTO'];
			document.getElementById('IT_NOM_PRODUCTO_' + vl_row).innerHTML = URLDecode(result[i]['NOM_PRODUCTO']);
			document.getElementById('IT_CANTIDAD_' + vl_row).value = to_num(document.getElementById('CANTIDAD_0').value);
			document.getElementById('IT_CANTIDAD_TOTAL_' + vl_row).innerHTML = to_num(document.getElementById('CANTIDAD_0').value);
			document.getElementById('IT_CANTIDAD_TOTAL_H_' + vl_row).value = to_num(document.getElementById('CANTIDAD_0').value);
			document.getElementById('IT_GENERA_COMPRA_' + vl_row).checked = (result[i]['GENERA_COMPRA']=='S'); 
	
			var vl_cod_empresa = document.getElementById('IT_COD_EMPRESA_' + vl_row);
			vl_cod_empresa.length = 0;
			// item vacio
			var vl_opcion = document.createElement("option");
			vl_opcion.value = '';
			vl_opcion.innerHTML = '';
			vl_cod_empresa.appendChild(vl_opcion);
			for (var j=0; j < result[i]['COD_EMPRESA'].length; j++) {
				var vl_opcion = document.createElement("option");
				vl_opcion.value = result[i]['COD_EMPRESA'][j]['IT_COD_EMPRESA'];
				vl_opcion.innerHTML = URLDecode(result[i]['COD_EMPRESA'][j]['IT_ALIAS']);
				vl_opcion.label = result[i]['COD_EMPRESA'][j]['PRECIO_COMPRA'];
				vl_cod_empresa.appendChild(vl_opcion);
			}
		}	 
	} 
}
function change_empresa(ve_cod_empresa) {
	var vl_precio_compra = ve_cod_empresa.options[ve_cod_empresa.selectedIndex].dataset.dropdown;
	var vl_record = get_num_rec_field(ve_cod_empresa.id); 
	
	document.getElementById('IT_PRECIO_COMPRA_' + vl_record).innerHTML = number_format(vl_precio_compra, 0, ',', '.'); 
	document.getElementById('IT_PRECIO_COMPRA_H_' + vl_record).value = vl_precio_compra; 
}
function change_cantidad(ve_cantidad) {
	var vl_cantidad = to_num(ve_cantidad.value);
	if (vl_cantidad=='')
		vl_cantidad = 0;
	var vl_tabla = document.getElementById('ITEM_SOLICITUD_COMPRA');
	var aTR = vl_tabla.getElementsByTagName("tr");
	for (var i=0; i<aTR.length; i++) {
		var vl_record = get_num_rec_field(aTR[i].id); 
		var vl_cantidad_unitaria = document.getElementById('IT_CANTIDAD_' + vl_record).value;
		document.getElementById('IT_CANTIDAD_TOTAL_' + vl_record).innerHTML = vl_cantidad * vl_cantidad_unitaria;
		document.getElementById('IT_CANTIDAD_TOTAL_H_' + vl_record).value = vl_cantidad * vl_cantidad_unitaria;
	}
}
function terminado_compuesto(ve_terminado_compuesto) {
	var vl_field = get_nom_field(ve_terminado_compuesto.id);
	if (vl_field == 'TERMINADO') 
		busca_items(true);
	else 
		busca_items(false);
}