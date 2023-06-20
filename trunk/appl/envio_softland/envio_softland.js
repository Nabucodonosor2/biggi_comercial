function validate() {
	const K_ENVIO_CONFIRMADA = 2;
	const K_TIPO_COMPRA = 2;
	
	const tipoEnvio = get_value('COD_TIPO_ENVIO_0');
	const aTR = get_TR('ENVIO_TRANSBANK');

	if(tipoEnvio == '5'){ //si es del tipo transbank
		const nroComprobante	= get_value('NRO_COMPROBANTE_0');
		const totalBanco		= get_value('TOTAL_BANCO_0');
		const totalComision 	= get_value('COMISION_TBK_0');
		const fechaAbono		= get_value('FECHA_ABONO_0');

		if(nroComprobante == ''){
			alert('Debe llenar el campo "N° Comprobante"');
			document.getElementById('NRO_COMPROBANTE_0').focus();
			return false;
		}
		if(totalBanco == ''){
			alert('Debe llenar el campo "Total Banco"');
			document.getElementById('TOTAL_BANCO_0').focus();
			return false;
		}
		if(totalComision == ''){
			alert('Debe llenar el campo "Total Comisión"');
			document.getElementById('COMISION_TBK_0').focus();
			return false;
		}
		if(fechaAbono == ''){
			alert('Debe llenar el campo "Fecha Abono"');
			document.getElementById('FECHA_ABONO_0').focus();
			return false;
		}

		let count = 0;

		for (j=0; j < aTR.length; j++){
			let vl_record = get_num_rec_field(aTR[j].id);
			
			let nroAutorizacion	= get_value('NRO_AUTORIZA_TB_'+vl_record);
			let montoAbono		= get_value('MONTO_ABONO_'+vl_record);
			let cuota			= get_value('CUOTAS_N_'+vl_record);

			if(nroAutorizacion == '' && montoAbono == '' && cuota == ''){
				count++;
			}else{
				if(nroAutorizacion == ''){
					alert('Debe llenar el campo "Nro. Autorización"');
					document.getElementById('NRO_AUTORIZA_TB_'+vl_record).focus();
					return false;
				}
				if(montoAbono == ''){
					alert('Debe llenar el campo "Monto Abono"');
					document.getElementById('MONTO_ABONO_'+vl_record).focus();
					return false;
				}
				if(cuota == ''){
					alert('Debe llenar el campo "Cuota n/n"');
					document.getElementById('CUOTAS_N_'+vl_record).focus();
					return false;
				}
			}
		}

		if(aTR.length == count){
			alert('Debe ingresar al menos 1 item.');
			return false;
		}
	}

	var vl_cod_estado_envio = document.getElementById('COD_ESTADO_ENVIO_0');
	if(vl_cod_estado_envio){	// esta ingresable
		if (vl_cod_estado_envio.value==K_ENVIO_CONFIRMADA){	// confirmada
			// valida que este ingresado el nro del comprobante
			var vl_nro_comprobante = document.getElementById('NRO_COMPROBANTE_0');
			if (vl_nro_comprobante.value=='' || vl_nro_comprobante.value==0) {
				alert("Para confirmar bebe ingresar el número del comprobante");
				vl_nro_comprobante.focus();
				return false;
			}

			// si es envio de compras debe ingresar el correlativo interno
			var vl_cod_tipo_envio = document.getElementById('COD_TIPO_ENVIO_0').value;
			if (vl_cod_tipo_envio==K_TIPO_COMPRA) {
				var vl_nro_correlativo_interno = document.getElementById('NRO_CORRELATIVO_INTERNO_0');
				if (vl_nro_correlativo_interno.value=='' || vl_nro_correlativo_interno.value==0) {
					alert("Para confirmar bebe ingresar el número de Correlativo interno");
					vl_nro_correlativo_interno.focus();
					return false;					
				}
			}
		}
	}
 
	var vl_total_banco = document.getElementById('TOTAL_BANCO_0').value;
	var vl_comision_tbk = document.getElementById('COMISION_TBK_0').value;
	var vl_monto_abono =0;
	var total_abono = 0;
	var total_banco_com = 0;
 
	vl_total_banco = parseInt(to_num(vl_total_banco));
	vl_comision_tbk = parseInt(to_num(vl_comision_tbk));
	total_abono = parseInt(to_num(total_abono));
	total_banco_com = parseInt(to_num(total_banco_com));

	for (i=0; i < aTR.length; i++){
		vl_record = get_num_rec_field(aTR[i].id);
	
		vl_monto_abono = document.getElementById('MONTO_ABONO_' + vl_record).value;
		total_abono = parseInt(to_num(total_abono)) + parseInt(to_num(vl_monto_abono));
	}
 
  	total_banco_com = parseInt(to_num(total_abono)) - vl_comision_tbk
 
	if(parseInt(to_num(total_banco_com))!=parseInt(to_num(vl_total_banco))){
		alert('El monto total banco: '+ vl_total_banco+' es distinto al monto de los Item menos la comisión Tbk: ' + total_banco_com);
		return false;
	}
	
	return true;
}

