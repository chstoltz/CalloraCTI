<?php

include('config.php');
include('session.php');
include('adm_header.php');

$file = __DIR__.'/install.php';
if(file_exists($file)) {
  die('<div class="w3-center"><h2>Bitte zuerst die Datei <span class="w3-tag w3-blue">install.php</span> löschen!</h2><p>... weiter zu <a href="index.php"><b>Callora<span style="color:#b3b2b2;">C</span><span style="color:#878786;">T</span><span style="color:#23b0e6;">I</span></b></a> ...</p></div>');
}

  if (isset($_POST['submit'])) {
    extract($_POST, EXTR_OVERWRITE);
    $query = mysqli_query($db_conn,"SELECT * FROM adm_benutzer WHERE username='$username'");
    $array = mysqli_fetch_array($query);
    $salt = $array['salt'];
    $password = $salt . $_POST['password'];
    $password = hash('sha512', $password);

    $query = mysqli_query($db_conn,"SELECT * FROM adm_benutzer WHERE username='$username' AND password='$password'");
    $num_rows = mysqli_num_rows($query);
    $userarray = mysqli_fetch_array($query);

    if ($num_rows == '1') {
      $_SESSION['signon'] = true;
      $_SESSION['username'] = $username;
      $_SESSION['level'] = $userarray['level'];
      $_SESSION['id'] = $userarray['id'];
      $_SESSION['nst'] = $userarray['nst'];

      if($userarray['level']==9) {
        header('Location: adm_benutzer.php');
      } else {
        header('Location: anrufliste.php');
      }
    } else {
      $feedback = '<b>Fehler:</b> Benutzername existiert nicht oder falsches Kennwort';
      session_destroy();
    }
  } 
  
?>

<div class="w3-content" style="max-width:2000px;margin-top:46px">
  <div class="w3-container w3-content w3-padding-64" style="max-width:800px" id="Anmelden">
    <div class="w3-row" ><span style="padding: 70px 0;font-size:2em"><b>Callora<span style="color:#b3b2b2;">C</span><span style="color:#878786;">T</span><span style="color:#23b0e6;">I</span></b></span></div>
<br />
<br />
<form name="login" action="login.php" method="post">
  <p><label><b>Benutzername:</b></label><input class="w3-input" name="username" type="text" /></p>
  <p><label><b>Passwort:</b></label><input class="w3-input" name="password" type="password" /></p>
  <p><input type="hidden" name="submit"><button class="w3-btn w3-blue" type="submit">Anmelden</button></p>
</form>
</div>
<?php 
  include('footer.php'); 
?>
