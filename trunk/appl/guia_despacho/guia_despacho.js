function display_motivo_gd_facturada(){
	if(document.getElementById('AUTORIZA_GD_FACTURADA_0').checked)
		document.getElementById('ID_DISPLAY_GD_FACTURADA').style.display = '';
	else
		document.getElementById('ID_DISPLAY_GD_FACTURADA').style.display = 'none';
}

function NumText(ve_control){
	var string = ve_control.value;
    var out = '';
    //Se anaden las letras validas
    var filtro = 'abcdefghijklmn�opqrstuvwxyzABCDEFGHIJKLMN�OPQRSTUVWXYZ1234567890';//Caracteres validos
	
    for (var i=0; i<string.length; i++)
       if (filtro.indexOf(string.charAt(i)) != -1) 
	     out += string.charAt(i);
	     
    ve_control.value = out;
}

function ajax_tipo_gd_interno(ve_control){
	ajax = nuevoAjax();
  	ajax.open("GET", "ajax_tipo_gd_interno.php?ve_cod_tipo_guia_despacho="+ve_control.value, false);
    ajax.send(null);
	var resp = ajax.responseText;
	
	document.getElementById('NOM_TIPO_GD_INTERNO_SII_0').innerHTML = resp;
}

function validate() {
	
	if($("#viene_bnt_add").val() == 'S'){
		var cod_tipo_gd = parseInt($("#COD_TIPO_GUIA_DESPACHO_0").val());
		if(cod_tipo_gd == 2){
			if($("#COD_DOC_H_0").val().length == 0){
				alert('Debe ingresar una nota de venta en "Documento"');
				return false;
			}
		}
	}
	
	var vl_motivo_gd_fac = get_value('MOTIVO_GD_FACTURADA_0');
	var cod_estado_doc_sii_value = get_value('COD_ESTADO_DOC_SII_0');
	
	if(document.getElementById('AUTORIZA_GD_FACTURADA_0').checked == true && vl_motivo_gd_fac.trim() == ''){
		alert('Debe ingresar un motivo para marcar esta GD como Facturada.');
		document.getElementById('MOTIVO_GD_FACTURADA_0').focus();
		return false;
	}
	// cod_estado_doc_sii_value = 1 = emitida
	if (to_num(cod_estado_doc_sii_value) == 1){
		var aTR = get_TR('ITEM_GUIA_DESPACHO');
		var cant_total = 0;
		if (aTR.length==0) {
			alert('Debe ingresar al menos 1 item antes de grabar.');
			return false;
		}
	
		for (var i = 0; i < aTR.length; i++){
			cant_total = cant_total + document.getElementById('CANTIDAD_' + i).value;		
		}	
		
		if(cant_total == 0){
			alert('La Cantidad a Despachar debe ser superior a "0"');
			document.getElementById('CANTIDAD_0').focus();
			return false;
		}		
	}
	// cod_estado_doc_sii_value = 4 = anulada
	if (to_num(cod_estado_doc_sii_value) == 4){	
		var motivo_anula = document.getElementById('MOTIVO_ANULA_0');
		if (motivo_anula.value == '') {
			alert('Debe ingresar el motivo de Anulaci�n antes de grabar.');
			motivo_anula.focus();
			return false;
		}
	}
	
	if(document.getElementById('COD_USU_VENDEDOR_RESP_0')){
		if(document.getElementById('COD_USU_VENDEDOR_RESP_0').value == ""){
			alert("Debe seleccionar un usuario como \"VENDEDOR RESPONSABLE\" al cual se le notificar� v�a mail sobre esta GD.");
			document.getElementById('COD_USU_VENDEDOR_RESP_0').focus();
			return false;
		}
	}
	
	//valida campos  de observaciones
	var retirado_por = document.getElementById('RETIRADO_POR_0').value;
	var rut_rp = document.getElementById('RUT_RETIRADO_POR_0').value;
	var df_rp =document.getElementById('DIG_VERIF_RETIRADO_POR_0').value;
	var gia_trans =document.getElementById('GUIA_TRANSPORTE_0').value;
	var patente =document.getElementById('PATENTE_0').value;
		
	if((retirado_por == "") || (rut_rp == "") || (df_rp == "")|| (gia_trans == "")|| (patente == "") ){
		if((retirado_por == "") && (rut_rp == "") && (df_rp == "")&& (gia_trans == "")&& (patente == "") ){
			return true;
		}else{
			alert('Debe ingresar los datos de los campos RETIRA, RUT, GUIA TRANSPORTE, PATENTE en el �rea de observaciones.Si no desea especificarlos, debe dejarlos todos en blanco');
			return false;
		}			
	}else{
		return true;
	}

	return true;
}

