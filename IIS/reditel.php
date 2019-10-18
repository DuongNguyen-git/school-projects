<?php
    require_once "lib/db-connect.php";
    session_start();
    include "uvod.php";
    $DB = new mymysql; 
    //autoinkrement novych zaznamu
    $id = $DB->dbSelectNumber("SELECT Max(id_osoby) FROM osoba") + 1;
    $id_s = $DB->dbSelectNumber("SELECT Max(id_oddeleni) FROM nemocnicni_oddeleni") + 1;
    if(isset($_SESSION['idr']) && !empty($_SESSION['idr'])){
        if( $_SESSION['last_activityr'] < ((time()) - $_SESSION['expire_timer']) ) { 
          $_SESSION['timeoutr'] = "abc";
          header('Location: timeout.php'); 
        } 
        else{ 
          $_SESSION['last_activityr'] = time(); 
        }
        $id_reditel = $_SESSION['idr'];//id doktora ktory sa prihlasil
        $meno_r = $DB->dbQuery("SELECT jmeno FROM osoba WHERE id_osoby = '$id_reditel'");
        $priezvisko_r = $DB->dbQuery("SELECT prijmeni FROM osoba WHERE id_osoby = '$id_reditel'");
        echo "<p class='r'><input type='submit' name='edit' id=$id_reditel value='Upravit profil' onclick='osoba_edit($id_reditel)'/>";
        foreach($meno_r as $m){
            foreach($priezvisko_r as $p){
                echo '<h2>Ředitelka: '.$m["jmeno"].' '.$p["prijmeni"].'</h2>';
            }
        }
    }
    else
    {
        header('Location: index.php');
    }

    //pridani noveho oddeleni
    if(isset($_POST['submit5']))
    {
	$oddeleni=$_POST["oddeleni"];
	$zamereni=$_POST["zamereni"];
	$sestra=$_POST["sestra"];
	$add_od1=$DB->dbQuery("INSERT INTO nemocnicni_oddeleni (id_oddeleni, nazev, zamereni, id_zdravotni_sestra) VALUES ('$id_s','$oddeleni', '$zamereni', '$sestra')");
	$add_od2=$DB->dbQuery("UPDATE zdravotni_sestra SET id_oddeleni='$id_s' WHERE id_sestry=$sestra");//to do - upravit tabulku - kde id sestry (osetrit 1 sestra = 1 oddeleni)
	if($add_od1 && $add_od2)
        {
            echo "<h6>Bylo přidáno nové oddeleni</h6>";
        }
        else
        {
            echo "<h6>Bylo přidáno nové oddeleni</h6>";
        }    
    }	
?>
<h3 onclick="zobrazSkryj('oddil1')">Vyhledat osobu</h3>
<div id="oddil1" class="skryvany">
<form method="post">
<table>
    <tr><td>Zadat jméno:</td><td> <input type="text" name="term" /></td></tr>
    <tr><td></td><td><input type="submit" name="submit1" value="Hledej" /></td></tr>
</table>
</form>
</div>
<?php
//vyhledavani
    if(isset($_POST['submit1'])){ 
	if(preg_match("/^[  a-zA-Z]+/", $_POST['term'])){ 
            $name=$_POST['term']; 
            $result=$DB->dbSelect("SELECT * FROM osoba WHERE jmeno LIKE '%" . $name .  "%' OR prijmeni LIKE '%" . $name ."%'");
            if (!$result)
            {
		echo "<h6>Osoba nenalezena</h6>";
            }
            else
            {
		echo '<table class="t"> '; //druha barva #84c3f7 for ($i=0; $i<$pocet; $i++):$temp .= "<tr class=".($i%2 ? "s" : "l").">
		echo '<tr><th>Jméno pacienta</th><th>Rok narození</th><th>Adresa</th><th>Pohlavi</th><th></th><th></th></tr>';
		foreach($result as $r)
		{
                    $id_osoba=$r["id_osoby"];	 
                    echo '<tr><td>'.$r["titul"].' '.$r["jmeno"].' '.$r["prijmeni"].'</td>';
                    echo '<td>'.$r["datum_narozeni"].'</td>';
                    echo '<td>'.$r["adresa"].'</td>';
                    echo '<td>'.$r["pohlavi"].'</td>';
                    echo "<form action='' method='POST'><input type='hidden' name='del' value='".$id_osoba."'>";
                    echo "<td><input type='submit' name='submit' id=$id_osoba value='Upravit' onclick='osoba($id_osoba)'/>";
                    echo "<td><input type='submit' name='delete' value='Smazat' />  </td>";
                    echo "</form></td> </tr> ";
		}
		echo '</table>';
            }
	}
        else
	{ 
            echo "<h6>Špatně zadané znaky</h6>"; 
	}
    }
