<?php

/**
 *
 * SESAE - Admin - Elenco host con http-equiv=refresh
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2020-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * 
 * 20190106 prima versione
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

intestazione('http-equiv=refresh');

$q = $db->query("SELECT meta.idtarget,meta.raw 
                 FROM meta 
                 JOIN target ON meta.idtarget=target.idtarget
                 WHERE meta.raw LIKE '%refresh%' AND meta.raw LIKE '%url%' AND (meta.raw LIKE '%http:%' OR meta.raw LIKE '%https:%')
                 ORDER BY target.visited DESC");

if ($q->num_rows > 0) {
	echo "\n<table border='0' align='center'>";
	echo $b2->intestazioneTabella(array('Target', 'URL/Location', 'HTTP', "Header"));
	while ($r = $q->fetch_array()) {
		$rr = $db->query("SELECT target.idtarget,target.description,target.url,
                             targetdata.http_code,targetdata.http_location,targetdata.hostname
                      FROM target
                      JOIN targetdata ON target.idtarget=targetdata.idtarget
                      WHERE target.idtarget='$r[idtarget]'")->fetch_array();
		if (strpos($r['raw'], $rr['url']) === false) {
			$bg = $b2->bgcolor();
			$match = get_longest_common_subsequence($rr['url'], $r['raw']);
			echo "\n<tr $bg>";
			echo "<td $bg align='left'><a target='_blank' href='ana_targetedit.php?idtarget=$rr[idtarget]'>$rr[description]</a></td>";
			echo "<td $bg align='left'><a target='_blank' href='ana_targetedit.php?idtarget=$rr[idtarget]'>" . str_replace($match, "<b>$match</b>", $rr['url']) . "<br/>$rr[http_location]</a></td>";
			echo "<td $bg align='right'><a target='_blank' href='ana_targetedit.php?idtarget=$rr[idtarget]'>$rr[http_code]</a></td>";
			echo "<td $bg align='left'><a target='_blank' href='ana_targetedit.php?idtarget=$rr[idtarget]'>" . str_replace($match, "<b>$match</b>", htmlentities($r['raw'])) . "</a></td>";
			echo "\n</tr>";
		}
	}
	echo "\n</table>";
} else {
	echo "<b>Nessun target corrisponde alla ricerca indicata.</b>";
}

piede();

// ### END OF FILE ###