<!DOCTYPE html>
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <link rel="stylesheet" href="lib/nemocnice.css">
  <title>Nemocnice</title>
  </head>
  <body>
    <header>
      <div id="logo"></div><h1>Informační systém Nemocnice</h1>
    </header>

     <div id="cont">
	 <h4><a href="logout.php">Odhlásit se</a></h4>
<script>//zobrazovani a skryvani obsahu
    function zobrazSkryj(idecko)
    {
        el=document.getElementById(idecko).style; 
        el.display=(el.display == 'block')?'none':'block';
    }

    function osoba(id)
    {
        window.open("search.php?id_osoby="+id, "Osoba", "width=400,height=450");
    }
    
    function osoba_edit(id)
    {
        window.open("edit.php?id_osoby="+id, "Osoba", "width=400,height=450");
    }


    function otevrit()
    {
        myWindow=window.open("search.php", "Osoba", "width=400,height=450");
    }

    function zavrit()
    {
       myWindow.close();
    }
</script>