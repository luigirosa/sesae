<?php

/**
 *
 * B2TOOLS - B2TEAM Tools
 *
 * @package     B2TOOLS
 * @author      Luigi Rosa (lists@b2team.com)
 * @copyright   (C) 2015-2022 Luigi Rosa <lists@b2team.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * @version     20170407
 *
 * 20150312 file creato
 * 20150607 il master passa su GitHub, repository luigirosa/b2tools
 * 20150617 migliorati i commenti per auto-documentare la classe
 * 20150814 l'handle del database e' passato come parametro
 * 20160605 required; *Auto(); tabcampi;
 * 20211204 cambio licenza per pubblicazione sorgenti
 *
 * This file is part of B2TOOLS.
 *
 * B2TOOLS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * B2TOOLS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with B2TOOLS.  If not, see <https://www.gnu.org/licenses/>.
 *
 * NB: Le intestazioni delle singole funzioni riportano il changelog delle stesse, qui c'e' il changelog generico
 *
 */

define('B2_DT_ZEROFILL', 1);     // data: fill a zero dei numeri
define('B2_DT_ANNODUE', 2);      // data: anno a due cifre
define('B2_DT_SETTGM', 4);       // data: formato "giorno della settimana, giorno/mese"
define('B2_DT_MESE3', 1);        // data: prime 3 lettere del mese
define('B2_DT_MESELUNGO', 2);    // data: nome completo del mese
define('B2_DT_DOW3', 1);         // data: prime tre lettere del giorno della settimana
define('B2_DT_DOWLUNGO', 2);     // data: nome completo del giorno della settimana
define('B2_NORM_SQL', 1);        // normalizzazione: escape MySQL
define('B2_NORM_FORM', 2);       // normalizzazione: form (doble quotes in entities)
define('B2_NORM_TRIM', 4);       // normalizzazione: trim
define('B2_NORM_UPPER', 8);      // normalizzazione: converti in maiuscolo
define('B2_NORM_LOWER', 16);     // normalizzazione: converti in minuscolo
define('B2_NORM_FILTNUM', 32);   // normalizzazione: filtra i numeri che arrivano dall'input formattato
define('B2_ED_VTOP', 1);         // rigaEdit valign='top'
define('B2_INPUT_REQUIRED', 1);  // required='required' per gli <input> che li supportano (text, search, url, tel, email, password, date pickers, number, checkbox, radio, and file)  
define('B2_IT_RIGHT', 2);        // inputText align='right'
define('B2_IT_CENTER', 4);       // inputText align='center'
define('B2_SE_3EQUAL', 2);       // inputSelect triple equal nei test di SELECTED


/**
 * Classe B2
 *
 * @author Luigi Rosa <luigi.rosa@b2team.com>
 *
 * 20150312 prima versione
 * 20150814 l'handle del database e' passato come parametro
 * 20160605 nuovo parametro $tabcampi per la tabella dei campi 
 *
 */

class objB2 {
	private $flipflop;      // mantiene lo stato dell'alternanza dei colori 
	private $mydb;          // handle del database
	private $tabcampi;      // tabella con la definizione dei campi
	var $bg_0 = '#ffffff';  // background per bgcolor()
	var $bg_1 = '#d0d0d0';  // background per bgcolor()


	/**
	 * Costruttore  
	 * Inizializzaione delle variabili interne all'oggetto 
	 *
	 * @author Luigi Rosa <luigi.rosa@b2team.com>
	 *
	 * 20150313 prima versione
	 * 20150814 l'handle del database e' passato come parametro
	 * 20160605 nuovo parametro $tabcampi per la tabella dei campi 
	 * 
	 */
	function __construct($db, $tabcampi = '') {
		$this->mydb     = $db;
		$this->tabcampi = $tabcampi;
		$this->flipflop = true;
	}

	
	/**
	 * bgcolor($reset)
	 * Restituisce il valore alternato di background per le righe delle tabelle
	 * 
	 * 20150327 prima versione
	 * 20160525 aggiunto reset
	 *
	 */
	function bgcolor($reset = false) {
		if ($reset) {
			$this->flipflop = true;
			return (true);
		} else {
			$this->flipflop = !$this->flipflop;
			$bg = $this->flipflop ? $this->bg_0 : $this->bg_1;
			return ("bgcolor='$bg'");
		}
	}


