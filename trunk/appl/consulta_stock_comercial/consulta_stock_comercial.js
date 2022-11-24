//Implemente help_producto
function change_stock_producto(ve_valor, ve_campo) {
	var record= get_num_rec_field(ve_valor.id);
	help_producto(ve_valor, record);
		//llamo a la nueva funcion y le paso el "record" que toma el id del registro solicitado.	
}
//funcion implementada para la consulta de stock producto a producto
function consulta_stock(ve_cod_producto, record){

	var aTR = get_TR('STOCK_PRODUCTO');
	for (var i = 0; i < aTR.length; i++){
		var vl_record = get_num_rec_field(aTR[i].id);
		//var vl_cod_producto		= get_value('COD_PRODUCTO_' + vl_record);
		//excluye los siguientes productos.
		if ((ve_cod_producto != 'F') && (ve_cod_producto != 'E')&&(ve_cod_producto != 'I')&& (ve_cod_producto != 'VT')&& (ve_cod_producto != 'TE')){
			
			var lv_prod_proveedor = ve_cod_producto;
			
		}else{
			alert ('El producto ingresado no cuenta con un stock valido.') 
			document.getElementById('COD_PRODUCTO_' + record).values = '';
			document.getElementById('NOM_PRODUCTO_' + record).values = '';
			document.getElementById('COD_PRODUCTO_' + record).focus(); 
			return false;
		}
	}
	var ajax = nuevoAjax();
	//Comunicación con ws.
	ajax.open("GET", "ajax_consulta_stock_comercial.php?cod_producto="+lv_prod_proveedor, false);
	ajax.send(null);
	var resp = ajax.responseText.split('|');
	
		if(resp[0] == 'TODOINOX'){
			document.getElementById('STOCK_TODOINOX_'+record).innerHTML		= resp[1];
			document.getElementById('STOCK_BODEGA_'+record).innerHTML		= '-';
		}else if (resp[0] == 'BODEGA'){
			document.getElementById('STOCK_TODOINOX_'+record).innerHTML		= '-';
			document.getElementById('STOCK_BODEGA_'+record).innerHTML		= resp[1];
		}else{
			document.getElementById('STOCK_TODOINOX_'+record).innerHTML		= '-';
			document.getElementById('STOCK_BODEGA_'+record).innerHTML		= '-';
		}
}

function select_1_producto(valores, record) {
	consulta_stock(valores[1], record);
	
	set_values_producto(valores, record);
	
}