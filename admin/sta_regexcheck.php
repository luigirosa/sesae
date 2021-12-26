<?php

/**
 *
 * SESAE - Admin - Statistiche, controllo RegEx
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2020-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * 
 * 20200118 prima versione
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

if (isset($_POST['p'])) {
	foreach ($_POST['p'] as $idstatisticacampo=>$x) {
		if (isset($x['xxx'])) {
			$db->query("DELETE FROM statisticacampo WHERE idstatisticacampo='$idstatisticacampo'");
		} else {
			$a = array();
			$a[] = $b2->campoSQL("regola", $x['generator']);
			$db->query("UPDATE statisticacampo SET " . implode(',', $a) . " WHERE idstatisticacampo='$idstatisticacampo'");
		}
	}
}

intestazione("Statistiche: controllo RegEx");

$q = $db->query("SELECT * FROM statisticacampo WHERE algoritmo='RGX'");

echo "\n<form method='post' action='sta_regexcheck.php'>";

echo "\n<table border='0'>";
while ($r = $q->fetch_array()) {
	if (preg_match($r['regola'], 'pippo') === false) {
		$bg = $b2->bgcolor();
		$id = $r['idstatisticacampo'];
		echo "\n<tr $bg>";
		echo "\n<td $bg align='right'>$id</td>";
		echo "\n<td $bg>" . $b2->inputText("p[$id][regola]", $r['regola'], 200) . "</td>";
		echo "\n<td $bg>" . $b2->inputcheck("p[$id][xxx]", false) . "</td>";
		echo "\n</tr>";  
	}
}
echo "\n</table>";  
echo "\n<input type='submit' value='Aggiorna'/>";
echo "\n</form>";

piede();

// ### END OF FILE ###