function add_line_gd(ve_tabla_item, nomTabla) {
	var aTR = get_TR(ve_tabla_item);
	var VALOR_GD_H = document.getElementById('VALOR_GD_H_0').value;
	if (aTR.length >= VALOR_GD_H){
		alert('�No se pueden agregar m�s �tems, se ha llegado al m�ximo permitido!');
		return false;
		}
	else
		add_line(ve_tabla_item,nomTabla);
}

function change_item_guia_despacho(ve_valor, ve_campo) {
	var record_item_nc = get_num_rec_field(ve_valor.id);
	var item_value = document.getElementById('ITEM_' + record_item_nc).value;
	var cod_producto_old = document.getElementById('COD_PRODUCTO_OLD_' + record_item_nc);
	var cod_producto = document.getElementById('COD_PRODUCTO_' + record_item_nc);
	//var cod_item_nv = document.getElementById('COD_ITEM_NOTA_CREDITO_' + record_item_nc).value;
	
	if(ve_campo == 'COD_PRODUCTO' | ve_campo == 'NOM_PRODUCTO'){
	
		help_producto(ve_valor, 0);
		if(cod_producto.value == 'T'){
			alert('No se pueden agregar T�tulos a una Guia de Despacho.');
			if(cod_producto_old.value=='T'){ //es la primera vez que se ingresa el codigo
				document.getElementById('COD_PRODUCTO_' + record_item_nc).value = '';
				document.getElementById('NOM_PRODUCTO_' + record_item_nc).value = '';
			}
			else{
				cod_producto.value = cod_producto_old; 
				help_producto(cod_producto, 0); 
			}	
		}
	}	
}

