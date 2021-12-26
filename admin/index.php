<?php

/**
 *
 * SESAE - Admin - Login
 *
 * @package     SESAE
 * @subpackage  SESAE Admin
 * @author      Luigi Rosa (lists@luigirosa.com)
 * @copyright   (C) 2018-2022 Luigi Rosa <lists@luigirosa.com>
 * @license     https://www.gnu.org/licenses/gpl-3.0.html   
 * 
 * 20180217 prima versione
 * 20190121 spostamento su set.sesae.com
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

session_start();
if(!empty($_SESSION['utente'])) {
	
	header('Location: home.php');
}
?>

<!doctype html>
<html>
	<head>
		<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/>
		<meta name='robots' content='noindex'/>
		<link rel="stylesheet" href="static/login.css"/>
		<link rel='stylesheet' type='text/css' href='https://fonts.googleapis.com/css?family=Open+Sans|Open+Sans:b' />
		<!-- script src="https://code.jquery.com/jquery-3.3.1.min.js"></script -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
		<script src="/static/jquery.ui.shake.js"></script>
		<meta name='viewport' content='width=device-width, initial-scale=1.0'/>	
		<meta http-equiv='cache-control' content='no-cache'/>
		<meta http-equiv='pragma' content='no-cache'/>
	</head>
	<body>
	<div id="main">
		<div id="box">
			<form action="" method="post">
				<label>Utente:</label> <input type="text" name="utente" class="input" autocomplete="off" id="utente"/>
				<label>Password:</label> <input type="password" name="password" class="input" autocomplete="off" id="password"/><br/>
				<input type="submit" class="button button-primary" value="Accesso" id="login"/> 
				<span class='msg'></span> 
				<div id="error"></div>	
			</form>	
		</div>
	</div>

	<script>
		$(document).ready(function() {
			$('#login').click(function() {
				var username=$("#utente").val();
				var password=$("#password").val();
				var dataString = 'user='+username+'&password='+password;
				if($.trim(utente).length>0 && $.trim(password).length>0) {
					$.ajax({
						type: "POST",
						url: "index.server.php",
						data: dataString,
						cache: false,
						beforeSend: function(){ $("#login").val('Attendere...');},
						success: function(data) {
							if(data == 'OK') {
								$("body").load("/home.php").hide().fadeIn(1500).delay(6000);
	            } else {
	            	$('#box').shake();
	            	$("#login").val('Accesso');
	            	$("#error").html("<span style='color:#cc0000'>Errore:</span> Utente o password errati");
	            }
	          }
	        });
				}
				return false;
			});
		});
	</script>
	</body>
</html>
