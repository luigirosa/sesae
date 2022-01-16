<?php

/**
 *
 * SESAE - Admin - Elenco host con troppi slash
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * 
 * 20220116 prima versione
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

// ajax
if (isset($_POST['dispatch']) and 'ex' == $_POST['dispatch']) {
	//error_log(print_r($_POST, true));
	list ($nonserve,$idtarget) = explode('-', $_POST['nome']);
	$idtarget = $b2->normalizza($idtarget);
	$valore = $_POST['stato'] == 'true' ? 1 : 0;
	$db->query("UPDATE target SET checked='$valore' WHERE idtarget='$idtarget'");
	die();
}

intestazione('Troppi slash');

$q = $db->query("SELECT idtarget,url,description,visited,CHAR_LENGTH(url)-CHAR_LENGTH(REPLACE(url,'/', '')) AS `count`
                 FROM target 
								 HAVING `count`>3
								 ORDER BY `count` DESC,description	 ");

if ($q->num_rows > 0) {
	echo "\n<table border='0' align='center'>";
	echo $b2->intestazioneTabella(array('Last', 'Sito', 'URL'));
	while ($r = $q->fetch_array()) {
		$bg = $b2->bgcolor();
		echo "\n<tr $bg>";
		echo "<td $bg align='left'><a href='ana_targetedit.php?idtarget=$r[idtarget]' target='_blank'>" . date("j/n/Y H:i", $r['visited']) . "</a>&nbsp;</td>";
		echo "<td $bg align='left'><a target='_blank' href='ana_targetedit.php?idtarget=$r[idtarget]'>$r[description]</a></td>";
		echo "<td $bg align='left'><a target='_blank' href='ana_targetedit.php?idtarget=$r[idtarget]'>$r[url]<br/>$r[http_location]</a></td>";
		echo "<td $bg align='center'>" . $b2->inputCheck("ex-$r[idtarget]", false, "ex-$r[idtarget]", "class='ex'") .  "</td>";
		echo "</tr>";
	}
	echo "\n</table>";
	echo "\n<p>&nbsp;</p>";
} else {
	echo "<b>Nessun target corrisponde alla ricerca indicata.</b>";
}

?> 
<script>
	$(document).ready(function() {
  	// cambio checkbox
  	$(".ex").change(function(){
  		var nome = $(this).attr("name");
  		var stato = $(this).is(':checked');
			$.post("man_sospetti.php", 
				{dispatch: "ex", nome: nome, stato: stato})
  	});
	});	
</script>

<?php

piede();

// ### END OF FILE ###