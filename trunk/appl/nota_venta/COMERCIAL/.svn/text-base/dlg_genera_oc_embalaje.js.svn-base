function genera_oc(){
	ve_cod_nota_venta = document.getElementById('COD_NOTA_VENTA_0').value;
	ve_cod_usuario = document.getElementById('COD_USUARIO_0').value;
	ve_monto_neto = document.getElementById('MONTO_NETO_0').value;
	var ajax = nuevoAjax();
	ajax.open("GET", "ajax_genera_oc_embalaje.php?datos="+ve_cod_nota_venta+"|"+ve_cod_usuario+"|"+ve_monto_neto, false);
	ajax.send(null);
	var resp = ajax.responseText;	
	if(resp != "fracaso"){
		alert("Se ha creado la OC N\u00B0 "+resp+" por concepto de embalaje");
		respuesta = "exito"
		}
	else{
		alert("No se puede crear la OC en estos momentos");
		respuesta = "fracaso"
	}	
	window.close();	
	returnValue = respuesta;	
}