function selecciona_documento(ve_seleccion) {
	var vl_record = get_num_rec_field(ve_seleccion.id);
	var vl_tipo;
	if (ve_seleccion.id.substr(0,3)=='FA_')
		vl_tipo = 'FA';
	else if (ve_seleccion.id.substr(0,3)=='FC_')
		vl_tipo = 'FC';
	else if (ve_seleccion.id.substr(0,4)=='NCC_')
		vl_tipo = 'NCC';
	
	// obtiene los datos de la linea clickeada
	var vl_neto = document.getElementById(vl_tipo + '_TOTAL_NETO_' + vl_record).innerHTML;
	var vl_iva = document.getElementById(vl_tipo + '_MONTO_IVA_' + vl_record).innerHTML;
	var vl_total = document.getElementById(vl_tipo + '_TOTAL_CON_IVA_' + vl_record).innerHTML;
	// borra los puntos en los miles
	vl_neto = parseInt(to_num(vl_neto));
	vl_iva = parseInt(to_num(vl_iva));
	vl_total = parseInt(to_num(vl_total));
	
	// Para las compras, FC -> FA; NCC -> NC
	if (vl_tipo == 'FC')
		vl_tipo = 'FA';
	else if (vl_tipo == 'NCC')
		vl_tipo = 'NC';
	
	// obtiene los totales del resumen
	var vl_tot_cant = document.getElementById('RE_CANT_' + vl_tipo + '_0').innerHTML;
	var vl_tot_neto = document.getElementById('RE_TOTAL_NETO_' + vl_tipo + '_0').innerHTML;
	var vl_tot_iva = document.getElementById('RE_MONTO_IVA_' + vl_tipo + '_0').innerHTML;
	var vl_tot_total = document.getElementById('RE_TOTAL_' + vl_tipo + '_0').innerHTML;
	// borra los puntos en los miles
	vl_tot_cant = parseInt(to_num(vl_tot_cant));
	vl_tot_neto = parseInt(to_num(vl_tot_neto));
	vl_tot_iva = parseInt(to_num(vl_tot_iva));
	vl_tot_total = parseInt(to_num(vl_tot_total));
	
	// calcula los nuevos totales
	if (ve_seleccion.checked) {
		vl_tot_cant = vl_tot_cant + 1;
		vl_tot_neto = vl_tot_neto + vl_neto;
		vl_tot_iva = vl_tot_iva + vl_iva;
		vl_tot_total = vl_tot_total + vl_total;
	}
	else {
		vl_tot_cant = vl_tot_cant - 1;
		vl_tot_neto = vl_tot_neto - vl_neto;
		vl_tot_iva = vl_tot_iva - vl_iva;
		vl_tot_total = vl_tot_total - vl_total;
	}
	
	//actualiza los totales
	document.getElementById('RE_CANT_' + vl_tipo + '_0').innerHTML = number_format(vl_tot_cant, 0, ',', '.');
	document.getElementById('RE_TOTAL_NETO_' + vl_tipo + '_0').innerHTML = number_format(vl_tot_neto, 0, ',', '.');
	document.getElementById('RE_MONTO_IVA_' + vl_tipo + '_0').innerHTML = number_format(vl_tot_iva, 0, ',', '.');
	document.getElementById('RE_TOTAL_' + vl_tipo + '_0').innerHTML = number_format(vl_tot_total, 0, ',', '.');
	
	// verifica si existen FA o NC de diferentes meses
	var vl_dif_meses = false;
	var vl_mes = 0;
	var aTR = get_TR('ENVIO_FACTURA');
	if (aTR.length > 0) {
		for (var i=0; i < aTR.length; i++)	{
			vl_record = get_num_rec_field(aTR[i].id);
			if (document.getElementById('FA_SELECCION_' + vl_record).checked) {
				var vl_fecha = document.getElementById('FA_FECHA_FACTURA_' + vl_record).innerHTML;
				var aFecha = vl_fecha.split('/');
				if (vl_mes==0)
					vl_mes = aFecha[1];
				else {
					if (vl_mes != aFecha[1]) {
						vl_dif_meses = true;
						break;
					}
				}
			}
		}
	}
	var aTR = get_TR('ENVIO_NOTA_CREDITO');
	if (vl_dif_meses==false && aTR.length > 0) {
		for (i=0; i < aTR.length; i++)	{
			vl_record = get_num_rec_field(aTR[i].id);
			if (document.getElementById('NC_SELECCION_' + vl_record).checked) {
				var vl_fecha = document.getElementById('NC_FECHA_NOTA_CREDITO_' + vl_record).innerHTML;
				var aFecha = vl_fecha.split('/');
				if (vl_mes==0)
					vl_mes = aFecha[1];
				else {
					if (vl_mes != aFecha[1]) {
						vl_dif_meses = true;
						break;
					}
				}
			}
		}
	}
	if (vl_dif_meses)
		document.getElementById('RE_DIF_MESES_0').innerHTML = 'Existen Facturas o Nota de Crédito de diferentes meses';
	else
		document.getElementById('RE_DIF_MESES_0').innerHTML = '';
}
function change_nro_comprobante(ve_nro_comprobante) {
	//Ajax para saber si el nro ya esta siendo usado
	var ajax = nuevoAjax();
	ajax.open("GET", "ajax_nro_comprobante_usado.php?nro_comprobante="+ve_nro_comprobante.value, false);
	ajax.send(null);
	var resp = URLDecode(ajax.responseText);
	if (resp != 0) {
		alert('El nro de comprobante ' + ve_nro_comprobante.value + ' ya esta siendo usado en el Envío nro: ' + resp);
		ve_nro_comprobante.value = '';
		ve_nro_comprobante.focus();
	}
}
function dlg_print() {
	
	var vl_cod_tipo_envio = document.getElementById('COD_TIPO_ENVIO_0').value;
	var url = "dlg_print_envio_softland.php?cod_tipo_envio="+vl_cod_tipo_envio;
				$.showModalDialog({
					 url: url,
					 dialogArguments: '',
					 height: 235,
					 width: 470,
					 scrollable: false,
					 onClose: function(){ 
					 	var returnVal = this.returnValue;
					 	if (returnVal == null){		
							return false;
						}			
						else {
							var input = document.createElement("input");
							input.setAttribute("type", "hidden");
							input.setAttribute("name", "b_print_x");
							input.setAttribute("id", "b_print_x");
							document.getElementById("input").appendChild(input);
							document.getElementById('wi_hidden').value = returnVal;
							document.input.submit();
							document.getElementById("input").removeChild(input);
							
							
							// Asigna correlativo compra
							var vl_correlativo = document.getElementById('NRO_CORRELATIVO_H_0').value;
							var aTR = get_TR('ENVIO_FAPROV');
							for (var i=0; i < aTR.length; i++)	{
								var vl_record = get_num_rec_field(aTR[i].id);
								if (document.getElementById('FC_SELECCION_' + vl_record).checked) {
									document.getElementById('FC_CORRELATIVO_' + vl_record).innerHTML = vl_correlativo;
									vl_correlativo++; 
								}
							}
							var aTR = get_TR('ENVIO_NCPROV');
							for (var i=0; i < aTR.length; i++)	{
								var vl_record = get_num_rec_field(aTR[i].id);
								if (document.getElementById('NCC_SELECCION_' + vl_record).checked) {
									document.getElementById('NCC_CORRELATIVO_' + vl_record).innerHTML = vl_correlativo;
									vl_correlativo++; 
								}
							}
							
					   		return true;
						}
					}
				});		
}
function marcar_todo(ve_tabla_id, ve_prefijo) {
	var aTR = get_TR(ve_tabla_id);
	for (var i=0; i < aTR.length; i++)	{
		vl_record = get_num_rec_field(aTR[i].id);
		document.getElementById(ve_prefijo + '_' + vl_record).checked = true;
	}
}
function desmarcar_todo(ve_tabla_id, ve_prefijo) {
	var aTR = get_TR(ve_tabla_id);
	for (var i=0; i < aTR.length; i++)	{
		vl_record = get_num_rec_field(aTR[i].id);
		document.getElementById(ve_prefijo + '_' + vl_record).checked = false;
	}
}
function dejar_seleccion(ve_tabla_id, ve_prefijo) {
	var aTR = get_TR(ve_tabla_id);
	for (var i=0; i < aTR.length; i++)	{
		vl_record = get_num_rec_field(aTR[i].id);
		if (document.getElementById(ve_prefijo + '_' + vl_record).checked == false)
			del_line(aTR[i].id, 'envio_softland');
	}
}
function request_cuenta(ve_tabla_id, ve_prefijo) {
	
	var url = "request_cuenta.php";
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 480,
		 width: 650,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	if (returnVal == null){		
				return false;	
			}			
			else {
				var aLista = returnVal.split('|'); 
				var vl_cod_cuenta_compra = aLista[0];
				var vl_nom_cuenta_compra = aLista[1];
				var aTR = get_TR(ve_tabla_id);
				for (var i=0; i < aTR.length; i++)	{
					vl_record = get_num_rec_field(aTR[i].id);
					if (document.getElementById(ve_prefijo + '_SELECCION_CUENTA_' + vl_record).checked == true) {
						document.getElementById(ve_prefijo + '_COD_CUENTA_COMPRA_' + vl_record).value = vl_cod_cuenta_compra;
						document.getElementById(ve_prefijo + '_NOM_CUENTA_COMPRA_' + vl_record).innerHTML = vl_nom_cuenta_compra;
						document.getElementById(ve_prefijo + '_SELECCION_CUENTA_' + vl_record).checked = false;
					}
				}
			   	return true;	
			}
		}
	});		
}
function selecciona_faprov(ve_seleccion) {
	var vl_record = get_num_rec_field(ve_seleccion.id);
	
	// obtiene los datos de la linea clickeada
	var vl_monto = document.getElementById(vl_tipo + 'EG_MONTO_DOCUMENTO_' + vl_record).innerHTML;
	// borra los puntos en los miles
	vl_monto = parseInt(to_num(vl_monto));
	
	// obtiene los totales del resumen
	var vl_tot_cant = document.getElementById('RE_CANT_EG_0').innerHTML;
	var vl_tot_monto = document.getElementById('RE_MONTO_EG_0').innerHTML;
	// borra los puntos en los miles
	vl_tot_cant = parseInt(to_num(vl_tot_cant));
	vl_tot_monto = parseInt(to_num(vl_tot_monto));
	
	// calcula los nuevos totales
	if (ve_seleccion.checked) {
		vl_tot_cant = vl_tot_cant + 1;
		vl_tot_monto = vl_tot_monto + vl_monto;
	}
	else {
		vl_tot_cant = vl_tot_cant - 1;
		vl_tot_monto = vl_tot_monto - vl_monto;
	}
	
	//actualiza los totales
	document.getElementById('RE_CANT_EG_0').innerHTML = number_format(vl_tot_cant, 0, ',', '.');
	document.getElementById('RE_MONTO_EG_0').innerHTML = number_format(vl_tot_monto, 0, ',', '.');
	
	/*
	// verifica si existen FA o NC de diferentes meses
	var vl_dif_meses = false;
	var vl_mes = 0;
	var aTR = get_TR('ENVIO_FACTURA');
	if (aTR.length > 0) {
		for (var i=0; i < aTR.length; i++)	{
			vl_record = get_num_rec_field(aTR[i].id);
			if (document.getElementById('FA_SELECCION_' + vl_record).checked) {
				var vl_fecha = document.getElementById('FA_FECHA_FACTURA_' + vl_record).innerHTML;
				var aFecha = vl_fecha.split('/');
				if (vl_mes==0)
					vl_mes = aFecha[1];
				else {
					if (vl_mes != aFecha[1]) {
						vl_dif_meses = true;
						break;
					}
				}
			}
		}
	}
	var aTR = get_TR('ENVIO_NOTA_CREDITO');
	if (vl_dif_meses==false && aTR.length > 0) {
		for (i=0; i < aTR.length; i++)	{
			vl_record = get_num_rec_field(aTR[i].id);			
			if (document.getElementById('NC_SELECCION_' + vl_record).checked) {
				var vl_fecha = document.getElementById('NC_FECHA_NOTA_CREDITO_' + vl_record).innerHTML;
				var aFecha = vl_fecha.split('/');
				if (vl_mes==0)
					vl_mes = aFecha[1];
				else {
					if (vl_mes != aFecha[1]) {
						vl_dif_meses = true;
						break;
					}
				}
			}
		}
	}
	if (vl_dif_meses)
		document.getElementById('RE_DIF_MESES_0').innerHTML = 'Existen Facturas o Nota de Crédito de diferentes meses';
	else
		document.getElementById('RE_DIF_MESES_0').innerHTML = '';
	*/
}
function decode_utf8( s ){
  return decodeURIComponent( escape( s ) );
}

