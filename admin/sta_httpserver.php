<?php

/**
 *
 * SESAE - Admin - Statistiche, filtri HTTP Server
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2020-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * 
 * 20200113 prima versione
 * 20200423 rimozione _fam
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
$tf = "HTS";   // tipo di filtro da applicare alla tabella statisticacampo

$filtro = 'a%';
if (isset($_POST['filtro'])) {
	$filtro = trim($_POST['filtro']);
	if ('' == $filtro) {
		$filtro = 'a%';
	}
}

if (isset($_POST['filtro'])) {
	foreach ($_POST['p'] as $idhttp_server=>$x) {
		$rr = $db->query("SELECT http_server FROM http_server WHERE idhttp_server='$idhttp_server'")->fetch_array();
		if ('' != trim($x['newreplace'])) {
			// aggiungo la regola
			$a = array();
			$a[] = $b2->campoSQL("gruppo", $tf);
			$a[] = $b2->campoSQL("algoritmo", $x['algoritmo']);
			$a[] = $b2->campoSQL("regola", $rr['http_server']);
			$a[] = $b2->campoSQL("statistica", $x['newreplace'], B2_NORM_SQL||B2_NORM_TRIM);
			$db->query("INSERT INTO statisticacampo SET " . implode(',', $a));
		}
	}
}

intestazione("Statistiche: HTTP Server");

$q = $db->query("SELECT idhttp_server,http_server,http_server_stat,COUNT(http_server_stat) AS c 
                 FROM http_server 
                 WHERE `http_server` COLLATE UTF8_GENERAL_CI LIKE '$filtro' 
                 GROUP BY http_server 
                 ORDER BY http_server");
echo "\n<form method='post' action='sta_httpserver.php'>";
echo "\n<div>Filtro: " . $b2->inputText('filtro', $filtro, 50, 50) . " <input type='submit' value='Aggiorna'/></div>";

echo "\n<table border='0'><tr>"; //tabellona
echo "<td valign='top'>";//tabellona

echo "\n<table border='0'>";
while ($r = $q->fetch_array()) {
	$s = array();
	$s = statisticacampo($r['http_server'], $tf);
	if (!$s['trovato']) {
		$bg = $b2->bgcolor();
		$id = $r['idhttp_server'];
		echo "\n<tr $bg>";
		echo "\n<td align='right' $bg>$r[c]&nbsp;</td>";
		echo "\n<td $bg>" . $b2->inputText("p[$id][http_server]", $r['http_server'], 20, 100) . "</td>";
		echo "\n<td $bg>$r[http_server_stat]&nbsp;</td>";
		echo "\n<td $bg>" . $b2->inputSelect("p[$id][algoritmo]", $aAlgoStat) . "</td>";
		echo "\n<td $bg>" . $b2->inputText("p[$id][newreplace]", '', 20, 100) . "</td>";
		echo "\n<td $bg>" . $b2->inputText("p[$id][newreplacefam]", '', 20, 100) . "</td>";
		echo "\n</tr>";  
	}
}
echo "\n</table>";  
echo "\n<input type='submit' value='Aggiorna'/>";

echo "\n</td><td valign='top'>"; //tabellona

echo "\n<table border='0'>";
$q = $db->query("SELECT * FROM statisticacampo WHERE gruppo='$tf' ORDER BY statistica");
	$bg = $b2->bgcolor(true);
while ($r = $q->fetch_array()) {
	$bg = $b2->bgcolor();
	echo "\n<tr $bg><td>$r[algoritmo]</td><td>$r[regola]</td><td>$r[statistica]</td></tr>";
}
echo "\n</table>";  

echo "\n</tr></table>"; //tabellona

echo "\n</form>";

piede();

// ### END OF FILE ###