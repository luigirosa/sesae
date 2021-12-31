<?php

/**
 *
 * SESAE - Admin - Definizione delle variabili e delle funzioni comuni a tutte le procedure (tranne il login)
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2018-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * 
 * RICHIEDE: pear install Net_DNS2
 *
 * 20180217 prima versione
 * 20181013 nuovo formato log
 * 20190121 spostamento su set.sesae.com
 * 20200614 basta log
 * 20201105 DNS2
 * 20211204 cambio licenza per pubblicazione sorgenti
 * 20211210 aggiunto forzarescan()
 * 20211226 merge admin+public e ristrutturazione albero directory
 * 20211230 romosso RRD e storicizzazione su SQL
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

define('SESAE_VERSION', "2021123102");

if(!defined('SESAE')) {
	header ('Location: https://www.google.com');
	die();
}

// timeout per get_headers della scansione del target
ini_set('default_socket_timeout', 20);

// sessione
session_start();

// overridmx_statide di qls impostazione del server http
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
	echo "<html><head><title>Procedura in manutenzione</title><body><p>La procedura &egrave; in manutenzione, torneremo appena possibile.\n<!-- " . mysqli_connect_error() . " -->\n</p></body></html>";
	die();
}
$db->set_charset('UTF8');

// B2TOOLS
require('../inc_b2tools/b2tools.inc.php');
$b2 = new objB2($db, 'sys_campi');

// Composer
require __DIR__ . '/vendor/autoload.php';
use GuzzleHttp\Client;

define('HTTP_ENORESOLVE', 9991);
define('HTTP_ENONUMERIC', 9992);

// gruppi di storicizzazione
define('ST_GEN_NUMEROSITI',      1);
define('ST_GEN_INHTTPS',         2);
define('ST_GEN_CONIPV6',         3);
define('ST_GEN_IPV4UNIVOCI',     4);
define('ST_GEN_CONFRAME',       20);
define('ST_HTTPSERVER',          5);
 define('ST_APACHEVER',           6);
 define('ST_APACHEOS',            7);
 define('ST_IISVER',              8);
 define('ST_POWERBY',             9);
 define('ST_PHPVER',             10);
 define('ST_COUNTRYIPV4',        11);
define('ST_SSLISSUER',          12);
define('ST_SSLHASH',            13);
define('ST_CONTENTTYPE',        14);
define('ST_DNS',                15);
define('ST_MX',                 16);
 define('ST_TLD',                17);
define('ST_GENERATOR',          19);

//array con gli algoritmi delle statistiche
$aAlgoStat = array('RPL'=>'Sostituisci il testo','RGX'=>'RegEx');

// array dei resolver utilizzati da Net_DNS2
$aNetDNS2resolvers = array('9.9.9.9', '1.1.1.1', '8.8.8.8');

if(!defined('SKIPCHECK')) {
	// prima di tutto: se non e' impostato l'array di sessione, e' inutile continuare (e generare warning nei log!)
	if (!isset($_SESSION['user'])) {
		session_destroy();
		error_log("*** SESAE - Sessione distrutta dalla mancanza di _SESSION[user]");
		header('Location: index.php');
		die();
	}
	// controllo di sicurezza 1/3 - attivo
	$q = $db->query("SELECT isactive FROM user WHERE iduser='" . $db->escape_string($_SESSION['user']['iduser']) . "'");
	if ($q->num_rows < 1) {
		error_log("user " . $_SESSION['user']['login'] . " non trovato per il controllo di stato attivo. Strano.");
		session_destroy();
		header('Location: index.php');
		die();
	} else {
		$r = $q->fetch_array();
		if ('0' == $r['isactive']) {
			error_log("user " . $_SESSION['user']['login'] . " disattivato, lo caccio.");
			session_destroy();
			header('Location: index.php');
			die();
		}
	}
	// controllo di sicurezza 2/3 - password
	$q = $db->query("SELECT iduser FROM user WHERE iduser='" . $db->escape_string($_SESSION['user']['iduser']) . "' AND password='" . $db->escape_string($_SESSION['user']['password']) . "'");
	if ($q->num_rows < 1) {
		error_log("user " . $_SESSION['user']['login'] . ": password mismatch, lo caccio.");
		session_destroy();
		header('Location: index.php');
		die();
	}
	// controllo di sicurezza 3/3 - accesso allo script
	$r = explode('/', $_SERVER["SCRIPT_NAME"]);
	$filename = $r[count($r) - 1]; 
	$q = $db->query("SELECT idmenu FROM menu WHERE filename='" . $db->escape_string($filename) . "'");
	// se lo script non e' nel menu non e' una bella cosa
	if ($q->num_rows < 1) {
		error_log("Script $filename non trovato nella tabella menu: caccio l'user, ma bisognerebbe trovare una soluzione.");
		session_destroy();
		header('Location: index.php');
		die();
	} else {
		$r = $q->fetch_array();
	if (!isabilitato($_SESSION['user']['idlevel'], $r['idmenu'])) {
			error_log($_SESSION['user']['login'] . " ha violato la sicurezza di $filename: lo caccio.");
			session_destroy();
			header('Location: index.php');
			die();
		}
	}
	unset($filename);
	unset($r);
	unset($q);
	unset($qq);
}
// fine controlli di sicurezza


/**
 * intestazione($titolo)
 * 
 * Disegna l'intestazione e prepara l'ambiente
 *
 */
function intestazione($titolo) {
	global $db;
	// favivon da favicon.cc
	echo "<!DOCTYPE html>
	      <html>
	      <head>
	      <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/>
	      <meta name='robots' content='noindex'/>
	      <link rel='stylesheet' type='text/css' href='https://fonts.googleapis.com/css?family=Open+Sans|Open+Sans:b' />
	      <link rel='stylesheet' href='static/style.css' type='text/css'/>
	      <link rel='stylesheet' href='/inc_jquery-ui/jquery-ui.css' type='text/css'>
	      <meta http-equiv='X-UA-Compatible' content='IE=edge'/>
	      <meta name='viewport' content='width=device-width, initial-scale=1'/>
	      <meta http-equiv='cache-control' content='no-cache'/>
	      <meta http-equiv='pragma' content='no-cache'/>
	      <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
	      <script src='/inc_jquery-ui/jquery-ui.js'></script>
	      <script type='text/javascript' src='/inc_jquery-ui/datepicker-it.js'></script>
	      <script src='/static/menu-script.js'></script>
	      <title>SESAE ADMIN - $titolo</title>
	      </head>
	      <body>";
	// menu e logo
	echo "\n<div id='stickyheader' style='background-color: white;'>\n<table border='0' cellspacing='0' cellpadding='0' align='left'><tr>";
	//logo
	echo "<td valign='bottom'><!-- img src='static/logo.png' border='0'/ -->&nbsp;</td>";
	// menu
	echo "\n<td valign='middle'><div id='cssmenu'><ul>";
	$q = $db->query("SELECT menu,url,idmenu FROM menu WHERE isvisible='1' AND idfather='0' ORDER BY weight");
	while ($r = $q->fetch_array()) {
		// figli?
		$qf = $db->query("SELECT idmenu,menu,url FROM menu WHERE isvisible='1' AND idfather='$r[idmenu]' ORDER BY weight");
		if (isabilitato($_SESSION['user']['idlevel'], $r['idmenu'])) {
			$url = '' == $r['url'] ? '#' : $r['url'];
			$class = $qf->num_rows == 0 ? 'active' : 'has-sub';
			echo "<li class='$class'><a href='$url'><span>$r[menu]</span></a>";
			// figli
			if ($qf->num_rows > 0) {
				echo "<ul>";
				while ($rf = $qf->fetch_array()) {
					$ai = array();
					$count = 0;
					if (isabilitato($_SESSION['user']['idlevel'], $rf['idmenu'])) {
						$count++;
						$ai[$count]['url'] = '' == $rf['url'] ? '#' : $rf['url'];
						$ai[$count]['menu'] = $rf['menu'];
					}
					for ($n = 1; $n < $count; $n++) if (isset($ai[$n]['url'])) echo "<li><a href='" . $ai[$n]['url'] . "'><span>" . $ai[$n]['menu'] . "</span></a></li>";
					if (isset($ai[$n]['url'])) echo "<li class='last'><a href='" . $ai[$count]['url'] . "'><span>" . $ai[$count]['menu'] . "</span></a></li>";
				}
				echo "</ul>";
			}
			echo "</li>";
		}
	}
	echo "</ul></div></td></tr></table></div><br clear='all'/>";
	// titolo
	if ('' != $titolo) echo "\n<div class='titolopagina'>$titolo</div>";
	
}


