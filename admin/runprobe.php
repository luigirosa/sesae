<?php

/**
 * SESAE - Probe
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2018-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * 
 * 20180217 prima versione
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
 
define('SESAE', TRUE);
define('SKIPCHECK', TRUE);

require('global.php');
	
//pulizia cache 
getcoutryipv4('127.0.0.1', true);
	
if (isset($argv[1]) and "ripescaggio" == $argv[1]) {
	$q = $db->query("SELECT target.idtarget FROM target LEFT JOIN targetdata ON target.idtarget=targetdata.idtarget WHERE (http_code<200 OR http_code>=500)  AND NOT (hostname='' OR ISNULL(hostname)) ");
} else {
	$q = $db->query("SELECT target.idtarget
	                 FROM target
	                 WHERE target.enabled='1'
	                 ORDER BY RAND()");
}

while ($r = $q->fetch_array()) {
	echo scantarget($r['idtarget']);
}

// ### END OF FILE ###
