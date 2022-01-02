<?php

/**
 *
 * SESAE - Definizione delle variabili e delle funzioni comuni 
 *
 * @package     SESAE
 * @subpackage  SESAE Web
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2019-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 *
 * 20190504 prima versione (vedere i commenti alle singole funzioni per i dettagli)
 * 20211204 cambio licenza per pubblicazione sorgenti
 * 20211206 aggiunta DNS, MX
 * 20211211 nocache, content type, hash ssl, organizzzione ssl
 * 20211226 merge admin+public e ristrutturazione albero directory
 * 20211230 rimosso RRD
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
 
//error_reporting ( E_ALL );
//ini_set ( "display_errors", 'on' );

if(!defined('SESAE')) {
	header ('Location: https://www.google.com');
	die();
}

// sessione
session_start();

// override di qls impostazione del server http
header("Content-Type: text/html; charset=UTF-8");

// sicurezza X-Content-Type-Options: nosniff
header("X-Frame-Options: deny");
header("Frame-Options: deny");
header("X-XSS-Protection: \"1; mode=block\"");
header("X-Content-Type-Options: nosniff");

// DB
$aSetup = parse_ini_file('setup.ini', true);
$db = new mysqli($aSetup['sql']['host'], $aSetup['sql']['user'], $aSetup['sql']['password'], $aSetup['sql']['database']);
if (mysqli_connect_errno()) {
	echo "<html><head><title>SESAE &egrave; in manutenzione</title><body><p>SESAE &egrave; in manutenzione, torneremo appena possibile.\n<!-- " . mysqli_connect_error() . " -->\n</p></body></html>";
	die();
}
$db->set_charset('UTF8');

// B2TOOLS
require('../inc_b2tools/b2tools.inc.php');
$b2 = new objB2($db, 'sys_campi');

// oggetti della cache
define('CH_STATGEN',        '001');   // statistiche generali
define('CH_GENERATOR',      '002');   // generator
define('CH_HTTPSERVER',     '003'); 	// http server
define('CH_DNS',            '004'); 	// DNS
define('CH_MX',             '005');	  // Mail Exchanger
define('CH_CONTTYPE',       '006');	  // html content type
define('CH_SSLHASH',        '007');	  // hash dei certificati ssl
define('CH_SSLISSUER',      '008');	  // organizzazione emettitrice del certificato SSL
define('CH_COUNTRYIPV4',    '009');	  // Country IPv4

function cache_dati($quale, $nocache = '') {
	global $b2,$db;
	$cachedir = './cache/';
	$cachettl = 666;
	$b = '';
	$cachefile = $cachedir . $quale;
	//se devo invalidare la cache
	if (file_exists($cachefile) and $nocache == 'nocache') unlink($cachefile);
	//se posso servire la cache
	if (file_exists($cachefile) and (time() - filemtime($cachefile)) < $cachettl ) {
		$b .= file_get_contents($cachefile)	;
	} else {
		list ($classe, $idcategory) = explode('-', $quale, 2);
		switch ($classe) {
			// statistiche generali
			case CH_STATGEN:
				$b .=   "\n<table border='0' align='center'>";
				if (0 == $idcategory) {
					$b .= "\n<tr><td align='center' colspan='3'><h2>Tutte le categorie</h2></td></tr>";
				} else {
					$r = $db->query("SELECT category FROM category WHERE idcategory='$idcategory'")->fetch_array();
					$b .= "\n<tr><td align='center' colspan='3'><h2>$r[category]</h2</td></tr>";
				}
				$wha = $idcategory == 0 ? '' : " AND target.idcategory='$idcategory'";
				$whw = $idcategory == 0 ? '' : " WHERE target.idcategory='$idcategory'";
				// quanti siti
				$r = $db->query("SELECT COUNT(*) FROM target WHERE enabled='1' $wha")->fetch_array();
				$tutti = $r[0];
				$b .= "\n<tr><td align='left'><b>Totale</b></td><td align='right'>". number_format($tutti, 0, ',', '.') . "</td><td>&nbsp;</td></tr>";
				//https
				$r = $db->query("SELECT COUNT(*)
                         FROM target 
	                       JOIN targetdata ON targetdata.idtarget=target.idtarget
		                     WHERE targetdata.ishttps='1' $wha")->fetch_array();
				$b .=   "\n<tr><td align='left'><b>HTTPS</b></td><td align='right'>". number_format($r[0], 0, ',', '.') . "</td><td align='right'>&nbsp;" . number_format(100 * $r[0] / $tutti, 2, ',', '.') . "%</td></tr>";
				//ipv6
				$r = $db->query("SELECT COUNT(*) 
				                 FROM target
				                 JOIN ip ON target.idipv6=ip.idip 
				                 $whw")->fetch_array();
				$b .=   "\n<tr><td align='left'><b>Con IPv6</b></td><td align='right'>". number_format($r[0], 0, ',', '.') . "</td><td align='right'>&nbsp;" . number_format(100 * $r[0] / $tutti, 2, ',', '.') . "%</td></tr>";
				// ipv4 univoci
				$r = $db->query("SELECT COUNT(DISTINCT ip.idip) 
				                 FROM target
				                 JOIN ip ON target.idipv4=ip.idip 
				                 $whw")->fetch_array();
				$b .=   "\n<tr><td align='left'><b>IPv4 univoci</b></td><td align='right'>". number_format($r[0], 0, ',', '.') . "</td><td align='right'>&nbsp;" . number_format(100 * $r[0] / $tutti, 2, ',', '.') . "%</td></tr>";
				// frame
				$r = $db->query("SELECT COUNT(*)
                         FROM target 
	                       JOIN http_html ON http_html.idtarget=target.idtarget
		                     WHERE (http_html.http_html LIKE '%<iframe%' OR http_html.http_html LIKE '%<frameset%') $wha")->fetch_array();
				$b .=   "\n<tr><td align='left'><b>Che usano frame</b></td><td align='right'>". number_format($r[0], 0, ',', '.') . "</td><td align='right'>&nbsp;" . number_format(100 * $r[0] / $tutti, 2, ',', '.') . "%</td></tr>";

				$b .=   "\n</table>";
				$b .= "\n<!-- CH_STATGEN "	. date("j/n/Y G:i:s") . " -->\n";
				file_put_contents($cachefile, $b);
			break;
			// generator
			case CH_GENERATOR:
				$wha = $idcategory == 0 ? '' : " AND target.idcategory='$idcategory'";
				$whw = $idcategory == 0 ? '' : " WHERE target.idcategory='$idcategory'";
				$t = $db->query("SELECT COUNT(*) FROM target $whw")->fetch_array();
				$b .= "\n<table border='0' align='center'>";
				$b .= "\n<tr><td align='center' colspan='3'><h2>Generator</h2></td></tr>";
				$q = $db->query("SELECT COUNT(*) AS c,http_generator_stat_fam 
				                 FROM http_generator  
				                 JOIN target ON http_generator.idtarget=target.idtarget
				                 $whw 
				                 GROUP BY http_generator_stat_fam 
				                 HAVING c>=9
				                 ORDER BY c DESC,http_generator_stat_fam");
				while ($r = $q->fetch_array()) {
					$b .= "\n<tr><td align='left' style='text-align: left;'>$r[http_generator_stat_fam]</td><td align='right' style='text-align: right;'>" . number_format($r['c'], 0, ',', '.') . "</td><td align='right' style='text-align: right;'>" . number_format(($r['c']*100/$t[0]), 2, ',', '.') . "%</td></tr>";
				}
				$b.= "\n</table>";
				$b .= "\n<!-- CH_GENERATOR "	. date("j/n/Y G:i:s") . " -->\n";
				file_put_contents($cachefile, $b);
			break;
			// http server
			case CH_HTTPSERVER:
				$wha = $idcategory == 0 ? '' : " AND target.idcategory='$idcategory'";
				$whw = $idcategory == 0 ? '' : " WHERE target.idcategory='$idcategory'";
				$t = $db->query("SELECT COUNT(*) FROM target $whw")->fetch_array();
				$b .= "\n<table border='0' align='center'>";
				$b .= "\n<tr><td align='center' colspan='3'><h2>Server HTTP</h2></td></tr>";
				$q = $db->query("SELECT COUNT(*) AS c,http_server_stat_fam 
				                 FROM http_server  
				                 JOIN target ON http_server.idtarget=target.idtarget
				                 $whw
				                 GROUP BY http_server_stat_fam 
				                 HAVING c>=9
				                 ORDER BY c DESC,http_server_stat_fam");
				while ($r = $q->fetch_array()) {
					$b .= "\n<tr><td align='left' style='text-align: left;'>$r[http_server_stat_fam]</td><td align='right' style='text-align: right;'>" . number_format($r['c'], 0, ',', '.') . "</td><td align='right' style='text-align: right;'>" . number_format(($r['c']*100/$t[0]), 2, ',', '.') . "%</td></tr>";
				}
				$b.= "\n</table>";
				$b .= "\n<!-- CH_HTTPSERVER "	. date("j/n/Y G:i:s") . " -->\n";
				file_put_contents($cachefile, $b);
			break;
			// dns server
			case CH_DNS:
				$wha = $idcategory == 0 ? '' : " AND target.idcategory='$idcategory'";
				$whw = $idcategory == 0 ? '' : " WHERE target.idcategory='$idcategory'";
				$t = $db->query("SELECT COUNT(*) FROM target $whw")->fetch_array();
				$b .= "\n<table border='0' align='center'>";
				$b .= "\n<tr><td align='center' colspan='3'><h2>Server DNS</h2></td></tr>";
				$q = $db->query("SELECT COUNT(*) AS c,dnsauth_stat
				                 FROM dnsauth
				                 JOIN target ON dnsauth.idtarget=target.idtarget
				                 $whw
				                 GROUP BY dnsauth_stat
				                 HAVING c>=9
				                 ORDER BY c DESC,dnsauth_stat");
				while ($r = $q->fetch_array()) {
					$b .= "\n<tr><td align='left' style='text-align: left;'>$r[dnsauth_stat]</td><td align='right' style='text-align: right;'>" . number_format($r['c'], 0, ',', '.') . "</td><td align='right' style='text-align: right;'>" . number_format(($r['c']*100/$t[0]), 2, ',', '.') . "%</td></tr>";
				}
				$b.= "\n</table>";
				$b .= "\n<!-- CH_DNS "	. date("j/n/Y G:i:s") . " -->\n";
				file_put_contents($cachefile, $b);
			break;
			// mail exchanger
			case CH_MX:
				$wha = $idcategory == 0 ? '' : " AND target.idcategory='$idcategory'";
				$whw = $idcategory == 0 ? '' : " WHERE target.idcategory='$idcategory'";
				$t = $db->query("SELECT COUNT(*) FROM target $whw")->fetch_array();
				$b .= "\n<table border='0' align='center'>";
				$b .= "\n<tr><td align='center' colspan='3'><h2>Server MX</h2></td></tr>";
				$q = $db->query("SELECT COUNT(*) AS c,mxserver.mxserver_stat
				                 FROM targetmx
				                 JOIN target ON targetmx.idtarget=target.idtarget
				                 JOIN mxserver ON targetmx.idmxserver=mxserver.idmxserver
				                 $whw
				                 GROUP BY mxserver.mxserver_stat
				                 HAVING c>=9
				                 ORDER BY c DESC,mxserver.mxserver_stat");
				while ($r = $q->fetch_array()) {
					$b .= "\n<tr><td align='left' style='text-align: left;'>$r[mxserver_stat]</td><td align='right' style='text-align: right;'>" . number_format($r['c'], 0, ',', '.') . "</td><td align='right' style='text-align: right;'>" . number_format(($r['c']*100/$t[0]), 2, ',', '.') . "%</td></tr>";
				}
				$b.= "\n</table>";
				$b .= "\n<!-- CH_MX "	. date("j/n/Y G:i:s") . " -->\n";
				file_put_contents($cachefile, $b);
			break;
			// http content type
			case CH_CONTTYPE:
				$wha = $idcategory == 0 ? '' : " AND target.idcategory='$idcategory'";
				$whw = $idcategory == 0 ? '' : " WHERE target.idcategory='$idcategory'";
				$t = $db->query("SELECT COUNT(*) FROM target $whw")->fetch_array();
				$b .= "\n<table border='0' align='center'>";
				$b .= "\n<tr><td align='center' colspan='3'><h2>HTML Content Type</h2></td></tr>";
				$q = $db->query("SELECT http_contenttype,COUNT(http_contenttype) AS c 
				                 FROM targetdata 
				                 JOIN target ON targetdata.idtarget=target.idtarget
				                 WHERE http_contenttype<>'' $wha 
				                 GROUP BY http_contenttype 
				                 HAVING c>=1
				                 ORDER BY c DESC");
				while ($r = $q->fetch_array()) {
					$b .= "\n<tr><td align='left' style='text-align: left;'>$r[http_contenttype]</td><td align='right' style='text-align: right;'>" . number_format($r['c'], 0, ',', '.') . "</td><td align='right' style='text-align: right;'>" . number_format(($r['c']*100/$t[0]), 2, ',', '.') . "%</td></tr>";
				}
				$b.= "\n</table>";
				$b .= "\n<!-- CH_CONTTYPE "	. date("j/n/Y G:i:s") . " -->\n";
				file_put_contents($cachefile, $b);
			break;
			// hash dei certificati ssl
			case CH_SSLHASH:
				$wha = $idcategory == 0 ? '' : " AND target.idcategory='$idcategory'";
				$whw = $idcategory == 0 ? '' : " WHERE target.idcategory='$idcategory'";
				$t = $db->query("SELECT COUNT(*) FROM target JOIN targetdata ON targetdata.idtarget=target.idtarget WHERE targetdata.ishttps='1' $wha")->fetch_array();
				$b .= "\n<table border='0' align='center'>";
				$b .= "\n<tr><td align='center' colspan='3'><h2>Hash dei certificati ssl</h2></td></tr>";
				$q = $db->query("SELECT https_signature,COUNT(https_signature) AS c 
				                 FROM targetdata 
				                 JOIN target ON targetdata.idtarget=target.idtarget
				                 WHERE https_signature<>'' $wha 
				                 GROUP BY https_signature 
				                 HAVING c>=0
				                 ORDER BY c DESC");
				while ($r = $q->fetch_array()) {
					$b .= "\n<tr><td align='left' style='text-align: left;'>$r[https_signature]</td><td align='right' style='text-align: right;'>" . number_format($r['c'], 0, ',', '.') . "</td><td align='right' style='text-align: right;'>" . number_format(($r['c']*100/$t[0]), 2, ',', '.') . "%</td></tr>";
				}
				$b.= "\n</table>";
				$b .= "\n<!-- CH_SSLHASH "	. date("j/n/Y G:i:s") . " -->\n";
				file_put_contents($cachefile, $b);
			break;
			// organizzazione emettitrice del certificato SSL
			case CH_SSLISSUER:
				$wha = $idcategory == 0 ? '' : " AND target.idcategory='$idcategory'";
				$whw = $idcategory == 0 ? '' : " WHERE target.idcategory='$idcategory'";
				$t = $db->query("SELECT COUNT(*) FROM target JOIN targetdata ON targetdata.idtarget=target.idtarget WHERE targetdata.ishttps='1' $wha")->fetch_array();
				$b .= "\n<table border='0' align='center'>";
				$b .= "\n<tr><td align='center' colspan='3'><h2>Emettitore dei certificati ssl</h2></td></tr>";
				$q = $db->query("SELECT https_issuerorg,COUNT(https_issuerorg) AS c 
				                 FROM targetdata 
				                 JOIN target ON targetdata.idtarget=target.idtarget
				                 WHERE https_issuerorg<>'' $wha 
				                 GROUP BY https_issuerorg 
				                 HAVING c>=5
				                 ORDER BY c DESC");
				while ($r = $q->fetch_array()) {
					$b .= "\n<tr><td align='left' style='text-align: left;'>$r[https_issuerorg]</td><td align='right' style='text-align: right;'>" . number_format($r['c'], 0, ',', '.') . "</td><td align='right' style='text-align: right;'>" . number_format(($r['c']*100/$t[0]), 2, ',', '.') . "%</td></tr>";
				}
				$b.= "\n</table>";
				$b .= "\n<!-- CH_SSLISSUER "	. date("j/n/Y G:i:s") . " -->\n";
				file_put_contents($cachefile, $b);
			break;
			// Country IPv4
			case CH_COUNTRYIPV4:
				$wha = $idcategory == 0 ? '' : " AND target.idcategory='$idcategory'";
				$whw = $idcategory == 0 ? '' : " WHERE target.idcategory='$idcategory'";
				$t = $db->query("SELECT COUNT(*) FROM target JOIN targetdata ON targetdata.idtarget=target.idtarget WHERE targetdata.ishttps='1' $wha")->fetch_array();
				$b .= "\n<table border='0' align='center'>";
				$b .= "\n<tr><td align='center' colspan='3'><h2>Country IPv4</h2></td></tr>";
				$q = $db->query("SELECT COUNT(ip.countrycode) AS c,ip.countrycode
				                 FROM target 
				                 JOIN ip on target.idipv4=ip.idip
				                 $whw
				                 GROUP BY ip.countrycode
				                 HAVING c>=9
				                 ORDER BY c DESC");
				while ($r = $q->fetch_array()) {
					$b .= "\n<tr><td align='left' style='text-align: left;'>$r[countrycode]</td><td align='right' style='text-align: right;'>" . number_format($r['c'], 0, ',', '.') . "</td><td align='right' style='text-align: right;'>" . number_format(($r['c']*100/$t[0]), 2, ',', '.') . "%</td></tr>";
				}
				$b.= "\n</table>";
				$b .= "\n<!-- CH_COUNTRYIPV4 "	. date("j/n/Y G:i:s") . " -->\n";
				file_put_contents($cachefile, $b);
			break;
			// errore!
			default:
				$b .= "CACHE_DATI errore, $quale non riconosciuto";
		}
	}
	return $b;
}


// ### END OF FILE ###