/**
 * piede()
 * 
 * Disegna la fine della pagina
 *
 */
function piede() {
	global $b2;
	$amsg = array();
	$amsg[] = $_SESSION['user']['name'];
	if ('' != $_SESSION['user']['lastloginok']) $amsg[] = "Ultima connessione OK: " . $b2->ts2ita($_SESSION['user']['lastloginok']);
	if ('' != $_SESSION['user']['lastloginko']) $amsg[] = "Ultima connessione KO: " . $b2->ts2ita($_SESSION['user']['lastloginko']);
	echo "<div class='piedepagina' id='piedepagina'>" . implode('<br/>', $amsg) . "</div>";
	echo '<script type="text/javascript">setInterval(function(){$.post("/set/rigenera_sessione.php");},600000);</script>'; // rigenera la sessione ogni 10 minuti
	echo "<script>$(function(){
        // Check the initial Poistion of the Sticky Header
        var stickyHeaderTop = $('#stickyheader').offset().top;
        $(window).scroll(function(){
                if( $(window).scrollTop() > stickyHeaderTop ) {
                        $('#stickyheader').css({position: 'fixed', top: '0px'});
                        $('#stickyalias').css('display', 'block');
                } else {
                        $('#stickyheader').css({position: 'static', top: '0px'});
                        $('#stickyalias').css('display', 'none');
                }
        });
  });</script>";
	echo "\n</body>\n</html>";
}


/**
 * isabilitato($idlevel, $idmenu)
 * 
 * controlla se il livello utente $idlevel e' abilitato al menu $idmenu
 * 
 */
function isabilitato($idlevel, $idmenu) {
	global $db;
	$q = $db->query("SELECT idlevel FROM permission WHERE idlevel='$idlevel' AND idmenu='$idmenu'");
	return $q->num_rows > 0;
}


/**
 * cercaheader($a, $h)
 * 
 * cerca un header tra quelli tornati dal server HTTP
 * 
 */
function cercaheader($a, $h) {
	$h = strtolower(trim($h));
	$retval = '';
	foreach ($a as $riga) {
		// registro solo il primo valore e controllo che sia un header di tipo nome: valore
		if ('' == $retval and strpos($riga, ':') > 0) {
			list($nome, $contenuto) = explode(':', $riga, 2);
			$nome = strtolower(trim($nome));
			if ($nome == $h) {
				$retval = trim($contenuto);
			}
		}
	}
	return $retval;
}


/**
 * getipgeo($ip)
 * 
 * Restituisce la geolocalizzazione di un IP, gestendo la cache su SQL
 * https://ip-api.com/
 *
 * 20211230 prima versione
 * 
 */
function getipgeo($ip) {
	global $db, $b2;
	$ttl = 2600000; // un mese suppergiu`
	$retval = array();
	if ('clearcache' == $ip) {
		// cleanup dei record scaduti
		$primadi = time() - $ttl;
		$db->query("DELETE FROM ipcountrycache WHERE ctime<$primadi");
	} else {
		$ip = $b2->normalizza($ip, B2_NORM_SQL || B2_NORM_TRIM);		
		$q = $db->query("SELECT * FROM ipcountrycache WHERE ip='$ip'");
		$leggidaweb = true;
		if ($q->num_rows > 0) {
			$r = $q->fetch_array();
			if (($r['ctime'] + $ttl) > time()) { // se la cache non e` scaduta
				$leggidaweb = false;
				$retval = $r;
			}
		} 
		if ($leggidaweb) {
			$db->query("DELETE FROM ipcountrycache WHERE ip='$ip'");  // nel caso sia in cache e scaduto
			$a = array();
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://ip-api.com/json/$ip?fields=22076931");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // ritorna il trasferimento come stringa
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			$json = curl_exec($ch);
			curl_close($ch);
			$ret = json_decode($json);
			if ('success' == $ret->status) {
				$a[] = $b2->campoSQL("ip", $ip);
				$a[] = $b2->campoSQL("ctime", time());
				if (isset($ret->continent))   {$a[] = $b2->campoSQL("continent",   $ret->continent);   $retval['continent']   = $ret->continent;}
				if (isset($ret->country))     {$a[] = $b2->campoSQL("country",     $ret->country);     $retval['country']     = $ret->country;}
				if (isset($ret->countryCode)) {$a[] = $b2->campoSQL("countrycode", $ret->countryCode); $retval['countrycode'] = $ret->countryCode;}
				if (isset($ret->isp))         {$a[] = $b2->campoSQL("isp",         $ret->isp);         $retval['isp']         = $ret->isp;}
				if (isset($ret->org))         {$a[] = $b2->campoSQL("org",         $ret->org);         $retval['org']         = $ret->org;}
				if (isset($ret->asname))      {$a[] = $b2->campoSQL("asname",      $ret->asname);      $retval['asname']      = $ret->asname;}
				if (isset($ret->reverse))     {$a[] = $b2->campoSQL("reverse",     $ret->reverse);     $retval['reverse']     = $ret->reverse;}
				if (isset($ret->as)) {
					list($as,$asowner) = explode(' ', $ret->as, 2);
					$as = trim($as);
					$a[] = $b2->campoSQL("as", $as);
					$retval['as'] = $as;
					$asowner = trim($asowner);
					$a[] = $b2->campoSQL("asowner", $asowner);
					$retval['asowner'] = $asowner;
				}
				
				
				if (isset($ret->hosting)) {
					$hosting = $ret->hosting == 'true' ? 1 : 0;
					$a[] = $b2->campoSQL("ishosting", $hosting);
					$retval['ishosting'] = $hosting;
				}
				$db->query("INSERT INTO ipcountrycache SET " . implode(',', $a));
			} else {
				error_log("grtipgeo() IP $ip - " . $ret->message);
			}
		}
	}
	return $retval;
}


/**
 * aggiornacampo($idtarget, $campo, $valore)
 * 
 * Aggiorna un campo di un target
 *
 * 20211205 cleanup
 * 
 */
