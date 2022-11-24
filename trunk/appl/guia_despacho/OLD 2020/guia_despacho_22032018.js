function validate() {
	var cod_estado_doc_sii_value = get_value('COD_ESTADO_DOC_SII_0');
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
			alert('Debe ingresar el motivo de Anulación antes de grabar.');
			motivo_anula.focus();
			return false;
		}
	}
	return true;
}

function add_line_gd(ve_tabla_item, nomTabla) {
	var aTR = get_TR(ve_tabla_item);
	var VALOR_GD_H = document.getElementById('VALOR_GD_H_0').value;
	if (aTR.length >= VALOR_GD_H){
		alert('¡No se pueden agregar más ítems, se ha llegado al máximo permitido!');
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
			alert('No se pueden agregar Títulos a una Guia de Despacho.');
			if(cod_producto_old.value=='T'){ //es la primera vez que se ingresa el código
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
		return request('Ingrese el número de la Guía de Despacho:', vl_new_nro_guia_despacho);
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