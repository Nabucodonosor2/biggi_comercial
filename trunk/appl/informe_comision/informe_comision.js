function request_crear_desde(){
	$.showModalDialog({
		url: "request_crear_desde.php",
		dialogArguments: '',
		height: 200,
		width: 380,
		scrollable: false,
		onClose: function(){ 
		 	const returnVal = this.returnValue;
		 	
		 	if (returnVal == null)
				return false;
			else{
				const input = document.createElement("input");
                input.setAttribute("type", "hidden");
                input.setAttribute("name", "b_create_x");
                input.setAttribute("id", "b_create_x");
                document.getElementById("output").appendChild(input);
                
                document.getElementById('wo_hidden').value = returnVal;
                document.output.submit();
                return true;
			}
		}
	});	
}

function check_nc(ve_control){
	const record = get_num_rec_field(ve_control.id);
	const nro_factura_fa = get_value('NRO_FACTURA_FA_'+record);

	const aTR = get_TR('ITEM_INFORME_COMISION_NC');

	for(let i = 0; i < aTR.length; i++){
		let record = get_num_rec_field(aTR[i].id);
		
		if(nro_factura_fa == get_value('NRO_FACTURA_NC_'+record)){
			if(ve_control.checked){
				document.getElementById('SELECCIONAR_NC_'+record).checked = true;
				set_value('SELECCIONAR_NC_H_'+record, 'S', 'S');
			}else{
				document.getElementById('SELECCIONAR_NC_'+record).checked = false;
				set_value('SELECCIONAR_NC_H_'+record, 'N', 'N');
			}
		}
	}
}

function calcula_totales(){
	//Tabla Factura
	const aTR = get_TR('ITEM_INFORME_COMISION_FA');

	let vl_T_NETO_FA = 0;
	let vl_T_COMISION_FA = 0;
	let count_fa = 0;
	for(let i = 0; i < aTR.length; i++){
		let record = get_num_rec_field(aTR[i].id);
		
		if(document.getElementById('SELECCIONAR_'+record).checked){
			vl_T_NETO_FA		= parseInt(vl_T_NETO_FA) + parseInt(get_value('TOTAL_NETO_'+record).replaceAll('.', ''));
			vl_T_COMISION_FA	= parseInt(vl_T_COMISION_FA) + parseInt(get_value('MONTO_COMISION_'+record).replaceAll('.', ''));
			count_fa++;
		}
	}

	set_value('T_NETO_FA_0', number_format(vl_T_NETO_FA, 0, ',', '.'), number_format(vl_T_NETO_FA, 0, ',', '.'));
	set_value('T_COMISION_FA_0', number_format(vl_T_COMISION_FA, 0, ',', '.'), number_format(vl_T_COMISION_FA, 0, ',', '.'));
	set_value('INICIO_FA', count_fa, count_fa);

	//Tabla Nota Credito
	const aTR_NC = get_TR('ITEM_INFORME_COMISION_NC');

	let vl_T_NETO_NC = 0;
	let vl_T_COMISION_NC = 0;
	let count_nc = 0;
	for(let j = 0; j < aTR_NC.length; j++){
		let record = get_num_rec_field(aTR_NC[j].id);
		
		if(document.getElementById('SELECCIONAR_NC_'+record).checked){
			vl_T_NETO_NC		= parseInt(vl_T_NETO_NC) + parseInt(get_value('TOTAL_NETO_NC_'+record).replaceAll('.', ''));
			vl_T_COMISION_NC	= parseInt(vl_T_COMISION_NC) + parseInt(get_value('MONTO_COMISION_NC_'+record).replaceAll('.', ''));
			count_nc++;
		}
	}

	set_value('T_NETO_NC_0', number_format(vl_T_NETO_NC, 0, ',', '.'), number_format(vl_T_NETO_NC, 0, ',', '.'));
	set_value('T_COMISION_NC_0', number_format(vl_T_COMISION_NC, 0, ',', '.'), number_format(vl_T_COMISION_NC, 0, ',', '.'));
	set_value('INICIO_NC', count_nc, count_nc);

	const total_comision = vl_T_COMISION_FA - vl_T_COMISION_NC;
	set_value('TOTAL_COMISION_0', number_format(total_comision, 0, ',', '.'), number_format(total_comision, 0, ',', '.'));
}

function validate(){
	const aTR = get_TR('ITEM_INFORME_COMISION_FA');
	let item_check = false;
	for(let i = 0; i < aTR.length; i++){
		let record = get_num_rec_field(aTR[i].id);
		
		if(document.getElementById('SELECCIONAR_'+record).checked){
			item_check = true;	
		}
	}

	if(!item_check){
		alert('Debe seleccionar al menos 1 item');
		return false
	}

	return true;
}