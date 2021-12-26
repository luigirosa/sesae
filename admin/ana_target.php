<?php

/**
 *
 * SESAE - Admin - Anarafica target, elenco
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2018-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * 
 * 20180217 prima versione
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

define('SESAE', true);
require('global.php');

// AJAX
if (isset($_GET['filtro'])) {
	$filtro = $b2->normalizza($_GET['filtro'], B2_NORM_SQL | B2_NORM_TRIM);
	if ('***' == $filtro) {
		$wh = '';
	} else {
		$filtrolike = "'%" . $filtro . "%'";
		$wh = "WHERE target.description LIKE $filtrolike OR target.url LIKE $filtrolike OR category.category LIKE $filtrolike";
	}
	$q = $db->query("SELECT target.idtarget,target.description,target.url,target.enabled,
	                        category.category
	                 FROM target
	                 JOIN category ON target.idcategory=category.idcategory
	                 $wh
	                 ORDER BY target.description");
	if ($q->num_rows > 0) {
		echo "\n<table border='0' align='center'>";
		echo $b2->intestazioneTabella(array('Target', 'URL', 'Categoria', "Test"));
		while ($r = $q->fetch_array()) {
			$bg = $b2->bgcolor();
			$abilitato = '1' == $r['enabled'] ? '' : ' (disabilitato)';
			echo "\n<tr $bg>";
			echo "<td $bg align='left'>&nbsp;<b><a href='ana_targetedit.php?idtarget=$r[idtarget]'>$r[description]$abilitato</a></b>&nbsp;</td>";
			echo "<td $bg align='left'>&nbsp;<b><a href='ana_targetedit.php?idtarget=$r[idtarget]'>$r[url]</a></b>&nbsp;</td>";
			echo "<td $bg align='left'>&nbsp;<b><a href='ana_targetedit.php?idtarget=$r[idtarget]'>$r[category]</a></b>&nbsp;</td>";
			echo "<td $bg align='left'>&nbsp;<b><a href='$r[url]' target='_blank'>Test URL</a></b>&nbsp;</td>";
			echo "\n</tr>";
		}
		echo "\n</table>";
	} else {
		echo "<b>Nessun target corrisponde alla ricerca indicata.</b>";
	}
	die();
}

intestazione('Anagrafica target');

if (isset($_SESSION['retscan']) and $_SESSION['retscan'] != '') {
	echo "<p align='center'>Risultato ultima scansione: $_SESSION[retscan]</p>";
}
echo "<p align='center'>Cerca target: <input type='text' id='cerca' name='cerca' size='50'><br>Almeno tre lettere, tre asterischi per visualizzarli tutti.</p>";
echo "<p align='center'><b><a href='ana_targetedit.php?idtarget=0'>Nuovo target</a></b></p>";
echo "<span align='center' id='risultato'></span>";

?>
<script language="Javascript">

$("#cerca").on("input", function() {
	if (this.value.length > 2) {
		$("#risultato").load("ana_target.php?filtro=" + encodeURIComponent(this.value));  
	} else {
		$("#risultato").empty();
	}
});

</script>
<?php

piede();

//### END OF FILE ###