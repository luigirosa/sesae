<?php

/**
 *
 * SESAE - Admin - Elenco host sospetti
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2020-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * 
 * 20190113 prima versione
 * 20211204 cambio licenza per pubblicazione sorgenti
 * 20220124 rimosso campo target.checked
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

intestazione('Sospetti');

$q = $db->query("SELECT target.idtarget,target.description,target.url,target.visited,
                        targetdata.http_location,targetdata.html_title
                 FROM target
                 JOIN targetdata ON target.idtarget=targetdata.idtarget
                 WHERE target.visited>0 AND (INSTR(targetdata.html_title,'sospes')>0 OR INSTR(targetdata.html_title,'dominio')>0 OR INSTR(targetdata.html_title,'domain')>0 OR INSTR(targetdata.html_title,'redirect')>0 OR INSTR(targetdata.html_title,'aruba')>0 OR INSTR(targetdata.html_title,'error')>0 OR INSTR(targetdata.html_title,'vendit')>0 OR HEX(targetdata.html_title) REGEXP '^(..)*(E[4-9])')
                 ORDER BY target.visited");

if ($q->num_rows > 0) {
	echo "\n<table border='0' align='center'>";
	echo $b2->intestazioneTabella(array('Last', 'Sito', 'URL', 'Titolo HTML'));
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