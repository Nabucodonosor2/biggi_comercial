function pinta_salida(){
	if(document.getElementById('GENERA_SALIDA_0').checked){
		document.getElementById('tdGSalida').className = "oscuro B-VERDE";
		document.getElementById('msj_salida').innerHTML = 'FACTURA PERMITE DESPACHAR EQUIPOS'
	}else{
		document.getElementById('tdGSalida').className = "oscuro B-ROJO";
		document.getElementById('msj_salida').innerHTML = 'FACTURA NO PERMITE DESPACHAR EQUIPOS'
	}
}
function f_valida_oc(){
	var vl_no_tiene_OC = document.getElementById('NO_TIENE_OC_0');
		
	if (vl_no_tiene_OC.checked) {
		document.getElementById('NRO_ORDEN_COMPRA_0').value = '';
		document.getElementById('NRO_ORDEN_COMPRA_0').readOnly = true;
		
		document.getElementById('FECHA_ORDEN_COMPRA_CLIENTE_0').value = '';
		document.getElementById('FECHA_ORDEN_COMPRA_CLIENTE_0').readOnly = true;
				
	}
	if (!vl_no_tiene_OC.checked) {
		document.getElementById('NRO_ORDEN_COMPRA_0').readOnly = false;
		document.getElementById('FECHA_ORDEN_COMPRA_CLIENTE_0').readOnly = false;
	}
}


function valida_referencias(ve_control){
	var vl_record = get_num_rec_field(ve_control.id);
	var vl_hem = document.getElementById('REFERENCIA_HEM_0').value;
	var vl_hes = document.getElementById('REFERENCIA_HES_0').value;
	var count1 = 0;
	var count2 = 0;
	var count_cto = 0;
	var count_m_cto = 0;
	var count_hep = 0;
	var count_802_ccu = 0;
	var count_migo = 0;

	var aTR = get_TR('REFERENCIAS');
	for(i = 0; i < aTR.length ; i++){
	 	var vl_rec = get_num_rec_field(aTR[i].id);
	 	
	 	if(document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value == 1)//HEM
	 		count1++;
	 	else if(document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value == 2)//HES
	 		count2++;
	 	else if(document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value == 3){//CONTACTO
	 		count_cto++;
	 	}else if(document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value == 4){//CORREO CONTACTO
	 		count_m_cto++;
	 		
	 		if(count_m_cto == 1){
		 		var theElement = document.getElementById('DOC_REFERENCIA_'+vl_rec);
		 		validate_mail(theElement);
			}
		}else if(document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value == 5)//HEP
	 		count_hep++;
	 	else if(document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value == 6)//802 CCU
	 		count_802_ccu++;
	 	else if(document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value == 7)//MIGO
	 		count_migo++;			
	}
	
	if(count1 > 2){
		alert('No puede ingresar mas referencias tipo HEM');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}
	
	if(count2 > 2){
		alert('No puede ingresar mas referencias tipo HES');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}
	
	/*if(vl_hem == 'S' && count2 > 0){
		alert('Esta empresa tiene como referencia HEM, no puede agregar referencias de tipo HES');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}
	
	if(vl_hes == 'S' && count1 > 0){
		alert('Esta empresa tiene como referencia HES, no puede agregar referencias de tipo HEM');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}*/
	
	if(count_cto > 1){
		alert('No debe ingresar mas de un tipo sea Contacto');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_record).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}
	if(count_m_cto > 1){
		alert('No debe ingresar mas de un correo de contacto');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_record).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}
	if(count_hep > 1){
		alert('No debe ingresar mas de un tipo HEP');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_record).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}
	if(count_802_ccu > 1){
		alert('No debe ingresar mas de un tipo 802 CCU');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_record).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}
	if(count_migo > 1){
		alert('No debe ingresar mas de un tipo MIGO');
		document.getElementById('COD_TIPO_REFERENCIA_'+vl_record).value = "";
		document.getElementById('DOC_REFERENCIA_'+vl_record).value = "";
	}
}