	/**
	 * dt2iso($dtita)
	 * Converte una data italiana (gg/mm/aaaa) in ISO (aaaa-mm-gg)
	 * Se l'anno e' minore di 999, aggiunge 2000 all'anno
	 * 
	 * 20150313 prima versione
	 * 20150616 corretta correzione per Y2K
	 *
	 */
	function dt2iso($dtita) {
		if ('' != trim($dtita)) {
			list($g,$m,$a) = explode('/', trim($dtita));
			if ($a < 999) $a += 2000;
			$retval = sprintf('%04d-%02d-%02d', $a, $m, $g);
		} else {
			$retval = '';
		}
		return $retval;
	}


	/**
	 * dt2ita($dtiso, $opzioni)
	 * Converte una data ISO (aaaa-mm-gg) in data italiana (gg/mm/aaaa)
	 * 
	 * Opzioni:
	 *  B2_DT_ZEROFILL filla a zero giorno e mese
	 *  B2_DT_ANNODUE  restituisce l'anno a due cifre
	 *  B2_DT_SETTGM   formato "giorno della settimana, giorno/mese"
	 * 
	 * 20150315 prima versione
	 * 20150322 cast a int per forzare la rimozione di zero
	 * 20150603 formato giorno mese settimana
	 *
	 */
	function dt2ita($dtiso, $opzioni = 0) {
		$iszerofill = $opzioni & B2_DT_ZEROFILL;
		$isannodue = $opzioni & B2_DT_ANNODUE;
		$issettgm = $opzioni & B2_DT_SETTGM;
		if ('' != $dtiso and '0000-00-00' != $dtiso ) {
			list($a,$m,$g) = explode('-', trim($dtiso));
			if ($issettgm) {
 				$dow = date("N", strtotime($dtiso));
 				$retval = $this->dtDowIta($dow, B2_DT_DOW3) . " $g/$m";
			} else {
				if ($isannodue) $a = substr($a, 2);
				if ($iszerofill) {
					$g = sprintf("%02d", $g);
					$m = sprintf("%02d", $m);
				} else {
					$g = (int)$g;
					$m = (int)$m;
				}
				$retval = "$g/$m/$a";
			}
		} else {
			$retval = '';
		}
		return $retval;
	}


	/**
	 * ts2ita($timestamp, $opzioni)
	 * Converte un timestamp ISO (aaaa-mm-gg hh:mm:ss) in data italiana (gg/mm/aaaa hh:mm:ss)
	 * Le opzioni sono quelle di dt2ita()
	 * 
	 * 20150408 prima versione
	 *
	 */
	function ts2ita($timestamp, $opzioni = 0) {
		if ('' == trim($timestamp)) {
			$retval = '';
		} else {
			list($dt,$tm) = explode(' ', $timestamp);
			$retval = $this->dt2ita($dt, $opzioni) . ' ' . $tm;
		}
		return $retval;
	}


	/**
	 * dtMeseIta($mese, $opzioni = B2_DT_MESELUNGO)
	 * Ritorna il nome di un mese in italiano
	 * 
	 * Opzioni:
	 *  B2_DT_MESELUNGO nome intero del mese (default)
	 *  B2_DT_MESE3 prime 3 lettere del nome
	 * 
	 * 20150315 prima versione
	 *
	 */
	function dtMeseIta($mese, $opzioni = B2_DT_MESELUNGO) {
		$retval = '';
		if ($opzioni & B2_DT_MESELUNGO) {
			switch ($mese) {
				case 1: $retval = 'Gennaio'; break;
				case 2: $retval = 'Febbraio'; break;
				case 3: $retval = 'Marzo'; break;
				case 4: $retval = 'Aprile'; break;
				case 5: $retval = 'Maggio'; break;
				case 6: $retval = 'Giugno'; break;
				case 7: $retval = 'Luglio'; break;
				case 8: $retval = 'Agosto'; break;
				case 9: $retval = 'Settembre'; break;
				case 10: $retval = 'Ottobre'; break;
				case 11: $retval = 'Novembre'; break;
				case 12: $retval = 'Dicembre'; break;
				default: $retval = ''; break;
			}
		}
		if ($opzioni & B2_DT_MESE3) {
			switch ($mese) {
				case 1: $retval = 'Gen'; break;
				case 2: $retval = 'Feb'; break;
				case 3: $retval = 'Mar'; break;
				case 4: $retval = 'Apr'; break;
				case 5: $retval = 'Mag'; break;
				case 6: $retval = 'Giu'; break;
				case 7: $retval = 'Lug'; break;
				case 8: $retval = 'Ago'; break;
				case 9: $retval = 'Set'; break;
				case 10: $retval = 'Ott'; break;
				case 11: $retval = 'Nov'; break;
				case 12: $retval = 'Dic'; break;
				default: $retval = ''; break;
			}
		};
		return $retval;
	}


