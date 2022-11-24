function valida_correo(){

	var vl_cod_vendedor_1 = document.getElementById('COD_VENDEDOR1_H').value;
	
	//abre el dialogo.
	var ajax = nuevoAjax();
	//Comunicación con ws.
	ajax.open("GET", "COMERCIAL/ajax_valida_email_vendedor.php?cod_vendedor_1="+vl_cod_vendedor_1, false);
	ajax.send(null);
	var resp = ajax.responseText;	
	
	if(resp == 1){
		alert('No puede Enviar Mail via WebPay, ya que no tiene correo registrado');
		return false;
	}else{
		var vl_cod_nota_venta = get_value('COD_NOTA_VENTA_H_0');
		//abre el dialogo.
		var ajax = nuevoAjax();
		//Comunicación con ws.
		ajax.open("GET", "COMERCIAL/ajax_valida_caducidad_mail.php?cod_nota_venta="+vl_cod_nota_venta, false);
		ajax.send(null);
		var resp = ajax.responseText;	
		
		if(resp == 'VIGENTE'){
			var vl_confirm = confirm('El link de pago para esta Nota Venta se encuentra vigente.\n\n¿Desea realizar un nuevo envío?');
		
			if(vl_confirm == false)
				return false;
			else{
				
				var url = "COMERCIAL/dlg_valida_correo.php?cod_nota_venta="+vl_cod_nota_venta;
				$.showModalDialog({
					 url: url,
					 dialogArguments: '',
					 height: 250,
					 width: 520,
					 scrollable: false,
					 onClose: function(){ 
					 	var returnVal = this.returnValue;
					 	if (typeof returnVal == 'undefined' || returnVal == null){
							return false;
						}else {
							var input = document.createElement("input");
							input.setAttribute("type", "hidden");
							input.setAttribute("name", "b_envia_mail_pago_x");
							input.setAttribute("id", "b_envia_mail_pago_x");
							document.getElementById("input").appendChild(input);
						
							document.getElementById('wi_hidden').value = returnVal;
							document.input.submit();
					  		return true;
						}
					}
				});	
			}
			
			
		}
		else if(resp == 'NUEVO'){ 
			var url = "COMERCIAL/dlg_valida_correo.php?cod_nota_venta="+vl_cod_nota_venta;
			$.showModalDialog({
				 url: url,
				 dialogArguments: '',
				 height: 250,
				 width: 520,
				 scrollable: false,
				 onClose: function(){ 
				 	var returnVal = this.returnValue;
				 	
				 	
					if (typeof returnVal == 'undefined' || returnVal == null){
						return false;
					}else {
						var input = document.createElement("input");
						input.setAttribute("type", "hidden");
						input.setAttribute("name", "b_envia_mail_pago_x");
						input.setAttribute("id", "b_envia_mail_pago_x");
						document.getElementById("input").appendChild(input);
						document.getElementById('wi_hidden').value = returnVal;
						
						document.input.submit();
				  		return true;
					}
				}
			});			
		}
		else if(resp == 'CADUCADO'){
		
			var url = "COMERCIAL/dlg_valida_correo.php?cod_nota_venta="+vl_cod_nota_venta;
				$.showModalDialog({
					 url: url,
					 dialogArguments: '',
					 height: 250,
					 width: 520,
					 scrollable: false,
					 onClose: function(){ 
					 	var returnVal = this.returnValue;
					 	
					 	
						if (typeof returnVal == 'undefined' || returnVal == null){
							return false;
						}else {
							var input = document.createElement("input");
							input.setAttribute("type", "hidden");
							input.setAttribute("name", "b_envia_mail_pago_x");
							input.setAttribute("id", "b_envia_mail_pago_x");
							document.getElementById("input").appendChild(input);
							document.getElementById('wi_hidden').value = returnVal;
							document.input.submit();
					  		return true;
						}
					}
				});	
		}
	}
}
function consulta_stock(){
var lv_prod_proveedor = '';
var aTR = get_TR('ITEM_NOTA_VENTA');
for (var i = 0; i < aTR.length; i++){
	var cantidad = 0;
	var vl_cod_producto		= get_value('COD_PRODUCTO_' + i);
	var vl_cantidad			= get_value('CANTIDAD_' + i);
	
	//cuando los productos se encuentran en load_record los cod_producto estan desabilitados.
	if (vl_cod_producto == ''){
		vl_cod_producto = get_value('COD_PRODUCTO_H_' + i);
	}
	
	//excluye los siguientes productos.
	if ((vl_cod_producto != 'F') && (vl_cod_producto != 'E')&&(vl_cod_producto != 'I')&& (vl_cod_producto != 'VT')&& (vl_cod_producto != 'TE')){
		if (lv_prod_proveedor == '')
			lv_prod_proveedor  = lv_prod_proveedor + vl_cod_producto + '*' + vl_cantidad;
		else
			lv_prod_proveedor  = lv_prod_proveedor + '|' + vl_cod_producto + '*' + vl_cantidad;
	}
}
var ajax = nuevoAjax();
	//Comunicación con ws.
	ajax.open("GET", "COMERCIAL/ajax_proveedor_prod.php?cod_producto="+lv_prod_proveedor, false);
	ajax.send(null);
	var resp = ajax.responseText;	
	//abre el dialogo.
	var url = "COMERCIAL/dlg_consulta_stock.php?cod_producto="+resp;
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 250,
		 width: 890,
		 scrollable: false
	});	
}
function valida_btn_flete(cod_usuario){
	ve_cod_usuario = cod_usuario;
	var cant_tr = parseInt(get_TR('ITEM_NOTA_VENTA').length);
	for(i = 0;i < cant_tr ; i++){
		cod_producto = get_value('COD_PRODUCTO_'+i);
		if(cod_producto == 'F'){
			alert('Se registran ítems en esta Nota de Venta con código F. No puede crear la OC Flete mediante este botón');
			return false;
		}
	}
	
	ve_cod_nota_venta = document.getElementById('COD_NOTA_VENTA_H_0').value;
	ve_nro_cuenta_corriente = document.getElementById('NRO_CUENTA_CORRIENTE_0').innerHTML;
	var ajax = nuevoAjax();
	ajax.open("GET", "COMERCIAL/ajax_validador_flete.php?cod_nota_venta="+ve_cod_nota_venta, false);
	ajax.send(null);
	var resp = ajax.responseText;	
	var resp = (ajax.responseText).split("|");
	
	cant_item_nv =  resp[0];
	cant_item_oc =  resp[1];
	if(cant_item_nv > 0)
		alert('Se registran ítems en esta Nota de Venta con código F. No puede crear la OC Flete mediante este botón');
	if(cant_item_oc > 0)
		alert('Se registran compras asociadas con código F. No puede crear la OC Flete mediante este botón');
	if(cant_item_oc ==0 && cant_item_nv == 0){
		var url = "COMERCIAL/dlg_genera_oc_flete.php?datos="+ve_cod_nota_venta+"|"+ve_cod_usuario+"|"+ve_nro_cuenta_corriente;
		$.showModalDialog({
			 url: url,
			 dialogArguments: '',
			 height: 350,
			 width: 1010,
			 scrollable: false,
			 onClose: function(){ 
			 	var returnVal = this.returnValue;
			 	if (returnVal == "exito"){		
					document.getElementById('b_no_save').click();	
					return true;	
				}else 
				{		
					return false;
				}
			}
		});			
	}	
}
function valida_btn_embalaje(cod_usuario){
	var cant_tr = parseInt(get_TR('ITEM_NOTA_VENTA').length);
	for(i = 0;i < cant_tr ; i++){
		cod_producto = get_value('COD_PRODUCTO_'+i);
		if(cod_producto == 'E'){
			alert('Se registran ítems en esta Nota de Venta con código E. No puede crear la OC Embalaje mediante este botón');
			return false;
		}
	}
	ve_cod_nota_venta = document.getElementById('COD_NOTA_VENTA_H_0').value;
	ve_cod_usuario = cod_usuario;
	var ajax = nuevoAjax();
	ajax.open("GET", "COMERCIAL/ajax_validador_embalaje.php?cod_nota_venta="+ve_cod_nota_venta, false);
	ajax.send(null);

	var resp = (ajax.responseText).split("|");
	
	cant_item_nv =  resp[0];
	cant_item_oc =  resp[1];
	if(cant_item_nv > 0)
		alert('Se registran ítems en esta Nota de Venta con código E. No puede crear la OC Embalaje mediante este botón');
	if(cant_item_oc > 0)
		alert('Se registran compras asociadas con código E. No puede crear la OC Embalaje mediante este botón');
	if(cant_item_oc ==0 && cant_item_nv == 0){
		var url = "COMERCIAL/dlg_genera_oc_embalaje.php?datos="+ve_cod_nota_venta+"|"+ve_cod_usuario;
		$.showModalDialog({
			 url: url,
			 dialogArguments: '',
			 height: 200,
			 width: 330,
			 scrollable: false,
			 onClose: function(){ 
			 	var returnVal = this.returnValue;
			 	if (returnVal == "exito"){		
					document.getElementById('b_no_save').click();	
					return true;	
				}else 
				{		
					return false;
				}
			}
		});	
	}	
}
