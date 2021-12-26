<?php

/**
 * 
 * SESAE - Admin - Cambio password
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
require('global.php');

$minpw = 8;
$killflag = FALSE;

intestazione("Cambio password");

if (isset($_POST['oldp'])) {
//  !!!!!!!!!!!!!!!!!!!!!!!!	
	if (!password_verify($_POST['oldp'], $_SESSION['user']['password']))  {
		echo "\n<p align='center'>La password attuale non &egrave; corretta.</p>";
		echo "\n<p align='center'>La password non &egrave; stata modificata.</p>";
	} else {
		if ($_POST['newp2'] != $_POST['newp1']) {
			echo "\n<p align='center'>Le due password nuove non coincidono.</p>";
			echo "\n<p align='center'>La password non &egrave; stata modificata.</p>";
		} else {
			if (strlen($_POST['newp1']) < $minpw) {
				echo "\n<p align='center'>La password nuova deve essere lunga almeno <b>$minpw</b> caratteri.</p>";
				echo "\n<p align='center'>La password non &egrave; stata modificata.</p>";
			} else {
				echo "\n<p align='center'>La password &egrave; stata modificata.</p>";
				echo "\n<p align='center'>Rifare l'accesso alla procedura con le nuove credenziali.</p>";
				$db->query("UPDATE user SET password='" .  password_hash($_POST['newp1'], PASSWORD_DEFAULT) . "' WHERE iduser='" . $_SESSION['user']['iduser'] . "'");
				$killflag = TRUE;
			}
		}
	}
} else {

	echo "\n<form action='uty_password.php' method='post'>";
	echo "\n<table border='0' align='center'>";
	echo "<tr><td align='right'><b>Password attuale:</b></td><td align='left'><input type='password' name='oldp' size='30' maxlength='50' /></td></tr>";
	echo "<tr><td align='right'><b>Nuova password:</b></td><td align='left'><input type='password' name='newp1' size='30' maxlength='50' /></td></tr>";
	echo "<tr><td align='right'><b>Ripeti:</b></td><td align='left'><input type='password' name='newp2' size='30' maxlength='50' /></td></tr>";
	echo "<tr><td align='center' colspan='2'><input type='submit' value='Conferma il cambio della password' alt='Conferma il cambio della password' /></td></tr>";
	echo "\n</table></form>";
	
	echo "\n<p align='center'>La password deve essere lunga almeno <b>$minpw</b> caratteri.";
}

piede();

if ($killflag) session_destroy();

// ### END OF FILE ###