<?php

/**
 *
 * SESAE - Admin - Elenco host per filtro campo
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2018-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * 
 * 20180217 prima versione
 * 20181007 campo visited
 * 20211204 cambio licenza per pubblicazione sorgenti
 * 20220116 rimosso 3xx inutile
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

if (isset($_GET['type'])) {
	$filtro = '';
	$confiltro = false;
	switch ($_GET['type']) {
		case 'm200':  // http < 200
			$filtro = "targetdata.http_code<200";
			$intestazione = "HTTP < 200";
		break;
		case '3xx':  // http 3xx
			$filtro = "(targetdata.http_code>=200 AND targetdata.http_code<=399)";
			$intestazione = "HTTP 3xx";
			$confiltro = true;
		break;
		case '4xx':  // http 4xx
			$filtro = "(targetdata.http_code>=400 AND targetdata.http_code<=499)";
			$intestazione = "HTTP 4xx";
		break;
		case '5xx':  // http 5xx
			$filtro = "(targetdata.http_code>=500 AND targetdata.http_code<=599)";
			$intestazione = "HTTP 5xx";
		break;
		case 'enoresolve':  // http 5xx
			$filtro = "targetdata.http_code=" . HTTP_ENORESOLVE;
			$intestazione = "Impossibile risolvere il nome";
		break;
	}
	if ('' == $filtro) {
		header('Location: home.php');
		die();
	}
} else {
		header('Location: home.php');
		die();
}

intestazione('Anagrafica target');

$quante = 0;
	
$q = $db->query("SELECT target.idtarget,target.description,target.url,target.enabled,target.visited,
                        targetdata.http_code,targetdata.http_location,targetdata.html_title
                 FROM target
                 JOIN targetdata ON target.idtarget=targetdata.idtarget
                 WHERE $filtro
                 ORDER BY target.visited DESC");

if ($q->num_rows > 0) {
	echo "\n<table border='0' align='center'>";
	echo $b2->intestazioneTabella(array('Visited', 'Target', 'Title', 'URL/Location', ' '));
	while ($r = $q->fetch_array()) {
		$display = true;
		if ($confiltro) {
			$url = substr(trim($r['url']), 0, -1); // tolgo lo slash finale perche' alcune ridirezioni non hanno lo slash finale
			$urlnos = str_replace('https://', 'http://', $url);
			$loc = strtolower(trim($r['http_location']));
			// se nel redirect c'e` tutto l'URL
			if (strpos($loc, $r['url']) === true) $display = false;
			// se il redirect contiene tutto l' url, non visualizzo
			if (stripos($loc, $url) !== false) $display = false;
			// se il redirect non inizia con http non visualizzo
			if (substr($loc, 0, 4) != 'http') $display = false;
			// se ridirige verso halleyweb, non visualizzo
			if (stripos($loc, 'halley')) $display = false;
			// se ridirige verso nuke, non visualizzo
			if (stripos($loc, 'nuke.')) $display = false;
			// se ridirige verso lnx, non visualizzo
			if (stripos($loc, 'lnx.')) $display = false;
			// se ridirige verso wordpress, non visualizzo
			if (stripos($loc, 'wordpress.')) $display = false;
			// se ridirige verso web, non visualizzo
			if (stripos($loc, 'web.')) $display = false;
			// se ridirige verso portale, non visualizzo
			if (stripos($loc, 'portale.')) $display = false;
			// se ridirige verso home.html, non visualizzo
			if ($loc == ($urlnos . 'home.html')) $display = false;
			// se ridirige verso comune.html, non visualizzo
			if ($loc == ($urlnos . 'comune.html')) $display = false;
			// se ridirige verso agrosoft, non visualizzo
			if (stripos($loc, 'argosoft.cloud')) $display = false;
			// se ridirige verso google, non visualizzo
			if (stripos($loc, 'sites.google')) $display = false;
			// se ridirige verso Studio K, non visualizzo
			if (stripos($loc, 'studiok')) $display = false;
			// se ridirige verso wix, non visualizzo
			if (stripos($loc, 'wix.com')) $display = false;
		}
		if ($display) {
			$match = get_longest_common_subsequence($r['url'], $r['http_location']);
			$bg = $b2->bgcolor();
			$abilitato = '1' == $r['enabled'] ? '' : ' (disabilitato)';
			$isurls = !(strpos($r['url'], 'https://') === 0);
			$isurll = !(strpos($r['http_location'], 'https://') === 0);
			$display = $isurls & !$isurll ? '>>>' : '';
			echo "\n<tr $bg>";
			echo "<td $bg align='right'><a href='ana_targetedit.php?idtarget=$r[idtarget]' target='_blank'>" . date("j/n H:i", $r['visited']) . "</a>&nbsp;</td>";
			echo "<td $bg align='left'><a href='ana_targetedit.php?idtarget=$r[idtarget]' target='_blank'>$r[description]$abilitato<br />$r[html_title]</a>&nbsp;</td>";
			echo "<td $bg align='center'>$display</td>";
			echo "<td $bg align='left'><a href='ana_targetedit.php?idtarget=$r[idtarget]' target='_blank'>" . str_replace($match, "<b>$match</b>", $r['url']) . "<br/>" . str_replace($match, "<b>$match</b>", $r['http_location']) . "</a>&nbsp;</td>";
			echo "</tr>";
			$quante++;
		}
	}
	echo "\n</table>";
	echo "<div>$quante righe</div>";
} else {
	echo "\n<b>Nessun target corrisponde alla ricerca indicata.</b>";
}

echo "\n<p>&nbsp;</p>";
echo "\n<p>&nbsp;</p>";
echo "\n<p>&nbsp;</p>";

piede();

// ### END OF FILE ###