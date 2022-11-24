function add_line_item(ve_tabla_id, ve_nom_tabla) {
	var vl_row = add_line(ve_tabla_id, ve_nom_tabla);
	return vl_row
}


function valida_cantidad_max(ve_campo){
	var record = get_num_rec_field(ve_campo.id);
	var cantidad = ve_campo.value;
	var cantidad_max = document.getElementById('CANTIDAD_MAX_'+record).value;
	
	if (parseFloat(cantidad) > parseFloat(cantidad_max)){
		alert('La cantidad máxima de entrada es: '+cantidad_max);
		document.getElementById('CANTIDAD_'+record).value = cantidad_max;
	}
}
