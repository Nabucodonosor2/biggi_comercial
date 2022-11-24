function display_check(){
	if (document.getElementById('gas').checked)
		document.getElementById('check_tot_consumo').style.display = '';
	else{
		document.getElementById('check_tot_consumo').style.display = 'none';
		document.getElementById('total_consumo').checked = false;
	}	
}

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
	else if (document.getElementById('res_int').checked)
		res = res + "res_int|";	
		
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
	
	if(document.getElementById('MUESTRA_0').value == 'SI'){
		if(document.getElementById('item').checked){
			res = res + "item|";
		}else if(document.getElementById('descuento').checked){
			res = res + "total|";
		}
	}
	
	if(document.getElementById('gas').checked && document.getElementById('total_consumo').checked)
		res = res + "S|";
	
	if(document.getElementById('test_resumen').checked && 
			(document.getElementById('resumen').checked || document.getElementById('ampliada').checked)){
		res = 'test_resumen|';
	}
	if(document.getElementById('test_ampliada').checked && 
			(document.getElementById('resumen').checked || document.getElementById('ampliada').checked)){
		res = 'test_ampliada|noDesc';
	}
	
	if(document.getElementById('test_resumen').checked && 
			(document.getElementById('resumen').checked || document.getElementById('ampliada').checked)
		&& 	document.getElementById('itemTest').checked){
		res = 'test_resumen|item_test|';
	}
	if(document.getElementById('test_ampliada').checked && 
			(document.getElementById('resumen').checked || document.getElementById('ampliada').checked)
		&&	document.getElementById('descuentoTest').checked){
		res = 'test_ampliada|total_test|';
	}
	
	return res;
}

function show_tabla_lista_tecnica() {
	if(document.getElementById('resumen').checked){
		document.getElementById('ch_pdf').checked = true;
		document.getElementById('ch_excel').checked = false;
		document.getElementById('ch_pdf').disabled = false;
		document.getElementById('ch_excel').disabled = false;
		document.getElementById('descuentos').style.display = 'none';
		document.getElementById('div_embalaje').style.display = 'none';
		document.getElementById('tabla_lista_tecnica').style.display = 'none';
		document.getElementById('tr_tecnica').className = "";
	}else if(document.getElementById('ampliada').checked){
		document.getElementById('ch_pdf').checked = true;
		document.getElementById('ch_excel').checked = false;
		document.getElementById('ch_pdf').disabled = false;
		document.getElementById('ch_excel').disabled = false;
		document.getElementById('descuentos').style.display = 'none';
		document.getElementById('div_embalaje').style.display = 'none';
		document.getElementById('tabla_lista_tecnica').style.display = 'none';
		document.getElementById('tr_tecnica').className = "";
	}else if(document.getElementById('resumen_item').checked){
		document.getElementById('ch_pdf').checked = true;
		document.getElementById('ch_excel').checked = false;
		document.getElementById('ch_pdf').disabled = false;
		document.getElementById('ch_excel').disabled = true;
		document.getElementById('descuentos').style.display = 'none';
		document.getElementById('div_embalaje').style.display = 'none';
		document.getElementById('tabla_lista_tecnica').style.display = 'none';
		document.getElementById('tr_tecnica').className = "";
	}else if(document.getElementById('pesomedida').checked){
		document.getElementById('ch_pdf').checked = true;
		document.getElementById('ch_excel').checked = false;
		document.getElementById('ch_pdf').disabled = false;
		document.getElementById('ch_excel').disabled = false;
		document.getElementById('descuentos').style.display = 'none';
		document.getElementById('div_embalaje').style.display = '';
		document.getElementById('tabla_lista_tecnica').style.display = 'none';
		document.getElementById('tr_tecnica').className = "";
	}else if(document.getElementById('cad').checked || document.getElementById('res_int').checked){
		document.getElementById('ch_pdf').checked = false;
		document.getElementById('ch_excel').checked = true;
		document.getElementById('ch_pdf').disabled = true;
		document.getElementById('ch_excel').disabled = false;
		document.getElementById('descuentos').style.display = 'none';
		document.getElementById('div_embalaje').style.display = 'none';
		document.getElementById('tabla_lista_tecnica').style.display = 'none';
		document.getElementById('tr_tecnica').className = "";
	}else if(document.getElementById('tecnica').checked){
		document.getElementById('ch_pdf').checked = true;
		document.getElementById('ch_excel').checked = false;
		document.getElementById('ch_pdf').disabled = false;
		document.getElementById('ch_excel').disabled = false;
		document.getElementById('descuentos').style.display = 'none';
		document.getElementById('div_embalaje').style.display = 'none';
		document.getElementById('tabla_lista_tecnica').style.display = '';
		document.getElementById('tr_tecnica').className = "table_wm";
	}
}

function valida_descuento(muestra){
	if(document.getElementById('ch_excel').checked && (document.getElementById('resumen').checked || document.getElementById('ampliada').checked)){
		if(muestra == 'SI'){
			document.getElementById('descuentos').style.display = '';
		}
		else{
			document.getElementById('descuentos').style.display = 'none';
		}
	}else if(document.getElementById('ch_pdf').checked)
		document.getElementById('descuentos').style.display = 'none';
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
function testResumen(){
	if(document.getElementById('test_resumen').checked){
		document.getElementById('test_ampliada').checked = false;
	}
}
function testAmpliada(){
	if(document.getElementById('test_ampliada').checked){
		document.getElementById('test_resumen').checked = false;
	}
}
function noTest(){
	document.getElementById('test_ampliada').checked = false;
	document.getElementById('test_resumen').checked = false;
}