function request_autorizatb(ve_nro_autoriza_tb){
	var nro_autoriza_valida = ''
	var nro_auto_rep = 0

  	var aTR = get_TR('ENVIO_TRANSBANK');
	for (i=0; i < aTR.length; i++){
		vl_record = get_num_rec_field(aTR[i].id);
		nro_autoriza_valida = document.getElementById('NRO_AUTORIZA_TB_' + vl_record).value;
        
      	if (nro_autoriza_valida == ve_nro_autoriza_tb.value){
          	nro_auto_rep = nro_auto_rep + 1;
         
			if (nro_auto_rep > 1){
				alert('El Nro. Autorización ya se encuentra registrado');
				document.getElementById('NRO_AUTORIZA_TB_' + vl_record).value = '';
				return false;
			}
        }
	}
 
	var vl_record = get_num_rec_field(ve_nro_autoriza_tb.id);

	//Ajax para saber si el nro ya esta siendo usado
	var ajax = nuevoAjax();
	ajax.open("GET", "ajax_ingreso_transbank.php?nro_autoriza_tb="+ve_nro_autoriza_tb.value, false);
	ajax.send(null);
	const resp = URLDecode(ajax.responseText);

	if(resp == '2registros'){

		var url = "../envio_softland/help_doc_transbank.php?nro_autoriza_tb="+ve_nro_autoriza_tb.value;
		$.showModalDialog({
			url: url,
			dialogArguments: '',
			height: 480,
			width: 650,
			scrollable: false,
			onClose: function () {
				let returnVal = this.returnValue;
				if(returnVal == null){
					document.getElementById('NRO_AUTORIZA_TB_' + vl_record).value = '';
				}else{
					returnVal = URLDecode(returnVal);
					resultado(returnVal, vl_record);
				}
			}
		});

	}else{
		if(resp == 'err1'){
			alert('El Nro. Autorizacion ingresado NO existe');
			document.getElementById('NRO_AUTORIZA_TB_' + vl_record).value = '';
			return;
		}else if(resp == 'err2-1'){
			alert('El Nro. Autorizacion ingresado NO es del tipo Débito / Crédito');
			document.getElementById('NRO_AUTORIZA_TB_' + vl_record).value = '';
			return;
		}/*else if(resp == 'err2-2'){
			alert('El Nro. Autorizacion esta asociado a un ingreso pago que no esta confirmado');
			document.getElementById('NRO_AUTORIZA_TB_' + vl_record).value = '';
			return;
		}*/else if(resp == 'err3-1'){
			alert('El Nro. Autorizacion no se encuentra dentro del rango permitido de fecha para Débito');
			document.getElementById('NRO_AUTORIZA_TB_' + vl_record).value = '';
			return;
		}/*else if(resp == 'err3-2'){
			alert('El Nro. Autorizacion ya se encuentra traspasado a Softland');
			document.getElementById('NRO_AUTORIZA_TB_' + vl_record).value = '';
			return;
		}*/else if(resp == 'err4-1'){
			alert('El Nro. Autorizacion no se encuentra dentro del rango permitido de fecha para Crédito');
			document.getElementById('NRO_AUTORIZA_TB_' + vl_record).value = '';
			return;
		}/*else if(resp == 'err4-2'){
			alert('El Nro. Autorizacion excede la cantidad de traspasos a Softland según cantidad de cuotas para Crédito');
			document.getElementById('NRO_AUTORIZA_TB_' + vl_record).value = '';
			return;
		}*/

		resultado(resp, vl_record);
	}
}

