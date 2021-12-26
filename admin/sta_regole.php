<?php

/**
 *
 * SESAE - Admin - Statistiche, anagrafica regole
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
$gruppo = "GNR";   // tipo di gruppo di default
if (isset($_POST['gruppo'])) $gruppo = $b2->normalizza($_POST['gruppo']);

$agruppi = array();
$q = $db->query("SELECT statisticacampogruppo,statisticacampogruppodesc FROM statisticacampogruppo ORDER BY statisticacampogruppo");
while ($r = $q->fetch_array()) {
	$agruppi[$r['statisticacampogruppo']] = $r['statisticacampogruppodesc'];
}

if (isset($_POST['p'])) {
	foreach ($_POST['p'] as $idstatisticacampo=>$x) {
		if (isset($x['xxx'])) {
			$db->query("DELETE FROM statisticacampo WHERE idstatisticacampo='$idstatisticacampo'");
		} else {
			$a = array();
			$a[] = $b2->campoSql('gruppo', $gruppo);
			$a[] = $b2->campoSql('algoritmo', $x['algoritmo']);
			$a[] = $b2->campoSql('regola', $x['regola']);
			$a[] = $b2->campoSql('statistica', $x['statistica']);
			$a[] = $b2->campoSql('statistica_fam', $x['statistica_fam']);
			if ((0 == $idstatisticacampo) and ('' != trim($x['regola']))) {
				$db->query("INSERT INTO statisticacampo SET " . implode(',', $a));
			} else {
				if ($idstatisticacampo > 0 and '' != trim($x['regola'])) {
					$db->query("UPDATE statisticacampo SET " . implode(',', $a) . " WHERE idstatisticacampo='$idstatisticacampo'");
				}
			}
		}
	}
}

intestazione("Statistiche: regole");

// per contare i record
switch ($gruppo) {
	case 'GNR':
		$qry = "SELECT http_generator AS a FROM http_generator";
	break;
	case 'MX':
		$qry = "SELECT mxserver_real AS a FROM mxserver";
	break;
	case 'DNS':
		$qry = "SELECT dnsauth AS a FROM dnsauth";
	break;
	case 'HTS':
		$qry = "SELECT http_server AS a FROM http_server";
	break;
}

$q = $db->query("SELECT *  
                 FROM statisticacampo
                 WHERE `gruppo`='$gruppo'
                 ORDER BY statistica");
echo "\n<form method='post' action='sta_regole.php'>";
echo "\n<div>Gruppo: " . $b2->inputSelect('gruppo', $agruppi, $gruppo) . " <input type='submit' value='Cambia gruppo'/></div>";
echo "\n</form>";

echo "\n<form method='post' action='sta_regole.php'>";
echo $b2->inputHidden("gruppo", $gruppo);

echo "\n<div align='center'><input type='submit' value='Aggiorna'/></div>";

echo "\n<table border='0'>"; 
// nuovo record
$id = 0;
$bg = $b2->bgcolor();
echo "\n<tr $bg>";
echo "\n<td $bg>" . $b2->inputSelect("p[$id][algoritmo]", $aAlgoStat) . "</td>";
echo "\n<td $bg>" . $b2->inputText("p[$id][regola]", '', 40, 100) . "</td>";
echo "\n<td $bg>" . $b2->inputText("p[$id][statistica]", '', 40, 100) . "</td>";
echo "\n<td $bg colspan='2'>" . $b2->inputText("p[$id][statistica_fam]",'', 30, 100) . "</td>";
echo "\n<td $bg>New</td>";

while ($r = $q->fetch_array()) {
	// conto quante sono usate
	$conta = 0;
	$qq = $db->query($qry);
	while ($rr = $qq->fetch_array()) {
		switch ($r['algoritmo']) {
			case 'RPL':  // rimpiazza secco
				if ($rr['a'] == $r['regola']) {
					$conta++;
					//break 2;  /* Exit the switch and the while. */
				}
			break;
			case 'RGX':  // regular expression
				if (preg_match($r['regola'], $rr['a'])) {
					$conta++;
					//break 2;  /* Exit the switch and the while. */
				}
			break;
		}
	}
	$bg = $b2->bgcolor();
	$id = $r['idstatisticacampo'];
	echo "\n<tr $bg>";
	echo "\n<td $bg>" . $b2->inputSelect("p[$id][algoritmo]", $aAlgoStat, $r['algoritmo']) . "</td>";
	echo "\n<td $bg>" . $b2->inputText("p[$id][regola]", $r['regola'], 40, 100) . "</td>";
	echo "\n<td $bg>" . $b2->inputText("p[$id][statistica]", $r['statistica'], 40, 100) . "</td>";
	echo "\n<td $bg>" . $b2->inputText("p[$id][statistica_fam]", $r['statistica_fam'], 30, 100) . "</td>";
	echo "\n<td $bg align='right'>$conta</td>";
	echo "\n<td $bg>" . $b2->inputCheck("p[$id][xxx]", false) . "</td>";
	echo "\n</tr>";  
}
echo "\n</table>";  
echo "\n<div align='center'><input type='submit' value='Aggiorna'/></div>";
echo "\n</form>";

piede();

// ### END OF FILE ###