function validate_mail(theElement ) {
	var s = theElement.value;	
	var filter=/^[A-Za-z0-9][A-Za-z0-9_.-]*@[A-Za-z0-9_-]+\.[A-Za-z0-9_.-]+[A-za-z]$/;
	if (s.length == 0 ) return true;
	if (filter.test(s))
		return true;
	else
		alert("Ingrese una dirección de correo válida");
		theElement.value='';
		theElement.focus();
	return false;
}

function ajax_load_ref_hidden(){
	var vl_cod_empresa = document.getElementById('COD_EMPRESA_0').value;

	if(vl_cod_empresa != ""){
		var ajax = nuevoAjax();
		ajax.open("GET", "ajax_load_ref_hidden.php?vl_cod_empresa="+vl_cod_empresa, false);
		ajax.send(null);
		var resp = ajax.responseText.split("|");
		
		document.getElementById('REFERENCIA_HEM_0').value = resp[0];
		document.getElementById('REFERENCIA_HES_0').value = resp[1];
	}
}

function add_line_ref(ve_tabla_id, ve_nom_tabla){
	var vl_row = add_line(ve_tabla_id, ve_nom_tabla);
	return vl_row;
}

function change_fecha() {
	
	var fecha_nueva = document.getElementById('FECHA_FACTURA_0').value;
	alert(fecha_nueva+' valor');
	
	document.getElementById('FECHA_FACTURA_I_0').innerHTML = fecha_nueva;
	document.getElementById('FECHA_FACTURA_P_0').innerHTML = fecha_nueva;
	document.getElementById('FECHA_FACTURA_C_0').innerHTML = fecha_nueva;
	
	/* valida que no ingrese un fecha vacia*/
	if(fecha_nueva == ''){
		alert('Debe ingresar la fecha de la Factura');
		return false;
	}
}

