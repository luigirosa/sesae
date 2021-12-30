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
 * 20211230 rimosso RRD e storicizzazione su SQL
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
getipgeo('clearcache');

//
// Storicizzazione
//
$today = date('Y-m-d');

// si fa pulizia nel caso di esecuzioni multiple lo stesso giorno
$db->query("DELETE FROM storico WHERE `data`='$today'");

// Storicizzazione valori generali

// numerositi
$r = $db->query("SELECT COUNT(*) FROM target WHERE enabled='1'")->fetch_array();
storicizza($today, 0, ST_GEN_NUMEROSITI, $r[0]);
// numerositihttps
$r = $db->query("SELECT COUNT(*)
                 FROM target 
                 JOIN targetdata ON targetdata.idtarget=target.idtarget
                 WHERE targetdata.ishttps='1'")->fetch_array();
storicizza($today, 0, ST_GEN_INHTTPS, $r[0]);
// numerositiipv6
$r = $db->query("SELECT COUNT(*)
                 FROM target 
                 JOIN targetdata ON targetdata.idtarget=target.idtarget
                 WHERE targetdata.ipv6<>''")->fetch_array();
storicizza($today, 0, ST_GEN_CONIPV6, $r[0]);
// ipv4univoci
$r = $db->query("SELECT COUNT(DISTINCT ipv4) FROM targetdata")->fetch_array();
storicizza($today, 0, ST_GEN_IPV4UNIVOCI, $r[0]);
// conframe
$r = $db->query("SELECT COUNT(*)
                 FROM target 
                 JOIN http_html ON http_html.idtarget=target.idtarget
                 WHERE http_html.http_html LIKE '%<iframe%' OR http_html.http_html LIKE '%<frameset%'")->fetch_array();
storicizza($today, 0, ST_GEN_CONFRAME, $r[0]);
// http server
$r = $db->query("SELECT limiteminimo FROM campostorico WHERE idcampostorico=" . ST_HTTPSERVER)->fetch_array();
$limiteminimo = $r[0];
$q = $db->query("SELECT COUNT(*) AS c,http_server_stat_fam 
                 FROM http_server  
                 GROUP BY http_server_stat_fam
                 HAVING c>=$limiteminimo");
while ($r = $q->fetch_array()) {
	storicizza($today, 0, ST_HTTPSERVER, $r['c'], $r['http_server_stat_fam']);
}
// generator
$r = $db->query("SELECT limiteminimo FROM campostorico WHERE idcampostorico=" . ST_GENERATOR)->fetch_array();
$limiteminimo = $r[0];
$q = $db->query("SELECT COUNT(*) AS c,http_generator_stat_fam 
				         FROM http_generator  
				         GROUP BY http_generator_stat_fam 
                 HAVING c>=$limiteminimo");
while ($r = $q->fetch_array()) {
	storicizza($today, 0, ST_GENERATOR, $r['c'], $r['http_generator_stat_fam']);
}
// DNS
$r = $db->query("SELECT limiteminimo FROM campostorico WHERE idcampostorico=" . ST_DNS)->fetch_array();
$limiteminimo = $r[0];
$q = $db->query("SELECT COUNT(*) AS c,dnsauth_stat
				         FROM dnsauth
				         GROUP BY dnsauth_stat
                 HAVING c>=$limiteminimo");
while ($r = $q->fetch_array()) {
	storicizza($today, 0, ST_DNS, $r['c'], $r['dnsauth_stat']);
}
// MX
$r = $db->query("SELECT limiteminimo FROM campostorico WHERE idcampostorico=" . ST_MX)->fetch_array();
$limiteminimo = $r[0];
$q = $db->query("SELECT COUNT(*) AS c,mxserver.mxserver_stat
                 FROM targetmx
                 JOIN mxserver ON targetmx.idmxserver=mxserver.idmxserver
                 GROUP BY mxserver.mxserver_stat
                 HAVING c>=$limiteminimo");
while ($r = $q->fetch_array()) {
	storicizza($today, 0, ST_MX, $r['c'], $r['mxserver_stat']);
}
// ContentType
$r = $db->query("SELECT limiteminimo FROM campostorico WHERE idcampostorico=" . ST_CONTENTTYPE)->fetch_array();
$limiteminimo = $r[0];
$q = $db->query("SELECT http_contenttype,COUNT(http_contenttype) AS c 
                 FROM targetdata 
                 WHERE http_contenttype<>''
                 GROUP BY http_contenttype 
                 HAVING c>=$limiteminimo");
while ($r = $q->fetch_array()) {
	storicizza($today, 0, ST_CONTENTTYPE, $r['c'], $r['http_contenttype']);
}
// hash del certificato SSL
$r = $db->query("SELECT limiteminimo FROM campostorico WHERE idcampostorico=" . ST_SSLHASH)->fetch_array();
$limiteminimo = $r[0];
$q = $db->query("SELECT https_signature,COUNT(https_signature) AS c 
                 FROM targetdata 
                 WHERE https_signature<>''
                 GROUP BY https_signature 
                 HAVING c>=$limiteminimo");
while ($r = $q->fetch_array()) {
	storicizza($today, 0, ST_SSLHASH, $r['c'], $r['https_signature']);
}
// emettitore del certificato SSL
$r = $db->query("SELECT limiteminimo FROM campostorico WHERE idcampostorico=" . ST_SSLISSUER)->fetch_array();
$limiteminimo = $r[0];
$q = $db->query("SELECT https_issuerorg,COUNT(https_issuerorg) AS c 
                 FROM targetdata 
                 WHERE https_issuerorg<>'' 
                 GROUP BY https_issuerorg 
                 HAVING c>=$limiteminimo");
while ($r = $q->fetch_array()) {
	storicizza($today, 0, ST_SSLISSUER, $r['c'], $r['https_issuerorg']);
}



// Storicizzazione categorie
$qq = $db->query("SELECT idcategory FROM category");
while ($rr = $qq->fetch_array()) {
	// numerositi
	$r = $db->query("SELECT COUNT(*) FROM target WHERE enabled='1' AND idcategory='$rr[idcategory]'")->fetch_array();
	storicizza($today, $rr['idcategory'], ST_GEN_NUMEROSITI, $r[0]);
	// numerositihttps
	$r = $db->query("SELECT COUNT(*)
	                 FROM target 
	                 JOIN targetdata ON targetdata.idtarget=target.idtarget
	                 WHERE targetdata.ishttps='1' AND target.idcategory='$rr[idcategory]'")->fetch_array();
	storicizza($today, $rr['idcategory'], ST_GEN_INHTTPS, $r[0]);
	// numerositiipv6
	$r = $db->query("SELECT COUNT(*)
	                 FROM target 
	                 JOIN targetdata ON targetdata.idtarget=target.idtarget
	                 WHERE targetdata.ipv6<>'' AND target.idcategory='$rr[idcategory]'")->fetch_array();
	storicizza($today, $rr['idcategory'], ST_GEN_CONIPV6, $r[0]);
	// ipv4univoci
	$r = $db->query("SELECT COUNT(DISTINCT ipv4) 
	                 FROM targetdata 
	                 JOIN target ON targetdata.idtarget=target.idtarget
	                 WHERE target.idcategory='$rr[idcategory]'")->fetch_array();
	storicizza($today, $rr['idcategory'], ST_GEN_IPV4UNIVOCI, $r[0]);
	// conframe
	$r = $db->query("SELECT COUNT(*)
	                 FROM target 
	                 JOIN http_html ON http_html.idtarget=target.idtarget
	                 WHERE target.idcategory='$rr[idcategory]' AND (http_html.http_html LIKE '%<iframe%' OR http_html.http_html LIKE '%<frameset%')")->fetch_array();
	storicizza($today, $rr['idcategory'], ST_GEN_CONFRAME, $r[0]);
	// http server
	$r = $db->query("SELECT limiteminimo FROM campostorico WHERE idcampostorico=" . ST_HTTPSERVER)->fetch_array();
	$limiteminimo = $r[0];
	$q = $db->query("SELECT COUNT(*) AS c,http_server_stat_fam 
	                 FROM http_server  
	                 JOIN target ON http_server.idtarget=target.idtarget
	                 WHERE target.idcategory='$rr[idcategory]'
	                 GROUP BY http_server_stat_fam
	                 HAVING c>=$limiteminimo");
	while ($r = $q->fetch_array()) {
		storicizza($today, 0, ST_HTTPSERVER, $r['c'], $r['http_server_stat_fam']);
	}
	// generator
	$r = $db->query("SELECT limiteminimo FROM campostorico WHERE idcampostorico=" . ST_GENERATOR)->fetch_array();
	$limiteminimo = $r[0];
	$q = $db->query("SELECT COUNT(*) AS c,http_generator_stat_fam 
	                 FROM http_generator  
	                 JOIN target ON http_generator.idtarget=target.idtarget
	                 WHERE target.idcategory='$rr[idcategory]'
	                 GROUP BY http_generator_stat_fam 
	                 HAVING c>=$limiteminimo");
	while ($r = $q->fetch_array()) {
		storicizza($today, 0, ST_GENERATOR, $r['c'], $r['http_generator_stat_fam']);
	}
	// DNS
	$r = $db->query("SELECT limiteminimo FROM campostorico WHERE idcampostorico=" . ST_DNS)->fetch_array();
	$limiteminimo = $r[0];
	$q = $db->query("SELECT COUNT(*) AS c,dnsauth_stat
	                 FROM dnsauth
	                 JOIN target ON dnsauth.idtarget=target.idtarget
	                 WHERE target.idcategory='$rr[idcategory]'
	                 GROUP BY dnsauth_stat
	                 HAVING c>=$limiteminimo");
	while ($r = $q->fetch_array()) {
		storicizza($today, 0, ST_DNS, $r['c'], $r['dnsauth_stat']);
	}
	// MX
	$r = $db->query("SELECT limiteminimo FROM campostorico WHERE idcampostorico=" . ST_MX)->fetch_array();
	$limiteminimo = $r[0];
	$q = $db->query("SELECT COUNT(*) AS c,mxserver.mxserver_stat
	                 FROM targetmx
	                 JOIN target ON targetmx.idtarget=target.idtarget
	                 JOIN mxserver ON targetmx.idmxserver=mxserver.idmxserver
	                 WHERE target.idcategory='$rr[idcategory]'
	                 GROUP BY mxserver.mxserver_stat
	                 HAVING c>=$limiteminimo");
	while ($r = $q->fetch_array()) {
		storicizza($today, 0, ST_MX, $r['c'], $r['mxserver_stat']);
	}
	// ContentType
	$r = $db->query("SELECT limiteminimo FROM campostorico WHERE idcampostorico=" . ST_CONTENTTYPE)->fetch_array();
	$limiteminimo = $r[0];
	$q = $db->query("SELECT http_contenttype,COUNT(http_contenttype) AS c 
	                 FROM targetdata 
	                 JOIN target ON targetdata.idtarget=target.idtarget
	                 WHERE target.idcategory='$rr[idcategory]' AND http_contenttype<>'' 
	                 GROUP BY http_contenttype 
	                 HAVING c>=$limiteminimo");
	while ($r = $q->fetch_array()) {
		storicizza($today, 0, ST_CONTENTTYPE, $r['c'], $r['http_contenttype']);
	}
	// hash del certificato SSL
	$r = $db->query("SELECT limiteminimo FROM campostorico WHERE idcampostorico=" . ST_SSLHASH)->fetch_array();
	$limiteminimo = $r[0];
	$q = $db->query("SELECT https_signature,COUNT(https_signature) AS c 
	                 FROM targetdata 
	                 JOIN target ON targetdata.idtarget=target.idtarget
	                 WHERE target.idcategory='$rr[idcategory]' AND https_signature<>''
	                 GROUP BY https_signature 
	                 HAVING c>=$limiteminimo");
	while ($r = $q->fetch_array()) {
		storicizza($today, 0, ST_SSLHASH, $r['c'], $r['https_signature']);
	}
	// emettitore del certificato SSL
	$r = $db->query("SELECT limiteminimo FROM campostorico WHERE idcampostorico=" . ST_SSLISSUER)->fetch_array();
	$limiteminimo = $r[0];
	$q = $db->query("SELECT https_issuerorg,COUNT(https_issuerorg) AS c 
	                 FROM targetdata 
	                 JOIN target ON targetdata.idtarget=target.idtarget
	                 WHERE target.idcategory='$rr[idcategory]' AND https_issuerorg<>''
	                 GROUP BY https_issuerorg 
	                 HAVING c>=$limiteminimo");
	while ($r = $q->fetch_array()) {
		storicizza($today, 0, ST_SSLISSUER, $r['c'], $r['https_issuerorg']);
	}
}


// ### END OF FILE ###