function aggiornacampo($idtarget, $campo, $valore) {
	global $db, $b2;
	$checked = false;
	switch ($campo) {
		case 'hostname':
			$valore = trim(strtolower($valore));
			$r = $db->query("SELECT hostname FROM targetdata WHERE idtarget='$idtarget'")->fetch_array();
			if (trim($r[0]) != $valore) {
				$db->query("UPDATE targetdata SET hostname='" . $b2->normalizza($valore) . "',checked='0' WHERE idtarget='$idtarget'");
				$checked = true;
			}
		break;
		case 'ipv6':
			$valore = trim(strtolower($valore));
			$idipv6 = 0;
			if ('' != $valore) {
				$idipv6 = getipid($valore);
			} 
			$db->query("UPDATE target SET idipv6='" . $b2->normalizza($idipv6) . "' WHERE idtarget='$idtarget'");
		break;
		case 'ipv4':
			$valore = trim($valore);
			$idipv4 = 0;
			if ('' != $valore) {
				$idipv4 = getipid($valore);
			} 
			$db->query("UPDATE target SET idipv4='" . $b2->normalizza($idipv4) . "' WHERE idtarget='$idtarget'");
		break;
		case 'head':
			$db->query("UPDATE targetdata SET head='" . $b2->normalizza($valore, B2_NORM_TRIM || B2_NORM_SQL) . "' WHERE idtarget='$idtarget'");
		break;
		case 'ipv6host':
			$valore = trim(strtolower($valore));
			$db->query("UPDATE targetdata SET ipv6host='" . $b2->normalizza($valore) . "' WHERE idtarget='$idtarget'");
		break;
		case 'ipv4host':
			$valore = trim(strtolower($valore));
			$db->query("UPDATE targetdata SET ipv4host='" . $b2->normalizza($valore) . "' WHERE idtarget='$idtarget'");
		break;
		case 'http_code':
			if (!is_numeric($valore)) $valore = HTTP_ENONUMERIC;
			$db->query("UPDATE targetdata SET http_code='" . $b2->normalizza($valore) . "' WHERE idtarget='$idtarget'");
		break;
		case 'http_contenttype':
			$valore = trim($valore);
			$db->query("UPDATE targetdata SET http_contenttype='" . $b2->normalizza($valore) . "' WHERE idtarget='$idtarget'");
		break;
		case 'http_server':
			$valore = trim($valore);
			if ('' != $valore) {
				$s = array();
				$s = statisticacampo($valore, 'HTS');
				$a = array();
				$a[] = $b2->campoSQL("idtarget", $idtarget);
				$a[] = $b2->campoSQL("http_server", $valore);
				$a[] = $b2->campoSQL("http_server_stat", $s['stat']);
				$a[] = $b2->campoSQL("http_server_stat_fam", $s['stat_fam']);
				$db->query("INSERT INTO http_server SET " . implode(',', $a));
			}
		break;
		case 'http_generator':
			$valore = trim($valore);
			if ('' != $valore) {
				$a = array();
				$s = array();
				$s = statisticacampo($valore, 'GNR');
				$a[] = $b2->campoSQL("idtarget", $idtarget);
				$a[] = $b2->campoSQL("http_generator", $valore);
				$a[] = $b2->campoSQL("http_generator_stat", $s['stat']);
				$a[] = $b2->campoSQL("http_generator_stat_fam", $s['stat_fam']);
				$db->query("INSERT INTO http_generator SET " . implode(',', $a));
			}
		break;
		case 'http_location':
			$valore = trim(strtolower($valore));
			$db->query("UPDATE targetdata SET http_location='" . $b2->normalizza($valore) . "' WHERE idtarget='$idtarget'");
		break;
		case 'poweredby':
			$valore = trim($valore);
			$db->query("DELETE FROM poweredby WHERE idtarget='$idtarget'");
			if ('' != $valore) {
				$a = array();
				$a[] = $b2->campoSQL("idtarget", $idtarget);
				$a[] = $b2->campoSQL("poweredby", $valore);
				$db->query("INSERT INTO poweredby SET " . implode(',', $a));
			}
		break;
		case 'http_html':
			$db->query("DELETE FROM http_html WHERE idtarget='$idtarget'");
			$db->query("INSERT INTO http_html SET http_html='" . $b2->normalizza($valore) . "',idtarget='$idtarget'");
		break;
		case 'html_title':
			$valore = strip_tags($valore);
			$valore = trim(str_replace(array("\n", "\t", "\r"), ' ', $valore));
			$valore = trim(str_replace('  ', ' ', $valore));
			$db->query("UPDATE targetdata SET html_title='" . $b2->normalizza($valore) . "' WHERE idtarget='$idtarget'");
		break;
		case 'ishttps':
			$db->query("UPDATE targetdata SET ishttps='" . $b2->normalizza($valore) . "' WHERE idtarget='$idtarget'");
		break;
		case 'https_cert':
			$db->query("UPDATE targetraw SET https_cert='" . $b2->normalizza($valore, B2_NORM_TRIM || B2_NORM_SQL) . "' WHERE idtarget='$idtarget'");
		break;
		case 'https_certname':
			$db->query("UPDATE targetdata SET https_certname='" . $b2->normalizza($valore, B2_NORM_TRIM || B2_NORM_SQL) . "' WHERE idtarget='$idtarget'");
		break;
		case 'https_subject':
			$db->query("UPDATE targetdata SET https_subject='" . $b2->normalizza($valore, B2_NORM_TRIM || B2_NORM_SQL) . "' WHERE idtarget='$idtarget'");
		break;
		case 'https_issuer':
			$db->query("UPDATE targetdata SET https_issuer='" . $b2->normalizza($valore, B2_NORM_TRIM || B2_NORM_SQL) . "' WHERE idtarget='$idtarget'");
		break;
		case 'https_issuerorg':
			$db->query("UPDATE targetdata SET https_issuerorg='" . $b2->normalizza($valore, B2_NORM_TRIM || B2_NORM_SQL) . "' WHERE idtarget='$idtarget'");
		break;
		case 'https_validto':
			if (!is_numeric($valore)) $valore = 0;
			$db->query("UPDATE targetdata SET https_validto='" . $b2->normalizza($valore) . "' WHERE idtarget='$idtarget'");
		break;
		case 'https_signature':
			$db->query("UPDATE targetdata SET https_signature='" . $b2->normalizza($valore, B2_NORM_TRIM || B2_NORM_SQL) . "' WHERE idtarget='$idtarget'");
			break;
		case 'robots':
			$db->query("UPDATE targetraw SET robots='" . $b2->normalizza($valore, B2_NORM_TRIM || B2_NORM_SQL) . "' WHERE idtarget='$idtarget'");
		break;
		case 'cookies':
			$db->query("UPDATE targetraw SET cookies='" . $b2->normalizza($valore, B2_NORM_TRIM || B2_NORM_SQL) . "' WHERE idtarget='$idtarget'");
		break;
		case 'url':
			$valore = strtolower($valore);
			$db->query("UPDATE target SET url='" . $b2->normalizza($valore, B2_NORM_TRIM || B2_NORM_SQL) . "' WHERE idtarget='$idtarget'");
		break;
		case 'mailhost':
			$valore = strtolower($valore);
			$db->query("UPDATE target SET mailhost='" . $b2->normalizza($valore, B2_NORM_TRIM || B2_NORM_SQL) . "' WHERE idtarget='$idtarget'");
		break;
		case 'goog_analytics':
			$valore = strtolower($valore);
			$db->query("UPDATE targetdata SET goog_analytics='" . $b2->normalizza($valore, B2_NORM_TRIM || B2_NORM_SQL) . "' WHERE idtarget='$idtarget'");
		break;
		case 'goog_tag':
			$valore = trim(strtolower($valore));
			$db->query("UPDATE targetdata SET goog_tag='" . $b2->normalizza($valore) . "' WHERE idtarget='$idtarget'");
		break;
		case 'goog_asy':
			$valore = trim(strtolower($valore));
			$db->query("UPDATE targetdata SET goog_ASY='" . $b2->normalizza($valore) . "' WHERE idtarget='$idtarget'");
		break;
		case 'ipv4cname':
			$valore = trim(strtolower($valore));
			$db->query("UPDATE targetdata SET ipv4cname='" . $b2->normalizza($valore) . "' WHERE idtarget='$idtarget'");
		break;
		case 'ipv6cname':
			$valore = trim(strtolower($valore));
			$db->query("UPDATE targetdata SET ipv6cname='" . $b2->normalizza($valore) . "' WHERE idtarget='$idtarget'");
		break;
			
		default:
			error_log("aggiornacampo() campo $campo valore $valore - Campo non riconosciuto.");
	}
	// se e' cambiato qualche campo, flaggo 
	$db->query("UPDATE target SET checked='0' WHERE idtarget='$idtarget'");
}

