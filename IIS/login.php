<?php
  require_once "lib/db-connect.php";
  include "uvod.php";

  session_start();
  $DB = new mymysql;
  
  if ((!empty($_POST['surname0'])) && (!empty($_POST['heslo0']))) {
    $meno_reditel = $_POST['surname0'];
    $psw_reditel = $_POST['heslo0'];
    $query = "SELECT * FROM osoba INNER JOIN reditel ON osoba.id_osoby = reditel.id_osoba WHERE osoba.prijmeni = '$meno_reditel' AND osoba.heslo = '$psw_reditel'";
    $result = $DB->dbQuery($query);
    $count = mysqli_num_rows($result);
    if ($count == 1){
      $id_reditel = $DB->dbSelectNumber("SELECT id_osoby FROM osoba WHERE prijmeni = '$meno_reditel'");
      $_SESSION['idr'] = $id_reditel;
      $_SESSION['last_activityr'] = time();
      $_SESSION['expire_timer'] = 900;
      header('Location: reditel.php');
    }
    else {
      echo "Špatné jméno nebo heslo";
      echo '<p>Pro návrat na úvodní stránku <a href = "index.php">here</a> klikněte sem</p>';
    }
        
  }
  
  else if ((!empty($_POST['surname1'])) && (!empty($_POST['heslo1']))) {
    $meno_doktor = $_POST['surname1'];
    $psw_doktor = $_POST['heslo1'];
    $query = "SELECT * FROM osoba INNER JOIN doktor ON osoba.id_osoby = doktor.id_osoba WHERE osoba.prijmeni = '$meno_doktor' AND osoba.heslo = '$psw_doktor'";
    $result = $DB->dbQuery($query);
    $count = mysqli_num_rows($result);
    if ($count == 1){
      $id_doktor = $DB->dbSelectNumber("SELECT id_osoby FROM osoba WHERE prijmeni = '$meno_doktor'");
      $_SESSION['idd'] = $id_doktor;
      $_SESSION['last_activityd'] = time();
      $_SESSION['expire_timed'] = 900; 
      header('Location: doktor.php');      
    }
    else {
      echo "Špatné jméno nebo heslo";
      echo '<p>Pro návrat na úvodní stránku <a href = "index.php">here</a> klikněte sem</p>';
    }
        
  } 
  
  else if ((!empty($_POST['surname2'])) && (!empty($_POST['heslo2']))) {
    $meno_lekarnik = $_POST['surname2'];
    $psw_lekarnik = $_POST['heslo2'];
    $query = "SELECT * FROM osoba INNER JOIN lekarnik ON osoba.id_osoby = lekarnik.id_osoba WHERE osoba.prijmeni = '$meno_lekarnik' AND osoba.heslo = '$psw_lekarnik'";
    $result = $DB->dbQuery($query);
    $count = mysqli_num_rows($result);
    if ($count == 1){
      $id_lekarnik = $DB->dbSelectNumber("SELECT id_osoby FROM osoba WHERE prijmeni = '$meno_lekarnik'");
      $_SESSION['id'] = $id_lekarnik;
      $_SESSION['last_activity'] = time();
      $_SESSION['expire_time'] = 900;
      header('Location: lekarnik.php');      
    }
    else {
     echo "Špatné jméno nebo heslo";
     echo '<p>Pro návrat na úvodní stránku <a href = "index.php">here</a> klikněte sem</p>';
    }
  }
  
  else if ((!empty($_POST['surname3'])) && (!empty($_POST['heslo3']))) {
    $meno_sestra = $_POST['surname3'];
    $psw_sestra = $_POST['heslo3'];
    $query = "SELECT * FROM osoba INNER JOIN zdravotni_sestra ON osoba.id_osoby = zdravotni_sestra.id_osoba WHERE osoba.prijmeni = '$meno_sestra' AND osoba.heslo = '$psw_sestra'";
    $result = $DB->dbQuery($query);
    $count = mysqli_num_rows($result);
    if ($count == 1){
      $id_sestra = $DB->dbSelectNumber("SELECT id_osoby FROM osoba WHERE prijmeni = '$meno_sestra'");
      $_SESSION['ids'] = $id_sestra;
      $_SESSION['last_activitys'] = time();
      $_SESSION['expire_times'] = 900;
      header('Location: sestra.php'); 
    }
    else {
     echo "Špatné jméno nebo heslo";
      echo '<p>Pro návrat na úvodní stránku <a href = "index.php">here</a> klikněte sem</p>';
    }
  }    
  else{
    echo "Prosím vyplňte uživateské jméno a heslo";
    echo '<p>Pro návrat na úvodní stránku <a href = "index.php">here</a> klikněte sem</p>';
  }   
?>
<?
include "zaver.php";
?>