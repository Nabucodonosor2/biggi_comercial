function load_saldo(){
	
	var vl_aTR				= get_TR('ITEM_CARTOLA_PARTICIPACION');
	
	var td = document.getElementById("ITEM_CARTOLA_PARTICIPACION_0").getElementsByTagName("td");
	var abono = td[2].innerHTML;
	var retiro = td[3].innerHTML;
	
	var saldo_total = abono - retiro;	
	td[4].innerHTML=saldo_total;
	
	for (var i = 1; i < vl_aTR.length; i++){
		var vl_rec	= get_num_rec_field(vl_aTR[i].id);
	
		
		var td = document.getElementById("ITEM_CARTOLA_PARTICIPACION_"+vl_rec).getElementsByTagName("td");
		var abono = td[2].innerHTML;
		var retiro = td[3].innerHTML;

		saldo_total = abono - retiro + saldo_total;
		td[4].innerHTML=saldo_total;
	}
	
	
}


function request_cartola_part(){
	var ve_valor =document.getElementById('COD_USUARIO_VENDEDOR_H_0').value;
		var url2 = "request_agrega_part.php?cod_usuario_vendedor="+ve_valor;
			$.showModalDialog({
				 url: url2,
				 dialogArguments: '',
				 height: 380,
				 width: 1000,
				 scrollable: false,
				 onClose: function(){ 
				 	var returnVal2 = this.returnValue;
				 	if (returnVal2 == null){		
						return false;
					}else{
						var input = document.createElement("input");
						input.setAttribute("type", "hidden");
						input.setAttribute("name", "b_create_x");
						input.setAttribute("id", "b_create_x");
						document.getElementById("input").appendChild(input);
						
					 	var returnVal = this.returnValue;				 	
					 	
						if (returnVal == null)		
							return false;		
						else {													
							for (i=0; i < returnVal.length; i++) {
									
								var resp = returnVal[i].split('|');
								var vl_row = add_line('ITEM_CARTOLA_PARTICIPACION','cartola_participacion'); 								
								var linea_ant=vl_row-1;								
								var abono=Number(resp[4].replace('.',''));
								
								document.getElementById('GLOSA_H_'+vl_row).value = 'ABONO COMISIONES PARTICIPACION '+resp[0];
								document.getElementById('COD_PARTICIPACION_'+vl_row).value = resp[0];
								document.getElementById('FECHA_MOVIMIENTO_H_'+vl_row).value = resp[1];
								//document.getElementById('NOM_USUARIO_VENDEDOR').value = resp[2];
								//document.getElementById('NOM_ESTADO_PARTICIPACION').value = resp[3];
								document.getElementById('ABONO_H_'+vl_row).value = abono;
								document.getElementById('RETIRO_H_'+vl_row).value = 0;
								
								if(linea_ant<0){
									var saldo= 0;									
								}else{
									var saldo=  Number(document.getElementById('SALDO_H_'+linea_ant).value);
								}
								
								saldo= abono+saldo;
								document.getElementById('SALDO_H_'+vl_row).value=saldo;
								
								///coloca los valores en el html
								abono=new Intl.NumberFormat("es-CL").format(abono);
								saldo=new Intl.NumberFormat("es-CL").format(saldo);
								document.getElementById("FECHA_MOVIMIENTO_"+vl_row).innerHTML=resp[1]; //fecha
								document.getElementById("GLOSA_"+vl_row).innerHTML= 'ABONO COMISIONES PARTICIPACION '+resp[0]; //glosa
								document.getElementById("ABONO_"+vl_row).innerHTML= abono;  //abono
								document.getElementById("RETIRO_"+vl_row).innerHTML=0 ; //retiro
								document.getElementById("SALDO_"+vl_row).innerHTML=saldo; //saldo
								document.getElementById("MES_"+vl_row).innerHTML=resp[5];; //saldo


							}														
							return true;
						}
					}
				}
			});
		
	  	return true;

}

function request_cartola_ret(){
	var ve_valor =document.getElementById('COD_USUARIO_VENDEDOR_H_0').value;
		var url2 = "request_agrega_ret.php?cod_usuario_vendedor="+ve_valor;
			$.showModalDialog({
				 url: url2,
				 dialogArguments: '',
				 height: 230,
				 width: 410,
				 scrollable: false,
				 onClose: function(){ 
				 	var returnVal2 = this.returnValue;
				 	if (returnVal2 == null){		
						return false;
					}else{
						var input = document.createElement("input");
						input.setAttribute("type", "hidden");
						input.setAttribute("name", "b_create_x");
						input.setAttribute("id", "b_create_x");
						document.getElementById("input").appendChild(input);
						
						var returnVal = this.returnValue;
						var saldo = 0;
						
						var resp = returnVal.split('|');
						var vl_row = add_line('ITEM_CARTOLA_PARTICIPACION','cartola_participacion'); 
						var linea_ant=vl_row-1;						
						var retiro=Number(resp[0].replace('.',''));						
						var date = new Date();

						var day = date.getDate();
						var month = date.getMonth() + 1;
						var year = date.getFullYear();
						
						 if (day < 10) { 
							 day = '0' + day; 
					        } 
					     if (month < 10) { 
					    	 month = '0' + month; 
					        } 

						document.getElementById('GLOSA_H_'+vl_row).value = resp[1];
						document.getElementById('FECHA_MOVIMIENTO_H_'+vl_row).value = day+'/'+month+'/'+year;
						document.getElementById('ABONO_H_'+vl_row).value = 0;
						document.getElementById('RETIRO_H_'+vl_row).value = resp[0];
						
						var saldo=  Number(document.getElementById('SALDO_H_'+linea_ant).value);															
						saldo= saldo-retiro;
						document.getElementById('SALDO_H_'+vl_row).value=saldo;
						
						retiro=new Intl.NumberFormat("es-CL").format(retiro);
						saldo=new Intl.NumberFormat("es-CL").format(saldo);
						document.getElementById("FECHA_MOVIMIENTO_"+vl_row).innerHTML=day+'/'+month+'/'+year; //fecha
						document.getElementById("GLOSA_"+vl_row).innerHTML=  resp[1]; //glosa
						document.getElementById("ABONO_"+vl_row).innerHTML= 0;  //abono
						document.getElementById("RETIRO_"+vl_row).innerHTML=retiro; //retiro
						document.getElementById("SALDO_"+vl_row).innerHTML=saldo; //saldo

					}
				}
			});
		
	  	return true;

}