	/**
	 * dtDowIta($dow, $opzioni = B2_DT_DOWLUNGO)
	 * Ritorna un giorno della settimana in italiano (1 = lunedi')
	 * 
	 * Opzioni:
	 *  B2_DT_DOWLUNGO nome intero del giorno (default)
	 *  B2_DT_DOW3 prime 3 lettere del nome
	 * 
	 * 20150320 prima versione
	 *
	 */
	function dtDowIta($dow, $opzioni = B2_DT_DOWLUNGO) {
		$retval = '';
		if ($opzioni & B2_DT_DOWLUNGO) {
			switch ($dow) {
				case 1: $retval = 'Luned&igrave;'; break;
				case 2: $retval = 'Marted&igrave;'; break;
				case 3: $retval = 'Mercoled&igrave;'; break;
				case 4: $retval = 'Gioved&igrave;'; break;
				case 5: $retval = 'Venerd&igrave;'; break;
				case 6: $retval = 'Sabato'; break;
				case 7: $retval = 'Domenica'; break;
				default: $retval = ''; break;
			}
		}
		if ($opzioni & B2_DT_DOW3) {
			switch ($dow) {
				case 1: $retval = 'Lun'; break;
				case 2: $retval = 'Mar'; break;
				case 3: $retval = 'Mer'; break;
				case 4: $retval = 'Gio'; break;
				case 5: $retval = 'Ven'; break;
				case 6: $retval = 'Sab'; break;
				case 7: $retval = 'Dom'; break;
				default: $retval = ''; break;
			}
		};
		return $retval;
	}

	/**
	 * rigaEdit($descrizione, $campo, $opzioni)
	 * Ritorna una riga di una tabella con una cella (descrizione) allineata a destra e una cella (campo) allineata a sinistra
	 *
	 * Opzioni:
	 *    B2_ED_VTOP: valign=top in entrambe le celle
	 * 
	 * 20150319 prima versione
	 * 20150326 aggiunte le opzioni e B2_ED_VTOP
	 *
	 */
	function rigaEdit($descrizione, $campo, $opzioni = 0) {
		$valign = $opzioni & B2_ED_VTOP ? " valign='top'" : '';
		return "\n<tr><td$valign align='right'><b>$descrizione</b></td><td$valign align='left'>$campo</td></tr>";
	}


	/**
	 * rigaEditAuto($tag, $valore = '', $altrotesto = '')
	 * Ritorna una riga automatica di una tabella di edit in base al tag del campo
	 *
	 * Parametri:
	 *	$tag        tag del campo
	 *	$valore     valore da visualizzare
	 *	$altrotesto altro testo da aggiungere all'interno del tag
   *
	 * 20160605 prima versione
	 *
	 */
	function rigaEditAuto($tag, $valore = '', $altrotesto = '') {
		$q = $this->mydb->query("SELECT dida,tipo FROM " . $this->tabcampi . " WHERE tag='" . $this->mydb->escape_string($tag) . "'");
		if ($q->num_rows > 0) {
			$r = $q->fetch_array();
			$valign = $r['tipo'] == 'TXTAR' ? " valign='top'" : '';
			$b = "\n<tr><td$valign align='right'><b>$r[dida]</b></td><td$valign align='left'>" . $this->inputAuto($tag, $valore, $altrotesto) . "</td></tr>";
		} else {
			$b = "\n<b><br/>@@@ERRORE@@@</b> rigaEditAuto: $tag<br/>\n";
		}
		return $b;
	}


	/**
	 * intestazioneTabella($atitoli)
	 * Ritorna una riga con l'intestazione di una tabella 
	 * 
	 * 20150326 prima versione
	 *
	 */
	function intestazioneTabella($atitoli) {
		$b = "\n<tr>";
		foreach($atitoli as $titolo) $b .= "<th align='center'><b>$titolo</b></th>";
		$b .= "</tr>";
		return $b;
	}


