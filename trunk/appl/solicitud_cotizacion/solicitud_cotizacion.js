function request_anular(ve_prompt, ve_valor){
	var url = "../../../trunk/appl/solicitud_cotizacion/request_solicitud_cotizacion.php?prompt="+URLEncode(ve_prompt)+"&valor="+URLEncode(ve_valor);
	$.showModalDialog({
		url: url,
		dialogArguments: '',
		height: 320,
		width: 480,
		scrollable: false,
		onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	if (returnVal == null){		
				return false;
			}			
			else{
                var input = document.createElement("input");
				input.setAttribute("type", "hidden");
				input.setAttribute("name", "b_save_x");
				input.setAttribute("id", "b_save_x");
				document.getElementById("output").appendChild(input);
			
				document.getElementById('wo_hidden').value = returnVal;
				document.output.submit();
				return true;
			}
		}
	});			
}