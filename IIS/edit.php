<?php
    require_once "lib/db-connect.php";
    include "uvod2.php";
    $DB = new mymysql; 
    if($_GET['id_osoby']){
        $person=$DB->dbSelect("SELECT * FROM osoba WHERE id_osoby=$_GET[id_osoby]");
	foreach($person as $p)
	{
            $jmeno=$p["jmeno"];
            $prijmeni=$p["prijmeni"];
            $titul=$p["titul"];
            $adresa=$p["adresa"];
	    $heslo=$p["heslo"];
        }	
	if(isset($_POST['submit3'])){ 
            $name=$_POST['name'];
            $surname=$_POST['surname'];
            $titul=$_POST['titul'];
            $date=$_POST['date'];
            $adresa=$_POST['adress'];
	    $position=$_POST['position'];
            $heslo=$_POST['heslo'];
            $telefon=$_POST['telefon'];
            $id_oddeleni=$_POST['oddeleni'];
            if($DB->dbQuery("UPDATE osoba SET jmeno='$name', prijmeni='$surname', titul='$titul', datum_narozeni='$date', adresa='$adresa', pohlavi='$pohlavi', heslo='$heslo' WHERE id_osoby=$_GET[id_osoby]"))
            {
                switch($position)
                {
                    case "doktor":
                        $uvazek=$_POST["uvazek"];
                        echo $uvazek;
                        if($DB->dbQuery("UPDATE ma_na_oddeleni, doktor SET ma_na_oddeleni.uvazek='$uvazek', ma_na_oddeleni.telefon='$telefon' WHERE doktor.id_osoba=$_GET[id_osoby] AND ma_na_oddeleni.id_doktor=doktor.id_doktora"))
                        {
                            echo "<meta http-equiv='refresh' content='0'>";
                            echo '<h6>Změny uloženy</h6>';
                        }
                        else
                        {
                            echo '<h6>Chyba při ukládání změn</h6>';
                        }
                        break;
                    case "lekarnik":
                        $umisteni=$_POST["umisteni"];
                        $povinnost=$_POST["povinnost"];
                        if($DB->dbQuery("UPDATE lekarnik SET povinnost='$povinnost', umisteni='$umisteni' WHERE id_osoba=$_GET[id_osoby]"))
                        {
                            echo "<meta http-equiv='refresh' content='0'>";
                            echo '<h6>Změny uloženy</h6>';
                        }
                        else
                        {
                            echo '<h6>Chyba při ukládání změn</h6>';
                        }
                        break;
                    case "sestra":
                        $povinnost=$_POST["povinnost"];
                        if($DB->dbQuery("UPDATE zdravotni_sestra SET povinnost='$povinnost', id_oddeleni='$id_oddeleni' WHERE zdravotni_sestra.id_osoba=$_GET[id_osoby]"))
                        {
                            echo "<meta http-equiv='refresh' content='0'>";
                            echo '<h6>Změny uloženy</h6>';
                        }
                        else
                        {
                            echo '<h6>Chyba při ukládání změn</h6>';
                        }
                        break;
		    case "reditel":
			echo '<h6>Změny uloženy</h6>';
			break;
                }
            }          
	} 
?>
<form method="post">
<table>
    <tr><td> Jméno:</td><td> <input type="text" name="name" value="<? echo $jmeno; ?>" /> </td></tr>
    <tr><td>Příjmení:</td><td> <input type="text" name="surname" value="<? echo $prijmeni; ?>" /></td></tr>
    <tr><td>Titul:</td><td> <input style="width: 100px;" type="text" name="titul" value="<? echo $titul; ?>" /></td></tr>
    <tr><td>Adresa:</td><td> <input style="width: 210px;" type="text" name="adress" value="<? echo $adresa; ?>" /></td></tr>
    <tr><td>Heslo:</td><td>  <input style="width: 130px;" type="password" name="heslo" value="<? echo $datum_narozeni; ?>" /></td></tr>
    <?php 
	$pozice1=$DB->dbSelect("SELECT * FROM doktor WHERE id_osoba=$_GET[id_osoby]");
	$pozice2=$DB->dbSelect("SELECT * FROM lekarnik WHERE id_osoba=$_GET[id_osoby]");
	$pozice3=$DB->dbSelect("SELECT * FROM zdravotni_sestra WHERE id_osoba=$_GET[id_osoby]");
	$pozice4=$DB->dbSelect("SELECT * FROM reditel WHERE id_osoba=$_GET[id_osoby]");
        if ($pozice1!=NULL)
	{
            $doktor=$DB->dbSelect("SELECT * FROM doktor, ma_na_oddeleni, nemocnicni_oddeleni,osoba WHERE ma_na_oddeleni.id_oddeleni=nemocnicni_oddeleni.id_oddeleni AND ma_na_oddeleni.id_doktor=doktor.id_doktora AND doktor.id_osoba=osoba.id_osoby AND id_osoby =$_GET[id_osoby]");
            foreach($doktor as $d)
            {
                echo ' <tr><td>Oddělení:</td><td>'.$d["nazev"].'</td></tr>';
		echo " <tr><td>Zameření: </td><td>".$d["zamereni"]."</td></tr>";
		echo " <tr><td>Obor:</td><td>".$d["obor"]."</td></tr>";
		echo " <tr><td>Úvazek:</td><td><select name='uvazek'>";
		echo "<option value='plny'".($d["uvazek"]=="plny" ? " selected" : "").">Plný</option>\n";
		echo "<option value='castecny'".($d["uvazek"]=="castecny" ? " selected" : "").">Částečný</option>\n";
		echo "</select></td></tr>";
		echo '<input type="hidden" name="position" value="doktor">';
		echo ' <tr><td> Telefon: </td><td><input style="width: 90px;" type="text" name="telefon" value='.$d["telefon"].' /></td></tr>';
            }
	}
	elseif ($pozice2!=NULL) 
	{
            $lekarnik=$DB->dbSelect("SELECT * FROM lekarnik, osoba WHERE osoba.id_osoby=lekarnik.id_osoba AND osoba.id_osoby =$_GET[id_osoby]");
            foreach($lekarnik as $l)
            {
		echo '  <tr><td> Umístění: </td><td><input type="text" name="umisteni" value='.$l["umisteni"].' /></td></tr>';
		echo '  <tr><td> Povinnost:</td><td> <input type="text" name="povinnost" value='.$l["povinnost"].' /></td></tr>';
		echo '<input type="hidden" name="position" value="lekarnik">';
            }
	}
	elseif ($pozice3!=NULL)
	{
            $sestra=$DB->dbSelect("SELECT * FROM zdravotni_sestra, nemocnicni_oddeleni,osoba WHERE zdravotni_sestra.id_sestry=nemocnicni_oddeleni.id_zdravotni_sestra AND zdravotni_sestra.id_osoba=osoba.id_osoby AND id_osoby =$_GET[id_osoby]");
            $oddeleni=$DB->dbSelect("SELECT * FROM nemocnicni_oddeleni");
            foreach($sestra as $s)
            {
		echo '  <tr><td> Povinnost:</td><td> <input type="text" name="povinnost" value='.$s["povinnost"].' /></td></tr>';
                echo " <tr><td>Oddělení:</td><td> <select name='oddeleni' id='oddeleni'>";
            	foreach($oddeleni as $o)
		{
                    echo "<option value=".$o["id_oddeleni"].">".$o["nazev"]."</option>\n";
		}
		echo "</select></td></tr>";
        	echo '<input type="hidden" name="position" value="sestra">';
		echo '<input type="hidden" name="id_zdrav" value='.$s["id_sestry"].'></td></tr>';
            }
	}
	elseif ($pozice4!=NULL)
	{   
	    echo '<input type="hidden" name="position" value="reditel">';
	}
	else
	{
            echo 'Špatně zvolená pozice';
        }
?>
    <tr><td> <input type="submit" name="submit3" value="Uložit změny" /></td><td>
    <input type="button" value="Zavřít okno" onclick="window.close()"/></td></tr>
</table>
</form>	
<?php } ?>	
	
<?php	
    include "zaver.php";
    $DB->dbClose();
?>