	/**
	 * normalizza($testo, $opzioni = B2_NORM_SQL)
	 * Ritorna il testo normalizzato, filtrato, ripulito, codificato, asciugato, stirato e pronto all'uso
	 * 
	 * Opzioni:
	 *	B2_NORM_SQL   normalizzazioneper MySQL (default)
	 *	B2_NORM_TRIM  trim degli spazi
	 * 	B2_NORM_FORM  normalizzazione per i form
	 *	B2_NORM_UPPER testo convertito in maiuscolo
	 * 	B2_NORM_LOWER testo convertito in minuscolo
	 * 
	 * 20150322 prima versione
	 * 20150503 aggiunto upper e lower
	 * 20150509 default a B2_NORM_SQL
	 * 20150814 l'handle del database e' passato come parametro
	 * 20161013 aggiunto B2_NORM_FILTNUM
	 *
	 */
	function normalizza($testo, $opzioni = B2_NORM_SQL) {
		if ($opzioni & B2_NORM_SQL) {
			$testo = $this->mydb->escape_string($testo);
		} 
		if ($opzioni & B2_NORM_TRIM) {
			$testo = trim($testo);
		} 
		if ($opzioni & B2_NORM_FORM) { 
			$testo = str_replace('"', '&quot;', $testo);
		}
		if ($opzioni & B2_NORM_UPPER) { 
			$testo = strtoupper($testo);
		}
		if ($opzioni & B2_NORM_LOWER) { 
			$testo = strtolower($testo);
		}
		if ($opzioni & B2_NORM_FILTNUM) { 
			$testo = str_replace(',', '', $testo);
			$testo = str_replace('.', '', $testo);
		}
		return $testo;
	}

	
	/**
	 * inputAuto($tag, $valore='', $altrotesto='')
	 * Ritorna un campo di input automatico
	 * 
	 * Parametri:
	 *	$tag        tag del campo
	 *	$valore     valore da visualizzare
	 *	$altrotesto altro testo da aggiungere all'interno del tag
	 * 
	 * 20160605 prima versione
	 * 20160611 testodopo; EURO0
	 * 20160612 INT
	 *
	 */
	function inputAuto($tag, $valore='', $altrotesto='') {
		$q = $this->mydb->query("SELECT * FROM " . $this->tabcampi . " WHERE tag='" . $this->mydb->escape_string($tag) . "'");
		if ($q->num_rows > 0) {
			$r = $q->fetch_array();
			if ('' == $valore) {
				$valore = $r['html_default'];
				$isdefault = true;
			} else {
				$isdefault = false;
			}
			switch ($r['tipo']) {
				case 'TEXT':
					$b = "<input type='text' name='$r[html_id]' id='$r[html_id]' value=\"" . $this->normalizza($valore, B2_NORM_FORM) . "\"";
					$b .= " size='$r[html_size]' maxlength='$r[html_maxlength]'";
					if ('' != $r['html_placeholder']) $b .= " placeholder=\"" . $this->normalizza($r['html_placeholder'], B2_NORM_FORM) . "\"";
					if ('1' == $r['isrequired']) $b .= " required='required'";
					if ('' != $altrotesto) $b .= " $altrotesto";
					$b .= '/>';
				break;
				case 'EMAIL':
					$valore = str_replace(' ', ',', trim($valore));
					$b = "<input type='email' name='$r[html_id]' id='$r[html_id]' value=\"" . $this->normalizza($valore, B2_NORM_FORM) . "\"";
					$b .= " size='$r[html_size]' maxlength='$r[html_maxlength]'";
					if ('' != $r['html_placeholder']) $b .= " placeholder=\"" . $this->normalizza($r['html_placeholder'], B2_NORM_FORM) . "\"";
					if ('1' == $r['isrequired']) $b .= " required='required'";
					if ('1' == $r['html_ismultiple']) $b .= " multiple='multiple'";
					if ('' != $altrotesto) $b .= " $altrotesto";
					$b .= '/>';
				break;
				case 'TXTAR':
					$b = "<textarea name='$r[html_id]' id='$r[html_id]' cols='$r[html_size]' rows='$r[html_maxlength]'";
					if ('1' == $r['isrequired']) $b .= " required='required'";
					if ('' != $altrotesto) $b .= " $altrotesto";
					if ('' != $r['html_placeholder']) $b .= " placeholder=\"" . $this->normalizza($r['html_placeholder'], B2_NORM_FORM) . "\"";
					$b .= '>' . $this->normalizza($valore, B2_NORM_FORM) . '</textarea>';
				break;
				case 'NUM00':		// numerico registrato come x100 nel DB
					if (!$isdefault) $valore = $valore / 100;
					$b = "<input type='number' name='$r[html_id]' id='$r[html_id]' value=\"" . $this->normalizza($valore, B2_NORM_FORM) . "\"";
					$b .= " size='$r[html_size]' maxlength='$r[html_maxlength]'";
					$b .= " min='$r[html_min]' max='$r[html_max]' step='$r[html_step]'";
					if ('' != $r['html_placeholder']) $b .= " placeholder=\"" . $this->normalizza($r['html_placeholder'], B2_NORM_FORM) . "\"";
					if ('1' == $r['isrequired']) $b .= " required='required'";
					if ('' != $altrotesto) $b .= " $altrotesto";
					$b .= " style='text-align:right;'/>";
				break;
				case 'EURO0':		// euro registrato come x100 nel DB
					if (!$isdefault) $valore = number_format($valore / 100, 2, ',', '');
					$b = "<input type='text' name='$r[html_id]' id='$r[html_id]' value=\"" . $this->normalizza($valore, B2_NORM_FORM) . "\"";
					$b .= " size='$r[html_size]' maxlength='$r[html_maxlength]'";
					if ('' != $r['html_placeholder']) $b .= " placeholder=\"" . $this->normalizza($r['html_placeholder'], B2_NORM_FORM) . "\"";
					if ('1' == $r['isrequired']) $b .= " required='required'";
					if ('' != $altrotesto) $b .= " $altrotesto";
					$b .= " style='text-align:right;'/>&#8364;";
				break;
				case 'INT':		// numero intero
					$b = "<input type='number' name='$r[html_id]' id='$r[html_id]' value=\"" . $this->normalizza($valore, B2_NORM_FORM) . "\"";
					$b .= " size='$r[html_size]' maxlength='$r[html_maxlength]'";
					$b .= " min='$r[html_min]' max='$r[html_max]' step='$r[html_step]'";
					if ('' != $r['html_placeholder']) $b .= " placeholder=\"" . $this->normalizza($r['html_placeholder'], B2_NORM_FORM) . "\"";
					if ('1' == $r['isrequired']) $b .= " required='required'";
					if ('' != $altrotesto) $b .= " $altrotesto";
					$b .= " style='text-align:right;'/>";
				break;
				case 'DATA0':  // data, centrata, default oggi formato d/m/Y
					if ($isdefault) $valore = date($valore);  // il default nel db è la mask per il formato data
					$b = "<input type='text' name='$r[html_id]' id='$r[html_id]' value=\"" . $this->normalizza($valore, B2_NORM_FORM) . "\"";
					$b .= " size='12' maxlength='10'";
					if ('' != $r['html_placeholder']) $b .= " placeholder=\"" . $this->normalizza($r['html_placeholder'], B2_NORM_FORM) . "\"";
					if ('1' == $r['isrequired']) $b .= " required='required'";
					if ('' != $altrotesto) $b .= " $altrotesto";
					$b .= " style='text-align:center;'/>";
				break;
			}
			if ('' != $r['testodopo']) $b .= $r['testodopo'];
		} else {
			$b = "\n<b><br/>@@@ERRORE@@@</b> inputAuto: $tag<br/>\n";
		}
		return $b;
	}
	
	
	/**
	 * inputText($campo, $valore='', $size='', $maxlength='', $id='', $altrotesto='', $opzioni = 0)
	 * Ritorna un campo di input testo
	 * 
	 * Parametri:
	 *	$campo      nome del campo
	 *	$valore     valore da visualizzare
	 *	$size       dimensione del campo
	 *	$maxlength  dimensione massima del testo nel campo
	 *	$id         id del campo, se nullo e' uguale al nome
	 *	$altrotesto altro testo da aggiungere all'interno del tag
	 *	$opzioni    opzioni:
   *              B2_INPUT_REQUIRED aggiunge required
   *              B2_IT_RIGHT allienato a destra
   *              B2_IT_CENTER centrato
	 * 
	 * 20150408 prima versione
	 * 20150616 allineamento (bella Fabry!)
	 * 20160605 $align diventa $opzioni; aggiunto B2_INPUT_REQUIRED 
	 *
	 */
	function inputText($campo, $valore='', $size='', $maxlength='', $id='', $altrotesto='', $opzioni = 0) {
		$b = "<input type='text' name='$campo'";
		if ('' != $valore) {
			$valore = $this->normalizza($valore, B2_NORM_FORM);
			$b .= " value=\"$valore\"";
		}
		if ('' != $size) $b .= " size='$size'";
		if ('' != $maxlength) {
			$b .= " maxlength='$maxlength'";
		} else {
			if ('' != $size) $b .= " maxlength='$size'";
		}
		if ('' == $id) {
			if ('p[' != substr($campo, 0, 2)) {
				$b .= " id='$campo'";
			}
		} else {
			$b .= " id='$id'";
		}
		if ('' != $altrotesto) $b .= " $altrotesto";
		if ($opzioni & B2_IT_RIGHT) $b .= " style='text-align:right;'";
		if ($opzioni & B2_IT_CENTER) $b .= " style='text-align:center;'";
		if ($opzioni & B2_INPUT_REQUIRED) $b .= " required='required'";
		$b .= "/>";
		return $b;
	}


