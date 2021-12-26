<?php

/**
 *
 * SESAE - Admin - Statistiche, record non regolati
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2021-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * 
 * 20211205 prima versione
 * 20211206 modifica gestione MX
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

// gruppo da visualizzare
$gruppo = "HTS";   // tipo di gruppo di default
if (isset($_POST['gruppo'])) $gruppo = $b2->normalizza($_POST['gruppo']);

$agruppi = array();
$q = $db->query("SELECT statisticacampogruppo,statisticacampogruppodesc FROM statisticacampogruppo ORDER BY statisticacampogruppo");
while ($r = $q->fetch_array()) {
	$agruppi[$r['statisticacampogruppo']] = $r['statisticacampogruppodesc'];
}




if (isset($_POST['p'])) {
	foreach ($_POST['p'] as $id=>$x) {
		if ('' != trim($x['regola'])) {
			// aggiungo la regola
			$a = array();
			$a[] = $b2->campoSQL("gruppo", $gruppo);
			$a[] = $b2->campoSQL("algoritmo", $x['algoritmo']);
			$a[] = $b2->campoSQL("regola", $x['regola']);
			$a[] = $b2->campoSQL("statistica", $x['statistica']);
			if (isset($x['statistica_fam'])) $a[] = $b2->campoSQL("statistica_fam", $x['statistica_fam']);
			$db->query("INSERT INTO statisticacampo SET " . implode(',', $a));
		}
	}
}

intestazione("Record non ancora regolati");

switch ($gruppo) {
	case 'GNR':
		$qry01 = "SELECT idhttp_generator AS id,http_generator AS daregolare,http_generator_stat,COUNT(http_generator_stat) AS c 
		          FROM http_generator 
		          GROUP BY http_generator 
		          ORDER BY http_generator";
		$secondocampo = true;
	break;
	case 'MX':
		$qry01 = "SELECT idmxserver AS id,mxserver AS daregolare,mxserver_stat,COUNT(mxserver_stat) AS c 
		          FROM mxserver 
		          GROUP BY mxserver 
		          ORDER BY REVERSE(mxserver)";
		$secondocampo = false;
	break;
	case 'DNS':
		$qry01 = "SELECT iddnsauth AS id,dnsauth AS daregolare,dnsauth_stat,COUNT(dnsauth_stat) AS c 
		          FROM dnsauth 
		          GROUP BY dnsauth 
		          ORDER BY REVERSE(dnsauth)";
		$secondocampo = false;
	break;
	case 'HTS':
		$qry01 = "SELECT idhttp_server AS id,http_server AS daregolare,http_server_stat,COUNT(http_server_stat) AS c 
		          FROM http_server 
		          GROUP BY http_server 
		          ORDER BY http_server";
		$secondocampo = true;
	break;
}

echo "\n<form method='post' action='sta_nonregolati.php'>";
echo "\n<div>Gruppo: " . $b2->inputSelect('gruppo', $agruppi, $gruppo) . " <input type='submit' value='Cambia gruppo'/></div>";
echo "\n</form>";


echo "\n<form method='post' action='sta_nonregolati.php'>";
echo $b2->inputHidden("gruppo", $gruppo);

echo "\n<div align='center'><input type='submit' value='Aggiorna'/></div>";

$q = $db->query($qry01);

echo "\n<table border='0'>";
while ($r = $q->fetch_array()) {
	$s = array();
	$s = statisticacampo($r['daregolare'], $gruppo);
	if (!$s['trovato']) {
		$bg = $b2->bgcolor();
		$id = $r['id'];
		echo "\n<tr $bg>";
		echo "\n<td align='right' $bg>$r[c]&nbsp;</td>";
		echo "\n<td $bg>$r[daregolare]</td>";
		echo "\n<td $bg>" . $b2->inputSelect("p[$id][algoritmo]", $aAlgoStat) . "</td>";
		echo "\n<td $bg>" . $b2->inputText("p[$id][regola]", '', 40, 100) . "</td>";
		echo "\n<td $bg>" . $b2->inputText("p[$id][statistica]", '', 40, 100) . "</td>";
		if ($secondocampo) echo "\n<td $bg>" . $b2->inputText("p[$id][statistica_fam]", '', 30, 100) . "</td>";
		echo "\n</tr>";  
	}
}
echo "\n</table>";  
echo "\n<div align='center'><input type='submit' value='Aggiorna'/></div>";

echo "\n</form>";

piede();

// ### END OF FILE ###