function refresh(){
	var cod_contacto = document.getElementById('COD_CONTACTO_0').value;
	if(!(cod_contacto == '')){
		var vl_ajax = nuevoAjax();
		vl_ajax.open("GET", "ajax_refresh_contacto.php?cod_contacto="+cod_contacto, false);
		vl_ajax.send(null);		
		var vl_resp = URLDecode(vl_ajax.responseText);
		var vl_result = eval("(" + vl_resp + ")");
		
		document.getElementById('NOM_CONTACTO_0').value = URLDecode(vl_result[0]['NOM_CONTACTO']);
		document.getElementById('DIRECCION_0').innerHTML = URLDecode(vl_result[0]['DIRECCION']);
		document.getElementById('NOM_CIUDAD_0').innerHTML = URLDecode(vl_result[0]['COD_CIUDAD']);
		document.getElementById('NOM_COMUNA_0').innerHTML = URLDecode(vl_result[0]['COD_COMUNA']);
		
	}	
}

function add_line_conversacion(tabla_id, nom_tabla,cod_usuario) {	
	add_line(tabla_id, nom_tabla);
	
	aTR = get_TR('CONVERSACION');
	for (var i=0; i < aTR.length; i++) {
		var record = get_num_rec_field(aTR[i].id);
		var dd_dest_conv = document.getElementById('COD_DESTINATARIO_CONV_'+record);
		
		if(dd_dest_conv!=null){
			var ajax = nuevoAjax();
			ajax.open("GET", "ajax_dd_conversacion.php?cod_usuario="+cod_usuario, false);	
			ajax.send(null);
			var usuario_actual = ajax.responseText;
			
			var lista = usuario_actual.split('|');
			
			if(lista[1] != ''){
				var cantidad = dd_dest_conv.length;
				for (var j=0; j < cantidad; j++) {
					var dd_set_dest = document.getElementById('COD_DESTINATARIO_CONV_'+i);
					
					if(lista[1] == dd_set_dest.options[j].innerHTML){
						dd_dest_conv.selectedIndex = j;
					}
				}
				
			}
			else
				alert('¡El Usuario Actual no se encuentra en Destinatarios!');
		}
	}
}


function validate() {
	//valida Razón Social: | Nombre:CONTACTO | Teléfono:CONTACTO | Teléfono:EMPRESA
	var vl_txt_telefono_contacto = document.getElementById('TXT_TELEFONO_CONTACTO').style.display;
	var vl_txt_persona = document.getElementById('TXT_PERSONA').style.display;
	var vl_txt_telefono_persona =  document.getElementById('TXT_TELEFONO_PERSONA').style.display;
	
	if (vl_txt_telefono_contacto == 'none') {
		//var dd_telefono_contacto = dd_telefono_contacto.options[dd_telefono_contacto.selectedIndex].value;
		var vl_dd_telefono_contacto = document.getElementById('DD_TELEFONO_CONTACTO_0').value;
		if (vl_dd_telefono_contacto == 0) {
			alert('Debe ingresar "Teléfono Empresa" antes de grabar.');
			document.getElementById('DD_TELEFONO_CONTACTO_0').focus();
			return false;
		}
	}
	
	if (vl_txt_persona == 'none') {
		var dd_nom_persona = document.getElementById('DD_NOM_PERSONA_0').value;
		if (dd_nom_persona == 0) {
			alert('Debe ingresar "Nombre Contacto dd" antes de grabar.');
			document.getElementById('DD_NOM_PERSONA_0').focus();
			return false;
		}	
	}else {
		var txt_nom_persona = document.getElementById('NOM_PERSONA_0').value;
		if (txt_nom_persona == '') {
			alert('Debe ingresar "Nombre Contacto tx" antes de grabar.');
			document.getElementById('NOM_PERSONA_0').focus();
			return false;
		}	
	}
	
	if (vl_txt_telefono_persona == 'none') {
		var dd_telefono_persona = document.getElementById('DD_TELEFONO_PERSONA_0').value;
		if (dd_telefono_persona == 0) {
			alert('Debe ingresar "Teléfono Contacto" antes de grabar.');
			document.getElementById('DD_TELEFONO_PERSONA_0').focus();
			return false;
		}	
	}
	
	var aTR = get_TR('DESTINATARIO');
	if (aTR.length==0) {
		alert('¡Debe ingresar al menos 1 DESTINATARIO antes de grabar!');
		return false;
	}
	var vl_tiene_responsable = false;
	for (var i = 0; i < aTR.length; i++){
		if (document.getElementById('RESPONSABLE_' + i).checked)
			vl_tiene_responsable = true;		
	}	
	
	if (!vl_tiene_responsable){
		alert('¡Debe marcar a 1 destinatario como RESPONSABLE!');
		return false;
	}	

	return true;
}