	/**
	 * inputCheck($campo, $checked, $id='', $altrotesto='', $opzioni = 0)
	 * Ritorna un campo checkbox
	 * 
	 * Parametri:
	 *	$campo      nome del campo
	 *	$checked    booleano, se true il campo e' checked
	 *	$id         id del campo, se nullo e' uguale al nome
	 *	$altrotesto altro testo da aggiungere all'interno del tag
	 *	$opzioni    opzioni:
   *              B2_INPUT_REQUIRED aggiunge required
   *
	 * 20150408 prima versione
	 * 20150620 aggiunto $altrotesto
	 * 20160605 aggiunto parametro $opzioni
	 *
	 */
	function inputCheck($campo, $checked=false, $id='', $altrotesto='', $opzioni = 0) {
		$b = "<input type='checkbox' name='$campo'";
		if ($checked) $b .= " checked";
		if ('' == $id) {
			if ('p[' != substr($campo, 0, 2)) {
				$b .= " id='$campo'";
			}
		} else {
			$b .= " id='$id'";
		}
		if ('' != $altrotesto) $b .= " $altrotesto";
		if ($opzioni & B2_INPUT_REQUIRED) $b .= " required='required'";
		$b .= "/>";
		return $b;
	}


	/**
	 * inputHidden($campo, $valore, $id='')
	 * Ritorna un campo nascosto
	 * 
	 * Parametri:
	 *	$campo      nome del campo
	 *	$valore     valore da assegnare
	 *	$id         id del campo, se nullo e' uguale al nome
	 * 
	 * 20150618 prima versione
	 * 20170407 tolto check per campo vuoto
	 *
	 */
	function inputHidden($campo, $valore, $id='') {
		$b = "<input type='hidden' name='$campo'";
		$valore = $this->normalizza($valore, B2_NORM_FORM);
		$b .= " value=\"$valore\"";
		if ('' == $id) {
			$b .= " id='$campo'";
		} else {
			$b .= " id='$id'";
		}
		$b .= "/>";
		return $b;
	}


