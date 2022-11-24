function request_fecha_ingreso(ve_prompt, ve_valor){
	var args = "location:no;dialogLeft:100px;dialogTop:200px;dialogWidth:300px;dialogHeight:250px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("request_fecha_ingreso.php?prompt="+URLEncode(ve_prompt)+"&valor="+URLEncode(ve_valor), "_blank", args);
 	if (returnVal == null)		
		return false;

	document.getElementById('wo_hidden').value = returnVal;
	document.output.submit();
	return true;
}