?>

<h5>Přehled osob
<form action="reditel.php" method="post">
	<select name="listPosition">
		<option>Pozice</option>
		<option name="doktor" value="doktor">Doktor</option>
		<option name="sestra" value="sestra">Zdravotní sestra</option>
		<option name="lekarnik" value="lekarnik">Lékárník</option>
		<option name="pacient" value="pacient">Pacient</option>
	</select>
	<input type="submit" name="submit4" value="Vybrat" />
</form></h5>
<?php	
    //prehled osob a mazani
    if(isset($_POST['submit4'])){
        switch($_POST['listPosition'])
	{	
            case "doktor":
		$result=$DB->dbSelect("SELECT * FROM osoba, doktor, nemocnicni_oddeleni, ma_na_oddeleni WHERE osoba.id_osoby=doktor.id_osoba AND nemocnicni_oddeleni.id_oddeleni=ma_na_oddeleni.id_oddeleni AND ma_na_oddeleni.id_doktor=doktor.id_doktora");
		echo "<table class='t'>\n";
		echo '<tr><th>Jméno doktora</th><th>Oddělení</th><th>Telefon</th><th>Uvazek</th><th>Specializace</th><th></th><th></th></tr>';
		foreach($result as $r)
		{
                    $id_osoby=$r["id_osoby"];
                    echo '<tr><td >'.$r["titul"].' '.$r["jmeno"].' '.$r["prijmeni"].'</td>';
                    echo '<td >'.$r["nazev"].'</td>';
                    echo '<td >'.$r["telefon"].'</td>';
                    echo '<td >'.$r["uvazek"].'</td>';
                    echo '<td >'.$r["obor"].'</td>';
                    echo "<form action='' method='POST'><input type='hidden' name='del' value='".$id_osoby."'>";
                    echo "<td><input type='submit' name='submit3' id=$id_osoby value='Upravit' onclick='osoba($id_osoby)'/></td>  ";
                    echo "<td><input type='submit' name='delete' value='Smazat' />  </form></td></tr>";
		}
		echo "</table>\n";
		break;
            case "sestra":
            	$result=$DB->dbSelect("SELECT * FROM osoba, zdravotni_sestra, nemocnicni_oddeleni WHERE zdravotni_sestra.id_osoba=osoba.id_osoby AND nemocnicni_oddeleni.id_oddeleni=zdravotni_sestra.id_oddeleni");
		echo "<table class='t'>\n";
		echo '<tr><th>Jméno sestry</th><th>Oddělení</th><th>Povinnost</th><th>Zaměření</th><th></th><th></th></tr>';
		foreach($result as $r)
		{
                    $id_osoby=$r["id_osoby"];
                    echo '<tr><td>'.$r["titul"].' '.$r["jmeno"].' '.$r["prijmeni"].'</td>';
                    echo '<td>'.$r["nazev"].'</td>';
                    echo '<td>'.$r["povinnost"].'</td>';
                    echo '<td>'.$r["zamereni"].'</td>';
                    echo "<td><form action='' method='POST'><input type='hidden' name='del' value='".$id_osoby."'>";
                    echo "<input type='submit' name='submit3' id=$id_osoby value='Upravit' onclick='osoba($id_osoby)'/></td>  ";
                    echo "<td><input type='submit' name='delete' value='Smazat' />  </form></td></tr>";
		}
		echo "</table>\n";
		break;
            case "lekarnik":
		$result=$DB->dbSelect("SELECT * FROM osoba, lekarnik WHERE lekarnik.id_osoba=osoba.id_osoby");
		echo "<table class='t'>\n";
		echo '<tr><th>Jméno lékarníka</th><th>Umístění</th><th>Povinnost</th><th></th><th></th></tr>';
		foreach($result as $r)
		{
                    $id_osoby=$r["id_osoby"];
                    echo '<tr><td>'.$r["titul"].' '.$r["jmeno"].' '.$r["prijmeni"].'</td>';
                    echo '<td>'.$r["umisteni"].'</td>';
                    echo '<td>'.$r["povinnost"].'</td>';
                    echo "<td><form action='' method='POST'><input type='hidden' name='del' value='".$id_osoby."'>";
                    echo "<input type='submit' name='submit3' id=$id_osoby value='Upravit' onclick='osoba($id_osoby)'/></td>  ";
                    echo "<td><input type='submit' name='delete' value='Smazat' />  </form></td></tr>";
		}
		echo "</table>\n";
		break;
            case "pacient":
		$result=$DB->dbSelect("SELECT * FROM osoba, pacient WHERE pacient.id_osoba=osoba.id_osoby");
            	echo "<table class='t'>\n";
		echo '<tr><th>Jméno pacienta</th><th>Prodělané nemoci</th><th></th><th></th></tr>';
		foreach($result as $r)
		{
                    $id_osoby=$r["id_osoby"];
                    echo '<tr><td>'.$r["titul"].' '.$r["jmeno"].' '.$r["prijmeni"].'</td>';
                    echo '<td>'.$r["prodelane_nemoci"].'</td>';
                    echo "<td><form action='' method='POST'><input type='hidden' name='del' value='".$id_osoby."'>";
                    echo "<input type='submit' name='submit3' id=$id_osoby value='Upravit' onclick='osoba($id_osoby)'/></td>  ";
                    echo "<td><input type='submit' name='delete' value='Smazat' />  </form></td></tr>";
		}
		echo "</table>\n";
		break;
	}
    }		
    
    //mazani osob
    if(isset($_POST['delete'])){
        $id = intval($_POST['del']);
	$del=$DB->dbQuery("DELETE FROM osoba WHERE id_osoby=$id ");
	$del1=$DB->dbQuery("DELETE FROM pacient WHERE id_osoba=$id ");
	$del2=$DB->dbQuery("DELETE FROM doktor WHERE id_osoba=$id ");
	$del3=$DB->dbQuery("DELETE FROM zdravotni_sestra WHERE id_osoba=$id ");
	$del4=$DB->dbQuery("DELETE FROM lekarnik WHERE id_osoba=$id ");
	if($del) {
            echo "<h6>Smazání úspěšné</h6>";
	}
	else
	{
	    echo "<h6>Smazání se nepovedlo</h6>";
	}
	    
    }

    //mazani oddeleni
    if(isset($_POST['delete1'])){
        $id1 = intval($_POST['delete1']);
	$del1=$DB->dbQuery("DELETE FROM nemocnicni_oddeleni WHERE id_oddeleni=$id1");
	$del2=$DB->dbQuery("DELETE FROM ma_na_oddeleni WHERE id_oddeleni=$id1");
	$upd=$DB->dbQuery("UPDATE zdravotni_sestra SET id_oddeleni=0");
	if($del1) {
            echo "<meta http-equiv='refresh' content='0'>";
            echo "<h6>Smazani uspesne</h6>";
	}
	else
	{
	    echo "Smazání se nepovedlo";
	}
    }
