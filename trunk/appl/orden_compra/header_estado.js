function dlg_estado(ve_nom_header, ve_valor_filtro, ve_campo) {
	var id_campo = ve_campo.id;
 	var url = "dlg_estado.php?nom_header="+URLEncode(ve_nom_header)+"&valor_filtro="+URLEncode(ve_valor_filtro);
		$.showModalDialog({
			 url: url,
			 dialogArguments: '',
			 height: 320,
			 width: 650,
			 scrollable: false,
			 onClose: function(){ 
			 	var returnVal = this.returnValue;
			 	if (navigator.appName=='Microsoft Internet Explorer'){
			 		if (returnVal == null)
						document.getElementById('wo_header').value = '__BORRAR_FILTRO__';
					else
						document.getElementById('wo_header').value = returnVal;
					
				   	var input = document.createElement("input");
					input.setAttribute("type", "hidden");
					input.setAttribute("name", id_campo+'_X');
					input.setAttribute("id", id_campo+'_X');
					document.getElementById("output").appendChild(input);
					
					document.getElementById('wo_hidden').value = returnVal;
					document.forms["output"].submit();
			   		return true;
			 	}else{
			 		if (returnVal == null){		
						document.getElementById('wo_hidden').value = '__BORRAR_FILTRO__';
					}			
					else {
						document.getElementById('wo_hidden').value = returnVal;
					}
						var input = document.createElement("input");
						input.setAttribute("type", "hidden");
						input.setAttribute("name", id_campo+'_X');
						input.setAttribute("id", id_campo+'_X');
						document.getElementById("output").appendChild(input);
						
						document.getElementById('wo_hidden').value = returnVal;
						document.output.submit();
				   		return true;
			 	}
				 	
			}
		});		
}
