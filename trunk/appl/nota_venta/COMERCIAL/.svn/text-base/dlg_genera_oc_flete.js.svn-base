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
			alert("No se puede crear la OC en estos momentos");
			respuesta = "fracaso"
		}	
		window.close();	
		returnValue = respuesta;	
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