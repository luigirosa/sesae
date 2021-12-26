<?php

/**
 * SESAE - Manutenzione giornaliera
 *
 * @package     SESAE
 * @subpackage  SESAE Cron
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2018-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 *
 * 20181230 prima versione
 * 20190121 spostamento su set.sesae.com
 * 20200125 pulizia di mx_stat orfani
 * 20211204 cambio licenza per pubblicazione sorgenti
 * 20211224 buttato via quasi tutto e ripartito daccapo
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

// cancello la cache degli IPv4 (dopo)
//getcoutryipv4('1.1.1.1', true);

//
// RRD valori globali
//
// numerositi
$r = $db->query("SELECT COUNT(*) FROM target WHERE enabled='1'")->fetch_array();
rrdaddvalue(0, 'numerositi', $r[0], true);
// numerositihttps
$r = $db->query("SELECT COUNT(*)
                 FROM target 
                 JOIN targetdata ON targetdata.idtarget=target.idtarget
                 WHERE targetdata.ishttps='1'")->fetch_array();
rrdaddvalue(0, 'numerositihttps', $r[0], true);
// numerositiipv6
$r = $db->query("SELECT COUNT(*)
                 FROM target 
                 JOIN targetdata ON targetdata.idtarget=target.idtarget
                 WHERE targetdata.ipv6<>''")->fetch_array();
rrdaddvalue(0, 'numerositiipv6', $r[0], true);
// ipv4univoci
$r = $db->query("SELECT COUNT(DISTINCT ipv4) FROM targetdata")->fetch_array();
rrdaddvalue(0, 'ipv4univoci', $r[0], true);
// conframe
$r = $db->query("SELECT COUNT(*)
                 FROM target 
                 JOIN http_html ON http_html.idtarget=target.idtarget
                 WHERE http_html.http_html LIKE '%<iframe%' OR http_html.http_html LIKE '%<frameset%'")->fetch_array();
rrdaddvalue(0, 'conframe', $r[0], true);

die();
//
// RRD categorie
//
$qq = $db->query("SELECT idcategory FROM category");
while ($rr = $qq->fetch_array()) {
	// numerositi
	$r = $db->query("SELECT COUNT(*) FROM target WHERE enabled='1' AND idcategory='$rr[idcategory]'")->fetch_array();
	rrdaddvalue($rr['idcategory'], 'numerositi', $r[0], true);
	// numerositihttps
	$r = $db->query("SELECT COUNT(*)
	                 FROM target 
	                 JOIN targetdata ON targetdata.idtarget=target.idtarget
	                 WHERE targetdata.ishttps='1' AND target.idcategory='$rr[idcategory]'")->fetch_array();
	rrdaddvalue($rr['idcategory'], 'numerositihttps', $r[0], true);
	// numerositiipv6
	$r = $db->query("SELECT COUNT(*)
	                 FROM target 
	                 JOIN targetdata ON targetdata.idtarget=target.idtarget
	                 WHERE targetdata.ipv6<>'' AND target.idcategory='$rr[idcategory]'")->fetch_array();
	rrdaddvalue($rr['idcategory'], 'numerositiipv6', $r[0], true);
	// ipv4univoci
	$r = $db->query("SELECT COUNT(DISTINCT ipv4) 
	                 FROM targetdata 
	                 JOIN target ON targetdata.idtarget=target.idtarget
	                 WHERE target.idcategory='$rr[idcategory]'")->fetch_array();
	rrdaddvalue($rr['idcategory'], 'ipv4univoci', $r[0], true);
	// conframe
	$r = $db->query("SELECT COUNT(*)
	                 FROM target 
	                 JOIN http_html ON http_html.idtarget=target.idtarget
	                 WHERE target.idcategory='$rr[idcategory]' AND (http_html.http_html LIKE '%<iframe%' OR http_html.http_html LIKE '%<frameset%')")->fetch_array();
	rrdaddvalue($rr['idcategory'], 'conframe', $r[0], true);
}


// ### END OF FILE ###