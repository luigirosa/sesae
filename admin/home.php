<?php

/**
 *
 * SESAE - Admin - Pagina del menu principale
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2018-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * 
 * 20180217 prima versione
 * 20181011 indicazione del target
 * 20200614 basta log
 * 20211204 cambio licenza per pubblicazione sorgenti
 * 20211211 aggiunta visualizzazione target che saranno analizzati
 * 20220125 nuovo metodo di accodamento
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

intestazione('');

echo "\n<table border='0'><tr><td valign='top'>"; // tabellona

echo "<b>Ultime scansioni:</b>";
echo "\n<table border='0'>"; // tabella ultime scansioni
$b2->bgcolor(true);
$q = $db->query("SELECT target.idtarget,target.description,target.url,target.visited,target.visited_before,
                        targetdata.http_code,targetdata.html_title,
                        probe.probe
                 FROM target 
                 JOIN targetdata ON target.idtarget=targetdata.idtarget
                 LEFT JOIN probe ON target.lastprobe=probe.idprobe
                 ORDER BY target.visited DESC 
                 LIMIT 40");
while ($r = $q->fetch_array()) {
	$bg = $b2->bgcolor();
	echo "\n<tr $bg>";
	echo "<td $bg align='left'><a href='ana_targetedit.php?idtarget=$r[idtarget]' target='_blank'>$r[description]</a></td>";
	echo "<td $bg align='right'>$r[http_code]</td>";
	echo "<td $bg align='left'>" . substr($r['html_title'], 0, 50) . "</td>";
	echo "<td $bg align='left'>" . tempopassato($r['visited'] - $r['visited_before'], true) . "</td>";
	echo "<td $bg align='left'>$r[probe]</td>";
	echo "</tr>";
}
echo "\n</table>"; // tabella ultime scansioni

echo "\n</td><td valign='top'>"; // tabellona

echo "<b>Prossime scansioni:</b>";
echo "\n<table border='0'>"; // tabella scansioni future
$b2->bgcolor(true);
$q = $db->query("SELECT target.idtarget,target.description,target.url,target.visited,target.visited_before,
                        targetdata.http_code,targetdata.html_title,
												probe.probe
                 FROM target 
                 JOIN targetdata ON target.idtarget=targetdata.idtarget
                 LEFT JOIN probe ON target.nextprobe=probe.idprobe
                 ORDER BY target.visited  
                 LIMIT 40");
while ($r = $q->fetch_array()) {
	$bg = $b2->bgcolor();
	echo "\n<tr $bg>";
	echo "<td $bg align='left'><a href='ana_targetedit.php?idtarget=$r[idtarget]' target='_blank'>$r[description]</a></td>";
	echo "<td $bg align='right'>$r[http_code]</td>";
	echo "<td $bg align='left'>" . substr($r['html_title'], 0, 50) . "</td>";
	echo "<td $bg align='left'>" . tempopassato(time() - $r['visited'], true) . "</td>";
	echo "<td $bg align='left'>$r[probe]</td>";
	echo "</tr>";
}
echo "\n</table>"; // tabella scansioni future

echo "\n<tr>";  //seconda riga tabellona

echo "<td valign='top'><b>Sonde:</b>";
$b2->bgcolor(true);
$q = $db->query("SELECT probe.probe,probe.lasttime,probe.isenabled,probe.counter,probe.version,
                        target.description
                 FROM probe
                 JOIN target ON probe.lasttarget=target.idtarget");
echo "\n<table border='0'>";
while ($r = $q->fetch_array()) {
	$bg = $b2->bgcolor();
	echo "\n<tr $bg>";
	echo "<td $bg align='left'>$r[probe]</td>";
	echo "<td $bg align='left'>$r[description]</td>";
	echo "<td $bg align='right'>" . date("j/n/Y G:i:s", $r['lasttime']) . "</td>";
	echo "<td $bg align='right'>" . number_format($r['counter'], 0, ',', '.') . "</td>";
	echo "<td $bg align='left'>$r[version]</td>";
	echo "</tr>";
}
echo "\n</table></td>";  //sonde

echo "<td valign='top'><b>Prossime scansioni per sonde:</b>";
$b2->bgcolor(true);
$qp = $db->query("SELECT idprobe FROM probe WHERE isenabled='1' AND isadmin='0'");
echo "\n<table border='0'>";
while ($rp = $qp->fetch_array()) {
	$q = $db->query("SELECT target.idtarget,target.description,target.url,target.visited,target.visited_before,
	                 targetdata.http_code,targetdata.html_title,
									 probe.probe
									 FROM target 
									 JOIN targetdata ON target.idtarget=targetdata.idtarget
									 LEFT JOIN probe ON target.nextprobe=probe.idprobe
									 WHERE target.nextprobe='$rp[idprobe]'
									 ORDER BY target.visited
									 LIMIT 3");
	while ($r = $q->fetch_array()) {
		$bg = $b2->bgcolor();
		echo "\n<tr $bg>";
		echo "<td $bg align='left'><a href='ana_targetedit.php?idtarget=$r[idtarget]' target='_blank'>$r[description]</a></td>";
		echo "<td $bg align='right'>$r[http_code]</td>";
		echo "<td $bg align='left'>" . substr($r['html_title'], 0, 50) . "</td>";
		echo "<td $bg align='left'>" . tempopassato(time() - $r['visited'], true) . "</td>";
		echo "<td $bg align='left'>$r[probe]</td>";
			echo "</tr>";
	}
}
echo "\n</table></td>";  //prossime per sonde

echo "\n</tr></table>"; // tabellona

echo "\n<p>&nbsp;</p>";

piede();

// ### END OF FILE ###