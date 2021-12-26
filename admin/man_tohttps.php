<?php

/**
 *
 * SESAE - Admin - Elenco host migrati in HTTPS
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2019-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * 
 * 20180218 prima versione
 * 20181010 aggiunta reset visited per forzare visita immediata
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
	foreach ($_POST['p'] as $idtarget=>$x) {
		if (isset($x['go'])) {
			$a = array();
			$rr = $db->query("SELECT http_location FROM targetdata WHERE idtarget='$idtarget'")->fetch_array();
			$a[] = $b2->campoSQL("url", $rr['http_location']);
			$a[] = $b2->campoSQL("visited", 0);
			$db->query("UPDATE target SET " . implode(',', $a) . " WHERE idtarget='$idtarget'");
		}
	}
}

intestazione('To HTTPS');
	
$q = $db->query("SELECT target.idtarget,target.description,target.url,
                        targetdata.http_code,targetdata.http_location
                 FROM target
                 JOIN targetdata ON target.idtarget=targetdata.idtarget
                 WHERE INSTR(http_location, '://')>0
                 ORDER BY target.visited");

$almenouno = false;
if ($q->num_rows > 0) {
	echo "\n<form action='man_tohttps.php' method='post'>";
	echo "\n<table border='0' align='center'>";
	echo $b2->intestazioneTabella(array('Target', 'URL/Location', 'HTTP', "Migrare?"));
	while ($r = $q->fetch_array()) {
		$idtarget = $r['idtarget'];
		$transformurl = str_replace('http:', 'https:', $r['url']);
		if (substr($transformurl, -1) != '/') $transformurl = $transformurl . '/';
		$location = substr($r['http_location'], -1) == '/' ? $r['http_location'] : $r['http_location'] . '/';
		if ($transformurl == $location and $transformurl != $r['url']) {
			$bg = $b2->bgcolor();
			echo "\n<tr $bg>";
			echo "<td $bg align='left'><a target='_blank' href='ana_targetedit.php?idtarget=$r[idtarget]'>$r[description]&nbsp;</td>";
			echo "<td $bg align='left'>$r[url]<br/>$r[http_location]</td>";
			echo "<td $bg align='right'>$r[http_code]</a></td>";
			echo "<td $bg align='center'>" . $b2->inputCheck("p[$idtarget][go]", true) . "</td>";
			echo "\n</tr>";
			$almenouno = true;
		}
	}
	echo "\n</table>";
	if ($almenouno) {
		echo "<div align='center'><input type='submit' value='Converti'/></div>";
	} else {
		echo "<div align='center'>Nessun target corrisponde alla ricerca indicata.</div>";
	}
	echo "\n</form>";
} else {
	echo "<div align='center'>Nessun target corrisponde alla ricerca indicata.</div>";
}

piede();

// ### END OF FILE ###
