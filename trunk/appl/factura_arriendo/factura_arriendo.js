function dlg_print_anexo() {
var url = "dlg_print_anexo.php";
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 180,
		 width: 470,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	if (returnVal == null){		
				return false;
			}			
			else {
				document.getElementById('wi_hidden').value = returnVal;
				var input = document.createElement('input');
				input.setAttribute('type', 'hidden');
				input.setAttribute('name', 'b_print_dte_x');
				input.setAttribute('id', 'b_print_dte_x');
				document.getElementById('input').appendChild(input);
				document.input.submit();
				
			}
		}
	});		
}