/////////////////
////////////// HELP DE CONTACTO
/////////////////
function set_contacto_vacio(campo) {
	var campo_id = campo.id;
	
	set_value('COD_CONTACTO_PERSONA_0', '', '');
	set_value('NOM_PERSONA_0', '', '');
	set_value('CARGO_0', '', '');
	set_value('CARGO_H_0', '', '');
	set_value('COD_CONTACTO_0', '', '');
	set_value('NOM_CONTACTO_0', '', '');
			
	set_value('RUT_0', '', '');
	set_value('DIG_VERIF_0', '', '');
	set_value('DIRECCION_0', '', '');
	set_value('NOM_CIUDAD_0', '', '');
	set_value('NOM_COMUNA_0', '', '');
	
	document.getElementById('TXT_PERSONA').style.display = '';
	document.getElementById('DD_PERSONA').style.display = 'none';
	
	campo.focus();
}

function select_1_contacto(valores) {
	set_value('COD_CONTACTO_PERSONA_0', valores[1], valores[1]);
	set_value('NOM_PERSONA_0', valores[2], valores[2]);
	set_value('CARGO_0', valores[3], valores[3]);
	set_value('CARGO_H_0', valores[3], valores[3]);
	set_value('COD_CONTACTO_0', valores[4], valores[4]);
	set_value('NOM_CONTACTO_0', valores[5], valores[5]);
	
	set_value('RUT_0', valores[6], valores[6]);
	set_value('DIG_VERIF_0', valores[7], valores[7]);
	set_value('DIRECCION_0', valores[8], valores[8]);
	set_value('NOM_CIUDAD_0', valores[9], valores[9]);
	set_value('NOM_COMUNA_0', valores[10], valores[10]);
	}

function help_contacto(campo) {
	//vacio drop down y setear con 0
	var selOpcion_combo = new Option('', 0); //CREA UN OBJETO OPTION
	var combo_contacto = document.getElementById('DD_TELEFONO_CONTACTO_0');
	while(combo_contacto.options.length > 0){
		combo_contacto.options[combo_contacto.options.length-1] = null;
	}
	var combo_persona = document.getElementById('DD_TELEFONO_PERSONA_0');
	while(combo_persona.options.length > 0){
		combo_persona.options[combo_persona.options.length-1] = null;
	}
	eval(combo_contacto.options[0]=selOpcion_combo); //LE ASIGNA UN VALOR DE 0 Y '' AL OBJETO OPTION
	eval(combo_persona.options[0]=selOpcion_combo); //LE ASIGNA UN VALOR DE 0 Y '' AL OBJETO OPTION

	var campo_id = campo.id;
	var field = get_nom_field(campo_id);
	if ((campo.value == '') && (field == 'NOM_CONTACTO' || field == 'RUT' || field == 'COD_CONTACTO')){
		set_contacto_vacio(campo);
	}
	else if(campo.value == '' && field == 'NOM_PERSONA'){
		set_value('COD_CONTACTO_PERSONA_0', '', '');
		set_value('NOM_PERSONA_0', '', '');
		set_value('CARGO_0', '', '');
		set_value('CARGO_H_0', '', '');
		dd_telefono('CONTACTO');
	}
	else{
		var nom_contacto_value = rut_contacto_value = nom_persona_value = cod_contacto_value = '';
		switch (field) {
			case 'COD_CONTACTO': 
						cod_contacto_value = campo.value;
				break;
			case 'NOM_CONTACTO': 
						nom_contacto_value = campo.value;
				break;
			case 'RUT':	
		   				rut_contacto_value = campo.value;
		   		break;	
		   	case 'NOM_PERSONA':	
		   				nom_persona_value = campo.value;
		   		break;	
		}
	
		var ajax = nuevoAjax();
		nom_contacto_value = URLEncode(nom_contacto_value);
		nom_persona_value = URLEncode(nom_persona_value);
		var php = "../llamado/ajax_help_contacto.php?nom_contacto="+nom_contacto_value+"&rut_contacto="+rut_contacto_value+"&nom_persona="+nom_persona_value+"&cod_contacto="+cod_contacto_value;
		ajax.open("GET", php, true);
		ajax.onreadystatechange=function() { 
			if (ajax.readyState==4) {
				var resp = URLDecode(ajax.responseText);
				var lista = resp.split('|');
				switch (lista[0]) {
			  	case '0':	
		 				alert('¡El contacto no existe!');
		 				set_contacto_vacio(campo);
				   	break;
			  	case '1':
			  		select_1_contacto(lista);
			  		if (field == 'NOM_CONTACTO' || field == 'RUT' || field == 'COD_CONTACTO'){
			  			dd_persona ();
			  			dd_telefono('CONTACTO');
			  		}
			  		else{
			  			dd_telefono('PERSONA');
			  			dd_telefono('CONTACTO');
			  		}		
				   	break;
			  	default:
					var args = "dialogLeft:100px;dialogTop:300px;dialogWidth:650px;dialogHeight:450px;dialogLocation:0;Toolbar:'yes';";
	
					if (field == 'NOM_CONTACTO' || field == 'RUT'){ //listado de empresas
		  				var returnVal = window.showModalDialog("../llamado/help_lista_contacto.php?sql="+URLEncode(lista[1]), "_blank", args);
		  			}
		  			else if(field == 'NOM_PERSONA'){ // listado de personas
		  				var returnVal = window.showModalDialog("../llamado/help_lista_persona.php?sql="+URLEncode(lista[1]), "_blank", args);
		  			}
		  			
				   	if (returnVal == null){
			 			set_contacto_vacio(campo);
			 		}
					else {
						returnVal = URLDecode(returnVal);
				   		var valores = returnVal.split('|');
			  			select_1_contacto(valores);
			  			if (field == 'NOM_CONTACTO' || field == 'RUT'){ 
			  				dd_persona ();
			  				dd_telefono('CONTACTO');
			  			}
			  			else{
			  				dd_telefono('PERSONA');
			  				dd_telefono('CONTACTO');
			  			}	
			  		}
				break;
				}
			}
		}	 
	}//else
	ajax.send(null);
	return;
}