/**
 * aggiornacampoarray($idtarget, $campo, $avalore)
 * 
 * Aggiorna un campo di un target da un array
 * 
 * 20211206 modifica gestione MX
 *
 */
function aggiornacampoarray($idtarget, $campo, $avalore) {
	global $db, $b2;
	$checked = false;
	switch ($campo) {
		case 'mx':
			//pulizia
			$db->query("DELETE FROM targetmx WHERE idtarget='$idtarget'");
			// se l'array in input non e` vuoto, popoliamo 
			if (!empty($avalore)) {
				foreach ($avalore as $riga) {
					// normalizzo
					$b = trim(strtolower($riga));
					list($peso, $mx) = explode(' ', $b);
					$mx = trim($mx);
					// cerco il server nella tabella dei server
					$q = $db->query("SELECT idmxserver,mxserver FROM mxserver WHERE mxserver='" . $b2->normalizza($mx) . "'");
					if ($q->num_rows > 0) {
						$r = $q->fetch_array();
						$idmxserver = $r['idmxserver'];
					} else { //se non lo trovo, lo creo
						$a = array();
						$a[] = $b2->campoSQL('mxserver', $mx);
						$s = statisticacampo($mx, 'MX');
						$a[] = $b2->campoSQL('mxserver_stat', $s['stat']);
						$db->query("INSERT INTO mxserver SET " . implode(',', $a));
						$idmxserver = $db->insert_id;
					}
					// e ora aggiungo il record alla tabella di raccordo
					$a = array();
					$a[] = $b2->campoSQL('idtarget', $idtarget);
					$a[] = $b2->campoSQL('peso', $peso);
					$a[] = $b2->campoSQL('idmxserver', $idmxserver);
					$db->query("INSERT INTO targetmx SET " . implode(',', $a));
				}
			}
		break;
		case 'dnsauth':
			$db->query("DELETE FROM dnsauth WHERE idtarget='$idtarget'");
			foreach ($avalore as $riga) {
				$riga = trim(strtolower($riga));
				$a = array();
				$s = statisticacampo($riga, 'DNS');
				$a[] = $b2->campoSQL("idtarget", $idtarget);
				$a[] = $b2->campoSQL('dnsauth', $riga);
				$a[] = $b2->campoSQL('dnsauth_stat', $s['stat']);
				$db->query("INSERT INTO dnsauth SET " . implode(',', $a));
			}
		break;
		case 'http_header':
			$db->query("DELETE FROM http_header WHERE idtarget='$idtarget'");
			foreach ($avalore as $riga) {
				// normalizzo
				$b = trim($riga);
				$a = array();
				$a[] = $b2->campoSQL('idtarget', $idtarget);
				$a[] = $b2->campoSQL('http_header', $riga);
				$db->query("INSERT INTO http_header SET " . implode(',', $a));
			}
		break;

		default:
			error_log("aggiornacampoarray() $campo non riconosciuto.");
	}
	// se e' cambiato qualche campo, flaggo 
	$db->query("UPDATE target SET checked='0' WHERE idtarget='$idtarget'");
}


/**
 * scantarget($idtarget, idprobe)
 * 
 * Analizza un target
 * 
 */
