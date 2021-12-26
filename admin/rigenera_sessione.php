<?php

/**
 *
 * SESAE - Admin - Rigenerazione della sessione (la sessione ammette piu' di 12 rigenerazioni)
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2018-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * 
 * 20180217 prima versione
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
session_start();

// DB
$a = parse_ini_file('../setup.ini', true);
$db = new mysqli($a['sql']['host'], $a['sql']['user'], $a['sql']['password'], $a['sql']['database']);
if (mysqli_connect_errno()) {
	echo "<html><head><title>Procedura in manutenzione</title><body><p>La procedura &egrave; in manutenzione, torneremo appena possibile.\n<!-- " . mysqli_connect_error() . " -->\n</p></body></html>";
	die();
}
$db->set_charset('UTF8');

// prima di tutto: se non e' impostato l'array di sessione, e' inutile continuare
if (!isset($_SESSION['user'])) {
	session_destroy();
	header('Location: index.php');
	error_log("*** SESAE - Sessione distrutta dalla rigenerazione ");
	die();
}

$q = $db->query("SELECT iduser FROM user WHERE iduser='" . $db->escape_string($_SESSION['user']['iduser']) . "' AND password='" . $db->escape_string($_SESSION['user']['password']) . "'");
if ($q->num_rows < 1) {
	error_log("Utente " . $_SESSION['user']['login'] . ": password mismatch, lo caccio (rigenerazione)");
	session_destroy();
	header('Location: index.php');
	die();
}

$_SESSION['timestamp'] = time();

// ### END OF FILE ###