<?php

/**
 *
 * SESAE - Admin - Elenco URL doppi
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2019-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * 
 * 20181022 prima versione
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

intestazione('URL doppi');
	
$q = $db->query("SELECT GROUP_CONCAT(idtarget), url, COUNT(*) c FROM target GROUP BY url  HAVING c > 1 ORDER BY idtarget");

if ($q->num_rows > 0) {
	echo "\n<table border='0' align='center'>";
	echo $b2->intestazioneTabella(array('Target', 'URL'));
	while ($r = $q->fetch_array()) {
		$bg = $b2->bgcolor();
		echo "\n<tr $bg>";
		$aidtarget = explode(',', $r[0]);
		echo "<td $bg align='left' valign='top'>";
		foreach ($aidtarget as $idtarget) {
			$rr = $db->query("SELECT description FROM target WHERE idtarget='$idtarget'")->fetch_array();
			echo "<a href='ana_targetedit.php?idtarget=$idtarget&exterminate=2'>DELETE</a> $idtarget <a href='ana_targetedit.php?idtarget=$idtarget' target='_blank'>$rr[description]</a><br/>";
		}
		echo "</td>";
		echo "<td $bg align='left' valign='top'>$r[url]</td>";
		echo "\n</tr>";
	}
	echo "\n</table>";
} else {
	echo "<b>Nessun target corrisponde alla ricerca indicata.</b>";
}


piede();

// ### END OF FILE ###