function scantarget($idtarget, $idprobe = 0) {
	global $db, $b2, $aNetDNS2resolvers;

	// salvo l'ultima visita, che diventa a questo punto penultima
	// lo faccio subito per ridurre la probabilita` di collisioni
	$db->query("UPDATE target SET visited_before=visited WHERE idtarget='$idtarget'");
	// aggiorno l'ultima visita
	$db->query("UPDATE target SET visited ='" . time() . "' WHERE idtarget='$idtarget'");	

	$retval = '';
	// default per get_headers
	$opts = array(
	  'http'=>array(
	    'timeout'=>20,
	    'user_agent'=>randomuseragent(),
	    'ignore_errors'=>TRUE
	  ),
	  'https'=>array(
	    'timeout'=>20,
	    'user_agent'=>randomuseragent(),
	    'ignore_errors'=>TRUE
	  )
	);
	$context = stream_context_set_default($opts);
	// definisco l'oggetto Net_DNS2 per usarlo all' interno della funzione
	$dns2 = new Net_DNS2_Resolver(array(
		'nameservers'   => $aNetDNS2resolvers,
		'ns_random'    => true
		));
	
	$r = $db->query("SELECT target.url,target.mailhost,
	                        targetdata.hostname,targetdata.ipv6,targetdata.ipv4,targetdata.ipv6host,targetdata.ipv4host
	                 FROM target
	                 LEFT JOIN targetdata ON target.idtarget=targetdata.idtarget
	                 WHERE target.idtarget='$idtarget'")->fetch_array();

	// vedo se e` un record flaggato come nuovo
	$isnewtarget = false;
	if ($r['mailhost'] == '__new__') {
		$isnewtarget = true;
		cleantarget($idtarget);
		$r['mailhost'] = '';
	} 
	
	$r['hostname'] = calcolahostname($r['url']);
	aggiornacampo($idtarget, 'hostname', $r['hostname']);
	$retval = "\n$r[hostname]";	
	// popolaro mailhost
	if ('' == trim($r['mailhost'])) {
		$mailhost = str_ireplace('www.', '', $r['hostname']);
		aggiornacampo($idtarget, 'mailhost', $mailhost);
		$r['mailhost'] = $mailhost;
	}

	// controllo se esiste il record nelle tabelle di supporto
	$qq = $db->query("SELECT idtarget FROM targetdata WHERE idtarget='$idtarget'");
	if ($qq->num_rows < 1) {
		$db->query("INSERT INTO targetdata SET idtarget='$idtarget',hostname='$r[hostname]'");
		$retval .= " targetdata creato";
	}
	$qq = $db->query("SELECT idtarget FROM targetraw WHERE idtarget='$idtarget'");
	if ($qq->num_rows < 1) {
		$db->query("INSERT INTO targetraw SET idtarget='$idtarget'");
		$retval .= " targetraw creato";
	}
	
	// vedo se il dominio ha un DNS che riesce a risolvere almeno l'IPv4
	$dnsok = true;
	try {
  	$adns = $dns2->query($r['hostname'], 'A');
  } catch(Net_DNS2_Exception $e) {
		$retval .= " impossibile risolvere il nome: " . $e->getMessage();
		aggiornacampo($idtarget, 'http_code', HTTP_ENORESOLVE);
		$dnsok = false;
	}
	if ($dnsok) {
		// setup dei cookies
		$cookiejar = tempnam("/tmp", "SESAE");
		$qq = $db->query("SELECT cookies FROM targetraw WHERE idtarget='$idtarget'");
		if ($qq->num_rows > 0) {
			$rr = $qq->fetch_array();
			file_put_contents($cookiejar, $rr['cookies']);
		}
		
		// IPv4
		$ipv4 = isset($adns->answer[0]->address) ? $adns->answer[0]->address : '';
		$ipv4 = substr(trim($ipv4), 0, 15);
		$ipv4host = isset($adns->answer[0]->name) ? $adns->answer[0]->name : '';
		$ipv4host = substr(trim($ipv4host), 0, 250);
		aggiornacampo($idtarget, 'ipv4host', $ipv4host);
		// se la query ritorna un CNAME, il secondo elemento di answer[] e` il record A 
		if (isset($adns->answer[0]->cname)) {
			$ipv4cname = substr(trim($adns->answer[0]->cname), 0, 250);
			$ipv4 = isset($adns->answer[1]->address) ? $adns->answer[1]->address : '';
			$ipv4 = trim($ipv4);
		} else {
			$ipv4cname = '';
		}
		aggiornacampo($idtarget, 'ipv4', $ipv4);
		aggiornacampo($idtarget, 'ipv4cname', $ipv4cname);
		// IPv6
  	$aipv6 = $dns2->query($r['hostname'], 'AAAA');
		if (isset($aipv6->answer[0])) {
			$retval .= " IPV6 ";
			$ipv6 = isset($adns->answer[0]->address) ? $aipv6->answer[0]->address : '';
			$ipv6 = substr(trim($ipv6), 0, 250);
			$ipv6host = isset($adns->answer[0]->name) ? $aipv6->answer[0]->name : '';
			$ipv6host = substr(trim($ipv6host), 0, 250);
			aggiornacampo($idtarget, 'ipv6host', $ipv6host);
			// se la query ritorna un CNAME, il secondo elemento di answer[] e` il record A 
			if (isset($aipv6->answer[0]->cname)) {
				$ipv6cname = substr(trim($aipv6->answer[0]->cname), 0, 250);
				$ipv6 = isset($aipv6->answer[1]->address) ? $aipv6->answer[1]->address : '';
				$ipv6 = trim($ipv6);
			} else {
				$ipv6cname = '';
			}
			aggiornacampo($idtarget, 'ipv6', $ipv6);
			aggiornacampo($idtarget, 'ipv6cname', $ipv6cname);
	  } else {
			aggiornacampo($idtarget, 'ipv6', '');
			aggiornacampo($idtarget, 'ipv6host', '');
			aggiornacampo($idtarget, 'ipv6cname', '');
		}
		// DNS autoritari
		$dnsns = true;
		$nscount = 0;
		try {
	  	$nameservers = $dns2->query($r['hostname'], 'NS');
			$authdns = array();
			foreach($nameservers->answer as $record) {
				if (isset($record->nsdname)) {
					$authdns[] = $record->nsdname;
	        $nscount++;
	       }
	    }
	  } catch(Net_DNS2_Exception $e) {
			$retval .= " errore NS: " . $e->getMessage();
			$dnsns = false;
		}
    // se non ci sono DNS autoritativi espliciti, guardo l'autorita` che ha risposto
		if ($nscount < 1) {
			$authdns[] = $nameservers->authority[0]->mname;
		}
		aggiornacampoarray($idtarget, 'dnsauth', $authdns);
		
		//pulizia!
		$db->query("DELETE FROM http_server WHERE idtarget='$idtarget'");
		$db->query("DELETE FROM meta WHERE idtarget='$idtarget'");
		$db->query("DELETE FROM http_generator WHERE idtarget='$idtarget'");
		
		// headers
		//$ah = get_headers($r['url']);
		$ah = leggiheader($r['url'], 'guzzle', $cookiejar);
		aggiornacampoarray($idtarget, 'http_header',  $ah);
		aggiornacampo($idtarget, 'http_location', cercaheader($ah, 'location'));
		aggiornacampo($idtarget, 'http_server', cercaheader($ah, 'server'));
		aggiornacampo($idtarget, 'http_contenttype', cercaheader($ah, 'Content-Type'));
		aggiornacampo($idtarget, 'poweredby', cercaheader($ah, 'X-Powered-By'));
		// analisi contenuto html
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		curl_setopt($curl, CURLOPT_COOKIESESSION, true);
		curl_setopt($curl, CURLOPT_COOKIEJAR, $cookiejar);
		curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiejar);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 15);
		curl_setopt($curl, CURLOPT_USERAGENT, randomuseragent());
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
              'Accept-Language: it-IT,it,en-US,en;q=0.5',
              'Connection: keep-alive',
              'Upgrade-Insecure-Requests: 1'));
		curl_setopt($curl, CURLOPT_URL, $r['url']);
		$content = curl_exec($curl);
		$curlinfo = curl_getinfo($curl);
		curl_close($curl);
		//http_code
		$http_code = $curlinfo['http_code'];
		$retval .= " http_$http_code";
		aggiornacampo($idtarget, 'http_code', $http_code);
		// cleanup HTML
		$content = str_replace(chr(8), '', $content);
		$content = str_replace(chr(31), '', $content);  //0x1f
		aggiornacampo($idtarget, 'http_html', $content);
		// title
		$res = preg_match("/<title>(.*)<\/title>/siU", $content, $title_matches);
		if ($res) {
			// Clean up title: remove EOL's and excessive whitespace.
      $html_title = preg_replace('/\s+/', ' ', $title_matches[1]);
			aggiornacampo($idtarget, 'html_title', $html_title);
		} else {
			aggiornacampo($idtarget, 'html_title', '');
		}
		// meta
		$dom = new DOMDocument;
		if($dom->loadHTML($content)) {
			// meta
			foreach( $dom->getElementsByTagName('meta') as $meta ) { 
				$a = array();
				$a[] = $b2->campoSQL('idtarget', $idtarget);
				$a[] = $b2->campoSQL('raw', $meta->ownerDocument->saveXML($meta));
				$db->query("INSERT INTO meta SET " . implode(',', $a));
				// analisi meta generator
				if (stripos($meta->ownerDocument->saveXML($meta), 'generator') !== false) {
					$amatch = array();
					preg_match('/content=[\'"](.*?)[\'"]/i', $meta->ownerDocument->saveXML($meta), $amatch);
					aggiornacampo($idtarget, 'http_generator', $amatch[1]);
				}
			}
		}
		
		// google analytics
		$amatch = array();
		if (preg_match('/ua-\d{5,10}(-\d{1,4})?/i',$content, $amatch)) {
			aggiornacampo($idtarget, 'goog_analytics', $amatch[0]);
		} else {
			aggiornacampo($idtarget, 'goog_analytics', '');
		}
		// google analytics async
		$amatch = array();
		if (preg_match('/ca-pub-\d{16,18}/i',$content, $amatch)) {
			aggiornacampo($idtarget, 'goog_asy', $amatch[0]);
		} else {
			aggiornacampo($idtarget, 'goog_asy', '');
		}
		// google tag manager
		$amatch = array();
		if (preg_match('/www\.googletagmanager\.com\/ns\.html\?id=GTM-\w{6,8}/i',$content, $amatch)) {
			$pos = stripos($amatch[0], 'id=');
			$tagid = substr($amatch[0], $pos+3);
			aggiornacampo($idtarget, 'goog_tag', $tagid);
		} else {
			aggiornacampo($idtarget, 'goog_tag', '');
		}
		
		// https
		$errno = 0;
		$errstr = '';
		$pem_cert = '';
		$pem_chain = '';
		if ('https' == substr($r['url'], 0, 5)) {
			$retval .= " https";
			$stream = stream_context_create (array("ssl" => array("capture_peer_cert" => TRUE, "SNI_enabled" => TRUE, "allow_self_signed"=>TRUE )));
			if ($streamsocket = @stream_socket_client("ssl://$r[hostname]:443", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $stream)) {
				$retval .= " found";
				$acont = stream_context_get_params($streamsocket);
				openssl_x509_export($acont["options"]["ssl"]["peer_certificate"], $pem_cert);
				$acert = openssl_x509_parse($pem_cert);
				aggiornacampo($idtarget, 'ishttps', 1);
				aggiornacampo($idtarget, 'https_cert', json_encode($acert, JSON_UNESCAPED_UNICODE));
				aggiornacampo($idtarget, 'https_certname', $acert['name']);
				aggiornacampo($idtarget, 'https_subject', $acert['subject']['CN']);
				aggiornacampo($idtarget, 'https_issuer', $acert['issuer']['CN']);
				aggiornacampo($idtarget, 'https_issuerorg', $acert['issuer']['O']);
				aggiornacampo($idtarget, 'https_validto', $acert['validTo_time_t']);
				aggiornacampo($idtarget, 'https_signature', $acert['signatureTypeSN']);
			} else {
				$retval .= " not found";
				aggiornacampo($idtarget, 'ishttps', 0);
				aggiornacampo($idtarget, 'https_cert', "$errno: $errstr");
				aggiornacampo($idtarget, 'https_certname', '');
				aggiornacampo($idtarget, 'https_subject', '');
				aggiornacampo($idtarget, 'https_issuer', '');
				aggiornacampo($idtarget, 'https_issuerorg', '');
				aggiornacampo($idtarget, 'https_validto', 0);
				aggiornacampo($idtarget, 'https_signature', '');
			}
		} else {
			aggiornacampo($idtarget, 'ishttps', 0);
			aggiornacampo($idtarget, 'https_cert', '');
			aggiornacampo($idtarget, 'https_certname', '');
			aggiornacampo($idtarget, 'https_subject', '');
			aggiornacampo($idtarget, 'https_issuer', '');
			aggiornacampo($idtarget, 'https_issuerorg', '');
			aggiornacampo($idtarget, 'https_validto', 0);
			aggiornacampo($idtarget, 'https_signature', '');
		}
		
		// robots.txt
		if ($http_code >= 200 or $http_code < 500) {
			// ROBOTS.TXT
			$curl = curl_init();
			// http://php.net/manual/en/function.curl-setopt.php
			curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
			curl_setopt($curl, CURLOPT_COOKIESESSION, true);
			curl_setopt($curl, CURLOPT_COOKIEJAR, $cookiejar);
			curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiejar);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($curl, CURLOPT_TIMEOUT, 8);
			curl_setopt($curl, CURLOPT_USERAGENT, randomuseragent());
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: it-IT,it,en-US,en;q=0.5',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1'));
			curl_setopt($curl, CURLOPT_URL, $r['url'] . '/robots.txt');
			$robots = curl_exec($curl);
			if (!curl_errno($curl)) {
				$ainfo = curl_getinfo($curl);
				if ($ainfo['http_code'] == 200 ) {
					aggiornacampo($idtarget, 'robots', $robots);
				} else {
					aggiornacampo($idtarget, 'robots', '');
					$retval .= " no_robots $ainfo[http_code]";
				}
			} else {
				aggiornacampo($idtarget, 'robots', '');
				$retval .= " no_robots (curl)";
			}
			curl_close($curl);
			// salvo i cookies
			aggiornacampo($idtarget, 'cookies', file_get_contents($cookiejar));
			unlink($cookiejar);
		}
		
		// mx
		$amx = array();
		try {
	  	$mxservers = $dns2->query($r['mailhost'], 'MX');
			foreach($mxservers->answer as $record) {
				if (isset($record->exchange)) {
					$amx[] = $record->preference . ' ' . $record->exchange;
	       }
	    }
			aggiornacampoarray($idtarget, 'mx', $amx);
	  } catch(Net_DNS2_Exception $e) {
			$retval .= " errore MX: " . $e->getMessage();
			aggiornacampoarray($idtarget, 'mx', array());
		}

		$retval .= " $r[url]";
	} // if dnsok

	// aggiona stats
	if ($idprobe > 0) {
		$db->query("UPDATE probe SET counter=counter+1,lasttime='" . time() . "',lasttarget='$idtarget', version='" . $b2->normalizza(SESAE_VERSION) . "' WHERE idprobe='$idprobe'");
		$db->query("UPDATE target SET counter=counter+1,lastprobe='$idprobe' WHERE idtarget='$idtarget'");
	} else {
		$db->query("UPDATE target SET counter=counter+1 WHERE idtarget='$idtarget'");
	}
	return ($retval);
}


