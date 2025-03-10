function validate() {
	var cod_estado_gr_value = get_value('COD_ESTADO_GUIA_RECEPCION_0');
	if (to_num(cod_estado_gr_value) == 3){
		var motivo_anula = document.getElementById('MOTIVO_ANULA_0');
		if (motivo_anula.value == '') {
			alert('Debe ingresar el motivo de Anulación antes de grabar.');
			motivo_anula.focus();
			return false;
		}
	}

	if(cod_estado_gr_value == 1){
		var aTR = get_TR('ITEM_GUIA_RECEPCION');
		var res= 0;
			
		if (aTR.length==0) {
			alert('Debe ingresar al menos 1 item antes de grabar.');
			return false;
		}

		for (i=0; i<aTR.length; i++){
			var rec_tr =get_num_rec_field(aTR[i].id);
			var cantidad = document.getElementById('CANTIDAD_' + rec_tr).value;
			cantidad = cantidad.replace(",",".")
			res = res + parseFloat(cantidad);
		}
					
		if (res == 0){
			alert('Debe ingresar "Cantidad" antes de grabar');
			document.getElementById('CANTIDAD_0').focus();
			return false;
		}
	}
	
	const resuelta	= document.getElementById('GR_RESUELTA_0').checked;
	if(resuelta){
		const tipo_doc	= get_value('COD_DOC_GR_RESUELTA_0');
		const cod_doc	= get_value('COD_DOC_RESUELTA_0');
		if(tipo_doc == '' || cod_doc == ''){
			alert('Favor debe indicar un tipo de Documento, y un número de documento con el cual se confirme esta Guía de recepción como resuelta');
			return false;
		}
	}

	return true;
}

function valida_doc(){
	const tipo_doc	= get_value('COD_DOC_GR_RESUELTA_0');
	const cod_doc	= get_value('COD_DOC_RESUELTA_0');
	const dropdown	= document.getElementById('COD_DOC_GR_RESUELTA_0');
	const text		= dropdown.options[dropdown.selectedIndex].text;

	if(tipo_doc != '' && cod_doc != ''){
		ajax = nuevoAjax();
		ajax.open("GET", "ajax_guia_recepcion.php?tipo_doc="+tipo_doc+"&cod_doc="+cod_doc+"&fx=valida_doc", false);
		ajax.send(null);
		const resp = ajax.responseText;
		
		if(resp == 'NO_EXISTE'){
			alert('El numero de '+text+' ingresado no existe.');
			set_value('COD_DOC_RESUELTA_0', '', '');
		}else if(resp == 'ANULADO'){
			alert('El numero de '+text+' ingresado está en estado "Anulado".');
			set_value('COD_DOC_RESUELTA_0', '', '');
		}
	}
}

function dlg_print(){
	var cod_est_gr_value_ant = get_value('COD_ESTADO_GUIA_RECEPCION_H_0');
	
	if(cod_est_gr_value_ant == 1){
		var cod_usu_resp	= get_value('COD_USUARIO_RESPONSABLE_H_0');
		var cod_tipo_recep	= get_value('TIPO_RECEPCION_H_0');
		var obs_post_venta	= get_value('OBS_POST_VENTA_H_0');
		
		if(cod_usu_resp == "" || cod_tipo_recep == "" || obs_post_venta == ""){
			if(cod_usu_resp == "")
				alert("Debe ingresar \"Usuario responsable\" antes de grabar.");
			else if(cod_tipo_recep == "")
				alert("Debe ingresar \"Tipo Recepción\" antes de grabar.");
			else
				alert("Debe ingresar \"Observación\" antes de grabar.");
			
			return false;
		}
	}

	const url = "dlg_tipo_impresion_gr.php";
	$.showModalDialog({
		url: url,
		dialogArguments: '',
		height: 240,
		width: 360,
		scrollable: false,
		onClose: function(){ 
			const returnVal = this.returnValue;
			if (returnVal == null)	
				return false;
			else{
				const input = document.createElement("input");
				input.setAttribute("type", "hidden");
				input.setAttribute("name", "b_print_x");
				input.setAttribute("id", "b_print_x");
				document.getElementById("input").appendChild(input);
								
				document.getElementById('wi_hidden').value = returnVal;
				document.input.submit();
				return false;
			}
		}
	});
}

function recepcionar_todo(){
	var aTR = get_TR('ITEM_GUIA_RECEPCION');

	if (aTR.length==0) {
		alert('No existe item para recepcionar.');
		return false;
	}else{
		for (i=0; i<aTR.length; i++){
			var rec_tr =get_num_rec_field(aTR[i].id);
			var cantidad = document.getElementById('CANTIDAD_' + rec_tr).value;
			var por_recepcionar = document.getElementById('POR_RECEPCIONAR_H_' + rec_tr).value;
			
			document.getElementById('CANTIDAD_'+ rec_tr).value = por_recepcionar;
		}
	}
}

