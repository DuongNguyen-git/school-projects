<?php
    require_once "lib/db-connect.php";
    session_start();
    include "uvod.php";
    $DB = new mymysql; 
    $id = $DB->dbSelectNumber("SELECT Max(id_osoby) FROM osoba") + 1;
    $idv = $DB->dbSelectNumber("SELECT Max(id_vysetreni) FROM vysetreni") + 1;
    $idpr = $DB->dbSelectNumber("SELECT Max(id_predpisu) FROM predpis") + 1;
    $idda = $DB->dbSelectNumber("SELECT Max(id_davkovani) FROM davkovani") + 1;
    if(isset($_SESSION['idd']) && !empty($_SESSION['idd'])){
        if( $_SESSION['last_activityd'] < ((time()) - $_SESSION['expire_timed']) ) { 
          $_SESSION['timeoutd'] = "abc";
          header('Location: timeout.php'); 
        } 
        else{ 
          $_SESSION['last_activityd'] = time(); 
        }
        $doktor = $_SESSION['idd'];//id doktora ktory sa prihlasil
        $meno_d = $DB->dbQuery("SELECT jmeno FROM osoba WHERE id_osoby = '$doktor'");
        $priezvisko_d = $DB->dbQuery("SELECT prijmeni FROM osoba WHERE id_osoby = '$doktor'");
        echo "<p class='r'><input type='submit' name='edit' id=$doktor value='Upravit profil' onclick='osoba_edit($doktor)'/>";
        foreach($meno_d as $m){
            foreach($priezvisko_d as $p){
                echo '<h2>Doktor: '.$m["jmeno"].' '.$p["prijmeni"].'</h2>';
            }
        }
    }
    else{
        header('Location: index.php');
    }
?>       
<h3 onclick="zobrazSkryj('oddil1')">Vyhledat a upravit/smazat pacienta</h3>
<div id="oddil1" class="skryvany">
<form method="post">
<table>
    <tr><td>Zadat jméno:</td><td><input type="text" name="term" /></td></tr>
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
		echo '<tr><th>Jméno pacienta</th><th>Rok narození</th><th>Adresa</th><th>Pohlaví</th><th></th><th></th><th></th></tr>';
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
                            echo "<td><input type='submit' name='prehled' value='Přehled vysetreni' />  </td>";
                            echo "<td><input type='submit' name='delete' value='Smazat' />  </td>";
                            echo "</form></td> </tr> ";
                        }
                    }
		}
		echo '</table>';
            }
	}
	else
	{ 
            echo "<h6>Špatně zadané znaky</h6>"; 
	}
    }
    
    if(isset($_POST['prehled']))
    {
	$idpacienta = $_POST['del'];
	$vysetreni=$DB->dbSelect("SELECT * FROM pacient, vysetreni, osoba WHERE pacient.id_pacienta='$idpacienta' AND vysetreni.id_pacient='$idpacienta' AND pacient.id_osoba=osoba.id_osoby");		
        if(!$vysetreni)
	{	
            echo "<h6>Pacient ještě nebyl ošetřován</h6>";
	}
	else
	{
            echo '<table class="t">';
            echo "<tr><th>Datum vyšetření</th><th>Jméno pacienta</th><th>Průběh vyšetření</th><th>Výsledek vyšetření</th></tr>";
            foreach($vysetreni as $v)
            {	
		echo "<tr>";
		echo "<td>".$v["datum_vysetreni"]."</td>";
		echo "<td>".$v["jmeno"]." ".$v["prijmeni"]."</td>";
		echo "<td>".$v["prubeh_vysetreni"]."</td>";
		echo "<td>".$v["vysledek_vysetreni"]."</td>";
		echo "</tr>";
            }
            echo "</table>";
	}
		
    }
	
    //smazani			
    if(isset($_POST['delete'])){
	$id = intval($_POST['del']);
	$del=$DB->dbQuery("DELETE FROM osoba WHERE id_osoby=$id ");
	$del=$DB->dbQuery("DELETE FROM pacient WHERE id_osoba=$id ");
        if($del)
        {
            echo "<h6>Pacient byl smazán!</h6>";
        }
        else
        {
            echo "<h6>Smazání pacienta se nepovedlo!</h6>";
        }
    }
?>	
<h3 onclick="zobrazSkryj('oddil2')">Přidat vyšetření</h3>
<div id="oddil2" class="skryvany">
<form method="post">
<table>
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
    <tr><td>Průběh vyšetření:</td><td> <input style="width: 700px;" required type="text" name="prubeh" /> 
    <tr><td>Výsledek vyšetření:</td><td> <input style="width: 700px;" required type="text" name="vysledek" /> 
    <tr><td>Datum vyšetření:</td><td> <input type="date" name="datum" required value="<? echo date("Y-m-d");?>" /> 
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
    <tr><td><input type="submit" name="submit5" value="Přidat" /></td></tr>
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
	  if($datum > date("Y-m-d"))
        {
             echo "<h6>Chyba - špatně zadané datum - vyšetření nepřidáno</h6>";
        }
        else {
        $add=$DB->dbQuery("INSERT INTO vysetreni (id_vysetreni, datum_vysetreni, prubeh_vysetreni, vysledek_vysetreni, id_doktor, id_pacient, id_oddeleni) VALUES ('$idv','$datum', '$prubeh', '$vysledek', '$doktor', '$pacient', '$oddeleni' )");
        if($add)
        {
            if ($lek !="0")
            {
                $add_lek1=$DB->dbQuery("INSERT INTO predpis (id_predpisu, id_lek, id_vysetreni) VALUES ('$idpr','$lek', '$idv' )");
                $add_lek2=$DB->dbQuery("INSERT INTO davkovani (id_davkovani, davka, id_lek) VALUES ('$idda','$davkovani', '$lek' )");
                if ($add_lek1 && $add_lek2)
                {
                     echo "<h6>Vyšetření bylo přidáno.</h6>";    
                }
                else {
                      echo "<h6>Vyšetření nebylo přidáno.</h6>";    
                    }
                
            }
            else{
                echo "<h6>Vyšetření bylo přidáno.</h6>";   
            }        
        }
        else
        {
             echo "<h6>Vyšetření nebylo přidáno.</h6>";    
        }
       
    }
    }
?>
<?php
	include "zaver.php";
	$DB->dbClose();	
?>