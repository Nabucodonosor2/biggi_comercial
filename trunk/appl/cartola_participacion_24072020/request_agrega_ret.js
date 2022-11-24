function get_returnVal(){

	var monto= Number(document.getElementById('MONTO_0').value);		
	var glosa = document.getElementById('GLOSA_0').value;
	
	var dato= monto+'|'+glosa;
	return dato;		
}

// DIALOG Required Code
var prntWindow = getParentWindowWithDialog(); //$(top)[0];

var $dlg = prntWindow && prntWindow.$dialog;

//DIALOG Required Code
var prntWindow = getParentWindowWithDialog(); //$(top)[0];

var $dlg = prntWindow && prntWindow.$dialog;

function getParentWindowWithDialog(){
	var p = window.parent;
	var previousParent = p;
	while (p != null) {
		if ($(p.document).find('#iframeDialog').length) return p;

		p = p.parent;

		if (previousParent == p) return null;

		// save previous parent

		previousParent = p;
	}
	return null;
}

function setWindowReturnValue(value) {
	if ($dlg) $dlg.returnValue = value;
	window.returnValue = value; // in case popup is called using showModalDialog

}

function getWindowReturnValue() {
	// in case popup is called using showModalDialog

	if (!$dlg && window.returnValue != null)
		return window.returnValue;

	return $dlg && $dlg.returnValue;
}

if ($dlg) window.dialogArguments = $dlg.dialogArguments;
if ($dlg) window.close = function() { if ($dlg) $dlg.dialogWindow.dialog('close'); };
// END of dialog Required Code

   function okMe() {
   	var vl_value = get_returnVal();
   	if(!vl_value) 
		return false; 
	else{
		returnValue=vl_value;
		setWindowReturnValue(returnValue);
	}	
	$dlg.dialogWindow.dialog('close');
   }
   function closeMe() {
       setWindowReturnValue(null);
       $dlg.dialogWindow.dialog('close');
   }     
       
       
       
       