function validate(){
	var vl_rut		= document.getElementById('RUT_0').value;
	var cc_cliente	= document.getElementById('CENTRO_COSTO_CLIENTE_0').value;

	var ajax = nuevoAjax();
	ajax.open("GET", "../nota_venta/ajax_valida_sodexo.php?ve_rut="+vl_rut, false);
	ajax.send(null);
	var resp = ajax.responseText;
	
	if(resp == 'ES_SODEXO' && cc_cliente == ''){
		alert('Debe ingresar un Centro Costo Cliente para esta empresa.');
		document.getElementById('NRO_ORDEN_COMPRA_0').focus();
		return false;
	}

	var vl_cod_tipo_factura = document.getElementById('COD_TIPO_FACTURA_H_0').value;
	var K_TIPO_ARRIENDO = 2;
	
	if (vl_cod_tipo_factura != K_TIPO_ARRIENDO) {
		var aTR = get_TR('ITEM_FACTURA');
		if (aTR.length==0) {
			alert('Debe ingresar al menos 1 item antes de grabar.');
			return false;
		}
	}
	var cod_estado_doc_sii_value = get_value('COD_ESTADO_DOC_SII_0'); 
	if (to_num(cod_estado_doc_sii_value) == 4){	
		var motivo_anula = document.getElementById('MOTIVO_ANULA_0');
		if (motivo_anula.value == '') {
			alert('Debe ingresar el motivo de Anulación antes de grabar.');
			motivo_anula.focus();
			return false;
		}
	}
	var porc_dscto1 = get_value('PORC_DSCTO1_0');
	var monto_dscto1 = get_value('MONTO_DSCTO1_0');
	var monto_dscto2 = get_value('MONTO_DSCTO2_0');
	var sum_total = document.getElementById('SUM_TOTAL_H_0');		
	var porc_dscto_max = document.getElementById('PORC_DSCTO_MAX_0');
	if (sum_total.value=='') sum_total.value = 0;
	if (monto_dscto1=='') monto_dscto2 = 0;
	if (monto_dscto2=='') monto_dscto2 = 0;
	if (((parseFloat(monto_dscto1) + parseFloat(monto_dscto2))/parseFloat(sum_total.value))*100 > parseFloat(porc_dscto_max.value)) {
		var monto_permitido = (parseFloat(sum_total.value) * parseFloat(porc_dscto_max.value)) / 100 ;
		alert('La suma de los descuentos es mayor al permitido (máximo '+number_format(porc_dscto_max.value, 0, ',', '.')+' % entre los dos descuentos, equivalente a '+number_format(monto_permitido, 0, ',', '.')+')');
		document.getElementById('PORC_DSCTO1_0').focus();
		return false;
	}
	var aTR = get_TR('BITACORA_FACTURA');
	for (var i = 0; i < aTR.length; i++){
		var tiene_compromiso = document.getElementById('TIENE_COMPROMISO_' + i).checked;
		if (tiene_compromiso == true){
			var fecha_compromiso = document.getElementById('FECHA_COMPROMISO_E_' + i).value;
			var hora_compromiso = document.getElementById('HORA_COMPROMISO_E_' + i).value;
			var glosa_compromiso = document.getElementById('GLOSA_COMPROMISO_E_' + i).value;
			if(fecha_compromiso == ''){
				alert('Debe ingresar la fecha del compromiso');
				return false;
			}
			else if (hora_compromiso == ''){
				alert('Debe ingresar la hora del compromiso');
				return false;
			}
			else if (glosa_compromiso == ''){
				alert('Debe ingresar la descripción del compromiso');
				return false;
			}
		}
	}
	
	var aTR = get_TR('REFERENCIAS');
	for(i = 0; i < aTR.length ; i++){
	 	var vl_rec = get_num_rec_field(aTR[i].id);
	 	
	 	if(document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value == 1){//HEM
	 		if(document.getElementById('FECHA_REFERENCIA_'+vl_rec).value == ''){
		 		alert('Debe ingresar una fecha para el tipo de referencia HEM');
		 		document.getElementById('FECHA_REFERENCIA_'+vl_rec).focus();
		 		return false;
		 	}	
	 	}else if(document.getElementById('COD_TIPO_REFERENCIA_'+vl_rec).value == 2){//HES
	 		if(document.getElementById('FECHA_REFERENCIA_'+vl_rec).value == ''){
		 		alert('Debe ingresar una fecha para el tipo de referencia HES');
		 		document.getElementById('FECHA_REFERENCIA_'+vl_rec).focus();
		 		return false;
		 	}	
	 	}
	}
	
	if (document.getElementById('COD_FORMA_PAGO_0')){
		var cod_forma_pago = document.getElementById('COD_FORMA_PAGO_0').options[document.getElementById('COD_FORMA_PAGO_0').selectedIndex].value;
		var nom_forma_pago_otro = document.getElementById('NOM_FORMA_PAGO_OTRO_0').value;
		
		if (parseFloat(cod_forma_pago) == 1 && nom_forma_pago_otro == ''){
			alert ('Debe ingresar la Descripción de la forma de pago seleccionada.');
			document.getElementById('NOM_FORMA_PAGO_OTRO_0').focus();
			return false;
		}
	}
	
	var vl_no_tiene_OC = document.getElementById('NO_TIENE_OC_0');
	var vl_orden_compra = document.getElementById('NRO_ORDEN_COMPRA_0').value;
	var vl_fecha_orden_compra = document.getElementById('FECHA_ORDEN_COMPRA_CLIENTE_0').value;

	if (!vl_no_tiene_OC.checked) {
		if(vl_orden_compra == ''){
			alert('Debe Ingresar Orden Compra Cliente');
			return false;
		}
		
		if(vl_fecha_orden_compra == ''){
			alert('Debe Ingresar Fecha Orden Compra Cliente');
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
			alert('Debe ingresar los datos de los campos RETIRA, RUT, GUIA TRANSPORTE, PATENTE en el área de observaciones.Si no desea especificarlos, debe dejarlos todos en blanco');
			return false;
		}			
	}else{
		return true;
	}
	return true;
}

