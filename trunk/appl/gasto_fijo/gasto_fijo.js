function validate() {
	var cod_estado_doc_sii_value = get_value('COD_ESTADO_ORDEN_COMPRA_0'); 
	if (to_num(cod_estado_doc_sii_value) == 2){	
		var motivo_anula = document.getElementById('MOTIVO_ANULA_0');
		if (motivo_anula.value == '') {
			alert('Debe ingresar el motivo de Anulación antes de grabar.');
			motivo_anula.focus();
			return false;
		}
	}

	const cod_usuario_h = get_value('COD_USUARIO_H_0');
	
	if(cod_usuario_h == 102){
		const nro_cotizacion	= get_value('NRO_COTIZACION_0');
		const nro_nv			= get_value('NRO_NOTA_VENTA_0');

		if(nro_cotizacion == "" && nro_nv == ""){
			alert('Debe ingresar alguna cotización u nota de venta');
			return false;
		}
	}

	return true;
}

// funcion que despliega un tipo texto si es que el cod_doc_sii='anulada' 
function mostrarOcultar_Anula(ve_campo) {
	var tr_anula = document.getElementById('tr_anula');
	
	if (to_num(ve_campo.value)==2) {
		tr_anula.style.display = ''; 
		
		document.getElementById('MOTIVO_ANULA_0').type='text';
		document.getElementById('MOTIVO_ANULA_0').setAttribute('onblur', "this.style.border=''");				
		document.getElementById('MOTIVO_ANULA_0').setAttribute('onfocus', "this.style.border='1px solid #FF0000'");	
		document.getElementById('MOTIVO_ANULA_0').focus();
	}
	else{
		document.getElementById('MOTIVO_ANULA_0').value = '';
		tr_anula.style.display = 'none'; 
	}
}

function change_producto(ve_valor){
	var record = get_num_rec_field(ve_valor.id);
	var precio = document.getElementById('COD_PRODUCTO_'+record).options[document.getElementById('COD_PRODUCTO_'+record).selectedIndex].dataset.dropdown;
	document.getElementById('PRECIO_'+record).value = precio;	
}

function calcula_totales(ve_valor){
	var cantidad = document.getElementById('CANTIDAD_0').value;
	var precio = document.getElementById('PRECIO_0').value;
	var subtotal = roundNumber(to_num(cantidad) * to_num(precio), 0);
	document.getElementById('TOTAL_0').innerHTML = number_format(subtotal, 0, ',', '.');
	document.getElementById('SUM_TOTAL_0').innerHTML = number_format(subtotal, 0, ',', '.');
	
	if (ve_valor.id == 'PORC_DSCTO1_0')
		document.getElementById('INGRESO_USUARIO_DSCTO1_0').value = 'P';
	else if (ve_valor.id == 'MONTO_DSCTO1_0')
		document.getElementById('INGRESO_USUARIO_DSCTO1_0').value = 'M';
	else if (ve_valor.id == 'PORC_DSCTO2_0')
		document.getElementById('INGRESO_USUARIO_DSCTO2_0').value = 'P';
	else if (ve_valor.id == 'MONTO_DSCTO2_0')
		document.getElementById('INGRESO_USUARIO_DSCTO2_0').value = 'M';
		
	var ingreso_usuario_dscto1 = document.getElementById('INGRESO_USUARIO_DSCTO1_0').value;
	var ingreso_usuario_dscto2 = document.getElementById('INGRESO_USUARIO_DSCTO2_0').value;
	var monto_dscto1 = document.getElementById('MONTO_DSCTO1_0').value;
	var monto_dscto2 = document.getElementById('MONTO_DSCTO2_0').value;
	var porc_dscto1 = document.getElementById('PORC_DSCTO1_0').value;
	var porc_dscto2 = document.getElementById('PORC_DSCTO2_0').value;
	var porc_iva = document.getElementById('PORC_IVA_0').options[document.getElementById('PORC_IVA_0').selectedIndex].value;
		
	if(ingreso_usuario_dscto1 == 'M'){
		var porc_dscto1 = roundNumber((to_num(monto_dscto1) / subtotal) * 100, 1);
		document.getElementById('PORC_DSCTO1_0').value = porc_dscto1;
	}
	else if (ingreso_usuario_dscto1 == 'P'){
		var monto_dscto1 = roundNumber(subtotal * to_num(porc_dscto1) / 100, 0);
		document.getElementById('MONTO_DSCTO1_0').value = monto_dscto1;
	}	
	var subtotal_con_dscto1 = subtotal - monto_dscto1;
	if(ingreso_usuario_dscto2 == 'M'){
		var porc_dscto2 = roundNumber((to_num(monto_dscto2) / subtotal_con_dscto1) * 100, 1);
		document.getElementById('PORC_DSCTO2_0').value = porc_dscto2;
	}
	else if (ingreso_usuario_dscto2 == 'P'){
		var monto_dscto2 = roundNumber(subtotal_con_dscto1 * to_num(porc_dscto2) / 100, 0);
		document.getElementById('MONTO_DSCTO2_0').value = monto_dscto2;
	}	
		
	var total_neto = subtotal - monto_dscto1 - monto_dscto2;
	var monto_iva = roundNumber(total_neto * to_num(porc_iva) / 100, 0);
	var total_con_iva = total_neto + monto_iva;
	
	document.getElementById('TOTAL_NETO_0').innerHTML = number_format(total_neto, 0, ',', '.');
	document.getElementById('MONTO_IVA_0').innerHTML = number_format(monto_iva, 0, ',', '.');
	document.getElementById('TOTAL_CON_IVA_0').innerHTML = number_format(total_con_iva, 0, ',', '.');
}

