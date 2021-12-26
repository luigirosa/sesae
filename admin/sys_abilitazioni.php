<?php

/**
 * 
 * SESAE - Admin - Modifica permission
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

// gestione post, inutile fare due file per 10 righe
if (isset($_POST['questoid'])) {
	//error_log(print_r($_POST, TRUE));
	list($nonserve1,$nonserve2,$idlevel,$idmenu) = explode('-', $_POST['questoid']);
	if ('true' == $_POST['ischecked']) {
		$db->query("INSERT INTO permission SET idlevel='$idlevel',idmenu='$idmenu'");
	} else {
		$db->query("DELETE FROM permission WHERE idlevel='$idlevel' AND idmenu='$idmenu'");
	}
	die();
}

// precarico i level
$alevel = array();
$q = $db->query("SELECT * FROM level ORDER BY level");
while ($r = $q->fetch_array()) $alevel[$r['idlevel']] = $r['level'];

intestazione("Abilitazioni");

echo "\n<table border='0' align='center'>";

echo "\n<tr><th valign='bottom' aling='center'><b>Voce di menu</b></th>";
foreach ($alevel as $livello) echo "<th valign='bottom' aling='center'><b>$livello</b></th>";
echo "</tr>";

$contaspan = 1;
$q = $db->query("SELECT idmenu,menu FROM menu WHERE idfather='0' ORDER BY weight");

while ($r = $q->fetch_array()) {
	$bg = $b2->bgcolor();
	echo "\n<tr $bg>";
	echo "<td $bg align='left'><b>$r[menu]</b></td>";
	foreach ($alevel as $idlevel=>$livello) {
		$qq = $db->query("SELECT idlevel FROM permission WHERE idlevel='$idlevel' AND idmenu='$r[idmenu]'");
		$x = $qq->num_rows > 0 ? 'checked' : '';
		echo "<td $bg align='center'><input type='checkbox' $x id='campo-$contaspan-$idlevel-$r[idmenu]' class='abilita'/></td>";
		$contaspan++;
	}
	echo "</tr>";
	// tiene figli?
	$qf = $db->query("SELECT idmenu,menu FROM menu WHERE idfather='$r[idmenu]' ORDER BY weight");
	if ($qf->num_rows > 0) {
		while ($rf = $qf->fetch_array()) {
			echo "\n<tr $bg>";
			$bg = $b2->bgcolor();
			echo "<td $bg align='left'>&nbsp;&nbsp;$rf[menu]</td>";
			foreach ($alevel as $idlevel=>$livello) {
				$qq = $db->query("SELECT idlevel FROM permission WHERE idlevel='$idlevel' AND idmenu='$rf[idmenu]'");
				$x = $qq->num_rows > 0 ? 'checked' : '';
				echo "<td $bg align='center'><input type='checkbox' $x id='campo-$contaspan-$idlevel-$rf[idmenu]' class='abilita'/></td>";
				$contaspan++;
			}
			echo "</tr>";
		}
	}
}

echo "\n<tr><th valign='bottom' aling='center'><b>Voce di menu</b></th>";
foreach ($alevel as $livello) echo "<th valign='top' aling='center'><b>$livello</b></th>";
echo "</tr>";

echo "\n</table>";

?>
<script language="Javascript">
$(document).ready(function() {
	$(".abilita").click(function() { 
		var questoid = $(this).attr('id');
		var ischecked = $(this).is(':checked');
		$.post("sys_abilitazioni.php", {questoid: questoid, ischecked: ischecked});
	}) // class
}) // ready

</script>
<?php

piede();

// ### END OF FILE ###