<?php
function encriptar_url($txt_input, $key){	
	$result = '';
	for($i=0; $i<strlen($txt_input); $i++) {
		$char = substr($txt_input, $i, 1);
		$keychar = substr($key, ($i % strlen($key))-1, 1);
		$char = chr(ord($char)+ord($keychar));
		$result.=$char;
	}
	$txt_ouput = base64_encode($result);

	return $txt_ouput;
}

function dencriptar_url($txt_input, $key){	
	$result = '';
	$string = base64_decode($txt_input);
	for($i=0; $i<strlen($string); $i++) {
		$char = substr($string, $i, 1);
		$keychar = substr($key, ($i % strlen($key))-1, 1);
		$char = chr(ord($char)-ord($keychar));
		$result.=$char;
	}
	$txt_ouput = $result;
	
	return $txt_ouput;
}

function del_list($file_list){
	unlink($file_list);
}

function load_list(){	
	require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
	require_once ("../../../appl.ini");

	session::set('K_ROOT_URL', K_ROOT_URL);
	session::set('K_ROOT_DIR', K_ROOT_DIR);
	session::set('K_CLIENTE', K_CLIENTE);
	session::set('K_APPL', K_APPL);
	
	$sql = "SELECT	 COD_DESTINATARIO
					,NOM_DESTINATARIO
					,MAIL
					,COD_USUARIO
					,VIGENTE
			FROM	DESTINATARIO
			WHERE	VIGENTE = 'S'";
		
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$result = $db->build_results($sql);	
	
	if (file_exists('fetched.dat')) {
	    del_list('fetched.dat');
	}
	$fp=fopen("fetched.dat","x");
	//$contenido = '[';
	fwrite($fp,'[');
	for ($i = 0; $i < count($result); $i++){
		$cod_destinatario = $result[$i]['COD_DESTINATARIO'];
		$nom_destinatario = $result[$i]['NOM_DESTINATARIO'];
		$mail = $result[$i]['MAIL'];
		
		$contenido .='{"caption":"&#34;'.$nom_destinatario.'&#34;  &#60'.$mail.'&#62;","value":"'.$cod_destinatario.'|'.$nom_destinatario.'"},';
	}
	
	$contenido = substr($contenido, 0, -1);
	$contenido .= ']';
	fwrite($fp,$contenido);
	fclose($fp);
}
?>