function valida_ct_x_gd(ve_campo){
	var aTR = get_TR('ITEM_GUIA_RECEPCION');
	var res= 0;
		
		for (i=0; i<aTR.length; i++){
		var rec_tr =get_num_rec_field(aTR[i].id);
		var cantidad = parseFloat(document.getElementById('CANTIDAD_' + rec_tr).value);
		res = res + parseFloat(document.getElementById('CANTIDAD_' + rec_tr).value);
		var por_recepcionar = parseFloat(document.getElementById('POR_RECEPCIONAR_H_' + rec_tr).value);
		var tipo_gr = to_num(document.getElementById('COD_TIPO_GUIA_RECEPCION_0').value);
		if(tipo_gr!= 3){
			if(cantidad > por_recepcionar){
				alert('La "Cantidad" no puede ser mayor que "por Recepcionar"');
				document.getElementById('CANTIDAD_'+ rec_tr).value = por_recepcionar;
				document.getElementById('CANTIDAD_'+ rec_tr).type='text';
				document.getElementById('CANTIDAD_'+ rec_tr).setAttribute('onblur', "this.style.border=''");				
				document.getElementById('CANTIDAD_'+ rec_tr).setAttribute('onfocus', "this.style.border='1px solid #FF0000'");	
				document.getElementById('CANTIDAD_'+ rec_tr).focus();
				return ve_campo.value;
			}
		}
		
	}//fin for		
	return ve_campo.value;
}