function mostrarOcultar_Anula(ve_campo) {
	var tr_anula = document.getElementById('tr_anula');
	
	if (to_num(ve_campo.value)==4) {
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

function valida_ct_x_despachar(ve_campo) {
	// valida solo si la GD es creada desde
	var cod_doc = to_num(document.getElementById('COD_DOC_0').innerHTML);
		
	
	if (cod_doc != 0){
		var record = get_num_rec_field(ve_campo.id);
		var cant_por_despachar = to_num(document.getElementById('CANTIDAD_POR_DESPACHAR_' + record).innerHTML);
		var cant_ingresada = to_num(ve_campo.value);
			if (parseFloat(cant_por_despachar) < parseFloat(cant_ingresada)) {
				alert('El valor ingresado no puede ser mayor que la cantidad "por Despachar": '+ number_format(cant_por_despachar, 1, ',', '.'));
				return number_format(cant_por_despachar, 1, ',', '.');
			}
			else
				return ve_campo.value;
	}
	else
		return ve_campo.value;
}

function dlg_print() {
	var vl_nro_guia_despacho = document.getElementById('NRO_GUIA_DESPACHO_0').innerHTML;
	if (vl_nro_guia_despacho == '') {
		var vl_new_nro_guia_despacho = document.getElementById('NEW_NRO_GUIA_DESPACHO_0').value;
		return request('Ingrese el n�mero de la Gu�a de Despacho:', vl_new_nro_guia_despacho);
	}
	else {
		document.getElementById('wi_hidden').value = vl_nro_guia_despacho;
		return true;
	}
}
function entrable()
{
	if(document.getElementById('NRO_ORDEN_COMPRA_0'))
		return true;
	else
		return false;	
}
function enviar_dte(){
	if(entrable()){
		document.getElementById('entrable').value = 'entrable';
		if (validate_save()) {
			var input = document.createElement('input');
			input.setAttribute('type', 'hidden');
			input.setAttribute('name', 'b_print_dte_x');
			input.setAttribute('id', 'b_print_dte_x');
			document.getElementById('input').appendChild(input);
			document.input.submit(); 
		}
		else
			return false;
	}
	else{
			var input = document.createElement('input');
			input.setAttribute('type', 'hidden');
			input.setAttribute('name', 'b_print_dte_x');
			input.setAttribute('id', 'b_print_dte_x');
			document.getElementById('input').appendChild(input);
			document.input.submit(); 
	}	
	
}
function select_printer_dte() {
	
	var cod_guia_despacho = document.getElementById('COD_GUIA_DESPACHO_0').value;
	
	// retorna la cantudad de registros en IMPRESORA_DTE, si es cero 
	var ajax = nuevoAjax();
	ajax.open("GET", "../../../trunk/appl/factura/ajax_select_printer_dte.php", false);
	ajax.send(null);
	var resp = URLDecode(ajax.responseText);
	
	// retorna si es que esta  factura fue creada desde NV
	var ajax = nuevoAjax();
	ajax.open("GET", "ajax_gd_desde_nv.php?cod_guia_despacho="+cod_guia_despacho, false);
	ajax.send(null);
	var resp_desde_nv = URLDecode(ajax.responseText);
	
	if (resp != 0) {
		var url = "../../../trunk/appl/factura/select_printer_dte.php";
		$.showModalDialog({
			 url: url,
			 dialogArguments: '',
			 height: 150,
			 width: 370,
			 scrollable: false,
			 onClose: function(){ 
			 	var returnVal = this.returnValue;
			 	if (returnVal == null){		
					return false;
				}			
				else {
					document.getElementById('wi_impresora_dte').value = returnVal;
					enviar_dte();
					return true;
				}
			}
		});		
	}
	if(resp_desde_nv == 'S'){
			document.getElementById('wi_impresora_dte').value = 100
		}
	return false;			
}

function request(ve_prompt, ve_valor, ve_campo){
	var id_campo = ve_campo.id;
	var url = "../../../../commonlib/trunk/php/request.php?prompt="+URLEncode(ve_prompt)+"&valor="+URLEncode(ve_valor);
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 140,
		 width: 400,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	
			if(returnVal == null)		
				return false;		
			else{
				
				var ajax = nuevoAjax();
				ajax.open("GET", "ajax_valida_gd_parcial.php?cod_nota_venta="+returnVal, false);
				ajax.send(null);	
				var resp = ajax.responseText.split('|');
 				
 				if(resp[0] == 'ERROR'){
 					alert(resp[1]);
 					return false;
 				}

				var url2 = "request_gd_parcial.php?cod_doc="+returnVal+"&tipo_doc=NV";
					$.showModalDialog({
						 url: url2,
						 dialogArguments: '',
						 height: 530,
						 width: 1000,
						 scrollable: false,
						 onClose: function(){ 
						 	var returnVal2 = this.returnValue;
						 	if (returnVal2 == null){		
								return false;
							}else{
								var input = document.createElement("input");
								input.setAttribute("type", "hidden");
								input.setAttribute("name", "b_create_x");
								input.setAttribute("id", "b_create_x");
								document.getElementById("output").appendChild(input);
								
								document.getElementById('wo_hidden').value = returnVal2;
								document.output.submit();
							}
						}
					});
				
			  	return true;
			}
		}
	});
}

function valida_demo(){
	if($("#viene_bnt_add").val() == 'S'){
		var cod_tipo_gd = parseInt($("#COD_TIPO_GUIA_DESPACHO_0").val());
		if(cod_tipo_gd == 2){
			document.getElementById("COD_DOC_H_0").type  = "text";
			document.getElementById("COD_DOC_H_0").onchange = function() {
				validaNv();
				};
		}else{
			if(!$("#COD_DOC_H_0").attr("hidden")){
				document.getElementById("COD_DOC_H_0").type  = "hidden";
			}
		}
	}
}

function validaNv(){
	var cod_nv = $("#COD_DOC_H_0").val();
	
	parametros = { "cod_nv" : cod_nv};
	url = "ajax_valida_nv.php";

	$.ajax({    
		async: false,
		url: url,
		data: parametros,
		success: function(data)             
		{
			if(data == 'N'){
				$("#COD_DOC_H_0").val('');
				alert('Nota de Venta no existe');
			}
		}
	});
}

function dlg_display_fa(){
	const cod_nota_venta	= get_value('COD_NOTA_VENTA_H_0');
	const nro_factura		= get_value('NRO_FACTURA_H_0');

	$.showModalDialog({
		url: "dlg_factura.php?cod_nota_venta="+cod_nota_venta+"&nro_factura="+nro_factura,
		dialogArguments: '',
		height: 350,
		width: 550,
		scrollable: false,
		onClose: function(){ 
			const returnVal = this.returnValue;
			if (returnVal == null){		
				return false;
			}else{
				set_value('NRO_FACTURA_H_0', returnVal, returnVal);
			}
		}
	});
}