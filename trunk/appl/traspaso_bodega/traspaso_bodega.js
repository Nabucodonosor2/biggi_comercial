function validate(){
	/*var aTR = get_TR('ITEM_TRASPASO_BODEGA');
	if(aTR.length < 1){
		alert("Tiene que agregar a lo menos 1 Ítem.");
		return false;
	}*/	
	estado = get_value('COD_ESTADO_TRASPASO_0');
	cod_traspaso = get_value('COD_TRASPASO_BODEGA_H_0');
	if(estado == 2){
		var ajax = nuevoAjax();	
	    ajax.open("GET", "ajax_valida_stock.php?cod_traspaso="+cod_traspaso, false);    
	    ajax.send(null);    
		var resp = URLDecode(ajax.responseText);
		
		if(resp > 0){
			alert("Ya no hay stock suficiente para realizar el traspaso");
			return false;	
		}
	}
	return true;
}

function chqueaOrigen(){
	bodega_origen = get_value('COD_BODEGA_ORIGEN_0');
	bodega_destino = get_value('COD_BODEGA_DESTINO_0');
	
	if(bodega_destino == bodega_origen){
		alert('Seleccione una bodega diferente');
		set_value_dropdown('COD_BODEGA_DESTINO_0','');
	}
}
function limpiarCeros(){
	var aTR = get_TR('ITEM_TRASPASO_BODEGA');
	if(aTR.length > 0){
		for (i=0; i<aTR.length; i++){
			row =  get_num_rec_field(aTR[i].id)
			if(document.getElementById('CT_TRASPASAR_' + row).value.length == 0)
				del_line(aTR[i].id, 'item_traspaso_bodega');
			
		}
	}
}
function limpiaTabla(){
	var aTR = get_TR('ITEM_TRASPASO_BODEGA');
	if(aTR.length > 0){
		for (i=0; i<aTR.length; i++){
			//del_line(aTR[i].id, 'traspaso_bodega');
			var rec_tr = get_num_rec_field(aTR[i].id);
			del_line('ITEM_TRASPASO_BODEGA_'+rec_tr, 'traspaso_bodega');
		}
	}
	document.getElementById('td_stcock').innerHTML = 'Equipos en stock Bodega: '
	bodega_origen = get_value('COD_BODEGA_ORIGEN_0');
	vl_row = 0;
	
	var ajax = nuevoAjax();
	ajax.open("GET", "ajax_carga_items.php?cod_bodega="+bodega_origen, false);
	ajax.send(null);
	var resp = URLDecode(ajax.responseText);
	
	var result = eval("(" + resp + ")");
	//document.getElementById('DIRECCION_0').innerHTML = result[0]['DIRECCION'];
	for (var i=0; i < result.length; i++) {
		var vl_row = add_line('ITEM_TRASPASO_BODEGA', 'traspaso_bodega');
	
		document.getElementById('ITEM_' + vl_row).innerHTML	= parseInt(i) +1;
		document.getElementById('ITEM_H_' + vl_row).value	= parseInt(i) +1;
		document.getElementById('COD_PRODUCTO_' + vl_row).innerHTML		= result[i]['COD_PRODUCTO'];
		document.getElementById('COD_PRODUCTO_' + vl_row).value			= result[i]['COD_PRODUCTO'];
		document.getElementById('NOM_PRODUCTO_' + vl_row).innerHTML		= result[i]['NOM_PRODUCTO'];
		document.getElementById('NOM_PRODUCTO_' + vl_row).value			= result[i]['NOM_PRODUCTO'];
		document.getElementById('CT_STOCK_H_' + vl_row).value				= result[i]['STOCK'];
		document.getElementById('CT_STOCK_' + vl_row).innerHTML				= result[i]['STOCK'];
	}
	document.getElementById('td_stcock').innerHTML = 'Equipos en stock Bodega: '+result.length;
}
function cargaStock(campo){
	var campo_id = campo.id;
	var field = get_nom_field(campo_id);
	var record = get_num_rec_field(campo_id);
	
	bodega_origen = get_value('COD_BODEGA_ORIGEN_0');
	if(bodega_origen == 0){
		alert('Seleccione primero una bodega de Origen');
		set_value('COD_PRODUCTO_'+record,'','');
		set_value('NOM_PRODUCTO_'+record,'','');
		return false;
	}
	cod_producto = get_value('COD_PRODUCTO_'+record);
	
	var ajax = nuevoAjax();	
    ajax.open("GET", "ajax_valida_stock.php?cod_producto="+cod_producto+"&cod_bodega="+bodega_origen, false);    
    ajax.send(null);    
	var resp = URLDecode(ajax.responseText);
	
	set_value('CT_STOCK_'+record,resp,resp);
	set_value('CT_STOCK_H_'+record,resp,resp);
	
}
function validaStock(campo){
	var campo_id = campo.id;
	var field = get_nom_field(campo_id);
	var record = get_num_rec_field(campo_id);
	
	stock = parseInt(get_value('CT_STOCK_H_'+record));
	solicitado = parseInt(campo.value);
	
	if(stock < solicitado){
		alert('No hay suficiente stock para esa cantidad');
		campo.value = stock;
	}
	
}
function help_producto(campo, num_dec) {
	var campo_id = campo.id;
	var field = get_nom_field(campo_id);
	var record = get_num_rec_field(campo_id);
	
	bodega_origen = get_value('COD_BODEGA_ORIGEN_0');
	if(bodega_origen == 0){
		alert('Seleccione primero una bodega de Origen');
		set_value('COD_PRODUCTO_'+record,'','');
		set_value('NOM_PRODUCTO_'+record,'','');
		return false;
	}

	var cod_producto = document.getElementById('COD_PRODUCTO_' + record); 
	var nom_producto = document.getElementById('NOM_PRODUCTO_' + record); 
	var precio = document.getElementById('PRECIO_' + record);
	var precio_h = document.getElementById('PRECIO_H_' + record);

	cod_producto.value = cod_producto.value.toUpperCase();
	var cod_producto_value = nom_producto_value = '';
	switch (field) {
	case 'COD_PRODUCTO': if (cod_producto.value=='TE') {
   							ingreso_TE(cod_producto);
   							return;
   						}
   						var boton_precio = document.getElementById('BOTON_PRECIO_' + record);
   						if (boton_precio)
   							boton_precio.value =  'Precio';
   						cod_producto_value = campo.value;	
   						break;
	case 'NOM_PRODUCTO': if (cod_producto.value=='T' || cod_producto.value=='TE') return;   											
   						nom_producto_value = campo.value;	
   						break;
	}
	var ajax = nuevoAjax();
	cod_producto_value = URLEncode(cod_producto_value);
	nom_producto_value = URLEncode(nom_producto_value);
	ajax.open("GET", "../../../../commonlib/trunk/php/help_producto.php?cod_producto="+cod_producto_value+"&nom_producto="+nom_producto_value, false);
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
  		select_1_producto(lista, record);
  		cargaStock(campo);
	   	break;
  	default:
		
  	var id_campo = campo.id;
	   var url = "../../../../commonlib/trunk/php/help_lista_producto.php?sql="+URLEncode(lista[1]);
		$.showModalDialog({
			 url: url,
			 dialogArguments: '',
			 height: 200,
			 width: 650,
			 async: false,
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
			  		select_1_producto(valores, record);
			  		cargaStock(campo);
			  		if (precio_h) {
	
						precio_h.value = findAndReplace(precio.innerHTML, '.', '');	// borra los puntos en los miles
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
			}
		});	
			break;
	}
	// reclacula los computed que usan precio
	
	if (precio_h) {
	
		precio_h.value = findAndReplace(precio.innerHTML, '.', '');	// borra los puntos en los miles
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