// funcion que despliega un tipo texto si es que el cod_doc_sii='anulada' 
function mostrarOcultar_Anula() { 
	var tr_anula = document.getElementById('tr_anula');
	var cod_estado_gr = get_value('COD_ESTADO_GUIA_RECEPCION_0'); 
	
	if (to_num(cod_estado_gr)== 3) {
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

function mostrarOcultar_datos() {
	var tr_tipo_doc = document.getElementById('tr_tipo_doc');
	var cod_tipo_gr = get_value('COD_TIPO_GUIA_RECEPCION_0'); 
	if (to_num(cod_tipo_gr)== 3) {
		document.getElementById('TIPO_DOC_0').value = '';
		document.getElementById('NRO_DOC_0').value = '';
		document.getElementById('RUT_0').value = '';
		document.getElementById('DIG_VERIF_0').innerHTML = '';
		document.getElementById('ALIAS_0').value = '';
		document.getElementById('COD_EMPRESA_0').value = '';
		document.getElementById('NOM_EMPRESA_0').value = '';
		document.getElementById('GIRO_0').innerHTML = '';
		document.getElementById('COD_SUCURSAL_FACTURA_0').value = '';
		document.getElementById('DIRECCION_FACTURA_0').innerHTML = '';
		document.getElementById('COD_PERSONA_0').value = '';
		tr_tipo_doc.style.display = 'none'; 
	}
	else{
	document.getElementById('NRO_DOC_0').value = '';
	document.getElementById('RUT_0').value = '';
	document.getElementById('DIG_VERIF_0').innerHTML = '';
	document.getElementById('ALIAS_0').value = '';
	document.getElementById('COD_EMPRESA_0').value = '';
	document.getElementById('NOM_EMPRESA_0').value = '';
	document.getElementById('GIRO_0').innerHTML = '';
	document.getElementById('COD_SUCURSAL_FACTURA_0').value = '';	
	document.getElementById('DIRECCION_FACTURA_0').innerHTML = '';
	document.getElementById('COD_PERSONA_0').value = '';
	tr_tipo_doc.style.display = ''; 
		
	document.getElementById('TIPO_DOC_0').setAttribute('onblur', "this.style.border=''");				
	document.getElementById('TIPO_DOC_0').setAttribute('onfocus', "this.style.border='1px solid #FF0000'");	
	document.getElementById('TIPO_DOC_0').focus();
	}
	var aTR = get_TR('ITEM_GUIA_RECEPCION');
	for (i=0; i<aTR.length; i++){
		//del_line(aTR[i].id, 'guia_recepcion'); 
	}
}

function mostrarOcultar_nro_doc() {
	var tr_tipo_doc = document.getElementById('tr_tipo_doc');
	var cod_tipo_gr = get_value('COD_TIPO_GUIA_RECEPCION_0'); 
	if (to_num(cod_tipo_gr)== 3) {
		//document.getElementById('TIPO_DOC_0').value = '';
		document.getElementById('NRO_DOC_0').value = '';
		document.getElementById('RUT_0').value = '';
		document.getElementById('DIG_VERIF_0').innerHTML = '';
		document.getElementById('ALIAS_0').value = '';
		document.getElementById('COD_EMPRESA_0').value = '';
		document.getElementById('NOM_EMPRESA_0').value = '';
		document.getElementById('GIRO_0').innerHTML = '';
		document.getElementById('COD_SUCURSAL_FACTURA_0').value = '';
		document.getElementById('DIRECCION_FACTURA_0').innerHTML = '';
		document.getElementById('COD_PERSONA_0').value = '';
		tr_tipo_doc.style.display = 'none'; 
	}
	else{
		//document.getElementById('TIPO_DOC_0').value = 'none';
		document.getElementById('RUT_0').value = '';
		document.getElementById('DIG_VERIF_0').innerHTML = '';
		document.getElementById('ALIAS_0').value = '';
		document.getElementById('COD_EMPRESA_0').value = '';
		document.getElementById('NOM_EMPRESA_0').value = '';
		document.getElementById('GIRO_0').innerHTML = '';
		document.getElementById('COD_SUCURSAL_FACTURA_0').value = '';	
		document.getElementById('DIRECCION_FACTURA_0').innerHTML = '';
		document.getElementById('COD_PERSONA_0').value = '';
		tr_tipo_doc.style.display = ''; 	
		document.getElementById('TIPO_DOC_0').setAttribute('onblur', "this.style.border=''");				
		document.getElementById('TIPO_DOC_0').setAttribute('onfocus', "this.style.border='1px solid #FF0000'");	
		document.getElementById('NRO_DOC_0').focus();
	}
	var aTR = get_TR('ITEM_GUIA_RECEPCION');
	for (i=0; i<aTR.length; i++){
		//del_line(aTR[i].id, 'guia_recepcion'); 
	}
}

// funcion que oculta el tr si el tipo de documento es = "OTRO"
function mostrarOcultar_tipo_doc(defecto = 'S') { 
	var tr_tipo_doc = document.getElementById('tr_tipo_doc');
	var cod_tipo_gr = get_value('COD_TIPO_GUIA_RECEPCION_0'); 
	
	if (to_num(cod_tipo_gr)== 3) {
		if(defecto == 'S'){
			document.getElementById('TIPO_DOC_0').value = '';
			document.getElementById('NRO_DOC_0').value = '';
			document.getElementById('RUT_0').value = '';
			document.getElementById('DIG_VERIF_0').innerHTML = '';
			document.getElementById('ALIAS_0').value = '';
			document.getElementById('COD_EMPRESA_0').value = '';
			document.getElementById('NOM_EMPRESA_0').value = '';
			document.getElementById('GIRO_0').innerHTML = '';
			document.getElementById('COD_SUCURSAL_FACTURA_0').value = '';
			document.getElementById('DIRECCION_FACTURA_0').innerHTML = '';
			document.getElementById('COD_PERSONA_0').value = '';
		}
		tr_tipo_doc.style.display = 'none'; 
	}
	else{
		if(defecto == 'S'){
			if(document.getElementById('TIPO_DOC_0'))
				document.getElementById('TIPO_DOC_0').value = '';
			if(document.getElementById('NRO_DOC_0'))
				document.getElementById('NRO_DOC_0').value = '';
			document.getElementById('RUT_0').value = '';
			document.getElementById('DIG_VERIF_0').innerHTML = '';
			document.getElementById('ALIAS_0').value = '';
			document.getElementById('COD_EMPRESA_0').value = '';
			document.getElementById('NOM_EMPRESA_0').value = '';
			document.getElementById('GIRO_0').innerHTML = '';
			document.getElementById('COD_SUCURSAL_FACTURA_0').value = '';	
			document.getElementById('DIRECCION_FACTURA_0').innerHTML = '';
			document.getElementById('COD_PERSONA_0').value = '';
		}
		
		tr_tipo_doc.style.display = ''; 
			
		document.getElementById('TIPO_DOC_0').setAttribute('onblur', "this.style.border=''");				
		document.getElementById('TIPO_DOC_0').setAttribute('onfocus', "this.style.border='1px solid #FF0000'");	
		document.getElementById('TIPO_DOC_0').focus();
		
		if (cod_tipo_gr==4) {	// ARRIENDO
			var vl_tipo_doc = document.getElementById('TIPO_DOC_0');
			vl_tipo_doc.length = 0;

			var vl_option = document.createElement("option");
			vl_option.value = ''; 
			vl_option.innerHTML = '';
			vl_tipo_doc.appendChild(vl_option);

			var vl_option = document.createElement("option");
			vl_option.value = 'ARRIENDO'; 
			vl_option.innerHTML = 'CONTRATO ARRIENDO';
			vl_tipo_doc.appendChild(vl_option);

			vl_tipo_doc.selectedIndex = 1;
		}
	}
	var aTR = get_TR('ITEM_GUIA_RECEPCION');
	for (i=0; i<aTR.length; i++){
		if(defecto == 'S')
			del_line(aTR[i].id, 'guia_recepcion'); 
	}
		
	var cod_tipo_gr = get_value('COD_TIPO_GUIA_RECEPCION_0'); 
	if(cod_tipo_gr == 3 ){
		 document.getElementById("RUT_0").removeAttribute("readOnly",0);
		 document.getElementById("ALIAS_0").removeAttribute("readOnly",0);
		 document.getElementById("COD_EMPRESA_0").removeAttribute("readOnly",0);
		 document.getElementById("NOM_EMPRESA_0").removeAttribute("readOnly",0);
	}else{
		document.getElementById("RUT_0").setAttribute("readOnly",0);
		document.getElementById("ALIAS_0").setAttribute("readOnly",0);
		document.getElementById("COD_EMPRESA_0").setAttribute("readOnly",0);
		document.getElementById("NOM_EMPRESA_0").setAttribute("readOnly",0);
	}
}


function mostrarOcultar_item(ve_campo){
	var td_item_r_label = document.getElementById('td_item_r_label');
	var td_item_e_label = document.getElementById('td_item_e_label');
	var td_item_recepcion = document.getElementById('RECEPCION_ITEM_GUIA_RECEPCION_0');
	var td_boton_mas  	= document.getElementById('td_boton_mas');
	var recepcionar_todo = document.getElementById('recepcionar_todo');
	var cod_tipo_gr = get_value('COD_TIPO_GUIA_RECEPCION_0');  
	
	if(to_num(cod_tipo_gr)== 3){
		td_item_r_label.style.display = 'none'; 
		td_item_e_label.style.display = ''; 
		td_boton_mas.style.display = '';
		recepcionar_todo.style.display = 'none';
		document.getElementById('val_items').style.display = 'block';		
		document.getElementById('val_items').focus();	
	}else if(cod_tipo_gr == ''){
		tr_tipo_doc.style.display = 'none'; 
		document.getElementById('val_items').style.display = 'none';
		recepcionar_todo.style.display = '';
	}else{
		td_item_r_label.style.display = '';
		td_item_e_label.style.display = 'none'; 
		td_boton_mas.style.display = 'none';
		document.getElementById('val_items').style.display = 'block';
		recepcionar_todo.style.display = '';
		document.getElementById('val_items').focus();
	}
		
	var cod_tipo_gr = get_value('COD_TIPO_GUIA_RECEPCION_0'); 
	if(cod_tipo_gr == 3 ){
		 document.getElementById("RUT_0").removeAttribute("readOnly",0);
		 document.getElementById("ALIAS_0").removeAttribute("readOnly",0);
		 document.getElementById("COD_EMPRESA_0").removeAttribute("readOnly",0);
		 document.getElementById("NOM_EMPRESA_0").removeAttribute("readOnly",0);
	}else{
		document.getElementById("RUT_0").setAttribute("readOnly",0);
		document.getElementById("ALIAS_0").setAttribute("readOnly",0);
		document.getElementById("COD_EMPRESA_0").setAttribute("readOnly",0);
		document.getElementById("NOM_EMPRESA_0").setAttribute("readOnly",0);
	}
	return ve_campo.value;
}

function add_line(tabla_id, nom_tabla) {
	var row 			= add_line_standard(tabla_id, nom_tabla);
	var cod_tipo_gr 	= to_num(get_value('COD_TIPO_GUIA_RECEPCION_0')); 

	if(to_num(cod_tipo_gr)== 3){
		td_item_r_label.style.display = 'none'; 
		td_item_e_label.style.display = ''; 
		td_boton_mas.style.display = '';	
		document.getElementById('val_items').style.display = 'block';		
		document.getElementById('val_items').focus();	
	}else if(cod_tipo_gr == ''){
		tr_tipo_doc.style.display = 'none'; 
		document.getElementById('val_items').style.display = 'none';
	}else{
		td_item_r_label.style.display = '';
		td_item_e_label.style.display = ''; 
		td_boton_mas.style.display = '';
		document.getElementById('val_items').style.display = 'block';		
		document.getElementById('val_items').focus();
	}	

}

function existe_fa_gd() {
	var nro_doc = document.getElementById('NRO_DOC_0');
	var tipo_doc = document.getElementById('TIPO_DOC_0');
	var cod_doc = document.getElementById('COD_DOC_0');
	if (tipo_doc.selectedIndex==0) {
		alert('Primero debe seleccionar "Tipo Documento"');
		return;
	}
  	
  	ajax = nuevoAjax();
  	ajax.open("GET", "../guia_recepcion/existe_fa_gd.php?tipo_doc="+tipo_doc.options[tipo_doc.selectedIndex].value+"&nro_doc="+nro_doc.value,false);
    ajax.send(null);
	var resp = ajax.responseText.split('|');
	
	if (resp[0]==0) {
		alert('El "Nro Documento" no existe, favor ingrese nuevamente');
		document.getElementById('NRO_DOC_0').value = '';	
		document.getElementById('NRO_DOC_0').focus();
		return;
	}
	cod_doc.value = resp[0];
	var cod_empresa = document.getElementById('COD_EMPRESA_0')
	cod_empresa.value = resp[1];
	help_empresa(cod_empresa, 'C');
	document.getElementById('COD_NOTA_VENTA_0').innerHTML = resp[2];
}

function select_1_empresa(valores, record) {
	set_values_empresa(valores, record);

	var tabla = document.getElementById('ITEM_GUIA_RECEPCION');
	
	// borra todos los tr
	while (tabla.firstChild) {
	  tabla.removeChild(tabla.firstChild);
	}
	
	var cod_doc		= document.getElementById('COD_DOC_0');
	var tipo_doc 	= document.getElementById('TIPO_DOC_0');
	ajax = nuevoAjax();
	ajax.open("GET", "../guia_recepcion/ajax_load_item_fa_gd.php?cod_doc="+cod_doc.value+"&tipo_doc="+tipo_doc.value,false);
    ajax.send(null);
    var resp = ajax.responseText.split('|'); 
    
    // Copia los TR a la tabla correspondiente, 
	// este codigo se copio desde general.js -> add_line
	var table_aux = document.createElement("TABLE"); 
	table_aux.innerHTML = resp;
 	var children = table_aux.childNodes;
	for (var i=0; i < children.length; i++) {
		if (children[i].nodeName=='TBODY') {
		  	var children2 = children[i].childNodes;
		  	for (j=0; j < children2.length; j++) {
				if (children2[j].nodeName=='TR') {
					var tr_contenido = children2[j].innerHTML;
					
					var tbody = null; 
					var child_tabla = tabla.childNodes;
					for (k=0; k < child_tabla.length; k++)
						if (child_tabla[k].nodeName=='TBODY') {
							tbody = child_tabla[k];
							break;
						}
					if (! tbody) {
						tbody = document.createElement("TBODY"); 
						tabla.appendChild(tbody);
					}		
					tbody.appendChild(children2[j]);
				}
			}
		}
	}	
	var cod_tipo_gr = get_value('COD_TIPO_GUIA_RECEPCION_0'); 
		if(cod_tipo_gr != 3){
		var aTR = get_TR('ITEM_GUIA_RECEPCION');
			for (i=0; i<aTR.length; i++){
				var record = get_num_rec_field(aTR[i].id);
				var cod_producto = document.getElementById('COD_PRODUCTO_' + record);
				var nom_producto = document.getElementById('NOM_PRODUCTO_' + record);
				cod_producto.setAttribute("readOnly",0);
				nom_producto.setAttribute("readOnly",0);
			}
		}
}

function compruebaBodega(ve_campo){
	var marca = ve_campo.checked;
	cod_guia = get_value('COD_GUIA_RECEPCION_0'); 
	if(cod_guia.length > 0){
		if (marca == true){
			ajax = nuevoAjax();
		  	ajax.open("GET", "../guia_recepcion/existe_eb_gd.php?cod_guia="+cod_guia,false);
		    ajax.send(null);
			var resp = ajax.responseText;
			
			if(resp > 0){
				ve_campo.checked = false;
				ve_campo.value = 'N';
			}
		}
	}
}

function valida_archivo(ve_control){
	const archivo = ve_control.value.toLowerCase();
	const archivoExt = archivo.substring(archivo.lastIndexOf('.'), archivo.length);
	
	if(archivoExt != '.jpg' && archivoExt != '.gif' && archivoExt != '.tiff'){
		alert('Debe ingresar un archivo valido');
		ve_control.value = '';
	}
	
	if(ve_control.files[0].size > (1024*1024)*10){
		alert('El peso del archivo supera los 10 megas');
		ve_control.value = '';
	}
}