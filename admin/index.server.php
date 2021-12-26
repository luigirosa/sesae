<?php

/**
 *
 * SESAE - Admin - Login lato server
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2018-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * 
 * 20180217 prima versione
 * 20190121 spostamento su set.sesae.com
 * 20211204 cambio licenza per pubblicazione sorgenti
 *
 * This file is part of SESAE.
 *
 * SESAE is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SESAE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SESAE.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

define('SESAE', true);

ini_set("session.gc_maxlifetime", "9000"); 
ini_set('session.cookie_httponly', 1);
session_start();

// sicurezza X-Content-Type-Options: nosniff
header("X-Frame-Options: deny");
header("Frame-Options: deny");
header("X-XSS-Protection: \"1; mode=block\"");
header("X-Content-Type-Options: nosniff");

// DB
$a = parse_ini_file('setup.ini', true);
$db = new mysqli($a['sql']['host'], $a['sql']['user'], $a['sql']['password'], $a['sql']['database']);
if (mysqli_connect_errno()) {
	echo "<html><head><title>Procedura in manutenzione</title><body><p>La procedura &egrave; in manutenzione, torneremo appena possibile.\n<!-- " . mysqli_connect_error() . " -->\n</p></body></html>";
	die();
}
$db->set_charset('UTF8');
unset($a);

if(isset($_POST['user']) and isset($_POST['password'])) {
	$login = trim($db->escape_string($_POST['user']));
	$password = $db->escape_string($_POST['password']);
	$loginok = FALSE;
	if ('' !=  $login) {
		$q = $db->query("SELECT * FROM user WHERE login='$login' AND isactive='1'"); 
		// user sconociuto
		if ($q->num_rows == 1) {
			$r = $q->fetch_array();
			if (password_verify($password, $r['password'])) {
				$loginok = TRUE;
			} else {
				$db->query("UPDATE user SET lastloginko=NOW() WHERE iduser='$r[iduser]'");
			}
		}
	}
	// se il login è OK, valorizzo le variabili di sessione
	if ($loginok) {
		$_SESSION['user'] = $r;  // $_SESSION['user'] è un ARRAY che contiene il record dell'user
		$db->query("UPDATE user SET lastloginok=NOW() WHERE iduser='$r[iduser]'");
		echo "OK";
	}
}

// ### END OF FILE ###