/**
 * calcolahostname($url)
 * 
 * Ritorna l'hostname di un URL
 * 
 */
function calcolahostname($url) {
	$dove = strpos($url, '://');
	$hostname = substr($url, $dove + 3);
	$slash = strpos($hostname, '/');
	if ($slash === FALSE) {
		// nulla
	} else {
		$hostname = substr($hostname, 0, $slash);
	}
	return $hostname;
}



/**
 * readsetup($item)
 * 
 * Ritorna un valore di setup 
 * 
 */
function readsetup($item) {
	global $db;
	$r = $db->query("SELECT valore FROM setup WHERE item='$item'")->fetch_array();
	return $r['valore'];
}


/**
 * leggiheader($url, $method = 'curl')
 * 
 * Ritorna un array con gli headers di un URL
 * 
 */
function leggiheader($url, $method = 'curl', $cookiejar) {
	global $db,$b2;
	$aret = array();
	switch ($method) {
		case 'curl':
			$curl = curl_init();
			// http://php.net/manual/en/function.curl-setopt.php
			curl_setopt($curl, CURLOPT_COOKIESESSION, true);
			curl_setopt($curl, CURLOPT_COOKIEJAR, $cookiejar);
			curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiejar);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($curl, CURLOPT_TIMEOUT, 15);
			curl_setopt($curl, CURLOPT_USERAGENT, randomuseragent());
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
			curl_setopt($curl, CURLOPT_HEADER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: it-IT,it,en-US,en;q=0.5',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1'));
			curl_setopt($curl, CURLOPT_URL, $url);
			$h = curl_exec($curl);
			list($headers, $content) = explode("\r\n\r\n", $h, 2);
			curl_close($curl);
			$arighe = explode("\n", $headers);
			$aret = $arighe;
			break;
		case 'guzzle':
			$client = new GuzzleHttp\Client(['base_uri' => $url, 'timeout'  => 10, 'allow_redirects' => false]);
			$response = $client->request('GET', $url);
			foreach ($response->getHeaders() as $name => $values) {
				$aret[] = $name . ': ' . implode(', ', $values);
			}	
			break;
		}
	return $aret;
}



