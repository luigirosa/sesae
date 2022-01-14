<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/>
		<link rel='stylesheet' type='text/css' href='https://fonts.googleapis.com/css?family=Open+Sans|Open+Sans:b' />
		<link rel='stylesheet' href='static/stili.css' type='text/css'/>
		<meta http-equiv='X-UA-Compatible' content='IE=edge'/>
		<meta name='viewport' content='width=device-width, initial-scale=1'/>
    <link rel="author" href="mailto:sesae@sesae.com">
		<title>SESAE - Download</title>
	</head>
	<body>
    <header>
      <h1>SESAE - Download</h1>
      <nav>
        <ul class="lista-nostile">
          <li><a rel="home" href="index.html" title="pagina principale">Home</a></li>
          <li><a href="mostradati.php" title="Mostra i dati raccolti in tempo reale">Visualizzazione dei dati raccolti</a></li>
          <li><a href="elencodati.html" title="Elenco dei dati raccolti">Elenco dati raccolti</a></li>
          <li><a href="faq.html" title="Risposte alle domande pi&ugrave; comuni">FAQ</a>
          </li>
        </ul>
      </nav>
    </header>
    <section>
      <p>Da questa pagina &egrave; possibile scaricare i dataset dei dati raccolti per poterli elaborare e pubblicare in proprio secondo la licenza <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International</a>.</p>
      <table border=0 cellpadding=4 cellspacing=0>
        <tr>
          <td><a href="dati/target.zip">target.zip</a></td>
          <td><?php echo date("d/m/Y", filemtime("dati/target.zip")) ?></td>
          <td align="right"><?php echo number_format(filesize("dati/target.zip"), 0, ',', '.') ?></td>
          <td>Dati dei siti analizzati, questo &egrave; probabilmente il file che state cercando, le altre tabelle hanno campi legati a questa tabella</td>
        </tr>  
        <tr>
          <td><a href="dati/categorie.zip">categorie.zip</a></td>
          <td><?php echo date("d/m/Y", filemtime("dati/categorie.zip")) ?></td>
          <td align="right"><?php echo number_format(filesize("dati/categorie.zip"), 0, ',', '.') ?></td>
          <td>Categorie dei target, campo di join: <code>idcategory</code></td>
        </tr>
        <tr>
          <td><a href="dati/dns.zip">dns.zip</a></td>
          <td><?php echo date("d/m/Y", filemtime("dati/dns.zip")) ?></td>
          <td align="right"><?php echo number_format(filesize("dati/dns.zip"), 0, ',', '.') ?></td>
          <td>DNS autoritari, campo di join: <code>idtarget</code></td>
        </tr>
        <tr>
          <td><a href="dati/httpserver.zip">httpserver.zip</a></td>
          <td><?php echo date("d/m/Y", filemtime("dati/httpserver.zip")) ?></td>
          <td align="right"><?php echo number_format(filesize("dati/httpserver.zip"), 0, ',', '.') ?></td>
          <td>Server http, campo di join: <code>idtarget</code></td>
        </tr>
        <tr>
          <td><a href="dati/header.zip">header.zip</a></td>
          <td><?php echo date("d/m/Y", filemtime("dati/header.zip")) ?></td>
          <td align="right"><?php echo number_format(filesize("dati/header.zip"), 0, ',', '.') ?></td>
          <td>Header trasmessi dal server http, campo di join: <code>idtarget</code></td>
        </tr>
        <tr>
          <td><a href="dati/generator.zip">generator.zip</a></td>
          <td><?php echo date("d/m/Y", filemtime("dati/generator.zip")) ?></td>
          <td align="right"><?php echo number_format(filesize("dati/generator.zip"), 0, ',', '.') ?></td>
          <td>HTML generator, campo di join: <code>idtarget</code></td>
        </tr>
        <tr>
          <td><a href="dati/mx.zip">mx.zip</a></td>
          <td><?php echo date("d/m/Y", filemtime("dati/mx.zip")) ?></td>
          <td align="right"><?php echo number_format(filesize("dati/mx.zip"), 0, ',', '.') ?></td>
          <td>SMTP mail exchanger, campo di join: <code>idtarget</code></td>
        </tr>
        <tr>
          <td><a href="dati/poweredby.zip">poweredby.zip</a></td>
          <td><?php echo date("d/m/Y", filemtime("dati/poweredby.zip")) ?></td>
          <td align="right"><?php echo number_format(filesize("dati/poweredby.zip"), 0, ',', '.') ?></td>
          <td>Powered by (script engine lato server), campo di join: <code>idtarget</code></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><a href="dati/campostorico.zip">campostorico.zip</a></td>
          <td><?php echo date("d/m/Y", filemtime("dati/campostorico.zip")) ?></td>
          <td align="right"><?php echo number_format(filesize("dati/campostorico.zip"), 0, ',', '.') ?></td>
          <td>Tabella dei campi storici con id e descrizione</td>
        </tr>
        <tr>
          <td><a href="dati/storicog.zip">storicog.zip</a></td>
          <td><?php echo date("d/m/Y", filemtime("dati/storicog.zip")) ?></td>
          <td align="right"><?php echo number_format(filesize("dati/storicog.zip"), 0, ',', '.') ?></td>
          <td>Dati storici giornalieri a partire da inizio 2022.<br/>
              Campo di join per il tipo di dato con la tabella <code>campostorico</code>: <code>idcampostorico</code><br/>
              Campo di join per la categoria tipo di dato con la tabella <code>categorie</code>: <code>idcategory</code> (se idcategory=0, il dato &egrave; riferito a tutte le categorie)
          </td>
        </tr>
      </table>
      <p>I testi sono codificati in UTF-8, tenetelo presente quando importate i file.</p>
      <p>Quando Microsoft Excel localizzato in italiano apre un file CSV presume che sia separato da punto e virgola anzich&eacute; da virgola, 
         ignorando completamnte il significato di <b>Comma</b>-Separated Values.
         Per risolvere il problema, consultare <a href="https://support.microsoft.com/it-it/office/importare-o-esportare-file-di-testo-txt-o-csv-5250ac4c-663c-47ce-937b-339e391393ba">questa pagina del manuale di Excel</a>, 
         oppure il <a href="https://support.microsoft.com/it-it/office/importazione-guidata-testo-c5b02af6-fda1-4440-899f-f78bafe41857">dettaglio dell'importazione guidata testo</a>.<br/>
         O, molto pi&ugrave; semplicemente, usate LibreOffice Calc.
      </p>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
    </section>
    <footer id="site-footer">
      <p><a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/"><img alt="Creative Commons Licence" style="border-width:0" src="https://i.creativecommons.org/l/by-sa/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International License</a>.</p>
      <p>Per info e suggerimenti: <img src="static/sesaeaddr.png" border="0" alt="anti harvesting" /></p>
      <p>Copyright &copy; 2022 Luigi Rosa</p>
    </footer>    
  </body>
</html>
