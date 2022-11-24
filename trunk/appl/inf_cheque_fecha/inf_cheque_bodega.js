function request_fecha_ingreso(ve_prompt, ve_valor){
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