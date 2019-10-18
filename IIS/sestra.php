<?php
    require_once "lib/db-connect.php";
    session_start();
    include "uvod.php";
    $DB = new mymysql; 
    $id = $DB->dbSelectNumber("SELECT Max(id_osoby) FROM osoba") + 1;
    $idv = $DB->dbSelectNumber("SELECT Max(id_vysetreni) FROM vysetreni") + 1;
    $idpr = $DB->dbSelectNumber("SELECT Max(id_predpisu) FROM predpis") + 1;
    $idda = $DB->dbSelectNumber("SELECT Max(id_davkovani) FROM davkovani") + 1;
    if(isset($_SESSION['ids']) && !empty($_SESSION['ids'])){
        if( $_SESSION['last_activitys'] < ((time()) - $_SESSION['expire_times']) ) { 
          $_SESSION['timeouts'] = "abc";
          header('Location: timeout.php'); 
        } 
        else{ 
          $_SESSION['last_activitys'] = time(); 
        }
        $id_sestra = $_SESSION['ids'];//id doktora ktory sa prihlasil
        $meno_s = $DB->dbQuery("SELECT jmeno FROM osoba WHERE id_osoby = '$id_sestra'");
        $priezvisko_s = $DB->dbQuery("SELECT prijmeni FROM osoba WHERE id_osoby = '$id_sestra'");
        echo "<p class='r'><input type='submit' name='edit' id=$id_sestra value='Upravit profil' onclick='osoba_edit($id_sestra)'/>";
        foreach($meno_s as $m){
            foreach($priezvisko_s as $p){
                echo '<h2>Zdravotní sestra: '.$m["jmeno"].' '.$p["prijmeni"].'</h1>';
            }
        }
    }
    else{
        header('Location: index.php');
    } 
?>

<h3 onclick="zobrazSkryj('oddil1')">Přidat pacienta</h3>
<div id="oddil1" class="skryvany">
<form method="post">
<table>
    <tr><td>Jméno:</td><td> <input type="text" required name="name" /> </td></tr>
    <tr><td>Příjmení:</td><td> <input type="text" required name="surname" /></td></tr>
    <tr><td>Titul:</td><td> <input type="text" required name="titul" /></td></tr>
    <tr><td>Datum narození:</td><td>  <input type="date" required name="date" /></td></tr>
    <tr><td>Adresa: </td><td><input type="text" required name="adress" /></td></tr>
    <tr><td>Pohlaví:</td><td>   <input type="radio" required name="pohlavi" id=M value="M"><label for=M>Muž</label>
            <input type="radio" name="pohlavi" id=Z value="Z"><label for=Z>Žena</label></td></tr>
    <tr><td>Alergie:</td><td> <input type="text" name="alergie" /></td></tr>
    <tr><td>Prodělané nemoci:</td><td> <input type="text" name="nemoci" /></td></tr>
    <tr><td></td><td><input type="submit" name="submit1" value="Přidat" /></td></tr>
</table>
</form>
</div>
<?php
    //pridat pacienta
    if(isset($_POST['submit1']))
    {
	$name=$_POST['name'];
	$surname=$_POST['surname'];
	$titul=$_POST['titul'];
	$date=$_POST['date'];
	$adresa=$_POST['adress'];
	$pohlavi=$_POST['pohlavi'];
	$alergie=$_POST['alergie'];
	$nemoci=$_POST['nemoci'];
	if($DB->dbQuery("INSERT INTO osoba (id_osoby, jmeno, prijmeni, titul, datum_narozeni, adresa, pohlavi) VALUES ('$id', '$name','$surname', '$titul', '$date', '$adresa', '$pohlavi')"))
        {
            $id5 = $DB->dbSelectNumber("SELECT Max(id_pacienta) FROM pacient") + 1;
            if($DB->dbQuery("INSERT INTO pacient (id_pacienta, id_osoba, alergie, prodelane_nemoci) VALUES ('$id5', '$id', '$alergie', '$nemoci')"))
            {
                echo '<h6>Pacient byl vložen</h6>';
            }
            else
            {
                echo '<h6>Chyba při vkládání pacienta</h6>';
            }
        }
        else
        {
            echo '<h6>Chyba při vkládání pacienta</h6>';
        }
    }
?>

<h3 onclick="zobrazSkryj('oddil2')">Vyhledat pacienta</h3>
<div id="oddil2" class="skryvany">
<form  method="post">
<table>
    <tr><td> Vyhledávání:</td><td><input type="text" name="term" /></td></tr>
    <tr><td></td><td> <input type="submit" name="submit2" value="Hledej" /></td></tr>
