<?php

/**
 * 
 * SESAE - Admin - Anagrafica categorie
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

if (isset($_POST['p'])) {
	foreach ($_POST['p'] as $idcategory=>$x) {
		if (isset($x['xxx'])) {
			$db->query("DELETE FROM category WHERE idcategory='$idcategory'");
		} else {
			$a = array();
			$a[] = $b2->campoSql('weight', $x['weight']);
			$a[] = $b2->campoSql('category', $x['category']);
			$a[] = $b2->campoSql('enabled', isset($x['enabled']) ? '1' : '0');
			if ((0 == $idcategory) and ('' != trim($x['category']))) {
				$db->query("INSERT INTO category SET " . implode(',', $a));
			} else {
				if ($idcategory > 0) {
					$db->query("UPDATE category SET " . implode(',', $a) . " WHERE idcategory='$idcategory'");
				}
			}
		}
	}
}

intestazione("Anagrafica categorie");
	
$q = $db->query("SELECT * FROM category ORDER BY weight");

echo "\n<form action='ana_categorie.php' method='post'>";
echo "\n<table border='0' align='center'>";
echo $b2->intestazioneTabella(array('Peso', 'Categoria', 'Abilitata', 'Del'));

// nuovo record
$bg = $b2->bgcolor();
echo "\n<tr $bg>";
echo "<td $bg align='center'>" . $b2->inputText("p[0][weight]", 100, 7, 5, '', '', B2_IT_RIGHT) . "</td>";
echo "<td $bg align='center'>" . $b2->inputText("p[0][category]", '', 50, 250) . "</td>";
echo "<td $bg align='center'>" . $b2->inputCheck("p[0][enabled]", true) . "</td>";
echo "<td $bg align='center'>New</td>";


while ($r = $q->fetch_array()) {
	$bg = $b2->bgcolor();
	$id = $r['idcategory'];
	echo "\n<tr $bg>";
	echo "<td $bg align='center'>" . $b2->inputText("p[$id][weight]", $r['weight'], 7, 5, '', '', B2_IT_RIGHT) . "</td>";
	echo "<td $bg align='center'>" . $b2->inputText("p[$id][category]", $r['category'], 50, 250) . "</td>";
	echo "<td $bg align='center'>" . $b2->inputCheck("p[$id][enabled]", $r['enabled'] == 1) . "</td>";
	$rr = $db->query("SELECT COUNT(*) FROM target WHERE idcategory='$id'")->fetch_array();
	if ($rr[0] > 0) {
		$del = "$rr[0]";	
	} else {
		$del = $b2->inputCheck("p[$id][xxx]", false); 
	}
	echo "<td $bg align='center'>$del</td>";
	echo "\n</tr>";
}

echo "\n</table>";
echo "\n<p align='center'><input type='submit' value='Conferma le modifiche' alt='Conferma le modifiche'></p>\n</form>";

piede();

// ### END OF FILE ###