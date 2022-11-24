/////////////////////////////////////////////
// Se duplican funciones del general.js, por un error en Safari, por alguna razon no reconoce las rutinas de general.js
function nuevoAjax()
{ 
	/* Crea el objeto AJAX. Esta funcion es generica para cualquier utilidad de este tipo, por
	lo que se puede copiar tal como esta aqui */
	var xmlhttp=false;
	try
	{
		// Creacion del objeto AJAX para navegadores no IE
		xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
	}
	catch(e)
	{
		try
		{
			// Creacion del objet AJAX para IE
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		catch(E)
		{
			if (!xmlhttp && typeof XMLHttpRequest!='undefined') xmlhttp=new XMLHttpRequest();
		}
	}
	return xmlhttp; 
}
function get_TR(tabla_id) {
	var aTR = Array();
	var tabla = document.getElementById(tabla_id);
	if (tabla) {   
		if (tabla.hasChildNodes()) {
			var children = tabla.childNodes;
			for (var i=0; i < children.length; i++) {
				if (children[i].nodeName=='TBODY') {
					if (children[i].hasChildNodes()) {
						var children2 = children[i].childNodes;
						for (var j=0; j<children2.length; j++) {
							if (children2[j].nodeName=='TR') {
								aTR[aTR.length] = children2[j];
	            }					
						}
					}
				}
			}
		}
	}
	return aTR;	
}
function get_num_rec_field(field) {
// dado un nombre tipo NOM_SUCURSAL_2 retorna el num del campo => "2"
	var pos = field.lastIndexOf('_');
	return field.substr(pos + 1, field.length - pos - 1);
}
////////////////////////////////////////////

var vl_id_control = 0;
var vl_id_control_resp = 0;

function get_returnVal() {
	var array					= '';
	var vl_cod_llamado_accion	= document.getElementById('COD_LLAMADO_ACCION').value;
	vl_aTR						= get_TR('DESTINATARIO');
	
	for (var i=0; i < vl_aTR.length; i++){
		var rec_tr = get_num_rec_field(vl_aTR[i].id);
		var cod_destinatario = document.getElementById('COD_DESTINATARIO_'+rec_tr).value;
		array = array+cod_destinatario+'|';
	}

    var vl_mensaje_length = document.formul.MENSAJE.value.length;
	var vl_array_length = array.length;
	
	if (vl_array_length == 0){
		alert('¡Debe seleccionar algún Destinatario!');
		return false;
	}else if (vl_mensaje_length == 0){
		alert('¡Debe escribir la Respuesta!');
		return false;
	}else{
		if(vl_cod_llamado_accion == 4){//solicitud_cotizacion
			var vl_cod_solicitud_cotizacion	= document.getElementById('COD_SOLICITUD_COTIZACION').value;
			var vl_nom_estado_sol_cot		= document.getElementById('NOM_EST_SOL_COT').value;
			var vl_cod_destinatario	= document.getElementById('COD_DESTINATARIO_H').value;
			
			if(vl_cod_destinatario != 2){//Angel Scianca
				var aTR = get_TR('RESPONSABLE');
				if(aTR.length == 0){
					alert('La Solicitud de Cotización asociada a este registro ('+vl_cod_solicitud_cotizacion+') se encuentra en estado '+vl_nom_estado_sol_cot+', por lo tanto debe indicar a un responsable');
					return false;
				}
			}
		}
		document.getElementById('COD_DESTINATARIO_ENVIO_H').value = array;
	}
	
	
	//return false;
	return true;
}

function ver_destinatarios_selecc(){
	var b_selecc = document.getElementById('b_selecc');
	b_selecc.style.display = 'none';
	
	var b_todos = document.getElementById('b_todos');
	b_todos.style.display = '';
		
	var aTR = get_TR('DESTINATARIO');
	for (var i=0; i<aTR.length; i++){
		var seleccion = document.getElementById('ENVIAR_MAIL_'+i).checked;
		if(seleccion == false){
			var vl_destinatario = document.getElementById('DESTINATARIO_' + i);
			vl_destinatario.style.display = 'none';
		}
	}
}

function ver_destinatarios_todos(){
	var b_todos = document.getElementById('b_todos');
	b_todos.style.display = 'none';
	
	var b_selecc = document.getElementById('b_selecc');
	b_selecc.style.display = '';
	
	var aTR = get_TR('DESTINATARIO');
	for (var i=0; i<aTR.lenegth; i++){
		var vl_destinatario = document.getElementById('DESTINATARIO_' + i);
		vl_destinatario.style.display = '';
	}
}

function numbersonly(myfield, e, dec)
{
var key;
var keychar;

if (window.event)
   key = window.event.keyCode;
else if (e)
   key = e.which;
else
   return true;
keychar = String.fromCharCode(key);

// control keys
if ((key==null) || (key==0) || (key==8) || 
    (key==9) || (key==13) || (key==27) )
   return true;

// numbers
else if ((("0123456789").indexOf(keychar) > -1))
   return true;

// decimal point jump
else if (dec && (keychar == "."))
   {
   myfield.form.elements[dec].focus();
   return false;
   }
else
   return false;
}

function del_destinatario(ve_control){
	record = get_num_rec_field(ve_control.id);
	var tr = document.getElementById('DESTINATARIO_' + record); 
	tr.parentNode.removeChild(tr);
}

function del_destinatario2(ve_control){
	record = get_num_rec_field(ve_control.id);
	var tr = document.getElementById('RESPONSABLE_' + record); 
	tr.parentNode.removeChild(tr);
}

function load_destinatarios2(ve_dat){
	var lista = ve_dat.split('|');
	//elimina al usuario si es que esta ingresado para ser ingresado el nuevo
	aTR = get_TR('RESPONSABLE');
	for (var i=0; i < aTR.length; i++){
		var record = get_num_rec_field(aTR[i].id);
		var vl_cod_usuario_resp = document.getElementById('COD_USUARIO_VENDEDOR1_RESP_'+record);
		
		del_destinatario2(vl_cod_usuario_resp);
	}

	var vl_tbody = document.getElementById('TBODY_RESPONSABLE');
	
	var vl_tr = document.createElement("tr");
	vl_tr.id = 'RESPONSABLE_0';
		
	vl_tr.className="oscuro";
		
	var vl_td_label = document.createElement("td");
	vl_td_label.width = "75%";
	vl_td_label.align = "left";
	vl_tr.appendChild(vl_td_label);
		var vl_label_nom_dest = document.createElement("label");
		vl_label_nom_dest.innerHTML = lista[1];
		vl_label_nom_dest.id = 'NOM_USUARIO_0';
		vl_td_label.appendChild(vl_label_nom_dest);
		//
			var vl_input_cod_dest_h = document.createElement("input");
			vl_input_cod_dest_h.type = 'hidden';
			vl_input_cod_dest_h.id = 'COD_USUARIO_VENDEDOR1_RESP_0';
			vl_input_cod_dest_h.name = 'COD_USUARIO_VENDEDOR1_RESP_0';
			
			var vl_ajax = nuevoAjax();
			vl_ajax.open("GET", "ajax_cod_destinatario2usuario.php?cod_destinatario="+lista[0], false);
			vl_ajax.send(null);		
			vl_input_cod_dest_h.value = vl_ajax.responseText;
			
			vl_td_label.appendChild(vl_input_cod_dest_h);	

	var vl_td_btn = document.createElement("td");
	vl_td_btn.width = "25%";
	vl_td_btn.align = "center";
	vl_tr.appendChild(vl_td_btn);
		var vl_imag_selec = document.createElement("img");
			vl_imag_selec.id = 'BTN_DEL_RESP_0';
			vl_imag_selec.name = 'BTN_DEL_RESP_0';
			vl_imag_selec.style.cursor = 'pointer';
			vl_imag_selec.src = '../../../../../commonlib/trunk/images/b_delete_line.jpg';
			vl_imag_selec.setAttribute('onclick', 'del_destinatario2(this);');
			vl_td_btn.appendChild(vl_imag_selec);
			
	vl_tbody.appendChild(vl_tr);
}

function load_destinatarios(ve_dat){
	var lista = ve_dat.split('|');
	//valida que no tenga el mismo Destinario
	aTR = get_TR('DESTINATARIO');
	for (var i=0; i < aTR.length; i++) {
		var record = get_num_rec_field(aTR[i].id);
		var vl_cod_destinatario = document.getElementById('COD_DESTINATARIO_'+record).value;
		
		if(vl_cod_destinatario == lista[0]){
			alert('¡El Usuario Seleccionado. Ya se encuentra en Destinatarios!');
			return false;
		}
	}
	
	
	//ultimo record
	var record = 0;
	aTR = get_TR('DESTINATARIO');
	for (var i=0; i < aTR.length; i++) {
		record = get_num_rec_field(aTR[i].id);
	}
	var vl_id = parseInt(record) + 1;
	
	//var vl_tabla = document.getElementById('DESTINATARIO');
	var vl_tbody = document.getElementById('TBODY_DESTINATARIO');
	
	var vl_tr = document.createElement("tr");
	vl_tr.id = 'DESTINATARIO_' + vl_id;
		
	if (vl_id%2==0)
		vl_tr.className="claro";
	else
		vl_tr.className="oscuro";
		
	/*
	var vl_td_check = document.createElement("td");
	vl_td_check.width = "20%";
	vl_td_check.align = "center";
	vl_tr.appendChild(vl_td_check);
		var vl_check_selec = document.createElement("input");
			vl_check_selec.type = "checkbox";
			//vl_check_selec.setAttribute('name', "R_ANUALIDAD")
			vl_check_selec.id = 'ENVIAR_MAIL_' + vl_id;
			//vl_check_selec.onclick = function(){select_color();hidden_anualidad();}						
			vl_td_check.appendChild(vl_check_selec);
	*/
	var vl_td_label = document.createElement("td");
	vl_td_label.width = "75%";
	vl_td_label.align = "left";
	vl_tr.appendChild(vl_td_label);
		var vl_label_nom_dest = document.createElement("label");
		vl_label_nom_dest.innerHTML = lista[1];
		vl_label_nom_dest.id = 'NOM_DESTINATARIO_' + vl_id;
		vl_td_label.appendChild(vl_label_nom_dest);
		//
			var vl_input_cod_dest_h = document.createElement("input");
			vl_input_cod_dest_h.type = 'hidden';
			vl_input_cod_dest_h.id = 'COD_DESTINATARIO_' + vl_id;
			vl_input_cod_dest_h.value = lista[0];
			vl_td_label.appendChild(vl_input_cod_dest_h);	

	var vl_td_btn = document.createElement("td");
	vl_td_btn.width = "25%";
	vl_td_btn.align = "center";
	vl_tr.appendChild(vl_td_btn);
		var vl_imag_selec = document.createElement("img");
			vl_imag_selec.id = 'BTN_DEL_' + vl_id;
			vl_imag_selec.name = 'BTN_DEL_' + vl_id;
			vl_imag_selec.style.cursor = 'pointer';
			vl_imag_selec.src = '../../../../../commonlib/trunk/images/b_delete_line.jpg';
			//vl_imag_selec.onclick = 'del_destinatario(this)'; //function(){del_destinatario(this);}
			vl_imag_selec.setAttribute('onclick', 'del_destinatario(this);');
			vl_td_btn.appendChild(vl_imag_selec);
			
	vl_tbody.appendChild(vl_tr);
}

function add_destinatarios(ve_list){
	vl_id_control = vl_id_control+1;

	tlist2.update(); 
	var vl_list = $F('facebook-demo');
	load_destinatarios(vl_list);
	
	var id = vl_id_control - 1;
	tlist2.dispose(document.getElementById('bit-'+id));
	
}

function add_destinatarios2(ve_list){
	vl_id_control_resp = vl_id_control_resp+1;

	tlist3.update(); 
	var vl_list = $F('facebook-demo2');
	load_destinatarios2(vl_list);
	
	var id = vl_id_control_resp - 1;
	tlist3.dispose(document.getElementById('bit-'+id));
	
}