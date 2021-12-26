<?php

/**
 *
 * SESAE - Admin - Ricalcolo statistiche DNS server
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2021-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 *
 * 20211208 prima versione
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

echo "\nRicalcolo statistiche DNS\n";

$c = 0;

$q = $db->query("SELECT * FROM dnsauth");
while ($r=$q->fetch_array()) {
	$c++;
	$a = array();
	$s = statisticacampo($r['dnsauth'], 'DNS');
	$a[] = $b2->campoSQL('dnsauth_stat', $s['stat']);
	$db->query("UPDATE dnsauth SET " . implode(',', $a) . " WHERE iddnsauth='$r[iddnsauth]'");
	if (($c % 100) == 0) echo "$c ";
}

echo "\n";

// ### END OF FILE ###