function sel_count(){
	var vl_aTR				= get_TR('ITEM_FACTURA');
	var vl_count			= 0;
	for (var i = 0; i < vl_aTR.length; i++){
		var vl_rec	= get_num_rec_field(vl_aTR[i].id);
		
		if(document.getElementById('SELECCION_'+vl_rec).checked)
			vl_count++;
	}
	
	document.getElementById('CANT_SEL_0').innerHTML = vl_count;
}

function sel_todo(vl_valor){
	var vl_aTR				= get_TR('ITEM_FACTURA');
	for (var i = 0; i < vl_aTR.length; i++){
		var vl_rec	= get_num_rec_field(vl_aTR[i].id);
		
		if(vl_valor == 'S')
			document.getElementById('SELECCION_'+vl_rec).checked = true;
		else
			document.getElementById('SELECCION_'+vl_rec).checked = false;
		
		value_check(document.getElementById('SELECCION_'+vl_rec));
	}
	
	sel_count();
}

function dej_sel(){
	var vl_aTR	= get_TR('ITEM_FACTURA');
	var vl_count = 0;
	
	for (var j = 0; j < vl_aTR.length; j++){
		var vl_rec	= get_num_rec_field(vl_aTR[j].id);
	
		if(document.getElementById('SELECCION_'+vl_rec).checked)
			vl_count++;
	}
	
	if(vl_count > 0){
		for (var i = 0; i < vl_aTR.length; i++){
			var vl_rec	= get_num_rec_field(vl_aTR[i].id);
			
			if(!document.getElementById('SELECCION_'+vl_rec).checked)
				document.getElementById('ITEM_FACTURA_'+vl_rec).style.display = 'none';
			
			value_check(document.getElementById('SELECCION_'+vl_rec));
		}
	}	
}

function value_check(ve_control){
	var vl_rec		= get_num_rec_field(ve_control.id);
	var vl_nom_porc	= get_nom_field(ve_control.id);

	if(vl_nom_porc == 'SELECCION'){
		if(ve_control.checked == true){
			var vl_cantidad	= document.getElementById('CANTIDAD_'+vl_rec).innerHTML;
			document.getElementById('CANTIDAD_X_FACTURAR_'+vl_rec).value = findAndReplace(vl_cantidad, '.', '');
		}else{
			document.getElementById('CANTIDAD_X_FACTURAR_'+vl_rec).value = 0;
		}
	}else{
		var vl_cantidad			= findAndReplace(document.getElementById('CANTIDAD_'+vl_rec).innerHTML, '.', '');
		var cantidad_x_facturar	= document.getElementById('CANTIDAD_X_FACTURAR_'+vl_rec).value;
		
		if(vl_cantidad < cantidad_x_facturar){
			alert('Se esta ingresando mas de la cantidad permitida.');
			document.getElementById('CANTIDAD_X_FACTURAR_'+vl_rec).value = vl_cantidad
		}
		
		if(vl_cantidad == cantidad_x_facturar)
			document.getElementById('SELECCION_'+vl_rec).checked = true;
		else
			document.getElementById('SELECCION_'+vl_rec).checked = false;	
	}
}

function get_returnVal(){
	var vl_aTR				= get_TR('ITEM_FACTURA');
	var vl_cantidad_max		= document.getElementById('MAX_ITEM_0').value;
	var vl_cod_nota_venta	= document.getElementById('COD_NOTA_VENTA_0').value;
	var count				= 0;
	var vl_result			= "";
	var vl_opcion			= "";
	
	for (var i = 0; i < vl_aTR.length; i++){
		var vl_rec					= get_num_rec_field(vl_aTR[i].id);
		var vl_cantidad				= document.getElementById('CANTIDAD_X_FACTURAR_'+vl_rec).value;
		var vl_cod_item_nota_venta	= document.getElementById('COD_DOC_'+vl_rec).value;
		
		if(vl_cantidad == '')
			vl_cantidad = 0;
			
		if(vl_cantidad > 0)
			count++;
		
		if(vl_cantidad != 0)	
			vl_result = vl_result + vl_cod_item_nota_venta + ',' + vl_cantidad + '|';
	}
	
	if(count == 0){
		alert('Debe al menos seleccionar una cantidad');
		return false;
	}
	
	if(count > vl_cantidad_max){
		var vl_cant_fa = parseFloat(count)/parseFloat(vl_cantidad_max);
		
		if(vl_cant_fa%1 != 0)
			vl_cant_fa++;
		
		vl_cant_fa = Math.trunc(vl_cant_fa);

		var vl_confirm = confirm("Se han seleccionado "+count+" Items a despachar desde la Nota de Venta "+vl_cod_nota_venta+".\nSe generarán "+vl_cant_fa+" Guías de despacho\n\n¿Está seguro?");
		
		if(!vl_confirm)
			return false;
	}
	
	var vl_result = vl_result.substring(0, vl_result.length - 1);
	
	return vl_cod_nota_venta+'-'+vl_result;
}

// DIALOG Required Code
var prntWindow = getParentWindowWithDialog(); //$(top)[0];

var $dlg = prntWindow && prntWindow.$dialog;

function getParentWindowWithDialog(){
	var p = window.parent;
	var previousParent = p;
	while (p != null) {
		if ($(p.document).find('#iframeDialog').length) return p;

		p = p.parent;

		if (previousParent == p) return null;

		// save previous parent

		previousParent = p;
	}
	return null;
}

function setWindowReturnValue(value) {
	if ($dlg) $dlg.returnValue = value;
	window.returnValue = value; // in case popup is called using showModalDialog

}

function getWindowReturnValue() {
	// in case popup is called using showModalDialog

	if (!$dlg && window.returnValue != null)
		return window.returnValue;

	return $dlg && $dlg.returnValue;
}

if ($dlg) window.dialogArguments = $dlg.dialogArguments;
if ($dlg) window.close = function() { if ($dlg) $dlg.dialogWindow.dialog('close'); };
// END of dialog Required Code

   function okMe() {
   	var vl_value = get_returnVal();
   	if(!vl_value) 
		return false; 
	else{
		returnValue=vl_value;
		setWindowReturnValue(returnValue);
	}	
	$dlg.dialogWindow.dialog('close');
   }
   function closeMe() {
       setWindowReturnValue(null);
       $dlg.dialogWindow.dialog('close');
}