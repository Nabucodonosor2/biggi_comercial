$(document).ready(function () {
	quitar_descuentos();
});
function display_check(){
	if (document.getElementById('gas').checked)
		document.getElementById('check_tot_consumo').style.display = '';
	else{
		document.getElementById('check_tot_consumo').style.display = 'none';
		document.getElementById('total_consumo').checked = false;
	}	
}
function quitar_descuentos(){
	$("#tr_descuentos").hide();
}
function show_descuentos(){
	$("#tr_descuentos").show();
}
function quitar_exel(){
	$("#lavel_execl").hide();
}
function show_exel(){
	$("#lavel_execl").show(); 
}
function quitar_pdf(){
	$("#lavel_pdf").hide();
}
function show_pdf(){
	$("#lavel_pdf").show();
}
function show_pesoDimension(){
	$("#div_embalaje").show();
}
function quitar_pesoDimension(){
	$("#div_embalaje").hide();
}
function show_tecnica(){
	$("#tr_tecnico").show();
}
function quitar_tecnica(){
	$("#tr_tecnico").hide();
}

function ampliada(){
	show_exel();
	quitar_pesoDimension();
	quitar_tecnica();
}
function resumen(){
	show_exel();
	quitar_pesoDimension();
	quitar_tecnica();
}
function resumenItem(){
	quitar_descuentos();
	quitar_exel();
	quitar_pesoDimension();
	quitar_tecnica();
}
function pesomedida(){
	show_pdf();
	show_exel();
	quitar_descuentos();
	show_pesoDimension();
	quitar_tecnica();
}
function cad(){
	show_exel();
	quitar_descuentos();
	quitar_pdf();
	quitar_pesoDimension();
	quitar_tecnica();
}
function tecnico(){
	show_exel();
	show_pdf();
	show_tecnica();
	quitar_descuentos();
	quitar_pesoDimension();
}
function resumen_antigua(){
	quitar_exel();
	show_pdf();
	quitar_descuentos();
	quitar_pesoDimension();
	quitar_tecnica();
}
function ampliada_antigua(){
	quitar_exel();
	show_pdf();
	quitar_descuentos();
	quitar_pesoDimension();
	quitar_tecnica();
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
	if (document.getElementById('resumen_old').checked)
		res = res + "resumen|";
	else if (document.getElementById('ampliada_old').checked)
		res = res + "ampliada|";
	else if (document.getElementById('pesomedida').checked)
		res = res + "pesomedida|";		
	else if (document.getElementById('tecnica').checked)
		res = res + "tecnica|";
	else if (document.getElementById('resumen_item').checked)
		res = res + "resumen_item|";
	else if (document.getElementById('cad').checked)
		res = res + "cad|";
	/*else if (document.getElementById('res_int').checked)
		res = res + "res_int|";	*/
		
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
	/*if (document.getElementById('logo').checked)
		res = res + "logo|";
	else if (document.getElementById('sinlogo').checked)
		res = res + "sinlogo|";*/
	res = res + "logo|";	
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
	
	if(document.getElementById('resumen').checked){
		res = 'test_resumen|';
	}
	if(document.getElementById('ampliada').checked){
		res = 'test_ampliada|';
	}
	
	if(document.getElementById('resumen').checked && document.getElementById('item').checked 
			&& document.getElementById('MUESTRA_0').value == 'SI' && document.getElementById('ch_excel').checked){
		res = "resumen||excel|logo||item";

	}
	if(document.getElementById('resumen').checked && document.getElementById('descuento').checked 
			&& document.getElementById('MUESTRA_0').value == 'SI' && document.getElementById('ch_excel').checked){
		res = 'resumen||excel|logo||total';
		
	}
	if(document.getElementById('ampliada').checked && document.getElementById('item').checked 
			&& document.getElementById('MUESTRA_0').value == 'SI' && document.getElementById('ch_excel').checked){
		res = "ampliada||excel|logo||item";
	}
	if(document.getElementById('ampliada').checked && document.getElementById('descuento').checked 
			&& document.getElementById('MUESTRA_0').value == 'SI' && document.getElementById('ch_excel').checked){
		res = 'ampliada||excel|logo||total';
	}
	
	return res;
}

function show_tabla_lista_tecnica() {
	$("#lavel_execl").show();
	$("#lavel_pdf").show();
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
		$("#lavel_execl").hide();

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
	}else if(document.getElementById('cad').checked){
		document.getElementById('ch_pdf').checked = false;
		document.getElementById('ch_excel').checked = true;
		document.getElementById('ch_pdf').disabled = true;
		$("#lavel_pdf").hide();

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
			show_descuentos();
		}
		else{
			quitar_descuentos();
		}
	}else if(document.getElementById('ch_pdf').checked)
		quitar_descuentos();
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