?>

<h3 onclick="zobrazSkryj('oddil2')">Přidat osobu</h3>
<div id="oddil2" class="skryvany">
<form  method="post">
<table>
    <tr><td> Jméno: </td><td> <input type="text" required name="name" /></td></tr>
    <tr><td>Příjmení: </td><td>  <input type="text" required name="surname" /></td></tr>
    <tr><td>Titul: </td><td> <input type="text" name="titul" /></td></tr>
    <tr><td>Adresa: </td><td> <input style="width: 290px;" type="text" required name="adress" /></td></tr>
    <tr><td>Datum narození: </td><td>  <input style="width: 130px;" type="date" required name="date" /></td></tr>
    <tr><td>Pohlaví:</td><td>   <input type="radio" required name="pohlavi" id=M value="M"><label for=M>Muž</label>
                                <input type="radio" required name="pohlavi" value="Z" id=Z><label for=Z>Žena</label></td></tr>
    <tr><td>Pozice: </td><td>   <input type=radio required name=position value=doktor id=doktor onchange="zmena()"><label for=doktor>Doktor</label>
                                <input type=radio required name=position value=sestra id=sestra onchange="zmena()"><label for=sestra>Zdravotní sestra</label>
                                <input type=radio required name=position value=lekarnik id=lekarnik onchange="zmena()"><label for=lekarnik>Lékárník</label>
                                <input type=radio required name=position value=pacient id=pacient onchange="zmena()"><label for=pacient>Pacient</label></td></tr>
    <tr><td>Zaměření:</td><td>  <input type="text" required name="zamereni" id="zamereni" value="Zaměření"/></td></tr>
    <tr><td>Obor:</td><td>  <input type="text" name="obor" id="obor" /></td></tr>
    <tr><td>Úvazek:</td><td><select name='uvazek' id='uvazek'>";
                            <option value='plny'>Plný</option>
                            <option value='castecny'>Částečný</option>
                            </select></td></tr>
    <tr><td>Telefon:</td><td>  <input type="text" name="telefon" id="telefon" /></td></tr>
    <?php
	$odd=$DB->dbSelect("SELECT * FROM nemocnicni_oddeleni");
	echo "<tr><td>Oddělení: </td><td> <select name='oddeleni' id='oddeleni'>";
	foreach($odd as $o)
	{
            echo "<option value=".$o["id_oddeleni"].">".$o["nazev"]."</option>\n";
	}
        echo "</select>	</td></tr>";
    ?>
    <tr><td>Povinnost:</td><td>  <input type="text" name="povinnost" id="povinnost"  /></td></tr>
    <tr><td>Umisteni: </td><td> <input type="text" name="umisteni" id="umisteni" />	</td></tr>
    <tr><td> </td><td> <input type="submit" name="submit2" value="Přidat" /></td></tr>
 </table>