function add_line_fa(ve_tabla_item, nomTabla) {
	var aTR = get_TR(ve_tabla_item);
	var VALOR_FA_H = document.getElementById('VALOR_FA_H_0').value;
	if (aTR.length >= VALOR_FA_H){
		alert('¡No se pueden agregar más ítems, se ha llegado al máximo permitido!');
		return false;
		}
	else
		add_line(ve_tabla_item,nomTabla);
}

function change_item_factura(ve_valor, ve_campo) {
	var record_item_f = get_num_rec_field(ve_valor.id);
	var item_value = document.getElementById('ITEM_' + record_item_f).value;
	var cod_producto_old = document.getElementById('COD_PRODUCTO_OLD_' + record_item_f);
	var cod_producto = document.getElementById('COD_PRODUCTO_' + record_item_f);
	if(ve_campo == 'COD_PRODUCTO' | ve_campo == 'NOM_PRODUCTO'){
		help_producto(ve_valor, 0);	
		if(cod_producto.value == 'T'){
			alert('No se pueden agregar Títulos.');
			if(cod_producto_old.value=='T'){ //es la primera vez que se ingresa el código
				document.getElementById('COD_PRODUCTO_' + record_item_f).value = '';
				document.getElementById('NOM_PRODUCTO_' + record_item_f).value = '';
			}
			else{
				cod_producto.value = cod_producto_old; 
				help_producto(cod_producto, 0); 
			}	
		}
	}	
}

// funcion que despliega un tipo texto si es que el cod_doc_sii='anulada' 
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

// funcion que despliega un tipo texto si es que la forma de pago =='otro'
function mostrarOcultar(ve_cod_forma_pago) {
	var cod_forma_pago = ve_cod_forma_pago.options[ve_cod_forma_pago.selectedIndex].value; 	
	if (parseFloat(cod_forma_pago) == 1){
    	document.getElementById('NOM_FORMA_PAGO_OTRO_0').type='text';
		document.getElementById('NOM_FORMA_PAGO_OTRO_0').setAttribute('onblur', "this.style.border=''");					
   
    }
    else{
    	document.getElementById('NOM_FORMA_PAGO_OTRO_0').value='';
    	document.getElementById('NOM_FORMA_PAGO_OTRO_0').type='hidden';
    	document.getElementById('AA').type='hidden';
  
    }
}

function change_forma_pago(ve_tipo_forma_pago, ve_cod_forma_pago) {
	if (ve_tipo_forma_pago == 'OTRO')  // forma de pago = OTRO
		var cant_docs = document.getElementById('CANTIDAD_DOC_FORMA_PAGO_OTRO_0').value;			
	else{
		mostrarOcultar(ve_cod_forma_pago);
		var cant_docs = ve_cod_forma_pago.options[ve_cod_forma_pago.selectedIndex].label;
	}
}

function valida_ct_x_facturar(ve_campo) {
	// valida solo si la GD es creada desde
	var cod_doc = to_num(document.getElementById('COD_DOC_0').innerHTML);
	
	if (cod_doc != 0){
		var record = get_num_rec_field(ve_campo.id);
		var cant_por_facturar = to_num(document.getElementById('CANTIDAD_POR_FACTURAR_' + record).innerHTML);
		var cant_ingresada = to_num(ve_campo.value);
			if (parseFloat(cant_por_facturar) < parseFloat(cant_ingresada)) {
				alert('El valor ingresado no puede ser mayor que la cantidad "por Facturar": '+ number_format(cant_por_facturar, 1, ',', '.'));
				return number_format(cant_por_facturar, 1, ',', '.');
			}
			else
				return ve_campo.value;
	}
	else
		return ve_campo.value;
}

