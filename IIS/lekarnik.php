<?php
    require_once "lib/db-connect.php";
    include "uvod.php";
    session_start();
    $DB = new mymysql;
    $idle = $DB->dbSelectNumber("SELECT Max(id_leku) FROM lek") + 1;
    
    if(isset($_SESSION['id']) && !empty($_SESSION['id'])){
        if( $_SESSION['last_activity'] < ((time()) - $_SESSION['expire_time']) ) { 
          $_SESSION['timeout'] = "abc";
          header('Location: timeout.php'); 
        } 
        else{ 
          $_SESSION['last_activity'] = time(); 
        }
        $id_lekarnik = $_SESSION['id'];//id lekarnika ktory sa prihlasil
        /*
        $re = $DB->dbQuery("SELECT * FROM lekarnik WHERE id_osoba = '$id_lekarnik'");
        $count = mysqli_num_rows($re);
        if ($count != 1){
          header('Location: logout.php');
        } */
        $meno_l = $DB->dbQuery("SELECT jmeno FROM osoba WHERE id_osoby = '$id_lekarnik'");
        $priezvisko_l = $DB->dbQuery("SELECT prijmeni FROM osoba WHERE id_osoby = '$id_lekarnik'");
        echo "<p class='r'><input type='submit' name='edit' id=$id_lekarnik value='Upravit profil' onclick='osoba_edit($id_lekarnik)'/>";
        foreach($meno_l as $m){
            foreach($priezvisko_l as $p){
                echo '<h2>Lékárník: '.$m["jmeno"].' '.$p["prijmeni"].'</h2>';
            }
        }
    }
    else{
        header('Location: index.php');  
    }
    $pocet=$DB->dbSelect("SELECT * FROM lek");
    foreach ($pocet as $poc)
    {
	if ($poc["mnozstvi"]=="0")
	{
            echo "<p style='color:red;'>Lek ".$poc["nazev"]." dosel"."\n";
	}
    } 	
?>

<h3 onclick="zobrazSkryj('oddil1')">Přidat lék</h3>
<div id="oddil1" class="skryvany">
<form method="post">
<table>
    <tr><td>Název:</td><td> <input style="width: 300px;" required type="text" name="nazev" /></td></tr> 
    <tr><td>Učinné látky: </td><td><input style="width: 300px;" required type="text" name="ucinne" /></td></tr>
    <tr><td>Kontraindikace: </td><td><input style="width: 300px;" required type="text" name="kontraindikace" /></td></tr>
    <tr><td>Množství: </td><td><input type="number" required name="mnozstvi" /></td></tr>
    <tr><td>Cena: </td><td><input type="number" required name="cena" /></td></tr>
    <tr><td></td><td><input type="submit" name="submit1" value="Přidat" /></td></tr>
</table>
</form>
</div>
                  
<?php
    if(isset($_POST['submit1']))
    { 		
	$nazev=$_POST['nazev'];
	$ucinne=$_POST['ucinne'];
	$kontraindikace=$_POST['kontraindikace'];
	$mnozstvi=$_POST['mnozstvi'];
	$cena=$_POST['cena'];
	if($DB->dbQuery("INSERT INTO lek (id_leku, nazev, ucinne_latky, kontraindikace, spravuje_lekarnik, cena, mnozstvi) VALUES ('$idle', '$nazev', '$ucinne', '$kontraindikace', '$id_lekarnik' , $cena, '$mnozstvi')"))
        {
            echo '<h6>Lek pridan do DB</h6>';
        }
        else
        {
             echo '<h6>Chyba při přidávání léku</h6>';
        }
	
    }
?>
          
<h3 onclick="zobrazSkryj('oddil2')">Výdej léků</h3>
<div id="oddil2" class="skryvany">
<form method="post">
<table>
  <tr>
      <td><input type="radio" onclick="zobraz('oddil4', 'oddil5')" name="recept" id="recept" value="recept"><label for=recept>S receptem</label></td>
      <td></td><td><input type="radio" onclick="zobraz('oddil5', 'oddil4')" name="recept" id="norecept" value="norecept"><label for=norecept>Bez receptu</label></td>
  </tr>
</table>
</form>
</div>

<div id="oddil4" class="skryvany">
  <form method = "post">
    <table>
      <?php
      $vypis=$DB->dbSelect("SELECT * FROM predpis,lek, vysetreni, doktor, osoba WHERE vysetreni.id_vysetreni=predpis.id_vysetreni AND predpis.id_lek=lek.id_leku AND doktor.id_doktora=vysetreni.id_doktor AND osoba.id_osoby=doktor.id_osoba");    
      ?>
      <tr><td>Recept:</td><td><select style="width: 450px;" name='predpis'>
      <?php
      foreach ($vypis as $v)
		  {
			  echo "<option value=".$v["id_leku"].">Název léku: ".$v["nazev"]." -- Cena: ".$v["cena"]." Kč -- Doktor: ".$v["prijmeni"]."</option>\n";
		  }
      ?>
      </select></td></tr>
      <tr><td>Množství:</td><td> <input type="number" name="vydane_mnozstvi" value="1" /></td></tr>
      <tr><td></td><td><input type="submit" name="submit4" value="Vydat lék" /> </td></tr>
    </table>
  </form>
