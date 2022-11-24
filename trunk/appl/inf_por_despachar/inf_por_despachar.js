function dlg_print() {
var vl_nom_usuario = document.getElementById('NOM_USUARIO_H').value;
var	vl_len_usuario = vl_nom_usuario.length;
	vl_nom_usuario = vl_nom_usuario.substr(4, vl_len_usuario);

document.getElementById('wo_hidden').value = vl_nom_usuario;

	/*var args = "location:no;dialogLeft:400px;dialogTop:300px;dialogWidth:400px;dialogHeight:150px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("dlg_print_por_despachar.php", "_blank", args);*/
	/*$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 580,
		 width: 1038,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	if (returnVal == null)
		 		return false;
			else {
				var input = document.createElement('input');
				input.setAttribute('type', 'hidden');
				input.setAttribute('name', 'b_print_x');
				input.setAttribute('id', 'b_print_x');
				document.getElementById('output').appendChild(input);
				
				document.getElementById('wo_hidden').value = returnVal;
				document.output.submit();
		   		return true;
			}
		}
	});*/
}