</form>
</div>
<?php
    //pridani nove osoby
    if(isset($_POST['submit2']))
    { 
	$name=$_POST['name'];
	$surname=$_POST['surname'];
	$titul=$_POST['titul'];
	$date=$_POST['date'];
	$adresa=$_POST['adress'];
	$pohlavi=$_POST['pohlavi'];
	$position=$_POST['position'];
	$uvazek=$_POST["uvazek"];
	$zamereni=$_POST["zamereni"];
	$obor=$_POST["obor"];
	$telefon=$_POST["telefon"];
	$povinnost=$_POST["povinnost"];
	$umisteni=$_POST["umisteni"];
	$oddeleni=$_POST["oddeleni"];
	$id2 = $DB->dbSelectNumber("SELECT Max(id_doktora) FROM doktor") + 1;
	$id4 = $DB->dbSelectNumber("SELECT Max(id_lekarnika) FROM lekarnik") + 1;
	$id3 = $DB->dbSelectNumber("SELECT Max(id_sestry) FROM zdravotni_sestra") + 1;
	$id5 = $DB->dbSelectNumber("SELECT Max(id_pacienta) FROM pacient") + 1;
	$id6 = $DB->dbSelectNumber("SELECT Max(id_na_oddeleni) FROM ma_na_oddeleni") + 1;
          if($datum > date("Y-m-d"))
        {
             echo "<h6>Chyba - špatně zadané datum</h6>";
        }
        else {
	$add=$DB->dbQuery("INSERT INTO osoba (id_osoby, jmeno, prijmeni, titul, datum_narozeni, adresa, pohlavi) VALUES ('$id', '$name','$surname', '$titul', '$date', '$adresa', '$pohlavi')");
	if($add)
        {
            switch($position)
            {	
                case "doktor":
                    $add1=$DB->dbQuery("INSERT INTO doktor (id_doktora, id_osoba, obor, specializace) VALUES ('$id2', '$id', '$obor', '$zamereni')");
                    $add2=$DB->dbQuery("INSERT INTO ma_na_oddeleni (id_na_oddeleni, uvazek, telefon, id_doktor, id_oddeleni) VALUES('$id6', '$uvazek', '$telefon', '$id2', '$oddeleni')");
                    if($add1 && $add2)
                    {
                        echo "<h6>Vložení nové osoby proběhlo úspěšně.</h6>";
                    }
                    else
                    {
                        echo "<h6>Vložení nové osoby se nepovedlo</h6>";
                    }
                    break;
                case "sestra":
                    $add1=$DB->dbQuery("INSERT INTO zdravotni_sestra (id_sestry, povinnost, id_osoba, id_oddeleni) VALUES ('$id3', '$povinnost' ,'$id', '$id_oddeleni')");
                    if($add1)
                    {
                        echo "<h6>Vložení nové osoby proběhlo úspěšně.</h6>";
                    }
                    else
                    {
                        echo "<h6>Vložení nové osoby se nepovedlo</h6>";
                    }
                    break;
                case "lekarnik":
                    $add1=$DB->dbQuery("INSERT INTO lekarnik (id_lekarnika, id_osoba, umisteni, povinnost) VALUES ('$id4', '$id', '$umisteni', '$povinnost')");
                     if($add1)
                    {
                        echo "<h6>Vložení nové osoby proběhlo úspěšně.</h6>";
                    }
                    else
                    {
                        echo "<h6>Vložení nové osoby se nepovedlo</h6>";
                    }
                    break;
                case "pacient":
                    $add1=$DB->dbQuery("INSERT INTO pacient (id_pacienta, id_osoba) VALUES ('$id5', '$id')");
                     if($add1)
                    {
                        echo "<h6>Vložení nové osoby proběhlo úspěšně.</h6>";
                    }
                    else
                    {
                        echo "<h6>Vložení nové osoby se nepovedlo</h6>";
                    }
                    break;
            }

	}
	
        else
        {
            echo "<h6>Vložení nové osoby se nepovedlo</h6>";
	}
	}
    }

