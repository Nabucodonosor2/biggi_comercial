function asignacion_monto(ve_seleccion) {
	var vl_campo_id = ve_seleccion.id;
	var vl_record = get_num_rec_field(vl_campo_id);
	
	if(ve_seleccion.checked == true)
		document.getElementById('MONTO_ASIGNADO_' + vl_record).readOnly = false;
	else
		document.getElementById('MONTO_ASIGNADO_' + vl_record).readOnly = true;	
	
	var aTR = get_TR('PAGO_FAPROV_FAPROV');
	var suma = 0;
	
	for (i=0; i<aTR.length; i++){
		var rec_tr =get_num_rec_field(aTR[i].id);
		if (document.getElementById('SELECCION_' + rec_tr).checked == true){
			if(document.getElementById('MONTO_ASIGNADO_' + rec_tr).value == 0){
				suma = suma + parseFloat(document.getElementById('SALDO_SIN_PAGO_FAPROV_H_' + rec_tr).value);
				set_monto_asignado(rec_tr, parseFloat(document.getElementById('SALDO_SIN_PAGO_FAPROV_H_' + rec_tr).value));
			}	
		}
		else
			set_monto_asignado(rec_tr, 0);//si la seleccion es false setea el valor en cero
	}//fin for				
	computed(get_num_rec_field(ve_seleccion.id), 'MONTO_ASIGNADO_C');
}