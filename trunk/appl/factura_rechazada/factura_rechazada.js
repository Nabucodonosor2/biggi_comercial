function dlg_print_dte_wo(ve_control, ve_cod_factura){
	var vl_rec = get_num_rec_field(ve_control.id);
	if(ve_cod_factura > 37204){
		var url = "../factura/dlg_cedible.php";
		
		$.showModalDialog({
			 url: url,
			 dialogArguments: '',
			 height: 240,
			 width: 360,
			 scrollable: false,
			 onClose: function(){ 
			 	var returnVal = this.returnValue;
			 	if (returnVal == null){		
					return false;
				}			
				else{
					var input = document.createElement("input");
					input.setAttribute("type", "hidden");
					input.setAttribute("name", "b_printDTE_"+vl_rec+"_x");
					input.setAttribute("id", "b_printDTE_"+vl_rec+"_x");
					document.getElementById("output").appendChild(input);
								
					document.getElementById('wo_hidden').value = ve_cod_factura;
                    document.getElementById('wo_hidden2').value = returnVal;
					document.output.submit();
					return true;
				}
			}
		});
	}else{
		var input = document.createElement("input");
		input.setAttribute("type", "hidden");
		input.setAttribute("name", "b_printDTE_"+vl_rec+"_x");
		input.setAttribute("id", "b_printDTE_"+vl_rec+"_x");
		document.getElementById("output").appendChild(input);
		
		document.output.submit();
		return true;
	}
}