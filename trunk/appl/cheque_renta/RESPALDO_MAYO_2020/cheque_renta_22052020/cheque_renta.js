function tipoDoc(){
	doc = $('input[name="cheque"]:checked').val();//document.getElementById();
	
	switch (doc) {
	case 'talonario':
		$('#tablaCheque').hide();
		$('#tablaTalonario').show();
		break;
	case 'santander':
		$('#tablaCheque').hide();
		$('#tablaSantander').show();
		break;
	case 'arriendo':
		alert('santander');
		break;
	}
}
