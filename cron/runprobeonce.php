<?php

/**
 * SESAE - Probe, one ping only
 *
 * @package     SESAE
 * @subpackage  SESAE Cron
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2018-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 *
 * 20181007 prima versione
 * 20181223 random sleep
 * 20190121 spostamento su set.sesae.com
 * 20200125 aggiunto scan SMTP
 * 20211204 cambio licenza per pubblicazione sorgenti
 * 20211206 modifica gestione MX
 * 20211210 prende ID forzato da command line
 * 20211226 merge admin+public e ristrutturazione albero directory
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
 
define('SESAE', TRUE);
define('SKIPCHECK', TRUE);

require('../admin/global.php');

echo "\nID del sito di scansione: " . $aSetup['site']['id'];

if (!isset($argv[1])) {
	// scan SMTP, lo faccio prima per aumentare l'entropia e limitare le collisioni
	$r = $db->query("SELECT idmxserver FROM mxserver ORDER BY visited LIMIT 1")->fetch_array();
	echo "\n" . scantargetmx($r['idmxserver'], $aSetup['site']['id']);	
}

if (isset($argv[1]) and is_numeric($argv[1])) {
	$r = array();
	$r['idtarget'] = $argv[1];
	echo "\nIDtarget forzato da command line.";
} else {
	//$r = $db->query("SELECT target.idtarget,target.visited
	//                 FROM target
	//                 LEFT JOIN targetprobe ON target.idtarget=targetprobe.idtarget
	//                 WHERE target.enabled='1' AND targetprobe.idprobe='" . $aSetup['site']['id'] . "'
	//                 ORDER BY target.visited 
	//                 LIMIT 1")->fetch_array();
	$r = $db->query("SELECT target.idtarget,target.visited
	                 FROM target
	                 WHERE target.nextprobe='" . $aSetup['site']['id'] . "'
	                 ORDER BY target.visited 
	                 LIMIT 1")->fetch_array();
}


echo "\nIDtarget: $r[idtarget]\n";
echo "\n" . scantarget($r['idtarget'], $aSetup['site']['id']) . "\n";

// ### END OF FILE ###