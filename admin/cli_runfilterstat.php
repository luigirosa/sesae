<?php

/**
 * SESAE - Rilancia i filtri di normalizzazione delle statistiche
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2020-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 *
 * 20200121 prima versione
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

echo "\nSESAE - Applicazione forzata dei filtri di normalizzazione delle statistiche";

// http_generator
echo "\nHTTP generator: ";
$count = 0;
$q = $db->query("SELECT idhttp_generator,http_generator FROM http_generator");
while ($r = $q->fetch_array()) {
	$s = array();
	$s = statisticacampo($r['http_generator'], 'GNR');
	$a = array();
	$a[] = $b2->campoSQL("http_generator_stat", $s['stat']);
	$a[] = $b2->campoSQL("http_generator_stat_fam", $s['stat_fam']);
	$db->query("UPDATE http_generator SET " . implode(',', $a) . "WHERE idhttp_generator='$r[idhttp_generator]'");
	$count++;
	if (0 == ($count % 100)) echo "$count ";
}
echo "fatto.\n";

// dnsauth
echo "\nMX: ";
$count = 0;
$q = $db->query("SELECT idmx,mx FROM mx");
while ($r = $q->fetch_array()) {
	$s = array();
	$s = statisticacampo($r['mx'], 'MX');
	$a = array();
	$a[] = $b2->campoSQL("mx_stat", $s['stat']);
	$db->query("UPDATE mx SET " . implode(',', $a) . "WHERE idmx='$r[idmx]'");
	$count++;
	if (0 == ($count % 100)) echo "$count ";
}
echo "fatto.\n";

// dnsauth
echo "\nDNS auth: ";
$count = 0;
$q = $db->query("SELECT iddnsauth,dnsauth FROM dnsauth");
while ($r = $q->fetch_array()) {
	$s = array();
	$s = statisticacampo($r['dnsauth'], 'DNS');
	$a = array();
	$a[] = $b2->campoSQL("dnsauth_stat", $s['stat']);
	$db->query("UPDATE dnsauth SET " . implode(',', $a) . "WHERE iddnsauth='$r[iddnsauth]'");
	$count++;
	if (0 == ($count % 100)) echo "$count ";
}
echo "fatto.\n";

// http_server
echo "\nHTTP server: ";
$count = 0;
$q = $db->query("SELECT idhttp_server,http_server FROM http_server");
while ($r = $q->fetch_array()) {
	$s = array();
	$s = statisticacampo($r['http_server'], 'HTS');
	$a = array();
	$a[] = $b2->campoSQL("http_server_stat", $s['stat']);
	$a[] = $b2->campoSQL("http_server_stat_fam", $s['stat_fam']);
	$db->query("UPDATE http_server SET " . implode(',', $a) . "WHERE idhttp_server='$r[idhttp_server]'");
	$count++;
	if (0 == ($count % 100)) echo "$count ";
}
echo "fatto.\n";

echo "\n";

// ### END OF FILE ###