?>
<script>
function zmena()
{
    if (document.getElementById("doktor").checked){
	document.getElementById("oddeleni").style.display = "";
        document.getElementById("uvazek").style.display = "";
	document.getElementById("zamereni").style.display = "";
	document.getElementById("obor").style.display = "";
	document.getElementById("telefon").style.display = "";
	document.getElementById("povinnost").style.display = "none";
	document.getElementById("umisteni").style.display = "none";
    }
     else if(document.getElementById("lekarnik").checked){
        document.getElementById("oddeleni").style.display = "none";
        document.getElementById("uvazek").style.display = "none";
	document.getElementById("zamereni").style.display = "none";
	document.getElementById("obor").style.display = "none";
	document.getElementById("telefon").style.display = "none";
	document.getElementById("povinnost").style.display = "";
	document.getElementById("umisteni").style.display = "";
    }
    else if(document.getElementById("sestra").checked){
	document.getElementById("oddeleni").style.display = "";
        document.getElementById("uvazek").style.display = "none";
	document.getElementById("zamereni").style.display = "none";
	document.getElementById("obor").style.display = "none";
	document.getElementById("telefon").style.display = "none";
	document.getElementById("povinnost").style.display = "";
	document.getElementById("umisteni").style.display = "none";
    }
    else{
	document.getElementById("oddeleni").style.display = "none";
        document.getElementById("uvazek").style.display = "none";
	document.getElementById("zamereni").style.display = "none";
	document.getElementById("obor").style.display = "none";
	document.getElementById("telefon").style.display = "none";
	document.getElementById("povinnost").style.display = "none";
	document.getElementById("umisteni").style.display = "none";
    }	
}

zmena();

</script>

<h3 onclick="zobrazSkryj('oddil3')">Přidat oddělení</h3>
<div id="oddil3" class="skryvany">
<form method="post">
<table>
    <tr><td>Název:</td><td> <input type="text" required name="oddeleni" /> </td></tr>
    <tr><td>Zaměření: </td><td><input type="text" required name="zamereni" /></td></tr>
    <tr><td>Přidat zdravotní sestru:</td><td> 
    <?php
	$zdrav=$DB->dbSelect("SELECT * FROM osoba, zdravotni_sestra, nemocnicni_oddeleni WHERE zdravotni_sestra.id_osoba=osoba.id_osoby AND nemocnicni_oddeleni.id_oddeleni=zdravotni_sestra.id_oddeleni");
	echo "<select name='sestra'>";
	foreach ($zdrav as $z)
	{
            echo "<option value=".$z["id_sestry"].">".$z["jmeno"]." ".$z["prijmeni"]." - ".$z["nazev"]."</option>\n";
	}
	echo "</select></td></tr>";
    ?>
    <tr><td></td><td><input type="submit" name="submit5" value="Přidat" /></td></tr>
</table>
</form>
</div>