</div>

<div id="oddil5" class="skryvany">
  <form method="post">
    
    <table>
        <tr><th>Název</th><th>Množství</th><th>Cena</th></tr>
      <?php
      $result=$DB->dbSelect("SELECT * FROM lek");
      foreach($result as $r){
        $ceny = $r["cena"];
        $nazov = $r["nazev"];
        echo '<tr><td><input type="text" required  name="nazov_liek[]" value="'.$nazov.'" /></td>';
        echo '<td><input type="number" required name="vydane_mnozstvo[]" value="0" /></td>';
        echo '<td> <input type="text" name="vydana_cena[]" value="'.$ceny.'" /> Kč</td></tr>';
      }
      ?>
        <tr><td colspan="3"><input class="r" type="submit" name="submit6" value="Vydat" /></center></td></tr>
    </table>
  </form>
</div>
<?php
    if(isset($_POST['submit6'])){ 
        $celkova_cena = 0;
        $flag = 1;
        for ($i = 0; $i < count($_POST['nazov_liek']); $i++){  // na kontrolu ci je v zozname nejaky liek, ktory uz neni na sklade (aby sme nevydali ostatne lieky zbytocne)
            $vydane_mnozstvi=$_POST['vydane_mnozstvo'][$i];
            $nazov = $_POST['nazov_liek'][$i];
            $mnozstvi=$DB->dbSelectNumber("SELECT mnozstvi FROM lek WHERE nazev='$nazov'");
            if (($mnozstvi == "0") && ($vydane_mnozstvi != "0"))
            {
                echo "<h6>Lék nelze vydat! Není dostatečné množství na skladě!</h6>";
                $flag = 0;
                break;
            }   
        }
        if ($flag == "1"){
            for ($i = 0; $i < count($_POST['nazov_liek']); $i++){
                $vydane_mnozstvi=$_POST['vydane_mnozstvo'][$i]; 
                $cen = $_POST['vydana_cena'][$i];
                $nazov = $_POST['nazov_liek'][$i];
                $mnozstvi=$DB->dbSelectNumber("SELECT mnozstvi FROM lek WHERE nazev='$nazov'");
                $celkem = $mnozstvi - $vydane_mnozstvi;
                if($DB->dbQuery("UPDATE lek SET mnozstvi='$celkem' WHERE nazev = '$nazov'"))
                {
                    $cen = $cen * $vydane_mnozstvi;
                    $celkova_cena = $celkova_cena + $cen;
                }
                else {
                    echo "<h6>Chyba při vkládání do databáze?</h6>";
                }
                
            }
            echo '<h6>Celková cena = '.$celkova_cena.'</h6>';
        }
    }  
?>
<script>
function zobraz(id1, id2)
{
    el=document.getElementById(id1).style; 
    el1=document.getElementById(id2).style;
    el.display= 'block';  
    el1.display= 'none';
}
</script>
<?php
    //smazani			
    if(isset($_POST['delete']))
    {
  	$id = $_POST['idlek'];
  	$del=$DB->dbQuery("DELETE FROM lek WHERE id_leku=$id ");
	if($del) {
            echo "<h6>Lek smazán</h6>";
	}
        else
        {
             echo "<h6>Smazání léku se nepovedlo</h6>";
        }
    }
	
    if(isset($_POST['submit4'])){ 
  	$predpis=$_POST['predpis'];
  	$vydane_mnozstvi=$_POST['vydane_mnozstvi'];
  	$mnozstvi=$DB->dbSelectNumber("SELECT mnozstvi FROM lek WHERE id_leku = '$predpis'");
        $cen=$DB->dbSelectNumber("SELECT cena FROM lek WHERE id_leku = '$predpis'");
  	if ($mnozstvi == "0")
  	{
            echo "<h6>Lék nelze vydat! Není dostatečné množství na skladě!</h6>";
  	}
  	else
  	{
            $celkem=$mnozstvi-$vydane_mnozstvi;
            if($DB->dbQuery("UPDATE lek SET mnozstvi='$celkem' WHERE id_leku='$predpis'"))
            {
                $cen = $cen * $vydane_mnozstvi;
                echo '<h6>Celkova cena = '.$cen.'</h6>';
            }
            else
            {
                echo '<h6>Nepovedlo se nahrát změnu do databáze</h6>';
            }
            
  	}
    }