function dd_persona (){
	document.getElementById('TXT_PERSONA').style.display = 'none';
	document.getElementById('DD_PERSONA').style.display = '';
	
	var cod_contacto = document.getElementById('COD_CONTACTO_0').value;
	var ajax = nuevoAjax();
	var php = "ajax_dd_persona.php?cod_contacto="+cod_contacto;
	ajax.open("GET", php, false);
	ajax.send(null);	
	var resp = URLDecode(ajax.responseText);
	var lista1	= resp.split('*');
	//vacia  drop down
	var selOpcion = new Option('', 0); //CREA UN OBJETO OPTION
	eval(document.getElementById('DD_NOM_PERSONA_0').options[0]=selOpcion); //LE ASIGNA UN VALOR DE 0 Y '' AL OBJETO OPTION

	var j = 1; //crear y manejar indices de options
	for(var i=0;i<lista1.length-1;i++){
    	var lista2 = lista1[i].split('|');
    	var selOpcion = new Option(lista2[1], lista2[0]);//creacion de options con ajax
		eval(document.getElementById('DD_NOM_PERSONA_0').options[j]=selOpcion);//asiganacion de valores
		j++;  
    }
}

function select_dd_persona (campo){
	var cod_contacto_persona = campo.options[campo.selectedIndex].value;
	
	if(campo.value == 0){
	
		document.getElementById('COD_CONTACTO_PERSONA_0').value = '';
		document.getElementById('CARGO_0').innerHTML = '';
		document.getElementById('CARGO_H_0').value = '';
		var combo_persona = document.getElementById('DD_TELEFONO_PERSONA_0');
		while(combo_persona.options.length > 0){
			combo_persona.options[combo_persona.options.length-1] = null;
		}
		eval(combo_persona.options[0]=selOpcion_combo); //LE ASIGNA UN VALOR DE 0 Y '' AL OBJETO OPTION
		dd_telefono('CONTACTO');
	}else{
	
		var ajax = nuevoAjax();
		var php = "ajax_select_dd_persona.php?cod_contacto_persona="+cod_contacto_persona;
		
		ajax.open("GET", php, false);
		ajax.send(null);	
		var resp = ajax.responseText.split('|');

		document.getElementById('COD_CONTACTO_PERSONA_0').value = resp[0];
		document.getElementById('CARGO_0').innerHTML = URLDecode(resp[1]);
		document.getElementById('CARGO_H_0').value = URLDecode(resp[1]);
		document.getElementById('NOM_PERSONA_0').value = URLDecode(resp[2]);	
		
		//vacio drop down y setear con 0
		var selOpcion_combo = new Option('', 0); //CREA UN OBJETO OPTION
		var combo_persona = document.getElementById('DD_TELEFONO_PERSONA_0');
		while(combo_persona.options.length > 0){
			combo_persona.options[combo_persona.options.length-1] = null;
		}
		eval(combo_persona.options[0]=selOpcion_combo); //LE ASIGNA UN VALOR DE 0 Y '' AL OBJETO OPTION
		
		dd_telefono('PERSONA');
	}	
}

