<?php

/**
 * 
 * SESAE - Admin - Gestione menu
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

intestazione("Gstione menu");

if (isset($_POST['p'])) {
	foreach($_POST['p'] as $id=>$x) {
		$a = array();
		$a[] = $b2->campoSQL('weight', $x['weight']);
		$menu = trim('' == $x['menu']) ? '&nbsp;' : $x['menu'];
		$a[] = $b2->campoSQL('menu', $menu);
		$db->query("UPDATE menu SET " . implode(',', $a) . " WHERE idmenu='$id'");
	}
}

$q = $db->query("SELECT * FROM menu WHERE idfather='0' ORDER BY weight");

echo "\n<form action='sys_menu.php' method='post'>";
echo "\n<p align='center'><input type='submit' value='Conferma le modifiche' alt='Conferma le modifiche'></p>";
echo "\n<table border='0' align='center'>";
echo $b2->intestazioneTabella(array('Peso', 'Menu'));

while ($r = $q->fetch_array()) {
	$bg = $b2->bgcolor();
	$id = $r['idmenu'];
	echo "\n<tr $bg>";
	echo "<td $bg align='center'>" . $b2->inputText("p[$id][weight]", $r['weight'], 7, 5, '', '', B2_IT_RIGHT) . "</td>";
	if ('&nbsp;' == $r['menu']) {
		echo "<td $bg align='left'>Separatore<input type='hidden' name='p[$id][menu]' value=''></td>";
	} else {
		$x = $b2->normalizza($r['menu'], B2_NORM_FORM);
		echo "<td $bg align='left'><input type='text' name='p[$id][menu]' value=\"$x\" size='30' maxlength='50'></td>";
	}
	echo "</tr>";
	// tiene figli?
	$qf = $db->query("SELECT * FROM menu WHERE idfather='$r[idmenu]' ORDER BY weight");
	if ($qf->num_rows > 0) {
		while ($rf = $qf->fetch_array()) {
			$bg = $b2->bgcolor();
			$id = $rf['idmenu'];
			echo "\n<tr>";
			echo "<td $bg align='center'>" . $b2->inputText("p[$id][weight]", $rf['weight'], 7, 5, '', '', B2_IT_RIGHT) . "</td>";
			if ('&nbsp;' == $rf['menu']) {
				echo "<td $bg align='left'>&nbsp;&nbsp;Separatore<input type='hidden' name='p[$id][menu]' value=''></td>";
			} else {
				$x = $b2->normalizza($rf['menu'], B2_NORM_FORM);
				echo "<td $bg align='left'>&nbsp;&nbsp;<input type='text' name='p[$id][menu]' value=\"$x\" size='30' maxlength='50'></td>";
			}
			echo "</tr>";
		}
	}
}

echo "\n</table>";
echo "\n<p align='center'><input type='submit' value='Conferma le modifiche' alt='Conferma le modifiche'></p>";
echo "\n</form>";

piede();

//### END OF FILE ###