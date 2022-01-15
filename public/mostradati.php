<?php

/**
 *
 * SESAE - Pagina principale
 *
 * @package     SESAE
 * @subpackage  SESAE Web
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2019-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 *
 * 20190504 prima versione
 * 20211204 cambio licenza per pubblicazione sorgenti
 * 20211206 aggiunta DNS, MX
 * 20211211 nocache, content type, hash ssl, organizzzione ssl
 * 20220109 cambio da home a pagina di mostra dati
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

define('SESAE', 1);

require('global.php');

$displagraph = false;
$idcategory = 0;
if (isset($_GET['c']) and is_numeric($_GET['c'])) $idcategory = $b2->normalizza($_GET['c'], B2_NORM_TRIM);
$idcategoryg = 0;
if (isset($_GET['g']) and is_numeric($_GET['g'])) {
	$idcategoryg = $b2->normalizza($_GET['g'], B2_NORM_TRIM);
	$displagraph = true;
	$wha = " AND idcategory='$idcategoryg'";
}

$nocache='';
if (isset($_GET['nocache'])) $nocache = 'nocache';

?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/>
		<link rel='stylesheet' type='text/css' href='https://fonts.googleapis.com/css?family=Open+Sans|Open+Sans:b' />
		<link rel='stylesheet' href='static/mostradati.css' type='text/css'/>
		<meta http-equiv='X-UA-Compatible' content='IE=edge'/>
		<meta name='viewport' content='width=device-width, initial-scale=1'/>
		<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
		<script src='static/chart.min.js'></script>
		<title>SESAE</title>
	</head>
	<body>
		<h1><a href="index.html">SESAE</a></h1>
		<div class="contenitorecolonne">
			<div class="colindice">
				<p><b>Dati numerici</b></p>
				<div class='linavigatore'><a href='mostradati.php' class='linavigatore'>Tutto</a></div>
				<?php
					$q = $db->query("SELECT idcategory,category FROM category WHERE enabled='1' ORDER BY weight");
					while ($r = $q->fetch_array()) {
						echo  "\n<div class='linavigatore'><a href='mostradati.php?c=$r[idcategory]' class='linavigatore'>$r[category]</a></div>";
					}
				?>
				<p><b>Grafici</b></p>
				<div class='linavigatore'><a href='mostradati.php?g=0' class='linavigatore'>Tutto</a></div>
				<?php
					$q = $db->query("SELECT idcategory,category FROM category WHERE enabled='1' ORDER BY weight");
					while ($r = $q->fetch_array()) {
						echo  "\n<div class='linavigatore'><a href='mostradati.php?g=$r[idcategory]' class='linavigatore'>$r[category]</a></div>";
					}
				?>
			</div>
			<div class="colcontenuto">
				<?php
					if ($displagraph) {
						echo "\n<div class='contenitoregrafico'>";
						echo "\n<div class='titolografico'></div>";
						echo "
						<div class='grafico'><canvas id='chNumeroSiti' width='100' height='50'></canvas></div>
						<script>
							const ctx = document.getElementById('chNumeroSiti');
							const myChart = new Chart(ctx, {
								type: 'line',
								data: {";
						echo "\nlabels: [";
						$a = array();
						$q = $db->query("SELECT `data` FROM `storico` WHERE idcampostorico=1 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = "'". date('j/n/Y', strtotime($r['data'])) . "'";
						echo (implode(',', $a));
						echo "],";
						echo "\n datasets: [{
            label: 'Numero di siti',";
						echo "\n data: [";
						$a = array();
						$q = $db->query("SELECT `valoreint` FROM `storico` WHERE idcampostorico=1 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = $r['valoreint'];
						echo (implode(',', $a));
						echo "],";
						echo "\nfill: false,
						        borderColor: 'rgb(75, 192, 192)',
						        borderJoinStyle: 'miter',
						        tension: 0.1
						          }]
						       },
						       options: {
						       	legend: {
						       		display: false
						       	},
						       	scales: {
						       		xAxes: [
						       			{
						       				ticks: {
						       					autoSkip: false,
						       					maxRotation: 90,
						       					minRotation: 00
						       				}
						       			}
						       		]
						       	}
						       }
						       });
					   	    </script>";
						echo "\n</div>"; // class='contenitoregrafico'>
						
					} else {
						echo cache_dati(CH_STATGEN .     '-' . $idcategory, $nocache);
						echo cache_dati(CH_COUNTRYIPV4 . '-' . $idcategory, $nocache);
						echo cache_dati(CH_COUNTRYIPV6 . '-' . $idcategory, $nocache);
						echo cache_dati(CH_ASIPV4 .      '-' . $idcategory, $nocache);
						echo cache_dati(CH_ASIPV6 .      '-' . $idcategory, $nocache);
						echo cache_dati(CH_POWEREDBY .   '-' . $idcategory, $nocache);
						echo cache_dati(CH_CONTTYPE .    '-' . $idcategory, $nocache);
						echo cache_dati(CH_HTTPSERVER .  '-' . $idcategory, $nocache);
						echo cache_dati(CH_GENERATOR .   '-' . $idcategory, $nocache);
						echo cache_dati(CH_DNS .         '-' . $idcategory, $nocache);
						echo cache_dati(CH_MX .          '-' . $idcategory, $nocache);
						echo cache_dati(CH_SSLISSUER .   '-' . $idcategory, $nocache);
						echo cache_dati(CH_SSLHASH .     '-' . $idcategory, $nocache);
					}
				?>
			</div>
		</div>
	</body>
 
</html>
