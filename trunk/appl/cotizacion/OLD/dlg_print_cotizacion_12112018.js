function get_returnVal() {
	/* Se deben retornar 4 valores separados por "|"
	   valor0 = "resumen" o "ampliada" o "lista_tecnica" o "pesos_y_medidas"
	   valor1 = "electrico" y/o "gas" etc, sepatardos por ";"
	   valor2 = "pdf" o "excel"
	   valor3 = "con_logo" o "sinlogo"
	   valor4 = "con_embalaje" o "sin_embalaje"
	*/
	var res = 	'';
	if (document.getElementById('resumen').checked)
		res = res + "resumen|";
	else if (document.getElementById('ampliada').checked)
		res = res + "ampliada|";
	else if (document.getElementById('pesomedida').checked)
		res = res + "pesomedida|";		
	else if (document.getElementById('tecnica').checked)
		res = res + "tecnica|";
	else if (document.getElementById('resumen_item').checked)
		res = res + "resumen_item|";
	else if (document.getElementById('cad').checked)
		res = res + "cad|";		
		
	// tipo impresion tecnica (si no es tecnica va ""
	if (!document.getElementById('tecnica').checked)
		res = res + "|";
	else{
		// si seleccionaron lista tecnica se verifica que seleccionaron
		if(document.getElementById('electrico').checked)
			res = res + "electrico¬";
		if(document.getElementById('gas').checked)
			res = res + "gas¬";
		if(document.getElementById('vapor').checked)
			res = res + "vapor¬";
		if(document.getElementById('agua').checked)
			res = res + "agua¬";
		if(document.getElementById('ventilacion').checked)
			res = res + "ventilacion¬";
		if(document.getElementById('desague').checked)
			res = res + "desague¬";
		
		res = res + "|";
	}
	// pdf o excel
	if (document.getElementById('ch_pdf').checked)
		res = res + "pdf|";
	if (document.getElementById('ch_excel').checked)
		res = res + "excel|";
	
	// con o sin logo
	if (document.getElementById('logo').checked)
		res = res + "logo|";
	else if (document.getElementById('sinlogo').checked)
		res = res + "sinlogo|";
		
	// con o sin embalaje (siempre y cuando sea "pesomedida")
	if(document.getElementById('pesomedida').checked){
		if(document.getElementById('c_caja').checked)
			res = res + "embalada|";
		else
			res = res + "noembalada|";
	}
	else
		res = res + "|";
	
	if(document.getElementById('item').checked){
		res = res + "item|";
	}
	else if(document.getElementById('descuento').checked){
		res = res + "total|";
	}
	
	return res;
}

function show_tabla_lista_tecnica(showme_1ista_tecnica, showme_formato) {
	var tabla_lista_tecnica = document.getElementById('tabla_lista_tecnica');
	var tabla_formato = document.getElementById('tabla_formato');
	//document.getElementById('excel').style.display = '';
	if (showme_1ista_tecnica) {
		tabla_lista_tecnica.style.display = ''; 
		window.innerHeight = 340; // aumenta el tamaño de la ventana de print en cotizacion
	}
	else if (showme_formato){
		tabla_lista_tecnica.style.display = 'none'; 
		window.innerHeight = 225; // disminuye el tamaño de la ventana de print en cotizacion
	}
	else{
		tabla_lista_tecnica.style.display = 'none'; 
		window.innerHeight = 225; // disminuye el tamaño de la ventana de print en cotizacion
	}
	
	if(document.getElementById('resumen_item').checked){
		document.getElementById('check_excel').style.display = 'none';
		document.getElementById('ch_pdf').checked = true;
		document.getElementById('ch_excel').checked = false;
	}else
		document.getElementById('check_excel').style.display = '';
	
	if(document.getElementById('cad').checked){
		document.getElementById('check_pdf').style.display = 'none';
		document.getElementById('ch_pdf').checked = false;
		document.getElementById('ch_excel').checked = true;
	}else
		document.getElementById('check_pdf').style.display = '';
	
	
	show_embalaje();
}

function show_embalaje(){
	if(document.getElementById('pesomedida').checked){
		document.getElementById('div_embalaje').style.display = '';
		window.innerHeight = 255;
	}else
		document.getElementById('div_embalaje').style.display = 'none';
	
}

function valida_descuento(muestra){
	if(document.getElementById('ch_excel').checked){
		if(muestra == 'SI'){
			document.getElementById('desc_item').style.display = '';
			document.getElementById('desc_total').style.display = '';
		}
		else{
			document.getElementById('desc_item').style.display = 'none';
			document.getElementById('desc_total').style.display = 'none';
		}
	}
	else if(document.getElementById('ch_pdf').checked){
		document.getElementById('desc_item').style.display = 'none';
		document.getElementById('desc_total').style.display = 'none';
	}
}

// DIALOG Required Code
var prntWindow = getParentWindowWithDialog(); //$(top)[0];

var $dlg = prntWindow && prntWindow.$dialog;

function getParentWindowWithDialog() {
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

function okMe(){
	returnValue=get_returnVal(); 
	setWindowReturnValue(returnValue);
	$dlg.dialogWindow.dialog('close');	
}

function closeMe(){
	setWindowReturnValue(null);
	$dlg.dialogWindow.dialog('close');
}