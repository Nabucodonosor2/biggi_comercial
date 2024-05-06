function valida_descuento_aut(ve_control){
	if(ve_control.id == 'DESCTO_1_AUTORIZADO_0'){
		document.getElementById('DESCTO_1_AUTORIZADO_H_0').value = ve_control.value;
		if(ve_control.value == 0){
			document.getElementById('DESCTO_2_AUTORIZADO_0').value = 0;
			document.getElementById('DESCTO_2_AUTORIZADO_H_0').value = 0;
		}
			
	}else if(ve_control.id == 'DESCTO_2_AUTORIZADO_0'){
		document.getElementById('DESCTO_2_AUTORIZADO_H_0').value = ve_control.value;
		if(ve_control.value != 0 && document.getElementById('DESCTO_1_AUTORIZADO_0').value == 0){
			alert("Para autorizar 'Descto 2' es necesario que se ingrese previamente un valor distinto a cero en 'Descto 1'.");
			document.getElementById('DESCTO_2_AUTORIZADO_0').value = 0;
			document.getElementById('DESCTO_2_AUTORIZADO_H_0').value = 0;
		}
	}
}

function validate() {
	
	var aTR = get_TR('SEGUIMIENTO_COTIZACION');
	for (var i=0; i < aTR.length; i++){
		var vl_record = get_num_rec_field(aTR[i].id);
		var vl_fecha_compromiso = document.getElementById('FECHA_COMPROMISO_E_' + vl_record).value;
		var vl_hora_compromiso = document.getElementById('HORA_COMPROMISO_E_' + vl_record).value;
		var vl_glosa_compromiso = document.getElementById('GLOSA_COMPROMISO_E_' + vl_record).value;
		
		if(vl_fecha_compromiso == ''){
			alert('Debe ingresar "Fecha compromiso" antes de grabar.');
			document.getElementById('FECHA_COMPROMISO_E_' + vl_record).focus();
			return false;
		}else if(vl_hora_compromiso == ''){
			alert('Debe ingresar "Hora compromiso" antes de grabar.');
			document.getElementById('HORA_COMPROMISO_E_' + vl_record).focus();
			return false;
		}else if(vl_glosa_compromiso == ''){
			alert('Debe ingresar "Glosa compromiso" antes de grabar.');
			document.getElementById('GLOSA_COMPROMISO_E_' + vl_record).focus();
			return false;
		}
	}
	
	if(document.getElementById('COD_COTIZACION_H_0').value == ''){
		if(document.getElementById('COD_ESTADO_COTIZACION_0').value == 5){
			var	vl_cod_cotizacion_desde = document.getElementById('COD_COTIZACION_DESDE_0').innerHTML;
			var vl_confirm = confirm('La Cotización Nº '+vl_cod_cotizacion_desde+' se encuentra en estado rechazada.\nAl crear una nueva Cotización, la Cotización Nº '+vl_cod_cotizacion_desde+' quedara en estado RE-ABIERTA.\n\n¿Esta seguro que desea grabar esta Cotización?');
			if(vl_confirm == false)
				return false;
		}
	}	
		
	return validate_cot_nv('ITEM_COTIZACION');
}


function refresh(){
	var vl_cod_empresa = document.getElementById('COD_EMPRESA_0').value;

	var vl_ajax = nuevoAjax();	
	var php = "../cotizacion/COMERCIAL/ajax_empresa_refresh.php?cod_empresa="+vl_cod_empresa;
	vl_ajax.open("GET", php, false);
	vl_ajax.send(null);
	
	var vl_resp = URLDecode(vl_ajax.responseText);
	var vl_result = eval("(" + vl_resp + ")");
	
	var vl_cod_persona = document.getElementById('COD_PERSONA_0');
	var vl_cod_persona_select = vl_cod_persona.value; 
	vl_cod_persona.length = 0;
	
	for (var k=0; k < vl_result['labels'].length; k++){
		var vl_option = new Option(URLDecode(vl_result['labels'][k]), vl_result['values'][k]);
		if (vl_cod_persona_select == vl_result['values'][k])
			vl_option.selected = true;
		vl_cod_persona.appendChild(vl_option);
	}
	
	var aTR = get_TR('SEGUIMIENTO_COTIZACION');
	for (var i=0; i < aTR.length; i++){
		var record = get_num_rec_field(aTR[i].id);
		var vl_persona = document.getElementById('SC_COD_PERSONA_' + record);
		var vl_cod_persona_select = vl_persona.value; 
		
		vl_persona.length = 0;	// borra todos los optionx
		//var vl_option = new Option('', '');
		//vl_persona.appendChild(vl_option);
		for (var j=0; j < vl_result['labels'].length; j++){
			var vl_option = new Option(URLDecode(vl_result['labels'][j]), vl_result['values'][j]);
			if (vl_cod_persona_select == vl_result['values'][j])
				vl_option.selected = true;
			vl_persona.appendChild(vl_option);
		} 		
	}	
}

function set_mail_telefono(ve_control){
	var vl_cod_persona = ve_control.value;
	var vl_contacto = ve_control.options[ve_control.selectedIndex].text;
	var vl_campo_id = ve_control.id;
	var vl_record = get_num_rec_field(vl_campo_id);
	
	var vl_ajax = nuevoAjax();	
	var php = "../cotizacion/COMERCIAL/ajax_mail_telefono.php?cod_persona="+vl_cod_persona;
	vl_ajax.open("GET", php, false);
	vl_ajax.send(null);
	
	var vl_resp = URLDecode(vl_ajax.responseText);
	var vl_result = eval("(" + vl_resp + ")");
	
	document.getElementById('SC_TELEFONO_' + vl_record).innerHTML = vl_result[0]['TELEFONO'];
	document.getElementById('SC_TELEFONO_H_' + vl_record).value = vl_result[0]['TELEFONO'];
	
	document.getElementById('SC_MAIL_' + vl_record).innerHTML = vl_result[0]['EMAIL'];
	document.getElementById('SC_MAIL_H_' + vl_record).value = vl_result[0]['EMAIL'];
	document.getElementById('SC_CONTACTO_' + vl_record).value = vl_contacto;
}

function display_rechazo(){
	if(document.getElementById('RECHAZADA_0').checked){
		document.getElementById('TIPO_RECHAZO').style.display= '';
		document.getElementById('MOTIVO_RECHAZO').style.display= '';
	}else{
		document.getElementById('TIPO_RECHAZO').style.display= 'none';
		document.getElementById('MOTIVO_RECHAZO').style.display= 'none';
	}
}

function select_1_empresa(valores, record) {
	const cod_empresa			= get_value('COD_EMPRESA_0');
	const valida_usuario_biggi	= get_value('VALIDA_USUARIO_BIGGI_0');

	if(cod_empresa != 1337){
		set_values_empresa(valores, record);
	}else{
		if(valida_usuario_biggi == 'S'){
			set_values_empresa(valores, record);
		}else{
			const control = document.getElementById('COD_EMPRESA_0');
			alert('No se puede indicar a "Comercial Biggi Chile" como cliente de la cotización.');
			set_empresa_vacio(control)
		}
	}
}