/**
 * get_longest_common_subsequence($string_1, $string_2)
 * 
 * Ritorna la stringa piu' lunga in comune tra due stringhe
 * 
 */
function get_longest_common_subsequence($string_1, $string_2) {
	$string_1_length = strlen($string_1);
	$string_2_length = strlen($string_2);
	$return          = '';
	
	if ($string_1_length === 0 || $string_2_length === 0) {
		// No similarities
		return $return;
	}
	
	$longest_common_subsequence = array();
	
	// Initialize the CSL array to assume there are no similarities
	$longest_common_subsequence = array_fill(0, $string_1_length, array_fill(0, $string_2_length, 0));
	
	$largest_size = 0;
	
	for ($i = 0; $i < $string_1_length; $i++) {
		for ($j = 0; $j < $string_2_length; $j++) {
			// Check every combination of characters
			if ($string_1[$i] === $string_2[$j]) {
				// These are the same in both strings
				if ($i === 0 || $j === 0) {
					// It's the first character, so it's clearly only 1 character long
					$longest_common_subsequence[$i][$j] = 1;
				}
				else {
					// It's one character longer than the string from the previous character
					$longest_common_subsequence[$i][$j] = $longest_common_subsequence[$i - 1][$j - 1] + 1;
				}
				
				if ($longest_common_subsequence[$i][$j] > $largest_size) {
					// Remember this as the largest
					$largest_size = $longest_common_subsequence[$i][$j];
					// Wipe any previous results
					$return       = '';
					// And then fall through to remember this new value
				}
				
				if ($longest_common_subsequence[$i][$j] === $largest_size) {
					// Remember the largest string(s)
					$return = substr($string_1, $i - $largest_size + 1, $largest_size);
				}
			}
			// Else, $CSL should be set to 0, which it was already initialized to
		}
	}
	// Return the list of matches
	return $return;
}


/**
 * isprobe($idtarget, $idprobe)
 * 
 * True se la sonda e' abilitata sul target
 * 
 */
function isprobe($idtarget, $idprobe) {
	global $db,$b2;
	$q = $db->query("SELECT idprobe FROM targetprobe WHERE idtarget='" . $b2->normalizza($idtarget) . "' AND idprobe='" . $b2->normalizza($idprobe) . "'");
	return ($q->num_rows > 0);
}


/**
 * cleantarget($idtarget)
 * 
 * Ripulisce i record di un target, da usare quando si cambia target o prima di cancellarlo
 * 
 */
function cleantarget($idtarget) {
	global $db,$b2;
	if ($idtarget > 0) {
		$db->query("DELETE FROM dnsauth WHERE idtarget='$idtarget'");
		$db->query("DELETE FROM http_generator WHERE idtarget='$idtarget'");
		$db->query("DELETE FROM http_header WHERE idtarget='$idtarget'");
		$db->query("DELETE FROM http_server WHERE idtarget='$idtarget'");
		$db->query("DELETE FROM http_html WHERE idtarget='$idtarget'");
		$db->query("DELETE FROM meta WHERE idtarget='$idtarget'");
		$db->query("DELETE FROM targetmx WHERE idtarget='$idtarget'");
		$db->query("DELETE FROM targetdata WHERE idtarget='$idtarget'");
		$db->query("DELETE FROM targetraw WHERE idtarget='$idtarget'");
		$a = array();
		$a[] = $b2->campoSQL("checked", 0);
		$a[] = $b2->campoSQL("counter", 0);
		$a[] = $b2->campoSQL("visited", 0);
		$a[] = $b2->campoSQL("enabled", 1 );
		$a[] = $b2->campoSQL("mailhost", '');
		$db->query("UPDATE target SET " . implode(',', $a) . " WHERE idtarget='$idtarget'");
	}
	return 0;
}


/**
 * statisticacampo($valore, $gruppo)
 * 
 * Espande un campo basato sulla tabella di conversione per le statistiche
 *
 * 20211205 aggiunta famiglia
 * 
 */
function statisticacampo($valore, $gruppo) {
	global $db,$b2;
	$b = array();
	$valore = trim($valore);
	//default in caso in cui non venga trovato
	$b['stat'] = $valore;
	$b['stat_fam'] = $valore;
	$b['trovato'] = false;
	$b['algoritmo'] = '';
	$b['idstatisticacampo'] = 0;
	$q = $db->query("SELECT * FROM statisticacampo WHERE gruppo='$gruppo'");
	while ($r = $q->fetch_array()) {
		switch ($r['algoritmo']) {
			case 'RPL':  // rimpiazza secco
				if ($valore == $r['regola']) {
					$b['stat'] = trim($r['statistica']);
					$b['stat_fam'] = trim($r['statistica_fam']);
					$b['trovato'] = true;
					$b['idstatisticacampo'] = $r['idstatisticacampo'];
					$b['algoritmo'] = $r['algoritmo'];
					break 2;  /* Exit the switch and the while. */
				}
			break;
			case 'RGX':  // regular expression
				if (preg_match($r['regola'], $valore)) {
					$b['stat'] = trim($r['statistica']);
					$b['stat_fam'] = trim($r['statistica_fam']);
					$b['trovato'] = true;
					$b['idstatisticacampo'] = $r['idstatisticacampo'];
					$b['algoritmo'] = $r['algoritmo'];
					break 2;  /* Exit the switch and the while. */
				}
			break;
			case '|':  // contiene
			break;
		}
	}
	return $b;
}


/**
 * scantargetmx($idmxserver, idprobe)
 * 
 * Analizza un target MX
 *
 * 20211206 modifica gestione MX
 * 
 */
