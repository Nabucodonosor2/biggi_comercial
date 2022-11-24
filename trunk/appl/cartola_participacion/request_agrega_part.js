function sel_count(){
	var vl_aTR				= get_TR('ITEM_CARTOLA_PARTICIPACION');
	var vl_count			= 0;
	for (var i = 0; i < vl_aTR.length; i++){
		var vl_rec	= get_num_rec_field(vl_aTR[i].id);
		
		if(document.getElementById('SELECCION_'+vl_rec).checked)
			vl_count++;
	}
	
	document.getElementById('CANT_SEL_0').innerHTML = vl_count;
}

function sel_todo(vl_valor){
	var vl_aTR				= get_TR('ITEM_CARTOLA_PARTICIPACION');
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
				document.getElementById('COD_PARTICIPACION_'+vl_rec).style.display = 'none';
			
			value_check(document.getElementById('SELECCION_'+vl_rec));
		}
	}	
}

function value_check(ve_control){
/*	var vl_rec		= get_num_rec_field(ve_control.id);
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
	}*/
}

function get_returnVal(){
	var vl_aTR	= get_TR('ITEM_CARTOLA_PARTICIPACION');

	var dato =new Array();
	var num=0;
	for (var i = 0; i < vl_aTR.length; i++){
		var vl_rec					= get_num_rec_field(vl_aTR[i].id);
	
		var cod_participacio = document.getElementById('COD_PARTICIPACION_'+vl_rec).innerHTML;		
		var fecha = document.getElementById('FECHA_PARTICIPACION_'+vl_rec).innerHTML;
		var nom_vendedor = document.getElementById('NOM_USUARIO_VENDEDOR_'+vl_rec).innerHTML;
		var nom_estado = document.getElementById('NOM_ESTADO_PARTICIPACION_'+vl_rec).innerHTML;
		var total = document.getElementById('TOTAL_CON_IVA_'+vl_rec).innerHTML;
		var mes = document.getElementById('MES_'+vl_rec).value;	
		var total=Number(total.replace('.',''));
		
		var fila= cod_participacio+'|'+fecha+'|'+nom_vendedor+'|'+nom_estado+'|'+total+'|'+mes;
			
		if(document.getElementById('SELECCION_'+vl_rec).checked == true){
			dato[num]=fila;
			num=num+1;
		}		
	}		
	return dato;		
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