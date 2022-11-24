function change_precio(boton, ve_tabla) {
	var record = get_num_rec_field(boton.id);
	var cod_item = document.getElementById('COD_'+ve_tabla+'_'+record).value;
	
	var cod_prod = document.getElementById('COD_PRODUCTO_'+record);
	if (! cod_prod)
		cod_prod = document.getElementById('COD_PRODUCTO_H_'+record);		
	var cod_producto = cod_prod.value;
	
	var precio = document.getElementById('PRECIO_H_'+record).value;
	if (cod_producto=='') {
		alert('Debe ingresar el producto antes de modificar el precio.');
		return false;
	}
	else if (cod_producto=='TE') {
		return ingreso_TE(document.getElementById('COD_PRODUCTO_'+record));
	}
	var args = "location:no;dialogLeft:100px;dialogTop:300px;dialogWidth:840px;dialogHeight:400px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("../common_appl/change_precio.php?tabla="+ve_tabla+"&cod_item="+cod_item+"&cod_producto="+cod_producto+"&precio="+precio, "_blank", args);
 	if (returnVal == null)
 		return false;
	else {
		var precio = document.getElementById('PRECIO_' + record);
		var precio_h = document.getElementById('PRECIO_H_' + record);
		var motivo = document.getElementById('MOTIVO_' + record);
		var res = returnVal.split('|');
		precio.innerHTML = number_format(res[0], 0, ',', '.'); 
		precio_h.value = res[0];
		motivo.value = res[1];
		computed(record, 'TOTAL');		
   		return true;
	}
}