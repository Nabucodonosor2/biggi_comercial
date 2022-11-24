function add_line_item(tabla_id, nom_tabla) {
	var row = add_line(tabla_id, nom_tabla);
	var aTR = get_TR('FAMILIA_PRODUCTO');
	document.getElementById('COD_PRODUCTO_' + row).focus();
}
function add_line2(tabla_id, nom_tabla) {
	var row = add_line(tabla_id, nom_tabla);
	var aTR = get_TR('FAMILIA_ACCESORIO');
	document.getElementById('COD_PRODUCTO_FA_' + row).focus();
}
function add_line3(tabla_id, nom_tabla) {

	var row = add_line(tabla_id, nom_tabla);
	var aTR = get_TR('FAMILIA_SUBFAMILIA');
	document.getElementById('COD_FAMILIA_SUB_' + row).focus();
}
function validate(){
		var aTR = get_TR('FAMILIA_SUBFAMILIA');
	for (var i = 0; i < aTR.length; i++){
		var cod_subfamilia = document.getElementById('COD_SUBFAMILIA_' + i).value;
		if(i >= 0){
			var aTR = get_TR('FAMILIA_PRODUCTO');
			for (var i = 0; i < aTR.length; i++){
				var cod_producto = document.getElementById('COD_PRODUCTO_' + i).value;
				if(i >= 0){
				 alert("No puede tener producto seleccionados en el modulo '(Familia Producto)'");
				 return false
				}
			}
		}
	}
	
	var subfamilia = document.getElementById('SUBFAMILIA_0');
	es_subfamilia = document.getElementById('ES_SUBFAMILIA_0').checked;
	var tab_subfamilia = document.getElementById('TAB_SUBFAMILIA');

	if(es_subfamilia = true){
		
	var aTR = get_TR('FAMILIA_SUBFAMILIA');
		for (var i = 0; i < aTR.length; i++){
		
			var cod_subfamilia = document.getElementById('COD_SUBFAMILIA_' + i).value;
			alerta = 0;
			if(i >= 0){
			alerta = 'null';
				 document.getElementById('ES_SUBFAMILIA_0').value= 'N';
				 document.getElementById('ES_SUBFAMILIA_0').checked= false;
			}
		}
	}
	
}
function help_producto(campo, num_dec) {

	var campo_id = campo.id;
	
	
	var field = get_nom_field(campo_id);
	nom_producto_id = campo_id.substring(3);
	var record = get_num_rec_field(campo_id);

	var cod_producto = document.getElementById(campo_id).value;
	 
	var nom_producto = document.getElementById('NOM'+nom_producto_id).value;
	 
	var precio = document.getElementById('PRECIO_' + record);
	var precio_h = document.getElementById('PRECIO_H_' + record);

	//cod_producto.value = cod_producto.value;
	var cod_producto_value = nom_producto_value = '';
	switch (field) {
	case 'COD_PRODUCTO_FA': if (cod_producto.value=='TE') {
   							ingreso_TE(cod_producto);
   							return;
   						}
   						var boton_precio = document.getElementById('BOTON_PRECIO_' + record);
   						if (boton_precio)
   							boton_precio.value =  'Precio';
   						cod_producto_value = campo.value;	
   						break;
	case 'NOM_PRODUCTO_FA': if (cod_producto.value=='T' || cod_producto.value=='TE') return;   											
   						nom_producto_value = campo.value;	
   						break;
	}
	var ajax = nuevoAjax();
	cod_producto_value = URLEncode(cod_producto);
	nom_producto_value = URLEncode(nom_producto);
	
	ajax.open("GET", "help_producto.php?cod_producto="+cod_producto_value+"&nom_producto="+nom_producto_value, false);
	ajax.send(null);		

	var resp = URLDecode(ajax.responseText);
	
	var lista = resp.split('|');
	switch (lista[0]) {
  	case '0':	
				alert('El producto no existe, favor ingrese nuevamente');
			cod_producto.value = nom_producto.value = precio.innerHTML = ''; 
			campo.focus();
	   	break;
  	case '1': 				
  		select_1_producto(lista, record,nom_producto_id);
	   	break;
  	default:
		var url = "help_lista_producto.php?sql="+URLEncode(lista[1]);
		$.showModalDialog({
			 url: url,
			 dialogArguments: '',
			 height: 250,
			 width: 650,
			 scrollable: false,
			 onClose: function(){ 
			 	var returnVal = this.returnValue;
			 	if (returnVal == null){		
					alert('El producto no existe, favor ingrese nuevamente');
					cod_producto.value = nom_producto.value = precio.innerHTML = ''; 
					campo.focus();	
				}else {
					returnVal = URLDecode(returnVal);
				   	var valores = returnVal.split('|');
			  		select_1_producto(valores, record,nom_producto_id);
				}
			}
		});	
	break;
	}
	// reclacula los computed que usan precio   
	if (precio_h) {
		precio_h.value = findAndReplace(precio_h.innerHTML, '.', '');	// borra los puntos en los miles
		precio_h.value = findAndReplace(precio_h.value, ',', '.');	// cambia coma decimal por punto
	}
	
	recalc_computed_relacionados(record, 'PRECIO');
	
	var cantidad = document.getElementById('CANTIDAD_' + record);
	if (cantidad)
		cantidad.setAttribute('type', "text");				
	var item = document.getElementById('ITEM_' + record);
	if (item)
		item.setAttribute('type', "text");				
	var boton_precio = document.getElementById('BOTON_PRECIO_' + record);
	if (boton_precio)	
		boton_precio.removeAttribute('disabled');
	nom_producto.removeAttribute('disabled');
	if (cod_producto.value=='T') {
		document.getElementById('NOM_PRODUCTO_' + record).select();
		if (cantidad) {
			cantidad.setAttribute('type', "hidden");
			cantidad.value = 1;
		}		
		if (item) {
			var aTR = get_TR('ITEM_COTIZACION');
			for (var i=0; i<aTR.length; i++) {
				if (get_num_rec_field(aTR[i].id)==record)
					break;
			}
			var letra = 'A'.charCodeAt(0);
			for (i=i-1; i >=0; i--) {
				var cod_producto_value = document.getElementById('COD_PRODUCTO_' + get_num_rec_field(aTR[i].id)).value;
				if (cod_producto_value=='T') {
					letra = document.getElementById('ITEM_' + get_num_rec_field(aTR[i].id)).value.charCodeAt(0);
					if (letra >= 'A'.charCodeAt(0) && letra<='Z'.charCodeAt(0))
						letra++;
					else
						letra = 'A'.charCodeAt(0);
					break;
				}
			}	
			item.value = String.fromCharCode(letra);
		}
		if (boton_precio)	
			boton_precio.setAttribute('disabled', "");				
	}
	else if (cod_producto.value!='')
		if (cantidad)
			cantidad.focus();
		
	var cod_producto_old = document.getElementById('COD_PRODUCTO_OLD_' + record);
	if (cod_producto_old)
		cod_producto_old.value = cod_producto.value;  
	
}
function select_1_producto(valores, record,nom_producto_id) {
/* Esta funcion se llama cuando el usuario selecciono un producto de la lista o el dato
ingresado dio como resultado 1 producto 

En los modulos donde es usado help_producto, si se desea agregar un código adiconal se debe 
reimplementar esta funcion
ver ejmplo en produco.js
*/
	 set_values_producto(valores, record,nom_producto_id);
	 
}
function set_values_producto(valores, record,nom_producto_id) {


	set_value('COD' + nom_producto_id, valores[1], valores[1]);
	set_value('NOM' + nom_producto_id, valores[2], valores[2]);
	//set_value('PRECIO_' + record, valores[3], valores[3]);
}
function es_subfamilia(){
		
	var es_subfamilia = document.getElementById('ES_SUBFAMILIA_0').checked;
	var tab = document.getElementById('TAB_SUBFAMILIA');
	
	var aTR = get_TR('FAMILIA_SUBFAMILIA');
	cant_item = aTR.length;
		if(cant_item == 0){
			if(es_subfamilia == true){
			tab.style.display = 'none';
		} 
		if(es_subfamilia == false){
			tab.style.display = '';
		}
	}
}