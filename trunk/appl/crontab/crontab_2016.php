<?php
// se ejecuta en la noche como las 1:00
require_once("class_PHPMailer.php");
require_once("class_database.php");
require_once("../../appl.ini");

class crontab_noche {
	function crontab_noche() {
		$this->prueba();
	}
	function prueba() {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$db->EXECUTE_SP('sp_crontab_noche', "'ALL'");
	}
}

$c = new crontab_noche();
//http://www.biggi.cl/sysbiggi_new/comercial_biggi/biggi/trunk/appl/crontab/crontab.php
?>