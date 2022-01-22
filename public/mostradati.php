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
				<p><b>Grafici giornalieri</b></p>
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
						echo "\n<div class='titolografico'></div>";
						// numero siti
						echo "\n<div class='contenitoregrafico'>";
						echo "
						<div class='grafico' style='position: relative; height:200px'><canvas id='chNumeroSiti'></canvas></div>
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
						        borderColor: 'rgba(75, 192, 192, 0.8)',
						        borderJoinStyle: 'miter',
						        tension: 0.1
						          }]   },
						        options: {
						          maintainAspectRatio: false,
									    plugins: {
									      title: { display: true, text: 'Numero di siti analizzati' },
 												legend: {display: false, },
									    },
									  scales: {
									    xAxes: [ { ticks: {autoSkip: false, maxRotation: 90, minRotation: 00 } } ]
						       	 }
						       }  });
					   	    </script>";
						echo "\n</div>"; // class='contenitoregrafico'>
						// numero siti in https
						echo "\n<div class='contenitoregrafico'>";
						echo "
						<div class='grafico' style='position: relative; height:200px'><canvas id='chNumeroSitiHttps'></canvas></div>
						<script>
							const ctx_https = document.getElementById('chNumeroSitiHttps');
							const myChart_https = new Chart(ctx_https, {
								type: 'line',
								data: {";
						echo "\nlabels: [";
						$a = array();
						$q = $db->query("SELECT `data` FROM `storico` WHERE idcampostorico=2 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = "'". date('j/n/Y', strtotime($r['data'])) . "'";
						echo (implode(',', $a));
						echo "],";
						echo "\n datasets: [{
            label: 'Numero di siti in HTTPS',";
						echo "\n data: [";
						$a = array();
						$q = $db->query("SELECT `valoreint` FROM `storico` WHERE idcampostorico=2 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = $r['valoreint'];
						echo (implode(',', $a));
						echo "],";
						echo "\nfill: false,
						        borderColor: 'rgba(75, 192, 192, 0.8)',
						        borderJoinStyle: 'miter',
						        tension: 0.1
						          }]   },
						        options: {
						          maintainAspectRatio: false,
									    plugins: {
									      title: { display: true, text: 'Numero di siti in HTTPS' },
 												legend: {display: false, },
									    },
									  scales: {
									    xAxes: [ { ticks: {autoSkip: false, maxRotation: 90, minRotation: 00 } } ]
						       	 }
						       }  });
					   	    </script>";
						echo "\n</div>"; // class='contenitoregrafico'>
						// numero siti in IPv6
						echo "\n<div class='contenitoregrafico'>";
						echo "
						<div class='grafico' style='position: relative; height:200px'><canvas id='chNumeroSitiIPv6'></canvas></div>
						<script>
							const ctx_ipv6 = document.getElementById('chNumeroSitiIPv6');
							const myChart_ipv6 = new Chart(ctx_ipv6, {
								type: 'line',
								data: {";
						echo "\nlabels: [";
						$a = array();
						$q = $db->query("SELECT `data` FROM `storico` WHERE idcampostorico=3 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = "'". date('j/n/Y', strtotime($r['data'])) . "'";
						echo (implode(',', $a));
						echo "],";
						echo "\n datasets: [{
            label: 'Numero di siti in IPv6',";
						echo "\n data: [";
						$a = array();
						$q = $db->query("SELECT `valoreint` FROM `storico` WHERE idcampostorico=3 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = $r['valoreint'];
						echo (implode(',', $a));
						echo "],";
						echo "\nfill: false,
						        borderColor: 'rgba(75, 192, 192, 0.8)',
						        borderJoinStyle: 'miter',
						        tension: 0.1
						          }]   },
						        options: {
						          maintainAspectRatio: false,
									    plugins: {
									      title: { display: true, text: 'Numero di siti in IPv6' },
 												legend: {display: false, },
									    },
									  scales: {
									    xAxes: [ { ticks: {autoSkip: false, maxRotation: 90, minRotation: 00 } } ]
						       	 }
						       }  });
					   	    </script>";
						echo "\n</div>"; // class='contenitoregrafico'>
						// numero IPv4 univoci
						echo "\n<div class='contenitoregrafico'>";
						echo "
						<div class='grafico' style='position: relative; height:200px'><canvas id='chIPv4univoci'></canvas></div>
						<script>
							const ctx_ipv4uni = document.getElementById('chIPv4univoci');
							const myChart_ipv4uni = new Chart(ctx_ipv4uni, {
								type: 'line',
								data: {";
						echo "\nlabels: [";
						$a = array();
						$q = $db->query("SELECT `data` FROM `storico` WHERE idcampostorico=4 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = "'". date('j/n/Y', strtotime($r['data'])) . "'";
						echo (implode(',', $a));
						echo "],";
						echo "\n datasets: [{
            label: 'IPv4 univoci',";
						echo "\n data: [";
						$a = array();
						$q = $db->query("SELECT `valoreint` FROM `storico` WHERE idcampostorico=4 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = $r['valoreint'];
						echo (implode(',', $a));
						echo "],";
						echo "\nfill: false,
						        borderColor: 'rgba(75, 192, 192, 0.8)',
						        borderJoinStyle: 'miter',
						        tension: 0.1
						          }]   },
						        options: {
						          maintainAspectRatio: false,
									    plugins: {
									      title: { display: true, text: 'IPv4 univoci' },
 												legend: {display: false, },
									    },
									  scales: {
									    xAxes: [ { ticks: {autoSkip: false, maxRotation: 90, minRotation: 00 } } ]
						       	 }
						       }  });
					   	    </script>";
						echo "\n</div>"; // class='contenitoregrafico'>
						// numero AS IPv4 univoci
						echo "\n<div class='contenitoregrafico'>";
						echo "
						<div class='grafico' style='position: relative; height:200px'><canvas id='chASIPv4univoci'></canvas></div>
						<script>
							const ctx_asipv4uni = document.getElementById('chASIPv4univoci');
							const myChart_asipv4uni = new Chart(ctx_asipv4uni, {
								type: 'line',
								data: {";
						echo "\nlabels: [";
						$a = array();
						$q = $db->query("SELECT `data` FROM `storico` WHERE idcampostorico=10 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = "'". date('j/n/Y', strtotime($r['data'])) . "'";
						echo (implode(',', $a));
						echo "],";
						echo "\n datasets: [{
            label: 'AS IPv4 univoci',";
						echo "\n data: [";
						$a = array();
						$q = $db->query("SELECT `valoreint` FROM `storico` WHERE idcampostorico=10 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = $r['valoreint'];
						echo (implode(',', $a));
						echo "],";
						echo "\nfill: false,
						        borderColor: 'rgba(75, 192, 192, 0.8)',
						        borderJoinStyle: 'miter',
						        tension: 0.1
						          }]   },
						        options: {
						          maintainAspectRatio: false,
									    plugins: {
									      title: { display: true, text: 'AS IPv4 univoci' },
 												legend: {display: false, },
									    },
									  scales: {
									    xAxes: [ { ticks: {autoSkip: false, maxRotation: 90, minRotation: 00 } } ]
						       	 }
						       }  });
					   	    </script>";
						echo "\n</div>"; // class='contenitoregrafico'>
						// numero AS IPv6 univoci
						echo "\n<div class='contenitoregrafico'>";
						echo "
						<div class='grafico' style='position: relative; height:200px'><canvas id='chASIPv6univoci'></canvas></div>
						<script>
							const ctx_asipv6uni = document.getElementById('chASIPv6univoci');
							const myChart_asipv6uni = new Chart(ctx_asipv6uni, {
								type: 'line',
								data: {";
						echo "\nlabels: [";
						$a = array();
						$q = $db->query("SELECT `data` FROM `storico` WHERE idcampostorico=17 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = "'". date('j/n/Y', strtotime($r['data'])) . "'";
						echo (implode(',', $a));
						echo "],";
						echo "\n datasets: [{
            label: 'AS IPv6 univoci',";
						echo "\n data: [";
						$a = array();
						$q = $db->query("SELECT `valoreint` FROM `storico` WHERE idcampostorico=17 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = $r['valoreint'];
						echo (implode(',', $a));
						echo "],";
						echo "\nfill: false,
						        borderColor: 'rgba(75, 192, 192, 0.8)',
						        borderJoinStyle: 'miter',
						        tension: 0.1
						          }]   },
						        options: {
						          maintainAspectRatio: false,
									    plugins: {
									      title: { display: true, text: 'AS IPv6 univoci' },
 												legend: {display: false, },
									    },
									  scales: {
									    xAxes: [ { ticks: {autoSkip: false, maxRotation: 90, minRotation: 00 } } ]
						       	 }
						       }  });
					   	    </script>";
						echo "\n</div>"; // class='contenitoregrafico'>
						// numero in hosting/colo
						echo "\n<div class='contenitoregrafico'>";
						echo "
						<div class='grafico' style='position: relative; height:200px'><canvas id='chhosting'></canvas></div>
						<script>
							const ctx_hosting = document.getElementById('chhosting');
							const myChart_hosting = new Chart(ctx_hosting, {
								type: 'line',
								data: {";
						echo "\nlabels: [";
						$a = array();
						$q = $db->query("SELECT `data` FROM `storico` WHERE idcampostorico=6 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = "'". date('j/n/Y', strtotime($r['data'])) . "'";
						echo (implode(',', $a));
						echo "],";
						echo "\n datasets: [{
            label: 'In hosting/colocation',";
						echo "\n data: [";
						$a = array();
						$q = $db->query("SELECT `valoreint` FROM `storico` WHERE idcampostorico=6 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = $r['valoreint'];
						echo (implode(',', $a));
						echo "],";
						echo "\nfill: false,
						        borderColor: 'rgba(75, 192, 192, 0.8)',
						        borderJoinStyle: 'miter',
						        tension: 0.1
						          }]   },
						        options: {
						          maintainAspectRatio: false,
									    plugins: {
									      title: { display: true, text: 'In hosting/colocation' },
 												legend: {display: false, },
									    },
									  scales: {
									    xAxes: [ { ticks: {autoSkip: false, maxRotation: 90, minRotation: 00 } } ]
						       	 }
						       }  });
					   	    </script>";
						echo "\n</div>"; // class='contenitoregrafico'>
						// AS IPv4 piu` comuni
						// devo iniziare a capire quali sono gli AS piu` comuni, prendo i primi 10 e vedo anche la descrizione per popolare la legenda
						$aas = array();   // array dei 10 nomi di AS piu` famosi
						$legenda = '';
						$q = $db->query("SELECT storico.valorestr,SUM(storico.valoreint) AS s,ip.asname,ip.org 
						                 FROM storico
														 JOIN ip ON storico.valorestr=ip.as
														 WHERE storico.idcampostorico=7 $wha
														 GROUP BY storico.valorestr 
														 ORDER BY s DESC 
														 LIMIT 10");
						while ($r = $q->fetch_array()) {
							$aas[] = $r['valorestr'];
							$legenda .= "\n$r[valorestr] $r[asname] $r[org]<br/>";
						}
						echo "\n<div class='contenitoregrafico'>";
						echo "
						<div class='grafico' style='position: relative; height:400px'><canvas id='chasipv4'></canvas></div>
						<script>
							const ctx_asipv4 = document.getElementById('chasipv4');
							const myChart_asipv4 = new Chart(ctx_asipv4, {
								type: 'line',
								data: {";
						echo "\nlabels: [";
						$a = array();
						$q = $db->query("SELECT DISTINCT `data` FROM `storico` WHERE idcampostorico=7 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = "'". date('j/n/Y', strtotime($r['data'])) . "'";
						echo (implode(',', $a));
						echo "],";
						// dataset
						echo "\n datasets: [";
						foreach ($aas as $as) {
							$randcolor = rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ',';
							echo "\n{label: '$as', data: [";
							$a = array();
							$q = $db->query("SELECT `valoreint` FROM `storico` WHERE idcampostorico=7 AND valorestr='$as' $wha ORDER BY `data`");
								while ($r = $q->fetch_array()) $a[] = $r['valoreint'];
								echo (implode(',', $a));
								echo "],";
								echo "\nfill: false,
												borderColor: 'rgba($randcolor 0.8)',
												backgroundColor: 'rgba($randcolor 0.8)',
												borderJoinStyle: 'miter',
												tension: 0.1 },";
						}
						// fine dei dataset
						echo "\n	]   },
						        options: {
						          maintainAspectRatio: false,
									    plugins: {
									      title: { display: true, text: 'AS IPv4' },
									    },
									  scales: {
									    xAxes: [ { ticks: {autoSkip: false, maxRotation: 90, minRotation: 00 } } ]
						       	 }
						       }  });
					   	    </script>";
						echo "\n</div>"; // class='contenitoregrafico'>
						echo "<p>$legenda</p>";
						// AS IPv6 piu` comuni
						// devo iniziare a capire quali sono gli AS piu` comuni, prendo i primi 10 e vedo anche la descrizione per popolare la legenda
						$aas = array();   // array dei 10 nomi di AS piu` famosi
						$legenda = '';
						$q = $db->query("SELECT storico.valorestr,SUM(storico.valoreint) AS s,ip.asname,ip.org 
						                 FROM storico
														 JOIN ip ON storico.valorestr=ip.as
														 WHERE storico.idcampostorico=8 $wha
														 GROUP BY storico.valorestr 
														 ORDER BY s DESC 
														 LIMIT 10");
						while ($r = $q->fetch_array()) {
							$aas[] = $r['valorestr'];
							$legenda .= "\n$r[valorestr] $r[asname] $r[org]<br/>";
						}
						echo "\n<div class='contenitoregrafico'>";
						echo "
						<div class='grafico' style='position: relative; height:400px'><canvas id='chasipv6'></canvas></div>
						<script>
							const ctx_asipv6 = document.getElementById('chasipv6');
							const myChart_asipv6 = new Chart(ctx_asipv6, {
								type: 'line',
								data: {";
						echo "\nlabels: [";
						$a = array();
						$q = $db->query("SELECT DISTINCT `data` FROM `storico` WHERE idcampostorico=8 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = "'". date('j/n/Y', strtotime($r['data'])) . "'";
						echo (implode(',', $a));
						echo "],";
						// dataset
						echo "\n datasets: [";
						foreach ($aas as $as) {
							$randcolor = rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ',';
							echo "\n{label: '$as', data: [";
							$a = array();
							$q = $db->query("SELECT `valoreint` FROM `storico` WHERE idcampostorico=8 AND valorestr='$as' $wha ORDER BY `data`");
								while ($r = $q->fetch_array()) $a[] = $r['valoreint'];
								echo (implode(',', $a));
								echo "],";
								echo "\nfill: false,
												borderColor: 'rgba($randcolor 0.8)',
												backgroundColor: 'rgba($randcolor 0.8)',
												borderJoinStyle: 'miter',
												tension: 0.1 },";
						}
						// fine dei dataset
						echo "\n	]   },
						        options: {
						          maintainAspectRatio: false,
									    plugins: {
									      title: { display: true, text: 'AS IPv6' },
									    },
									  scales: {
									    xAxes: [ { ticks: {autoSkip: false, maxRotation: 90, minRotation: 00 } } ]
						       	 }
						       }  });
					   	    </script>";
						echo "\n</div>"; // class='contenitoregrafico'>
						echo "<p>$legenda</p>";
						// web server
						// devo iniziare a capire quali sono i dati piu`comuni
						$aas = array();   // array dei 10 nomi  piu` famosi
						$q = $db->query("SELECT storico.valorestr,SUM(storico.valoreint) AS s
						                 FROM storico
														 WHERE storico.idcampostorico=5 $wha
														 GROUP BY storico.valorestr 
														 ORDER BY s DESC 
														 LIMIT 10");
						while ($r = $q->fetch_array()) {
							$aas[] = $r['valorestr'];
						}
						echo "\n<div class='contenitoregrafico'>";
						echo "
						<div class='grafico' style='position: relative; height:400px'><canvas id='chwebserver'></canvas></div>
						<script>
							const ctx_webserver = document.getElementById('chwebserver');
							const myChart_webserver = new Chart(ctx_webserver, {
								type: 'line',
								data: {";
						echo "\nlabels: [";
						$a = array();
						$q = $db->query("SELECT DISTINCT `data` FROM `storico` WHERE idcampostorico=5 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = "'". date('j/n/Y', strtotime($r['data'])) . "'";
						echo (implode(',', $a));
						echo "],";
						// dataset
						echo "\n datasets: [";
						foreach ($aas as $as) {
							$randcolor = rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ',';
							echo "\n{label: '$as', data: [";
							$a = array();
							$q = $db->query("SELECT `valoreint` FROM `storico` WHERE idcampostorico=5 AND valorestr='$as' $wha ORDER BY `data`");
								while ($r = $q->fetch_array()) $a[] = $r['valoreint'];
								echo (implode(',', $a));
								echo "],";
								echo "\nfill: false,
												borderColor: 'rgba($randcolor 0.8)',
												backgroundColor: 'rgba($randcolor 0.8)',
												borderJoinStyle: 'miter',
												tension: 0.1 },";
						}
						// fine dei dataset
						echo "\n	]   },
						        options: {
						          maintainAspectRatio: false,
									    plugins: {
									      title: { display: true, text: 'Web server' },
									    },
									  scales: {
									    xAxes: [ { ticks: {autoSkip: false, maxRotation: 90, minRotation: 00 } } ]
						       	 }
						       }  });
					   	    </script>";
						echo "\n</div>"; // class='contenitoregrafico'>
						// powered by
						// devo iniziare a capire quali sono i dati piu`comuni
						$aas = array();   // array dei 10 nomi  piu` famosi
						$q = $db->query("SELECT storico.valorestr,SUM(storico.valoreint) AS s
						                 FROM storico
														 WHERE storico.idcampostorico=9 $wha
														 GROUP BY storico.valorestr 
														 ORDER BY s DESC 
														 LIMIT 10");
						while ($r = $q->fetch_array()) {
							$aas[] = $r['valorestr'];
						}
						echo "\n<div class='contenitoregrafico'>";
						echo "
						<div class='grafico' style='position: relative; height:400px'><canvas id='chpoweredby'></canvas></div>
						<script>
							const ctx_poweredby = document.getElementById('chpoweredby');
							const myChart_poweredby = new Chart(ctx_poweredby, {
								type: 'line',
								data: {";
						echo "\nlabels: [";
						$a = array();
						$q = $db->query("SELECT DISTINCT `data` FROM `storico` WHERE idcampostorico=9 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = "'". date('j/n/Y', strtotime($r['data'])) . "'";
						echo (implode(',', $a));
						echo "],";
						// dataset
						echo "\n datasets: [";
						foreach ($aas as $as) {
							$randcolor = rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ',';
							echo "\n{label: '$as', data: [";
							$a = array();
							$q = $db->query("SELECT `valoreint` FROM `storico` WHERE idcampostorico=9 AND valorestr='$as' $wha ORDER BY `data`");
								while ($r = $q->fetch_array()) $a[] = $r['valoreint'];
								echo (implode(',', $a));
								echo "],";
								echo "\nfill: false,
												borderColor: 'rgba($randcolor 0.8)',
												backgroundColor: 'rgba($randcolor 0.8)',
												borderJoinStyle: 'miter',
												tension: 0.1 },";
						}
						// fine dei dataset
						echo "\n	]   },
						        options: {
						          maintainAspectRatio: false,
									    plugins: {
									      title: { display: true, text: 'Powered by' },
									    },
									  scales: {
									    xAxes: [ { ticks: {autoSkip: false, maxRotation: 90, minRotation: 00 } } ]
						       	 }
						       }  });
					   	    </script>";
						echo "\n</div>"; // class='contenitoregrafico'>
						// country IPv4
						// devo iniziare a capire quali sono i dati piu`comuni
						$aas = array();   // array dei 10 nomi  piu` famosi
						$q = $db->query("SELECT storico.valorestr,SUM(storico.valoreint) AS s
						                 FROM storico
														 WHERE storico.idcampostorico=11 $wha
														 GROUP BY storico.valorestr 
														 ORDER BY s DESC 
														 LIMIT 10");
						while ($r = $q->fetch_array()) {
							$aas[] = $r['valorestr'];
						}
						echo "\n<div class='contenitoregrafico'>";
						echo "
						<div class='grafico' style='position: relative; height:400px'><canvas id='chcountryipv4'></canvas></div>
						<script>
							const ctx_countryipv4 = document.getElementById('chcountryipv4');
							const myChart_countryipv4 = new Chart(ctx_countryipv4, {
								type: 'line',
								data: {";
						echo "\nlabels: [";
						$a = array();
						$q = $db->query("SELECT DISTINCT `data` FROM `storico` WHERE idcampostorico=11 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = "'". date('j/n/Y', strtotime($r['data'])) . "'";
						echo (implode(',', $a));
						echo "],";
						// dataset
						echo "\n datasets: [";
						foreach ($aas as $as) {
							$randcolor = rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ',';
							$rr = $db->query("SELECT stato FROM iso3166a2 WHERE iso='$as'")->fetch_array();
							echo "\n{label: '$rr[stato]', data: [";
							$a = array();
							$q = $db->query("SELECT `valoreint` FROM `storico` WHERE idcampostorico=11 AND valorestr='$as' $wha ORDER BY `data`");
								while ($r = $q->fetch_array()) $a[] = $r['valoreint'];
								echo (implode(',', $a));
								echo "],";
								echo "\nfill: false,
												borderColor: 'rgba($randcolor 0.8)',
												backgroundColor: 'rgba($randcolor 0.8)',
												borderJoinStyle: 'miter',
												tension: 0.1 },";
						}
						// fine dei dataset
						echo "\n	]   },
						        options: {
						          maintainAspectRatio: false,
									    plugins: {
									      title: { display: true, text: 'Country IPv4' },
									    },
									  scales: {
									    xAxes: [ { ticks: {autoSkip: false, maxRotation: 90, minRotation: 00 } } ]
						       	 }
						       }  });
					   	    </script>";
						echo "\n</div>"; // class='contenitoregrafico'>
						// country IPv6
						// devo iniziare a capire quali sono i dati piu`comuni
						$aas = array();   // array dei 10 nomi  piu` famosi
						$q = $db->query("SELECT storico.valorestr,SUM(storico.valoreint) AS s
						                 FROM storico
														 WHERE storico.idcampostorico=21 $wha
														 GROUP BY storico.valorestr 
														 ORDER BY s DESC 
														 LIMIT 10");
						while ($r = $q->fetch_array()) {
							$aas[] = $r['valorestr'];
						}
						echo "\n<div class='contenitoregrafico'>";
						echo "
						<div class='grafico' style='position: relative; height:400px'><canvas id='chcountryipv6'></canvas></div>
						<script>
							const ctx_countryipv6 = document.getElementById('chcountryipv6');
							const myChart_countryipv6 = new Chart(ctx_countryipv6, {
								type: 'line',
								data: {";
						echo "\nlabels: [";
						$a = array();
						$q = $db->query("SELECT DISTINCT `data` FROM `storico` WHERE idcampostorico=21 $wha ORDER BY `data`");
						while ($r = $q->fetch_array()) $a[] = "'". date('j/n/Y', strtotime($r['data'])) . "'";
						echo (implode(',', $a));
						echo "],";
						// dataset
						echo "\n datasets: [";
						foreach ($aas as $as) {
							$randcolor = rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ',';
							$rr = $db->query("SELECT stato FROM iso3166a2 WHERE iso='$as'")->fetch_array();
							echo "\n{label: '$rr[stato]', data: [";
							$a = array();
							$q = $db->query("SELECT `valoreint` FROM `storico` WHERE idcampostorico=21 AND valorestr='$as' $wha ORDER BY `data`");
								while ($r = $q->fetch_array()) $a[] = $r['valoreint'];
								echo (implode(',', $a));
								echo "],";
								echo "\nfill: false,
												borderColor: 'rgba($randcolor 0.8)',
												backgroundColor: 'rgba($randcolor 0.8)',
												borderJoinStyle: 'miter',
												tension: 0.1 },";
						}
						// fine dei dataset
						echo "\n	]   },
						        options: {
						          maintainAspectRatio: false,
									    plugins: {
									      title: { display: true, text: 'Country IPv6' },
									    },
									  scales: {
									    xAxes: [ { ticks: {autoSkip: false, maxRotation: 90, minRotation: 00 } } ]
						       	 }
						       }  });
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