function crear_gf(){
	var url = "dlg_crear_gf.php";
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 250,
		 width: 730,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	if (returnVal == null)		
				return false;		
			else 
			{
				var input = document.createElement("input");
				input.setAttribute("type", "hidden");
				input.setAttribute("name", "b_create_x");
				input.setAttribute("id", "b_create_x");
				document.getElementById("output").appendChild(input);
				
				document.getElementById('wo_hidden').value = returnVal;
				document.output.submit();
			   	return true;	
			}
		}
	});			
}

function ajax_cotizacion(ve_control){
	const nro_cotizacion = ve_control.value;

	if(nro_cotizacion != ''){
		const ajax = nuevoAjax();
		ajax.open("GET", "ajax_gasto_fijo.php?fx=getCotizacion&variable="+nro_cotizacion, false);
		ajax.send(null);

		if(URLDecode(ajax.responseText) != 'NULL'){
			const resp = URLDecode(ajax.responseText).split('|');

			set_value('NOM_VENDEDOR_C_0', resp[0], resp[0]);
			set_value('REF_COTIZACION_0', resp[1], resp[1]);
		}else{
			alert('Nro de cotización no existe');
			set_value('NRO_COTIZACION_0', '', '');
			set_value('REF_COTIZACION_0', '', '');
			set_value('NOM_VENDEDOR_C_0', '', '');

			ve_control.focus();
		}
	}else{
		set_value('REF_COTIZACION_0', '', '');
		set_value('NOM_VENDEDOR_C_0', '', '');
	}
	
}

function ajax_nota_venta(ve_control){
	const nro_nota_venta = ve_control.value;

	if(nro_nota_venta != ''){
		const ajax = nuevoAjax();
		ajax.open("GET", "ajax_gasto_fijo.php?fx=getNotaVenta&variable="+nro_nota_venta, false);
		ajax.send(null);
		const resp = URLDecode(ajax.responseText).split('|');

		if(URLDecode(ajax.responseText) != 'NULL'){
			const resp = URLDecode(ajax.responseText).split('|');

			set_value('NOM_VENDEDOR_NV_0', resp[0], resp[0]);
			set_value('REF_NOTA_VENTA_0', resp[1], resp[1]);
		}else{
			alert('Nro de nota de venta no existe');
			set_value('NRO_NOTA_VENTA_0', '', '');
			set_value('NOM_VENDEDOR_NV_0', '', '');
			set_value('REF_NOTA_VENTA_0', '', '');

			ve_control.focus();
		}
	}else{
		set_value('NOM_VENDEDOR_NV_0', '', '');
		set_value('REF_NOTA_VENTA_0', '', '');
	}
	
}