<?php

/**
 *
 * SESAE - Admin - Elenco host orfani di probe
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2020-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * 
 * 20200524 prima versione
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

intestazione('Orfani');

$q = $db->query("SELECT target.idtarget,target.description,target.url,target.visited,
                        targetdata.http_location,targetdata.html_title
                 FROM target
                 JOIN targetdata ON target.idtarget=targetdata.idtarget
                 LEFT JOIN targetprobe ON target.idtarget=targetprobe.idtarget
                 WHERE targetprobe.idtarget IS NULL");

if ($q->num_rows > 0) {
	echo "\n<table border='0' align='center'>";
	echo $b2->intestazioneTabella(array('Target', 'URL/Location', 'Title', 'Ex'));
	while ($r = $q->fetch_array()) {
		$bg = $b2->bgcolor();
		echo "\n<tr $bg>";
		echo "<td $bg align='left'><a href='ana_targetedit.php?idtarget=$r[idtarget]' target='_blank'>" . date("j/n/Y H:i", $r['visited']) . "</a>&nbsp;</td>";
		echo "<td $bg align='left'><a target='_blank' href='ana_targetedit.php?idtarget=$r[idtarget]'>$r[description]</a></td>";
		echo "<td $bg align='left'><a target='_blank' href='ana_targetedit.php?idtarget=$r[idtarget]'>$r[url]<br/>$r[http_location]</a></td>";
		echo "<td $bg align='left'><a target='_blank' href='ana_targetedit.php?idtarget=$r[idtarget]'>$r[html_title]</a></td>";
		echo "</tr>";
	}
	echo "\n</table>";
} else {
	echo "<b>Nessun target corrisponde alla ricerca indicata.</b>";
}

piede();

// ### END OF FILE ###