</table>
</form>
</div>
<?php
    //vyhledat pacienta
    if(isset($_POST['submit2'])){ 
	if(preg_match("/^[  a-zA-Z]+/", $_POST['term'])){ 
            $name=$_POST['term']; 
            $result=$DB->dbSelect("SELECT * FROM osoba WHERE jmeno LIKE '%".$name."%' OR prijmeni LIKE '%".$name."%'");
            $pac=$DB->dbSelect("SELECT * FROM pacient");
            if (!$result)
            {
		echo "<h6>Osoba nenelazena</h6>";
            }
            else
            {
		echo '<table class="t"> '; //druha barva #84c3f7 for ($i=0; $i<$pocet; $i++):$temp .= "<tr class=".($i%2 ? "s" : "l").">
		echo '<tr><th>Jméno pacienta</th><th>Rok narození</th><th>Adresa</th><th>Pohlaví</th><th></th></tr>';
		foreach($result as $r)
		{
                    $id_osoba=$r["id_osoby"];
                    foreach($pac as $p)
                    {					
			if ($id_osoba==$p["id_osoba"]){	
                            echo '<tr><td>'.$r["titul"].' '.$r["jmeno"].' '.$r["prijmeni"].'</td>';
                            echo '<td>'.$r["datum_narozeni"].'</td>';
                            echo '<td>'.$r["adresa"].'</td>';
                            echo '<td>'.$r["pohlavi"].'</td>';
                            echo "<form action='' method='POST'><input type='hidden' name='del' value='".$id_osoba."'>";
                            echo "<td><input type='submit' name='submit3' id=$id_osoba value='Upravit' onclick='osoba($id_osoba)'/>";
                            echo "</form></td> </tr> ";
			}
                    }
		}
		echo '</table>';
            }
	}
	else
	{ 
            echo "Špatně zadané znaky"; 
	}
    }
?>
<h3 onclick="zobrazSkryj('oddil3')">Přidat vyšetření</h3>
<div id="oddil3" class="skryvany">
<form method="post">
<table>
    <tr><td>Doktor:</td><td>
    <?php
        $dok=$DB->dbSelect("SELECT * FROM osoba, doktor WHERE doktor.id_osoba=osoba.id_osoby");
	echo "<select name='doktor'>";
	foreach ($dok as $d)
	{
            echo "<option value=".$d["id_doktora"].">".$d["jmeno"]." ".$d["prijmeni"]."</option>\n";
	}
        echo "</select></td></tr>";
    ?>
    <tr><td>Pacient:</td><td>
    <?php
	$pac=$DB->dbSelect("SELECT * FROM osoba, pacient WHERE pacient.id_osoba=osoba.id_osoby");
	echo "<select name='pacient'>";
	foreach ($pac as $p)
	{
            echo "<option value=".$p["id_pacienta"].">".$p["jmeno"]." ".$p["prijmeni"]."</option>\n";
	}
	echo "</select></td></tr>";
    ?>
    <tr><td>Oddělení: </td><td>
    <?php
	$odd=$DB->dbSelect("SELECT * FROM nemocnicni_oddeleni, ma_na_oddeleni WHERE ma_na_oddeleni.id_oddeleni=nemocnicni_oddeleni.id_oddeleni");
	echo "<select name='oddeleni'>";
	foreach ($odd as $o)
	{
            echo "<option value=".$o["id_oddeleni"].">".$o["nazev"]."</option>\n";
	}
	echo "</select></td></tr>";
    ?>
    <tr><td>Průběh vyšetření: </td><td><input style="width:700px;" type="text" name="prubeh" /> </td></tr>
    <tr><td>Výsledek vyšetření: </td><td><input style="width:700px;" type="text" name="vysledek" /> </td></tr>
    <tr><td>Datum vyšetření:</td><td> <input type="date" name="datum" value="<? echo date("Y-m-d");?>" /> 
    <tr><td>Lék:</td><td>
    <?php
        $lek=$DB->dbSelect("SELECT * FROM lek");
	echo "<select name='lek'>";
	echo "<option value='0'>zadny</option>\n";
	foreach ($lek as $l)
	{
            echo "<option value=".$l["id_leku"].">".$l['nazev']." - ".$l['ucinne_latky']."</option>\n";
	}
	echo "</select></td></tr>";
    ?>
    <tr><td>Dávkování:</td><td><input type="text" name="davkovani" /></td></tr>
    <tr><td></td><td><input type="submit" name="submit5" value="Přidat" /></td></tr>
</table>	
</form>
</div>
<?php
 //pridat vysetreni
    if(isset($_POST['submit5']))
    {
	$oddeleni=$_POST["oddeleni"];
	$pacient=$_POST["pacient"];
	$prubeh=$_POST["prubeh"];
	$vysledek=$_POST["vysledek"];
	$davkovani=$_POST["davkovani"];
	$datum=$_POST["datum"];
	$lek=$_POST["lek"];
	$doktor=$_POST["doktor"];
        if($datum > date("Y-m-d"))
        {
             echo "<h6>Chyba - špatně zadané datum - vyšetření nepřidáno</h6>";
        }
        else {
            if($DB->dbQuery("INSERT INTO vysetreni (id_vysetreni, datum_vysetreni, prubeh_vysetreni, vysledek_vysetreni, id_doktor, id_pacient, id_oddeleni) VALUES ('$idv','$datum', '$prubeh', '$vysledek', '$doktor', '$pacient', '$oddeleni' )"))
            {
                if ($lek !="0")
                {
                    if(!($DB->dbQuery("INSERT INTO predpis (id_predpisu, id_lek, id_vysetreni) VALUES ('$idpr','$lek', '$idv' )")) || !($DB->dbQuery("INSERT INTO davkovani (id_davkovani, davka, id_lek) VALUES ('$idda','$davkovani', '$lek' )")))
                    {
                        echo "<h6>Chyba - vyšetření nepřidáno</h6>";
                    }
                }
                echo "<h6>Vyšetření přidáno</h6>";
            }
            else {
                echo "<h6>Chyba - vyšetření nepřidáno</h6>";
            }
        }
               
	
    }
?>
<?php
	include "zaver.php";
	$DB->dbClose();
?>