//////////////////////////
/// bitacora cobranza
function compromiso_realizado(ve_compromiso_realizado) {
	var vl_record = get_num_rec_field(ve_compromiso_realizado.id);
	if (ve_compromiso_realizado.checked) {
		var currentTime = new Date();
		var day = currentTime.getDate();
		var month = currentTime.getMonth() + 1;
		var year = currentTime.getFullYear();
		var hora = currentTime.getHours();
		var minuto = currentTime.getMinutes();
		document.getElementById('FECHA_REALIZADO_' + vl_record).innerHTML = sprintf("%02d/%02d/%04d", day, month, year);
		document.getElementById('HORA_REALIZADO_' + vl_record).innerHTML = sprintf("%02d:%02d", hora, minuto);

		//Ajax para obtener el usuario actual
		var ajax = nuevoAjax();
		ajax.open("GET", "ajax_ini_usuario_actual.php", false);
		ajax.send(null);
		var resp = URLDecode(ajax.responseText);
		document.getElementById('INI_USUARIO_REALIZADO_' + vl_record).innerHTML = resp;
	}
	else {
		document.getElementById('FECHA_REALIZADO_' + vl_record).innerHTML = '';
		document.getElementById('HORA_REALIZADO_' + vl_record).innerHTML = '';
		document.getElementById('INI_USUARIO_REALIZADO_' + vl_record).innerHTML = '';
	}
}
function tiene_compromiso(ve_tiene_compromiso) {
	var vl_record = get_num_rec_field(ve_tiene_compromiso.id);
	if (ve_tiene_compromiso.checked) {
		// hace entrables los campos
		document.getElementById("FECHA_COMPROMISO_E_" + vl_record).type = 'text'; 
		document.getElementById("FECHA_COMPROMISO_S_" + vl_record).style.display = 'none'; 
		document.getElementById("HORA_COMPROMISO_E_" + vl_record).type = 'text'; 
		document.getElementById("HORA_COMPROMISO_S_" + vl_record).style.display = 'none'; 
		document.getElementById("GLOSA_COMPROMISO_E_" + vl_record).type = 'text'; 
		document.getElementById("GLOSA_COMPROMISO_S_" + vl_record).style.display = 'none'; 
		
		document.getElementById("COMPROMISO_REALIZADO_" + vl_record).removeAttribute("disabled");
	}
	else {
		// inicializa en vacio todos los campos que quedan no entrables
		document.getElementById("FECHA_COMPROMISO_E_" + vl_record).value = '';
		document.getElementById("FECHA_COMPROMISO_S_" + vl_record).innerHTML = '';
		document.getElementById("HORA_COMPROMISO_E_" + vl_record).value = '';
		document.getElementById("HORA_COMPROMISO_S_" + vl_record).innerHTML = '';
		document.getElementById("GLOSA_COMPROMISO_E_" + vl_record).value = '';
		document.getElementById("GLOSA_COMPROMISO_S_" + vl_record).innerHTML = '';

		// si no tiene compromiso, deja no visibel Realizado	
		document.getElementById("COMPROMISO_REALIZADO_" + vl_record).setAttribute("disabled",0);
		document.getElementById("COMPROMISO_REALIZADO_" + vl_record).checked = false;
		document.getElementById('FECHA_REALIZADO_' + vl_record).innerHTML = '';
		document.getElementById('HORA_REALIZADO_' + vl_record).innerHTML = '';
		document.getElementById('INI_USUARIO_REALIZADO_' + vl_record).innerHTML = '';

		// deja no entrables los campos
		document.getElementById("FECHA_COMPROMISO_E_" + vl_record).type = 'hidden'; 
		document.getElementById("FECHA_COMPROMISO_S_" + vl_record).style.display = ''; 
		document.getElementById("HORA_COMPROMISO_E_" + vl_record).type = 'hidden' 
		document.getElementById("HORA_COMPROMISO_S_" + vl_record).style.display = ''; 
		document.getElementById("GLOSA_COMPROMISO_E_" + vl_record).type = 'hidden' 
		document.getElementById("GLOSA_COMPROMISO_S_" + vl_record).style.display = ''; 
	}
}
function change_protected(ve_campo) {
	var vl_record = get_num_rec_field(ve_campo.id);
	var vl_field = get_nom_field(ve_campo.id);
	document.getElementById(vl_field.substr(0, vl_field.length - 1) + "S_" + vl_record).innerHTML = ve_campo.value;
}