<h3 onclick="zobrazSkryj('oddil4')">Upravit nebo odebrat oddělení</h3>
<div id="oddil4" class="skryvany">
<?php
    $odebrat=$DB->dbSelect("SELECT * FROM nemocnicni_oddeleni, ma_na_oddeleni, doktor, osoba WHERE nemocnicni_oddeleni.id_oddeleni=ma_na_oddeleni.id_oddeleni AND ma_na_oddeleni.id_doktor=doktor.id_doktora AND doktor.id_osoba=osoba.id_osoby");
    echo '<table class="t">';
    echo '<tr><th>Nazev odddeleni</th><th>Zamereni</th><th>Telefon</th><th>Zdravotní sestry</th><th>Doktor</th><th colspan="2"></th></tr>';
    foreach($odebrat as $o)
    {
	$id_oddeleni=$o["id_oddeleni"];
	echo "<form method='POST'><tr><td> <input  type='text' required name='nazev' value=".$o["nazev"]." /></td>";
	echo "<td> <input type='text' required name='zamereni' value=".$o["zamereni"]." /></td>";
	echo "<td> <input style='width: 100px;' type='number' required name='telefon' value=".$o["telefon"]." /></td>";
	$zdravses=$DB->dbSelect("SELECT * FROM osoba, zdravotni_sestra WHERE zdravotni_sestra.id_osoba=osoba.id_osoby");
        echo "<td><select name='sestra'>";
	foreach ($zdravses as $z)
	{
            echo "<option value=".$z["id_sestry"].">".$z["jmeno"]." ".$z["prijmeni"]." ".($id_oddeleni==$z["id_oddeleni"] ? " prirazena" : "")."</option>\n";
	}
	echo "</select></td>";
	echo "<td> Doktor:".$o["titul"]." ".$o["jmeno"]." ".$o["prijmeni"]."</td>";
	echo "<td><input type='submit' name='submit_ses' value='Upravit' /></td>  ";
	echo "<input type='hidden' name='del' value='".$id_oddeleni."'>";
	echo "<td><input type='submit' name='delete' value='Smazat' />  </td></tr></form>";
    }
    echo '</table>';
    if(isset($_POST['submit_ses'])){ 
	$nazev=$_POST['nazev'];
	$zamereni=$_POST['zamereni'];
	$telefon=$_POST['telefon'];
	$sestra=$_POST['sestra'];	
	$id_oddeleni=$_POST['del'];
	$upd1=$DB->dbQuery("UPDATE ma_na_oddeleni SET telefon='$telefon' WHERE id_oddeleni='$id_oddeleni'");
	$upd2=$DB->dbQuery("UPDATE zdravotni_sestra SET id_oddeleni='$id_oddeleni' WHERE zdravotni_sestra.id_sestry='$sestra'");
	$upd3=$DB->dbQuery("UPDATE nemocnicni_oddeleni SET nazev='$nazev', zamereni='$zamereni' WHERE id_oddeleni='$id_oddeleni'");
	if (!$upd1 || !$upd2 || !$upd3 )
	{
	    echo '<h6>Změny se nepovedlo uložit</h6>';   
	}
	else
	{
	     echo '<h6>Změny uloženy do databáze</h6>';
	    echo "<meta http-equiv='refresh' content='0'>";
	}
    }
?>
</div>

<h3>Přehled vyšetření
<form action="reditel.php" method="post">
    <select name="listZoradit">
	<option name="cv" value="cv">Podle č.v.</option>
	<option name="datum" value="datum">Podle data</option>
	<option name="jdoktor" value="jdoktor">Podle jména doktora</option>
    </select>
    <input type="submit" name="submit6" value="Zobrazit a seřadit" />
</form>
    </h3>