function scantargetmx($idmxserver, $idprobe = 0) {
	global $db, $b2;
	$retval = '';
	$r = $db->query("SELECT * FROM mxserver  WHERE idmxserver='$idmxserver'")->fetch_array();
	$a = array();
	// aggiorno l'ultima visita
	$a[] =	$b2->campoSQL("visited", time());
	$retval .= "\nAnalisi MX: $r[mxserver]";
	//	timeout
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>10, "usec"=>0));
	socket_set_option($socket,SOL_SOCKET, SO_SNDTIMEO, array("sec"=>10, "usec"=>0));
	if ($socket) {
		if (socket_connect($socket, $r['mxserver'], 25)) {
			$smtp_head = socket_read($socket, 2048);
			$a[] =	$b2->campoSQL("headerraw", $smtp_head, B2_NORM_SQL || B2_NORM_TRIM);
			// ESMPT
			if (strpos($smtp_head, 'ESMTP')) {
				$he = 'EHLO';
				$a[] =	$b2->campoSQL("isesmtp", 1);
			} else {
				$he = 'HELO';
				$a[] =	$b2->campoSQL("isesmtp", 0);
			}
			// vero server name
			$mxserver_real = $r['mxserver']; // abiamo un default
			if (strlen($smtp_head) > 10) { // se non e` una risposta valida nemmeno inizio
				// sistemo la risposta 220- anzich 220&spazio;
				$smtp_headmx = str_replace('220-', '220 ', $smtp_head);
				list($codice,$hostname,$nonserve) = explode(' ', $smtp_headmx, 3);
				// la bestia di stringa viene da https://stackoverflow.com/questions/3026957/how-to-validate-a-domain-name-using-regex-php
				// controllo se e` vuoto prima cosi` non invoco inutilmente i demoni delle regex
				if (!empty($hostname) and preg_match('^(?!\-)(?:(?:[a-zA-Z\d][a-zA-Z\d\-]{0,61})?[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$^', $hostname)) {
					$mxserver_real = strtolower($hostname);
				}
			}
			$a[] =	$b2->campoSQL("mxserver_real", $mxserver_real, B2_NORM_SQL || B2_NORM_TRIM);
			$s = statisticacampo($mxserver_real, 'MX');
			$a[] = $b2->campoSQL('mxserver_stat', $s['stat']);			
			socket_write($socket, "$he " . gethostname() . "\n");
			$smtp_cap = socket_read($socket, 2048);
			$a[] =	$b2->campoSQL("capabilitiesraw", $smtp_cap, B2_NORM_SQL || B2_NORM_TRIM);
			socket_close($socket);
		} else {
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			error_log("Errore socket_connect() su $r[mxserver]: [$errorcode] $errormsg");
			$a[] =	$b2->campoSQL("headerraw", "999 Errore socket_connect() su $r[mxserver]: [$errorcode] $errormsg", B2_NORM_SQL || B2_NORM_TRIM);
			$a[] =	$b2->campoSQL("isesmtp", 0);
			$a[] =	$b2->campoSQL("capabilitiesraw", $errormsg);
		}
	} else {
		$errorcode = socket_last_error();
		$errormsg = socket_strerror($errorcode);
		error_log("Impossibile creare il socket: [$errorcode] $errormsg");
		$a[] =	$b2->campoSQL("headerraw", "999 Errore socket_create() su $r[mxserver]: [$errorcode] $errormsg", B2_NORM_SQL || B2_NORM_TRIM);
		$a[] =	$b2->campoSQL("isesmtp", 0);
		$a[] =	$b2->campoSQL("capabilitiesraw", $errormsg);
	}
	$db->query("UPDATE mxserver SET " . implode(',', $a) . " WHERE idmxserver='$idmxserver'");
	$retval .= "\n";
	return $retval;	
}


/**
 * randomuseragent()
 * 
 * Ritorna uno user agent casuale
 * 
 */
function randomuseragent() {
	global $db,$b2;
	$r = $db->query("SELECT probeagent FROM probeagent ORDER BY RAND() LIMIT 1")->fetch_array();
	return ($r[0]);
}


/**
 * tempopassato()
 * 
 * Ritorna il tempo trascorso tra due timestamp
 * 
 */
function tempopassato($secondi, $short = false) {
	$retval = "";
  $s = $secondi % 60;
	$m = ($secondi / 60) % 60;
	$h = ($secondi / 3600) % 24;
	$g = floor($secondi / 86400);
	if ($short) {
		if ($g > 0)  $retval .= $g . 'g ';
		if ($h > 0)  $retval .= $h . 'h ';
		if ($m > 0)  $retval .= $m . 'm ';
		if ($s > 0)  $retval .= $s . 's ';
		
	} else {
		if ($g > 0)  $retval .= "$g giorni ";
		if ($h > 0)  $retval .= "$h ore ";
		if ($m > 0)  $retval .= "$m minuti ";
		if ($s > 0)  $retval .= "$s secondi ";
	}
	return ($retval);
}


/**
 * storicizza($data, $idcategory, $idcampostorico, $valoreint=0, $valorestr='')
 * 
 * Storicizza un campo
 *
 * 20211230 prima versione
 * 
 */
function storicizza($data, $idcategory, $idcampostorico, $valoreint=0, $valorestr='') {
	global $db,$b2;
	$a = array();
	$a[] =	$b2->campoSQL("data", $data);
	$a[] =	$b2->campoSQL("idcategory", $idcategory);
	$a[] =	$b2->campoSQL("idcampostorico", $idcampostorico);
	$a[] =	$b2->campoSQL("valoreint", $valoreint);
	$a[] =	$b2->campoSQL("valorestr", $valorestr, B2_NORM_SQL || B2_NORM_TRIM);
	$db->query("INSERT INTO storico SET " . implode(',', $a));
}


/**
 * getipid($ip)
 * 
 * Ritorna l'id di un IP nella tabella degli IP
 *
 * 20211231 prima versione
 * 
 */
function getipid($ip) {
	global $db,$b2;
	$retval = 0;
	$q = $db->query("SELECT idip FROM ip WHERE ip='" . $b2->normalizza($ip) ."'");
	if ($q->num_rows > 0) {
		$r = $q->fetch_array();
		$retval = $r['idip'];
		// aggiorno i dati, semmai siano cambiati
		$aipinfo = getipgeo($ip);
		$a = array();
		if (isset($aipinfo['continent']))   $a[] = $b2->campoSQL("continent",   $aipinfo['continent']);
		if (isset($aipinfo['country']))     $a[] = $b2->campoSQL("country",     $aipinfo['country']);
		if (isset($aipinfo['countrycode'])) $a[] = $b2->campoSQL("countrycode", $aipinfo['countrycode']);
		if (isset($aipinfo['isp']))         $a[] = $b2->campoSQL("isp",         $aipinfo['isp']);
		if (isset($aipinfo['org']))         $a[] = $b2->campoSQL("org",         $aipinfo['org']);
		if (isset($aipinfo['as']))          $a[] = $b2->campoSQL("as",          $aipinfo['as']);
		if (isset($aipinfo['asowner']))     $a[] = $b2->campoSQL("asowner",     $aipinfo['asowner']);
		if (isset($aipinfo['asname']))      $a[] = $b2->campoSQL("asname",      $aipinfo['asname']);
		if (isset($aipinfo['ishosting']))   $a[] = $b2->campoSQL("ishosting",   $aipinfo['ishosting']);
		if (isset($aipinfo['reverse']))     $a[] = $b2->campoSQL("reverse",     $aipinfo['reverse']);
		$db->query("UPDATE ip SET " . implode(',', $a) . "WHERE idip='$r[idip]'");
	} else { // tocca andarlo a cercare
		$aipinfo = getipgeo($ip);
		if (!empty($aipinfo)) {
			$a = array();
			$a[] = $b2->campoSQL("ip", $ip);
			if (isset($aipinfo['continent']))   $a[] = $b2->campoSQL("continent",   $aipinfo['continent']);
			if (isset($aipinfo['country']))     $a[] = $b2->campoSQL("country",     $aipinfo['country']);
			if (isset($aipinfo['countrycode'])) $a[] = $b2->campoSQL("countrycode", $aipinfo['countrycode']);
			if (isset($aipinfo['isp']))         $a[] = $b2->campoSQL("isp",         $aipinfo['isp']);
			if (isset($aipinfo['org']))         $a[] = $b2->campoSQL("org",         $aipinfo['org']);
			if (isset($aipinfo['as']))          $a[] = $b2->campoSQL("as",          $aipinfo['as']);
			if (isset($aipinfo['asowner']))     $a[] = $b2->campoSQL("asowner",     $aipinfo['asowner']);
			if (isset($aipinfo['asname']))      $a[] = $b2->campoSQL("asname",      $aipinfo['asname']);
			if (isset($aipinfo['ishosting']))   $a[] = $b2->campoSQL("ishosting",   $aipinfo['ishosting']);
			if (isset($aipinfo['reverse']))     $a[] = $b2->campoSQL("reverse",     $aipinfo['reverse']);
			$db->query("INSERT INTO ip SET " . implode(',', $a));
			$retval = $db->insert_id;
		}
	}
	return($retval);
}


// ### END OF FILE ###