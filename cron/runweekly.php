<?php

/**
 * SESAE - Manutenzione giornaliera
 *
 * @package     SESAE
 * @subpackage  SESAE Cron
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 *
 * 20210109 prima versione
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

require('../admin/global.php');

$outdir = '../public/dati/';

//
// esportazione JSON
//
// categorie
$f = fopen($outdir . 'categorie.csv', 'w');
fputcsv($f, ['idcategory','category']);
$q = $db->query("SELECT idcategory,category FROM category WHERE enabled=1");
while ($r = $q->fetch_assoc()) {
  fputcsv($f,$r);
}
fclose($f);
// target
$f = fopen($outdir . 'target.csv', 'w');
fputcsv($f, ['idtarget','idcategory','description','url','visited','counter','external_id','http_code','http_contenttype',
              'http_server','ishttps','https_certname','https_subject','https_issuer','https_validto','https_issuerorg',
              'https_signature','html_title','goog_analytics','goog_tag','goog_asy','ipv4_ip','ipv4_continente','ipv4_codicestato',
              'ipv4_isp','ipv4_org','ipv4_as','ipv4_asname','ipv4_reverse','ipv4_ishosting','ipv4_asowner','ipv6_ip',
              'ipv6_continente','ipv6_codicestato','ipv6_isp','ipv6_org','ipv6_as','ipv6_asname','ipv6_reverse','ipv6_ishosting',
              'ipv6_asowner']);
$q = $db->query("SELECT target.idtarget,target.idcategory,target.description,target.url,target.visited,target.counter,target.external_id,
                        targetdata.http_code,targetdata.http_contenttype,targetdata.http_server,targetdata.ishttps,targetdata.https_certname,targetdata.https_subject,targetdata.https_issuer,targetdata.https_validto,targetdata.https_issuerorg,targetdata.https_signature,targetdata.html_title,targetdata.goog_analytics,targetdata.goog_tag,targetdata.goog_asy,
                        ipv4.ip AS ipv4_ip,ipv4.continent AS ipv4_continente,ipv4.countrycode AS ipv4_codicestato,ipv4.isp AS ipv4_isp,ipv4.org AS ipv4_org,ipv4.`as` AS ipv4_as,ipv4.asname AS ipv4_asname,ipv4.reverse AS ipv4_reverse,ipv4.ishosting AS ipv4_ishosting,ipv4.asowner AS ipv4_asowner,
                        ipv6.ip AS ipv6_ip,ipv4.continent AS ipv6_continente,ipv6.countrycode AS ipv6_codicestato,ipv6.isp AS ipv6_isp,ipv6.org AS ipv6_org,ipv6.`as` AS ipv6_as,ipv6.asname AS ipv6_asname,ipv6.reverse AS ipv6_reverse,ipv6.ishosting AS ipv6_ishosting,ipv6.asowner AS ipv6_asowner
                 FROM target
                 LEFT JOIN targetdata ON target.idtarget=targetdata.idtarget
                 LEFT JOIN ip AS ipv4 ON target.idipv4=ipv4.idip
                 LEFT JOIN ip AS ipv6 ON target.idipv6=ipv6.idip");
while ($r = $q->fetch_assoc()) {
  fputcsv($f,$r);
}
fclose($f);
// dns
$f = fopen($outdir . 'dns.csv', 'w');
fputcsv($f, ['idtarget','dnsauth','dnsauth_stat']);
$q = $db->query("SELECT target.idtarget,dnsauth.dnsauth,dnsauth.dnsauth_stat
                 FROM target
                 JOIN dnsauth ON target.idtarget=dnsauth.idtarget");
while ($r = $q->fetch_assoc()) {
  fputcsv($f,$r);
}
fclose($f);
// generator
$f = fopen($outdir . 'generator.csv', 'w');
fputcsv($f, ['idtarget','http_generator','http_generator_stat','http_generator_stat_fam']);
$q = $db->query("SELECT target.idtarget,http_generator.http_generator,http_generator.http_generator_stat,http_generator.http_generator_stat_fam
                 FROM target
                 JOIN http_generator ON target.idtarget=http_generator.idtarget");
while ($r = $q->fetch_assoc()) {
  fputcsv($f,$r);
}
fclose($f);
// header
$f = fopen($outdir . 'header.csv', 'w');
fputcsv($f, ['idtarget','http_header']);
$q = $db->query("SELECT target.idtarget,http_header.http_header
                 FROM target
                 JOIN http_header ON target.idtarget=http_header.idtarget");
while ($r = $q->fetch_assoc()) {
  fputcsv($f,$r);
}
fclose($f);
// httpserver
$f = fopen($outdir . 'httpserver.csv', 'w');
fputcsv($f, ['idtarget','http_server','http_server_stat','http_server_stat_fam']);
$q = $db->query("SELECT target.idtarget,http_server.http_server,http_server.http_server_stat,http_server.http_server_stat_fam
                 FROM target
                 JOIN http_server ON target.idtarget=http_server.idtarget");
while ($r = $q->fetch_assoc()) {
  fputcsv($f,$r);
}
fclose($f);
// mx
$f = fopen($outdir . 'mx.csv', 'w');
fputcsv($f, ['idtarget','http_server','http_server_stat','http_server_stat_fam']);
$q = $db->query("SELECT target.idtarget,mxserver.mxserver,mxserver.mxserver_real,mxserver.mxserver_stat,mxserver.isesmtp
                 FROM target
                 JOIN targetmx ON target.idtarget=targetmx.idtarget
                 JOIN mxserver ON targetmx.idmxserver=mxserver.idmxserver");
while ($r = $q->fetch_assoc()) {
  fputcsv($f,$r);
}
fclose($f);

