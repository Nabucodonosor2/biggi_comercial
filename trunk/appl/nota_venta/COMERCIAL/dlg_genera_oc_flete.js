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

    function okMe() {
    	
		returnValue=genera_oc();
		setWindowReturnValue(returnValue);
		$dlg.dialogWindow.dialog('close');
    }
    function closeMe() {
        setWindowReturnValue(null);
        $dlg.dialogWindow.dialog('close');
    }
function genera_oc(){
	if(validar()== false)
		return false;
	else{	
		ve_cod_nota_venta = document.getElementById('COD_NOTA_VENTA_0').value;
		ve_cod_usuario = document.getElementById('COD_USUARIO_0').value;
		ve_monto_neto = document.getElementById('MONTO_NETO_0').value;
		ve_provedor = document.getElementById('PROVEDOR_H_0').value;
		ve_cuenta_corriente = document.getElementById('NRO_CUENTA_CORRIENTE_0').value;
		var ajax = nuevoAjax();
		ajax.open("GET", "ajax_genera_oc_flete.php?datos="+ve_cod_nota_venta+"|"+ve_cod_usuario+"|"+ve_monto_neto+"|"+ve_provedor+"|"+ve_cuenta_corriente, false);
		ajax.send(null);
		var resp = ajax.responseText;	
		if(resp != "fracaso"){
			alert("Se ha creado la OC N\u00B0 "+resp);
			respuesta = "exito"
			}
		else{
			alert("No se puede crear la OC en estos momentos. Favor notificar a Dpto. Informática");
			respuesta = "fracaso"
		}	
		returnValue = respuesta;
		return returnValue;
	}
}
function provedor(){

	provedor = document.getElementById('PROVEDOR_H_0');
	var cant_tr = 7;
	for(i = 0;i < cant_tr ; i++){
		if(document.getElementById('PROVEDOR_'+i).checked){
			provedor.value = document.getElementById('COD_EMPRESA_'+i).innerHTML;
		}
	}
}
function validar(){
	provedor = document.getElementById('PROVEDOR_H_0').value;
	monto_neto = document.getElementById('MONTO_NETO_0').value;
	if(provedor != ' ' && monto_neto != '')
		return true;
	else if (provedor == ' ' && monto_neto == ''){
		alert('Seleccione un proveedor y ingrese un monto neto');
		return false;
	}	
	else if (provedor == ' '){
		alert('Seleccione un proveedor');
		return false;			
	}
	else if (monto_neto == ''){
		alert('Ingrese un monto neto');
		document.getElementById('MONTO_NETO_0').focus();
		return false;			
	}
}