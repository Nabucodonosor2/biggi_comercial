function validate() {
	var aTR = get_TR('ITEM_ORDEN_COMPRA');
	if (aTR.length==0) {
		alert('Debe ingresar al menos 1 item antes de grabar.');
		return false;
	}
	
	var cod_estado_doc_sii_value = get_value('COD_ESTADO_ORDEN_COMPRA_H_0'); 
	if (to_num(cod_estado_doc_sii_value) == 2){	
		var motivo_anula = document.getElementById('MOTIVO_ANULA_0');
		if (motivo_anula.value == '') {
			alert('Debe ingresar el motivo de Anulaciï¿½n antes de grabar.');
			motivo_anula.focus();
			return false;
		}
	}
	
	var vl_maximo_precio_oc = get_value('MAXIMO_PRECIO_OC_H_0');
	var vl_autorizada_20_proc = document.getElementById('AUTORIZADA_20_PROC_0').checked;  
	var vl_total_neto_oc = get_value('TOTAL_NETO_0'); 
	//vl_maximo_precio_oc = vl_maximo_precio_oc.replace('.',',');
	vl_maximo_precio_oc = parseInt(vl_maximo_precio_oc);
	vl_total_neto_oc = parseInt(to_num(vl_total_neto_oc));
	
	/* VMC, 29-03-2011 se deja comentado hasta que se retome esta restriccion
		esta restriccion la implemento MU antes de irnos de vacaciones y se hecho para atras porque pedia autorizar de todo
	
	if (vl_total_neto_oc > vl_maximo_precio_oc){
		if(vl_autorizada_20_proc){
			alert('La OC excede Monto Neto permitido, se debe Autorizar la OC para ser impresa.');
		}
	}
	*/
	
	return true;
}

function change_item_orden_compra(ve_valor, ve_campo) {
	var record_item_oc = get_num_rec_field(ve_valor.id);
	var item_value = document.getElementById('ITEM_' + record_item_oc).value;
	var cod_producto_old = document.getElementById('COD_PRODUCTO_OLD_' + record_item_oc);
	var cod_producto = document.getElementById('COD_PRODUCTO_' + record_item_oc);
	
	if(ve_campo == 'COD_PRODUCTO' | ve_campo == 'NOM_PRODUCTO'){
		help_producto(ve_valor, 0);
		if(cod_producto.value == 'T'){
			alert('No se pueden agregar Tï¿½tulos a una Orden de Compra.');
			if(cod_producto_old.value=='T'){ //es la primera vez que se ingresa el cï¿½digo
				document.getElementById('COD_PRODUCTO_' + record_item_oc).value = '';
				document.getElementById('NOM_PRODUCTO_' + record_item_oc).value = '';
			}
			else{
				cod_producto.value = cod_producto_old; 
				help_producto(cod_producto, 0); 
			}	
		}
		document.getElementById('PRECIO_H_'+record_item_oc).value = document.getElementById('PRECIO_'+record_item_oc).value;
	}	
}