?>
<h3 onclick="zobrazSkryj('oddil3')">Vyhledat lék</h3>
<div id="oddil3" class="skryvany">
<form method="post">
<table>
      <tr><td>Název léku: </td><td><input type="text" name="term" /></td></tr>
     <tr><td> </td><td><input type="submit" name="submit5" value="Hledej" /></td></tr>
</table>
</form>
</div>
 
<?php
    if (isset($_POST['submit5'])){ 
	if(preg_match("/^[  a-zA-Z]+/", $_POST['term'])){
            $name=$_POST['term']; 
            $result=$DB->dbSelect("SELECT * FROM lek WHERE nazev LIKE '%" . $name .  "%'");
            if (!$result)
            {
		echo "<h6>Lék nenalezen</h6>";
            }
            else
            {
		echo '<table class="t"> '; //druha barva #84c3f7 for ($i=0; $i<$pocet; $i++):$temp .= "<tr class=".($i%2 ? "s" : "l").">
		echo '<tr><th>Lek</th><th>Účinné látky</th><th>Kontraindikace</th><th>Cena</th><th>Množství</th><th></th><th></th></tr>';
		foreach($result as $r)
                {
                    $id_leku=$r["id_leku"];	
                    echo '<tr><td>'.$r["nazev"].'</td>';
                    echo '<td>'.$r["ucinne_latky"].'</td>';
                    echo '<td>'.$r["kontraindikace"].'</td>';
                    echo '<td>'.$r["cena"].'</td>';
                    echo '<td>'.$r["mnozstvi"].'</td>';
                    echo "<form action='' method='POST'><input type='hidden' name='idlek' value='".$id_leku."'>";
                    echo "<td><a href=?id_leku=".$r["id_leku"].">Upravit</a>";   
                    echo "<td><input type='submit' name='delete' value='Smazat' />  </td>";
                    echo "</form> </tr> ";
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
<?php
	
    if($_GET['id_leku']){
  	$lek=$DB->dbSelect("SELECT * FROM lek WHERE id_leku=$_GET[id_leku]");
  	foreach($lek as $p)
  	{
            $nazev=$p["nazev"];
            $ucinne=$p["ucinne_latky"];
            $kontraindikace=$p["kontraindikace"];
            $mnozstvi=$p["mnozstvi"];
            $cena=$p["cena"];
            $sprava=$p["spravuje_lekarnik"];	 
  	}	
  	if(isset($_POST['submit3'])){ 
            $nazev=$_POST['nazev'];
            $ucinne=$_POST['ucinne'];
            $kontraindikace=$_POST['kontraindikace'];
            $mnozstvi=$_POST['mnozstvi'];
            $cena=$_POST['cena'];
            $sprava=$_POST['lekarnik'];
            if($DB->dbQuery("UPDATE lek SET nazev='$nazev', ucinne_latky='$ucinne', kontraindikace='$kontraindikace', spravuje_lekarnik='$sprava', cena='$cena', mnozstvi='$mnozstvi' WHERE id_leku=$_GET[id_leku]"))
            {
                echo '<h6>Změny uloženy</h6>';
            }
            else {
                echo '<h6>Změny nejsou uloženy</h6>';
            }

	}
?>
	
<h5>Upravit lék</h5>
<form method="post">
<table class="t"> 
    <tr><td>Název: </td><td><input style="width: 300px;" type="text" required name="nazev" value="<? echo $nazev; ?>" /> </td></tr>
    <tr><td>Účinné látky: </td><td><input style="width: 300px;" type="text" required name="ucinne" value="<? echo $ucinne; ?>" /></td></tr>
    <tr><td>Kontraindikace: </td><td><input style="width: 300px;" type="text" required name="kontraindikace" value="<? echo $kontraindikace; ?>" /></td></tr>
    <tr><td>Množství: </td><td><input type="number" required name="mnozstvi" value="<? echo $mnozstvi; ?>" /></td></tr>
    <tr><td>Cena:</td><td> <input type="number" required name="cena" value="<? echo $cena; ?>" /> Kč</td></tr>
    <?php
	$lekarnik=$DB->dbSelect("SELECT * FROM osoba, lekarnik WHERE lekarnik.id_osoba=osoba.id_osoby");
	echo "<tr><td>Lékárník:</td><td><select required name='lekarnik'>";
	foreach ($lekarnik as $l)
	{
            echo "<option value=".$l["id_lekarnika"].">".$l["jmeno"]." ".$l["prijmeni"]."</option>\n";
        }
	echo "</select></td></tr>";
    ?>
    <tr><td></td><td><input type="submit" name="submit3" value="Uložit změny" /> </td></tr>
</table>
</form>	
<?php } ?>	

<?
    include "zaver.php";
    $DB->dbClose();	
    echo '<META HTTP-EQUIV=Refresh CONTENT="901">'; 
?>