	/**
	 * inputTextarea($campo, $valore, $larghezza, $altezza, $id='', $altrotesto='', $opzioni = 0)
	 * Ritorna un'area di testo multiriga
	 * 
	 * Parametri:
	 *	$campo      nome del campo
	 *	$valore     valore da assegnare
	 *	$larghezza  larghezza (colonne) della textarea in numero di caratteri
	 *	$altezza    altezza (righe) della textarea
	 *	$id         id del campo, se nullo e' uguale al nome
	 *	$altrotesto altro testo da aggiungere all'interno del tag
	 *	$opzioni    opzioni:
   *              B2_INPUT_REQUIRED aggiunge required
	 * 
	 * 20150618 prima versione
	 * 20150830 compattazione codice
	 * 20150901 $altrotesto
	 * 20160605 aggiunto parametro $opzioni
	 *
	 */
	function inputTextarea($campo, $valore, $larghezza, $altezza, $id='', $altrotesto='', $opzioni = 0) {
		$b = "<textarea name='$campo' rows='$altezza' cols='$larghezza'";
		if ($opzioni & B2_INPUT_REQUIRED) $b .= " required='required'";
		if ('' == $id) {
			$b .= " id='$campo'";
		} else {
			$b .= " id='$id'";
		}
		if ('' != $altrotesto) $b .= " $altrotesto";
		$b .= '>' . $this->normalizza($valore, B2_NORM_FORM) . '</textarea>';
		return $b;
	}


