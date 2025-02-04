function request_fecha_ingreso(ve_prompt, ve_valor){
	const aTR = get_TR('INFORME_CHEQUE_FECHA');
	for (let i = 0; i < aTR.length; i++){
		let vl_rec = get_num_rec_field(aTR[i].id);

		let vl_nom_tipo_doc = get_value('NOM_TIPO_DOC_H_'+vl_rec);
		if(vl_nom_tipo_doc == '')
			continue;
		
		let vl_check = document.getElementById('SELECCION_'+vl_rec).checked;
		
		if(vl_nom_tipo_doc == 'EFECTIVO' && vl_check == true){
			alert('La utilidad "Cambiar Fecha Deposito" es solo para los documentos tipo "Cheque", se detectaron documentos del tipo "Efectivo" en la selección actual.');
			return false;
		}	
	}

	var url = "request_fecha_ingreso.php?prompt="+URLEncode(ve_prompt)+"&valor="+URLEncode(ve_valor);
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 280,
		 width: 320,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	if (returnVal == null)		
				return false;		
			else 
			{
				var input = document.createElement("input");
				input.setAttribute("type", "hidden");
				input.setAttribute("name", "b_change_date_deposit_x");
				input.setAttribute("id", "b_change_date_deposit_x");
				document.getElementById("output").appendChild(input);
			
				document.getElementById('wo_hidden').value = returnVal;
				document.output.submit();
			   	return true;	
			}
		}
	});			
}

function request_fecha_efectivo(ve_prompt, ve_valor){
	const aTR = get_TR('INFORME_CHEQUE_FECHA');
	for (let i = 0; i < aTR.length; i++){
		let vl_rec = get_num_rec_field(aTR[i].id);

		let vl_nom_tipo_doc = get_value('NOM_TIPO_DOC_H_'+vl_rec);
		if(vl_nom_tipo_doc == '')
			continue;
		
		let vl_check = document.getElementById('SELECCION_'+vl_rec).checked;
		
		if(vl_nom_tipo_doc == 'CHEQUE' && vl_check == true){
			alert('La utilidad "Depositar" es solo para los documentos tipo "Efectivo", se detectaron documentos del tipo "Cheque" en la selección actual.');
			return false;
		}	
	}

	var url = "request_fecha_efectivo.php?prompt="+URLEncode(ve_prompt)+"&valor="+URLEncode(ve_valor);
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 280,
		 width: 320,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	if (returnVal == null)		
				return false;		
			else 
			{
				var input = document.createElement("input");
				input.setAttribute("type", "hidden");
				input.setAttribute("name", "b_change_date_efectivo_x");
				input.setAttribute("id", "b_change_date_efectivo_x");
				document.getElementById("output").appendChild(input);
			
				document.getElementById('wo_hidden').value = returnVal;
				document.output.submit();
			   	return true;	
			}
		}
	});
}