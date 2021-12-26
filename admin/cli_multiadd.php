<?php

/**
 *
 * SESAE - Admin - Anarafica target, aggiunta multipla da command line
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2019-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 *
 * 20190210 prima versione
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

set_time_limit (1000);

define('SESAE', true);
define('SKIPCHECK', true);
require('global.php');

$handle = fopen("siti.txt", "r");
if ($handle) {
	while (($line = fgets($handle)) !== false) {
		$line = trim($line);
		if (strpos($line, '|') !== false) {
			list($idcategoria,$nome,$url) = explode('|', $line);
			$url = trim(strtolower($url));
			$nome = trim($nome);
			$nome = preg_replace('#\s+#', ' ', $nome);
			$qq = $db->query("SELECT description FROM target WHERE url='" . $b2->normalizza($url) . "'");
			if ($qq->num_rows == 0) {
				$hostname = calcolahostname($url);
				$ip = trim(`/usr/bin/dig $hostname A +short`);
				if ($ip != '' ) {
					// ripulisco la descrizione
					$a = array();
					$a[] = $b2->campoSql('idcategory', $idcategoria, B2_NORM_TRIM || B2_NORM_SQL);
					$a[] = $b2->campoSql('description', $nome);
					$a[] = $b2->campoSql('url', $url);
					$a[] = $b2->campoSql('mailhost', '__new__');
					$db->query("INSERT INTO target SET " . implode(',', $a));
					$idtarget = $db->insert_id;
					// creo i record vuoti
					$db->query("INSERT INTO targetdata SET idtarget='$idtarget'");
					$db->query("INSERT INTO targetraw SET idtarget='$idtarget'");			
					// popolo le sonde
					$q = $db->query("SELECT idprobe FROM probe WHERE isadmin='0'");
					while ($r = $q->fetch_array()) {
						$db->query("INSERT INTO targetprobe SET idtarget='$idtarget', idprobe='$r[idprobe]'");
					}
					echo "\n+++ $nome ($url) aggiunto";
				} else {
					echo "\nNNN $nome ($url) host |$hostname| not found";
				}
			} else {
				echo "\nEEE $nome ($url) esiste gia`";
			}
		} else {
			echo "\nXXX Riga $line non valida";
		}
	}
	fclose($handle);
} else {
  echo "\nErrore nell'apertura di siti.txt\n";
} 
echo "\n";

// ### END OF FILE ###