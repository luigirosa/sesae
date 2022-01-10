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
      <p>Da questa pagina sar&agrave; possibile scaricare i dataset dei dati raccolti per poterli elaborare e pubblicare in proprio secondo la licenza <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International</a>.</p>
      <table border=0 cellpadding=4 cellspacing=0>
        <tr>
          <td><a href="dati/categorie.zip">categorie.zip</a></td>
          <td><?php echo date("d/m/Y", filemtime("dati/categorie.zip")) ?></td>
          <td><?php echo number_format(filesize("dati/categorie.zip"), 0, ',', '.') ?></td>
          <td>Categorie dei target</td>
        </tr>  
      </table>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
    </section>
    <footer id="site-footer">
      <p><a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/"><img alt="Creative Commons Licence" style="border-width:0" src="https://i.creativecommons.org/l/by-sa/4.0/88x31.png" /></a><br />This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International License</a>.</p>
      <p>Copyright &copy; 2022 Luigi Rosa</p>
    </footer>    
  </body>
</html>