<?php
    //prehled vysetreni
    if(isset($_POST['submit6'])){
	switch($_POST['listZoradit']){	
            case "cv":
                $vysetreni=$DB->dbSelect("SELECT * FROM vysetreni, doktor, osoba, nemocnicni_oddeleni WHERE vysetreni.id_doktor=doktor.id_doktora AND vysetreni.id_oddeleni=nemocnicni_oddeleni.id_oddeleni AND doktor.id_osoba=osoba.id_osoby ORDER BY id_vysetreni ASC");
                $pacient=$DB->dbSelect("SELECT * FROM osoba, pacient, vysetreni WHERE vysetreni.id_pacient=pacient.id_pacienta AND osoba.id_osoby=pacient.id_osoba ORDER BY id_vysetreni ASC");
                echo "<table class='t'>";
                echo "<tr> <th>Č. v.</th><th>Datum vyšetření</th><th>Jméno doktora</th><th>Jméno pacienta</th><th>Oddělení</th><th>Průběh vyšetření</th><th>Výsledek vyšetření</th></tr>";
                foreach($vysetreni as $v)
                {
		    echo "<tr>";
		    echo "<td>".$v["id_vysetreni"]."</td>";
		    echo "<td>".$v["datum_vysetreni"]."</td>";
		    echo "<td>".$v["jmeno"]." ".$v["prijmeni"]."</td>";
		    foreach($pacient as $p)
		    {
                        if ($p["id_vysetreni"]==$v["id_vysetreni"])
			{
                            echo "<td>".$p["titul"]." ".$p["jmeno"]." ".$p["prijmeni"]."</td>";
                        }
                    }
		    echo "<td>".$v["nazev"]."</td>";
		    echo "<td>".$v["prubeh_vysetreni"]."</td>";
		    echo "<td>".$v["vysledek_vysetreni"]."</td>";
		    echo "</tr>";
                }
                echo "</table>";
                break;
            case "datum":
                $vysetreni=$DB->dbSelect("SELECT * FROM vysetreni, doktor, osoba, nemocnicni_oddeleni WHERE vysetreni.id_doktor=doktor.id_doktora AND vysetreni.id_oddeleni=nemocnicni_oddeleni.id_oddeleni AND doktor.id_osoba=osoba.id_osoby ORDER BY datum_vysetreni ASC");
                $pacient=$DB->dbSelect("SELECT * FROM osoba, pacient, vysetreni WHERE vysetreni.id_pacient=pacient.id_pacienta AND osoba.id_osoby=pacient.id_osoba ");
                echo '<table class="t">';
                echo "<tr> <th>Č. v.</th><th>Datum vyšetření</th><th>Jméno doktora</th><th>Jméno pacienta</th><th>Oddělení</th><th>Průběh vyšetření</th><th>Výsledek vyšetření</th></tr>";
                foreach($vysetreni as $v)
                {
		    echo "<tr>";
		    echo "<td>".$v["id_vysetreni"]."</td>";
		    echo "<td>".$v["datum_vysetreni"]."</td>";
		    echo "<td>".$v["jmeno"]." ".$v["prijmeni"]."</td>";
		    foreach($pacient as $p)
		    {
                        if ($p["id_vysetreni"]==$v["id_vysetreni"])
                        {
                            echo "<td>".$p["titul"]." ".$p["jmeno"]." ".$p["prijmeni"]."</td>";
                        }
                    }
                    echo "<td>".$v["nazev"]."</td>";
                    echo "<td>".$v["prubeh_vysetreni"]."</td>";
                    echo "<td>".$v["vysledek_vysetreni"]."</td>";
                    echo "</tr>";
                }
                echo "</table>";
                break;
            case "jdoktor":
                $vysetreni=$DB->dbSelect("SELECT * FROM vysetreni, doktor, osoba, nemocnicni_oddeleni WHERE vysetreni.id_doktor=doktor.id_doktora AND vysetreni.id_oddeleni=nemocnicni_oddeleni.id_oddeleni AND doktor.id_osoba=osoba.id_osoby ORDER BY prijmeni ASC");
                $pacient=$DB->dbSelect("SELECT * FROM osoba, pacient, vysetreni WHERE vysetreni.id_pacient=pacient.id_pacienta AND osoba.id_osoby=pacient.id_osoba");
                echo '<table class="t">';
                echo "<tr><th>Č. v.</th><th>Datum vyšetření</th><th>Jméno doktora</th><th>Jméno pacienta</th><th>Oddělení</th><th>Průběh vyšetření</th><th>Výsledek vyšetření</th></tr>";
                foreach($vysetreni as $v)
                {
		    echo "<tr>";
                    echo "<td>".$v["id_vysetreni"]."</td>";
		    echo "<td>".$v["datum_vysetreni"]."</td>";
		    echo "<td>".$v["jmeno"]." ".$v["prijmeni"]."</td>";
		    foreach($pacient as $p)
		    {
                        if ($p["id_vysetreni"]==$v["id_vysetreni"])
			{
                            echo "<td>".$p["titul"]." ".$p["jmeno"]." ".$p["prijmeni"]."</td>";
			}
                    }
                    echo "<td>".$v["nazev"]."</td>";
                    echo "<td>".$v["prubeh_vysetreni"]."</td>";
		    echo "<td>".$v["vysledek_vysetreni"]."</td>";
                    echo "</tr>";
                }
                echo "</table>";
                break;
        }
    }
?>
<?php
	include "zaver.php";
	$DB->dbClose();	
?>
