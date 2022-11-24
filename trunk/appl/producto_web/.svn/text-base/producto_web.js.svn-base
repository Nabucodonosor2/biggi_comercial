function validate() {
	
	var es_oferta 	= document.getElementById('ES_OFERTA_0').checked;
	var precio_oferta 	= document.getElementById('PRECIO_OFERTA_0').value;
	if(es_oferta == true){
		if(precio_oferta == ''){
			alert('Debe ingresar el Precio Oferta');
			return false;
		}
		if(precio_oferta < 10000){
			alert('El precio de la oferta debe ser mayor a $10.000');
			return false;
		}
		return true;
	}



}