function crear_contacto() {
	var returnVal = add_documento('contacto', '10250575'); 
 	if (returnVal == null)
 		return false;
	else {

		var cod_contacto = document.getElementById('COD_CONTACTO_0'); 
		cod_contacto.value = returnVal;
		help_contacto(cod_contacto);
		
		document.getElementById('COD_CONTACTO_PERSONA_0').value = '';
		document.getElementById('CARGO_0').innerHTML = '';
		document.getElementById('CARGO_H_0').value = '';
   		return true;
	}
}
 
function crear_modifica(ve_cod_item_menu) {
	var cod_contacto_value = document.getElementById('COD_CONTACTO_0').value;
	if (cod_contacto_value=='') {
		alert('Debe ingresar un contacto');
		return false;
	}
	var returnVal = mod_documento('contacto', cod_contacto_value, ve_cod_item_menu, 'S');
 	if (returnVal == null)
 		return false;
 		
   	return true;
}

function crear_persona(){
	var cod_contacto = document.getElementById('COD_CONTACTO_0').value;
	if (cod_contacto.length==0) {
		alert('¡Antes debe seleccionar una Empresa!');
		return false;
	}

	mod_documento('contacto', cod_contacto, '10250575', 'S');
	dd_persona();
	return true;
}

function dd_telefono (ve_tipo){
	if (ve_tipo == 'CONTACTO'){
		var cod_contacto = document.getElementById('COD_CONTACTO_0').value;
		var cod_persona = '';
		var objeto = document.getElementById('DD_TELEFONO_CONTACTO_0');
	}
	else if(ve_tipo == 'PERSONA'){	
		var cod_persona = document.getElementById('COD_CONTACTO_PERSONA_0').value;
		var cod_contacto = '';
		var objeto = document.getElementById('DD_TELEFONO_PERSONA_0');
	}
	var ajax = nuevoAjax();
	var php = "ajax_dd_telefono.php?cod_contacto="+cod_contacto+"&cod_persona="+cod_persona;
	ajax.open("GET", php, false);

	ajax.send(null);	
	var resp = URLDecode(ajax.responseText);
	var result = eval("(" + resp + ")");

	var selOpcion = new Option('', 0); //CREA UN OBJETO OPTION
	eval(objeto.options[0]=selOpcion); //LE ASIGNA UN VALOR DE 0 Y '' AL OBJETO OPTION

	var j = 1; //crear y manejar indices de options
	for(var i=0;i<result.length;i++){
    	//var lista2 = lista1[i].split('|');
    	var selOpcion = new Option(result[i]['TELEFONO'], result[i]['TELEFONO']);//creacion de options con ajax
		eval(objeto.options[j]=selOpcion);//asiganacion de valores
		j++;  
    } 
}

function copia_telefono(ve_campo){
	if (ve_campo == 'CONTACTO'){
		var vl_telefono = document.getElementById('DD_TELEFONO_CONTACTO_0');
	}else if (ve_campo == 'PERSONA'){
		var vl_telefono = document.getElementById('DD_TELEFONO_PERSONA_0');
	}
	vl_telefono     = vl_telefono.options[vl_telefono.selectedIndex].value;
	
	vl_llamar_telefono = document.getElementById('LLAMAR_TELEFONO_0').value;
	vl_llamar_telefono = vl_llamar_telefono+vl_telefono+'\n';

	document.getElementById('LLAMAR_TELEFONO_0').value= vl_llamar_telefono;	
}

function realizado_conv (ve_seleccion){
	var seleccion = ve_seleccion.checked;
	var record = get_num_rec_field(ve_seleccion.id);

	//verifica que solo se pueda marcar realizado una conversacion
	aTR = get_TR('CONVERSACION');
	var vl_count_realizado = 0;
	for (var i=0; i < aTR.length; i++) {
		var record_b = get_num_rec_field(aTR[i].id);
		var realizado_conv = document.getElementById('REALIZADO_CONV_'+record_b).checked;
		if (realizado_conv){
			vl_count_realizado = vl_count_realizado+1;
		}
	}

	if (vl_count_realizado == 0){
		document.getElementById('REALIZADO_0').checked = false;
	}
	else if (vl_count_realizado == 1){
		document.getElementById('REALIZADO_0').checked = true;
	}		
	else{
		alert('¡Solo se puede marcar REALIZADO una conversación!');
		document.getElementById('REALIZADO_CONV_'+record).checked = false;
		return false;
	}
	return true;
}

function realizado (ve_seleccion){
	var seleccion = ve_seleccion.checked;

	//desmarca las conversaciones realizadas 	
	if (!seleccion){		
		aTR = get_TR('CONVERSACION');
		for (var i=0; i < aTR.length; i++) {
			var record_b = get_num_rec_field(aTR[i].id);
			var realizado_conv = document.getElementById('REALIZADO_CONV_'+record_b).checked;
			
			if (realizado_conv){
				document.getElementById('REALIZADO_CONV_'+record_b).checked = false;
			}
		}
	}
	return true;
}