	/**
	 * inputSelect($campo, $avalori, $selected='', $id='', $altrotesto='', $opzioni=0)
	 * Ritorna una combo box
	 * 
	 * Parametri:
	 *	$campo      nome del campo
	 *	$avalori    array dei valori da visualizzare
	 *	$selected   valore della combo box
	 *	$id         id del campo, se nullo e' uguale al nome
	 *	$altrotesto altro testo da aggiungere all'interno del tag
	 *  $opzioni    opzioni alternative
	 *              B2_SE_3EQUAL  triple equal nei test di SELECTED
	 *
	 * 20150430 prima versione
	 * 20150509 default per $selected
	 * 20150512 altrotesto nei parametri
	 * 20150910 triple_equal nei parametri
	 *
	 */
	function inputSelect($campo, $avalori, $selected='', $id='', $altrotesto='', $opzioni = 0) {
		$triple_equal = $opzioni & B2_SE_3EQUAL;
		$id = $id == '' ? $campo : $id;
		$b = "<select name='$campo' id='$id'";
		if ('' != $altrotesto) $b .= " $altrotesto";
		$b .= ">";
		foreach ($avalori as $valore=>$messaggio) {
			$b .= "<option value='$valore'";
			if ($triple_equal) {
				if ($valore === $selected) $b .= ' selected';
			} else {
				if ($valore == $selected) $b .= ' selected';
			}
			$b .= ">$messaggio</option>";
		}
		$b .= "</select>";
		return $b;
	}


	/**
	 * risolviIP($ip)
	 * Risolve il nome di un IP
	 * 
	 * 20150408 prima versione
	 *
	 */
	function risolviIP($ip) {
		$retval = '';
		if ('' != trim($ip)) {
			$hostname = gethostbyaddr($ip);
			$retval = $ip == $hostname ? $ip : "$hostname ($ip)";
		}
		return $retval;
	}


	/**
	 * chkCF($cf)
	 * Verifica un codice fiscale
	 * 
	 * 20150501 prima versione
	 *
	 */
	function chkCF($cf) {
		if ('' == $cf) return false; 
		if (strlen($cf) != 16) return false; 
		$cf = strtoupper($cf); 
		if (!preg_match("/[A-Z0-9]+$/", $cf)) return false; 
		$s = 0; 
		for($i=1; $i<=13; $i+=2) { 
			$c = $cf[$i]; 
			if('0' <= $c and $c<= '9') $s += ord($c) - ord('0'); 
			else $s += ord($c) - ord('A'); 
		} 
		for ($i=0; $i<=14; $i+=2) { 
			$c = $cf[$i]; 
			switch ($c) { 
				case '0': $s += 1;  break; 
				case '1': $s += 0;  break; 
				case '2': $s += 5;  break; 
				case '3': $s += 7;  break; 
				case '4': $s += 9;  break; 
				case '5': $s += 13; break; 
				case '6': $s += 15; break; 
				case '7': $s += 17; break; 
				case '8': $s += 19; break; 
				case '9': $s += 21; break; 
				case 'A': $s += 1;  break; 
				case 'B': $s += 0;  break; 
				case 'C': $s += 5;  break; 
				case 'D': $s += 7;  break; 
				case 'E': $s += 9;  break; 
				case 'F': $s += 13; break; 
				case 'G': $s += 15; break; 
				case 'H': $s += 17; break; 
				case 'I': $s += 19; break; 
				case 'J': $s += 21; break; 
				case 'K': $s += 2;  break; 
				case 'L': $s += 4;  break; 
				case 'M': $s += 18; break; 
				case 'N': $s += 20; break; 
				case 'O': $s += 11; break; 
				case 'P': $s += 3;  break; 
				case 'Q': $s += 6;  break; 
				case 'R': $s += 8;  break; 
				case 'S': $s += 12; break;
				case 'T': $s += 14; break; 
				case 'U': $s += 16; break; 
				case 'V': $s += 10; break; 
				case 'W': $s += 22; break; 
				case 'X': $s += 25; break; 
				case 'Y': $s += 24; break; 
				case 'Z': $s += 23; break; 
			} 
		} 
		if (chr($s%26 + ord('A')) != $cf[15]) return false; 
		return true; 
	} 


	/**
	 * chkPIita($partitaIVA)
	 * Verifica partita IVA italiana
	 * 
	 * 20150501 prima versione
	 *
	 */
	function chkPIita($partitaIVA) {
		if( '' == $partitaIVA) return false;
		//la p.iva deve essere lunga 11 caratteri
		if(strlen($partitaIVA) != 11) return false;
		//la p.iva deve avere solo cifre
		if(!ereg("^[0-9]+$", $partitaIVA)) return false;
		$primo = 0;
		for($i = 0; $i <= 9; $i += 2) $primo+= ord($partitaIVA[$i]) - ord('0');
		for($i = 1; $i <= 9; $i += 2 ) {
			$secondo = 2 * (ord($partitaIVA[$i])-ord('0'));
			if ($secondo > 9) $secondo = $secondo - 9;
			$primo += $secondo;
		}
		if ((10 - $primo % 10) % 10 != ord($partitaIVA[10]) - ord('0')) return false;
		return true;
	}


