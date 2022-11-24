function ajuste_valores_foto(){
	if(document.getElementById('USA_FOTO_ANTIGUA_0').checked){
		document.getElementById('COORDENADA_X_0').value = 65;
		document.getElementById('COORDENADA_Y_0').value = 25;
	}else{
		document.getElementById('COORDENADA_X_0').value = 0;
		document.getElementById('COORDENADA_Y_0').value = 0;
	}
}

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

function home_oferta(){
	if(document.getElementById('ES_OFERTA_0').checked)
		document.getElementById('DISPLAY_HOME_0').style.display = '';
	else{
		document.getElementById('DISPLAY_HOME_0').style.display = 'none';
		document.getElementById('PUBLICAR_EN_HOME_0').checked = false;
	}
}

function check_salto_linea(ve_control){
	var rec_it_selected = get_num_rec_field(ve_control.id);
	var aTR = get_TR('ATRIBUTO_PRODUCTO');
	
	for(i=0 ; i < aTR.length ; i++){
		var vl_record = get_num_rec_field(aTR[i].id);
	
		if(i != rec_it_selected)
			document.getElementById('SALTO_LINEA_'+vl_record).checked = false;
	}
}

function add_line_ad(ve_label_record, ve_nom_tabla){
	var vl_len = get_TR(ve_label_record).length;
	
	if(vl_len >= 3){
		if(ve_label_record == 'ATRIBUTO_DESTACADO')
			alert('Sólo puede ingresar tres atributos destacados.');
		else
			alert('Sólo puede ingresar tres Especificaciones para este producto.');
	}else
		add_line(ve_label_record, ve_nom_tabla);
}