function resultado(ve_resp, vl_record){
	const lista 	= ve_resp.split('|');

	const cod_ingreso_pago		= lista[0];
	const nom_tipo_origen_pago	= lista[1];
	const fecha_ingreso_pago	= lista[2];
	const nom_tipo_doc_pago		= lista[3];
	const cuotas				= lista[4];
	const cuotas_n				= lista[5];
	const cod_tipo_doc_pago		= lista[6];
	const anno_ingreso_pago		= lista[7];
	const monto_doc				= lista[8];
	const cod_empresa			= lista[9];

	if(cod_tipo_doc_pago != '') {
		document.getElementById('COD_INGRESO_PAGO_'+ vl_record).innerHTML = cod_ingreso_pago;
		document.getElementById('COD_INGRESO_PAGO_I_'+ vl_record).value = cod_ingreso_pago;
		document.getElementById('NOM_TIPO_ORIGEN_PAGO_'+ vl_record).innerHTML = nom_tipo_origen_pago;
		document.getElementById('FECHA_INGRESO_PAGO_'+ vl_record).innerHTML = fecha_ingreso_pago;
		document.getElementById('NOM_TIPO_DOC_PAGO_'+ vl_record).innerHTML = nom_tipo_doc_pago;
		document.getElementById('CUOTAS_'+ vl_record).innerHTML = cuotas;
		document.getElementById('CUOTAS_I_'+ vl_record).value = cuotas;
		document.getElementById('CUOTAS_N_'+ vl_record).value = cuotas_n;
		document.getElementById('COD_TIPO_DOC_PAGO_'+ vl_record).value = cod_tipo_doc_pago;
		document.getElementById('MONTO_ABONO_'+ vl_record).value = Math.trunc(monto_doc);
		document.getElementById('COD_EMPRESA_'+ vl_record).value = cod_empresa; 
    
    	if(anno_ingreso_pago < 2021){
       		alert ('El ingreso pago es de un año menor al 2022');
    	}  

	}else{
		alert('El nro autoriza Transbank no esta permitido..');
		return false;
	}
}

//utf8 to 1251 converter (1 byte format, RU/EN support only + any other symbols) by drgluck
function utf8_decode (aa) {
    var bb = '', c = 0;
    for (var i = 0; i < aa.length; i++) {
        c = aa.charCodeAt(i);
        if (c > 127) {
            if (c > 1024) {
                if (c == 1025) {
                    c = 1016;
                } else if (c == 1105) {
                    c = 1032;
                }
                bb += String.fromCharCode(c - 848);
            }
        } else {
            bb += aa.charAt(i);
        }
    }
    return bb;
}