	/**
	 * campoSqlAuto($tag, $valore)
	 * Crea automaticamente un campo di SQL per le insert/update
	 *
	 * Parametri:
	 *	$tag     tag del campo
	 *	$valore  valore da salvare
   *
	 * 20160605 prima versione
	 * 20160611 EURO0
	 * 20160612 INT
	 * 20160920 nome del campo delimitato
	 *
	 */
	function campoSqlAuto($tag, $valore) {
		$q = $this->mydb->query("SELECT * FROM " . $this->tabcampi . " WHERE tag='" . $this->mydb->escape_string($tag) . "'");
		if ($q->num_rows > 0) {
			$r = $q->fetch_array();
			if ('1' == $r['sql_istrim']) $valore = trim($valore);
			if ('L' == $r['sql_case']) $valore = strtolower($valore);
			if ('U' == $r['sql_case']) $valore = strtoupper($valore);
			switch ($r['tipo']) {
				case 'NUM00':  // numero che nel db è x100
				case 'EURO0':  // euro che nel db è x100
					$valore = str_replace(',', '.', $valore); // se per caso l'utente ha messo la virgola al posto del punto decimale
					$valore = $valore * 100;
					$b = "`$r[sql_campo]`='" . $this->normalizza($valore) . "'";
				break;
				case 'INT':  // intero
					if (!is_numeric($valore)) $valore = $r['html_default'];
					$b = "`$r[sql_campo]`='" . $this->normalizza($valore) . "'";
				break;
				case 'DATA0':  // data in italiano zero filled
					if ('' == $valore) {
						$b = "`$r[sql_campo]`=''";
					}	else {
						list($dd,$mm,$yy) = explode('/', $valore);
						if (checkdate($mm, $dd, $yy)) {
							$b = "`$r[sql_campo]`='$yy-$mm-$dd'";
						} else {
							$b = "`$r[sql_campo]`=''";
						}
					}					
				break;
				case 'EMAIL':
					$b = "`$r[sql_campo]`='" . $this->normalizza(str_replace(',', ' ', $valore)) . "'";
				break;
				default:
					$b = "`$r[sql_campo]`='" . $this->normalizza($valore) . "'";
			}
		} else {
			error_log("@@@ERRORE@@@ campoSqlAuto: $tag");
			return false;
		}
		return $b;
	}


	/**
	 * campoSQL($campo, $valore, $par_normalizza = B2_NORM_SQL)
	 * Crea un campo di SQL per le insert/update
	 * 
	 * 20150603 prima versione
	 * 20160920 nome del campo delimitato
	 *
	 */
	function campoSQL($campo, $valore, $par_normalizza = B2_NORM_SQL) {
		$retval ="`$campo`='" . $this->normalizza($valore, $par_normalizza) . "'";
		return $retval;
	}


	/**
	 * uuid()
	 * Ritorna uno UUID pseudorandom da 36 byte
	 * 
	 * 20150624 prima versione
	 *
	 */
	function uuid() {
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
      // 16 bits for "time_mid"
			mt_rand(0, 0xffff),
			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,
			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,
			// 48 bits for "node"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}
	

	/**
	 * creaArraySelect($query)
	 * Ritorna un array pronto per la funzione inputSelect()
	 * La query deve essere una SELECT con due campi: ID e valore
	 * 
	 * 20150624 prima versione
	 * 20150814 l'handle del database e' passato come parametro
	 *
	 */
	function creaArraySelect($query) {
		$a = array();
		$q = $this->mydb->query($query);
		while ($r = $q->fetch_array()) $a[$r[0]] = $this->normalizza($r[1], B2_NORM_FORM);
		return $a;
	}


	/**
	 * chkEmail($email)
	 * Verifica un indirizzo di posta elettronica
	 * 
	 * 20150801 prima versione
	 *
	 */
	function chkEmail($email) {
		$validmail = FALSE;
		// First, we check that there's one @ symbol, 
		// and that the lengths are right.
		if (ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
			list($localpart,$domain) = explode('@', $email, 2);
			if (ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&?'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $localpart)) {
				// nessun numerico per la parte di dominio
				if (!ereg("^\[?[0-9\.]+\]?$", $domain)) {
					if (checkdnsrr($domain)) {
						$validmail = TRUE;
					}
				}
			}
		}
		return $validmail;
	}


}

// ### END OF FILE ###
