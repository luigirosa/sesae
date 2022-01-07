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

$idcategory = 0;
if (isset($_GET['c']) and is_numeric($_GET['c'])) $idcategory = $b2->normalizza($_GET['c'], B2_NORM_TRIM);

$nocache='';
if (isset($_GET['nocache'])) $nocache = 'nocache';

?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/>
		<link rel='stylesheet' type='text/css' href='https://fonts.googleapis.com/css?family=Open+Sans|Open+Sans:b' />
		<link rel='stylesheet' href='static/style.css' type='text/css'/>
		<meta http-equiv='X-UA-Compatible' content='IE=edge'/>
		<meta name='viewport' content='width=device-width, initial-scale=1'/>
		<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
		<title>SESAE</title>
	</head>
	<body>
		<h1><a href="https://sesae.com/">SESAE</a></h1>
		<div class="contenitorecolonne">
		<div class="colindice">
				<h2>Categorie</h2>
				<?php
					$q = $db->query("SELECT idcategory,category FROM category WHERE enabled='1' ORDER BY weight");
					while ($r = $q->fetch_array()) {
						echo  "\n<div class='linavigatore'><a href='index.php?c=$r[idcategory]' class='linavigatore'>$r[category]</a></div>";
					}
				?>
			</div>
			<div class="colcontenuto">
				<?php
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
				?>
			</div>
		</div>
	</body>
</html>
