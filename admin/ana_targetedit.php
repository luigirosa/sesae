<?php

/**
 *
 * SESAE - Admin - Anarafica target, edit
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2018-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * 
 * 20180217 prima versione
 * 20181007 campo visited
 * 20181013 reset campo visited
 * 20181223 idprobe nel log 
 * 20200423 rimozione _fam
 * 20200614 basta log
 * 20211204 cambio licenza per pubblicazione sorgenti
 * 20211227 aggiunta campo id univoco esterno
 * 20211231 nuova gestione IP
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

function displaymeta($idtarget) {
	global $db, $b2;
	$b = '';
	$q = $db->query("SELECT raw
	                 FROM meta
	                 WHERE meta.idtarget='$idtarget'");
	if ($q->num_rows > 0) {
		$b2->bgcolor(true);
		$b .= "\n<table border='0' align='center'>";
		$b .= $b2->intestazioneTabella(array('Meta'));
		while ($r = $q->fetch_array()) {
			$bg = $b2->bgcolor();
			$b .= "\n<tr $bg>";
			$b .= "<td align='left'>&nbsp;" . htmlentities($r['raw'])  . "&nbsp;</td>";
			$b .= "</tr>";
		}
		$b .= "\n</table>";
	}
	return $b;
}

// ajax
if (isset($_POST['dispatch']) and  'url' == $_POST['dispatch']) {
	$idtarget = $b2->normalizza($_POST['idtarget']);
	$url = $b2->normalizza($_POST['url']);
	$q = $db->query("SELECT idtarget,description FROM target WHERE idtarget<>'$idtarget' AND url='$url'");
	if ($q->num_rows > 0) {
		$r = $q->fetch_array();
		echo date("G:i:s") . "<b>*** <a href='ana_targetedit.php?idtarget=$r[idtarget]' target='_blank' >$r[description]</a></b>";
	} else {
		echo date("G:i:s");
	}
	die();
}

// cancellazione rapida per i siti catturati	
if (isset($_GET['idtarget']) and is_numeric($_GET['idtarget']) and isset($_GET['exterminate'])) {	
	$idtarget = $b2->normalizza($_GET['idtarget']);	
	$db->query("DELETE FROM target WHERE idtarget='$idtarget'");	
	if (2 == $_GET['exterminate']) {	
		echo "<script>window.close();</script>";
	} else {	
		header('Location: home.php');	
	}	
	die();	
}	

$_SESSION['retscan'] = '';

// di che target stiamo parlando?
$idtarget = 0;
if (isset($_GET['idtarget']) and is_numeric($_GET['idtarget'])) {
	$idtarget = $_GET['idtarget'];
}
if (isset($_POST['idtarget']) and is_numeric($_POST['idtarget'])) {
	$idtarget = $_POST['idtarget'];
}

if (isset($_POST['idtarget'])) {
	$nonmorire = false;   // flag che fa un redirect al posto di chiudere la finestra se si sta agiungendo un record
	if (isset($_POST['xxx1']) and isset($_POST['xxx2'])) {
		cleantarget($idtarget);
		$db->query("DELETE FROM target WHERE idtarget='$idtarget'");
		echo "<script>window.close();</script>";
//		header('Location: ana_target.php');
		die();
	} else {
		// ripulisco la descrizione
		$desc = trim($_POST['description']);
		$desc = str_replace('"', ' ', $desc );
		$desc = preg_replace('#\s+#', ' ', $desc);
		$a = array();
		$a[] = $b2->campoSQL("checked", 0);
		$a[] = $b2->campoSQL("enabled", isset($_POST['enabled']) ? 1 : 0);
		$a[] = $b2->campoSQL("idcategory", $_POST['idcategory']);
		$a[] = $b2->campoSQL("description", $desc);
		$a[] = $b2->campoSQL("mailhost", $_POST['mailhost']);
		$a[] = $b2->campoSQL("external_id", $_POST['external_id']);
		if (isset($_POST['resetvisita'])) {
			$a[] = $b2->campoSQL("visited", rand(1,20000));
		}
		if (isset($_POST['resetcounters'])) {
			$a[] = $b2->campoSQL("lastprobe", 0);
			$a[] = $b2->campoSQL("counter", 0);
		}
		if ('' != trim($_POST['description']) and '' != trim($_POST['url'])) {
			if (0 == $idtarget) {
				$nonmorire = true;
				$db->query("INSERT INTO target SET " . implode(',', $a));
				$idtarget = $db->insert_id;
				// per pulire il record alla prima visita
				$db->query("UPDATE target SET visited='" . rand(1,20000) . "',mailhost='__new__' WHERE idtarget='$idtarget'");
				// creo i record vuoti
				$db->query("INSERT INTO targetdata SET idtarget='$idtarget'");
				$db->query("INSERT INTO targetraw SET idtarget='$idtarget'");
			} else {
				$db->query("UPDATE target SET " . implode(',', $a) . " WHERE idtarget='$idtarget'");
			}
		}
		// probe
		$db->query("DELETE FROM targetprobe WHERE idtarget='$idtarget'");
		$qx = $db->query("SELECT idprobe,probe FROM probe WHERE isadmin<>1 ORDER BY probe");
		while ($rx = $qx->fetch_array()) {
			if (isset($_POST["probe-$rx[idprobe]"])) $db->query("INSERT INTO targetprobe SET idtarget='$idtarget', idprobe='$rx[idprobe]'");
		}
		// ricalcolo hostname
		if ($idtarget > 0) {
			$r = $db->query("SELECT url FROM target WHERE idtarget='$idtarget'")->fetch_array();
			$hostname = parse_url($r['url'], PHP_URL_HOST);
			$q = $db->query("SELECT idtarget FROM targetdata WHERE idtarget='$idtarget'");
			if ($q->num_rows > 0) {
				$db->query("UPDATE targetdata SET hostname='" . $b2->normalizza($hostname) . "' WHERE idtarget='$idtarget'");	
			} else {
				$db->query("INSERT INTO targetdata SET hostname='" . $b2->normalizza($hostname) . "',idtarget='$idtarget'");	
			}
		}
		// qui in fondo perche' prima potrebbe non esistere il record di targetdata
		aggiornacampo($idtarget, 'url', $_POST['url']);
	}
	if (isset($_POST['scantarget'])) {
		$_SESSION['retscan'] = scantarget($idtarget, 4);
	}
	if ($nonmorire) {
		header('Location: ana_target.php');
	} else {
		echo "<script>window.close();</script>";	
	}
	die();
}

if (0 == $idtarget) {
	$intestazione = "Aggiunta di un nuovo target";
	$r['idtarget'] = 0;
	$r['idcategory'] = 0;
	$r['enabled'] = '1';
	$r['description'] = '';
	$r['url'] = '';
	$r['visited'] = rand(1,20000);
	$r['lastprobe'] = 0;
	$r['mailhost'] = '';
	$r['external_id'] = '';
} else {
	$r = $db->query("SELECT * FROM target WHERE idtarget='$idtarget'")->fetch_array();
	$intestazione = "Modifica $r[description]";
}

intestazione($intestazione);

echo "\n<form action='ana_targetedit.php' method='post'>";
echo $b2->inputHidden('idtarget', $r['idtarget']);

$aprobe = $b2->creaArraySelect("SELECT idprobe,probe FROM probe");
$aprobe[0] = 'Qualsiasi';

echo "\n<table border='0' align='center'>";
echo $b2->rigaEdit('Attivo', $b2->inputCheck('enabled', $r['enabled'] == '1'));
echo $b2->rigaEdit('Categoria', $b2->inputSelect('idcategory', $b2->creaArraySelect("SELECT idcategory,category FROM category ORDER BY category"), $r['idcategory']));
echo $b2->rigaEdit('Descrizione', $b2->inputText('description', $r['description'], 100, 250) . " <input type='button' id='btnfixcase' value='Correggi masiuscolo'>");
echo $b2->rigaEdit('URL', $b2->inputText('url', $r['url'], 100, 250) . "<br/><span class='err' id='errurl' name='errurl'>" . date("G:i:s") . "</span>");
echo $b2->rigaEdit('Mail host', $b2->inputText('mailhost', $r['mailhost'], 100, 250, 'mailhost', "autocomplete='off'"));
echo $b2->rigaEdit('ID esterno', $b2->inputText('external_id', $r['external_id'], 50, 50) . " es. codice Belfiore per i Comuni");
$probe = '';
$qx = $db->query("SELECT idprobe,probe FROM probe WHERE isadmin<>1 ORDER BY probe");
while ($rx = $qx->fetch_array()) {
	$ck = $r['idtarget'] == 0 ? true : isprobe($r['idtarget'], $rx['idprobe']);
	$probe .= "<label>$rx[probe]:" . $b2->inputCheck("probe-$rx[idprobe]", $ck) . "</label>&nbsp;&nbsp;";
}
echo $b2->rigaEdit('Probe', $probe);
echo $b2->rigaEdit('Test', "<a href='$r[url]' target='_blank'>$r[url]</a>");
if ($r['idcategory'] == 4) { // comune
	echo $b2->rigaEdit('Google', "<a href='https://www.google.com/search?q=" . urlencode("comune di " . $r['description']) . "' target='_blank'>Cerca su Goooooogle</a>");
	echo $b2->rigaEdit('Tuttitalia', "<a href='https://www.tuttitalia.it/search?q=" . urlencode($r['description']) . "' target='_blank'>Cerca su Tuttitalia</a>");
} elseif  ($r['idcategory'] == 3) { // provincia
	echo $b2->rigaEdit('Google', "<a href='https://www.google.com/search?q=" . urlencode("provincia di " . $r['description']) . "' target='_blank'>Cerca su Goooooogle</a>");
	echo $b2->rigaEdit('Tuttitalia', "<a href='https://www.tuttitalia.it/search?q=" . urlencode($r['description']) . "' target='_blank'>Cerca su Tuttitalia</a>");
} elseif  ($r['idcategory'] == 2) { // regione
	echo $b2->rigaEdit('Google', "<a href='https://www.google.com/search?q=" . urlencode("regione " . $r['description']) . "' target='_blank'>Cerca su Goooooogle</a>");
	echo $b2->rigaEdit('Tuttitalia', "<a href='https://www.tuttitalia.it/search?q=" . urlencode($r['description']) . "' target='_blank'>Cerca su Tuttitalia</a>");
} else {
	echo $b2->rigaEdit('Google', "<a href='https://www.google.com/search?q=" . urlencode($r['description']) . "' target='_blank'>Cerca su Goooooogle</a>");
}
// ultima visita
if ($r['lastprobe'] > 0) {
	$rr = $db->query("SELECT probe FROM probe WHERE idprobe='$r[lastprobe]'")->fetch_array();
	$zz = " da parte di $rr[probe].";
} else {
	$zz='';
}
echo $b2->rigaEdit('Ultima visita', date("j/n/Y G:i", $r['visited']) . $zz . "  dalla visita precedente: ". tempopassato($r['visited'] - $r['visited_before']));
if ($r['idtarget'] > 0 ) echo $b2->rigaEdit('Visite:', number_format($r['counter'], 0, ',', '.'));
echo $b2->rigaEdit('Rianalizza il target', $b2->inputCheck('scantarget', false));
if ($idtarget > 0) echo $b2->rigaEdit('Reset visita', $b2->inputCheck('resetvisita', true));
if ($idtarget > 0) echo $b2->rigaEdit('Reset contatori', $b2->inputCheck('resetcounters', false));
if ($idtarget > 0) echo $b2->rigaEdit('Cancella', $b2->inputCheck('xxx1', false) . $b2->inputCheck('xxx2', false));
echo "\n<tr><td align='center' colspan='2'><input type='submit' value='-------- Aggiorna --------'></td></tr>";
echo "\n</table>";
// per la copia
echo "\n</form>";

if ($idtarget > 0) {
	// dati
	$rt = $r; // e' l'array di target, majalata, ma faccio prima che non ho voglia di cambiare il codice
	$r = $db->query("SELECT * FROM targetdata WHERE idtarget='$idtarget'")->fetch_array();
	$rr = $db->query("SELECT * FROM targetraw WHERE idtarget='$idtarget'")->fetch_array();
	$rs = $db->query("SELECT * FROM http_server WHERE idtarget='$idtarget'")->fetch_array();
	echo "\n" . $b2->inputhidden('htmltitle',  $r['html_title']);
	echo "\n<table border='0' align='center'>";
	echo "\n<tr><td align='center' colspan='2'><input type='button' id='btnreset' value='Reset'> <input type='button' id='btnnovisit' value='Non rivisitare'></td></tr>";
	echo $b2->rigaEdit('HTTP code', $r['http_code']);
	if ('' != trim($r['http_location']))  echo $b2->rigaEdit('HTTP location', $r['http_location'] . " <input type='button' id='btncopy' value='Copia'>");
	// dnsauth
	$qx = $db->query("SELECT * FROM dnsauth WHERE idtarget='$idtarget' ORDER BY dnsauth");
	if ($qx->num_rows > 0) {
		$ax = array();
		while ($rx = $qx->fetch_array()) {
			$ax[] = "$rx[dnsauth] ($rx[dnsauth_stat])" ;
		}
		echo $b2->rigaEdit('DNS autoritativi', implode('<br/>', $ax), B2_ED_VTOP);
	}
	// mx
	$qx = $db->query("SELECT targetmx.peso,mxserver.mxserver,mxserver.mxserver_stat 
	                  FROM targetmx 
	                  JOIN mxserver ON targetmx.idmxserver=mxserver.idmxserver
	                  WHERE targetmx.idtarget='$idtarget' 
	                  ORDER BY targetmx.peso");
	if ($qx->num_rows > 0) {
		$ax = array();
		while ($rx = $qx->fetch_array()) {
			$ax[] = "$rx[peso] $rx[mxserver] ($rx[mxserver_stat])";
		}
		echo $b2->rigaEdit('MX', implode('<br/>', $ax), B2_ED_VTOP);
	}
	if ('' != trim($r['html_title'])) echo $b2->rigaEdit('Title', $r['html_title'] . " <input type='button' id='btntitlecopy' value='Copia'>");
	if ('' != trim($r['goog_analytics'])) echo $b2->rigaEdit('Google Analytics', $r['goog_analytics']);
	if ('' != trim($r['goog_asy'])) echo $b2->rigaEdit('Google Analytics Asy', $r['goog_asy']);
	if ('' != trim($r['goog_tag'])) echo $b2->rigaEdit('Google Tag Manager', $r['goog_tag']);
	if ('' != trim($r['http_contenttype'])) echo $b2->rigaEdit('HTTP content type', $r['http_contenttype']);
	if (isset($rs) and '' != trim($rs['http_server'])) echo $b2->rigaEdit('HTTP server', "$rs[http_server] ($rs[http_server_stat])");
	// powered by
	$qp = $db->query("SELECT * FROM poweredby WHERE idtarget='$idtarget'");
	if ($qp->num_rows > 0) {
		$rp = $qp->fetch_array();
		echo $b2->rigaEdit('Powered by', "$rp[poweredby] | $rp[poweredby_stat] | $rp[poweredby_stat_fam]");
	}
	// generator
	$qx = $db->query("SELECT * FROM http_generator WHERE idtarget='$idtarget'");
	if ($qx->num_rows > 0) {
		$ax = array();
		while ($rx = $qx->fetch_array()) {
			$ax[] = "$rx[http_generator] ($rx[http_generator_stat])"; 
		}
		echo $b2->rigaEdit('HTTP Generator', implode('<br/>', $ax), B2_ED_VTOP);
	}
	// https
	if ($r['ishttps'] > 0) {
		echo $b2->rigaEdit('HTTPS', $r['ishttps']);
		echo $b2->rigaEdit('Nome certificato', $r['https_certname']);
		echo $b2->rigaEdit('Hostname certificato', $r['https_subject']);
		echo $b2->rigaEdit('Emittente certificato', $r['https_issuer']);
		echo $b2->rigaEdit('Organizzazione emittente certificato', $r['https_issuerorg']);
		echo $b2->rigaEdit('Scadenza certificato', date('j/n/Y h:i', $r['https_validto']));
		echo $b2->rigaEdit('Firma certificato', $r['https_signature']);
	}
	echo "\n</table>";
	// IP
	echo "\n<table border='0' align='center'>";
	echo "\n<tr><td>&nbsp;</td><td align='center'><b>IPv4</b></td><td align='center'><b>IPv6</b></td></tr>";
	if ($rt['idipv4'] > 0) {
		$ripv4 = $db->query("SELECT * FROM ip WHERE idip='$rt[idipv4]'")->fetch_array();
	} else {
		$ripv4 = array();
	}
	if ($rt['idipv6'] > 0) {
		$ripv6 = $db->query("SELECT * FROM ip WHERE idip='$rt[idipv6]'")->fetch_array();
	} else {
		$ripv6 = array();
	}
	$ishosting4 = $ripv4['ishosting'] == 1 ? 'S&igrave;' : 'No';
	$ishosting6 = $ripv6['ishosting'] == 1 ? 'S&igrave;' : 'No';
	echo "\n<tr><td align='left'><b>IP</b></td><td align='left'>$ripv4[ip]</td><td align='left'>$ripv6[ip]</td></tr>";
	echo "\n<tr><td align='left'><b>Host</b></td><td align='left'>$r[ipv4host]</td><td align='left'>$r[ipv6host]</td></tr>";
	echo "\n<tr><td align='left'><b>Reverse</b></td><td align='left'>$ripv4[reverse]</td><td align='left'>$ripv6[reverse]</td></tr>";
	echo "\n<tr><td align='left'><b>Country</b></td><td align='left'>$ripv4[countrycode] $ripv4[country] $ripv4[continent]</td><td align='left'>$ripv6[countrycode] $ripv6[country] $ripv6[continent]</td></tr>";
	echo "\n<tr><td align='left'><b>Organizzazione</b></td><td align='left'>$ripv4[org]</td><td align='left'>$ripv6[org]</td></tr>";
	echo "\n<tr><td align='left'><b>ISP</b></td><td align='left'>$ripv4[isp]</td><td align='left'>$ripv6[isp]</td></tr>";
	echo "\n<tr><td align='left'><b>AS</b></td><td align='left'>$ripv4[as] $ripv4[asname] $ripv4[asowner]</td><td align='left'>$ripv6[as] $ripv6[asname] $ripv6[asowner]</td></tr>";
	echo "\n<tr><td align='left'><b>IP in hosting?</b></td><td align='left'>$ishosting4</td><td align='left'>$ishosting6</td></tr>";
	echo "\n</table>";
	// campo hidden per la copia del valore
	echo "\n" . $b2->inputhidden('redirected',  $r['http_location']);
	// cookies
	$b2->bgcolor(true);
	if (trim($rr['cookies']) != '') {
		$b2->bgcolor(true);
		echo "\n<table border='0' align='center'><tr>";
		echo "\n<td align='left' valign='top'><b>Cookies</b></td>";
		echo "\n<td align='left'>";
		echo "<table border='0' align='left'>";
		echo $b2->intestazioneTabella(array('domain', 'tailmatch', 'path', 'secure', 'expires', 'name', 'value' ));
		$arighe = explode("\n", $rr['cookies']);
		foreach ($arighe as $riga) {
			if (trim($riga != '') and substr($riga, 0, 1) != '#') {
				$bg = $b2->bgcolor();
				echo "<tr $bg>";
				list($xdomain,$xtailmatch,$xpath,$xsecure,$xexpires,$xname,$xvalue) = explode("\t", $riga);
				echo "<td align='left'>$xdomain</td>";
				echo "<td align='center'>$xtailmatch</td>";
				echo "<td align='left'>$xpath</td>";
				echo "<td align='center'>$xsecure</td>";
				echo "<td align='right'>" . date('d/m/Y h:i', $xexpires) . "</td>";
				echo "<td align='left'>$xname</td>";
				echo "<td align='left'>$xvalue</td>";
				echo "</tr>";
			}
		}
		echo "\n</table>";
		echo "</td>";
		echo "\n</tr></table>";
	}
	// meta
	echo "<div id='metadiv'>" . displaymeta($idtarget) . "</div>";
	// http_header
	$qx = $db->query("SELECT * FROM http_header WHERE idtarget='$idtarget'");
	if ($qx->num_rows > 0) {
		$b2->bgcolor(true);
		echo "\n<div id='http_headerdiv'><table border='0' align='center'>";
		echo "\n<tr><td align='center' valign='top'><b>Header HTTP</b></td></tr>";
		while ($rx = $qx->fetch_array()) {
			$bg = $b2->bgcolor();
			echo "\n<tr $bg><td align='left'>$rx[http_header]</td></tr>";
		}
		echo "\n</table></div>";
	}
	// robots
	if (trim($rr['robots']) != '') {
		echo "\n<table border='0' align='center'><tr>";
		echo "\n<td align='left' valign='top'><b>robots.txt</b></td>";
		echo "\n<td align='left'>" . str_replace("\n","<br/>",strip_tags($rr['robots'])) . "</td>";
		echo "\n</tr></table>";
	}
}

echo "\n<p>&nbsp;</p>";

?>
<script>
	$(document).ready(function() {
		$('#btncopy').click(function() {
			$('#url').val($('#redirected').val());
			$('#mailhost').val('');
			$('#resetvisita').prop( "checked", true );
			/* $('#scantarget').prop( "checked", true ); */
			$('#url').trigger("change");
		});
		$('#btnnovisit').click(function() {
			$('#resetvisita').prop( "checked", false );
			$('#scantarget').prop( "checked", false );
		});
		$('#btnfixcase').click(function() {
			var oldvalue = $("#description").val();
			oldvalue = oldvalue.toLowerCase();
			newvalue = oldvalue.replace(/(\b)([a-zA-Z])/g,
           function(firstLetter){
              return   firstLetter.toUpperCase();
           });
			$('#description').val(newvalue);
		});
		$('#btntitlecopy').click(function() {
			$('#description').val($('#htmltitle').val().trim());
			$('#resetvisita').prop( "checked", false );
			$('#scantarget').prop( "checked", false );
			$('#description').trigger("change");
		});
		$('#btneducopy').click(function() {
			$('#url').val($('#conedu').val().trim());
			$('#mailhost').val('');
			$('#resetvisita').prop( "checked", true );
			$('#url').trigger("change");
		});
		$('#btnreset').click(function() {
			$('#mailhost').val('__new__');
		});
		
  	// cambio url
  	$("#url").change(function(){
			$('#mailhost').val('');
	    var url = $("#url").val();
	    var idtarget = $("#idtarget").val();
			$.post("ana_targetedit.php", 
				{dispatch: "url", url: url, idtarget: idtarget})
				.done(function( data ) {
					$("#errurl").html(data);
  			})  	
  	});
	});	
</script>

<?php

piede();

// ### END OF FILE ###
