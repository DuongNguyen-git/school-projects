<?php
    require_once "lib/db-connect.php";
    $DB = new mymysql;  
?>
<!DOCTYPE html>
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <link rel="stylesheet" href="lib/nemocnice.css">
  <title>Nemocnice</title>
  </head>
  <body>
    <header>
      <div id="logo"></div><h1>IIS 2017 - Informační systém Nemocnice - zadání č.28</h1>
    </header>

     <div id="cont">

<h5>Vyberte přístup do informačního systému</h5>
<table>
<tr>
<td ><fieldset style="max-width: 400px; margin-right:100px; margin-bottom: 30px;"><legend>Ředitel</legend>
<form action="login.php" method = "post">
	<p>Příjmení: <input type="text" name="surname0" />
	<p>Heslo: <input style="margin-left: 19px;" type="password" name="heslo0" />
    <input type="submit" name="reditel" value="Vstoupit" />
</form>
</fieldset></td>
<td><fieldset style="max-width: 400px; margin-bottom: 30px;"><legend>Doktor</legend>
<form action="login.php" method = "post">
	<p>Příjmení: <input type="text" name="surname1" />
	<p>Heslo: <input style="margin-left: 19px;" type="password" name="heslo1" />
	<input type="submit" name="doktor" value="Vstoupit" />
</form>
</fieldset></td>
</tr>
<tr>
<td><fieldset style="max-width: 400px; margin-right:100px;"><legend>Lékárník</legend>
<form action="login.php" method = "post">
	<p>Příjmení: <input type="text" name="surname2" />
	<p>Heslo: <input style="margin-left: 19px;" type="password" name="heslo2" />
    
    <input type="submit" name="lekarnik" value="Vstoupit" />
</form>
</fieldset></td>
<td><fieldset style="max-width: 400px;"><legend>Zdravotní sestra</legend>
<form action="login.php" method = "post">
	<p>Příjmení: <input type="text" name="surname3" />
	<p>Heslo: <input style="margin-left: 19px;" type="password" name="heslo3" />	
    <input type="submit" name="sestra" value="Vstoupit" />
</form>
</fieldset></td>
</tr>
</table>

<?php
	include "zaver.php";
	$DB->dbClose();
?>