// funcion que despliega un tipo texto si es que el cod_doc_sii='anulada' 
function mostrarOcultar_Anula(ve_campo) {
	var vl_cod_estado_orden_compra_h = document.getElementById('COD_ESTADO_ORDEN_COMPRA_H_0');
	vl_cod_estado_orden_compra_h.value = ve_campo.value;
	
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

function existe_nv(cod_nv) {
	var cod_nv = document.getElementById('COD_NOTA_VENTA_0');
   	ajax = nuevoAjax();
	ajax.open("GET", "existe_nv.php?cod_nv="+cod_nv.value,false);
    ajax.send(null);	        
	var resp = ajax.responseText;
    if(resp == 'NO'){    	 
    	alert('La Nota de Venta NO Existe!!');
       	cod_nv.value = '';       	       	        	
    }else if(resp == 'EMITIDA'){    	 
    	alert('La Nota de Venta estï¿½ Emitida.');
       	cod_nv.value = '';
    }else if(resp == 'CERRADA'){    	 
    	alert('La Nota de Venta estï¿½ Cerrada.');
       	cod_nv.value = '';
    }else if(resp == 'CERRADA_PUEDE'){    	 
    	alert('La Nota de Venta estï¿½ Cerrada.  La compra serï¿½ considerada como backcharge.');
       	//cod_nv.value = '';	=> permite continuar
    }else if(resp == 'ANULADA'){    	 
    	alert('La Nota de Venta estï¿½ Anulada!!');
       	cod_nv.value = ''; 
    } 
}

function select_1_producto(valores, record) {
	set_values_producto(valores, record);
	 
	var cod_producto_value = document.getElementById('COD_PRODUCTO_' + record).value;
	var cod_empresa = document.getElementById('COD_EMPRESA_0').value;
		 
	var ajax = nuevoAjax();	
    ajax.open("GET", "get_precio_proveedor.php?cod_producto="+cod_producto_value+"&cod_empresa="+cod_empresa, false);    
    ajax.send(null);    
	var resp = ajax.responseText.split('|');	
	var precio_pub = resp[0];	
	
	document.getElementById('PRECIO_'+record).value = precio_pub;
}

function change_precio(ve_precio) {
	var por_modifica_precio = parseFloat(document.getElementById('PORC_MODIFICA_PRECIO_OC_H_0').value);
	var record = get_num_rec_field(ve_precio.id);
	var cod_producto = get_value('COD_PRODUCTO_' + record);
	if (cod_producto.toUpperCase() == 'F' | cod_producto.toUpperCase() == 'E'| cod_producto.toUpperCase() == 'I'){ //para el caso de los flete o embalaje el precio es libre
		return;
	}
		
	if(por_modifica_precio == 0.0){//se mantiene el precio que tenï¿½a
		var precio = document.getElementById('PRECIO_H_'+record).value;
		var precio_min = precio;
		var precio_max = precio;
		
		alert('Sr. usuario, su porcentaje de variaciï¿½n definido en los precios de compra es de un 0%.\n \n ï¿½Se mantendrï¿½ el precio anterior!');
		ve_precio.value = document.getElementById('PRECIO_H_'+record).value;
	}
	else{
		//obtiene el precio del proveedor
		var cod_empresa = document.getElementById('COD_EMPRESA_0').value;
		var ajax = nuevoAjax();
		ajax.open("GET", "ajax_change_precio.php?cod_producto="+URLEncode(cod_producto)+"&cod_empresa="+cod_empresa, false);
		ajax.send(null);
		var resp = URLDecode(ajax.responseText);
		var aDato = eval("(" + resp + ")");
		var precio = parseInt(aDato[0]['PRECIO']);

		var precio_min = roundNumber(precio - (precio * por_modifica_precio/100), 0);
		var precio_max = roundNumber(precio + (precio * por_modifica_precio/100), 0);
		
		if (ve_precio.value>precio_max && por_modifica_precio < 100){
			alert('Sr. usuario, su porcentaje de variaciï¿½n definido en los precios de compra es de un '+por_modifica_precio+'%. El monto ingresado supera el permitido, ya que el precio de compra vigente es de $'+number_format(precio, 0, ',', '.')+'.\n \n - Mï¿½ximo permitido: '+number_format(precio_max, 0, ',', '.'));
			ve_precio.value = document.getElementById('PRECIO_H_'+record).value;		
		}
		else if (ve_precio.value<precio_min && por_modifica_precio < 100){
			alert('Sr. usuario, su porcentaje de variaciï¿½n definido en los precios de compra es de un '+por_modifica_precio+'%. El monto ingresado supera el permitido, ya que el precio de compra vigente es de $'+number_format(precio, 0, ',', '.')+'.\n \n - Mï¿½nimo permitido: '+number_format(precio_min, 0, ',', '.'));
			ve_precio.value = document.getElementById('PRECIO_H_'+record).value;		
		}
	}
}

function valida_aut_facturacion(){
	var vl_fecha_solicita_fact = get_value('FECHA_SOLICITA_FACTURACION_0'); 
	
	if(document.getElementById('AUTORIZA_FACTURACION_0').checked){
		var ajax = nuevoAjax();
		ajax.open("GET", "ajax_get_fecha_actual.php?fecha_solicita_fact="+vl_fecha_solicita_fact, false);
		ajax.send(null);
		var resp = ajax.responseText.split('|');

		if(document.getElementById('FECHA_SOLICITA_FACTURACION_0').value == '')
			document.getElementById('FECHA_SOLICITA_FACTURACION_0').value = resp[0];
		else{
			if(resp[1] == 'MAYOR'){
				alert('La fecha ingresada es menor a la fecha actual');
				document.getElementById('FECHA_SOLICITA_FACTURACION_0').value = resp[0];
			}
		}	
	}else
		document.getElementById('FECHA_SOLICITA_FACTURACION_0').value = '';
}

function dlg_print() {
	var vl_monto_neto = get_value('TOTAL_NETO_0');
	var vl_cod_empresa = get_value('COD_EMPRESA_0');
	var vl_cod_orden_compra = get_value('COD_ORDEN_COMPRA_0');
	
	if(document.getElementById('AUTORIZA_MONTO_COMPRA_0').checked == false){
		var ajax = nuevoAjax();
		ajax.open("GET", "ajax_get_monto_maximo_neto.php?cod_empresa="+vl_cod_empresa+"&cod_orden_compra="+vl_cod_orden_compra, false);
		ajax.send(null);
		var vl_monto_maximo_neto = ajax.responseText;
		if(vl_monto_maximo_neto != 'OMITIR'){
			if(parseInt(findAndReplace(vl_monto_neto, '.', '')) >= parseInt(findAndReplace(vl_monto_maximo_neto, '.', ''))){
				alert('El monto de esta OC exede el mï¿½ximo permitido sin autorizaciï¿½n.\nDebe solicitar autorizaciï¿½n para proceder con esta compra.');
				return false;
			}
		}		
		
		return true;
	}
	return true;
}

function display_aut_monto(){
	if(document.getElementById('AUTORIZA_MONTO_COMPRA_0').checked)
		document.getElementById('DISPLAY_DIV_AUT_MONTO').style.display= '';
	else
		document.getElementById('DISPLAY_DIV_AUT_MONTO').style.display= 'none';
}

function confirm_autoriza(){
	var vl_confirm = confirm('Desea autorizar el monto OC?');
	
	if(vl_confirm)
		return true;
	else
		return false;
}

function valida_descts(){
	cod_usuario = $('#COD_USUARIO_0').val();
	cod_empresa = $('#COD_EMPRESA_0').val();
	
	usuario_permitido = 'S';
	if(cod_empresa == 1138 || cod_empresa == 1302)
	    usuario_permitido = 'N';
	if(cod_usuario == 1 || cod_usuario == 2 || cod_usuario == 4 || cod_usuario == 8 || cod_usuario == 71)
		usuario_permitido = 'S';
	
	if(usuario_permitido == 'N'){
		document.getElementById("PORC_DSCTO1_0").value = '';
		document.getElementById("MONTO_DSCTO1_0").value = ''; 
		document.getElementById("PORC_DSCTO2_0").value = ''; 
		document.getElementById("MONTO_DSCTO2_0").value = ''; 
		
		document.getElementById("PORC_DSCTO1_0").readOnly = true;
		document.getElementById("MONTO_DSCTO1_0").readOnly = true; 
		document.getElementById("PORC_DSCTO2_0").readOnly = true; 
		document.getElementById("MONTO_DSCTO2_0").readOnly = true; 
     
    }
	if(usuario_permitido == 'S'){
		document.getElementById("PORC_DSCTO1_0").value = '';
		document.getElementById("MONTO_DSCTO1_0").value = ''; 
		document.getElementById("PORC_DSCTO2_0").value = ''; 
		document.getElementById("MONTO_DSCTO2_0").value = ''; 
		
		document.getElementById("PORC_DSCTO1_0").readOnly = false;
		document.getElementById("MONTO_DSCTO1_0").readOnly = false; 
		document.getElementById("PORC_DSCTO2_0").readOnly = false; 
		document.getElementById("MONTO_DSCTO2_0").readOnly = false; 
    }

}
function select_1_empresa(valores, record) { 
	/* Se reimplementa para agregar codigo adicional */
		 set_values_empresa(valores, record);

		// Codigo adicional
		 valida_descts();
}

function imprime_ccosto(){
	if(document.getElementById('imprime_costo_chk').checked){
		$("#imprime_costo").val('S');
	}else{
		$("#imprime_costo").val('N');
	}
}

function request_orden_compra(ve_prompt, ve_valor){
	const urlRequest = "../../../trunk/appl/orden_compra/request_orden_compra.php?prompt="+URLEncode(ve_prompt)+"&valor="+URLEncode(ve_valor);
	$.showModalDialog({
		 url: urlRequest,
		 dialogArguments: '',
		 height: 200,
		 width: 360,
		 scrollable: false,
		 onClose: function(){ 
		 	const returnValue = this.returnValue;
		 	if(returnValue == null)	
				return false;		
			else{
				const input = document.createElement("input");
								input.setAttribute("type", "hidden");
								input.setAttribute("name", "b_cambio_estado_x");
								input.setAttribute("id", "b_cambio_estado_x");
								document.getElementById("output").appendChild(input);

				document.getElementById('wo_hidden').value = returnValue;
				document.output.submit();
				return true;
			} 
		}
	});			
}

function valida_cambio_estado_oc(ve_cod_orden_compra){
	const ajax = nuevoAjax();
	ajax.open("GET", "ajax_valida_estado_od.php?cod_orden_compra="+ve_cod_orden_compra, false);
	ajax.send(null);	
	const resp = ajax.responseText;

	if(resp == '-1'){
		alert('La OC Nro. '+ve_cod_orden_compra+' tiene más de 12 meses de antiguedad, no se puede realizar el cambio de estado.');
		return false;
	}else if(resp == '1'){
		let vl_confirm = confirm('Se cambiará a estado CERRADA la OC Nro. '+ve_cod_orden_compra+'. Esta seguro?.');
		if(vl_confirm == false)
			return false;
		else
			return true;
	}else if(resp == '3'){
		let vl_confirm = confirm('Se cambiará a estado EMITIDA la a OC Nro. '+ve_cod_orden_compra+'. Esta seguro?.');
		if(vl_confirm == false)
			return false;
		else
			return true;
	}else{
		alert('Error');
		return false;
	}
}

function display_respetar_precio(ve_control){
	const codUsuario = get_value('COD_USUARIO_ACTUAL_H_0');

	if(ve_control.checked == true){
		const ajax = nuevoAjax();
		ajax.open("GET", "ajax_get_usuario_fecha.php?cod_usuario="+codUsuario, false);
		ajax.send(null);
		const arr = ajax.responseText.split('|');

		set_value('NOM_USUARIO_RP_CLIENTE_0', arr[0], arr[0]);
		set_value('FECHA_RP_CLIENTE_0', arr[1], arr[1]);
	}else{
		set_value('NOM_USUARIO_RP_CLIENTE_0', '', '');
		set_value('FECHA_RP_CLIENTE_0', '', '');
	}
}