function change_item_factura_anticipo(ve_cod_producto) {
	ve_cod_producto.value = ve_cod_producto.value.toUpperCase();
	if (ve_cod_producto.value != 'TE') {
		alert('Debe ingresar el anticipo como TE');
		ve_cod_producto.value = 'TE';
	}
	help_producto(ve_cod_producto, 0);	
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
function entrable()
{
	if(document.getElementById('NRO_ORDEN_COMPRA_0'))
		return true;
	else
		return false;	
}
function select_printer_dte() {
	var vl_cod_factura = document.getElementById('COD_FACTURA_0').value;
	
	// retorna la cantudad de registros en IMPRESORA_DTE, si es cero 
	var ajax = nuevoAjax();
	ajax.open("GET", "ajax_select_printer_dte.php", false);
	ajax.send(null);
	var resp = URLDecode(ajax.responseText);
	
	// retorna si es que esta  factura fue creada desde NV
		var ajax = nuevoAjax();
		ajax.open("GET", "ajax_factura_desde_nv.php?cod_factura="+vl_cod_factura, false);
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
				else{
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
function select_1_empresa(valores, record) {
	if(valores[1] != '1337'){
		set_values_empresa(valores, record);
	}else{
		alert('Usted no puede generar una factura para: COMERCIAL BIGGI CHILE S.A.\n\nFavor asegúrese de indicar el cliente correcto de esta factura');
		set_value('COD_EMPRESA_' + record, '', '');
		set_value('RUT_' + record, '', '');
		set_value('ALIAS_' + record, '', '');
		set_value('NOM_EMPRESA_' + record, '', '');
		set_value('DIG_VERIF_' + record, '', '');
		set_value('DIRECCION_FACTURA_' + record, '', '');
		set_value('DIRECCION_DESPACHO_' + record, '', '');
		set_value('GIRO_' + record, '', '');
		set_value('SUJETO_A_APROBACION_' + record, '', '');
		set_drop_down_vacio('COD_SUCURSAL_FACTURA_' + record);
		set_drop_down_vacio('COD_SUCURSAL_DESPACHO_' + record);
		set_drop_down_vacio('COD_PERSONA_' + record);
		set_value('MAIL_CARGO_PERSONA_' + record, '', '');
		set_value('COD_CUENTA_CORRIENTE_' + record, '', '');
		set_value('NOM_CUENTA_CORRIENTE_' + record, '', '');
		set_value('NRO_CUENTA_CORRIENTE_' + record, '', '');
	}
	ajax_load_ref_hidden();
	
	var aTR = get_TR('REFERENCIAS');
	for(i = 0; i < aTR.length ; i++){
	 	var vl_rec = get_num_rec_field(aTR[i].id);
	 	del_line('REFERENCIAS_'+vl_rec, 'factura');
	}
}

function f_valida_oc(){
	var vl_no_tiene_OC = document.getElementById('NO_TIENE_OC_0');
		
	if (vl_no_tiene_OC.checked) {
		document.getElementById('NRO_ORDEN_COMPRA_0').value = '';
		document.getElementById('NRO_ORDEN_COMPRA_0').readOnly = true;
		
		document.getElementById('FECHA_ORDEN_COMPRA_CLIENTE_0').value = '';
		document.getElementById('FECHA_ORDEN_COMPRA_CLIENTE_0').readOnly = true;
				
	}
	if (!vl_no_tiene_OC.checked) {
		document.getElementById('NRO_ORDEN_COMPRA_0').readOnly = false;
		document.getElementById('FECHA_ORDEN_COMPRA_CLIENTE_0').readOnly = false;
	}
}

$(document).ready(function () {
	$('#NRO_ORDEN_COMPRA_0').live('input', function (e) {
	    if (!/^[ a-z0-9áéíóúüñ-]*$/i.test(this.value)) {
	        this.value = this.value.replace(/[^ a-z0-9áéíóúüñ-]+/ig,"");
	    }
	});
});
