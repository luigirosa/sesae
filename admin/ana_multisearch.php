<?php

/**
 *
 * SESAE - Admin - Anarafica target, ricerca multipla
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2018-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 *
 * 20181224 prima versione
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
	$acerca = $array = preg_split ('/$\R?^/m', trim($_GET['filtro']));
	foreach ($acerca as $cerca) {
		$cerca = trim($cerca);
		if ('' != $cerca) {
			echo "\n<p><b>$cerca</b>";
			$filtrolike = "'%" . $b2->normalizza($cerca) . "%'";
			$q = $db->query("SELECT target.idtarget,target.description,target.url,
                       category.category
                       FROM target
                       JOIN category ON target.idcategory=category.idcategory
                       WHERE target.description LIKE $filtrolike OR target.url LIKE $filtrolike OR category.category LIKE $filtrolike
                       ORDER BY target.description");
			if ($q->num_rows > 0) {
				echo "\n<br/><ul>";
				while ($r = $q->fetch_array()) {
					echo "<li><a href='ana_targetedit.php?idtarget=$r[idtarget]' target='_blank'>$r[description] - $r[url] ($r[category])</a></li>";
				}
				echo "\n</ul></p>";
			} else {
				echo ": nulla.</p>";
			}
		}
	}
	die();
}

intestazione('Ricerca multipla');

echo "\n<table border='0' align='center'><tr valign='top'>";
// colonna textarea
echo "<td valign='top'><textarea name='multisearch' id='multisearch' cols='40' rows='40'></textarea></td>";
// colonna risultato
echo "<td valign='top'><span align='left' id='risultato'></span></td>";
echo "\n</tr></table>"

?>
<script language="Javascript">

$("#multisearch").on("input", function() {
	if (this.value.length > 2) {
		$("#risultato").load("ana_multisearch.php?filtro=" + encodeURIComponent(this.value));  
	} else {
		$("#risultato").empty();
	}
});

</script>
<?php

piede();

//### END OF FILE ###